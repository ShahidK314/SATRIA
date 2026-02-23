<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5; color: #000; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header img { height: 80px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; }
        .title { text-align: center; font-weight: bold; text-decoration: underline; margin: 20px 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 6px 10px; vertical-align: top; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .no-border td { border: none; padding: 4px; }
        .signature { margin-top: 40px; width: 100%; }
        .signature td { border: none; text-align: center; vertical-align: bottom; height: 80px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h2>
        <h2>POLITEKNIK NEGERI JAKARTA</h2>
        <p>Jl. Prof. Dr. G.A. Siwabessy, Kampus Universitas Indonesia Depok 16425</p>
    </div>

    <div class="title">KERANGKA ACUAN KEGIATAN (KAK)</div>

    <table class="no-border">
        <tr><td width="20%">Nama Kegiatan</td><td width="2%">:</td><td><?= htmlspecialchars($usulan['nama_kegiatan']) ?></td></tr>
        <tr><td>Unit Pengusul</td><td>:</td><td><?= htmlspecialchars($usulan['username']) ?></td></tr>
        <tr><td>Tahun Anggaran</td><td>:</td><td><?= date('Y') ?></td></tr>
    </table>

    <table>
        <tr><th width="10%" align="center">NO</th><th>URAIAN</th></tr>
        <tr>
            <td align="center">1</td>
            <td>
                <strong>Gambaran Umum</strong><br>
                <?= nl2br(htmlspecialchars($usulan['gambaran_umum'])) ?>
            </td>
        </tr>
        <tr>
            <td align="center">2</td>
            <td>
                <strong>Penerima Manfaat</strong><br>
                <?= htmlspecialchars($usulan['penerima_manfaat']) ?>
            </td>
        </tr>
        <tr>
            <td align="center">3</td>
            <td>
                <strong>Strategi Pencapaian Keluaran</strong><br>
                <?= nl2br(htmlspecialchars($usulan['strategi_pencapaian_keluaran'])) ?>
            </td>
        </tr>
        <tr>
            <td align="center">4</td>
            <td>
                <strong>Metode Pelaksanaan & Tahapan</strong><br>
                <?php 
                    $metode = $usulan['metode_pelaksanaan'] ? json_decode($usulan['metode_pelaksanaan'], true) : [];
                    $tahapan = $usulan['tahapan_pelaksanaan'] ? json_decode($usulan['tahapan_pelaksanaan'], true) : [];
                    echo "Metode: " . (!empty($metode) ? implode(', ', $metode) : 'N/A') . "<br>";
                    echo "Tahapan: " . (!empty($tahapan) ? implode(', ', $tahapan) : 'N/A');
                ?>
            </td>
        </tr>
        <tr>
            <td align="center">5</td>
            <td>
                <strong>Indikator Kinerja & Target</strong><br>
                <?php $indikator = $usulan['indikator_kinerja'] ? json_decode($usulan['indikator_kinerja'], true) : []; ?>
                <?php if(!empty($indikator)): ?>
                    <ul style="padding-left: 20px; margin: 5px 0;">
                        <?php foreach($indikator as $i): ?>
                            <li><?= htmlspecialchars($i['indikator']) ?> (Target: <?= htmlspecialchars($i['bulan_target']) ?>, Bobot: <?= $i['bobot'] ?>%)</li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td align="center">6</td>
            <td>
                <strong>Anggaran Biaya</strong><br>
                Rp <?= number_format($usulan['nominal_pencairan'], 0, ',', '.') ?> (MAK: <?= htmlspecialchars($usulan['kode_mak'] ?: '-') ?>)
            </td>
        </tr>
    </table>

    <table class="signature">
        <tr>
            <td width="50%">
                Mengetahui,<br>
                Kepala Unit/Jurusan<br><br><br><br>
                _________________________<br>NIP. .................................
            </td>
            <td width="50%">
                Depok, <?= date('d F Y') ?><br>
                Pengusul Kegiatan<br><br><br><br>
                <strong><?= htmlspecialchars($usulan['username']) ?></strong><br>NIP. .................................
            </td>
        </tr>
    </table>
</body>
</html>