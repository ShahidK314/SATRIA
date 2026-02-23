<?php
// app/Controllers/ApprovalController.php (UPDATED: Using Database Procedure)
namespace App\Controllers;

use App\Models\UsulanModel;
use App\Models\PengajuanModel; 
use PDO;

class ApprovalController
{
    private $db;
    public function __construct($db) { $this->db = $db; }

    private function ensureLogin() {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }
    }
    
    // Helper functions for notification
    private function notifyWD2()
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'WD2' LIMIT 1");
        $stmt->execute();
        $wd2 = $stmt->fetch();

        if ($wd2) {
            $this->db->prepare("
                INSERT INTO notifikasi (user_id, judul, pesan, link, created_at) 
                VALUES (?, 'Approval PPK Selesai', 'Ada pengajuan kegiatan yang perlu disetujui WD2', '/pengajuan/wd2', NOW())
            ")->execute([$wd2['id'],]);
        }
    }

    private function notifyBendahara()
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'Bendahara' LIMIT 1");
        $stmt->execute();
        $bendahara = $stmt->fetch();

        if ($bendahara) {
            $this->db->prepare("
                INSERT INTO notifikasi (user_id, judul, pesan, link, created_at) 
                VALUES (?, 'Siap Pencairan', 'Ada pengajuan yang telah disetujui WD2 dan siap untuk dicairkan', '/pencairan', NOW())
            ")->execute([$bendahara['id'],]);
        }
    }


    /**
     * Aksi Approval oleh PPK atau WD2
     */
    public function aksi($id)
    {
        $this->ensureLogin();
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('Invalid Token');

        $aksi = $_POST['aksi']; 
        $rekomendasi = trim($_POST['rekomendasi'] ?? ''); 
        $role = $_SESSION['role'];
        $userId = $_SESSION['user_id'];
        
        $pengajuanModel = new PengajuanModel($this->db);
        $usulanModel = new UsulanModel($this->db);
        $pengajuan = $pengajuanModel->findById($id);

        if (!$pengajuan) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Pengajuan tidak ditemukan.'];
            header('Location: /dashboard'); exit;
        }

        $usulanId = $pengajuan['usulan_id'];
        $statusLamaUsulan = $pengajuan['usulan_status']; // Status dari usulan_kegiatan ('Disetujui')
        $targetRedirect = '';
        $pesanNotif = '';
        
        // ENFORCEMENT: PPK dan WD2 HANYA BISA MENYETUJUI (SESUAI SKENARIO)
        if ($aksi !== 'setuju') {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Aksi Ditolak: Pimpinan hanya dapat menyetujui di fase Pengajuan Kegiatan ini.'];
            $targetRedirect = ($role === 'PPK') ? '/pengajuan/ppk' : '/pengajuan/wd2';
            header("Location: $targetRedirect"); exit;
        }

        try {
            $this->db->beginTransaction();
            $statusBaruPengajuan = 'Disetujui'; // Nilai ENUM untuk status_ppk/status_wd2
            $logStatusBaru = '';

            if ($role === 'PPK') {
                $targetRedirect = '/pengajuan/ppk';
                $logStatusBaru = 'Menunggu WD2'; 
                $pesanNotif = "Pengajuan disetujui PPK. Dilanjutkan ke WD2.";
                $this->notifyWD2();
                
                // Update tabel pengajuan_kegiatan (status_ppk = Disetujui)
                $pengajuanModel->updateStatus($id, $statusBaruPengajuan, $rekomendasi, $role);


            } elseif ($role === 'WD2') {
                $targetRedirect = '/pengajuan/wd2';
                $logStatusBaru = 'Siap Pencairan'; 
                $pesanNotif = "Pengajuan DISETUJUI oleh WD2. Dana siap dicairkan.";
                $this->notifyBendahara();
                
                 // Update tabel pengajuan_kegiatan (status_wd2 = Disetujui)
                $pengajuanModel->updateStatus($id, $statusBaruPengajuan, $rekomendasi, $role);
            }
            
            // [AMAN] Gunakan Procedure Database
            // Kita menggunakan 'changeStatus' yang memanggil SP 'sp_ubah_status_usulan'.
            // Karena Procedure ini melakukan UPDATE pada tabel usulan_kegiatan dan INSERT log sekaligus,
            // kita harus mengirimkan status usulan yang valid (sesuai ENUM database).
            // Status usulan tetap 'Disetujui' (tidak berubah), tapi kita update lognya.
            
            $catatanLog = "Disetujui oleh $role. Status Pengajuan: $logStatusBaru. Rekomendasi: $rekomendasi";
            
            $usulanModel->changeStatus(
                $usulanId, 
                $userId, 
                $statusLamaUsulan, // Status tetap (misal: 'Disetujui') agar valid di mata Database/Enum
                $catatanLog
            );

            // Notifikasi ke Pengusul
            $getOwner = $this->db->prepare("SELECT user_id FROM usulan_kegiatan WHERE id = ?");
            $getOwner->execute([$usulanId]);
            $uid = $getOwner->fetchColumn();

            if ($uid) {
                $this->db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, link, created_at) VALUES (?, ?, ?, ?, NOW())")
                         ->execute([$uid, "Update: Pengajuan $role", $pesanNotif, "/monitoring"]);
            }

            $this->db->commit();
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Keputusan berhasil disimpan.'];

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Approval Error: " . $e->getMessage());
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal menyimpan keputusan: ' . $e->getMessage()];
            $targetRedirect = ($role === 'PPK') ? '/pengajuan/ppk' : '/pengajuan/wd2';
        }

        header("Location: $targetRedirect");
        exit;
    }
}