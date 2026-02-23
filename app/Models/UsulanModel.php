<?php
// app/Models/UsulanModel.php (FIXED: Added 'disetujui' count to getUserStats)
namespace App\Models;

use PDO;

class UsulanModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // --- CORE FILTER LOGIC ---
    private function buildWhereClause($filters, &$params)
    {
        $sql = " WHERE 1=1";
        if (!empty($filters['user_id'])) { 
            $sql .= " AND u.user_id = :uid";
            $params['uid'] = $filters['user_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (u.nama_kegiatan LIKE :q1 OR us.username LIKE :q2)"; 
            $params['q1'] = "%" . $filters['search'] . "%";
            $params['q2'] = "%" . $filters['search'] . "%";
        }

        if (!empty($filters['status'])) {
            $status = $filters['status'];
            
            // Logika Filter Status Kompleks
            if ($status === 'Menunggu PPK') {
                $sql .= " AND u.status_terkini = 'Disetujui' 
                        AND EXISTS (SELECT 1 FROM pengajuan_kegiatan p WHERE p.usulan_id = u.id AND p.status_ppk = 'Menunggu')";
            } 
            elseif ($status === 'Menunggu WD2') {
                $sql .= " AND EXISTS (SELECT 1 FROM pengajuan_kegiatan p WHERE p.usulan_id = u.id AND p.status_ppk = 'Disetujui' AND p.status_wd2 = 'Menunggu')";
            }
            elseif ($status === 'Siap Pencairan') {
                $sql .= " AND EXISTS (SELECT 1 FROM pengajuan_kegiatan p WHERE p.usulan_id = u.id AND p.status_wd2 = 'Disetujui')";
            }
            elseif ($status === 'LPJ Wajib Upload') {
                $sql .= " AND EXISTS (SELECT 1 FROM pengajuan_kegiatan p 
                            JOIN pencairan_dana pc ON p.id = pc.pengajuan_id 
                            LEFT JOIN lpj l ON pc.id = l.pencairan_id
                            WHERE p.usulan_id = u.id AND (l.status_terkini IS NULL OR l.status_terkini = 'Belum Upload'))";
            }
            elseif ($status === 'Selesai') {
                $sql .= " AND (u.status_terkini = 'Selesai' OR EXISTS (SELECT 1 FROM lpj l JOIN pencairan_dana pc ON l.pencairan_id = pc.id JOIN pengajuan_kegiatan p ON p.id = pc.pengajuan_id WHERE p.usulan_id = u.id AND l.status_terkini = 'Disetujui'))";
            }
            else {
                // Status standar (Draft, Diajukan, Revisi, Ditolak, Disetujui)
                $sql .= " AND u.status_terkini = :status";
                $params['status'] = $status;
            }
        }
        
        if (!empty($filters['date'])) {
            $sql .= " AND DATE(u.created_at) = :fdate";
            $params['fdate'] = $filters['date'];
        }
        
        if (empty($filters['user_id']) && !empty($filters['jurusan_id'])) {
            $sql .= " AND us.jurusan_id = :jid";
            $params['jid'] = $filters['jurusan_id'];
        }
        
        if (empty($filters['user_id']) && !empty($filters['year'])) {
             $sql .= " AND YEAR(u.created_at) = :fyear";
             $params['fyear'] = $filters['year'];
        }
        return $sql;
    }

    public function syncTotalRAB($usulanId, $userId = null)
    {
        $userId = $userId ?? ($_SESSION['user_id'] ?? 0);
        $stmt = $this->db->prepare("CALL sp_finalisasi_rab(?, ?)");
        return $stmt->execute([$usulanId, $userId]);
    }

    public function changeStatus($usulanId, $userId, $statusBaru, $catatan)
    {
        $stmt = $this->db->prepare("CALL sp_ubah_status_usulan(?, ?, ?, ?)");
        return $stmt->execute([
            $usulanId, $userId, $statusBaru, $catatan
        ]);
    }

    public function getAllWithUser($filters = [], $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        
        if (isset($_GET['q'])) {
             $filters['search'] = $_GET['q'];
        }

        $whereClause = $this->buildWhereClause($filters, $params);
        
        $sql = "SELECT u.*, us.username, mj.nama_jurusan, 
                       (SELECT pc.tanggal_batas_lpj FROM pencairan_dana pc 
                        JOIN pengajuan_kegiatan p ON p.id = pc.pengajuan_id 
                        WHERE p.usulan_id = u.id ORDER BY pc.tanggal_pencairan DESC LIMIT 1) as tgl_batas_lpj,
                       
                       (SELECT COALESCE(p.status_ppk, '') FROM pengajuan_kegiatan p 
                        WHERE p.usulan_id = u.id ORDER BY p.created_at DESC LIMIT 1) as pengajuan_status_ppk,
                       (SELECT COALESCE(p.status_wd2, '') FROM pengajuan_kegiatan p 
                        WHERE p.usulan_id = u.id ORDER BY p.created_at DESC LIMIT 1) as pengajuan_status_wd2,

                        (SELECT COALESCE(p.tgl_status_ppk, '') FROM pengajuan_kegiatan p 
                        WHERE p.usulan_id = u.id ORDER BY p.created_at DESC LIMIT 1) as tgl_status_ppk,
                        (SELECT COALESCE(p.tgl_status_wd2, '') FROM pengajuan_kegiatan p 
                        WHERE p.usulan_id = u.id ORDER BY p.created_at DESC LIMIT 1) as tgl_status_wd2,

                       (SELECT COALESCE(p.created_at, '') FROM pengajuan_kegiatan p 
                        WHERE p.usulan_id = u.id ORDER BY p.created_at DESC LIMIT 1) as tgl_pengajuan_ppk,

                        (SELECT COALESCE(p.penanggung_jawab, '') FROM pengajuan_kegiatan p 
                         WHERE p.usulan_id = u.id ORDER BY p.created_at DESC LIMIT 1) as penanggung_jawab,
                        (SELECT COALESCE(p.pelaksana_kegiatan, '') FROM pengajuan_kegiatan p 
                         WHERE p.usulan_id = u.id ORDER BY p.created_at DESC LIMIT 1) as pelaksana_kegiatan,
                        
                       (SELECT COALESCE(SUM(pc.nominal_dicairkan), 0) FROM pencairan_dana pc 
                        JOIN pengajuan_kegiatan p ON p.id = pc.pengajuan_id
                        WHERE p.usulan_id = u.id) as total_sudah_cair,
                        
                       (SELECT COALESCE(l.status_terkini, '') FROM lpj l
                        JOIN pencairan_dana pc ON l.pencairan_id = pc.id
                        JOIN pengajuan_kegiatan p ON p.id = pc.pengajuan_id
                        WHERE p.usulan_id = u.id ORDER BY l.created_at DESC LIMIT 1) as lpj_status,
                        
                       (SELECT pc.tanggal_pencairan FROM pencairan_dana pc 
                        JOIN pengajuan_kegiatan p ON p.id = pc.pengajuan_id 
                        WHERE p.usulan_id = u.id ORDER BY pc.tanggal_pencairan ASC LIMIT 1) as tgl_cair_pertama
                FROM usulan_kegiatan u 
                JOIN users us ON u.user_id = us.id
                LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id" 
                . $whereClause 
                . " ORDER BY u.updated_at DESC, u.id DESC LIMIT :offset, :perPage";

        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);

        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function countAllWithUser($filters = [])
    {
        $params = [];
        if (isset($_GET['q'])) {
             $filters['search'] = $_GET['q'];
        }
        
        $whereClause = $this->buildWhereClause($filters, $params);
        $sql = "SELECT COUNT(*) FROM usulan_kegiatan u 
                JOIN users us ON u.user_id = us.id
                LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id" . $whereClause;
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(":$k", $v);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    public function getUsulanGlobalStats()
    {
        // [PERBAIKAN] total_usulan mengecualikan status 'Draft'
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM usulan_kegiatan WHERE status_terkini != 'Draft') as total_usulan,
                    SUM(CASE WHEN status_terkini = 'Draft' THEN 1 ELSE 0 END) as draft,
                    SUM(CASE WHEN status_terkini = 'Diajukan' THEN 1 ELSE 0 END) as diajukan,
                    SUM(CASE WHEN status_terkini = 'Revisi' THEN 1 ELSE 0 END) as revisi,
                    SUM(CASE WHEN status_terkini = 'Ditolak' THEN 1 ELSE 0 END) as ditolak,
                    SUM(CASE WHEN status_terkini = 'Disetujui' THEN 1 ELSE 0 END) as disetujui_verif,
                    SUM(CASE WHEN status_terkini = 'Diajukan' THEN 1 ELSE 0 END) as menunggu_verif
                FROM usulan_kegiatan";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getByStatus($statuses)
    {
        if (!is_array($statuses)) $statuses = [$statuses];
        $placeholders = [];
        $params = [];
        foreach ($statuses as $k => $val) {
            $key = ":status_" . $k;
            $placeholders[] = $key;
            $params[$key] = $val;
        }
        $sql = "SELECT u.*, us.username, mj.nama_jurusan FROM usulan_kegiatan u 
                JOIN users us ON u.user_id = us.id 
                LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id
                WHERE u.status_terkini IN (" . implode(',', $placeholders) . ") ORDER BY u.updated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT u.*, us.username, mj.nama_jurusan FROM usulan_kegiatan u JOIN users us ON u.user_id = us.id LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id WHERE u.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getMasterKategori()
    {
        $sql = "SELECT id, nama_kategori FROM master_kategori_anggaran ORDER BY id ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getMasterIku()
    {
        $sql = "SELECT id, deskripsi_iku, status FROM master_iku WHERE status = 'active' ORDER BY deskripsi_iku ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getMasterSatuan()
    {
        $sql = "SELECT id, nama_satuan FROM master_satuan_anggaran WHERE is_active = 1 ORDER BY nama_satuan ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createUsulan($data)
    {
        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO usulan_kegiatan ({$fields}, created_at) VALUES ({$placeholders}, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    public function updateUsulan($id, $data)
    {
        $setClauses = [];
        foreach (array_keys($data) as $field) {
            $setClauses[] = "{$field} = :{$field}";
        }
        $setClause = implode(', ', $setClauses);
        
        $sql = "UPDATE usulan_kegiatan SET {$setClause} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $params = array_merge($data, ['id' => $id]);
        return $stmt->execute($params);
    }
    
    public function getRABDetails($usulanId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                r.*, mk.nama_kategori, 
                COALESCE(r.satuan_custom, s_final.nama_satuan) as nama_satuan, 
                COALESCE(r.satuan_factor_1_custom, s1.nama_satuan) as nama_satuan_f1, 
                COALESCE(r.satuan_factor_2_custom, s2.nama_satuan) as nama_satuan_f2
            FROM rab_detail r
            JOIN master_kategori_anggaran mk ON r.kategori_id = mk.id
            LEFT JOIN master_satuan_anggaran s_final ON r.satuan_id = s_final.id
            LEFT JOIN master_satuan_anggaran s1 ON r.satuan_factor_1_id = s1.id
            LEFT JOIN master_satuan_anggaran s2 ON r.satuan_factor_2_id = s2.id
            WHERE r.usulan_id = ? 
            ORDER BY mk.id ASC, r.id ASC
        ");
        $stmt->execute([$usulanId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function deleteRABDetail($usulanId)
    {
        $stmt = $this->db->prepare("DELETE FROM rab_detail WHERE usulan_id = ?");
        return $stmt->execute([$usulanId]);
    }
    
    public function insertRABDetail($usulanId, $rabDetails)
    {
        if (empty($rabDetails)) return true;

        $sql = "INSERT INTO rab_detail 
                (usulan_id, kategori_id, deskripsi, volume, 
                 satuan_id, satuan_custom, 
                 harga_satuan, 
                 volume_factor_1, satuan_factor_1_id, satuan_factor_1_custom, 
                 volume_factor_2, satuan_factor_2_id, satuan_factor_2_custom, 
                 total) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $success = true;
        foreach ($rabDetails as $item) {
            
            if (empty($item['uraian']) || $item['total_harga'] <= 0 || $item['kategori_id'] <= 0) continue;
            
            $res = $stmt->execute([
                $usulanId, 
                $item['kategori_id'],
                $item['uraian'], 
                $item['total_volume'], 
                $item['satuan_id'],
                $item['satuan_custom'] ?? null, 
                $item['harga_satuan'],
                $item['volume_factor_1'],
                $item['satuan_factor_1_id'],
                $item['satuan_factor_1_custom'] ?? null,
                $item['volume_factor_2'],
                $item['satuan_factor_2_id'],
                $item['satuan_factor_2_custom'] ?? null,
                $item['total_harga']
            ]);
            if (!$res) $success = false;
        }
        return $success;
    }
    
    public function deleteTorIKU($usulanId)
    {
        $stmt = $this->db->prepare("DELETE FROM tor_iku WHERE usulan_id = ?");
        return $stmt->execute([$usulanId]);
    }
    
    public function insertTorIKU($usulanId, $ikuDetails)
    {
        if (empty($ikuDetails)) return true;

        $sql = "INSERT INTO tor_iku (usulan_id, iku_id, target) 
                VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $success = true;
        foreach ($ikuDetails as $item) {
            $res = $stmt->execute([
                $usulanId, 
                $item['iku_id'], 
                $item['target']
            ]);
            if (!$res) $success = false;
        }
        return $success;
    }
    
    public function getIKUDetails($usulanId)
    {
        $stmt = $this->db->prepare("
            SELECT t.*, m.deskripsi_iku 
            FROM tor_iku t 
            JOIN master_iku m ON t.iku_id = m.id 
            WHERE t.usulan_id = ?
        ");
        $stmt->execute([$usulanId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function addLog($usulanId, $userId, $oldStatus, $newStatus, $note, $refTable = 'usulan', $refId = 0)
    {
        $sql = "INSERT INTO log_histori_usulan (usulan_id, user_id, status_lama, status_baru, catatan, ref_table, ref_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usulanId, $userId, $oldStatus, $newStatus, $note, $refTable, $refId]);
    }

    public function getUserStats($userId)
    {
        // [PERBAIKAN] Menambahkan perhitungan count untuk status 'Disetujui'
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM usulan_kegiatan WHERE user_id = :uid1) as total,
                    (SELECT COUNT(*) FROM usulan_kegiatan WHERE user_id = :uid2 AND status_terkini = 'Diajukan') as diajukan,
                    (SELECT COUNT(*) FROM usulan_kegiatan WHERE user_id = :uid3 AND status_terkini = 'Draft') as draft,
                    (SELECT COUNT(*) FROM usulan_kegiatan WHERE user_id = :uid4 AND status_terkini = 'Revisi') as revisi,
                    (SELECT COUNT(*) FROM usulan_kegiatan WHERE user_id = :uid5 AND status_terkini = 'Ditolak') as ditolak,
                    (SELECT COUNT(*) FROM usulan_kegiatan WHERE user_id = :uid6 AND status_terkini = 'Disetujui') as disetujui
                FROM DUAL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'uid1' => $userId,
            'uid2' => $userId,
            'uid3' => $userId,
            'uid4' => $userId,
            'uid5' => $userId,
            'uid6' => $userId
        ]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $selesaiCount = $this->db->prepare("
            SELECT COUNT(u.id) FROM usulan_kegiatan u 
            JOIN pengajuan_kegiatan p ON u.id = p.usulan_id 
            LEFT JOIN pencairan_dana pc ON p.id = pc.pengajuan_id
            LEFT JOIN lpj l ON pc.id = l.pencairan_id
            WHERE u.user_id = ? AND l.status_terkini = 'Disetujui'
        ");
        $selesaiCount->execute([$userId]);
        $stats['selesai'] = $selesaiCount->fetchColumn() ?? 0;

        $customStats = [
            'total'     => $stats['total'],
            'draft'     => $stats['draft'],
            'diajukan'  => $stats['diajukan'],
            'revisi'    => $stats['revisi'],
            'ditolak'   => $stats['ditolak'],
            'disetujui' => $stats['disetujui'], // Ditambahkan agar view bisa membacanya
            'selesai'   => $stats['selesai'],
        ];
        
        return $customStats;
    }

    public function getRecentActivity($limit = 5)
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.nama_kegiatan, u.status_terkini, u.updated_at, u.nominal_pencairan,
                   us.username,
                   (SELECT COALESCE(p.status_ppk, '') FROM pengajuan_kegiatan p WHERE p.usulan_id = u.id ORDER BY p.created_at DESC LIMIT 1) as pengajuan_status_ppk,
                   (SELECT COALESCE(p.status_wd2, '') FROM pengajuan_kegiatan p WHERE p.usulan_id = u.id ORDER BY p.created_at DESC LIMIT 1) as pengajuan_status_wd2,
                   (SELECT COALESCE(SUM(pc.nominal_dicairkan), 0) FROM pencairan_dana pc JOIN pengajuan_kegiatan p ON p.id = pc.pengajuan_id WHERE p.usulan_id = u.id) as total_sudah_cair,
                   (SELECT COALESCE(l.status_terkini, '') FROM lpj l JOIN pencairan_dana pc ON l.pencairan_id = pc.id JOIN pengajuan_kegiatan p ON p.id = pc.pengajuan_id WHERE p.usulan_id = u.id ORDER BY l.created_at DESC LIMIT 1) as lpj_status
            FROM usulan_kegiatan u 
            JOIN users us ON u.user_id = us.id 
            ORDER BY u.updated_at DESC LIMIT :lim
        ");
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProposerStats($year = null)
    {
        $params = [];
        $where = " WHERE 1=1 ";
        if ($year) {
            $where .= " AND YEAR(u.created_at) = :year";
            $params['year'] = $year;
        }

        $sql = "SELECT
                    us.username,
                    mj.nama_jurusan,
                    COUNT(u.id) as total_usulan,
                    
                    SUM(CASE WHEN (
                        SELECT l.status_terkini FROM lpj l
                        JOIN pencairan_dana pc ON l.pencairan_id = pc.id
                        JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id
                        WHERE p.usulan_id = u.id ORDER BY l.id DESC LIMIT 1
                    ) = 'Disetujui' THEN 1 ELSE 0 END) as total_selesai,

                    SUM(CASE WHEN u.status_terkini != 'Draft' AND COALESCE((
                        SELECT l.status_terkini FROM lpj l
                        JOIN pencairan_dana pc ON l.pencairan_id = pc.id
                        JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id
                        WHERE p.usulan_id = u.id ORDER BY l.id DESC LIMIT 1
                    ), '') != 'Disetujui' THEN 1 ELSE 0 END) as total_berlangsung

                FROM usulan_kegiatan u
                JOIN users us ON u.user_id = us.id
                LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id
                {$where}
                GROUP BY us.id, us.username, mj.nama_jurusan
                ORDER BY total_usulan DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBudgetDistribution()
    {
        $sql = "SELECT k.nama_kategori, COALESCE(SUM(r.total), 0) as total_anggaran
                FROM rab_detail r
                JOIN master_kategori_anggaran k ON r.kategori_id = k.id
                JOIN usulan_kegiatan u ON r.usulan_id = u.id
                WHERE u.status_terkini IN ('Disetujui')
                GROUP BY k.id, k.nama_kategori
                ORDER BY total_anggaran DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- NEW: Total RAB Submitted (for Report) ---
    public function getTotalRABSubmitted()
    {
        // Hitung total RAB untuk semua usulan yang sudah diajukan (Status != Draft)
        $sql = "SELECT COALESCE(SUM(nominal_pencairan), 0) FROM usulan_kegiatan WHERE status_terkini != 'Draft'";
        return $this->db->query($sql)->fetchColumn();
    }

    // --- NEW: Budget Distribution per Proposer (for Pie Charts) ---
    public function getBudgetDistributionByProposer()
    {
        $sql = "SELECT k.nama_kategori, us.username, mj.nama_jurusan, COALESCE(SUM(r.total), 0) as total_anggaran
                FROM rab_detail r
                JOIN master_kategori_anggaran k ON r.kategori_id = k.id
                JOIN usulan_kegiatan u ON r.usulan_id = u.id
                JOIN users us ON u.user_id = us.id
                LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id
                WHERE u.status_terkini IN ('Disetujui')
                GROUP BY k.id, k.nama_kategori, us.id, us.username
                ORDER BY k.nama_kategori, total_anggaran DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}