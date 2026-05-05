let currentDeleteNip = null;
let currentDetailNip = null;

// === FETCH DETAIL PEGAWAI ===
function openDetailModal(nip) {
    currentDetailNip = nip;
    document.getElementById('modalDetailPegawai').style.display = 'flex';

    // Toggle Skeleton vs Content
    const skeleton = document.getElementById('detailSkeleton');
    const content = document.getElementById('detailContent');
    const loadingSpinner = document.getElementById('detailLoading'); // Fallback if skeleton not present

    if (skeleton && content) {
        skeleton.style.display = 'block';
        content.style.display = 'none';
        if (loadingSpinner) loadingSpinner.style.display = 'none';
    } else if (loadingSpinner && content) {
        // Fallback to spinner if skeleton element missing
        loadingSpinner.style.display = 'block';
        content.style.display = 'none';
    }

    fetch(`/data-pegawai/${nip}`)
        .then(response => response.json())
        .then(res => {
            const data = res.data;

            // Populate Data
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

            // Initials Avatar
            const initials = data.nama.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
            setText('detAvatar', initials);

            // Next KGB
            setText('detNextKGB', data.next_kgb ? data.next_kgb : '-');

            // Documents
            const docContainer = document.getElementById('docStatusContainer');
            if (docContainer) {
                if (data.missing_documents && data.missing_documents.length > 0) {
                    let docListHtml = `
                        <div class="doc-warning-box">
                            <div class="doc-warning-header">
                                <span>STATUS DOKUMEN</span>
                                <span>TIDAK LENGKAP</span>
                            </div>
                    `;
                    data.missing_documents.forEach((doc, index) => {
                        docListHtml += `
                            <div class="doc-list-item">
                                <div class="doc-number">${index + 1}</div>
                                <div style="flex: 1;">${doc.nama_dokumen}</div>
                            </div>
                        `;
                    });
                    docListHtml += `</div>`;
                    docContainer.innerHTML = docListHtml;
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

            // Hide Skeleton/Spinner, Show Content
            if (skeleton) skeleton.style.display = 'none';
            if (loadingSpinner) loadingSpinner.style.display = 'none';
            if (content) content.style.display = 'flex';
        })
        .catch(err => {
            console.error(err);
            showCustomToast('Gagal mengambil data pegawai.', 'error');
            closeDetailModal();
        });
}

function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.innerText = text;
}

function closeDetailModal() {
    document.getElementById('modalDetailPegawai').style.display = 'none';
}

// === DELETE LOGIC ===
function openDeleteModal(nip, nama) {
    currentDeleteNip = nip;
    document.getElementById('modalHapusPegawai').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('modalHapusPegawai').style.display = 'none';
    currentDeleteNip = null;
}

function confirmDelete() {
    if (!currentDeleteNip) return;

    fetch(`/data-pegawai/${currentDeleteNip}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeDeleteModal();
                showCustomToast('Pegawai berhasil dihapus!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showCustomToast(data.message || 'Gagal menghapus pegawai!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showCustomToast('Terjadi kesalahan pada server!', 'error');
        });
}

// === REMINDER LOGIC ===
let reminderTomSelectDP = null;

function openReminderModal() {
    document.getElementById('modalReminder').style.display = 'flex';
}

function closeReminderModal() {
    document.getElementById('modalReminder').style.display = 'none';
    // Reset Tom Select value
    if (reminderTomSelectDP) {
        reminderTomSelectDP.clear();
        reminderTomSelectDP.enable();
    }
    // Reset form state
    const checkbox = document.getElementById('checkCustom');
    if (checkbox) checkbox.checked = false;
    const msg = document.getElementById('reminderMessage');
    if (msg) { msg.value = ''; msg.disabled = true; }
}

function toggleMessageMode() {
    const isCustom = document.getElementById('checkCustom').checked;
    const txtMessage = document.getElementById('reminderMessage');

    if (isCustom) {
        // Disable Tom Select (or native select) dan clear value
        if (reminderTomSelectDP) {
            reminderTomSelectDP.disable();
            reminderTomSelectDP.clear();
        } else {
            const selectTemplate = document.getElementById('reminderTemplate');
            if (selectTemplate) { selectTemplate.disabled = true; selectTemplate.value = ''; }
        }
        if (txtMessage) { txtMessage.disabled = false; txtMessage.focus(); }
    } else {
        // Enable Tom Select (or native select)
        if (reminderTomSelectDP) {
            reminderTomSelectDP.enable();
        } else {
            const selectTemplate = document.getElementById('reminderTemplate');
            if (selectTemplate) selectTemplate.disabled = false;
        }
        if (txtMessage) { txtMessage.disabled = true; txtMessage.value = ''; }
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
            showCustomToast('Harap isi pesan custom!', 'error');
            return;
        }
        payload = { custom_message: customMessage };
    } else {
        if (!templateId) {
            showCustomToast('Harap pilih template!', 'error');
            return;
        }
        payload = { template_id: templateId };
    }

    // Button Loading State
    const btnSend = document.getElementById('btnSendManual');
    const originalText = btnSend.innerText;
    btnSend.innerText = 'Mengirim...';
    btnSend.disabled = true;

    fetch(`/data-pegawai/${currentDetailNip}/send-manual`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payload)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeReminderModal();
                showCustomToast('Email berhasil dikirim!', 'success');
            } else {
                showCustomToast(data.message || 'Gagal mengirim email.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showCustomToast('Terjadi kesalahan saat mengirim email.', 'error');
        })
        .finally(() => {
            btnSend.innerText = originalText;
            btnSend.disabled = false;
        });
}

// === SYNC TOAST (Now in app-common.js) ===

// === NAVBAR: DROPDOWN PROFILE (Now in app-common.js) ===

// === NAVBAR: NOTIFIKASI (Now in app-common.js) ===

document.addEventListener('DOMContentLoaded', function () {
    fetchNotifications();

    // Auto-search logic moved here
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            if (this.value === '') {
                document.getElementById('searchForm').submit();
            }
        });
    }
});

// Window click listener moved to app-common.js
// Use addEventListener instead of window.onclick to avoid overriding app-common.js
window.addEventListener('click', function (event) {
    const detailModal = document.getElementById('modalDetailPegawai');
    const deleteModal = document.getElementById('modalHapusPegawai');
    const reminderModal = document.getElementById('modalReminder');

    if (detailModal && event.target == detailModal) detailModal.style.display = "none";
    if (deleteModal && event.target == deleteModal) deleteModal.style.display = "none";
    if (reminderModal && event.target == reminderModal) reminderModal.style.display = "none";
});
