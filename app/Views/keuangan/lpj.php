<?php 
// app/Views/keuangan/lpj.php
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}
?>

<div class="m-5">
    <div class="mb-6 md:mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Verifikasi LPJ</h1>
        <p class="text-slate-500 mt-1 text-sm md:text-base">Pemeriksaan dokumen Laporan Pertanggungjawaban kegiatan yang diajukan pengusul.</p>
    </div>

    <div class="flex justify-end mb-4">
        <a href="/lpj/riwayat" class="inline-flex items-center px-4 py-2 bg-slate-100 border border-slate-200 text-slate-700 font-bold rounded-lg hover:bg-slate-200 transition-colors text-sm">
            <span class="material-icons text-sm mr-2">history</span> Riwayat Disetujui
        </a>
    </div>

    <?php if (empty($usulan)): ?>
        <div class="bg-white rounded-xl p-12 text-center shadow-sm border border-slate-200">
            <span class="material-icons text-slate-300 text-6xl mb-4">task_alt</span>
            <h3 class="text-lg font-bold text-slate-700">Antrian LPJ Kosong</h3>
            <p class="text-slate-500">
                Tidak ada LPJ dengan status 'Diajukan' atau 'Revisi' saat ini.
            </p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Kegiatan</th>
                            <th class="px-6 py-4">Pengusul</th>
                            <th class="px-6 py-4">Tanggal Submit</th>
                            <th class="px-6 py-4">Total RAB</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($usulan as $row): 
                            $statusClass = $row['status_terkini'] === 'Diajukan' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700 animate-pulse';
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800 whitespace-normal min-w-[200px]"><?php echo htmlspecialchars($row['nama_kegiatan']); ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-slate-700"><?php echo htmlspecialchars($row['username']); ?></p>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($row['nama_jurusan']); ?></p>
                            </td>
                            <td class="px-6 py-4 text-slate-600 text-xs">
                                <?php echo date('d M Y H:i', strtotime($row['tanggal_submit'])); ?>
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-bold">
                                <?php echo formatRupiah($row['nominal_pencairan'] ?? 0); ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded text-[10px] font-bold uppercase <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($row['status_terkini']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="/lpj/detail?id=<?php echo $row['id']; ?>" class="px-4 py-2 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition-all text-xs">
                                    <span class="material-icons text-xs align-middle mr-1">task</span> Periksa
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