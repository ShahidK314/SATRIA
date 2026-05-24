<?php 
include __DIR__.'/../partials/sidebar.php'; 
?>

<div class="m-4 md:m-5">

    <div class="mb-6 md:mb-10 text-center md:text-left">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Notifikasi Anda</h1>

        <p class="text-slate-500 mt-1 text-sm md:text-base">
            Total notifikasi belum dibaca: 
            <span class="font-bold text-blue-600"><?php echo $total ?? 0; ?></span>
        </p>

        <p class="text-[10px] md:text-xs text-slate-400 mt-2 bg-slate-100 p-2 rounded inline-block">
            <span class="material-icons text-[12px] align-text-bottom mr-1">info</span> 
            Catatan: Notifikasi akan otomatis terhapus setelah Anda membacanya.
        </p>
    </div>

    <?php if (empty($notifikasi)): ?>

        <div class="bg-white rounded-2xl p-8 md:p-12 text-center shadow-sm border border-slate-200 w-full max-w-md mx-auto md:mx-0">
            <span class="material-icons text-slate-300 text-5xl md:text-6xl mb-4">notifications_off</span>
            <h3 class="text-base md:text-lg font-bold text-slate-700">Tidak Ada Notifikasi Baru</h3>
            <p class="text-slate-500 text-sm mt-1">Semua notifikasi sudah dibaca atau dihapus.</p>
        </div>

    <?php else: ?>

        <div class="space-y-3 md:space-y-4 w-full max-w-3xl">

            <?php foreach ($notifikasi as $n):

                $icon = match ($n['judul']) {
                    'Usulan Baru', 'Pengajuan Kegiatan Baru', 'LPJ Baru' => 'notifications_active',
                    'Update Usulan: Revisi', 'LPJ Perlu Revisi' => 'autorenew',
                    'Update Usulan: Disetujui', 'Dana Telah Dicairkan' => 'check_circle',
                    default => 'info',
                };

                $color = match ($icon) {
                    'notifications_active' => 'text-blue-600',
                    'autorenew' => 'text-rose-600',
                    'check_circle' => 'text-emerald-600',
                    default => 'text-slate-500',
                };
            ?>

            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow border border-slate-200 p-4 md:p-5 flex flex-col sm:flex-row gap-3 sm:gap-5 items-start w-full">

                <div class="flex items-center gap-3 sm:block">
                    <span class="material-icons text-xl md:text-2xl <?php echo $color; ?> mt-0 sm:mt-1 flex-shrink-0">
                        <?php echo $icon; ?>
                    </span>
                    <p class="font-bold text-slate-800 text-sm sm:hidden leading-snug">
                        <?php echo htmlspecialchars($n['judul']); ?>
                    </p>
                </div>

                <div class="flex-1 w-full">
                    <p class="font-bold text-slate-800 text-sm hidden sm:block">
                        <?php echo htmlspecialchars($n['judul']); ?>
                    </p>

                    <p class="text-[11px] md:text-xs text-slate-600 mt-1 mb-2 leading-relaxed">
                        <?php echo htmlspecialchars($n['pesan']); ?>
                    </p>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-3">
                        <p class="text-[10px] text-slate-400 font-mono font-medium flex items-center">
                            <span class="material-icons text-[12px] mr-1">schedule</span>
                            <?php echo date('d M Y H:i', strtotime($n['created_at'])); ?>
                        </p>

                        <a href="/notifikasi/read?id=<?php echo $n['id']; ?>&link=<?php echo urlencode($n['link'] ?: '/dashboard'); ?>"
                           class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors text-center shadow-sm">
                            Lihat Detail
                        </a>
                    </div>
                </div>

            </div>

            <?php endforeach; ?>

        </div>

        <div class="mt-6 text-slate-500 text-xs font-medium bg-slate-50 px-4 py-2 rounded-lg border border-slate-200 inline-block">
            Halaman <?php echo $page; ?> dari <?php echo $totalPages; ?>
        </div>

    <?php endif; ?>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>