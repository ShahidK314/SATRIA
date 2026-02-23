<?php 
// app/Views/pdf/berita_acara.php

// FIX: Naikkan limit PCRE dan Memory untuk PDF besar
ini_set('pcre.backtrack_limit', '5000000');
ini_set('memory_limit', '256M');

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

$tanggalFormatted = date('d F Y', strtotime($tanggal_berita_acara));
$tanggalIndo = str_replace(
    ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
    $tanggalFormatted
);
$tahun = date('Y', strtotime($usulan['tanggal_mulai']));
$totalRABKegiatan = array_sum(array_column($rabTotalPerKategori, 'total'));

// --- LOGIKA GROUPING LPJ ---
$lpjGrouped = [];
$itemDescriptions = [];

if (!empty($rabDetails)) {
    foreach ($rabDetails as $r) {
        $itemDescriptions[$r['id']] = $r['deskripsi'];
    }
}

foreach ($lpjDokumen as $doc) {
    $key = $doc['rab_detail_id'];
    
    if (!isset($lpjGrouped[$key])) {
        $lpjGrouped[$key] = [
            'nama_kategori' => $doc['nama_kategori'],
            'uraian' => $itemDescriptions[$key] ?? 'Item Tidak Dikenal',
            'nominal' => 0,
            'keterangan' => [],
            'files' => [],
            'latest_timestamp' => 0 // Variabel untuk melacak tanggal terbaru
        ];
    }
    
    $lpjGrouped[$key]['nominal'] += $doc['nominal'];
    
    if (!empty($doc['keterangan'])) {
        $lpjGrouped[$key]['keterangan'][] = $doc['keterangan'];
    }
    
    if (!empty($doc['file_path'])) {
        $lpjGrouped[$key]['files'][] = $doc['file_path'];
    }

    // --- LOGIKA TANGGAL TERBARU ---
    // Cek tanggal upload dokumen ini, jika lebih baru dari yang tersimpan, update.
    if (!empty($doc['uploaded_at'])) {
        $currentTs = strtotime($doc['uploaded_at']);
        if ($currentTs > $lpjGrouped[$key]['latest_timestamp']) {
            $lpjGrouped[$key]['latest_timestamp'] = $currentTs;
        }
    }
}

// Gabungkan keterangan dan format tanggal final
foreach ($lpjGrouped as $k => $v) {
    $lpjGrouped[$k]['keterangan'] = implode(', ', array_unique($v['keterangan']));
    
    // Gunakan tanggal terbaru saja. Jika tidak ada data (0), tampilkan strip.
    $lpjGrouped[$k]['dates_str'] = ($v['latest_timestamp'] > 0) ? date('d/m/Y', $v['latest_timestamp']) : '-';
}

$rabRows = $rabDetails; 
$ikuDetails = $ikuDetails ?? []; 

// Path root project
$projectRoot = realpath(__DIR__ . '/../../../');

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: \'Times New Roman\', serif; font-size: 11pt; line-height: 1.6; color: #000; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 30px; }
        .header img { height: 60px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto; }
        .header h2 { margin: 5px 0; font-size: 13pt; text-transform: uppercase; }
        .header p { margin: 5px 0; font-size: 10pt; }
        .title { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 30px; font-size: 14pt; }
        .section-title { font-weight: bold; font-size: 12pt; margin-top: 20px; margin-bottom: 10px; border-bottom: 1px solid #000; padding-bottom: 5px; }
        
        /* TABEL UMUM */
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; vertical-align: top; word-wrap: break-word; }
        th { background-color: #f0f0f0; font-weight: bold; font-size: 10pt; text-align: center; }
        .no-border th, .no-border td { border: none; padding: 3px 6px; }
        .summary-box { border: 1px solid #ccc; padding: 10px; margin-bottom: 20px; }
        .rab-table th, .rab-table td { font-size: 8pt; }
        .center { text-align: center; }
        .num { text-align: right; }
        .list-spacing { margin: 5px 0 10px 0; padding-left: 20px; }
        
        /* --- STYLE KHUSUS LPJ (ANTI POTONG) --- */
        .lpj-header-table { margin-bottom: 0; border-bottom: none; }
        
        /* Wrapper Item: Ini kuncinya. page-break-inside: avoid mencegah pemisahan */
        .lpj-item-wrapper {
            page-break-inside: avoid; 
            margin-bottom: -1px; /* Agar border tabel menyatu */
            width: 100%;
        }

        /* Tabel di dalam wrapper */
        .lpj-item-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 0;
        }
        
        .lpj-item-table td { font-size: 9pt; }

        /* Style Bukti */
        .evidence-row td { 
            background-color: #fdfdfd; 
            border-top: 1px dashed #999; 
            padding: 10px 15px; 
        }
        
        .file-label { 
            font-size: 10pt; 
            color: #333; 
            margin-bottom: 10px; 
            font-weight: bold; 
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
            display: block;
            width: 100%;
        }
        
        .evidence-gallery { width: 100%; text-align: left; }
        
        .img-evidence { 
            width: 46%; 
            height: auto; 
            max-height: 350px; 
            display: inline-block; 
            margin: 5px 1.5%; 
            border: 1px solid #ccc; 
            padding: 4px; 
            background: #fff; 
            vertical-align: top;
            box-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .file-generic { 
            display: inline-block; 
            padding: 15px; 
            border: 1px solid #ccc; 
            background: #eee; 
            margin: 5px; 
            font-size: 9pt; 
            width: 150px; 
            text-align: center; 
            vertical-align: top;
        }
    </style>
</head>
<body>
    <div class="header">
        ' . (!empty($logo_src) ? '<img src="' . $logo_src . '" alt="Logo PNJ">' : '') . '
        <h2>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h2>
        <h2>POLITEKNIK NEGERI JAKARTA</h2>
        <p>Jl. Prof. Dr. G.A. Siwabessy, Kampus Universitas Indonesia Depok 16425</p>
    </div>

    <div class="title">REKAPITULASI DOKUMEN FINAL KEGIATAN</div>

    <div class="summary-box">
        <table class="no-border" style="line-height:1.2;">
            <tr><td width="30%">Kegiatan</td><td>: <strong>' . htmlspecialchars($usulan['nama_kegiatan']) . '</strong></td></tr>
            <tr><td>Unit Kerja</td><td>: ' . htmlspecialchars($usulan['nama_jurusan'] ?? $usulan['username']) . '</td></tr>
            <tr><td>Tahun Anggaran</td><td>: ' . $tahun . '</td></tr>
        </table>
        
        <div class="section-title" style="margin-top:15px; font-size:10pt;">Indikator Kinerja</div>
        <table class="rab-table" style="margin-top:0;">
            <thead>
                <tr>
                    <th width="70%" style="font-size:9pt; background-color:#e6e6e6;">Deskripsi IKU</th>
                    <th width="30%" style="font-size:9pt; background-color:#e6e6e6;">Target </th>
                </tr>
            </thead>
            <tbody>';
            
            if (!empty($ikuDetails)) {
                foreach ($ikuDetails as $iku) {
                    $target = htmlspecialchars($iku['target'] ?? '-'); 
                    $deskripsi_iku = htmlspecialchars($iku['deskripsi_iku'] ?? '-');
                    $html .= '<tr><td style="font-size:9pt;">' . $deskripsi_iku . '</td><td class="center" style="font-size:9pt;">' . $target . '</td></tr>';
                }
            } else {
                $html .= '<tr><td colspan="2" class="center" style="font-size:9pt; padding:8px;">Tidak ada IKU yang dipilih.</td></tr>';
            }
            $html .= '
            </tbody>
        </table>
    </div>

    <div class="section-title">I. Kerangka Acuan Kegiatan (KAK)</div>
    <div style="font-weight: bold; margin-bottom: 5px;">A. Gambaran Umum / Latar Belakang</div>
    <div style="text-align: justify; margin-bottom: 10px;">' . nl2br(htmlspecialchars($usulan['gambaran_umum'])) . '</div>

    <div style="font-weight: bold; margin-bottom: 5px;">B. Penerima Manfaat</div>
    <div style="text-align: justify; margin-bottom: 10px;">' . htmlspecialchars($usulan['penerima_manfaat']) . '</div>
    
    <div style="font-weight: bold; margin-bottom: 5px;">C. Strategi Pencapaian Keluaran</div>
    <div style="text-align: justify; margin-bottom: 10px;">' . nl2br(htmlspecialchars($usulan['strategi_pencapaian_keluaran'])) . '</div>

    <div style="font-weight: bold; margin-bottom: 5px;">D. Metode Pelaksanaan</div>
    <ul class="list-spacing" style="list-style-type: disc;">';
    if (!empty($usulan['metode_array'])) {
        foreach ($usulan['metode_array'] as $m) { $html .= '<li>' . htmlspecialchars($m) . '</li>'; }
    } else { $html .= '<li>-</li>'; }
    $html .= '</ul>
    
    <div style="font-weight: bold; margin-bottom: 5px;">E. Tahapan Pelaksanaan</div>
    <ul class="list-spacing" style="list-style-type: decimal;">';
    if (!empty($usulan['tahapan_array'])) {
        foreach ($usulan['tahapan_array'] as $t) { $html .= '<li>' . htmlspecialchars($t) . '</li>'; }
    } else { $html .= '<li>-</li>'; }
    $html .= '</ul>

    <div style="font-weight: bold; margin-bottom: 5px;">F. Indikator Kinerja & Target</div>
    <table class="rab-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="50%">Indikator Keberhasilan</th>
                <th width="25%">Bulan Target</th>
                <th width="20%">Bobot (%)</th>
            </tr>
        </thead>
        <tbody>';
            $totalBobot = 0; 
            if (!empty($usulan['indikator_array'])) {
                foreach ($usulan['indikator_array'] as $i => $row) {
                    $totalBobot += $row['bobot'];
                    $html .= '<tr><td class="center">' . ($i+1) . '</td><td style="font-size:10pt;">' . htmlspecialchars($row['indikator']) . '</td><td class="center">' . htmlspecialchars($row['bulan_target']) . '</td><td class="center">' . $row['bobot'] . '%</td></tr>';
                }
            } else {
                $html .= '<tr><td colspan="4" class="center">Tidak ada indikator kinerja yang dicatat.</td></tr>';
            }
            $html .= '<tr style="font-weight: bold; background-color: #f0f0f0;"><td colspan="3" style="text-align: right;">Total Bobot</td><td class="center">' . $totalBobot . '%</td></tr>
        </tbody>
    </table>

    <pagebreak />

    <div class="section-title">II. Rincian Anggaran Biaya (RAB)</div>
    <div style="font-weight: bold; margin-bottom: 5px; font-size:10pt;">Total Biaya: Rp ' . number_format($usulan['nominal_pencairan'], 0, ',', '.') . ' (MAK: ' . htmlspecialchars($usulan['kode_mak'] ?: '-') . ')</div>
    
    <table class="rab-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="20%">Uraian Belanja</th>
                <th width="6%">Vol 1</th><th width="10%">Sat 1</th><th width="3%"></th>
                <th width="6%">Vol 2</th><th width="10%">Sat 2</th><th width="3%"></th>
                <th width="13%">Harga Satuan</th><th width="13%">Total</th>
            </tr>
        </thead>
        <tbody>';
            $grandTotal = 0; 
            if (!empty($rabRows)) {
                foreach ($rabRows as $i => $row) {
                    $grandTotal += $row['total'];
                    // (Logic Volume & Satuan sama)
                    $v1 = floatval($row['volume_factor_1'] ?? 0); $v2 = floatval($row['volume_factor_2'] ?? 0);
                    $s1 = !empty($row['satuan_factor_1_custom']) ? htmlspecialchars($row['satuan_factor_1_custom']) : htmlspecialchars($row['nama_satuan_f1'] ?? '');
                    $s2 = !empty($row['satuan_factor_2_custom']) ? htmlspecialchars($row['satuan_factor_2_custom']) : htmlspecialchars($row['nama_satuan_f2'] ?? '');
                    if (empty($s1) && $row['volume'] > 0) { $v1 = floatval($row['volume']); $s1 = !empty($row['satuan_custom']) ? htmlspecialchars($row['satuan_custom']) : htmlspecialchars($row['nama_satuan'] ?? ''); }
                    $sep1 = ($v2 > 0) ? 'x' : '';
                    $html .= '<tr>
                        <td class="center">' . ($i+1) . '</td>
                        <td>' . htmlspecialchars($row['deskripsi']) . '<br><span style="font-size: 8px; color: #555;">' . htmlspecialchars($row['nama_kategori']) . '</span></td>
                        <td class="center">' . (($v1 > 0) ? $v1 : '-') . '</td><td class="center">' . $s1 . '</td><td class="center" style="font-weight:bold; color:#666;">' . $sep1 . '</td>
                        <td class="center">' . (($v2 > 0) ? $v2 : '-') . '</td><td class="center">' . (($v2 > 0) ? $s2 : '-') . '</td><td class="center" style="font-weight:bold; color:#666;">x</td>
                        <td class="num">Rp ' . number_format($row['harga_satuan'], 0, ',', '.') . '</td><td class="num">Rp ' . number_format($row['total'], 0, ',', '.') . '</td>
                    </tr>';
                }
            } else { $html .= '<tr><td colspan="10" class="center">Belum ada data RAB.</td></tr>'; }
            if ($grandTotal > 0) { $html .= '<tr style="font-weight: bold; background-color: #f0f0f0;"><td colspan="9" style="text-align: right;">GRAND TOTAL RAB</td><td class="num">Rp ' . number_format($grandTotal, 0, ',', '.') . '</td></tr>'; }
            $html .= '
        </tbody>
    </table>
    
    <div class="section-title">III. Realisasi Keuangan dan Finalisasi</div>
    
    <div style="font-weight: bold; margin-bottom: 5px;">A. Riwayat Pencairan Dana</div>
    <table class="lpj-table">
        <thead>
            <tr><th width="10%">No</th><th width="30%">Tanggal Cair</th><th width="30%">Nominal Dicairkan</th><th width="30%">Batas LPJ</th></tr>
        </thead>
        <tbody>';
            if (empty($pencairanHistory)) { $html .= '<tr><td colspan="4" class="center">Belum ada riwayat pencairan.</td></tr>'; } else {
                foreach ($pencairanHistory as $i => $pc) {
                    $html .= '<tr><td class="center">' . ($i+1) . '</td><td>' . date('d/m/Y', strtotime($pc['tanggal_pencairan'])) . '</td><td class="num">Rp ' . number_format($pc['nominal_dicairkan'], 0, ',', '.') . '</td><td class="center">' . date('d/m/Y', strtotime($pc['tanggal_batas_lpj'])) . '</td></tr>';
                }
            }
            $html .= '
        </tbody>
    </table>

    <div style="font-weight: bold; margin-bottom: 5px;">B. Dokumen LPJ (Bukti Pertanggungjawaban)</div>
    
    <table class="lpj-header-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="30%">Uraian Kegiatan</th>
                <th width="25%">Tanggal Upload</th>
                <th width="20%">Nominal Realisasi</th>
                <th width="20%">Keterangan</th>
            </tr>
        </thead>
    </table>';
            
            if (empty($lpjGrouped)) {
                $html .= '<table class="lpj-item-table"><tbody><tr><td colspan="5" class="center" style="border-top:none;">Belum ada dokumen LPJ yang diunggah.</td></tr></tbody></table>';
            } else {
                $lpjTotal = 0; 
                $counter = 1;
                foreach ($lpjGrouped as $item) {
                    $lpjTotal += $item['nominal'];
                    
                    // --- STRATEGI DIV WRAPPER UNTUK ANTI-PISAH ---
                    $html .= '<div class="lpj-item-wrapper">';
                    $html .= '<table class="lpj-item-table">';
                    
                    // --- Baris 1: DATA ITEM ---
                    $html .= '
                    <tr>
                        <td width="5%" class="center" style="vertical-align: top;">' . $counter++ . '</td>
                        <td width="30%" style="vertical-align: top;">
                            <strong>' . htmlspecialchars($item['uraian']) . '</strong><br>
                            <span style="font-size:8pt; color:#666;">' . htmlspecialchars($item['nama_kategori']) . '</span>
                        </td>
                        <td width="25%" class="center" style="vertical-align: top;">' . htmlspecialchars($item['dates_str']) . '</td>
                        <td width="20%" class="num" style="vertical-align: top;" style="text-align: left;">Rp ' . number_format($item['nominal'], 0, ',', '.') . '</td>
                        <td width="20%" style="vertical-align: top;">' . htmlspecialchars($item['keterangan'] ?? '-') . '</td>
                    </tr>';
                    
                    // --- Baris 2: BUKTI ---
                    $filesHtml = '';
                    if (!empty($item['files'])) {
                         foreach($item['files'] as $fPath) {
                            $absPath = $projectRoot . '/' . $fPath;
                            if (!file_exists($absPath) && strpos($fPath, 'public/') === false) {
                                $absPath = $projectRoot . '/public/' . $fPath;
                            }
                            if (file_exists($absPath)) {
                                $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    $src = $absPath;
                                    $filesHtml .= '<img src="' . $src . '" class="img-evidence" />';
                                } else {
                                    $filesHtml .= '<div class="file-generic">📄 ' . strtoupper($ext) . '</div>';
                                }
                            }
                        }
                    }

                    if (!empty($filesHtml)) {
                        $html .= '
                        <tr class="evidence-row">
                            <td></td>
                            <td colspan="4">
                                <div class="file-label">Bukti Dukung / Dokumentasi:</div>
                                <div class="evidence-gallery">
                                    ' . $filesHtml . '
                                </div>
                            </td>
                        </tr>';
                    }
                    
                    $html .= '</table>'; // Tutup Tabel per Item
                    $html .= '</div>';   // Tutup Wrapper
                }
                
                // TOTAL ROW (Tabel Terpisah agar di bawah)
                if ($lpjTotal > 0) {
                    $html .= '
                    <table class="lpj-item-table">
                        <tr style="font-weight: bold; background-color: #f0f0f0;">
                            <td width="60%" colspan="3" style="text-align: right;">TOTAL LPJ DIUNGGAH</td>
                            <td width="40%" class="num" style="text-align: left;">Rp ' . number_format($lpjTotal, 0, ',', '.') . '</td>
                        </tr>
                    </table>';
                }
            }
            
            $html .= '
    <div style="clear:both;"></div>
    
    <div style="margin-top: 40px; width: 100%;">
        <table class="no-border" style="line-height:1.2;">
            <tr>
                <td width="50%" style="text-align: center;">
                    Mengetahui,<br>
                    Ketua Unit/Jurusan<br><br><br><br>
                    _________________________<br>NIP. .................................
                </td>
                <td width="50%" style="text-align: center;">
                    Depok, ' . $tanggalIndo . '<br>
                    Penanggung Jawab Kegiatan<br><br><br><br>
                    <strong>' . htmlspecialchars($usulan['penanggung_jawab'] ?? $usulan['username']) . '</strong><br>NIP. .................................
                </td>
            </tr>
        </table>
    </div>

</body>
</html>';

echo $html;
?>