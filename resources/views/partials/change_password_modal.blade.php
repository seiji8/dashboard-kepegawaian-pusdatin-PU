@auth
<!-- MODAL GANTI PASSWORD -->
<div id="modalChangePassword" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div class="modal-box" style="background: white; padding: 35px 30px; border-radius: 16px; width: 450px; max-width: 90%; position: relative; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
        <div class="modal-header" style="margin-bottom: 25px; text-align: center;">
            <div style="background:#eff6ff; width:70px; height:70px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin: 0 auto 15px auto; border: 1px solid #dbeafe;">
                <i class="ph-fill ph-lock-key" style="font-size: 36px; color: #1e3a8a;"></i>
            </div>
            <h2 style="font-size: 20px; color: #0f172a; font-weight: 700; margin: 0;">Ganti Kata Sandi</h2>
            <p style="font-size: 13px; color: #64748b; margin: 5px 0 0 0;">Perbarui kata sandi Anda untuk menjaga keamanan akun.</p>
        </div>
        
        <div class="modal-body">
            <form id="formChangePassword">
                @csrf
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 6px; font-size: 12px; font-weight: 700; color: #475569; letter-spacing: 0.3px;">PASSWORD SAAT INI</label>
                    <div style="position: relative;">
                        <input type="password" name="current_password" class="form-input" style="width: 100%; padding: 12px 14px; padding-right: 40px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; transition: all 0.2s; box-sizing: border-box;" autocomplete="off" onfocus="this.style.borderColor='#1e3a8a'; this.style.boxShadow='0 0 0 3px rgba(30,58,138,0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'" required>
                        <i class="ph-bold ph-eye-slash toggle-password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; font-size: 18px; transition: color 0.2s;" onmouseover="this.style.color='#1e3a8a'" onmouseout="this.style.color='#94a3b8'" onclick="togglePassword(this)"></i>
                    </div>
                    <span class="text-danger error-current_password" style="color: #ef4444; font-size: 12px; display: none; margin-top: 5px; font-weight: 500;"></span>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 6px; font-size: 12px; font-weight: 700; color: #475569; letter-spacing: 0.3px;">PASSWORD BARU</label>
                    <div style="position: relative;">
                        <input type="password" name="new_password" class="form-input" style="width: 100%; padding: 12px 14px; padding-right: 40px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; transition: all 0.2s; box-sizing: border-box;" autocomplete="new-password" onfocus="this.style.borderColor='#1e3a8a'; this.style.boxShadow='0 0 0 3px rgba(30,58,138,0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'" required>
                        <i class="ph-bold ph-eye-slash toggle-password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; font-size: 18px; transition: color 0.2s;" onmouseover="this.style.color='#1e3a8a'" onmouseout="this.style.color='#94a3b8'" onclick="togglePassword(this)"></i>
                    </div>
                    <span class="text-danger error-new_password" style="color: #ef4444; font-size: 12px; display: none; margin-top: 5px; font-weight: 500;"></span>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="display: block; margin-bottom: 6px; font-size: 12px; font-weight: 700; color: #475569; letter-spacing: 0.3px;">KONFIRMASI PASSWORD BARU</label>
                    <div style="position: relative;">
                        <input type="password" name="new_password_confirmation" class="form-input" style="width: 100%; padding: 12px 14px; padding-right: 40px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; transition: all 0.2s; box-sizing: border-box;" autocomplete="new-password" onfocus="this.style.borderColor='#1e3a8a'; this.style.boxShadow='0 0 0 3px rgba(30,58,138,0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'" required>
                        <i class="ph-bold ph-eye-slash toggle-password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; font-size: 18px; transition: color 0.2s;" onmouseover="this.style.color='#1e3a8a'" onmouseout="this.style.color='#94a3b8'" onclick="togglePassword(this)"></i>
                    </div>
                </div>

                <div class="modal-footer" style="display: flex; gap: 12px;">
                    <button type="button" onclick="closeChangePasswordModal()" style="flex: 1; padding: 12px; background: white; color: #64748b; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; font-family: 'Poppins', sans-serif;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">Batal</button>
                    <button type="submit" style="flex: 1; padding: 12px; background: #1e3a8a; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(30,58,138,0.2); font-family: 'Poppins', sans-serif;" onmouseover="this.style.background='#1e40af'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#1e3a8a'; this.style.transform='translateY(0)'">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePassword(icon) {
        const input = icon.previousElementSibling;
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("ph-eye-slash");
            icon.classList.add("ph-eye");
        } else {
            input.type = "password";
            icon.classList.remove("ph-eye");
            icon.classList.add("ph-eye-slash");
        }
    }

    function openChangePasswordModal() {
        document.getElementById('modalChangePassword').style.display = 'flex';
        document.getElementById('formChangePassword').reset();
        // Reset icons to eye-slash
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.classList.remove("ph-eye");
            icon.classList.add("ph-eye-slash");
            icon.previousElementSibling.type = "password";
        });
        document.querySelectorAll('.text-danger').forEach(el => el.style.display = 'none');
    }

    function closeChangePasswordModal() {
        document.getElementById('modalChangePassword').style.display = 'none';
    }

    // Close on outside click
    window.addEventListener('click', function(e) {
        if (e.target == document.getElementById('modalChangePassword')) {
            closeChangePasswordModal();
        }
    });

    // AJAX Submission
    document.getElementById('formChangePassword').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        let submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyimpan...';

        // Reset errors
        document.querySelectorAll('.text-danger').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
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
            submitBtn.disabled = false;
            submitBtn.textContent = 'Simpan';

            if (status === 200) {
                closeChangePasswordModal();
                showCustomToast(body.message, 'success');
            } else if (status === 422) {
                // Validation Errors
                for (let [key, messages] of Object.entries(body.errors)) {
                    let errorSpan = document.querySelector(`.error-${key}`);
                    if (errorSpan) {
                        errorSpan.textContent = messages[0];
                        errorSpan.style.display = 'block';
                    }
                }
            } else {
                showCustomToast('Terjadi kesalahan server!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Simpan';
            showCustomToast('Terjadi kesalahan jaringan!', 'error');
        });
    });
</script>
@endauth
