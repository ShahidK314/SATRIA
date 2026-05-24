<?php 
// app/Views/dashboard/direktur.php
include __DIR__.'/../partials/sidebar.php'; 
// Asumsi variabel: $stats, $recent, $proposerStats

// --- WARNA UNTUK CHART & FUNGSI (KODE PHP TIDAK DIUBAH SAMA SEKALI) ---
$chartColors = [ '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16', '#6366f1', '#14b8a6', '#f43f5e', '#a855f7', '#d946ef', '#0ea5e9', '#22c55e' ];

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) { return 'Rp ' . number_format($number, 0, ',', '.'); }
}

if (!function_exists('deriveFinalStatus')) {
    function deriveFinalStatus($u) {
        $usulanStatus = trim($u['status_terkini'] ?? '');
        $pengajuanStatusPPK = trim($u['pengajuan_status_ppk'] ?? '');
        $pengajuanStatusWD2 = trim($u['pengajuan_status_wd2'] ?? '');
        $lpjStatus = trim($u['lpj_status'] ?? '');
        $totalCair = $u['total_sudah_cair'] ?? 0;
        
        $hasPencairanStarted = ($totalCair > 0);
        $isReadyForPencairan = ($pengajuanStatusWD2 === 'Disetujui');
        
        if ($lpjStatus === 'Disetujui') return 'Selesai';
        if ($hasPencairanStarted || $isReadyForPencairan) { 
            if ($lpjStatus === 'Diajukan') return 'LPJ Diajukan';
            if ($lpjStatus === 'Revisi') return 'LPJ Revisi';
            if ($lpjStatus === 'Belum Upload' && $totalCair > 0) return 'LPJ Wajib Upload';
        }
        if ($isReadyForPencairan) {
            $isFullCair = ($u['nominal_pencairan'] > 0) && (abs($totalCair - $u['nominal_pencairan']) < 1);
            if ($isFullCair) return 'Pencairan Selesai';
            if ($hasPencairanStarted) return 'Pencairan Bertahap';
            return 'Siap Pencairan'; 
        }
        if (!empty($pengajuanStatusPPK) && $pengajuanStatusPPK === 'Disetujui' && $pengajuanStatusWD2 === 'Menunggu') return 'Menunggu WD2';
        if (!empty($pengajuanStatusPPK) && $pengajuanStatusPPK === 'Menunggu') return 'Menunggu PPK';
        if ($usulanStatus === 'Disetujui') return 'Disetujui Verifikator'; 
        if ($usulanStatus === 'Diajukan') return 'Menunggu Verif';
        if ($usulanStatus === 'Revisi') return 'Revisi';
        if ($usulanStatus === 'Ditolak') return 'Ditolak';
        return $usulanStatus ?: 'Draft'; 
    }
}

if (!function_exists('getStatusClass')) {
    function getStatusClass($status) {
        return match($status) {
            'Draft' => 'bg-amber-100 text-amber-700', 'Diajukan', 'Menunggu Verif' => 'bg-blue-100 text-blue-700', 'Revisi', 'LPJ Revisi' => 'bg-rose-100 text-rose-700',
            'Ditolak' => 'bg-slate-800 text-white', 'Disetujui', 'Disetujui Verifikator' => 'bg-emerald-100 text-emerald-700', 'Menunggu PPK' => 'bg-violet-100 text-violet-700',
            'Menunggu WD2' => 'bg-indigo-100 text-indigo-700', 'Siap Pencairan', 'Pencairan Bertahap', 'Pencairan Selesai' => 'bg-cyan-100 text-cyan-700',
            'LPJ Diajukan', 'LPJ Wajib Upload' => 'bg-amber-100 text-amber-700', 'Selesai' => 'bg-slate-800 text-white', default => 'bg-gray-100 text-gray-700',
        };
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<div class="m-4 md:m-5">
    <div class="mb-6 md:mb-8 text-center md:text-left">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard Direktur</h1>
        <p class="text-slate-500 mt-1 text-sm md:text-base">Selamat datang, Direktur. Ringkasan Kinerja dan Pengawasan Sistem.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8 md:mb-10">
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col items-center justify-center">
            <span class="material-icons text-4xl text-slate-800">folder_open</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Total Usulan</p>
            <h3 class="text-3xl font-extrabold text-slate-800 mt-1"><?php echo $stats['total_usulan'] ?? 0; ?></h3>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col items-center justify-center">
            <span class="material-icons text-4xl text-blue-500">pending_actions</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Menunggu Verif</p>
            <h3 class="text-3xl font-extrabold text-blue-600 mt-1"><?php echo $stats['menunggu_verif'] ?? 0; ?></h3>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col items-center justify-center">
            <span class="material-icons text-4xl text-emerald-500">check_circle</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Disetujui Verif</p>
            <h3 class="text-3xl font-extrabold text-emerald-600 mt-1"><?php echo $stats['disetujui_verif'] ?? 0; ?></h3>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center flex flex-col items-center justify-center">
            <span class="material-icons text-4xl text-rose-500">autorenew</span>
            <p class="text-xs font-bold text-slate-500 uppercase mt-2">Revisi/Ditolak</p>
            <h3 class="text-3xl font-extrabold text-rose-600 mt-1"><?php echo ($stats['revisi'] ?? 0) + ($stats['ditolak'] ?? 0); ?></h3>
        </div>
    </div>
    
    <?php if(!empty($proposerStats)): 
        $labels = []; $dataTotal = []; $dataBerlangsung = []; $dataSelesai = []; $bgColors = [];
        foreach($proposerStats as $index => $p) {
            $labels[] = $p['username']; $dataTotal[] = $p['total_usulan'];
            $dataBerlangsung[] = $p['total_berlangsung']; $dataSelesai[] = $p['total_selesai'];
            $bgColors[] = $chartColors[$index % count($chartColors)];
        }
    ?>
    <div class="mb-10 space-y-6 md:space-y-8">
        
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-4 md:p-6 overflow-hidden">
            <h2 class="text-lg md:text-xl font-bold text-slate-800 mb-6 flex items-center">
                <span class="material-icons text-lg mr-2 text-blue-600">bar_chart</span> Total Usulan Semua Pengusul
            </h2>
            <div class="relative w-full overflow-x-auto" style="height: <?php echo count($labels) * 40 + 100; ?>px; min-height: 300px;">
                <canvas id="barChartTotal"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
            <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-4 md:p-6">
                <h2 class="text-base md:text-lg font-bold text-slate-800 mb-6 flex items-center">
                    <span class="material-icons text-lg mr-2 text-amber-500">timelapse</span> Distribusi Status Berlangsung
                </h2>
                <div class="relative h-64 md:h-72 w-full flex justify-center">
                    <canvas id="pieChartBerlangsung"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-4 md:p-6">
                <h2 class="text-base md:text-lg font-bold text-slate-800 mb-6 flex items-center">
                    <span class="material-icons text-lg mr-2 text-emerald-500">task_alt</span> Distribusi Status Selesai
                </h2>
                <div class="relative h-64 md:h-72 w-full flex justify-center">
                    <canvas id="pieChartSelesai"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        Chart.register(ChartDataLabels);
        const labels = <?php echo json_encode($labels); ?>;
        const bgColors = <?php echo json_encode($bgColors); ?>;
        
        const ctxBar = document.getElementById('barChartTotal').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: { labels: labels, datasets: [{ label: 'Total Usulan', data: <?php echo json_encode($dataTotal); ?>, backgroundColor: bgColors, borderWidth: 1, barPercentage: 0.7 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { beginAtZero: true, grid: { display: false } }, y: { grid: { display: false } } }, plugins: { legend: { display: false }, datalabels: { anchor: 'end', align: 'end', color: '#555', font: { weight: 'bold' }, formatter: Math.round } } }
        });

        const ctxPieBerlangsung = document.getElementById('pieChartBerlangsung').getContext('2d');
        new Chart(ctxPieBerlangsung, {
            type: 'pie',
            data: { labels: labels, datasets: [{ data: <?php echo json_encode($dataBerlangsung); ?>, backgroundColor: bgColors, hoverOffset: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }, datalabels: { color: '#fff', font: { weight: 'bold', size: 11 }, formatter: (value) => { return value > 0 ? value : ''; } } } }
        });

        const ctxPieSelesai = document.getElementById('pieChartSelesai').getContext('2d');
        new Chart(ctxPieSelesai, {
            type: 'pie',
            data: { labels: labels, datasets: [{ data: <?php echo json_encode($dataSelesai); ?>, backgroundColor: bgColors, hoverOffset: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }, datalabels: { color: '#fff', font: { weight: 'bold', size: 11 }, formatter: (value) => { return value > 0 ? value : ''; } } } }
        });
    </script>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-4 md:p-6 mt-8">
        <h2 class="text-lg md:text-xl font-bold text-slate-800 mb-4 md:mb-6 flex items-center">
            <span class="material-icons text-lg mr-2 text-slate-600">history</span> Aktivitas Sistem Terbaru
        </h2>
         <?php if(empty($recent)): ?>
            <div class="text-center py-10 text-slate-400">Belum ada aktivitas.</div>
        <?php else: ?>
        <div class="space-y-3 md:space-y-4">
            <?php foreach ($recent as $r): 
                $finalStatus = deriveFinalStatus($r);
            ?>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 md:p-4 hover:bg-slate-50 rounded-xl transition-colors border border-slate-100 gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs shrink-0">
                        <?php echo substr($r['nama_kegiatan'], 0, 2); ?>
                    </div>
                    <div>
                        <div class="font-bold text-slate-700 text-sm line-clamp-2 md:line-clamp-1"><?php echo htmlspecialchars($r['nama_kegiatan']); ?></div>
                        <div class="text-xs text-slate-400 mt-0.5">
                            <?php echo date('d M H:i', strtotime($r['updated_at'])); ?> • <?php echo htmlspecialchars($r['username']); ?>
                        </div>
                    </div>
                </div>
                <div class="flex sm:justify-end">
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide <?php echo getStatusClass($finalStatus); ?> whitespace-nowrap">
                        <?php echo $finalStatus; ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>      
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>