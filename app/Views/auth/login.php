<?php
// app/Views/auth/login.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem - SATRIA PNJ</title>
    <link href="/css/style.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animated-bg { background: linear-gradient(-45deg, #0f172a, #1e293b, #0f172a, #172554); background-size: 400% 400%; animation: gradientBG 15s ease infinite; }
        @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-20px); } 100% { transform: translateY(0px); } }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .input-group:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
    </style>
</head>
<body class="animated-bg min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[400px] md:w-[600px] h-[400px] md:h-[600px] bg-blue-600/30 rounded-full blur-[100px] md:blur-[120px] animate-float"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[300px] md:w-[500px] h-[300px] md:h-[500px] bg-emerald-500/20 rounded-full blur-[80px] md:blur-[100px] animate-float" style="animation-delay: -2s;"></div>
    </div>

    <div class="relative w-full max-w-[1000px] bg-white/90 backdrop-blur-xl rounded-2xl md:rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row z-10 border border-white/20">
        
        <div class="hidden md:flex w-5/12 bg-slate-900 relative flex-col justify-between p-12 text-white overflow-hidden">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl -mr-16 -mt-16"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-500/20 rounded-full blur-3xl -ml-16 -mb-16"></div>

            <div class="relative z-10">
                <a href="/" class="flex items-center gap-3 mb-10 group w-fit">
                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/10 group-hover:bg-white/20 transition-all">
                        <img src="/logo_pnj.png" alt="Logo" class="w-6 h-6 brightness-200">
                    </div>
                    <span class="font-heading font-bold text-lg tracking-tight">SATRIA</span>
                </a>
                <h2 class="text-3xl lg:text-4xl font-heading font-bold leading-tight mb-4">Selamat<br>Datang</h2>
                <p class="text-slate-400 text-sm leading-relaxed">Sistem Administrasi Terintegrasi Politeknik Negeri Jakarta. Kelola TOR, RAB, dan LPJ dalam satu platform modern.</p>
            </div>
            <div class="relative z-10 mt-auto">
                <div class="flex items-center gap-4 text-xs text-slate-500 font-mono">
                    <span>v1.0.0</span><span class="w-1 h-1 bg-slate-600 rounded-full"></span><span>Secure Access</span>
                </div>
            </div>
        </div>

        <div class="w-full md:w-7/12 p-6 sm:p-8 md:p-12 lg:p-16 bg-white relative">
            <a href="/" class="absolute top-6 right-6 md:top-8 md:right-8 text-slate-400 hover:text-blue-600 transition-colors flex items-center text-[10px] md:text-xs font-bold uppercase tracking-wider group">
                <span class="material-icons-round text-base md:text-lg mr-1 group-hover:-translate-x-1 transition-transform">arrow_back</span> Beranda
            </a>

            <div class="mb-8 md:mb-10 mt-4 md:mt-0">
                <div class="md:hidden flex items-center gap-2 mb-6">
                    <img src="/logo_pnj.png" alt="Logo" class="w-8 h-8">
                    <span class="font-heading font-bold text-slate-900 text-lg">SATRIA</span>
                </div>
                <h3 class="text-xl md:text-2xl font-heading font-bold text-slate-900 mb-2">Masuk ke Akun</h3>
                <p class="text-slate-500 text-xs md:text-sm">Silakan masukkan kredensial Anda untuk melanjutkan.</p>
            </div>

            <?php if (isset($error) && !empty($error)): ?>
                <div class="mb-6 p-4 bg-rose-50 border border-rose-100 rounded-xl flex items-start gap-3 animate-fade-in-down">
                    <span class="material-icons-round text-rose-500 text-xl mt-0.5">error_outline</span>
                    <div>
                        <h4 class="text-sm font-bold text-rose-700">Gagal Masuk</h4>
                        <p class="text-xs text-rose-600 mt-0.5"><?php echo $error; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form method="post" action="/login" class="space-y-4 md:space-y-5" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="space-y-1.5">
                    <label class="text-[10px] md:text-xs font-bold text-slate-700 uppercase tracking-wide ml-1">Username</label>
                    <div class="input-group flex items-center w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 transition-all">
                        <span class="material-icons-round text-slate-400 text-lg md:text-xl mr-3">person_outline</span>
                        <input type="text" name="username" id="usernameInput" required list="userList" class="flex-1 bg-transparent border-none outline-none text-sm text-slate-800 font-medium placeholder-slate-400" placeholder="Username" autocomplete="off">
                        <datalist id="userList"></datalist>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] md:text-xs font-bold text-slate-700 uppercase tracking-wide ml-1">Password</label>
                    <div class="input-group flex items-center w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 transition-all">
                        <span class="material-icons-round text-slate-400 text-lg md:text-xl mr-3">lock_outline</span>
                        <input type="password" name="password" id="passwordInput" required class="flex-1 bg-transparent border-none outline-none text-sm text-slate-800 font-medium placeholder-slate-400" placeholder="••••••••" autocomplete="current-password">
                        <button type="button" onclick="togglePassword()" class="text-slate-400 hover:text-blue-600 transition-colors focus:outline-none" tabindex="-1" title="Lihat Password">
                            <span class="material-icons-round text-lg md:text-xl" id="eyeIcon">visibility_off</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1 md:pt-2">
                    <label class="flex items-center cursor-pointer group select-none">
                        <input type="checkbox" id="rememberMe" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 cursor-pointer">
                        <span class="ml-2 text-xs md:text-sm text-slate-500 group-hover:text-slate-700 transition-colors font-medium">Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 md:py-4 bg-gradient-to-r from-blue-700 to-blue-600 hover:from-blue-600 hover:to-blue-500 text-white rounded-xl font-bold shadow-lg shadow-blue-600/20 hover:shadow-blue-600/40 transform hover:-translate-y-0.5 transition-all duration-300 flex justify-center items-center gap-2 group text-sm md:text-base">
                    <span>Masuk Aplikasi</span>
                    <span class="material-icons-round text-sm group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </button>
            </form>

            <div class="mt-6 md:mt-8 pt-6 border-t border-slate-100 text-center">
                <p class="text-[10px] md:text-xs text-slate-400">
                    Mengalami kendala teknis? <a href="mailto:it@pnj.ac.id" class="text-slate-600 font-bold hover:text-blue-600 transition-colors">Hubungi IT Support</a>
                </p>
            </div>
        </div>
    </div>
    
    <div class="fixed bottom-4 md:bottom-6 w-full text-center pointer-events-none z-0">
        <p class="text-[8px] md:text-[10px] text-slate-500/50 font-medium uppercase tracking-widest">&copy; 2025 Politeknik Negeri Jakarta</p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text'; icon.innerText = 'visibility'; icon.classList.add('text-blue-600');
            } else {
                input.type = 'password'; icon.innerText = 'visibility_off'; icon.classList.remove('text-blue-600');
            }
        }
        const STORAGE_KEY = 'satria_saved_accounts';
        document.addEventListener('DOMContentLoaded', function() {
            const usernameInput = document.getElementById('usernameInput');
            const passwordInput = document.getElementById('passwordInput');
            const rememberCheckbox = document.getElementById('rememberMe');
            const loginForm = document.getElementById('loginForm');
            const userList = document.getElementById('userList');

            let savedAccounts = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            savedAccounts.forEach(acc => {
                const option = document.createElement('option'); option.value = acc.username; userList.appendChild(option);
            });

            usernameInput.addEventListener('input', function() {
                const typedVal = this.value;
                const foundAccount = savedAccounts.find(acc => acc.username === typedVal);
                if (foundAccount) {
                    try {
                        passwordInput.value = atob(foundAccount.token);
                        rememberCheckbox.checked = true;
                        passwordInput.classList.add('bg-blue-50');
                        setTimeout(() => passwordInput.classList.remove('bg-blue-50'), 500);
                    } catch (e) { console.error('Error decoding password'); }
                } else {
                    passwordInput.value = ''; rememberCheckbox.checked = false;
                }
            });

            loginForm.addEventListener('submit', function() {
                const user = usernameInput.value.trim();
                const pass = btoa(passwordInput.value);
                if (!user) return;
                savedAccounts = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
                savedAccounts = savedAccounts.filter(acc => acc.username !== user);
                if (rememberCheckbox.checked) { savedAccounts.push({ username: user, token: pass }); } 
                localStorage.setItem(STORAGE_KEY, JSON.stringify(savedAccounts));
            });
        });
    </script>
</body>
</html>