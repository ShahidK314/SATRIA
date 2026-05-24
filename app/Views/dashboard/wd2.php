<?php 
// app/Views/dashboard/wd2.php
include __DIR__.'/../partials/sidebar.php'; 
// Asumsi variabel: $stats, $usulan (antrian)
if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

// FIX: Initialize variables
$usulan = $usulan ?? [];

// 1. Dapatkan hitungan Menunggu WD2
$menungguWD2 = count($usulan); 
// 2. Dapatkan hitungan Disetujui WD2 & Lanjut Bendahara
$disetujuiLanjutBendahara = $stats['disetujui_total'] ?? 0; 
// 3. Hitung Total Kegiatan di Alur WD2
$totalApprovalFlow = $menungguWD2 + $disetujuiLanjutBendahara; 
?>

<div class="m-4 md:m-5">
    <div class="mb-6 md:mb-8 text-center md:text-left">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard Wakil Direktur 2</h1>
        <p class="text-slate-500 mt-1 text-sm md:text-base">Selamat datang, <?php echo htmlspecialchars($_SESSION['username'] ?? 'WD2'); ?>. Daftar persetujuan kegiatan.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-8">
        
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col justify-center items-center">
            <span class="material-icons text-4xl text-slate-800">folder_open</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Total Kegiatan</p>
            <h3 class="text-3xl font-extrabold text-slate-800 mt-1"><?php echo $totalApprovalFlow; ?></h3>
        </div>
        
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col justify-center items-center">
            <span class="material-icons text-4xl text-blue-500">pending_actions</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Menunggu Keputusan</p>
            <h3 class="text-3xl font-extrabold text-blue-600 mt-1"><?php echo $menungguWD2; ?></h3>
        </div>
        
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col justify-center items-center">
            <span class="material-icons text-4xl text-emerald-500">check_circle</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Disetujui</p>
            <h3 class="text-3xl font-extrabold text-emerald-600 mt-1"><?php echo $disetujuiLanjutBendahara; ?></h3>
        </div>
        
    </div>
    
    <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-4 md:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
            <h2 class="text-lg md:text-xl font-bold text-slate-800 flex items-center">
                <span class="material-icons text-lg mr-2 text-blue-600">gavel</span> Daftar Menunggu Putusan
            </h2>
        </div>

        <?php if(empty($usulan)): ?>
            <div class="text-center py-10 text-slate-400">
                <span class="material-icons text-5xl mb-2">task_alt</span>
                <p class="text-sm">Tidak ada kegiatan yang menunggu persetujuan WD2 saat ini.</p>
            </div>
        <?php else: ?>
        <div class="space-y-3 md:space-y-4">
            <?php foreach ($usulan as $r): ?>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-3 md:p-4 border border-slate-100 rounded-lg hover:bg-blue-50 transition-colors gap-3">
                <div class="flex flex-col">
                    <span class="font-bold text-slate-700 text-sm mb-0.5"><?php echo htmlspecialchars($r['nama_kegiatan']); ?></span>
                    <span class="text-xs text-slate-500">Pengusul: <?php echo htmlspecialchars($r['username']); ?> (<?php echo htmlspecialchars($r['nama_jurusan']); ?>)</span>
                </div>
                <a href="/pengajuan/wd2/detail?id=<?php echo $r['id']; ?>" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 text-center shadow-sm">
                    <span class="material-icons text-[14px] align-text-bottom mr-1">check</span> Putuskan
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>