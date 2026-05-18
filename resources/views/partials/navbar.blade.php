<header class="top-navbar">
    <div class="welcome-section">
        <h2 class="welcome-title">Selamat Datang</h2>
        <p class="welcome-subtitle">Halo, {{ Auth::user()->nama_lengkap ?? 'Admin' }}</p>
    </div>

    <div class="user-actions">
        @include('partials.tour_button')

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
                    <a href="{{ route('database.backup') }}" class="dropdown-item" style="color: #059669; font-weight: 500;">
                        <i class="ph-fill ph-database" style="font-size: 18px; margin-right: 8px;"></i>
                        Backup Database
                    </a>
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
