<?php
namespace App\Controllers;

use App\Models\AdminModel;
use PDO;
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
            header('Location: /login'); exit;
        }
    }

    private function validateCsrf() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Security Alert: Invalid CSRF Token.');
        }
    }

    // --- MANAJEMEN PENGGUNA ---

    public function users() {
        $this->checkAccess();
        
        $search = filter_var($_GET['search'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $jurusanId = filter_var($_GET['jurusan'] ?? '', FILTER_VALIDATE_INT) ?: null;

        $model = new AdminModel($this->db);
        $users = $model->getUsers($search, $jurusanId);
        $jurusan = $model->getAllJurusan(); 
        
        require __DIR__ . '/../Views/admin/users.php';
    }

    public function createUser() {
        $this->checkAccess();
        $this->validateCsrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            $rawPassword = $_POST['password'];
            $role = $_POST['role'];
            $jurusanId = !empty($_POST['jurusan_id']) ? $_POST['jurusan_id'] : null;

            $errors = [];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid.";
            if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) $errors[] = "Username tidak valid (hanya huruf, angka, underscore, 3-20 char).";
            if (strlen($rawPassword) < 8) $errors[] = "Password minimal 8 karakter.";
            if (!in_array($role, $this->allowedRoles)) $errors[] = "Role tidak valid.";

            if (!empty($errors)) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => implode(' ', $errors)];
                header('Location: /users'); exit;
            }

            try {
                $data = [
                    'username'   => $username,
                    'email'      => $email,
                    'password'   => password_hash($rawPassword, PASSWORD_BCRYPT),
                    'role'       => $role,
                    'jurusan_id' => $jurusanId
                ];

                $model = new AdminModel($this->db);
                $model->createUser($data);
                $this->logAudit("Create User: $username ($role)");

                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'User berhasil dibuat.'];
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Username atau Email sudah terdaftar.'];
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Kesalahan sistem database.'];
                }
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
        if (!in_array($role, $this->allowedRoles)) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Role tidak valid.'];
            header('Location: /users'); exit;
        }
        
        $jurusanId = !empty($_POST['jurusan_id']) ? $_POST['jurusan_id'] : null;

        try {
            // [ELITE FIX] Menggunakan Model, bukan SQL Query langsung
            $model = new AdminModel($this->db);
            $model->updateUser($id, $role, $jurusanId, $password);
            
            $this->logAudit("Update User ID: $id");
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Data User diperbarui.'];
        } catch (Exception $e) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal update user.'];
        }

        header('Location: /users'); exit;
    }

    public function deleteUser() {
        $this->checkAccess();
        $this->validateCsrf();
        
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

        if ($id == $_SESSION['user_id']) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: Tidak bisa menonaktifkan akun sendiri.'];
            header('Location: /users'); exit;
        }
        
        $model = new AdminModel($this->db);
        if ($model->softDeleteUser($id)) {
            $this->logAudit("Deactivate User ID: $id");
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'User dinonaktifkan.'];
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal proses.'];
        }
        
        header('Location: /users'); exit;
    }

    // --- MASTER DATA ---
    
    public function indexMaster() {
        $this->checkAccess();
        require __DIR__ . '/../Views/admin/master_landing.php';
    }

    public function jurusan() {
        $this->checkAccess();
        $model = new AdminModel($this->db);
        $jurusan = $model->getAllJurusan(); 
        require __DIR__ . '/../Views/admin/jurusan.php';
    }

    public function storeJurusan() {
        $this->checkAccess();
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
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: Nama Jurusan mungkin duplikat.'];
            }
        }
        header('Location: /master/jurusan'); exit;
    }

    public function updateJurusan() {
        $this->checkAccess();
        $this->validateCsrf();

        $id = $_POST['id'];
        $nama = trim(strip_tags($_POST['nama_jurusan']));

        try {
            // [ELITE FIX] Menggunakan Model
            $model = new AdminModel($this->db);
            $model->updateJurusan($id, $nama);
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Jurusan diperbarui.'];
        } catch (Exception $e) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal update jurusan.'];
        }
        
        header('Location: /master/jurusan'); exit;
    }

    public function deleteJurusan() {
        $this->checkAccess();
        $this->validateCsrf();

        $model = new AdminModel($this->db);
        if ($model->deleteJurusan($_POST['id'])) {
            $this->logAudit("Delete Jurusan ID: " . $_POST['id']);
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Jurusan dihapus.'];
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: Masih ada User di jurusan ini.'];
        }
        header('Location: /master/jurusan'); exit;
    }

    public function iku() {
        $this->checkAccess();
        $model = new AdminModel($this->db);
        $iku = $model->getAllIku(); 
        require __DIR__ . '/../Views/admin/iku.php';
    }

    public function storeIku() {
        $this->checkAccess();
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
        $this->checkAccess();
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
        $this->checkAccess();
        $this->validateCsrf();
        
        $id = $_POST['id'];
        $currentStatus = $_POST['current_status'];
        $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';
        
        $model = new AdminModel($this->db);
        $model->toggleIkuStatus($id, $newStatus);
        
        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Status IKU diubah.'];
        header('Location: /master/iku'); exit;
    }

    public function deleteIku() {
        $this->checkAccess();
        $this->validateCsrf();

        $model = new AdminModel($this->db);
        if ($model->deleteIku($_POST['id'])) {
            $this->logAudit("Delete IKU ID: " . $_POST['id']);
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'IKU dihapus.'];
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: IKU sedang dipakai.'];
        }
        header('Location: /master/iku'); exit;
    }

    private function logAudit($action) {
        $stmt = $this->db->prepare("INSERT INTO log_audit_sistem (user_id, aksi, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $action, $_SERVER['REMOTE_ADDR']]);
    }
}