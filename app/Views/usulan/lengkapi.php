<?php include __DIR__.'/../partials/sidebar.php'; ?>

<div class="p-8 max-w-3xl mx-auto">
    <div class="mb-8">
        <a href="/monitoring" class="text-slate-500 hover:text-blue-600 font-bold flex items-center mb-4"><span class="material-icons text-sm mr-2">arrow_back</span> Kembali</a>
        <h1 class="text-2xl font-extrabold text-slate-900">Lengkapi Administrasi</h1>
        <p class="text-slate-500">Tahap akhir sebelum diajukan ke Wakil Direktur 2.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
        <div class="bg-violet-50 px-8 py-6 border-b border-violet-100">
            <h2 class="font-bold text-violet-900 flex items-center">
                <span class="material-icons mr-2">assignment_ind</span> Data Pelaksana & Dokumen
            </h2>
        </div>
        
        <form action="/usulan/proses-lengkapi?id=<?php echo $usulan['id']; ?>" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Penanggung Jawab (PJ)</label>
                    <input type="text" name="penanggung_jawab" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-violet-500" placeholder="Nama Lengkap & Gelar">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pelaksana Kegiatan</label>
                    <input type="text" name="pelaksana_kegiatan" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-violet-500" placeholder="Tim / Unit Pelaksana">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="<?php echo $usulan['tanggal_mulai']; ?>" required class="w-full px-4 py-3 border border-slate-300 rounded-xl">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="<?php echo $usulan['tanggal_selesai']; ?>" required class="w-full px-4 py-3 border border-slate-300 rounded-xl">
                </div>
            </div>

            <div class="border-2 border-dashed border-violet-200 rounded-xl p-6 bg-violet-50/50 text-center">
                <span class="material-icons text-4xl text-violet-300 mb-2">cloud_upload</span>
                <h3 class="font-bold text-violet-900 mb-1">Upload Surat Pengantar</h3>
                <p class="text-xs text-violet-600 mb-4">Format PDF, Maksimal 5MB. Wajib.</p>
                <input type="file" name="surat_pengantar" required accept="application/pdf" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-100 file:text-violet-700 hover:file:bg-violet-200">
            </div>

            <button type="submit" class="w-full py-4 bg-violet-700 text-white font-bold rounded-xl shadow-lg hover:bg-violet-800 transition-all">
                Simpan & Ajukan ke WD2
            </button>
        </form>
    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>