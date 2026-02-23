<?php
// app/Controllers/LaporanController.php (UPDATED: Added RAB Submitted & Proposer Budget Pie Stats)
namespace App\Controllers;

use App\Models\UsulanModel;
use App\Models\StatsModel;
use App\Models\PengajuanModel;
use PDO;
use Exception;

class LaporanController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }
        
        $role = $_SESSION['role'];
        if (!in_array($role, ['Direktur', 'Admin'])) {
             header('Location: /dashboard'); exit;
        }

        $usulanModel = new UsulanModel($this->db);
        $statsModel = new StatsModel($this->db);
        
        // 1. Statistik Global
        $stats  = $statsModel->getGlobalDashboardStats();
        $usulanStats = $usulanModel->getUsulanGlobalStats(); 
        
        $stats['dana'] = $stats['total_dana_lpj_disetujui'] ?? 0;
        
        // [BARU] Total RAB Diajukan
        $stats['total_rab_diajukan'] = $usulanModel->getTotalRABSubmitted();

        $stats['total_usulan'] = $usulanStats['total_usulan'] ?? 0;
        $stats['selesai'] = $usulanStats['total_usulan'] - $stats['menunggu_ppk'] - $stats['menunggu_wd2'] - $usulanStats['menunggu_verif'] - $usulanStats['draft'] - $usulanStats['revisi'];

        // 2. Aktivitas Terbaru (Tidak ditampilkan di view akhir, tapi tetap diambil jika perlu)
        $recent = $usulanModel->getRecentActivity(5);

        // 3. Distribusi Anggaran (Bar Chart)
        $distribusi = $usulanModel->getBudgetDistribution();
        
        // 4. [BARU] Data untuk Pie Chart per Kategori (Usage per Proposer)
        $rawDistByProposer = $usulanModel->getBudgetDistributionByProposer();
        $pieChartData = [];
        foreach ($rawDistByProposer as $row) {
            $cat = $row['nama_kategori'];
            if (!isset($pieChartData[$cat])) {
                $pieChartData[$cat] = [];
            }
            $pieChartData[$cat][] = [
                'proposer' => $row['username'],
                'amount' => (float) $row['total_anggaran']
            ];
        }

        require __DIR__ . '/../Views/laporan/index.php';
    }
    
    // ... (Sisa method riwayatApproval dan rabKategori tetap sama) ...
    
    public function riwayatApproval($role)
    {
        // 1. Cek Login & Role
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }
        if (!in_array($_SESSION['role'], ['PPK', 'WD2']) && $_SESSION['role'] !== 'Admin') { 
            header('Location: /dashboard'); exit; 
        }
        
        $pengajuanModel = new \App\Models\PengajuanModel($this->db);

        // 2. Judul Halaman (Wajib ada)
        $title = ($role === 'PPK') ? 'Riwayat Persetujuan PPK' : 'Riwayat Persetujuan WD2';

        // 3. Ambil Parameter dari URL
        $search = $_GET['q'] ?? '';
        $filterProposer = $_GET['pengusul'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // --- SETTING PAGINATION ---
        $limit = 10; // Menampilkan 10 data per halaman
        $offset = ($page - 1) * $limit; // Menghitung data mulai dari mana
        // --------------------------

        // 4. Ambil Daftar Pengusul (Wajib ada untuk Filter)
        $stmtPengusul = $this->db->query("SELECT id, username, nama FROM users WHERE role = 'Pengusul' ORDER BY nama ASC");
        $listPengusul = $stmtPengusul->fetchAll(PDO::FETCH_ASSOC);

        $riwayat = [];
        $totalItems = 0;

        // 5. Logika Pengambilan Data
        if ($role === 'PPK') {
            // (A) HITUNG TOTAL DATA (Mengambil angka 322)
            // Pastikan fungsi countRiwayatByStatusPPK SUDAH DITAMBAHKAN di Model
            $totalItems = $pengajuanModel->countRiwayatByStatusPPK($search, $filterProposer);
            
            // (B) AMBIL DATA SESUAI HALAMAN (Hanya ambil 10 data)
            $riwayat = $pengajuanModel->getRiwayatByStatusPPKOptimized($limit, $offset, $search, $filterProposer);
        
        } elseif ($role === 'WD2') {
            // Fallback logika lama untuk WD2
            $raw = $pengajuanModel->getRiwayatByStatusWD2();
            $processedData = [];
            foreach ($raw as $row) {
                if (!empty($search) && stripos($row['nama_kegiatan'], $search) === false) continue;
                if (!empty($filterProposer) && $row['username'] !== $filterProposer) continue;
                $processedData[] = $row;
            }
            $totalItems = count($processedData);
            $riwayat = array_slice($processedData, $offset, $limit);
        }

        // 6. Hitung Total Halaman (322 / 10 = 33 Halaman)
        $totalPages = ceil($totalItems / $limit);
        
        // 7. Data Pagination untuk View
        $pager = [
            'current' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems
        ];

        require __DIR__ . '/../Views/pengajuan/riwayat_approval.php';
    }

    public function rabKategori($usulanId = null) {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }
        $allowedRoles = ['Direktur', 'Admin', 'PPK', 'WD2'];
        if (!in_array($_SESSION['role'], $allowedRoles)) { header('Location: /dashboard'); exit; }
        $usulanId = $usulanId ?? ($_GET['usulan_id'] ?? null);
        if (!$usulanId) { header('Location: /laporan?error=Usulan ID required'); exit; }
        try {
            $stmt = $this->db->prepare("CALL sp_laporan_rab_kategori(?)");
            $stmt->execute([$usulanId]);
            $kategoriData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            $totalData = $stmt->fetch(PDO::FETCH_ASSOC);
            $usulanModel = new UsulanModel($this->db);
            $usulan = $usulanModel->findById($usulanId);
            if (!$usulan) { header('Location: /laporan?error=Usulan not found'); exit; }
            require __DIR__ . '/../Views/laporan/rab_kategori.php';
        } catch (Exception $e) {
            error_log("Error in rabKategori: " . $e->getMessage());
            header('Location: /laporan?error=Failed to generate report'); exit;
        }
    }
}
?>