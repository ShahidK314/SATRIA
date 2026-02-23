<?php 
// app/Views/admin/iku.php (READ ONLY FOR DIREKTUR)
include __DIR__.'/../partials/sidebar.php'; 
// Asumsi variabel: $iku
$isEditable = ($_SESSION['role'] === 'Admin');
?>

<div class="m-5">
    <div class="mb-8">
        <a href="/master" class="text-slate-500 hover:text-blue-600 font-bold flex items-center gap-2 mb-4 transition-colors">
            <span class="material-icons text-sm">arrow_back</span> Kembali ke Master Data
        </a>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Master IKU</h1>
        <p class="text-slate-500 mt-1">Kelola daftar Indikator Kinerja Utama (IKU) yang digunakan pengusul.</p>
    </div>

    <?php if ($isEditable): ?>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-slate-200 mb-8">
            <h2 class="text-xl font-bold text-slate-800 mb-4">Tambah IKU Baru</h2>
            <form action="/master/iku/store" method="POST" class="flex gap-4">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <input type="text" name="deskripsi_iku" placeholder="Deskripsi IKU (Contoh: Persentase lulusan...)" required class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-sm">
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 text-sm">Tambah</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <?php if (empty($iku)): ?>
            <div class="p-12 text-center">Tidak ada data IKU.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-bold text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 w-12">ID</th>
                            <th class="px-6 py-4">Kode IKU</th> <th class="px-6 py-4">Deskripsi IKU</th>
                            <th class="px-6 py-4 w-24 text-center">Status</th>
                            <?php if ($isEditable): ?>
                                <th class="px-6 py-4 w-40 text-right">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($iku as $i): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-xs text-slate-400"><?php echo $i['id']; ?></td>
                            <td class="px-6 py-3 font-bold text-blue-600"><?php echo htmlspecialchars($i['kode_iku'] ?? '-'); ?></td> <td class="px-6 py-3 text-slate-700"><?php echo htmlspecialchars($i['deskripsi_iku']); ?></td>
                            <td class="px-6 py-3 text-center">
                                <span class="px-2 py-1 rounded text-xs font-bold <?php echo $i['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'; ?>">
                                    <?php echo $i['status'] === 'active' ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </td>
                            <?php if ($isEditable): ?>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="openEditModal(<?php echo $i['id']; ?>, '<?php echo htmlspecialchars($i['deskripsi_iku']); ?>')" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-all" title="Edit Deskripsi">
                                            <span class="material-icons text-sm">edit</span>
                                        </button>
                                        <form action="/master/iku/toggle-status" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                            <input type="hidden" name="id" value="<?php echo $i['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $i['status']; ?>">
                                            <button type="submit" 
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 <?php echo $i['status'] === 'active' ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50'; ?> transition-all" 
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
<div id="modalEdit" class="fixed inset-0 bg-slate-900/50 hidden flex items-center justify-center z-[100]">
    <div class="bg-white rounded-xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-xl font-bold text-slate-800">Edit Deskripsi IKU</h3>
        </div>
        <form action="/master/iku/update" method="POST" class="p-6">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <input type="hidden" id="edit_id" name="id">
            
            <div class="mb-4">
                <label for="edit_deskripsi_iku" class="block text-sm font-medium text-slate-700 mb-1">Deskripsi IKU</label>
                <textarea id="edit_deskripsi_iku" name="deskripsi_iku" rows="3" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm"></textarea>
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 text-sm">Batal</button>
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white font-bold rounded-lg hover:bg-amber-700 text-sm">Update</button>
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