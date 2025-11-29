<?php
namespace App\Controllers;

use App\Models\UsulanModel;

class DashboardController
{
    private $db;

    // Wajib menerima koneksi DB dari index.php
    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $role = $_SESSION['role'] ?? '';
        $userId = $_SESSION['user_id'];
        
        // Inisialisasi Model
        $usulanModel = new UsulanModel($this->db);
        
        // Siapkan Variabel Default (Agar tidak error Undefined Variable di View)
        $stats = [];
        $recent = [];
        $usulan = [];    // Untuk menampung daftar antrian approval
        $lateItems = []; // Khusus untuk WD2 (Surat Teguran)

        // Logika Pengambilan Data Berdasarkan Peran
        if ($role === 'Pengusul') {
            $stats = $usulanModel->getUserStats($userId);
            $recent = $usulanModel->getAllWithUser(['user_id' => $userId], 1, 5);
        } else {
            // Untuk Direktur, Admin, Verifikator -> Lihat data Global
            $stats = $usulanModel->getDashboardStats();
            $recent = $usulanModel->getRecentActivity(5);

            // [LOGIKA KHUSUS WD2]
            if ($role === 'WD2') {
                // Ambil data keterlambatan untuk fitur Surat Teguran
                $lateItems = $usulanModel->getOverdueItems();
                
                // Ambil daftar usulan yang KHUSUS menunggu persetujuan WD2
                $usulan = $usulanModel->getAllWithUser(['status' => 'Menunggu WD2']);
            }

            // [LOGIKA KHUSUS PPK]
            if ($role === 'PPK') {
                // Ambil daftar usulan yang KHUSUS menunggu persetujuan PPK
                $usulan = $usulanModel->getAllWithUser(['status' => 'Menunggu PPK']);
            }
        }

        // Routing ke View yang Tepat
        switch ($role) {
            case 'Admin':
                require __DIR__ . '/../Views/dashboard/admin.php'; break;
            case 'Pengusul':
                require __DIR__ . '/../Views/dashboard/pengusul.php'; break;
            case 'Verifikator':
                require __DIR__ . '/../Views/dashboard/verifikator.php'; break;
            case 'WD2':
                // Variabel $lateItems dan $usulan otomatis terkirim ke sini
                require __DIR__ . '/../Views/dashboard/wd2.php'; break;
            case 'PPK':
                require __DIR__ . '/../Views/dashboard/ppk.php'; break;
            case 'Bendahara':
                // [ELITE UPGRADE] Hitung Antrian Tugas Bendahara
                // 1. Siap Cair (Status: Disetujui)
                $countCair = $usulanModel->countAllWithUser(['status' => 'Disetujui']);
                // 2. Tunggu Verifikasi LPJ (Status: LPJ)
                $countLPJ = $usulanModel->countAllWithUser(['status' => 'LPJ']);
                
                require __DIR__ . '/../Views/dashboard/bendahara.php'; 
                break;
            case 'Direktur':
                require __DIR__ . '/../Views/dashboard/direktur.php'; break;
            default:
                http_response_code(403);
                require __DIR__ . '/../Views/errors/403.php';
        }
    }
}