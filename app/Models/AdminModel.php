<?php
namespace App\Models;
use PDO;

class AdminModel {
    private $db;
    public function __construct($db) { $this->db = $db; }

    // =========================================================================
    // 1. MANAJEMEN PENGGUNA (USER)
    // =========================================================================
    
    public function getUsers($search = '', $jurusanId = null) {
        $sql = "SELECT u.*, j.nama_jurusan FROM users u LEFT JOIN master_jurusan j ON u.jurusan_id = j.id WHERE u.is_active = 1"; // Default hanya user aktif, kecuali filter lain dibutuhkan
        // Note: Logic filter bisa disesuaikan kebutuhan, di sini kita ambil dasar dulu
        $params = [];
        
        // Override query jika ada pencarian (sesuai controller lama)
        $sql = "SELECT u.*, j.nama_jurusan FROM users u LEFT JOIN master_jurusan j ON u.jurusan_id = j.id WHERE 1=1";
        
        if ($search) {
            $sql .= " AND (u.username LIKE :s OR u.email LIKE :s)";
            $params['s'] = "%$search%";
        }
        if ($jurusanId) {
            $sql .= " AND u.jurusan_id = :j";
            $params['j'] = $jurusanId;
        }
        $sql .= " ORDER BY u.id DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createUser($data) {
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, role, jurusan_id, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        return $stmt->execute([$data['username'], $data['email'], $data['password'], $data['role'], $data['jurusan_id']]);
    }

    public function updateUser($id, $role, $jurusanId, $password = null) {
        $sql = "UPDATE users SET role = :role, jurusan_id = :jid";
        $params = ['role' => $role, 'jid' => $jurusanId, 'id' => $id];

        if ($password) {
            $sql .= ", password = :pwd";
            $params['pwd'] = $password;
        }
        $sql .= " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function softDeleteUser($id) {
        $stmt = $this->db->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // =========================================================================
    // 2. MASTER DATA (JURUSAN & IKU)
    // =========================================================================

    public function getAllJurusan() {
        return $this->db->query("SELECT * FROM master_jurusan ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createJurusan($nama) {
        $stmt = $this->db->prepare("INSERT INTO master_jurusan (nama_jurusan) VALUES (?)");
        return $stmt->execute([$nama]);
    }

    public function updateJurusan($id, $nama) {
        $stmt = $this->db->prepare("UPDATE master_jurusan SET nama_jurusan = ? WHERE id = ?");
        return $stmt->execute([$nama, $id]);
    }

    public function deleteJurusan($id) {
        // Cek User aktif
        $cek = $this->db->prepare("SELECT COUNT(*) FROM users WHERE jurusan_id = ? AND is_active = 1");
        $cek->execute([$id]);
        if ($cek->fetchColumn() > 0) return false; 

        $stmt = $this->db->prepare("DELETE FROM master_jurusan WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllIku() {
        return $this->db->query("SELECT * FROM master_iku ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkIkuExists($deskripsi, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM master_iku WHERE deskripsi_iku = :desc";
        $params = [':desc' => $deskripsi];
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function createIku($deskripsi) {
        if ($this->checkIkuExists($deskripsi)) return false;
        $stmt = $this->db->prepare("INSERT INTO master_iku (deskripsi_iku) VALUES (?)");
        return $stmt->execute([$deskripsi]);
    }

    public function updateIku($id, $deskripsi) {
        if ($this->checkIkuExists($deskripsi, $id)) return false;
        $stmt = $this->db->prepare("UPDATE master_iku SET deskripsi_iku = ? WHERE id = ?");
        return $stmt->execute([$deskripsi, $id]);
    }

    public function toggleIkuStatus($id, $newStatus) {
        $stmt = $this->db->prepare("UPDATE master_iku SET status = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $id]);
    }

    public function deleteIku($id) {
        $cek = $this->db->prepare("SELECT COUNT(*) FROM tor_iku WHERE iku_id = ?");
        $cek->execute([$id]);
        if ($cek->fetchColumn() > 0) return false;

        $stmt = $this->db->prepare("DELETE FROM master_iku WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // =========================================================================
    // 3. AUDIT LOG SYSTEM (OPTIMIZED)
    // =========================================================================

    private function buildLogQuery($filters) {
        $sql = " FROM log_audit_sistem l JOIN users u ON l.user_id = u.id WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user'])) { 
            $sql .= " AND u.username LIKE :user"; 
            $params['user'] = "%{$filters['user']}%"; 
        }
        if (!empty($filters['action'])) { 
            $sql .= " AND l.aksi LIKE :action"; 
            $params['action'] = "%{$filters['action']}%"; 
        }
        if (!empty($filters['date'])) { 
            $sql .= " AND DATE(l.timestamp) = :date"; 
            $params['date'] = $filters['date']; 
        }
        return ['sql' => $sql, 'params' => $params];
    }

    public function getAuditLogs($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $query = $this->buildLogQuery($filters);
        
        $sql = "SELECT l.*, u.username " . $query['sql'] . " ORDER BY l.timestamp DESC LIMIT :offset, :perPage";
        
        $stmt = $this->db->prepare($sql);
        foreach ($query['params'] as $k => $v) { $stmt->bindValue(":$k", $v); }
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAuditLogs($filters = []) {
        $query = $this->buildLogQuery($filters);
        $sql = "SELECT COUNT(*) " . $query['sql'];
        
        $stmt = $this->db->prepare($sql);
        foreach ($query['params'] as $k => $v) { $stmt->bindValue(":$k", $v); }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getAllLogsForExport() {
        // Limit 1000 terakhir untuk keamanan memori
        $sql = "SELECT l.timestamp, u.username, l.aksi, l.ip_address 
                FROM log_audit_sistem l 
                JOIN users u ON l.user_id = u.id 
                ORDER BY l.timestamp DESC LIMIT 1000";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}