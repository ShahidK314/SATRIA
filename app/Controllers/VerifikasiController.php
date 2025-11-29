<?php
namespace App\Controllers;

use PDO;
use App\Models\UsulanModel;

class VerifikasiController
{
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
    }

    private function ensureLogin() {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Verifikator') {
            header('Location: /login'); exit;
        }
    }

    public function index($page = 1, $perPage = 10)
    {
        $this->ensureLogin();
        $usulanModel = new UsulanModel($this->db);
        
        // Filter hanya status 'Verifikasi'
        $filters = ['status' => 'Verifikasi'];
        
        // Ambil data
        $usulan = $usulanModel->getAllWithUser($filters, $page, $perPage);
        $total = $usulanModel->countAllWithUser($filters); 

        require __DIR__ . '/../Views/verifikasi/index.php';
    }

    public function proses($id)
    {
        $this->ensureLogin();

        // 1. Ambil Header Usulan
        // [FIX] Join ke master_jurusan agar nama unit muncul
        $stmt = $this->db->prepare("SELECT u.*, us.username, mj.nama_jurusan 
                                    FROM usulan_kegiatan u 
                                    JOIN users us ON u.user_id = us.id 
                                    LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id 
                                    WHERE u.id = :id");
        $stmt->execute(['id' => $id]);
        $usulan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usulan) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Data tidak ditemukan.'];
            header('Location: /verifikasi'); exit;
        }

        // [CRITICAL FIX] 2. Ambil Data Detail (RAB & IKU)
        // Tanpa ini, View proses.php akan ERROR (Undefined variable)
        $rab = $this->db->prepare("SELECT r.*, k.nama_kategori 
                                   FROM rab_detail r 
                                   JOIN master_kategori_anggaran k ON r.kategori_id = k.id 
                                   WHERE r.usulan_id = ? 
                                   ORDER BY k.id ASC");
        $rab->execute([$id]);
        $rabData = $rab->fetchAll(PDO::FETCH_ASSOC);

        $iku = $this->db->prepare("SELECT t.*, m.deskripsi_iku 
                                   FROM tor_iku t 
                                   JOIN master_iku m ON t.iku_id = m.id 
                                   WHERE t.usulan_id = ?");
        $iku->execute([$id]);
        $ikuData = $iku->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/verifikasi/proses.php';
    }

    public function aksi($id)
    {
        $this->ensureLogin();
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Security Alert: Invalid Token');
        }

        $aksi = $_POST['aksi'];
        $catatan = trim($_POST['catatan'] ?? '');
        $kode_mak = trim($_POST['kode_mak'] ?? '');

        // [SECURITY] Validasi: Kode MAK Wajib diisi jika menyetujui
        if ($aksi === 'setuju' && empty($kode_mak)) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Kode MAK wajib diisi untuk menyetujui usulan!'];
            header("Location: /verifikasi/proses?id=$id"); 
            exit;
        }

        // Tentukan Status Baru
        $status = 'Disetujui Verifikator'; // Flow: Lanjut ke Upload Surat (Skenario 2)
        if ($aksi === 'revisi') $status = 'Revisi';
        if ($aksi === 'tolak') $status = 'Ditolak';

        try {
            $this->db->beginTransaction();

            // 1. Update Status & MAK
            $this->db->prepare("UPDATE usulan_kegiatan SET status_terkini=?, kode_mak=? WHERE id=?")
                     ->execute([$status, $kode_mak, $id]);
            
            // 2. Catat Log Histori
            $this->db->prepare("INSERT INTO log_histori_usulan (usulan_id, user_id, status_lama, status_baru, catatan) VALUES (?, ?, 'Verifikasi', ?, ?)")
                     ->execute([$id, $_SESSION['user_id'], $status, $catatan]);
            
            // 3. Kirim Notifikasi ke Pengusul
            $userSql = $this->db->prepare("SELECT user_id, nama_kegiatan FROM usulan_kegiatan WHERE id=?");
            $userSql->execute([$id]);
            $u = $userSql->fetch(PDO::FETCH_ASSOC);

            if ($u) {
                // Pesan Notifikasi Spesifik
                if ($aksi === 'setuju') {
                    $pesan = "Usulan disetujui Verifikator. Silakan lengkapi Data PJ & Upload Surat Pengantar untuk lanjut ke WD2.";
                    $link = "/usulan/lengkapi?id=$id"; // Arahkan ke halaman Lengkapi
                } else {
                    $pesan = "Usulan status: $status. Cek catatan verifikator untuk detail.";
                    $link = "/usulan/detail?id=$id";
                }

                $this->db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, link, created_at) VALUES (?, ?, ?, ?, NOW())")
                         ->execute([$u['user_id'], "Update: $status", $pesan, $link]);
            }

            $this->db->commit();
            $_SESSION['toast'] = ['type' => 'success', 'msg' => "Usulan berhasil diproses ($status)."];

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Verifikasi Error: " . $e->getMessage());
            $_SESSION['toast'] = ['type' => 'error', 'msg' => "Terjadi kesalahan sistem."];
        }

        header('Location: /verifikasi');
        exit;
    }
}