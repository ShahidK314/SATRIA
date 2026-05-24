<?php 
// app/Views/admin/master_landing.php
include __DIR__.'/../partials/sidebar.php'; 
$isEditable = ($_SESSION['role'] === 'Admin');
?>

<div class="m-4 md:m-5">
    <div class="mb-6 md:mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Master Data</h1>
        <p class="text-slate-500 mt-1 text-sm md:text-base">Pengaturan data inti sistem, termasuk Jurusan, IKU, dan Satuan Anggaran.</p>
        <?php if (!$isEditable): ?>
            <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-700 p-3 md:p-4 mt-4 text-xs md:text-sm rounded-r-lg">
                <p class="font-bold flex items-center"><span class="material-icons text-sm mr-1">info</span> Mode Hanya Lihat</p>
                <p class="mt-1">Sebagai Direktur, Anda hanya dapat melihat data Master. Perubahan hanya bisa dilakukan oleh Admin.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
        
        <a href="/master/jurusan" class="block bg-white p-5 md:p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-lg hover:border-blue-300 transition-all group">
            <span class="material-icons text-3xl md:text-4xl text-blue-600 mb-2 md:mb-3 block">school</span>
            <h2 class="text-lg md:text-xl font-bold text-slate-800 group-hover:text-blue-700 transition-colors">Jurusan</h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Kelola daftar jurusan PNJ untuk alokasi pengusul.</p>
        </a>

        <a href="/master/iku" class="block bg-white p-5 md:p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-lg hover:border-emerald-300 transition-all group">
            <span class="material-icons text-3xl md:text-4xl text-emerald-600 mb-2 md:mb-3 block">trending_up</span>
            <h2 class="text-lg md:text-xl font-bold text-slate-800 group-hover:text-emerald-700 transition-colors">IKU</h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Kelola Indikator Kinerja Utama (IKU) dan status aktif/nonaktifnya.</p>
        </a>

        <a href="/master/satuan" class="block bg-white p-5 md:p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-lg hover:border-amber-300 transition-all group">
            <span class="material-icons text-3xl md:text-4xl text-amber-600 mb-2 md:mb-3 block">widgets</span>
            <h2 class="text-lg md:text-xl font-bold text-slate-800 group-hover:text-amber-700 transition-colors">Satuan Anggaran</h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Kelola satuan RAB (ORG, LS, PP, dll.) dan status aktif/nonaktifnya.</p>
        </a>

    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>