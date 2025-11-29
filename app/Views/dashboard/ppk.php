<?php include __DIR__.'/../partials/sidebar.php'; ?>

<div class="p-8 max-w-7xl mx-auto">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard PPK</h1>
            <p class="text-slate-500 mt-1">Pejabat Pembuat Komitmen: Validasi akhir sebelum pencairan dana.</p>
        </div>
        
        <div class="bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm text-sm flex items-center">
            <span class="material-icons text-violet-600 mr-2">assignment_turned_in</span>
            <span class="font-bold text-slate-700 mr-2">Menunggu Validasi:</span>
            <span class="bg-violet-100 text-violet-700 px-2 py-0.5 rounded font-mono font-bold">
                <?php echo count($usulan ?? []); ?>
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <a href="/approval" class="col-span-2 group flex items-center p-8 bg-white rounded-2xl shadow-lg border border-violet-100 hover:border-violet-500 transition-all cursor-pointer relative overflow-hidden">
            <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity transform group-hover:scale-110">
                <span class="material-icons text-9xl text-violet-600">verified_user</span>
            </div>
            <div class="mr-6">
                <div class="w-16 h-16 rounded-2xl bg-violet-600 text-white flex items-center justify-center shadow-lg shadow-violet-600/30">
                    <span class="material-icons text-3xl">signature</span>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-2xl font-bold text-slate-800 mb-1 group-hover:text-violet-700">Validasi Komitmen</h3>
                <p class="text-slate-500 mb-4 text-sm leading-relaxed max-w-md">
                    Anda memiliki <strong class="text-violet-600"><?php echo count($usulan ?? []); ?> usulan</strong> yang menunggu tanda tangan persetujuan untuk penerbitan perintah bayar.
                </p>
                <span class="inline-flex items-center font-bold text-violet-600 group-hover:translate-x-2 transition-transform text-sm">
                    Buka Meja Kerja <span class="material-icons ml-2 text-sm">arrow_forward</span>
                </span>
            </div>
        </a>

        <div class="grid grid-rows-2 gap-6">
            <a href="/monitoring" class="p-6 bg-white rounded-xl shadow-sm border border-slate-200 hover:shadow-md flex items-center justify-between group transition-all">
                <div>
                    <h4 class="font-bold text-slate-700 text-lg group-hover:text-blue-600">Monitoring Global</h4>
                    <p class="text-slate-400 text-sm mt-1">Lacak semua progres.</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <span class="material-icons">insights</span>
                </div>
            </a>
            
            <div class="p-6 bg-gradient-to-br from-violet-50 to-white rounded-xl border border-violet-100 flex flex-col justify-center">
                <span class="text-violet-800 font-bold text-xs uppercase tracking-wider mb-2">Status Anggaran</span>
                <div class="flex items-center text-slate-600 text-sm">
                    <span class="material-icons text-violet-400 mr-2 text-base">info</span>
                    <span>Pastikan pagu tersedia sebelum menyetujui.</span>
                </div>
            </div>
        </div>
    </div>

    </div>
<?php include __DIR__.'/../partials/footer.php'; ?>