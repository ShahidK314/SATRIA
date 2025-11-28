<?php
namespace App\Controllers;

use App\Models\AdminModel;

class AuditLogController
{
    private $db;
    public function __construct($db) { $this->db = $db; }

    private function checkAccess() {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
            header('Location: /login'); exit;
        }
    }

    public function index()
    {
        $this->checkAccess();

        // 1. Sanitasi Input Filter
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        $perPage = 20;
        
        $filters = [
            'user'   => filter_var($_GET['user'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS),
            'action' => filter_var($_GET['action'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS),
            'date'   => filter_var($_GET['date'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS)
        ];

        // 2. Ambil Data & Hitung Total
        $model = new AdminModel($this->db);
        $logs = $model->getAuditLogs($filters, $page, $perPage);
        $totalLogs = $model->countAuditLogs($filters);
        
        // 3. Hitung Total Halaman
        $totalPages = ceil($totalLogs / $perPage);

        require __DIR__ . '/../Views/admin/audit_log.php';
    }

    public function export()
    {
        $this->checkAccess();

        $model = new AdminModel($this->db);
        $logs = $model->getAllLogsForExport();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="audit_log_'.date('Y-m-d').'.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Waktu', 'User', 'Aksi', 'IP Address']);

        foreach ($logs as $log) {
            fputcsv($output, $log);
        }

        fclose($output);
        exit;
    }
}