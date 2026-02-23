<?php
// app/Controllers/VerifikasiController.php (UPDATED: Fix Pagination & Draft Count)
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

    // ===============================================
    //  DASHBOARD VERIFIKATOR
    // ===============================================
    public function dashboard()
    {
        $this->ensureLogin();

        // [PERBAIKAN] total_usulan exclude Draft
        $stats = [
            'total_usulan'    => $this->db->query("SELECT COUNT(*) FROM usulan_kegiatan WHERE status_terkini != 'Draft'")->fetchColumn(),
            'menunggu_verif'  => $this->db->query("SELECT COUNT(*) FROM usulan_kegiatan WHERE status_terkini='Diajukan'")->fetchColumn(),
            'disetujui_verif' => $this->db->query("SELECT COUNT(*) FROM usulan_kegiatan WHERE status_terkini IN ('Disetujui', '')")->fetchColumn(),
            'revisi'          => $this->db->query("SELECT COUNT(*) FROM usulan_kegiatan WHERE status_terkini='Revisi'")->fetchColumn(),
        ];

        $stmt = $this->db->prepare("
            SELECT id, nama_kegiatan, tanggal_mulai, tanggal_selesai, status_terkini, 
                   (SELECT username FROM users WHERE id = u.user_id) as username
            FROM usulan_kegiatan u
            WHERE status_terkini = 'Diajukan'
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $stmt->execute();
        $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/dashboard/verifikator.php';
    }

    // ===============================================
    //  INDEX
    // ===============================================
    public function index($page = 1, $perPage = 10)
    {
        $this->ensureLogin();
        $usulanModel = new UsulanModel($this->db);
        
        // [PERBAIKAN] Mengambil halaman dari URL Query String agar paginasi bekerja
        if (isset($_GET['page'])) {
            $page = max(1, (int)$_GET['page']);
        }

        $filters = ['status' => 'Diajukan'];
        
        $usulan = $usulanModel->getAllWithUser($filters, $page, $perPage);
        $total = $usulanModel->countAllWithUser($filters); 
        $totalPages = ceil($total / $perPage); 

        require __DIR__ . '/../Views/verifikasi/index.php';
    }

    // ===============================================
    //  RIWAYAT
    // ===============================================
    public function riwayat($page = 1, $perPage = 10)
    {
        $this->ensureLogin();
        $usulanModel = new UsulanModel($this->db);
        
        $filters = ['status' => ['Disetujui', 'Revisi', 'Ditolak', '']];
        
        $usulan = $usulanModel->getByStatus($filters['status']);
        $total = count($usulan); 
        
        require __DIR__ . '/../Views/verifikasi/riwayat.php';
    }

    // ===============================================
    //  PROSES
    // ===============================================
    public function proses($id)
    {
        $this->ensureLogin();

        $usulanModel = new UsulanModel($this->db);

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

        $usulan['indikator_array'] = $usulan['indikator_kinerja'] ? json_decode($usulan['indikator_kinerja'], true) : [];
        $usulan['metode_array'] = $usulan['metode_pelaksanaan'] ? json_decode($usulan['metode_pelaksanaan'], true) : [];
        $usulan['tahapan_array'] = $usulan['tahapan_pelaksanaan'] ? json_decode($usulan['tahapan_pelaksanaan'], true) : [];

        $rabData = $usulanModel->getRABDetails($id);
        $ikuData = $usulanModel->getIKUDetails($id);

        require __DIR__ . '/../Views/verifikasi/proses.php';
    }

    // ===============================================
    //  AKSI (setuju/revisi/tolak)
    // ===============================================
    public function aksi($id)
    {
        $this->ensureLogin();
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Security Alert: Invalid Token');
        }

        $aksi = $_POST['aksi'];
        $catatan = trim($_POST['catatan'] ?? ''); 
        $kode_mak = trim($_POST['kode_mak'] ?? '');

        // Validasi Sisi Server
        if ($aksi === 'setuju' && empty($kode_mak)) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Kode MAK wajib diisi untuk menyetujui usulan!'];
            header("Location: /verifikasi/proses?id=$id"); 
            exit;
        }
        if (($aksi === 'revisi' || $aksi === 'tolak') && empty($catatan)) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Catatan wajib diisi saat meminta revisi/menolak usulan!'];
            header("Location: /verifikasi/proses?id=$id"); 
            exit;
        }

        $status = 'Disetujui'; 
        if ($aksi === 'revisi') $status = 'Revisi';
        if ($aksi === 'tolak') $status = 'Ditolak';

        try {
            $this->db->beginTransaction();
            $usulanModel = new UsulanModel($this->db);

            // [AMAN] Gunakan Procedure untuk update status & Log History
            // Database yang akan menangani perubahan status dan insert log
            $usulanModel->changeStatus($id, $_SESSION['user_id'], $status, $catatan);

            // Update Kode MAK (Khusus jika disetujui, karena ini field khusus yang tidak ada di SP standar)
            if ($status === 'Disetujui') {
                $this->db->prepare("UPDATE usulan_kegiatan SET kode_mak=? WHERE id=?")
                         ->execute([$kode_mak, $id]);
            }
            
            // Notifikasi (Fitur Aplikasi, tetap di PHP)
            $userSql = $this->db->prepare("SELECT user_id FROM usulan_kegiatan WHERE id=?");
            $userSql->execute([$id]);
            $u = $userSql->fetch(PDO::FETCH_ASSOC);

            if ($u) {
                if ($aksi === 'setuju') {
                    $pesan = "Usulan disetujui Verifikator. Silakan lanjut ke menu Pengajuan Kegiatan.";
                    $link = "/pengajuan/kegiatan"; 
                } elseif ($aksi === 'revisi') {
                    $pesan = "Usulan dikembalikan (Revisi). Cek catatan verifikator untuk detail dan ajukan ulang.";
                    $link = "/usulan/edit?id=$id";
                } else {
                    $pesan = "Usulan DITOLAK. Anda dapat menghapus usulan ini dan mengganti yang baru.";
                    $link = "/usulan/detail?id=$id";
                }

                $this->db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, link, created_at)
                                    VALUES (?, ?, ?, ?, NOW())")
                         ->execute([$u['user_id'], "Update Usulan: $status", $pesan, $link]);
            }

            $this->db->commit();
            $_SESSION['toast'] = ['type' => 'success', 'msg' => "Usulan berhasil diproses ($status)."];

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Verifikasi Error: " . $e->getMessage());
            $_SESSION['toast'] = ['type' => 'error', 'msg' => "Terjadi kesalahan sistem!"];
        }

        header('Location: /verifikasi');
        exit;
    }
}