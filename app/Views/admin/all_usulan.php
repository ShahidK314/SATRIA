<?php 
// app/Views/admin/all_usulan.php (NEW: Full Usulan View for Admin/Direktur)
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}

// LOGIKA DERIVASI STATUS (Copy dari Dashboard/Admin atau Monitoring)
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
        
        if ($lpjStatus === 'Disetujui') return 'Selesai';
        
        if ($hasPencairanStarted || $isReadyForPencairan) { 
            if ($lpjStatus === 'Diajukan') return 'LPJ Diajukan';
            if ($lpjStatus === 'Revisi') return 'LPJ Revisi';
            if ($lpjStatus === 'Belum Upload' && $totalCair > 0) return 'LPJ Wajib Upload';
        }
        
        if ($isReadyForPencairan) {
            $isFullCair = ($nominalRAB > 0) && (abs($totalCair - $nominalRAB) < 1);
            if ($isFullCair) return 'Pencairan Selesai';
            if ($hasPencairanStarted) return 'Pencairan Bertahap';
            return 'Siap Pencairan'; 
        }

        if (!empty($pengajuanStatusPPK) && $pengajuanStatusPPK === 'Disetujui' && $pengajuanStatusWD2 === 'Menunggu') {
            return 'Menunggu WD2';
        }
        if (!empty($pengajuanStatusPPK) && $pengajuanStatusPPK === 'Menunggu') {
            return 'Menunggu PPK';
        }
        
        if ($usulanStatus === 'Disetujui') return 'Disetujui Verifikator'; 
        if ($usulanStatus === 'Diajukan') return 'Menunggu Verif';
        if ($usulanStatus === 'Revisi') return 'Revisi';
        if ($usulanStatus === 'Ditolak') return 'Ditolak';
        
        return $usulanStatus ?: 'Draft'; 
    }
}

// Helper Class Warna Status
if (!function_exists('getStatusClass')) {
    function getStatusClass($status) {
        return match($status) {
            'Draft' => 'bg-amber-100 text-amber-700',
            'Diajukan', 'Menunggu Verif' => 'bg-blue-100 text-blue-700',
            'Revisi', 'LPJ Revisi' => 'bg-rose-100 text-rose-700',
            'Ditolak' => 'bg-slate-800 text-white',
            
            'Disetujui', 'Disetujui Verifikator' => 'bg-emerald-100 text-emerald-700',
            'Menunggu PPK' => 'bg-violet-100 text-violet-700',
            'Menunggu WD2' => 'bg-indigo-100 text-indigo-700',

            'Siap Pencairan', 'Pencairan Bertahap', 'Pencairan Selesai' => 'bg-cyan-100 text-cyan-700',
            'LPJ Diajukan', 'LPJ Wajib Upload' => 'bg-amber-100 text-amber-700',
            
            'Selesai' => 'bg-slate-800 text-white',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}
?>

<div class="m-5">
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Semua Usulan Kegiatan</h1>
            <p class="text-slate-500 mt-1">Daftar lengkap seluruh usulan dan status terkini.</p>
        </div>
        
        <form method="GET" action="/admin/usulan" class="flex gap-2">
            <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" 
                   class="px-4 py-2 border border-slate-300 rounded-lg text-sm w-64" 
                   placeholder="Cari Kegiatan / Pengusul...">
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white font-bold rounded-lg hover:bg-slate-900 text-sm">
                Cari
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <?php if (empty($usulan)): ?>
            <div class="p-12 text-center">
                <span class="material-icons text-slate-300 text-6xl mb-4">folder_off</span>
                <h3 class="text-lg font-bold text-slate-700">Data Tidak Ditemukan</h3>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-bold text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Kegiatan</th>
                            <th class="px-6 py-4">Pengusul</th>
                            <th class="px-6 py-4 text-right">Anggaran</th>
                            <th class="px-6 py-4">Tanggal Buat</th>
                            <th class="px-6 py-4 text-center">Status Akhir</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($usulan as $row): 
                            $finalStatus = deriveFinalStatus($row);
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-800">
                                <?= htmlspecialchars($row['nama_kegiatan']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-700"><?= htmlspecialchars($row['username']); ?></div>
                                <div class="text-xs text-slate-500"><?= htmlspecialchars($row['nama_jurusan']); ?></div>
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">
                                <?= formatRupiah($row['nominal_pencairan']); ?>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                <?= date('d M Y', strtotime($row['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide <?= getStatusClass($finalStatus); ?>">
                                    <?= $finalStatus; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($finalStatus === 'Selesai'): ?>
                                    <a href="/pdf/berita_acara?id=<?= $row['id']; ?>" target="_blank" 
                                       class="inline-flex items-center px-3 py-1.5 bg-slate-800 text-white font-bold rounded-lg hover:bg-slate-700 transition-all text-xs" 
                                       title="Unduh Berita Acara">
                                        <span class="material-icons text-xs mr-1">download</span> Unduh PDF
                                    </a>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400 italic">Belum Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-between items-center">
                <div class="text-xs text-slate-500">Halaman <?= $page; ?> dari <?= $totalPages; ?></div>
                <div class="flex gap-1">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1; ?>&q=<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="px-3 py-1 bg-white border border-slate-300 rounded text-xs hover:bg-slate-100">Prev</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1; ?>&q=<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="px-3 py-1 bg-white border border-slate-300 rounded text-xs hover:bg-slate-100">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>