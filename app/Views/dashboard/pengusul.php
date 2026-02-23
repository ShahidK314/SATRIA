<?php 
// app/Views/dashboard/pengusul.php
include __DIR__.'/../partials/sidebar.php'; 
// Asumsi variabel: $stats, $usulan, $pager

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

if (!function_exists('deriveFinalStatus')) {
    function deriveFinalStatus($u) {
        $usulanStatus = trim($u['status_terkini'] ?? '');
        $pengajuanStatusPPK = trim($u['pengajuan_status_ppk'] ?? '');
        $pengajuanStatusWD2 = trim($u['pengajuan_status_wd2'] ?? '');
        $lpjStatus = trim($u['lpj_status'] ?? '');
        $totalCair = $u['total_sudah_cair'] ?? 0;
        $nominalRAB = $u['nominal_pencairan'] ?? 0;
        
        $hasPencairanStarted = ($totalCair > 0);
        $isReadyForPencairan = ($pengajuanStatusWD2 === 'Disetujui');
        
        if ($lpjStatus === 'Disetujui' || $usulanStatus === 'Selesai') return 'Selesai';
        if ($hasPencairanStarted || $isReadyForPencairan) { 
            if ($lpjStatus === 'Diajukan') return 'LPJ Diajukan';
            if ($lpjStatus === 'Revisi') return 'LPJ Revisi';
            if ($lpjStatus === 'Belum Upload') return 'LPJ Wajib Upload';
        }
        if ($isReadyForPencairan) {
            $isFullCair = ($nominalRAB > 0) && (abs($totalCair - $nominalRAB) < 0.01);
            if ($isFullCair) return 'Pencairan Selesai';
            if ($hasPencairanStarted) return 'Pencairan Bertahap';
            return 'Siap Pencairan'; 
        }
        if (!empty($pengajuanStatusPPK) && $pengajuanStatusPPK === 'Disetujui' && $pengajuanStatusWD2 === 'Menunggu') return 'Menunggu WD2';
        if (!empty($pengajuanStatusPPK) && $pengajuanStatusPPK === 'Menunggu' && $usulanStatus === 'Disetujui') return 'Menunggu PPK';
        if ($usulanStatus === 'Disetujui') return 'Disetujui Verifikator'; 
        if ($usulanStatus === 'Diajukan') return 'Menunggu Verif';
        if ($usulanStatus === 'Revisi') return 'Revisi';
        if ($usulanStatus === 'Ditolak') return 'Ditolak';
        return $usulanStatus ?: 'Draft'; 
    }
}

if (!function_exists('getStatusClass')) {
    function getStatusClass($status) {
        return match($status) {
            'Draft' => 'bg-amber-100 text-amber-700',
            'Diajukan' => 'bg-blue-100 text-blue-700',
            'Revisi' => 'bg-rose-100 text-rose-700',
            'Ditolak' => 'bg-slate-800 text-white',
            'Disetujui', 'Disetujui Verifikator', 'Siap Pencairan' => 'bg-emerald-100 text-emerald-700', 
            'Menunggu PPK' => 'bg-violet-100 text-violet-700',
            'Menunggu WD2' => 'bg-indigo-100 text-indigo-700',
            'Pencairan Bertahap', 'Pencairan Selesai' => 'bg-cyan-100 text-cyan-700',
            'LPJ Revisi' => 'bg-rose-100 text-rose-700',
            'LPJ Wajib Upload', 'LPJ Diajukan' => 'bg-amber-100 text-amber-700',
            'Selesai' => 'bg-slate-800 text-white',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}
?>

<div class="m-5">
    <div class="p-5 max-w-full mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard Pengusul</h1>
            <p class="text-slate-500 mt-1">Selamat datang, 
                <span class="font-bold text-slate-800">
                    <?php echo htmlspecialchars($_SESSION['role'] ?? 'Pengusul'); ?>
                </span>. Berikut adalah ringkasan progres kegiatan Anda.</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center">
                <span class="material-icons text-4xl text-blue-600">edit_note</span>
                <p class="text-xs font-bold text-slate-500 uppercase mt-2">Diajukan</p>
                <h3 class="text-3xl font-extrabold text-blue-600 mt-1"><?php echo $stats['diajukan'] ?? 0; ?></h3>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center">
                <span class="material-icons text-4xl text-amber-600">pending_actions</span>
                <p class="text-xs font-bold text-slate-500 uppercase mt-2">Revisi</p>
                <h3 class="text-3xl font-extrabold text-amber-600 mt-1"><?php echo $stats['revisi'] ?? 0; ?></h3>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center">
                <span class="material-icons text-4xl text-rose-500">highlight_off</span>
                <p class="text-xs font-bold text-slate-500 uppercase mt-2">Ditolak</p>
                <h3 class="text-3xl font-extrabold text-rose-600 mt-1"><?php echo $stats['ditolak'] ?? 0; ?></h3>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-lg border border-slate-200 text-center">
                <span class="material-icons text-4xl text-emerald-600">check_circle</span>
                <p class="text-xs font-bold text-slate-500 uppercase mt-2">Selesai</p>
                <h3 class="text-3xl font-extrabold text-emerald-600 mt-1"><?php echo $stats['selesai'] ?? 0; ?></h3>
            </div>
        </div>

        <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="text-xl font-bold text-slate-800">Daftar Progres Usulan</h2>
            <a href="/usulan/create" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition-colors text-sm">
                <span class="material-icons text-sm mr-2">add</span> Buat Usulan Baru
            </a>
        </div>

        <div class="mb-6 bg-slate-50 p-4 rounded-lg border border-slate-200">
            <form method="GET" action="" class="flex flex-col md:flex-row gap-4 w-full items-center">
                <div class="relative w-full md:w-[45%]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fas fa-search"></i></span>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" placeholder="Cari Nama Kegiatan..." class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm shadow-sm">
                </div>
                <div class="relative w-full md:w-[40%]">
                    <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm appearance-none bg-white shadow-sm cursor-pointer">
                        <option value="">Semua Status</option>
                        <?php 
                        // MODIFIKASI: Filter Semua Status KECUALI Draft
                        $statuses = [
                            'Diajukan', 'Revisi', 'Ditolak', 'Disetujui', 
                            'Menunggu PPK', 'Menunggu WD2', 
                            'Siap Pencairan', 'Pencairan Bertahap', 'Pencairan Selesai',
                            'LPJ Wajib Upload', 'LPJ Diajukan', 'LPJ Revisi', 'Selesai'
                        ];
                        foreach ($statuses as $s) {
                            $selected = (($_GET['status'] ?? '') === $s) ? 'selected' : '';
                            echo "<option value=\"$s\" $selected>$s</option>";
                        }
                        ?>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500"><i class="fas fa-filter text-xs"></i></span>
                </div>
                <div class="w-full md:flex-1 flex gap-2 justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center justify-center flex-1 md:flex-none">Filter</button>
                    <?php if(!empty($_GET['q']) || !empty($_GET['status'])): ?>
                        <a href="/dashboard" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center justify-center flex-1 md:flex-none text-center">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <?php if (empty($usulan)): ?>
                <div class="p-12 text-center">
                    <span class="material-icons text-slate-300 text-5xl mb-2">toc</span>
                    <h3 class="text-lg font-bold text-slate-700">Tidak ada data</h3>
                    <p class="text-slate-500 text-sm">Tidak ada usulan yang sesuai dengan filter Anda.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-bold text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-4 min-w-[250px]">Nama Kegiatan</th>
                                <th class="px-4 py-4 min-w-[150px]">Penanggung Jawab</th>
                                <th class="px-4 py-4 min-w-[150px]">Pelaksana</th>
                                <th class="px-4 py-4 w-28 text-right min-w-[150px]">Nominal RAB</th>
                                <th class="px-4 py-4 text-center w-28 border-l border-slate-200 bg-slate-100/50">Status Akhir</th>
                                <th class="px-4 py-4 text-right w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php 
                            date_default_timezone_set('Asia/Jakarta'); 
                            foreach ($usulan as $row): 
                                $statusAkhir = deriveFinalStatus($row);
                                $isLate = false;
                                if (!empty($row['tgl_batas_lpj']) && $row['lpj_status'] !== 'Disetujui' && $row['status_terkini'] !== 'Ditolak') {
                                    try {
                                        $deadline = new DateTime($row['tgl_batas_lpj']);
                                        $today = new DateTime();
                                        if ($today > $deadline) $isLate = true;
                                    } catch (Exception $e) {}
                                }
                                $isSelesai = $statusAkhir === 'Selesai';
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors group <?php echo $isLate ? 'bg-rose-50/40' : ''; ?>">
                                <td class="px-4 py-4">
                                    <div class="font-bold text-slate-800 group-hover:text-blue-700 transition-colors mb-1"><?php echo htmlspecialchars($row['nama_kegiatan']); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo date('d M Y', strtotime($row['tanggal_mulai'])); ?> - <?php echo date('d M Y', strtotime($row['tanggal_selesai'])); ?></div>
                                </td>
                                <td class="px-4 py-4 text-slate-700 text-sm"><?php echo htmlspecialchars($row['penanggung_jawab'] ?: '-'); ?></td>
                                <td class="px-4 py-4 text-slate-600 text-xs"><?php echo htmlspecialchars(substr($row['pelaksana_kegiatan'], 0, 30) . (strlen($row['pelaksana_kegiatan'] ?? '') > 30 ? '...' : '') ?: '-'); ?></td>
                                <td class="px-4 py-4 text-right font-bold text-emerald-600 whitespace-nowrap">Rp <?php echo number_format($row['nominal_pencairan'], 0, ',', '.'); ?></td>
                                <td class="px-4 py-4 text-center border-l border-slate-100 bg-slate-50/30">
                                    <span class="inline-flex px-2 py-1 rounded text-[10px] font-bold <?= getStatusClass($statusAkhir) ?> border border-slate-200 whitespace-nowrap"><?php echo $statusAkhir; ?></span>
                                    <?php if($isLate): ?><div class="text-rose-600 text-[9px] font-bold mt-1">⚠ Terlambat LPJ</div><?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end items-center gap-1">
                                        <?php if ($isSelesai): ?>
                                            <a href="/pdf/berita_acara?id=<?php echo $row['id']; ?>" target="_blank" class="inline-flex items-center px-3 py-1 bg-slate-800 text-white font-bold rounded-lg hover:bg-slate-700 transition-all text-xs" title="Unduh Berita Acara (Final)"><span class="material-icons text-xs mr-1">download</span> PDF Final</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($pager['total_pages'] > 1): ?>
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                    <div class="text-xs text-gray-500">Halaman <strong><?php echo $pager['current']; ?></strong> dari <strong><?php echo $pager['total_pages']; ?></strong></div>
                    <div class="flex space-x-1">
                        <?php if ($pager['current'] > 1): ?>
                            <a href="?page=<?php echo $pager['current'] - 1; ?>&q=<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>&status=<?php echo htmlspecialchars($_GET['status'] ?? ''); ?>" class="px-3 py-1 bg-white border border-gray-300 rounded text-xs font-medium hover:bg-gray-100">Prev</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $pager['total_pages']; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&q=<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>&status=<?php echo htmlspecialchars($_GET['status'] ?? ''); ?>" class="px-3 py-1 border rounded text-xs font-medium <?php echo ($i == $pager['current']) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        <?php if ($pager['current'] < $pager['total_pages']): ?>
                            <a href="?page=<?php echo $pager['current'] + 1; ?>&q=<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>&status=<?php echo htmlspecialchars($_GET['status'] ?? ''); ?>" class="px-3 py-1 bg-white border border-gray-300 rounded text-xs font-medium hover:bg-gray-100">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>