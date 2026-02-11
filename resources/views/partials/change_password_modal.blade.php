@auth
<!-- MODAL GANTI PASSWORD -->
<div id="modalChangePassword" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div class="modal-box" style="background: white; padding: 30px; border-radius: 12px; width: 500px; max-width: 90%; position: relative;">
        <div class="modal-header" style="margin-bottom: 20px; text-align: center;">
            <h2 style="font-size: 24px; color: #1e3a8a; font-weight: 700;">Ganti Kata Sandi</h2>
        </div>
        
        <div class="modal-body">
            <form id="formChangePassword">
                @csrf
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Password Saat Ini</label>
                    <div style="position: relative;">
                        <input type="password" name="current_password" class="form-input" style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid #d1d5db; border-radius: 8px;" required>
                        <i class="ph-bold ph-eye-slash toggle-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280;" onclick="togglePassword(this)"></i>
                    </div>
                    <span class="text-danger error-current_password" style="color: #dc2626; font-size: 12px; display: none;"></span>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Password Baru</label>
                    <div style="position: relative;">
                        <input type="password" name="new_password" class="form-input" style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid #d1d5db; border-radius: 8px;" required>
                        <i class="ph-bold ph-eye-slash toggle-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280;" onclick="togglePassword(this)"></i>
                    </div>
                    <span class="text-danger error-new_password" style="color: #dc2626; font-size: 12px; display: none;"></span>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Konfirmasi Password Baru</label>
                    <div style="position: relative;">
                        <input type="password" name="new_password_confirmation" class="form-input" style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid #d1d5db; border-radius: 8px;" required>
                        <i class="ph-bold ph-eye-slash toggle-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280;" onclick="togglePassword(this)"></i>
                    </div>
                </div>

                <div class="modal-footer" style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn-modal-cancel" onclick="closeChangePasswordModal()" style="flex: 1; padding: 10px; border: none; background: #fee2e2; color: #ef4444; border-radius: 8px; font-weight: 600; cursor: pointer;">Batal</button>
                    <button type="submit" class="btn-modal-save" style="flex: 1; padding: 10px; border: none; background: #dbeafe; color: #2563eb; border-radius: 8px; font-weight: 600; cursor: pointer;">Simpan</button>
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
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Simpan';

            if (status === 200) {
                closeChangePasswordModal();
                // Gunakan fungsi showToast global jika ada, atau alert fallback
                if (typeof showToast === 'function') {
                    showToast(body.message, 'success');
                } else {
                    alert(body.message);
                }
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
                alert('Terjadi kesalahan server!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Simpan';
            alert('Terjadi kesalahan jaringan!');
        });
    });
</script>
@endauth
