<?php include __DIR__.'/../partials/sidebar.php'; ?>

<div class="p-8 max-w-7xl mx-auto">
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Laporan Kinerja Institusi</h1>
            <p class="text-slate-500 mt-1">Rekapitulasi realisasi kegiatan dan serapan anggaran tahun berjalan.</p>
        </div>
        <div class="bg-slate-100 px-4 py-2 rounded-lg text-xs font-mono text-slate-600">
            Data per: <?php echo date('d F Y H:i'); ?>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl p-6 text-white shadow-lg shadow-blue-600/20 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <p class="text-blue-200 text-xs font-bold uppercase tracking-wider mb-1">Total Kegiatan</p>
                <h3 class="text-4xl font-extrabold"><?php echo number_format($stats['total']); ?></h3>
                <div class="mt-4 text-xs bg-blue-500/30 inline-block px-2 py-1 rounded border border-blue-400/20">Semua Status</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-emerald-100 shadow-sm relative overflow-hidden">
            <div class="absolute right-0 bottom-0 w-24 h-24 bg-emerald-50 rounded-full blur-xl"></div>
            <div class="relative z-10">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Kegiatan Selesai</p>
                <h3 class="text-4xl font-extrabold text-emerald-600"><?php echo number_format($stats['selesai']); ?></h3>
                <p class="text-xs text-emerald-600 mt-2 flex items-center">
                    <span class="material-icons text-sm mr-1">check_circle</span> Fully Audited
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-amber-100 shadow-sm relative overflow-hidden">
             <div class="absolute right-0 bottom-0 w-24 h-24 bg-amber-50 rounded-full blur-xl"></div>
            <div class="relative z-10">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Dana Terserap</p>
                <h3 class="text-3xl font-extrabold text-slate-800">Rp <?php echo number_format($stats['dana'] / 1000000, 0, ',', '.'); ?> <span class="text-lg text-slate-400 font-medium">Juta</span></h3>
                <p class="text-xs text-amber-600 mt-2 flex items-center">
                    <span class="material-icons text-sm mr-1">trending_up</span> Realisasi Anggaran
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center">
                <span class="material-icons text-slate-400 mr-2">history</span> Aktivitas Terbaru
            </h3>
            <?php if(empty($recent)): ?>
                <p class="text-slate-400 text-sm text-center py-8">Belum ada aktivitas.</p>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($recent as $r): ?>
                <div class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-xl transition-colors border border-transparent hover:border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs">
                            <?php echo substr($r['nama_kegiatan'], 0, 2); ?>
                        </div>
                        <div>
                            <div class="font-bold text-slate-700 text-sm line-clamp-1"><?php echo htmlspecialchars($r['nama_kegiatan']); ?></div>
                            <div class="text-xs text-slate-400">
                                <?php echo date('d M H:i', strtotime($r['updated_at'])); ?> • <?php echo htmlspecialchars($r['username']); ?>
                            </div>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-blue-50 text-blue-600 border border-blue-100 whitespace-nowrap">
                        <?php echo $r['status_terkini']; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center">
                <span class="material-icons text-slate-400 mr-2">pie_chart</span> Distribusi Anggaran (Real-time)
            </h3>
            
            <?php 
                if (empty($distribusi)) {
                    echo '<div class="text-center py-10 text-slate-400 text-sm">Belum ada anggaran yang disetujui.</div>';
                } else {
                    // Hitung total untuk persentase
                    $totalGlobal = array_sum(array_column($distribusi, 'total_anggaran'));
            ?>
            <div class="space-y-6">
                <?php foreach ($distribusi as $d): 
                    $persen = ($totalGlobal > 0) ? ($d['total_anggaran'] / $totalGlobal) * 100 : 0;
                    // Warna dinamis berdasarkan index/kategori bisa ditambahkan
                    $colors = ['bg-blue-500', 'bg-emerald-500', 'bg-amber-500', 'bg-violet-500'];
                    $barColor = $colors[rand(0,3)];
                ?>
                <div>
                    <div class="flex justify-between text-xs font-bold text-slate-600 mb-1">
                        <span><?php echo htmlspecialchars($d['nama_kategori']); ?></span>
                        <span><?php echo number_format($persen, 1); ?>%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5 mb-1 overflow-hidden">
                        <div class="<?php echo $barColor; ?> h-2.5 rounded-full transition-all duration-1000" style="width: <?php echo $persen; ?>%"></div>
                    </div>
                    <div class="text-right text-xs text-slate-400 font-mono">
                        Rp <?php echo number_format($d['total_anggaran'], 0, ',', '.'); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-8 p-4 bg-slate-50 rounded-xl border border-slate-100">
                <p class="text-xs text-slate-500 leading-relaxed text-center">
                    Total Pagu Terserap: <strong class="text-slate-700">Rp <?php echo number_format($totalGlobal, 0, ',', '.'); ?></strong><br>
                    <span class="text-[10px] opacity-75">(Hanya menghitung kegiatan berstatus Disetujui, Pencairan, LPJ, Selesai)</span>
                </p>
            </div>
            <?php } ?>
        </div>

    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>