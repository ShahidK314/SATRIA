<?php
// app/Controllers/DashboardController.php
namespace App\Controllers;

use App\Models\UsulanModel;
use App\Models\PengajuanModel;
use App\Models\LpjModel;
use App\Models\PencairanModel;
use App\Models\StatsModel;
use PDO;
use Exception;

class DashboardController
{
    private $db;

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
        
        $usulanModel = new UsulanModel($this->db);
        $statsModel = new StatsModel($this->db);
        
        $stats = [];
        $recent = [];
        $usulanAntrian = []; 
        $lateItems = []; 
        $proposerStats = []; 
        $usulan = []; 
        
        // --- LOGIKA PENGATURAN NAMA TAMPILAN ---
        $userProfile = $this->db->prepare("SELECT u.username, u.nama, mj.nama_jurusan 
                                        FROM users u 
                                        LEFT JOIN master_jurusan mj ON u.jurusan_id = mj.id 
                                        WHERE u.id = ?");
        $userProfile->execute([$userId]);
        $profile = $userProfile->fetch(PDO::FETCH_ASSOC);
        
        $namaToStore = ($role === 'Pengusul') ? ($profile['nama_jurusan'] ?? $profile['username']) : $profile['username'];
        
        if ($profile['nama'] !== $namaToStore) {
             $this->db->prepare("UPDATE users SET nama = ? WHERE id = ?")
                      ->execute([$namaToStore, $userId]);
        }
        
        $_SESSION['display_name'] = $role; 
        
        // --- LOGIKA PENGAMBILAN DATA BERDASARKAN PERAN ---
        if ($role === 'Pengusul') {
            $stats = $usulanModel->getUserStats($userId);
            
            // 1. Ambil Parameter Filter dari URL
            $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
            $search = filter_var($_GET['q'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $status = filter_var($_GET['status'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $limit = 10; // Data per halaman

            // 2. Siapkan Filter
            $filters = [
                'user_id' => $userId,
                'search'  => $search,
                'status'  => $status
            ];

            // 3. Ambil Data dengan Filter & Paging
            $usulan = $usulanModel->getAllWithUser($filters, $page, $limit);
            $totalData = $usulanModel->countAllWithUser($filters);
            $totalPages = ceil($totalData / $limit);

            // 4. Siapkan Data Pager untuk View
            $pager = [
                'current' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalData,
                'limit' => $limit
            ]; 
        } elseif (in_array($role, ['Admin', 'Direktur'])) {
            $globalStats = $statsModel->getGlobalDashboardStats();
            $usulanStatusStats = $usulanModel->getUsulanGlobalStats(); 
            
            $stats = array_merge($globalStats, $usulanStatusStats);

            $recent = $usulanModel->getRecentActivity(5); 
            $proposerStats = $usulanModel->getProposerStats(date('Y')); 
            
        } elseif ($role === 'Verifikator') {
            $usulanStats = $usulanModel->getUsulanGlobalStats(); 
            $stats['total_verifikasi'] = $usulanStats['menunggu_verif']; 
            $stats = array_merge($stats, $usulanStats); 
            $recent = $usulanModel->getRecentActivity(5); 
        } elseif (in_array($role, ['PPK', 'WD2'])) {
            $pengajuanModel = new PengajuanModel($this->db);
            $stats = $statsModel->getGlobalDashboardStats(); 
            
            if ($role === 'PPK') {
                $usulanAntrian = $pengajuanModel->getByStatus('Menunggu PPK');
            } else {
                $usulanAntrian = $pengajuanModel->getByStatus('Menunggu WD2');
                // [REMOVED] $lateItems = $statsModel->getOverdueItems(); -> Tidak perlu untuk WD2
            }
            $usulan = $usulanAntrian;
        } elseif ($role === 'Bendahara') {
            $globalStats = $statsModel->getGlobalDashboardStats();
            $lpjModel = new LpjModel($this->db);
            $pencairanModel = new PencairanModel($this->db);

            // Parameter Filter & Pagination
            $search = $_GET['q'] ?? '';
            $filterProposer = $_GET['pengusul'] ?? '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 10;

            // Ambil data statistik untuk card
            $stats = [
                'total_cair' => $globalStats['total_dana_cair'], 
                'count_cair' => count($pencairanModel->getPendingCair()),
                'count_lpj' => count($lpjModel->getPendingVerifikasi())
            ];
            
            // Ambil daftar pengusul untuk dropdown filter
            $stmtPengusul = $this->db->query("SELECT id, username, nama FROM users WHERE role = 'Pengusul' ORDER BY nama ASC");
            $listPengusul = $stmtPengusul->fetchAll(PDO::FETCH_ASSOC);

            // Ambil semua aktivitas dari model
            $activities = $pencairanModel->getBendaharaActivities();
            
            $processedData = [];
            foreach ($activities as $item) {
                // Filter Pencarian (Nama Kegiatan)
                if (!empty($search) && stripos($item['nama_kegiatan'], $search) === false) {
                    continue;
                }
                // Filter Pengusul (Berdasarkan username atau id user pengusul)
                if (!empty($filterProposer) && $item['username'] !== $filterProposer) {
                    continue;
                }
                $processedData[] = $item;
            }

            // Hitung Pagination
            $totalItems = count($processedData);
            $totalPages = ceil($totalItems / $limit);
            $page = max(1, min($page, $totalPages > 0 ? $totalPages : 1));
            $offset = ($page - 1) * $limit;
            
            $usulan = array_slice($processedData, $offset, $limit);
            
            $pager = [
                'current' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
                'limit' => $limit
            ];
        }

        $this->autoSendLpjReminders();

        switch ($role) {
            case 'Pengusul': require __DIR__ . '/../Views/dashboard/pengusul.php'; break;
            case 'Verifikator': require __DIR__ . '/../Views/dashboard/verifikator.php'; break;
            case 'PPK': require __DIR__ . '/../Views/dashboard/ppk.php'; break;
            case 'WD2': require __DIR__ . '/../Views/dashboard/wd2.php'; break;
            case 'Bendahara': require __DIR__ . '/../Views/dashboard/bendahara.php'; break;
            case 'Admin': require __DIR__ . '/../Views/dashboard/admin.php'; break;
            case 'Direktur': require __DIR__ . '/../Views/dashboard/direktur.php'; break;
            default: http_response_code(403); require __DIR__ . '/../Views/errors/403.php';
        }
    }

    private function autoSendLpjReminders()
    {
        $allowedRoles = ['Admin', 'Direktur'];
        if (!in_array($_SESSION['role'] ?? '', $allowedRoles)) {
            return;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT
                    lpj_id, usulan_id, judul_usulan,
                    fn_hitung_keterlambatan_lpj(lpj_id) as hari_keterlambatan,
                    u.user_id as pengusul_id, u.nama as nama_pengusul
                FROM v_lpj_keterlambatan v
                JOIN usulan us ON v.usulan_id = us.id
                JOIN users u ON us.user_id = u.id
                WHERE fn_hitung_keterlambatan_lpj(lpj_id) > 0
                AND lpj_id NOT IN (
                    SELECT DISTINCT JSON_UNQUOTE(JSON_EXTRACT(data, '$.lpj_id'))
                    FROM notifikasi
                    WHERE judul = 'Pengingat Keterlambatan LPJ'
                    AND DATE(created_at) = CURDATE()
                )
            ");
            $stmt->execute();
            $lateLpjList = $stmt->fetchAll();

            foreach ($lateLpjList as $lpj) {
                $pesan = "Pengingat Otomatis: LPJ untuk usulan '{$lpj['judul_usulan']}' sudah terlambat {$lpj['hari_keterlambatan']} hari. Segera lengkapi dan ajukan LPJ Anda.";

                $stmt = $this->db->prepare("
                    INSERT INTO notifikasi (user_id, judul, pesan, link, created_at, data)
                    VALUES (?, 'Pengingat Keterlambatan LPJ', ?, '/pengajuan/lpj', NOW(), ?)
                ");
                $stmt->execute([
                    $lpj['pengusul_id'],
                    $pesan,
                    json_encode(['lpj_id' => $lpj['lpj_id'], 'auto_reminder' => true])
                ]);
            }
        } catch (Exception $e) {
            error_log("Auto LPJ Reminder Error: " . $e->getMessage());
        }
    }
}