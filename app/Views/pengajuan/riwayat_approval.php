<?php 
// app/Views/pengajuan/riwayat_approval.php
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

$backUrl = ($title === 'Riwayat Persetujuan PPK') ? '/pengajuan/ppk' : '/pengajuan/wd2';
$approvalRole = ($title === 'Riwayat Persetujuan PPK') ? 'PPK' : 'WD2';

$queryParams = $_GET;
unset($queryParams['page']);
$baseUrl = '?' . http_build_query($queryParams);
?>

<div class="m-4 md:m-5">

    <div class="mb-6 md:mb-8 w-full max-w-full">
        <a href="<?php echo $backUrl; ?>" 
           class="text-slate-500 hover:text-blue-600 font-bold flex items-center gap-2 mb-4 w-fit transition-colors">
            <span class="material-icons text-sm">arrow_back</span> 
            Kembali ke Antrian <?php echo $approvalRole; ?>
        </a>
        
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4 w-full">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">
                    <?php echo htmlspecialchars($title ?? 'Riwayat Approval'); ?>
                </h1>
                <p class="text-slate-500 mt-1 text-sm md:text-base">
                    Total Data: <b><?php echo number_format($pager['total_items'] ?? 0); ?></b> Kegiatan
                </p>
            </div>

            <form method="GET" class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto items-stretch sm:items-center">
                <select name="pengusul" class="w-full sm:w-auto border border-slate-300 rounded-lg px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm truncate">
                    <option value="">- Semua Pengusul -</option>
                    <?php if (!empty($listPengusul)): ?>
                        <?php foreach ($listPengusul as $p): ?>
                            <option value="<?php echo $p['username']; ?>" 
                                <?php echo (isset($_GET['pengusul']) && $_GET['pengusul'] === $p['username']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <div class="relative w-full sm:w-64">
                    <input type="text" name="q" 
                           value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" 
                           placeholder="Cari nama kegiatan..." 
                           class="w-full border border-slate-300 rounded-lg pl-4 pr-10 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm">
                    <button type="submit" class="absolute right-2 top-1.5 text-slate-400 hover:text-blue-600 p-1">
                        <span class="material-icons text-lg">search</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($riwayat)): ?>
        <div class="bg-white rounded-xl p-8 md:p-12 text-center shadow-sm border border-slate-200">
            <span class="material-icons text-slate-300 text-5xl md:text-6xl mb-4">archive</span>
            <h3 class="text-base md:text-lg font-bold text-slate-700">Data Tidak Ditemukan</h3>
            <p class="text-slate-500 text-sm mt-1">Coba ubah kata kunci pencarian atau filter Anda.</p>
            <?php if (!empty($_GET['q']) || !empty($_GET['pengusul'])): ?>
                <a href="?q=" class="mt-4 inline-block text-blue-600 font-bold hover:underline text-sm">Reset Filter</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="overflow-x-auto w-full custom-scrollbar">
                <table class="w-full text-sm text-left whitespace-nowrap min-w-[800px]">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] md:text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-4 md:px-6 py-4 w-1/3">Kegiatan</th>
                            <th class="px-4 md:px-6 py-4">Pengusul</th>
                            <th class="px-4 md:px-6 py-4">Nominal</th>
                            <th class="px-4 md:px-6 py-4 text-center">Tanggal Putusan</th>
                            <th class="px-4 md:px-6 py-4 text-left">Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($riwayat as $row): 
                            $rekomendasi = ($title === 'Riwayat Persetujuan PPK') 
                                ? ($row['rekomendasi_ppk'] ?? '-') 
                                : ($row['rekomendasi_wd2'] ?? '-');
                            
                            $tglPutusan = ($title === 'Riwayat Persetujuan PPK') 
                                ? ($row['tgl_status_ppk'] ?? null) 
                                : ($row['tgl_status_wd2'] ?? null);
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 md:px-6 py-4">
                                <p class="font-bold text-slate-800 whitespace-normal min-w-[200px] leading-snug">
                                    <?php echo htmlspecialchars($row['nama_kegiatan']); ?>
                                </p>
                            </td>
                            <td class="px-4 md:px-6 py-4">
                                <p class="text-slate-700 font-medium"><?php echo htmlspecialchars($row['username']); ?></p>
                                <p class="text-[11px] text-slate-500 mt-0.5"><?php echo htmlspecialchars($row['nama_jurusan']); ?></p>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-emerald-600 font-bold font-mono">
                                <?php echo formatRupiah($row['nominal_pencairan']); ?>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-center text-xs text-slate-600">
                                <?php echo $tglPutusan ? date('d M Y H:i', strtotime($tglPutusan)) : '-'; ?>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-left text-xs text-slate-600 whitespace-normal min-w-[200px] leading-relaxed italic">
                                <?php echo htmlspecialchars($rekomendasi); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pager) && $pager['total_pages'] > 1): ?>
            <div class="px-4 md:px-6 py-4 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-3 bg-slate-50">
                <div class="text-xs text-slate-500">
                    Halaman <b><?php echo $pager['current']; ?></b> dari <b><?php echo $pager['total_pages']; ?></b>
                </div>
                
                <div class="flex flex-wrap justify-center gap-1 sm:gap-2">
                    <?php if ($pager['current'] > 1): ?>
                        <a href="<?php echo $baseUrl . '&page=' . ($pager['current'] - 1); ?>" 
                           class="px-3 py-1.5 bg-white border border-slate-300 rounded hover:bg-slate-100 text-slate-600 text-xs font-bold shadow-sm transition-colors">
                           &laquo; Prev
                        </a>
                    <?php endif; ?>

                    <?php 
                    $start = max(1, $pager['current'] - 2);
                    $end = min($pager['total_pages'], $pager['current'] + 2);
                    
                    for ($i = $start; $i <= $end; $i++): 
                        $activeClass = ($i == $pager['current']) 
                            ? 'bg-blue-600 text-white border-blue-600 shadow-sm' 
                            : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-100 shadow-sm transition-colors';
                    ?>
                        <a href="<?php echo $baseUrl . '&page=' . $i; ?>" 
                           class="px-3 py-1.5 border rounded text-xs font-bold <?php echo $activeClass; ?>">
                           <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pager['current'] < $pager['total_pages']): ?>
                        <a href="<?php echo $baseUrl . '&page=' . ($pager['current'] + 1); ?>" 
                           class="px-3 py-1.5 bg-white border border-slate-300 rounded hover:bg-slate-100 text-slate-600 text-xs font-bold shadow-sm transition-colors">
                           Next &raquo;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            </div>
    <?php endif; ?>

</div>

<?php include __DIR__.'/../partials/footer.php'; ?>