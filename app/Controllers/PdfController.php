<?php
// app/Controllers/PdfController.php
namespace App\Controllers;

use Mpdf\Mpdf;
use PDO;
use Exception;

// Define helper function globally if it doesn't exist
if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

// =========================================================
//  NAMESPACE FUNCTION: tanggal_indo()  (namespace App\Controllers)
// =========================================================
if (!function_exists(__NAMESPACE__ . '\\tanggal_indo')) {
    function tanggal_indo($tanggal) {
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        if (!$tanggal) return '-';
        $tgl = date('d', strtotime($tanggal));
        $bln = date('n', strtotime($tanggal));
        $thn = date('Y', strtotime($tanggal));

        return $tgl . ' ' . $bulan[$bln] . ' ' . $thn;
    }
}

// =========================================================
//  GLOBAL WRAPPER: membuat fungsi tanggal_indo() tersedia di VIEW
// =========================================================
if (!function_exists('tanggal_indo')) {
    if (function_exists('App\\Controllers\\tanggal_indo')) {
        // wrapper global memanggil fungsi namespace
        eval('function tanggal_indo($tanggal) { return \\App\\Controllers\\tanggal_indo($tanggal); }');
    }
}

class PdfController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function kak($id)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login'); 
            exit;
        }

        $stmt = $this->db->prepare("
            SELECT u.*, us.username, j.nama_jurusan 
            FROM usulan_kegiatan u 
            JOIN users us ON u.user_id = us.id 
            LEFT JOIN master_jurusan j ON us.jurusan_id = j.id 
            WHERE u.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $usulan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usulan) { 
            http_response_code(404); 
            exit; 
        }

        // Ambil data detail KAK
        $usulan['metode_array'] = $usulan['metode_pelaksanaan'] ? json_decode($usulan['metode_pelaksanaan'], true) : [];
        $usulan['tahapan_array'] = $usulan['tahapan_pelaksanaan'] ? json_decode($usulan['tahapan_pelaksanaan'], true) : [];
        $usulan['indikator_array'] = $usulan['indikator_kinerja'] ? json_decode($usulan['indikator_kinerja'], true) : [];

        $mpdf = new Mpdf(['format' => 'A4']);
        
        // --- LOGO COVER KAK (Menggunakan pnj.jpg) ---
        $logo_pnj_path = __DIR__ . '/../../public/pnj.jpg'; 
        $logo_data_uri_kak = '';
        $logo_path_abs = realpath($logo_pnj_path); 

        if ($logo_path_abs && file_exists($logo_path_abs) && is_readable($logo_path_abs)) {
            $data = file_get_contents($logo_path_abs);
            $mime = 'image/jpeg'; 
            if ($data !== false && !empty($data)) {
                $logo_data_uri_kak = 'data:' . $mime . ';base64,' . base64_encode($data);
            }
        }
        $logo_path_for_view_kak = $logo_data_uri_kak; // Variabel untuk view kak.php

        ob_start();
        include __DIR__.'/../Views/pdf/kak.php';
        $html = ob_get_clean();

        $mpdf->WriteHTML($html);
        $mpdf->Output('KAK_'.$usulan['nama_kegiatan'].'.pdf', 'I');
        exit;
    }

    public function rab($id)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login'); 
            exit;
        }

        $stmt = $this->db->prepare("
            SELECT u.*, us.username, j.nama_jurusan 
            FROM usulan_kegiatan u 
            JOIN users us ON u.user_id = us.id 
            LEFT JOIN master_jurusan j ON us.jurusan_id = j.id 
            WHERE u.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $usulan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usulan) { 
            http_response_code(404); 
            exit; 
        }

        // Ambil detail RAB multi-faktor
        $rab = $this->db->prepare("
            SELECT r.*, k.nama_kategori, s_final.nama_satuan AS nama_satuan,
                   s1.nama_satuan AS nama_satuan_f1, 
                   s2.nama_satuan AS nama_satuan_f2
            FROM rab_detail r 
            JOIN master_kategori_anggaran k ON r.kategori_id = k.id 
            LEFT JOIN master_satuan_anggaran s_final ON r.satuan_id = s_final.id
            LEFT JOIN master_satuan_anggaran s1 ON r.satuan_factor_1_id = s1.id
            LEFT JOIN master_satuan_anggaran s2 ON r.satuan_factor_2_id = s2.id
            WHERE r.usulan_id = :id
        ");
        $rab->execute(['id' => $id]);
        $rabRows = $rab->fetchAll(PDO::FETCH_ASSOC);

        $mpdf = new Mpdf(['format' => 'A4']);
        
        ob_start();
        include __DIR__.'/../Views/pdf/rab.php';
        $html = ob_get_clean();

        $mpdf->WriteHTML($html);
        $mpdf->Output('RAB_'.$usulan['nama_kegiatan'].'.pdf', 'I');
        exit;
    }

    public function beritaAcara($id)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login'); 
            exit;
        }

        try {
            // --- 1. Ambil Data Usulan & Pengajuan ---
            $stmt = $this->db->prepare("
                SELECT u.*, us.username, us.nama, us.email, us.role, mj.nama_jurusan, 
                       p.penanggung_jawab, p.pelaksana_kegiatan, p.surat_pengantar_path
                FROM usulan_kegiatan u 
                JOIN users us ON u.user_id = us.id 
                LEFT JOIN master_jurusan mj ON us.jurusan_id = mj.id
                LEFT JOIN pengajuan_kegiatan p ON u.id = p.usulan_id
                WHERE u.id = :id
            ");
            $stmt->execute(['id' => $id]);
            $usulan = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usulan) { 
                http_response_code(404); 
                exit; 
            }

            // JSON decode KAK
            $usulan['metode_array'] = $usulan['metode_pelaksanaan'] ? json_decode($usulan['metode_pelaksanaan'], true) : [];
            $usulan['tahapan_array'] = $usulan['tahapan_pelaksanaan'] ? json_decode($usulan['tahapan_pelaksanaan'], true) : [];
            $usulan['indikator_array'] = $usulan['indikator_kinerja'] ? json_decode($usulan['indikator_kinerja'], true) : [];

            // --- 2. Ambil Data RAB, Pencairan, LPJ, IKU ---
            $rabDetails = $this->db->prepare("
                SELECT r.*, mk.nama_kategori, s.nama_satuan,
                       s1.nama_satuan AS nama_satuan_f1, 
                       s2.nama_satuan AS nama_satuan_f2 
                FROM rab_detail r
                JOIN master_kategori_anggaran mk ON r.kategori_id = mk.id
                LEFT JOIN master_satuan_anggaran s ON r.satuan_id = s.id
                LEFT JOIN master_satuan_anggaran s1 ON r.satuan_factor_1_id = s1.id
                LEFT JOIN master_satuan_anggaran s2 ON r.satuan_factor_2_id = s2.id
                WHERE r.usulan_id = ? 
                ORDER BY mk.id ASC, r.id ASC
            ");
            $rabDetails->execute([$id]);
            $rabDetails = $rabDetails->fetchAll(PDO::FETCH_ASSOC);

            // Ambil total RAB per kategori
            $rabTotalPerKategori = $this->db->prepare("
                SELECT mk.nama_kategori, r.kategori_id, SUM(r.total) as total
                FROM rab_detail r
                JOIN master_kategori_anggaran mk ON r.kategori_id = mk.id
                WHERE r.usulan_id = ?
                GROUP BY mk.nama_kategori, r.kategori_id
            ");
            $rabTotalPerKategori->execute([$id]);
            $rabTotalPerKategori = $rabTotalPerKategori->fetchAll(PDO::FETCH_ASSOC);
            
            // Ambil Data IKU
            $ikuDetails = $this->db->prepare("
                SELECT t.*, m.deskripsi_iku 
                FROM tor_iku t 
                JOIN master_iku m ON t.iku_id = m.id 
                WHERE t.usulan_id = ?
                ORDER BY t.id ASC
            ");
            $ikuDetails->execute([$id]);
            $ikuDetails = $ikuDetails->fetchAll(PDO::FETCH_ASSOC);

            // Lanjutan Data Pencairan & LPJ
            $pencairanHistory = $this->db->prepare("
                SELECT pc.*, p.id AS pengajuan_id, l.id AS lpj_id, l.status_terkini AS lpj_status
                FROM pencairan_dana pc
                JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id
                LEFT JOIN lpj l ON pc.id = l.pencairan_id
                WHERE p.usulan_id = ? 
                ORDER BY pc.tanggal_pencairan ASC
            ");
            $pencairanHistory->execute([$id]);
            $pencairanHistory = $pencairanHistory->fetchAll(PDO::FETCH_ASSOC);

            $lpjDokumen = $this->db->prepare("
                SELECT ld.*, mk.nama_kategori
                FROM lpj_dokumen ld
                JOIN lpj l ON ld.lpj_id = l.id
                JOIN pencairan_dana pc ON l.pencairan_id = pc.id
                JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id
                JOIN master_kategori_anggaran mk ON ld.kategori_id = mk.id
                WHERE p.usulan_id = ?
                ORDER BY mk.id ASC, ld.uploaded_at DESC
            ");
            $lpjDokumen->execute([$id]);
            $lpjDokumen = $lpjDokumen->fetchAll(PDO::FETCH_ASSOC);

            // --- Ambil Catatan LPJ Terakhir ---
            $lpjData = $this->db->prepare("
                SELECT l.catatan_bendahara, l.status_terkini, l.updated_at FROM lpj l
                JOIN pencairan_dana pc ON l.pencairan_id = pc.id
                JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id
                WHERE p.usulan_id = ? AND l.status_terkini = 'Disetujui'
                ORDER BY l.id DESC LIMIT 1
            ");
            $lpjData->execute([$id]);
            $lpjRecord = $lpjData->fetch(PDO::FETCH_ASSOC);

            $lpjCatatan = $lpjRecord['catatan_bendahara'] ?? '';
            $catatanDetailArray = [];
            
            if (strpos($lpjCatatan, 'DETAIL:') !== false) {
                $detailJson = substr($lpjCatatan, strpos($lpjCatatan, 'DETAIL:') + 7);
                $catatanDetailArray = json_decode(trim($detailJson), true) ?? [];
            }
            $lpj_notes_per_doc = $catatanDetailArray;
            $tanggal_berita_acara = $lpjRecord['updated_at'] ?? date('Y-m-d'); 
            
            // --- 3. Generate Logo dengan Base64 ---
            $mpdf = new Mpdf(['format' => 'A4']);

            $tahun = date('Y', strtotime($usulan['tanggal_mulai']));
            $nama_unit = htmlspecialchars($usulan['nama_jurusan'] ?? $usulan['username']);
            
            // Generate Logo Base64
            $logo_pnj_path = __DIR__ . '/../../public/pnj.jpg'; 
            $logo_data_uri = '';
            $logo_path_abs = realpath($logo_pnj_path); 

            if ($logo_path_abs && file_exists($logo_path_abs) && is_readable($logo_path_abs)) {
                try {
                    ob_start();
                    $data = file_get_contents($logo_path_abs);
                    $mime = 'image/jpeg'; 
                    if ($data !== false && !empty($data)) {
                        $logo_data_uri = 'data:' . $mime . ';base64,' . base64_encode($data);
                    }
                    ob_end_clean();
                } catch (Exception $e) {
                    if (ob_get_level() > 0) ob_end_clean();
                    error_log("PDF LOGO ERROR: " . $e->getMessage());
                }
            }
            $logo_path_for_view = $logo_data_uri;

            // COVER PAGE (Halaman 1)
            // Fix: Gunakan padding-top saja dan pagebreak tag
            $cover_html = '
                <div style="text-align:center; font-family:\'Times New Roman\',serif; line-height:1.5; padding-top:100px; padding-bottom:0;">
                    <img src="'.$logo_data_uri.'" style="width:150px; margin-bottom:20px; display:block; margin-left:auto; margin-right:auto;">
                    <h2 style="font-size:16pt; margin-top:0;">KERANGKA ACUAN KERJA</h2>
                    <h1 style="font-size:18pt; margin-bottom:50px;">TAHUN ANGGARAN '.$tahun.'</h1>
                    <p style="font-size:14pt; margin-bottom:10px;">Kegiatan:</p>
                    <h3 style="font-size:18pt; font-weight:bold; margin:0 0 50px; padding:0 50px;">'.htmlspecialchars($usulan['nama_kegiatan']).'</h3>
                    <p style="font-size:14pt; margin-bottom:10px;">Unit Kerja:</p>
                    <h3 style="font-size:16pt; font-weight:bold; margin:0 0 50px; text-transform:uppercase;">'.$nama_unit.'</h3>
                    <p style="font-size:14pt; margin-top:100px;">
                        Kementerian Pendidikan Tinggi, Riset,<br>dan Teknologi
                    </p>
                    <h4 style="font-size:14pt;">Tahun '.$tahun.'</h4>
                </div>
                <pagebreak />
            ';

            $mpdf->WriteHTML($cover_html);

            // HALAMAN ISI (Halaman 2 dan seterusnya)
            ob_start();
            $logo_src = ''; 
            include __DIR__.'/../Views/pdf/berita_acara.php';
            $html = ob_get_clean();

            $mpdf->WriteHTML($html);
            $mpdf->Output('DOKUMEN_FINAL_'.$usulan['nama_kegiatan'].'.pdf', 'I');
            exit;

        } catch (Exception $e) {
            error_log("PDF Berita Acara Error: " . $e->getMessage());
            http_response_code(500);
            echo "Gagal memuat PDF: " . htmlspecialchars($e->getMessage());
            exit;
        }
    }
}