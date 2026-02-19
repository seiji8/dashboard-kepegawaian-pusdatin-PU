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
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Gagal mengambil data pegawai.',
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
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Pegawai berhasil dihapus!',
                confirmButtonColor: '#1e3a8a'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.message || 'Gagal menghapus pegawai!',
                confirmButtonColor: '#dc2626'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan',
            text: 'Terjadi kesalahan!',
            confirmButtonColor: '#dc2626'
        });
    });
}

// === REMINDER LOGIC ===
function openReminderModal() {
    document.getElementById('modalReminder').style.display = 'flex';
}

function closeReminderModal() {
    document.getElementById('modalReminder').style.display = 'none';
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

    // Button Loading State
    const btnSend = document.querySelector('.btn-send-soft');
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

// === SYNC TOAST ===
function showSyncToast() {
    var toast = document.getElementById("syncToast");
    toast.className = "toast-notification show";
    setTimeout(function(){ 
        toast.className = toast.className.replace("show", ""); 
    }, 3000);
}

// === NAVBAR: DROPDOWN PROFILE ===
function toggleDropdown() {
    var dropdown = document.getElementById("profileDropdown");
    if (dropdown.style.display === "block") {
        dropdown.style.display = "none";
    } else {
        dropdown.style.display = "block";
    }
}

// === NAVBAR: NOTIFIKASI ===
function toggleNotifDropdown() {
    var dropdown = document.getElementById('notifDropdown');
    if (dropdown.classList.contains('active')) {
        dropdown.classList.remove('active');
    } else {
        dropdown.classList.add('active');
        fetchNotifications();
    }
}

function fetchNotifications() {
    fetch('/notifications', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        renderNotifications(data.notifications);
        updateBadge(data.unread_count);
    })
    .catch(err => console.error('Gagal fetch notifikasi:', err));
}

function renderNotifications(notifications) {
    var list = document.getElementById('notifList');
    if (!notifications || notifications.length === 0) {
        list.innerHTML = '<div class="notif-empty">' +
            '<i class="ph-light ph-bell-slash" style="font-size: 32px; color: #9ca3af;"></i>' +
            '<p>Belum ada notifikasi</p></div>';
        return;
    }
    var html = '';
    notifications.forEach(function(n) {
        var unreadClass = n.read ? '' : ' unread';
        var clickAction = n.read ? '' : ' onclick="markNotifRead(\'' + n.id + '\')"}'; 
        html += '<div class="notif-item' + unreadClass + '"' + clickAction + '>' +
            '<div class="notif-content">' +
                '<p class="notif-title">' + n.title + '</p>' +
                '<p class="notif-message">' + n.message + '</p>' +
                '<span class="notif-time">' + n.time + '</span>' +
            '</div></div>';
    });
    list.innerHTML = html;
}

function updateBadge(count) {
    var badge = document.getElementById('notifBadge');
    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

function markAllRead() {
    fetch('/notifications/mark-read', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => { if (data.success) fetchNotifications(); })
    .catch(err => console.error('Gagal mark read:', err));
}

function markNotifRead(notifId) {
    fetch('/notifications/' + notifId + '/read', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => { if (data.success) fetchNotifications(); })
    .catch(err => console.error('Gagal mark notif:', err));
}

document.addEventListener('DOMContentLoaded', function() {
    fetchNotifications();
    
    // Auto-search logic moved here
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            if (this.value === '') {
                document.getElementById('searchForm').submit();
            }
        });
    }
});

// Close Modal on Click Outside + Navbar Dropdowns
window.onclick = function(event) {
    const detailModal = document.getElementById('modalDetailPegawai');
    const deleteModal = document.getElementById('modalHapusPegawai');
    const reminderModal = document.getElementById('modalReminder');

    if (event.target == detailModal) detailModal.style.display = "none";
    if (event.target == deleteModal) deleteModal.style.display = "none";
    if (event.target == reminderModal) reminderModal.style.display = "none";

    // Navbar: tutup profile dropdown
    if (!event.target.closest('.profile-btn')) {
        var dropdowns = document.getElementsByClassName("dropdown-menu");
        for (var i = 0; i < dropdowns.length; i++) {
            if (dropdowns[i].style.display === "block") dropdowns[i].style.display = "none";
        }
    }
    // Navbar: tutup notif dropdown
    if (!event.target.closest('.notif-wrapper')) {
        var notifDropdown = document.getElementById('notifDropdown');
        if (notifDropdown) notifDropdown.classList.remove('active');
    }
}
