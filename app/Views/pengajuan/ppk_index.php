<?php 
// app/Views/pengajuan/ppk_index.php
include __DIR__.'/../partials/sidebar.php'; 
// Asumsi variabel: $pengajuan (antrian)
if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}
?>

<div class="m-4 md:m-5">

    <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6 md:mb-8 gap-4">

        <div class="flex flex-col sm:flex-row sm:items-center gap-4 md:gap-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Monitoring Persetujuan PPK
                </h1>
                <p class="text-slate-500 mt-1 text-sm md:text-base">
                    Daftar kegiatan yang menunggu persetujuan Anda
                </p>
            </div>

            <div class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg font-bold border border-blue-100 w-fit whitespace-nowrap shadow-sm">
                <span class="material-icons text-sm mr-1 align-middle">pending_actions</span>
                Menunggu: <?= count($pengajuan); ?>
            </div>
        </div>

        <a href="/pengajuan/ppk/riwayat"
           class="w-full lg:w-auto inline-flex justify-center items-center px-4 py-2.5 bg-slate-100 border border-slate-200 
                  text-slate-700 font-bold rounded-lg hover:bg-slate-200 transition-colors text-sm shadow-sm">
            <span class="material-icons text-sm mr-2">history</span>
            Riwayat Persetujuan
        </a>
    </div>

    <?php if(empty($pengajuan)): ?>

        <div class="bg-white shadow-sm rounded-xl border border-slate-200 p-8 md:p-10">
            <div class="text-center text-slate-600">
                <span class="material-icons text-4xl md:text-5xl mb-3 text-slate-300">task_alt</span>
                <h2 class="text-base md:text-lg font-bold text-slate-700 mb-1">Semua Selesai</h2>
                <p class="text-sm text-slate-500">Tidak ada pengajuan kegiatan yang menunggu persetujuan PPK saat ini.</p>
            </div>
        </div>

    <?php else: ?>

        <div class="grid grid-cols-1 gap-4 md:gap-6">
            <?php foreach($pengajuan as $row): ?>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-all">

                <div class="bg-blue-50/50 px-4 md:px-6 py-4 border-b border-blue-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-start sm:items-center gap-3">
                            <span class="px-3 py-1 bg-blue-600 text-white rounded-full text-[10px] md:text-xs font-bold shrink-0 mt-0.5 sm:mt-0">
                                #<?= $row['id']; ?>
                            </span>
                            <h3 class="text-base md:text-lg font-bold text-slate-800 leading-snug"><?= htmlspecialchars($row['nama_kegiatan']); ?></h3>
                        </div>
                        
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-full text-[10px] md:text-xs font-bold w-fit shrink-0 whitespace-nowrap">
                            ✓ Menunggu PPK
                        </span>
                    </div>
                </div>

                <div class="p-4 md:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-6">

                        <div>
                            <div class="text-[10px] md:text-xs text-slate-400 uppercase font-bold mb-1.5">Pengusul</div>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold shrink-0">
                                    <?= strtoupper(substr($row['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-800 font-bold"><?= htmlspecialchars($row['username']); ?></div>
                                    <div class="text-[10px] md:text-xs text-slate-500 leading-tight"><?= htmlspecialchars($row['nama_jurusan']); ?></div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="text-[10px] md:text-xs text-slate-400 uppercase font-bold mb-1.5">Penanggung Jawab</div>
                            <div class="text-sm text-slate-700 font-medium">
                                <?= htmlspecialchars($row['penanggung_jawab']); ?>
                            </div>
                            <?php if(!empty($row['kode_mak'])): ?>
                                <div class="text-[10px] md:text-xs font-bold text-slate-400 mt-1 bg-slate-50 inline-block px-2 py-0.5 rounded border border-slate-100">MAK: <?= htmlspecialchars($row['kode_mak']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <div class="text-[10px] md:text-xs text-slate-400 uppercase font-bold mb-1.5">Anggaran</div>
                            <div class="text-base md:text-lg font-bold text-emerald-600 font-mono">
                                Rp <?= number_format($row['nominal_pencairan'], 0, ',', '.'); ?>
                            </div>
                        </div>

                        <div>
                            <div class="text-[10px] md:text-xs text-slate-400 uppercase font-bold mb-1.5">Tanggal Pelaksanaan</div>
                            <div class="text-xs md:text-sm text-slate-700 font-medium bg-slate-50 px-2 py-1.5 rounded-lg border border-slate-100 inline-block">
                                <?= date('d M', strtotime($row['tanggal_mulai'])); ?> -
                                <?= date('d M Y', strtotime($row['tanggal_selesai'])); ?>
                            </div>
                        </div>

                    </div>

                    <div class="flex justify-end pt-2 border-t border-slate-100 mt-2">
                        <a href="/pengajuan/ppk/detail?id=<?= $row['id']; ?>" 
                           class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-sm transition-all text-sm">
                            <span class="material-icons text-sm mr-2">gavel</span> Beri Putusan
                        </a>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<?php include __DIR__.'/../partials/footer.php'; ?>