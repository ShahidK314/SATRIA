<?php
// app/Controllers/VerifikasiController.php
namespace App\Controllers;

use PDO;
use App\Models\UsulanModel; // Gunakan Model

class VerifikasiController
{
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function index($page = 1, $perPage = 10)
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Verifikator') {
            header('Location: /login'); exit;
        }

        // [ELITE REFACTORING] Gunakan Model
        $usulanModel = new UsulanModel($this->db);
        
        // Kita gunakan filter 'status' yang sudah didukung oleh Model
        $filters = [
            'status' => 'Verifikasi' 
        ];

        // Ambil data bersih dari Model
        $usulan = $usulanModel->getAllWithUser($filters, $page, $perPage);
        
        // Anda mungkin perlu menambahkan method countByStatus di Model nanti untuk pagination yang sempurna
        // Untuk sekarang, kita pakai countAllWithUser dengan filter yang sama
        $total = $usulanModel->countAllWithUser($filters); 

        require __DIR__ . '/../Views/verifikasi/index.php';
    }

    public function proses($id)
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Verifikator') {
            header('Location: /login');
            exit;
        }

        // Ambil data usulan
        $stmt = $this->db->prepare("SELECT * FROM usulan_kegiatan WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $usulan = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$usulan) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            exit;
        }
        require __DIR__ . '/../Views/verifikasi/proses.php';
    }

    public function aksi($id)
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Verifikator') {
            header('Location: /login'); exit;
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('Invalid Token');

        $aksi = $_POST['aksi'];
        $catatan = trim($_POST['catatan']);
        $kode_mak = trim($_POST['kode_mak']);

        $status = 'Disetujui Verifikator'; // TARGET BARU
        if ($aksi === 'revisi') $status = 'Revisi';
        if ($aksi === 'tolak') $status = 'Ditolak';

        $this->db->prepare("UPDATE usulan_kegiatan SET status_terkini=?, kode_mak=? WHERE id=?")->execute([$status, $kode_mak, $id]);
        
        // Log & Notif
        $this->db->prepare("INSERT INTO log_histori_usulan (usulan_id, user_id, status_lama, status_baru, catatan) VALUES (?,?,'Verifikasi',?,?)")->execute([$id, $_SESSION['user_id'], $status, $catatan]);
        
        // Jika disetujui, notif user untuk upload
        if($status === 'Disetujui Verifikator') {
            $user = $this->db->query("SELECT user_id FROM usulan_kegiatan WHERE id=$id")->fetchColumn();
            $this->db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, link) VALUES (?, 'Verifikasi Selesai', 'Usulan disetujui. Silakan lengkapi data PJ & Upload Surat Pengantar.', ?)")->execute([$user, "/usulan/lengkapi?id=$id"]);
        }

        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Verifikasi Selesai.'];
        header('Location: /verifikasi'); exit;
    }
}
