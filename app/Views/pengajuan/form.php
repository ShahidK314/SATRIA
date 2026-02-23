<?php 
// app/Views/pengajuan/form.php
include __DIR__.'/../partials/sidebar.php'; 
?>

<div class="p-8 max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Form Pengajuan Kegiatan</h1>
        <p class="text-slate-500 mt-1">Lengkapi data Penanggung Jawab, Pelaksana, dan Surat Pengantar untuk kegiatan yang telah disetujui Verifikator.</p>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-6">
        <h2 class="text-xl font-bold text-slate-800 mb-4">Kegiatan: <?php echo htmlspecialchars($usulan['nama_kegiatan']); ?></h2>
        

        <form action="/pengajuan/submit" method="POST" enctype="multipart/form-data" id="formPengajuan">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <input type="hidden" name="usulan_id" value="<?php echo $usulan['id']; ?>">

            <div class="mb-5">
                <label for="penanggung_jawab" class="block text-sm font-medium text-slate-700 mb-1">Nama Penanggung Jawab Kegiatan </label>
                <input type="text" id="penanggung_jawab" name="penanggung_jawab" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Nama Lengkap Penanggung Jawab">
            </div>

            <div class="mb-5">
                <label for="pelaksana_kegiatan" class="block text-sm font-medium text-slate-700 mb-1">Nama Pelaksana Kegiatan </label>
                <textarea id="pelaksana_kegiatan" name="pelaksana_kegiatan" rows="3" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Daftar nama pelaksana (Bisa lebih dari satu, pisahkan dengan koma atau baris baru)"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-5">
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-medium text-slate-700 mb-1">Waktu Pelaksanaan Mulai </label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" 
                        min="<?php echo $usulan['tanggal_mulai']; ?>" 
                        max="<?php echo $usulan['tanggal_selesai']; ?>"
                        required 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label for="tanggal_selesai" class="block text-sm font-medium text-slate-700 mb-1">Waktu Pelaksanaan Selesai </label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" 
                        min="<?php echo $usulan['tanggal_mulai']; ?>" 
                        max="<?php echo $usulan['tanggal_selesai']; ?>"
                        required 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
            </div>
            
            <div class="mb-6">
                <label for="surat_pengantar" class="block text-sm font-medium text-slate-700 mb-1">Upload Surat Pengantar (PDF) </label>
                <input type="file" id="surat_pengantar" name="surat_pengantar" accept=".pdf" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-slate-400 mt-1">Hanya format PDF, Maks. 40MB.</p>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <button type="submit" class="w-full py-3 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition-colors flex items-center justify-center">
                    <span class="material-icons text-lg mr-2">send</span> Submit Pengajuan Kegiatan ke PPK
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('formPengajuan').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('surat_pengantar');
    if (fileInput.files.length > 0) {
        const fileSize = fileInput.files[0].size;
        const maxSize = 40 * 1024 * 1024; // 40MB
        if (fileSize > maxSize) {
            alert('Ukuran file terlalu besar! Maksimal 40MB. Silakan kompres file Anda.');
            e.preventDefault();
        }
    }
});
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>