<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Access Denied - SATRIA</title>
    <link href="/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col items-center justify-center text-center px-4 md:px-6">
    
    <div class="relative mb-6">
        <div class="text-[6rem] sm:text-[8rem] font-black text-slate-200 leading-none select-none tracking-tighter">403</div>
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center shadow-inner">
                <span class="material-icons text-3xl sm:text-4xl">lock</span>
            </div>
        </div>
    </div>

    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 mb-2">Akses Dibatasi</h1>
    <p class="text-slate-500 text-xs sm:text-sm max-w-xs sm:max-w-md mx-auto mb-8 leading-relaxed">
        Maaf, Anda tidak memiliki izin (Role) yang sesuai untuk mengakses halaman atau sumber daya ini. Silakan hubungi Administrator jika ini adalah kesalahan.
    </p>

    <div class="flex flex-col-reverse sm:flex-row gap-3 sm:gap-4 w-full sm:w-auto max-w-xs mx-auto">
        <a href="javascript:history.back()" class="w-full sm:w-auto px-6 py-3 bg-white border border-slate-300 text-slate-600 font-bold rounded-lg hover:bg-slate-50 transition-all text-sm shadow-sm">
            Kembali
        </a>
        <a href="/dashboard" class="w-full sm:w-auto px-6 py-3 bg-slate-900 text-white font-bold rounded-lg shadow-lg hover:bg-slate-800 hover:-translate-y-0.5 transition-all text-sm flex items-center justify-center">
            <span class="material-icons text-sm mr-2">dashboard</span> Ke Dashboard
        </a>
    </div>

    <div class="mt-12 text-[9px] sm:text-[10px] text-slate-400 font-mono uppercase tracking-widest">
        SATRIA Security System • Error: 403_FORBIDDEN
    </div>
</body>
</html>