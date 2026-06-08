<!-- MODAL DETAIL DIKLAT -->
<div id="diklatModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2200; justify-content:center; align-items:center;">
    <div style="background:#fff; width:900px; max-width:92vw; max-height:85vh; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.15); overflow:hidden; display:flex; flex-direction:column;">
        <div style="padding:20px 25px; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <div>
                <h3 id="diklatModalTitle" style="margin:0; font-size:17px; font-weight:700; color:#0f172a;"></h3>
                <p id="diklatModalSub" style="margin:4px 0 0; font-size:13px; color:#64748b;"></p>
            </div>
            <button onclick="closeDiklatModal()" style="background:none; border:none; cursor:pointer; padding:5px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div style="padding:15px 25px 25px; overflow-y:auto; flex:1;">
            <div id="diklatModalLoading" style="text-align:center; padding:30px; color:#64748b;">
                <p>Memuat data...</p>
            </div>
            <div style="overflow-x:auto;">
                <table id="diklatModalTable" class="custom-table" style="display:none; min-width:750px;">
                    <thead>
                        <tr>
                            <th style="width:35px; text-align:center;">No</th>
                            <th style="min-width:200px;">Nama Diklat</th>
                            <th style="min-width:120px;">Periode</th>
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
