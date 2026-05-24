<?php 
// app/Views/pengajuan/wd2_detail.php
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}
$roleTitle = 'WD2';
$actionUrl = '/pengajuan/' . strtolower($roleTitle) . '/aksi?id=' . $pengajuan['id'];
$logPPK = $logPPK ?? ['catatan' => null, 'timestamp' => null];
?>

<div class="m-4 md:m-8 max-w-6xl mx-auto">
    <div class="mb-6 md:mb-8">
        <a href="/pengajuan/wd2" class="text-slate-500 hover:text-blue-600 font-bold flex items-center gap-2 mb-4 w-fit transition-colors">
            <span class="material-icons text-sm">arrow_back</span> Kembali ke Antrian
        </a>
        <div class="flex flex-col md:flex-row justify-between items-start gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Detail Pengajuan Kegiatan</h1>
                <p class="text-slate-500 mt-1 text-sm md:text-base">Review akhir sebelum menyetujui pencairan dana.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center border-b border-slate-100 pb-3">
                    <span class="material-icons text-blue-600 mr-2">info</span> Informasi Kegiatan
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] md:text-xs font-bold text-slate-400 uppercase">Nama Kegiatan</label>
                        <div class="text-slate-800 font-bold text-base md:text-lg mt-1"><?php echo htmlspecialchars($usulan['nama_kegiatan']); ?></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] md:text-xs font-bold text-slate-400 uppercase">Pengusul (Unit)</label>
                            <div class="text-slate-700 mt-1 bg-slate-50 px-3 py-2 rounded-lg border border-slate-100"><?php echo htmlspecialchars($usulan['username']); ?> (<?php echo htmlspecialchars($usulan['nama_jurusan']); ?>)</div>
                        </div>
                        <div>
                            <label class="text-[10px] md:text-xs font-bold text-slate-400 uppercase">Penanggung Jawab</label>
                            <div class="text-slate-700 font-medium mt-1 bg-slate-50 px-3 py-2 rounded-lg border border-slate-100"><?php echo htmlspecialchars($pengajuan['penanggung_jawab']); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center justify-between border-b pb-3">
                    <div class="flex items-center">
                         <span class="material-icons text-blue-600 mr-2">assignment</span> KAK (Kerangka Acuan)
                    </div>
                    <a href="/pdf/kak?id=<?php echo $pengajuan['usulan_id']; ?>" target="_blank" class="text-xs px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-md font-bold flex items-center transition-colors">
                        <span class="material-icons text-[14px] mr-1">picture_as_pdf</span> KAK
                    </a>
                </h3>

                <div class="space-y-4 md:space-y-6 text-sm">
                    <div class="border-b border-slate-100 pb-4">
                        <label class="text-[10px] md:text-xs font-bold text-slate-400 uppercase">Kurun Waktu Pelaksanaan</label>
                        <p class="text-slate-800 mt-1 font-bold"><?php echo date('d M Y', strtotime($pengajuan['tanggal_mulai'])); ?> s/d <?php echo date('d M Y', strtotime($pengajuan['tanggal_selesai'])); ?></p>
                    </div>
                    <div class="border-b border-slate-100 pb-4">
                        <label class="text-[10px] md:text-xs font-bold text-slate-400 uppercase">Gambaran Umum / Latar Belakang</label>
                        <p class="text-slate-700 mt-1 bg-slate-50 p-3 rounded-lg border border-slate-100 max-h-40 overflow-y-auto leading-relaxed"><?php echo nl2br(htmlspecialchars($usulan['gambaran_umum'])); ?></p>
                    </div>
                    <div class="border-b border-slate-100 pb-4">
                        <label class="text-[10px] md:text-xs font-bold text-slate-400 uppercase">Penerima Manfaat</label>
                        <p class="text-slate-700 mt-1 bg-slate-50 p-3 rounded-lg border border-slate-100"><?php echo htmlspecialchars($usulan['penerima_manfaat']); ?></p>
                    </div>
                    <div class="border-b border-slate-100 pb-4">
                        <label class="text-[10px] md:text-xs font-bold text-slate-400 uppercase">Strategi Pencapaian Keluaran</label>
                        <p class="text-slate-700 mt-1 bg-slate-50 p-3 rounded-lg border border-slate-100 max-h-40 overflow-y-auto leading-relaxed"><?php echo nl2br(htmlspecialchars($usulan['strategi_pencapaian_keluaran'])); ?></p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6 border-b border-slate-100 pb-4">
                        <div>
                            <label class="text-[10px] md:text-xs font-bold text-slate-400 uppercase">Metode Pelaksanaan</label>
                            <ul class="list-disc list-inside text-slate-700 mt-1 pl-2 bg-slate-50 p-3 rounded-lg border border-slate-100">
                                <?php if (!empty($usulan['metode_array'])): foreach ($usulan['metode_array'] as $m): ?>
                                    <li class="mb-1"><?php echo htmlspecialchars($m); ?></li>
                                <?php endforeach; else: ?>
                                    <li class="italic text-slate-400">N/A</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div>
                            <label class="text-[10px] md:text-xs font-bold text-slate-400 uppercase">Tahapan Pelaksanaan</label>
                            <ul class="list-decimal list-inside text-slate-700 mt-1 pl-2 bg-slate-50 p-3 rounded-lg border border-slate-100">
                                <?php if (!empty($usulan['tahapan_array'])): foreach ($usulan['tahapan_array'] as $t): ?>
                                    <li class="mb-1"><?php echo htmlspecialchars($t); ?></li>
                                <?php endforeach; else: ?>
                                    <li class="italic text-slate-400">N/A</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="text-[10px] md:text-xs font-bold text-slate-400 uppercase block mb-2">Indikator Kinerja (KPI)</label>
                        <div class="overflow-x-auto w-full custom-scrollbar pb-2">
                            <table class="w-full text-xs border border-slate-200 min-w-[500px]">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="p-3 border border-slate-200 text-left w-8/12">Indikator</th>
                                        <th class="p-3 border border-slate-200 text-center w-2/12">Bulan Target</th>
                                        <th class="p-3 border border-slate-200 text-center w-2/12">Bobot (%)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if (!empty($usulan['indikator_array'])): $totalBobot = 0; foreach ($usulan['indikator_array'] as $i): $totalBobot += $i['bobot']; ?>
                                    <tr>
                                        <td class="p-3 text-slate-700"><?php echo htmlspecialchars($i['indikator']); ?></td>
                                        <td class="p-3 text-center text-slate-600 font-medium"><?php echo htmlspecialchars($i['bulan_target']); ?></td>
                                        <td class="p-3 text-center font-bold text-blue-600"><?php echo $i['bobot']; ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="bg-slate-50 font-bold border-t border-slate-200">
                                        <td colspan="2" class="p-3 text-right uppercase text-slate-500">Total Bobot:</td>
                                        <td class="p-3 text-center text-emerald-600"><?php echo $totalBobot; ?>%</td>
                                    </tr>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="p-4 text-center text-slate-400 italic">Tidak ada indikator kinerja yang dicatat.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center border-b border-slate-100 pb-3">
                    <span class="material-icons text-emerald-600 mr-2">trending_up</span> IKU (Indikator Kinerja Utama)
                </h3>
                <div class="overflow-x-auto w-full custom-scrollbar pb-2">
                    <table class="w-full text-sm border border-slate-200 min-w-[500px]">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="p-3 border border-slate-200 w-4/5 text-left">Deskripsi IKU</th>
                                <th class="p-3 border border-slate-200 text-center w-1/5">Target</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($ikuDetails)): ?>
                                <tr><td colspan="2" class="p-4 text-center text-slate-400 italic">Tidak ada IKU yang dipilih.</td></tr>
                            <?php else: foreach ($ikuDetails as $iku): ?>
                            <tr>
                                <td class="p-3 text-slate-700"><?php echo htmlspecialchars($iku['deskripsi_iku']); ?></td>
                                <td class="p-3 text-center font-bold text-blue-700"><?php echo htmlspecialchars($iku['target'] ?? '-'); ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center">
                        <span class="material-icons text-amber-600 mr-2">payments</span> RAB (Anggaran)
                    </div>
                    <a href="/pdf/rab?id=<?php echo $pengajuan['usulan_id']; ?>" target="_blank" class="text-xs px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-md font-bold flex items-center transition-colors">
                        <span class="material-icons text-[14px] mr-1">print</span> Cetak RAB
                    </a>
                </h3>
                
                <div class="overflow-x-auto w-full custom-scrollbar pb-2">
                    <table class="w-full text-sm min-w-[900px]">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] md:text-xs border border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left">Uraian</th>
                                <th class="px-2 py-3 text-center">Vol 1</th>
                                <th class="px-2 py-3 text-center">Sat 1</th>
                                <th class="px-2 py-3 text-center">Vol 2</th>
                                <th class="px-2 py-3 text-center">Sat 2</th>
                                <th class="px-4 py-3 text-right">Total Vol</th>
                                <th class="px-4 py-3 text-right">Harga Satuan</th>
                                <th class="px-4 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 border-x border-b border-slate-200">
                            <?php $grandTotal = 0; foreach($rabData as $rab): $grandTotal += $rab['total']; ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-700"><?php echo htmlspecialchars($rab['deskripsi']); ?></div>
                                    <div class="text-[10px] uppercase text-slate-400 mt-0.5"><?php echo htmlspecialchars($rab['nama_kategori']); ?></div>
                                </td>
                                
                                <td class="px-2 py-3 text-center text-slate-600"><?php echo ($rab['volume_factor_1'] > 0) ? floatval($rab['volume_factor_1']) : '-'; ?></td>
                                <td class="px-2 py-3 text-center text-slate-500 text-xs"><?php echo htmlspecialchars($rab['nama_satuan_f1'] ?? '-'); ?></td>
                                <td class="px-2 py-3 text-center text-slate-600"><?php echo ($rab['volume_factor_2'] > 0) ? floatval($rab['volume_factor_2']) : '-'; ?></td>
                                <td class="px-2 py-3 text-center text-slate-500 text-xs"><?php echo htmlspecialchars($rab['nama_satuan_f2'] ?? '-'); ?></td>
                                
                                <td class="px-4 py-3 text-right font-bold text-slate-600"><?php echo floatval($rab['volume']) . ' ' . $rab['nama_satuan']; ?></td>
                                <td class="px-4 py-3 text-right text-xs text-slate-500"><?php echo formatRupiah($rab['harga_satuan']); ?></td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700"><?php echo formatRupiah($rab['total']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="bg-amber-50 font-bold border-t-2 border-slate-300">
                                <td colspan="7" class="px-4 py-4 text-right uppercase text-slate-600 text-xs">Grand Total RAB :</td>
                                <td class="px-4 py-4 text-right text-amber-700 text-lg md:text-xl"><?php echo formatRupiah($grandTotal); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if(!empty($logPPK['catatan'])): ?>
            <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-4 md:p-6">
                <h3 class="text-lg font-bold text-amber-800 mb-4 flex items-center">
                    <span class="material-icons text-amber-600 mr-2">rate_review</span> Rekomendasi PPK
                </h3>
                <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
                    <div class="text-sm text-amber-900 leading-relaxed font-medium">"<?php echo nl2br(htmlspecialchars($logPPK['catatan'])); ?>"</div>
                    <div class="text-xs text-amber-600 mt-3 flex items-center"><span class="material-icons text-[14px] mr-1">history</span> Disetujui pada: <?php echo date('d M Y H:i', strtotime($logPPK['timestamp'])); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($pengajuan['surat_pengantar_path'])): ?>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center border-b border-slate-100 pb-3">
                    <span class="material-icons text-rose-600 mr-2">description</span> Dokumen Pendukung
                </h3>
                
                <a href="<?php echo htmlspecialchars($pengajuan['surat_pengantar_path']); ?>" target="_blank" 
                   class="flex flex-col sm:flex-row items-center sm:items-start gap-4 p-4 border border-slate-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-all group shadow-sm">
                    <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <span class="material-icons text-2xl">picture_as_pdf</span>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <div class="font-bold text-slate-700 group-hover:text-blue-700 text-base">Surat Pengantar Kegiatan</div>
                        <div class="text-xs text-slate-500 mt-1">Klik untuk membuka dokumen PDF di tab baru</div>
                    </div>
                    <span class="material-icons text-slate-300 group-hover:text-blue-600 mt-2 sm:mt-0">open_in_new</span>
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden sticky top-6 z-10">
                <div class="bg-blue-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <span class="material-icons mr-2">gavel</span> Keputusan WD2
                    </h3>
                </div>

                <form action="<?php echo $actionUrl; ?>" method="POST" class="p-4 md:p-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Rekomendasi / Catatan Akhir
                            <span class="text-slate-400 font-normal text-xs ml-1 block mt-1">(Opsional)</span>
                        </label>
                        <textarea name="rekomendasi" rows="4" 
                                  class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm shadow-inner"
                                  placeholder="Catatan untuk bendahara..."></textarea>
                        <p class="text-[11px] text-slate-500 mt-2 italic">Instruksi khusus atau catatan untuk proses pencairan dana oleh Bendahara.</p>
                    </div>

                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        <button type="submit" name="aksi" value="setuju" 
                                class="w-full py-4 bg-emerald-600 text-white font-bold rounded-xl shadow-md hover:bg-emerald-700 hover:shadow-lg transition-all flex justify-center items-center text-sm md:text-base"
                                onclick="return confirm('Yakin menyetujui pengajuan kegiatan ini? Dana akan siap Dicairkan oleh Bendahara.')">
                            <span class="material-icons text-lg mr-2">check_circle</span>
                            Setujui & Siap Pencairan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>