<?php
namespace App\Controllers;
use PDO;

class ApprovalController
{
    private $db;
    public function __construct($db) { $this->db = $db; }

    // Menampilkan Antrian Approval
    public function index($page = 1, $perPage = 10)
    {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }
        
        $role = $_SESSION['role'];
        // [ELITE LOGIC] Penentuan Target Status Berdasarkan Role
        if ($role === 'WD2') {
            $targetStatus = 'Menunggu WD2';
        } elseif ($role === 'PPK') {
            $targetStatus = 'Menunggu PPK';
        } else {
            // Jika bukan WD2/PPK, tendang keluar
            header('Location: /dashboard'); exit;
        }
        
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("SELECT u.*, us.username FROM usulan_kegiatan u JOIN users us ON u.user_id = us.id WHERE u.status_terkini = :status ORDER BY u.id ASC LIMIT :offset, :perPage");
        $stmt->bindValue(':status', $targetStatus);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
        $stmt->execute();
        $usulan = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../Views/approval/index.php';
    }

    public function proses($id)
    {
        if (!isset($_SESSION['user_id'])) exit;
        
        // Validasi Kepemilikan Antrian
        $role = $_SESSION['role'];
        $validStatus = ($role === 'WD2') ? 'Menunggu WD2' : 'Menunggu PPK';

        $stmt = $this->db->prepare("SELECT u.*, us.username FROM usulan_kegiatan u JOIN users us ON u.user_id = us.id WHERE u.id = :id AND u.status_terkini = :status");
        $stmt->execute(['id' => $id, 'status' => $validStatus]);
        $usulan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usulan) {
            // Jika data tidak ditemukan di status yang sesuai, redirect error
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Dokumen tidak tersedia atau sudah diproses.'];
            header('Location: /approval'); exit;
        }

        require __DIR__ . '/../Views/approval/proses.php';
    }

    // [CORE BUSINESS LOGIC] Eksekusi Approval
    public function aksi($id)
    {
        if (!isset($_SESSION['user_id'])) exit;
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Security Alert: Invalid Token.');
        }

        $aksi = $_POST['aksi'] ?? '';
        $catatan = trim($_POST['catatan'] ?? '');
        $role = $_SESSION['role'];
        $userId = $_SESSION['user_id'];
        
        $statusBaru = '';
        $pesanNotif = '';

        // State Machine Logic
        if ($aksi === 'setuju') {
            if ($role === 'WD2') {
                $statusBaru = 'Menunggu PPK'; // Flow: WD2 -> PPK
                $pesanNotif = "Usulan disetujui WD2. Saat ini sedang direview oleh PPK.";
            } elseif ($role === 'PPK') {
                $statusBaru = 'Disetujui'; // Flow: PPK -> Bendahara (Siap Cair)
                $pesanNotif = "Usulan DISETUJUI oleh PPK. Silakan hubungi Bendahara untuk pencairan.";
            }
        } elseif ($aksi === 'revisi') {
            $statusBaru = 'Revisi'; // Flow: Back to User
            $pesanNotif = "Usulan dikembalikan oleh $role untuk perbaikan. Cek catatan revisi.";
        } elseif ($aksi === 'tolak') {
            $statusBaru = 'Ditolak'; // Flow: Dead End
            $pesanNotif = "Mohon maaf, usulan Anda ditolak oleh $role.";
        }

        if (empty($statusBaru)) {
            header('Location: /approval'); exit;
        }

        try {
            $this->db->beginTransaction();

            // 1. Update Status Utama
            $this->db->prepare("UPDATE usulan_kegiatan SET status_terkini = :st, updated_at = NOW() WHERE id = :id")
                     ->execute(['st' => $statusBaru, 'id' => $id]);

            // 2. Catat Log Histori (Audit Trail)
            $this->db->prepare("INSERT INTO log_histori_usulan (usulan_id, user_id, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?)")
                     ->execute([$id, $userId, "Approval $role", $statusBaru, $catatan]);
                     
            // 3. Kirim Notifikasi ke Pengusul
            $getOwner = $this->db->prepare("SELECT user_id, nama_kegiatan FROM usulan_kegiatan WHERE id = ?");
            $getOwner->execute([$id]);
            $owner = $getOwner->fetch(PDO::FETCH_ASSOC);

            if ($owner) {
                $judul = "Update: " . $statusBaru;
                $link = "/usulan/detail?id=$id";
                $this->db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, link) VALUES (?, ?, ?, ?)")
                         ->execute([$owner['user_id'], $judul, $pesanNotif, $link]);
            }

            $this->db->commit();
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Keputusan berhasil disimpan.'];

        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Terjadi kesalahan sistem.'];
        }

        header('Location: /approval');
        exit;
    }
}