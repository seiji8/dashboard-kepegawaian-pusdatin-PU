<!-- MODAL DETAIL DIKLAT -->
<div id="diklatModal" style="display:none; position:fixed; inset:0; background:rgba(10, 18, 40, 0.55); backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px); z-index:2200; justify-content:center; align-items:center;">
    <div style="background:#fff; width:900px; max-width:92vw; max-height:85vh; border-radius:20px; box-shadow:0 32px 64px -16px rgba(20,43,111,0.25), 0 0 0 1px rgba(20,43,111,0.06); overflow:hidden; display:flex; flex-direction:column;">
        
        <!-- Header (Award/Premium Style) -->
        <div class="tm-header" style="background: linear-gradient(135deg, #142B6F 0%, #1e3a8a 100%); padding: 24px 28px; position: relative; overflow: hidden; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; border-radius: 20px 20px 0 0; color: white;">
            <div style="position: absolute; top: -30px; right: -30px; width: 140px; height: 140px; background: rgba(255,201,40,0.08); border-radius: 50%; pointer-events: none;"></div>
            <div style="position: absolute; bottom: -50px; left: -20px; width: 160px; height: 160px; background: rgba(255,255,255,0.04); border-radius: 50%; pointer-events: none;"></div>
            
            <div class="tm-header-left" style="display:flex; align-items:center; gap:14px; position:relative; z-index:1;">
                <div id="diklatModalAvatar" style="width: 50px; height: 50px; border-radius: 50%; font-size: 18px; font-weight: 700; background: rgba(255,255,255,0.15); border: 1.5px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; color: white; z-index: 1; flex-shrink: 0;">
                    -
                </div>
                <div class="tm-title-wrap">
                    <h2 id="diklatModalTitle" style="font-size: 18px; margin-bottom: 6px; margin-top: 0; color: #ffffff; font-weight: 700;">Memuat...</h2>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span id="diklatModalBadge" style="font-size:11px; font-weight:700; color:#1e3a8a; background:rgba(255,255,255,0.9); padding:4px 10px; border-radius:12px; line-height:1; letter-spacing: 0.5px; text-transform: uppercase;">DIKLAT</span>
                        <div style="width:4px; height:4px; background-color:rgba(255,255,255,0.5); border-radius:50%;"></div>
                        <span id="diklatModalSub" style="font-size:13px; font-weight:600; color:rgba(255,255,255,0.85); line-height:1; font-family: monospace;">-</span>
                    </div>
                </div>
            </div>
            <button type="button" onclick="closeDiklatModal()" class="tm-close-btn" style="background:rgba(255,255,255,0.1); border:none; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; color:#ffffff; cursor:pointer; transition:all 0.2s; position:relative; z-index:1;" onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='rotate(90deg)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='none'">
                <i class="ph-bold ph-x" style="font-size: 16px;"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="tm-body" style="padding:24px 28px; overflow-y:auto; flex:1; background: #ffffff;">
            
            <!-- Info Banner & Keterangan Pengusulan -->
            <div id="diklatModalInfoWrapper" style="display:none; margin-bottom:20px; background:#f8fafc; padding:15px; border-radius:12px; border:1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="display:grid; grid-template-columns:1fr; gap:12px;">
                    <div>
                        <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px; text-transform: uppercase;">Status Kelengkapan Dokumen</div>
                        <div id="diklatModalKeterangan" style="font-size:13px; font-weight:600; padding:10px 15px; border-radius:8px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-fill ph-warning" style="font-size: 16px;"></i> <span id="diklatModalKeteranganText">-</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Section Title -->
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">
                <i class="ph-fill ph-certificate" style="color:#1e3a8a; font-size:20px;"></i>
                <h4 style="margin:0; font-size:15px; font-weight:700; color:#1e293b;">Riwayat Pendidikan dan Keahlian (Diklat)</h4>
            </div>
            <div id="diklatModalLoading" style="text-align:center; padding:30px; color:#64748b;">
                <p>Memuat data...</p>
            </div>
            <div style="overflow-x:auto;">
                <table id="diklatModalTable" class="custom-table" style="display:none; min-width:750px;">
                    <thead>
                        <tr>
                            <th style="width:35px; text-align:center;">No</th>
                            <th style="min-width:200px;">Nama Diklat</th>
                            <th style="width:80px; text-align:center;">Jenis</th>
                            <th style="min-width:150px; text-align:center;">Arsip</th>
                            <th style="min-width:150px; text-align:center;">Arsip BPSDM</th>
                        </tr>
                    </thead>
                    <tbody id="diklatModalBody"></tbody>
                </table>
            </div>
        </div>
        
        <!-- Footer Diklat Modal -->
        <div style="padding:15px 25px; border-top:1px solid #e2e8f0; background:#f8fafc; display:flex; justify-content:flex-end; flex-shrink:0;">
            <button class="btn-reminder-yellow" onclick="openReminderModal()" style="width:auto; padding:8px 20px; margin:0; display:flex; align-items:center; gap:8px;">
                <i class="ph-bold ph-bell-ringing"></i> Kirim Pengingat
            </button>
        </div>
    </div>
</div>
