{{-- MODAL: Konfirmasi Usulan KP & KGB --}}
<div id="modalKonfirmasiUsulan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:620px; max-width:95vw; max-height:90vh; overflow:hidden; display:flex; flex-direction:column; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <!-- Header -->
        <div style="background:linear-gradient(135deg,#1e3a8a,#15803d); padding:20px 24px; color:white; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
            <div>
                <h3 style="margin:0; font-size:17px; font-weight:700;"><i class="ph-fill ph-check-circle" style="font-size:16px; vertical-align:-2px;"></i> Konfirmasi Usulan</h3>
                <p id="konfirmasiSubtitle" style="margin:4px 0 0; font-size:12px; opacity:0.85;">Kenaikan Pangkat / KGB</p>
            </div>
            <button onclick="closeKonfirmasiModal()" style="background:rgba(255,255,255,0.2); border:none; border-radius:8px; color:white; width:32px; height:32px; cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center;">&times;</button>
        </div>
        <!-- Info Banner -->
        <div style="background:#f0fdf4; border-left:4px solid #16a34a; margin:16px 24px 0; padding:12px 16px; border-radius:0 8px 8px 0;">
            <p style="margin:0; font-size:12px; color:#15803d; line-height:1.5;">
                <strong>Informasi:</strong> Surat pengajuan untuk kategori ini dibuat langsung melalui <strong>E-HRM</strong>.
                Klik konfirmasi untuk menandai bahwa usulan sudah diproses dan mengubah status ke <strong>Proses TTE</strong>.
            </p>
        </div>
        <!-- Body -->
        <div style="padding:16px 24px; overflow-y:auto; flex:1;">
            <!-- Catatan -->
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Catatan / Keterangan <span style="color:#9ca3af; font-weight:400;">(opsional)</span></label>
                <textarea id="konfirmasiCatatan" rows="3" placeholder="Contoh: Sudah diinput ke E-HRM pada 30 April 2026 oleh Admin..." style="width:100%; border:1.5px solid #d1d5db; border-radius:8px; padding:10px 12px; font-size:13px; font-family:'Inter',sans-serif; resize:vertical; outline:none; box-sizing:border-box; transition:border 0.2s;" onfocus="this.style.borderColor='#1e3a8a'" onblur="this.style.borderColor='#d1d5db'"></textarea>
            </div>
            <!-- Daftar Pegawai -->
            <div style="font-size:13px; font-weight:600; color:#374151; margin-bottom:10px;">
                Pilih Pegawai yang akan Dikonfirmasi:
            </div>
            <div id="konfirmasiPegawaiList" style="display:flex; flex-direction:column; gap:6px; max-height:280px; overflow-y:auto;">
                <div style="text-align:center; padding:20px; color:#9ca3af;">Memuat data...</div>
            </div>
            <!-- Pilih Semua -->
            <div style="margin-top:10px; display:flex; gap:8px;">
                <button onclick="toggleSelectAllKonfirmasi(true)" style="font-size:12px; padding:5px 12px; background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; border-radius:6px; cursor:pointer;"><i class="ph-fill ph-check-square" style="vertical-align:-1px;"></i> Pilih Semua</button>
                <button onclick="toggleSelectAllKonfirmasi(false)" style="font-size:12px; padding:5px 12px; background:#f9fafb; color:#6b7280; border:1px solid #e5e7eb; border-radius:6px; cursor:pointer;"><i class="ph-bold ph-square" style="vertical-align:-1px;"></i> Batal Semua</button>
            </div>
        </div>
        <!-- Footer -->
        <div style="padding:16px 24px; border-top:1px solid #f1f5f9; display:flex; justify-content:flex-end; gap:10px; flex-shrink:0;">
            <button onclick="closeKonfirmasiModal()" style="padding:10px 20px; background:#f1f5f9; color:#374151; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:13px;">Batal</button>
            <button id="btnSubmitKonfirmasi" onclick="submitKonfirmasi()" style="padding:10px 22px; background:linear-gradient(135deg,#1e3a8a,#15803d); color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:13px; display:flex; align-items:center; gap:8px;">
                <i class="ph-bold ph-check-circle"></i> Konfirmasi Usulan
            </button>
        </div>
    </div>
</div>
