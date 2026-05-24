<?php 
// app/Views/dashboard/verifikator.php (FIXED & SAFE)
include __DIR__.'/../partials/sidebar.php'; 

// Pastikan recent selalu array
$recent = $recent ?? [];
$recent = array_map(fn($r) => (array)$r, $recent);

$antrian = array_filter($recent, function($r) {
    return ($r['status_terkini'] ?? '') === 'Diajukan';
});

$limit = 5;
$count = 0;
?>

<div class="m-4 md:m-5">
    <div class="mb-6 md:mb-8 text-center md:text-left">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard Verifikator</h1>
        <p class="text-slate-500 mt-1 text-sm md:text-base">Selamat datang, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Verifikator'); ?>.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col items-center justify-center">
            <span class="material-icons text-4xl text-slate-400">folder_open</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Total Usulan</p>
            <h3 class="text-3xl font-extrabold text-slate-800 mt-1"><?= $stats['total_usulan'] ?? 0 ?></h3>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col items-center justify-center">
            <span class="material-icons text-4xl text-blue-500 animate-pulse">pending_actions</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Menunggu Verifikasi</p>
            <h3 class="text-3xl font-extrabold text-blue-600 mt-1"><?= $stats['menunggu_verif'] ?? 0 ?></h3>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col items-center justify-center">
            <span class="material-icons text-4xl text-emerald-500">check_circle</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Telah Disetujui</p>
            <h3 class="text-3xl font-extrabold text-emerald-600 mt-1"><?= $stats['disetujui_verif'] ?? 0 ?></h3>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col items-center justify-center">
            <span class="material-icons text-4xl text-rose-500">autorenew</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Perlu Revisi</p>
            <h3 class="text-3xl font-extrabold text-rose-600 mt-1"><?= $stats['revisi'] ?? 0 ?></h3>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-4 md:p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg md:text-xl font-bold text-slate-800 flex items-center">
                <span class="material-icons text-lg mr-2 text-blue-600">list_alt</span> 
                Antrian Baru (<?= $stats['menunggu_verif'] ?? 0 ?>)
            </h2>
            <a href="/verifikasi" class="text-sm font-bold text-blue-600 hover:text-blue-700">Lihat Semua</a>
        </div>

        <?php if(($stats['menunggu_verif'] ?? 0) == 0): ?>
            <div class="text-center py-10 text-slate-400">
                <span class="material-icons text-5xl mb-2">task_alt</span>
                <p>Tidak ada usulan yang menunggu verifikasi saat ini.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3 md:space-y-4">
                <?php foreach ($antrian as $r): if ($count >= $limit) break; $count++; ?>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-3 md:p-4 border border-slate-100 rounded-lg hover:bg-blue-50 transition-colors gap-3">
                    <div class="flex flex-col">
                        <span class="font-bold text-slate-700 text-sm mb-0.5"><?= htmlspecialchars($r['nama_kegiatan'] ?? 'Tanpa Nama') ?></span>
                        <span class="text-xs text-slate-500">Pengusul: <?= htmlspecialchars($r['username'] ?? '-') ?></span>
                    </div>
                    <a href="/verifikasi/proses?id=<?= $r['id'] ?? 0 ?>" class="w-full sm:w-auto text-center px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 shadow-sm">
                       <span class="material-icons text-[14px] align-text-bottom mr-1">check</span> Proses
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>