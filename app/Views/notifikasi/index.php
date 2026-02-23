<?php 
include __DIR__.'/../partials/sidebar.php'; 
?>

<div class="m-5">

    <!-- HEADER -->
    <div class="mb-10">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Notifikasi Anda</h1>

        <p class="text-slate-500 mt-1">
            Total notifikasi belum dibaca: 
            <span class="font-bold text-blue-600"><?php echo $total ?? 0; ?></span>
        </p>

        <p class="text-xs text-slate-400 mt-1">
            Catatan: Notifikasi akan otomatis terhapus setelah Anda membacanya.
        </p>
    </div>

    <?php if (empty($notifikasi)): ?>

        <!-- EMPTY STATE (mengikuti layout card sebelumnya) -->
        <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-200 w-[380px]">
            <span class="material-icons text-slate-300 text-6xl mb-4">notifications_off</span>

            <h3 class="text-lg font-bold text-slate-700">Tidak Ada Notifikasi Baru</h3>
            <p class="text-slate-500">Semua notifikasi sudah dibaca atau dihapus.</p>
        </div>

    <?php else: ?>

        <!-- LIST NOTIFIKASI -->
        <div class="space-y-4">

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

            <!-- CARD NOTIFIKASI -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition border border-slate-200 p-5 flex gap-5 items-start w-[700px]">

                <span class="material-icons text-2xl <?php echo $color; ?> mt-1">
                    <?php echo $icon; ?>
                </span>

                <div class="flex-1">
                    <p class="font-bold text-slate-800 text-sm">
                        <?php echo htmlspecialchars($n['judul']); ?>
                    </p>

                    <p class="text-xs text-slate-600 mt-1 mb-2">
                        <?php echo htmlspecialchars($n['pesan']); ?>
                    </p>

                    <p class="text-[11px] text-slate-400 font-mono">
                        <?php echo date('d M Y H:i', strtotime($n['created_at'])); ?>
                    </p>
                </div>

                <a href="/notifikasi/read?id=<?php echo $n['id']; ?>&link=<?php echo urlencode($n['link'] ?: '/dashboard'); ?>"
                   class="px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition">
                    Lihat Detail
                </a>

            </div>

            <?php endforeach; ?>

        </div>

        <div class="mt-6 text-slate-500 text-xs">
            Halaman <?php echo $page; ?> dari <?php echo $totalPages; ?>
        </div>

    <?php endif; ?>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>
