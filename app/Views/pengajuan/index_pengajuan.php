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

<div class="m-5">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Pengajuan Usulan Kegiatan</h1>
        <p class="text-slate-500 mt-1">Kelola dan lacak status usulan KAK/IKU/RAB yang Anda buat.</p>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-5 gap-6 mb-8">
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center">
            <p class="text-xs font-bold text-slate-500 uppercase">Draft</p>
            <h3 class="text-3xl font-extrabold text-amber-600 mt-1"><?php echo $stats['draft'] ?? 0; ?></h3>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center">
            <p class="text-xs font-bold text-slate-500 uppercase">Diajukan</p>
            <h3 class="text-3xl font-extrabold text-blue-600 mt-1"><?php echo $stats['diajukan'] ?? 0; ?></h3>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center">
            <p class="text-xs font-bold text-slate-500 uppercase">Revisi</p>
            <h3 class="text-3xl font-extrabold text-rose-600 mt-1"><?php echo $stats['revisi'] ?? 0; ?></h3>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center">
            <p class="text-xs font-bold text-slate-500 uppercase">Ditolak</p>
            <h3 class="text-3xl font-extrabold text-slate-800 mt-1"><?php echo $stats['ditolak'] ?? 0; ?></h3>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center">
            <p class="text-xs font-bold text-slate-500 uppercase">Disetujui Verif</p>
            <h3 class="text-3xl font-extrabold text-emerald-600 mt-1"><?php echo $stats['disetujui'] ?? 0; ?></h3>
        </div>
    </div>
    
    <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        
        <form method="GET" action="" class="flex flex-col md:flex-row gap-3 w-full md:w-auto flex-1">
            <div class="relative w-full md:w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fas fa-search"></i></span>
                <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" 
                       placeholder="Cari Kegiatan..." 
                       class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            
            <div class="relative w-full md:w-48">
                <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm appearance-none bg-white cursor-pointer">
                    <option value="">Semua Status</option>
                    <?php 
                    $statuses = ['Draft', 'Diajukan', 'Revisi', 'Disetujui', 'Ditolak'];
                    foreach ($statuses as $s) {
                        $selected = (($_GET['status'] ?? '') === $s) ? 'selected' : '';
                        echo "<option value=\"$s\" $selected>$s</option>";
                    }
                    ?>
                </select>
                <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500"><i class="fas fa-chevron-down text-xs"></i></span>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-slate-700 transition-colors">Filter</button>
                <?php if(!empty($_GET['q']) || !empty($_GET['status'])): ?>
                    <a href="/pengajuan/usulan" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-300 transition-colors">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <a href="/usulan/create" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition-colors text-sm shadow-sm whitespace-nowrap">
            <span class="material-icons text-sm mr-2">add</span> Buat Usulan Baru
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <?php if (empty($usulan)): ?>
            <div class="p-12 text-center">
                <span class="material-icons text-slate-300 text-6xl mb-4">folder_open</span>
                <h3 class="text-lg font-bold text-slate-700">Tidak Ada Data Ditemukan</h3>
                <p class="text-slate-500 text-sm">Coba sesuaikan kata kunci pencarian atau filter Anda.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Nama Kegiatan</th>
                            <th class="px-6 py-4">Anggaran</th>
                            <th class="px-6 py-4 w-32">Tanggal</th>
                            <th class="px-6 py-4 w-24 text-center">Status</th>
                            <th class="px-6 py-4 w-40 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($usulan as $row): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800"><?php echo htmlspecialchars($row['nama_kegiatan']); ?></div>
                                <div class="text-xs text-slate-500 mt-0.5"><?php echo htmlspecialchars($row['nama_jurusan']); ?></div>
                            </td>
                            <td class="px-6 py-4 text-emerald-600 font-bold"><?php echo formatRupiah($row['nominal_pencairan']); ?></td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded text-xs font-bold <?php echo getStatusClass($row['status_terkini']); ?>">
                                    <?php echo htmlspecialchars($row['status_terkini']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="/usulan/detail?id=<?php echo $row['id']; ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all" title="Lihat Detail">
                                        <span class="material-icons text-sm">visibility</span>
                                    </a>

                                    <?php if (in_array($row['status_terkini'], ['Draft', 'Revisi'])): ?>
                                        <a href="/usulan/edit?id=<?php echo $row['id']; ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-amber-600 hover:bg-amber-50 transition-all" title="Edit">
                                            <span class="material-icons text-sm">edit</span>
                                        </a>
                                        <form action="/usulan/ajukan/<?php echo $row['id']; ?>" method="POST" onsubmit="return confirm('Yakin ingin mengajukan usulan ini?');">
                                             <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-emerald-600 hover:bg-emerald-50 transition-all" title="Ajukan">
                                                <span class="material-icons text-sm">send</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (in_array($row['status_terkini'], ['Draft', 'Revisi', 'Ditolak'])): ?>
                                        <form action="/usulan/delete/<?php echo $row['id']; ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus usulan ini? Data yang dihapus tidak dapat dikembalikan.');">
                                             <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-rose-600 hover:bg-rose-50 transition-all" title="Hapus">
                                                <span class="material-icons text-sm">delete</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pager) && $pager['total_pages'] > 1): ?>
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                <div class="text-xs text-gray-500">
                    Halaman <strong><?php echo $pager['current']; ?></strong> dari <strong><?php echo $pager['total_pages']; ?></strong>
                </div>
                <div class="flex space-x-1">
                    <?php if ($pager['current'] > 1): ?>
                        <a href="?page=<?php echo $pager['current'] - 1; ?>&q=<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>&status=<?php echo htmlspecialchars($_GET['status'] ?? ''); ?>" class="px-3 py-1 bg-white border border-gray-300 rounded text-xs font-medium hover:bg-gray-100">Prev</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $pager['total_pages']; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&q=<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>&status=<?php echo htmlspecialchars($_GET['status'] ?? ''); ?>" 
                           class="px-3 py-1 border rounded text-xs font-medium <?php echo ($i == $pager['current']) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($pager['current'] < $pager['total_pages']): ?>
                        <a href="?page=<?php echo $pager['current'] + 1; ?>&q=<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>&status=<?php echo htmlspecialchars($_GET['status'] ?? ''); ?>" class="px-3 py-1 bg-white border border-gray-300 rounded text-xs font-medium hover:bg-gray-100">Next</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>