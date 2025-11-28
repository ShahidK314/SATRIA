<?php include __DIR__.'/../partials/sidebar.php'; ?>

<?php
$isEditMode = isset($isEdit) && $isEdit;
$actionUrl = $isEditMode ? '/usulan/update?id=' . $usulan['id'] : '/usulan/create';
$title = $isEditMode ? 'Edit Usulan Kegiatan' : 'Pengajuan Telaah Baru';
?>

<div class="p-8 max-w-6xl mx-auto">
    <div class="mb-10 text-center">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight"><?php echo $title; ?></h1>
        <p class="text-slate-500 mt-2">Lengkapi data substansi kegiatan (Upload Surat dilakukan setelah disetujui Verifikator).</p>
    </div>

    <div class="flex items-center justify-center mb-12">
        <div class="flex items-center">
            <div class="step-indicator active" data-step="1"><div class="step-circle">1</div><div class="step-label">KAK</div></div>
            <div class="step-line"></div>
            <div class="step-indicator" data-step="2"><div class="step-circle">2</div><div class="step-label">IKU</div></div>
            <div class="step-line"></div>
            <div class="step-indicator" data-step="3"><div class="step-circle">3</div><div class="step-label">RAB</div></div>
        </div>
    </div>

    <form id="wizardForm" method="post" action="<?php echo $actionUrl; ?>" class="space-y-8">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    
    <div class="wizard-step active" id="step1">
        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
            <div class="bg-blue-50 px-8 py-6 border-b border-blue-100">
                <h2 class="text-xl font-bold text-blue-900 flex items-center"><span class="material-icons mr-3">description</span> Step 1: Kerangka Acuan Kerja</h2>
            </div>
            <div class="p-8 space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Kegiatan *</label>
                    <input type="text" name="nama_kegiatan" required value="<?php echo $isEditMode ? htmlspecialchars($usulan['nama_kegiatan']) : ''; ?>" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Gambaran Umum *</label>
                    <textarea name="gambaran_umum" required rows="4" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 transition-all"><?php echo $isEditMode ? htmlspecialchars($usulan['gambaran_umum']) : ''; ?></textarea>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Penerima Manfaat *</label>
                        <input type="text" name="penerima_manfaat" required value="<?php echo $isEditMode ? htmlspecialchars($usulan['penerima_manfaat']) : ''; ?>" class="w-full px-4 py-3 border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Target Luaran</label>
                        <input type="text" name="target_luaran" value="<?php echo $isEditMode ? htmlspecialchars($usulan['target_luaran'] ?? '') : ''; ?>" class="w-full px-4 py-3 border border-slate-300 rounded-xl">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Metode Pelaksanaan</label>
                    <div id="metodeContainer" class="space-y-2">
                        <?php $metode = $isEditMode && isset($usulan['metode_array']) ? $usulan['metode_array'] : ['']; foreach($metode as $m): ?>
                        <div class="flex gap-2 metode-row">
                            <input type="text" name="metode[]" value="<?php echo htmlspecialchars($m); ?>" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg">
                            <button type="button" onclick="removeRow(this, '.metode-row')" class="px-3 py-2 bg-rose-100 text-rose-600 rounded-lg"><span class="material-icons text-sm">close</span></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addRow('#metodeContainer', 'metode')" class="mt-2 text-sm text-blue-600 font-bold flex items-center"><span class="material-icons text-sm mr-1">add_circle</span> Tambah</button>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tahapan Pelaksanaan</label>
                    <div id="tahapanContainer" class="space-y-2">
                        <?php $tahapan = $isEditMode && isset($usulan['tahapan_array']) ? $usulan['tahapan_array'] : ['']; foreach($tahapan as $t): ?>
                        <div class="flex gap-2 tahapan-row">
                            <input type="text" name="tahapan[]" value="<?php echo htmlspecialchars($t); ?>" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg">
                            <button type="button" onclick="removeRow(this, '.tahapan-row')" class="px-3 py-2 bg-rose-100 text-rose-600 rounded-lg"><span class="material-icons text-sm">close</span></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addRow('#tahapanContainer', 'tahapan')" class="mt-2 text-sm text-blue-600 font-bold flex items-center"><span class="material-icons text-sm mr-1">add_circle</span> Tambah</button>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Indikator Keberhasilan</label>
                    <div id="indikatorContainer" class="space-y-3">
                        <?php $indikator = $isEditMode && isset($usulan['indikator_array']) ? $usulan['indikator_array'] : [['indikator'=>'','bulan_target'=>'','bobot'=>'']]; foreach($indikator as $ind): ?>
                        <div class="flex gap-2 items-start indikator-row border border-slate-200 p-3 rounded-lg bg-slate-50">
                            <input type="text" name="indikator_keberhasilan[]" value="<?php echo htmlspecialchars($ind['indikator']); ?>" class="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Indikator...">
                            <select name="bulan_target[]" class="px-3 py-2 border border-slate-300 rounded-lg text-sm">
                                <option value="">Bulan</option>
                                <?php foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $b): ?>
                                    <option value="<?php echo $b; ?>" <?php echo ($ind['bulan_target'] == $b) ? 'selected' : ''; ?>><?php echo $b; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="bobot_keberhasilan[]" value="<?php echo $ind['bobot']; ?>" class="w-24 px-3 py-2 border border-slate-300 rounded-lg text-sm text-right" placeholder="0">
                            <button type="button" onclick="removeRow(this, '.indikator-row')" class="px-2 py-2 bg-rose-100 text-rose-600 rounded-lg"><span class="material-icons text-sm">close</span></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addIndikatorRow()" class="mt-2 text-sm text-blue-600 font-bold flex items-center"><span class="material-icons text-sm mr-1">add_circle</span> Tambah</button>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div><label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Mulai</label><input type="date" name="tanggal_mulai" value="<?php echo $isEditMode ? $usulan['tanggal_mulai'] : ''; ?>" class="w-full px-4 py-3 border border-slate-300 rounded-xl"></div>
                    <div><label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Selesai</label><input type="date" name="tanggal_selesai" value="<?php echo $isEditMode ? $usulan['tanggal_selesai'] : ''; ?>" class="w-full px-4 py-3 border border-slate-300 rounded-xl"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="wizard-step" id="step2">
        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
            <div class="bg-emerald-50 px-8 py-6 border-b border-emerald-100">
                <h2 class="text-xl font-bold text-emerald-900 flex items-center"><span class="material-icons mr-3">analytics</span> Step 2: IKU & Bobot (Total 100%)</h2>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 gap-3">
                    <?php foreach ($iku as $i): $checked = ($isEditMode && isset($selectedIku[$i['id']])) ? 'checked' : ''; $bobot = ($isEditMode && isset($selectedIku[$i['id']])) ? $selectedIku[$i['id']] : ''; ?>
                    <div class="flex items-center gap-4 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors iku-item">
                        <input type="checkbox" name="iku_id[]" value="<?php echo $i['id']; ?>" <?php echo $checked; ?> class="w-5 h-5 text-emerald-600 rounded iku-checkbox" onchange="toggleBobotInput(this)">
                        <div class="flex-1"><label class="text-sm font-medium text-slate-700"><?php echo htmlspecialchars($i['deskripsi_iku']); ?></label></div>
                        <div class="flex items-center gap-2 bobot-input" style="<?php echo $checked ? '' : 'display:none;'; ?>">
                            <input type="number" name="bobot_iku[<?php echo $i['id']; ?>]" value="<?php echo $bobot; ?>" step="0.01" min="0" max="100" class="w-24 px-3 py-2 border rounded-lg text-right text-sm bobot-value" placeholder="0" onchange="calculateTotalBobot()">
                            <span class="text-sm text-slate-500 font-bold">%</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-6 p-4 bg-slate-50 rounded-lg border flex justify-between"><span class="text-sm font-bold text-slate-600">Total Bobot:</span><span class="text-2xl font-extrabold" id="totalBobotDisplay">0%</span></div>
            </div>
        </div>
    </div>

    <div class="wizard-step" id="step3">
        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
            <div class="bg-amber-50 px-8 py-6 border-b border-amber-100 flex justify-between items-center">
                <h2 class="text-xl font-bold text-amber-900 flex items-center"><span class="material-icons mr-3">payments</span> Step 3: RAB</h2>
                <button type="button" id="addRowRAB" class="text-xs font-bold text-amber-700 bg-white px-4 py-2 rounded-lg border border-amber-200"><span class="material-icons text-sm mr-1">add</span> Tambah Item</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs">
                        <tr><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Uraian</th><th class="px-4 py-3">Vol</th><th class="px-4 py-3">Satuan</th><th class="px-4 py-3">Harga</th><th class="px-4 py-3">Total</th><th class="px-4 py-3"></th></tr>
                    </thead>
                    <tbody id="rabBody">
                        <?php $rows = ($isEditMode && !empty($rabData)) ? $rabData : [null]; foreach($rows as $row): ?>
                        <tr class="hover:bg-slate-50 rab-row">
                            <td class="p-3"><select name="kategori_id[]" class="w-full border-slate-200 rounded-lg text-sm bg-white"><?php foreach ($kategori as $k): ?><option value="<?php echo $k['id']; ?>" <?php echo ($row && $row['kategori_id'] == $k['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($k['nama_kategori']); ?></option><?php endforeach; ?></select></td>
                            <td class="p-3"><input type="text" name="uraian[]" value="<?php echo $row ? htmlspecialchars($row['uraian']) : ''; ?>" class="w-full border-slate-200 rounded-lg text-sm" required></td>
                            <td class="p-3"><input type="number" name="volume[]" value="<?php echo $row ? $row['volume'] : '1'; ?>" class="w-full border-slate-200 rounded-lg text-sm volume text-center" min="1" required></td>
                            <td class="p-3"><input type="text" name="satuan[]" value="<?php echo $row ? htmlspecialchars($row['satuan']) : ''; ?>" class="w-full border-slate-200 rounded-lg text-sm text-center" required></td>
                            <td class="p-3"><input type="number" name="harga_satuan[]" value="<?php echo $row ? $row['harga_satuan'] : ''; ?>" class="w-full border-slate-200 rounded-lg text-sm harga_satuan text-right" required></td>
                            <td class="p-3"><input type="text" name="total[]" class="w-full bg-slate-100 border-transparent rounded-lg text-sm font-bold total text-right" readonly></td>
                            <td class="p-3 text-center"><button type="button" onclick="removeRABRow(this)" class="text-slate-300 hover:text-rose-500"><span class="material-icons">delete</span></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 text-right"><p class="text-sm text-slate-500">Total Pengajuan: <span class="text-2xl font-extrabold text-slate-800 ml-2" id="grandTotal">Rp 0</span></p></div>
        </div>
    </div>

    <div class="flex items-center justify-between pt-6 pb-20">
        <button type="button" id="prevBtn" onclick="changeStep(-1)" class="px-6 py-3 bg-white border border-slate-300 font-bold rounded-xl" style="display:none;">Sebelumnya</button>
        <div class="flex-1"></div>
        <button type="button" id="nextBtn" onclick="changeStep(1)" class="px-8 py-3 bg-blue-700 text-white font-bold rounded-xl">Selanjutnya</button>
        <button type="submit" id="submitBtn" style="display:none;" class="px-8 py-3 bg-emerald-700 text-white font-bold rounded-xl">Simpan & Ajukan Draft</button>
    </div>
    </form>
</div>

<style>.wizard-step { display: none; } .wizard-step.active { display: block; } .step-circle { width: 40px; height: 40px; border-radius: 50%; background: #fff; border: 3px solid #cbd5e1; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #94a3b8; } .active .step-circle { border-color: #3b82f6; color: #3b82f6; } .step-line { width: 40px; height: 3px; background: #e2e8f0; margin: 0 8px; } .step-indicator.completed .step-circle { background: #10b981; border-color: #10b981; color: white; }</style>

<script>
    let currentStep = 1;
    const totalSteps = 3; // KEMBALI KE 3 STEP
    
    function changeStep(d) {
        if (d === 1) {
            if (currentStep === 1 && !document.querySelector('[name="nama_kegiatan"]').value) { alert('Nama Kegiatan Wajib!'); return; }
            if (currentStep === 2 && document.querySelectorAll('.iku-checkbox:checked').length === 0) { alert('Pilih minimal 1 IKU'); return; }
        }

        document.getElementById(`step${currentStep}`).classList.remove('active');
        document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
        if(d === 1) document.querySelector(`[data-step="${currentStep}"]`).classList.add('completed');
        
        currentStep += d;
        
        document.getElementById(`step${currentStep}`).classList.add('active');
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
        
        document.getElementById('prevBtn').style.display = currentStep === 1 ? 'none' : 'block';
        document.getElementById('nextBtn').style.display = currentStep === totalSteps ? 'none' : 'block';
        document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'block' : 'none';
        window.scrollTo(0,0);
    }

    // ... (Helper JS lain sama persis, hanya Step 4 dihapus)
    function addRow(containerId, fieldName) {
        const container = document.querySelector(containerId);
        const div = document.createElement('div');
        div.className = `flex gap-2 ${fieldName === 'metode' ? 'metode-row' : 'tahapan-row'}`;
        div.innerHTML = `<input type="text" name="${fieldName}[]" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg"><button type="button" onclick="this.parentElement.remove()" class="px-3 py-2 bg-rose-100 text-rose-600 rounded-lg"><span class="material-icons text-sm">close</span></button>`;
        container.appendChild(div);
    }
    
    function removeRow(btn, cls) { btn.closest(cls).remove(); }

    function addIndikatorRow() {
        const c = document.getElementById('indikatorContainer');
        const div = document.createElement('div');
        div.className = 'flex gap-2 items-start indikator-row border border-slate-200 p-3 rounded-lg bg-slate-50';
        div.innerHTML = `<input type="text" name="indikator_keberhasilan[]" class="flex-1 px-3 py-2 border rounded-lg text-sm" placeholder="Indikator"><select name="bulan_target[]" class="px-3 py-2 border rounded-lg text-sm"><option>Bulan</option><?php foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $b) echo "<option>$b</option>"; ?></select><input type="number" name="bobot_keberhasilan[]" class="w-24 px-3 py-2 border rounded-lg text-sm text-right" placeholder="0"><button type="button" onclick="removeRow(this, '.indikator-row')" class="px-2 py-2 bg-rose-100 text-rose-600 rounded-lg"><span class="material-icons text-sm">close</span></button>`;
        c.appendChild(div);
    }

    function toggleBobotInput(cb) {
        const div = cb.closest('.iku-item').querySelector('.bobot-input');
        div.style.display = cb.checked ? 'flex' : 'none';
        if(!cb.checked) div.querySelector('input').value = '';
        calculateTotalBobot();
    }

    function calculateTotalBobot() {
        let t = 0;
        document.querySelectorAll('.bobot-value').forEach(i => t += parseFloat(i.value || 0));
        const disp = document.getElementById('totalBobotDisplay');
        disp.innerText = t + '%';
        disp.style.color = Math.abs(t - 100) < 0.01 ? '#059669' : '#e11d48';
    }

    function formatRupiah(angka) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka); }
    
    function updateTotalRAB(row) {
        const vol = row.querySelector('.volume').valueAsNumber || 0;
        const harga = row.querySelector('.harga_satuan').valueAsNumber || 0;
        row.querySelector('.total').value = formatRupiah(vol * harga);
        let grand = 0;
        document.querySelectorAll('.rab-row').forEach(r => {
            grand += (r.querySelector('.volume').valueAsNumber || 0) * (r.querySelector('.harga_satuan').valueAsNumber || 0);
        });
        document.getElementById('grandTotal').innerText = formatRupiah(grand);
    }

    function removeRABRow(btn) { btn.closest('tr').remove(); updateTotalRAB(document.querySelector('.rab-row')); }
    
    document.getElementById('addRowRAB').onclick = function() {
        const row = document.querySelector('.rab-row').cloneNode(true);
        row.querySelectorAll('input').forEach(i => i.value = '');
        document.getElementById('rabBody').appendChild(row);
        attachEvents(row);
    }

    function attachEvents(row) {
        row.querySelectorAll('.volume, .harga_satuan').forEach(i => i.addEventListener('input', () => updateTotalRAB(row)));
    }
    document.querySelectorAll('.rab-row').forEach(r => attachEvents(r));
    calculateTotalBobot();
</script>
<?php include __DIR__.'/../partials/footer.php'; ?>