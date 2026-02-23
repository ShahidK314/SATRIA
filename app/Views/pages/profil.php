<?php include __DIR__.'/../partials/sidebar.php'; ?>

<div class="m-5">

    <!-- Breadcrumb & Title -->
    <div class="flex justify-between items-end mb-10">
        <div>
            <div class="flex items-center text-xs text-slate-500 mb-2">
                <span class="material-icons text-[14px] mr-1">home</span> Home
                <span class="mx-2">/</span>
                <span>Pengaturan</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Profil Pengguna</h1>
        </div>

        <button onclick="document.getElementById('modalEditProfile').classList.remove('hidden')" 
                class="hidden md:inline-flex items-center px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-bold rounded-lg hover:bg-slate-50 hover:text-blue-700 hover:border-blue-300 transition-all shadow-sm">
            <span class="material-icons text-sm mr-2">edit</span> Edit Profil
        </button>
    </div>


    <!-- Card Container -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden relative">

        <!-- Header Banner -->
        <div class="h-48 bg-gradient-to-r from-slate-900 to-blue-900 relative">
            <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl -mr-20 -mt-20"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-blue-400 opacity-10 rounded-full blur-2xl -ml-12 -mb-12"></div>
        </div>

        <!-- Profile Section -->
        <div class="px-8 relative">
            <div class="flex flex-col md:flex-row items-start md:items-end -mt-10 mb-10 pl-2">

                <!-- Avatar -->
                <div class="relative z-10">
                    <div class="w-32 h-32 rounded-full bg-white p-1.5 shadow-lg ring-1 ring-slate-100">
                        <div class="w-full h-full rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-4xl font-bold border border-slate-200">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                    </div>
                    <div class="absolute bottom-2 right-2 w-6 h-6 bg-emerald-500 border-4 border-white rounded-full"></div>
                </div>

                <!-- Username & Role -->
                <div class="mt-10 md:mt-16 md:ml-10">
                    <h2 class="text-3xl font-bold text-slate-900 leading-tight">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </h2>

                    <div class="flex flex-wrap gap-2 mt-3">

                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-600 text-white shadow-sm">
                            <span class="material-icons text-[12px] mr-1.5">badge</span>
                            <?php echo htmlspecialchars($user['role']); ?>
                        </span>

                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            <span class="material-icons text-[12px] mr-1.5 text-emerald-500">verified</span>
                            Akun Terverifikasi
                        </span>

                    </div>
                </div>

            </div>
        </div>

        <hr class="border-slate-100">

        <!-- Account Info -->
        <div class="p-8 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-6 border-l-4 border-blue-600 pl-3">
                Informasi Akun
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Email -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-start gap-4 hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                        <span class="material-icons">email</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase">Alamat Email</label>
                        <div class="text-slate-800 font-semibold mt-1 text-sm md:text-base">
                            <?php echo htmlspecialchars($user['email']); ?>
                        </div>
                    </div>
                </div>

                <!-- Jurusan -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-start gap-4 hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 flex-shrink-0">
                        <span class="material-icons">apartment</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase">Unit / Jurusan</label>
                        <div class="text-slate-800 font-semibold mt-1 text-sm md:text-base">
                            <?php echo $user['nama_jurusan'] ? htmlspecialchars($user['nama_jurusan']) : 'Pusat / General'; ?>
                        </div>
                    </div>
                </div>

                <!-- Security -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-start gap-4 hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 flex-shrink-0">
                        <span class="material-icons">gpp_good</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase">Status Keamanan</label>
                        <div class="text-emerald-700 font-bold mt-1 text-sm">
                            Aktif & Aman
                        </div>
                    </div>
                </div>

                <!-- Registered -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-start gap-4 hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 flex-shrink-0">
                        <span class="material-icons">history</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase">Terdaftar Sejak</label>
                        <div class="text-slate-800 font-semibold mt-1 text-sm">
                            <?php echo date('d F Y'); ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer Info + Button -->
        <div class="px-8 py-6 bg-slate-50 border-t border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-xs text-slate-500 text-center md:text-left">
                <p>Terakhir login: <span class="font-mono text-slate-700"><?php echo date('d-m-Y H:i'); ?> WIB</span></p>
                <p>IP Address: <span class="font-mono text-slate-700"><?php echo $_SERVER['REMOTE_ADDR']; ?></span></p>
            </div>

            <button onclick="document.getElementById('modalPassword').classList.remove('hidden')" 
                    class="inline-flex items-center px-6 py-3 bg-slate-800 text-white text-sm font-bold rounded-lg shadow-md hover:bg-slate-900 hover:shadow-lg transition-all transform hover:-translate-y-0.5 w-full md:w-auto justify-center">
                <span class="material-icons text-sm mr-2">lock_reset</span> Ubah Password
            </button>
        </div>

    </div>
</div>
