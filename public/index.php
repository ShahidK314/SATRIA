<?php
// public/index.php (FINAL FIX: Added Global Output Buffering Control & Access Update)

session_start();

// ========================================
// START: AGGRESSIVE GLOBAL OUTPUT BUFFERING
// Mencegah output yang tidak disengaja merusak header gambar/PDF
// ========================================
$current_url = $_SERVER['REQUEST_URI'] ?? '';
$is_public_asset = strpos($current_url, '/public/uploads/') !== false || strpos($current_url, '/css/') !== false;

if (!$is_public_asset) {
    // Hanya aktifkan buffering jika bukan request untuk aset publik (CSS/JS)
    // dan mungkin memerlukan header khusus (seperti image/pdf)
    ob_start();
}
// ========================================

// --- 1. Konfigurasi dan Autoload ---
require __DIR__ . '/../config/database.php'; 
require __DIR__ . '/../vendor/autoload.php'; 

// Fungsi untuk mendapatkan URL yang bersih
function getUri() {
    $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    if ($uri === 'index.php') return '';
    
    $uri = preg_replace('/^public\//', '', $uri);
    return $uri;
}

// Mendapatkan URL saat ini
$uri = getUri();
$method = $_SERVER['REQUEST_METHOD'];

// Fungsi connectDB() sekarang bisa dipanggil
$pdo = connectDB(); 

// ========================================
// PRIORITY: HANDLE CAPTCHA FIRST (Before any other logic)
// ========================================
if ($uri === 'captcha' || $uri === 'captcha.php' || $uri === 'captcha/generate' || strpos($uri, 'captcha.php') !== false) {
    // Bersihkan output buffer yang mungkin sudah terkumpul sebelum mengirim image header
    if (ob_get_level() > 0) ob_end_clean(); 
    // Mengganti pemanggilan Controller dengan menyertakan file generator Captcha yang berdiri sendiri
    require __DIR__ . '/captcha.php'; 
    exit; 
}

// --- 2. Middleware Otentikasi & Otorisasi ---

// Daftar rute yang TIDAK memerlukan login
$publicRoutes = [
    '', // Halaman utama (Landing Page)
    'login',
    'logout',
    'bantuan',
    'syarat',
];

// Cek apakah user sudah login
$isLoggedIn = isset($_SESSION['user_id']);

// Middleware Otentikasi
if (!$isLoggedIn && !in_array($uri, $publicRoutes) && !str_starts_with($uri, 'pdf/')) {
    if ($uri !== '') {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    }
    header('Location: /login');
    exit;
}

// Middleware Otorisasi
function checkRole(array $allowedRoles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
        http_response_code(403);
        require __DIR__ . '/../app/Views/errors/403.php';
        exit;
    }
}

// Set CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- [PERBAIKAN] CSRF & CONTENT LENGTH CHECK ---
if ($method === 'POST') {
    // Deteksi jika data $_POST hilang karena melebihi post_max_size server
    if (empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $_SESSION['toast'] = [
            'type' => 'error', 
            'msg' => 'Gagal mengirim data: Ukuran file terlalu besar. Silakan unggah file yang lebih kecil (Maks. 40MB).'
        ];
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/dashboard'));
        exit;
    }

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Security Alert: Invalid CSRF Token atau Sesi Form Telah Berakhir.');
    }
}

// --- 3. Routing (Pemetaan URL ke Controller) ---

switch ($uri) {
    // --- LANDING PAGE & AUTH ---
    case '':
        if ($isLoggedIn) {
            header('Location: /dashboard');
        } else {
            require __DIR__ . '/../app/Views/welcome.php';
        }
        break;
    case 'login':
        $controller = new App\Controllers\AuthController($pdo);
        if ($method === 'POST') {
            $controller->login();
        } else {
            $controller->showLogin();
        }
        break;
    case 'logout':
        (new App\Controllers\AuthController($pdo))->logout();
        break;
    // --- PUBLIC PAGES ---
    case 'bantuan':
        (new App\Controllers\PageController($pdo))->bantuan();
        break;
    case 'syarat':
        (new App\Controllers\PageController($pdo))->syarat();
        break;

    // --- DASHBOARD ---
    case 'dashboard':
        (new App\Controllers\DashboardController($pdo))->index();
        break;
    
    case 'verifikasi':
        checkRole(['Verifikator']);
        (new App\Controllers\VerifikasiController($pdo))->index();
        break;
    case 'verifikasi/riwayat':
        checkRole(['Verifikator']);
        (new App\Controllers\VerifikasiController($pdo))->riwayat();
        break;
    case 'verifikasi/proses':
        checkRole(['Verifikator']);
        (new App\Controllers\VerifikasiController($pdo))->proses($_GET['id'] ?? 0);
        break;
    case 'verifikasi/aksi':
        checkRole(['Verifikator']);
        (new App\Controllers\VerifikasiController($pdo))->aksi($_GET['id'] ?? 0);
        break;

    // --- PENGUSUL FLOW (MASTER KAK/IKU/RAB) ---
    case 'pengajuan/usulan': 
        checkRole(['Pengusul']);
        (new App\Controllers\MenuController($pdo))->indexPengajuanUsulan();
        break;
    case 'usulan/create':
        checkRole(['Pengusul']);
        (new App\Controllers\UsulanController($pdo))->create();
        break;
    case 'usulan/edit':
        checkRole(['Pengusul']);
        (new App\Controllers\UsulanController($pdo))->edit($_GET['id'] ?? 0);
        break;
    case 'usulan/process-store': 
        checkRole(['Pengusul']);
        (new App\Controllers\UsulanController($pdo))->processStore();
        break;
    case (preg_match('/usulan\/ajukan\/(\d+)/', $uri, $matches) ? 'usulan/ajukan/'.$matches[1] : ''):
        checkRole(['Pengusul']);
        (new App\Controllers\UsulanController($pdo))->ajukan($matches[1] ?? 0);
        break;
    case (preg_match('/usulan\/delete\/(\d+)/', $uri, $matches) ? 'usulan/delete/'.$matches[1] : ''):
        checkRole(['Pengusul']);
        (new App\Controllers\UsulanController($pdo))->delete($matches[1] ?? 0);
        break;
    case 'usulan/detail':
        (new App\Controllers\UsulanController($pdo))->detail($_GET['id'] ?? 0);
        break;

    // --- PENGUSUL FLOW (PENGAJUAN KEGIATAN KE PPK) ---
    case 'pengajuan/kegiatan': 
        checkRole(['Pengusul']);
        (new App\Controllers\MenuController($pdo))->indexPengajuanKegiatan();
        break;
    case 'pengajuan/form': 
        checkRole(['Pengusul']);
        (new App\Controllers\PengajuanController($pdo))->form($_GET['id'] ?? 0);
        break;
    case 'pengajuan/submit':
        checkRole(['Pengusul']);
        (new App\Controllers\PengajuanController($pdo))->submit();
        break;

    case 'pengajuan/lpj': 
        checkRole(['Pengusul']);
        require_once __DIR__ . '/../app/Controllers/LpjController.php';
        (new App\Controllers\LpjController($pdo))->index();
        break;
        
    case 'lpj/upload/detail':
        checkRole(['Pengusul']);
        require_once __DIR__ . '/../app/Controllers/LpjController.php';
        (new App\Controllers\LpjController($pdo))->uploadDetail($_GET['id'] ?? 0);
        break;
        
    case 'lpj/upload/proses':
        checkRole(['Pengusul']);
        require_once __DIR__ . '/../app/Controllers/LpjController.php';
        (new App\Controllers\LpjController($pdo))->prosesUpload();
        break;
        
    case 'lpj/submit':
        checkRole(['Pengusul']);
        require_once __DIR__ . '/../app/Controllers/LpjController.php';
        (new App\Controllers\LpjController($pdo))->submit();
        break;
        
    case 'lpj/delete-dokumen':
        checkRole(['Pengusul']);
        require_once __DIR__ . '/../app/Controllers/LpjController.php';
        (new App\Controllers\LpjController($pdo))->deleteDokumen();
        break;

    // --- BENDAHARA (KEUANGAN) ---
    case 'pencairan': 
        checkRole(['Bendahara']);
        require_once __DIR__ . '/../app/Controllers/KeuanganController.php';
        (new App\Controllers\KeuanganController($pdo))->indexPencairan();
        break;
        
    case 'pencairan/proses':
        checkRole(['Bendahara']);
        require_once __DIR__ . '/../app/Controllers/KeuanganController.php';
        (new App\Controllers\KeuanganController($pdo))->prosesPencairan();
        break;
        
    case 'lpj': 
        checkRole(['Bendahara']);
        require_once __DIR__ . '/../app/Controllers/KeuanganController.php';
        (new App\Controllers\KeuanganController($pdo))->indexLpj();
        break;
        
    case 'lpj/detail': // Verifikasi Detail Bendahara
        checkRole(['Bendahara']);
        require_once __DIR__ . '/../app/Controllers/KeuanganController.php';
        (new App\Controllers\KeuanganController($pdo))->detailLpj($_GET['id'] ?? 0);
        break;
        
    case 'lpj/verifikasi': // Proses Verifikasi
        checkRole(['Bendahara']);
        require_once __DIR__ . '/../app/Controllers/KeuanganController.php';
        (new App\Controllers\KeuanganController($pdo))->verifikasiLpj();
        break;
        
    case 'lpj/riwayat': // Riwayat Disetujui
        checkRole(['Bendahara']);
        require_once __DIR__ . '/../app/Controllers/KeuanganController.php';
        (new App\Controllers\KeuanganController($pdo))->riwayatLpj();
        break;

    // --- PPK ---
    case 'pengajuan/ppk': 
        checkRole(['PPK']);
        (new App\Controllers\PengajuanController($pdo))->indexPPK();
        break;
    case 'pengajuan/ppk/detail':
        checkRole(['PPK']);
        (new App\Controllers\PengajuanController($pdo))->detailPPK($_GET['id'] ?? 0);
        break;
    case 'pengajuan/ppk/aksi':
        checkRole(['PPK']);
        (new App\Controllers\ApprovalController($pdo))->aksi($_GET['id'] ?? 0);
        break;
    case 'pengajuan/ppk/riwayat': 
        checkRole(['PPK']);
        (new App\Controllers\LaporanController($pdo))->riwayatApproval('PPK');
        break;

    // --- WD2 ---
    case 'pengajuan/wd2': 
        checkRole(['WD2']);
        (new App\Controllers\PengajuanController($pdo))->indexWD2();
        break;
    case 'pengajuan/wd2/detail':
        checkRole(['WD2']);
        (new App\Controllers\PengajuanController($pdo))->detailWD2($_GET['id'] ?? 0);
        break;
    case 'pengajuan/wd2/aksi':
        checkRole(['WD2']);
        (new App\Controllers\ApprovalController($pdo))->aksi($_GET['id'] ?? 0);
        break;
    case 'pengajuan/wd2/riwayat': 
        checkRole(['WD2']);
        (new App\Controllers\LaporanController($pdo))->riwayatApproval('WD2');
        break;

    // --- MONITORING & NOTIFIKASI ---
    case 'monitoring':
        (new App\Controllers\MonitoringController($pdo))->index();
        break;
    case 'monitoring/keterlambatan':
        checkRole(['Admin', 'Direktur', 'PPK', 'WD2']);
        (new App\Controllers\LpjController($pdo))->monitoringKeterlambatan();
        break;
    case 'notifikasi':
        (new App\Controllers\NotifikasiController($pdo))->index();
        break;
    case 'notifikasi/read':
        (new App\Controllers\NotifikasiController($pdo))->read($_GET['id'] ?? 0);
        break;
    
    // --- ADMIN / DIREKTUR ---
    case 'users':
        checkRole(['Admin', 'Direktur']);
        (new App\Controllers\AdminController($pdo))->users();
        break;
    case 'users/create':
        checkRole(['Admin']);
        (new App\Controllers\AdminController($pdo))->createUser();
        break;
    case 'users/update':
        checkRole(['Admin']);
        (new App\Controllers\AdminController($pdo))->updateUser();
        break;
    case 'users/toggle-status': 
        checkRole(['Admin']);
        (new App\Controllers\AdminController($pdo))->toggleUserStatus();
        break;

    // --- MASTER DATA ---
    case 'master':
        checkRole(['Admin', 'Direktur']);
        (new App\Controllers\AdminController($pdo))->indexMaster();
        break;
    case 'master/jurusan':
        checkRole(['Admin', 'Direktur']);
        (new App\Controllers\AdminController($pdo))->jurusan();
        break;
    case 'master/jurusan/store':
        checkRole(['Admin']);
        (new App\Controllers\AdminController($pdo))->storeJurusan();
        break;
    case 'master/jurusan/update':
        checkRole(['Admin']);
        (new App\Controllers\AdminController($pdo))->updateJurusan();
        break;
    case 'master/jurusan/toggle-status':
        checkRole(['Admin']);
        (new App\Controllers\AdminController($pdo))->toggleJurusanStatus();
        break;
    
    case 'master/iku':
        checkRole(['Admin', 'Direktur']);
        (new App\Controllers\AdminController($pdo))->iku();
        break;
    case 'master/iku/store':
        checkRole(['Admin']);
        (new App\Controllers\AdminController($pdo))->storeIku();
        break;
    case 'master/iku/update':
        checkRole(['Admin']);
        (new App\Controllers\AdminController($pdo))->updateIku();
        break;
    case 'master/iku/toggle-status':
        checkRole(['Admin']);
        (new App\Controllers\AdminController($pdo))->toggleIkuStatus();
        break;
        
    case 'master/satuan':
        checkRole(['Admin', 'Direktur']);
        (new App\Controllers\AdminController($pdo))->satuan();
        break;
    case 'master/satuan/store':
        checkRole(['Admin']);
        (new App\Controllers\AdminController($pdo))->storeSatuan();
        break;
    case 'master/satuan/update':
        checkRole(['Admin']);
        (new App\Controllers\AdminController($pdo))->updateSatuan();
        break;
    case 'master/satuan/toggle-status':
        checkRole(['Admin']);
        (new App\Controllers\AdminController($pdo))->toggleSatuanStatus();
        break;

    // --- ADMIN / DIREKTUR: SEMUA USULAN (BARU) ---
    case 'admin/usulan':
        checkRole(['Admin', 'Direktur']); 
        (new App\Controllers\AdminController($pdo))->allUsulan(); 
        break;

    // --- LAPORAN & AUDIT ---
    case 'laporan':
        checkRole(['Admin', 'Direktur']);
        (new App\Controllers\LaporanController($pdo))->index();
        break;
    
    // [MODIFIKASI] Membuka akses baca Audit Log untuk Direktur
    case 'audit-log':
        checkRole(['Admin', 'Direktur']); // Direktur bisa akses
        (new App\Controllers\AuditLogController($pdo))->index();
        break;
    case 'audit-log/export':
        checkRole(['Admin', 'Direktur']); // Direktur bisa export
        (new App\Controllers\AuditLogController($pdo))->export();
        break;
        
    case 'audit-log/delete-all':
        checkRole(['Admin']); // HANYA ADMIN YANG BISA HAPUS
        (new App\Controllers\AdminController($pdo))->clearAuditLogs();
        break;
    
    // --- PROFIL ---
    case 'profil':
        (new App\Controllers\PageController($pdo))->profil();
        break;
    case 'profil/update-data':
        (new App\Controllers\PageController($pdo))->updateProfile();
        break;
    case 'profil/update-password':
        (new App\Controllers\PageController($pdo))->updatePassword();
        break;

    // --- PDF GENERATOR ---
    case 'pdf/kak':
        (new App\Controllers\PdfController($pdo))->kak($_GET['id'] ?? 0);
        break;
    case 'pdf/rab':
        (new App\Controllers\PdfController($pdo))->rab($_GET['id'] ?? 0);
        break;
    case 'pdf/berita_acara':
        (new App\Controllers\PdfController($pdo))->beritaAcara($_GET['id'] ?? 0);
        break;
        
    // --- DEFAULT: 404 Not Found ---
    default:
        http_response_code(404);
        require __DIR__ . '/../app/Views/errors/404.php';
        break;
}

// ========================================
// END: AGGRESSIVE GLOBAL OUTPUT BUFFERING
// Pastikan output yang valid terkirim di akhir eksekusi non-redirect/non-image
// ========================================
if (ob_get_level() > 0) ob_end_flush();