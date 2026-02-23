<?php
// app/Controllers/MonitoringController.php (UPDATED: Status Derivation Logic)
namespace App\Controllers;

use PDO;
use App\Models\UsulanModel;
use App\Models\AdminModel;

class MonitoringController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // PERBAIKAN: Ambil halaman dari $_GET, jika tidak ada default ke 1
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5; 

        $usulanModel = new UsulanModel($this->db);
        $role = $_SESSION['role'] ?? '';
        
        if (!($role === 'Pengusul')) {
            header('Location: /dashboard'); 
            exit;
        }

        $filters = [
            'user_id' => $_SESSION['user_id'],
            'role'    => $role,
            'search'  => $_GET['q'] ?? '',
            'status'  => $_GET['status'] ?? '',
        ];

        // Data for table
        $usulan = $usulanModel->getAllWithUser($filters, $page, $perPage);
        $total  = $usulanModel->countAllWithUser($filters);
        
        $totalPages = ceil($total / $perPage);

        require __DIR__ . '/../Views/monitoring/index.php';
    }
}