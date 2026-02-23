<?php 
// app/Views/verifikasi/riwayat.php (FINALIZED)
include __DIR__.'/../partials/sidebar.php'; 
// Asumsi variabel: $usulan (riwayat), $total
if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}
function getStatusClass($status) {
    return match($status) {
        'Disetujui' => 'bg-emerald-100 text-emerald-700',
        'Revisi' => 'bg-rose-100 text-rose-700',
        'Ditolak' => 'bg-slate-800 text-white',
        default => 'bg-gray-100 text-gray-700',
    };
}
?>

<div class="m-5">
    <div class="mb-8">
        <a href="/verifikasi" class="text-slate-500 hover:text-blue-600 font-bold flex items-center gap-2 mb-4 transition-colors">
            <span class="material-icons text-sm">arrow_back</span> Kembali ke Antrian
        </a>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Riwayat Putusan Usulan</h1>
        <p class="text-slate-500 mt-1">Daftar usulan yang telah Anda putuskan (Disetujui, Revisi, atau Ditolak).</p>
    </div>

    <?php if(empty($usulan)): ?>
        <div class="bg-white rounded-xl p-12 text-center shadow-sm border border-slate-200">
            <span class="material-icons text-slate-300 text-6xl mb-4">archive</span>
            <h3 class="text-lg font-bold text-slate-700">Belum Ada Riwayat Putusan</h3>
            <p class="text-slate-500">Anda belum membuat keputusan pada usulan manapun.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Nama Kegiatan</th>
                            <th class="px-6 py-4">Pengusul</th>
                            <th class="px-6 py-4">Anggaran</th>
                            <th class="px-6 py-4 w-24 text-center">Status Putusan</th>
                            <th class="px-6 py-4 w-40 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($usulan as $row): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-800"><?php echo htmlspecialchars($row['nama_kegiatan']); ?></td>
                            <td class="px-6 py-4">
                                <p class="text-slate-700"><?php echo htmlspecialchars($row['username']); ?></p>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($row['nama_jurusan']); ?></p>
                            </td>
                            <td class="px-6 py-4 text-emerald-600 font-bold">
                                <?php echo formatRupiah($row['nominal_pencairan']); ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded text-xs font-bold <?php echo getStatusClass($row['status_terkini']); ?>">
                                    <?php echo htmlspecialchars($row['status_terkini']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="/usulan/detail?id=<?php echo $row['id']; ?>" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 transition-all text-xs">
                                    <span class="material-icons text-xs align-middle mr-1">visibility</span> Lihat Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>