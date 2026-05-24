<?php include __DIR__.'/../partials/sidebar.php'; ?>

    <?php
    // Data dari controller
    $isEditMode = isset($isEdit) && $isEdit;
    $actionUrl = '/usulan/process-store';
    $title = $isEditMode ? 'Edit Usulan Kegiatan' : 'Pengajuan Telaah Baru';

    // Data yang perlu dipastikan tersedia
    $ikuDetails = $ikuDetails ?? []; 
    $rabDetails = $rabDetails ?? []; 
    $masterKategori = $masterKategori ?? [];
    $masterSatuan = $masterSatuan ?? [];
    $masterIku = $masterIku ?? [];

    // Hitung index awal untuk item baru
    $ikuItemIndex = count($ikuDetails);
    
    // Prepare KAK multi-fields for display
    $metode_arr = $usulan['metode_array'] ?? [''];
    if (count($metode_arr) === 0) $metode_arr = [''];
    $tahapan_arr = $usulan['tahapan_array'] ?? [''];
    if (count($tahapan_arr) === 0) $tahapan_arr = [''];
    
    // Default indikator
    $indikator_arr = $usulan['indikator_array'] ?? [];
    $isDefaultRow = false;
    if (!empty($indikator_arr) && count($indikator_arr) === 1) {
        if (empty($indikator_arr[0]['indikator']) && ($indikator_arr[0]['bulan_target'] == 'Januari' || empty($indikator_arr[0]['bulan_target']))) {
            $isDefaultRow = true;
        }
    }
    if (empty($indikator_arr) || $isDefaultRow) {
        $indikator_arr = [['indikator' => '', 'bulan_target' => '', 'bobot' => '']];
    }

    // Calculate current Grand Total for display
    $currentGrandTotal = 0;
    foreach ($rabDetails as $item) {
        $currentGrandTotal += $item['total'] ?? 0;
    }

    // Helper fungsi untuk mengambil nilai RAB multi-faktor
    function getFactor1Volume($item) { return $item['volume_factor_1'] ?? 0; }
    function getFactor2Volume($item) { return $item['volume_factor_2'] ?? 0; }
    
    // Helper Satuan Custom (Edit Mode)
    function getFactor1SatuanId($item) { 
        if (empty($item['satuan_factor_1_id']) && !empty($item['satuan_factor_1_custom'])) return 'NEW';
        return $item['satuan_factor_1_id'] ?? ''; 
    }
    function getFactor1SatuanCustom($item) { return $item['satuan_factor_1_custom'] ?? ''; }

    function getFactor2SatuanId($item) { 
        if (empty($item['satuan_factor_2_id']) && !empty($item['satuan_factor_2_custom'])) return 'NEW';
        return $item['satuan_factor_2_id'] ?? ''; 
    }
    function getFactor2SatuanCustom($item) { return $item['satuan_factor_2_custom'] ?? ''; }

    function getFinalVolume($item) { return $item['volume'] ?? 0; }
    function getFinalSatuanId($item) { return $item['satuan_id'] ?? ''; }
    
    // Helper function for PHP number formatting
    if (!function_exists('number_format_id')) {
        function number_format_id($number) {
            return number_format($number, 0, ',', '.');
        }
    }
    ?>

    <div class="m-4 md:m-5">
        <div class="mb-8 md:mb-10 text-center">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight"><?php echo $title; ?></h1>
            <p class="text-slate-500 mt-2 text-sm md:text-base">Lengkapi data substansi kegiatan (3 Langkah: KAK - IKU - RAB).</p>
        </div>

        <div class="flex items-center justify-center mb-8 md:mb-12 overflow-hidden px-2">
            <style>
                .step-indicator { display: flex; align-items: center; flex-direction: column; position: relative; z-index: 10; width: 80px; md:width: 100px; }
                .step-circle { width: 32px; height: 32px; md:width: 40px; md:height: 40px; border-radius: 50%; background-color: #e2e8f0; color: #64748b; display: flex; justify-content: center; align-items: center; font-weight: bold; transition: all 0.3s; border: 3px solid #e2e8f0; font-size: 14px; }
                .step-label { margin-top: 8px; font-size: 10px; md:font-size: 11px; font-weight: 600; text-align: center; color: #64748b; transition: color 0.3s; line-height: 1.2; }
                .step-indicator.active .step-circle { background-color: #2563eb; color: white; border-color: #bfdbfe; box-shadow: 0 0 0 4px #bfdbfe; }
                .step-indicator.active .step-label { color: #1e293b; }
                .step-indicator.completed .step-circle { background-color: #10b981; color: white; border-color: #059669; }
                .step-indicator.completed .step-label { color: #1e293b; }
                .step-line { flex-grow: 1; height: 3px; background-color: #e2e8f0; margin: 0 -15px; md:margin: 0 -20px; transition: background-color 0.3s; min-width: 30px; }
                .step-indicator:nth-child(even).completed ~ .step-line { background-color: #10b981; }
                @media (min-width: 768px) {
                    .step-circle { width: 40px; height: 40px; font-size: 16px; }
                    .step-label { font-size: 11px; }
                }
            </style>
            <div class="flex items-center w-full max-w-md justify-center">
                <div class="step-indicator active" data-step="1"><div class="step-circle">1</div><div class="step-label">KAK</div></div>
                <div class="step-line"></div>
                <div class="step-indicator" data-step="2"><div class="step-circle">2</div><div class="step-label">IKU</div></div>
                <div class="step-line"></div>
                <div class="step-indicator" data-step="3"><div class="step-circle">3</div><div class="step-label">RAB & Simpan</div></div>
            </div>
        </div>
        
        <form method="POST" action="<?php echo $actionUrl; ?>" id="usulanForm" class="bg-white p-4 md:p-8 rounded-xl shadow-lg border border-slate-200">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <?php if ($isEditMode): ?>
                <input type="hidden" name="id" value="<?php echo $usulan['id']; ?>">
            <?php endif; ?>
            
            <div class="step-content" data-step="1">
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 mb-4 md:mb-6">1. Detail KAK</h2>
                
                <div class="mb-4 md:mb-6">
                    <label for="nama_kegiatan" class="block text-sm font-medium text-slate-700 mb-1">Nama Kegiatan </label>
                    <input type="text" id="nama_kegiatan" name="nama_kegiatan" value="<?php echo htmlspecialchars($usulan['nama_kegiatan'] ?? ''); ?>" required class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="mb-4 md:mb-6">
                    <label for="gambaran_umum" class="block text-sm font-medium text-slate-700 mb-1">Gambaran Umum</label>
                    <textarea id="gambaran_umum" name="gambaran_umum" rows="4" required class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($usulan['gambaran_umum'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-4 md:mb-6">
                    <label for="penerima_manfaat" class="block text-sm font-medium text-slate-700 mb-1">Penerima Manfaat </label>
                    <input type="text" id="penerima_manfaat" name="penerima_manfaat" value="<?php echo htmlspecialchars($usulan['penerima_manfaat'] ?? ''); ?>" required class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="mb-4 md:mb-6">
                    <label for="strategi_pencapaian_keluaran" class="block text-sm font-medium text-slate-700 mb-1">Strategi Pencapaian Keluaran</label>
                    <textarea id="strategi_pencapaian_keluaran" name="strategi_pencapaian_keluaran" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($usulan['strategi_pencapaian_keluaran'] ?? ''); ?></textarea>
                </div>
                
                <h3 class="font-bold text-slate-700 mb-3">Metode Pelaksanaan</h3>
                <div id="metode-container" class="space-y-3 mb-4 md:mb-6">
                    <?php 
                    foreach ($metode_arr as $i => $metode): 
                        $hideDelete = ($i === 0 && count($metode_arr) === 1 && empty($metode));
                    ?>
                    <div class="flex gap-2 metode-item">
                        <input type="text" name="metode[]" value="<?php echo htmlspecialchars($metode); ?>" class="flex-1 px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm md:text-base" placeholder="Contoh: Kuliah Umum / Workshop">
                        <button type="button" onclick="removeMultiInput(this, 'metode')" class="text-rose-600 hover:text-rose-800 p-2 <?php echo $hideDelete ? 'hidden' : ''; ?>"><span class="material-icons text-lg">delete</span></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" onclick="addMultiInput('metode')" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-bold mb-6">
                    <span class="material-icons text-sm mr-1">add_circle</span> Tambah Metode
                </button>

                <h3 class="font-bold text-slate-700 mb-3">Tahapan Pelaksanaan</h3>
                <div id="tahapan-container" class="space-y-3 mb-4 md:mb-6">
                    <?php 
                    foreach ($tahapan_arr as $i => $tahapan): 
                        $hideDelete = ($i === 0 && count($tahapan_arr) === 1 && empty($tahapan));
                    ?>
                    <div class="flex gap-2 tahapan-item">
                        <input type="text" name="tahapan[]" value="<?php echo htmlspecialchars($tahapan); ?>" class="flex-1 px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm md:text-base" placeholder="Contoh: Pra-Acara: Pembuatan TOR">
                        <button type="button" onclick="removeMultiInput(this, 'tahapan')" class="text-rose-600 hover:text-rose-800 p-2 <?php echo $hideDelete ? 'hidden' : ''; ?>"><span class="material-icons text-lg">delete</span></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" onclick="addMultiInput('tahapan')" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-bold mb-6">
                    <span class="material-icons text-sm mr-1">add_circle</span> Tambah Tahapan
                </button>
                
                <h3 class="font-bold text-slate-700 mb-3">Kurun Waktu Pelaksanaan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-6 bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div>
                        <label for="tanggal_mulai_kak" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" id="tanggal_mulai_kak" name="tanggal_mulai" value="<?php echo htmlspecialchars($usulan['tanggal_mulai'] ?? ''); ?>" required class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="tanggal_selesai_kak" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Selesai</label>
                        <input type="date" id="tanggal_selesai_kak" name="tanggal_selesai" value="<?php echo htmlspecialchars($usulan['tanggal_selesai'] ?? ''); ?>" required class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <h3 class="font-bold text-slate-700 mb-3">Indikator Kinerja (KPI) & Bobot (Total 100%)</h3>
                <div class="overflow-x-auto w-full border border-slate-300 rounded-lg mb-4 custom-scrollbar">
                    <table class="w-full text-sm min-w-[650px]">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="px-4 py-2 text-left w-2/5 text-slate-600">Indikator Keberhasilan</th>
                                <th class="px-4 py-2 text-center w-1/3 text-slate-600">Bulan Target</th>
                                <th class="px-4 py-2 text-center w-1/5 text-slate-600">Bobot (%)</th>
                                <th class="px-4 py-2 text-center w-auto">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kpi-items" class="divide-y divide-slate-200">
                            <?php 
                            $kpiIdx = 0;
                            foreach ($indikator_arr as $kpi): ?>
                                <tr class="kpi-item" data-index="<?php echo $kpiIdx; ?>">
                                    <td class="px-4 py-2">
                                        <input type="text" name="indikator_kinerja[<?php echo $kpiIdx; ?>][indikator]" value="<?php echo htmlspecialchars($kpi['indikator']); ?>" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Cth: 90% target peserta" required>
                                    </td>
                                    <td class="px-4 py-2">
                                        <select name="indikator_kinerja[<?php echo $kpiIdx; ?>][bulan_target]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-sm kpi-month-select focus:ring-blue-500 focus:border-blue-500" required>
                                            <option value="" <?php echo empty($kpi['bulan_target']) ? 'selected' : ''; ?> disabled hidden>-- Pilih --</option>
                                            <?php 
                                            $months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                                            foreach ($months as $m) {
                                                $selected = (isset($kpi['bulan_target']) && $kpi['bulan_target'] == $m) ? 'selected' : '';
                                                echo "<option value='{$m}' {$selected}>{$m}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" name="indikator_kinerja[<?php echo $kpiIdx; ?>][bobot]" value="<?php echo htmlspecialchars($kpi['bobot']); ?>" min="1" max="100" class="w-full text-center border-slate-300 rounded-md shadow-sm p-2 text-sm kpi-bobot-input focus:ring-blue-500 focus:border-blue-500" required>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button" onclick="removeKpiItem(this)" class="text-rose-600 hover:text-rose-800 p-1"><span class="material-icons text-lg">delete</span></button>
                                    </td>
                                </tr>
                            <?php $kpiIdx++; endforeach; ?>
                        </tbody>
                        <tfoot class="bg-slate-50 border-t border-slate-200">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-right">
                                    <button type="button" onclick="addKpiItem()" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-xs md:text-sm font-bold rounded-lg hover:bg-emerald-700">
                                        <span class="material-icons mr-1 text-base">add</span> Tambah KPI
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span id="kpi-total-bobot" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg font-extrabold text-xs md:text-sm">0%</span>
                                </td>
                                <td class="px-4 py-3 text-center"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="flex justify-end mt-8">
                    <button type="button" class="btn-next w-full sm:w-auto bg-blue-600 text-white font-bold px-6 py-3 rounded-lg hover:bg-blue-700 flex items-center justify-center">Lanjut ke IKU <span class="material-icons text-sm ml-2">arrow_forward</span></button>
                </div>
            </div>

            <div class="step-content hidden" data-step="2">
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 mb-4 md:mb-6">2. Indikator Kinerja Utama (IKU)</h2>
                <p class="text-slate-500 mb-4 text-sm md:text-base">Pilih IKU yang relevan dan tetapkan target kinerjanya.</p>
                
                <div class="mb-6">
                    <div class="overflow-x-auto w-full border border-slate-300 rounded-lg custom-scrollbar">
                        <table class="w-full text-sm min-w-[500px]">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="px-4 py-2 text-left w-3/4 text-slate-600">Pilih IKU</th>
                                    <th class="px-4 py-2 text-center w-1/4 text-slate-600">Target</th>
                                    <th class="px-4 py-2 text-center w-auto">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="iku-items" class="divide-y divide-slate-200">
                                <?php 
                                $currentIkuIndex = 0;
                                foreach ($ikuDetails as $item):
                                ?>
                                    <tr data-index="<?php echo $currentIkuIndex; ?>" class="iku-item">
                                        <td class="px-4 py-2">
                                            <select name="iku_data[<?php echo $currentIkuIndex; ?>][iku_id]" class="w-full border-slate-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" required>
                                                <option value="">-- Pilih IKU --</option>
                                                <?php foreach ($masterIku as $iku): ?>
                                                    <option value="<?php echo $iku['id']; ?>" title="<?php echo htmlspecialchars($iku['deskripsi_iku']); ?>" <?php echo ($iku['id'] == $item['iku_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($iku['deskripsi_iku']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" name="iku_data[<?php echo $currentIkuIndex; ?>][target]" value="<?php echo htmlspecialchars($item['target'] ?? ''); ?>" class="w-full text-center border-slate-300 rounded-md shadow-sm p-2 iku-target-input focus:ring-blue-500 focus:border-blue-500" placeholder="Cth: 100%" required>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <button type="button" onclick="removeItemIKU(this)" class="text-rose-600 hover:text-rose-800 p-1">
                                                <span class="material-icons text-lg">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php 
                                    $currentIkuIndex++;
                                endforeach; 
                                ?>
                            </tbody>
                            <tfoot class="bg-slate-50 border-t border-slate-200">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right">
                                        <button type="button" onclick="addItemIKU()" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-xs md:text-sm font-bold rounded-lg hover:bg-emerald-700">
                                            <span class="material-icons mr-1 text-base">add</span> Tambah IKU
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-between gap-4">
                    <button type="button" class="btn-prev w-full sm:w-auto bg-slate-400 text-white font-bold px-6 py-3 rounded-lg hover:bg-slate-500 flex items-center justify-center"><span class="material-icons text-sm mr-2">arrow_back</span> Kembali</button>
                    <button type="button" class="btn-next w-full sm:w-auto bg-blue-600 text-white font-bold px-6 py-3 rounded-lg hover:bg-blue-700 flex items-center justify-center">Lanjut ke RAB <span class="material-icons text-sm ml-2">arrow_forward</span></button>
                </div>
            </div>

            <div class="step-content hidden" data-step="3">
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 mb-6">3. Rencana Anggaran Biaya (RAB)</h2>
                
                <?php $rabGlobalIndex = 0; ?>
                <?php foreach ($masterKategori as $category): 
                    $catId = $category['id'];
                    $catName = htmlspecialchars($category['nama_kategori']);
                    $categoryRows = array_filter($rabDetails, fn($r) => ($r['kategori_id'] ?? 0) == $catId);
                    $subtotal = array_sum(array_column($categoryRows, 'total'));
                ?>
                <div class="mb-8 p-3 md:p-4 border border-slate-200 rounded-xl bg-slate-50/50">
                    <h3 class="text-lg md:text-xl font-bold text-slate-700 mb-3 flex items-center">
                        <span class="material-icons text-lg mr-2 text-blue-600">bookmark</span> <?= $catName ?>
                    </h3>
                    
                    <div class="overflow-x-auto w-full custom-scrollbar pb-2">
                        <table class="w-full text-sm mb-3 rab-table min-w-[1100px]" data-category-id="<?= $catId ?>">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider whitespace-nowrap min-w-[200px]">Uraian</th>
                                    <th class="px-2 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider whitespace-nowrap w-[80px]">Vol 1</th>
                                    <th class="px-2 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider whitespace-nowrap w-[110px]">Sat 1</th>
                                    <th class="px-1 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-[30px]"></th> 
                                    <th class="px-2 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider whitespace-nowrap w-[80px]">Vol 2</th>
                                    <th class="px-2 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider whitespace-nowrap w-[110px]">Sat 2</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider whitespace-nowrap w-[120px]">Tot. Vol</th>
                                    <th class="px-1 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-[30px]"></th> 
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider whitespace-nowrap w-[150px]">Harga (Rp)</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider whitespace-nowrap w-[150px]">Total (Rp)</th>
                                    <th class="px-2 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider whitespace-nowrap w-[50px]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="rabBody_<?= $catId ?>" class="divide-y divide-slate-200 bg-white">
                                <?php 
                                    foreach ($categoryRows as $item):
                                        $vol1 = getFactor1Volume($item);
                                        $vol2 = getFactor2Volume($item);
                                        
                                        // HANDLE CUSTOM SATUAN
                                        $sat1_id = getFactor1SatuanId($item);
                                        $sat1_custom = getFactor1SatuanCustom($item);
                                        $sat2_id = getFactor2SatuanId($item);
                                        $sat2_custom = getFactor2SatuanCustom($item);
                                        
                                        $total_vol = getFinalVolume($item);
                                        $sat_final_id = getFinalSatuanId($item);
                                    ?>
                                    <tr class="rab-row" data-index="<?php echo $rabGlobalIndex; ?>">
                                        <td class="px-4 py-2 border-r border-slate-100">
                                            <input type="text" name="rab_data[<?php echo $rabGlobalIndex; ?>][deskripsi]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-sm focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($item['deskripsi']); ?>" required>
                                            <input type="hidden" name="rab_data[<?php echo $rabGlobalIndex; ?>][kategori_id]" value="<?php echo $catId; ?>">
                                            <input type="hidden" name="rab_data[<?php echo $rabGlobalIndex; ?>][id]" value="<?php echo $item['id'] ?? 0; ?>">
                                        </td>
                                        
                                        <td class="px-2 py-2">
                                            <input type="number" name="rab_data[<?php echo $rabGlobalIndex; ?>][volume_factor_1]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-sm text-right rab-volume-factor-1 focus:ring-blue-500 focus:border-blue-500" value="<?php echo $vol1; ?>" min="0" step="0.01">
                                        </td>
                                        <td class="px-2 py-2 border-r border-slate-100">
                                            <select name="rab_data[<?php echo $rabGlobalIndex; ?>][satuan_factor_1_id]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-xs md:text-sm rab-satuan-factor-1 focus:ring-blue-500 focus:border-blue-500" required onchange="toggleSatuanInput(this)">
                                                <option value="" <?php echo empty($sat1_id) ? 'selected' : ''; ?> disabled hidden>-- Pilih Satuan --</option>
                                                <?php foreach ($masterSatuan as $s): ?>
                                                    <option value="<?php echo $s['id']; ?>" <?php echo ($sat1_id == $s['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['nama_satuan']); ?></option>
                                                <?php endforeach; ?>
                                                <option value="NEW" class="font-bold text-blue-600" <?php echo ($sat1_id === 'NEW') ? 'selected' : ''; ?>>+ Input Baru</option>
                                            </select>
                                            <input type="text" name="rab_data[<?php echo $rabGlobalIndex; ?>][satuan_factor_1_custom]" value="<?php echo htmlspecialchars($sat1_custom); ?>" class="satuan-custom-input w-full border-slate-300 rounded-md shadow-sm p-2 text-xs mt-1 <?php echo ($sat1_id !== 'NEW') ? 'hidden' : ''; ?> focus:ring-blue-500" placeholder="Nama Satuan">
                                        </td>
                                        
                                        <td class="px-1 py-2 text-center text-slate-300 font-bold">x</td>

                                        <td class="px-2 py-2">
                                            <input type="number" name="rab_data[<?php echo $rabGlobalIndex; ?>][volume_factor_2]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-sm text-right rab-volume-factor-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo $vol2; ?>" min="0" step="0.01">
                                        </td>
                                        <td class="px-2 py-2 border-r border-slate-100">
                                            <select name="rab_data[<?php echo $rabGlobalIndex; ?>][satuan_factor_2_id]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-xs md:text-sm rab-satuan-factor-2 focus:ring-blue-500 focus:border-blue-500" onchange="toggleSatuanInput(this)">
                                                <option value="" <?php echo empty($sat2_id) ? 'selected' : ''; ?> disabled hidden>-- Pilih Satuan --</option>
                                                <?php foreach ($masterSatuan as $s): ?>
                                                    <option value="<?php echo $s['id']; ?>" <?php echo ($sat2_id == $s['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['nama_satuan']); ?></option>
                                                <?php endforeach; ?>
                                                <option value="NEW" class="font-bold text-blue-600" <?php echo ($sat2_id === 'NEW') ? 'selected' : ''; ?>>+ Input Baru</option>
                                            </select>
                                            <input type="text" name="rab_data[<?php echo $rabGlobalIndex; ?>][satuan_factor_2_custom]" value="<?php echo htmlspecialchars($sat2_custom); ?>" class="satuan-custom-input w-full border-slate-300 rounded-md shadow-sm p-2 text-xs mt-1 <?php echo ($sat2_id !== 'NEW') ? 'hidden' : ''; ?> focus:ring-blue-500" placeholder="Nama Satuan">
                                        </td>
                                        
                                        <td class="px-4 py-2 text-right border-r border-slate-100 bg-slate-50/50">
                                            <span class="font-semibold rab-total-volume-display"><?= number_format_id($total_vol); ?></span>
                                            <input type="hidden" name="rab_data[<?php echo $rabGlobalIndex; ?>][total_volume]" class="rab-total-volume-input" value="<?= $total_vol ?>">
                                        </td>
                                        
                                        <td class="px-1 py-2 text-center text-slate-300 font-bold">x</td>

                                        <td class="px-4 py-2">
                                            <input type="number" name="rab_data[<?php echo $rabGlobalIndex; ?>][harga_satuan]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-sm text-right rab-harga-satuan focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($item['harga_satuan']); ?>" min="0" step="0.01" required>
                                        </td>
                                        <td class="px-4 py-2 text-right border-l border-slate-100 bg-slate-50/50">
                                            <span class="font-black text-emerald-700 rab-total-display"><?= number_format_id($item['total']); ?></span>
                                            <input type="hidden" name="rab_data[<?php echo $rabGlobalIndex; ?>][total_harga]" class="rab-total-input" value="<?= $item['total'] ?>">
                                            <input type="hidden" name="rab_data[<?php echo $rabGlobalIndex; ?>][satuan_id]" class="rab-satuan-final-hidden" value="<?= $sat_final_id ?>">
                                        </td>
                                        <td class="px-2 py-2 text-center border-l border-slate-100">
                                            <button type="button" onclick="removeRABRow(this)" class="text-rose-600 hover:text-rose-800 p-1"><span class="material-icons text-lg">delete</span></button>
                                        </td>
                                    </tr>
                                    <?php
                                    $rabGlobalIndex++;
                                    endforeach; 
                                ?>
                            </tbody>
                            <tfoot class="bg-slate-100 border-t border-slate-200">
                                <tr class="font-bold">
                                    <td colspan="9" class="px-4 py-3 text-right text-slate-600">Subtotal <?= $catName ?>:</td>
                                    <td colspan="2" class="px-4 py-3 text-right font-extrabold subtotal-display" data-cat-id="<?= $catId ?>">
                                        <div class="flex justify-end items-baseline gap-1">
                                            <span class="text-xs text-slate-500 font-medium">Rp</span>
                                            <span class="text-base text-slate-800">
                                                <?= number_format_id($subtotal); ?>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <button type="button" onclick="addRABRow(<?= $catId ?>)" class="w-full md:w-auto inline-flex justify-center items-center px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-lg hover:bg-emerald-700 shadow-sm">
                            <span class="material-icons mr-1 text-base">add</span> Tambah Item <?= $catName ?>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="mt-10 pt-6 border-t border-slate-200">
                    <h3 class="text-xl md:text-2xl font-bold text-slate-800 mb-4">Finalisasi & Simpan Usulan</h3>
                    
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 p-4 md:p-6 bg-blue-50 rounded-xl border border-blue-200 shadow-sm gap-2">
                        <div class="font-bold text-lg md:text-xl text-slate-800 uppercase tracking-wide">Total RAB:</div>
                        
                        <div class="flex items-baseline gap-2">
                            <span class="font-bold text-xl md:text-2xl text-slate-600">Rp</span>
                            <span id="finalGrandTotal" class="font-black text-3xl md:text-4xl text-emerald-600 tracking-tight">
                                <?= number_format_id($currentGrandTotal); ?>
                            </span>
                        </div>
                        
                        <input type="hidden" name="nominal_pencairan" value="<?= $currentGrandTotal ?>">
                    </div>
                    
                    <div class="flex flex-col-reverse sm:flex-row justify-between gap-4">
                        <button type="button" class="btn-prev w-full sm:w-auto bg-slate-400 text-white font-bold px-6 py-3 rounded-lg hover:bg-slate-500 flex items-center justify-center"><span class="material-icons text-sm mr-2">arrow_back</span> Kembali</button>
                        
                        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                            <button type="submit" name="action" value="draft" onclick="return validateDraft(event)" class="w-full sm:w-auto bg-amber-500 text-white font-bold px-6 py-3 rounded-lg shadow-md hover:bg-amber-600 transition-colors flex items-center justify-center">
                                <span class="material-icons mr-2 text-base">save</span> Simpan Draft
                            </button>
                            
                            <button type="submit" name="action" value="ajukan" class="w-full sm:w-auto bg-blue-600 text-white font-bold px-6 py-3 rounded-lg shadow-lg hover:bg-blue-700 transition-colors flex items-center justify-center" onclick="return validateSubmission(event)">
                                <span class="material-icons mr-2 text-base">send</span> Ajukan Usulan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <template id="iku-options-template">
        <?php foreach ($masterIku as $iku): ?>
            <option value="<?php echo $iku['id']; ?>">
                <?php echo htmlspecialchars($iku['deskripsi_iku']); ?>
            </option>
        <?php endforeach; ?>
    </template>

    <template id="rab-satuan-options-template">
        <option value="" selected disabled hidden>-- Pilih Satuan --</option>
        <?php foreach ($masterSatuan as $s): ?>
            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nama_satuan']); ?></option>
        <?php endforeach; ?>
        <option value="NEW" class="font-bold text-blue-600">+ Input Baru</option>
    </template>

    <template id="kpi-month-options-template">
        <option value="" selected disabled hidden>-- Pilih --</option>
        <?php 
        $months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        foreach ($months as $m) {
            echo "<option value='{$m}'>{$m}</option>";
        }
        ?>
    </template>

    <template id="kpi-row-template">
        <tr class="kpi-item">
            <td class="px-4 py-2">
                <input type="text" name="indikator_kinerja[IDX][indikator]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Tercapainya 90% target peserta" required>
            </td>
            <td class="px-4 py-2">
                <select name="indikator_kinerja[IDX][bulan_target]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-sm kpi-month-select focus:ring-blue-500 focus:border-blue-500" required></select>
            </td>
            <td class="px-4 py-2">
                <input type="number" name="indikator_kinerja[IDX][bobot]" min="1" max="100" class="w-full text-center border-slate-300 rounded-md shadow-sm p-2 text-sm kpi-bobot-input focus:ring-blue-500 focus:border-blue-500" required>
            </td>
            <td class="px-4 py-2 text-center">
                <button type="button" onclick="removeKpiItem(this)" class="text-rose-600 hover:text-rose-800 p-1">
                    <span class="material-icons text-lg">delete</span>
                </button>
            </td>
        </tr>
    </template>

    <template id="iku-row-template">
        <tr class="iku-item">
            <td class="px-4 py-2">
                <select name="iku_data[IDX][iku_id]" class="w-full border-slate-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">-- Pilih IKU --</option>
                </select>
            </td>
            <td class="px-4 py-2">
                <input type="text" name="iku_data[IDX][target]" class="w-full text-center border-slate-300 rounded-md shadow-sm p-2 iku-target-input focus:ring-blue-500 focus:border-blue-500" placeholder="Cth: 100%" required>
            </td>
            <td class="px-4 py-2 text-center">
                <button type="button" onclick="removeItemIKU(this)" class="text-rose-600 hover:text-rose-800 p-1">
                    <span class="material-icons text-lg">delete</span>
                </button>
            </td>
        </tr>
    </template>

    <script>
        let currentStep = 1;
        const form = document.getElementById('usulanForm');
        const steps = document.querySelectorAll('.step-content');
        const indicators = document.querySelectorAll('.step-indicator');
        
        const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

        function number_format_id(number) {
            return number.toLocaleString('id-ID', { minimumFractionDigits: 0 });
        }
        
        let ikuItemIndex = 0; 
        const ikuItemsContainer = document.getElementById('iku-items');
        const ikuOptionsTemplate = document.getElementById('iku-options-template');
        
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.kpi-bobot-input').forEach(input => input.addEventListener('input', updateKpiBobotTotal));

            ikuItemIndex = <?php echo count($ikuDetails); ?>; 
            
            if (ikuItemsContainer.children.length === 0) {
                const isEditMode = <?php echo $isEditMode ? 'true' : 'false'; ?>;
                if (!isEditMode) {
                    addItemIKU();
                }
            }
            
            document.querySelectorAll('.rab-table').forEach(table => {
                table.querySelectorAll('.rab-row').forEach(row => attachEventsRAB(row));
            });
            
            document.querySelectorAll('.rab-row').forEach(row => {
                updateTotalRAB(row);
            });
            
            updateKpiBobotTotal();
            updateGrandTotalRABDisplay(); 
            
            let initialRabRowsExist = document.querySelectorAll('.rab-row').length > 0;
            if (!initialRabRowsExist) {
                document.querySelectorAll('.rab-table').forEach(table => {
                    const catId = table.getAttribute('data-category-id');
                    addRABRow(catId); 
                });
            }
            
            ['metode', 'tahapan'].forEach(type => {
                const container = document.getElementById(`${type}-container`);
                if (container.children.length === 1) {
                    container.querySelector(`.${type}-item button`).classList.add('hidden');
                }
            });

            const startDateInput = document.getElementById('tanggal_mulai_kak');
            const endDateInput = document.getElementById('tanggal_selesai_kak');

            if(startDateInput && endDateInput) {
                startDateInput.addEventListener('change', () => {
                    updateKpiMonthOptions(true); // Force update to start month
                });
                endDateInput.addEventListener('change', () => updateKpiMonthOptions(false));
                
                updateKpiMonthOptions(false);
                
                if(startDateInput.value) {
                     updateKpiMonthOptions(true); 
                }
            }
        });

        function updateKpiMonthOptions(forceUpdateToStart = false) {
            const startDateInput = document.getElementById('tanggal_mulai_kak');
            const endDateInput = document.getElementById('tanggal_selesai_kak');
            
            if (!startDateInput || !endDateInput || !startDateInput.value || !endDateInput.value) return;

            const startParts = startDateInput.value.split('-');
            const endParts = endDateInput.value.split('-');
            
            const startYear = parseInt(startParts[0]);
            const startMonth = parseInt(startParts[1]) - 1; 
            
            const endYear = parseInt(endParts[0]);
            const endMonth = parseInt(endParts[1]) - 1;

            const selects = document.querySelectorAll('.kpi-month-select');
            
            selects.forEach(select => {
                const currentVal = select.value;
                let isCurrentValid = false;
                
                Array.from(select.options).forEach((option, index) => {
                    if (option.value === "") return;

                    let isValid = false;
                    const monthIdx = monthNames.indexOf(option.value);
                    if (monthIdx === -1) return; 

                    if (startYear === endYear) {
                        isValid = (monthIdx >= startMonth && monthIdx <= endMonth);
                    } else if (endYear > startYear) {
                        if (endYear - startYear > 1) {
                            isValid = true;
                        } else {
                            isValid = (monthIdx >= startMonth || monthIdx <= endMonth);
                        }
                    }

                    if (isValid) {
                        option.disabled = false;
                        option.style.color = ''; 
                        if (option.value === currentVal) isCurrentValid = true;
                    } else {
                        option.disabled = true;
                        option.style.color = '#cbd5e1'; 
                    }
                });
                
                if ((forceUpdateToStart || select.value === '' || !isCurrentValid) && monthNames[startMonth]) {
                     select.value = monthNames[startMonth];
                }
            });
        }

        function toggleSatuanInput(selectElement) {
            const customInput = selectElement.parentElement.querySelector('.satuan-custom-input');
            if (selectElement.value === 'NEW') {
                customInput.classList.remove('hidden');
                customInput.required = true;
                customInput.focus();
            } else {
                customInput.classList.add('hidden');
                customInput.required = false;
                customInput.value = ''; 
            }
        }

        function updateStep(newStep) {
            steps.forEach(step => {
                if (parseInt(step.getAttribute('data-step')) === newStep) {
                    step.classList.remove('hidden');
                } else {
                    step.classList.add('hidden');
                }
            });

            indicators.forEach(indicator => {
                const stepNum = parseInt(indicator.getAttribute('data-step'));
                indicator.classList.remove('active', 'completed');
                if (stepNum < newStep) {
                    indicator.classList.add('completed');
                } else if (stepNum === newStep) {
                    indicator.classList.add('active');
                }
            });

            currentStep = newStep;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        document.querySelectorAll('.btn-next').forEach(btn => {
            btn.addEventListener('click', () => {
                const currentStepElement = document.querySelector(`.step-content[data-step="${currentStep}"]`);
                const requiredInputs = currentStepElement.querySelectorAll('[required]');
                let allValid = true;

                requiredInputs.forEach(input => {
                    if (!input.offsetParent) return; 
                    
                    if (!input.value) {
                        allValid = false;
                        input.focus();
                    }
                });
                
                if (currentStep === 1) {
                    const totalKpiBobot = parseInt(document.getElementById('kpi-total-bobot').innerText.replace('%', ''));
                    if (totalKpiBobot !== 100) {
                        alert('Total Bobot Indikator Kinerja (KPI) harus 100% sebelum melanjutkan!');
                        allValid = false;
                    }
                    
                    const tglMulai = document.getElementById('tanggal_mulai_kak').value;
                    const tglSelesai = document.getElementById('tanggal_selesai_kak').value;
                    if(tglMulai > tglSelesai) {
                         alert('Tanggal Selesai tidak boleh lebih awal dari Tanggal Mulai.');
                         allValid = false;
                    }
                }

                if (allValid && currentStep < 3) {
                    updateStep(currentStep + 1);
                } else if (!allValid) {
                    alert('Mohon lengkapi semua field yang wajib diisi dan pastikan format bobot benar.');
                }
            });
        });

        document.querySelectorAll('.btn-prev').forEach(btn => {
            btn.addEventListener('click', () => {
                if (currentStep > 1) {
                    updateStep(currentStep - 1);
                }
            });
        });

        function validateDraft(event) {
            const grandTotalText = document.getElementById('finalGrandTotal').innerText;
            const grandTotal = parseFloat(grandTotalText.replace(/[^0-9,-]+/g,"").replace(",", ".")) || 0;
            
            if (grandTotal <= 0) {
                alert('Gagal Simpan! Total RAB harus lebih dari Rp 0 (Minimal 1 item).');
                return false; 
            }
            return true;
        }

        function validateSubmission(event) {
            const totalKpiBobot = parseInt(document.getElementById('kpi-total-bobot').innerText.replace('%', ''));
            const grandTotalText = document.getElementById('finalGrandTotal').innerText;
            const grandTotal = parseFloat(grandTotalText.replace(/[^0-9,-]+/g,"").replace(",", ".")) || 0;
            
            let isValid = true;
            
            if (totalKpiBobot !== 100) {
                alert('Gagal Ajukan! Total Bobot KPI (Langkah 1) harus 100%.');
                updateStep(1);
                isValid = false;
            }

            if (grandTotal <= 0) {
                alert('Gagal Ajukan! Total RAB (Langkah 3) harus lebih dari Rp 0.');
                if (isValid) updateStep(3);
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault(); 
                return false;
            }

            return confirm('Anda yakin ingin mengajukan usulan ini untuk diverifikasi?');
        }
        
        function addMultiInput(type) { 
            const container = document.getElementById(`${type}-container`);
            const itemHtml = `
                <div class="flex gap-2 ${type}-item">
                    <input type="text" name="${type}[]" value="" class="flex-1 px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm md:text-base" placeholder="Contoh: Item Baru">
                    <button type="button" onclick="removeMultiInput(this, '${type}')" class="text-rose-600 hover:text-rose-800 p-2"><span class="material-icons text-lg">delete</span></button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', itemHtml);
            
            const firstButton = container.querySelector(`.${type}-item:first-child button`);
            if (container.children.length > 1) {
                firstButton.classList.remove('hidden');
            }
        }

        function removeMultiInput(button, type) { 
            const row = button.closest(`.${type}-item`);
            const container = document.getElementById(`${type}-container`);
            
            if (container.children.length > 1) {
                row.remove();
            }

            if (container.children.length === 1) {
                container.querySelector(`.${type}-item button`).classList.add('hidden');
            }
        }
        
        let kpiItemIndex = <?php echo $kpiIdx; ?>;
        const kpiItemsContainer = document.getElementById('kpi-items');
        
        function updateKpiBobotTotal() {
            let total = 0;
            const bobotInputs = kpiItemsContainer.querySelectorAll('.kpi-bobot-input');
            
            bobotInputs.forEach(input => {
                let bobot = parseInt(input.value) || 0;
                if (bobot < 0) bobot = 0;
                if (bobot > 100) input.value = 100;
                total += parseInt(input.value) || 0;
            });

            document.getElementById('kpi-total-bobot').textContent = total + '%';

            const totalSpan = document.getElementById('kpi-total-bobot');
            if (total === 100) {
                totalSpan.className = 'px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg font-extrabold text-xs md:text-sm';
            } else {
                totalSpan.className = 'px-3 py-1 bg-rose-100 text-rose-700 rounded-lg font-extrabold text-xs md:text-sm';
            }
        }

        function addKpiItem() {
            const newRow = document.createElement('tr');
            newRow.className = 'kpi-item';

            const kpiTemplate = document.getElementById('kpi-row-template');
            const newRowContent = kpiTemplate.content.cloneNode(true);
            
            newRow.innerHTML = newRowContent.querySelector('tr').innerHTML.replace(/IDX/g, kpiItemIndex);
            
            const monthSelect = newRow.querySelector('.kpi-month-select');
            const monthOptions = document.getElementById('kpi-month-options-template').content.cloneNode(true);
            monthSelect.appendChild(monthOptions);
            
            newRow.querySelector('.kpi-bobot-input').addEventListener('input', updateKpiBobotTotal);

            kpiItemsContainer.appendChild(newRow);

            kpiItemIndex++;
            updateKpiBobotTotal(); 

            updateKpiMonthOptions(true);
        }

        function removeKpiItem(button) {
            const row = button.closest('.kpi-item');
            row.remove();
            updateKpiBobotTotal(); 
        }
        
        function addItemIKU() {
            const newRow = document.createElement('tr');
            newRow.className = 'iku-item';

            const rowTemplate = document.getElementById('iku-row-template');
            const newRowContent = rowTemplate.content.cloneNode(true);
            
            newRow.innerHTML = newRowContent.querySelector('tr').innerHTML.replace(/IDX/g, ikuItemIndex);
            
            const selectElement = newRow.querySelector('select');
            const options = ikuOptionsTemplate.content.cloneNode(true);
            selectElement.appendChild(options);

            ikuItemsContainer.appendChild(newRow);
            ikuItemIndex++;
        }

        function removeItemIKU(button) {
            const row = button.closest('tr');
            row.remove();
        }
        
        let rabGlobalIndex = <?php echo $rabGlobalIndex; ?>;

        function formatRupiah(angka) { 
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka); 
        }
        
        function number_format(number, decimals, dec_point, thousands_sep) {
             number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + (Math.round(n * k) / k).toFixed(prec);
                };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        function empty(v) {
            return v === null || v === undefined || v === '';
        }
        
        function cleanNumber(v) {
            let cleanV = (v + '').replace(/[^0-9.,]/g, '');
            cleanV = cleanV.replace(',', '.');
            return empty(cleanV) ? 0 : parseFloat(cleanV);
        }
        
        function updateTotalRAB(row) {
            const v1Input = row.querySelector('.rab-volume-factor-1');
            const v2Input = row.querySelector('.rab-volume-factor-2');
            const hargaInput = row.querySelector('.rab-harga-satuan');
            const sat1Select = row.querySelector('.rab-satuan-factor-1');
            
            const totalVolumeInput = row.querySelector('.rab-total-volume-input');
            const totalVolumeDisplay = row.querySelector('.rab-total-volume-display');
            const totalInput = row.querySelector('.rab-total-input');
            const totalDisplay = row.querySelector('.rab-total-display');
            const finalSatuanHidden = row.querySelector('.rab-satuan-final-hidden');

            const v1 = cleanNumber(v1Input.value) || (v1Input.value === '' ? NaN : 0);
            const v2 = cleanNumber(v2Input.value) || (v2Input.value === '' ? NaN : 0);
            const harga = cleanNumber(hargaInput.value) || (hargaInput.value === '' ? NaN : 0);

            let totalVolume = NaN;
            if (!isNaN(v1) && !isNaN(v2)) {
                totalVolume = v1 * v2;
            } else if (v1Input.value === '' && v2Input.value === '') {
                totalVolume = 0; 
            } else if (v1Input.value === '' || v2Input.value === '') {
                totalVolume = NaN; 
            }
            
            let total = NaN;
            if (!isNaN(totalVolume) && !isNaN(harga)) {
                total = totalVolume * harga;
            }
            
            if (!isNaN(totalVolume)) {
                totalVolumeInput.value = totalVolume.toFixed(2);
                totalVolumeDisplay.textContent = totalVolume.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, "."); 
            } else {
                 totalVolumeInput.value = '';
                 totalVolumeDisplay.textContent = '-';
            }
            
            if (!isNaN(total)) {
                totalInput.value = total.toFixed(2);
                totalDisplay.textContent = number_format(total, 0, ',', '.'); 
            } else {
                 totalInput.value = '0.00';
                 totalDisplay.textContent = '-';
            }
            
            finalSatuanHidden.value = sat1Select.value;
            
            updateGrandTotalRABDisplay();
        }
        
        function updateGrandTotalRABDisplay() {
            let grand = 0;
            let subtotalMap = {};
            let totalHiddenInput = document.querySelector('input[name="nominal_pencairan"]');

            document.querySelectorAll('.rab-table').forEach(table => {
                const catId = table.getAttribute('data-category-id');
                let sub = 0;
                
                table.querySelectorAll('.rab-row').forEach(r => {
                    const row_total = cleanNumber(r.querySelector('.rab-total-input').value);
                    sub += row_total;
                    grand += row_total;
                });
                subtotalMap[catId] = sub;
            });

            document.querySelectorAll('.subtotal-display').forEach(display => {
                const catId = display.getAttribute('data-cat-id');
                display.querySelector('.text-base').textContent = number_format(subtotalMap[catId] || 0, 0, ',', '.');
            });

            document.getElementById('finalGrandTotal').innerText = number_format(grand, 0, ',', '.');
            if (totalHiddenInput) totalHiddenInput.value = grand.toFixed(2);
        }
        
        function removeRABRow(btn) { 
            const row = btn.closest('.rab-row');
            row.remove();
            updateGrandTotalRABDisplay();
        }
        
        function addRABRow(catId) {
            const tableBody = document.getElementById(`rabBody_${catId}`);
            
            const newRowHtml = `
                <tr class="rab-row" data-index="${rabGlobalIndex}">
                    <td class="px-4 py-2 border-r border-slate-100">
                        <input type="text" name="rab_data[${rabGlobalIndex}][deskripsi]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Konsumsi" required>
                        <input type="hidden" name="rab_data[${rabGlobalIndex}][kategori_id]" value="${catId}">
                        <input type="hidden" name="rab_data[${rabGlobalIndex}][id]" value="0">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" name="rab_data[${rabGlobalIndex}][volume_factor_1]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-sm text-right rab-volume-factor-1 focus:ring-blue-500 focus:border-blue-500" value="" min="0" step="0.01">
                    </td>
                    <td class="px-2 py-2 border-r border-slate-100">
                        <select name="rab_data[${rabGlobalIndex}][satuan_factor_1_id]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-xs md:text-sm rab-satuan-factor-1 focus:ring-blue-500 focus:border-blue-500" required onchange="toggleSatuanInput(this)">
                            ${document.getElementById('rab-satuan-options-template').innerHTML}
                        </select>
                        <input type="text" name="rab_data[${rabGlobalIndex}][satuan_factor_1_custom]" class="satuan-custom-input w-full border-slate-300 rounded-md shadow-sm p-2 text-xs mt-1 hidden focus:ring-blue-500 focus:border-blue-500" placeholder="Nama Satuan">
                    </td>

                    <td class="px-1 py-2 text-center text-slate-300 font-bold">x</td>

                    <td class="px-2 py-2">
                        <input type="number" name="rab_data[${rabGlobalIndex}][volume_factor_2]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-sm text-right rab-volume-factor-2 focus:ring-blue-500 focus:border-blue-500" value="" min="0" step="0.01">
                    </td>
                    <td class="px-2 py-2 border-r border-slate-100">
                        <select name="rab_data[${rabGlobalIndex}][satuan_factor_2_id]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-xs md:text-sm rab-satuan-factor-2 focus:ring-blue-500 focus:border-blue-500" onchange="toggleSatuanInput(this)">
                            ${document.getElementById('rab-satuan-options-template').innerHTML}
                        </select>
                        <input type="text" name="rab_data[${rabGlobalIndex}][satuan_factor_2_custom]" class="satuan-custom-input w-full border-slate-300 rounded-md shadow-sm p-2 text-xs mt-1 hidden focus:ring-blue-500 focus:border-blue-500" placeholder="Nama Satuan">
                    </td>
                    <td class="px-4 py-2 text-right border-r border-slate-100 bg-slate-50/50">
                        <span class="font-semibold rab-total-volume-display"></span>
                        <input type="hidden" name="rab_data[${rabGlobalIndex}][total_volume]" class="rab-total-volume-input" value="0">
                    </td>

                    <td class="px-1 py-2 text-center text-slate-300 font-bold">x</td>

                    <td class="px-4 py-2">
                        <input type="number" name="rab_data[${rabGlobalIndex}][harga_satuan]" class="w-full border-slate-300 rounded-md shadow-sm p-2 text-sm text-right rab-harga-satuan focus:ring-blue-500 focus:border-blue-500" value="" min="0" step="0.01" required>
                    </td>
                    <td class="px-4 py-2 text-right border-l border-slate-100 bg-slate-50/50">
                        <span class="font-black text-emerald-700 rab-total-display"></span>
                        <input type="hidden" name="rab_data[${rabGlobalIndex}][total_harga]" class="rab-total-input" value="0">
                        <input type="hidden" name="rab_data[${rabGlobalIndex}][satuan_id]" class="rab-satuan-final-hidden" value="">
                    </td>
                    <td class="px-2 py-2 text-center border-l border-slate-100">
                        <button type="button" onclick="removeRABRow(this)" class="text-rose-600 hover:text-rose-800 p-1"><span class="material-icons text-lg">delete</span></button>
                    </td>
                </tr>
            `;
            
            const newRow = document.createElement('tr');
            newRow.className = 'rab-row';
            newRow.innerHTML = newRowHtml;
            
            attachEventsRAB(newRow);
            
            tableBody.appendChild(newRow);
            rabGlobalIndex++;
            updateTotalRAB(newRow);
        }

        function attachEventsRAB(row) {
            row.querySelectorAll('.rab-volume-factor-1, .rab-volume-factor-2, .rab-harga-satuan, .rab-satuan-factor-1').forEach(i => i.addEventListener('input', () => updateTotalRAB(row)));
        }
    </script>
<?php include __DIR__.'/../partials/footer.php'; ?>