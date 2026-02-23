<?php
// app/Controllers/LpjController.php
namespace App\Controllers;

use App\Models\LpjModel;
use PDO;

class LpjController
{
    private $db;

    public function __construct($db) { $this->db = $db; }

    private function ensureLogin() {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }
    }

    // --- METHOD UPDATE: Monitoring Keterlambatan ---
    public function monitoringKeterlambatan() {
        $this->ensureLogin();

        // 1. Ambil Parameter Filter (Status DIHAPUS)
        $search = $_GET['q'] ?? '';
        $filterPengusul = $_GET['pengusul'] ?? ''; 
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;

        // 2. Ambil Daftar Pengusul untuk Dropdown Filter
        $stmtPengusul = $this->db->prepare("SELECT id, username, nama FROM users WHERE role = 'Pengusul' ORDER BY username ASC");
        $stmtPengusul->execute();
        $listPengusul = $stmtPengusul->fetchAll(PDO::FETCH_ASSOC);

        // 3. Query Dasar
        $sql = "
            SELECT 
                l.id,
                u.nama_kegiatan as judul_usulan,
                us.username as nama_pengusul,
                us.nama as nama_lengkap_pengusul,
                us.id as pengusul_id,
                p.created_at as tanggal_pengajuan,
                pc.tanggal_batas_lpj as tanggal_deadline,
                l.status_terkini,
                l.tanggal_submit
            FROM lpj l
            JOIN pencairan_dana pc ON l.pencairan_id = pc.id
            JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id
            JOIN usulan_kegiatan u ON p.usulan_id = u.id
            JOIN users us ON u.user_id = us.id
            WHERE 
                (pc.tanggal_batas_lpj < CURDATE() AND l.status_terkini != 'Disetujui')
                OR 
                (l.tanggal_submit IS NOT NULL AND l.tanggal_submit > pc.tanggal_batas_lpj)
            ORDER BY pc.tanggal_batas_lpj ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $raw_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 4. Processing & Filtering
        $processedData = [];
        $total_hari_terlambat = 0;
        
        foreach ($raw_data as $row) {
            $deadline = new \DateTime($row['tanggal_deadline']);
            $now = new \DateTime(); 
            $days = 0;
            
            if (!empty($row['tanggal_submit'])) {
                $submitDate = new \DateTime($row['tanggal_submit']);
                if ($submitDate > $deadline) {
                    $days = $deadline->diff($submitDate)->days;
                }
            } else {
                if ($now > $deadline) {
                    $days = $deadline->diff($now)->days;
                }
            }
            
            if ($days > 0) {
                // Mapping Status
                $displayStatus = match ($row['status_terkini']) {
                    'Diajukan' => 'Submitted',
                    'Revisi' => 'Rejected',
                    'Disetujui' => 'Verified',
                    default => 'Draft',
                };

                // --- LOGIKA FILTER ---
                
                // 1. Filter Search
                if (!empty($search) && stripos($row['judul_usulan'], $search) === false) {
                    continue; 
                }

                // 2. Filter Pengusul
                if (!empty($filterPengusul) && $row['pengusul_id'] != $filterPengusul) {
                    continue;
                }

                $processedData[] = [
                    'judul_usulan' => $row['judul_usulan'],
                    'nama_pengusul' => $row['nama_pengusul'], 
                    'tanggal_pengajuan' => $row['tanggal_pengajuan'],
                    'tanggal_deadline' => $row['tanggal_deadline'],
                    'hari_keterlambatan' => $days,
                    'status_lpj' => $displayStatus
                ];
                $total_hari_terlambat += $days;
            }
        }
        
        // 5. Statistik
        $stmtTotal = $this->db->query("SELECT COUNT(*) FROM lpj");
        $total_lpj = $stmtTotal->fetchColumn();
        
        $total_terlambat_count = count($processedData);
        $rata_rata = $total_terlambat_count > 0 ? round($total_hari_terlambat / $total_terlambat_count) : 0;
        
        $statistik = [
            'total_lpj' => $total_lpj,
            'total_terlambat' => $total_terlambat_count,
            'rata_rata_keterlambatan' => $rata_rata
        ];

        // 6. Pagination
        $totalItems = count($processedData);
        $totalPages = ceil($totalItems / $limit);
        $page = max(1, min($page, $totalPages > 0 ? $totalPages : 1));
        $offset = ($page - 1) * $limit;
        
        $keterlambatan_lpj = array_slice($processedData, $offset, $limit);
        
        $pager = [
            'current' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'limit' => $limit
        ];

        require __DIR__ . '/../Views/monitoring/keterlambatan.php';
    }

    public function index() {
        $this->ensureLogin();
        $userId = $_SESSION['user_id'];
        
        // Auto Insert LPJ (Draft) jika belum ada
        $sqlInsert = "
            INSERT INTO lpj (pencairan_id, status_terkini, created_at) 
            SELECT pc.id, 'Belum Upload', NOW() 
            FROM pencairan_dana pc 
            JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id 
            JOIN usulan_kegiatan u ON p.usulan_id = u.id 
            WHERE u.user_id = ? 
            AND NOT EXISTS (
                SELECT 1 
                FROM lpj l2 
                JOIN pencairan_dana pc2 ON l2.pencairan_id = pc2.id 
                WHERE pc2.pengajuan_id = p.id
            ) 
            GROUP BY p.id
        "; 
        $this->db->prepare($sqlInsert)->execute([$userId]);

        // Get List Kegiatan LPJ
        $stmt = $this->db->prepare("
            SELECT 
                MAX(l.id) as id, 
                u.nama_kegiatan, 
                MAX(l.status_terkini) as status_terkini, 
                (SELECT MIN(tanggal_batas_lpj) 
                 FROM pencairan_dana 
                 WHERE pengajuan_id = p.id) as tanggal_batas_lpj, 
                (SELECT SUM(nominal_dicairkan) 
                 FROM pencairan_dana 
                 WHERE pengajuan_id = p.id) as nominal_dicairkan 
            FROM lpj l 
            JOIN pencairan_dana pc ON l.pencairan_id = pc.id 
            JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id 
            JOIN usulan_kegiatan u ON p.usulan_id = u.id 
            WHERE u.user_id = ? 
            AND l.status_terkini IN ('Belum Upload', 'Revisi', 'Terlambat') 
            GROUP BY p.id, u.nama_kegiatan 
            ORDER BY MAX(l.created_at) DESC
        ");
        $stmt->execute([$userId]);
        $lpjList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/lpj/upload_form.php'; 
    }

    public function uploadDetail($urlId = null) {
        $this->ensureLogin();
        $lpjId = $urlId ?? $_GET['id'] ?? 0;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['is_ajax'])) {
            $this->handleAjaxRequest($lpjId); exit; 
        }

        $lpjModel = new LpjModel($this->db);
        
        $stmt = $this->db->prepare("
            SELECT 
                l.*, 
                p.id as pengajuan_id, 
                u.nama_kegiatan, 
                u.id as usulan_id, 
                u.user_id 
            FROM lpj l 
            JOIN pencairan_dana pc ON l.pencairan_id = pc.id 
            JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id 
            JOIN usulan_kegiatan u ON p.usulan_id = u.id 
            WHERE l.id = ?
        ");
        $stmt->execute([$lpjId]);
        $lpj = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$lpj || $lpj['user_id'] != $_SESSION['user_id']) { header('Location: /pengajuan/lpj'); exit; }
        
        $usulanId = $lpj['usulan_id'];
        $pengajuanId = $lpj['pengajuan_id'];

        $stmtCair = $this->db->prepare("
            SELECT SUM(nominal_dicairkan) 
            FROM pencairan_dana 
            WHERE pengajuan_id = ?
        ");
        $stmtCair->execute([$pengajuanId]);
        $lpj['nominal_dicairkan_akumulasi'] = (float) $stmtCair->fetchColumn();
        
        $stmtDate = $this->db->prepare("
            SELECT MIN(tanggal_batas_lpj) 
            FROM pencairan_dana 
            WHERE pengajuan_id = ?
        ");
        $stmtDate->execute([$pengajuanId]);
        $lpj['tanggal_batas_lpj'] = $stmtDate->fetchColumn();

        $stmtRab = $this->db->prepare("
            SELECT 
                r.*, 
                s_main.nama_satuan as satuan_master, 
                r.satuan_custom,
                s1.nama_satuan as satuan_master_1, 
                r.satuan_factor_1_custom,
                s2.nama_satuan as satuan_master_2,
                r.satuan_factor_2_custom
            FROM rab_detail r 
            LEFT JOIN master_satuan_anggaran s_main ON r.satuan_id = s_main.id 
            LEFT JOIN master_satuan_anggaran s1 ON r.satuan_factor_1_id = s1.id 
            LEFT JOIN master_satuan_anggaran s2 ON r.satuan_factor_2_id = s2.id 
            WHERE r.usulan_id = ? 
            ORDER BY r.kategori_id ASC, r.id ASC
        ");
        $stmtRab->execute([$usulanId]);
        $rabDetails = $stmtRab->fetchAll(PDO::FETCH_ASSOC);

        $rabItems = $this->mergeRabWithData($rabDetails, $lpjId);
        
        require __DIR__ . '/../Views/lpj/upload_detail.php';
    }

    public function prosesUpload() {
        $this->ensureLogin();
        if (empty($_POST['action'])) {
            $_POST['action'] = 'save';
        }
        $lpjId = $_POST['lpj_id'] ?? 0;
        $this->handleAjaxRequest($lpjId);
    }

    public function deleteDokumen() {
        $this->ensureLogin();
        if (empty($_POST['action'])) {
            $_POST['action'] = 'delete';
        }
        $this->handleAjaxRequest(0);
    }

    private function handleAjaxRequest($lpjId) {
        header('Content-Type: application/json');
        try {
            $lpjModel = new LpjModel($this->db);
            $action = $_POST['action'] ?? '';

            if ($action === 'save') {
                $rabId = $_POST['rab_id'];
                $kategoriId = $_POST['kategori_id'];
                $nominal = (float) str_replace(['Rp', '.', ',', ' '], '', $_POST['nominal']);
                $keterangan = $_POST['keterangan'] ?? '';

                // 1. Update Nominal dan Keterangan pada row utama
                $lpjModel->updateNominalItem($lpjId, $rabId, $kategoriId, $nominal, $keterangan);
                
                $uploadedFiles = [];
                
                // Ambil deskripsi item untuk nama dasar
                $stmtItem = $this->db->prepare("SELECT deskripsi FROM rab_detail WHERE id = ?");
                $stmtItem->execute([$rabId]);
                $baseItemName = $stmtItem->fetchColumn() ?: 'Item';

                // 2. Proses Multi-upload
                if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
                    $fileCount = count($_FILES['files']['name']);
                    $uploadDir = __DIR__ . '/../../public/uploads/lpj/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    
                    for($i = 0; $i < $fileCount; $i++) {
                        if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) continue;

                        $tmpName = $_FILES['files']['tmp_name'][$i];
                        $orgName = $_FILES['files']['name'][$i];
                        $ext = strtolower(pathinfo($orgName, PATHINFO_EXTENSION));
                        $newFileName = "LPJ_{$lpjId}_ITEM_{$rabId}_" . time() . "_" . uniqid() . "." . $ext;

                        if (move_uploaded_file($tmpName, $uploadDir . $newFileName)) {
                            $newWebPath = "/uploads/lpj/" . $newFileName;
                            
                            // Simpan file ke database
                            $newId = $lpjModel->uploadOrUpdateDokumen([
                                'lpj_id' => $lpjId, 
                                'rab_detail_id' => $rabId, 
                                'kategori_id' => $kategoriId,
                                'file_path' => $newWebPath, 
                                'keterangan' => '' 
                            ]);

                            // --- LOGIKA PENAMAAN DINAMIS ---
                            // Hitung jumlah file yang sudah memiliki path (aktif) untuk item ini
                            $stmtCount = $this->db->prepare("
                                SELECT COUNT(*) FROM lpj_dokumen 
                                WHERE lpj_id = ? AND rab_detail_id = ? AND file_path IS NOT NULL AND file_path != ''
                            ");
                            $stmtCount->execute([$lpjId, $rabId]);
                            $count = (int)$stmtCount->fetchColumn();

                            // Jika file pertama (count = 1), jangan beri angka. 
                            // Jika file kedua dan seterusnya, beri angka (2), (3), dst.
                            $displayName = ($count <= 1) ? $baseItemName : $baseItemName . " ({$count})";
                            
                            $uploadedFiles[] = [
                                'id' => $newId, 
                                'name' => $displayName, 
                                'path' => $newWebPath
                            ];
                        }
                    }
                }
                echo json_encode(['status' => 'success', 'uploaded_files' => $uploadedFiles]);
            }
            elseif ($action === 'delete') {
                $lpjModel->deletePreserveNominal($_POST['doc_id']);
                echo json_encode(['status' => 'success']);
            }
        } catch (\Exception $e) { 
            echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]); 
        }
    }

        private function mergeRabWithData($items, $lpjId) {
            $lpjModel = new LpjModel($this->db);
            $documents = $lpjModel->getDokumen($lpjId); 
            $stmtKat = $this->db->query("SELECT * FROM master_kategori_anggaran")->fetchAll(PDO::FETCH_KEY_PAIR);

            foreach ($items as &$item) {
                $item['rab_id'] = $item['id']; 
                $item['nominal_rab'] = $item['total']; 
                $item['nama_kategori'] = $stmtKat[$item['kategori_id']] ?? '-';
                
                // --- PERBAIKAN LOGIKA SATUAN CUSTOM ---
                // Mengambil satuan 1 (Master atau Custom)
                $item['nama_satuan_1'] = !empty($item['satuan_factor_1_custom']) 
                                        ? $item['satuan_factor_1_custom'] 
                                        : ($item['satuan_master_1'] ?? '-');

                // Mengambil satuan 2 (Master atau Custom)
                $item['nama_satuan_2'] = !empty($item['satuan_factor_2_custom']) 
                                        ? $item['satuan_factor_2_custom'] 
                                        : ($item['satuan_master_2'] ?? '-');
                // --------------------------------------

                $item['uploaded_files'] = [];
                $myFiles = [];
                foreach ($documents as $doc) {
                    if ($doc['rab_detail_id'] == $item['id']) { $myFiles[] = $doc; }
                }
            usort($myFiles, function($a, $b) { return $a['id'] <=> $b['id']; });

            $validFileCount = 0;
            foreach ($myFiles as $doc) if (!empty($doc['file_path'])) $validFileCount++;

            $currentCounter = 1;
            foreach ($myFiles as $doc) {
                 $displayName = $item['deskripsi'];
                 if (!empty($doc['file_path'])) {
                     if ($validFileCount > 1) $displayName .= ' (' . $currentCounter . ')';
                     $currentCounter++;
                 }
                 $item['uploaded_files'][] = ['id' => $doc['id'], 'file_path' => $doc['file_path'], 'name' => $displayName];
            }
            
            $savedData = $lpjModel->getItemRealisasi($lpjId, $item['id']);
            $item['total_realisasi'] = $savedData['nominal_realisasi']; 
            $item['keterangan'] = $savedData['keterangan'];             
        }
        return $items;
    }

    public function submit() {
        $this->ensureLogin();
        if (!isset($_POST['csrf_token'])) die('CSRF Error');
        
        $lpjId = $_POST['lpj_id'];
        $lpjModel = new LpjModel($this->db);
        
        // 1. Ambil data usulan terkait untuk mendapatkan usulan_id
        $lpjData = $lpjModel->getLpjUsulanData($lpjId);
        $usulanId = $lpjData['usulan_id'];

        // 2. VALIDASI 1: Cek apakah ada item yang nominalnya diisi tapi filenya kosong
        $missingItems = $lpjModel->checkMissingEvidence($lpjId);
        if (!empty($missingItems)) {
            $_SESSION['toast'] = [
                'type' => 'error', 
                'msg' => 'GAGAL SUBMIT: Terdapat item realisasi tanpa bukti upload.'
            ];
            header("Location: /lpj/upload/detail?id=$lpjId");
            exit;
        }

        // 3. VALIDASI 2: Cek kesesuaian nominal Realisasi vs RAB per kategori
        // Fungsi ini mengecek apakah total per kategori di LPJ sudah sama dengan RAB
        $errors = $lpjModel->validateKelengkapan($lpjId, $usulanId);
        
        if (!empty($errors)) {
            // Gabungkan semua pesan error kategori menjadi satu string
            $errorMsg = "GAGAL SUBMIT: " . implode(" | ", $errors);
            
            $_SESSION['toast'] = [
                'type' => 'error', 
                'msg' => $errorMsg
            ];
            header("Location: /lpj/upload/detail?id=$lpjId");
            exit;
        }
        
        // Jika semua validasi lolos, baru jalankan fungsi submit
        $lpjModel->submit($lpjId);
        
        // Logika notifikasi ke bendahara...
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'Bendahara' LIMIT 1");
        $stmt->execute();
        $bendahara = $stmt->fetch();
        if ($bendahara) {
            $this->db->prepare("
                INSERT INTO notifikasi (user_id, judul, pesan, link, created_at) 
                VALUES (?, 'LPJ Masuk', 'Ada LPJ baru menunggu verifikasi', '/lpj', NOW())
            ")->execute([$bendahara['id']]);
        }

        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'LPJ Berhasil Disubmit.'];
        header('Location: /pengajuan/lpj');
        exit;
    }
}
?>