<?php
// app/Models/PengajuanModel.php (FINALIZED: Added Riwayat Approval Functions)
namespace App\Models;

use PDO;

class PengajuanModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Buat pengajuan kegiatan baru dari usulan yang disetujui
     */
    public function create($data)
    {
        // status_ppk dan status_wd2 otomatis default ke 'Menunggu'
        $stmt = $this->db->prepare("
            INSERT INTO pengajuan_kegiatan 
            (usulan_id, penanggung_jawab, pelaksana_kegiatan, tanggal_mulai, tanggal_selesai, surat_pengantar_path) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['usulan_id'],
            $data['penanggung_jawab'],
            $data['pelaksana_kegiatan'],
            $data['tanggal_mulai'],
            $data['tanggal_selesai'],
            $data['surat_pengantar_path']
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Ambil semua pengajuan dengan status PPK/WD2 tertentu
     */
    public function getByStatus($role_status)
    {
        $sql = "
            SELECT p.*, u.nama_kegiatan, u.nominal_pencairan, u.kode_mak, 
                   us.username, mj.nama_jurusan
            FROM pengajuan_kegiatan p
            JOIN usulan_kegiatan u ON p.usulan_id = u.id
            JOIN users us ON u.user_id = us.id
            LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id
        ";
        
        $params = [];
        
        // Logika status: Menunggu PPK berarti status_ppk = Menunggu
        if ($role_status === 'Menunggu PPK') {
            $sql .= " WHERE p.status_ppk = 'Menunggu' ORDER BY p.created_at ASC";
        } 
        // Logika status: Menunggu WD2 berarti status_ppk = Disetujui DAN status_wd2 = Menunggu
        elseif ($role_status === 'Menunggu WD2') {
            $sql .= " WHERE p.status_ppk = 'Disetujui' AND p.status_wd2 = 'Menunggu' ORDER BY p.tgl_status_ppk ASC";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil detail pengajuan by ID
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, u.id as usulan_id, u.nama_kegiatan, u.gambaran_umum, u.nominal_pencairan, u.kode_mak, 
                   u.status_terkini as usulan_status,
                   us.username, mj.nama_jurusan
            FROM pengajuan_kegiatan p
            JOIN usulan_kegiatan u ON p.usulan_id = u.id
            JOIN users us ON u.user_id = us.id
            LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id
            WHERE p.id = ?
        ");
        
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update status pengajuan oleh PPK/WD2 (Mencatat Rekomendasi/Tgl)
     */
    public function updateStatus($id, $status, $catatan = null, $role = null)
    {
        $params = ['id' => $id];
        $sql = "UPDATE pengajuan_kegiatan SET updated_at = NOW()";
        
        if ($role === 'PPK') {
            // Kolom status_ppk dan tgl_status_ppk digunakan
            $sql .= ", status_ppk = :status, tgl_status_ppk = NOW(), rekomendasi_ppk = :catatan";
            $params['status'] = $status; 
            $params['catatan'] = $catatan;
        } elseif ($role === 'WD2') {
             // Kolom status_wd2 dan tgl_status_wd2 digunakan
             $sql .= ", status_wd2 = :status, tgl_status_wd2 = NOW(), rekomendasi_wd2 = :catatan";
            $params['status'] = $status; 
            $params['catatan'] = $catatan;
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
     
    /**
     * Mengambil riwayat persetujuan untuk WD2 (Semua status selain Menunggu)
     */
    public function getRiwayatByStatusWD2()
    {
        $sql = "
            SELECT p.*, u.nama_kegiatan, u.nominal_pencairan, us.username, us.nama, mj.nama_jurusan
            FROM pengajuan_kegiatan p
            JOIN usulan_kegiatan u ON p.usulan_id = u.id
            JOIN users us ON u.user_id = us.id
            LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id
            WHERE p.status_wd2 = 'Disetujui' 
            ORDER BY p.tgl_status_wd2 DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // app/Models/PengajuanModel.php

// ... (kode yang sudah ada)

    /**
     * OPTIMASI: Mengambil riwayat dengan Filter dan Limit langsung di SQL
     */
    public function getRiwayatByStatusPPKOptimized($limit, $offset, $search = null, $filterProposer = null)
    {
        $sql = "
            SELECT p.*, u.nama_kegiatan, u.nominal_pencairan, us.username, us.nama, mj.nama_jurusan
            FROM pengajuan_kegiatan p
            JOIN usulan_kegiatan u ON p.usulan_id = u.id
            JOIN users us ON u.user_id = us.id
            LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id
            WHERE p.status_ppk = 'Disetujui'
        ";

        $params = [];

        // 1. Filter dilakukan di SQL, bukan di PHP
        if (!empty($search)) {
            $sql .= " AND u.nama_kegiatan LIKE :search";
            $params['search'] = "%$search%";
        }

        if (!empty($filterProposer)) {
            $sql .= " AND us.username = :user";
            $params['user'] = $filterProposer;
        }

        // 2. Sorting di SQL
        $sql .= " ORDER BY p.tgl_status_ppk DESC";

        // 3. Limit (Paging) dilakukan di SQL
        $sql .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        // Binding parameter khusus untuk integer (Limit/Offset harus int)
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue('limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * [WAJIB ADA] Hitung total data untuk Pagination
     * Fungsi ini menghitung angka "322" agar sistem tahu ada berapa halaman.
     */
    public function countRiwayatByStatusPPK($search = null, $filterProposer = null)
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM pengajuan_kegiatan p
            JOIN usulan_kegiatan u ON p.usulan_id = u.id
            JOIN users us ON u.user_id = us.id
            WHERE p.status_ppk = 'Disetujui'
        ";

        $params = [];
        
        // Filter Pencarian (harus sama persis dengan fungsi get data)
        if (!empty($search)) {
            $sql .= " AND u.nama_kegiatan LIKE :search";
            $params['search'] = "%$search%";
        }

        // Filter Pengusul
        if (!empty($filterProposer)) {
            $sql .= " AND us.username = :user";
            $params['user'] = $filterProposer;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total']; // Mengembalikan angka 322
    }
}