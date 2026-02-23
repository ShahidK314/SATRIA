<?php
// app/Controllers/MenuController.php (FIXED: Added Stats Fetching)
namespace App\Controllers;

use PDO;
use App\Models\UsulanModel;

class MenuController
{
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    private function ensureLogin()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }
    
    /**
     * Menu: Pengajuan Usulan (List Draft, Diajukan, dll)
     */
    public function indexPengajuanUsulan()
    {
        $this->ensureLogin();
        
        $usulanModel = new UsulanModel($this->db);
        $userId = $_SESSION['user_id'];

        // 1. Ambil Parameter
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        $search = filter_var($_GET['q'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $status = filter_var($_GET['status'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $limit = 10;

        // 2. Siapkan Filter
        $filters = [
            'user_id' => $userId,
            'search'  => $search,
            'status'  => $status
        ];

        // 3. Ambil Data List Usulan
        $usulan = $usulanModel->getAllWithUser($filters, $page, $limit);
        $totalData = $usulanModel->countAllWithUser($filters);
        $totalPages = ceil($totalData / $limit);

        // [PERBAIKAN] 4. Ambil Data Statistik untuk User ini
        $stats = $usulanModel->getUserStats($userId);

        // 5. Data Paginasi
        $pager = [
            'current' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalData,
            'limit' => $limit
        ];
        
        require __DIR__ . '/../Views/pengajuan/index_pengajuan.php';
    }
    
    /**
     * Menu: Pengajuan Kegiatan (Usulan yang sudah Disetujui Verifikator)
     */
    public function indexPengajuanKegiatan()
    {
        $this->ensureLogin();
        
        // Ambil usulan yang statusnya "Disetujui" (dari Verifikator)
        // DAN belum ada di tabel pengajuan_kegiatan
        $stmt = $this->db->prepare("
            SELECT u.* FROM usulan_kegiatan u
            LEFT JOIN pengajuan_kegiatan p ON u.id = p.usulan_id
            WHERE u.user_id = ? 
            AND u.status_terkini = 'Disetujui'
            AND p.id IS NULL
            ORDER BY u.updated_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $usulan = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../Views/usulan/index_pengajuan_kegiatan.php'; 
    }
    
    /**
     * Menu: Pengajuan LPJ (Kegiatan yang sudah Dicairkan)
     */
    public function indexPengajuanLPJ()
    {
        $this->ensureLogin();
        
        // Ambil LPJ yang perlu diupload (terhubung ke pencairan)
        $stmt = $this->db->prepare("
            SELECT l.*, pc.nominal_dicairkan, pc.tanggal_batas_lpj, p.id as pengajuan_id, u.nama_kegiatan, u.nominal_pencairan, l.catatan_bendahara
            FROM lpj l
            JOIN pencairan_dana pc ON l.pencairan_id = pc.id
            JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id
            JOIN usulan_kegiatan u ON p.usulan_id = u.id
            WHERE u.user_id = ? 
            AND l.status_terkini IN ('Belum Upload', 'Revisi')
            ORDER BY pc.tanggal_batas_lpj ASC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $lpjList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../Views/lpj/upload_form.php'; 
    }
}