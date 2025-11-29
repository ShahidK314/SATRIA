<?php include __DIR__.'/../partials/sidebar.php'; ?>

<div class="p-8 max-w-7xl mx-auto">
    
    <div class="relative bg-gradient-to-r from-amber-500 to-orange-600 rounded-2xl p-10 text-white shadow-2xl mb-10 overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/diamond-upholstery.png')] opacity-10"></div>
        <div class="relative z-10 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight mb-2">Bendahara Pengeluaran</h1>
                <p class="text-amber-50 text-lg">Pusat eksekusi anggaran dan validasi pertanggungjawaban.</p>
            </div>
            <div class="hidden md:block text-right">
                <div class="text-3xl font-black"><?php echo date('F Y'); ?></div>
                <div class="text-sm text-amber-100 uppercase tracking-wider">Periode Anggaran</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <a href="/pencairan" class="group relative flex items-center p-8 bg-white rounded-2xl shadow-lg border border-amber-100 hover:border-amber-500 transition-all overflow-hidden">
            <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity transform group-hover:scale-110">
                <span class="material-icons text-9xl text-amber-600">payments</span>
            </div>
            
            <div class="mr-6 z-10">
                <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shadow-sm group-hover:bg-amber-600 group-hover:text-white transition-colors">
                    <span class="material-icons text-3xl">payments</span>
                </div>
            </div>
            
            <div class="flex-1 z-10">
                <div class="flex justify-between items-start">
                    <h3 class="text-xl font-bold text-slate-800 group-hover:text-amber-700">Pencairan Dana</h3>
                    <?php if(isset($countCair) && $countCair > 0): ?>
                        <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full animate-pulse">
                            <?php echo $countCair; ?> Menunggu
                        </span>
                    <?php endif; ?>
                </div>
                <p class="text-slate-500 text-sm mb-4 mt-1">Proses pembayaran untuk usulan yang telah disetujui PPK.</p>
                <span class="text-amber-600 font-bold text-sm flex items-center group-hover:translate-x-2 transition-transform">
                    Buka Loket <span class="material-icons text-sm ml-2">arrow_forward</span>
                </span>
            </div>
        </a>

        <a href="/lpj" class="group relative flex items-center p-8 bg-white rounded-2xl shadow-lg border border-emerald-100 hover:border-emerald-500 transition-all overflow-hidden">
            <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity transform group-hover:scale-110">
                <span class="material-icons text-9xl text-emerald-600">receipt_long</span>
            </div>

            <div class="mr-6 z-10">
                <div class="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-sm group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <span class="material-icons text-3xl">receipt_long</span>
                </div>
            </div>
            
            <div class="flex-1 z-10">
                <div class="flex justify-between items-start">
                    <h3 class="text-xl font-bold text-slate-800 group-hover:text-emerald-700">Verifikasi LPJ</h3>
                    <?php if(isset($countLPJ) && $countLPJ > 0): ?>
                        <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full animate-pulse">
                            <?php echo $countLPJ; ?> Baru
                        </span>
                    <?php endif; ?>
                </div>
                <p class="text-slate-500 text-sm mb-4 mt-1">Validasi dokumen pertanggungjawaban dari pengusul.</p>
                <span class="text-emerald-600 font-bold text-sm flex items-center group-hover:translate-x-2 transition-transform">
                    Mulai Periksa <span class="material-icons text-sm ml-2">arrow_forward</span>
                </span>
            </div>
        </a>
    </div>

    <div class="mt-8 bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-start gap-3">
        <span class="material-icons text-slate-400">info</span>
        <div class="text-sm text-slate-600">
            <strong class="text-slate-800">Info Sistem:</strong> Pastikan dokumen Hard Copy telah diterima sebelum menekan tombol "Selesai" pada verifikasi LPJ. Sistem akan menutup siklus kegiatan secara permanen.
        </div>
    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>