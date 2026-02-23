<?php 
// app/Views/admin/jurusan.php (READ ONLY FOR DIREKTUR)
include __DIR__.'/../partials/sidebar.php'; 
// Asumsi variabel: $jurusan
$isEditable = ($_SESSION['role'] === 'Admin');
?>

<div class="m-5">
    <div class="mb-8">
        <a href="/master" class="text-slate-500 hover:text-blue-600 font-bold flex items-center gap-2 mb-4 transition-colors">
            <span class="material-icons text-sm">arrow_back</span> Kembali ke Master Data
        </a>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Master Jurusan</h1>
        <p class="text-slate-500 mt-1">Daftar jurusan yang tersedia untuk penetapan akun pengusul.</p>
    </div>

    <?php if ($isEditable): ?>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-slate-200 mb-8">
            <h2 class="text-xl font-bold text-slate-800 mb-4">Tambah Jurusan Baru</h2>
            <form action="/master/jurusan/store" method="POST" class="flex gap-4">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <input type="text" name="nama_jurusan" placeholder="Nama Jurusan (Contoh: Teknik Elektro)" required class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-sm">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 text-sm">Tambah</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <?php if (empty($jurusan)): ?>
            <div class="p-12 text-center">Tidak ada data jurusan.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-bold text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 w-12">ID</th>
                            <th class="px-6 py-4">Nama Jurusan</th>
                            <th class="px-6 py-4 w-24 text-center">Status</th> 
                            <?php if ($isEditable): ?>
                                <th class="px-6 py-4 w-40 text-right">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($jurusan as $j): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-xs text-slate-400"><?php echo $j['id']; ?></td>
                            <td class="px-6 py-3 font-bold text-slate-700"><?php echo htmlspecialchars($j['nama_jurusan']); ?></td>
                            <td class="px-6 py-3 text-center">
                                <span class="px-2 py-1 rounded text-xs font-bold <?php echo $j['is_active'] == 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'; ?>">
                                    <?php echo $j['is_active'] == 1 ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </td>
                            <?php if ($isEditable): ?>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="openEditModal(<?php echo $j['id']; ?>, '<?php echo htmlspecialchars($j['nama_jurusan']); ?>')" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-all" title="Edit Nama">
                                            <span class="material-icons text-sm">edit</span>
                                        </button>
                                        <form action="/master/jurusan/toggle-status" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                            <input type="hidden" name="id" value="<?php echo $j['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $j['is_active']; ?>">
                                            <button type="submit" 
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 <?php echo $j['is_active'] == 1 ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50'; ?> transition-all" 
                                                    title="<?php echo $j['is_active'] == 1 ? 'Nonaktifkan' : 'Aktifkan'; ?>"
                                                    onclick="return confirm('Yakin ingin <?php echo $j['is_active'] == 1 ? 'menonaktifkan' : 'mengaktifkan'; ?> jurusan <?php echo htmlspecialchars($j['nama_jurusan']); ?>?');">
                                                <span class="material-icons text-sm"><?php echo $j['is_active'] == 1 ? 'block' : 'check_circle'; ?></span>
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
            <h3 class="text-xl font-bold text-slate-800">Edit Jurusan</h3>
        </div>
        <form action="/master/jurusan/update" method="POST" class="p-6">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <input type="hidden" id="edit_id" name="id">
            
            <div class="mb-4">
                <label for="edit_nama_jurusan" class="block text-sm font-medium text-slate-700 mb-1">Nama Jurusan</label>
                <input type="text" id="edit_nama_jurusan" name="nama_jurusan" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Nama Jurusan">
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
        document.getElementById('edit_nama_jurusan').value = nama;
        document.getElementById('modalEdit').classList.remove('hidden');
    }
</script>
<?php endif; ?>

<?php include __DIR__.'/../partials/footer.php'; ?>