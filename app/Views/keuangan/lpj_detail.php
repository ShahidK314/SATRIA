<?php 
// app/Views/keuangan/lpj_detail.php
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) { function formatRupiah($n) { return 'Rp ' . number_format($n, 0, ',', '.'); } }

$groupedData = [];
$grandTotalRab = 0;
$grandTotalReal = 0;

foreach ($rabItems as $item) {
    $catId = $item['kategori_id'];
    if (!isset($groupedData[$catId])) {
        $groupedData[$catId] = [
            'nama' => $item['nama_kategori'],
            'items' => [],
            'total_rab' => 0
        ];
    }
    $item['is_match'] = abs($item['total_realisasi'] - $item['nominal_rab']) <= 1;
    
    $groupedData[$catId]['items'][] = $item;
    $groupedData[$catId]['total_rab'] += $item['nominal_rab'];
    $grandTotalRab += $item['nominal_rab'];
    $grandTotalReal += $item['total_realisasi'];
}

$savedCatatan = $lpj['catatan_bendahara'] ?? '';
$catatanDetailArray = json_decode($savedCatatan, true) ?? [];
$isApproved = ($lpj['status_terkini'] ?? '') === 'Disetujui';
?>

<div class="m-5 max-w-full">
    
    <div class="mb-8 flex justify-between items-start">
        <div>
            <a href="/lpj" class="text-slate-500 hover:text-blue-600 font-bold flex items-center gap-2 mb-4">
                <span class="material-icons text-sm">arrow_back</span> Kembali
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900">Verifikasi LPJ</h1>
            <p class="text-slate-500 mt-1 text-sm">
                Kegiatan: <span class="font-bold text-slate-800"><?php echo htmlspecialchars($lpjData['nama_kegiatan'] ?? '-'); ?></span>
            </p>
        </div>
        <div class="text-right bg-white p-4 rounded-lg shadow-sm border border-slate-200">
             <div class="text-xs text-slate-400 uppercase font-bold mb-1">Ringkasan Dana</div>
             <div class="text-sm text-slate-600">Total RAB: <span class="font-mono font-bold"><?php echo formatRupiah($grandTotalRab); ?></span></div>
             <div class="text-lg text-blue-600">Realisasi: <span class="font-mono font-bold"><?php echo formatRupiah($grandTotalReal); ?></span></div>
        </div>
    </div>

    <form action="/lpj/verifikasi" method="POST" id="verifikasiLpjForm" onsubmit="return validateLpjAction(this);">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="lpj_id" value="<?php echo $lpj['id']; ?>">
        <input type="hidden" id="catatan_detail_combined">

        <div class="space-y-8 mb-10">
            <?php foreach ($groupedData as $catId => $data): ?>
            <div class="bg-white rounded-lg shadow border border-slate-200 overflow-hidden">
                <div class="p-3 bg-slate-50 border-b border-slate-100 font-bold text-slate-700 flex justify-between text-sm">
                    <span><?php echo htmlspecialchars($data['nama']); ?></span>
                    <span class="text-slate-500 font-normal text-xs">Target: <?php echo formatRupiah($data['total_rab']); ?></span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead class="bg-white text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-3 py-3 w-[20%] align-top">Uraian</th>
                                <th class="px-2 py-3 w-[4%] text-center align-top">Vol 1</th>
                                <th class="px-2 py-3 w-[4%] text-center align-top">Sat 1</th>
                                <th class="px-1 py-3 w-[1%] text-center align-top"></th>
                                <th class="px-2 py-3 w-[4%] text-center align-top">Vol 2</th>
                                <th class="px-2 py-3 w-[4%] text-center align-top">Sat 2</th>
                                <th class="px-3 py-3 w-[10%] text-right align-top">Harga Sat</th>
                                <th class="px-3 py-3 w-[10%] text-right align-top">Total RAB</th>
                                <th class="px-3 py-3 w-[14%] align-top">Realisasi (Rp)</th>
                                <th class="px-3 py-3 w-[14%] align-top">Keterangan</th>
                                <th class="px-3 py-3 w-[15%] align-top">Bukti & Revisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach ($data['items'] as $item): 
                                $match = $item['is_match'];
                            ?>
                            <tr class="hover:bg-slate-50">
                                
                                <td class="px-3 py-3 font-bold text-slate-800 align-top whitespace-normal break-words leading-relaxed">
                                    <?php echo htmlspecialchars($item['deskripsi']); ?>
                                </td>
                                
                                <td class="px-2 py-3 text-center align-top text-slate-600 pt-3"><?php echo floatval($item['volume_factor_1']); ?></td>
                                <td class="px-2 py-3 text-center align-top text-slate-600 text-[10px] uppercase pt-3"><?php echo htmlspecialchars($item['nama_satuan_1'] ?? '-'); ?></td>
                                <td class="px-1 py-3 text-center align-top text-slate-300 pt-3">x</td>
                                <td class="px-2 py-3 text-center align-top text-slate-600 pt-3"><?php echo floatval($item['volume_factor_2']); ?></td>
                                <td class="px-2 py-3 text-center align-top text-slate-600 text-[10px] uppercase pt-3"><?php echo htmlspecialchars($item['nama_satuan_2'] ?? '-'); ?></td>

                                <td class="px-3 py-3 text-right align-top text-slate-600 whitespace-nowrap pt-3">
                                    <?php echo formatRupiah($item['harga_satuan']); ?>
                                </td>
                                <td class="px-3 py-3 text-right align-top font-mono font-bold text-emerald-600 whitespace-nowrap pt-3">
                                    <?php echo formatRupiah($item['nominal_rab']); ?>
                                </td>
                                
                                <td class="px-3 py-3 align-top">
                                    <div class="font-bold font-mono text-slate-800 text-sm leading-none pt-0.5">
                                        <?php echo formatRupiah($item['total_realisasi']); ?>
                                    </div>
                                    <div class="mt-2">
                                        <?php if ($match): ?>
                                            <span class="text-[9px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full font-bold flex items-center w-fit">
                                                <span class="material-icons text-[10px] mr-1">check_circle</span> Sesuai
                                            </span>
                                        <?php else: ?>
                                            <span class="text-[9px] bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded-full font-bold flex items-center w-fit">
                                                <span class="material-icons text-[10px] mr-1">warning</span> Selisih
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="px-3 py-3 text-slate-600 italic text-xs align-top leading-relaxed pt-3">
                                    <?php echo nl2br(htmlspecialchars($item['keterangan'] ?: '-')); ?>
                                </td>

                                <td class="px-3 py-3 bg-slate-50/50 border-l border-slate-100 align-top">
                                    <div class="space-y-2">
                                        <?php 
                                        $validFiles = array_filter($item['uploaded_files'], fn($f) => !empty($f['file_path']));
                                        if (empty($validFiles)): ?>
                                            <span class="text-[10px] text-slate-400 italic">Tidak ada bukti</span>
                                        <?php else: ?>
                                            <?php 
                                            // Re-index
                                            $validFiles = array_values($validFiles);
                                            
                                            foreach ($validFiles as $index => $file): 
                                                $docIdKey = 'dokumen_' . $file['id'];
                                                $noteSaved = $catatanDetailArray[$docIdKey] ?? '';

                                                // NAMA FILE = Uraian
                                                $displayName = $item['deskripsi'];
                                                if (count($validFiles) > 1) {
                                                    $displayName .= " (" . ($index + 1) . ")";
                                                }
                                            ?>
                                                <div class="p-1.5 bg-white border border-slate-200 rounded shadow-sm text-xs group hover:border-blue-300 transition-colors">
                                                    <a href="<?php echo $file['file_path']; ?>" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1 mb-1 break-words">
                                                        <span class="material-icons text-[12px] text-slate-400 flex-shrink-0">description</span>
                                                        <span><?php echo htmlspecialchars($displayName); ?></span>
                                                        <span class="material-icons text-[10px] ml-auto text-slate-300">open_in_new</span>
                                                    </a>
                                                    
                                                    <?php if ($isApproved): ?>
                                                        <?php if($noteSaved): ?>
                                                            <div class="bg-amber-50 text-amber-700 p-1 rounded border border-amber-100 italic text-[10px]">
                                                                "<?php echo htmlspecialchars($noteSaved); ?>"
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <textarea name="catatan_detail[<?php echo $docIdKey; ?>]" rows="1" 
                                                          class="w-full px-2 py-1 border border-slate-300 rounded text-[10px] catatan-input focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none" 
                                                          placeholder="Catatan revisi..."><?php echo htmlspecialchars($noteSaved); ?></textarea>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-8 bg-white p-6 rounded-lg shadow border border-slate-200 mb-20">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <span class="material-icons mr-2">gavel</span> Keputusan Verifikasi
            </h3>
            <div class="flex justify-end gap-4">
                <?php if (!$isApproved): ?>
                    <button type="submit" name="aksi" value="revisi" class="px-6 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg shadow transition flex items-center" 
                            onclick="document.activeElement.value='revisi';">
                        <span class="material-icons text-sm mr-2">undo</span> Minta Revisi
                    </button>
                    
                    <button type="submit" name="aksi" value="setuju" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow transition flex items-center"
                            onclick="document.activeElement.value='setuju';">
                        <span class="material-icons text-sm mr-2">check_circle</span> Setujui LPJ
                    </button>
                <?php else: ?>
                    <div class="px-4 py-2 bg-emerald-100 text-emerald-700 font-bold rounded-lg flex items-center border border-emerald-300">
                        <span class="material-icons mr-2">lock</span> LPJ Sudah Disetujui
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<script>
function validateLpjAction(form) {
    const action = document.activeElement.getAttribute('value');
    const allDetailInputs = document.querySelectorAll('.catatan-input');
    let hasNotes = false;

    allDetailInputs.forEach(input => {
        if (!input.readOnly && input.value.trim() !== "") hasNotes = true;
    });

    if (action === 'setuju') {
        let totalRab = <?php echo $grandTotalRab; ?>;
        let totalReal = <?php echo $grandTotalReal; ?>;
        if (Math.abs(totalReal - totalRab) > 1) {
            alert('GAGAL: Total Realisasi tidak sama dengan RAB. Tidak bisa disetujui.');
            return false;
        }
        return confirm('Yakin setujui LPJ ini? Data akan dikunci.');
    }
    
    if (action === 'revisi') {
        if (!hasNotes) {
            alert('Untuk meminta revisi, Anda WAJIB memberikan catatan pada minimal satu file bukti di tabel.');
            return false;
        }
        return confirm('Kembalikan LPJ ke pengusul untuk direvisi?');
    }
    
    return true;
}
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>