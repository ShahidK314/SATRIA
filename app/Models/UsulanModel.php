<?php
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

        // Filter Khusus Pengusul (Hanya lihat data sendiri)
        if (!empty($filters['role']) && $filters['role'] === 'Pengusul' && !empty($filters['user_id'])) {
            $sql .= " AND u.user_id = :uid";
            $params['uid'] = $filters['user_id'];
        }
        // Pencarian Global
        if (!empty($filters['search'])) {
            $sql .= " AND (u.nama_kegiatan LIKE :q OR us.username LIKE :q)";
            $params['q'] = "%" . $filters['search'] . "%";
        }
        // Filter Status
        if (!empty($filters['status'])) {
            $sql .= " AND u.status_terkini = :status";
            $params['status'] = $filters['status'];
        }
        // Filter Tanggal
        if (!empty($filters['date'])) {
            $sql .= " AND DATE(u.created_at) = :fdate";
            $params['fdate'] = $filters['date'];
        }

        return $sql;
    }

    public function getAllWithUser($filters = [], $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereClause = $this->buildWhereClause($filters, $params);
        
        $sql = "SELECT u.*, us.username 
                FROM usulan_kegiatan u 
                JOIN users us ON u.user_id = us.id" 
                . $whereClause 
                . " ORDER BY u.updated_at DESC, u.id DESC LIMIT :offset, :perPage";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue(":$k", $v);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAllWithUser($filters = [])
    {
        $params = [];
        $whereClause = $this->buildWhereClause($filters, $params);
        $sql = "SELECT COUNT(*) FROM usulan_kegiatan u JOIN users us ON u.user_id = us.id" . $whereClause;
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue(":$k", $v);
        $stmt->execute();
        return $stmt->fetchColumn();
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
        
        $sql = "SELECT u.*, us.username 
                FROM usulan_kegiatan u 
                JOIN users us ON u.user_id = us.id 
                WHERE u.status_terkini IN (" . implode(',', $placeholders) . ") 
                ORDER BY u.updated_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- DATA DETAIL & DOKUMEN ---
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM usulan_kegiatan WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // [BARU] Fitur Dokumen
    public function getDocuments($usulanId)
    {
        $stmt = $this->db->prepare("SELECT * FROM dokumen_pendukung WHERE usulan_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$usulanId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addDocument($usulanId, $jenis, $path)
    {
        $stmt = $this->db->prepare("INSERT INTO dokumen_pendukung (usulan_id, jenis_dokumen, file_path) VALUES (?, ?, ?)");
        return $stmt->execute([$usulanId, $jenis, $path]);
    }

    // --- STATISTIK & TOOLS ---
    public function getDashboardStats()
    {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM usulan_kegiatan) as total,
                    (SELECT COUNT(*) FROM usulan_kegiatan WHERE status_terkini='Selesai') as selesai,
                    (SELECT COALESCE(SUM(nominal_pencairan), 0) FROM usulan_kegiatan WHERE status_terkini IN ('Pencairan','LPJ','Selesai')) as dana_cair,
                    (SELECT COUNT(*) FROM usulan_kegiatan WHERE status_terkini='Draft') as draft";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserStats($userId)
    {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM usulan_kegiatan WHERE user_id = :uid) as total,
                    (SELECT COUNT(*) FROM usulan_kegiatan WHERE user_id = :uid AND status_terkini = 'Selesai') as selesai,
                    (SELECT COALESCE(SUM(nominal_pencairan), 0) FROM usulan_kegiatan WHERE user_id = :uid AND status_terkini IN ('Pencairan','LPJ','Selesai')) as dana_cair,
                    (SELECT COUNT(*) FROM usulan_kegiatan WHERE user_id = :uid AND status_terkini IN ('Draft', 'Revisi')) as draft";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRecentActivity($limit = 5)
    {
        $stmt = $this->db->prepare("SELECT u.nama_kegiatan, u.status_terkini, u.updated_at, us.username 
                                   FROM usulan_kegiatan u 
                                   JOIN users us ON u.user_id = us.id 
                                   ORDER BY u.updated_at DESC LIMIT :lim");
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cairkanDana($id, $tglCair, $tglLpj, $nominal)
    {
        $sql = "UPDATE usulan_kegiatan 
                SET status_terkini = 'Pencairan', 
                    tgl_pencairan = :tc, 
                    tgl_batas_lpj = :tl,
                    nominal_pencairan = :nom 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tc' => $tglCair, 'tl' => $tglLpj, 'nom' => $nominal, 'id' => $id]);
    }

    public function selesaikanLPJ($id)
    {
        $stmt = $this->db->prepare("UPDATE usulan_kegiatan SET status_terkini = 'Selesai' WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function addLog($usulanId, $userId, $oldStatus, $newStatus, $note)
    {
        $sql = "INSERT INTO log_histori_usulan (usulan_id, user_id, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usulanId, $userId, $oldStatus, $newStatus, $note]);
    }

    public function getOverdueItems()
    {
        $sql = "SELECT u.*, us.username, us.email 
                FROM usulan_kegiatan u 
                JOIN users us ON u.user_id = us.id 
                WHERE u.status_terkini IN ('Pencairan', 'LPJ') 
                AND u.tgl_batas_lpj IS NOT NULL 
                AND u.tgl_batas_lpj < CURDATE()
                ORDER BY u.tgl_batas_lpj ASC";
        
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}