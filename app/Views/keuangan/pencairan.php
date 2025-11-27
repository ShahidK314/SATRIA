<?php include __DIR__.'/../partials/sidebar.php'; ?>

<div class="p-8 max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Kasir & Pencairan Dana</h1>
        <p class="text-slate-500 mt-1">Proses pencairan dana untuk usulan yang telah disetujui PPK.</p>
    </div>

    <?php if (empty($usulan)): ?>
        <div class="bg-white rounded-xl p-12 text-center shadow-sm border border-slate-200">
            <span class="material-icons text-slate-300 text-6xl mb-4">account_balance_wallet</span>
            <h3 class="text-lg font-bold text-slate-700">Tidak ada antrian pencairan</h3>
            <p class="text-slate-500">Semua usulan yang disetujui sudah dicairkan.</p>
        </div>
    <?php else: ?>
        <div class="grid gap-6">
            <?php foreach ($usulan as $row): ?>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row justify-between items-center gap-6 hover:shadow-md transition-all">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">Siap Cair</span>
                        <span class="text-xs text-slate-400 font-mono">#<?php echo $row['id']; ?></span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-1"><?php echo htmlspecialchars($row['nama_kegiatan']); ?></h3>
                    <p class="text-slate-500 text-sm mb-3">Pengusul: <span class="font-medium text-slate-700"><?php echo htmlspecialchars($row['username']); ?></span></p>
                    
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 inline-block">
                        <p class="text-xs text-slate-500 uppercase font-bold">Rencana Anggaran (RAB)</p>
                        <p class="text-lg font-mono font-bold text-slate-700">Rp <?php echo number_format($row['nominal_pencairan'], 0, ',', '.'); ?></p>
                    </div>
                </div>

                <form action="/pencairan/proses?id=<?php echo $row['id']; ?>" method="POST" class="w-full md:w-80 bg-slate-50 p-5 rounded-xl border border-slate-200" onsubmit="return confirm('Pastikan uang fisik sudah disiapkan. Lanjutkan pencairan?');">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nominal Dicairkan (Rp)</label>
                        <input type="number" name="nominal_cair" value="<?php echo $row['nominal_pencairan']; ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none font-mono text-right" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Batas Waktu LPJ</label>
                        <input type="date" name="tgl_batas_lpj" value="<?php echo date('Y-m-d', strtotime('+14 weekdays')); ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none" required>
                        <p class="text-[10px] text-slate-400 mt-1">*Default: 14 Hari Kerja</p>
                    </div>

                    <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-lg shadow-emerald-600/20 transition-all flex items-center justify-center">
                        <span class="material-icons text-sm mr-2">payments</span> Serahkan Dana
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>