@auth
<!-- ============================================================
     MODAL: Ganti Password
     Trigger    : openChangePasswordModal() dari navbar
     ============================================================ -->

<style>
    /* ===== Overlay ===== */
    #modalChangePassword {
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(10, 18, 40, 0.55);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        display: flex; align-items: center; justify-content: center;
        opacity: 0; visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
    }
    #modalChangePassword.open {
        opacity: 1; visibility: visible;
    }

    /* ===== Card ===== */
    .cp-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 32px 64px -16px rgba(20,43,111,0.25), 0 0 0 1px rgba(20,43,111,0.06);
        width: 100%; max-width: 420px;
        padding: 0;
        overflow: hidden;
        transform: translateY(20px) scale(0.97);
        transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);
    }
    #modalChangePassword.open .cp-card {
        transform: translateY(0) scale(1);
    }

    /* ===== Header Gradient ===== */
    .cp-header {
        background: linear-gradient(135deg, #142B6F 0%, #1e3a8a 100%);
        padding: 28px 28px 24px;
        position: relative;
        overflow: hidden;
        text-align: center;
    }
    .cp-header::before {
        content: '';
        position: absolute; top: -30px; right: -30px;
        width: 140px; height: 140px;
        background: rgba(255,201,40,0.08);
        border-radius: 50%;
    }
    .cp-header::after {
        content: '';
        position: absolute; bottom: -50px; left: -20px;
        width: 160px; height: 160px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }
    .cp-icon-wrap {
        width: 56px; height: 56px;
        background: rgba(255,255,255,0.1);
        border: 1.5px solid rgba(255,255,255,0.2);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px auto;
        position: relative; z-index: 1;
    }
    .cp-title {
        font-size: 18px; font-weight: 700;
        color: #ffffff; margin: 0 0 4px;
        position: relative; z-index: 1;
    }
    .cp-subtitle {
        font-size: 13px; color: rgba(255,255,255,0.65);
        margin: 0; position: relative; z-index: 1;
    }

    /* ===== Body ===== */
    .cp-body { padding: 24px 28px; }

    /* Forms */
    .cp-form-group { margin-bottom: 18px; }
    .cp-label {
        display: block; margin-bottom: 6px; 
        font-size: 11px; font-weight: 700; 
        color: #64748b; letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .cp-input-wrap { position: relative; }
    .cp-input {
        width: 100%;
        padding: 12px 40px 12px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px; color: #1e293b;
        background: #f8fafc;
        transition: all 0.2s ease;
        outline: none; box-sizing: border-box;
    }
    .cp-input:focus {
        border-color: #142B6F;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(20,43,111,0.08);
    }
    .cp-input::placeholder { color: #94a3b8; }
    
    .cp-toggle-icon {
        position: absolute; right: 14px; top: 50%;
        transform: translateY(-50%); cursor: pointer;
        color: #94a3b8; font-size: 18px; transition: color 0.2s;
    }
    .cp-toggle-icon:hover { color: #142B6F; }

    .cp-error {
        color: #dc2626; font-size: 12px; font-weight: 500;
        display: none; margin-top: 6px;
        display: flex; align-items: center; gap: 4px;
    }

    /* ===== Footer Buttons ===== */
    .cp-footer {
        padding: 0 28px 24px;
        display: flex; gap: 10px;
    }
    .cp-btn-cancel {
        flex: 1; padding: 12px;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        font-size: 14px; font-weight: 600; color: #64748b;
        cursor: pointer; transition: all 0.2s ease;
        font-family: inherit;
    }
    .cp-btn-cancel:hover {
        background: #f1f5f9; border-color: #cbd5e1; color: #374151;
    }
    .cp-btn-confirm {
        flex: 2; padding: 12px;
        border-radius: 10px; border: none;
        background: linear-gradient(135deg, #142B6F 0%, #1e3a8a 100%);
        font-size: 14px; font-weight: 700; color: #ffffff;
        cursor: pointer; transition: all 0.2s ease;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        box-shadow: 0 4px 12px rgba(20,43,111,0.25);
        font-family: inherit;
    }
    .cp-btn-confirm:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(20,43,111,0.35);
    }
    .cp-btn-confirm:active { transform: translateY(0); }
    .cp-btn-confirm:disabled { opacity: 0.7; pointer-events: none; }

    /* Loading spinner */
    .cp-spinner {
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: cpSpin 0.7s linear infinite;
        display: none;
    }
    @keyframes cpSpin { to { transform: rotate(360deg); } }
</style>

<div id="modalChangePassword" onclick="handleCpOverlayClick(event)">
    <div class="cp-card" role="dialog" aria-modal="true">
        
        <!-- Header -->
        <div class="cp-header">
            <div class="cp-icon-wrap">
                <i class="ph-fill ph-lock-key" style="font-size: 28px; color: #ffffff;"></i>
            </div>
            <h2 class="cp-title">Ganti Kata Sandi</h2>
            <p class="cp-subtitle">Perbarui kata sandi Anda secara berkala</p>
        </div>

        <form id="formChangePassword">
            @csrf
            
            <!-- Body -->
            <div class="cp-body">
                
                <!-- Password Saat Ini -->
                <div class="cp-form-group">
                    <label class="cp-label">Password Saat Ini</label>
                    <div class="cp-input-wrap">
                        <input type="password" name="current_password" class="cp-input" placeholder="Masukkan password lama" autocomplete="off" required>
                        <i class="ph-bold ph-eye-slash cp-toggle-icon" onclick="toggleCpPassword(this)"></i>
                    </div>
                    <div class="cp-error error-current_password" style="display: none;">
                        <i class="ph-fill ph-warning-circle"></i> <span></span>
                    </div>
                </div>

                <!-- Password Baru -->
                <div class="cp-form-group">
                    <label class="cp-label">Password Baru</label>
                    <div class="cp-input-wrap">
                        <input type="password" name="new_password" class="cp-input" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
                        <i class="ph-bold ph-eye-slash cp-toggle-icon" onclick="toggleCpPassword(this)"></i>
                    </div>
                    <div class="cp-error error-new_password" style="display: none;">
                        <i class="ph-fill ph-warning-circle"></i> <span></span>
                    </div>
                </div>

                <!-- Konfirmasi Password -->
                <div class="cp-form-group" style="margin-bottom: 0;">
                    <label class="cp-label">Konfirmasi Password Baru</label>
                    <div class="cp-input-wrap">
                        <input type="password" name="new_password_confirmation" class="cp-input" placeholder="Ketik ulang password baru" autocomplete="new-password" required>
                        <i class="ph-bold ph-eye-slash cp-toggle-icon" onclick="toggleCpPassword(this)"></i>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="cp-footer">
                <button type="button" class="cp-btn-cancel" onclick="closeChangePasswordModal()">Batal</button>
                <button type="submit" class="cp-btn-confirm" id="cp-submit-btn">
                    <div class="cp-spinner" id="cp-spinner"></div>
                    <i class="ph-bold ph-check-circle" id="cp-btn-icon"></i>
                    <span id="cp-btn-text">Simpan Perubahan</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function toggleCpPassword(icon) {
        const input = icon.previousElementSibling;
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("ph-eye-slash");
            icon.classList.add("ph-eye");
            icon.style.color = '#142B6F';
        } else {
            input.type = "password";
            icon.classList.remove("ph-eye");
            icon.classList.add("ph-eye-slash");
            icon.style.color = '#94a3b8';
        }
    }

    function openChangePasswordModal() {
        const modal = document.getElementById('modalChangePassword');
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
        
        document.getElementById('formChangePassword').reset();
        
        // Reset icons to eye-slash
        document.querySelectorAll('.cp-toggle-icon').forEach(icon => {
            icon.classList.remove("ph-eye");
            icon.classList.add("ph-eye-slash");
            icon.style.color = '#94a3b8';
            icon.previousElementSibling.type = "password";
        });
        
        // Hide errors
        document.querySelectorAll('.cp-error').forEach(el => {
            el.style.display = 'none';
        });
    }

    function closeChangePasswordModal() {
        const modal = document.getElementById('modalChangePassword');
        modal.classList.remove('open');
        document.body.style.overflow = '';
        
        // Reset button state
        setTimeout(() => {
            const btn = document.getElementById('cp-submit-btn');
            btn.disabled = false;
            document.getElementById('cp-spinner').style.display = 'none';
            document.getElementById('cp-btn-icon').style.display = 'inline-block';
            document.getElementById('cp-btn-text').textContent = 'Simpan Perubahan';
        }, 300);
    }

    function handleCpOverlayClick(e) {
        if (e.target === document.getElementById('modalChangePassword')) {
            closeChangePasswordModal();
        }
    }

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('modalChangePassword').classList.contains('open')) {
            closeChangePasswordModal();
        }
    });

    // AJAX Submission
    document.getElementById('formChangePassword').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        let submitBtn = document.getElementById('cp-submit-btn');
        let spinner   = document.getElementById('cp-spinner');
        let btnIcon   = document.getElementById('cp-btn-icon');
        let btnText   = document.getElementById('cp-btn-text');

        // Loading state
        submitBtn.disabled = true;
        spinner.style.display = 'block';
        btnIcon.style.display = 'none';
        btnText.textContent = 'Menyimpan...';

        // Reset errors
        document.querySelectorAll('.cp-error').forEach(el => {
            el.style.display = 'none';
            el.querySelector('span').textContent = '';
        });

        fetch("{{ route('change-password.update') }}", {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            // Restore button state
            submitBtn.disabled = false;
            spinner.style.display = 'none';
            btnIcon.style.display = 'inline-block';
            btnText.textContent = 'Simpan Perubahan';

            if (status === 200) {
                closeChangePasswordModal();
                showCustomToast(body.message, 'success');
            } else if (status === 422) {
                // Validation Errors
                for (let [key, messages] of Object.entries(body.errors)) {
                    let errorDiv = document.querySelector(`.error-${key}`);
                    if (errorDiv) {
                        errorDiv.querySelector('span').textContent = messages[0];
                        errorDiv.style.display = 'flex';
                    }
                }
            } else {
                showCustomToast('Terjadi kesalahan server!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.disabled = false;
            spinner.style.display = 'none';
            btnIcon.style.display = 'inline-block';
            btnText.textContent = 'Simpan Perubahan';
            showCustomToast('Terjadi kesalahan jaringan!', 'error');
        });
    });
</script>
@endauth
