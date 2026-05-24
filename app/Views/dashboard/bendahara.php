<?php 
// app/Views/dashboard/bendahara.php
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) { return 'Rp ' . number_format($number, 0, ',', '.'); }
}

// LOGIKA PHP SAMA PERSIS
function deriveDualStatus($row) {
    $totalCair = $row['total_sudah_cair'] ?? 0;
    $nominalRAB = $row['nominal_pencairan'] ?? 0;
    $lpjStatus = trim($row['lpj_status_terkini'] ?? 'Belum Upload');
    $deadline = new DateTime($row['tgl_batas_lpj']);
    $today = new DateTime();
    $isOverdue = $today > $deadline;
    $isFullyCair = ($nominalRAB > 0) && (abs($totalCair - $nominalRAB) < 1);
    $percentage = $nominalRAB > 0 ? round(($totalCair / $nominalRAB) * 100) : 0;
    
    $s_cair = ['text' => 'Menunggu Keputusan', 'class' => 'bg-slate-100 text-slate-700'];
    if ($totalCair < 1) { $s_cair = ['text' => 'Belum Dicairkan', 'class' => 'bg-blue-100 text-blue-700']; } 
    elseif ($isFullyCair) { $s_cair = ['text' => 'Pencairan Selesai', 'detail' => $percentage . '%', 'class' => 'bg-emerald-100 text-emerald-700']; } 
    else { $s_cair = ['text' => 'Cair Bertahap', 'detail' => $percentage . '%', 'class' => 'bg-cyan-100 text-cyan-700']; }

    $s_lpj = ['text' => 'Belum ', 'class' => 'bg-slate-50 text-slate-500'];
    if ($totalCair >= 1) {
        if ($lpjStatus === 'Disetujui') { $s_lpj = ['text' => 'Selesai/Disetujui', 'class' => 'bg-slate-800 text-white']; } 
        elseif ($lpjStatus === 'Revisi') { $s_lpj = ['text' => 'LPJ Revisi', 'class' => 'bg-rose-100 text-rose-700 animate-pulse']; } 
        elseif ($lpjStatus === 'Diajukan') { $s_lpj = ['text' => 'Diajukan (Verifikasi)', 'class' => 'bg-violet-100 text-violet-700']; } 
        elseif ($lpjStatus === 'Belum Upload' && $isOverdue) { $s_lpj = ['text' => 'TELAT LPJ!', 'class' => 'bg-rose-600 text-white animate-pulse']; } 
        elseif ($lpjStatus === 'Belum Upload') { $s_lpj = ['text' => 'Wajib Upload', 'class' => 'bg-amber-100 text-amber-700']; }
    }
    return ['cair' => $s_cair, 'lpj' => $s_lpj];
}
?>

<div class="m-4 md:m-5">
    <div class="mb-6 md:mb-8 text-center md:text-left">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard Bendahara</h1>
        <p class="text-slate-500 mt-1 text-sm md:text-base">Selamat datang, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Bendahara'); ?>. Ringkasan Keuangan Sistem.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-8">
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col justify-center items-center">
            <span class="material-icons text-4xl text-amber-500">payments</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Total Dana Keluar</p>
            <h3 class="text-2xl font-extrabold text-amber-600 mt-1"><?php echo formatRupiah($stats['total_cair'] ?? 0); ?></h3>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col justify-center items-center">
            <span class="material-icons text-4xl text-blue-500">send_to_mobile</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Menunggu Pencairan</p>
            <h3 class="text-3xl font-extrabold text-blue-600 mt-1"><?php echo $stats['count_cair'] ?? 0; ?></h3>
            <a href="/pencairan" class="text-xs text-blue-400 hover:text-blue-600 mt-2 block">Proses Sekarang</a>
        </div>
        
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col justify-center items-center">
            <span class="material-icons text-4xl text-emerald-500">receipt_long</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Menunggu Verif LPJ</p>
            <h3 class="text-3xl font-extrabold text-emerald-600 mt-1"><?php echo $stats['count_lpj'] ?? 0; ?></h3>
            <a href="/lpj" class="text-xs text-emerald-400 hover:text-emerald-600 mt-2 block">Verifikasi Sekarang</a>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-3 md:gap-4 items-end w-full">
            <div class="w-full md:flex-1">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Cari Kegiatan</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 flex items-center">
                        <span class="material-icons text-[16px] leading-none">search</span>
                    </span>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search ?? ''); ?>" placeholder="Nama kegiatan..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm leading-5 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                </div>
            </div>

            <div class="w-full md:w-64">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Filter Pengusul</label>
                <select name="pengusul" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                    <option value="">Semua Pengusul</option>
                    <?php if (isset($listPengusul)): foreach ($listPengusul as $p): ?>
                        <option value="<?= $p['username'] ?>" <?= ($filterProposer ?? '') == $p['username'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['username']) ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="flex-1 md:flex-none px-6 py-2 bg-slate-800 text-white font-bold rounded-lg hover:bg-slate-900 transition-all text-sm shadow-sm">Filter</button>
                <a href="/dashboard" class="flex-1 md:flex-none px-6 py-2 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 transition-all text-sm text-center shadow-sm">Reset</a>
            </div>
        </form>
    </div>
    
    <div class="mt-8">
        <h2 class="text-lg md:text-xl font-bold text-slate-800 mb-4">Progres Semua Kegiatan yang Disetujui WD2</h2>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto w-full custom-scrollbar">
                <table class="w-full text-sm text-left whitespace-nowrap min-w-[800px]">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 w-1/3">Kegiatan</th>
                            <th class="px-6 py-4 text-right">Anggaran Total</th>
                            <th class="px-6 py-4 w-32 text-center">Status Pencairan</th>
                            <th class="px-6 py-4 w-32 text-center">Status LPJ</th>
                            <th class="px-6 py-4 w-32 text-center">Batas LPJ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($usulan)): ?>
                            <tr><td colspan="5" class="p-12 text-center text-slate-500">Data tidak ditemukan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($usulan as $row): 
                                $dualStatus = deriveDualStatus($row);
                                $isOverdue = $dualStatus['lpj']['text'] === 'TELAT LPJ!';
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors <?= $isOverdue ? 'bg-rose-50/40' : '' ?>">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-slate-800 whitespace-normal min-w-[200px]"><?php echo htmlspecialchars($row['nama_kegiatan']); ?></p>
                                    <p class="text-[10px] text-slate-400 uppercase tracking-wider mt-1">Oleh: <?php echo htmlspecialchars($row['username']); ?></p>
                                </td>
                                <td class="px-6 py-4 text-right text-emerald-600 font-bold font-mono"><?php echo formatRupiah($row['nominal_pencairan']); ?></td>
                                <td class="px-6 py-4 text-center"><span class="px-3 py-1 rounded text-[10px] font-bold <?= $dualStatus['cair']['class']; ?>"><?php echo $dualStatus['cair']['text']; ?></span></td>
                                <td class="px-6 py-4 text-center"><span class="px-3 py-1 rounded text-[10px] font-bold <?= $dualStatus['lpj']['class']; ?>"><?php echo $dualStatus['lpj']['text']; ?></span></td>
                                <td class="px-6 py-4 text-center text-xs text-slate-600">
                                     <?php if($dualStatus['cair']['text'] === 'Belum Dicairkan'): ?> - <?php else: ?>
                                        <?php echo !empty($row['tgl_batas_lpj']) ? date('d M Y', strtotime($row['tgl_batas_lpj'])) : '-'; ?>
                                     <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pager) && $pager['total_pages'] > 1): ?>
            <div class="bg-slate-50 border-t border-slate-200 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center sm:text-left">
                    Halaman <?= $pager['current']; ?> dari <?= $pager['total_pages']; ?>
                </div>

                <div class="flex items-center gap-1 sm:gap-2 flex-wrap justify-center">
                    <?php if ($pager['current'] > 1): ?>
                        <a href="?page=1&q=<?= urlencode($search ?? ''); ?>&pengusul=<?= urlencode($filterProposer ?? ''); ?>" 
                           class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                            <span class="material-icons text-sm sm:text-base">first_page</span>
                        </a>
                        <a href="?page=<?= $pager['current'] - 1; ?>&q=<?= urlencode($search ?? ''); ?>&pengusul=<?= urlencode($filterProposer ?? ''); ?>" 
                           class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                            <span class="material-icons text-sm sm:text-base">chevron_left</span>
                        </a>
                    <?php endif; ?>

                    <?php 
                    $start = max(1, $pager['current'] - 2);
                    $end = min($pager['total_pages'], $start + 4);
                    if ($end - $start < 4) $start = max(1, $end - 4);
                    for ($i = $start; $i <= $end; $i++): 
                    ?>
                        <a href="?page=<?= $i; ?>&q=<?= urlencode($search ?? ''); ?>&pengusul=<?= urlencode($filterProposer ?? ''); ?>" 
                           class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-lg font-bold text-xs sm:text-sm transition-all border 
                           <?= $i == $pager['current'] ? 'bg-slate-800 border-slate-800 text-white shadow-lg' : 'bg-white border-slate-200 text-slate-500 hover:border-slate-400 hover:text-slate-800 shadow-sm'; ?>">
                            <?= $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pager['current'] < $pager['total_pages']): ?>
                        <a href="?page=<?= $pager['current'] + 1; ?>&q=<?= urlencode($search ?? ''); ?>&pengusul=<?= urlencode($filterProposer ?? ''); ?>" 
                           class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                            <span class="material-icons text-sm sm:text-base">chevron_right</span>
                        </a>
                        <a href="?page=<?= $pager['total_pages']; ?>&q=<?= urlencode($search ?? ''); ?>&pengusul=<?= urlencode($filterProposer ?? ''); ?>" 
                           class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                            <span class="material-icons text-sm sm:text-base">last_page</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
     </div>
</div> 
<?php include __DIR__.'/../partials/footer.php'; ?>