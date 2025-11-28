<?php
namespace App\Controllers;

use App\Models\UsulanModel;
use PDO;
use Throwable;

class UsulanController
{
    private $db;
    private $usulanModel;

    public function __construct($db) 
    { 
        $this->db = $db; 
        $this->usulanModel = new UsulanModel($db);
    }

    private function redirectWithMsg($url, $type, $msg)
    {
        $_SESSION['toast'] = ['type' => $type, 'msg' => $msg];
        header("Location: $url");
        exit;
    }

    private function cleanNumber($value)
    {
        if (empty($value)) return 0;
        $clean = preg_replace('/[^0-9.]/', '', $value); 
        return floatval($clean);
    }

    private function ensureLogin() {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }
    }

    private function validateCsrf() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("Security Alert: Invalid CSRF Token.");
        }
    }

    // ============================================
    // CREATE - Wizard Step untuk Buat Usulan Baru
    // ============================================
    public function create() 
    {
        $this->ensureLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processStore($_POST, 'create');
        } else {
            // Data Master untuk Form
            $iku = $this->db->query("SELECT * FROM master_iku WHERE status = 'active' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
            $kategori = $this->db->query("SELECT * FROM master_kategori_anggaran ORDER BY nama_kategori")->fetchAll(PDO::FETCH_ASSOC);
            
            $rabData = []; 
            $usulan = null; // Untuk mode create
            $selectedIku = [];
            
            require __DIR__ . '/../Views/usulan/wizard_new.php';
        }
    }

    // ============================================
    // EDIT - Load Data Usulan yang Sudah Ada
    // ============================================
    public function edit($id) 
    {
        $this->ensureLogin();
        $usulan = $this->usulanModel->findById($id);

        if (!$usulan || $usulan['user_id'] != $_SESSION['user_id']) {
            $this->redirectWithMsg('/monitoring', 'error', 'Akses ditolak atau data tidak ditemukan.');
        }
        
        // Hanya boleh edit jika status Draft atau Revisi
        if (!in_array($usulan['status_terkini'], ['Draft', 'Revisi', 'Ditolak'])) {
            $this->redirectWithMsg('/monitoring', 'error', 'Usulan sedang diproses, tidak dapat diedit.');
        }

        // Load RAB Details
        $rabData = $this->db->prepare("SELECT * FROM rab_detail WHERE usulan_id = ? ORDER BY kategori_id");
        $rabData->execute([$id]);
        $rabData = $rabData->fetchAll(PDO::FETCH_ASSOC);

        // Load IKU dengan Bobot
        $ikuRel = $this->db->prepare("SELECT iku_id, bobot_persen FROM tor_iku WHERE usulan_id = ?");
        $ikuRel->execute([$id]);
        $selectedIku = [];
        while ($row = $ikuRel->fetch(PDO::FETCH_ASSOC)) {
            $selectedIku[$row['iku_id']] = $row['bobot_persen'];
        }

        $iku = $this->db->query("SELECT * FROM master_iku WHERE status = 'active' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        $kategori = $this->db->query("SELECT * FROM master_kategori_anggaran ORDER BY nama_kategori")->fetchAll(PDO::FETCH_ASSOC);

        // Decode JSON fields
        if (!empty($usulan['metode_pelaksanaan'])) {
            $usulan['metode_array'] = json_decode($usulan['metode_pelaksanaan'], true) ?: [];
        }
        if (!empty($usulan['tahapan_pelaksanaan'])) {
            $usulan['tahapan_array'] = json_decode($usulan['tahapan_pelaksanaan'], true) ?: [];
        }
        if (!empty($usulan['indikator_kinerja'])) {
            $usulan['indikator_array'] = json_decode($usulan['indikator_kinerja'], true) ?: [];
        }

        $isEdit = true;
        require __DIR__ . '/../Views/usulan/wizard_new.php';
    }

    // ============================================
    // UPDATE - Simpan Perubahan Usulan
    // ============================================
    public function update($id) 
    {
        $this->ensureLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processStore($_POST, 'update', $id);
        } else {
            $this->edit($id);
        }
    }

    // ============================================
    // DELETE - Hapus Usulan
    // ============================================
    public function delete($id) 
    {
        $this->ensureLogin();
        $this->validateCsrf();

        $stmt = $this->db->prepare("SELECT status_terkini, user_id FROM usulan_kegiatan WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row && $row['user_id'] == $_SESSION['user_id'] && in_array($row['status_terkini'], ['Draft', 'Ditolak', 'Revisi'])) {
            $this->db->prepare("DELETE FROM usulan_kegiatan WHERE id = ?")->execute([$id]);
            $this->redirectWithMsg('/monitoring', 'success', 'Usulan berhasil dihapus permanen.');
        } else {
            $this->redirectWithMsg('/monitoring', 'error', 'Gagal hapus. Status usulan terkunci atau akses ditolak.');
        }
    }

    // ============================================
    // DETAIL - Lihat Detail Usulan
    // ============================================
    public function detail($id) 
    {
        $this->ensureLogin();
        
        $stmt = $this->db->prepare("SELECT u.*, us.username, us.email FROM usulan_kegiatan u JOIN users us ON u.user_id = us.id WHERE u.id = :id");
        $stmt->execute(['id' => $id]);
        $usulan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usulan) { require __DIR__ . '/../Views/errors/404.php'; exit; }

        $role = $_SESSION['role'];
        $isOwner = ($usulan['user_id'] == $_SESSION['user_id']);
        $isAdmin = ($role === 'Admin');
        $isApprover = in_array($role, ['Verifikator', 'WD2', 'PPK', 'Bendahara', 'Direktur']);

        if (!$isOwner && !$isAdmin && !$isApprover) { require __DIR__ . '/../Views/errors/403.php'; exit; }

        $rab = $this->db->prepare("SELECT r.*, k.nama_kategori FROM rab_detail r JOIN master_kategori_anggaran k ON r.kategori_id = k.id WHERE r.usulan_id = ?");
        $rab->execute([$id]);
        $rabDetails = $rab->fetchAll(PDO::FETCH_ASSOC);

        $iku = $this->db->prepare("SELECT m.deskripsi_iku, t.bobot_persen FROM tor_iku t JOIN master_iku m ON t.iku_id = m.id WHERE t.usulan_id = ?");
        $iku->execute([$id]);
        $ikuDetails = $iku->fetchAll(PDO::FETCH_ASSOC);
        
        $logs = $this->db->prepare("SELECT l.*, u.username, u.role FROM log_histori_usulan l JOIN users u ON l.user_id = u.id WHERE l.usulan_id = ? ORDER BY l.timestamp DESC");
        $logs->execute([$id]);
        $logHistori = $logs->fetchAll(PDO::FETCH_ASSOC);

        // Decode JSON
        if (!empty($usulan['metode_pelaksanaan'])) {
            $usulan['metode_array'] = json_decode($usulan['metode_pelaksanaan'], true) ?: [];
        }
        if (!empty($usulan['tahapan_pelaksanaan'])) {
            $usulan['tahapan_array'] = json_decode($usulan['tahapan_pelaksanaan'], true) ?: [];
        }
        if (!empty($usulan['indikator_kinerja'])) {
            $usulan['indikator_array'] = json_decode($usulan['indikator_kinerja'], true) ?: [];
        }

        require __DIR__ . '/../Views/usulan/detail.php';
    }

    // ==========================================
    // PROCESS STORE - Core Logic untuk Save
    // ==========================================
    private function processStore($data, $mode, $id = null) 
    {
        $this->validateCsrf();

        // STEP 1: Validasi Input Dasar (KAK)
        $namaKegiatan = trim($data['nama_kegiatan'] ?? '');
        $gambaranUmum = trim($data['gambaran_umum'] ?? '');
        $penerimaManfaat = trim($data['penerima_manfaat'] ?? '');
        $targetLuaran = trim($data['target_luaran'] ?? '');
        $tanggalMulai = $data['tanggal_mulai'] ?? null;
        $tanggalSelesai = $data['tanggal_selesai'] ?? null;

        if (empty($namaKegiatan) || empty($gambaranUmum)) {
            $this->redirectWithMsg($_SERVER['HTTP_REFERER'], 'error', 'Nama Kegiatan dan Gambaran Umum wajib diisi.');
        }

        // Array JSON untuk metode & tahapan
        $metodeArray = [];
        if (!empty($data['metode'])) {
            foreach ($data['metode'] as $m) {
                if (!empty(trim($m))) $metodeArray[] = trim($m);
            }
        }

        $tahapanArray = [];
        if (!empty($data['tahapan'])) {
            foreach ($data['tahapan'] as $t) {
                if (!empty(trim($t))) $tahapanArray[] = trim($t);
            }
        }

        // Indikator Kinerja (untuk tampilan, bukan IKU)
        $indikatorArray = [];
        if (!empty($data['indikator_keberhasilan'])) {
            foreach ($data['indikator_keberhasilan'] as $idx => $ind) {
                if (!empty(trim($ind))) {
                    $indikatorArray[] = [
                        'indikator' => trim($ind),
                        'bulan_target' => $data['bulan_target'][$idx] ?? '',
                        'bobot' => $this->cleanNumber($data['bobot_keberhasilan'][$idx] ?? 0)
                    ];
                }
            }
        }

        try {
            $this->db->beginTransaction();

            if ($mode === 'create') {
                // INSERT HEADER
                $sql = "INSERT INTO usulan_kegiatan (
                    user_id, nama_kegiatan, gambaran_umum, penerima_manfaat, target_luaran,
                    metode_pelaksanaan, tahapan_pelaksanaan, indikator_kinerja,
                    tanggal_mulai, tanggal_selesai,
                    status_terkini, nominal_pencairan
                ) VALUES (
                    :uid, :nama, :umum, :manfaat, :luaran,
                    :metode, :tahapan, :indikator,
                    :tgl_mulai, :tgl_selesai,
                    'Draft', 0
                )"; 
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'uid' => $_SESSION['user_id'],
                    'nama' => $namaKegiatan,
                    'umum' => $gambaranUmum,
                    'manfaat' => $penerimaManfaat,
                    'luaran' => $targetLuaran,
                    'metode' => json_encode($metodeArray),
                    'tahapan' => json_encode($tahapanArray),
                    'indikator' => json_encode($indikatorArray),
                    'tgl_mulai' => $tanggalMulai,
                    'tgl_selesai' => $tanggalSelesai
                ]);
                $usulanId = $this->db->lastInsertId();
                
                $this->usulanModel->addLog($usulanId, $_SESSION['user_id'], null, 'Draft', 'Usulan baru dibuat');

            } else {
                // UPDATE HEADER
                $usulanId = $id;
                $currentData = $this->usulanModel->findById($id);
                if(!$currentData) throw new \Exception("Data tidak ditemukan.");

                $sql = "UPDATE usulan_kegiatan 
                        SET nama_kegiatan = :nama, gambaran_umum = :umum, penerima_manfaat = :manfaat, 
                            target_luaran = :luaran, metode_pelaksanaan = :metode, tahapan_pelaksanaan = :tahapan,
                            indikator_kinerja = :indikator, tanggal_mulai = :tgl_mulai, tanggal_selesai = :tgl_selesai,
                            updated_at = NOW() 
                        WHERE id = :id";
                $this->db->prepare($sql)->execute([
                    'nama' => $namaKegiatan,
                    'umum' => $gambaranUmum,
                    'manfaat' => $penerimaManfaat,
                    'luaran' => $targetLuaran,
                    'metode' => json_encode($metodeArray),
                    'tahapan' => json_encode($tahapanArray),
                    'indikator' => json_encode($indikatorArray),
                    'tgl_mulai' => $tanggalMulai,
                    'tgl_selesai' => $tanggalSelesai,
                    'id' => $id
                ]);

                // Bersihkan Detail Lama
                $this->db->prepare("DELETE FROM tor_iku WHERE usulan_id = ?")->execute([$id]);
                $this->db->prepare("DELETE FROM rab_detail WHERE usulan_id = ?")->execute([$id]);
                
                $this->usulanModel->addLog($usulanId, $_SESSION['user_id'], $currentData['status_terkini'], 'Draft', 'Usulan diperbarui');
            }

            // STEP 2: INSERT IKU dengan Bobot
            if (!empty($data['iku_id']) && is_array($data['iku_id'])) {
                $stmtIku = $this->db->prepare("INSERT INTO tor_iku (usulan_id, iku_id, bobot_persen) VALUES (?, ?, ?)");
                $totalBobot = 0;
                
                foreach ($data['iku_id'] as $ikuId) {
                    $bobot = $this->cleanNumber($data['bobot_iku'][$ikuId] ?? 0);
                    $totalBobot += $bobot;
                    $stmtIku->execute([$usulanId, (int)$ikuId, $bobot]);
                }
                
                // Validasi total bobot harus 100%
                if (abs($totalBobot - 100) > 0.01) {
                    throw new \Exception("Total bobot IKU harus 100%. Saat ini: {$totalBobot}%");
                }
            }

            // STEP 3: INSERT RAB
            $totalPengajuan = 0;
            
            if (!empty($data['uraian']) && is_array($data['uraian'])) {
                $stmtRab = $this->db->prepare("INSERT INTO rab_detail (usulan_id, kategori_id, uraian, volume, satuan, harga_satuan, total) VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                foreach ($data['uraian'] as $key => $val) {
                    $uraian = trim($val);
                    if (empty($uraian)) continue;

                    $vol = isset($data['volume'][$key]) ? $this->cleanNumber($data['volume'][$key]) : 0;
                    $harga = isset($data['harga_satuan'][$key]) ? $this->cleanNumber($data['harga_satuan'][$key]) : 0;
                    $satuan = trim($data['satuan'][$key] ?? '');
                    $kategoriId = (int)($data['kategori_id'][$key] ?? 0);

                    if ($vol <= 0 || $harga < 0) continue;

                    $totalItem = $vol * $harga;
                    $totalPengajuan += $totalItem;
                    
                    $stmtRab->execute([$usulanId, $kategoriId, $uraian, $vol, $satuan, $harga, $totalItem]);
                }
            }

            // Update Total Nominal
            $this->db->prepare("UPDATE usulan_kegiatan SET nominal_pencairan = ? WHERE id = ?")
                     ->execute([$totalPengajuan, $usulanId]);

            $this->db->commit();
            
            $this->redirectWithMsg('/monitoring', 'success', 'Data usulan berhasil disimpan sebagai Draft!');

        } catch (Throwable $e) { 
            $this->db->rollBack();
            error_log("CRITICAL ERROR UsulanController: " . $e->getMessage());
            $this->redirectWithMsg($_SERVER['HTTP_REFERER'], 'error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    // ============================================
    // AJUKAN - Submit usulan dari Draft ke Verifikasi
    // ============================================
    public function ajukan($id)
    {
        $this->ensureLogin();
        $this->validateCsrf();

        $usulan = $this->usulanModel->findById($id);
        
        if (!$usulan || $usulan['user_id'] != $_SESSION['user_id']) {
            $this->redirectWithMsg('/monitoring', 'error', 'Akses ditolak.');
        }

        if ($usulan['status_terkini'] !== 'Draft') {
            $this->redirectWithMsg('/monitoring', 'error', 'Hanya usulan berstatus Draft yang bisa diajukan.');
        }

        // Update status ke Verifikasi
        $this->db->prepare("UPDATE usulan_kegiatan SET status_terkini = 'Verifikasi', updated_at = NOW() WHERE id = ?")
                 ->execute([$id]);

        $this->usulanModel->addLog($id, $_SESSION['user_id'], 'Draft', 'Verifikasi', 'Usulan diajukan untuk diverifikasi');

        $this->redirectWithMsg('/monitoring', 'success', 'Usulan berhasil diajukan ke Verifikator!');
    }
}