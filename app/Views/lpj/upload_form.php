<?php 
// app/Views/lpj/upload_form.php
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format((float)$number, 0, ',', '.');
    }
}
?>

<div class="m-4 md:m-5">
    <div class="mb-6 md:mb-8 text-center md:text-left">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Pengajuan LPJ</h1>
        <p class="text-slate-500 mt-1 text-sm md:text-base">Daftar kegiatan yang telah dicairkan dan memerlukan Laporan Pertanggungjawaban.</p>
    </div>

    <?php if (empty($lpjList)): ?>
        <div class="bg-white rounded-xl p-8 md:p-12 text-center shadow-sm border border-slate-200">
            <span class="material-icons text-slate-300 text-6xl mb-4">receipt_long</span>
            <h3 class="text-lg font-bold text-slate-700">Belum Ada Tagihan LPJ</h3>
            <p class="text-slate-500 text-sm">Kegiatan Anda belum dicairkan oleh Bendahara.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto w-full custom-scrollbar">
                <table class="w-full text-sm text-left whitespace-nowrap min-w-[800px]">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Nama Kegiatan</th>
                            <th class="px-6 py-4 text-right">Total Cair (Akumulasi)</th>
                            <th class="px-6 py-4 w-32 text-center">Batas LPJ</th>
                            <th class="px-6 py-4 w-24 text-center">Status</th>
                            <th class="px-6 py-4 w-40 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($lpjList as $row): 
                            $isOverdue = (new DateTime() > new DateTime($row['tanggal_batas_lpj']));
                            $statusClass = $row['status_terkini'] === 'Revisi' ? 'bg-rose-100 text-rose-700 animate-pulse' : 'bg-amber-100 text-amber-700';
                            $deadlineClass = $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-600';
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-800 whitespace-normal line-clamp-2 md:line-clamp-none">
                                    <?php echo htmlspecialchars($row['nama_kegiatan']); ?>
                                </span>
                                <?php if($row['status_terkini'] === 'Revisi'): ?><span class="text-rose-600 text-xs md:ml-2 block md:inline mt-1 md:mt-0">(Revisi)</span><?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right text-emerald-600 font-bold font-mono">
                                <?php echo formatRupiah($row['nominal_dicairkan']); ?>
                            </td>
                            <td class="px-6 py-4 text-center <?php echo $deadlineClass; ?>">
                                <?php echo date('d M Y', strtotime($row['tanggal_batas_lpj'])); ?>
                                <?php if($isOverdue && $row['status_terkini'] !== 'Disetujui'): ?><div class="text-[10px] text-rose-600 font-bold mt-1">TERLAMBAT</div><?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded text-[10px] font-bold border border-slate-100 <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($row['status_terkini']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($row['status_terkini'] == 'Disetujui'): ?>
                                    <span class="text-xs text-emerald-600 font-bold flex items-center justify-end gap-1">
                                        <span class="material-icons text-sm">verified</span> Selesai
                                    </span>
                                <?php else: ?>
                                    <a href="/lpj/upload/detail?id=<?php echo $row['id']; ?>" class="inline-flex px-4 py-2 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition-all text-xs items-center justify-center gap-1 shadow-sm">
                                        <span class="material-icons text-xs">edit</span> Ajukan LPJ
                                    </a>
                                <?php endif; ?>
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