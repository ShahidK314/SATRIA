<?php 
// app/Views/keuangan/lpj_riwayat.php
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}
?>

<div class="m-5">
    <div class="mb-6 md:mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Riwayat LPJ Disetujui</h1>
            <p class="text-slate-500 mt-1 text-sm md:text-base">Arsip Laporan Pertanggungjawaban yang telah selesai diverifikasi.</p>
        </div>
        <a href="/lpj" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg transition-colors flex items-center text-sm">
            <span class="material-icons text-sm mr-2">arrow_back</span> Kembali
        </a>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Cari Kegiatan</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <span class="material-icons text-sm">search</span>
                    </span>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                           placeholder="Nama kegiatan..." 
                           class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
            <div class="w-64">
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Filter Pengusul</label>
                <select name="pengusul" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Pengusul</option>
                    <?php foreach ($listPengusul as $p): ?>
                        <option value="<?= $p['username'] ?>" <?= ($filterProposer ?? '') == $p['username'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['username']) ?> (<?= htmlspecialchars($p['nama']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-slate-800 text-white font-bold rounded-lg hover:bg-slate-900 transition-all text-sm">Filter</button>
                <a href="/lpj/riwayat" class="px-6 py-2 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 transition-all text-sm">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <?php if (empty($usulan)): ?>
            <div class="p-12 text-center">
                <span class="material-icons text-slate-300 text-6xl mb-4">history_edu</span>
                <h3 class="text-lg font-bold text-slate-700">Tidak ada data</h3>
                <p class="text-slate-500">Data tidak ditemukan dengan kriteria pencarian tersebut.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Kegiatan</th>
                            <th class="px-6 py-4">Pengusul</th>
                            <th class="px-6 py-4">Tgl Disetujui</th>
                            <th class="px-6 py-4">Total Dana</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($usulan as $row): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800 whitespace-normal min-w-[200px]"><?php echo htmlspecialchars($row['nama_kegiatan']); ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-slate-700 font-medium"><?php echo htmlspecialchars($row['username']); ?></p>
                                <p class="text-[10px] text-slate-500 uppercase tracking-tighter"><?php echo htmlspecialchars($row['nama_jurusan']); ?></p>
                            </td>
                            <td class="px-6 py-4 text-slate-600 text-xs">
                                <?php echo date('d M Y', strtotime($row['updated_at'])); ?>
                            </td>
                            <td class="px-6 py-4 text-emerald-600 font-bold">
                                <?php echo formatRupiah($row['nominal_pencairan'] ?? 0); ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center w-fit mx-auto">
                                    <span class="material-icons text-[10px] mr-1">check_circle</span> Disetujui
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="/pdf/berita_acara?id=<?php echo $row['usulan_id']; ?>" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition-all text-xs shadow-sm group">
                                    <span class="material-icons text-xs mr-1 group-hover:scale-110 transition-transform text-white">download</span> PDF FINAL
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pager) && $pager['total_pages'] > 1): ?>
            <div class="bg-slate-50 border-t border-slate-200 px-6 py-4 flex items-center justify-between">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Halaman <?= $pager['current']; ?> dari <?= $pager['total_pages']; ?>
                </div>

                <div class="flex items-center gap-2">
                    <?php if ($pager['current'] > 1): ?>
                        <a href="?page=1&q=<?= urlencode($search ?? ''); ?>&pengusul=<?= urlencode($filterProposer ?? ''); ?>" 
                           class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                            <span class="material-icons text-base">first_page</span>
                        </a>
                        <a href="?page=<?= $pager['current'] - 1; ?>&q=<?= urlencode($search ?? ''); ?>&pengusul=<?= urlencode($filterProposer ?? ''); ?>" 
                           class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                            <span class="material-icons text-base">chevron_left</span>
                        </a>
                    <?php endif; ?>

                    <?php 
                    $start = max(1, $pager['current'] - 2);
                    $end = min($pager['total_pages'], $start + 4);
                    if ($end - $start < 4) $start = max(1, $end - 4);

                    for ($i = $start; $i <= $end; $i++): 
                    ?>
                        <a href="?page=<?= $i; ?>&q=<?= urlencode($search ?? ''); ?>&pengusul=<?= urlencode($filterProposer ?? ''); ?>" 
                           class="w-10 h-10 flex items-center justify-center rounded-lg font-bold text-sm transition-all border 
                           <?= $i == $pager['current'] 
                               ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-200' 
                               : 'bg-white border-slate-200 text-slate-500 hover:border-slate-400 hover:text-slate-800 shadow-sm'; ?>">
                            <?= $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pager['current'] < $pager['total_pages']): ?>
                        <a href="?page=<?= $pager['current'] + 1; ?>&q=<?= urlencode($search ?? ''); ?>&pengusul=<?= urlencode($filterProposer ?? ''); ?>" 
                           class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                            <span class="material-icons text-base">chevron_right</span>
                        </a>
                        <a href="?page=<?= $pager['total_pages']; ?>&q=<?= urlencode($search ?? ''); ?>&pengusul=<?= urlencode($filterProposer ?? ''); ?>" 
                           class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                            <span class="material-icons text-base">last_page</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div> <?php endif; ?>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>