<?php 
// app/Views/verifikasi/index.php
include __DIR__.'/../partials/sidebar.php'; 
// Asumsi variabel: $usulan (antrian), $total, $page, $totalPages
if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}
?>

<div class="m-4 md:m-5">
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Antrian Verifikasi Usulan</h1>
            <p class="text-slate-500 mt-1 text-sm md:text-base">Total usulan menunggu: <span class="font-bold text-blue-600"><?php echo $total ?? 0; ?></span></p>
        </div>
        
        <a href="/verifikasi/riwayat" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-slate-100 border border-slate-200 text-slate-700 font-bold rounded-lg hover:bg-slate-200 transition-colors text-sm shadow-sm">
            <span class="material-icons text-sm mr-2">history</span> Riwayat Putusan
        </a>
    </div>

    <?php if(empty($usulan)): ?>
        <div class="bg-white rounded-xl p-8 md:p-12 text-center shadow-sm border border-slate-200">
            <span class="material-icons text-slate-300 text-5xl md:text-6xl mb-4">task_alt</span>
            <h3 class="text-base md:text-lg font-bold text-slate-700">Antrian Verifikasi Kosong</h3>
            <p class="text-slate-500 text-sm mt-1">Tidak ada usulan baru yang menunggu verifikasi saat ini.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto w-full custom-scrollbar">
                <table class="w-full text-sm text-left whitespace-nowrap min-w-[750px]">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] md:text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-4 md:px-6 py-4 w-1/3">Nama Kegiatan</th>
                            <th class="px-4 md:px-6 py-4">Pengusul</th>
                            <th class="px-4 md:px-6 py-4">Anggaran</th>
                            <th class="px-4 md:px-6 py-4">Diajukan Pada</th>
                            <th class="px-4 md:px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($usulan as $row): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 md:px-6 py-4 font-bold text-slate-800 whitespace-normal min-w-[200px] leading-snug">
                                <?php echo htmlspecialchars($row['nama_kegiatan']); ?>
                            </td>
                            <td class="px-4 md:px-6 py-4">
                                <p class="text-slate-700 font-medium"><?php echo htmlspecialchars($row['username']); ?></p>
                                <p class="text-[11px] md:text-xs text-slate-500 mt-0.5"><?php echo htmlspecialchars($row['nama_jurusan']); ?></p>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-emerald-600 font-bold font-mono">
                                <?php echo formatRupiah($row['nominal_pencairan']); ?>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-xs text-slate-600">
                                <?php echo date('d M Y H:i', strtotime($row['created_at'])); ?>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-right">
                                <a href="/verifikasi/proses?id=<?php echo $row['id']; ?>" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-all text-xs w-full sm:w-auto shadow-sm">
                                    <span class="material-icons text-[14px] align-middle mr-1">check</span> Putuskan
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 md:px-6 py-4 border-t border-slate-100 bg-slate-50 flex flex-col sm:flex-row justify-between items-center gap-3">
                <div class="text-xs text-slate-500 font-medium">Halaman <strong class="text-slate-700"><?php echo $page; ?></strong> dari <strong class="text-slate-700"><?php echo $totalPages; ?></strong></div>
                <div class="flex gap-2 w-full sm:w-auto justify-center">
                    <?php if($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 text-xs font-bold shadow-sm transition-colors text-center flex-1 sm:flex-none">Prev</a>
                    <?php endif; ?>
                    
                    <?php if($page < $totalPages): ?>
                        <a href="?page=<?php echo $page+1; ?>" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 text-xs font-bold shadow-sm transition-colors text-center flex-1 sm:flex-none">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>