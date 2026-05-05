<aside class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}" style="text-decoration: none; color: inherit;">
            <h1 class="logo"><span class="logo-highlight">Dashboard</span>Alert</h1>
        </a>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="nav-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="4" y="4" width="6" height="8" rx="1" />
                <rect x="4" y="16" width="6" height="4" rx="1" />
                <rect x="14" y="12" width="6" height="8" rx="1" />
                <rect x="14" y="4" width="6" height="4" rx="1" />
            </svg>
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="{{ route('data-pegawai') }}" class="nav-item {{ request()->routeIs('data-pegawai') ? 'active' : '' }}">
            <svg class="nav-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="7" r="4" />
                <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
            </svg>
            <span class="nav-text">Data Pegawai</span>
        </a>
        <a href="{{ route('konfigurasi-pesan') }}" class="nav-item {{ request()->routeIs('konfigurasi-pesan') ? 'active' : '' }}">
            <svg class="nav-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 19H5a2 2 0 0 1 -2 -2V7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v4" />
                <path d="M3 7l9 6l9 -6" />
                <circle cx="19" cy="18" r="2" />
                <path d="M19 14.5v1.5" />
                <path d="M19 20v1.5" />
                <path d="M22.032 16.25l-1.3 .75" />
                <path d="M17.27 19.75l-1.3 .75" />
                <path d="M15.97 16.25l1.3 .75" />
                <path d="M20.733 19.75l1.3 .75" />
            </svg>
            <span class="nav-text">Konfigurasi Pesan</span>
        </a>
        <a href="{{ route('log-aktivitas') }}" class="nav-item {{ request()->routeIs('log-aktivitas') ? 'active' : '' }}">
            <svg class="nav-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <!-- Selembar kertas / Paper di belakang folder -->
                <path d="M8 8V5a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1v5" />
                <path d="M10 8h4" />
                <!-- Folder depan (tertutup) -->
                <path d="M5 8h4l2 3h8a2 2 0 0 1 2 2v5a2 2 0 0 1 -2 2H5a2 2 0 0 1 -2 -2v-8a2 2 0 0 1 2 -2z" />
            </svg>
            <span class="nav-text">Log Aktivitas</span>
        </a>
        <a href="{{ route('daftar-admin') }}" class="nav-item {{ request()->routeIs('daftar-admin') ? 'active' : '' }}">
            <svg class="nav-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="10" cy="7" r="4" />
                <path d="M4 21v-2a4 4 0 0 1 4 -4h2.5" />
                <rect x="15" y="14" width="6" height="7" rx="1" />
                <path d="M16 14v-2a2 2 0 0 1 4 0v2" />
                <path d="M18 18v.01" />
            </svg>
            <span class="nav-text">Daftar Admin</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <button class="sync-btn" id="btnSync" onclick="triggerSync()">
            <i class="ph-bold ph-arrows-clockwise sync-img" id="iconSync"></i>
            <span class="sync-icon" id="textSync">Sinkronisasi</span>
        </button>
        <div class="sidebar-footer mt-3 px-3 text-center" style="margin-top: 14px; padding-top: 12px;">
            <div style="padding: 14px 12px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.14); background: linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04)); box-shadow: 0 14px 30px rgba(15,23,42,0.18); backdrop-filter: blur(10px);">
                <div style="display:flex; align-items:center; justify-content:center; gap:6px; margin-bottom:8px;">
                    <span style="width:6px; height:6px; border-radius:999px; background:#fbbf24; display:inline-block;"></span>
                    <span style="font-size:0.58rem; letter-spacing:0.14em; text-transform:uppercase; font-weight:700; color:rgba(203,213,224,0.82);">Tim IT UNNES</span>
                </div>
                <p style="margin: 0 0 8px; font-size: 0.67rem; color: rgba(203,213,224,0.94); line-height: 1.35; font-weight: 500;">&copy; {{ date('Y') }} Pusdatin Kepegawaian</p>
                <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px 8px; font-size: 0.63rem; line-height: 1.25; color: rgba(203,213,224,0.9);">
                    <span>Muhammad Hasan Faedloni</span>
                    <span>Muhammad Raissa Akhdyan</span>
                    <span>Muhammad Hilmi Asardan</span>
                    <span>Eza Aditya Nugroho</span>
                </div>
            </div>
        </div>
    </div>
</aside>
