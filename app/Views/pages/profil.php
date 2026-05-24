<?php include __DIR__.'/../partials/sidebar.php'; ?>

<div class="m-4 md:m-8">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 md:mb-10 gap-4">
        <div>
            <div class="flex items-center text-[10px] md:text-xs text-slate-500 mb-2">
                <span class="material-icons text-[14px] mr-1">home</span> Home
                <span class="mx-2">/</span>
                <span>Pengaturan</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Profil Pengguna</h1>
        </div>

        <button onclick="document.getElementById('modalEditProfile').classList.remove('hidden')" 
                class="w-full md:w-auto inline-flex justify-center items-center px-4 py-2.5 md:py-2 bg-white border border-slate-300 text-slate-700 text-sm font-bold rounded-lg hover:bg-slate-50 hover:text-blue-700 hover:border-blue-300 transition-all shadow-sm">
            <span class="material-icons text-sm mr-2">edit</span> Edit Profil
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden relative">

        <div class="h-32 md:h-48 bg-gradient-to-r from-slate-900 to-blue-900 relative">
            <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            <div class="absolute top-0 right-0 w-40 h-40 md:w-64 md:h-64 bg-white opacity-5 rounded-full blur-3xl -mr-10 -mt-10 md:-mr-20 md:-mt-20"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 md:w-40 md:h-40 bg-blue-400 opacity-10 rounded-full blur-2xl -ml-8 -mb-8 md:-ml-12 md:-mb-12"></div>
        </div>

        <div class="px-4 md:px-8 relative">
            <div class="flex flex-col md:flex-row items-center md:items-end -mt-12 md:-mt-10 mb-8 md:mb-10 md:pl-2 text-center md:text-left">

                <div class="relative z-10 mx-auto md:mx-0">
                    <div class="w-24 h-24 md:w-32 md:h-32 rounded-full bg-white p-1.5 shadow-lg ring-1 ring-slate-100">
                        <div class="w-full h-full rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-3xl md:text-4xl font-bold border border-slate-200">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                    </div>
                    <div class="absolute bottom-1 right-1 md:bottom-2 md:right-2 w-5 h-5 md:w-6 md:h-6 bg-emerald-500 border-4 border-white rounded-full"></div>
                </div>

                <div class="mt-4 md:mt-16 md:ml-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-900 leading-tight">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </h2>

                    <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] md:text-xs font-bold bg-blue-600 text-white shadow-sm">
                            <span class="material-icons text-[12px] mr-1.5">badge</span>
                            <?php echo htmlspecialchars($user['role']); ?>
                        </span>

                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] md:text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            <span class="material-icons text-[12px] mr-1.5 text-emerald-500">verified</span>
                            Akun Terverifikasi
                        </span>
                    </div>
                </div>

            </div>
        </div>

        <hr class="border-slate-100">

        <div class="p-4 md:p-8 bg-slate-50/50">
            <h3 class="text-xs md:text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 md:mb-6 border-l-4 border-blue-600 pl-3">
                Informasi Akun
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">

                <div class="bg-white p-4 md:p-5 rounded-xl border border-slate-200 shadow-sm flex items-start gap-3 md:gap-4 hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                        <span class="material-icons text-[20px] md:text-[24px]">email</span>
                    </div>
                    <div class="overflow-hidden">
                        <label class="block text-[10px] md:text-xs font-bold text-slate-400 uppercase">Alamat Email</label>
                        <div class="text-slate-800 font-semibold mt-1 text-xs sm:text-sm md:text-base truncate">
                            <?php echo htmlspecialchars($user['email']); ?>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-4 md:p-5 rounded-xl border border-slate-200 shadow-sm flex items-start gap-3 md:gap-4 hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 flex-shrink-0">
                        <span class="material-icons text-[20px] md:text-[24px]">apartment</span>
                    </div>
                    <div class="overflow-hidden">
                        <label class="block text-[10px] md:text-xs font-bold text-slate-400 uppercase">Unit / Jurusan</label>
                        <div class="text-slate-800 font-semibold mt-1 text-xs sm:text-sm md:text-base truncate">
                            <?php echo $user['nama_jurusan'] ? htmlspecialchars($user['nama_jurusan']) : 'Pusat / General'; ?>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-4 md:p-5 rounded-xl border border-slate-200 shadow-sm flex items-start gap-3 md:gap-4 hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 flex-shrink-0">
                        <span class="material-icons text-[20px] md:text-[24px]">gpp_good</span>
                    </div>
                    <div>
                        <label class="block text-[10px] md:text-xs font-bold text-slate-400 uppercase">Status Keamanan</label>
                        <div class="text-emerald-700 font-bold mt-1 text-xs sm:text-sm md:text-base">
                            Aktif & Aman
                        </div>
                    </div>
                </div>

                <div class="bg-white p-4 md:p-5 rounded-xl border border-slate-200 shadow-sm flex items-start gap-3 md:gap-4 hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 flex-shrink-0">
                        <span class="material-icons text-[20px] md:text-[24px]">history</span>
                    </div>
                    <div>
                        <label class="block text-[10px] md:text-xs font-bold text-slate-400 uppercase">Terdaftar Sejak</label>
                        <div class="text-slate-800 font-semibold mt-1 text-xs sm:text-sm md:text-base">
                            <?php echo date('d F Y'); ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="px-4 md:px-8 py-4 md:py-6 bg-slate-50 border-t border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-[10px] md:text-xs text-slate-500 text-center md:text-left">
                <p>Terakhir login: <span class="font-mono text-slate-700 font-medium"><?php echo date('d-m-Y H:i'); ?> WIB</span></p>
                <p>IP Address: <span class="font-mono text-slate-700 font-medium"><?php echo $_SERVER['REMOTE_ADDR']; ?></span></p>
            </div>

            <button onclick="document.getElementById('modalPassword').classList.remove('hidden')" 
                    class="w-full md:w-auto inline-flex items-center justify-center px-6 py-3 bg-slate-800 text-white text-xs md:text-sm font-bold rounded-lg shadow-md hover:bg-slate-900 hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                <span class="material-icons text-[14px] md:text-sm mr-2">lock_reset</span> Ubah Password
            </button>
        </div>

    </div>
</div>