<?php 
// app/Views/usulan/index_pengajuan_kegiatan.php (FINALIZED)
include __DIR__.'/../partials/sidebar.php'; 
// Asumsi variabel: $usulan (list kegiatan yang status_terkini = Disetujui dan belum ada di pengajuan_kegiatan)
if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}
?>

<div class="m-5">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Mengajukan Kegiatan (Step 2)</h1>
        <p class="text-slate-500 mt-1">Daftar usulan yang telah disetujui oleh Verifikator dan siap diajukan ke PPK.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <?php if (empty($usulan)): ?>
            <div class="p-12 text-center">
                <span class="material-icons text-slate-300 text-6xl mb-4">task_alt</span>
                <h3 class="text-lg font-bold text-slate-700">Antrian Pengajuan Kegiatan Kosong</h3>
                <p class="text-slate-500">Tidak ada usulan yang menunggu persetujuan dari Verifikator atau sudah terlanjur diajukan ke PPK.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Nama Kegiatan</th>
                            <th class="px-6 py-4">Anggaran</th>
                            <th class="px-6 py-4">Kurun Waktu</th>
                            <th class="px-6 py-4 w-40 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($usulan as $row): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-800"><?php echo htmlspecialchars($row['nama_kegiatan']); ?></td>
                            <td class="px-6 py-4 text-emerald-600 font-bold">
                                <?php echo formatRupiah($row['nominal_pencairan']); ?>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                <?php echo date('d M Y', strtotime($row['tanggal_mulai'])); ?> - <?php echo date('d M Y', strtotime($row['tanggal_selesai'])); ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="/pengajuan/form?id=<?php echo $row['id']; ?>" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-all text-xs">
                                    <span class="material-icons text-xs align-middle mr-1">send</span> Ajukan Kegiatan
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>