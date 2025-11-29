<?php
namespace App\Controllers;

use App\Models\UsulanModel;
use PDO;

class ApprovalController
{
    private $db;
    public function __construct($db) { $this->db = $db; }

    private function ensureLogin() {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }
    }

    // Menampilkan Antrian Approval (WD2 & PPK)
    public function index($page = 1, $perPage = 10)
    {
        $this->ensureLogin();
        $role = $_SESSION['role'];
        
        // Routing Logic berdasarkan Role
        if ($role === 'WD2') {
            $targetStatus = 'Menunggu WD2';
        } elseif ($role === 'PPK') {
            $targetStatus = 'Menunggu PPK';
        } else {
            header('Location: /dashboard'); exit;
        }
        
        $model = new UsulanModel($this->db);
        // Gunakan Model untuk konsistensi
        $usulan = $model->getAllWithUser(['status' => $targetStatus], $page, $perPage);
        $total = $model->countAllWithUser(['status' => $targetStatus]);
        
        require __DIR__ . '/../Views/approval/index.php';
    }

    public function proses($id)
    {
        $this->ensureLogin();
        $role = $_SESSION['role'];
        
        // Security: Pastikan user berhak akses status ini
        $validStatus = ($role === 'WD2') ? 'Menunggu WD2' : 'Menunggu PPK';

        $model = new UsulanModel($this->db);
        
        // 1. Ambil Header
        $stmt = $this->db->prepare("SELECT u.*, us.username, m.nama_jurusan 
                                    FROM usulan_kegiatan u 
                                    JOIN users us ON u.user_id = us.id 
                                    LEFT JOIN master_jurusan m ON us.jurusan_id = m.id
                                    WHERE u.id = :id AND u.status_terkini = :status");
        $stmt->execute(['id' => $id, 'status' => $validStatus]);
        $usulan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usulan) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Dokumen tidak tersedia/akses ditolak.'];
            header('Location: /approval'); exit;
        }

        // [ELITE UPGRADE] 2. Ambil Data Pendukung untuk Keputusan
        $docs = $model->getDocuments($id); // Penting untuk WD2 (Lihat Surat)
        
        // Ambil RAB Summary (Total per kategori) untuk Executive View
        $rabSum = $this->db->prepare("SELECT k.nama_kategori, SUM(r.total) as subtotal 
                                      FROM rab_detail r 
                                      JOIN master_kategori_anggaran k ON r.kategori_id = k.id 
                                      WHERE r.usulan_id = ? GROUP BY k.nama_kategori");
        $rabSum->execute([$id]);
        $rabSummary = $rabSum->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/approval/proses.php';
    }

    public function aksi($id)
    {
        $this->ensureLogin();
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('Invalid Token');

        $aksi = $_POST['aksi'];
        $catatan = trim($_POST['catatan'] ?? '');
        $role = $_SESSION['role'];
        $userId = $_SESSION['user_id'];
        
        $statusBaru = '';
        $pesanNotif = '';

        // State Machine Logic
        if ($aksi === 'setuju') {
            if ($role === 'WD2') {
                $statusBaru = 'Menunggu PPK';
                $pesanNotif = "Usulan disetujui WD2. Saat ini sedang direview oleh PPK.";
            } elseif ($role === 'PPK') {
                $statusBaru = 'Disetujui'; 
                $pesanNotif = "Usulan DISETUJUI oleh PPK. Silakan hubungi Bendahara untuk pencairan.";
            }
        } elseif ($aksi === 'revisi') {
            $statusBaru = 'Revisi'; 
            $pesanNotif = "Usulan dikembalikan oleh $role. Catatan: $catatan";
        } elseif ($aksi === 'tolak') {
            $statusBaru = 'Ditolak';
            $pesanNotif = "Usulan ditolak oleh $role.";
        }

        try {
            $this->db->beginTransaction();

            // Update Status
            $this->db->prepare("UPDATE usulan_kegiatan SET status_terkini = :st, updated_at = NOW() WHERE id = :id")
                     ->execute(['st' => $statusBaru, 'id' => $id]);

            // Log Histori
            $this->db->prepare("INSERT INTO log_histori_usulan (usulan_id, user_id, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?)")
                     ->execute([$id, $userId, "Approval $role", $statusBaru, $catatan]);
                     
            // Notifikasi User
            $getOwner = $this->db->prepare("SELECT user_id FROM usulan_kegiatan WHERE id = ?");
            $getOwner->execute([$id]);
            $uid = $getOwner->fetchColumn();

            if ($uid) {
                $this->db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, link, created_at) VALUES (?, ?, ?, ?, NOW())")
                         ->execute([$uid, "Update: $statusBaru", $pesanNotif, "/usulan/detail?id=$id"]);
            }

            $this->db->commit();
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Keputusan berhasil disimpan.'];

        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal menyimpan keputusan.'];
        }

        header('Location: /approval');
        exit;
    }
}