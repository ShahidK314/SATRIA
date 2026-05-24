<?php include __DIR__.'/../partials/sidebar.php'; ?>
<div class="m-4 md:m-5">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 md:mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Antrian Persetujuan</h1>
            <p class="text-slate-500 mt-1 text-sm md:text-base">Usulan kegiatan yang menunggu keputusan Anda.</p>
        </div>
        <div class="w-full sm:w-auto px-4 py-2 bg-white border border-slate-200 rounded-lg shadow-sm text-sm font-bold text-slate-600 text-center">
            Menunggu: <span class="text-indigo-600 ml-1"><?php echo count($usulan); ?></span>
        </div>
    </div>

    <?php if (empty($usulan)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 md:p-16 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 md:w-20 md:h-20 rounded-full bg-slate-50 mb-4 md:mb-6">
                <span class="material-icons text-slate-300 text-3xl md:text-4xl">assignment_turned_in</span>
            </div>
            <h3 class="text-lg md:text-xl font-bold text-slate-800 mb-2">Tugas Selesai</h3>
            <p class="text-slate-500 text-sm md:text-base">Tidak ada usulan yang perlu diproses saat ini.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-4">
            <?php foreach ($usulan as $row): ?>
            <div class="bg-white p-4 md:p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition-all flex flex-col md:flex-row items-start md:items-center justify-between gap-4 md:gap-6 group">
                <div class="flex items-start gap-3 md:gap-4 flex-1 w-full">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 font-bold border border-indigo-100 text-sm md:text-base">
                        <?php echo strtoupper(substr($row['username'], 0, 2)); ?>
                    </div>
                    <div class="overflow-hidden w-full">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold uppercase rounded tracking-wider flex-shrink-0">
                                #<?php echo $row['id']; ?>
                            </span>
                            <span class="text-[10px] md:text-xs text-slate-500 truncate">Oleh <span class="font-bold text-slate-700"><?php echo htmlspecialchars($row['username']); ?></span></span>
                        </div>
                        <h3 class="text-base md:text-lg font-bold text-slate-900 group-hover:text-indigo-700 transition-colors mb-1 truncate leading-tight">
                            <?php echo htmlspecialchars($row['nama_kegiatan']); ?>
                        </h3>
                        <div class="text-sm text-emerald-600 font-bold font-mono">
                            Rp <?php echo number_format($row['nominal_pencairan'], 0, ',', '.'); ?>
                        </div>
                    </div>
                </div>
                <div class="w-full md:w-auto mt-2 md:mt-0 pt-3 md:pt-0 border-t md:border-t-0 border-slate-100">
                    <a href="/approval/proses?id=<?php echo $row['id']; ?>" class="w-full md:w-auto inline-flex justify-center items-center px-6 py-2.5 md:py-3 bg-indigo-600 text-white text-sm font-bold rounded-lg shadow-md md:shadow-lg md:shadow-indigo-600/20 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">
                        <span class="material-icons text-sm mr-2">gavel</span> Proses
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>