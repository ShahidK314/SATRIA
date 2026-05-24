<?php 
// app/Views/pengajuan/index_pengajuan.php
include __DIR__.'/../partials/sidebar.php'; 
// Asumsi variabel: $usulan, $stats, $pager

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

if (!function_exists('getStatusClass')) {
    function getStatusClass($status) {
        return match($status) {
            'Draft' => 'bg-amber-100 text-amber-700',
            'Diajukan' => 'bg-blue-100 text-blue-700',
            'Revisi' => 'bg-rose-100 text-rose-700',
            'Ditolak' => 'bg-slate-800 text-white',
            'Disetujui' => 'bg-emerald-100 text-emerald-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}
?>

<div class="m-4 md:m-5">
    <div class="mb-6 md:mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Pengajuan Usulan Kegiatan</h1>
        <p class="text-slate-500 mt-1 text-sm md:text-base">Kelola dan lacak status usulan KAK/IKU/RAB yang Anda buat.</p>
    </div>
    
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 md:gap-6 mb-6 md:mb-8">
        <div class="bg-white p-4 md:p-5 rounded-xl shadow-sm border border-slate-200 text-center">
            <p class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-wider">Draft</p>
            <h3 class="text-2xl md:text-3xl font-extrabold text-amber-600 mt-1"><?php echo $stats['draft'] ?? 0; ?></h3>
        </div>
        <div class="bg-white p-4 md:p-5 rounded-xl shadow-sm border border-slate-200 text-center">
            <p class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-wider">Diajukan</p>
            <h3 class="text-2xl md:text-3xl font-extrabold text-blue-600 mt-1"><?php echo $stats['diajukan'] ?? 0; ?></h3>
        </div>
        </div>
    
    <div class="mb-6 flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto flex-1">
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><span class="material-icons text-sm">search</span></span>
                <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" placeholder="Cari Kegiatan..." class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm outline-none shadow-sm">
            </div>
            <div class="relative w-full sm:w-48">
                <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm appearance-none bg-white cursor-pointer shadow-sm outline-none">
                    <option value="">Semua Status</option>
                    <?php $statuses = ['Draft', 'Diajukan', 'Revisi', 'Disetujui', 'Ditolak']; foreach ($statuses as $s) { $selected = (($_GET['status'] ?? '') === $s) ? 'selected' : ''; echo "<option value=\"$s\" $selected>$s</option>"; } ?>
                </select>
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-slate-900 transition-colors shadow-sm">Filter</button>
                <?php if(!empty($_GET['q']) || !empty($_GET['status'])): ?>
                    <a href="/pengajuan/usulan" class="flex-1 sm:flex-none text-center bg-slate-100 text-slate-600 px-4 py-2 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors shadow-sm">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <a href="/usulan/create" class="w-full md:w-auto inline-flex justify-center items-center px-4 py-2 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition-colors text-sm shadow-sm">
            <span class="material-icons text-sm mr-2">add</span> Buat Usulan Baru
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <?php if (empty($usulan)): ?>
            <div class="p-8 md:p-12 text-center">
                <span class="material-icons text-slate-300 text-5xl md:text-6xl mb-4">folder_open</span>
                <h3 class="text-base md:text-lg font-bold text-slate-700">Tidak Ada Data Ditemukan</h3>
                <p class="text-slate-500 text-xs md:text-sm mt-1">Coba sesuaikan kata kunci pencarian atau filter Anda.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto w-full custom-scrollbar">
                <table class="w-full text-sm text-left whitespace-nowrap min-w-[800px]">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] md:text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-4 md:px-6 py-4 w-1/3">Nama Kegiatan</th>
                            <th class="px-4 md:px-6 py-4">Anggaran</th>
                            <th class="px-4 md:px-6 py-4 w-32">Tanggal</th>
                            <th class="px-4 md:px-6 py-4 w-28 text-center">Status</th>
                            <th class="px-4 md:px-6 py-4 w-40 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($usulan as $row): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 md:px-6 py-4">
                                <div class="font-bold text-slate-800 whitespace-normal leading-snug"><?php echo htmlspecialchars($row['nama_kegiatan']); ?></div>
                                <div class="text-[10px] md:text-xs text-slate-500 mt-0.5 uppercase tracking-wider"><?php echo htmlspecialchars($row['nama_jurusan']); ?></div>
                            </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pager) && $pager['total_pages'] > 1): ?>
            <div class="px-4 md:px-6 py-4 border-t border-slate-100 bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-[10px] md:text-xs text-slate-500 font-medium">
                    Halaman <strong class="text-slate-700"><?php echo $pager['current']; ?></strong> dari <strong class="text-slate-700"><?php echo $pager['total_pages']; ?></strong>
                </div>
                <div class="flex gap-1 flex-wrap justify-center">
                    <?php if ($pager['current'] > 1): ?>
                        <a href="?page=<?php echo $pager['current'] - 1; ?>&q=<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>&status=<?php echo htmlspecialchars($_GET['status'] ?? ''); ?>" class="px-3 py-1.5 bg-white border border-slate-300 rounded text-xs font-bold hover:bg-slate-100 shadow-sm transition-colors text-slate-600">Prev</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $pager['total_pages']; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&q=<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>&status=<?php echo htmlspecialchars($_GET['status'] ?? ''); ?>" 
                           class="px-3 py-1.5 border rounded text-xs font-bold shadow-sm transition-colors <?php echo ($i == $pager['current']) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-100'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($pager['current'] < $pager['total_pages']): ?>
                        <a href="?page=<?php echo $pager['current'] + 1; ?>&q=<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>&status=<?php echo htmlspecialchars($_GET['status'] ?? ''); ?>" class="px-3 py-1.5 bg-white border border-slate-300 rounded text-xs font-bold hover:bg-slate-100 shadow-sm transition-colors text-slate-600">Next</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>