<?php include __DIR__.'/../partials/sidebar.php'; ?>

<div class="p-8 max-w-6xl mx-auto">
    <div class="mb-8">
        <a href="/verifikasi" class="text-slate-500 hover:text-emerald-600 font-bold flex items-center gap-2 mb-4 transition-colors">
            <span class="material-icons text-sm">arrow_back</span> Kembali ke Antrian
        </a>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Review Dokumen</h1>
                <p class="text-slate-500 mt-1">Verifikasi kelengkapan KAK, IKU, dan Kewajaran Anggaran (RAB).</p>
            </div>
            <div class="mt-4 md:mt-0 bg-slate-100 px-4 py-2 rounded-lg font-bold border border-slate-200 text-sm text-slate-600">
                ID Usulan: #<?php echo $usulan['id']; ?>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['toast'])): ?>
        <div class="mb-6 p-4 rounded-lg bg-rose-100 text-rose-700 border border-rose-200 font-bold text-sm">
            <?php echo $_SESSION['toast']['msg']; unset($_SESSION['toast']); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <div class="xl:col-span-2 space-y-8">
            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center">
                    <span class="material-icons text-emerald-600 mr-2">description</span> Kerangka Acuan Kerja (KAK)
                </h3>
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Kegiatan</label>
                        <p class="text-slate-800 font-bold text-lg"><?php echo htmlspecialchars($usulan['nama_kegiatan']); ?></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Penerima Manfaat</label>
                            <p class="text-slate-700 text-sm"><?php echo htmlspecialchars($usulan['penerima_manfaat']); ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pengusul</label>
                            <p class="text-slate-700 text-sm"><?php echo htmlspecialchars($usulan['username']); ?> (<?php echo htmlspecialchars($usulan['nama_jurusan'] ?? '-'); ?>)</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Gambaran Umum</label>
                        <div class="bg-slate-50 p-4 rounded-xl text-slate-600 text-sm leading-relaxed border border-slate-100 text-justify">
                            <?php echo nl2br(htmlspecialchars($usulan['gambaran_umum'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <span class="material-icons text-emerald-600 mr-2">analytics</span> Indikator Kinerja (IKU)
                </h3>
                <div class="space-y-2">
                    <?php foreach($ikuData as $i): ?>
                    <div class="flex justify-between items-center p-3 border border-slate-100 rounded-lg hover:bg-slate-50">
                        <span class="text-sm text-slate-700"><?php echo htmlspecialchars($i['deskripsi_iku']); ?></span>
                        <span class="text-xs font-bold bg-emerald-100 text-emerald-700 px-2 py-1 rounded"><?php echo $i['bobot_persen']; ?>%</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <span class="material-icons text-emerald-600 mr-2">payments</span> Rincian Anggaran (RAB)
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs">
                            <tr>
                                <th class="px-4 py-2">Item Belanja</th>
                                <th class="px-4 py-2 text-center">Vol</th>
                                <th class="px-4 py-2 text-right">Harga Satuan</th>
                                <th class="px-4 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php $total = 0; foreach($rabData as $r): $total += $r['total']; ?>
                            <tr>
                                <td class="px-4 py-2">
                                    <div class="font-bold text-slate-700"><?php echo htmlspecialchars($r['uraian']); ?></div>
                                    <div class="text-[10px] text-slate-400 uppercase"><?php echo htmlspecialchars($r['nama_kategori']); ?></div>
                                </td>
                                <td class="px-4 py-2 text-center"><?php echo $r['volume'] . ' ' . $r['satuan']; ?></td>
                                <td class="px-4 py-2 text-right"><?php echo number_format($r['harga_satuan'], 0, ',', '.'); ?></td>
                                <td class="px-4 py-2 text-right font-bold"><?php echo number_format($r['total'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="bg-slate-50 border-t-2 border-slate-200">
                                <td colspan="3" class="px-4 py-3 text-right font-bold text-slate-600 uppercase text-xs">Total Pengajuan</td>
                                <td class="px-4 py-3 text-right font-black text-emerald-600 text-base">Rp <?php echo number_format($total, 0, ',', '.'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="xl:col-span-1">
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden sticky top-8">
                <div class="bg-slate-900 px-6 py-4 flex items-center">
                    <span class="material-icons text-emerald-400 mr-3">gavel</span>
                    <h3 class="text-lg font-bold text-white">Panel Keputusan</h3>
                </div>
                <div class="p-6">
                    <form action="/verifikasi/aksi?id=<?php echo $usulan['id']; ?>" method="POST" class="space-y-5" onsubmit="return validateForm(this)">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Kode MAK <span class="text-rose-500">*</span></label>
                            <input type="text" name="kode_mak" id="kode_mak" placeholder="Contoh: 521211" value="<?php echo htmlspecialchars($usulan['kode_mak'] ?? ''); ?>"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none font-mono text-sm font-bold text-slate-800">
                            <p class="text-[10px] text-slate-500 mt-1">Wajib diisi jika menyetujui usulan.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Catatan / Revisi</label>
                            <textarea name="catatan" id="catatan" rows="4" placeholder="Tuliskan alasan penolakan atau instruksi revisi..." 
                                      class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-sm"></textarea>
                        </div>

                        <div class="pt-4 border-t border-slate-100 space-y-3">
                            <button type="submit" name="aksi" value="setuju" class="w-full py-3.5 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 hover:bg-emerald-700 transition-all flex justify-center items-center group">
                                <span class="material-icons text-sm mr-2 group-hover:scale-110 transition-transform">check_circle</span> 
                                Setujui & Lanjut
                            </button>

                            <div class="grid grid-cols-2 gap-3">
                                <button type="submit" name="aksi" value="revisi" class="py-3 bg-white border border-amber-400 text-amber-600 font-bold rounded-xl hover:bg-amber-50 transition-all text-sm">
                                    Minta Revisi
                                </button>
                                <button type="submit" name="aksi" value="tolak" class="py-3 bg-white border border-rose-400 text-rose-600 font-bold rounded-xl hover:bg-rose-50 transition-all text-sm" onclick="return confirm('Yakin ingin menolak permanen?');">
                                    Tolak Usulan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function validateForm(form) {
    // Ambil tombol yang diklik
    const btn = document.activeElement;
    const mak = document.getElementById('kode_mak').value.trim();
    const cat = document.getElementById('catatan').value.trim();

    if (btn.value === 'setuju') {
        if (!mak) {
            alert('Kode MAK wajib diisi untuk menyetujui usulan!');
            document.getElementById('kode_mak').focus();
            return false;
        }
    }
    
    if (btn.value === 'revisi' || btn.value === 'tolak') {
        if (!cat) {
            alert('Mohon isi catatan alasan revisi/penolakan!');
            document.getElementById('catatan').focus();
            return false;
        }
    }
    
    return confirm('Apakah keputusan Anda sudah final?');
}
</script>
<?php include __DIR__.'/../partials/footer.php'; ?>