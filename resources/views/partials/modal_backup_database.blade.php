{{-- ============================================================
     MODAL: Konfirmasi Backup Database
     Included di: layouts/app.blade.php
     Trigger    : openBackupModal() dari navbar
     ============================================================ --}}

<style>
    /* ===== Overlay ===== */
    #backupModalOverlay {
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(10, 18, 40, 0.55);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        display: flex; align-items: center; justify-content: center;
        opacity: 0; visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
    }
    #backupModalOverlay.open {
        opacity: 1; visibility: visible;
    }

    /* ===== Card ===== */
    #backupModalCard {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 32px 64px -16px rgba(20,43,111,0.25), 0 0 0 1px rgba(20,43,111,0.06);
        width: 100%; max-width: 420px;
        padding: 0;
        overflow: hidden;
        transform: translateY(20px) scale(0.97);
        transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);
    }
    #backupModalOverlay.open #backupModalCard {
        transform: translateY(0) scale(1);
    }

    /* ===== Header Gradient ===== */
    .bm-header {
        background: linear-gradient(135deg, #142B6F 0%, #1e3a8a 100%);
        padding: 28px 28px 24px;
        position: relative;
        overflow: hidden;
    }
    .bm-header::before {
        content: '';
        position: absolute; top: -30px; right: -30px;
        width: 140px; height: 140px;
        background: rgba(255,201,40,0.08);
        border-radius: 50%;
    }
    .bm-header::after {
        content: '';
        position: absolute; bottom: -50px; left: -20px;
        width: 160px; height: 160px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }
    .bm-icon-wrap {
        width: 56px; height: 56px;
        background: rgba(255,201,40,0.15);
        border: 1.5px solid rgba(255,201,40,0.3);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 14px;
        position: relative; z-index: 1;
    }
    .bm-title {
        font-size: 18px; font-weight: 700;
        color: #ffffff; margin: 0 0 4px;
        position: relative; z-index: 1;
    }
    .bm-subtitle {
        font-size: 13px; color: rgba(255,255,255,0.65);
        margin: 0; position: relative; z-index: 1;
    }

    /* ===== Body ===== */
    .bm-body { padding: 24px 28px; }

    /* Info Row */
    .bm-info-row {
        display: flex; align-items: center; gap: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 10px;
    }
    .bm-info-icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; font-size: 18px;
    }
    .bm-info-label {
        font-size: 11px; font-weight: 600;
        color: #94a3b8; text-transform: uppercase; letter-spacing: 0.6px;
        margin: 0 0 2px;
    }
    .bm-info-value {
        font-size: 13px; font-weight: 600; color: #1e293b; margin: 0;
    }

    /* Warning notice */
    .bm-warning {
        background: #fffbeb;
        border: 1.5px solid #fde68a;
        border-radius: 10px;
        padding: 12px 14px;
        display: flex; align-items: flex-start; gap: 10px;
        margin-top: 16px;
        font-size: 12.5px; color: #92400e; line-height: 1.6;
    }

    /* ===== Footer Buttons ===== */
    .bm-footer {
        padding: 0 28px 24px;
        display: flex; gap: 10px;
    }
    .bm-btn-cancel {
        flex: 1; padding: 12px;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        font-size: 14px; font-weight: 600; color: #64748b;
        cursor: pointer; transition: all 0.2s ease;
        font-family: inherit;
    }
    .bm-btn-cancel:hover {
        background: #f1f5f9; border-color: #cbd5e1; color: #374151;
    }
    .bm-btn-confirm {
        flex: 2; padding: 12px;
        border-radius: 10px; border: none;
        background: linear-gradient(135deg, #142B6F 0%, #1e3a8a 100%);
        font-size: 14px; font-weight: 700; color: #ffffff;
        cursor: pointer; transition: all 0.2s ease;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        box-shadow: 0 4px 12px rgba(20,43,111,0.25);
        font-family: inherit;
    }
    .bm-btn-confirm:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(20,43,111,0.35);
    }
    .bm-btn-confirm:active { transform: translateY(0); }

    /* Loading spinner inside confirm btn */
    .bm-spinner {
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        display: none;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<!-- ===== MODAL OVERLAY ===== -->
<div id="backupModalOverlay" onclick="handleBackupOverlayClick(event)">
    <div id="backupModalCard" role="dialog" aria-modal="true" aria-labelledby="backupModalTitle">

        <!-- Header -->
        <div class="bm-header">
            <div class="bm-icon-wrap">
                <i class="ph-fill ph-database" style="font-size:28px;color:#FFC928;"></i>
            </div>
            <p class="bm-title" id="backupModalTitle">Konfirmasi Backup Database</p>
            <p class="bm-subtitle">Tinjau detail sebelum memulai proses backup</p>
        </div>

        <!-- Body -->
        <div class="bm-body">

            <!-- Info: Format -->
            <div class="bm-info-row">
                <div class="bm-info-icon" style="background:#eff6ff;">
                    <i class="ph-fill ph-file-sql" style="color:#3b82f6;"></i>
                </div>
                <div>
                    <p class="bm-info-label">Format File</p>
                    <p class="bm-info-value">SQL &mdash; Struktural + Data Lengkap</p>
                </div>
            </div>

            <!-- Info: Database -->
            <div class="bm-info-row">
                <div class="bm-info-icon" style="background:#f0fdf4;">
                    <i class="ph-fill ph-stack" style="color:#22c55e;"></i>
                </div>
                <div>
                    <p class="bm-info-label">Database</p>
                    <p class="bm-info-value" id="bm-dbname">{{ config('database.connections.mysql.database') }}</p>
                </div>
            </div>

            <!-- Info: Timestamp -->
            <div class="bm-info-row">
                <div class="bm-info-icon" style="background:#fefce8;">
                    <i class="ph-fill ph-clock" style="color:#eab308;"></i>
                </div>
                <div>
                    <p class="bm-info-label">Waktu Backup</p>
                    <p class="bm-info-value" id="bm-timestamp">—</p>
                </div>
            </div>

            <!-- Warning -->
            <div class="bm-warning">
                <i class="ph-fill ph-warning" style="font-size:16px;flex-shrink:0;margin-top:2px;color:#d97706;"></i>
                <span>
                    Simpan file backup di tempat yang <strong>aman dan terlindungi</strong>.
                    File ini berisi seluruh data kepegawaian dan bersifat rahasia.
                </span>
            </div>
        </div>

        <!-- Footer -->
        <div class="bm-footer">
            <button class="bm-btn-cancel" onclick="closeBackupModal()">
                Batal
            </button>
            <button class="bm-btn-confirm" id="bm-confirm-btn" onclick="executeBackup()">
                <div class="bm-spinner" id="bm-spinner"></div>
                <i class="ph-bold ph-download-simple" id="bm-btn-icon"></i>
                <span id="bm-btn-text">Ya, Backup Sekarang</span>
            </button>
        </div>

    </div>
</div>

<script>
    function openBackupModal() {
        // Update timestamp realtime
        const now = new Date();
        const opts = { day:'2-digit', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit' };
        document.getElementById('bm-timestamp').textContent = now.toLocaleDateString('id-ID', opts);

        document.getElementById('backupModalOverlay').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeBackupModal() {
        document.getElementById('backupModalOverlay').classList.remove('open');
        document.body.style.overflow = '';

        // Reset button state
        setTimeout(resetBackupBtn, 300);
    }

    function handleBackupOverlayClick(e) {
        if (e.target === document.getElementById('backupModalOverlay')) {
            closeBackupModal();
        }
    }

    function executeBackup() {
        const btn    = document.getElementById('bm-confirm-btn');
        const spinner = document.getElementById('bm-spinner');
        const icon   = document.getElementById('bm-btn-icon');
        const text   = document.getElementById('bm-btn-text');

        // Loading state
        btn.disabled  = true;
        spinner.style.display = 'block';
        icon.style.display    = 'none';
        text.textContent      = 'Memproses...';

        // Trigger download
        setTimeout(function() {
            window.location.href = '{{ route("database.backup") }}';
            // Close modal setelah download dimulai
            setTimeout(closeBackupModal, 1500);
        }, 600);
    }

    function resetBackupBtn() {
        const btn    = document.getElementById('bm-confirm-btn');
        const spinner = document.getElementById('bm-spinner');
        const icon   = document.getElementById('bm-btn-icon');
        const text   = document.getElementById('bm-btn-text');

        btn.disabled  = false;
        spinner.style.display = 'none';
        icon.style.display    = 'inline';
        text.textContent      = 'Ya, Backup Sekarang';
    }

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeBackupModal();
    });
</script>
