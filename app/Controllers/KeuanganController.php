<?php
namespace App\Controllers;

use App\Models\PencairanModel;
use App\Models\PengajuanModel;
use App\Models\UsulanModel;
use App\Models\LpjModel;
use PDO;

class KeuanganController
{
    private $db;

    public function __construct($db) { $this->db = $db; }

    private function ensureBendahara() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Bendahara') {
            header('Location: /dashboard'); exit;
        }
    }

    // --- HELPER FUNCTIONS ---
    private function updateUsulanStatus($usulanId, $status) {
        $stmt = $this->db->prepare("
            UPDATE usulan_kegiatan 
            SET status_terkini = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$status, $usulanId]);
    }
    
    private function notifyPengusul($pengajuanId, $judul, $pesan) {
        $stmt = $this->db->prepare("
            SELECT u.user_id 
            FROM pengajuan_kegiatan p 
            JOIN usulan_kegiatan u ON p.usulan_id = u.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$pengajuanId]);
        $uid = $stmt->fetchColumn();
        
        if ($uid) {
            $this->db->prepare("
                INSERT INTO notifikasi (user_id, judul, pesan, link, created_at) 
                VALUES (?, ?, ?, '/monitoring', NOW())
            ")->execute([$uid, $judul, $pesan]);
        }
    }
    
    private function notifyUser($usulanId, $judul, $pesan, $link) {
        $stmt = $this->db->prepare("
            SELECT user_id 
            FROM usulan_kegiatan 
            WHERE id = ?
        ");
        $stmt->execute([$usulanId]);
        $uid = $stmt->fetchColumn();
        
        if ($uid) {
            $this->db->prepare("
                INSERT INTO notifikasi (user_id, judul, pesan, link, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ")->execute([$uid, $judul, $pesan, $link]);
        }
    }

    // --- HALAMAN PENCARIAN (PERBAIKAN STATISTIK) ---
    public function indexPencairan() {
        $this->ensureBendahara();
        $pencairanModel = new PencairanModel($this->db);
        
        // 1. Ambil Parameter
        $search = $_GET['q'] ?? '';
        $filterStatus = $_GET['status'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;

        // 2. Ambil Semua Data dari Model
        $activities = $pencairanModel->getBendaharaActivities();
        
        // --- INISIALISASI VARIABEL STATISTIK (DITAMBAHKAN) ---
        $totalDanaCair = 0;
        $totalKegiatanBelumCair = 0;
        $totalKegiatanPending = 0;   // Status 'Bertahap'
        $totalKegiatanSelesaiCair = 0; // Status 'Lunas'

        $processedData = [];
        foreach ($activities as $item) {
            $cair = (float) $pencairanModel->getTotalDisbursed($item['pengajuan_id']);
            $item['total_dicairkan'] = $cair;
            $item['sisa_dana'] = (float) $item['nominal_pencairan'] - $cair;
            
            // Akumulasi Statistik Global
            $totalDanaCair += $cair;

            // Penentuan Status Pembayaran
            if ($cair <= 0) {
                $status = 'Belum Dicairkan';
                $totalKegiatanBelumCair++;
            } elseif ($item['sisa_dana'] <= 0) {
                $status = 'Lunas';
                $totalKegiatanSelesaiCair++;
            } else {
                $status = 'Bertahap';
                $totalKegiatanPending++;
            }
            $item['status_pembayaran'] = $status;

            // --- Logika Filter Search ---
            if (!empty($search) && stripos($item['nama_kegiatan'], $search) === false) {
                continue;
            }

            // --- Logika Filter Status ---
            if (!empty($filterStatus) && $item['status_pembayaran'] !== $filterStatus) {
                continue;
            }

            $processedData[] = $item;
        }

        // 3. Pagination
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

        require __DIR__ . '/../Views/keuangan/pencairan.php';
    }

    public function prosesPencairan() {
        $this->ensureBendahara();
        if (!isset($_POST['csrf_token'])) die('Invalid CSRF');

        $pengajuanId = $_POST['pengajuan_id'];
        $nominalRequest = (float) str_replace(['Rp', '.', ','], '', $_POST['nominal_dicairkan']);
        
        $pencairanModel = new PencairanModel($this->db);
        $pengajuanModel = new PengajuanModel($this->db);
        
        $pengajuan = $pengajuanModel->findById($pengajuanId);
        if (!$pengajuan) {
             $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Data tidak ditemukan'];
             header("Location: /pencairan"); exit;
        }

        $totalSudahCair = $pencairanModel->getTotalDisbursed($pengajuanId);
        $totalRAB = (float) $pengajuan['nominal_pencairan'];
        $sisaDana = $totalRAB - $totalSudahCair;

        if ($nominalRequest <= 0) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: Nominal pencairan harus lebih dari 0.'];
            header("Location: /pencairan"); exit;
        }

        if (round($nominalRequest, 2) > round($sisaDana, 2)) { 
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: Nominal melebihi sisa dana (Sisa: Rp '.number_format($sisaDana).')'];
            header("Location: /pencairan"); exit;
        }

        if ($totalSudahCair == 0) {
            $tglBatasLpj = date('Y-m-d', strtotime('+14 days'));
        } else {
            $stmtDate = $this->db->prepare("
                SELECT tanggal_batas_lpj 
                FROM pencairan_dana 
                WHERE pengajuan_id = ? 
                ORDER BY id ASC 
                LIMIT 1
            ");
            $stmtDate->execute([$pengajuanId]);
            $existing = $stmtDate->fetchColumn();
            $tglBatasLpj = $existing ?: date('Y-m-d', strtotime('+14 days'));
        }

        try {
            $this->db->beginTransaction();

            $pencairanId = $pencairanModel->create([
                'pengajuan_id' => $pengajuanId,
                'nominal_dicairkan' => $nominalRequest,
                'tanggal_batas_lpj' => $tglBatasLpj,
                'bukti_transfer_path' => null 
            ]);
            
            $totalBaru = $totalSudahCair + $nominalRequest;
            $sisaBaru = $totalRAB - $totalBaru;
            
            $statusAkhir = ($sisaBaru <= 0) ? 'Pencairan Selesai' : 'Pencairan Bertahap';
            $this->updateUsulanStatus($pengajuan['usulan_id'], 'Disetujui'); 
            
            $this->db->prepare("
                INSERT INTO log_histori_usulan (usulan_id, ref_table, ref_id, user_id, status_baru, catatan) 
                VALUES (?, 'pencairan', ?, ?, ?, ?)
            ")->execute([$pengajuan['usulan_id'], $pencairanId, $_SESSION['user_id'], $statusAkhir, "Pencairan Tahap Rp " . number_format($nominalRequest)]);
            
            $this->notifyPengusul($pengajuanId, "Dana Cair ($statusAkhir)", "Dana sebesar Rp " . number_format($nominalRequest) . " telah dicairkan.");

            $this->db->commit();
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Berhasil dicairkan'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['toast'] = ['type' => 'error', 'msg' => $e->getMessage()];
        }
        
        header("Location: /pencairan"); exit;
    }
    
    // --- LPJ ---
    public function indexLpj() {
        $this->ensureBendahara();
        $lpjModel = new LpjModel($this->db);
        
        $search = $_GET['q'] ?? '';
        $filterPengusul = $_GET['pengusul'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;

        $stmtPengusul = $this->db->query("SELECT id, username, nama FROM users WHERE role = 'Pengusul' ORDER BY nama ASC");
        $listPengusul = $stmtPengusul->fetchAll(PDO::FETCH_ASSOC);

        $raw_lpj = $lpjModel->getPendingVerifikasi();
        
        $processedLpj = [];
        foreach ($raw_lpj as $row) {
            if (!empty($search) && stripos($row['nama_kegiatan'], $search) === false) continue;
            if (!empty($filterPengusul) && $row['user_id'] != $filterPengusul) continue;
            $processedLpj[] = $row;
        }

        $totalItems = count($processedLpj);
        $totalPages = ceil($totalItems / $limit);
        $page = max(1, min($page, $totalPages > 0 ? $totalPages : 1));
        $offset = ($page - 1) * $limit;
        
        $usulan = array_slice($processedLpj, $offset, $limit);
        
        $pager = [
            'current' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems
        ];

        require __DIR__ . '/../Views/keuangan/lpj.php';
    }

    public function detailLpj($lpjId) {
        $this->ensureBendahara();
        $lpjModel = new LpjModel($this->db);
        $usulanModel = new UsulanModel($this->db);
        
        $lpj = $lpjModel->getById($lpjId);
        if(!$lpj) { header('Location: /lpj'); exit; }
        
        $lpjData = $lpjModel->getLpjUsulanData($lpjId);
        $usulanId = $lpjData['usulan_id'];

        $usulanFull = $usulanModel->findById($usulanId);
        $lpjData['nama_kegiatan'] = $usulanFull['nama_kegiatan'] ?? 'Kegiatan Tidak Ditemukan';
        
        $stmtRab = $this->db->prepare("
            SELECT 
                r.*, 
                k.nama_kategori,
                s1.nama_satuan as satuan_master_1, 
                s2.nama_satuan as satuan_master_2
            FROM rab_detail r 
            LEFT JOIN master_kategori_anggaran k ON r.kategori_id = k.id
            LEFT JOIN master_satuan_anggaran s1 ON r.satuan_factor_1_id = s1.id 
            LEFT JOIN master_satuan_anggaran s2 ON r.satuan_factor_2_id = s2.id 
            WHERE r.usulan_id = ? 
            ORDER BY r.kategori_id ASC, r.id ASC
        ");
        $stmtRab->execute([$usulanId]);
        $rabDetails = $stmtRab->fetchAll(PDO::FETCH_ASSOC);

        $allDokumen = $lpjModel->getDokumen($lpjId);
        $rabItems = [];
        
        foreach ($rabDetails as $r) {
            $item = $r;
            $item['rab_id'] = $r['id'];
            $item['nominal_rab'] = $r['total'];
            
            $item['nama_satuan_1'] = !empty($r['satuan_factor_1_custom']) 
                                    ? $r['satuan_factor_1_custom'] 
                                    : ($r['satuan_master_1'] ?? '-');

            $item['nama_satuan_2'] = !empty($r['satuan_factor_2_custom']) 
                                    ? $r['satuan_factor_2_custom'] 
                                    : ($r['satuan_master_2'] ?? '-');

            $item['uploaded_files'] = [];
            $itemFiles = [];
            foreach ($allDokumen as $doc) {
                if ($doc['rab_detail_id'] == $r['id']) { $itemFiles[] = $doc; }
            }
            usort($itemFiles, function($a, $b) { return $a['id'] <=> $b['id']; });

            $counter = 1;
            foreach ($itemFiles as $f) {
                $dName = $r['deskripsi'];
                if (!empty($f['file_path'])) {
                    if ($counter > 1) { $dName .= ' (' . $counter . ')'; }
                    $counter++;
                }
                $item['uploaded_files'][] = [
                    'id' => $f['id'],
                    'file_path' => $f['file_path'],
                    'name' => $dName
                ];
            }
            
            $realisasiInfo = $lpjModel->getItemRealisasi($lpjId, $r['id']);
            $item['total_realisasi'] = $realisasiInfo['nominal_realisasi'];
            $item['keterangan'] = $realisasiInfo['keterangan'];
            
            $rabItems[] = $item;
        }

        require __DIR__ . '/../Views/keuangan/lpj_detail.php';
    }

    public function verifikasiLpj() {
        $this->ensureBendahara();
        $lpjId = $_POST['lpj_id'];
        $aksi = $_POST['aksi'];
        $catatan = json_encode(array_filter($_POST['catatan_detail'] ?? []));
        $lpjModel = new LpjModel($this->db);
        
        if($aksi == 'setuju') {
            $lpjModel->updateStatus($lpjId, 'Disetujui', $catatan);
            (new UsulanModel($this->db))->addLog($lpjModel->getLpjUsulanData($lpjId)['usulan_id'], $_SESSION['user_id'], 'Disetujui', 'LPJ Disetujui', "LPJ disetujui", 'lpj', $lpjId);
            $this->notifyUser($lpjModel->getLpjUsulanData($lpjId)['usulan_id'], "LPJ Disetujui", "LPJ Anda telah disetujui.", "/monitoring");
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'LPJ Disetujui'];
        } else {
            $lpjModel->updateStatus($lpjId, 'Revisi', $catatan);
            (new UsulanModel($this->db))->addLog($lpjModel->getLpjUsulanData($lpjId)['usulan_id'], $_SESSION['user_id'], 'Revisi', 'LPJ Revisi', "LPJ dikembalikan", 'lpj', $lpjId);
            $this->notifyUser($lpjModel->getLpjUsulanData($lpjId)['usulan_id'], "Revisi LPJ", "LPJ dikembalikan. Cek catatan.", "/pengajuan/lpj");
            $_SESSION['toast'] = ['type' => 'warning', 'msg' => 'LPJ Dikembalikan'];
        }
        header('Location: /lpj'); exit;
    }
    
    public function riwayatLpj() {
        $this->ensureBendahara();
        $lpjModel = new LpjModel($this->db);
        
        // 1. Ambil Parameter Filter
        $search = $_GET['q'] ?? '';
        $filterProposer = $_GET['pengusul'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;

        // 2. Ambil daftar pengusul untuk dropdown filter
        $stmtPengusul = $this->db->query("SELECT id, username, nama FROM users WHERE role = 'Pengusul' ORDER BY nama ASC");
        $listPengusul = $stmtPengusul->fetchAll(PDO::FETCH_ASSOC);

        // 3. Ambil data asli (Hanya yang Disetujui)
        $raw_usulan = $lpjModel->getByStatus('Disetujui');
        
        $processedData = [];
        foreach ($raw_usulan as $row) {
            // Filter Search (Nama Kegiatan)
            if (!empty($search) && stripos($row['nama_kegiatan'], $search) === false) {
                continue;
            }
            // Filter Pengusul (Berdasarkan Username)
            if (!empty($filterProposer) && $row['username'] !== $filterProposer) {
                continue;
            }
            $processedData[] = $row;
        }

        // 4. Hitung Pagination
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

        require __DIR__ . '/../Views/keuangan/lpj_riwayat.php';
    }
}