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

<div class="m-5">

    <div class="flex items-center justify-between mb-8">

        <div class="flex items-center gap-6">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                    Monitoring Persetujuan PPK
                </h1>
                <p class="text-slate-500 mt-1">
                    Daftar kegiatan yang menunggu persetujuan Anda
                </p>
            </div>

            <div class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg font-bold border border-blue-100">
                <span class="material-icons text-sm mr-1 align-middle">pending_actions</span>
                Menunggu: <?= count($pengajuan); ?>
            </div>
        </div>

        <a href="/pengajuan/ppk/riwayat"
           class="inline-flex items-center px-4 py-2 bg-slate-100 border border-slate-200 
                  text-slate-700 font-bold rounded-lg hover:bg-slate-200 transition-colors text-sm">
            <span class="material-icons text-sm mr-2">history</span>
            Riwayat Persetujuan
        </a>
    </div>

    <?php if(empty($pengajuan)): ?>

        <div class="bg-white shadow rounded-xl border p-10">
            <div class="text-center text-slate-600">
                <span class="material-icons text-3xl mb-2 text-slate-400">task_alt</span>
                <h2 class="text-lg font-semibold mb-1">Semua Selesai</h2>
                <p class="text-sm">Tidak ada pengajuan kegiatan yang menunggu persetujuan PPK saat ini.</p>
            </div>
        </div>

    <?php else: ?>

        <div class="grid gap-6">
            <?php foreach($pengajuan as $row): ?>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-lg transition-all">

                <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 bg-blue-600 text-white rounded-full text-xs font-bold">
                                #<?= $row['id']; ?>
                            </span>
                            <h3 class="text-lg font-bold text-slate-800"><?= htmlspecialchars($row['nama_kegiatan']); ?></h3>
                        </div>
                        
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">
                            ✓ Menunggu PPK
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">

                        <div>
                            <div class="text-xs text-slate-400 uppercase font-bold mb-1">Pengusul</div>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                                    <?= strtoupper(substr($row['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-800 font-bold"><?= htmlspecialchars($row['username']); ?></div>
                                    <div class="text-xs text-slate-500"><?= htmlspecialchars($row['nama_jurusan']); ?></div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-400 uppercase font-bold mb-1">Penanggung Jawab</div>
                            <div class="text-sm text-slate-700 font-medium">
                                <?= htmlspecialchars($row['penanggung_jawab']); ?>
                            </div>
                            <?php if(!empty($row['kode_mak'])): ?>
                                <div class="text-xs text-slate-400 mt-1">MAK: <?= htmlspecialchars($row['kode_mak']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <div class="text-xs text-slate-400 uppercase font-bold mb-1">Anggaran</div>
                            <div class="text-lg font-bold text-emerald-600">
                                Rp <?= number_format($row['nominal_pencairan'], 0, ',', '.'); ?>
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-400 uppercase font-bold mb-1">Tanggal Pelaksanaan</div>
                            <div class="text-xs text-slate-600">
                                <?= date('d M', strtotime($row['tanggal_mulai'])); ?> -
                                <?= date('d M Y', strtotime($row['tanggal_selesai'])); ?>
                            </div>
                        </div>

                    </div>

                    <div class="flex justify-end">
                        <a href="/pengajuan/ppk/detail?id=<?= $row['id']; ?>" 
                           class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-bold rounded-lg 
                                  hover:bg-blue-700 shadow-md transition-all text-sm">
                            <span class="material-icons text-sm mr-2">gavel</span> Putusan
                        </a>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<?php include __DIR__.'/../partials/footer.php'; ?>