<?php
// app/Controllers/PengajuanController.php
namespace App\Controllers;

use App\Models\UsulanModel;
use App\Models\PengajuanModel;
use PDO;

class PengajuanController
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
    
    private function updateUsulanStatus($usulanId, $status) {
        $stmt = $this->db->prepare("UPDATE usulan_kegiatan SET status_terkini=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$status, $usulanId]);
    }

    // --- Pengusul Flow (Form and Submit) ---

    public function form($usulanId)
    {
        $this->ensureLogin();
        $usulanModel = new UsulanModel($this->db);
        $usulan = $usulanModel->findById($usulanId);
        if (!$usulan || $usulan['user_id'] != $_SESSION['user_id'] || $usulan['status_terkini'] !== 'Disetujui') {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Akses ditolak atau status tidak valid.'];
            header('Location: /monitoring'); exit;
        }

        $stmt = $this->db->prepare("SELECT id, status_ppk, status_wd2 FROM pengajuan_kegiatan WHERE usulan_id = ?");
        $stmt->execute([$usulanId]);
        $pengajuanExist = $stmt->fetch();
        
        if ($pengajuanExist) {
            $currentStatus = $pengajuanExist['status_ppk'] . '/' . $pengajuanExist['status_wd2'];
            if (in_array($currentStatus, ['Menunggu/Menunggu', 'Disetujui/Menunggu', 'Disetujui/Disetujui'])) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Pengajuan sudah dalam proses approval.'];
            } else {
                 $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Pengajuan sudah pernah dibuat sebelumnya.'];
            }
            header('Location: /pengajuan/kegiatan');
            exit;
        }
        require __DIR__ . '/../Views/pengajuan/form.php';
    }

    public function submit() {
        $this->ensureLogin();
        
        $usulanId = filter_var($_POST['usulan_id'], FILTER_VALIDATE_INT);
        
        if (isset($_FILES['surat_pengantar']) && $_FILES['surat_pengantar']['error'] === UPLOAD_ERR_INI_SIZE) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'File terlalu besar untuk diproses server.'];
            header("Location: /pengajuan/form?id=$usulanId"); exit;
        }

        $maxSize = 40 * 1024 * 1024; 
        if ($_FILES['surat_pengantar']['size'] > $maxSize) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Ukuran file melebihi batas 40MB.'];
            header("Location: /pengajuan/form?id=$usulanId"); exit;
        }

        $pj = trim($_POST['penanggung_jawab']);
        $pelaksana = trim($_POST['pelaksana_kegiatan']);
        $tglMulai = $_POST['tanggal_mulai'];
        $tglSelesai = $_POST['tanggal_selesai'];
        
        $usulanModel = new UsulanModel($this->db);
        $usulan = $usulanModel->findById($usulanId);

        if (!$usulan || $usulan['user_id'] != $_SESSION['user_id'] || $usulan['status_terkini'] !== 'Disetujui') {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Akses ditolak.'];
            header('Location: /pengajuan/kegiatan'); exit;
        }

        if ($tglMulai < $usulan['tanggal_mulai'] || $tglSelesai > $usulan['tanggal_selesai']) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Tanggal pelaksanaan di luar rentang usulan.'];
            header("Location: /pengajuan/form?id=$usulanId"); exit;
        }

        $file = $_FILES['surat_pengantar'];
        $uploadDir = __DIR__ . '/../../public/uploads/surat/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $filename = "SURAT_{$usulanId}_" . time() . ".pdf";
        $filePath = "/uploads/surat/$filename";
        move_uploaded_file($file['tmp_name'], $uploadDir . $filename);

        try {
            $this->db->beginTransaction();
            $pengajuanModel = new PengajuanModel($this->db);
            $pengajuanId = $pengajuanModel->create([
                'usulan_id' => $usulanId,
                'penanggung_jawab' => $pj,
                'pelaksana_kegiatan' => $pelaksana,
                'tanggal_mulai' => $tglMulai,
                'tanggal_selesai' => $tglSelesai,
                'surat_pengantar_path' => $filePath
            ]);
            
            $usulanModel->addLog($usulanId, $_SESSION['user_id'], 'Disetujui', 'Menunggu PPK', 'Diajukan ke PPK', 'pengajuan', $pengajuanId);
            $this->notifyPPK();
            $this->db->commit();
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Pengajuan berhasil dikirim.'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal submit.'];
        }

        header('Location: /monitoring');
        exit;
    }


    // --- PPK Approval Flow ---

    public function indexPPK()
    {
        $this->ensureLogin();
        if ($_SESSION['role'] !== 'PPK') { header('Location: /dashboard'); exit; }
        
        $pengajuanModel = new PengajuanModel($this->db);
        $pengajuan = $pengajuanModel->getByStatus('Menunggu PPK');

        $role = 'PPK';
        require __DIR__ . '/../Views/pengajuan/ppk_index.php';
    }
    
    public function detailPPK($id)
    {
        $this->ensureLogin();
        if ($_SESSION['role'] !== 'PPK') { header('Location: /dashboard'); exit; }
        
        $pengajuanModel = new PengajuanModel($this->db);
        $usulanModel = new UsulanModel($this->db);
        
        $pengajuan = $pengajuanModel->findById($id);

        if (!$pengajuan || $pengajuan['status_ppk'] !== 'Menunggu') {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Dokumen tidak tersedia/akses ditolak.'];
            header('Location: /pengajuan/ppk'); exit;
        }

        $usulan = $usulanModel->findById($pengajuan['usulan_id']); 
        $rabData = $usulanModel->getRABDetails($pengajuan['usulan_id']); 
        $ikuDetails = $usulanModel->getIKUDetails($pengajuan['usulan_id']); 

        $usulan['indikator_array'] = $usulan['indikator_kinerja'] ? json_decode($usulan['indikator_kinerja'], true) : [];
        $usulan['metode_array'] = $usulan['metode_pelaksanaan'] ? json_decode($usulan['metode_pelaksanaan'], true) : [];
        $usulan['tahapan_array'] = $usulan['tahapan_pelaksanaan'] ? json_decode($usulan['tahapan_pelaksanaan'], true) : [];
        
        $role = 'PPK'; 
        require __DIR__ . '/../Views/pengajuan/ppk_detail.php';
    }
    
    public function riwayatPPK()
    {
        $this->ensureLogin();
        if ($_SESSION['role'] !== 'PPK') { header('Location: /dashboard'); exit; }
        
        $pengajuanModel = new PengajuanModel($this->db);

        // Ambil Parameter Filter & Pagination
        $search = $_GET['q'] ?? '';
        $filterProposer = $_GET['pengusul'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;

        // Ambil Daftar Pengusul untuk Dropdown
        $stmtPengusul = $this->db->query("SELECT id, username, nama FROM users WHERE role = 'Pengusul' ORDER BY nama ASC");
        $listPengusul = $stmtPengusul->fetchAll(PDO::FETCH_ASSOC);

        // Ambil Semua Riwayat
        $raw_riwayat = $pengajuanModel->getRiwayatByStatusPPK(); 

        // Filter Data secara manual
        $processedData = [];
        foreach ($raw_riwayat as $row) {
            if (!empty($search) && stripos($row['nama_kegiatan'], $search) === false) continue;
            if (!empty($filterProposer) && $row['username'] !== $filterProposer) continue;
            $processedData[] = $row;
        }

        // Pagination Logic
        $totalItems = count($processedData);
        $totalPages = ceil($totalItems / $limit);
        $page = max(1, min($page, $totalPages > 0 ? $totalPages : 1));
        $offset = ($page - 1) * $limit;
        
        $riwayat = array_slice($processedData, $offset, $limit);
        
        $pager = [
            'current' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems
        ];

        $role = 'PPK';
        require __DIR__ . '/../Views/pengajuan/riwayat_approval.php';
    }


    // --- WD2 Approval Flow ---

    public function indexWD2()
    {
        $this->ensureLogin();
        if ($_SESSION['role'] !== 'WD2') { header('Location: /dashboard'); exit; }
        
        $pengajuanModel = new PengajuanModel($this->db);
        $pengajuan = $pengajuanModel->getByStatus('Menunggu WD2');
        $stats = (new \App\Models\StatsModel($this->db))->getGlobalDashboardStats();

        $role = 'WD2'; 
        require __DIR__ . '/../Views/pengajuan/wd2_index.php';
    }
    
    public function detailWD2($id)
    {
        $this->ensureLogin();
        if ($_SESSION['role'] !== 'WD2') { header('Location: /dashboard'); exit; }
        
        $pengajuanModel = new PengajuanModel($this->db);
        $usulanModel = new UsulanModel($this->db);
        
        $pengajuan = $pengajuanModel->findById($id);

        if (!$pengajuan || $pengajuan['status_ppk'] !== 'Disetujui' || $pengajuan['status_wd2'] !== 'Menunggu') {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Dokumen tidak tersedia/akses ditolak.'];
            header('Location: /pengajuan/wd2'); exit;
        }

        $usulan = $usulanModel->findById($pengajuan['usulan_id']); 
        $rabData = $usulanModel->getRABDetails($pengajuan['usulan_id']); 
        $ikuDetails = $usulanModel->getIKUDetails($pengajuan['usulan_id']); 

        $usulan['indikator_array'] = $usulan['indikator_kinerja'] ? json_decode($usulan['indikator_kinerja'], true) : [];
        $usulan['metode_array'] = $usulan['metode_pelaksanaan'] ? json_decode($usulan['metode_pelaksanaan'], true) : [];
        $usulan['tahapan_array'] = $usulan['tahapan_pelaksanaan'] ? json_decode($usulan['tahapan_pelaksanaan'], true) : [];
        
        $logPPK = [
            'catatan' => $pengajuan['rekomendasi_ppk'],
            'timestamp' => $pengajuan['tgl_status_ppk']
        ];
        
        $role = 'WD2'; 
        require __DIR__ . '/../Views/pengajuan/wd2_detail.php';
    }

    public function riwayatWD2()
    {
        $this->ensureLogin();
        if ($_SESSION['role'] !== 'WD2') { header('Location: /dashboard'); exit; }
        
        $pengajuanModel = new PengajuanModel($this->db);

        // Ambil Parameter Filter & Pagination
        $search = $_GET['q'] ?? '';
        $filterProposer = $_GET['pengusul'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;

        // Ambil Daftar Pengusul untuk Dropdown
        $stmtPengusul = $this->db->query("SELECT id, username, nama FROM users WHERE role = 'Pengusul' ORDER BY nama ASC");
        $listPengusul = $stmtPengusul->fetchAll(PDO::FETCH_ASSOC);

        // Ambil Semua Riwayat
        $raw_riwayat = $pengajuanModel->getRiwayatByStatusWD2(); 

        // Filter Data secara manual
        $processedData = [];
        foreach ($raw_riwayat as $row) {
            if (!empty($search) && stripos($row['nama_kegiatan'], $search) === false) continue;
            if (!empty($filterProposer) && $row['username'] !== $filterProposer) continue;
            $processedData[] = $row;
        }

        // Pagination Logic
        $totalItems = count($processedData);
        $totalPages = ceil($totalItems / $limit);
        $page = max(1, min($page, $totalPages > 0 ? $totalPages : 1));
        $offset = ($page - 1) * $limit;
        
        $riwayat = array_slice($processedData, $offset, $limit);
        
        $pager = [
            'current' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems
        ];

        $role = 'WD2';
        require __DIR__ . '/../Views/pengajuan/riwayat_approval.php';
    }

    private function notifyPPK()
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'PPK' LIMIT 1");
        $stmt->execute();
        $ppk = $stmt->fetch();

        if ($ppk) {
            $this->db->prepare("
                INSERT INTO notifikasi (user_id, judul, pesan, link, created_at) 
                VALUES (?, 'Pengajuan Kegiatan Baru', 'Ada pengajuan kegiatan baru yang perlu disetujui PPK', '/pengajuan/ppk', NOW())
            ")->execute([$ppk['id']]);
        }
    }
}   