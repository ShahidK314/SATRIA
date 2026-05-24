<?php 
// app/Views/dashboard/ppk.php (FINALIZED)
include __DIR__.'/../partials/sidebar.php'; 
if (!function_exists('formatRupiah')) {
    function formatRupiah($number) { return 'Rp ' . number_format($number, 0, ',', '.'); }
}
$usulan = $usulan ?? [];
$menungguPPK = count($usulan); 
$pendingWD2 = $stats['menunggu_wd2'] ?? 0; 
$approvedTotal = $stats['disetujui_total'] ?? 0;
$disetujuiTotal = $pendingWD2 + $approvedTotal; 
$totalApprovalFlow = $menungguPPK + $disetujuiTotal; 
?>

<div class="m-4 md:m-5">
    <div class="mb-6 md:mb-8 text-center md:text-left">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard PPK</h1>
        <p class="text-slate-500 mt-1 text-sm md:text-base">Selamat datang, <?php echo htmlspecialchars($_SESSION['username'] ?? 'PPK'); ?>. Ringkasan kegiatan dan antrian persetujuan Anda.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-8">
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col items-center justify-center">
            <span class="material-icons text-4xl text-slate-800">folder_open</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Total Kegiatan</p>
            <h3 class="text-3xl font-extrabold text-slate-800 mt-1"><?php echo $totalApprovalFlow; ?></h3>
        </div>
        
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col items-center justify-center">
            <span class="material-icons text-4xl text-blue-500">pending_actions</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Menunggu Keputusan</p>
            <h3 class="text-3xl font-extrabold text-blue-600 mt-1"><?php echo $menungguPPK; ?></h3>
        </div>
        
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col items-center justify-center">
            <span class="material-icons text-4xl text-emerald-500">check_circle</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Disetujui</p>
            <h3 class="text-3xl font-extrabold text-emerald-600 mt-1"><?php echo $disetujuiTotal; ?></h3>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-4 md:p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg md:text-xl font-bold text-slate-800 flex items-center">
                <span class="material-icons text-lg mr-2 text-blue-600">assignment</span> Daftar Menunggu Persetujuan
            </h2>
        </div>

        <?php if(empty($usulan)): ?>
            <div class="text-center py-10 text-slate-400">
                <span class="material-icons text-5xl mb-2">task_alt</span>
                <p>Tidak ada kegiatan yang menunggu persetujuan PPK saat ini.</p>
            </div>
        <?php else: ?>
        <div class="space-y-3 md:space-y-4">
            <?php foreach ($usulan as $r): ?>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-3 md:p-4 border border-slate-100 rounded-lg hover:bg-blue-50 transition-colors gap-3">
                <div class="flex flex-col">
                    <span class="font-bold text-slate-700 text-sm mb-0.5"><?php echo htmlspecialchars($r['nama_kegiatan']); ?></span>
                    <span class="text-xs text-slate-500">Pengusul: <?php echo htmlspecialchars($r['username']); ?> (<?php echo htmlspecialchars($r['nama_jurusan']); ?>)</span>
                </div>
                <a href="/pengajuan/ppk/detail?id=<?php echo $r['id']; ?>" class="w-full sm:w-auto text-center px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 shadow-sm">
                    <span class="material-icons text-[14px] align-text-bottom mr-1">check</span> Putuskan
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>