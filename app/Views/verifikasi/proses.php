<?php 
// app/Views/verifikasi/proses.php (RESPONSIVE)
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

function formatVolumeString($item) {
    $v1 = floatval($item['volume_factor_1'] ?? 0);
    $s1 = htmlspecialchars($item['nama_satuan_f1'] ?? '');
    
    $str = "{$v1} {$s1}";
    
    if (!empty($item['volume_factor_2']) && $item['volume_factor_2'] > 0) {
        $v2 = floatval($item['volume_factor_2']);
        $s2 = htmlspecialchars($item['nama_satuan_f2'] ?? '');
        $str .= " x {$v2} {$s2}";
    }
    
    if (empty($str) || $str == "0 ") {
        $vol = floatval($item['volume']);
        $sat = htmlspecialchars($item['nama_satuan'] ?? '');
        $str = "{$vol} {$sat}";
    }
    
    return $str;
}
?>

<div class="m-4 md:m-5">
    <div class="mb-6 md:mb-8">
        <a href="/verifikasi" class="text-slate-500 hover:text-blue-600 font-bold flex items-center gap-2 mb-4 transition-colors w-fit">
            <span class="material-icons text-sm">arrow_back</span> Kembali ke Antrian
        </a>
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Verifikasi Usulan</h1>
                <p class="text-slate-500 mt-1 text-sm md:text-base">Tinjau kelengkapan KAK, IKU, dan RAB kegiatan.</p>
            </div>
        </div>
    </div>

    <form action="/verifikasi/aksi?id=<?php echo $usulan['id']; ?>" method="POST" id="verifikasiForm" onsubmit="return validateMAK()">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6">
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 mb-6 flex items-center border-b pb-3">
                        <span class="material-icons text-blue-600 mr-2">assignment</span> KAK (Kerangka Acuan)
                        <a href="/pdf/kak?id=<?php echo $usulan['id']; ?>" target="_blank" class="ml-auto text-xs px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-md font-bold flex items-center transition-colors">
                            <span class="material-icons text-[14px] mr-1">picture_as_pdf</span> Cetak
                        </a>
                    </h2>
                    
                    <div class="space-y-4 text-sm">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 md:gap-4 border-b border-slate-100 pb-4 kak-item" data-section="KAK">
                            <div class="md:col-span-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase">Nama Kegiatan</label>
                                <p class="text-slate-800 font-bold text-base md:text-lg mt-1"><?php echo htmlspecialchars($usulan['nama_kegiatan']); ?></p>
                            </div>
                            <div class="md:col-span-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Catatan Revisi</label>
                                <textarea data-name="nama_kegiatan" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs catatan-input focus:ring-amber-400" placeholder="Ketik jika ada revisi..."></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 md:gap-4 border-b border-slate-100 pb-4 kak-item" data-section="KAK">
                            <div class="md:col-span-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase">Gambaran Umum / Latar Belakang</label>
                                <p class="text-slate-700 mt-1 bg-slate-50 border border-slate-100 p-3 rounded-lg max-h-40 overflow-y-auto leading-relaxed"><?php echo nl2br(htmlspecialchars($usulan['gambaran_umum'])); ?></p>
                            </div>
                            <div class="md:col-span-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Catatan Revisi</label>
                                <textarea data-name="gambaran_umum" rows="4" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs catatan-input focus:ring-amber-400" placeholder="Ketik jika ada revisi..."></textarea>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 pb-4">
                            <div class="kak-item bg-slate-50 p-3 md:p-4 rounded-lg border border-slate-100" data-section="KAK">
                                <label class="text-[10px] font-bold text-slate-400 uppercase">Penerima Manfaat</label>
                                <p class="text-slate-700 mt-1 bg-white border border-slate-200 p-2 rounded"><?php echo htmlspecialchars($usulan['penerima_manfaat']); ?></p>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mt-4 mb-1 block">Catatan</label>
                                <textarea data-name="penerima_manfaat" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs catatan-input focus:ring-amber-400" placeholder="Catatan..."></textarea>
                            </div>
                            <div class="kak-item bg-slate-50 p-3 md:p-4 rounded-lg border border-slate-100" data-section="KAK">
                                <label class="text-[10px] font-bold text-slate-400 uppercase">Strategi Pencapaian Keluaran</label>
                                <p class="text-slate-700 mt-1 bg-white border border-slate-200 p-2 rounded max-h-24 overflow-y-auto"><?php echo nl2br(htmlspecialchars($usulan['strategi_pencapaian_keluaran'])); ?></p>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mt-4 mb-1 block">Catatan</label>
                                <textarea data-name="strategi_pencapaian" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs catatan-input focus:ring-amber-400" placeholder="Catatan..."></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 border-b border-slate-100 pb-4">
                            <div class="kak-item" data-section="KAK">
                                <label class="text-[10px] font-bold text-slate-400 uppercase">Metode Pelaksanaan</label>
                                <ul class="list-disc list-inside text-slate-700 mt-1 pl-4 bg-slate-50 border border-slate-100 p-3 rounded-lg">
                                    <?php if (!empty($usulan['metode_array'])): foreach ($usulan['metode_array'] as $m): ?>
                                        <li class="mb-1"><?php echo htmlspecialchars($m); ?></li>
                                    <?php endforeach; else: ?>
                                        <li class="italic text-slate-400">Tidak ada data.</li>
                                    <?php endif; ?>
                                </ul>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mt-4 mb-1 block">Catatan Revisi</label>
                                <textarea data-name="metode_pelaksanaan" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs catatan-input focus:ring-amber-400" placeholder="Catatan..."></textarea>
                            </div>
                            <div class="kak-item" data-section="KAK">
                                <label class="text-[10px] font-bold text-slate-400 uppercase">Tahapan Pelaksanaan</label>
                                <ul class="list-decimal list-inside text-slate-700 mt-1 pl-4 bg-slate-50 border border-slate-100 p-3 rounded-lg">
                                    <?php if (!empty($usulan['tahapan_array'])): foreach ($usulan['tahapan_array'] as $t): ?>
                                        <li class="mb-1"><?php echo htmlspecialchars($t); ?></li>
                                    <?php endforeach; else: ?>
                                        <li class="italic text-slate-400">Tidak ada data.</li>
                                    <?php endif; ?>
                                </ul>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mt-4 mb-1 block">Catatan Revisi</label>
                                <textarea data-name="tahapan_pelaksanaan" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs catatan-input focus:ring-amber-400" placeholder="Catatan..."></textarea>
                            </div>
                        </div>
                        
                        <div class="pt-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block">Indikator Kinerja (KPI)</label>
                            <div class="overflow-x-auto w-full custom-scrollbar pb-2">
                                <table class="w-full text-xs border border-slate-200 min-w-[500px]">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="p-3 border border-slate-200 text-left w-1/2">Indikator</th>
                                            <th class="p-3 border border-slate-200 text-center w-24">Bobot (%)</th>
                                            <th class="p-3 border border-slate-200 text-left w-1/3">Catatan Revisi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php if (!empty($usulan['indikator_array'])): $totalBobot = 0; foreach ($usulan['indikator_array'] as $idx => $i): $totalBobot += $i['bobot']; ?>
                                        <tr class="kpi-item" data-section="KAK">
                                            <td class="p-3 text-slate-700 leading-snug"><?php echo htmlspecialchars($i['indikator']); ?></td>
                                            <td class="p-3 text-center font-bold text-blue-600"><?php echo $i['bobot']; ?>%</td>
                                            <td class="p-2 bg-rose-50/30">
                                                <textarea data-name="kpi_<?php echo $idx; ?>" rows="2" class="w-full px-2 py-1 border border-slate-300 rounded-md text-xs catatan-input focus:ring-amber-400" placeholder="Ketik catatan di sini..."></textarea>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="bg-slate-50 font-bold border-t border-slate-200">
                                            <td class="p-3 text-right uppercase text-slate-500">Total Bobot:</td>
                                            <td class="p-3 text-center text-emerald-600"><?php echo $totalBobot; ?>%</td>
                                            <td class="p-3"></td>
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
                    <h3 class="text-xl md:text-2xl font-bold text-slate-800 mb-6 flex items-center border-b pb-3">
                        <span class="material-icons text-emerald-600 mr-2">trending_up</span> IKU (Indikator Kinerja Utama)
                    </h3>
                    
                    <div class="overflow-x-auto w-full custom-scrollbar pb-2">
                        <table class="w-full text-sm border border-slate-200 mb-2 min-w-[500px]">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="p-3 border border-slate-200 text-left w-1/2">Deskripsi IKU</th>
                                    <th class="p-3 border border-slate-200 text-center w-32">Target IKU</th>
                                    <th class="p-3 border border-slate-200 text-left w-1/3">Catatan Revisi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($ikuData)): ?>
                                    <tr><td colspan="3" class="p-4 text-center text-slate-400 italic">Tidak ada IKU yang dipilih.</td></tr>
                                <?php else: foreach ($ikuData as $idx => $iku): ?>
                                <tr class="iku-item" data-section="IKU">
                                    <td class="p-3 text-slate-700 leading-snug"><?php echo htmlspecialchars($iku['deskripsi_iku']); ?></td>
                                    <td class="p-3 text-center font-bold text-blue-700"><?php echo htmlspecialchars($iku['target'] ?? '-'); ?></td>
                                    <td class="p-2 bg-rose-50/30">
                                        <textarea data-name="iku_<?php echo $idx; ?>" rows="2" class="w-full px-2 py-1 border border-slate-300 rounded-md text-xs catatan-input focus:ring-amber-400" placeholder="Catatan..."></textarea>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6">
                    <h3 class="text-xl md:text-2xl font-bold text-slate-800 mb-6 flex items-center border-b pb-3">
                        <span class="material-icons text-amber-600 mr-2">payments</span> RAB (Anggaran)
                        <a href="/pdf/rab?id=<?php echo $usulan['id']; ?>" target="_blank" class="ml-auto text-xs px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-md font-bold flex items-center transition-colors">
                            <span class="material-icons text-[14px] mr-1">print</span> Cetak
                        </a>
                    </h3>
                    
                    <?php if (empty($rabData)): ?>
                        <p class="text-slate-400 text-sm italic text-center p-4">Tidak ada item RAB yang dicatat.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto w-full custom-scrollbar pb-2 mb-4">
                            <table class="w-full text-sm border border-slate-200 whitespace-nowrap min-w-[1000px]">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="p-3 border border-slate-200 text-left w-2/12">Kategori</th>
                                        <th class="p-3 border border-slate-200 text-left w-3/12">Uraian</th>
                                        <th class="p-3 border border-slate-200 text-center w-[50px]">Vol 1</th>
                                        <th class="p-3 border border-slate-200 text-center w-[60px]">Sat 1</th>
                                        <th class="p-3 border border-slate-200 text-center w-[20px] bg-slate-100"></th> 
                                        <th class="p-3 border border-slate-200 text-center w-[50px]">Vol 2</th>
                                        <th class="p-3 border border-slate-200 text-center w-[60px]">Sat 2</th>
                                        <th class="p-3 border border-slate-200 text-center w-[20px] bg-slate-100"></th> 
                                        <th class="p-3 border border-slate-200 text-right w-2/12">Harga Satuan</th>
                                        <th class="p-3 border border-slate-200 text-right w-1/12">Total</th>
                                        <th class="p-3 border border-slate-200 text-left w-2/12">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php $grandTotalRAB = 0; $currentCat = ''; foreach ($rabData as $idx => $rab): $grandTotalRAB += $rab['total']; ?>
                                        <tr class="rab-item" data-section="RAB">
                                            <td class="p-3 font-bold text-xs text-slate-800 <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">
                                                <?php echo htmlspecialchars($rab['nama_kategori']); ?>
                                            </td>
                                            <td class="p-3 text-slate-700 font-medium <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">
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

                                            <td class="p-3 text-center text-slate-600 <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>"><?php echo $v1 > 0 ? $v1 : '-'; ?></td>
                                            <td class="p-3 text-center text-slate-500 text-xs <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>"><?php echo $s1; ?></td>
                                            <td class="p-3 text-center text-slate-300 font-bold <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>"><?php echo ($v2 > 0) ? 'x' : ''; ?></td>
                                            <td class="p-3 text-center text-slate-600 <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>"><?php echo $v2 > 0 ? $v2 : '-'; ?></td>
                                            <td class="p-3 text-center text-slate-500 text-xs <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>"><?php echo $v2 > 0 ? $s2 : '-'; ?></td>
                                            <td class="p-3 text-center text-slate-300 font-bold <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">x</td>
                                            <td class="p-3 text-right text-slate-600 text-xs <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>"><?php echo formatRupiah($rab['harga_satuan']); ?></td>
                                            <td class="p-3 text-right font-bold text-emerald-700 whitespace-nowrap <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>"><?php echo formatRupiah($rab['total']); ?></td>
                                            
                                            <td class="p-2 bg-rose-50/30 <?php echo $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : ''; ?>">
                                                <textarea data-name="rab_<?php echo $idx; ?>" rows="2" class="w-full px-2 py-1 border border-slate-300 rounded-md text-xs catatan-input focus:ring-amber-400" placeholder="Catatan..."></textarea>
                                            </td>
                                        </tr>
                                    <?php $currentCat = $rab['nama_kategori']; endforeach; ?>
                                    
                                    <tr class="bg-amber-50 font-bold border-t-2 border-slate-300">
                                        <td colspan="9" class="px-3 py-4 text-right uppercase text-slate-600">Grand Total RAB:</td>
                                        <td class="px-3 py-4 text-right text-lg md:text-xl text-amber-700 whitespace-nowrap"><?php echo formatRupiah($grandTotalRAB); ?></td>
                                        <td class="px-3 py-4"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden sticky top-6 z-10">
                    <div class="bg-blue-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <span class="material-icons mr-2">gavel</span> Putusan Verifikator
                        </h3>
                    </div>
                    
                    <div class="p-4 md:p-6 space-y-6">
                         <div class="mb-4">
                            <label for="kode_mak" class="block text-sm font-bold text-slate-700 mb-2">
                                Kode MAK <span class="text-rose-600">*Wajib jika Disetujui</span>
                            </label>
                            <input type="text" id="kode_mak" name="kode_mak" value="<?php echo htmlspecialchars($usulan['kode_mak'] ?? ''); ?>" 
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg text-base font-mono focus:ring-blue-500 focus:border-blue-500" placeholder="Cth: 521211 / 522151">
                        </div>
                        
                        <div class="space-y-3 pt-4 border-t border-slate-100">
                            <button type="submit" name="aksi" value="setuju" 
                                    class="w-full py-3.5 bg-emerald-600 text-white font-bold rounded-xl shadow-md hover:bg-emerald-700 transition-all flex justify-center items-center"
                                    onclick="return combineCatatan('setuju');">
                                <span class="material-icons text-base mr-2">check_circle</span> Setujui Usulan
                            </button>
                            
                            <button type="submit" name="aksi" value="revisi" 
                                    class="w-full py-3.5 bg-amber-500 text-white font-bold rounded-xl shadow-md hover:bg-amber-600 transition-all flex justify-center items-center"
                                    onclick="return combineCatatan('revisi');">
                                <span class="material-icons text-base mr-2">autorenew</span> Kembalikan (Revisi)
                            </button>
                            
                            <button type="submit" name="aksi" value="tolak" 
                                    class="w-full py-3.5 bg-rose-600 text-white font-bold rounded-xl shadow-md hover:bg-rose-700 transition-all flex justify-center items-center"
                                    onclick="return combineCatatan('tolak');">
                                <span class="material-icons text-base mr-2">cancel</span> Tolak Usulan
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
            if (combined.trim() === '') {
                alert('Harap berikan catatan pada minimal satu item (KAK/IKU/RAB) untuk melakukan Revisi atau Penolakan.');
                if (allItemNotes.length > 0) {
                    for(let i=0; i<allItemNotes.length; i++) {
                        if(allItemNotes[i].offsetParent !== null) {
                            allItemNotes[i].focus();
                            break;
                        }
                    }
                }
                return false;
            }
             return confirm(`Yakin ingin ${aksi} usulan ini?`);
        }
        
        return true;
    }
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>