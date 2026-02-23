<?php 
// app/Views/admin/audit_log.php (UPDATED: Hide Delete Button for Direktur)
include __DIR__.'/../partials/sidebar.php'; 
// Asumsi variabel: $logs, $totalLogs, $page, $totalPages

// [LOGIC] Cek apakah user adalah Admin (untuk tombol Hapus)
$isAdmin = ($_SESSION['role'] === 'Admin');
?>

<div class="m-5">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Log Audit Sistem</h1>
        <p class="text-slate-500 mt-1">Mencatat semua aktivitas penting pengguna dalam sistem.</p>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-4">
                <label for="user" class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter User</label>
                <input type="text" id="user" name="user" placeholder="Username..." value="<?php echo htmlspecialchars($_GET['user'] ?? ''); ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
            </div>
             <div class="md:col-span-4">
                <label for="action" class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter Aksi</label>
                <input type="text" id="action" name="action" placeholder="Aksi..." value="<?php echo htmlspecialchars($_GET['action'] ?? ''); ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
            </div>
            <div class="md:col-span-2">
                 <label for="date" class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter Tanggal</label>
                 <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($_GET['date'] ?? ''); ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
            </div>
            
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="w-full py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-all text-sm shadow-md">Filter</button>
                <a href="/audit-log" class="w-10 h-10 flex items-center justify-center bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200" title="Reset Filter">
                    <span class="material-icons text-lg">refresh</span>
                </a>
            </div>
        </form>
    </div>
    
    <div class="flex justify-end gap-3 mb-4">
        <a href="/audit-log/export" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition-colors text-sm">
            <span class="material-icons text-sm mr-2">download</span> Export CSV
        </a>
        
        <?php if ($isAdmin): ?>
        <form action="/audit-log/delete-all" method="POST" onsubmit="return confirm('ANDA YAKIN INGIN MENGHAPUS SEMUA LOG? Aksi ini tidak dapat dibatalkan.');">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-rose-600 text-white font-bold rounded-lg hover:bg-rose-700 transition-colors text-sm">
                <span class="material-icons text-sm mr-2">delete_forever</span> Hapus Semua
            </button>
        </form>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <?php if (empty($logs)): ?>
            <div class="p-12 text-center">
                <span class="material-icons text-slate-300 text-5xl mb-2">find_in_page</span>
                <h3 class="text-lg font-bold text-slate-700">Tidak ada Log Ditemukan</h3>
                <p class="text-slate-500 text-sm">Log kosong atau tidak sesuai kriteria pencarian.</p>
            </div>
        <?php else: ?>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-bold text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 w-40">Waktu</th>
                            <th class="px-6 py-4 w-32">User</th>
                            <th class="px-6 py-4 min-w-[300px]">Aksi</th>
                            <th class="px-6 py-4 w-32">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 text-xs text-slate-600">
                                <?php echo date('d M Y', strtotime($log['timestamp'])); ?><br>
                                <span class="font-mono text-slate-400"><?php echo date('H:i:s', strtotime($log['timestamp'])); ?></span>
                            </td>
                            <td class="px-6 py-3 font-bold text-slate-700"><?php echo htmlspecialchars($log['username']); ?></td>
                            <td class="px-6 py-3 text-slate-600"><?php echo htmlspecialchars($log['aksi']); ?></td>
                            <td class="px-6 py-3 font-mono text-slate-500 text-xs"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-between items-center">
                <div class="text-xs text-slate-500">Menampilkan <?php echo count($logs); ?> dari <?php echo $totalLogs; ?> entri.</div>
                <div class="flex gap-1">
                    <?php 
                        $maxPagesToShow = 5;
                        $startPage = max(1, $page - floor($maxPagesToShow / 2));
                        $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);

                        if ($endPage - $startPage + 1 < $maxPagesToShow) {
                            $startPage = max(1, $endPage - $maxPagesToShow + 1);
                        }
                    ?>
                    <?php if ($page > 1): ?>
                        <a href="/audit-log?page=<?php echo $page - 1; ?>&<?php echo http_build_query(['user' => $_GET['user'] ?? '', 'action' => $_GET['action'] ?? '', 'date' => $_GET['date'] ?? '']); ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all bg-white border border-slate-200 text-slate-600 hover:bg-slate-200">
                            &lt;
                        </a>
                    <?php endif; ?>

                    <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                        <a href="/audit-log?page=<?php echo $p; ?>&<?php echo http_build_query(['user' => $_GET['user'] ?? '', 'action' => $_GET['action'] ?? '', 'date' => $_GET['date'] ?? '']); ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all <?php echo ($p == $page) ? 'bg-slate-800 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-200'; ?>">
                            <?php echo $p; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="/audit-log?page=<?php echo $page + 1; ?>&<?php echo http_build_query(['user' => $_GET['user'] ?? '', 'action' => $_GET['action'] ?? '', 'date' => $_GET['date'] ?? '']); ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all bg-white border border-slate-200 text-slate-600 hover:bg-slate-200">
                            &gt;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>