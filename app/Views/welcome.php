<?php
// app/Views/welcome.php
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SATRIA - Politeknik Negeri Jakarta</title>
    <link href="/css/style.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6, .font-heading { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 8px; background: #020617; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .text-gradient { background: linear-gradient(135deg, #60A5FA 0%, #34D399 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-[#020617] text-slate-300 antialiased overflow-x-hidden selection:bg-blue-500 selection:text-white">
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] left-[-10%] w-[400px] md:w-[600px] h-[400px] md:h-[600px] bg-blue-600/20 rounded-full blur-[100px] md:blur-[120px] opacity-40 animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[300px] md:w-[500px] h-[300px] md:h-[500px] bg-emerald-500/10 rounded-full blur-[80px] md:blur-[100px] opacity-30"></div>
    </div>

    <nav class="fixed w-full z-50 transition-all duration-300" id="navbar">
        <div class="absolute inset-0 bg-[#020617]/70 backdrop-blur-md border-b border-white/5"></div>
        <div class="max-w-7xl mx-auto px-4 md:px-6 h-16 md:h-20 flex justify-between items-center relative z-10">
            <a href="/" class="flex items-center gap-2 md:gap-3 group">
                <div class="relative w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-blue-600/20 rounded-lg md:rounded-xl border border-blue-500/30 group-hover:border-blue-400/50 transition-all">
                    <img src="/logo_pnj.png" alt="Logo PNJ" class="w-5 h-5 md:w-6 md:h-6 brightness-200">
                </div>
                <div class="flex flex-col">
                    <span class="text-lg md:text-xl font-bold text-white tracking-tight leading-none">SATRIA</span>
                    <span class="hidden sm:block text-[8px] md:text-[10px] font-bold text-blue-400 uppercase tracking-widest">Politeknik Negeri Jakarta</span>
                </div>
            </a>
            <div class="hidden md:flex gap-1">
                <a href="#fitur" class="px-5 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 rounded-full transition-all">Fitur</a>
                <a href="#alur" class="px-5 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 rounded-full transition-all">Alur Kerja</a>
                <a href="/bantuan" class="px-5 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 rounded-full transition-all">Bantuan</a>
            </div>
            <a href="/login" class="px-4 py-2 md:px-6 md:py-2.5 bg-white text-slate-900 text-xs md:text-sm font-bold rounded-full hover:bg-blue-50 hover:shadow-[0_0_20px_rgba(255,255,255,0.3)] transition-all transform hover:-translate-y-0.5 flex items-center gap-1 md:gap-2">
                Masuk <span class="material-icons-round text-[14px] md:text-sm">login</span>
            </a>
        </div>
    </nav>

    <section class="relative pt-24 pb-16 md:pt-48 md:pb-32 overflow-hidden min-h-screen flex flex-col justify-center z-10">
        <div class="max-w-7xl mx-auto px-4 md:px-6 text-center">
            <div data-aos="fade-down" data-aos-duration="1000">
                <div class="inline-flex items-center px-3 md:px-4 py-1.5 rounded-full border border-blue-500/30 bg-blue-500/10 backdrop-blur-md text-blue-300 text-[10px] md:text-xs font-bold mb-6 md:mb-8">
                    <span class="w-1.5 h-1.5 md:w-2 md:h-2 rounded-full bg-blue-400 mr-2 animate-pulse"></span>
                    Sistem Administrasi Terintegrasi 
                </div>
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-7xl font-extrabold text-white tracking-tight mb-4 md:mb-6 leading-[1.2] md:leading-[1.1]" data-aos="zoom-in" data-aos-duration="1200">
                Kelola Anggaran <br class="hidden sm:block">
                <span class="text-gradient">Lebih Transparan & Cepat.</span>
            </h1>
            <p class="text-sm sm:text-base md:text-xl text-slate-400 max-w-2xl mx-auto mb-8 md:mb-10 leading-relaxed font-light px-4" data-aos="fade-up" data-aos-delay="200">
                Platform digital resmi PNJ untuk pengajuan TOR, RAB, dan LPJ. Memangkas birokrasi, mempercepat pencairan, dan menjamin akuntabilitas data.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-3 md:gap-4 px-6 sm:px-0" data-aos="fade-up" data-aos-delay="400">
                <a href="/login" class="w-full sm:w-auto px-6 md:px-8 py-3 md:py-4 bg-gradient-to-r from-blue-600 to-blue-500 text-white text-sm md:text-base font-bold rounded-full shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 hover:scale-105 transition-all duration-300 flex items-center justify-center gap-2 group">
                    <span class="material-icons-round text-lg md:text-xl group-hover:rotate-12 transition-transform">rocket_launch</span> Mulai Sekarang
                </a>
                <a href="/bantuan" class="w-full sm:w-auto px-6 md:px-8 py-3 md:py-4 bg-slate-800/50 border border-slate-700 text-white text-sm md:text-base font-bold rounded-full hover:bg-slate-800 hover:border-slate-500 transition-all flex items-center justify-center gap-2 group">
                    <span class="material-icons-round text-slate-400 text-lg md:text-xl group-hover:text-white transition-colors">menu_book</span> Pelajari Alur
                </a>
            </div>
            <div class="mt-12 md:mt-20 grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8 border-t border-white/5 pt-8 md:pt-10" data-aos="fade-up" data-aos-delay="600">
                <div><div class="text-xl md:text-3xl font-bold text-white mb-1">100%</div><div class="text-[10px] md:text-xs text-slate-500 font-bold uppercase tracking-wider">Digitalisasi</div></div>
                <div><div class="text-xl md:text-3xl font-bold text-white mb-1">24/7</div><div class="text-[10px] md:text-xs text-slate-500 font-bold uppercase tracking-wider">Akses Sistem</div></div>
                <div><div class="text-xl md:text-3xl font-bold text-white mb-1">Real-time</div><div class="text-[10px] md:text-xs text-slate-500 font-bold uppercase tracking-wider">Monitoring</div></div>
                <div><div class="text-xl md:text-3xl font-bold text-white mb-1">Paperless</div><div class="text-[10px] md:text-xs text-slate-500 font-bold uppercase tracking-wider">Environment</div></div>
            </div>
        </div>
    </section>

    <section id="fitur" class="py-16 md:py-24 relative z-10">
        <div class="max-w-7xl mx-auto px-4 md:px-6">
            <div class="text-center mb-12 md:mb-20" data-aos="fade-up">
                <h2 class="text-xs md:text-sm font-bold text-blue-400 uppercase tracking-widest mb-2 md:mb-3">Fitur Unggulan</h2>
                <h3 class="text-2xl md:text-4xl font-bold text-white">Solusi Administrasi Modern</h3>
                <div class="w-16 md:w-20 h-1 bg-gradient-to-r from-blue-600 to-emerald-500 mx-auto mt-4 md:mt-6 rounded-full"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                <div class="glass p-6 md:p-8 rounded-2xl md:rounded-3xl hover:bg-white/5 transition-all duration-300 group" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-12 h-12 md:w-16 md:h-16 rounded-xl md:rounded-2xl bg-blue-500/10 flex items-center justify-center mb-4 md:mb-6 group-hover:scale-110 transition-transform duration-300 border border-blue-500/20">
                        <span class="material-icons-round text-3xl md:text-4xl text-blue-400">cloud_upload</span>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold text-white mb-2 md:mb-3">Arsip Terpusat</h4>
                    <p class="text-slate-400 text-xs md:text-sm leading-relaxed">Tidak ada lagi dokumen hilang. Upload TOR, RAB, dan bukti LPJ dalam satu cloud storage yang aman dan mudah diakses kembali.</p>
                </div>
                <div class="glass p-6 md:p-8 rounded-2xl md:rounded-3xl hover:bg-white/5 transition-all duration-300 group" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-12 h-12 md:w-16 md:h-16 rounded-xl md:rounded-2xl bg-emerald-500/10 flex items-center justify-center mb-4 md:mb-6 group-hover:scale-110 transition-transform duration-300 border border-emerald-500/20">
                        <span class="material-icons-round text-3xl md:text-4xl text-emerald-400">timeline</span>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold text-white mb-2 md:mb-3">Tracking Transparan</h4>
                    <p class="text-slate-400 text-xs md:text-sm leading-relaxed">Pantau posisi usulan Anda secara real-time. Ketahui kapan disetujui, kapan cair, dan kapan harus setor LPJ.</p>
                </div>
                <div class="glass p-6 md:p-8 rounded-2xl md:rounded-3xl hover:bg-white/5 transition-all duration-300 group" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-12 h-12 md:w-16 md:h-16 rounded-xl md:rounded-2xl bg-amber-500/10 flex items-center justify-center mb-4 md:mb-6 group-hover:scale-110 transition-transform duration-300 border border-amber-500/20">
                        <span class="material-icons-round text-3xl md:text-4xl text-amber-400">verified_user</span>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold text-white mb-2 md:mb-3">Audit Compliance</h4>
                    <p class="text-slate-400 text-xs md:text-sm leading-relaxed">Sistem memvalidasi nominal RAB vs LPJ secara otomatis. Meminimalisir kesalahan hitung dan temuan audit.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="alur" class="py-16 md:py-24 bg-slate-900/50 relative z-10 border-y border-white/5">
        <div class="max-w-7xl mx-auto px-4 md:px-6">
            <div class="text-center mb-12 md:mb-20" data-aos="fade-up">
                <h2 class="text-xs md:text-sm font-bold text-emerald-400 uppercase tracking-widest mb-2 md:mb-3">Alur Kerja</h2>
                <h3 class="text-2xl md:text-4xl font-bold text-white">Sederhana & Terstruktur</h3>
            </div>
            <div class="relative">
                <div class="hidden md:block absolute top-1/2 left-0 w-full h-0.5 bg-gradient-to-r from-blue-900 via-blue-600 to-emerald-600 -translate-y-1/2 rounded opacity-30"></div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 md:gap-12">
                    <div class="relative text-center group" data-aos="fade-up" data-aos-delay="100">
                        <div class="w-16 h-16 md:w-20 md:h-20 mx-auto bg-[#020617] border-2 border-blue-600 rounded-full flex items-center justify-center mb-4 md:mb-6 relative z-10 shadow-[0_0_30px_rgba(37,99,235,0.3)] group-hover:scale-110 transition-transform duration-300">
                            <span class="material-icons-round text-2xl md:text-3xl text-blue-500">edit_note</span>
                            <div class="absolute -top-2 -right-2 w-6 h-6 md:w-8 md:h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xs md:text-sm">1</div>
                        </div>
                        <h4 class="text-base md:text-lg font-bold text-white mb-2">Usulan Baru</h4>
                        <p class="text-[11px] md:text-xs text-slate-400 px-2 md:px-4 leading-relaxed">Pengusul mengisi KAK, IKU, & RAB melalui wizard 3 langkah.</p>
                    </div>
                    <div class="relative text-center group" data-aos="fade-up" data-aos-delay="200">
                        <div class="w-16 h-16 md:w-20 md:h-20 mx-auto bg-[#020617] border-2 border-indigo-500 rounded-full flex items-center justify-center mb-4 md:mb-6 relative z-10 shadow-[0_0_30px_rgba(99,102,241,0.3)] group-hover:scale-110 transition-transform duration-300">
                            <span class="material-icons-round text-2xl md:text-3xl text-indigo-400">fact_check</span>
                            <div class="absolute -top-2 -right-2 w-6 h-6 md:w-8 md:h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-xs md:text-sm">2</div>
                        </div>
                        <h4 class="text-base md:text-lg font-bold text-white mb-2">Verifikasi & Approval</h4>
                        <p class="text-[11px] md:text-xs text-slate-400 px-2 md:px-4 leading-relaxed">Verifikator cek dokumen, dilanjutkan persetujuan PPK & WD2.</p>
                    </div>
                    <div class="relative text-center group" data-aos="fade-up" data-aos-delay="300">
                        <div class="w-16 h-16 md:w-20 md:h-20 mx-auto bg-[#020617] border-2 border-emerald-500 rounded-full flex items-center justify-center mb-4 md:mb-6 relative z-10 shadow-[0_0_30px_rgba(16,185,129,0.3)] group-hover:scale-110 transition-transform duration-300">
                            <span class="material-icons-round text-2xl md:text-3xl text-emerald-400">payments</span>
                            <div class="absolute -top-2 -right-2 w-6 h-6 md:w-8 md:h-8 bg-emerald-600 rounded-full flex items-center justify-center text-white font-bold text-xs md:text-sm">3</div>
                        </div>
                        <h4 class="text-base md:text-lg font-bold text-white mb-2">Pencairan Dana</h4>
                        <p class="text-[11px] md:text-xs text-slate-400 px-2 md:px-4 leading-relaxed">Dana ditransfer. Kegiatan dilaksanakan sesuai jadwal.</p>
                    </div>
                    <div class="relative text-center group" data-aos="fade-up" data-aos-delay="400">
                        <div class="w-16 h-16 md:w-20 md:h-20 mx-auto bg-[#020617] border-2 border-amber-500 rounded-full flex items-center justify-center mb-4 md:mb-6 relative z-10 shadow-[0_0_30px_rgba(245,158,11,0.3)] group-hover:scale-110 transition-transform duration-300">
                            <span class="material-icons-round text-2xl md:text-3xl text-amber-400">receipt_long</span>
                            <div class="absolute -top-2 -right-2 w-6 h-6 md:w-8 md:h-8 bg-amber-600 rounded-full flex items-center justify-center text-white font-bold text-xs md:text-sm">4</div>
                        </div>
                        <h4 class="text-base md:text-lg font-bold text-white mb-2">Pelaporan (LPJ)</h4>
                        <p class="text-[11px] md:text-xs text-slate-400 px-2 md:px-4 leading-relaxed">Upload bukti transaksi. Verifikasi akhir oleh Bendahara.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-[#020617] border-t border-white/10 py-8 md:py-12 relative z-10">
        <div class="max-w-7xl mx-auto px-4 md:px-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6 md:gap-8 mb-6 md:mb-8">
                <div class="flex flex-col items-center md:items-start text-center md:text-left">
                    <div class="flex items-center gap-2 md:gap-3 mb-2">
                        <img src="/logo_pnj.png" alt="Logo" class="w-6 h-6 md:w-8 md:h-8 brightness-200">
                        <span class="font-bold text-lg md:text-xl text-white tracking-tight">SATRIA System</span>
                    </div>
                    <p class="text-xs md:text-sm text-slate-500">Sistem Administrasi Terintegrasi & Akuntabel.</p>
                </div>
                <div class="flex flex-wrap justify-center md:justify-end gap-4 md:gap-8 text-xs md:text-sm font-medium text-slate-400">
                    <a href="#" class="hover:text-blue-400 transition-colors">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-blue-400 transition-colors">Syarat Penggunaan</a>
                    <a href="mailto:it@pnj.ac.id" class="hover:text-blue-400 transition-colors flex items-center gap-1 md:gap-2">
                        <span class="material-icons-round text-sm">email</span> Hubungi IT
                    </a>
                </div>
            </div>
            <div class="border-t border-white/5 pt-6 text-center md:text-left flex flex-col md:flex-row justify-between items-center text-[10px] md:text-xs text-slate-600 gap-2">
                <p>&copy; 2025 Politeknik Negeri Jakarta. All rights reserved.</p>
                <p>Designed for Excellence.</p>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ once: true, offset: 50, duration: 800, easing: 'ease-out-cubic' });
        window.addEventListener('scroll', function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) nav.classList.add('shadow-lg', 'shadow-blue-900/10');
            else nav.classList.remove('shadow-lg', 'shadow-blue-900/10');
        });
    </script>
</body>
</html>