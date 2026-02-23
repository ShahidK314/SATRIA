<?php
// app/Views/partials/sidebar.php (UPDATED: Added 'Semua Usulan' menu)
include __DIR__ . '/header.php';

$role = $_SESSION['role'] ?? '';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Definisi Menu Baru Sesuai Alur
$menus = [
    'Admin' => [
        ['label' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'dashboard'],
        ['label' => 'Manajemen Pengguna', 'url' => '/users', 'icon' => 'manage_accounts'],
        ['label' => 'Master Data', 'url' => '/master', 'icon' => 'dns'],
        // [BARU] Menu Semua Usulan
        ['label' => 'Semua Usulan', 'url' => '/admin/usulan', 'icon' => 'list_alt'],
        ['label' => 'Monitoring Keterlambatan LPJ', 'url' => '/monitoring/keterlambatan', 'icon' => 'warning'],
        ['label' => 'Log Audit', 'url' => '/audit-log', 'icon' => 'security'],
    ],
    'Pengusul' => [
        ['label' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'dashboard'],
        ['label' => 'Pengajuan', 'icon' => 'folder', 'submenu' => [
            ['label' => 'Usulan', 'url' => '/pengajuan/usulan'],
            ['label' => 'Kegiatan', 'url' => '/pengajuan/kegiatan'],
            ['label' => 'LPJ', 'url' => '/pengajuan/lpj'],
        ]],
        ['label' => 'Monitoring', 'url' => '/monitoring', 'icon' => 'history'],
        ['label' => 'Notifikasi', 'url' => '/notifikasi', 'icon' => 'notifications'],
    ],
    'Verifikator' => [
        ['label' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'dashboard'],
        ['label' => 'Verifikasi Usulan', 'url' => '/verifikasi', 'icon' => 'fact_check'],
        ['label' => 'Riwayat', 'url' => '/verifikasi/riwayat', 'icon' => 'rule'],
        ['label' => 'Notifikasi', 'url' => '/notifikasi', 'icon' => 'notifications'],
    ],
    'PPK' => [
        ['label' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'dashboard'],
        ['label' => 'Approval Kegiatan', 'url' => '/pengajuan/ppk', 'icon' => 'verified_user'],
        ['label' => 'Riwayat Approval', 'url' => '/pengajuan/ppk/riwayat', 'icon' => 'rule'],
        ['label' => 'Notifikasi', 'url' => '/notifikasi', 'icon' => 'notifications'],
    ],
    'WD2' => [
        ['label' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'dashboard'],
        ['label' => 'Approval Kegiatan', 'url' => '/pengajuan/wd2', 'icon' => 'gavel'],
        ['label' => 'Riwayat Approval', 'url' => '/pengajuan/wd2/riwayat', 'icon' => 'rule'],
        ['label' => 'Notifikasi', 'url' => '/notifikasi', 'icon' => 'notifications'],
    ],
    'Bendahara' => [
        ['label' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'dashboard'],
        ['label' => 'Pencairan Dana', 'url' => '/pencairan', 'icon' => 'payments'],
        ['label' => 'Verifikasi LPJ', 'url' => '/lpj', 'icon' => 'receipt_long'],
        ['label' => 'Riwayat', 'url' => '/lpj/riwayat', 'icon' => 'rule'],
        ['label' => 'Notifikasi', 'url' => '/notifikasi', 'icon' => 'notifications'],
    ],
    'Direktur' => [
        ['label' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'dashboard'],
        ['label' => 'Laporan Kinerja', 'url' => '/laporan', 'icon' => 'analytics'],
        // [BARU] Menu Semua Usulan untuk Direktur
        ['label' => 'Semua Usulan', 'url' => '/admin/usulan', 'icon' => 'list_alt'],
        ['label' => 'Monitoring Keterlambatan LPJ', 'url' => '/monitoring/keterlambatan', 'icon' => 'warning'],
    ],
];
$menu = $menus[$role] ?? [];
?>

<aside class="fixed top-0 left-0 z-50 w-64 h-screen bg-slate-900 border-r border-slate-800 flex flex-col transition-transform duration-300">
    
    <div class="flex items-center h-20 border-b border-slate-800 shrink-0 px-4 py-6">
        <img src="/logo_pnj.png" alt="Logo PNJ" class="w-9 h-9 mr-3 drop-shadow-sm brightness-200">
        <div class="flex flex-col">
            <span class="text-xl font-extrabold text-white tracking-tight leading-none">SATRIA</span>
            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mt-0.5">PNJ</span>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto px-4 py-6 space-y-1 custom-scrollbar hover:overflow-y-auto">
        <div class="px-3 mb-2 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Menu Utama</div>
        
        <ul class="space-y-1">
            <?php foreach ($menu as $item): 
                if (isset($item['submenu'])): 
                    $isAnySubActive = false;
                    foreach ($item['submenu'] as $sub) {
                        if ($uri === $sub['url'] || (strpos($uri, $sub['url']) === 0 && $sub['url'] !== '/')) {
                            $isAnySubActive = true;
                            break;
                        }
                    }
                ?>
                    <li class="relative">
                        <button onclick="toggleSubmenu(this)" 
                                class="w-full flex items-center justify-between px-3 py-3 text-sm font-semibold rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all duration-200 group
                                <?php echo $isAnySubActive ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : ''; ?>"
                                <?php echo $isAnySubActive ? 'aria-expanded="true"' : 'aria-expanded="false"'; ?>>
                            <div class="flex items-center">
                                <span class="material-icons text-[20px] mr-3 <?php echo $isAnySubActive ? 'text-white' : 'text-slate-600 group-hover:text-blue-400'; ?>"><?php echo $item['icon']; ?></span>
                                <span class="<?php echo $isAnySubActive ? 'text-white' : ''; ?>"><?php echo $item['label']; ?></span>
                            </div>
                            <span class="material-icons text-sm submenu-arrow transition-transform <?php echo $isAnySubActive ? 'rotate-180 text-white' : 'text-slate-600'; ?>">expand_more</span>
                        </button>
                        <ul class="submenu pl-10 mt-1 space-y-1 overflow-hidden transition-all duration-300" 
                            style="<?php echo $isAnySubActive ? 'max-height: 200px; padding-top: 5px; padding-bottom: 5px;' : 'max-height: 0; padding: 0;'; ?>">
                            <?php foreach ($item['submenu'] as $sub): 
                                $isSubActive = ($uri === $sub['url']) || (strpos($uri, $sub['url']) === 0 && $sub['url'] !== '/');
                            ?>
                                <li>
                                    <a href="<?php echo $sub['url']; ?>" 
                                       class="block px-3 py-2 text-xs font-medium rounded-lg transition-all <?php echo $isSubActive ? 'bg-blue-100/20 text-blue-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                                        <?php echo $sub['label']; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php else: 
                    $isExactMatch = ($uri === $item['url']);
                    $isDetailMatch = false;
                    if (($item['url'] === '/pengajuan/ppk' || $item['url'] === '/pengajuan/wd2') && strpos($uri, $item['url'] . '/detail') === 0) {
                        $isDetailMatch = true;
                    }
                    $isActive = $isExactMatch || $isDetailMatch; 
                ?>
                    <li>
                        <a href="<?php echo $item['url']; ?>" 
                           class="flex items-center px-3 py-3 text-sm font-semibold rounded-xl transition-all duration-200 group relative overflow-hidden whitespace-nowrap
                           <?php echo $isActive 
                               ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' 
                               : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                            
                            <?php if($isActive): ?>
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-blue-500"></div>
                            <?php endif; ?>

                            <div class="w-6 flex justify-center mr-3 relative z-10">
                                <span class="material-icons text-[20px] transition-colors
                                    <?php echo $isActive ? 'text-white' : 'text-slate-600 group-hover:text-blue-400'; ?>">
                                    <?php echo $item['icon']; ?>
                                </span>
                            </div>

                            <span class="relative z-10 font-medium tracking-wide"><?php echo $item['label']; ?></span>
                            
                            <?php if($isActive): ?>
                                <span class="material-icons text-[16px] absolute right-3 z-10 text-blue-300">chevron_right</span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>

        <div class="my-6 border-t border-slate-800"></div>

        <div class="px-3 mb-2 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Pengaturan</div>
        <ul class="space-y-1">
            <li>
                <?php $isProfil = ($uri === '/profil'); ?>
                <a href="/profil" class="flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 group whitespace-nowrap
                   <?php echo $isProfil ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                    
                    <div class="w-6 flex justify-center mr-3">
                        <span class="material-icons text-[20px] <?php echo $isProfil?'text-white':'text-slate-600 group-hover:text-slate-400'; ?>">account_circle</span>
                    </div>
                    <span>Profil Saya</span>
                </a>
            </li>
            <li>
                <a href="mailto:it@pnj.ac.id" class="flex items-center px-3 py-3 text-sm font-medium rounded-xl text-slate-400 hover:bg-slate-800 hover:text-amber-400 transition-all group whitespace-nowrap">
                    <div class="w-6 flex justify-center mr-3">
                        <span class="material-icons text-[20px] text-slate-600 group-hover:text-amber-400">bug_report</span>
                    </div>
                    <span>Lapor Masalah</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="p-4 border-t border-slate-800 bg-slate-900 shrink-0">
        <button onclick="document.getElementById('modalLogout').classList.remove('hidden')" class="flex items-center justify-center w-full p-3 text-sm font-bold text-rose-400 bg-slate-800 rounded-xl hover:bg-rose-600 hover:text-white hover:shadow-lg hover:shadow-rose-600/30 transition-all duration-300 group">
            <span class="material-icons text-[20px] mr-2 transition-transform group-hover:-translate-x-1">logout</span>
            Keluar Sistem
        </button>
    </div>
</aside>

<div class="ml-64 min-h-screen flex flex-col relative selection:bg-blue-100">

<div id="modalLogout" class="fixed inset-0 z-[999] hidden flex items-center justify-center bg-slate-900/80 backdrop-blur-sm transition-opacity duration-300 px-4">
    <div class="relative bg-white rounded-3xl shadow-2xl p-8 w-full max-w-sm text-center transform transition-all scale-100 animate-fade-in-down overflow-hidden border border-slate-100">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-rose-500 via-red-500 to-orange-500"></div>
        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-rose-50 mb-6 ring-8 ring-rose-50/50 shadow-inner group cursor-default">
            <span class="material-icons text-4xl text-rose-600 group-hover:scale-110 group-hover:rotate-12 transition-transform duration-500">logout</span>
        </div>
        <h3 class="text-2xl font-black text-slate-800 mb-2 tracking-tight">Berhenti Sesi?</h3>
        <p class="text-slate-500 text-sm mb-8 leading-relaxed px-2">
            Anda akan keluar dari sistem <span class="font-bold text-slate-800">SATRIA</span>. Pastikan pekerjaan Anda sudah tersimpan aman.
        </p>
        <div class="grid grid-cols-2 gap-3">
            <button onclick="document.getElementById('modalLogout').classList.add('hidden')" class="px-4 py-3.5 bg-slate-50 text-slate-600 font-bold rounded-xl hover:bg-slate-100 hover:text-slate-900 transition-all border border-slate-200 focus:ring-2 focus:ring-slate-200">
                Batal
            </button>
            <a href="/logout" class="px-4 py-3.5 bg-gradient-to-r from-rose-600 to-red-600 text-white font-bold rounded-xl hover:from-rose-700 hover:to-red-700 hover:shadow-lg hover:shadow-rose-600/30 transition-all flex items-center justify-center group">
                <span>Keluar</span>
                <span class="material-icons text-sm ml-2 group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </a>
        </div>
    </div>
</div>

<script>
function toggleSubmenu(button) {
    const submenu = button.nextElementSibling;
    const arrow = button.querySelector('.submenu-arrow');
    const isExpanded = button.getAttribute('aria-expanded') === 'true';

    if (isExpanded) {
        submenu.style.maxHeight = '0';
        submenu.style.paddingTop = '0';
        submenu.style.paddingBottom = '0';
        arrow.style.transform = 'rotate(0deg)';
        button.setAttribute('aria-expanded', 'false');
    } else {
        submenu.style.maxHeight = submenu.scrollHeight + 'px';
        submenu.style.paddingTop = '5px';
        submenu.style.paddingBottom = '5px';
        arrow.style.transform = 'rotate(180deg)';
        button.setAttribute('aria-expanded', 'true');
    }
}
</script>