/**
 * Common App Logic (Shared across all pages)
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Sidebar Drag Prevention
    document.addEventListener('dragstart', function (event) {
        if (event.target.closest('.sidebar') ||
            event.target.closest('.profile-btn') ||
            event.target.tagName === 'IMG') {
            event.preventDefault();
        }
    });

    // 2. Initial Notifications Fetch (if applicable)
    if (typeof fetchNotifications === 'function') {
        fetchNotifications();
    }
});

// --- NAVBAR: DROPDOWN PROFILE ---
function toggleDropdown() {
    var dropdown = document.getElementById("profileDropdown");
    var btn = document.querySelector(".profile-btn");
    
    if (dropdown) {
        if (dropdown.style.display === "block") {
            dropdown.style.display = "none";
            if (btn) btn.classList.remove("active");
        } else {
            dropdown.style.display = "block";
            if (btn) btn.classList.add("active");
        }
    }
}

// --- NAVBAR: NOTIFIKASI ---
function toggleNotifDropdown() {
    var dropdown = document.getElementById('notifDropdown');
    var btn = document.querySelector('.btn-icon-header');

    if (dropdown) {
        if (dropdown.classList.contains('active')) {
            dropdown.classList.remove('active');
            if (btn) btn.classList.remove('active');
        } else {
            dropdown.classList.add('active');
            if (btn) btn.classList.add('active');
            if (typeof fetchNotifications === 'function') {
                fetchNotifications();
            }
        }
    }
}

// --- GLOBAL CLICK LISTENER ---
window.onclick = function (event) {
    // Tutup profile dropdown
    if (!event.target.closest('.profile-btn')) {
        var dropdowns = document.getElementsByClassName("dropdown-menu");
        for (var i = 0; i < dropdowns.length; i++) {
            if (dropdowns[i].style.display === "block") {
                dropdowns[i].style.display = "none";
            }
        }
        var profileBtn = document.querySelector(".profile-btn");
        if (profileBtn) profileBtn.classList.remove("active");
    }
    // Tutup notif dropdown
    if (!event.target.closest('.notif-wrapper')) {
        var notifDropdown = document.getElementById('notifDropdown');
        if (notifDropdown) notifDropdown.classList.remove('active');
        var notifBtn = document.querySelector('.btn-icon-header');
        if (notifBtn) notifBtn.classList.remove('active');
    }
}

// --- SINKRONISASI LOGIC ---
function triggerSync() {
    var loadingModal = document.getElementById('loadingModal');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // 1. Reset Modal State & Show
    if (loadingModal) {
        document.getElementById('syncProgressBar').style.width = '0%';
        document.getElementById('syncProgressText').innerText = '0%';
        document.getElementById('syncDetailText').innerText = 'Bersiap memulai sinkronisasi...';
        
        // Reset 4 Steps UI
        for (let i = 1; i <= 4; i++) {
            let stepEl = document.getElementById(`step${i}`);
            let iconEl = document.getElementById(`step${i}Icon`);
            if (stepEl && iconEl) {
                stepEl.className = 'progress-step';
                iconEl.innerHTML = '<span class="circle-pending"></span>';
            }
        }
        loadingModal.style.display = 'flex';
    }

    // 2. AJAX Request to start Job
    fetch('/sync-now', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            if (loadingModal) loadingModal.style.display = 'none';
            showCustomToast(data.message || 'Sinkronisasi gagal.', 'error');
            return;
        }

        // Job started in background, let's poll!
        startSyncPolling();
    })
    .catch(error => {
        console.error('Error starting sync:', error);
        if (loadingModal) loadingModal.style.display = 'none';
        showCustomToast('Terjadi kesalahan sistem saat memulai sinkronisasi.', 'error');
    });
}

// Global variable tracker to prevent duplicate intervals
let syncPollInterval = null;

function startSyncPolling() {
    if (syncPollInterval) {
        clearInterval(syncPollInterval);
    }

    syncPollInterval = setInterval(function() {
        fetch('/sync-progress')
            .then(res => res.json())
            .then(data => {
                updateSyncUI(data);

                // If progress reaches 100%, finish up
                if (data.progress >= 100) {
                    clearInterval(syncPollInterval);
                    syncPollInterval = null;
                    finishSyncSuccess();
                }
            })
            .catch(err => {
                console.error("Error polling sync progress:", err);
            });
    }, 1500); // Poll every 1.5 seconds
}

function updateSyncUI(data) {
    if (!data) return;

    // Update Overall Bar & Text
    let barFill = document.getElementById('syncProgressBar');
    let pctText = document.getElementById('syncProgressText');
    let detailText = document.getElementById('syncDetailText');

    if (barFill) barFill.style.width = data.progress + '%';
    if (pctText) pctText.innerText = data.progress + '%';
    if (detailText) detailText.innerText = data.detail_text || '';

    // Update 4 Steps Iteratively
    for (let i = 1; i <= 4; i++) {
        let status = data[`step_${i}_status`]; // 'pending', 'processing', 'done'
        let stepEl = document.getElementById(`step${i}`);
        let iconEl = document.getElementById(`step${i}Icon`);

        if (stepEl && iconEl && status) {
            // Remove previous classes
            stepEl.classList.remove('processing', 'done');
            
            if (status === 'processing') {
                stepEl.classList.add('processing');
                iconEl.innerHTML = '<i class="ph-bold ph-spinner icon-processing"></i>';
            } else if (status === 'done') {
                stepEl.classList.add('done');
                iconEl.innerHTML = '<i class="ph-fill ph-check-circle icon-done"></i>';
            } else {
                // pending
                iconEl.innerHTML = '<span class="circle-pending"></span>';
            }
        }
    }
}

function finishSyncSuccess() {
    var loadingModal = document.getElementById('loadingModal');
    
    // Slight delay so user sees 100% and checkmarks before it closes
    setTimeout(() => {
        if (loadingModal) loadingModal.style.display = 'none';

        var toast = document.getElementById("syncToast");
        if (toast) {
            toast.className = "toast-notification show";
            setTimeout(function () {
                if (toast) toast.className = toast.className.replace("show", "");
            }, 3000);
        }

        setTimeout(() => {
            location.reload();
        }, 1000);
    }, 1200);
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
    if (!list) return;

    if (!notifications || notifications.length === 0) {
        list.innerHTML = '<div class="notif-empty">' +
            '<i class="ph-light ph-bell-slash" style="font-size: 32px; color: #9ca3af;"></i>' +
            '<p>Belum ada notifikasi</p></div>';
        return;
    }

    var html = '';
    notifications.forEach(function (n) {
        var unreadClass = n.read ? '' : ' unread';
        var clickAction = n.read ? '' : ' onclick="markNotifRead(\'' + n.id + '\')"';
        
        html += '<div class="notif-item' + unreadClass + '"' + clickAction + '>' +
            '<div class="notif-content">' +
            '<p class="notif-title">' + n.title + '</p>' +
            '<p class="notif-message">' + n.message.replace(/\n/g, '<br>') + '</p>' +
            '<span class="notif-time">' + n.time + '</span>' +
            '</div>' +
            '</div>';
    });
    list.innerHTML = html;
}

function updateBadge(count) {
    var badge = document.getElementById('notifBadge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
}

function markAllRead() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/notifications/mark-read', {
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
                fetchNotifications();
            }
        })
        .catch(err => console.error('Gagal mark read:', err));
}

function markNotifRead(notifId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/notifications/' + notifId + '/read', {
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
                fetchNotifications();
            }
        })
        .catch(err => console.error('Gagal mark notif:', err));
}

// === CUSTOM TOAST NOTIFICATION ===
function showCustomToast(message, type = 'success') {
    // Cari toast element yang ada, atau buat baru jika tidak ada
    let toast = document.getElementById("syncToast");
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'syncToast';
        toast.className = 'toast-notification';
        toast.innerHTML = '<i class="ph-bold ph-check-circle"></i> <span></span>';
        document.body.appendChild(toast);
    }

    const icon = toast.querySelector("i");
    const text = toast.querySelector("span");
    if (text) text.innerText = message;
    
    toast.className = "toast-notification";
    
    if (type === 'error') {
        toast.style.backgroundColor = "#ef4444";
        toast.style.color = "#ffffff";
        toast.style.border = "none";
        if (icon) icon.className = "ph-bold ph-warning-circle";
    } else {
        toast.style.backgroundColor = "#10b981";
        toast.style.color = "#ffffff";
        toast.style.border = "none";
        if (icon) icon.className = "ph-bold ph-check-circle";
    }

    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 3500);
}
