<?php 
// app/Views/admin/satuan.php (READ ONLY FOR DIREKTUR)
include __DIR__.'/../partials/sidebar.php'; 
// Asumsi variabel: $satuan
$isEditable = ($_SESSION['role'] === 'Admin');
?>

<div class="m-5">
    <div class="mb-8">
        <a href="/master" class="text-slate-500 hover:text-blue-600 font-bold flex items-center gap-2 mb-4 transition-colors">
            <span class="material-icons text-sm">arrow_back</span> Kembali ke Master Data
        </a>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Master Satuan Anggaran</h1>
        <p class="text-slate-500 mt-1">Daftar satuan yang digunakan dalam penyusunan RAB (ORG, PP, LS, dll.).</p>
    </div>

    <?php if ($isEditable): ?>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-slate-200 mb-8">
            <h2 class="text-xl font-bold text-slate-800 mb-4">Tambah Satuan Baru</h2>
            <form action="/master/satuan/store" method="POST" class="flex gap-4">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <input type="text" name="nama_satuan" placeholder="Nama Satuan (Contoh: ORG, LS, PP)" required class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-sm uppercase">
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white font-bold rounded-lg hover:bg-amber-700 text-sm">Tambah</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <?php if (empty($satuan)): ?>
            <div class="p-12 text-center">Tidak ada data satuan.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-bold text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 w-12">ID</th>
                            <th class="px-6 py-4">Nama Satuan</th>
                            <th class="px-6 py-4 w-24 text-center">Status</th>
                            <?php if ($isEditable): ?>
                                <th class="px-6 py-4 w-40 text-right">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($satuan as $s): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-xs text-slate-400"><?php echo $s['id']; ?></td>
                            <td class="px-6 py-3 font-bold text-slate-700 uppercase"><?php echo htmlspecialchars($s['nama_satuan']); ?></td>
                            <td class="px-6 py-3 text-center">
                                <span class="px-2 py-1 rounded text-xs font-bold <?php echo $s['is_active'] == 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'; ?>">
                                    <?php echo $s['is_active'] == 1 ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </td>
                            <?php if ($isEditable): ?>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="openEditModal(<?php echo $s['id']; ?>, '<?php echo htmlspecialchars($s['nama_satuan']); ?>')" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-all" title="Edit Nama">
                                            <span class="material-icons text-sm">edit</span>
                                        </button>
                                        <form action="/master/satuan/toggle-status" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $s['is_active']; ?>">
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 <?php echo $s['is_active'] == 1 ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50'; ?> transition-all" title="<?php echo $s['is_active'] == 1 ? 'Nonaktifkan/Arsipkan' : 'Aktifkan'; ?>">
                                                <span class="material-icons text-sm"><?php echo $s['is_active'] == 1 ? 'block' : 'check_circle'; ?></span>
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
            <h3 class="text-xl font-bold text-slate-800">Edit Satuan Anggaran</h3>
        </div>
        <form action="/master/satuan/update" method="POST" class="p-6">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <input type="hidden" id="edit_id" name="id">
            
            <div class="mb-4">
                <label for="edit_nama_satuan" class="block text-sm font-medium text-slate-700 mb-1">Nama Satuan</label>
                <input type="text" id="edit_nama_satuan" name="nama_satuan" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm uppercase" placeholder="Nama Satuan">
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 text-sm">Batal</button>
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white font-bold rounded-lg hover:bg-amber-700 text-sm">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, nama) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama_satuan').value = nama;
        document.getElementById('modalEdit').classList.remove('hidden');
    }
</script>
<?php endif; ?>

<?php include __DIR__.'/../partials/footer.php'; ?>