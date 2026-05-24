<?php 
// app/Views/lpj/upload_detail.php
include __DIR__.'/../partials/sidebar.php'; 

if (!function_exists('formatRupiah')) {
    function formatRupiah($n) { return 'Rp ' . number_format($n, 0, ',', '.'); } 
}

$groupedData = [];
$grandTotalRab = 0;
$grandTotalReal = 0; 

foreach ($rabItems as $item) {
    $catId = $item['kategori_id'];
    if (!isset($groupedData[$catId])) {
        $groupedData[$catId] = [
            'nama' => $item['nama_kategori'],
            'items' => [],
            'total_rab' => 0
        ];
    }
    
    // VALIDASI KETAT
    $item['is_match'] = ($item['total_realisasi'] == $item['nominal_rab']);
    
    $groupedData[$catId]['items'][] = $item;
    $groupedData[$catId]['total_rab'] += $item['nominal_rab'];
    $grandTotalRab += $item['nominal_rab'];
    $grandTotalReal += $item['total_realisasi'];
}

$catatanRevisi = json_decode($lpj['catatan_bendahara'] ?? '', true) ?? [];
?>

<div class="m-4 md:m-5">
    <div class="mb-6">
        <a href="/pengajuan/lpj" class="text-slate-500 hover:text-emerald-600 font-bold flex items-center gap-2 mb-4 w-fit transition-colors">
            <span class="material-icons text-sm">arrow_back</span> Kembali
        </a>
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Lengkapi LPJ</h1>
        <p class="text-slate-500 mt-1 text-sm md:text-base flex flex-col sm:flex-row sm:gap-2">
            <span>Total RAB: <span class="font-bold text-slate-700"><?php echo formatRupiah($grandTotalRab); ?></span></span>
            <span class="hidden sm:inline">|</span>
            <span>Realisasi (Input): <span class="font-bold text-blue-600" id="grandTotalReal">Rp 0</span></span>
        </p>
        
        <?php if($lpj['status_terkini'] === 'Revisi'): ?>
            <div class="mt-4 p-4 bg-red-50 border border-red-200 border-l-4 border-l-red-500 text-red-800 rounded-lg shadow-sm text-sm">
                <p class="font-bold flex items-center text-base"><span class="material-icons mr-2 text-base">warning</span> PERLU REVISI</p>
                <p class="mt-1">Terdapat catatan perbaikan dari Bendahara. Cek pada kolom "Bukti Upload" yang bertanda merah.</p>
            </div>
        <?php endif; ?>
    </div>

    <div id="error-toast" class="hidden fixed bottom-4 right-4 bg-rose-600 text-white px-4 py-3 rounded-lg shadow-lg z-50 text-xs md:text-sm font-bold flex items-center border border-rose-700">
        <span class="material-icons text-base md:text-lg mr-2">error</span> <span id="error-msg">Gagal</span>
    </div>

    <div class="space-y-6 md:space-y-8">
        <?php foreach ($groupedData as $catId => $data): ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-3 md:p-4 bg-slate-50 border-b border-slate-100 font-bold text-slate-700 flex flex-col sm:flex-row justify-between text-sm gap-1">
                <span><?php echo htmlspecialchars($data['nama']); ?></span>
                <span class="text-slate-500 font-normal text-xs bg-white px-2 py-1 rounded border border-slate-200 w-fit">Target: <strong class="text-slate-700"><?php echo formatRupiah($data['total_rab']); ?></strong></span>
            </div>

            <div class="overflow-x-auto w-full custom-scrollbar pb-2">
                <table class="w-full text-xs text-left min-w-[1100px]">
                    <thead class="bg-white text-slate-500 border-b border-slate-100 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-3 md:px-4 py-3 w-[20%] align-top font-bold">Uraian</th>
                            <th class="px-1 py-3 w-[4%] text-center align-top font-bold">Vol 1</th>
                            <th class="px-1 py-3 w-[4%] text-center align-top font-bold">Sat 1</th>
                            <th class="px-1 py-3 w-[1%] text-center align-top font-bold"></th>
                            <th class="px-1 py-3 w-[4%] text-center align-top font-bold">Vol 2</th>
                            <th class="px-1 py-3 w-[4%] text-center align-top font-bold">Sat 2</th>
                            <th class="px-1 py-3 w-[1%] text-center align-top font-bold"></th>
                            <th class="px-2 py-3 w-[10%] text-right align-top font-bold">Harga Sat</th>
                            <th class="px-2 py-3 w-[10%] text-right align-top font-bold">Total RAB</th>
                            <th class="px-2 py-3 w-[14%] align-top font-bold">Realisasi (Rp)</th>
                            <th class="px-2 py-3 w-[14%] align-top font-bold">Keterangan</th>
                            <th class="px-3 md:px-4 py-3 w-[15%] align-top font-bold">Bukti Upload</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 border-b border-slate-100">
                        <?php foreach ($data['items'] as $item): 
                            $rabId = $item['rab_id'];
                            $rowId = "item-row-" . $rabId;
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors" id="<?php echo $rowId; ?>">
                            
                            <td class="px-3 md:px-4 py-4 font-bold text-slate-800 align-top whitespace-normal break-words leading-relaxed">
                                <?php echo htmlspecialchars($item['deskripsi']); ?>
                            </td>
                            
                            <td class="px-1 py-4 text-center align-top text-slate-600 pt-4"><?php echo floatval($item['volume_factor_1']); ?></td>
                            <td class="px-1 py-4 text-center align-top text-slate-600 text-[10px] uppercase pt-4"><?php echo htmlspecialchars($item['nama_satuan_1'] ?? '-'); ?></td>
                            <td class="px-1 py-4 text-center align-top text-slate-300 pt-4">x</td>
                            <td class="px-1 py-4 text-center align-top text-slate-600 pt-4"><?php echo floatval($item['volume_factor_2']); ?></td>
                            <td class="px-1 py-4 text-center align-top text-slate-600 text-[10px] uppercase pt-4"><?php echo htmlspecialchars($item['nama_satuan_2'] ?? '-'); ?></td>
                            <td class="px-1 py-4 text-center align-top text-slate-300 pt-4">x</td>

                            <td class="px-2 py-4 text-right align-top text-slate-600 whitespace-nowrap pt-4">
                                <?php echo formatRupiah($item['harga_satuan']); ?>
                            </td>
                            <td class="px-2 py-4 text-right align-top font-mono font-bold text-slate-800 whitespace-nowrap pt-4">
                                <?php echo formatRupiah($item['nominal_rab']); ?>
                            </td>
                            
                            <td class="px-2 py-4 align-top bg-slate-50/50">
                                <div class="relative w-full mb-2">
                                    <span class="absolute left-2 top-2 text-slate-400 text-[10px]">Rp</span>
                                    <input type="text" 
                                           class="w-full pl-6 pr-2 py-1.5 border border-slate-300 rounded text-xs md:text-sm font-bold input-nominal focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner bg-white"
                                           value="<?php echo ($item['total_realisasi'] > 0) ? number_format($item['total_realisasi'],0,',','.') : ''; ?>" 
                                           data-max="<?php echo $item['nominal_rab']; ?>"
                                           data-rab-id="<?php echo $rabId; ?>"
                                           data-kategori-id="<?php echo $catId; ?>"
                                           placeholder="0" autocomplete="off"
                                           oninput="handleInputVisual(this)" 
                                           onchange="autoSave(this)">
                                </div>
                                <div id="status-badge-<?php echo $rabId; ?>">
                                    <?php if($item['is_match']): ?>
                                        <span class="text-[9px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full font-bold flex items-center w-fit border border-emerald-200"><span class="material-icons text-[10px] mr-1">check_circle</span> Sesuai</span>
                                    <?php else: ?>
                                        <span class="text-[9px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full font-bold flex items-center w-fit border border-amber-200"><span class="material-icons text-[10px] mr-1">warning</span> Belum Sesuai</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td class="px-2 py-4 align-top">
                                <textarea rows="2" 
                                    class="w-full px-2 py-1.5 border border-slate-300 rounded text-xs resize-y focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none placeholder-slate-400 input-keterangan" 
                                    placeholder="Ketik rincian..."
                                    onchange="autoSave(this)"><?php echo htmlspecialchars($item['keterangan'] ?? ''); ?></textarea>
                            </td>

                            <td class="px-3 md:px-4 py-4 bg-slate-50/50 border-l border-slate-100 align-top">
                                <div class="flex gap-2 items-center mb-3">
                                    <label class="block w-full cursor-pointer group">
                                        <input type="file" 
                                            name="files[]" 
                                            multiple 
                                            class="block w-full text-[9px] text-slate-500 input-file file:mr-1 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 cursor-pointer transition-colors" 
                                            accept=".png,.jpg,.jpeg,.pdf"/>
                                    </label>
                                    <button type="button" onclick="saveViaAjax(this)" class="bg-blue-600 hover:bg-blue-700 text-white p-1 rounded-lg shadow-sm transition flex-shrink-0 flex items-center justify-center h-8 w-8" title="Upload & Simpan">
                                        <span class="material-icons text-base">cloud_upload</span>
                                    </button>
                                </div>

                                <div class="space-y-2 file-list-container" id="file-list-<?php echo $rabId; ?>">
                                    <?php foreach ($item['uploaded_files'] as $file): 
                                        $docKey = 'dokumen_' . $file['id'];
                                        $note = $catatanRevisi[$docKey] ?? '';

                                        if (empty($file['file_path']) && empty($note)) continue;
                                    ?>
                                        <div class="mb-1 doc-wrapper" id="doc-row-<?php echo $file['id']; ?>">
                                            
                                            <?php if (!empty($file['file_path'])): ?>
                                                <div class="file-display flex justify-between items-center p-2 bg-white border border-slate-200 rounded-lg shadow-sm text-[10px] md:text-xs group hover:border-blue-300 transition-colors">
                                                    <a href="<?php echo $file['file_path']; ?>" target="_blank" class="text-slate-600 hover:text-blue-600 hover:underline flex items-center gap-1.5 break-all pr-1 font-medium">
                                                        <span class="material-icons text-[14px] text-slate-400 flex-shrink-0">description</span>
                                                        <span class="leading-tight"><?php echo basename($file['name']); ?></span>
                                                    </a>
                                                    
                                                    <button type="button" onclick="deleteFile(<?php echo $file['id']; ?>, <?php echo $rabId; ?>)" class="text-slate-300 hover:text-rose-600 p-0.5 transition flex-shrink-0" title="Hapus Bukti">
                                                        <span class="material-icons text-[14px]">delete</span>
                                                    </button>
                                                </div>
                                            <?php endif; ?>

                                            <?php if($note): ?>
                                                <div class="note-display mt-1.5 p-2 bg-red-50 border border-red-200 rounded-lg text-[10px] md:text-[11px] text-red-700 flex items-start shadow-sm">
                                                    <span class="material-icons text-[14px] mr-1.5 mt-0.5 flex-shrink-0">error_outline</span>
                                                    <span class="break-words font-semibold leading-snug">"<?php echo htmlspecialchars($note); ?>"</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="mt-10 pt-6 border-t border-slate-200 mb-20 sticky bottom-4 z-20">
        <form action="/lpj/submit" method="POST" id="formFinalSubmit">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="lpj_id" value="<?php echo $lpj['id']; ?>">
            <button type="button" onclick="validateAndSubmit()" class="w-full py-4 bg-emerald-600 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-700 hover:shadow-xl transition-all flex justify-center items-center text-sm md:text-base border border-emerald-500">
                <span class="material-icons mr-2">send</span> Finalisasi & Ajukan LPJ
            </button>
        </form>
    </div>
</div>

<script>
// SEMUA JAVASCRIPT BAWAAN TIDAK DIUBAH SAMA SEKALI
document.addEventListener("DOMContentLoaded", function() {
    recalculateHeaderTotal();
});

function handleInputVisual(input) {
    let val = input.value.replace(/[^0-9]/g, '');
    if(val) input.value = new Intl.NumberFormat('id-ID').format(val); 
    else input.value = '';
    checkStatusVisual(input);
    recalculateHeaderTotal();
}

function validateAndSubmit() {
    let allUploaded = true;
    document.querySelectorAll('.file-list-container').forEach(container => {
        const hasFile = container.querySelectorAll('.file-display').length > 0;
        if (!hasFile) {
            allUploaded = false;
            container.closest('tr').style.backgroundColor = '#fef2f2'; 
        } else {
            container.closest('tr').style.backgroundColor = '';
        }
    });

    if (!allUploaded) {
        showError('Gagal: Ada item yang belum diupload bukti struk/notanya.');
        return;
    }

    if (confirm('Sudah yakin semua bukti dan nominal sesuai? Jika sudah diajukan, data tidak bisa diubah.')) {
        document.getElementById('formFinalSubmit').submit();
    }
}

function saveViaAjax(triggerElement) {
    const row = triggerElement.closest('tr');
    const nominalInput = row.querySelector('.input-nominal');
    const ketInput = row.querySelector('.input-keterangan');
    const fileInput = row.querySelector('.input-file');
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    const rabId = nominalInput.dataset.rabId;
    const katId = nominalInput.dataset.kategoriId;
    const nominalVal = nominalInput.value;
    const ketVal = ketInput.value;
    const files = fileInput ? fileInput.files : [];

    if(triggerElement.tagName === 'BUTTON') {
        triggerElement.disabled = true;
        triggerElement.innerHTML = '<span class="material-icons text-base animate-spin">refresh</span>';
    }

    let formData = new FormData();
    formData.append('is_ajax', '1');
    formData.append('action', 'save');
    formData.append('csrf_token', csrfToken); 
    formData.append('rab_id', rabId);
    formData.append('kategori_id', katId);
    formData.append('nominal', nominalVal);
    formData.append('keterangan', ketVal);
    
    if (files.length > 0) {
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }
    }

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(triggerElement.tagName === 'BUTTON') {
            triggerElement.disabled = false;
            triggerElement.innerHTML = '<span class="material-icons text-base">cloud_upload</span>';
        }
        
        if(data.status === 'success') {
            checkStatusVisual(nominalInput);
            recalculateHeaderTotal();
            
            if (data.uploaded_files && data.uploaded_files.length > 0) {
                data.uploaded_files.forEach(file => {
                    updateOrAppendFile(rabId, file);
                });
                if(fileInput) fileInput.value = ''; 
            }
        } else {
            showError(data.msg);
        }
    })
    .catch(err => {
        if(triggerElement.tagName === 'BUTTON') {
            triggerElement.disabled = false;
            triggerElement.innerHTML = '<span class="material-icons text-base">cloud_upload</span>';
        }
        showError('Koneksi Error. Pastikan ukuran file tidak melebihi batas.');
    });
}

function autoSave(input) {
    saveViaAjax(input);
}

function deleteFile(docId, rabId) {
    if(!confirm('Yakin ingin menghapus bukti file ini?')) return;
    
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    let formData = new FormData();
    formData.append('is_ajax', '1');
    formData.append('action', 'delete');
    formData.append('csrf_token', csrfToken); 
    formData.append('doc_id', docId);

    fetch(window.location.href, { 
        method: 'POST', 
        body: formData 
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            let row = document.getElementById('doc-row-' + docId);
            if(row) {
                let fileDisplay = row.querySelector('.file-display');
                if(fileDisplay) fileDisplay.remove();

                let hasNote = row.querySelector('.note-display');
                if (!hasNote) {
                    row.remove();
                } 
            }
        } else {
            showError(data.msg || 'Gagal Hapus File');
        }
    })
    .catch(err => {
        console.error(err);
        showError('Koneksi Error');
    });
}

function updateOrAppendFile(rabId, file) {
    const existingRow = document.getElementById('doc-row-' + file.id);
    
    const fileHtml = `
        <div class="file-display flex justify-between items-center p-2 bg-white border border-slate-200 rounded-lg shadow-sm text-[10px] md:text-xs group hover:border-blue-300 transition-colors">
            <a href="${file.path}" target="_blank" class="text-slate-600 hover:text-blue-600 hover:underline flex items-center gap-1.5 break-all pr-1 font-medium">
                <span class="material-icons text-[14px] text-slate-400 flex-shrink-0">description</span>
                <span class="leading-tight">${file.name}</span>
            </a>
            <button type="button" onclick="deleteFile(${file.id}, ${rabId})" class="text-slate-300 hover:text-rose-600 p-0.5 transition flex-shrink-0" title="Hapus Bukti">
                <span class="material-icons text-[14px]">delete</span>
            </button>
        </div>
    `;

    if (existingRow) {
        let currentFileDisplay = existingRow.querySelector('.file-display');
        if (currentFileDisplay) {
            currentFileDisplay.outerHTML = fileHtml; 
        } else {
            existingRow.insertAdjacentHTML('afterbegin', fileHtml);
        }
    } else {
        const container = document.getElementById('file-list-' + rabId);
        const newRowHtml = `
            <div class="mb-1 doc-wrapper" id="doc-row-${file.id}">
                ${fileHtml}
            </div>
        `;
        container.insertAdjacentHTML('beforeend', newRowHtml);
    }
}

function showError(msg) {
    const toast = document.getElementById('error-toast');
    const msgSpan = document.getElementById('error-msg');
    msgSpan.textContent = msg;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 4000);
}

function checkStatusVisual(input) {
    let rawVal = parseFloat(input.value.replace(/\./g, '')) || 0;
    let maxVal = parseFloat(input.dataset.max) || 0;
    let badge = document.getElementById('status-badge-' + input.dataset.rabId);
    
    // VALIDASI KETAT (Harus pas dengan RAB)
    let isMatch = (rawVal === maxVal);

    if (isMatch) {
        input.classList.remove('border-slate-300', 'focus:border-blue-500');
        input.classList.add('border-emerald-400', 'text-emerald-700', 'bg-emerald-50');
        badge.innerHTML = `<span class="text-[9px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full font-bold flex items-center w-fit border border-emerald-200"><span class="material-icons text-[10px] mr-1">check_circle</span> Sesuai</span>`;
    } else {
        input.classList.remove('border-emerald-400', 'text-emerald-700', 'bg-emerald-50');
        input.classList.add('border-slate-300', 'focus:border-blue-500');
        badge.innerHTML = `<span class="text-[9px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full font-bold flex items-center w-fit border border-amber-200"><span class="material-icons text-[10px] mr-1">warning</span> Belum Sesuai</span>`;
    }
}

function recalculateHeaderTotal() {
    let total = 0;
    document.querySelectorAll('.input-nominal').forEach(input => {
        total += parseFloat(input.value.replace(/\./g, '')) || 0;
    });
    let el = document.getElementById('grandTotalReal');
    if(el) el.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
}
</script>
<?php include __DIR__.'/../partials/footer.php'; ?>