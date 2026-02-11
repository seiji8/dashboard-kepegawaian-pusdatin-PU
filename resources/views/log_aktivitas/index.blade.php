<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - DashboardAlert</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/log_aktivitas.css') }}">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h1 class="logo"><span class="logo-highlight">Dashboard</span>Alert</h1>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item">
                <i class="ph-fill ph-squares-four nav-icon"></i>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="{{ route('data-pegawai') }}" class="nav-item {{ request()->routeIs('data-pegawai') ? 'active' : '' }}">
                <i class="ph-fill ph-users nav-icon"></i>
                <span class="nav-text">Data Pegawai</span>
            </a>
            <a href="{{ route('konfigurasi-pesan') }}" class="nav-item {{ request()->routeIs('konfigurasi-pesan') ? 'active' : '' }}">
                <i class="ph-fill ph-chat-dots nav-icon"></i>
                <span class="nav-text">Konfigurasi Pesan</span>
            </a>
            <a href="{{ route('log-aktivitas') }}" class="nav-item active">
                <i class="ph-fill ph-clock-counter-clockwise nav-icon"></i>
                <span class="nav-text">Log Aktivitas</span>
            </a>
            <a href="{{ route('daftar-admin') }}" class="nav-item {{ request()->routeIs('daftar-admin') ? 'active' : '' }}">
                <i class="ph-fill ph-shield-check nav-icon"></i>
                <span class="nav-text">Daftar Admin</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <button class="sync-btn" onclick="showSyncToast()">
                <i class="ph-bold ph-arrows-clockwise sync-img"></i>
                <span class="sync-icon">Sinkronisasi</span>
            </button>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-navbar">
            <div class="welcome-section">
                <h2 class="welcome-title">Selamat Datang</h2>
                <p class="welcome-subtitle">Halo, {{ Auth::user()->nama_lengkap ?? 'Admin' }}</p>
            </div>

            <div class="user-actions">
                <div class="notif-wrapper">
                    <button class="btn-icon-header" onclick="toggleNotifDropdown()">
                        <i class="ph-fill ph-bell" style="font-size: 24px; color: #1e3a8a;"></i>
                        <span class="notif-badge" id="notifBadge" style="display: none;">0</span>
                    </button>

                    <div id="notifDropdown" class="notif-dropdown">
                        <div class="notif-header">
                            <span class="notif-header-title">Notifikasi</span>
                            <button class="notif-mark-read" onclick="markAllRead()">Tandai Semua Dibaca</button>
                        </div>
                        <div id="notifList" class="notif-list">
                            <div class="notif-empty">
                                <i class="ph-light ph-bell-slash" style="font-size: 32px; color: #9ca3af;"></i>
                                <p>Belum ada notifikasi</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-wrapper">
                    <button class="profile-btn" onclick="toggleDropdown()">
                        <div class="avatar-circle">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->nama_lengkap ?? 'User') }}&background=random" alt="User">
                        </div>
                        <div class="profile-info">
                            <span class="profile-name">{{ Str::limit(Auth::user()->nama_lengkap ?? 'Admin', 15) }}</span>
                            <span class="profile-role">
                                {{ (auth()->user() && auth()->user()->isSuperAdmin()) ? 'Super Admin' : 'Admin Pegawai' }}
                            </span>
                        </div>
                        <i class="ph-bold ph-caret-down" style="font-size: 16px; color: #666;"></i>
                    </button>

                    <div id="profileDropdown" class="dropdown-menu">
                        <a href="#" onclick="openChangePasswordModal(); return false;" class="dropdown-item">
                            <i class="ph-fill ph-lock-key" style="font-size: 18px; margin-right: 8px;"></i>
                            Ganti Kata Sandi
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-red" style="width:100%; border:none; background:none; cursor:pointer;">
                                <i class="ph-fill ph-sign-out" style="font-size: 18px; margin-right: 8px;"></i>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="content-area">
        <div class="content-header">
            <h2 class="page-title">Log Aktivitas</h2>
        </div>

        <!-- FILTER SECTION -->
        <div class="filter-section">
            <form id="filterForm" method="GET" action="{{ route('log-aktivitas') }}">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Jenis Pengguna</label>
                        <select name="jenis_pengguna" class="filter-input" style="color: {{ request('jenis_pengguna') ? '#374151' : '#A9ACB1' }};">
                            <option value="">Semua</option>
                            <option value="super_admin" {{ request('jenis_pengguna') == 'super_admin' ? 'selected' : '' }}>Admin Super</option>
                            <option value="admin_pegawai" {{ request('jenis_pengguna') == 'admin_pegawai' ? 'selected' : '' }}>Admin Kepegawaian</option>
                            <option value="sistem" {{ request('jenis_pengguna') == 'sistem' ? 'selected' : '' }}>Sistem</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Aksi</label>
                        <div class="input-with-icon-left">
                            <i class="ph-bold ph-magnifying-glass icon-input-left"></i>
                            <input type="text" name="aksi" placeholder="Cari Aksi" class="filter-input pl-icon" value="{{ request('aksi') }}">
                        </div>
                    </div>

                    <div class="filter-group">
                        <label>Dari Tanggal</label>
                        <div class="date-wrapper">
                            <input type="date" name="dari_tanggal" class="filter-input date-input" value="{{ request('dari_tanggal') }}">
                        </div>
                    </div>

                    <div class="filter-group">
                        <label>Sampai Tanggal</label>
                        <div class="date-wrapper">
                            <input type="date" name="sampai_tanggal" class="filter-input date-input" value="{{ request('sampai_tanggal') }}">
                        </div>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-filter">
                        <i class="ph-fill ph-funnel"></i>
                        Filter
                    </button>
                    <button type="button" class="btn-reset" onclick="resetFilter()">Reset</button>
                </div>
            </form>
        </div>

        <!-- DATA TABLE SECTION -->
        <div class="content-section">
            <h3 class="section-title">Data Log</h3>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Waktu</th>
                            <th style="width: 18%;">Jenis Pengguna</th>
                            <th style="width: 17%;">Aksi</th>
                            <th style="width: 45%;">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <div class="time-cell">
                                    <span class="date-text">{{ \Carbon\Carbon::parse($log->waktu)->format('M d, Y') }}</span>
                                    <span class="time-badge">{{ \Carbon\Carbon::parse($log->waktu)->format('H:i') }}</span>
                                </div>
                                <span class="time-ago">{{ \Carbon\Carbon::parse($log->waktu)->diffForHumans() }}</span>
                            </td>
                            <td>
                                @if($log->admin)
                                    @if($log->admin->role === 'super_admin')
                                        <span class="badge badge-admin-super">Admin Super</span>
                                    @else
                                        <span class="badge badge-pegawai">Admin Kepegawaian</span>
                                    @endif
                                @else
                                    <span class="badge badge-sistem">Sistem</span>
                                @endif
                            </td>
                            <td>
                                @if($log->tipe == 'ADMIN_ACTION')
                                    @if(str_contains($log->deskripsi, 'Login'))
                                        Login
                                    @elseif(str_contains($log->deskripsi, 'Mengkonfirmasi'))
                                        Konfirmasi Tugas
                                    @elseif(str_contains($log->deskripsi, 'Verifikasi'))
                                        Verifikasi Data
                                    @else
                                        {{ Str::limit($log->deskripsi, 25) }}
                                    @endif
                                @elseif($log->tipe == 'NOTIF_SENT')
                                    Mengirim Notifikasi
                                @elseif($log->tipe == 'API_SYNC')
                                    Sinkronisasi API
                                @else
                                    Log Sistem
                                @endif
                            </td>
                            <td>{{ $log->deskripsi }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 40px; color: #9ca3af;">
                                Tidak ada data log aktivitas
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($logs->hasPages())
            <div class="pagination">
                @if($logs->onFirstPage())
                    <span class="pagination-text" style="opacity: 0.5;">Prev</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" class="pagination-text">Prev</a>
                @endif

                @php
                    $start = max(1, $logs->currentPage() - 2);
                    $end = min($start + 4, $logs->lastPage());
                    $start = max(1, $end - 4);
                @endphp

                @foreach($logs->getUrlRange($start, $end) as $page => $url)
                    <a href="{{ $url }}" class="pagination-btn {{ $page == $logs->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                @endforeach

                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" class="pagination-text">Next</a>
                @else
                    <span class="pagination-text" style="opacity: 0.5;">Next</span>
                @endif
            </div>
            @endif
        </div>
        </div><!-- end content-area -->
    </main>

    <!-- SYNC TOAST NOTIFICATION -->
    <div id="syncToast" class="toast-notification">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span>Sinkronisasi Data Berhasil!</span>
    </div>

    <script>
        // SINKRONISASI TOAST
        function showSyncToast() {
            var toast = document.getElementById("syncToast");
            toast.className = "toast-notification show";
            setTimeout(function(){ 
                toast.className = toast.className.replace("show", ""); 
            }, 3000);
        }

        // RESET FILTER
        function resetFilter() {
            document.getElementById('filterForm').reset();
            window.location.href = "{{ route('log-aktivitas') }}";
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
    });

    // Close Dropdowns on outside click
    window.addEventListener('click', function(e){
        // Navbar: tutup profile dropdown
        if (!e.target.closest('.profile-btn')) {
            var dropdowns = document.getElementsByClassName("dropdown-menu");
            for (var i = 0; i < dropdowns.length; i++) {
                if (dropdowns[i].style.display === "block") dropdowns[i].style.display = "none";
            }
        }
        // Navbar: tutup notif dropdown
        if (!e.target.closest('.notif-wrapper')) {
            var notifDropdown = document.getElementById('notifDropdown');
            if (notifDropdown) notifDropdown.classList.remove('active');
        }
    });

    </script>
    @include('partials.change_password_modal')
</body>
</html>
