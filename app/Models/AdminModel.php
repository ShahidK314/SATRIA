<?php
// app/Models/AdminModel.php (UPDATED: Jurusan toggle status and IKU delete removed)
namespace App\Models;
use PDO;

class AdminModel {
    private $db;
    public function __construct($db) { $this->db = $db; }

    // =========================================================================
    // 1. MANAJEMEN PENGGUNA (USER)
    // =========================================================================
    
    public function getUsers($search = '', $jurusanId = null) {
        $sql = "SELECT u.*, j.nama_jurusan FROM users u LEFT JOIN master_jurusan j ON u.jurusan_id = j.id WHERE 1=1";
        $params = [];
        
        if ($search) {
            // Menggunakan named parameter yang berbeda untuk setiap LIKE (untuk menghindari bug HY093)
            $sql .= " AND (u.username LIKE :s_user OR u.email LIKE :s_email OR u.nama LIKE :s_nama)";
            $search_term = "%$search%";
            $params['s_user'] = $search_term;
            $params['s_email'] = $search_term;
            $params['s_nama'] = $search_term;
        }
        if ($jurusanId) {
            $sql .= " AND u.jurusan_id = :j";
            $params['j'] = $jurusanId;
        }
        // Mengubah urutan dari DESC ke ASC
        $sql .= " ORDER BY u.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createUser($data) {
        // PERBAIKAN KRITIS: Menghapus `is_active` dari daftar kolom INSERT.
        // Ini memastikan 6 parameter (username, email, password, role, jurusan_id, nama)
        // cocok dengan 6 placeholder (?). is_active akan menggunakan nilai default DB (1).
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, role, jurusan_id, nama) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$data['username'], $data['email'], $data['password'], $data['role'], $data['jurusan_id'], $data['nama']]);
    }

    public function updateUser($id, $role, $jurusanId, $nama, $password = null) {
        $sql = "UPDATE users SET role = :role, jurusan_id = :jid, nama = :nama";
        $params = ['role' => $role, 'jid' => $jurusanId, 'nama' => $nama, 'id' => $id];

        if ($password) {
            $sql .= ", password = :pwd";
            $params['pwd'] = $password;
        }
        $sql .= " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function toggleUserStatus($id, $currentStatus) {
        $newStatus = $currentStatus == 1 ? 0 : 1;
        $stmt = $this->db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $id]);
    }
    
    // =========================================================================
    // 2. MASTER DATA (JURUSAN & IKU & SATUAN)
    // =========================================================================

    public function getAllJurusan() {
        // [MODIFIKASI] Seleksi kolom 'is_active' (menggunakan COALESCE untuk kompatibilitas sementara)
        return $this->db->query("SELECT id, nama_jurusan, COALESCE(is_active, 1) as is_active FROM master_jurusan ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createJurusan($nama) {
        // [MODIFIKASI] Tambahkan is_active
        $stmt = $this->db->prepare("INSERT INTO master_jurusan (nama_jurusan, is_active) VALUES (?, 1)");
        return $stmt->execute([$nama]);
    }

    public function updateJurusan($id, $nama) {
        $stmt = $this->db->prepare("UPDATE master_jurusan SET nama_jurusan = ? WHERE id = ?");
        return $stmt->execute([$nama, $id]);
    }

    // [BARU] Fungsi untuk toggle status Jurusan
    public function toggleJurusanStatus($id, $currentStatus) {
        $newStatus = $currentStatus == 1 ? 0 : 1;
        $stmt = $this->db->prepare("UPDATE master_jurusan SET is_active = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $id]);
    }
    
    // [DIHAPUS] public function deleteJurusan() { ... }
    
    public function getAllIkuForAdmin() {
        return $this->db->query("SELECT * FROM master_iku ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllIku() {
        return $this->db->query("SELECT * FROM master_iku WHERE status = 'active' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
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

        // 1. Ambil ID terbesar saat ini
        $stmt = $this->db->query("SELECT MAX(id) as max_id FROM master_iku");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 2. Tentukan ID baru (Jika tabel kosong, mulai dari 1)
        $newId = ($row['max_id'] ?? 0) + 1;
        
        // 3. Format Kode IKU (Misal: 9 menjadi IKU-09, 10 menjadi IKU-10)
        $kodeIku = "IKU-" . str_pad($newId, 2, "0", STR_PAD_LEFT);

        // 4. Masukkan ke database
        $stmtInsert = $this->db->prepare("INSERT INTO master_iku (id, kode_iku, deskripsi_iku, status) VALUES (?, ?, ?, 'active')");
        return $stmtInsert->execute([$newId, $kodeIku, $deskripsi]);
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

    // [DIHAPUS] public function deleteIku() { ... }
    
    // --- MASTER SATUAN ANGGARAN ---
    public function getAllSatuan() {
        return $this->db->query("SELECT * FROM master_satuan_anggaran ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createSatuan($nama) {
        $stmt = $this->db->prepare("INSERT INTO master_satuan_anggaran (nama_satuan) VALUES (?)");
        return $stmt->execute([$nama]);
    }

    public function updateSatuan($id, $nama) {
        $stmt = $this->db->prepare("UPDATE master_satuan_anggaran SET nama_satuan = ? WHERE id = ?");
        return $stmt->execute([$nama, $id]);
    }

    public function toggleSatuanStatus($id, $newStatus) {
        $stmt = $this->db->prepare("UPDATE master_satuan_anggaran SET is_active = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $id]);
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
        $sql = "SELECT l.timestamp, u.username, l.aksi, l.ip_address 
                FROM log_audit_sistem l 
                JOIN users u ON l.user_id = u.id 
                ORDER BY l.timestamp DESC LIMIT 1000";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function clearAuditLogs() {
        $this->db->exec("CALL sp_delete_audit_logs()");
    }
}