<?php 
// app/Views/admin/users.php
include __DIR__.'/../partials/sidebar.php'; 
$isEditable = ($_SESSION['role'] === 'Admin');
?>

<div class="m-5">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-slate-500 mt-1">Kelola akun pengguna, peran, dan status aktivasi.</p>
            <?php if (!$isEditable): ?>
                <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-700 p-3 mt-3 text-sm rounded-r-lg inline-block">
                    <strong>Mode Read Only:</strong> Anda hanya dapat melihat data pengguna.
                </div>
            <?php endif; ?>
        </div>

        <?php if ($isEditable): ?>
            <div>
                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-colors text-sm shadow-md">
                    <span class="material-icons text-sm mr-2">person_add</span> Tambah Pengguna Baru
                </button>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-2">
                <label for="search" class="block text-xs font-bold text-slate-500 uppercase mb-1">Cari Nama/Username/Email</label>
                <input type="text" id="search" name="search" placeholder="Masukkan nama, username, atau email..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
            </div>
            <div class="md:col-span-1">
                <label for="jurusan" class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter Jurusan</label>
                <select id="jurusan" name="jurusan" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-600">
                    <option value="">-- Semua Jurusan --</option>
                    <?php foreach ($jurusan as $j): ?>
                        <option value="<?php echo $j['id']; ?>" <?php echo (($_GET['jurusan'] ?? '') == $j['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($j['nama_jurusan']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="md:col-span-1">
                <button type="submit" class="w-full py-2 bg-slate-800 text-white font-bold rounded-lg hover:bg-slate-900 transition-all text-sm shadow-md">Filter</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <?php if (empty($users)): ?>
            <div class="p-12 text-center">
                <span class="material-icons text-slate-300 text-5xl mb-2">person_search</span>
                <h3 class="text-lg font-bold text-slate-700">Tidak ada Pengguna Ditemukan</h3>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-bold text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 w-12">ID</th>
                            <th class="px-6 py-4 w-40">Nama & Username</th>
                            <th class="px-6 py-4 w-32">Role</th>
                            <th class="px-6 py-4 min-w-[200px]">Unit/Jurusan</th>
                            <th class="px-6 py-4 w-32">Email</th>
                            <th class="px-6 py-4 w-24 text-center">Status</th>
                            <?php if ($isEditable): ?>
                                <th class="px-6 py-4 w-24 text-right">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-xs text-slate-400"><?php echo $user['id']; ?></td>
                            <td class="px-6 py-3">
                                <p class="font-bold text-slate-800"><?php echo htmlspecialchars($user['nama']); ?></p>
                                <p class="text-xs text-slate-500">@<?php echo htmlspecialchars($user['username']); ?></p>
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded text-xs font-bold <?php echo getRoleClass($user['role']); ?>">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-600">
                                <?php echo htmlspecialchars($user['nama_jurusan'] ?: '-'); ?>
                            </td>
                            <td class="px-6 py-3 text-xs text-slate-500">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span class="px-2 py-1 rounded text-xs font-bold <?php echo $user['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'; ?>">
                                    <?php echo $user['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </td>
                            <?php if ($isEditable): ?>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-all" title="Edit User">
                                            <span class="material-icons text-sm">edit</span>
                                        </button>
                                        <form action="/users/toggle-status" method="POST" onsubmit="return confirm('Yakin ingin <?php echo $user['is_active'] ? 'menonaktifkan' : 'mengaktifkan'; ?> user <?php echo htmlspecialchars($user['username']); ?>?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $user['is_active']; ?>">
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 <?php echo $user['is_active'] ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50'; ?> transition-all" title="<?php echo $user['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>">
                                                <span class="material-icons text-sm"><?php echo $user['is_active'] ? 'block' : 'check_circle'; ?></span>
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

<?php 
function getRoleClass($role) {
    return match($role) {
        'Admin' => 'bg-slate-800 text-white',
        'Pengusul' => 'bg-blue-100 text-blue-700',
        'Verifikator' => 'bg-emerald-100 text-emerald-700',
        'PPK' => 'bg-violet-100 text-violet-700',
        'WD2' => 'bg-indigo-100 text-indigo-700',
        'Bendahara' => 'bg-amber-100 text-amber-700',
        'Direktur' => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-700',
    };
}
?>

<?php if ($isEditable): ?>
<div id="modalAddUser" class="fixed inset-0 bg-slate-900/50 hidden flex items-center justify-center z-[100]">
    <div class="bg-white rounded-xl w-full max-w-lg shadow-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-xl font-bold text-slate-800">Tambah Pengguna Baru</h3>
        </div>
        <form id="formAddUser" action="/users/create" method="POST" class="p-6" onsubmit="submitAddUser(event)">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            
            <div class="mb-4">
                <label for="add_nama" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" id="add_nama" name="nama" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Nama Lengkap Pengguna">
            </div>
            
            <div class="mb-4">
                <label for="add_username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                <input type="text" id="add_username" name="username" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Username">
            </div>
            
            <div class="mb-4">
                <label for="add_email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" id="add_email" name="email" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm" placeholder="email@pnj.ac.id">
            </div>
            
            <div class="mb-4">
                <label for="add_password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" id="add_password" name="password" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Minimal 8 karakter">
            </div>
            
            <div class="mb-4">
                <label for="add_role" class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                <select id="add_role" name="role" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm" onchange="toggleJurusan(this.value, 'add')">
                    <?php 
                    $allowedRoles = ['Pengusul', 'Verifikator', 'WD2', 'PPK', 'Bendahara', 'Admin', 'Direktur'];
                    foreach ($allowedRoles as $role): ?>
                        <option value="<?php echo $role; ?>"><?php echo $role; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div id="jurusan-field-add" class="mb-4 hidden">
                <label for="add_jurusan_id" class="block text-sm font-medium text-slate-700 mb-1">Jurusan (Untuk Pengusul)</label>
                <select id="add_jurusan_id" name="jurusan_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="">-- Pilih Jurusan --</option>
                    <?php foreach ($jurusan as $j): ?>
                        <option value="<?php echo $j['id']; ?>"><?php echo htmlspecialchars($j['nama_jurusan']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4 pt-2 border-t border-slate-100">
                <label class="block text-sm font-medium text-slate-700 mb-1">Kode Keamanan (Captcha) *</label>
                <div class="flex items-center gap-3">
                    <div class="w-2/5 flex-shrink-0 border-2 border-slate-300 rounded-lg overflow-hidden bg-white flex items-center justify-center">
                        <img id="captchaImage" src="/captcha.php?t=<?php echo time(); ?>" alt="Captcha Code" onclick="refreshCaptcha()" class="w-full h-10 object-contain cursor-pointer" onerror="handleCaptchaError(this)">
                    </div>
                    <input type="text" id="add_captcha" name="captcha" required class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-sm uppercase" placeholder="Masukkan kode" autocomplete="off">
                    <button type="button" onclick="refreshCaptcha()" class="w-10 h-10 flex items-center justify-center rounded-lg bg-slate-100 text-blue-600 hover:bg-slate-200 transition-colors" title="Refresh Captcha">
                        <span class="material-icons text-lg">refresh</span>
                    </button>
                </div>
                <p id="captchaErrorMsg" class="text-xs text-rose-600 mt-1 hidden font-bold"></p>
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 text-sm">Batal</button>
                <button type="submit" id="btnSubmitAdd" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 text-sm flex items-center">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditUser" class="fixed inset-0 bg-slate-900/50 hidden flex items-center justify-center z-[100]">
    <div class="bg-white rounded-xl w-full max-w-lg shadow-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-xl font-bold text-slate-800">Edit Pengguna: <span id="edit_username_display"></span></h3>
        </div>
        <form action="/users/update" method="POST" class="p-6">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <input type="hidden" id="edit_id" name="id">
            
            <div class="mb-4">
                <label for="edit_nama" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" id="edit_nama" name="nama" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Nama Lengkap">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" id="edit_email" readonly class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm bg-slate-100 text-slate-500">
            </div>
            
            <div class="mb-4">
                <label for="edit_role" class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                <select id="edit_role" name="role" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm" onchange="toggleJurusan(this.value, 'edit')">
                    <?php foreach ($allowedRoles as $role): ?>
                        <option value="<?php echo $role; ?>"><?php echo $role; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div id="jurusan-field-edit" class="mb-4 hidden">
                <label for="edit_jurusan_id" class="block text-sm font-medium text-slate-700 mb-1">Jurusan (Untuk Pengusul)</label>
                <select id="edit_jurusan_id" name="jurusan_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="">-- Pilih Jurusan --</option>
                    <?php foreach ($jurusan as $j): ?>
                        <option value="<?php echo $j['id']; ?>"><?php echo htmlspecialchars($j['nama_jurusan']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4 pt-4 border-t border-slate-100">
                <label for="edit_password" class="block text-sm font-medium text-slate-700 mb-1">Ganti Password (Kosongkan jika tidak diubah)</label>
                <input type="password" id="edit_password" name="password" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Password baru (Min 8 karakter)">
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 text-sm">Batal</button>
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white font-bold rounded-lg hover:bg-amber-700 text-sm">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    let isRelativePathMode = false;

    document.addEventListener('DOMContentLoaded', function() {
        toggleJurusan(document.getElementById('add_role').value, 'add');
    });

    function toggleJurusan(role, prefix) {
        const field = document.getElementById(`jurusan-field-${prefix}`);
        const select = document.getElementById(`${prefix}_jurusan_id`);
        if (role === 'Pengusul') {
            field.classList.remove('hidden');
            select.setAttribute('required', 'required');
        } else {
            field.classList.add('hidden');
            select.removeAttribute('required');
        }
    }

    function openAddModal() {
        const modal = document.getElementById('modalAddUser');
        modal.classList.remove('hidden');
        document.getElementById('add_captcha').value = '';
        document.getElementById('captchaErrorMsg').classList.add('hidden');
        document.getElementById('captchaErrorMsg').innerText = '';
        
        isRelativePathMode = false;
        setTimeout(() => { refreshCaptcha(); }, 100);
    }

    function closeAddModal() {
        document.getElementById('modalAddUser').classList.add('hidden');
    }

    // --- AJAX SUBMIT LOGIC ---
    function submitAddUser(event) {
        event.preventDefault(); // Mencegah reload halaman
        
        const form = document.getElementById('formAddUser');
        const formData = new FormData(form);
        const btnSubmit = document.getElementById('btnSubmitAdd');
        const originalBtnText = btnSubmit.innerHTML;
        const errorMsgEl = document.getElementById('captchaErrorMsg');

        // Disable button loading state
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="material-icons animate-spin text-sm mr-2">sync</span> Menyimpan...';
        errorMsgEl.classList.add('hidden');

        fetch('/users/create', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest' // Header tanda AJAX
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.reload(); 
            } else {
                refreshCaptcha();
                document.getElementById('add_captcha').value = '';
                
                // Tampilkan pesan error spesifik jika captcha salah
                if (data.msg.toLowerCase().includes('captcha') || data.msg.toLowerCase().includes('kode keamanan')) {
                    errorMsgEl.innerText = data.msg;
                    errorMsgEl.classList.remove('hidden');
                } else {
                    alert("Gagal: " + data.msg);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Terjadi kesalahan koneksi sistem.");
        })
        .finally(() => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnText;
        });
    }

    function openEditModal(user) {
        document.getElementById('edit_id').value = user.id;
        document.getElementById('edit_username_display').textContent = user.username;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_nama').value = user.nama;
        
        const jurusanSelect = document.getElementById('edit_jurusan_id');
        jurusanSelect.value = user.jurusan_id || '';
        toggleJurusan(user.role, 'edit');
        
        document.getElementById('modalEditUser').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('modalEditUser').classList.add('hidden');
    }

    function refreshCaptcha() {
        const captchaImage = document.getElementById('captchaImage');
        const timestamp = new Date().getTime();
        let newSrc;
        
        if (isRelativePathMode) {
             newSrc = 'captcha.php?t=' + timestamp;
        } else {
             newSrc = '/captcha.php?t=' + timestamp; 
        }

        captchaImage.src = newSrc;
        captchaImage.style.opacity = '1';
        
        captchaImage.onerror = function() { handleCaptchaError(this); };
    }

    function handleCaptchaError(img) {
        if (!isRelativePathMode) {
            isRelativePathMode = true;
            refreshCaptcha();
            return;
        }
        const container = img.parentElement;
        if (container.classList.contains('w-2/5')) { 
            container.innerHTML = 
                '<div class="flex flex-col items-center justify-center h-10 p-2 text-xs text-rose-600 font-medium">Gagal memuat</div>';
        }
    }
</script>
<?php endif; ?>

<?php include __DIR__.'/../partials/footer.php'; ?>