<?php include __DIR__.'/../partials/sidebar.php'; ?>

<div class="p-4 md:p-8 max-w-7xl mx-auto">
    <div class="mb-6 md:mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Verifikasi LPJ</h1>
        <p class="text-slate-500 mt-1 text-sm md:text-base">Pemeriksaan dokumen Laporan Pertanggungjawaban kegiatan.</p>
    </div>

    <?php if (empty($usulan)): ?>
        <div class="bg-white rounded-xl p-12 text-center shadow-sm border border-slate-200">
            <span class="material-icons text-slate-300 text-6xl mb-4">task_alt</span>
            <h3 class="text-lg font-bold text-slate-700">Semua Beres</h3>
            <p class="text-slate-500">Belum ada kegiatan yang masuk masa pelaporan LPJ.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Kegiatan</th>
                            <th class="px-6 py-4">Status LPJ</th>
                            <th class="px-6 py-4">Deadline</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($usulan as $row): 
                            // [FIXED] Safe Date Checking
                            $isOverdue = false;
                            if (!empty($row['tgl_batas_lpj'])) {
                                $isOverdue = new DateTime() > new DateTime($row['tgl_batas_lpj']);
                            }
                            
                            $statusClass = $row['status_terkini'] === 'LPJ' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700';
                            $label = $row['status_terkini'] === 'LPJ' ? 'Sudah Upload' : 'Menunggu Upload';
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800 whitespace-normal min-w-[200px]"><?php echo htmlspecialchars($row['nama_kegiatan']); ?></p>
                                <p class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars($row['username']); ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $statusClass; ?>">
                                    <?php echo $label; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="<?php echo $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-600'; ?>">
                                    <?php echo !empty($row['tgl_batas_lpj']) ? date('d M Y', strtotime($row['tgl_batas_lpj'])) : '-'; ?>
                                </div>
                                <?php if($isOverdue): ?>
                                    <span class="text-[10px] text-rose-500 uppercase font-bold">Terlambat</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right min-w-[300px]">
                                <div class="flex flex-col gap-2 items-end">
                                    <div class="flex justify-end gap-2">
                                        <a href="/usulan/detail?id=<?php echo $row['id']; ?>" class="px-3 py-2 bg-white border border-slate-200 text-slate-600 font-bold rounded-lg hover:bg-slate-50 transition-all text-xs">
                                            <span class="material-icons text-xs align-middle mr-1">visibility</span> Dokumen
                                        </a>
                                        
                                        <a href="/pdf/berita_acara?id=<?php echo $row['id']; ?>" target="_blank" class="px-3 py-2 bg-slate-800 text-white font-bold rounded-lg hover:bg-slate-700 transition-all text-xs">
                                            <span class="material-icons text-xs align-middle mr-1">print</span> B.A.
                                        </a>
                                    </div>

                                    <?php if($row['status_terkini'] === 'LPJ'): ?>
                                        <form action="/lpj/verifikasi?id=<?php echo $row['id']; ?>" method="POST" class="bg-slate-50 p-3 rounded-lg border border-slate-200 mt-2 text-left w-full max-w-[300px]">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            
                                            <input type="text" name="catatan" placeholder="Catatan (Wajib jika revisi)..." class="w-full mb-2 px-2 py-1 text-xs border border-slate-300 rounded">
                                            
                                            <div class="flex gap-2">
                                                <button type="submit" name="aksi" value="revisi" class="flex-1 py-1.5 bg-white border border-amber-500 text-amber-600 text-xs font-bold rounded hover:bg-amber-50" onclick="return confirm('Kembalikan ke pengusul untuk revisi?');">
                                                    Minta Revisi
                                                </button>
                                                <button type="submit" name="aksi" value="setuju" class="flex-1 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded hover:bg-emerald-700 shadow-sm" onclick="return confirm('Pastikan semua dokumen fisik sudah lengkap. Tutup siklus?');">
                                                    Terima & Selesai
                                                </button>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <div class="mt-1 text-xs text-slate-400 italic text-right">
                                            Menunggu pengusul upload dokumen...
                                        </div>
                                    <?php endif; ?>
                                </div>
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