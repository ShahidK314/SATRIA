<?php
namespace App\Controllers;

use App\Models\UsulanModel;

class LaporanController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }
        
        // Pastikan hanya Direktur/Pimpinan yang bisa akses (Opsional, tapi disarankan)
        // if ($_SESSION['role'] !== 'Direktur') { ... }

        $model = new UsulanModel($this->db);
        
        // Ambil Statistik Global
        $stats  = $model->getDashboardStats();
        
        // [FIX] Pastikan variable 'dana' ada (karena di view dipanggil $stats['dana'])
        // Di Model outputnya 'dana_cair', kita mapping agar aman.
        $stats['dana'] = $stats['dana_cair'] ?? 0;

        $recent = $model->getRecentActivity(5);
        
        require __DIR__ . '/../Views/laporan/index.php';
    }
}