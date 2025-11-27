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

    // [ELITE HELPER] Membersihkan format uang/angka
    private function cleanNumber($value)
    {
        if (empty($value)) return 0;
        // Hapus karakter non-angka kecuali titik/koma desimal
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

    // --- CREATE ---
    public function create() 
    {
        $this->ensureLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processStore($_POST, 'create');
        } else {
            // Data Master untuk Dropdown
            $jurusan  = $this->db->query("SELECT * FROM master_jurusan ORDER BY nama_jurusan")->fetchAll(PDO::FETCH_ASSOC);
            $iku      = $this->db->query("SELECT * FROM master_iku ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
            $kategori = $this->db->query("SELECT * FROM master_kategori_anggaran ORDER BY nama_kategori")->fetchAll(PDO::FETCH_ASSOC);
            
            // Dummy array untuk view agar tidak error
            $rabData = []; 
            
            require __DIR__ . '/../Views/usulan/wizard.php';
        }
    }

    // --- EDIT ---
    public function edit($id) 
    {
        $this->ensureLogin();
        $usulan = $this->usulanModel->findById($id);

        if (!$usulan || $usulan['user_id'] != $_SESSION['user_id']) {
            $this->redirectWithMsg('/monitoring', 'error', 'Akses ditolak atau data tidak ditemukan.');
        }
        
        // Hanya boleh edit jika status Draft, Revisi, atau Ditolak
        if (!in_array($usulan['status_terkini'], ['Draft', 'Revisi', 'Ditolak'])) {
            $this->redirectWithMsg('/monitoring', 'error', 'Usulan sedang diproses, tidak dapat diedit.');
        }

        $rabData = $this->db->prepare("SELECT * FROM rab_detail WHERE usulan_id = ?");
        $rabData->execute([$id]);
        $rabData = $rabData->fetchAll(PDO::FETCH_ASSOC);

        $ikuRel = $this->db->prepare("SELECT iku_id FROM tor_iku WHERE usulan_id = ?");
        $ikuRel->execute([$id]);
        $selectedIku = $ikuRel->fetchAll(PDO::FETCH_COLUMN);

        $jurusan  = $this->db->query("SELECT * FROM master_jurusan ORDER BY nama_jurusan")->fetchAll(PDO::FETCH_ASSOC);
        $iku      = $this->db->query("SELECT * FROM master_iku ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        $kategori = $this->db->query("SELECT * FROM master_kategori_anggaran ORDER BY nama_kategori")->fetchAll(PDO::FETCH_ASSOC);

        $isEdit = true;
        require __DIR__ . '/../Views/usulan/wizard.php';
    }

    // --- UPDATE ---
    public function update($id) 
    {
        $this->ensureLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processStore($_POST, 'update', $id);
        } else {
            $this->edit($id); // Fallback jika diakses via GET
        }
    }

    // --- DELETE ---
    public function delete($id) 
    {
        $this->ensureLogin();
        $this->validateCsrf();

        // Cek kepemilikan & status
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

    // --- DETAIL ---
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

        $iku = $this->db->prepare("SELECT m.deskripsi_iku FROM tor_iku t JOIN master_iku m ON t.iku_id = m.id WHERE t.usulan_id = ?");
        $iku->execute([$id]);
        $ikuDetails = $iku->fetchAll(PDO::FETCH_ASSOC);
        
        $logs = $this->db->prepare("SELECT l.*, u.username, u.role FROM log_histori_usulan l JOIN users u ON l.user_id = u.id WHERE l.usulan_id = ? ORDER BY l.timestamp DESC");
        $logs->execute([$id]);
        $logHistori = $logs->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/usulan/detail.php';
    }

    // ==========================================
    // [ELITE CORE LOGIC] - PROCESS STORE (FIXED)
    // ==========================================
    private function processStore($data, $mode, $id = null) 
    {
        $this->validateCsrf();

        // 1. Sanitasi Input Dasar
        $namaKegiatan = trim($data['nama_kegiatan'] ?? '');
        $gambaranUmum = trim($data['gambaran_umum'] ?? ''); // HTMLSpecialChars sudah via Router
        $penerimaManfaat = trim($data['penerima_manfaat'] ?? '');
        $targetLuaran = trim($data['target_luaran'] ?? '');

        if (empty($namaKegiatan) || empty($gambaranUmum)) {
            $this->redirectWithMsg($_SERVER['HTTP_REFERER'], 'error', 'Nama Kegiatan dan Gambaran Umum wajib diisi.');
        }

        try {
            $this->db->beginTransaction();

            if ($mode === 'create') {
                // [INSERT HEADER]
                $sql = "INSERT INTO usulan_kegiatan (user_id, nama_kegiatan, gambaran_umum, penerima_manfaat, target_luaran, status_terkini, nominal_pencairan) 
                        VALUES (:uid, :nama, :umum, :manfaat, :luaran, 'Verifikasi', 0)"; 
                // Default status langsung 'Verifikasi' agar masuk antrian verifikator
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'uid' => $_SESSION['user_id'],
                    'nama' => $namaKegiatan,
                    'umum' => $gambaranUmum,
                    'manfaat' => $penerimaManfaat,
                    'luaran' => $targetLuaran
                ]);
                $usulanId = $this->db->lastInsertId();
                
                $this->usulanModel->addLog($usulanId, $_SESSION['user_id'], null, 'Verifikasi', 'Usulan baru diajukan');

            } else {
                // [UPDATE HEADER]
                $usulanId = $id;
                $currentData = $this->usulanModel->findById($id);
                if(!$currentData) throw new \Exception("Data tidak ditemukan.");

                // Jika status sebelumnya Revisi/Ditolak, kembalikan ke Verifikasi saat update
                $newStatus = in_array($currentData['status_terkini'], ['Revisi', 'Ditolak']) ? 'Verifikasi' : $currentData['status_terkini'];

                $sql = "UPDATE usulan_kegiatan 
                        SET nama_kegiatan = :nama, gambaran_umum = :umum, penerima_manfaat = :manfaat, target_luaran = :luaran, status_terkini = :status, updated_at = NOW() 
                        WHERE id = :id";
                $this->db->prepare($sql)->execute([
                    'nama' => $namaKegiatan,
                    'umum' => $gambaranUmum,
                    'manfaat' => $penerimaManfaat,
                    'luaran' => $targetLuaran,
                    'status' => $newStatus,
                    'id' => $id
                ]);

                // Bersihkan Detail Lama (Untuk di-insert ulang)
                $this->db->prepare("DELETE FROM tor_iku WHERE usulan_id = ?")->execute([$id]);
                $this->db->prepare("DELETE FROM rab_detail WHERE usulan_id = ?")->execute([$id]);
                
                if ($newStatus !== $currentData['status_terkini']) {
                    $this->usulanModel->addLog($usulanId, $_SESSION['user_id'], $currentData['status_terkini'], $newStatus, 'Revisi & Resubmit');
                }
            }

            // 2. INSERT IKU (Indikator Kinerja)
            if (!empty($data['iku_id']) && is_array($data['iku_id'])) {
                $stmtIku = $this->db->prepare("INSERT INTO tor_iku (usulan_id, iku_id) VALUES (?, ?)");
                foreach ($data['iku_id'] as $ikuId) {
                    $stmtIku->execute([$usulanId, (int)$ikuId]);
                }
            }

            // 3. INSERT RAB (Safe Loop Logic)
            $totalPengajuan = 0;
            
            if (!empty($data['uraian']) && is_array($data['uraian'])) {
                $stmtRab = $this->db->prepare("INSERT INTO rab_detail (usulan_id, kategori_id, uraian, volume, satuan, harga_satuan, total) VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                foreach ($data['uraian'] as $key => $val) {
                    $uraian = trim($val);
                    if (empty($uraian)) continue; // Skip baris kosong

                    // Ambil data berdasarkan key yang sama, handle potential missing keys
                    $vol = isset($data['volume'][$key]) ? $this->cleanNumber($data['volume'][$key]) : 0;
                    $harga = isset($data['harga_satuan'][$key]) ? $this->cleanNumber($data['harga_satuan'][$key]) : 0;
                    $satuan = trim($data['satuan'][$key] ?? '');
                    $kategoriId = (int)($data['kategori_id'][$key] ?? 0);

                    if ($vol <= 0 || $harga < 0) continue; // Validasi angka logis

                    $totalItem = $vol * $harga;
                    $totalPengajuan += $totalItem;
                    
                    $stmtRab->execute([$usulanId, $kategoriId, $uraian, $vol, $satuan, $harga, $totalItem]);
                }
            }

            // 4. Update Total Nominal ke Header (PENTING agar dashboard update)
            $this->db->prepare("UPDATE usulan_kegiatan SET nominal_pencairan = ? WHERE id = ?")
                     ->execute([$totalPengajuan, $usulanId]);

            $this->db->commit();
            
            // Redirect Sukses
            $this->redirectWithMsg('/monitoring', 'success', 'Data usulan berhasil disimpan & diteruskan!');

        } catch (Throwable $e) { 
            $this->db->rollBack();
            // Log error detail ke server
            error_log("CRITICAL ERROR UsulanController: " . $e->getMessage());
            // Tampilkan pesan user-friendly
            $this->redirectWithMsg($_SERVER['HTTP_REFERER'], 'error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}