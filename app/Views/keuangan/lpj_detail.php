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

<div class="m-4 md:m-5 max-w-full">
    
    <div class="mb-6 md:mb-8 flex flex-col md:flex-row justify-between items-start gap-4">
        <div class="w-full md:w-auto">
            <a href="/lpj" class="text-slate-500 hover:text-blue-600 font-bold flex items-center gap-2 mb-4 w-fit">
                <span class="material-icons text-sm">arrow_back</span> Kembali
            </a>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Verifikasi LPJ</h1>
            <p class="text-slate-500 mt-1 text-sm md:text-base">
                Kegiatan: <span class="font-bold text-slate-800 leading-snug"><?php echo htmlspecialchars($lpjData['nama_kegiatan'] ?? '-'); ?></span>
            </p>
        </div>
        <div class="w-full md:w-auto text-left md:text-right bg-white p-4 rounded-xl shadow-sm border border-slate-200">
             <div class="text-[10px] md:text-xs text-slate-400 uppercase font-bold mb-1">Ringkasan Dana</div>
             <div class="text-xs md:text-sm text-slate-600">Total RAB: <span class="font-mono font-bold"><?php echo formatRupiah($grandTotalRab); ?></span></div>
             <div class="text-base md:text-lg text-blue-600 font-bold mt-1">Realisasi: <span class="font-mono font-black"><?php echo formatRupiah($grandTotalReal); ?></span></div>
        </div>
    </div>

    <form action="/lpj/verifikasi" method="POST" id="verifikasiLpjForm" onsubmit="return validateLpjAction(this);">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="lpj_id" value="<?php echo $lpj['id']; ?>">
        <input type="hidden" id="catatan_detail_combined">

        <div class="space-y-6 md:space-y-8 mb-10">
            <?php foreach ($groupedData as $catId => $data): ?>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-3 md:p-4 bg-slate-50 border-b border-slate-100 font-bold text-slate-700 flex flex-col sm:flex-row justify-between text-sm gap-1">
                    <span><?php echo htmlspecialchars($data['nama']); ?></span>
                    <span class="text-slate-500 font-normal text-xs bg-white px-2 py-1 rounded border border-slate-200 w-fit">Target RAB: <strong class="text-slate-700"><?php echo formatRupiah($data['total_rab']); ?></strong></span>
                </div>

                <div class="overflow-x-auto w-full custom-scrollbar pb-2">
                    <table class="w-full text-xs text-left border-collapse min-w-[1000px]">
                        <thead class="bg-white text-slate-500 border-b border-slate-100 uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-3 md:px-4 py-3 w-[20%] align-top font-bold">Uraian</th>
                                <th class="px-2 py-3 w-[4%] text-center align-top font-bold">Vol 1</th>
                                <th class="px-2 py-3 w-[4%] text-center align-top font-bold">Sat 1</th>
                                <th class="px-1 py-3 w-[1%] text-center align-top font-bold"></th>
                                <th class="px-2 py-3 w-[4%] text-center align-top font-bold">Vol 2</th>
                                <th class="px-2 py-3 w-[4%] text-center align-top font-bold">Sat 2</th>
                                <th class="px-3 py-3 w-[10%] text-right align-top font-bold">Harga Sat</th>
                                <th class="px-3 py-3 w-[10%] text-right align-top font-bold">Total RAB</th>
                                <th class="px-3 py-3 w-[14%] align-top font-bold">Realisasi (Rp)</th>
                                <th class="px-3 py-3 w-[14%] align-top font-bold">Keterangan</th>
                                <th class="px-3 md:px-4 py-3 w-[15%] align-top font-bold">Bukti & Revisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach ($data['items'] as $item): 
                                $match = $item['is_match'];
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                
                                <td class="px-3 md:px-4 py-3 font-bold text-slate-800 align-top whitespace-normal break-words leading-relaxed">
                                    <?php echo htmlspecialchars($item['deskripsi']); ?>
                                </td>
                                
                                <td class="px-2 py-3 text-center align-top text-slate-600 pt-3"><?php echo floatval($item['volume_factor_1']); ?></td>
                                <td class="px-2 py-3 text-center align-top text-slate-600 text-[10px] uppercase pt-3"><?php echo htmlspecialchars($item['nama_satuan_1'] ?? '-'); ?></td>
                                <td class="px-1 py-3 text-center align-top text-slate-300 pt-3 font-bold">x</td>
                                <td class="px-2 py-3 text-center align-top text-slate-600 pt-3"><?php echo floatval($item['volume_factor_2']); ?></td>
                                <td class="px-2 py-3 text-center align-top text-slate-600 text-[10px] uppercase pt-3"><?php echo htmlspecialchars($item['nama_satuan_2'] ?? '-'); ?></td>

                                <td class="px-3 py-3 text-right align-top text-slate-600 whitespace-nowrap pt-3">
                                    <?php echo formatRupiah($item['harga_satuan']); ?>
                                </td>
                                <td class="px-3 py-3 text-right align-top font-mono font-bold text-slate-800 whitespace-nowrap pt-3">
                                    <?php echo formatRupiah($item['nominal_rab']); ?>
                                </td>
                                
                                <td class="px-3 py-3 align-top bg-slate-50/50">
                                    <div class="font-bold font-mono text-slate-900 text-sm leading-none pt-0.5">
                                        <?php echo formatRupiah($item['total_realisasi']); ?>
                                    </div>
                                    <div class="mt-2">
                                        <?php if ($match): ?>
                                            <span class="text-[9px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full font-bold flex items-center w-fit border border-emerald-200">
                                                <span class="material-icons text-[10px] mr-1">check_circle</span> Sesuai
                                            </span>
                                        <?php else: ?>
                                            <span class="text-[9px] bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded-full font-bold flex items-center w-fit border border-rose-200">
                                                <span class="material-icons text-[10px] mr-1">warning</span> Selisih
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="px-3 py-3 text-slate-600 italic text-[11px] md:text-xs align-top leading-relaxed pt-3 whitespace-normal">
                                    <?php echo nl2br(htmlspecialchars($item['keterangan'] ?: '-')); ?>
                                </td>

                                <td class="px-3 md:px-4 py-3 bg-slate-50/50 border-l border-slate-100 align-top">
                                    <div class="space-y-2">
                                        <?php 
                                        $validFiles = array_filter($item['uploaded_files'], fn($f) => !empty($f['file_path']));
                                        if (empty($validFiles)): ?>
                                            <span class="text-[10px] text-slate-400 italic bg-slate-100 px-2 py-1 rounded block w-fit">Tidak ada bukti diunggah</span>
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
                                                <div class="p-2 bg-white border border-slate-200 rounded-lg shadow-sm text-xs group hover:border-blue-300 transition-colors">
                                                    <a href="<?php echo $file['file_path']; ?>" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1.5 mb-2 break-words">
                                                        <span class="material-icons text-[14px] text-slate-400 flex-shrink-0">description</span>
                                                        <span class="font-medium leading-tight"><?php echo htmlspecialchars($displayName); ?></span>
                                                        <span class="material-icons text-[12px] ml-auto text-slate-300">open_in_new</span>
                                                    </a>
                                                    
                                                    <?php if ($isApproved): ?>
                                                        <?php if($noteSaved): ?>
                                                            <div class="bg-amber-50 text-amber-700 p-1.5 rounded text-[10px] leading-snug">
                                                                "<?php echo htmlspecialchars($noteSaved); ?>"
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <textarea name="catatan_detail[<?php echo $docIdKey; ?>]" rows="1" 
                                                          class="w-full px-2 py-1.5 border border-slate-300 rounded text-[11px] catatan-input focus:border-amber-400 focus:ring-1 focus:ring-amber-400 resize-y bg-slate-50 focus:bg-white transition-colors" 
                                                          placeholder="Ketik catatan revisi di sini..."><?php echo htmlspecialchars($noteSaved); ?></textarea>
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

        <div class="mt-8 bg-white p-4 md:p-6 rounded-xl shadow-lg border border-blue-200 mb-20 sticky bottom-4 md:static z-20">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center hidden md:flex">
                <span class="material-icons mr-2 text-blue-600">gavel</span> Keputusan Verifikasi LPJ
            </h3>
            <div class="flex flex-col sm:flex-row justify-end gap-3">
                <?php if (!$isApproved): ?>
                    <button type="submit" name="aksi" value="revisi" class="w-full sm:w-auto px-6 py-3 md:py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg shadow-md transition flex items-center justify-center text-sm" 
                            onclick="document.activeElement.value='revisi';">
                        <span class="material-icons text-base mr-2">undo</span> Minta Revisi
                    </button>
                    
                    <button type="submit" name="aksi" value="setuju" class="w-full sm:w-auto px-6 py-3 md:py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-md transition flex items-center justify-center text-sm"
                            onclick="document.activeElement.value='setuju';">
                        <span class="material-icons text-base mr-2">check_circle</span> Setujui LPJ
                    </button>
                <?php else: ?>
                    <div class="w-full sm:w-auto px-4 py-3 bg-emerald-50 text-emerald-700 font-bold rounded-lg flex items-center justify-center border border-emerald-200">
                        <span class="material-icons mr-2 text-base">lock</span> LPJ Sudah Disetujui
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
        return confirm('Yakin setujui LPJ ini? Data akan dikunci dan dianggap selesai.');
    }
    
    if (action === 'revisi') {
        if (!hasNotes) {
            alert('Untuk meminta revisi, Anda WAJIB memberikan catatan pada kolom "Bukti & Revisi" minimal satu file.');
            return false;
        }
        return confirm('Kembalikan LPJ ke pengusul untuk direvisi?');
    }
    
    return true;
}
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>