<?php
namespace App\Controllers;

use App\Models\UsulanModel;

class LaporanController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }
        
        // Hanya Direktur dan Admin yang bisa akses laporan lengkap
        $role = $_SESSION['role'];
        if (!in_array($role, ['Direktur', 'Admin', 'WD2'])) {
             header('Location: /dashboard'); exit;
        }

        $model = new UsulanModel($this->db);
        
        // 1. Statistik Global
        $stats  = $model->getDashboardStats();
        $stats['dana'] = $stats['dana_cair'] ?? 0;

        // 2. Aktivitas Terbaru
        $recent = $model->getRecentActivity(5);

        // 3. [BARU] Distribusi Anggaran (Real Data)
        $distribusi = $model->getBudgetDistribution();

        require __DIR__ . '/../Views/laporan/index.php';
    }
}