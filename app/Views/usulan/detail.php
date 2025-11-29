<?php include __DIR__.'/../partials/sidebar.php'; ?>

<div class="p-8 max-w-5xl mx-auto">
    
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start gap-4">
        <div>
            <a href="/monitoring" class="text-slate-500 hover:text-blue-600 text-xs font-bold flex items-center mb-2 uppercase tracking-wider transition-colors">
                <span class="material-icons text-sm mr-1">arrow_back</span> Kembali ke Monitoring
            </a>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight"><?php echo htmlspecialchars($usulan['nama_kegiatan']); ?></h1>
            <div class="flex items-center gap-3 mt-2 text-sm text-slate-500">
                <span class="flex items-center"><span class="material-icons text-xs mr-1">person</span> <?php echo htmlspecialchars($usulan['username']); ?></span>
                <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                <span class="flex items-center"><span class="material-icons text-xs mr-1">calendar_today</span> Diajukan: <?php echo isset($usulan['created_at']) ? date('d M Y', strtotime($usulan['created_at'])) : '-'; ?></span>
            </div>
        </div>
        
        <div class="flex gap-3 items-center">
            <?php if (
                $_SESSION['role'] === 'Pengusul' && 
                $_SESSION['user_id'] == $usulan['user_id'] && 
                in_array($usulan['status_terkini'], ['Draft', 'Revisi'])
            ): ?>
                <a href="/usulan/edit?id=<?php echo $usulan['id']; ?>" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-bold text-sm shadow-lg hover:bg-amber-600 transition-all flex items-center">
                    <span class="material-icons text-sm mr-2">edit</span> Edit Usulan
                </a>
            <?php endif; ?>

            <?php
                $s = $usulan['status_terkini'];
                $colorClass = match($s) {
                    'Disetujui', 'Selesai' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'Ditolak' => 'bg-rose-100 text-rose-700 border-rose-200',
                    'Pencairan', 'LPJ' => 'bg-blue-100 text-blue-700 border-blue-200',
                    default => 'bg-amber-100 text-amber-700 border-amber-200'
                };
            ?>
            <div class="px-4 py-2 rounded-lg border <?php echo $colorClass; ?> flex items-center shadow-sm">
                <span class="material-icons text-lg mr-2">info</span>
                <span class="font-bold text-sm uppercase tracking-wider"><?php echo $s; ?></span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-6 border-b border-slate-100 pb-4">Detail Kegiatan</h3>
                <div class="space-y-6">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Gambaran Umum</label>
                        <p class="text-slate-700 mt-1 leading-relaxed text-sm bg-slate-50 p-4 rounded-lg border border-slate-100">
                            <?php echo nl2br(htmlspecialchars($usulan['gambaran_umum'])); ?>
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase">Penerima Manfaat</label>
                            <div class="text-slate-800 font-bold mt-1"><?php echo htmlspecialchars($usulan['penerima_manfaat']); ?></div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase">Kode MAK</label>
                            <div class="font-mono text-slate-600 bg-slate-100 px-2 py-1 rounded inline-block mt-1 text-sm">
                                <?php echo $usulan['kode_mak'] ?: 'Belum ditentukan'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(!empty($rab)): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-6 border-b border-slate-100 pb-4">Rincian Anggaran (RAB)</h3>
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50">
                        <tr>
                            <th class="px-4 py-2">Uraian</th>
                            <th class="px-4 py-2 text-center">Vol</th>
                            <th class="px-4 py-2 text-right">Harga</th>
                            <th class="px-4 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($rab as $r): ?>
                        <tr>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($r['uraian']); ?></td>
                            <td class="px-4 py-2 text-center"><?php echo $r['volume'].' '.$r['satuan']; ?></td>
                            <td class="px-4 py-2 text-right"><?php echo number_format($r['harga_satuan']); ?></td>
                            <td class="px-4 py-2 text-right font-bold"><?php echo number_format($r['total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <?php if (
                $_SESSION['role'] === 'Pengusul' && 
                $usulan['user_id'] == $_SESSION['user_id'] && 
                $usulan['status_terkini'] === 'Pencairan'
            ): ?>
            <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 mt-6">
                <h3 class="font-bold text-indigo-900 mb-4 flex items-center">
                    <span class="material-icons mr-2">upload_file</span> Upload Laporan (LPJ)
                </h3>
                
                <form action="/usulan/upload-dokumen?id=<?php echo $usulan['id']; ?>" method="POST" enctype="multipart/form-data" class="mb-4 flex gap-3 items-end">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="jenis_dokumen" value="LPJ">
                    
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-indigo-400 uppercase mb-1">Pilih File (PDF/Bukti)</label>
                        <input type="file" name="dokumen" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-white file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer bg-white border border-indigo-200 rounded-lg">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 shadow-lg transition-all">
                        Upload
                    </button>
                </form>

                <div class="p-3 bg-white rounded-xl border border-indigo-100 text-center">
                    <p class="text-xs text-slate-500 mb-3">Sudah lengkap? Kirim ke Bendahara untuk diperiksa.</p>
                    <form action="/usulan/submit-lpj?id=<?php echo $usulan['id']; ?>" method="POST" onsubmit="return confirm('Yakin dokumen LPJ sudah lengkap?');">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" class="w-full py-3 bg-indigo-800 text-white font-bold rounded-lg hover:bg-indigo-900 shadow-md transition-all flex justify-center items-center">
                            <span class="material-icons text-sm mr-2">send</span> Kirim LPJ Final
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <?php if(!empty($docs)): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 mt-6">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-6 flex items-center border-b border-slate-100 pb-4">
                    <span class="material-icons text-violet-500 mr-2">attach_file</span> Dokumen Pendukung
                </h3>
                <div class="space-y-3">
                    <?php foreach($docs as $doc): ?>
                    <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="flex items-center p-4 border border-slate-100 rounded-xl hover:bg-violet-50 hover:border-violet-200 group transition-all">
                        <div class="w-12 h-12 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center mr-4 shadow-sm">
                            <span class="material-icons">description</span>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-bold text-slate-700 group-hover:text-violet-800"><?php echo htmlspecialchars($doc['jenis_dokumen']); ?></div>
                            <div class="text-xs text-slate-400 mt-0.5">Diunggah: <?php echo date('d M Y H:i', strtotime($doc['uploaded_at'])); ?></div>
                        </div>
                        <span class="material-icons text-slate-300 group-hover:text-violet-600">download</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if($usulan['status_terkini'] === 'Disetujui' || $usulan['status_terkini'] === 'Pencairan'): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-6 flex items-center">
                    <span class="material-icons text-slate-400 mr-2">folder</span> Dokumen Resmi
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="/pdf/kak?id=<?php echo $usulan['id']; ?>" target="_blank" class="flex items-center p-4 border border-slate-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center mr-3">
                            <span class="material-icons">picture_as_pdf</span>
                        </div>
                        <div>
                            <div class="font-bold text-slate-700 group-hover:text-blue-700">Cetak KAK</div>
                            <div class="text-xs text-slate-400">PDF Document</div>
                        </div>
                    </a>
                    <a href="/pdf/rab?id=<?php echo $usulan['id']; ?>" target="_blank" class="flex items-center p-4 border border-slate-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center mr-3">
                            <span class="material-icons">picture_as_pdf</span>
                        </div>
                        <div>
                            <div class="font-bold text-slate-700 group-hover:text-blue-700">Cetak RAB</div>
                            <div class="text-xs text-slate-400">PDF Document</div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-1 space-y-6">
            
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 text-white shadow-xl">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Anggaran</label>
                <div class="text-3xl font-extrabold mt-2 mb-1">Rp <?php echo number_format($usulan['nominal_pencairan'],0,',','.'); ?></div>
                <div class="text-xs text-slate-400 opacity-80">Diajukan untuk pencairan</div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-6">Riwayat Status</h3>
                
                <?php if (empty($logs)): ?>
                    <div class="text-center py-4 text-slate-400 text-sm">Belum ada histori.</div>
                <?php else: ?>
                    <div class="relative border-l-2 border-slate-100 ml-3 space-y-8 pb-2">
                        <?php foreach ($logs as $log): ?>
                        <div class="relative pl-6">
                            <div class="absolute -left-[7px] top-1.5 w-3.5 h-3.5 rounded-full border-2 border-white shadow-sm <?php echo $log['status_baru']=='Disetujui'?'bg-emerald-500':'bg-blue-500'; ?>"></div>
                            
                            <div class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($log['status_baru']); ?></div>
                            <div class="text-xs text-slate-400 mb-1 flex items-center">
                                <?php echo date('d M Y, H:i', strtotime($log['timestamp'])); ?>
                                <span class="mx-1">•</span>
                                by <?php echo htmlspecialchars($log['username']); ?>
                            </div>
                            <?php if ($log['catatan']): ?>
                                <div class="mt-2 text-xs bg-amber-50 text-amber-800 p-3 rounded-lg border border-amber-100 italic relative">
                                    <span class="material-icons absolute -top-2 -left-1 text-amber-200 text-lg transform -scale-x-100">format_quote</span>
                                    <?php echo htmlspecialchars($log['catatan']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>