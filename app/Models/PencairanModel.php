<?php
namespace App\Models;

use PDO;

class PencairanModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Buat record pencairan dana baru
     * UPDATE: Menghapus kolom bukti_transfer_path dari query
     */
    public function create($data)
    {
        // Hapus referensi bukti_transfer_path pada INSERT
        $stmt = $this->db->prepare("
            INSERT INTO pencairan_dana 
            (pengajuan_id, nominal_dicairkan, tanggal_pencairan, tanggal_batas_lpj) 
            VALUES (?, ?, NOW(), ?)
        ");
        
        $stmt->execute([
            $data['pengajuan_id'],
            $data['nominal_dicairkan'],
            $data['tanggal_batas_lpj']
        ]);
        
        $pencairanId = $this->db->lastInsertId();
        
        // Cek apakah ini adalah pencairan PERTAMA untuk pengajuan ini
        // Cek ini untuk memicu pembuatan record LPJ PERTAMA
        $cekCount = $this->db->prepare("
            SELECT COUNT(*) FROM pencairan_dana WHERE pengajuan_id = ?
        ");
        $cekCount->execute([$data['pengajuan_id']]);
        $count = $cekCount->fetchColumn();

        // Jika ini adalah pencairan pertama (count = 1), lakukan pemeriksaan keamanan
        if ($count == 1) { 
             // SAFETY CHECK: Pastikan belum ada LPJ record untuk pengajuan ini di seluruh history pencairan.
             // Mencari LPJ yang terhubung ke pengajuan_id ini melalui tabel pencairan_dana.
             $cekLpjExists = $this->db->prepare("
                 SELECT COUNT(l.id)
                 FROM lpj l
                 JOIN pencairan_dana pc ON l.pencairan_id = pc.id
                 WHERE pc.pengajuan_id = ?
             ");
             $cekLpjExists->execute([$data['pengajuan_id']]);
             $lpjExists = $cekLpjExists->fetchColumn() > 0;
             
             // Hanya buat record LPJ jika belum ada.
             if (!$lpjExists) {
                 $this->db->prepare("INSERT INTO lpj (pencairan_id, status_terkini) VALUES (?, 'Belum Upload')")
                         ->execute([$pencairanId]);
             }
        }
        
        return $pencairanId;
    }

    /**
     * Ambil semua pengajuan yang siap dicairkan (sudah disetujui WD2)
     */
    public function getPendingCair()
    {
        $stmt = $this->db->query("
            SELECT p.*, u.nama_kegiatan, u.nominal_pencairan, u.kode_mak, 
                   us.username, mj.nama_jurusan,
                   (SELECT COALESCE(SUM(pc.nominal_dicairkan), 0) FROM pencairan_dana pc WHERE pc.pengajuan_id = p.id) as total_sudah_cair
            FROM pengajuan_kegiatan p
            JOIN usulan_kegiatan u ON p.usulan_id = u.id
            JOIN users us ON u.user_id = us.id
            LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id
            WHERE p.status_ppk = 'Disetujui' AND p.status_wd2 = 'Disetujui'
            ORDER BY p.updated_at ASC
        ");
        
        // Hanya kembalikan yang total pencairan < nominal usulan (Bertahap/Pertama)
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_filter($result, function($item) {
            return $item['total_sudah_cair'] < $item['nominal_pencairan'];
        });
    }

    /**
     * Ambil SEMUA kegiatan yang sudah disetujui WD2, beserta status LPJ dan tenggat waktu.
     */
    public function getBendaharaActivities()
    {
        // Fetch all pengajuan that passed WD2, their usulan details, total disbursed, and LPJ status/deadline
        $sql = "
            SELECT 
                p.id as pengajuan_id, p.penanggung_jawab, p.status_ppk, p.status_wd2,
                u.id as usulan_id, u.nama_kegiatan, u.nominal_pencairan, u.kode_mak,
                us.username, mj.nama_jurusan,
                (SELECT COALESCE(SUM(pc.nominal_dicairkan), 0) FROM pencairan_dana pc WHERE pc.pengajuan_id = p.id) as total_sudah_cair,
                (SELECT pc.tanggal_batas_lpj FROM pencairan_dana pc 
                 WHERE pc.pengajuan_id = p.id ORDER BY pc.tanggal_pencairan ASC LIMIT 1) as tgl_batas_lpj,
                (SELECT COALESCE(l.status_terkini, 'Belum Upload') FROM lpj l
                 JOIN pencairan_dana pc ON l.pencairan_id = pc.id
                 WHERE pc.pengajuan_id = p.id ORDER BY l.updated_at DESC LIMIT 1) as lpj_status_terkini
            FROM pengajuan_kegiatan p
            JOIN usulan_kegiatan u ON p.usulan_id = u.id
            JOIN users us ON u.user_id = us.id
            LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id
            WHERE p.status_ppk = 'Disetujui' AND p.status_wd2 = 'Disetujui'
            ORDER BY u.updated_at DESC
        ";
        
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil total dana yang sudah dicairkan untuk suatu pengajuan kegiatan
     */
    public function getTotalDisbursed($pengajuanId)
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(nominal_dicairkan), 0) 
            FROM pencairan_dana 
            WHERE pengajuan_id = ?
        ");
        $stmt->execute([$pengajuanId]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Ambil detail pencairan by ID
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT pc.*, p.*, u.nama_kegiatan, u.nominal_pencairan, 
                   us.username, mj.nama_jurusan
            FROM pencairan_dana pc
            JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id
            JOIN usulan_kegiatan u ON p.usulan_id = u.id
            JOIN users us ON u.user_id = us.id
            LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id
            WHERE pc.id = ?
        ");
        
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}