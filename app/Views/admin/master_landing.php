<?php 
// app/Views/admin/master_landing.php (READ ONLY FOR DIREKTUR)
include __DIR__.'/../partials/sidebar.php'; 
$isEditable = ($_SESSION['role'] === 'Admin');
?>

<div class="m-5">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Master Data</h1>
        <p class="text-slate-500 mt-1">Pengaturan data inti sistem, termasuk Jurusan, IKU, dan Satuan Anggaran.</p>
        <?php if (!$isEditable): ?>
            <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-700 p-3 mt-3 text-sm rounded-r-lg">
                <p class="font-bold">Mode Hanya Lihat</p>
                <p>Sebagai Direktur, Anda hanya dapat melihat data Master. Perubahan hanya bisa dilakukan oleh Admin.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <a href="/master/jurusan" class="block bg-white p-6 rounded-xl shadow-lg border border-slate-200 hover:shadow-xl hover:border-blue-300 transition-all group">
            <span class="material-icons text-4xl text-blue-600 mb-3 block">school</span>
            <h2 class="text-xl font-bold text-slate-800 group-hover:text-blue-700">Jurusan</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola daftar jurusan PNJ untuk alokasi pengusul.</p>
        </a>

        <a href="/master/iku" class="block bg-white p-6 rounded-xl shadow-lg border border-slate-200 hover:shadow-xl hover:border-emerald-300 transition-all group">
            <span class="material-icons text-4xl text-emerald-600 mb-3 block">trending_up</span>
            <h2 class="text-xl font-bold text-slate-800 group-hover:text-emerald-700">IKU</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola Indikator Kinerja Utama (IKU) dan status aktif/nonaktifnya.</p>
        </a>

        <a href="/master/satuan" class="block bg-white p-6 rounded-xl shadow-lg border border-slate-200 hover:shadow-xl hover:border-amber-300 transition-all group">
            <span class="material-icons text-4xl text-amber-600 mb-3 block">widgets</span>
            <h2 class="text-xl font-bold text-slate-800 group-hover:text-amber-700">Satuan Anggaran</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola satuan RAB (ORG, LS, PP, dll.) dan status aktif/nonaktifnya.</p>
        </a>

    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>