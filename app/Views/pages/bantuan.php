<?php include __DIR__.'/../partials/sidebar.php'; ?>
<div class="m-4 md:m-8">
    <div class="text-center mb-8 md:mb-12 mt-4">
        <div class="inline-flex items-center justify-center w-14 h-14 md:w-16 md:h-16 rounded-full bg-blue-100 text-blue-600 mb-4 shadow-inner border border-blue-200">
            <span class="material-icons text-2xl md:text-3xl">help_outline</span>
        </div>
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Pusat Bantuan</h1>
        <p class="text-slate-500 mt-2 max-w-lg mx-auto text-sm md:text-base px-4">Unduh template dokumen resmi dan panduan penggunaan sistem SATRIA.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6 justify-center place-items-center max-w-3xl mx-auto">
        <a href="#" class="group bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-200 hover:border-blue-400 hover:shadow-lg transition-all text-center w-full max-w-sm">
            <div class="w-12 h-12 md:w-14 md:h-14 mx-auto bg-slate-50 rounded-xl flex items-center justify-center mb-4 text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-colors border border-slate-100">
                <span class="material-icons text-lg md:text-xl">description</span>
            </div>
            <h3 class="text-base md:text-lg font-bold text-slate-800 mb-1 group-hover:text-blue-700 transition-colors">Format Surat Pengantar</h3>
            <p class="text-[10px] md:text-xs text-slate-400 mb-5 uppercase tracking-wider font-bold">PDF • Updated 2025</p>
            <span class="inline-flex px-4 py-2 bg-blue-50 text-blue-600 text-xs font-bold rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors w-full justify-center">Download File</span>
        </a>

        <a href="#" class="group bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-200 hover:border-amber-400 hover:shadow-lg transition-all text-center w-full max-w-sm">
            <div class="w-12 h-12 md:w-14 md:h-14 mx-auto bg-slate-50 rounded-xl flex items-center justify-center mb-4 text-slate-400 group-hover:bg-amber-600 group-hover:text-white transition-colors border border-slate-100">
                <span class="material-icons text-lg md:text-xl">menu_book</span>
            </div>
            <h3 class="text-base md:text-lg font-bold text-slate-800 mb-1 group-hover:text-amber-700 transition-colors">User Manual</h3>
            <p class="text-[10px] md:text-xs text-slate-400 mb-5 uppercase tracking-wider font-bold">PDF • Panduan Lengkap</p>
            <span class="inline-flex px-4 py-2 bg-amber-50 text-amber-700 text-xs font-bold rounded-lg group-hover:bg-amber-600 group-hover:text-white transition-colors w-full justify-center">Download File</span>
        </a>
    </div>

    <div class="mt-12 md:mt-16 border-t border-slate-200 pt-8 md:pt-10 max-w-4xl mx-auto">
        <h3 class="text-lg md:text-xl font-bold text-slate-900 mb-4 md:mb-6 text-center sm:text-left">Pertanyaan Umum (FAQ)</h3>
        <div class="space-y-3 md:space-y-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 md:p-5 shadow-sm">
                <h4 class="font-bold text-slate-800 text-sm md:text-base mb-2 flex items-center"><span class="material-icons text-blue-500 mr-2 text-sm">chevron_right</span> Bagaimana jika usulan saya ditolak?</h4>
                <p class="text-slate-600 text-xs md:text-sm pl-6 leading-relaxed">Periksa catatan yang diberikan oleh verifikator/pimpinan di menu "Detail Usulan". Lakukan revisi dokumen dan ajukan ulang melalui menu Edit pada halaman Pengajuan.</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 md:p-5 shadow-sm">
                <h4 class="font-bold text-slate-800 text-sm md:text-base mb-2 flex items-center"><span class="material-icons text-blue-500 mr-2 text-sm">chevron_right</span> Berapa lama proses pencairan dana?</h4>
                <p class="text-slate-600 text-xs md:text-sm pl-6 leading-relaxed">Sesuai SOP, proses pencairan memakan waktu maksimal 3 hari kerja setelah status berubah menjadi "Disetujui" oleh PPK dan direkomendasikan oleh WD2.</p>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>