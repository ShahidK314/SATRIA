<?php 
// app/Views/pengajuan/riwayat_approval.php (FINAL: Added Search Filter & Pagination)
include __DIR__.'/../partials/sidebar.php'; 

// 1. Helper Function (Format Rupiah)
if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

// 2. Setup Variable Dasar
$backUrl = ($title === 'Riwayat Persetujuan PPK') ? '/pengajuan/ppk' : '/pengajuan/wd2';
$approvalRole = ($title === 'Riwayat Persetujuan PPK') ? 'PPK' : 'WD2';

// 3. Helper untuk membuat Link Pagination (Mempertahankan Filter Pencarian)
// Ini agar saat pindah ke halaman 2, pencarian "Workshop" tidak hilang.
$queryParams = $_GET;
unset($queryParams['page']); // Hapus page lama
$baseUrl = '?' . http_build_query($queryParams);
?>

<div class="m-5">

    <div class="mb-6 max-w-7xl">
        <a href="<?php echo $backUrl; ?>" 
           class="text-slate-500 hover:text-blue-600 font-bold flex items-center gap-2 mb-4 transition-colors">
            <span class="material-icons text-sm">arrow_back</span> 
            Kembali ke Antrian <?php echo $approvalRole; ?>
        </a>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                    <?php echo htmlspecialchars($title ?? 'Riwayat Approval'); ?>
                </h1>
                <p class="text-slate-500 mt-1">
                    Total Data: <b><?php echo number_format($pager['total_items'] ?? 0); ?></b> Kegiatan
                </p>
            </div>

            <form method="GET" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <select name="pengusul" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
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

                <div class="relative">
                    <input type="text" name="q" 
                           value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" 
                           placeholder="Cari nama kegiatan..." 
                           class="border border-slate-300 rounded-lg pl-3 pr-10 py-2 text-sm w-full sm:w-64 focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="absolute right-2 top-2 text-slate-400 hover:text-blue-600">
                        <span class="material-icons text-lg">search</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($riwayat)): ?>
        <div class="bg-white rounded-xl p-12 text-center shadow-sm border border-slate-200 max-w-7xl">
            <span class="material-icons text-slate-300 text-6xl mb-4">archive</span>
            <h3 class="text-lg font-bold text-slate-700">Data Tidak Ditemukan</h3>
            <p class="text-slate-500">Coba ubah kata kunci pencarian atau filter Anda.</p>
            <?php if (!empty($_GET['q']) || !empty($_GET['pengusul'])): ?>
                <a href="?q=" class="mt-4 inline-block text-blue-600 font-bold hover:underline">Reset Filter</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden max-w-7xl mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Kegiatan</th>
                            <th class="px-6 py-4">Pengusul</th>
                            <th class="px-6 py-4">Nominal</th>
                            <th class="px-6 py-4 text-center">Tanggal Putusan</th>
                            <th class="px-6 py-4 text-right">Rekomendasi</th>
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
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800 whitespace-normal min-w-[200px]">
                                    <?php echo htmlspecialchars($row['nama_kegiatan']); ?>
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-slate-700"><?php echo htmlspecialchars($row['username']); ?></p>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($row['nama_jurusan']); ?></p>
                            </td>
                            <td class="px-6 py-4 text-emerald-600 font-bold">
                                <?php echo formatRupiah($row['nominal_pencairan']); ?>
                            </td>
                            <td class="px-6 py-4 text-center text-xs text-slate-600">
                                <?php echo $tglPutusan ? date('d M Y H:i', strtotime($tglPutusan)) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-slate-600 min-w-[150px]">
                                <?php echo htmlspecialchars(substr($rekomendasi, 0, 40)) . (strlen($rekomendasi) > 40 ? '...' : ''); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pager) && $pager['total_pages'] > 1): ?>
            <div class="px-6 py-4 border-t border-slate-200 flex justify-between items-center bg-slate-50">
                <div class="text-xs text-slate-500">
                    Halaman <b><?php echo $pager['current']; ?></b> dari <b><?php echo $pager['total_pages']; ?></b>
                </div>
                
                <div class="flex gap-1">
                    <?php if ($pager['current'] > 1): ?>
                        <a href="<?php echo $baseUrl . '&page=' . ($pager['current'] - 1); ?>" 
                           class="px-3 py-1 bg-white border border-slate-300 rounded hover:bg-slate-100 text-slate-600 text-xs font-bold">
                           &laquo; Prev
                        </a>
                    <?php endif; ?>

                    <?php 
                    $start = max(1, $pager['current'] - 2);
                    $end = min($pager['total_pages'], $pager['current'] + 2);
                    
                    for ($i = $start; $i <= $end; $i++): 
                        $activeClass = ($i == $pager['current']) 
                            ? 'bg-blue-600 text-white border-blue-600' 
                            : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-100';
                    ?>
                        <a href="<?php echo $baseUrl . '&page=' . $i; ?>" 
                           class="px-3 py-1 border rounded text-xs font-bold <?php echo $activeClass; ?>">
                           <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pager['current'] < $pager['total_pages']): ?>
                        <a href="<?php echo $baseUrl . '&page=' . ($pager['current'] + 1); ?>" 
                           class="px-3 py-1 bg-white border border-slate-300 rounded hover:bg-slate-100 text-slate-600 text-xs font-bold">
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