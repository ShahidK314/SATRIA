<?php
// public/index.php - THE ELITE ROUTER v2.0

// 1. Load Autoloader (Composer + Manual Fallback)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Manual Autoloader (Jaga-jaga jika Composer error/belum dump-autoload)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

// 2. Environment & Session Setup
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
} catch (Exception $e) { /* Ignore if .env missing */ }

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('SATRIA_SESSION');
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. Global Error Handler (Mencegah White Screen of Death)
set_exception_handler(function ($e) {
    error_log("[CRITICAL] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);
    require __DIR__ . '/../app/Views/errors/500.php';
    exit;
});

// 4. Database Connection
$dbConfig = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
$db = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

// 5. Sanitizer & Helper
function recursiveSanitize($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) $data[$key] = recursiveSanitize($value);
        return $data;
    }
    if (is_null($data)) return '';
    return htmlspecialchars(strip_tags(trim($data)));
}

// Bersihkan Input
$_GET  = recursiveSanitize($_GET);
$_POST = recursiveSanitize($_POST);

// Helper: Ambil ID dari GET atau POST (Prioritas Keamanan)
function getId() {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) return (int)$_GET['id'];
    if (isset($_POST['id']) && is_numeric($_POST['id'])) return (int)$_POST['id'];
    return 0;
}

// 6. Routing System
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Import Controllers
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\MonitoringController;
use App\Controllers\UsulanController;
use App\Controllers\VerifikasiController;
use App\Controllers\ApprovalController;
use App\Controllers\NotifikasiController;
use App\Controllers\AdminController;
use App\Controllers\KeuanganController;
use App\Controllers\LaporanController;
use App\Controllers\PdfController;
use App\Controllers\PageController;

// --- ROUTES ---

if ($uri === '/' || $uri === '/index.php') {
    require __DIR__ . '/../app/Views/welcome.php';

} elseif ($uri === '/login') {
    $auth = new AuthController($db);
    if ($method === 'POST') $auth->login(); else $auth->showLogin();

} elseif ($uri === '/logout') {
    $auth = new AuthController($db);
    $auth->logout();

// DASHBOARD
} elseif ($uri === '/dashboard') {
    $ctl = new DashboardController($db); $ctl->index();
} elseif ($uri === '/monitoring') {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $ctl = new MonitoringController($db); $ctl->index($page, 10);

// NOTIFIKASI
} elseif ($uri === '/notifikasi') {
    $ctl = new NotifikasiController($db); $ctl->index();
} elseif ($uri === '/notifikasi/read') {
    $ctl = new NotifikasiController($db); $ctl->read(getId());

// USULAN (PENGUSUL)
} elseif ($uri === '/usulan/create') {
    $ctl = new UsulanController($db); $ctl->create();
} elseif ($uri === '/usulan/edit') {
    $ctl = new UsulanController($db); $ctl->edit(getId());
} elseif ($uri === '/usulan/update') {
    $ctl = new UsulanController($db); $ctl->update(getId());
} elseif ($uri === '/usulan/delete') {
    $ctl = new UsulanController($db); $ctl->delete(getId());
} elseif ($uri === '/usulan/ajukan') {
    $ctl = new UsulanController($db); $ctl->ajukan(getId());
} elseif ($uri === '/usulan/detail') {
    $ctl = new UsulanController($db); $ctl->detail(getId());
} elseif ($uri === '/usulan/ajukan') {
    $ctl = new UsulanController($db); $ctl->ajukan(getId());
// [BARU] Route untuk Skenario 2
} elseif ($uri === '/usulan/lengkapi') {
    $ctl = new UsulanController($db); $ctl->lengkapi(getId());
} elseif ($uri === '/usulan/proses-lengkapi') {
    $ctl = new UsulanController($db); $ctl->prosesLengkapi(getId());

// VERIFIKASI
} elseif ($uri === '/verifikasi') {
    $ctl = new VerifikasiController($db); $ctl->index();
} elseif ($uri === '/verifikasi/proses') {
    $ctl = new VerifikasiController($db); $ctl->proses(getId());
} elseif ($uri === '/verifikasi/aksi') {
    $ctl = new VerifikasiController($db); $ctl->aksi(getId());

// APPROVAL
} elseif ($uri === '/approval') {
    $ctl = new ApprovalController($db); $ctl->index();
} elseif ($uri === '/approval/proses') {
    $ctl = new ApprovalController($db); $ctl->proses(getId());
} elseif ($uri === '/approval/aksi') {
    $ctl = new ApprovalController($db); $ctl->aksi(getId());

// KEUANGAN
} elseif ($uri === '/pencairan') {
    $ctl = new KeuanganController($db); $ctl->indexPencairan();
} elseif ($uri === '/pencairan/proses') {
    $ctl = new KeuanganController($db); $ctl->prosesPencairan(getId());
} elseif ($uri === '/lpj') {
    $ctl = new KeuanganController($db); $ctl->indexLPJ();
} elseif ($uri === '/lpj/verifikasi') {
    $ctl = new KeuanganController($db); $ctl->verifikasiLPJ(getId());

// ADMIN & MASTER
} elseif ($uri === '/users') {
    $ctl = new AdminController($db); $ctl->users();
} elseif ($uri === '/users/create') {
    $ctl = new AdminController($db); $ctl->createUser();
} elseif ($uri === '/users/update') {
    $ctl = new AdminController($db); $ctl->updateUser();
} elseif ($uri === '/users/delete') {
    $ctl = new AdminController($db); $ctl->deleteUser();
} elseif ($uri === '/master') {
    $ctl = new AdminController($db); $ctl->indexMaster();
} elseif ($uri === '/master/jurusan') {
    $ctl = new AdminController($db); $ctl->jurusan();
} elseif ($uri === '/master/jurusan/store') {
    $ctl = new AdminController($db); $ctl->storeJurusan();
} elseif ($uri === '/master/jurusan/update') {
    $ctl = new AdminController($db); $ctl->updateJurusan();
} elseif ($uri === '/master/jurusan/delete') {
    $ctl = new AdminController($db); $ctl->deleteJurusan();
} elseif ($uri === '/master/iku') {
    $ctl = new AdminController($db); $ctl->iku();
} elseif ($uri === '/master/iku/store') {
    $ctl = new AdminController($db); $ctl->storeIku();
} elseif ($uri === '/master/iku/update') {
    $ctl = new AdminController($db); $ctl->updateIku();
} elseif ($uri === '/master/iku/toggle-status') {
    $ctl = new AdminController($db); $ctl->toggleIkuStatus();
} elseif ($uri === '/master/iku/delete') {
    $ctl = new AdminController($db); $ctl->deleteIku();
} elseif ($uri === '/audit-log') {
    $ctl = new \App\Controllers\AuditLogController($db); $ctl->index();
} elseif ($uri === '/audit-log/export') {
    $ctl = new \App\Controllers\AuditLogController($db); $ctl->export();
} elseif ($uri === '/laporan') {
    $ctl = new LaporanController($db); $ctl->index();

// PDF & PAGES
} elseif (strpos($uri, '/pdf/') === 0) {
    $ctl = new PdfController($db);
    $id = getId();
    if ($uri === '/pdf/kak') $ctl->kak($id);
    elseif ($uri === '/pdf/rab') $ctl->rab($id);
    elseif ($uri === '/pdf/surat_teguran') $ctl->suratTeguran($id);
    elseif ($uri === '/pdf/berita_acara') $ctl->beritaAcara($id);

} elseif ($uri === '/profil') {
    $ctl = new PageController($db); $ctl->profil();
} elseif ($uri === '/profil/update-data') {
    $ctl = new PageController($db); $ctl->updateProfile();
} elseif ($uri === '/profil/update-password') {
    $ctl = new PageController($db); $ctl->updatePassword();
} elseif ($uri === '/bantuan') {
    $ctl = new PageController($db); $ctl->bantuan();
} elseif ($uri === '/syarat') {
    $ctl = new PageController($db); $ctl->syarat();

} else {
    http_response_code(404);
    require __DIR__ . '/../app/Views/errors/404.php';
}