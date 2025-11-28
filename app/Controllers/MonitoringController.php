<?php
// app/Controllers/MonitoringController.php
namespace App\Controllers;

use PDO;
use App\Models\UsulanModel;

class MonitoringController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function index($page = 1, $perPage = 10)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Load Model
        $usulanModel = new UsulanModel($this->db);

        // Filters
        $filters = [
            'role'    => $_SESSION['role'] ?? '',
            'user_id' => $_SESSION['user_id'],
            'search'  => $_GET['q'] ?? '',
            'status'  => $_GET['status'] ?? '',
            'date'    => $_GET['date'] ?? ''
        ];

        // Data for table
        $usulan = $usulanModel->getAllWithUser($filters, $page, $perPage);
        $total  = $usulanModel->countAllWithUser($filters);

        // ----------------------------------------
        // FIX UTAMA → Tambahkan $isEditable
        // ----------------------------------------
        $role = $_SESSION['role'] ?? '';

        // Role yang boleh edit monitoring
        $isEditable = in_array($role, ['admin', 'superadmin', 'departemen']);

        // Kirim variable ke View
        require __DIR__ . '/../Views/monitoring/index.php';
    }
}
