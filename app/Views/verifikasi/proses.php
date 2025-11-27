<?php include __DIR__.'/../partials/sidebar.php'; ?>

<div class="p-8 max-w-5xl mx-auto">
    <div class="mb-8">
        <a href="/verifikasi" class="text-slate-500 hover:text-emerald-600 font-bold flex items-center gap-2 mb-4 transition-colors">
            <span class="material-icons text-sm">arrow_back</span> Kembali ke Antrian
        </a>
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Review Dokumen</h1>
                <p class="text-slate-500 mt-1">Verifikasi kelengkapan dan kelayakan usulan kegiatan.</p>
            </div>
            <div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg font-bold border border-emerald-100">
                Status: <?php echo $usulan['status_terkini']; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <h3 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-100 pb-4">Informasi Kegiatan (TOR)</h3>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Kegiatan</label>
                        <p class="text-slate-800 font-medium text-lg leading-relaxed"><?php echo htmlspecialchars($usulan['nama_kegiatan']); ?></p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Penerima Manfaat</label>
                            <p class="text-slate-700"><?php echo htmlspecialchars($usulan['penerima_manfaat']); ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal Pengajuan</label>
                            <p class="text-slate-700"><?php echo date('d F Y', strtotime($usulan['created_at'])); ?></p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Gambaran Umum</label>
                        <div class="bg-slate-50 p-4 rounded-xl text-slate-600 text-sm leading-relaxed border border-slate-100">
                            <?php echo nl2br(htmlspecialchars($usulan['gambaran_umum'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                <div class="bg-slate-900 px-8 py-4 flex items-center">
                    <span class="material-icons text-emerald-400 mr-3">gavel</span>
                    <h3 class="text-lg font-bold text-white">Panel Keputusan Verifikator</h3>
                </div>
                <div class="p-8">
                    <form action="/verifikasi/aksi?id=<?php echo $usulan['id']; ?>" method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Kode MAK (Mata Anggaran Kegiatan)</label>
                            <input type="text" name="kode_mak" placeholder="Contoh: 521211 (Belanja Bahan)" 
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-600 outline-none transition-all font-mono text-sm" required>
                            <p class="text-xs text-slate-500 mt-2">Wajib diisi untuk pengelompokan anggaran.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Catatan Verifikasi</label>
                            <textarea name="catatan" rows="3" placeholder="Tuliskan catatan perbaikan (jika revisi) atau keterangan tambahan..." 
                                      class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-600 outline-none transition-all"></textarea>
                        </div>

                        <div class="flex gap-3 pt-4 border-t border-slate-100">
                            <button type="submit" name="aksi" value="revisi" class="flex-1 px-4 py-3 bg-white border-2 border-amber-500 text-amber-600 font-bold rounded-xl hover:bg-amber-50 transition-all flex justify-center items-center">
                                <span class="material-icons text-sm mr-2">edit_note</span> Minta Revisi
                            </button>
                            <button type="submit" name="aksi" value="tolak" class="flex-1 px-4 py-3 bg-white border-2 border-rose-500 text-rose-600 font-bold rounded-xl hover:bg-rose-50 transition-all flex justify-center items-center" onclick="return confirm('Yakin ingin menolak permanen?');">
                                <span class="material-icons text-sm mr-2">block</span> Tolak
                            </button>
                            <button type="submit" name="aksi" value="setuju" class="flex-[2] px-4 py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 hover:bg-emerald-700 hover:-translate-y-1 transition-all flex justify-center items-center">
                                <span class="material-icons text-sm mr-2">check_circle</span> Verifikasi & Teruskan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-xl font-bold text-slate-600">
                        <?php echo strtoupper(substr($usulan['username'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase">Pengusul</p>
                        <p class="font-bold text-slate-800"><?php echo htmlspecialchars($usulan['username'] ?? 'User'); ?></p>
                        <p class="text-xs text-slate-500"><?php echo htmlspecialchars($usulan['email'] ?? '-'); ?></p>
                    </div>
                </div>
                
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
                    <p class="text-xs text-amber-600 font-bold uppercase mb-1">Total Pengajuan</p>
                    <p class="text-2xl font-black text-amber-700 font-mono">
                        Rp <?php echo number_format($usulan['nominal_pencairan'] ?? 0, 0, ',', '.'); ?>
                        <span class="text-xs font-normal text-amber-600 align-middle">*Estimasi</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>