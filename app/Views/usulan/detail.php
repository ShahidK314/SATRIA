<?php 
// app/Views/usulan/detail.php (RESPONSIVE)
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

function extractCatatan($log, $section) {
    if (empty($log['catatan'])) return '';
    $fullNote = $log['catatan'];

    if ($section === 'RINGKASAN') {
        if (preg_match('/^RINGKASAN: (.*?)(?:\n\n|\Z)/s', $fullNote, $matches)) {
            return trim($matches[1]);
        }
        if (in_array($log['status_baru'], ['Revisi', 'Ditolak']) && strpos($fullNote, 'Catatan KAK:') === false) {
             return $fullNote;
        }
        return '';
    }
    
    if ($section === 'KAK') {
        if (preg_match('/Catatan KAK:\s*\n(.*?)(\n\nCatatan IKU:|\n\nCatatan RAB:|\Z)/s', $fullNote, $matches)) return trim($matches[1]);
    } elseif ($section === 'IKU') {
        if (preg_match('/Catatan IKU:\s*\n(.*?)(\n\nCatatan KAK:|\n\nCatatan RAB:|\Z)/s', $fullNote, $matches)) return trim($matches[1]);
    } elseif ($section === 'RAB') {
        if (preg_match('/Catatan RAB:\s*\n(.*?)(\Z)/s', $fullNote, $matches)) return trim($matches[1]);
    }
    
    return '';
}

function extractNotesArray($rawNotes) {
    $result = [];
    if (empty($rawNotes)) return $result;
    $lines = preg_split('/\r\n|\r|\n/', $rawNotes);
    foreach ($lines as $line) {
        $line = trim($line);
        if (preg_match('/^-\s*\[(.*?)\]\s*(.*)/', $line, $m)) {
            $field = trim($m[1]);
            $note  = trim($m[2]);
            $key = strtolower(str_replace(' ', '_', $field));
            $result[$key] = $note;
        }
    }
    return $result;
}

function getFieldNote($notesArr, $fieldKeys) {
    if (empty($notesArr)) return '';
    if (!is_array($fieldKeys)) $fieldKeys = [$fieldKeys];
    foreach ($fieldKeys as $k) {
        $kNorm = strtolower(str_replace(' ', '_', $k));
        if (isset($notesArr[$kNorm]) && !empty($notesArr[$kNorm])) {
            return $notesArr[$kNorm];
        }
    }
    return '';
}

function getStatusClass($status) {
    return match($status) {
        'Draft' => 'bg-amber-100 text-amber-700',
        'Diajukan' => 'bg-blue-100 text-blue-700',
        'Revisi', 'LPJ Revisi' => 'bg-rose-100 text-rose-700',
        'Ditolak' => 'bg-slate-800 text-white',
        'Disetujui', 'Selesai' => 'bg-emerald-100 text-emerald-700',
        default => 'bg-gray-100 text-gray-700',
    };
}
?>

<div class="m-4 md:m-5">
    <div class="mb-6 md:mb-8">
        <a href="/pengajuan/usulan" class="text-slate-500 hover:text-blue-600 font-bold flex items-center gap-2 mb-4 transition-colors w-fit">
            <span class="material-icons text-sm">arrow_back</span> Kembali ke Daftar Usulan
        </a>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Detail Usulan Kegiatan</h1>
                <p class="text-slate-500 mt-1 text-sm md:text-base">Informasi lengkap KAK, IKU, dan RAB kegiatan.</p>
            </div>
            <div class="flex gap-2 w-full md:w-auto">
                <a href="/pdf/kak?id=<?php echo $usulan['id']; ?>" target="_blank" class="flex-1 md:flex-none justify-center px-4 py-2 bg-rose-600 text-white font-bold rounded-lg hover:bg-rose-700 text-sm flex items-center shadow-sm" title="Cetak KAK">
                    <span class="material-icons text-lg mr-1">picture_as_pdf</span> KAK
                </a>
                <a href="/pdf/rab?id=<?php echo $usulan['id']; ?>" target="_blank" class="flex-1 md:flex-none justify-center px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 text-sm flex items-center shadow-sm" title="Cetak RAB">
                    <span class="material-icons text-lg mr-1">print</span> RAB
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6 mb-8">
        <h2 class="text-xl md:text-2xl font-bold text-slate-800 mb-4"><?php echo htmlspecialchars($usulan['nama_kegiatan']); ?></h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-slate-100 pt-4">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Status Terkini</div>
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold <?php echo getStatusClass($usulan['status_terkini']); ?>">
                    <?php echo htmlspecialchars($usulan['status_terkini']); ?>
                </span>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Total RAB (Pencairan)</div>
                <div class="text-base md:text-lg font-bold text-emerald-600"><?php echo formatRupiah($usulan['nominal_pencairan']); ?></div>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Kurun Waktu</div>
                <div class="text-sm md:text-base text-slate-700 font-medium"><?php echo date('d M Y', strtotime($usulan['tanggal_mulai'])); ?> - <?php echo date('d M Y', strtotime($usulan['tanggal_selesai'])); ?></div>
            </div>
        </div>
        
        <?php 
        $latestLog = null;
        foreach ($logHistori as $log) {
            if (in_array($log['status_baru'], ['Revisi', 'Ditolak'])) {
                $latestLog = $log;
                break;
            }
        }
        ?>
    </div>

    <?php
    $kakNotesArr = [];
    $ikuNotesArr = [];
    $rabNotesArr = [];
    if (!empty($latestLog)) {
        $kakNotesArr = extractNotesArray(extractCatatan($latestLog, 'KAK'));
        $ikuNotesArr = extractNotesArray(extractCatatan($latestLog, 'IKU'));
        $rabNotesArr = extractNotesArray(extractCatatan($latestLog, 'RAB'));
    }
    ?>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6 mb-8">
        <h3 class="text-lg md:text-xl font-bold text-slate-800 mb-4 flex items-center">
            <span class="material-icons text-blue-600 mr-2">assignment</span> KAK (Kerangka Acuan Kegiatan)
        </h3>
        
        <div class="space-y-4 md:space-y-6 text-sm">
            <div class="flex flex-col md:flex-row md:items-start gap-2 md:gap-4">
                <div class="flex-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Nama Kegiatan</label>
                    <p class="text-slate-800 font-bold"><?php echo htmlspecialchars($usulan['nama_kegiatan']); ?></p>
                </div>
                <?php $note = getFieldNote($kakNotesArr, ['nama_kegiatan', 'judul', 'nama']); ?>
                <?php if ($note): ?>
                    <div class="w-full md:w-64 text-xs text-rose-700 bg-rose-50 border border-rose-200 p-3 rounded-lg shadow-sm">
                        <div class="font-bold text-rose-800 mb-1 flex items-center"><span class="material-icons text-[14px] mr-1">error_outline</span> Catatan Revisi</div>
                        <div><?php echo htmlspecialchars($note); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-2 md:gap-4 border-t border-slate-100 pt-4">
                <div class="flex-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Kurun Waktu</label>
                    <div class="text-slate-800 font-medium"><?php echo date('d M Y', strtotime($usulan['tanggal_mulai'])); ?> - <?php echo date('d M Y', strtotime($usulan['tanggal_selesai'])); ?></div>
                </div>
                <?php $note = getFieldNote($kakNotesArr, ['Kurun Waktu', 'tanggal_mulai', 'tanggal_selesai', 'Kurun Waktu_kegiatan']); ?>
                <?php if ($note): ?>
                    <div class="w-full md:w-64 text-xs text-rose-700 bg-rose-50 border border-rose-200 p-3 rounded-lg shadow-sm">
                        <div class="font-bold text-rose-800 mb-1 flex items-center"><span class="material-icons text-[14px] mr-1">error_outline</span> Catatan Revisi</div>
                        <div><?php echo htmlspecialchars($note); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-2 md:gap-4 border-t border-slate-100 pt-4">
                <div class="flex-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Gambaran Umum / Latar Belakang</label>
                    <p class="text-slate-700 mt-1 bg-slate-50 border border-slate-100 p-3 rounded-lg max-h-40 overflow-y-auto leading-relaxed"><?php echo nl2br(htmlspecialchars($usulan['gambaran_umum'])); ?></p>
                </div>
                <?php $note = getFieldNote($kakNotesArr, ['gambaran_umum', 'latar_belakang', 'deskripsi']); ?>
                <?php if ($note): ?>
                    <div class="w-full md:w-64 text-xs text-rose-700 bg-rose-50 border border-rose-200 p-3 rounded-lg shadow-sm">
                        <div class="font-bold text-rose-800 mb-1 flex items-center"><span class="material-icons text-[14px] mr-1">error_outline</span> Catatan Revisi</div>
                        <div><?php echo htmlspecialchars($note); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-2 md:gap-4 border-t border-slate-100 pt-4">
                <div class="flex-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Penerima Manfaat</label>
                    <p class="text-slate-700 mt-1 bg-slate-50 border border-slate-100 p-3 rounded-lg"><?php echo htmlspecialchars($usulan['penerima_manfaat']); ?></p>
                </div>
                <?php $note = getFieldNote($kakNotesArr, ['penerima_manfaat', 'penerima']); ?>
                <?php if ($note): ?>
                    <div class="w-full md:w-64 text-xs text-rose-700 bg-rose-50 border border-rose-200 p-3 rounded-lg shadow-sm">
                        <div class="font-bold text-rose-800 mb-1 flex items-center"><span class="material-icons text-[14px] mr-1">error_outline</span> Catatan Revisi</div>
                        <div><?php echo htmlspecialchars($note); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-2 md:gap-4 border-t border-slate-100 pt-4">
                <div class="flex-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Strategi Pencapaian Keluaran</label>
                    <p class="text-slate-700 mt-1 bg-slate-50 border border-slate-100 p-3 rounded-lg max-h-40 overflow-y-auto leading-relaxed"><?php echo nl2br(htmlspecialchars($usulan['strategi_pencapaian_keluaran'])); ?></p>
                </div>
                <?php $note = getFieldNote($kakNotesArr, ['strategi_pencapaian_keluaran', 'strategi']); ?>
                <?php if ($note): ?>
                    <div class="w-full md:w-64 text-xs text-rose-700 bg-rose-50 border border-rose-200 p-3 rounded-lg shadow-sm">
                        <div class="font-bold text-rose-800 mb-1 flex items-center"><span class="material-icons text-[14px] mr-1">error_outline</span> Catatan Revisi</div>
                        <div><?php echo htmlspecialchars($note); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-2 md:gap-4 border-t border-slate-100 pt-4">
                <div class="flex-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Metode Pelaksanaan</label>
                    <ul class="list-disc list-inside text-slate-700 mt-1 bg-slate-50 border border-slate-100 p-3 rounded-lg">
                        <?php if (!empty($usulan['metode_array'])): foreach ($usulan['metode_array'] as $m): ?>
                            <li class="mb-1"><?php echo htmlspecialchars($m); ?></li>
                        <?php endforeach; else: ?>
                            <li class="italic text-slate-400">N/A</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php $note = getFieldNote($kakNotesArr, ['metode_pelaksanaan', 'metode']); ?>
                <?php if ($note): ?>
                    <div class="w-full md:w-64 text-xs text-rose-700 bg-rose-50 border border-rose-200 p-3 rounded-lg shadow-sm">
                        <div class="font-bold text-rose-800 mb-1 flex items-center"><span class="material-icons text-[14px] mr-1">error_outline</span> Catatan Revisi</div>
                        <div><?php echo htmlspecialchars($note); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-2 md:gap-4 border-t border-slate-100 pt-4">
                <div class="flex-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Tahapan Pelaksanaan</label>
                    <ul class="list-decimal list-inside text-slate-700 mt-1 bg-slate-50 border border-slate-100 p-3 rounded-lg">
                        <?php if (!empty($usulan['tahapan_array'])): foreach ($usulan['tahapan_array'] as $t): ?>
                            <li class="mb-1"><?php echo htmlspecialchars($t); ?></li>
                        <?php endforeach; else: ?>
                            <li class="italic text-slate-400">N/A</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php $note = getFieldNote($kakNotesArr, ['tahapan_pelaksanaan', 'tahapan']); ?>
                <?php if ($note): ?>
                    <div class="w-full md:w-64 text-xs text-rose-700 bg-rose-50 border border-rose-200 p-3 rounded-lg shadow-sm">
                        <div class="font-bold text-rose-800 mb-1 flex items-center"><span class="material-icons text-[14px] mr-1">error_outline</span> Catatan Revisi</div>
                        <div><?php echo htmlspecialchars($note); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="border-t border-slate-100 pt-4">
                <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block">Indikator Kinerja & Bobot</label>
                <div class="overflow-x-auto w-full custom-scrollbar pb-2">
                    <table class="w-full text-xs border border-slate-200 min-w-[500px]">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="p-3 border border-slate-200 text-left">Indikator</th>
                                <th class="p-3 border border-slate-200 text-center w-24">Bulan Target</th>
                                <th class="p-3 border border-slate-200 text-center w-20">Bobot (%)</th>
                                <th class="p-3 border border-slate-200 text-left w-1/4">Catatan Revisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (!empty($usulan['indikator_array'])): $totalBobot = 0; foreach ($usulan['indikator_array'] as $kpiIdx => $i): $totalBobot += $i['bobot']; ?>
                            <tr>
                                <td class="p-3 text-slate-700"><?php echo htmlspecialchars($i['indikator']); ?></td>
                                <td class="p-3 text-center text-slate-600 font-medium"><?php echo htmlspecialchars($i['bulan_target']); ?></td>
                                <td class="p-3 text-center font-bold text-blue-600"><?php echo $i['bobot']; ?>%</td>
                                <td class="p-2">
                                     <?php $note = getFieldNote($kakNotesArr, ['kpi_'.$kpiIdx]); ?>
                                     <?php if ($note): ?>
                                         <div class="text-[11px] text-rose-700 bg-rose-50 border border-rose-200 p-2 rounded-lg leading-snug">
                                             <?php echo htmlspecialchars($note); ?>
                                         </div>
                                     <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="bg-slate-50 font-bold border-t border-slate-200">
                                <td colspan="2" class="p-3 text-right uppercase text-slate-500">Total Bobot:</td>
                                <td class="p-3 text-center text-emerald-600"><?php echo $totalBobot; ?>%</td>
                                <td class="p-3"></td>
                            </tr>
                            <?php else: ?>
                                <tr><td colspan="4" class="p-4 text-center text-slate-400 italic">Tidak ada indikator kinerja yang dicatat.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6 mb-8">
        <h3 class="text-lg md:text-xl font-bold text-slate-800 mb-4 flex items-center">
            <span class="material-icons text-emerald-600 mr-2">trending_up</span> IKU (Indikator Kinerja Utama)
        </h3>
        
        <?php if (empty($ikuDetails)): ?>
            <p class="text-slate-400 text-sm italic">Tidak ada IKU yang dipilih untuk usulan ini.</p>
        <?php else: ?>
            <div class="overflow-x-auto w-full custom-scrollbar pb-2">
                <table class="w-full text-sm border border-slate-200 min-w-[500px]">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="p-3 border border-slate-200 text-left w-1/2">Deskripsi IKU</th>
                            <th class="p-3 border border-slate-200 text-center w-32">Target</th>
                            <th class="p-3 border border-slate-200 text-left w-1/3">Catatan Revisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($ikuDetails as $ikuIdx => $iku): ?>
                        <tr>
                            <td class="p-3 text-slate-700 leading-snug"><?php echo htmlspecialchars($iku['deskripsi_iku']); ?></td>
                            <td class="p-3 text-center font-bold text-blue-600"><?php echo htmlspecialchars($iku['target'] ?? '-'); ?></td>
                            <td class="p-2">
                                 <?php $note = getFieldNote($ikuNotesArr, ['iku_'.$ikuIdx]); ?>
                                 <?php if ($note): ?>
                                     <div class="text-[11px] text-rose-700 bg-rose-50 border border-rose-200 p-2 rounded-lg leading-snug">
                                         <?php echo htmlspecialchars($note); ?>
                                     </div>
                                 <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6 mb-8">
        <h3 class="text-lg md:text-xl font-bold text-slate-800 mb-4 flex items-center">
            <span class="material-icons text-amber-600 mr-2">payments</span> RAB (Rencana Anggaran Biaya)
        </h3>

        <?php if (empty($rabDetails)): ?>
            <p class="text-slate-400 text-sm italic text-center p-4">Tidak ada item RAB yang dicatat.</p>
        <?php else: ?>
            <div class="overflow-x-auto w-full custom-scrollbar pb-2">
                <table class="w-full text-sm border border-slate-200 whitespace-nowrap min-w-[950px]">
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
                            <th class="p-3 border border-slate-200 text-left w-2/12">Catatan Revisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php 
                        $grandTotalRAB = 0;
                        $currentCat = '';

                        foreach ($rabDetails as $rabIdx => $rab):
                            $grandTotalRAB += $rab['total'];
                        ?>
                        <tr>
                            <td class="p-3 font-bold text-xs text-slate-800 <?= $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : '' ?>">
                                <?= htmlspecialchars($rab['nama_kategori']) ?>
                            </td>

                            <td class="p-3 text-slate-700 font-medium <?= $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : '' ?>">
                                <?= htmlspecialchars($rab['deskripsi']) ?>
                            </td>

                            <?php 
                                $v1 = floatval($rab['volume_factor_1'] ?? 0);
                                $s1 = htmlspecialchars($rab['nama_satuan_f1'] ?? '');
                                $v2 = floatval($rab['volume_factor_2'] ?? 0);
                                $s2 = htmlspecialchars($rab['nama_satuan_f2'] ?? '');
                                
                                if(empty($s1) && $rab['volume'] > 0) {
                                     $v1 = floatval($rab['volume']);
                                     $s1 = htmlspecialchars($rab['nama_satuan'] ?? '');
                                }
                            ?>

                            <td class="p-3 text-center text-slate-600 <?= $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : '' ?>">
                                <?= $v1 > 0 ? $v1 : '-' ?>
                            </td>
                            <td class="p-3 text-center text-slate-500 text-xs <?= $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : '' ?>">
                                <?= $s1 ?>
                            </td>
                            
                            <td class="p-3 text-center text-slate-300 font-bold <?= $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : '' ?>">
                                <?= ($v2 > 0) ? 'x' : '' ?>
                            </td>
                            
                            <td class="p-3 text-center text-slate-600 <?= $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : '' ?>">
                                <?= $v2 > 0 ? $v2 : '-' ?>
                            </td>
                            <td class="p-3 text-center text-slate-500 text-xs <?= $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : '' ?>">
                                <?= $v2 > 0 ? $s2 : '-' ?>
                            </td>
                            
                            <td class="p-3 text-center text-slate-300 font-bold <?= $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : '' ?>">
                                x
                            </td>

                            <td class="p-3 text-right text-xs text-slate-600 <?= $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : '' ?>">
                                <?= formatRupiah($rab['harga_satuan']) ?>
                            </td>

                            <td class="p-3 text-right font-bold text-emerald-700 <?= $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : '' ?>">
                                <?= formatRupiah($rab['total']) ?>
                            </td>
                            
                            <td class="p-2 text-left text-xs <?= $rab['nama_kategori'] !== $currentCat ? 'border-t-2 border-slate-300' : '' ?>">
                                <?php $note = getFieldNote($rabNotesArr, ['rab_'.$rabIdx]); ?>
                                 <?php if ($note): ?>
                                     <div class="text-[10px] md:text-[11px] text-rose-700 bg-rose-50 border border-rose-200 p-2 rounded-lg whitespace-normal leading-snug">
                                         <?php echo htmlspecialchars($note); ?>
                                     </div>
                                 <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            $currentCat = $rab['nama_kategori']; 
                        endforeach; 
                        ?>

                        <tr class="bg-amber-50 font-bold border-t-2 border-slate-300">
                            <td colspan="9" class="p-3 text-right uppercase text-slate-600">Grand Total RAB :</td>
                            <td class="p-3 text-right text-lg md:text-xl text-amber-700"><?= formatRupiah($grandTotalRAB) ?></td>
                            <td class="p-3"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>