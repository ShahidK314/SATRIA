<?php include __DIR__.'/../partials/sidebar.php'; ?>

<?php
$isEditMode = isset($isEdit) && $isEdit;
$actionUrl = $isEditMode ? '/usulan/update?id=' . $usulan['id'] : '/usulan/create';
$title = $isEditMode ? 'Edit Usulan Kegiatan' : 'Pengajuan Telaah Baru';
?>

<div class="p-8 max-w-6xl mx-auto">
    
    <div class="mb-10 text-center">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight"><?php echo $title; ?></h1>
        <p class="text-slate-500 mt-2">Lengkapi formulir 3 langkah berikut untuk mengajukan usulan kegiatan.</p>
    </div>

    <!-- Progress Steps -->
    <div class="flex items-center justify-center mb-12">
        <div class="flex items-center">
            <div class="step-indicator active" data-step="1">
                <div class="step-circle">1</div>
                <div class="step-label">Data KAK</div>
            </div>
            <div class="step-line"></div>
            <div class="step-indicator" data-step="2">
                <div class="step-circle">2</div>
                <div class="step-label">IKU & Bobot</div>
            </div>
            <div class="step-line"></div>
            <div class="step-indicator" data-step="3">
                <div class="step-circle">3</div>
                <div class="step-label">RAB</div>
            </div>
        </div>
    </div>
    <form id="wizardForm" method="post" action="<?php echo $actionUrl; ?>" class="space-y-8">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    
    <!-- STEP 1: Data KAK -->
    <div class="wizard-step active" id="step1">
        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
            <div class="bg-blue-50 px-8 py-6 border-b border-blue-100">
                <h2 class="text-xl font-bold text-blue-900 flex items-center">
                    <span class="material-icons mr-3">description</span>
                    Step 1: Kerangka Acuan Kerja (KAK)
                </h2>
            </div>
            <div class="p-8 space-y-6">
                <!-- Nama Kegiatan -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Kegiatan *</label>
                    <input type="text" name="nama_kegiatan" required 
                           value="<?php echo $isEditMode ? htmlspecialchars($usulan['nama_kegiatan']) : ''; ?>"
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all" 
                           placeholder="Contoh: Workshop Teknologi AI">
                </div>

                <!-- Gambaran Umum -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Gambaran Umum *</label>
                    <textarea name="gambaran_umum" required rows="4" 
                              class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all" 
                              placeholder="Jelaskan latar belakang dan tujuan kegiatan..."><?php echo $isEditMode ? htmlspecialchars($usulan['gambaran_umum']) : ''; ?></textarea>
                </div>

                <!-- Penerima Manfaat & Target Luaran -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Penerima Manfaat *</label>
                        <input type="text" name="penerima_manfaat" required 
                               value="<?php echo $isEditMode ? htmlspecialchars($usulan['penerima_manfaat']) : ''; ?>"
                               class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all" 
                               placeholder="Contoh: 50 Mahasiswa TI">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Target Luaran</label>
                        <input type="text" name="target_luaran" 
                               value="<?php echo $isEditMode ? htmlspecialchars($usulan['target_luaran'] ?? '') : ''; ?>"
                               class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all" 
                               placeholder="Contoh: Sertifikat Kompetensi">
                    </div>
                </div>

                <!-- Strategi Pencapaian / Metode Pelaksanaan (Multiple) -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Strategi Pencapaian / Metode Pelaksanaan</label>
                    <div id="metodeContainer" class="space-y-2">
                        <?php 
                        $metodeArray = $isEditMode && isset($usulan['metode_array']) ? $usulan['metode_array'] : [''];
                        foreach($metodeArray as $m): 
                        ?>
                        <div class="flex gap-2 metode-row">
                            <input type="text" name="metode[]" value="<?php echo htmlspecialchars($m); ?>"
                                   class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all" 
                                   placeholder="Contoh: Pelatihan intensif 3 hari">
                            <button type="button" onclick="removeRow(this, '.metode-row')" class="px-3 py-2 bg-rose-100 text-rose-600 rounded-lg hover:bg-rose-200">
                                <span class="material-icons text-sm">close</span>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addRow('#metodeContainer', 'metode')" class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-bold flex items-center">
                        <span class="material-icons text-sm mr-1">add_circle</span> Tambah Metode
                    </button>
                </div>

                <!-- Tahapan Pelaksanaan (Multiple) -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tahapan Pelaksanaan</label>
                    <div id="tahapanContainer" class="space-y-2">
                        <?php 
                        $tahapanArray = $isEditMode && isset($usulan['tahapan_array']) ? $usulan['tahapan_array'] : [''];
                        foreach($tahapanArray as $t): 
                        ?>
                        <div class="flex gap-2 tahapan-row">
                            <input type="text" name="tahapan[]" value="<?php echo htmlspecialchars($t); ?>"
                                   class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all" 
                                   placeholder="Contoh: Tahap 1 - Persiapan Materi">
                            <button type="button" onclick="removeRow(this, '.tahapan-row')" class="px-3 py-2 bg-rose-100 text-rose-600 rounded-lg hover:bg-rose-200">
                                <span class="material-icons text-sm">close</span>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addRow('#tahapanContainer', 'tahapan')" class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-bold flex items-center">
                        <span class="material-icons text-sm mr-1">add_circle</span> Tambah Tahapan
                    </button>
                </div>

                <!-- Indikator Kinerja (Multiple with Bulan & Bobot) -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Indikator Keberhasilan</label>
                    <div id="indikatorContainer" class="space-y-3">
                        <?php 
                        $indikatorArray = $isEditMode && isset($usulan['indikator_array']) ? $usulan['indikator_array'] : [['indikator'=>'','bulan_target'=>'','bobot'=>'']];
                        foreach($indikatorArray as $ind): 
                        ?>
                        <div class="flex gap-2 items-start indikator-row border border-slate-200 p-3 rounded-lg bg-slate-50">
                            <input type="text" name="indikator_keberhasilan[]" value="<?php echo htmlspecialchars($ind['indikator']); ?>"
                                   class="flex-1 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all text-sm" 
                                   placeholder="Contoh: Peserta lulus uji kompetensi">
                            <select name="bulan_target[]" class="px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm">
                                <option value="">Bulan</option>
                                <?php 
                                $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                foreach($bulanList as $b): 
                                    $sel = ($ind['bulan_target'] == $b) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $b; ?>" <?php echo $sel; ?>><?php echo $b; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="bobot_keberhasilan[]" value="<?php echo $ind['bobot']; ?>" step="0.01" min="0" max="100"
                                   class="w-24 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-right" 
                                   placeholder="0">
                            <button type="button" onclick="removeRow(this, '.indikator-row')" class="px-2 py-2 bg-rose-100 text-rose-600 rounded-lg hover:bg-rose-200">
                                <span class="material-icons text-sm">close</span>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addIndikatorRow()" class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-bold flex items-center">
                        <span class="material-icons text-sm mr-1">add_circle</span> Tambah Indikator
                    </button>
                </div>

                <!-- Kurun Waktu Pelaksanaan -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" 
                               value="<?php echo $isEditMode ? $usulan['tanggal_mulai'] : ''; ?>"
                               class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" 
                               value="<?php echo $isEditMode ? $usulan['tanggal_selesai'] : ''; ?>"
                               class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 2: IKU & Bobot -->
    <div class="wizard-step" id="step2">
        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
            <div class="bg-emerald-50 px-8 py-6 border-b border-emerald-100">
                <h2 class="text-xl font-bold text-emerald-900 flex items-center">
                    <span class="material-icons mr-3">analytics</span>
                    Step 2: Pilih IKU dengan Bobot (Total harus 100%)
                </h2>
            </div>
            <div class="p-8">
                <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <p class="text-sm text-amber-800"><strong>Catatan:</strong> Anda dapat memilih lebih dari 1 IKU. Pastikan total bobot = 100%.</p>
                </div>

                <div class="grid grid-cols-1 gap-3" id="ikuSelectionContainer">
                    <?php foreach ($iku as $i): 
                        $checked = ($isEditMode && isset($selectedIku[$i['id']])) ? 'checked' : '';
                        $bobot = ($isEditMode && isset($selectedIku[$i['id']])) ? $selectedIku[$i['id']] : '';
                    ?>
                    <div class="flex items-center gap-4 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors iku-item">
                        <input type="checkbox" name="iku_id[]" value="<?php echo $i['id']; ?>" <?php echo $checked; ?>
                               class="w-5 h-5 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500 iku-checkbox"
                               onchange="toggleBobotInput(this)">
                        <div class="flex-1">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer">
                                <?php echo htmlspecialchars($i['deskripsi_iku']); ?>
                            </label>
                        </div>
                        <div class="flex items-center gap-2 bobot-input" style="<?php echo $checked ? '' : 'display:none;'; ?>">
                            <input type="number" name="bobot_iku[<?php echo $i['id']; ?>]" value="<?php echo $bobot; ?>" 
                                   step="0.01" min="0" max="100"
                                   class="w-24 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 text-right text-sm bobot-value"
                                   placeholder="0" onchange="calculateTotalBobot()">
                            <span class="text-sm text-slate-500 font-bold">%</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-6 p-4 bg-slate-50 rounded-lg border border-slate-200 flex justify-between items-center">
                    <span class="text-sm font-bold text-slate-600">Total Bobot IKU:</span>
                    <span class="text-2xl font-extrabold" id="totalBobotDisplay">0%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 3: RAB -->
    <div class="wizard-step" id="step3">
        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
            <div class="bg-amber-50 px-8 py-6 border-b border-amber-100 flex justify-between items-center">
                <h2 class="text-xl font-bold text-amber-900 flex items-center">
                    <span class="material-icons mr-3">payments</span>
                    Step 3: Rencana Anggaran Biaya (RAB)
                </h2>
                <button type="button" id="addRowRAB" class="text-xs font-bold text-amber-700 bg-white hover:bg-amber-100 px-4 py-2 rounded-lg transition-colors flex items-center border border-amber-200">
                    <span class="material-icons text-sm mr-1">add</span> Tambah Item
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs">
                        <tr>
                            <th class="px-4 py-3 w-48">Kategori</th>
                            <th class="px-4 py-3">Uraian Belanja</th>
                            <th class="px-4 py-3 w-20">Vol</th>
                            <th class="px-4 py-3 w-28">Satuan</th>
                            <th class="px-4 py-3 w-36">Harga (@)</th>
                            <th class="px-4 py-3 w-36">Total</th>
                            <th class="px-4 py-3 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="rabBody" class="divide-y divide-slate-100">
                        <?php 
                        $rowsToRender = ($isEditMode && !empty($rabData)) ? $rabData : [null];
                        foreach($rowsToRender as $row): 
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors rab-row">
                            <td class="p-3">
                                <select name="kategori_id[]" class="w-full border-slate-200 rounded-lg text-sm focus:border-amber-500 focus:ring-0 bg-white">
                                    <?php foreach ($kategori as $k): 
                                        $sel = ($row && $row['kategori_id'] == $k['id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $k['id']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($k['nama_kategori']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="p-3">
                                <input type="text" name="uraian[]" value="<?php echo $row ? htmlspecialchars($row['uraian']) : ''; ?>" 
                                       class="w-full border-slate-200 rounded-lg text-sm focus:border-amber-500 focus:ring-0" 
                                       required placeholder="Nama item...">
                            </td>
                            <td class="p-3">
                                <input type="number" name="volume[]" value="<?php echo $row ? $row['volume'] : '1'; ?>" 
                                       class="w-full border-slate-200 rounded-lg text-sm focus:border-amber-500 focus:ring-0 volume text-center" 
                                       min="1" required>
                            </td>
                            <td class="p-3">
                                <input type="text" name="satuan[]" value="<?php echo $row ? htmlspecialchars($row['satuan']) : ''; ?>" 
                                       class="w-full border-slate-200 rounded-lg text-sm focus:border-amber-500 focus:ring-0 text-center" 
                                       required placeholder="Pcs">
                            </td>
                            <td class="p-3">
                                <input type="number" name="harga_satuan[]" value="<?php echo $row ? $row['harga_satuan'] : ''; ?>" 
                                       class="w-full border-slate-200 rounded-lg text-sm focus:border-amber-500 focus:ring-0 harga_satuan text-right" 
                                       min="0" required placeholder="0">
                            </td>
                            <td class="p-3">
                                <input type="text" name="total[]" 
                                       class="w-full bg-slate-100 border-transparent rounded-lg text-sm font-bold text-slate-700 total cursor-not-allowed text-right" 
                                       readonly>
                            </td>
                            <td class="p-3 text-center">
                                <button type="button" onclick="removeRABRow(this)" 
                                        class="text-slate-300 hover:text-rose-500 transition-colors p-2 rounded-full hover:bg-rose-50">
                                    <span class="material-icons text-lg">delete</span>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 text-right">
                <p class="text-sm text-slate-500">Total Pengajuan: <span class="text-2xl font-extrabold text-slate-800 ml-2" id="grandTotal">Rp 0</span></p>
            </div>
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="flex items-center justify-between pt-6 pb-20">
        <button type="button" id="prevBtn" onclick="changeStep(-1)" 
                class="px-6 py-3 bg-white border border-slate-300 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition-all"
                style="display:none;">
            <span class="material-icons text-sm mr-2 align-middle">arrow_back</span> Sebelumnya
        </button>
        <div class="flex-1"></div>
        <button type="button" id="nextBtn" onclick="changeStep(1)" 
                class="px-8 py-3 bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-700/30 hover:bg-blue-800 transition-all">
            Selanjutnya <span class="material-icons text-sm ml-2 align-middle">arrow_forward</span>
        </button>
        <button type="submit" id="submitBtn" style="display:none;" 
                class="px-8 py-3 bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-700/30 hover:bg-emerald-800 transition-all">
            <span class="material-icons text-sm mr-2 align-middle">save</span>
            <?php echo $isEditMode ? 'Simpan Perubahan' : 'Simpan sebagai Draft'; ?>
        </button>
    </div>
</form>

</div>
<style>
.wizard-step { display: none; }
.wizard-step.active { display: block; }

.step-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}

.step-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: white;
    border: 3px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    color: #94a3b8;
    font-size: 18px;
    transition: all 0.3s;
}

.step-indicator.active .step-circle {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border-color: #3b82f6;
    color: white;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.step-indicator.completed .step-circle {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.step-label {
    margin-top: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
}

.step-indicator.active .step-label {
    color: #1e40af;
}

.step-line {
    width: 80px;
    height: 3px;
    background: #e2e8f0;
    margin: 0 12px;
}

.step-indicator.active ~ .step-line {
    background: linear-gradient(90deg, #3b82f6, #e2e8f0);
}
</style>
<script>
let currentStep = 1;
const totalSteps = 3;

function changeStep(direction) {
    // Validasi Step
    if (direction === 1) {
        if (currentStep === 1) {
            // Validasi KAK
            const namaKegiatan = document.querySelector('[name="nama_kegiatan"]').value.trim();
            const gambaranUmum = document.querySelector('[name="gambaran_umum"]').value.trim();
            
            if (!namaKegiatan || !gambaranUmum) {
                alert('Nama Kegiatan dan Gambaran Umum wajib diisi!');
                return;
            }
        } else if (currentStep === 2) {
            // Validasi IKU & Bobot
            const checkedIKU = document.querySelectorAll('.iku-checkbox:checked');
            if (checkedIKU.length === 0) {
                alert('Pilih minimal 1 IKU!');
                return;
            }
            
            let totalBobot = 0;
            checkedIKU.forEach(cb => {
                const bobotInput = document.querySelector(`[name="bobot_iku[${cb.value}]"]`);
                totalBobot += parseFloat(bobotInput.value || 0);
            });
            
            if (Math.abs(totalBobot - 100) > 0.01) {
                alert(`Total bobot IKU harus 100%! Saat ini: ${totalBobot.toFixed(2)}%`);
                return;
            }
        }
    }

    // Hide current step
    document.getElementById(`step${currentStep}`).classList.remove('active');
    document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
    
    if (direction === 1 && currentStep < totalSteps) {
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('completed');
    }
    
    // Update step number
    currentStep += direction;
    
    // Show new step
    document.getElementById(`step${currentStep}`).classList.add('active');
    document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
    
    // Update buttons
    document.getElementById('prevBtn').style.display = currentStep === 1 ? 'none' : 'block';
    document.getElementById('nextBtn').style.display = currentStep === totalSteps ? 'none' : 'block';
    document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'block' : 'none';
    
    // Scroll to top
    window.scrollTo({top: 0, behavior: 'smooth'});
}

// Dynamic Add/Remove Rows
function addRow(containerId, fieldName) {
    const container = document.querySelector(containerId);
    const rowClass = fieldName === 'metode' ? 'metode-row' : 'tahapan-row';
    const div = document.createElement('div');
    div.className = `flex gap-2 ${rowClass}`;
    div.innerHTML = `
        <input type="text" name="${fieldName}[]" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all" placeholder="Masukkan ${fieldName}...">
        <button type="button" onclick="removeRow(this, '.${rowClass}')" class="px-3 py-2 bg-rose-100 text-rose-600 rounded-lg hover:bg-rose-200">
            <span class="material-icons text-sm">close</span>
        </button>
    `;
    container.appendChild(div);
}

function removeRow(btn, rowClass) {
    const container = btn.closest(rowClass).parentElement;
    if (container.querySelectorAll(rowClass).length > 1) {
        btn.closest(rowClass).remove();
    }
}

function addIndikatorRow() {
    const container = document.getElementById('indikatorContainer');
    const div = document.createElement('div');
    div.className = 'flex gap-2 items-start indikator-row border border-slate-200 p-3 rounded-lg bg-slate-50';
    div.innerHTML = `
        <input type="text" name="indikator_keberhasilan[]" class="flex-1 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all text-sm" placeholder="Indikator...">
        <select name="bulan_target[]" class="px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm">
            <option value="">Bulan</option>
            <?php foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $b): ?>
                <option value="<?php echo $b; ?>"><?php echo $b; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="bobot_keberhasilan[]" step="0.01" min="0" max="100" class="w-24 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-right" placeholder="0">
        <button type="button" onclick="removeRow(this, '.indikator-row')" class="px-2 py-2 bg-rose-100 text-rose-600 rounded-lg hover:bg-rose-200">
            <span class="material-icons text-sm">close</span>
        </button>
    `;
    container.appendChild(div);
}

// IKU Bobot Toggle
function toggleBobotInput(checkbox) {
    const parent = checkbox.closest('.iku-item');
    const bobotDiv = parent.querySelector('.bobot-input');
    const bobotInput = parent.querySelector('.bobot-value');
    
    if (checkbox.checked) {
        bobotDiv.style.display = 'flex';
        bobotInput.required = true;
    } else {
        bobotDiv.style.display = 'none';
        bobotInput.value = '';
        bobotInput.required = false;
    }
    calculateTotalBobot();
}

function calculateTotalBobot() {
let total = 0;
document.querySelectorAll('.iku-checkbox:checked').forEach(cb => {
const bobotInput = document.querySelector([name="bobot_iku[${cb.value}]"]);
total += parseFloat(bobotInput.value || 0);
});
const display = document.getElementById('totalBobotDisplay');
display.textContent = total.toFixed(2) + '%';

// Color coding
    if (Math.abs(total - 100) < 0.01) {
        display.className = 'text-2xl font-extrabold text-emerald-600';
    } else {
        display.className = 'text-2xl font-extrabold text-rose-600';
    }
}
// RAB Calculations
function formatRupiah(angka) {
return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
}
function updateTotalRAB(row) {
const vol = row.querySelector('.volume').valueAsNumber || 0;
const harga = row.querySelector('.harga_satuan').valueAsNumber || 0;
const total = vol * harga;
row.querySelector('.total').value = formatRupiah(total);
// Calc Grand Total
let grand = 0;
document.querySelectorAll('.rab-row').forEach(r => {
    const v = r.querySelector('.volume').valueAsNumber || 0;
    const h = r.querySelector('.harga_satuan').valueAsNumber || 0;
    grand += (v * h);
});
document.getElementById('grandTotal').innerText = formatRupiah(grand);
}
function attachRABEvents(row) {
row.querySelectorAll('.volume, .harga_satuan').forEach(input => {
input.addEventListener('input', () => updateTotalRAB(row));
});
updateTotalRAB(row);
}
// Init RAB rows
document.querySelectorAll('.rab-row').forEach(row => { attachRABEvents(row); });
// Add RAB Row
document.getElementById('addRowRAB').addEventListener('click', () => {
const tbody = document.getElementById('rabBody');
const clone = tbody.rows[0].cloneNode(true);
clone.querySelectorAll('input').forEach(i => i.value = '');
clone.querySelector('.volume').value = 1;
clone.querySelector('.total').value = '';

tbody.appendChild(clone);
attachRABEvents(clone);
});
function removeRABRow(btn) {
const tbody = document.getElementById('rabBody');
if (tbody.querySelectorAll('.rab-row').length > 1) {
btn.closest('.rab-row').remove();
updateTotalRAB(tbody.rows[0]); // Trigger recalc
}
}
// Init Bobot Calculation on load
calculateTotalBobot();
</script>
<?php include __DIR__.'/../partials/footer.php'; ?>