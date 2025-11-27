<?php
namespace App\Controllers;

use App\Models\UsulanModel;
use PDO;

class KeuanganController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    // --- PENCAIRAN DANA ---
    public function indexPencairan() {
        if ($_SESSION['role'] !== 'Bendahara') { header('Location: /dashboard'); exit; }
        $model = new UsulanModel($this->db);
        // Hanya ambil yang statusnya 'Disetujui' (Oleh PPK)
        $usulan = $model->getByStatus(['Disetujui']);
        require __DIR__ . '/../Views/keuangan/pencairan.php';
    }

    public function prosesPencairan($id) {
        if ($_SESSION['role'] !== 'Bendahara') return;
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die('Invalid Token');

        // Sanitasi Uang (Hapus titik/koma)
        $rawNominal = $_POST['nominal_cair'] ?? 0;
        $nominal = (float) preg_replace('/[^0-9]/', '', $rawNominal);
        
        $tglLpj  = $_POST['tgl_batas_lpj'] ?? '';
        $tglCair = date('Y-m-d H:i:s');

        if ($nominal <= 0) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Nominal pencairan wajib diisi & valid.'];
            header('Location: /pencairan'); exit;
        }
        
        // Validasi Timer LPJ (Minimal hari ini)
        if (empty($tglLpj) || new \DateTime($tglLpj) < new \DateTime('today')) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Tanggal batas LPJ tidak boleh lampau.'];
            header('Location: /pencairan'); exit;
        }

        $model = new UsulanModel($this->db);
        // Update status ke 'Pencairan' -> Pengusul mulai bisa upload dokumen
        $model->cairkanDana($id, $tglCair, $tglLpj, $nominal);
        
        // Log & Notif
        $model->addLog($id, $_SESSION['user_id'], 'Disetujui', 'Pencairan', "Dana cair Rp ".number_format($nominal).". Deadline LPJ: $tglLpj");
        
        // Notifikasi ke Pengusul
        $this->notifyUser($id, "Dana Telah Cair", "Dana kegiatan sebesar Rp ".number_format($nominal)." telah dicairkan. Silakan upload bukti penggunaan dana (LPJ) sebelum tanggal $tglLpj.");

        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Dana dicairkan. Timer LPJ dimulai.'];
        header('Location: /pencairan');
        exit;
    }

    // --- VERIFIKASI LPJ ---
    public function indexLPJ() {
        if ($_SESSION['role'] !== 'Bendahara') { header('Location: /dashboard'); exit; }
        $model = new UsulanModel($this->db);
        // Ambil status 'Pencairan' (Belum upload) dan 'LPJ' (Sudah upload, butuh verifikasi)
        $usulan = $model->getByStatus(['Pencairan', 'LPJ']);
        require __DIR__ . '/../Views/keuangan/lpj.php';
    }

    public function verifikasiLPJ($id) {
        if ($_SESSION['role'] !== 'Bendahara') return;
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die('Invalid Token');

        $aksi = $_POST['aksi'];
        $catatan = htmlspecialchars($_POST['catatan'] ?? '');
        $model = new UsulanModel($this->db);

        if ($aksi === 'setuju') {
            // FINAL STATE: Selesai
            $model->selesaikanLPJ($id);
            $model->addLog($id, $_SESSION['user_id'], 'LPJ', 'Selesai', 'Dokumen LPJ Lengkap & Valid. Kegiatan Ditutup.');
            $this->notifyUser($id, "Kegiatan Selesai", "Terima kasih! LPJ Anda telah diterima dan kegiatan dinyatakan selesai secara administratif.");
            
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Siklus kegiatan ditutup (Selesai).'];
        
        } elseif ($aksi === 'revisi') {
            // Logic Revisi LPJ: Status tetap 'Pencairan' atau khusus 'Revisi LPJ'?
            // Agar simpel, kita kembalikan ke 'Pencairan' supaya form upload muncul lagi di user,
            // tapi kita beri Log jelas.
            $this->db->prepare("UPDATE usulan_kegiatan SET status_terkini = 'Pencairan' WHERE id = ?")->execute([$id]);
            
            $model->addLog($id, $_SESSION['user_id'], 'LPJ', 'Pencairan', "LPJ Ditolak/Revisi. Catatan: $catatan");
            $this->notifyUser($id, "Revisi LPJ Diperlukan", "Dokumen LPJ Anda perlu diperbaiki. Catatan: $catatan");

            $_SESSION['toast'] = ['type' => 'warning', 'msg' => 'LPJ dikembalikan untuk revisi.'];
        }

        header('Location: /lpj');
        exit;
    }

    private function notifyUser($usulanId, $judul, $pesan) {
        $stmt = $this->db->prepare("SELECT user_id FROM usulan_kegiatan WHERE id = ?");
        $stmt->execute([$usulanId]);
        $uid = $stmt->fetchColumn();
        if($uid) {
            $this->db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, link, created_at) VALUES (?, ?, ?, ?, NOW())")
                     ->execute([$uid, $judul, $pesan, "/usulan/detail?id=$usulanId"]);
        }
    }
}