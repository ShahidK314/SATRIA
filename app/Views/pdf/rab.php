<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header { text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 15px; text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        th { background-color: #ccc; text-align: center; font-weight: bold; padding: 4px; border: 1px solid #000; font-size: 9px; }
        td { border: 1px solid #000; padding: 4px; vertical-align: middle; word-wrap: break-word; }
        .num { text-align: right; }
        .center { text-align: center; }
        .total-row { background-color: #f0f0f0; font-weight: bold; }
        .sep { border-left: none; border-right: none; color: #555; font-weight: bold; text-align: center;}
    </style>
</head>
<body>
    <div class="header">RENCANA ANGGARAN BIAYA (RAB)</div>
    
    <div style="margin-bottom: 15px;">
        <strong>Nama Kegiatan:</strong> <?= htmlspecialchars($usulan['nama_kegiatan']) ?><br>
        <strong>Kode MAK:</strong> <?= htmlspecialchars($usulan['kode_mak'] ?: '-') ?><br>
        <strong>Total RAB:</strong> Rp <?= number_format($usulan['nominal_pencairan'], 0, ',', '.') ?>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="20%">Uraian Belanja</th>
                
                <th width="6%">Vol 1</th>
                <th width="10%">Sat 1</th>
                <th width="3%"></th> <th width="6%">Vol 2</th>
                <th width="10%">Sat 2</th>
                <th width="3%"></th> <th width="13%">Harga Satuan</th>
                <th width="13%">Total</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $grandTotal = 0;
        foreach ($rabRows as $i => $row): 
            $grandTotal += $row['total'];
            
            // 1. Ambil Volume
            $v1 = floatval($row['volume_factor_1'] ?? 0);
            $v2 = floatval($row['volume_factor_2'] ?? 0);

            // 2. LOGIKA PERBAIKAN SATUAN:
            // Cek apakah ada inputan custom? Jika ya, pakai itu. Jika tidak, pakai dari master.
            
            // Satuan 1
            if (!empty($row['satuan_factor_1_custom'])) {
                $s1 = htmlspecialchars($row['satuan_factor_1_custom']);
            } else {
                $s1 = htmlspecialchars($row['nama_satuan_f1'] ?? '');
            }

            // Satuan 2
            if (!empty($row['satuan_factor_2_custom'])) {
                $s2 = htmlspecialchars($row['satuan_factor_2_custom']);
            } else {
                $s2 = htmlspecialchars($row['nama_satuan_f2'] ?? '');
            }
            
            // 3. Fallback jika data lama (single volume/satuan tunggal)
            // Jika Satuan 1 kosong tapi Volume utama ada
            if (empty($s1) && $row['volume'] > 0) {
                $v1 = floatval($row['volume']);
                
                // Cek custom satuan tunggal
                if (!empty($row['satuan_custom'])) {
                    $s1 = htmlspecialchars($row['satuan_custom']);
                } else {
                    $s1 = htmlspecialchars($row['nama_satuan'] ?? '');
                }
            }
        ?>
            <tr>
                <td class="center"><?= $i+1 ?></td>
                <td>
                    <?= htmlspecialchars($row['deskripsi']) ?><br>
                    <span style="font-size: 8px; color: #555;"><?= htmlspecialchars($row['nama_kategori']) ?></span>
                </td>
                
                <td class="center"><?= $v1 > 0 ? $v1 : '-' ?></td>
                <td class="center"><?= $s1 ?></td>
                
                <td class="center" style="color:#777; font-weight:bold;">
                    <?= ($v2 > 0) ? 'x' : '' ?>
                </td>
                
                <td class="center"><?= $v2 > 0 ? $v2 : '-' ?></td>
                <td class="center"><?= $v2 > 0 ? $s2 : '-' ?></td>
                
                <td class="center" style="color:#777; font-weight:bold;">x</td>

                <td class="num">Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
                <td class="num">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
            </tr>
        <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="9" style="text-align: right; padding-right: 10px;">GRAND TOTAL</td>
                <td class="num">Rp <?= number_format($grandTotal, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>
</body>
</html>