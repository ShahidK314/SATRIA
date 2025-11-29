<?php include __DIR__.'/../partials/sidebar.php'; ?>

<div class="p-8 max-w-6xl mx-auto">
    <div class="mb-8">
        <a href="/approval" class="text-slate-500 hover:text-indigo-600 font-bold flex items-center gap-2 mb-4 transition-colors">
            <span class="material-icons text-sm">arrow_back</span> Kembali ke Antrian
        </a>
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Keputusan Pimpinan</h1>
                <p class="text-slate-500 mt-1">Persetujuan anggaran dan validasi dokumen pendukung.</p>
            </div>
            <div class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg font-bold border border-indigo-100 text-sm">
                Tahap: <?php echo $usulan['status_terkini']; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-bl-full -mr-4 -mt-4"></div>
                
                <h2 class="text-xl font-bold text-slate-800 mb-6 relative z-10"><?php echo htmlspecialchars($usulan['nama_kegiatan']); ?></h2>
                
                <div class="grid grid-cols-2 gap-6 relative z-10">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Unit Pengusul</label>
                        <p class="text-slate-700 font-medium"><?php echo htmlspecialchars($usulan['nama_jurusan'] ?? 'General'); ?></p>
                        <p class="text-xs text-slate-500"><?php echo htmlspecialchars($usulan['username']); ?></p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Kode MAK</label>
                        <span class="inline-block px-2 py-1 bg-slate-100 border border-slate-200 rounded text-sm font-mono font-bold text-slate-700">
                            <?php echo htmlspecialchars($usulan['kode_mak'] ?? '-'); ?>
                        </span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Gambaran Umum</label>
                    <p class="text-slate-600 text-sm leading-relaxed text-justify">
                        <?php echo nl2br(htmlspecialchars($usulan['gambaran_umum'])); ?>
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center">
                    <span class="material-icons text-indigo-600 mr-2">folder_shared</span> Dokumen Administrasi
                </h3>
                
                <?php if(empty($docs)): ?>
                    <div class="p-4 bg-rose-50 text-rose-700 rounded-lg border border-rose-200 text-sm flex items-center">
                        <span class="material-icons mr-2">warning</span> 
                        <strong>Warning:</strong> Pengusul belum mengunggah Surat Pengantar!
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 gap-3">
                        <?php foreach($docs as $d): ?>
                        <a href="<?php echo htmlspecialchars($d['file_path']); ?>" target="_blank" class="flex items-center p-4 border border-slate-200 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition-all group">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center mr-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                <span class="material-icons">description</span>
                            </div>
                            <div class="flex-1">
                                <div class="font-bold text-slate-700 group-hover:text-indigo-700"><?php echo htmlspecialchars($d['jenis_dokumen']); ?></div>
                                <div class="text-xs text-slate-400">Klik untuk melihat dokumen PDF</div>
                            </div>
                            <span class="material-icons text-slate-300 group-hover:text-indigo-500">open_in_new</span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center">
                    <span class="material-icons text-emerald-600 mr-2">pie_chart</span> Rekapitulasi Anggaran
                </h3>
                <div class="space-y-3">
                    <?php foreach($rabSummary as $rs): ?>
                    <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                        <span class="text-sm text-slate-600 font-medium"><?php echo htmlspecialchars($rs['nama_kategori']); ?></span>
                        <span class="text-sm font-bold text-slate-800">Rp <?php echo number_format($rs['subtotal'], 0, ',', '.'); ?></span>
                    </div>
                    <?php endforeach; ?>
                    <div class="flex justify-between items-center p-4 bg-slate-800 text-white rounded-lg mt-2 shadow-lg">
                        <span class="text-sm font-bold uppercase tracking-wider">Total Disetujui</span>
                        <span class="text-lg font-extrabold">Rp <?php echo number_format($usulan['nominal_pencairan'], 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-lg border border-indigo-100 overflow-hidden sticky top-6">
                <div class="bg-indigo-900 p-6">
                    <h3 class="text-white font-bold text-lg flex items-center">
                        <span class="material-icons mr-2">verified_user</span> Lembar Persetujuan
                    </h3>
                    <p class="text-indigo-200 text-xs mt-1">Pastikan dokumen fisik/digital telah sesuai.</p>
                </div>
                
                <form action="/approval/aksi?id=<?php echo $usulan['id']; ?>" method="POST" class="p-6 space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Catatan Pimpinan</label>
                        <textarea name="catatan" rows="4" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm transition-all" placeholder="Berikan arahan, catatan revisi, atau alasan penolakan..."></textarea>
                    </div>

                    <div class="space-y-3 pt-2">
                        <button type="submit" name="aksi" value="setuju" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 hover:-translate-y-1 transition-all flex justify-center items-center">
                            <span class="material-icons text-sm mr-2">check_circle</span> 
                            Setujui (Lanjut PPK)
                        </button>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <button type="submit" name="aksi" value="revisi" class="py-3 bg-white border border-amber-300 text-amber-600 font-bold rounded-xl hover:bg-amber-50 transition-all text-sm">
                                Revisi
                            </button>
                            <button type="submit" name="aksi" value="tolak" class="py-3 bg-white border border-rose-300 text-rose-600 font-bold rounded-xl hover:bg-rose-50 transition-all text-sm" onclick="return confirm('Tolak usulan ini secara permanen?');">
                                Tolak
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>