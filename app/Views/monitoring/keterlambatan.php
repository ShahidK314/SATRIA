<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Keterlambatan LPJ - SATRIA</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="m-4 md:m-5">
        <div class="bg-white rounded-xl shadow-md p-4 md:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-2">
                <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-clock text-red-500 mr-2"></i>
                    Monitoring Keterlambatan LPJ
                </h1>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 mb-8">
                <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 shadow-sm">
                    <div class="flex items-center">
                        <div class="bg-blue-500 text-white w-12 h-12 flex items-center justify-center rounded-full shrink-0">
                            <i class="fas fa-file-alt text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs md:text-sm text-gray-600 font-bold uppercase tracking-wide">Total LPJ</p>
                            <p class="text-2xl md:text-3xl font-extrabold text-blue-600"><?php echo $statistik['total_lpj'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-red-50 p-4 rounded-xl border border-red-100 shadow-sm">
                    <div class="flex items-center">
                        <div class="bg-red-500 text-white w-12 h-12 flex items-center justify-center rounded-full shrink-0">
                            <i class="fas fa-exclamation-triangle text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs md:text-sm text-gray-600 font-bold uppercase tracking-wide">LPJ Terlambat</p>
                            <p class="text-2xl md:text-3xl font-extrabold text-red-600"><?php echo $statistik['total_terlambat'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-100 shadow-sm sm:col-span-2 md:col-span-1">
                    <div class="flex items-center">
                        <div class="bg-yellow-500 text-white w-12 h-12 flex items-center justify-center rounded-full shrink-0">
                            <i class="fas fa-chart-line text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs md:text-sm text-gray-600 font-bold uppercase tracking-wide">Rata-rata Telat</p>
                            <p class="text-2xl md:text-3xl font-extrabold text-yellow-600"><?php echo number_format($statistik['rata_rata_keterlambatan'] ?? 0, 0); ?> hari</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6 bg-slate-50 p-4 rounded-lg border border-slate-200">
                <form method="GET" action="" class="flex flex-col md:flex-row gap-3 md:gap-4 w-full items-stretch md:items-center">
                    
                    <div class="relative w-full md:w-[45%]">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fas fa-search text-sm"></i>
                        </span>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" 
                               placeholder="Cari Judul Kegiatan..." 
                               class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm shadow-sm bg-white">
                    </div>

                    <div class="relative w-full md:w-[40%]">
                        <select name="pengusul" class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm appearance-none bg-white shadow-sm cursor-pointer truncate">
                            <option value="">Semua Pengusul</option>
                            <?php foreach ($listPengusul as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo (($_GET['pengusul'] ?? '') == $p['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i class="fas fa-user text-sm"></i>
                        </span>
                    </div>

                    <div class="w-full md:flex-1 flex gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center justify-center flex-1 md:flex-none">
                            <i class="fas fa-filter mr-1 text-xs"></i> Filter
                        </button>
                        <?php if(!empty($_GET['q']) || !empty($_GET['pengusul'])): ?>
                            <a href="/monitoring/keterlambatan" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center justify-center flex-1 md:flex-none text-center">
                                Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="overflow-x-auto w-full custom-scrollbar">
                    <table class="min-w-[900px] w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16">No</th>
                                <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/3">Judul Usulan</th>
                                <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pengusul</th>
                                <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pengajuan</th>
                                <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Deadline</th>
                                <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Keterlambatan</th>
                                <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($keterlambatan_lpj)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-search text-4xl text-gray-300 mb-3"></i>
                                            <p class="text-sm font-medium">Tidak ada data keterlambatan yang ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                    $no = ($pager['current'] - 1) * $pager['limit'] + 1; 
                                    foreach ($keterlambatan_lpj as $lpj): 
                                ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-gray-600 font-medium"><?php echo $no++; ?></td>
                                        <td class="px-4 md:px-6 py-4 text-gray-900">
                                            <div class="font-bold leading-snug whitespace-normal min-w-[200px]"><?php echo htmlspecialchars($lpj['judul_usulan']); ?></div>
                                        </td>
                                        <td class="px-4 md:px-6 py-4 text-gray-700 whitespace-nowrap">
                                            <div class="font-medium flex items-center">
                                                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px] font-bold mr-2 shrink-0">
                                                    <?php echo strtoupper(substr($lpj['nama_pengusul'], 0, 1)); ?>
                                                </div>
                                                <?php echo htmlspecialchars($lpj['nama_pengusul']); ?>
                                            </div>
                                        </td>
                                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-gray-500 text-xs">
                                            <?php echo date('d/m/Y', strtotime($lpj['tanggal_pengajuan'])); ?>
                                        </td>
                                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-gray-500 text-xs font-bold">
                                            <?php echo date('d/m/Y', strtotime($lpj['tanggal_deadline'])); ?>
                                        </td>
                                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                                <i class="fas fa-exclamation-triangle mr-1 text-[10px]"></i>
                                                <?php echo $lpj['hari_keterlambatan']; ?> hari
                                            </span>
                                        </td>
                                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-center">
                                            <?php
                                            $statusConfig = [
                                                'Draft' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-edit', 'border' => 'border-gray-200'],
                                                'Submitted' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-paper-plane', 'border' => 'border-blue-200'],
                                                'Verified' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-check-circle', 'border' => 'border-green-200'],
                                                'Rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'fa-times-circle', 'border' => 'border-red-200'],
                                            ];
                                            $conf = $statusConfig[$lpj['status_lpj']] ?? $statusConfig['Draft'];
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] uppercase tracking-wide font-bold <?php echo $conf['bg'] . ' ' . $conf['text'] . ' border ' . $conf['border']; ?>">
                                                <i class="fas <?php echo $conf['icon']; ?> mr-1"></i>
                                                <?php echo $lpj['status_lpj']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($pager['total_pages'] > 1): ?>
            <div class="flex flex-col md:flex-row items-center justify-between mt-6 gap-4">
                <div class="text-xs text-gray-500 text-center md:text-left">
                    Menampilkan <strong><?php echo (($pager['current'] - 1) * $pager['limit']) + 1; ?></strong> 
                    s/d <strong><?php echo min($pager['current'] * $pager['limit'], $pager['total_items']); ?></strong> 
                    dari <strong><?php echo $pager['total_items']; ?></strong> data
                </div>
                <div class="flex flex-wrap justify-center gap-1">
                    <?php if ($pager['current'] > 1): ?>
                        <a href="?page=<?php echo $pager['current'] - 1; ?>&q=<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>&pengusul=<?php echo htmlspecialchars($_GET['pengusul'] ?? ''); ?>" 
                           class="px-3 py-1.5 bg-white border border-gray-300 rounded-md text-xs font-bold text-gray-700 hover:bg-gray-50 shadow-sm transition-colors">
                            Prev
                        </a>
                    <?php endif; ?>

                    <?php 
                    $start = max(1, $pager['current'] - 2);
                    $end = min($pager['total_pages'], $start + 4);
                    if ($end - $start < 4) $start = max(1, $end - 4);
                    for ($i = $start; $i <= $end; $i++): 
                    ?>
                        <a href="?page=<?php echo $i; ?>&q=<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>&pengusul=<?php echo htmlspecialchars($_GET['pengusul'] ?? ''); ?>" 
                           class="px-3 py-1.5 border rounded-md text-xs font-bold transition-colors <?php echo ($i == $pager['current']) ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 shadow-sm'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pager['current'] < $pager['total_pages']): ?>
                        <a href="?page=<?php echo $pager['current'] + 1; ?>&q=<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>&pengusul=<?php echo htmlspecialchars($_GET['pengusul'] ?? ''); ?>" 
                           class="px-3 py-1.5 bg-white border border-gray-300 rounded-md text-xs font-bold text-gray-700 hover:bg-gray-50 shadow-sm transition-colors">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>
<?php include __DIR__.'/../partials/footer.php'; ?>