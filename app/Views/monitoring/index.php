<?php 
// app/Views/monitoring/index.php (FINALIZED: Kaskade Status P5>P1 dan Dynamic Search Dihapus)
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

// Helper untuk status box
function getStatusBox($status, $date = null, $isPulse = false) {
    $statusText = $status;
    $dateStr = $date;
    
    // Format date string if it looks like a full timestamp
    if ($dateStr && !strpos($dateStr, '%') && !strpos($dateStr, 'Cair') && strlen($dateStr) > 10) {
        $dateStr = date('d/m/Y', strtotime($date));
    }

    $pulse = $isPulse ? ' animate-pulse' : '';
    
    $color = match($status) {
        'Diajukan', 'Menunggu Verif', 'Siap Cair', 'LPJ Diajukan' => 'bg-blue-100 text-blue-700 border-blue-400' . $pulse,
        'Disetujui', 'ACC' => 'bg-emerald-100 text-emerald-700 border-emerald-400',
        'Revisi' => 'bg-amber-100 text-amber-700 border-amber-400' . $pulse,
        'Ditolak' => 'bg-slate-800 text-white border-rose-400',
        
        'Menunggu PPK' => 'bg-violet-100 text-violet-700 border-violet-400' . $pulse,
        'Menunggu WD2' => 'bg-indigo-100 text-indigo-700 border-indigo-400' . $pulse,
        
        'Pencairan Bertahap' => 'bg-cyan-100 text-cyan-700 border-cyan-400',
        'Pencairan Selesai' => 'bg-emerald-100 text-emerald-700 border-emerald-400',
        
        'LPJ Revisi' => 'bg-orange-100 text-orange-700 border-orange-400' . $pulse,
        'LPJ Wajib Upload' => 'bg-amber-100 text-amber-700 border-amber-400',
        'LPJ Disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-400', 
        
        'Selesai' => 'bg-slate-800 text-white border-slate-600',
        default => 'bg-slate-50 text-slate-400 border-slate-200'
    };

    return [
        'color' => $color,
        'text' => $statusText,
        'date' => $dateStr
    ];
}

// FUNGSI DERIVASI FINAL STATUS (Paling Mutakhir)
function deriveFinalStatus($u) {
    // Menggunakan operator Null Coalescing (??) untuk menangani nilai null dari subquery di Model
    $usulanStatus = trim($u['status_terkini'] ?? '');
    $pengajuanStatusPPK = trim($u['pengajuan_status_ppk'] ?? '');
    $pengajuanStatusWD2 = trim($u['pengajuan_status_wd2'] ?? '');
    $lpjStatus = trim($u['lpj_status'] ?? '');
    $totalCair = $u['total_sudah_cair'] ?? 0;
    $nominalRAB = $u['nominal_pencairan'] ?? 0;
    
    $isFullCair = ($nominalRAB > 0) && (abs($totalCair - $nominalRAB) < 0.01);
    $hasPengajuan = !empty($pengajuanStatusPPK);
    $hasPencairanStarted = ($totalCair > 0);
    $isReadyForPencairan = ($pengajuanStatusWD2 === 'Disetujui');
    
    // --- CASCADING LOGIC: P5 (LPJ) -> P4 (Cair) -> P3 (WD2) -> P2 (PPK) -> P1 (Verif) ---
    
    // P5: Status LPJ (Prioritas Tertinggi: Selesai)
    if ($lpjStatus === 'Disetujui') {
        return 'Selesai';
    } 
    // P5 Lanjutan (LPJ sedang diproses/bermasalah, hanya relevan jika ada pencairan/siap cair)
    if ($hasPencairanStarted || $isReadyForPencairan) { 
        if ($lpjStatus === 'Diajukan') return 'LPJ Diajukan';
        if ($lpjStatus === 'Revisi') return 'LPJ Revisi';
        if ($lpjStatus === 'Belum Upload') return 'LPJ Wajib Upload';
    }
    
    // P4: Status Pencairan
    if ($isReadyForPencairan) {
        if ($isFullCair) return 'Pencairan Selesai';
        if ($hasPencairanStarted) return 'Pencairan Bertahap';
        if ($totalCair == 0) return 'Siap Pencairan'; // WD2 Approved, $0.00 cair
    }

    // P3: Approval WD2
    if ($hasPengajuan && $pengajuanStatusPPK === 'Disetujui' && $pengajuanStatusWD2 === 'Menunggu') {
        return 'Menunggu WD2';
    }
    
    // P2: Approval PPK
    if ($hasPengajuan && $pengajuanStatusPPK === 'Menunggu' && ($usulanStatus === 'Disetujui' || $usulanStatus === 'Selesai')) { // FIX: Tambah Selesai agar fase ini tetap terhitung
        return 'Menunggu PPK';
    }
    
    // P1: Status Verifikator/Initial (Prioritas Terendah)
    if ($usulanStatus === 'Disetujui' || $usulanStatus === 'Selesai') return 'Disetujui Verifikator'; // FIX: Tambah Selesai
    if ($usulanStatus === 'Diajukan') return 'Menunggu Verif';
    if ($usulanStatus === 'Revisi') return 'Revisi';
    if ($usulanStatus === 'Ditolak') return 'Ditolak';
    if ($usulanStatus === 'Draft') return 'Draft';
    
    // Fallback
    return 'Status Tidak Diketahui'; 
}

// FUNGSI DERIVASI STATUS KOMPLEKS (Updated to use deriveFinalStatus for the final output)
function deriveStatus($u) {
    // Menggunakan operator Null Coalescing (??) untuk menangani nilai null dari subquery di Model
    $usulanStatus = trim($u['status_terkini'] ?? '');
    $pengajuanStatusPPK = trim($u['pengajuan_status_ppk'] ?? '');
    $pengajuanStatusWD2 = trim($u['pengajuan_status_wd2'] ?? '');
    
    $lpjStatus = trim($u['lpj_status'] ?? '');
    $totalCair = $u['total_sudah_cair'] ?? 0;
    $nominalRAB = $u['nominal_pencairan'] ?? 0;
    
    // Safety check for division by zero
    $isFullCair = ($nominalRAB > 0) && (abs($totalCair - $nominalRAB) < 0.01);
    $hasPengajuan = !empty($pengajuanStatusPPK);
    
    // Tentukan Tanggal Acuan Pengajuan Kegiatan (Tanggal saat P2 dimulai)
    $tglAcuanKegiatan = $u['tgl_pengajuan_ppk'] ?: $u['updated_at'];
    
    // --- Derivasi Status Akhir dan Per Stage ---
    
    // Menggunakan fungsi yang sudah diperbaiki
    $statusAkhir = deriveFinalStatus($u);

    // P1: Verifikator
    $p1 = ['status' => 'Belum', 'date' => null];
    if ($usulanStatus === 'Diajukan') { $p1['status'] = 'Menunggu Verif'; $p1['date'] = $u['updated_at']; }
    if ($usulanStatus === 'Revisi') { $p1['status'] = 'Revisi'; $p1['date'] = $u['updated_at']; }
    if ($usulanStatus === 'Ditolak') { $p1['status'] = 'Ditolak'; $p1['date'] = $u['updated_at']; }
    if ($usulanStatus === 'Disetujui' || $usulanStatus === 'Selesai') { 
        $p1['status'] = 'ACC'; 
        // Gunakan tgl_status_ppk sebagai fallback tanggal persetujuan Verif jika tidak ada tanggal yang jelas untuk verif.
        $p1['date'] = $u['tgl_pengajuan_ppk'] ?: $u['updated_at']; 
    }

    // P2: Approval PPK
    $p2 = ['status' => 'Belum', 'date' => null];
    // FIX: Gunakan base status ACC P1 ATAU check langsung status PPK jika sudah ada
    if (trim($p1['status']) === 'ACC' || $pengajuanStatusPPK === 'Disetujui') { 
        if ($pengajuanStatusPPK === 'Menunggu') {
            $p2['status'] = 'Menunggu PPK';
            $p2['date'] = $tglAcuanKegiatan; 
        } elseif ($pengajuanStatusPPK === 'Disetujui') {
            $p2['status'] = 'ACC';
            if (!empty($u['tgl_status_ppk'])) {
                $p2['date'] = $u['tgl_status_ppk'];
            }
        }
    }
    
    // P3: Approval WD2
    $p3 = ['status' => 'Belum', 'date' => null];
    // FIX: Gunakan base status ACC P2 ATAU check langsung status WD2 jika sudah ada
    if (trim($p2['status']) === 'ACC' || $pengajuanStatusWD2 === 'Disetujui') { 
        if ($pengajuanStatusWD2 === 'Menunggu') {
            $p3['status'] = 'Menunggu WD2'; 
            $p3['date'] = $u['tgl_status_ppk'] ?: $tglAcuanKegiatan;
        } elseif ($pengajuanStatusWD2 === 'Disetujui') {
            $p3['status'] = 'ACC'; 
            if (!empty($u['tgl_status_wd2'])) {
                $p3['date'] = $u['tgl_status_wd2'];
            }
        }
    }

    // P4: Pencairan (Bendahara)
    $p4 = ['status' => 'Belum', 'date' => null];
    if (trim($p3['status']) === 'ACC' || $totalCair > 0) { // FIX: Selalu hitung jika sudah ada pencairan
        if ($isFullCair) {
            $p4['status'] = 'Pencairan Selesai'; $p4['date'] = 'Lunas';
        } elseif ($totalCair > 0) {
            $p4['status'] = 'Pencairan Bertahap'; $p4['date'] = round(($totalCair / $nominalRAB) * 100) . '% Cair';
        } elseif (trim($p3['status']) === 'ACC' && $totalCair == 0) {
            $p4['status'] = 'Siap Cair'; $p4['date'] = $u['tgl_status_wd2'] ?: $tglAcuanKegiatan;
        }
        
        if ($totalCair > 0 && !empty($u['tgl_cair_pertama'])) {
             $p4['date'] = $u['tgl_cair_pertama'];
        }
    }
    
    // P5: LPJ (Dari status LPJ)
    $p5 = ['status' => 'Belum', 'date' => null];
    if ($statusAkhir === 'Selesai' || $p4['status'] === 'Pencairan Selesai' || $p4['status'] === 'Pencairan Bertahap' || $statusAkhir === 'Selesai') { // Tambahkan Selesai agar P5 tetap terisi jika sudah selesai
        $lpjDate = $u['tgl_batas_lpj'] ?? $tglAcuanKegiatan;
        if ($lpjStatus === 'Disetujui') { $p5['status'] = 'LPJ Disetujui'; $p5['date'] = 'Selesai'; } 
        elseif ($lpjStatus === 'Diajukan') { $p5['status'] = 'LPJ Diajukan'; $p5['date'] = $lpjDate; }
        elseif ($lpjStatus === 'Revisi') { $p5['status'] = 'LPJ Revisi'; $p5['date'] = $lpjDate; } 
        elseif ($p4['status'] !== 'Belum') { $p5['status'] = 'LPJ Wajib Upload'; $p5['date'] = $lpjDate;} 
    }
    

    return ['p1' => getStatusBox($p1['status'], $p1['date'], true), 
            'p2' => getStatusBox($p2['status'], $p2['date'], true), 
            'p3' => getStatusBox($p3['status'], $p3['date'], true),
            'p4' => getStatusBox($p4['status'], $p4['date'], true), 
            'p5' => getStatusBox($p5['status'], $p5['date'], true),
            'final' => $statusAkhir
            ];
}

// Helper function untuk kelas warna status kolom akhir (PENTING AGAR TIDAK ERROR)
function getStatusClass($status) {
    return match($status) {
        'Draft' => 'bg-amber-100 text-amber-700',
        'Diajukan', 'Menunggu Verif' => 'bg-blue-100 text-blue-700',
        'Revisi', 'LPJ Revisi' => 'bg-rose-100 text-rose-700',
        'Ditolak' => 'bg-slate-800 text-white',
        
        'Disetujui', 'Disetujui Verifikator' => 'bg-emerald-100 text-emerald-700',
        'Menunggu PPK' => 'bg-violet-100 text-violet-700',
        'Menunggu WD2' => 'bg-indigo-100 text-indigo-700',

        // Pencairan Statuses
        'Siap Pencairan', 'Pencairan Bertahap', 'Pencairan Selesai' => 'bg-cyan-100 text-cyan-700',
        
        // LPJ Statuses
        'LPJ Diajukan', 'LPJ Wajib Upload' => 'bg-amber-100 text-amber-700',
        
        // Final Status
        'Selesai' => 'bg-slate-800 text-white',
        default => 'bg-gray-100 text-gray-700',
    };
}


?>

<div class="m-4 md:m-5">
    <?php $role = $_SESSION['role'] ?? ''; $isPengusul = $role === 'Pengusul'; $perPage = 5; ?>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-6 md:mb-8 gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Monitoring Progres Kegiatan</h1>
            <p class="text-slate-500 mt-1 text-sm md:text-base">Pelacakan real-time status usulan yang Anda ajukan.</p>
        </div>
        <div class="w-full sm:w-auto bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm text-sm text-center">
            <span class="font-bold text-slate-700">Total Usulan Anda:</span> 
            <span class="text-blue-600 font-mono ml-1 font-bold"><?php echo isset($total) ? $total : count($usulan); ?></span>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <?php if (empty($usulan)): ?>
            <div class="p-8 md:p-12 text-center">
                <span class="material-icons text-slate-300 text-5xl md:text-6xl mb-2 md:mb-4">toc</span>
                <h3 class="text-base md:text-lg font-bold text-slate-700">Tidak ada data</h3>
                <p class="text-slate-500 text-xs md:text-sm mt-1">Belum ada usulan yang masuk kriteria pencarian.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto w-full custom-scrollbar pb-2">
                <table class="w-full text-sm text-left border-collapse min-w-[1000px]">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-bold text-[10px] md:text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-4 w-16 text-center">ID</th>
                            <th class="px-4 py-4 min-w-[250px]">Detail Kegiatan</th>
                            <th class="px-2 py-4 text-center w-28 border-l border-slate-200 bg-slate-100/50 min-w-[120px]">Verifikator</th>
                            <th class="px-2 py-4 text-center w-28 border-l border-slate-200 bg-slate-100/50 min-w-[120px]">PPK</th>
                            <th class="px-2 py-4 text-center w-28 border-l border-slate-200 bg-slate-100/50 min-w-[120px]">WD2</th>
                            <th class="px-2 py-4 text-center w-28 border-l border-slate-200 bg-slate-100/50 min-w-[120px]">Pencairan</th>
                            <th class="px-2 py-4 text-center w-28 border-l border-slate-200 bg-slate-100/50 min-w-[120px]">LPJ</th>
                            <th class="px-4 py-4 text-center w-32 border-l border-slate-200 min-w-[140px]">Status Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($usulan as $row): 
                            $derivation = deriveStatus($row);
                            $isLate = false;
                            
                            if (!empty($row['tgl_batas_lpj']) && $row['status_terkini'] !== 'Selesai' && $row['status_terkini'] !== 'Ditolak') {
                                if (new DateTime() > new DateTime($row['tgl_batas_lpj'])) $isLate = true;
                            }

                        ?>
                        <tr class="hover:bg-slate-50 transition-colors group <?php echo $isLate ?? false ? 'bg-rose-50/40' : ''; ?>">
                            <td class="px-4 py-4 text-center font-mono text-xs text-slate-400">#<?php echo $row['id']; ?></td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-800 group-hover:text-blue-700 transition-colors mb-1 whitespace-normal leading-snug">
                                    <?php echo htmlspecialchars($row['nama_kegiatan']); ?>
                                </div>
                                <div class="text-xs text-slate-500">
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-mono font-bold">
                                        <?php echo formatRupiah($row['nominal_pencairan']); ?>
                                    </span>
                                </div>
                            </td>
                            
                            <td class="px-4 py-4 text-center border-l border-slate-100 bg-slate-50/30">
                                <span class="inline-flex px-2 py-1 rounded text-[10px] font-bold border border-slate-200 whitespace-nowrap bg-emerald-100 text-emerald-700">
                                    Contoh Status
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 md:px-6 py-4 border-t border-slate-100 bg-slate-50 flex flex-col sm:flex-row justify-between items-center gap-3">
                <div class="text-[10px] md:text-xs text-slate-500 font-medium">Halaman <?php echo $page; ?> dari <?php echo $totalPages; ?></div>
                <div class="flex gap-1 flex-wrap justify-center">
                    <?php 
                        $queryParams = $_GET; unset($queryParams['page']);
                        $queryString = http_build_query($queryParams); $connector = !empty($queryString) ? '&' : '';
                    ?>
                    <?php if ($page > 1): ?>
                        <a href="/monitoring?page=<?php echo $page - 1; ?><?php echo $connector . $queryString; ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all bg-white border border-slate-200 text-slate-600 hover:bg-slate-200 shadow-sm">&lt;</a>
                    <?php endif; ?>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a href="/monitoring?page=<?php echo $p; ?><?php echo $connector . $queryString; ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all <?php echo ($p == $page) ? 'bg-slate-800 text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-200 shadow-sm'; ?>"><?php echo $p; ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="/monitoring?page=<?php echo $page + 1; ?><?php echo $connector . $queryString; ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all bg-white border border-slate-200 text-slate-600 hover:bg-slate-200 shadow-sm">&gt;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>