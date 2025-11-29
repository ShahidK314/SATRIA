<?php include __DIR__.'/../partials/sidebar.php'; ?>

<div class="p-8 max-w-7xl mx-auto">
    <div class="relative bg-slate-900 rounded-2xl p-10 text-white shadow-2xl mb-10 overflow-hidden">
        <div class="absolute right-0 top-0 h-full w-1/2 bg-gradient-to-l from-blue-900 to-transparent opacity-50"></div>
        <div class="absolute -bottom-10 -left-10 w-48 h-48 bg-white opacity-5 rounded-full blur-3xl"></div>
        
        <div class="relative z-10">
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/20 text-blue-200 text-xs font-bold mb-4 backdrop-blur-sm">
                <span class="material-icons text-sm mr-2">analytics</span> Executive Room
            </div>
            <h1 class="text-4xl font-extrabold tracking-tight mb-2">Selamat Datang, Direktur.</h1>
            <p class="text-slate-400 text-lg max-w-2xl">Sistem SATRIA siap menyajikan data kinerja institusi secara real-time dan akurat.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <a href="/laporan" class="p-8 bg-white rounded-2xl shadow-lg border border-slate-200 hover:border-blue-500 hover:shadow-xl transition-all group flex items-center relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <span class="material-icons text-9xl text-blue-600">pie_chart</span>
            </div>
            <div class="w-20 h-20 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mr-6 group-hover:bg-blue-600 group-hover:text-white transition-colors z-10">
                <span class="material-icons text-4xl">monitoring</span>
            </div>
            <div class="z-10">
                <h3 class="text-2xl font-bold text-slate-800 group-hover:text-blue-700">Laporan Kinerja</h3>
                <p class="text-slate-500 mt-1 text-sm mb-4">Analisis statistik serapan anggaran dan progres kegiatan.</p>
                <span class="text-blue-600 font-bold text-sm flex items-center">Lihat Laporan <span class="material-icons ml-1 text-sm">arrow_forward</span></span>
            </div>
        </a>

        <a href="/monitoring" class="p-8 bg-white rounded-2xl shadow-lg border border-slate-200 hover:border-emerald-500 hover:shadow-xl transition-all group flex items-center relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <span class="material-icons text-9xl text-emerald-600">troubleshoot</span>
            </div>
            <div class="w-20 h-20 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mr-6 group-hover:bg-emerald-600 group-hover:text-white transition-colors z-10">
                <span class="material-icons text-4xl">toc</span>
            </div>
            <div class="z-10">
                <h3 class="text-2xl font-bold text-slate-800 group-hover:text-emerald-700">Monitoring Global</h3>
                <p class="text-slate-500 mt-1 text-sm mb-4">Pantau status detail setiap usulan yang sedang berjalan.</p>
                <span class="text-emerald-600 font-bold text-sm flex items-center">Buka Data <span class="material-icons ml-1 text-sm">arrow_forward</span></span>
            </div>
        </a>
    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>