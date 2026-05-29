<!-- SURAT PENGAJUAN MODAL -->
<div id="suratModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:2500; justify-content:center; align-items:center;">
    <div class="surat-modal-card">
        
        <!-- Header -->
        <div class="surat-modal-header">
            <div style="display:flex; align-items:center; gap:14px;">
                <div class="surat-modal-icon-box">
                    <i class="ph-bold ph-file-text" style="font-size:22px;"></i>
                </div>
                <div>
                    <h3 id="suratModalTitle" style="margin:0; font-size:18px; font-weight:850; color:#0f172a; letter-spacing:-0.3px;">Cetak Surat Pengajuan</h3>
                    <p id="suratModalSub" style="margin:4px 0 0; font-size:12.5px; color:#64748b; font-weight:500;">Pilih pegawai dan isi data surat</p>
                </div>
            </div>
            <button onclick="closeSuratModal()" class="surat-modal-close-btn">
                <i class="ph-bold ph-x" style="font-size:18px;"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="surat-modal-body">

            <!-- Loading -->
            <div id="suratLoading" style="text-align:center; padding:40px; color:#64748b;">
                <i class="ph-bold ph-spinner" style="font-size:32px; color:#0f172a; animation:spin 1s linear infinite;"></i>
                <p style="margin-top:10px;">Mengambil data pegawai...</p>
            </div>

            <!-- Content (hidden until loaded) -->
            <div id="suratContent" style="display:none;">

                <!-- STEP 1: Pilih Pegawai -->
                <div style="margin-bottom:28px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; border-left:4px solid #3b82f6; padding-left:12px;">
                        <div>
                            <span style="font-size:10px; font-weight:800; color:#3b82f6; text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:2px;">Langkah 01</span>
                            <h4 style="margin:0; font-size:15px; font-weight:800; color:#0f172a; letter-spacing:-0.2px;">Pilih Pegawai</h4>
                        </div>
                        <label id="labelSelectAllSurat" style="display:none; align-items:center; gap:8px; cursor:pointer; font-size:12.5px; font-weight:700; color:#3b82f6; background:#eff6ff; padding:6px 14px; border-radius:20px; transition:all 0.2s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                            <input type="checkbox" id="suratSelectAll" onchange="suratToggleAll()" style="width:15px; height:15px; accent-color:#3b82f6; cursor:pointer; margin:0;">
                            Pilih Semua
                        </label>
                    </div>

                    <div id="suratGroupsContainer">
                        <!-- Groups injected by JS -->
                    </div>
                </div>

                <!-- STEP 2: Data Surat -->
                <div style="border-top:1px solid #f1f5f9; padding-top:24px; margin-bottom:28px;">
                    <div style="border-left:4px solid #3b82f6; padding-left:12px; margin-bottom:18px;">
                        <span style="font-size:10px; font-weight:800; color:#3b82f6; text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:2px;">Langkah 02</span>
                        <h4 style="margin:0; font-size:15px; font-weight:800; color:#0f172a; letter-spacing:-0.2px;">Data Surat Usulan</h4>
                    </div>
                    
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                        <div>
                            <label class="surat-modal-label">NOMOR SURAT</label>
                            <input type="text" id="suratNomor" placeholder="Contoh: B-123/KP.01/04/2026" class="surat-modal-input">
                        </div>
                        <div>
                            <label class="surat-modal-label">TANGGAL SURAT</label>
                            <input type="date" id="suratTanggal" class="surat-modal-input">
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label class="surat-modal-label">TUJUAN SURAT (KEPADA YTH.)</label>
                            <input type="text" id="suratTujuan" placeholder="Contoh: Kepala Biro Kepegawaian" value="Kepala Biro Kepegawaian, Organisasi, dan Tata Laksana, Sekretariat Jenderal, Kementerian Pekerjaan Umum" class="surat-modal-input">
                        </div>
                        <div id="suratKPFields" style="display:none; grid-column: 1 / -1;">
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                                <div>
                                    <label class="surat-modal-label">MASA KERJA (TH/BLN)</label>
                                    <input type="text" id="suratMasaKerja" placeholder="Kosongkan untuk hitung otomatis" class="surat-modal-input">
                                    <span style="font-size:10px; color:#94a3b8; margin-top:5px; display:block;">Kosongkan = otomatis dari data CPNS</span>
                                </div>
                                <div>
                                    <label class="surat-modal-label">KPPN</label>
                                    <input type="text" id="suratKPPN" placeholder="Contoh: V Jakarta" value="V Jakarta" class="surat-modal-input">
                                </div>
                            </div>
                        </div>
                        <div id="suratKGBFields" style="display:none; grid-column: 1 / -1;">
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                                <div style="grid-column: 1 / -1;">
                                    <label class="surat-modal-label">OLEH PEJABAT (SK LAMA)</label>
                                    <input type="text" id="kgbSkPejabat" placeholder="Contoh: Kepala Biro Kepegawaian..." value="Kepala Biro Kepegawaian, Organisasi dan Tata Laksana" class="surat-modal-input">
                                </div>
                                <div style="grid-column: 1 / -1;">
                                    <label class="surat-modal-label">NOMOR & TANGGAL SK LAMA</label>
                                    <div style="display:flex; gap:10px;">
                                        <input type="text" id="kgbSkNomor" placeholder="No. SK Lama" value="318/KPTS/M/2026" class="surat-modal-input" style="flex:1;">
                                        <input type="text" id="kgbSkTanggal" placeholder="Tgl. SK Lama" value="20 Februari 2026" class="surat-modal-input" style="flex:1;">
                                    </div>
                                </div>
                                <div>
                                    <label class="surat-modal-label">GAJI POKOK LAMA (ANGKA)</label>
                                    <input type="number" id="kgbGajiLama" placeholder="Contoh: 3186600" class="surat-modal-input">
                                    <span style="font-size:10px; color:#94a3b8; margin-top:5px; display:block;">Hanya angka, tanpa titik/koma. Teks terbilang otomatis di-generate.</span>
                                </div>
                                <div>
                                    <label class="surat-modal-label">GAJI POKOK BARU (ANGKA)</label>
                                    <input type="number" id="kgbGajiBaru" placeholder="Contoh: 3287000" class="surat-modal-input">
                                    <span style="font-size:10px; color:#94a3b8; margin-top:5px; display:block;">Hanya angka, tanpa titik/koma. Teks terbilang otomatis di-generate.</span>
                                </div>
                            </div>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label class="surat-modal-label">NAMA PENANDATANGAN</label>
                            <input type="text" id="suratNamaTTD" placeholder="Nama lengkap pejabat" value="Komang Sri Hartini" class="surat-modal-input">
                            <input type="hidden" id="suratNipTTD" value="196811201994032001">
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label class="surat-modal-label">JABATAN PENANDATANGAN</label>
                            <input type="text" id="suratJabatanTTD" placeholder="Contoh: Kepala Pusat Data dan Teknologi Informasi" value="Kepala Pusat Data dan Teknologi Informasi" class="surat-modal-input">
                        </div>

                        <!-- Narahubung Fields (Khusus KJ) -->
                        <div id="suratNarahubungFields" style="display:none; grid-column: 1 / -1; border-top:1px dashed #e2e8f0; padding-top:20px; margin-top:10px;">
                            <h5 style="margin:0 0 15px; font-size:12.5px; font-weight:800; color:#0f172a; letter-spacing:0.3px; text-transform:uppercase;"><i class="ph-bold ph-user-circle-gears" style="color:#3b82f6; font-size:18px; vertical-align:-3px; margin-right:6px;"></i> Data Narahubung</h5>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                                <div>
                                    <label class="surat-modal-label">NAMA NARAHUBUNG</label>
                                    <input type="text" id="suratNarahubungNama" placeholder="Nama narahubung (misal: Sdri. Julia)" value="Sdri. Julia" class="surat-modal-input">
                                </div>
                                <div>
                                    <label class="surat-modal-label">NO. HP NARAHUBUNG</label>
                                    <input type="text" id="suratNarahubungHp" placeholder="No. HP (misal: 0822-9824-6907)" value="0822-9824-6907" class="surat-modal-input">
                                </div>
                                <div style="grid-column: 1 / -1;">
                                    <label class="surat-modal-label">EMAIL NARAHUBUNG</label>
                                    <input type="email" id="suratNarahubungEmail" placeholder="Alamat email (misal: julia.pujilestari@pu.go.id)" value="julia.pujilestari@pu.go.id" class="surat-modal-input">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2.5: Lampiran Khusus KJ -->
                <div id="suratLampiranContainer" style="display:none; border-top:1px solid #f1f5f9; padding-top:24px; margin-top:24px; margin-bottom:28px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-left:4px solid #3b82f6; padding-left:12px;">
                        <div>
                            <span id="suratLampiranStepNumber" style="font-size:10px; font-weight:800; color:#3b82f6; text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:2px;">Langkah 03</span>
                            <h4 style="margin:0; font-size:15px; font-weight:800; color:#0f172a; letter-spacing:-0.2px;"><i class="ph-bold ph-paperclip" style="color:#3b82f6; font-size:16px; vertical-align:-1px;"></i> Kelola Dokumen Lampiran</h4>
                        </div>
                        <span style="font-size:11px; background:linear-gradient(135deg, #eff6ff, #dbeafe); color:#1e40af; padding:5px 12px; border-radius:30px; font-weight:700; border:1px solid rgba(59,130,246,0.15); box-shadow:0 2px 4px rgba(59,130,246,0.04);">Disisipkan Otomatis</span>
                    </div>
                    
                    <div class="lampiran-upload-card">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                            <div>
                                <label class="surat-modal-label">JUDUL DOKUMEN *</label>
                                <input type="text" id="lampiranJudul" placeholder="Contoh: Sertifikat Kompetensi BKN" class="surat-modal-input" style="background:#fff;">
                                <p style="font-size:10.5px; color:#64748b; margin:6px 0 0; font-weight:500; line-height:1.4;">💡 Upload 2 gambar dengan judul SAMA &rarr; otomatis dijejerin 1 halaman</p>
                            </div>
                            <div>
                                <label class="surat-modal-label">FILE *</label>
                                <input type="file" id="lampiranFile" accept=".jpg,.jpeg,.png,.pdf" style="display:none;" onchange="handleFileSelected(this)">
                                <div onclick="document.getElementById('lampiranFile').click()" id="dropZone" style="border:1.5px dashed #cbd5e1; border-radius:10px; padding:11px; text-align:center; cursor:pointer; background:#fff; transition:all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='#cbd5e1'">
                                    <p id="dropZoneText" style="margin:0; font-size:12.5px; color:#64748b; font-weight:600;"><i class="ph-bold ph-upload-simple" style="vertical-align:-1px; margin-right:4px;"></i> Klik untuk pilih file</p>
                                </div>
                                <button type="button" onclick="uploadLampiran()" id="btnUploadLampiran" style="width:100%; margin-top:8px; background:linear-gradient(135deg, #1e3a8a, #2563eb); color:#fff; border:none; padding:10px; border-radius:10px; font-size:12.5px; font-weight:700; cursor:pointer; transition:all 0.2s; box-shadow: 0 4px 10px rgba(37,99,235,0.15);" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                                    Upload
                                </button>
                            </div>
                        </div>
                        
                        <div id="uploadProgress" style="display:none; margin-bottom:20px;">
                            <p id="uploadProgressText" style="font-size:11px; color:#64748b; font-weight:600; margin:0 0 6px 0; text-align:right;">Mengupload... 0%</p>
                            <div style="background:#e2e8f0; border-radius:99px; height:6px; overflow:hidden;">
                                <div id="uploadProgressBar" style="height:100%; background:linear-gradient(90deg,#1e3a8a,#3b82f6); width:0%; transition:width 0.3s; border-radius:99px;"></div>
                            </div>
                        </div>

                        <div style="border-top:1px solid #e2e8f0; padding-top:20px; margin-top:15px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <p style="font-size:12.5px; font-weight:800; color:#334155; margin:0; text-transform:uppercase; letter-spacing:0.5px;">Lampiran Tersimpan (<span id="lampiranCount" style="color:#2563eb;">0</span>)</p>
                                <button type="button" onclick="clearAllLampiran()" id="btnClearAllLampiran" style="background:#fee2e2; border:none; color:#ef4444; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; cursor:pointer; display:none; align-items:center; gap:4px; transition:all 0.2s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
                                    <i class="ph-bold ph-trash"></i> Bersihkan Semua
                                </button>
                            </div>
                            <div id="lampiranList" style="display:flex; flex-direction:column; gap:8px; max-height:220px; overflow-y:auto; padding-right:5px;">
                            </div>
                        </div>

                        <!-- Detail Preview & Edit Judul Lampiran -->
                        <div id="lampiranDetailPreview" style="display:none; border-top:1.5px dashed #cbd5e1; padding-top:20px; margin-top:20px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <h5 style="margin:0; font-size:12.5px; font-weight:800; color:#0f172a; text-transform:uppercase; letter-spacing:0.5px;"><i class="ph-bold ph-eye" style="color:#3b82f6; font-size:16px; vertical-align:-2px; margin-right:4px;"></i> Detail Preview Lampiran</h5>
                                <button type="button" onclick="closeLampiranDetailPreview()" style="background:none; border:none; color:#ef4444; font-size:12px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:3px;"><i class="ph-bold ph-x"></i> Tutup Preview</button>
                            </div>
                            
                            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:16px; margin-bottom:15px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                                <label class="surat-modal-label" style="font-size:11px;">Ubah Judul Dokumen (Opsional untuk PDF)</label>
                                <div style="display:flex; gap:10px;">
                                    <input type="text" id="editLampiranJudul" placeholder="Ketik judul kustom untuk dicetak di PDF..." class="surat-modal-input" style="background:#fff;">
                                    <button type="button" id="btnSaveLampiranJudul" style="padding:8px 18px; background:#10b981; color:#fff; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:4px; transition:all 0.2s; box-shadow: 0 4px 10px rgba(16,185,129,0.15);" onmouseover="this.style.background='#059669'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#10b981'; this.style.transform='translateY(0)'">
                                        Simpan
                                    </button>
                                </div>
                                <span style="font-size:10px; color:#64748b; margin-top:6px; display:block; font-weight:500; line-height:1.4;">💡 Edit judul di atas lalu klik Simpan. Judul ini yang akan dicetak di atas lampiran PDF.</span>
                            </div>

                            <div id="lampiranPreviewFrameContainer" style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:10px; padding:10px; text-align:center;">
                                <!-- Media preview dynamically injected -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Preview Surat -->
                <div id="suratPreviewContainer" style="display:none; margin-top:28px; border-top:1px solid #f1f5f9; padding-top:24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-left:4px solid #ef4444; padding-left:12px;">
                        <div>
                            <span id="suratPreviewStepNumber" style="font-size:10px; font-weight:800; color:#ef4444; text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:2px;">Live Preview</span>
                            <h4 style="margin:0; font-size:15px; font-weight:800; color:#0f172a; letter-spacing:-0.2px;"><i class="ph-fill ph-file-pdf" style="color:#ef4444; font-size:16px; vertical-align:-1px;"></i> Dokumen Nota Dinas & Lampiran</h4>
                        </div>
                        <button type="button" onclick="document.getElementById('suratPreviewContainer').style.display='none'; document.getElementById('suratPreviewFrame').src=''" style="background:#fee2e2; border:none; cursor:pointer; color:#ef4444; font-size:12px; font-weight:700; padding:6px 12px; border-radius:20px; display:flex; align-items:center; gap:4px; transition:all 0.2s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'"><i class="ph-bold ph-x"></i> Tutup Preview</button>
                    </div>
                    <div style="background:#f8fafc; padding:10px; border-radius:14px; border:1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                        <iframe id="suratPreviewFrame" style="width:100%; height:500px; border:1px solid #cbd5e1; border-radius:10px; background:#fff;" src=""></iframe>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div id="suratFooter" class="surat-modal-footer" style="display:none;">
            <div style="display:flex; align-items:center; gap:8px; font-size:13.5px; font-weight:700; color:#475569;">
                <i class="ph-bold ph-users" style="color:#3b82f6; font-size:18px;"></i>
                <span id="suratSelectedCount">0 pegawai terpilih</span>
            </div>
            <div style="display:flex; gap:12px; align-items:center;">
                <button onclick="closeSuratModal()" class="surat-btn-batal">Batal</button>
                <button id="btnPreviewSurat" onclick="generateSurat(true)" class="surat-btn-preview">
                    <i class="ph-bold ph-eye"></i> Preview PDF
                </button>
                <button id="btnGenerateSurat" onclick="generateSurat(false)" class="surat-btn-download">
                    <i class="ph-bold ph-download-simple"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>
