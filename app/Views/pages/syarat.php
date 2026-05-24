<?php include __DIR__.'/../partials/sidebar.php'; ?>
<div class="m-4 md:m-5 lg:m-8 max-w-5xl mx-auto">
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
        
        <div class="bg-slate-900 px-6 py-8 md:px-8 md:py-12 text-white text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            <span class="material-icons text-5xl md:text-6xl text-slate-700 mb-3 md:mb-4 block mx-auto">gavel</span>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight relative z-10">Syarat & Ketentuan Penggunaan</h1>
            <p class="text-slate-400 mt-2 text-xs md:text-sm relative z-10 font-medium">SATRIA - Sistem Administrasi TOR & LPJ</p>
        </div>

        <div class="p-5 sm:p-8 md:p-12 prose prose-sm md:prose-base prose-slate max-w-none">
            <div class="p-4 bg-amber-50 border-l-4 border-amber-400 rounded-r-lg mb-6 md:mb-8 text-xs md:text-sm text-amber-800 leading-relaxed shadow-sm">
                <strong class="flex items-center mb-1"><span class="material-icons text-sm mr-1">warning</span> Penting:</strong> 
                Dengan mengakses dan menggunakan sistem ini, Anda dianggap telah membaca, memahami, dan menyetujui seluruh ketentuan di bawah ini.
            </div>

            <h3 class="text-base md:text-lg font-bold text-slate-900 mb-4 flex items-center">
                <span class="w-6 h-6 md:w-7 md:h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs md:text-sm mr-3 font-bold shadow-inner">1</span>
                Akun & Keamanan
            </h3>
            <ul class="list-disc pl-10 md:pl-12 mb-8 space-y-2 text-slate-600 text-xs md:text-sm leading-relaxed marker:text-slate-300">
                <li>Pengguna wajib menjaga kerahasiaan <strong>Username</strong> dan <strong>Password</strong>. Kelalaian yang menyebabkan penyalahgunaan akun adalah tanggung jawab pengguna sepenuhnya.</li>
                <li>Dilarang keras meminjamkan akun kepada pihak lain untuk tujuan manipulasi data atau persetujuan (approval) tanpa wewenang yang sah.</li>
                <li>Sistem mencatat alamat IP, waktu akses, dan aktivitas (Audit Log) untuk keperluan keamanan dan investigasi digital oleh Unit IT.</li>
            </ul>

            <h3 class="text-base md:text-lg font-bold text-slate-900 mb-4 flex items-center">
                <span class="w-6 h-6 md:w-7 md:h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs md:text-sm mr-3 font-bold shadow-inner">2</span>
                Integritas Data & Dokumen
            </h3>
            <ul class="list-disc pl-10 md:pl-12 mb-8 space-y-2 text-slate-600 text-xs md:text-sm leading-relaxed marker:text-slate-300">
                <li>Segala bentuk dokumen yang diunggah (TOR, RAB, Surat Pengantar, Bukti Transaksi) wajib merupakan dokumen digital yang <strong>asli dan sah</strong> secara hukum.</li>
                <li>Manipulasi nilai anggaran (mark-up), pemalsuan dokumen pertanggungjawaban, atau fabrikasi tanda tangan elektronik merupakan pelanggaran berat dan akan dikenakan sanksi akademik/administratif sesuai peraturan ketat Politeknik Negeri Jakarta.</li>
            </ul>

            <hr class="border-slate-100 my-6 md:my-8">

            <div class="text-center text-[10px] md:text-xs text-slate-400 bg-slate-50 p-4 rounded-lg border border-slate-100">
                Terakhir diperbarui: <span class="font-bold text-slate-500"><?php echo date('d F Y'); ?></span><br>
                &copy; 2025 Unit IT Politeknik Negeri Jakarta
            </div>
        </div>
    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>