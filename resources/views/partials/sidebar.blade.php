<aside class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}" style="text-decoration: none; color: inherit;">
            <h1 class="logo"><span class="logo-highlight">Dashboard</span>Alert</h1>
        </a>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
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
        <a href="{{ route('log-aktivitas') }}" class="nav-item {{ request()->routeIs('log-aktivitas') ? 'active' : '' }}">
            <i class="ph-fill ph-clock-counter-clockwise nav-icon"></i>
            <span class="nav-text">Log Aktivitas</span>
        </a>
        <a href="{{ route('daftar-admin') }}" class="nav-item {{ request()->routeIs('daftar-admin') ? 'active' : '' }}">
            <i class="ph-fill ph-shield-check nav-icon"></i>
            <span class="nav-text">Daftar Admin</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <button class="sync-btn" id="btnSync" onclick="triggerSync()">
            <i class="ph-bold ph-arrows-clockwise sync-img" id="iconSync"></i>
            <span class="sync-icon" id="textSync">Sinkronisasi</span>
        </button>
    </div>
</aside>
