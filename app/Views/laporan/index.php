<?php 
// app/Views/laporan/index.php
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

$chartColors = [
    '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
    '#ec4899', '#06b6d4', '#84cc16', '#6366f1', '#14b8a6',
    '#f43f5e', '#a855f7', '#d946ef', '#0ea5e9', '#22c55e'
];

$uniqueProposers = [];
if (!empty($pieChartData)) {
    foreach ($pieChartData as $items) {
        foreach ($items as $item) {
            $uniqueProposers[$item['proposer']] = true;
        }
    }
}
$uniqueProposers = array_keys($uniqueProposers);
sort($uniqueProposers); 

$proposerColorMap = [];
foreach ($uniqueProposers as $index => $name) {
    $proposerColorMap[$name] = $chartColors[$index % count($chartColors)];
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<div class="m-4 md:m-5">
    <div class="mb-6 md:mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Laporan Kinerja</h1>
            <p class="text-slate-500 mt-1 text-sm md:text-base">Rekapitulasi realisasi kegiatan dan serapan anggaran tahun berjalan.</p>
        </div>
        
        <div class="w-full md:w-auto bg-slate-100 px-4 py-2 md:py-3 rounded-lg text-xs md:text-sm font-mono text-slate-600 flex items-center justify-center border border-slate-200">
            <span class="material-icons text-sm mr-2">schedule</span>
            <span id="realtimeClock"><?php echo date('d F Y H:i:s'); ?></span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl p-5 md:p-6 text-white shadow-lg shadow-blue-600/20 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <p class="text-blue-200 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1">Total Kegiatan</p>
                <h3 class="text-3xl font-extrabold"><?php echo number_format($stats['total_usulan'] ?? 0); ?></h3>
                <div class="mt-4 text-[10px] md:text-xs bg-blue-500/30 inline-block px-2 py-1 rounded border border-blue-400/20">Semua Status</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 md:p-6 border border-emerald-100 shadow-sm relative overflow-hidden">
            <div class="absolute right-0 bottom-0 w-24 h-24 bg-emerald-50 rounded-full blur-xl"></div>
            <div class="relative z-10">
                <p class="text-slate-400 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1">Kegiatan Selesai</p>
                <h3 class="text-3xl font-extrabold text-emerald-600"><?php echo number_format($stats['selesai'] ?? 0); ?></h3>
                <p class="text-[10px] md:text-xs text-emerald-600 mt-2 flex items-center">
                    <span class="material-icons text-[14px] mr-1">check_circle</span> Fully Audited
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 md:p-6 border border-violet-100 shadow-sm relative overflow-hidden">
             <div class="absolute right-0 bottom-0 w-24 h-24 bg-violet-50 rounded-full blur-xl"></div>
            <div class="relative z-10">
                <p class="text-slate-400 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1">Total RAB Diajukan</p>
                <h3 class="text-xl md:text-2xl font-extrabold text-slate-800 font-mono truncate"><?php echo formatRupiah($stats['total_rab_diajukan'] ?? 0); ?></h3>
                <p class="text-[10px] md:text-xs text-violet-600 mt-2 flex items-center">
                    <span class="material-icons text-[14px] mr-1">monetization_on</span> Estimasi Anggaran
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 md:p-6 border border-amber-100 shadow-sm relative overflow-hidden">
             <div class="absolute right-0 bottom-0 w-24 h-24 bg-amber-50 rounded-full blur-xl"></div>
            <div class="relative z-10">
                <p class="text-slate-400 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1">Dana Terserap (LPJ)</p>
                <h3 class="text-xl md:text-2xl font-extrabold text-slate-800 font-mono truncate"><?php echo formatRupiah($stats['dana'] ?? 0); ?></h3>
                <p class="text-[10px] md:text-xs text-amber-600 mt-2 flex items-center">
                    <span class="material-icons text-[14px] mr-1">verified</span> LPJ Disetujui
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 md:p-6 mb-8">
        <h3 class="font-bold text-slate-800 mb-6 flex items-center border-b border-slate-100 pb-3">
            <span class="material-icons text-slate-400 mr-2">bar_chart</span> Distribusi Anggaran per Kategori
        </h3>
        
        <?php 
            if (empty($distribusi)) {
                echo '<div class="text-center py-10 text-slate-400 text-sm italic">Belum ada anggaran yang disetujui.</div>';
            } else {
                $totalGlobal = array_sum(array_column($distribusi, 'total_anggaran'));
        ?>
        <div class="space-y-5 md:space-y-6">
            <?php foreach ($distribusi as $index => $d): 
                $persen = ($totalGlobal > 0) ? ($d['total_anggaran'] / $totalGlobal) * 100 : 0;
                $colors = ['bg-blue-500', 'bg-emerald-500', 'bg-amber-500', 'bg-violet-500', 'bg-rose-500', 'bg-cyan-500'];
                $barColor = $colors[$index % count($colors)];
            ?>
            <div>
                <div class="flex justify-between text-[11px] md:text-xs font-bold text-slate-600 mb-1.5">
                    <span class="truncate pr-2"><?php echo htmlspecialchars($d['nama_kategori']); ?></span>
                    <span class="flex-shrink-0"><?php echo number_format($persen, 1); ?>%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 mb-1.5 overflow-hidden shadow-inner">
                    <div class="<?php echo $barColor; ?> h-2.5 rounded-full transition-all duration-1000" style="width: <?php echo $persen; ?>%"></div>
                </div>
                <div class="text-right text-[11px] md:text-xs text-slate-500 font-mono font-medium">
                    Rp <?php echo number_format($d['total_anggaran'], 0, ',', '.'); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php } ?>
    </div>

    <?php if (!empty($pieChartData)): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($pieChartData as $kategori => $items): 
            $chartId = 'pie_' . md5($kategori);
            $labels = array_column($items, 'proposer');
            $dataValues = array_column($items, 'amount');
            $totalKat = array_sum($dataValues);
            
            $bgColors = [];
            foreach ($labels as $labelName) {
                $bgColors[] = $proposerColorMap[$labelName] ?? '#ccc';
            }
        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 md:p-6 flex flex-col h-full hover:shadow-md transition-shadow">
            <h3 class="font-bold text-slate-800 text-sm mb-4 flex items-center border-b border-slate-100 pb-3 min-h-[48px]">
                <span class="material-icons text-slate-400 mr-2 text-sm">pie_chart</span> 
                <span class="line-clamp-2 leading-tight"><?php echo htmlspecialchars($kategori); ?></span>
            </h3>
            
            <div class="flex-1 flex flex-col items-center justify-between">
                <div class="relative h-48 md:h-56 w-full flex justify-center mb-6">
                    <canvas id="<?php echo $chartId; ?>"></canvas>
                </div>
                
                <div class="w-full space-y-1.5">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Rincian Nominal:</p>
                    <?php foreach ($items as $item): 
                        $pct = ($totalKat > 0) ? ($item['amount'] / $totalKat) * 100 : 0;
                        $color = $proposerColorMap[$item['proposer']] ?? '#ccc';
                    ?>
                    <div class="flex items-center justify-between text-[10px] md:text-[11px] p-2 bg-slate-50 rounded-lg border border-slate-100">
                        <div class="flex items-center gap-2 overflow-hidden w-2/3">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: <?php echo $color; ?>;"></span>
                            <span class="font-bold text-slate-700 truncate" title="<?php echo htmlspecialchars($item['proposer']); ?>"><?php echo htmlspecialchars($item['proposer']); ?></span>
                        </div>
                        <div class="text-right flex-shrink-0 ml-2 w-1/3">
                            <div class="font-bold text-slate-800 font-mono"><?php echo formatRupiah($item['amount']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <script>
                (function() {
                    const ctx = document.getElementById('<?php echo $chartId; ?>').getContext('2d');
                    new Chart(ctx, {
                        type: 'pie',
                        plugins: [ChartDataLabels],
                        data: {
                            labels: <?php echo json_encode($labels); ?>,
                            datasets: [{
                                data: <?php echo json_encode($dataValues); ?>,
                                backgroundColor: <?php echo json_encode($bgColors); ?>,
                                hoverOffset: 4,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: { padding: 10 },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    enabled: true,
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            let value = context.parsed;
                                            return label + ': ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                                        }
                                    }
                                },
                                datalabels: {
                                    color: '#fff',
                                    font: { weight: 'bold', size: 10 },
                                    formatter: (value, ctx) => {
                                        let sum = 0;
                                        let dataArr = ctx.chart.data.datasets[0].data;
                                        dataArr.map(data => { sum += data; });
                                        let percentage = (value * 100 / sum).toFixed(0) + "%";
                                        if ((value * 100 / sum) < 5) return "";
                                        return percentage;
                                    },
                                    anchor: 'center', align: 'center', offset: 0
                                }
                            }
                        }
                    });
                })();
            </script>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<script>
    function updateClock() {
        const now = new Date();
        const options = { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        document.getElementById('realtimeClock').textContent = now.toLocaleDateString('id-ID', options);
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>