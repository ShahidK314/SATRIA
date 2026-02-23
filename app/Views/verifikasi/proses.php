<?php 
// app/Views/verifikasi/proses.php (UPDATED: Remove Summary Input, Validate by Item Notes)
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

// Helper Format Volume: V1 S1 x V2 S2
function formatVolumeString($item) {
    $v1 = floatval($item['volume_factor_1'] ?? 0);
    $s1 = htmlspecialchars($item['nama_satuan_f1'] ?? '');
    
    // Tampilkan V1 S1
    $str = "{$v1} {$s1}";
    
    // Jika ada V2, tambahkan " x V2 S2"
    if (!empty($item['volume_factor_2']) && $item['volume_factor_2'] > 0) {
        $v2 = floatval($item['volume_factor_2']);
        $s2 = htmlspecialchars($item['nama_satuan_f2'] ?? '');
        $str .= " x {$v2} {$s2}";
    }
    
    // Fallback jika data lama (single volume)
    if (empty($str) || $str == "0 ") {
        $vol = floatval($item['volume']);
        $sat = htmlspecialchars($item['nama_satuan'] ?? '');
        $str = "{$vol} {$sat}";
    }
    
    return $str;
}
?>

<div class="m-5">
    <div class="mb-8">
        <a href="/verifikasi" class="text-slate-500 hover:text-blue-600 font-bold flex items-center gap-2 mb-4 transition-colors">
            <span class="material-icons text-sm">arrow_back</span> Kembali ke Antrian
        </a>
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Verifikasi Usulan</h1>
                <p class="text-slate-500 mt-1">Tinjau kelengkapan KAK, IKU, dan RAB kegiatan.</p>
            </div>
        </div>
    </div>

    <form action="/verifikasi/aksi?id=<?php echo $usulan['id']; ?>" method="POST" id="verifikasiForm" onsubmit="return validateMAK()">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-2xl font-bold text-slate-800 mb-6 flex items-center border-b pb-3">
                        <span class="material-icons text-blue-600 mr-2">assignment</span> KAK (Kerangka Acuan Kegiatan)
                        <a href="/pdf/kak?id=<?php echo $usulan['id']; ?>" target="_blank" class="ml-auto text-xs text-rose-600 hover:text-rose-700 font-bold flex items-center">
                            <span class="material-icons text-sm mr-1">picture_as_pdf</span> Cetak
                        </a>
                    </h2>
                    
                    <div class="space-y-4 text-sm">
                        <div class="grid grid-cols-3 gap-4 border-b border-slate-100 pb-4 kak-item" data-section="KAK">
                            <div class="col-span-2">
                                <label class="text-xs font-bold text-slate-400 uppercase">Nama Kegiatan</label>
                                <p class="text-slate-800 font-bold text-lg mt-1"><?php echo htmlspecialchars($usulan['nama_kegiatan']); ?></p>
                            </div>
                            <div class="col-span-1">
                                <label class="text-xs font-bold text-slate-400 uppercase">Catatan</label>
                                <textarea data-name="nama_kegiatan" rows="2" class="w-full px-2 py-1 border border-slate-300 rounded-lg text-xs catatan-input" placeholder="Catatan..."></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 border-b border-slate-100 pb-4 kak-item" data-section="KAK">
                            <div class="col-span-2">
                                <label class="text-xs font-bold text-slate-400 uppercase">Gambaran Umum / Latar Belakang</label>
                                <p class="text-slate-700 mt-1 bg-slate-50 p-2 rounded max-h-40 overflow-y-auto"><?php echo nl2br(htmlspecialchars($usulan['gambaran_umum'])); ?></p>
                            </div>
                            <div class="col-span-1">
                                <label class="text-xs font-bold text-slate-400 uppercase">Catatan</label>
                                <textarea data-name="gambaran_umum" rows="4" class="w-full px-2 py-1 border border-slate-300 rounded-lg text-xs catatan-input" placeholder="Catatan..."></textarea>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-6 pb-4">
                            <div class="kak-item" data-section="KAK">
                                <label class="text-xs font-bold text-slate-400 uppercase">Penerima Manfaat</label>
                                <p class="text-slate-700 mt-1 bg-slate-50 p-2 rounded"><?php echo htmlspecialchars($usulan['penerima_manfaat']); ?></p>
                                <label class="text-xs font-bold text-slate-400 uppercase mt-4 block">Catatan</label>
                                <textarea data-name="penerima_manfaat" rows="2" class="w-full px-2 py-1 border border-slate-300 rounded-lg text-xs catatan-input" placeholder="Catatan..."></textarea>
                            </div>
                            <div class="kak-item" data-section="KAK">
                                <label class="text-xs font-bold text-slate-400 uppercase">Strategi Pencapaian Keluaran</label>
                                <p class="text-slate-700 mt-1 bg-slate-50 p-2 rounded max-h-24 overflow-y-auto"><?php echo nl2br(htmlspecialchars($usulan['strategi_pencapaian_keluaran'])); ?></p>
                                <label class="text-xs font-bold text-slate-400 uppercase mt-4 block">Catatan</label>
                                <textarea data-name="strategi_pencapaian" rows="2" class="w-full px-2 py-1 border border-slate-300 rounded-lg text-xs catatan-input" placeholder="Catatan..."></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6 border-b border-slate-100 pb-4">
                            <div class="kak-item" data-section="KAK">
                                <label class="text-xs font-bold text-slate-400 uppercase">Metode Pelaksanaan</label>
                                <ul class="list-disc list-inside text-slate-700 mt-1 pl-4 bg-slate-50 p-2 rounded">
                                    <?php if (!empty($usulan['metode_array'])): foreach ($usulan['metode_array'] as $m): ?>
                                        <li><?php echo htmlspecialchars($m); ?></li>
                                    <?php endforeach; else: ?>
                                        <li class="italic text-slate-400">Tidak ada data.</li>
                                    <?php endif; ?>
                                </ul>
                                <label class="text-xs font-bold text-slate-400 uppercase mt-4 block">Catatan</label>
                                <textarea data-name="metode_pelaksanaan" rows="2" class="w-full px-2 py-1 border border-slate-300 rounded-lg text-xs catatan-input" placeholder="Catatan..."></textarea>
                            </div>
                            <div class="kak-item" data-section="KAK">
                                <label class="text-xs font-bold text-slate-400 uppercase">Tahapan Pelaksanaan</label>
                                <ul class="list-decimal list-inside text-slate-700 mt-1 pl-4 bg-slate-50 p-2 rounded">
                                    <?php if (!empty($usulan['tahapan_array'])): foreach ($usulan['tahapan_array'] as $t): ?>
                                        <li><?php echo htmlspecialchars($t); ?></li>
                                    <?php endforeach; else: ?>
                                        <li class="italic text-slate-400">Tidak ada data.</li>
                                    <?php endif; ?>
                                </ul>
                                <label class="text-xs font-bold text-slate-400 uppercase mt-4 block">Catatan</label>
                                <textarea data-name="tahapan_pelaksanaan" rows="2" class="w-full px-2 py-1 border border-slate-300 rounded-lg text-xs catatan-input" placeholder="Catatan..."></textarea>
                            </div>
                        </div>
                        
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase mb-2 block">Indikator Kinerja (KPI)</label>
                            <table class="w-full text-xs border border-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="p-2 border border-slate-200 w-1/2">Indikator</th>
                                        <th class="p-2 border border-slate-200 text-center w-1/6">Bobot (%)</th>
                                        <th class="p-2 border border-slate-200 text-left w-1/3">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if (!empty($usulan['indikator_array'])): $totalBobot = 0; foreach ($usulan['indikator_array'] as $idx => $i): $totalBobot += $i['bobot']; ?>
                                    <tr class="kpi-item" data-section="KAK">
                                        <td class="p-2"><?php echo htmlspecialchars($i['indikator']); ?></td>
                                        <td class="p-2 text-center font-bold"><?php echo $i['bobot']; ?>%</td>
                                        <td class="p-1">
                                            <textarea data-name="kpi_<?php echo $idx; ?>" rows="1" class="w-full px-2 py-1 border border-slate-300 rounded-lg text-xs catatan-input" placeholder="Catatan..."></textarea>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="bg-blue-50 font-bold">
                                        <td class="p-2 text-right">Total:</td>
                                        <td class="p-2 text-center"><?php echo $totalBobot; ?>%</td>
                                        <td class="p-2"></td>
                                    </tr>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="p-4 text-center text-slate-400 italic">Tidak ada indikator kinerja yang dicatat.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-2xl font-bold text-slate-800 mb-6 flex items-center border-b pb-3">
                        <span class="material-icons text-emerald-600 mr-2">trending_up</span> IKU (Indikator Kinerja Utama)
                    </h3>
                    
                    <table class="w-full text-sm border border-slate-200 mb-4">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="p-2 border border-slate-200 w-2/5 text-left">Deskripsi IKU</th>
                                <th class="p-2 border border-slate-200 text-left w-1/4">Target IKU (String)</th>
                                <th class="p-2 border border-slate-200 text-left w-1/4">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($ikuData)): ?>
                                <tr><td colspan="3" class="p-4 text-center text-slate-400 italic">Tidak ada IKU yang dipilih.</td></tr>
                            <?php else: foreach ($ikuData as $idx => $iku): ?>
                            <tr class="iku-item" data-section="IKU">
                                <td class="p-2 text-slate-700"><?php echo htmlspecialchars($iku['deskripsi_iku']); ?></td>
                                <td class="p-2 text-center font-bold text-blue-700"><?php echo htmlspecialchars($iku['target'] ?? '-'); ?></td>
                                <td class="p-1">
                                    <textarea data-name="iku_<?php echo $idx; ?>" rows="1" class="w-full px-2 py-1 border border-slate-300 rounded-lg text-xs catatan-input" placeholder="Catatan..."></textarea>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-2xl font-bold text-slate-800 mb-6 flex items-center border-b pb-3">
                        <span class="material-icons text-amber-600 mr-2">payments</span> RAB (Rencana Anggaran Biaya)
                        <a href="/pdf/rab?id=<?php echo $usulan['id']; ?>" target="_blank" class="ml-auto text-xs text-blue-600 hover:text-blue-700 font-bold flex items-center">
                            <span class="material-icons text-sm mr-1">print</span> Cetak
                        </a>
                    </h3>
                    
                    <?php if (empty($rabData)): ?>
                        <p class="text-slate-400 text-sm italic text-center p-4">Tidak ada item RAB yang dicatat.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto mb-4">
                            <table class="w-full text-sm border border-slate-200 whitespace-nowrap">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="p-2 border border-slate-200 text-left w-2/12">Kategori</th>
                                        <th class="p-2 border border-slate-200 text-left w-3/12">Uraian</th>
                                        
                                        <th class="p-2 border border-slate-200 text-center w-[50px]">Vol 1</th>
                                        <th class="p-2 border border-slate-200 text-center w-[60px]">Sat 1</th>
                                        
                                        <th class="p-2 border border-slate-200 text-center w-[20px] bg-slate-100"></th> <th class="p-2 border border-slate-200 text-center w-[50px]">Vol 2</th>
                                        <th class="p-2 border border-slate-200 text-center w-[60px]">Sat 2</th>
                                        
                                        <th class="p-2 border border-slate-200 text-center w-[20px] bg-slate-100"></th> <th class="p-2 border border-slate-200 text-right w-2/12">Harga Satuan</th>
                                        <th class="p-2 border border-slate-200 text-right w-1/12">Total</th>
                                        <th class="p-2 border border-slate-200 text-left w-2/12">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php $grandTotalRAB = 0; $currentCat = ''; foreach ($rabData as $idx => $rab): $grandTotalRAB += $rab['total']; ?>
                                        <tr class="rab-item" data-section="RAB">
                                            <td class="p-2 font-bold text-xs <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">
                                                <?php echo htmlspecialchars($rab['nama_kategori']); ?>
                                            </td>
                                            
                                            <td class="p-2 text-slate-700 <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">
                                                <?php echo htmlspecialchars($rab['deskripsi']); ?> 
                                            </td>
                                            
                                            <?php 
                                                $v1 = floatval($rab['volume_factor_1'] ?? 0);
                                                $s1 = htmlspecialchars($rab['nama_satuan_f1'] ?? '');
                                                $v2 = floatval($rab['volume_factor_2'] ?? 0);
                                                $s2 = htmlspecialchars($rab['nama_satuan_f2'] ?? '');
                                                
                                                if (empty($s1) && $rab['volume'] > 0) {
                                                    $v1 = floatval($rab['volume']);
                                                    $s1 = htmlspecialchars($rab['nama_satuan'] ?? '');
                                                }
                                            ?>

                                            <td class="p-2 text-center text-slate-700 <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">
                                                <?php echo $v1 > 0 ? $v1 : '-'; ?>
                                            </td>
                                            <td class="p-2 text-center text-slate-700 <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">
                                                <?php echo $s1; ?>
                                            </td>
                                            
                                            <td class="p-2 text-center text-slate-400 font-bold <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">
                                                <?php echo ($v2 > 0) ? 'x' : ''; ?>
                                            </td>
                                            
                                            <td class="p-2 text-center text-slate-700 <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">
                                                <?php echo $v2 > 0 ? $v2 : '-'; ?>
                                            </td>
                                            <td class="p-2 text-center text-slate-700 <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">
                                                <?php echo $v2 > 0 ? $s2 : '-'; ?>
                                            </td>
                                            
                                            <td class="p-2 text-center text-slate-400 font-bold <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">
                                                x
                                            </td>
                                            
                                            <td class="p-2 text-right text-slate-700 <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">
                                                <?php echo formatRupiah($rab['harga_satuan']); ?>
                                            </td>
                                            
                                            <td class="p-2 text-right font-bold text-slate-700 whitespace-nowrap <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">
                                                <?php echo formatRupiah($rab['total']); ?>
                                            </td>
                                            
                                            <td class="p-1 <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">
                                                <textarea data-name="rab_<?php echo $idx; ?>" rows="1" class="w-full px-2 py-1 border border-slate-300 rounded-lg text-xs catatan-input" placeholder="Catatan..."></textarea>
                                            </td>
                                        </tr>
                                    <?php $currentCat = $rab['nama_kategori']; endforeach; ?>
                                    
                                    <tr class="bg-amber-50 font-bold">
                                        <td colspan="9" class="px-2 py-3 text-right uppercase">Grand Total:</td>
                                        <td class="px-2 py-3 text-right text-lg text-amber-700 whitespace-nowrap"><?php echo formatRupiah($grandTotalRAB); ?></td>
                                        <td class="px-2 py-3"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden sticky top-6">
                    <div class="bg-blue-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <span class="material-icons mr-2">gavel</span> Putusan Verifikator
                        </h3>
                    </div>
                    
                    <div class="p-6 space-y-6">
                         <div class="mb-4">
                            <label for="kode_mak" class="block text-sm font-bold text-slate-700 mb-2">
                                Kode MAK <span class="text-rose-600">*Wajib jika Disetujui</span>
                            </label>
                            <input type="text" id="kode_mak" name="kode_mak" value="<?php echo htmlspecialchars($usulan['kode_mak'] ?? ''); ?>" 
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm font-mono" placeholder="Cth: 521211 / 522151">
                        </div>
                        
                        <div class="space-y-3 pt-2 border-t border-slate-100">
                            <button type="submit" name="aksi" value="setuju" 
                                    class="w-full py-3 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition-all flex justify-center items-center"
                                    onclick="return combineCatatan('setuju');">
                                <span class="material-icons text-sm mr-2">check_circle</span> Setujui
                            </button>
                            
                            <button type="submit" name="aksi" value="revisi" 
                                    class="w-full py-3 bg-amber-600 text-white font-bold rounded-xl hover:bg-amber-700 transition-all flex justify-center items-center"
                                    onclick="return combineCatatan('revisi');">
                                <span class="material-icons text-sm mr-2">autorenew</span> Revisi
                            </button>
                            
                            <button type="submit" name="aksi" value="tolak" 
                                    class="w-full py-3 bg-rose-600 text-white font-bold rounded-xl hover:bg-rose-700 transition-all flex justify-center items-center"
                                    onclick="return combineCatatan('tolak');">
                                <span class="material-icons text-sm mr-2">cancel</span> Tolak
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <input type="hidden" name="catatan" id="catatan_combined">
    </form>
</div>

<script>
    function combineCatatan(aksi) {
        // Removed catatanRingkasan variable
        const allItemNotes = document.querySelectorAll('.catatan-input');
        
        let combined = '';
        let kakNotes = '';
        let ikuNotes = '';
        let rabNotes = '';
        
        allItemNotes.forEach(input => {
            const note = input.value.trim();
            if (note) {
                const dataName = input.getAttribute('data-name');
                const parentSection = input.closest('[data-section]').getAttribute('data-section');
                const noteLine = ` - [${dataName}] ${note}\n`;
                
                if (parentSection === 'KAK') kakNotes += noteLine;
                if (parentSection === 'IKU') ikuNotes += noteLine;
                if (parentSection === 'RAB') rabNotes += noteLine;
            }
        });
        
        // Removed logic for combining ringkasan
        
        if (kakNotes) combined += 'Catatan KAK:\n' + kakNotes;
        if (ikuNotes) combined += 'Catatan IKU:\n' + ikuNotes;
        if (rabNotes) combined += 'Catatan RAB:\n' + rabNotes;
        
        document.getElementById('catatan_combined').value = combined.trim();
        
        const kodeMak = document.getElementById('kode_mak').value.trim();
        
        if (aksi === 'setuju') {
            if (!kodeMak) {
                alert('Kode MAK wajib diisi untuk menyetujui usulan!');
                document.getElementById('kode_mak').focus();
                return false;
            }
            return confirm('Yakin ingin menyetujui usulan ini?');
        }
        
        if (aksi === 'revisi' || aksi === 'tolak') {
            // Check if there are any notes in the combined string (excluding whitespaces)
            if (combined.trim() === '') {
                alert('Harap berikan catatan pada minimal satu item (KAK/IKU/RAB) untuk melakukan Revisi atau Penolakan.');
                // Optional: Focus on the first visible input if needed, or just let user find it
                if (allItemNotes.length > 0) allItemNotes[0].focus();
                return false;
            }
             return confirm(`Yakin ingin ${aksi} usulan ini?`);
        }
        
        return true;
    }
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>