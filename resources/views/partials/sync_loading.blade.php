<!-- PROGRESS MODAL -->
<div id="loadingModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2300; justify-content: center; align-items: center;">
    <div class="progress-modal-content">
        <h3 class="progress-title">Sinkronisasi Data Pegawai</h3>
        
        <!-- Overall Progress -->
        <div class="progress-bar-container">
            <div class="progress-bar-fill" id="syncProgressBar" style="width: 0%;"></div>
            <span class="progress-percentage" id="syncProgressText">0%</span>
        </div>
        
        <p class="progress-detail-text" id="syncDetailText">Bersiap memulai sinkronisasi...</p>

        <!-- 4 Steps List -->
        <ul class="progress-steps">
            <!-- Step 1 -->
            <li class="progress-step" id="step1">
                <div class="step-icon" id="step1Icon">
                    <span class="circle-pending"></span>
                </div>
                <span class="step-text">Data Utama Pegawai</span>
            </li>
            
            <!-- Step 2 -->
            <li class="progress-step" id="step2">
                <div class="step-icon" id="step2Icon">
                    <span class="circle-pending"></span>
                </div>
                <span class="step-text">Riwayat Jabatan</span>
            </li>

            <!-- Step 3 -->
            <li class="progress-step" id="step3">
                <div class="step-icon" id="step3Icon">
                    <span class="circle-pending"></span>
                </div>
                <span class="step-text">Angka Kredit</span>
            </li>

            <!-- Step 4 -->
            <li class="progress-step" id="step4">
                <div class="step-icon" id="step4Icon">
                    <span class="circle-pending"></span>
                </div>
                <span class="step-text">Riwayat Diklat</span>
            </li>
        </ul>
    </div>
</div>

<style>
/* Styling for Progress UI */
.progress-modal-content {
    background: #fff;
    width: 450px;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    font-family: 'Poppins', sans-serif;
}

.progress-title {
    margin: 0 0 20px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1e3a8a;
    text-align: center;
}

.progress-bar-container {
    background: #e2e8f0;
    height: 22px;
    border-radius: 11px;
    position: relative;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-bar-fill {
    height: 100%;
    background: #3b82f6;
    border-radius: 11px;
    transition: width 0.4s ease;
}

.progress-percentage {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 12px;
    font-weight: 800;
    color: #ffffff;
    text-shadow: 0 1px 3px rgba(0,0,0,0.5), 0 0 6px rgba(30,58,138,0.7);
    z-index: 2;
}

.progress-detail-text {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 25px;
    text-align: center;
    font-style: italic;
}

.progress-steps {
    list-style: none;
    padding: 0;
    margin: 0;
}

.progress-step {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f1f5f9;
}

.progress-step:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.step-icon {
    width: 24px;
    height: 24px;
    margin-right: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.step-text {
    font-size: 14px;
    font-weight: 500;
    color: #475569;
}

/* Status Icons */
.circle-pending {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #cbd5e1;
}

.icon-processing {
    color: #3b82f6;
    font-size: 20px;
    animation: spin-icon 1s linear infinite;
}

.icon-done {
    color: #10b981;
    font-size: 24px;
}

/* Text Highlights */
.progress-step.processing .step-text {
    color: #1e40af;
    font-weight: 600;
}

.progress-step.done .step-text {
    color: #065f46;
}

@keyframes spin-icon {
    100% { transform: rotate(360deg); }
}
</style>

<!-- SYNC TOAST -->
<div id="syncToast" class="toast-notification">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>
    <span>Sinkronisasi Data Berhasil!</span>
</div>
