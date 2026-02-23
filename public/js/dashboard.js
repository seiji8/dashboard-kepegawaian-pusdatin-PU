// --- 1. DROPDOWN PROFILE (Now in app-common.js) ---

// --- 2. ACCORDION TASK ---
function toggleMainTask(targetId, headerElement) {
    const targetContent = document.getElementById(targetId);
    if (targetContent.classList.contains('active')) {
        targetContent.classList.remove('active');
        headerElement.querySelector('.arrow-icon').style.transform = 'rotate(0deg)';
    } else {
        targetContent.classList.add('active');
        headerElement.querySelector('.arrow-icon').style.transform = 'rotate(180deg)';
    }
}

function toggleSubTask(targetId) {
    const targetTable = document.getElementById(targetId);
    if (targetTable.classList.contains('active')) {
        targetTable.classList.remove('active');
    } else {
        targetTable.classList.add('active');
    }
}

// --- 3. MODALS ---
const detailModal = document.getElementById('detailModal');
const reminderModal = document.getElementById('reminderModal');
const confirmModal = document.getElementById('confirmModal');
let currentDetailNip = null;
let confirmTrackerId = null;

function openDetailModal(nip) {
    currentDetailNip = nip;

    if (detailModal) detailModal.style.display = 'flex';

    // Toggle Skeleton vs Content
    const skeleton = document.getElementById('detailSkeleton');
    const contentBody = document.getElementById('modalContentBody');
    const loadingSpinner = document.getElementById('detailLoading'); // Fallback

    // Show Skeleton, Hide Content
    if (skeleton) skeleton.style.display = 'block';
    if (contentBody) contentBody.style.display = 'none';
    if (loadingSpinner) loadingSpinner.style.display = 'none';

    fetch(`/data-pegawai/${nip}`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const data = res.data;
                const initials = data.nama.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();

                // Populate Static Fields
                setText('detNama', data.nama);
                setText('detNIP', data.nip);
                setText('detJabatan', data.jabatan);
                setText('detTipeJabatan', data.tipe_jabatan);
                setText('detPangkat', data.pangkat);
                setText('detJenjang', data.jenjang);
                setText('detTmt', data.tmt_cpns);
                setText('detKredit', data.angka_kredit);
                setText('detHP', data.no_hp);
                setText('detEmail', data.email);
                setText('detAvatar', initials);
                setText('detNextKGB', data.next_kgb ? data.next_kgb : '-');

                // Populate Documents
                const docContainer = document.getElementById('docStatusContainer');
                if (docContainer) {
                    if (data.missing_documents && data.missing_documents.length > 0) {
                        let docsHtml = `
                            <div class="doc-warning-box">
                                <div class="doc-warning-header">
                                    <span>STATUS DOKUMEN</span>
                                    <span>TIDAK LENGKAP</span>
                                </div>
                        `;
                        data.missing_documents.forEach((doc, index) => {
                            docsHtml += `
                                <div class="doc-list-item">
                                    <div class="doc-number">${index + 1}</div>
                                    <div style="flex: 1;">${doc.nama_dokumen}</div>
                                </div>
                            `;
                        });
                        docsHtml += `</div>`;
                        docContainer.innerHTML = docsHtml;
                    } else {
                        docContainer.innerHTML = `
                            <div class="doc-success-box">
                                <div style="background: #10b981; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="ph-bold ph-check" style="color: white; font-size: 14px;"></i>
                                </div>
                                Semua Dokumen Lengkap
                            </div>
                        `;
                    }
                }

                // Hide Skeleton, Show Content
                if (skeleton) skeleton.style.display = 'none';
                if (loadingSpinner) loadingSpinner.style.display = 'none';
                if (contentBody) contentBody.style.display = 'flex';

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memuat data.',
                    confirmButtonColor: '#dc2626'
                });
                closeDetailModal();
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan',
                text: 'Terjadi kesalahan koneksi.',
                confirmButtonColor: '#dc2626'
            });
            closeDetailModal();
        });
}

function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.innerText = text;
}

function closeDetailModal() {
    if (detailModal) detailModal.style.display = 'none';
}

function openReminderModal() {
    if (reminderModal) reminderModal.style.display = 'flex';
}

function closeReminderModal() {
    if (reminderModal) reminderModal.style.display = 'none';
}

function openConfirmModal(trackerId, pegawaiName) {
    confirmTrackerId = trackerId;
    const nameEl = document.getElementById('confirmPegawaiName');
    if (nameEl) nameEl.textContent = pegawaiName;
    if (confirmModal) confirmModal.style.display = 'flex';
}

function closeConfirmModal() {
    if (confirmModal) confirmModal.style.display = 'none';
    confirmTrackerId = null;
}

function toggleMessageMode() {
    const isCustom = document.getElementById('checkCustom').checked;
    const selectTemplate = document.getElementById('reminderTemplate');
    const txtMessage = document.getElementById('reminderMessage');

    if (isCustom) {
        selectTemplate.disabled = true;
        selectTemplate.value = "";
        txtMessage.disabled = false;
        txtMessage.focus();
    } else {
        selectTemplate.disabled = false;
        txtMessage.disabled = true;
        txtMessage.value = "";
    }
}

function sendReminder() {
    if (!currentDetailNip) return;

    const isCustom = document.getElementById('checkCustom').checked;
    const templateId = document.getElementById('reminderTemplate').value;
    const customMessage = document.getElementById('reminderMessage').value;

    let payload = {};

    if (isCustom) {
        if (!customMessage) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Harap isi pesan custom!',
                confirmButtonColor: '#1e3a8a'
            });
            return;
        }
        payload = { custom_message: customMessage };
    } else {
        if (!templateId) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Harap pilih template!',
                confirmButtonColor: '#1e3a8a'
            });
            return;
        }
        payload = { template_id: templateId };
    }

    const btnSend = document.getElementById('btnSendManual');
    const originalText = btnSend.innerText;
    btnSend.innerText = 'Mengirim...';
    btnSend.disabled = true;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/data-pegawai/${currentDetailNip}/send-manual`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(payload)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Email berhasil dikirim!',
                    confirmButtonColor: '#1e3a8a'
                });
                closeReminderModal();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Gagal mengirim email.',
                    confirmButtonColor: '#dc2626'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan',
                text: 'Terjadi kesalahan saat mengirim email.',
                confirmButtonColor: '#dc2626'
            });
        })
        .finally(() => {
            btnSend.innerText = originalText;
            btnSend.disabled = false;
        });
}

function submitConfirm() {
    if (!confirmTrackerId) return;

    var btn = document.getElementById('confirmYesBtn');
    btn.textContent = 'Memproses...';
    btn.disabled = true;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/tracker/' + confirmTrackerId + '/confirm', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeConfirmModal();
                // Reload halaman agar list terupdate
                window.location.reload();
            }
        })
        .catch(err => {
            console.error('Gagal konfirmasi:', err);
            btn.textContent = 'Ya, Sudah Diproses';
            btn.disabled = false;
        });
}

// --- GLOBAL CLICK LISTENER (Now in app-common.js) ---

// --- 4. SINKRONISASI LOGIC (Now in app-common.js) ---

// --- 5. NOTIFICATION LOGIC (Now in app-common.js) ---

// Auto-fetch notifications on load
// Side bar drag prevention moved to app-common.js

document.addEventListener('DOMContentLoaded', function () {
    fetchNotifications();
});
