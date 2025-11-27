<?php include __DIR__.'/../partials/sidebar.php'; ?>

<div class="p-8 max-w-5xl mx-auto">
    <div class="mb-8">
        <a href="/approval" class="text-slate-500 hover:text-indigo-600 font-bold flex items-center gap-2 mb-4 transition-colors">
            <span class="material-icons text-sm">arrow_back</span> Kembali ke Antrian
        </a>
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Keputusan Pimpinan</h1>
                <p class="text-slate-500 mt-1">Persetujuan anggaran dan pelaksanaan kegiatan.</p>
            </div>
            <div class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg font-bold border border-indigo-100 text-sm">
                Tahap: <?php echo $usulan['status_terkini']; ?>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden mb-8">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-200 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Kegiatan</label>
                <p class="font-bold text-slate-800"><?php echo htmlspecialchars($usulan['nama_kegiatan']); ?></p>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Anggaran</label>
                <p class="font-mono font-bold text-emerald-600 text-lg">Rp <?php echo number_format($usulan['nominal_pencairan'], 0, ',', '.'); ?></p>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Kode MAK</label>
                <p class="font-mono font-bold text-slate-700 bg-slate-200 px-2 py-0.5 rounded inline-block text-sm">
                    <?php echo htmlspecialchars($usulan['kode_mak'] ?? '-'); ?>
                </p>
            </div>
        </div>

        <div class="p-8 grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div class="space-y-6">
                <div>
                    <h3 class="font-bold text-slate-800 mb-2 flex items-center">
                        <span class="material-icons text-indigo-500 text-sm mr-2">description</span> Gambaran Umum
                    </h3>
                    <p class="text-slate-600 text-sm leading-relaxed text-justify">
                        <?php echo nl2br(htmlspecialchars($usulan['gambaran_umum'])); ?>
                    </p>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 mb-2 flex items-center">
                        <span class="material-icons text-indigo-500 text-sm mr-2">groups</span> Penerima Manfaat
                    </h3>
                    <p class="text-slate-600 text-sm"><?php echo htmlspecialchars($usulan['penerima_manfaat']); ?></p>
                </div>
            </div>

            <div class="bg-indigo-50/50 p-6 rounded-xl border border-indigo-100 h-fit">
                <h3 class="font-bold text-indigo-900 mb-4 flex items-center">
                    <span class="material-icons mr-2">gavel</span> Keputusan Anda
                </h3>
                
                <form action="/approval/aksi?id=<?php echo $usulan['id']; ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-indigo-800 uppercase mb-2">Catatan Pimpinan (Opsional)</label>
                        <textarea name="catatan" rows="3" class="w-full px-3 py-2 bg-white border border-indigo-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm" placeholder="Contoh: Disetujui dengan catatan hemat anggaran..."></textarea>
                    </div>

                    <div class="space-y-3">
                        <button type="submit" name="aksi" value="setuju" class="w-full py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/20 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all flex justify-center items-center">
                            <span class="material-icons text-sm mr-2">verified</span> 
                            Setujui & Teruskan
                        </button>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <button type="submit" name="aksi" value="revisi" class="w-full py-2.5 bg-white border border-amber-300 text-amber-700 font-bold rounded-xl hover:bg-amber-50 transition-all text-sm">
                                Kembalikan (Revisi)
                            </button>
                            <button type="submit" name="aksi" value="tolak" class="w-full py-2.5 bg-white border border-rose-300 text-rose-700 font-bold rounded-xl hover:bg-rose-50 transition-all text-sm" onclick="return confirm('Tolak usulan ini secara permanen?');">
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