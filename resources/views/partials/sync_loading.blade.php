<!-- LOADING MODAL -->
<div id="loadingModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2300; justify-content: center; align-items: center;">
    <div class="loading-modal-content">
        <div class="loading-spinner"></div>
        <div>
            <p class="loading-text">Sedang Sinkronisasi Data...</p>
            <p class="loading-subtext">Mohon tunggu, proses ini mungkin memakan waktu beberapa saat.</p>
        </div>
    </div>
</div>

<!-- SYNC TOAST -->
<div id="syncToast" class="toast-notification">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>
    <span>Sinkronisasi Data Berhasil!</span>
</div>
