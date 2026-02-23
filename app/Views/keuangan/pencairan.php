<?php 
// app/Views/keuangan/pencairan.php
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format((float)$number, 0, ',', '.');
    }
}

// Menangkap parameter filter agar input tetap sinkron
$search = $_GET['q'] ?? '';
$filterStatus = $_GET['status'] ?? '';
?>

<div class="m-5">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Pencairan Dana</h1>
        <p class="text-slate-500 mt-1">Proses pencairan dana (bertahap/penuh) untuk usulan yang telah disetujui WD2.</p>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-8">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Cari Kegiatan</label>
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Nama kegiatan..." 
                       class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="w-48">
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Status Pembayaran</label>
                <select name="status" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="Belum Dicairkan" <?php echo $filterStatus == 'Belum Dicairkan' ? 'selected' : ''; ?>>Belum Dicairkan</option>
                    <option value="Bertahap" <?php echo $filterStatus == 'Bertahap' ? 'selected' : ''; ?>>Bertahap</option>
                    <option value="Lunas" <?php echo $filterStatus == 'Lunas' ? 'selected' : ''; ?>>Lunas</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-all text-sm flex items-center gap-2">
                    <span class="material-icons text-sm">filter_list</span> Filter
                </button>
                <a href="/pencairan" class="px-6 py-2 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 transition-all text-sm">Reset</a>
            </div>
        </form>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-amber-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-amber-600 font-bold uppercase tracking-wider">Total Dana Keluar</span>
                <span class="material-icons text-amber-500">payments</span>
            </div>
            <div class="text-2xl font-bold text-amber-600"><?php echo formatRupiah($totalDanaCair ?? 0); ?></div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-rose-500 font-bold uppercase tracking-wider">Belum Dicairkan</span>
                <span class="material-icons text-rose-400">history_toggle_off</span>
            </div>
            <div class="text-2xl font-bold text-rose-700"><?php echo number_format($totalKegiatanBelumCair ?? 0); ?></div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-blue-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-blue-600 font-bold uppercase tracking-wider">Bertahap</span>
                <span class="material-icons text-blue-500">hourglass_empty</span>
            </div>
            <div class="text-2xl font-bold text-blue-600"><?php echo number_format($totalKegiatanPending ?? 0); ?></div>
        </div>
        
        <div class="bg-white p-5 rounded-xl shadow-sm border border-emerald-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-emerald-600 font-bold uppercase tracking-wider">Lunas</span>
                <span class="material-icons text-emerald-500">check_circle</span>
            </div>
            <div class="text-2xl font-bold text-emerald-600"><?php echo number_format($totalKegiatanSelesaiCair ?? 0); ?></div>
        </div>
    </div>

    <?php if (empty($usulan)): ?>
        <div class="bg-white rounded-xl p-12 text-center shadow-sm border border-slate-200">
            <span class="material-icons text-slate-300 text-6xl mb-4">account_balance_wallet</span>
            <h3 class="text-lg font-bold text-slate-700">Tidak ada antrian pencairan</h3>
            <p class="text-slate-500">Data tidak ditemukan dengan kriteria filter tersebut.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Nama Kegiatan</th>
                            <th class="px-6 py-4 text-right">Total RAB</th>
                            <th class="px-6 py-4 text-right">Sisa Belum Cair</th>
                            <th class="px-6 py-4 w-32 text-center">Status</th>
                            <th class="px-6 py-4 w-40 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($usulan as $row): 
                            $nominalRAB = (float)$row['nominal_pencairan'];
                            $nominalSisa = (float)$row['sisa_dana']; 
                            
                            $isLunas = $row['status_pembayaran'] === 'Lunas';
                            $statusClass = match($row['status_pembayaran']) {
                                'Lunas' => 'bg-emerald-100 text-emerald-700',
                                'Bertahap' => 'bg-blue-100 text-blue-700',
                                default => 'bg-rose-100 text-rose-700'
                            };
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800 whitespace-normal min-w-[200px]"><?php echo htmlspecialchars($row['nama_kegiatan']); ?></p>
                                <p class="text-xs text-slate-500 mt-1">Pengusul: <?php echo htmlspecialchars($row['username']); ?></p>
                            </td>
                            <td class="px-6 py-4 text-right text-slate-600 font-bold font-mono">
                                <?php echo formatRupiah($nominalRAB); ?>
                            </td>
                            <td class="px-6 py-4 text-right text-rose-600 font-bold font-mono">
                                <?php echo formatRupiah($nominalSisa); ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded text-xs font-bold <?php echo $statusClass; ?>">
                                    <?php echo $row['status_pembayaran']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if (!$isLunas): ?>
                                    <button type="button" 
                                            onclick="openPencairanModal(<?php echo $row['pengajuan_id']; ?>, '<?php echo htmlspecialchars($row['nama_kegiatan'], ENT_QUOTES); ?>', <?php echo $nominalSisa; ?>)"
                                            class="px-4 py-2 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition-all text-xs flex items-center justify-end gap-2 ml-auto">
                                        <span class="material-icons text-xs">payments</span> Cairkan
                                    </button>
                                <?php else: ?>
                                    <span class="text-xs text-emerald-600 font-bold flex items-center justify-end gap-1">
                                        <span class="material-icons text-sm">check_circle</span> Selesai
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-slate-50 border-t border-slate-200 px-6 py-4 flex items-center justify-between">
    <div class="text-sm text-slate-500">
        Menampilkan halaman <span class="font-bold text-slate-700"><?= $pager['current']; ?></span> dari <span class="font-bold text-slate-700"><?= $pager['total_pages']; ?></span>
    </div>
    
    <?php if ($pager['total_pages'] > 1): ?>
        <div class="flex items-center gap-2">
            <?php if ($pager['current'] > 1): ?>
                <a href="?page=1&q=<?= urlencode($search); ?>&status=<?= urlencode($filterStatus); ?>" 
                class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                    <span class="material-icons text-base">first_page</span>
                </a>
                <a href="?page=<?= $pager['current'] - 1; ?>&q=<?= urlencode($search); ?>&status=<?= urlencode($filterStatus); ?>" 
                class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                    <span class="material-icons text-base">chevron_left</span>
                </a>
            <?php endif; ?>

            <?php 
            $start = max(1, $pager['current'] - 2);
            $end = min($pager['total_pages'], $start + 4);
            if ($end - $start < 4) $start = max(1, $end - 4);

            for ($i = $start; $i <= $end; $i++): 
            ?>
                <a href="?page=<?= $i; ?>&q=<?= urlencode($search); ?>&status=<?= urlencode($filterStatus); ?>" 
                class="w-10 h-10 flex items-center justify-center rounded-lg font-bold text-sm transition-all border 
                <?= $i == $pager['current'] 
                    ? 'bg-blue-600 border-blue-600 text-white shadow-lg' 
                    : 'bg-white border-slate-200 text-slate-500 hover:border-slate-400 hover:text-slate-800 shadow-sm'; ?>">
                    <?= $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($pager['current'] < $pager['total_pages']): ?>
                <a href="?page=<?= $pager['current'] + 1; ?>&q=<?= urlencode($search); ?>&status=<?= urlencode($filterStatus); ?>" 
                class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                    <span class="material-icons text-base">chevron_right</span>
                </a>
                <a href="?page=<?= $pager['total_pages']; ?>&q=<?= urlencode($search); ?>&status=<?= urlencode($filterStatus); ?>" 
                class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                    <span class="material-icons text-base">last_page</span>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<div id="modalPencairan" class="fixed inset-0 bg-slate-900/50 hidden flex items-center justify-center z-[100]" onclick="closeModal(event)">
    <div class="bg-white rounded-xl w-full max-w-md shadow-2xl overflow-hidden" onclick="event.stopPropagation()">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-xl font-bold text-slate-800">Cairkan Dana</h3>
            <p id="modalKegiatanName" class="text-sm text-slate-500 mt-1 whitespace-nowrap overflow-hidden text-ellipsis"></p>
        </div>
        
        <form action="/pencairan/proses" method="POST" class="p-6">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <input type="hidden" id="modal_pengajuan_id" name="pengajuan_id">
            
            <div class="mb-6">
                <label for="modal_nominal_dicairkan" class="block text-sm font-bold text-slate-700 mb-1">Nominal Pencairan (Rp)</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-slate-400 font-bold">Rp</span>
                    <input type="number" id="modal_nominal_dicairkan" name="nominal_dicairkan" 
                           min="1" step="1" required 
                           class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-lg font-bold text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors" 
                           placeholder="0">
                </div>
                <p id="sisaDanaInfo" class="text-xs text-rose-600 mt-2 font-semibold"></p>
                <p class="text-[10px] text-slate-400 mt-1">* Wajib > 0 dan tidak boleh melebihi sisa dana.</p>
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalPencairan').classList.add('hidden')" 
                        class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 text-sm transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 text-sm shadow-lg shadow-emerald-200 transition">
                    Konfirmasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPencairanModal(pengajuanId, namaKegiatan, nominalSisa) {
        document.getElementById('modal_pengajuan_id').value = pengajuanId;
        document.getElementById('modalKegiatanName').textContent = namaKegiatan;
        document.getElementById('sisaDanaInfo').textContent = `Maksimal: Rp ${new Intl.NumberFormat('id-ID').format(nominalSisa)}`;
        
        const input = document.getElementById('modal_nominal_dicairkan');
        input.value = nominalSisa;
        input.max = nominalSisa;
        
        document.getElementById('modalPencairan').classList.remove('hidden');
    }

    function closeModal(event) {
        if (event.target.id === 'modalPencairan') {
            document.getElementById('modalPencairan').classList.add('hidden');
        }
    }
</script>
<?php include __DIR__.'/../partials/footer.php'; ?>