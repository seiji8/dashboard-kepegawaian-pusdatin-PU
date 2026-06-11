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
        <div class="sidebar-footer" style="border-top: none; padding-bottom: 24px;">
            <div style="border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 12px; padding: 16px; background: rgba(255, 255, 255, 0.04); text-align: center; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                {{-- Label Tim --}}
                <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 4px;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background-color: #fbbf24; flex-shrink: 0;"></div>
                    <span style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: rgba(255,255,255,0.85); text-transform: uppercase;">Tim IT H2ER Devs</span>
                </div>
                {{-- Nama Universitas --}}
                <p style="margin: 0 0 12px; font-size: 0.6rem; font-weight: 500; color: rgba(255,255,255,0.55); letter-spacing: 0.04em; text-transform: uppercase;">Universitas Negeri Semarang</p>
                {{-- Copyright --}}
                <p style="margin: 0 0 14px; font-size: 0.7rem; color: rgba(255,255,255,0.95); line-height: 1.4; font-weight: 500;">&copy; {{ date('Y') }} Tim Kepegawaian dan JF PUSDATIN</p>
                {{-- Logo berdampingan --}}
                <div style="display: flex; align-items: center; justify-content: center; gap: 16px;">
                    <img src="{{ asset('assets/Logounnes.png') }}"
                         alt="Logo UNNES"
                         style="height: 36px; width: auto; object-fit: contain;"
                         title="Universitas Negeri Semarang">
                    <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>
                    <img src="{{ asset('assets/Logo_PU.png') }}"
                         alt="Logo Kementerian PU"
                         style="height: 36px; width: auto; object-fit: contain;"
                         title="Kementerian Pekerjaan Umum">
                </div>
            </div>
        </div>
    </div>
</aside>
