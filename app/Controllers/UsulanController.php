<?php
// app/Controllers/UsulanController.php
namespace App\Controllers;

use App\Models\UsulanModel;
use PDO;
use Exception;

class UsulanController
{
    private $db;
    private $usulanModel;

    public function __construct($db) { 
        $this->db = $db; 
        $this->usulanModel = new UsulanModel($db);
    }

    private function redirectWithMsg($url, $type, $msg) {
        $_SESSION['toast'] = ['type' => $type, 'msg' => $msg];
        header("Location: $url"); exit;
    }
    
    private function cleanNumber($v) { 
        $v = trim(str_replace(['Rp', ' '], '', $v));
        if (strpos($v, ',') !== false) {
            $v = str_replace('.', '', $v); 
            $v = str_replace(',', '.', $v); 
        } else {
            if (substr_count($v, '.') > 1) {
                $v = str_replace('.', '', $v); 
            }
        }
        return empty($v) ? 0 : floatval($v);
    }
    
    private function ensureLogin() { 
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; } 
    }
    
    private function validateCsrf() { 
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? null)) 
            die("Security Alert: Invalid CSRF Token"); 
    }
    
    private function calculateTotalIndikatorBobot($indikator_kinerja_json) {
        if (!$indikator_kinerja_json) return 0;
        $data = json_decode($indikator_kinerja_json, true);
        $total = 0;
        foreach ($data as $item) {
            $total += intval($item['bobot'] ?? 0); 
        }
        return $total;
    }

    public function create() {
        $this->ensureLogin();
        $isEdit = false;
        
        $usulan = [
            'indikator_array' => [['indikator' => '', 'bulan_target' => '', 'bobot' => '']], 
            'metode_array' => [''], 
            'tahapan_array' => [''],
            'nama_kegiatan' => '',
            'gambaran_umum' => '',
            'penerima_manfaat' => '',
            'strategi_pencapaian_keluaran' => '',
            'tanggal_mulai' => '', 
            'tanggal_selesai' => '', 
        ]; 
        
        $masterIku = $this->usulanModel->getMasterIku();
        $masterSatuan = $this->usulanModel->getMasterSatuan();
        $masterKategori = $this->usulanModel->getMasterKategori();
        
        $rabDetails = [];
        $ikuDetails = [];
        
        require __DIR__.'/../Views/usulan/wizard_new.php';
    }

    public function edit($id) {
        $this->ensureLogin();
        $isEdit = true;
        $usulan = $this->usulanModel->findById($id);
        
        if (!$usulan || $usulan['user_id'] != $_SESSION['user_id'] || !in_array($usulan['status_terkini'], ['Draft', 'Revisi']))
            $this->redirectWithMsg('/pengajuan/usulan', 'error', 'Usulan tidak ditemukan atau status tidak diizinkan untuk diedit.');

        $defaultIndikator = [['indikator' => '', 'bulan_target' => '', 'bobot' => '']];
        
        $usulan['indikator_array'] = $usulan['indikator_kinerja'] ? json_decode($usulan['indikator_kinerja'], true) : $defaultIndikator;
        if (empty($usulan['indikator_array'])) $usulan['indikator_array'] = $defaultIndikator;
        
        $usulan['metode_array'] = $usulan['metode_pelaksanaan'] ? json_decode($usulan['metode_pelaksanaan'], true) : [''];
        if (empty($usulan['metode_array'])) $usulan['metode_array'] = [''];
        
        $usulan['tahapan_array'] = $usulan['tahapan_pelaksanaan'] ? json_decode($usulan['tahapan_pelaksanaan'], true) : [''];
        if (empty($usulan['tahapan_array'])) $usulan['tahapan_array'] = [''];

        $masterIku = $this->usulanModel->getMasterIku();
        $masterSatuan = $this->usulanModel->getMasterSatuan();
        $masterKategori = $this->usulanModel->getMasterKategori();
        
        $rabDetails = $this->usulanModel->getRABDetails($id); 
        $ikuDetails = $this->usulanModel->getIKUDetails($id);
        
        require __DIR__.'/../Views/usulan/wizard_new.php';
    }

    public function processStore() {
        $this->ensureLogin(); 
        $this->validateCsrf();
        
        // 1. DATA KAK & IKU
        $metode_array = array_filter(array_map('trim', $_POST['metode'] ?? []));
        $metode_json = $metode_array ? json_encode(array_values($metode_array)) : null;
        
        $tahapan_array = array_filter(array_map('trim', $_POST['tahapan'] ?? []));
        $tahapan_json = $tahapan_array ? json_encode(array_values($tahapan_array)) : null;
        
        $indikator_kinerja_json = null;
        $final_indikator = [];
        if (isset($_POST['indikator_kinerja']) && is_array($_POST['indikator_kinerja'])) {
            foreach ($_POST['indikator_kinerja'] as $item) {
                $bobot = intval($item['bobot'] ?? 0); 
                if (!empty($item['indikator']) && $bobot > 0) {
                     $final_indikator[] = [
                        'indikator' => $item['indikator'] ?? '',
                        'bulan_target' => $item['bulan_target'] ?? 'N/A',
                        'bobot' => $bobot
                     ];
                }
            }
            $indikator_kinerja_json = count($final_indikator) > 0 ? json_encode($final_indikator) : null;
        }
        
        $iku_details = [];
        if (isset($_POST['iku_data']) && is_array($_POST['iku_data'])) {
            foreach ($_POST['iku_data'] as $item) {
                $iku_id = filter_var($item['iku_id'] ?? 0, FILTER_VALIDATE_INT);
                $target = trim($item['target'] ?? ''); 
                if ($iku_id > 0 && !empty($target)) { 
                    $iku_details[] = ['iku_id' => $iku_id, 'target' => $target];
                }
            }
        }
        
        // 2. DATA RAB
        $rab_input = $_POST['rab_data'] ?? [];
        $cleanRAB = [];
        $grandTotalRAB = 0; 

        foreach ($rab_input as $item) {
            $uraian = trim($item['deskripsi'] ?? '');
            
            // --- LOGIKA SATUAN CUSTOM (Simpan Teks, Jangan Masuk Master) ---
            
            // Satuan Factor 1
            $s1_val = $item['satuan_factor_1_id'] ?? '';
            $s1_custom_text = trim($item['satuan_factor_1_custom'] ?? '');
            
            if ($s1_val === 'NEW') {
                $s1_id = null; // ID Kosong karena custom
                $s1_custom = $s1_custom_text; // Simpan Teksnya
            } else {
                $s1_id = !empty($s1_val) ? (int)$s1_val : null;
                $s1_custom = null;
            }

            // Satuan Factor 2
            $s2_val = $item['satuan_factor_2_id'] ?? '';
            $s2_custom_text = trim($item['satuan_factor_2_custom'] ?? '');
            
            if ($s2_val === 'NEW') {
                $s2_id = null;
                $s2_custom = $s2_custom_text;
            } else {
                $s2_id = !empty($s2_val) ? (int)$s2_val : null;
                $s2_custom = null;
            }

            // Satuan Final (biasanya mengikuti Factor 1)
            $satuan_final_id = $s1_id;
            $satuan_final_custom = $s1_custom;
            
            // Skip jika uraian kosong
            if (empty($uraian)) continue;

            $v1 = floatval($item['volume_factor_1'] ?? 0);
            $v2 = floatval($item['volume_factor_2'] ?? 0);
            $harga = $this->cleanNumber($item['harga_satuan'] ?? 0); 
            $totalVolume = floatval($item['total_volume'] ?? 0); 
            $totalHarga = $totalVolume * $harga;

            if ($totalVolume <= 0 || $harga <= 0) continue;

            $grandTotalRAB += $totalHarga;

            $cleanRAB[] = [
                'id' => (int)($item['id'] ?? 0), 
                'kategori_id' => (int)($item['kategori_id'] ?? 0),
                'uraian' => $uraian,
                
                'volume_factor_1' => $v1,
                'satuan_factor_1_id' => $s1_id,
                'satuan_factor_1_custom' => $s1_custom, 
                
                'volume_factor_2' => $v2,
                'satuan_factor_2_id' => $s2_id,
                'satuan_factor_2_custom' => $s2_custom,
                
                'harga_satuan' => $harga,
                'total_volume' => $totalVolume,
                'total_harga' => $totalHarga,
                
                'satuan_id' => $satuan_final_id,
                'satuan_custom' => $satuan_final_custom
            ];
        }

        $data = [
            'nama_kegiatan' => trim($_POST['nama_kegiatan'] ?? ''),
            'gambaran_umum' => trim($_POST['gambaran_umum'] ?? ''), 
            'penerima_manfaat' => trim($_POST['penerima_manfaat'] ?? ''), 
            'strategi_pencapaian_keluaran' => trim($_POST['strategi_pencapaian_keluaran'] ?? ''), 
            'metode_pelaksanaan' => $metode_json, 
            'tahapan_pelaksanaan' => $tahapan_json, 
            'tanggal_mulai' => $_POST['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $_POST['tanggal_selesai'] ?? null,
            'indikator_kinerja' => $indikator_kinerja_json, 
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $this->db->beginTransaction();
            $action = $_POST['action'] ?? 'draft';
            $usulanId = $_POST['id'] ?? 0;

            // [VALIDASI] TOTAL RAB TIDAK BOLEH 0
            if ($grandTotalRAB <= 0) {
                throw new Exception('Total RAB tidak boleh 0. Harap isi rincian biaya (minimal 1 item valid).');
            }

            if ($usulanId) {
                // MODE UPDATE
                $usulanCheck = $this->usulanModel->findById($usulanId);
                 if (!$usulanCheck || $usulanCheck['user_id'] != $_SESSION['user_id'] || !in_array($usulanCheck['status_terkini'], ['Draft', 'Revisi'])) {
                    throw new Exception('Usulan tidak dapat diubah (Akses ditolak atau status terkunci).');
                }

                if ($action === 'draft') $data['status_terkini'] = 'Draft';
                
                $this->usulanModel->updateUsulan($usulanId, $data);
                
                $this->usulanModel->deleteRABDetail($usulanId); 
                $this->usulanModel->insertRABDetail($usulanId, $cleanRAB); 
                
                $this->usulanModel->deleteTorIKU($usulanId);
                $this->usulanModel->insertTorIKU($usulanId, $iku_details);
                
                $this->usulanModel->syncTotalRAB($usulanId, $_SESSION['user_id']);
                
                if ($action === 'ajukan') {
                    $this->handleAjukanLogic($usulanId, $indikator_kinerja_json);
                    $msg = 'Usulan berhasil diperbarui dan Diajukan!';
                } else {
                    $msg = 'Usulan berhasil disimpan (Draft).';
                }
                
            } else {
                // MODE CREATE
                $data['user_id'] = $_SESSION['user_id'];
                $data['status_terkini'] = 'Draft'; 
                
                $newId = $this->usulanModel->createUsulan($data);
                $usulanId = $newId;

                $this->usulanModel->insertRABDetail($newId, $cleanRAB);
                $this->usulanModel->insertTorIKU($newId, $iku_details);
                
                $this->usulanModel->syncTotalRAB($newId, $_SESSION['user_id']);
                
                if ($action === 'ajukan') {
                    $this->handleAjukanLogic($newId, $indikator_kinerja_json);
                    $msg = 'Usulan berhasil dibuat dan Diajukan!';
                } else {
                    $msg = 'Usulan berhasil dibuat (Draft).';
                }
            }

            $this->db->commit();
            $this->redirectWithMsg('/pengajuan/usulan', 'success', $msg);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->redirectWithMsg('/pengajuan/usulan', 'error', 'Gagal menyimpan usulan: ' . $e->getMessage());
        }
    }

    private function handleAjukanLogic($id, $indikator_kinerja_json) {
        $usulan = $this->usulanModel->findById($id);
        
        if (!in_array($usulan['status_terkini'], ['Draft', 'Revisi'])) {
            throw new Exception('Usulan harus berstatus Draft atau Revisi untuk diajukan.');
        }
        
        if ($usulan['nominal_pencairan'] <= 0) {
            throw new Exception('Gagal Ajukan. Grand Total RAB harus lebih dari Rp 0.');
        }
        
        $totalIndikatorBobot = $this->calculateTotalIndikatorBobot($indikator_kinerja_json);
        if ($totalIndikatorBobot != 100) {
            throw new Exception('Gagal Ajukan. Total bobot Indikator Kinerja (KAK) harus 100% (Saat ini: ' . $totalIndikatorBobot . '%).');
        }
        
        $this->usulanModel->changeStatus(
            $id, 
            $_SESSION['user_id'], 
            'Diajukan', 
            'Diajukan ke Verifikator'
        );
        
        $stmtVerif = $this->db->prepare("SELECT id FROM users WHERE role = 'Verifikator' LIMIT 1");
        $stmtVerif->execute();
        $verifikator = $stmtVerif->fetch();
        if ($verifikator) {
             $this->db->prepare("
                INSERT INTO notifikasi (user_id, judul, pesan, link, created_at) 
                VALUES (?, 'Usulan Baru', 'Ada usulan kegiatan baru yang menunggu verifikasi', '/verifikasi', NOW())
            ")->execute([$verifikator['id']]);
        }
    }

    public function ajukan($id) {
        $this->ensureLogin(); 
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? null))) {
            die("Security Alert: Invalid CSRF Token");
        }

        $usulan = $this->usulanModel->findById($id);
        if (!$usulan || $usulan['user_id'] != $_SESSION['user_id']) {
            $this->redirectWithMsg('/pengajuan/usulan', 'error', 'Akses ditolak.');
        }
        
        $indikatorKinerjaJson = $usulan['indikator_kinerja'];

        try {
            $this->db->beginTransaction();
            $this->usulanModel->syncTotalRAB($id);
            $this->handleAjukanLogic($id, $indikatorKinerjaJson);
            $this->db->commit();
            $this->redirectWithMsg('/pengajuan/usulan', 'success', 'Usulan berhasil diajukan! Menunggu verifikasi.');
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->redirectWithMsg('/pengajuan/usulan', 'error', 'Error saat pengajuan: ' . $e->getMessage());
        }
    }
    
    public function detail($id) {
        $this->ensureLogin();
        
        $usulan = $this->usulanModel->findById($id);
        
        if (!$usulan) 
            $this->redirectWithMsg('/pengajuan/usulan', 'error', 'Usulan tidak ditemukan.');
            
        $rabDetails = $this->usulanModel->getRABDetails($id);
        $ikuDetails = $this->usulanModel->getIKUDetails($id);
        
        $logs = $this->db->prepare("SELECT l.*, u.username FROM log_histori_usulan l JOIN users u ON l.user_id=u.id WHERE usulan_id=? ORDER BY timestamp DESC");
        $logs->execute([$id]); 
        $logHistori = $logs->fetchAll(PDO::FETCH_ASSOC);
        
        $usulan['metode_array'] = $usulan['metode_pelaksanaan'] ? json_decode($usulan['metode_pelaksanaan'], true) : [];
        $usulan['tahapan_array'] = $usulan['tahapan_pelaksanaan'] ? json_decode($usulan['tahapan_pelaksanaan'], true) : [];
        $usulan['indikator_array'] = $usulan['indikator_kinerja'] ? json_decode($usulan['indikator_kinerja'], true) : [];

        require __DIR__.'/../Views/usulan/detail.php';
    }

    public function delete($id) {
        $this->ensureLogin(); 
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? null))) {
            die("Security Alert: Invalid CSRF Token");
        }
        
        $stmt = $this->db->prepare("SELECT status_terkini, user_id FROM usulan_kegiatan WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row && $row['user_id'] == $_SESSION['user_id'] && in_array($row['status_terkini'], ['Draft', 'Ditolak', 'Revisi'])) {
            try {
                $this->db->prepare("DELETE FROM usulan_kegiatan WHERE id = ?")->execute([$id]);
                $this->redirectWithMsg('/pengajuan/usulan', 'success', 'Usulan berhasil dihapus.');
            } catch (Exception $e) {
                $this->redirectWithMsg('/pengajuan/usulan', 'error', 'Gagal menghapus usulan: ' . $e->getMessage());
            }
        } else {
            $this->redirectWithMsg('/pengajuan/usulan', 'error', 'Usulan tidak dapat dihapus karena tidak ditemukan atau status tidak diizinkan.');
        }
    }
}