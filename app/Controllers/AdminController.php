<?php
// app/Controllers/AdminController.php (UPDATED: Added allUsulan method)
namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\UsulanModel; // Wajib import
use Exception;

class AdminController
{
    private $db;
    private $allowedRoles = ['Pengusul', 'Verifikator', 'WD2', 'PPK', 'Bendahara', 'Admin', 'Direktur'];

    public function __construct($db) { 
        $this->db = $db; 
    }

    private function checkAccess() {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
            error_log("Security Warning: Unauthorized Admin access by User ID: " . ($_SESSION['user_id'] ?? 'Guest'));
            header('Location: /dashboard'); exit;
        }
    }
    
    private function checkViewAccess() {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Direktur'])) {
            header('Location: /dashboard'); exit;
        }
    }
    
    private function checkAdminOnly() {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Akses ditolak. Hanya Admin yang dapat melakukan perubahan data.'];
            header('Location: /master'); exit;
        }
    }


    private function validateCsrf() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? null)) {
            die('Security Alert: Invalid CSRF Token.');
        }
    }
    
    // --- [BARU] SEMUA USULAN (UNTUK ADMIN & DIREKTUR) ---
    public function allUsulan() {
        $this->checkViewAccess();
        
        $usulanModel = new UsulanModel($this->db);
        
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        $perPage = 10;
        
        // Filter Pencarian
        $filters = [
            'search' => filter_var($_GET['q'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS),
            // Tidak menyertakan user_id agar mengambil semua data
        ];
        
        // Menggunakan getAllWithUser yang sudah ada di UsulanModel
        // Fungsi ini sudah mendukung join status lengkap (PPK, WD2, LPJ, Pencairan)
        $usulan = $usulanModel->getAllWithUser($filters, $page, $perPage);
        $total = $usulanModel->countAllWithUser($filters);
        
        $totalPages = ceil($total / $perPage);
        
        require __DIR__ . '/../Views/admin/all_usulan.php';
    }

    // --- MANAJEMEN PENGGUNA ---

    public function users() {
        $this->checkViewAccess(); 
        
        $search = filter_var($_GET['search'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $jurusanId = filter_var($_GET['jurusan'] ?? '', FILTER_VALIDATE_INT) ?: null;

        $model = new AdminModel($this->db);
        $users = $model->getUsers($search, $jurusanId);
        $jurusan = $model->getAllJurusan(); 
        
        require __DIR__ . '/../Views/admin/users.php';
    }

    public function createUser() {
        $this->checkAccess();
        
        // Cek apakah request AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validasi CSRF manual jika bukan framework otomatis
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? null)) {
                if ($isAjax) {
                    echo json_encode(['status' => 'error', 'msg' => 'Security Alert: Invalid CSRF Token.']); exit;
                }
                die('Security Alert: Invalid CSRF Token.');
            }

            $username = trim($_POST['username']);
            $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            $rawPassword = $_POST['password'];
            $role = $_POST['role'];
            $nama = trim($_POST['nama'] ?? ''); 
            $jurusanId = ($role === 'Pengusul' && !empty($_POST['jurusan_id'])) ? $_POST['jurusan_id'] : null;

            $userCaptcha = $_POST['captcha'] ?? '';
            $sessionCaptcha = $_SESSION['captcha_code'] ?? '';
            // Jangan unset session captcha dulu jika AJAX, agar bisa dicoba lagi tanpa refresh total (opsional, tapi lebih aman di-unset dan minta refresh)
            unset($_SESSION['captcha_code']); 

            $errors = [];
            
            if (empty($userCaptcha) || strtolower($userCaptcha) !== strtolower($sessionCaptcha)) {
                $errors[] = "Kode keamanan (Captcha) salah atau kadaluarsa.";
            }

            if (empty($nama)) $errors[] = "Nama wajib diisi."; 
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid.";
            if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) $errors[] = "Username tidak valid (hanya huruf, angka, underscore, 3-20 char).";
            if (strlen($rawPassword) < 8) $errors[] = "Password minimal 8 karakter.";
            if (!in_array($role, $this->allowedRoles)) $errors[] = "Role tidak valid.";
            if ($role === 'Pengusul' && empty($jurusanId)) $errors[] = "Jurusan wajib dipilih untuk role Pengusul.";

            if (!empty($errors)) {
                $msg = implode("\n", $errors);
                if ($isAjax) {
                    echo json_encode(['status' => 'error', 'msg' => $msg]); exit;
                }
                $_SESSION['toast'] = ['type' => 'error', 'msg' => $msg];
                header('Location: /users'); exit;
            }

            try {
                $data = [
                    'username'   => $username,
                    'email'      => $email,
                    'password'   => password_hash($rawPassword, PASSWORD_BCRYPT),
                    'role'       => $role,
                    'jurusan_id' => $jurusanId,
                    'nama'       => $nama 
                ];

                $model = new AdminModel($this->db);
                $model->createUser($data);
                $this->logAudit("Create User: $username ($role)");

                if ($isAjax) {
                    echo json_encode(['status' => 'success', 'msg' => 'User berhasil dibuat.']); exit;
                }
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'User berhasil dibuat.'];
            } catch (Exception $e) {
                $errMsg = 'Kesalahan sistem database.';
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $errMsg = 'Username atau Email sudah terdaftar.';
                }
                
                if ($isAjax) {
                    echo json_encode(['status' => 'error', 'msg' => $errMsg]); exit;
                }
                $_SESSION['toast'] = ['type' => 'error', 'msg' => $errMsg];
            }
            header('Location: /users'); exit;
        }
    }

    public function updateUser() {
        $this->checkAccess();
        $this->validateCsrf();

        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if (!$id) { header('Location: /users'); exit; }

        $password = null;
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 8) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Password baru minimal 8 karakter.'];
                header('Location: /users'); exit;
            }
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }

        $role = $_POST['role'];
        $nama = trim($_POST['nama'] ?? ''); 
        
        if (empty($nama)) { 
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Nama wajib diisi.'];
            header('Location: /users'); exit;
        }
        if (!in_array($role, $this->allowedRoles)) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Role tidak valid.'];
            header('Location: /users'); exit;
        }
        
        $jurusanId = ($role === 'Pengusul' && !empty($_POST['jurusan_id'])) ? $_POST['jurusan_id'] : null;

        try {
            $model = new AdminModel($this->db);
            $model->updateUser($id, $role, $jurusanId, $nama, $password);
            
            $this->logAudit("Update User ID: $id (Nama: $nama)");
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Data User diperbarui.'];
        } catch (Exception $e) {
            error_log("Update User Error: " . $e->getMessage());
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal update user.'];
        }

        header('Location: /users'); exit;
    }

    public function toggleUserStatus() {
        $this->checkAccess();
        $this->validateCsrf();
        
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $currentStatus = filter_var($_POST['current_status'], FILTER_VALIDATE_INT);

        if ($id == $_SESSION['user_id']) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: Tidak bisa mengubah status akun sendiri.'];
            header('Location: /users'); exit;
        }
        
        $model = new AdminModel($this->db);
        if ($model->toggleUserStatus($id, $currentStatus)) {
            $action = $currentStatus == 1 ? 'nonaktif' : 'aktif'; 
            $this->logAudit("User ID: $id di{$action}kan"); 
            $_SESSION['toast'] = ['type' => 'success', 'msg' => "User berhasil di{$action}kan."];
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal proses.'];
        }
        
        header('Location: /users'); exit;
    }

    // --- MASTER DATA ---
    
    public function indexMaster() {
        $this->checkViewAccess(); 
        require __DIR__ . '/../Views/admin/master_landing.php';
    }

    public function jurusan() {
        $this->checkViewAccess(); 
        $model = new AdminModel($this->db);
        $jurusan = $model->getAllJurusan(); 
        require __DIR__ . '/../Views/admin/jurusan.php';
    }

    public function storeJurusan() {
        $this->checkAdminOnly(); 
        $this->validateCsrf();

        $nama = trim(strip_tags($_POST['nama_jurusan']));
        if (strlen($nama) < 3) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Nama Jurusan terlalu pendek.'];
        } else {
            $model = new AdminModel($this->db);
            try {
                $model->createJurusan($nama);
                $this->logAudit("Add Jurusan: $nama");
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Jurusan ditambahkan.'];
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: Nama Jurusan mungkin duplikat.'];
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Kesalahan sistem database.'];
                }
            }
        }
        header('Location: /master/jurusan'); exit;
    }

    public function updateJurusan() {
        $this->checkAdminOnly(); 
        $this->validateCsrf();

        $id = $_POST['id'];
        $nama = trim(strip_tags($_POST['nama_jurusan']));

        try {
            $model = new AdminModel($this->db);
            $model->updateJurusan($id, $nama);
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Jurusan diperbarui.'];
        } catch (Exception $e) {
             if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: Nama Jurusan mungkin duplikat.'];
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal update jurusan.'];
            }
        }
        
        header('Location: /master/jurusan'); exit;
    }
    
    public function toggleJurusanStatus() {
        $this->checkAdminOnly(); 
        $this->validateCsrf();
        
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $currentStatus = filter_var($_POST['current_status'], FILTER_VALIDATE_INT);
        
        $model = new AdminModel($this->db);
        
        if ($currentStatus == 1) {
            $cek = $this->db->prepare("SELECT COUNT(*) FROM users WHERE jurusan_id = ? AND is_active = 1");
            $cek->execute([$id]);
            if ($cek->fetchColumn() > 0) {
                 $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal menonaktifkan: Masih ada Pengusul aktif di Jurusan ini.'];
                 header('Location: /master/jurusan'); exit;
            }
        }

        if ($model->toggleJurusanStatus($id, $currentStatus)) {
             $action = $currentStatus == 1 ? 'Nonaktifkan' : 'Aktifkan';
             $this->logAudit("$action Jurusan ID: $id");
             $_SESSION['toast'] = ['type' => 'success', 'msg' => "Status Jurusan berhasil diubah."];
        } else {
             $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal mengubah status Jurusan.'];
        }
        header('Location: /master/jurusan'); exit;
    }
    
    public function iku() {
        $this->checkViewAccess(); 
        $model = new AdminModel($this->db);
        $iku = $model->getAllIkuForAdmin(); 
        require __DIR__ . '/../Views/admin/iku.php';
    }

    public function storeIku() {
        $this->checkAdminOnly(); 
        $this->validateCsrf();

        $deskripsi = trim(strip_tags($_POST['deskripsi_iku']));
        if (empty($deskripsi)) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Deskripsi IKU wajib diisi.'];
        } else {
            $model = new AdminModel($this->db);
            if ($model->createIku($deskripsi)) {
                $this->logAudit("Add IKU");
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'IKU ditambahkan.'];
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: IKU sudah ada.'];
            }
        }
        header('Location: /master/iku'); exit;
    }

    public function updateIku() {
        $this->checkAdminOnly(); 
        $this->validateCsrf();

        $id = $_POST['id'];
        $deskripsi = trim(strip_tags($_POST['deskripsi_iku']));
        
        $model = new AdminModel($this->db);
        if ($model->updateIku($id, $deskripsi)) {
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'IKU diperbarui.'];
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: IKU duplikat.'];
        }
        
        header('Location: /master/iku'); exit;
    }

    public function toggleIkuStatus() {
        $this->checkAdminOnly(); 
        $this->validateCsrf();
        
        $id = $_POST['id'];
        $currentStatus = $_POST['current_status']; 
        $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';
        
        $model = new AdminModel($this->db);
        $model->toggleIkuStatus($id, $newStatus);
        
        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Status IKU diubah.'];
        header('Location: /master/iku'); exit;
    }
    
    public function satuan() {
        $this->checkViewAccess(); 
        $model = new AdminModel($this->db);
        $satuan = $model->getAllSatuan();
        require __DIR__ . '/../Views/admin/satuan.php';
    }

    public function storeSatuan() {
        $this->checkAdminOnly();
        $this->validateCsrf();

        $nama = trim(strip_tags($_POST['nama_satuan']));
        if (strlen($nama) < 1) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Nama Satuan wajib diisi.'];
        } else {
            $model = new AdminModel($this->db);
            try {
                $model->createSatuan($nama);
                $this->logAudit("Add Satuan: $nama");
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Satuan ditambahkan.'];
            } catch (Exception $e) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: Nama Satuan mungkin duplikat.'];
            }
        }
        header('Location: /master/satuan'); exit;
    }

    public function updateSatuan() {
        $this->checkAdminOnly();
        $this->validateCsrf();

        $id = $_POST['id'];
        $nama = trim(strip_tags($_POST['nama_satuan']));

        try {
            $model = new AdminModel($this->db);
            $model->updateSatuan($id, $nama);
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Satuan diperbarui.'];
        } catch (Exception $e) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal update satuan.'];
        }
        
        header('Location: /master/satuan'); exit;
    }

    public function toggleSatuanStatus() {
        $this->checkAdminOnly();
        $this->validateCsrf();
        
        $id = $_POST['id'];
        $currentStatus = $_POST['current_status'];
        $newStatus = ($currentStatus == 1) ? 0 : 1;
        
        $model = new AdminModel($this->db);
        $model->toggleSatuanStatus($id, $newStatus);
        
        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Status Satuan diubah.'];
        header('Location: /master/satuan'); exit;
    }
    
    public function clearAuditLogs() {
        $this->checkAccess();
        $this->validateCsrf();

        try {
            // Menggunakan procedure sp_delete_audit_logs untuk menghapus audit logs
            $stmt = $this->db->prepare("CALL sp_delete_audit_logs()");
            $stmt->execute();

            $this->logAudit("Clear All Audit Logs using sp_delete_audit_logs");
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Semua Audit Log berhasil dihapus menggunakan procedure database.'];
        } catch (Exception $e) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal menghapus Audit Log: ' . $e->getMessage()];
        }

        header('Location: /audit-log');
        exit;
    }

    private function logAudit($action) {
        $stmt = $this->db->prepare("INSERT INTO log_audit_sistem (user_id, aksi, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $action, $_SERVER['REMOTE_ADDR']]);
    }
}