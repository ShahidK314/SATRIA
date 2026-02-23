<?php
namespace App\Models;

use PDO;

class LpjModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // --- GET DATA UTAMA ---

    public function getById($lpjId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM lpj 
            WHERE id = ?
        ");
        $stmt->execute([$lpjId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getLpjUsulanData($lpjId)
    {
         $stmt = $this->db->prepare("
            SELECT 
                u.id as usulan_id, 
                u.user_id, 
                u.nominal_pencairan, 
                l.pencairan_id
            FROM lpj l
            JOIN pencairan_dana pc ON l.pencairan_id = pc.id
            JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id
            JOIN usulan_kegiatan u ON p.usulan_id = u.id
            WHERE l.id = ?
        ");
        $stmt->execute([$lpjId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- FITUR UPLOAD & UPDATE (SMART STORAGE) ---

    public function uploadOrUpdateDokumen($data)
    {
        // 1. Cek slot kosong (baris yang sudah ada tapi path-nya NULL/kosong)
        $stmt = $this->db->prepare("
            SELECT id 
            FROM lpj_dokumen 
            WHERE lpj_id = ? 
              AND rab_detail_id = ? 
              AND (file_path IS NULL OR file_path = '')
            ORDER BY id ASC 
            LIMIT 1
        ");
        $stmt->execute([$data['lpj_id'], $data['rab_detail_id']]);
        $emptyRowId = $stmt->fetchColumn();

        if ($emptyRowId) {
            // Update slot kosong tersebut
            $update = $this->db->prepare("
                UPDATE lpj_dokumen 
                SET file_path = ?, 
                    uploaded_at = NOW() 
                WHERE id = ?
            ");
            $update->execute([$data['file_path'], $emptyRowId]);
            return $emptyRowId;
        } else {
            // Jika tidak ada slot kosong, buat baris baru
            $stmt = $this->db->prepare("
                INSERT INTO lpj_dokumen (
                    lpj_id, 
                    rab_detail_id, 
                    kategori_id, 
                    file_path, 
                    nominal, 
                    keterangan, 
                    uploaded_at
                ) 
                VALUES (?, ?, ?, ?, 0, ?, NOW())
            ");
            $stmt->execute([
                $data['lpj_id'],
                $data['rab_detail_id'],
                $data['kategori_id'],
                $data['file_path'], 
                $data['keterangan'] ?? ''
            ]);
            return $this->db->lastInsertId();
        }
    }

    public function uploadDokumenGetId($data) {
        return $this->uploadOrUpdateDokumen($data); 
    }

    public function updateNominalItem($lpjId, $rabDetailId, $kategoriId, $totalNominal, $keterangan)
    {
        // Cari row pertama (Main Row) untuk menyimpan nominal total
        $stmt = $this->db->prepare("
            SELECT id 
            FROM lpj_dokumen 
            WHERE lpj_id = ? 
              AND rab_detail_id = ? 
            ORDER BY id ASC 
            LIMIT 1
        ");
        $stmt->execute([$lpjId, $rabDetailId]);
        $firstId = $stmt->fetchColumn();

        if ($firstId) {
            // Update Nominal & Keterangan di Row Pertama
            $this->db->prepare("
                UPDATE lpj_dokumen 
                SET nominal = ?, 
                    keterangan = ? 
                WHERE id = ?
            ")->execute([$totalNominal, $keterangan, $firstId]);
            
            // Pastikan row lain (file tambahan) nominalnya 0 agar tidak double counting
            $this->db->prepare("
                UPDATE lpj_dokumen 
                SET nominal = 0 
                WHERE lpj_id = ? 
                  AND rab_detail_id = ? 
                  AND id != ?
            ")->execute([$lpjId, $rabDetailId, $firstId]);
        } else {
            // Belum ada row sama sekali -> Buat row baru
            $this->db->prepare("
                INSERT INTO lpj_dokumen (
                    lpj_id, 
                    rab_detail_id, 
                    kategori_id, 
                    nominal, 
                    keterangan, 
                    file_path, 
                    uploaded_at
                ) 
                VALUES (?, ?, ?, ?, ?, '', NOW())
            ")->execute([$lpjId, $rabDetailId, $kategoriId, $totalNominal, $keterangan]);
        }
    }
    
    // --- VALIDASI ---

    public function checkMissingEvidence($lpjId)
    {
        // Cari item yang Realisasinya > 0 TAPI File Buktinya 0
        $stmt = $this->db->prepare("
            SELECT rab_detail_id
            FROM lpj_dokumen
            WHERE lpj_id = ?
            GROUP BY rab_detail_id
            HAVING SUM(nominal) > 0 
               AND COUNT(CASE WHEN file_path IS NOT NULL AND file_path != '' THEN 1 END) = 0
        ");
        $stmt->execute([$lpjId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function validateKelengkapan($lpjId, $usulanId)
    {
        $rabKategori = $this->getRabTotalPerKategori($usulanId);
        $lpjKategori = $this->getLpjTotalPerKategori($lpjId);
        $errors = [];
        
        foreach ($rabKategori as $catId => $data) {
            $totalRAB = floatval($data['total']);
            $totalLpj = floatval($lpjKategori[$catId] ?? 0);
            
            // VALIDASI KETAT: Tidak ada toleransi selisih
            if ($totalRAB > 0 && $totalLpj != $totalRAB) {
                $errors[] = "Kategori '{$data['nama_kategori']}' tidak sesuai. Target: " . number_format($totalRAB) . ", Realisasi: " . number_format($totalLpj);
            }
        }
        return $errors;
    }

    // --- HELPER DATA ---

    public function getItemRealisasi($lpjId, $rabDetailId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                SUM(nominal) as nominal_realisasi, 
                (SELECT keterangan 
                 FROM lpj_dokumen 
                 WHERE lpj_id = ? 
                   AND rab_detail_id = ? 
                 ORDER BY id ASC 
                 LIMIT 1) as keterangan
            FROM lpj_dokumen 
            WHERE lpj_id = ? 
              AND rab_detail_id = ?
        ");
        $stmt->execute([$lpjId, $rabDetailId, $lpjId, $rabDetailId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'nominal_realisasi' => $res['nominal_realisasi'] ?? 0,
            'keterangan' => $res['keterangan'] ?? ''
        ];
    }
    
    // Soft Delete (Hanya hapus path, row tetap ada untuk simpan history/catatan)
    public function deletePreserveNominal($dokumenId)
    {
        $stmt = $this->db->prepare("
            UPDATE lpj_dokumen 
            SET file_path = NULL 
            WHERE id = ?
        ");
        return $stmt->execute([$dokumenId]);
    }

    public function getDokumen($lpjId)
    {
        // Mengambil semua dokumen termasuk yang file_path NULL (soft deleted)
        $stmt = $this->db->prepare("
            SELECT ld.*, mk.nama_kategori 
            FROM lpj_dokumen ld
            JOIN master_kategori_anggaran mk ON ld.kategori_id = mk.id
            WHERE ld.lpj_id = ? 
            ORDER BY ld.uploaded_at DESC, ld.id DESC
        ");
        $stmt->execute([$lpjId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getLpjTotalPerKategori($lpjId)
    {
         $stmt = $this->db->prepare("
            SELECT kategori_id, SUM(nominal) as total 
            FROM lpj_dokumen 
            WHERE lpj_id = ? 
            GROUP BY kategori_id
        ");
        $stmt->execute([$lpjId]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    
    public function getRabTotalPerKategori($usulanId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                r.kategori_id, 
                k.nama_kategori, 
                SUM(r.total) as total
            FROM rab_detail r
            JOIN master_kategori_anggaran k ON r.kategori_id = k.id
            WHERE r.usulan_id = ?
            GROUP BY r.kategori_id, k.nama_kategori
        ");
        $stmt->execute([$usulanId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $output = [];
        foreach ($result as $row) {
            $output[$row['kategori_id']] = $row;
        }
        return $output;
    }

    // --- STATUS & VERIFIKASI ---

    public function submit($lpjId)
    {
        // PENTING: Set catatan_bendahara ke NULL agar tidak dianggap revisi lagi
        $stmt = $this->db->prepare("
            UPDATE lpj 
            SET status_terkini = 'Diajukan', 
                catatan_bendahara = NULL,
                tanggal_submit = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$lpjId]);
    }

    public function updateStatus($lpjId, $status, $catatan = null)
    {
        $stmt = $this->db->prepare("
            UPDATE lpj 
            SET status_terkini = ?, 
                catatan_bendahara = ?, 
                updated_at = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$status, $catatan, $lpjId]);
    }

    public function getPendingVerifikasi()
    {
        $stmt = $this->db->query("
            SELECT 
                l.*, 
                pc.nominal_dicairkan, 
                pc.tanggal_batas_lpj, 
                u.nama_kegiatan, 
                u.nominal_pencairan, 
                COALESCE(us.username, 'User Tidak Dikenal') as username, 
                COALESCE(mj.nama_jurusan, '-') as nama_jurusan,
                u.user_id
            FROM lpj l
            INNER JOIN pencairan_dana pc ON l.pencairan_id = pc.id
            LEFT JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id
            LEFT JOIN usulan_kegiatan u ON p.usulan_id = u.id
            LEFT JOIN users us ON u.user_id = us.id
            LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id
            WHERE l.status_terkini IN ('Diajukan')
            ORDER BY l.tanggal_submit ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getByStatus($status)
    {
        $stmt = $this->db->prepare("
            SELECT 
                l.*, 
                u.id as usulan_id, 
                u.nama_kegiatan, 
                us.username, 
                mj.nama_jurusan, 
                pc.nominal_dicairkan as nominal_pencairan
            FROM lpj l
            JOIN pencairan_dana pc ON l.pencairan_id = pc.id
            JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id
            JOIN usulan_kegiatan u ON p.usulan_id = u.id
            JOIN users us ON u.user_id = us.id
            LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id
            WHERE l.status_terkini = ?
            ORDER BY l.updated_at DESC
        ");
        $stmt->execute([$status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}