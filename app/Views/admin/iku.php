<?php 
// app/Views/admin/iku.php
include __DIR__.'/../partials/sidebar.php'; 
// Asumsi variabel: $iku
$isEditable = ($_SESSION['role'] === 'Admin');
?>

<div class="m-4 md:m-5">
    <div class="mb-6 md:mb-8">
        <a href="/master" class="text-slate-500 hover:text-blue-600 font-bold flex items-center gap-2 mb-4 w-fit transition-colors">
            <span class="material-icons text-sm">arrow_back</span> Kembali ke Master Data
        </a>
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Master IKU</h1>
        <p class="text-slate-500 mt-1 text-sm md:text-base">Kelola daftar Indikator Kinerja Utama (IKU) yang digunakan pengusul.</p>
    </div>

    <?php if ($isEditable): ?>
        <div class="bg-white p-4 md:p-6 rounded-xl shadow-sm border border-slate-200 mb-6 md:mb-8">
            <h2 class="text-lg md:text-xl font-bold text-slate-800 mb-4">Tambah IKU Baru</h2>
            <form action="/master/iku/store" method="POST" class="flex flex-col sm:flex-row gap-3 md:gap-4">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <input type="text" name="deskripsi_iku" placeholder="Deskripsi IKU (Contoh: Persentase lulusan...)" required class="flex-1 w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition-colors text-sm shadow-sm flex items-center justify-center">
                    <span class="material-icons text-sm mr-1">add</span> Tambah
                </button>
            </form>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <?php if (empty($iku)): ?>
            <div class="p-8 md:p-12 text-center text-slate-500 text-sm">Tidak ada data IKU.</div>
        <?php else: ?>
            <div class="overflow-x-auto w-full custom-scrollbar">
                <table class="w-full text-sm text-left border-collapse min-w-[700px]">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-bold text-[10px] md:text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-4 md:px-6 py-4 w-12">ID</th>
                            <th class="px-4 md:px-6 py-4 w-32">Kode IKU</th> 
                            <th class="px-4 md:px-6 py-4">Deskripsi IKU</th>
                            <th class="px-4 md:px-6 py-4 w-24 text-center">Status</th>
                            <?php if ($isEditable): ?>
                                <th class="px-4 md:px-6 py-4 w-32 text-right">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($iku as $i): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 md:px-6 py-3 font-mono text-xs text-slate-400"><?php echo $i['id']; ?></td>
                            <td class="px-4 md:px-6 py-3 font-bold text-blue-600"><?php echo htmlspecialchars($i['kode_iku'] ?? '-'); ?></td> 
                            <td class="px-4 md:px-6 py-3 text-slate-700 whitespace-normal leading-snug"><?php echo htmlspecialchars($i['deskripsi_iku']); ?></td>
                            <td class="px-4 md:px-6 py-3 text-center">
                                <span class="px-2 py-1 rounded text-[10px] font-bold uppercase <?php echo $i['status'] === 'active' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-rose-100 text-rose-700 border border-rose-200'; ?>">
                                    <?php echo $i['status'] === 'active' ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </td>
                            <?php if ($isEditable): ?>
                                <td class="px-4 md:px-6 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="openEditModal(<?php echo $i['id']; ?>, '<?php echo htmlspecialchars($i['deskripsi_iku'], ENT_QUOTES); ?>')" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-all shadow-sm" title="Edit Deskripsi">
                                            <span class="material-icons text-sm">edit</span>
                                        </button>
                                        <form action="/master/iku/toggle-status" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                            <input type="hidden" name="id" value="<?php echo $i['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $i['status']; ?>">
                                            <button type="submit" 
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 shadow-sm <?php echo $i['status'] === 'active' ? 'text-rose-600 hover:bg-rose-50 hover:border-rose-200' : 'text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200'; ?> transition-all" 
                                                    title="<?php echo $i['status'] === 'active' ? 'Nonaktifkan' : 'Aktifkan'; ?>">
                                                <span class="material-icons text-sm"><?php echo $i['status'] === 'active' ? 'block' : 'check_circle'; ?></span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($isEditable): ?>
<div id="modalEdit" class="fixed inset-0 bg-slate-900/50 hidden flex items-center justify-center z-[100] px-4">
    <div class="bg-white rounded-xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="p-4 md:p-6 border-b border-slate-100">
            <h3 class="text-lg md:text-xl font-bold text-slate-800">Edit Deskripsi IKU</h3>
        </div>
        <form action="/master/iku/update" method="POST" class="p-4 md:p-6">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <input type="hidden" id="edit_id" name="id">
            
            <div class="mb-6">
                <label for="edit_deskripsi_iku" class="block text-sm font-medium text-slate-700 mb-2">Deskripsi IKU</label>
                <textarea id="edit_deskripsi_iku" name="deskripsi_iku" rows="4" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 outline-none resize-none"></textarea>
            </div>
            
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 mt-6">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="w-full sm:w-auto px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 text-sm transition-colors">Batal</button>
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-amber-600 text-white font-bold rounded-lg hover:bg-amber-700 text-sm shadow-md transition-colors">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, deskripsi) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_deskripsi_iku').value = deskripsi;
        document.getElementById('modalEdit').classList.remove('hidden');
    }
</script>
<?php endif; ?>

<?php include __DIR__.'/../partials/footer.php'; ?>