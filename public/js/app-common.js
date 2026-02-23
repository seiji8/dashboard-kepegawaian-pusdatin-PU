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
    if (dropdown) {
        if (dropdown.style.display === "block") {
            dropdown.style.display = "none";
        } else {
            dropdown.style.display = "block";
        }
    }
}

// --- NAVBAR: NOTIFIKASI ---
function toggleNotifDropdown() {
    var dropdown = document.getElementById('notifDropdown');
    if (dropdown) {
        if (dropdown.classList.contains('active')) {
            dropdown.classList.remove('active');
        } else {
            dropdown.classList.add('active');
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
    }
    // Tutup notif dropdown
    if (!event.target.closest('.notif-wrapper')) {
        var notifDropdown = document.getElementById('notifDropdown');
        if (notifDropdown) notifDropdown.classList.remove('active');
    }
}

// --- SINKRONISASI LOGIC ---
function triggerSync() {
    var loadingModal = document.getElementById('loadingModal');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // 1. Show Loading Modal
    if (loadingModal) {
        loadingModal.style.display = 'flex';
    } else {
        // Fallback console warning if modal missing
        console.warn('Sync integration: #loadingModal missing from this view.');
    }

    // 2. AJAX Request
    fetch('/sync-now', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
        .then(response => response.json())
        .then(data => {
            if (loadingModal) loadingModal.style.display = 'none';

            if (data.success) {
                // Show Success Toast
                var toast = document.getElementById("syncToast");
                if (toast) {
                    toast.className = "toast-notification show";
                    setTimeout(function () {
                        if (toast) toast.className = toast.className.replace("show", "");
                    }, 3000);
                }

                // Reload page to reflect changes
                setTimeout(() => {
                    location.reload();
                }, 1000);

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Sinkronisasi gagal.',
                    confirmButtonColor: '#dc2626'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (loadingModal) loadingModal.style.display = 'none';
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan',
                text: 'Terjadi kesalahan sistem saat sinkronisasi.',
                confirmButtonColor: '#dc2626'
            });
        });
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
            '<p class="notif-message">' + n.message + '</p>' +
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
