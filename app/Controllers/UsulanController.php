<?php
namespace App\Controllers;

use App\Models\UsulanModel;
use PDO;
use Exception;
use Throwable;

class UsulanController
{
    private $db;
    private $usulanModel;

    public function __construct($db) { 
        $this->db = $db; 
        $this->usulanModel = new UsulanModel($db);
    }

    // --- HELPERS ---
    private function redirectWithMsg($url, $type, $msg) {
        $_SESSION['toast'] = ['type' => $type, 'msg' => $msg];
        header("Location: $url"); exit;
    }
    private function cleanNumber($v) { return empty($v) ? 0 : floatval(preg_replace('/[^0-9.]/', '', $v)); }
    private function ensureLogin() { if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; } }
    private function validateCsrf() { if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die("Security Alert"); }

    // --- WIZARD (CREATE/EDIT) ---
    public function create() {
        $this->ensureLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $this->processStore($_POST, 'create');
        else {
            $iku = $this->db->query("SELECT * FROM master_iku WHERE status='active'")->fetchAll(PDO::FETCH_ASSOC);
            $kategori = $this->db->query("SELECT * FROM master_kategori_anggaran")->fetchAll(PDO::FETCH_ASSOC);
            $rabData=[]; $usulan=null; $selectedIku=[];
            require __DIR__ . '/../Views/usulan/wizard_new.php';
        }
    }

    public function edit($id) {
        $this->ensureLogin();
        $usulan = $this->usulanModel->findById($id);
        if (!$usulan || $usulan['user_id'] != $_SESSION['user_id']) $this->redirectWithMsg('/monitoring', 'error', 'Akses Ditolak');
        // Hanya boleh edit Draft/Revisi
        if (!in_array($usulan['status_terkini'], ['Draft','Revisi','Ditolak'])) $this->redirectWithMsg('/monitoring', 'error', 'Status terkunci.');

        // Load Data
        $rabData = $this->db->prepare("SELECT * FROM rab_detail WHERE usulan_id=? ORDER BY kategori_id"); $rabData->execute([$id]); $rabData = $rabData->fetchAll(PDO::FETCH_ASSOC);
        $ikuRel = $this->db->prepare("SELECT iku_id, bobot_persen FROM tor_iku WHERE usulan_id=?"); $ikuRel->execute([$id]); 
        $selectedIku = []; while ($row = $ikuRel->fetch(PDO::FETCH_ASSOC)) $selectedIku[$row['iku_id']] = $row['bobot_persen'];
        
        $iku = $this->db->query("SELECT * FROM master_iku WHERE status='active'")->fetchAll(PDO::FETCH_ASSOC);
        $kategori = $this->db->query("SELECT * FROM master_kategori_anggaran")->fetchAll(PDO::FETCH_ASSOC);
        
        // Decode JSONs
        if($usulan['metode_pelaksanaan']) $usulan['metode_array']=json_decode($usulan['metode_pelaksanaan'],true);
        if($usulan['tahapan_pelaksanaan']) $usulan['tahapan_array']=json_decode($usulan['tahapan_pelaksanaan'],true);
        if($usulan['indikator_kinerja']) $usulan['indikator_array']=json_decode($usulan['indikator_kinerja'],true);

        $isEdit = true;
        require __DIR__ . '/../Views/usulan/wizard_new.php';
    }

    public function update($id) {
        $this->ensureLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $this->processStore($_POST, 'update', $id);
        else $this->edit($id);
    }

    // --- ACTION PENGUSUL: LENGKAPI DATA (SKENARIO 2) ---
    // Ini dijalankan saat status = 'Disetujui Verifikator'
    public function lengkapi($id) {
        $this->ensureLogin();
        $usulan = $this->usulanModel->findById($id);
        
        if (!$usulan || $usulan['user_id'] != $_SESSION['user_id']) $this->redirectWithMsg('/monitoring', 'error', 'Akses Ditolak');
        if ($usulan['status_terkini'] !== 'Disetujui Verifikator') $this->redirectWithMsg('/monitoring', 'error', 'Belum disetujui Verifikator.');

        require __DIR__ . '/../Views/usulan/lengkapi.php';
    }

    public function prosesLengkapi($id) {
        $this->ensureLogin();
        $this->validateCsrf();
        
        // Validasi Status
        $usulan = $this->usulanModel->findById($id);
        if ($usulan['status_terkini'] !== 'Disetujui Verifikator') die('Status Invalid');

        // Upload Surat
        if (empty($_FILES['surat_pengantar']['name'])) $this->redirectWithMsg("/usulan/lengkapi?id=$id", 'error', 'Surat Pengantar Wajib Diupload.');
        
        try {
            $this->handleUpload($id, 'Surat Pengantar', $_FILES['surat_pengantar']);
            
            // Update Data PJ & Pelaksana & Status ke WD2
            $pj = trim($_POST['penanggung_jawab']);
            $pelaksana = trim($_POST['pelaksana_kegiatan']);
            $tgl_mulai = $_POST['tanggal_mulai'];
            $tgl_selesai = $_POST['tanggal_selesai'];

            $this->db->prepare("UPDATE usulan_kegiatan SET 
                penanggung_jawab = ?, 
                pelaksana_kegiatan = ?, 
                tanggal_mulai = ?, 
                tanggal_selesai = ?,
                status_terkini = 'Menunggu WD2',
                updated_at = NOW()
                WHERE id = ?")
            ->execute([$pj, $pelaksana, $tgl_mulai, $tgl_selesai, $id]);

            $this->usulanModel->addLog($id, $_SESSION['user_id'], 'Disetujui Verifikator', 'Menunggu WD2', 'Melengkapi berkas & Surat Pengantar');
            
            $this->redirectWithMsg('/monitoring', 'success', 'Berkas lengkap! Usulan diteruskan ke Wakil Direktur 2.');

        } catch (Exception $e) {
            $this->redirectWithMsg("/usulan/lengkapi?id=$id", 'error', $e->getMessage());
        }
    }

    // --- DETAIL VIEW ---
    public function detail($id) {
        $this->ensureLogin();
        $stmt = $this->db->prepare("SELECT u.*, us.username FROM usulan_kegiatan u JOIN users us ON u.user_id=us.id WHERE u.id=:id");
        $stmt->execute(['id'=>$id]);
        $usulan = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$usulan) { require __DIR__.'/../Views/errors/404.php'; exit; }

        // Security Access
        $role=$_SESSION['role'];
        if($usulan['user_id']!=$_SESSION['user_id'] && $role!=='Admin' && !in_array($role,['Verifikator','WD2','PPK','Bendahara','Direktur'])) {
            require __DIR__.'/../Views/errors/403.php'; exit;
        }

        // Fetch Details
        $rab = $this->db->prepare("SELECT r.*,k.nama_kategori FROM rab_detail r JOIN master_kategori_anggaran k ON r.kategori_id=k.id WHERE usulan_id=?"); $rab->execute([$id]); $rabDetails=$rab->fetchAll(PDO::FETCH_ASSOC);
        $iku = $this->db->prepare("SELECT m.deskripsi_iku,t.bobot_persen FROM tor_iku t JOIN master_iku m ON t.iku_id=m.id WHERE usulan_id=?"); $iku->execute([$id]); $ikuDetails=$iku->fetchAll(PDO::FETCH_ASSOC);
        $logs = $this->db->prepare("SELECT l.*, u.username FROM log_histori_usulan l JOIN users u ON l.user_id=u.id WHERE usulan_id=? ORDER BY timestamp DESC"); $logs->execute([$id]); $logHistori=$logs->fetchAll(PDO::FETCH_ASSOC);
        
        $docs = $this->usulanModel->getDocuments($id);

        // Decode JSON
        if($usulan['metode_pelaksanaan']) $usulan['metode_array']=json_decode($usulan['metode_pelaksanaan'],true);
        if($usulan['tahapan_pelaksanaan']) $usulan['tahapan_array']=json_decode($usulan['tahapan_pelaksanaan'],true);
        if($usulan['indikator_kinerja']) $usulan['indikator_array']=json_decode($usulan['indikator_kinerja'],true);

        require __DIR__.'/../Views/usulan/detail.php';
    }

    // --- COMMON ACTIONS ---
    public function delete($id) {
        $this->ensureLogin(); $this->validateCsrf();
        $row = $this->db->prepare("SELECT status_terkini,user_id FROM usulan_kegiatan WHERE id=?")->execute([$id]);
        // ... (Logic delete sama seperti sebelumnya)
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

    public function ajukan($id) {
        $this->ensureLogin(); $this->validateCsrf();
        $this->db->prepare("UPDATE usulan_kegiatan SET status_terkini='Verifikasi', updated_at=NOW() WHERE id=?")->execute([$id]);
        $this->usulanModel->addLog($id, $_SESSION['user_id'], 'Draft', 'Verifikasi', 'Diajukan ke Verifikator');
        $this->redirectWithMsg('/monitoring', 'success', 'Usulan diajukan!');
    }

    // --- PROCESS STORE (WIZARD) ---
    private function processStore($data, $mode, $id = null) {
        $this->validateCsrf();
        $uid = $_SESSION['user_id'];
        $nama = trim($data['nama_kegiatan']);
        $umum = trim($data['gambaran_umum']);

        // JSON Data
        $metode = json_encode(array_filter($data['metode']??[]));
        $tahapan = json_encode(array_filter($data['tahapan']??[]));
        // ... (Indikator JSON Logic sama seperti sebelumnya)
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
        $indikator = json_encode($indikatorArray);


        try {
            $this->db->beginTransaction();
            if ($mode === 'create') {
                $stmt = $this->db->prepare("INSERT INTO usulan_kegiatan (user_id, nama_kegiatan, gambaran_umum, penerima_manfaat, target_luaran, metode_pelaksanaan, tahapan_pelaksanaan, indikator_kinerja, tanggal_mulai, tanggal_selesai, status_terkini) VALUES (?,?,?,?,?,?,?,?,?,?,'Draft')");
                $stmt->execute([$uid, $nama, $umum, $data['penerima_manfaat'], $data['target_luaran'], $metode, $tahapan, $indikator, $data['tanggal_mulai'], $data['tanggal_selesai']]);
                $id = $this->db->lastInsertId();
                $this->usulanModel->addLog($id, $uid, null, 'Draft', 'Usulan dibuat');
            } else {
                $stmt = $this->db->prepare("UPDATE usulan_kegiatan SET nama_kegiatan=?, gambaran_umum=?, penerima_manfaat=?, target_luaran=?, metode_pelaksanaan=?, tahapan_pelaksanaan=?, indikator_kinerja=?, tanggal_mulai=?, tanggal_selesai=?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$nama, $umum, $data['penerima_manfaat'], $data['target_luaran'], $metode, $tahapan, $indikator, $data['tanggal_mulai'], $data['tanggal_selesai'], $id]);
                $this->db->prepare("DELETE FROM tor_iku WHERE usulan_id=?")->execute([$id]);
                $this->db->prepare("DELETE FROM rab_detail WHERE usulan_id=?")->execute([$id]);
            }

            // IKU
            if(!empty($data['iku_id'])) {
                $stmtI = $this->db->prepare("INSERT INTO tor_iku (usulan_id, iku_id, bobot_persen) VALUES (?,?,?)");
                foreach($data['iku_id'] as $iid) $stmtI->execute([$id, $iid, $this->cleanNumber($data['bobot_iku'][$iid]??0)]);
            }

            // RAB
            $total = 0;
            if(!empty($data['uraian'])) {
                $stmtR = $this->db->prepare("INSERT INTO rab_detail (usulan_id, kategori_id, uraian, volume, satuan, harga_satuan, total) VALUES (?,?,?,?,?,?,?)");
                foreach($data['uraian'] as $k => $v) {
                    if(!trim($v)) continue;
                    $sub = $this->cleanNumber($data['volume'][$k]) * $this->cleanNumber($data['harga_satuan'][$k]);
                    $total += $sub;
                    $stmtR->execute([$id, $data['kategori_id'][$k], $v, $this->cleanNumber($data['volume'][$k]), $data['satuan'][$k], $this->cleanNumber($data['harga_satuan'][$k]), $sub]);
                }
            }
            $this->db->prepare("UPDATE usulan_kegiatan SET nominal_pencairan=? WHERE id=?")->execute([$total, $id]);

            // NO FILE UPLOAD HERE anymore.
            
            $this->db->commit();
            $this->redirectWithMsg('/monitoring', 'success', 'Draft tersimpan.');
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->redirectWithMsg($_SERVER['HTTP_REFERER'], 'error', $e->getMessage());
        }
    }

    private function handleUpload($id, $jenis, $file) {
        $allow=['application/pdf'];
        if(!in_array(mime_content_type($file['tmp_name']),$allow)) throw new Exception("File harus PDF");
        if($file['size']>5*1024*1024) throw new Exception("Max 5MB");
        
        $name = "DOC_{$id}_".time()."_".bin2hex(random_bytes(4)).".pdf";
        $dir = __DIR__.'/../../public/uploads/';
        if(!is_dir($dir)) mkdir($dir,0755,true);
        move_uploaded_file($file['tmp_name'], $dir.$name);
        
        $this->usulanModel->addDocument($id, $jenis, "/uploads/$name");
    }

    // --- FITUR TAMBAHAN: UPLOAD LPJ & SUBMIT ---
    public function uploadDokumen($id)
    {
        $this->ensureLogin();
        $this->validateCsrf();
        
        $usulan = $this->usulanModel->findById($id);
        if ($usulan['user_id'] != $_SESSION['user_id']) $this->redirectWithMsg('/monitoring', 'error', 'Akses ditolak.');

        // Validasi Status: Upload hanya boleh saat 'Disetujui Verifikator' (Surat) atau 'Pencairan'/'Revisi' (LPJ)
        $allowedStatus = ['Disetujui Verifikator', 'Pencairan', 'LPJ']; // LPJ boleh upload ulang jika status masih LPJ (sebelum disetujui)
        if (!in_array($usulan['status_terkini'], $allowedStatus)) {
             $this->redirectWithMsg("/usulan/detail?id=$id", 'error', 'Tidak dapat upload dokumen pada status ini.');
        }

        $jenis = $_POST['jenis_dokumen'] ?? 'LPJ';
        
        if (empty($_FILES['dokumen']['name'])) {
            $this->redirectWithMsg("/usulan/detail?id=$id", 'error', 'Pilih file terlebih dahulu.');
        }

        try {
            $this->handleUpload($id, $jenis, $_FILES['dokumen']);
            $this->redirectWithMsg("/usulan/detail?id=$id", 'success', "$jenis berhasil diunggah.");
        } catch (Exception $e) {
            $this->redirectWithMsg("/usulan/detail?id=$id", 'error', $e->getMessage());
        }
    }

    public function submitLpj($id)
    {
        $this->ensureLogin();
        $this->validateCsrf();

        $usulan = $this->usulanModel->findById($id);
        if ($usulan['user_id'] != $_SESSION['user_id']) $this->redirectWithMsg('/monitoring', 'error', 'Akses ditolak.');

        if ($usulan['status_terkini'] !== 'Pencairan') {
            $this->redirectWithMsg("/usulan/detail?id=$id", 'error', 'Status kegiatan belum masuk tahap pelaporan.');
        }

        // Cek apakah ada dokumen LPJ
        $docs = $this->usulanModel->getDocuments($id);
        $hasLpj = false;
        foreach($docs as $d) {
            if($d['jenis_dokumen'] === 'LPJ') { $hasLpj = true; break; }
        }

        if (!$hasLpj) {
            $this->redirectWithMsg("/usulan/detail?id=$id", 'error', 'Harap upload minimal satu dokumen LPJ sebelum submit.');
        }

        // Update Status
        $this->db->prepare("UPDATE usulan_kegiatan SET status_terkini = 'LPJ', updated_at = NOW() WHERE id = ?")->execute([$id]);
        $this->usulanModel->addLog($id, $_SESSION['user_id'], 'Pencairan', 'LPJ', 'Melaporkan pertanggungjawaban kegiatan.');

        $this->redirectWithMsg('/monitoring', 'success', 'LPJ Berhasil dikirim ke Bendahara!');
    }
}