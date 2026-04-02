<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - DashboardAlert</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons for fallback since assets are missing -->
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"> -->
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo_PU.png') }}">
</head>
<body>
    <div class="container">
        @include('partials.sidebar')

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
                
                <h2 class="page-title-dashboard">Dashboard</h2>

                <div class="dashboard-cards">
                    <div class="summary-card">
                        <div class="card-text">
                            <div class="card-header-group">
                                <span class="card-label">Total Pegawai</span>
                                <h3 class="card-value">{{ $totalPegawai }}</h3>
                            </div>
                            <span class="card-tag">Aktif</span>
                        </div>
                        <div class="card-icon-box">
                            <i class="ph-fill ph-users-three" style="font-size: 24px; color: #fbbf24;"></i>
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="card-text">
                            <div class="card-header-group">
                                <span class="card-label">Tingkat Kepatuhan</span>
                                <h3 class="card-value">{{ $tingkatKepatuhan }}%</h3>
                            </div>
                            <span class="card-tag">Bulan ini</span>
                        </div>
                        <div class="card-icon-box">
                            <i class="ph-fill ph-chart-bar" style="font-size: 24px; color: #fbbf24;"></i>
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="card-text">
                            <div class="card-header-group">
                                <span class="card-label">Tenggat Mendesak</span>
                                <h3 class="card-value">{{ $tenggatMendesak }}</h3>
                            </div>
                            <span class="card-tag">Perlu Atensi</span>
                        </div>
                        <div class="card-icon-box">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="13" r="7" />
                                <polyline points="12 10 12 14 15 14" />
                                <line x1="7" y1="5.5" x2="5.5" y2="7" />
                                <line x1="17" y1="5.5" x2="18.5" y2="7" />
                                <path d="M12 4v0" stroke-width="3" stroke-linecap="round" />
                            </svg>
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="card-text">
                            <div class="card-header-group">
                                <span class="card-label">Jumlah Usulan</span>
                                <h3 class="card-value">{{ $jumlahUsulan }}</h3>
                            </div>
                            <span class="card-tag">Sedang Proses</span>
                        </div>
                        <div class="card-icon-box">
                            <i class="ph-fill ph-file-text" style="font-size: 24px; color: #fbbf24;"></i>
                        </div>
                    </div>
                </div>

                <div class="task-section">
                    <h3 class="task-section-title" style="margin-bottom: 20px; font-weight: 700; color: #111;">Daftar Tugas yang harus diselesaikan</h3>
                    
                    <div class="task-list">

                        <!-- TASK: KENAIKAN PANGKAT -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-pangkat', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">
                                    {{ $listKenaikanPangkat->count() }}
                                </div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Kenaikan Pangkat</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>

                            <div id="task-pangkat" class="task-sub-container">
                                
                                <!-- Sub: Struktural -->
                                <div class="sub-item">
                                    <div class="sub-task-btn" onclick="toggleSubTask('sub-struktural')">
                                        <span class="sub-badge">{{ $kpStruktural->count() }}</span>
                                        <span style="flex:1;">Jabatan Struktural</span>
                                    </div>
                                    <div id="sub-struktural" class="sub-table-container">
                                        <div class="surat-btn-row">
                                            <button class="btn-cetak-surat" onclick="openSuratModal('KP_Struktural')">
                                                <i class="ph-bold ph-file-text"></i> Cetak Surat Pengajuan
                                            </button>
                                        </div>
                                        <table class="custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Mulai Notifikasi</th>
                                                    <th>Nama</th>
                                                    <th>Eselon</th>
                                                    <th>Pangkat Saat Ini</th>
                                                    <th>Status</th>
                                                    <th>Dokumen</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($kpStruktural as $item)
                                                <tr>
                                                    <td>{{ $item->tanggal_target ? \Carbon\Carbon::parse($item->tanggal_target)->format('d M Y') : '-' }}</td>
                                                    <td>{{ $item->pegawai->nama }}</td>
                                                    <td>{{ $item->pegawai->nama_eselon }}</td>
                                                    <td>{{ $item->pegawai->pangkat_golongan ?? '-' }}</td>
                                                    <td>
                                                        @if($item->status_saat_ini == 'Usulan')
                                                            <span class="status-badge status-missing">Usulan Pengajuan</span>
                                                        @elseif($item->status_saat_ini == 'Mendekati')
                                                            <span class="status-badge status-warning">Mendekati</span>
                                                        @elseif($item->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">Proses TTE</span>
                                                        @elseif($item->status_saat_ini == 'Upload E-HRM')
                                                            <span class="status-badge status-ok">Upload E-HRM</span>
                                                        @else
                                                            <span class="status-badge status-secondary">{{ $item->status_saat_ini }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span style="color: #dc2626; font-weight: 600;">
                                                            {{ $item->dokumen_total - $item->dokumen_terupload }} Belum
                                                        </span>
                                                    </td>
                                                    <td style="display: flex; gap: 6px;">
                                                        <button class="btn-action-view" onclick="openDashboardDetail('{{ $item->pegawai->nip }}', '{{ $item->kategori }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                        @if($item->status_saat_ini == 'Proses')
                                                        <button class="btn-action-confirm" onclick="openConfirmModal({{ $item->id }}, '{{ $item->pegawai->nama }}')" title="Konfirmasi TTE Selesai">
                                                            <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                        </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="7" style="text-align:center;">Tidak ada data.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Sub: Fungsional -->
                                <div class="sub-item">
                                    <div class="sub-task-btn" onclick="toggleSubTask('sub-fungsional')">
                                        <span class="sub-badge">{{ $kpFungsional->count() }}</span>
                                        <span style="flex:1;">Jabatan Fungsional</span>
                                    </div>
                                    <div id="sub-fungsional" class="sub-table-container">
                                        <div class="surat-btn-row">
                                            <button class="btn-cetak-surat" onclick="openSuratModal('KP_Jafung')">
                                                <i class="ph-bold ph-file-text"></i> Cetak Surat Pengajuan
                                            </button>
                                        </div>
                                        <table class="custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Mulai Notifikasi</th>
                                                    <th>Nama</th>
                                                    <th>Pangkat Saat Ini</th>
                                                    <th>Status</th>
                                                    <th>Dokumen</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($kpFungsional as $item)
                                                <tr>
                                                    <td>{{ $item->tanggal_target ? \Carbon\Carbon::parse($item->tanggal_target)->format('d M Y') : '-' }}</td>
                                                    <td>{{ $item->pegawai->nama }}</td>
                                                    <td>{{ $item->pegawai->pangkat_golongan ?? '-' }}</td>
                                                    <td>
                                                        @if($item->status_saat_ini == 'Usulan')
                                                            <span class="status-badge status-missing">Usulan Pengajuan</span>
                                                        @elseif($item->status_saat_ini == 'Mendekati')
                                                            <span class="status-badge status-warning">Mendekati</span>
                                                        @elseif($item->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">Proses TTE</span>
                                                        @elseif($item->status_saat_ini == 'Upload E-HRM')
                                                            <span class="status-badge status-ok">Upload E-HRM</span>
                                                        @else
                                                            <span class="status-badge status-secondary">{{ $item->status_saat_ini }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span style="color: #dc2626; font-weight: 600;">
                                                            {{ $item->dokumen_total - $item->dokumen_terupload }} Belum
                                                        </span>
                                                    </td>
                                                    <td style="display: flex; gap: 6px;">
                                                        <button class="btn-action-view" onclick="openDashboardDetail('{{ $item->pegawai->nip }}', '{{ $item->kategori }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                        @if($item->status_saat_ini == 'Proses')
                                                        <button class="btn-action-confirm" onclick="openConfirmModal({{ $item->id }}, '{{ $item->pegawai->nama }}')" title="Konfirmasi TTE Selesai">
                                                            <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                        </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="6" style="text-align:center;">Tidak ada data.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Sub: Reguler -->
                                <div class="sub-item">
                                    <div class="sub-task-btn" onclick="toggleSubTask('sub-reguler')">
                                        <span class="sub-badge">{{ $kpReguler->count() }}</span>
                                        <span style="flex:1;">Reguler</span>
                                    </div>
                                    <div id="sub-reguler" class="sub-table-container">
                                        <div class="surat-btn-row">
                                            <button class="btn-cetak-surat" onclick="openSuratModal('KP_Reguler')">
                                                <i class="ph-bold ph-file-text"></i> Cetak Surat Pengajuan
                                            </button>
                                        </div>
                                        <table class="custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Mulai Notifikasi</th>
                                                    <th>Nama</th>
                                                    <th>Pangkat Saat Ini</th>
                                                    <th>TMT Pangkat Terakhir</th>
                                                    <th>Status</th>
                                                    <th>Dokumen</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($kpReguler as $item)
                                                <tr>
                                                    <td>{{ $item->tanggal_target ? \Carbon\Carbon::parse($item->tanggal_target)->format('d M Y') : '-' }}</td>
                                                    <td>{{ $item->pegawai->nama }}</td>
                                                    <td>{{ $item->pegawai->pangkat_golongan ?? '-' }}</td>
                                                    <td>{{ $item->pegawai->tmt_pangkat_terakhir ? \Carbon\Carbon::parse($item->pegawai->tmt_pangkat_terakhir)->format('d M Y') : '-' }}</td>
                                                    <td>
                                                        @if($item->status_saat_ini == 'Usulan')
                                                            <span class="status-badge status-missing">Usulan Pengajuan</span>
                                                        @elseif($item->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">Proses TTE</span>
                                                        @else
                                                            <span class="status-badge status-secondary">{{ $item->status_saat_ini }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span style="color: #dc2626; font-weight: 600;">
                                                            {{ $item->dokumen_total - $item->dokumen_terupload }} Belum
                                                        </span>
                                                    </td>
                                                    <td style="display: flex; gap: 6px;">
                                                        <button class="btn-action-view" onclick="openDashboardDetail('{{ $item->pegawai->nip }}', '{{ $item->kategori }}')" title="Lihat Profil">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                        @if($item->status_saat_ini == 'Proses')
                                                        <button class="btn-action-confirm" onclick="openConfirmModal({{ $item->id }}, '{{ $item->pegawai->nama }}')" title="Konfirmasi TTE Selesai">
                                                            <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                        </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="7" style="text-align:center;">Tidak ada data usulan reguler.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- TASK: KENAIKAN JENJANG -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-jenjang', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">{{ $listKenaikanJenjang->count() }}</div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Kenaikan Jenjang</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div id="task-jenjang" class="task-sub-container">
                                <div class="sub-table-container active" style="display:block;">
                                    <div class="surat-btn-row">
                                        <button class="btn-cetak-surat" onclick="openSuratModal('KJ_Jafung')">
                                            <i class="ph-bold ph-file-text"></i> Cetak Surat Pengajuan
                                        </button>
                                    </div>
                                    <table class="custom-table">
                                        <thead>
                                            <tr>
                                                <th>Mulai Notifikasi</th>
                                                <th>Nama</th>
                                                <th>Jenjang Saat Ini</th>
                                                <th>Status</th>
                                                <th>Dokumen</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($listKenaikanJenjang as $item)
                                            <tr>
                                                <td>{{ $item->tanggal_target ? \Carbon\Carbon::parse($item->tanggal_target)->format('d M Y') : '-' }}</td>
                                                <td>{{ $item->pegawai->nama }}</td>
                                                <td>{{ $item->pegawai->jenjang ?? '-' }}</td>
                                                <td>
                                                    @if($item->status_saat_ini == 'Usulan')
                                                        <span class="status-badge status-missing">Usulan Pengajuan</span>
                                                    @elseif($item->status_saat_ini == 'Mendekati')
                                                        <span class="status-badge status-warning">Mendekati</span>
                                                    @elseif($item->status_saat_ini == 'Proses')
                                                        <span class="status-badge status-warning">Proses TTE</span>
                                                    @elseif($item->status_saat_ini == 'Upload E-HRM')
                                                        <span class="status-badge status-ok">Upload E-HRM</span>
                                                    @else
                                                        <span class="status-badge status-secondary">{{ $item->status_saat_ini }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span style="color: #dc2626; font-weight: 600;">
                                                        {{ $item->dokumen_total - $item->dokumen_terupload }} Belum
                                                    </span>
                                                </td>
                                                <td style="display: flex; gap: 6px;">
                                                    <button class="btn-action-view" onclick="openDashboardDetail('{{ $item->pegawai->nip }}', '{{ $item->kategori }}')">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                    </button>
                                                    @if($item->status_saat_ini == 'Proses')
                                                    <button class="btn-action-confirm" onclick="openUkomModal({{ $item->id }}, '{{ $item->pegawai->nama }}')" title="Konfirmasi TTE Selesai">
                                                        <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                    </button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="6" style="text-align:center;">Tidak ada usulan kenaikan jenjang.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                         <!-- TASK: KGB -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-gaji', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">
                                    {{ $listKGB->count() }}
                                </div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Kenaikan Gaji Berkala</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div id="task-gaji" class="task-sub-container">
                                <div class="sub-table-container active" style="display:block;">
                                    @if($listKGB->count() > 0)
                                        <div class="surat-btn-row">
                                            <button class="btn-cetak-surat" onclick="openSuratModal('KGB')">
                                                <i class="ph-bold ph-file-text"></i> Cetak Surat Pengajuan
                                            </button>
                                        </div>
                                        <table class="custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Mulai Notifikasi</th>
                                                    <th>Nama</th>
                                                    <th>TMT KGB Terakhir</th>
                                                    <th>Status</th>
                                                    <th>Dokumen</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($listKGB as $kgb)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($kgb->tanggal_target)->format('d M Y') }}</td>
                                                    <td>{{ $kgb->pegawai->nama }}</td>
                                                    <td>{{ optional($kgb->pegawai->tmt_kgb_terakhir)->format('d M Y') ?? '-' }}</td>
                                                    <td>
                                                        @if($kgb->status_saat_ini == 'Usulan')
                                                            <span class="status-badge status-missing">Usulan Pengajuan</span>
                                                        @elseif($kgb->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">Proses TTE</span>
                                                        @elseif($kgb->status_saat_ini == 'Upload E-HRM')
                                                            <span class="status-badge status-ok">Upload E-HRM</span>
                                                        @else
                                                            <span class="status-badge status-secondary">{{ $kgb->status_saat_ini }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span style="color: #dc2626; font-weight: 600;">
                                                            {{ $kgb->dokumen_total - $kgb->dokumen_terupload }} Belum
                                                        </span>
                                                    </td>
                                                    <td style="display: flex; gap: 6px;">
                                                        <button class="btn-action-view" onclick="openDashboardDetail('{{ $kgb->pegawai->nip }}', '{{ $kgb->kategori }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                        {{-- Tombol Confirm hanya muncul saat status Proses TTE --}}
                                                        @if($kgb->status_saat_ini == 'Proses')
                                                        <button class="btn-action-confirm" onclick="openConfirmModal({{ $kgb->id }}, '{{ $kgb->pegawai->nama }}')" title="Konfirmasi TTE Selesai">
                                                            <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                        </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div style="padding: 25px; text-align: center; color: #64748b; font-style: italic; background-color: #f8fafc;">
                                            Belum ada tugas untuk saat ini
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                         <!-- TASK: TUBEL (Placeholder) -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-tubel', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">0</div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Tugas Belajar dan Pengembalian Tubel</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div id="task-tubel" class="task-sub-container">
                                <div style="padding: 25px; text-align: center; color: #64748b; font-style: italic; background-color: #f8fafc; border-radius: 0 0 8px 8px;">
                                    Belum ada tugas untuk saat ini
                                </div>
                            </div>
                        </div>

                         <!-- TASK: PENDIDIKAN DAN KEAHLIAN (Monitoring Kompetensi) -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-sertifikat', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">
                                    {{ $listMonitoringDiklat->count() }}
                                </div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Pendidikan dan Keahlian</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div id="task-sertifikat" class="task-sub-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Nama</th>
                                            <th>Keterangan</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($listMonitoringDiklat as $item)
                                        <tr>
                                            <td>{{ $item->tanggal_target ? \Carbon\Carbon::parse($item->tanggal_target)->format('d M Y') : '-' }}</td>
                                            <td>{{ $item->pegawai->nama }}</td>
                                            <td style="max-width: 280px;">{{ $item->keterangan }}</td>
                                            <td>
                                                <span style="font-weight: 600; color: {{ $item->kategori == 'DIKLAT_HUTANG' ? '#dc2626' : '#d97706' }};">
                                                    {{ $item->dokumen_total }} Diklat
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge" style="background: #dcfce7; color: #166534;">Upload E-HRM</span>
                                            </td>
                                            <td>
                                                <button class="btn-action-view" onclick="openDiklatModal('{{ $item->pegawai->nip }}', '{{ $item->kategori }}')" title="Lihat Detail Diklat">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" style="text-align:center;">Tidak ada data monitoring diklat.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                         <!-- TASK: KOMPETENSI -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-kompetensi', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">{{ $listUkom ? $listUkom->count() : 0 }}</div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Uji Kompetensi</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div id="task-kompetensi" class="task-sub-container">
                                <!-- Sub: UKOM Biasa -->
                                <div class="sub-item">
                                    <div class="sub-task-btn" onclick="toggleSubTask('sub-ukom-biasa')">
                                        <span class="sub-badge">{{ isset($ukomBiasa) ? $ukomBiasa->count() : 0 }}</span>
                                        <span style="flex:1;">UKOM</span>
                                    </div>
                                    <div id="sub-ukom-biasa" class="sub-table-container">
                                        <div class="surat-btn-row">
                                            <button class="btn-cetak-surat" onclick="openSuratModal('UKOM')">
                                                <i class="ph-bold ph-file-text"></i> Cetak Surat Pengajuan
                                            </button>
                                        </div>
                                        <table class="custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Nama</th>
                                                    <th>Jenjang Saat Ini</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($ukomBiasa ?? [] as $ukom)
                                                <tr>
                                                    <td>{{ $ukom->pegawai->nama }}</td>
                                                    <td>{{ $ukom->pegawai->jenjang ?? '-' }}</td>
                                                    <td>
                                                        @if($ukom->status_saat_ini == 'Usulan')
                                                            <span class="status-badge status-missing">Usulan Pengajuan</span>
                                                        @elseif($ukom->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">Proses TTE</span>
                                                        @elseif($ukom->status_saat_ini == 'Upload E-HRM')
                                                            <span class="status-badge status-ok">Upload E-HRM</span>
                                                        @else
                                                            <span class="status-badge status-warning">{{ $ukom->status_saat_ini }}</span>
                                                        @endif
                                                    </td>
                                                    <td style="display: flex; gap: 6px;">
                                                        <button class="btn-action-view" onclick="openDashboardDetail('{{ $ukom->pegawai->nip }}', '{{ $ukom->kategori }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="4" style="text-align:center;">Tidak ada data.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Sub: UKOM Madya -->
                                <div class="sub-item">
                                    <div class="sub-task-btn" onclick="toggleSubTask('sub-ukom-madya')">
                                        <span class="sub-badge">{{ isset($ukomMadya) ? $ukomMadya->count() : 0 }}</span>
                                        <span style="flex:1;">UKOM Madya</span>
                                    </div>
                                    <div id="sub-ukom-madya" class="sub-table-container">
                                        <div class="surat-btn-row">
                                            <button class="btn-cetak-surat" onclick="openSuratModal('UKOM')">
                                                <i class="ph-bold ph-file-text"></i> Cetak Surat Pengajuan
                                            </button>
                                        </div>
                                        <table class="custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Nama</th>
                                                    <th>Jenjang Saat Ini</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($ukomMadya ?? [] as $ukom)
                                                <tr>
                                                    <td>{{ $ukom->pegawai->nama }}</td>
                                                    <td>{{ $ukom->pegawai->jenjang ?? '-' }}</td>
                                                    <td>
                                                        @if($ukom->status_saat_ini == 'Usulan')
                                                            <span class="status-badge status-missing">Usulan Pengajuan</span>
                                                        @elseif($ukom->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">Proses TTE</span>
                                                        @elseif($ukom->status_saat_ini == 'Upload E-HRM')
                                                            <span class="status-badge status-ok">Upload E-HRM</span>
                                                        @else
                                                            <span class="status-badge status-warning">{{ $ukom->status_saat_ini }}</span>
                                                        @endif
                                                    </td>
                                                    <td style="display: flex; gap: 6px;">
                                                        <button class="btn-action-view" onclick="openDashboardDetail('{{ $ukom->pegawai->nip }}', '{{ $ukom->kategori }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="4" style="text-align:center;">Tidak ada data.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- DETAIL MODAL MODERN -->
    <div id="detailModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center;">
        <div class="modal-modern-content">
            
            <div class="modal-modern-header">
                <div class="modal-modern-title">
                    <i class="ph-bold ph-user-circle" style="font-size: 24px;"></i>
                    Detail Pegawai
                </div>
                <button class="btn-close-modern" onclick="closeDetailModal()">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>

            <!-- SKELETON LOADING -->
            <link rel="stylesheet" href="{{ asset('css/partials/skeleton.css') }}">
            
            <div id="detailSkeleton" class="modal-modern-body" style="display: none;">
                <!-- Left Skeleton -->
                <div class="skeleton-profile-sidebar">
                    <div class="skeleton-box skeleton-avatar"></div>
                    <div class="skeleton-box skeleton-title" style="margin-top: 15px;"></div>
                    <div class="skeleton-box skeleton-subtitle"></div>
                    <div class="skeleton-box skeleton-text" style="width: 80%; height: 36px; margin-top: 20px; border-radius: 20px;"></div>
                </div>

                <!-- Right Skeleton -->
                <div class="skeleton-info-section">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        @for ($i = 0; $i < 8; $i++)
                        <div>
                            <div class="skeleton-box skeleton-text" style="width: 30%; height: 10px; margin-bottom: 5px;"></div>
                            <div class="skeleton-box skeleton-text" style="width: 70%; height: 16px;"></div>
                        </div>
                        @endfor
                    </div>
                    <div class="skeleton-box skeleton-text" style="width: 100%; height: 100px; border-radius: 8px;"></div>
                </div>
            </div>

            <!-- Fallback Spinner (Optional, hidden by JS logic usually) -->
            <div id="detailLoading" style="text-align: center; padding: 50px; display: none;">
                <i class="ph-bold ph-spinner" style="font-size: 40px; color: #1e3a8a; animation: spin 1s linear infinite;"></i>
                <p style="margin-top: 10px; color: #6b7280;">Memuat data...</p>
            </div>

<div id="modalContentBody" class="modal-modern-body" style="display: none;">
                <!-- LEFT SIDEBAR -->
                <div class="profile-sidebar">
                    <div class="profile-avatar-large" id="detAvatar">
                        <!-- Initials by JS -->
                    </div>
                    <h3 class="profile-name-large" id="detNama">-</h3>
                    <p class="profile-role-large" id="detJabatan">-</p>

                    <button class="btn-reminder-yellow" onclick="openReminderModal()">
                        <i class="ph-fill ph-bell-ringing"></i>
                        Kirim Pengingat
                    </button>
                    <div style="margin-top: 10px; width: 100%;">
                        <div style="font-size: 11px; color: #9ca3af; margin-bottom: 5px; font-weight: 700; text-align: left;">PROYEKSI KGB</div>
                        <div id="detNextKGB" style="background: #eff6ff; color: #1e40af; padding: 8px; border-radius: 6px; font-weight: 600; font-size: 13px; border: 1px solid #dbeafe;">-</div>
                    </div>
                </div>

                <!-- RIGHT CONTENT -->
                <div class="info-section">
                    <div class="info-grid">
                        <div class="info-item"><label>NIP / ID</label><span id="detNIP">-</span></div>
                        <div class="info-item"><label>EMAIL</label><span id="detEmail">-</span></div>
                        <div class="info-item"><label>NO. HP</label><span id="detHP">-</span></div>
                        <div class="info-item"><label>TIPE JABATAN</label><span id="detTipeJabatan">-</span></div>
                        <div class="info-item"><label>PANGKAT / GOLONGAN</label><span id="detPangkat">-</span></div>
                        <div class="info-item"><label>JENJANG</label><span id="detJenjang">-</span></div>
                        <div class="info-item"><label>TMT CPNS</label><span id="detTmt">-</span></div>
                        <div class="info-item"><label>ANGKA KREDIT</label><span id="detKredit">-</span></div>
                    </div>

                    <div class="doc-section borderless">
                        <div class="doc-section-title">
                            <i class="ph-fill ph-file-text" style="color: #4b5563;"></i>
                            Dokumen Wajib
                        </div>
                        <div id="docStatusContainer">
                            <!-- Injected by JS -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL DASHBOARD DETAIL (KHUSUS KATEGORI) -->
    <div id="dashboardDetailModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2300; justify-content:center; align-items:center;">
        <div style="background:#fff; width:650px; max-width:92vw; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.15); display:flex; flex-direction:column; overflow:hidden;">
            
            <!-- Header -->
            <div style="padding:20px 25px; border-bottom:1px solid #e2e8f0; display:flex; flex-direction:column; background:#f8fafc;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div style="display:flex; align-items:center; gap:15px;">
                        <div id="dashModalAvatar" style="background:#1e40af; color:#fff; width:50px; height:50px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:700;">
                        </div>
                        <div style="display:flex; flex-direction:column; gap:8px; padding-top:2px;">
                            <h3 id="dashModalNama" style="margin:0; font-size:18px; font-weight:700; color:#1e293b; line-height:1.1;">Memuat...</h3>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <p id="dashModalKategori" style="margin:0; font-size:12px; font-weight:600; color:#3b82f6; background:#eff6ff; padding:4px 10px; border-radius:12px; border:1px solid #bfdbfe; line-height:1;">-</p>
                                <div style="width:2px; height:14px; background-color:#cbd5e1; border-radius:1px;"></div>
                                <p id="dashModalNip" style="margin:0; font-size:13px; font-weight:500; color:#64748b; line-height:1;">-</p>
                            </div>
                        </div>
                    </div>
                    <button onclick="closeDashboardDetail()" style="background:none; border:none; cursor:pointer; padding:5px; color:#94a3b8; transition:color 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">
                        <i class="ph-bold ph-x" style="font-size:20px;"></i>
                    </button>
                </div>
                
                <!-- PROGRESS TRACKER SECTION -->
                <style>
                .tracker-step { display:flex; flex-direction:column; align-items:center; justify-content:flex-start; position:relative; z-index:1; flex:1; }
                .tracker-step .circle { width:32px; height:32px; border-radius:50%; background:#eff6ff; display:flex; align-items:center; justify-content:center; border:2px solid #bfdbfe; z-index:2; position:relative; transition:all 0.3s; flex-shrink:0; }
                .tracker-step.done .circle { background:#3b82f6; border-color:#3b82f6; }
                .tracker-step.done .circle::after { content:''; width:10px; height:10px; background:#fff; border-radius:50%; }
                .tracker-step.active .circle { background:#bfdbfe; border-color:#3b82f6; box-shadow:0 0 0 4px #eff6ff; }
                .tracker-step.active-inner .circle::after { content:''; width:12px; height:12px; background:#3b82f6; border-radius:50%; }
                
                .tracker-step .label { font-size:13px; font-weight:700; color:#1e293b; margin-top:10px; text-align:center; transition:color 0.3s; line-height: 1.2; }
                .tracker-step .sub-label { font-size:11px; color:#64748b; text-align:center; margin-top:4px; }
                
                .tracker-step:not(.done):not(.active) .label { color:#94a3b8; }
                .tracker-step:not(.done):not(.active) .sub-label { color:#cbd5e1; }
                
                .tracker-line { height:4px; flex:1; margin:14px -10px 0 -10px; z-index:0; transition:all 0.3s; }
                .tracker-line.done { background:#3b82f6; }
                .tracker-line.dashed { border-top:4px dashed #cbd5e1; background:transparent; }
                </style>
                <div id="dashModalTrackerContainer" style="margin-top:25px; display:none; width: 100%; border-top: 1px dashed #e2e8f0; padding-top: 20px;">
                    <h4 style="font-size:14px; font-weight:700; color:#1e293b; margin:0 0 15px 0; text-align:center;">Progres Status</h4>
                    <div id="dashModalTracker" style="display:flex; align-items:flex-start; width:100%; margin:0 auto;">
                        <!-- Tracker steps will be injected by JS -->
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div style="padding:25px; overflow-y:auto; max-height:60vh;">
                <div id="dashModalLoading" style="text-align:center; padding:30px; color:#64748b;">
                    <i class="ph-bold ph-spinner" style="font-size:32px; color:#1e3a8a; animation:spin 1s linear infinite;"></i>
                    <p style="margin-top:10px;">Mengambil data dokumen...</p>
                </div>

                <div id="dashModalContentBody" style="display:none;">
                    <!-- Info Grid -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:25px; background:#f1f5f9; padding:15px; border-radius:8px; border:1px solid #e2e8f0;">
                        <div>
                            <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">EMAIL</div>
                            <div id="dashModalEmail" style="font-size:14px; color:#0f172a; font-weight:500;">-</div>
                        </div>
                        
                        <!-- Dynamic fields based on category -->
                        <div id="dashModalAKWrapper" style="display:none;">
                            <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">ANGKA KREDIT</div>
                            <div id="dashModalAK" style="font-size:14px; color:#0f172a; font-weight:500;">-</div>
                        </div>
                        <div id="dashModalKGBWrapper" style="display:none;">
                            <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">PROYEKSI KGB</div>
                            <div id="dashModalKGB" style="font-size:14px; color:#0f172a; font-weight:500;">-</div>
                        </div>
                        <div id="dashModalPangkatWrapper" style="display:none;">
                            <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">PANGKAT/GOLONGAN</div>
                            <div id="dashModalPangkat" style="font-size:14px; color:#0f172a; font-weight:500;">-</div>
                        </div>
                    </div>

                    <!-- Document Requirements -->
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:15px;">
                        <i class="ph-fill ph-check-square-offset" style="color:#1e40af; font-size:18px;"></i>
                        <h4 style="margin:0; font-size:15px; font-weight:700; color:#1e293b;">Dokumen Persyaratan</h4>
                    </div>
                    
                    <div id="dashModalDocsContainer" style="display:flex; flex-direction:column; gap:10px;">
                        <!-- Injected by JS -->
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div id="dashModalFooter" style="padding:15px 25px; border-top:1px solid #e2e8f0; background:#f8fafc; display:none; justify-content:flex-end;">
                <button class="btn-reminder-yellow" onclick="openReminderModal()" style="width:auto; padding:8px 20px; margin:0; display:flex; align-items:center; gap:8px;">
                    <i class="ph-bold ph-bell-ringing"></i> Kirim Pengingat
                </button>
            </div>

        </div>
    </div>

    <!-- REMINDER MODAL -->
    <div id="reminderModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2400; justify-content: center; align-items: center;">
        <div class="modal-content" style="background: white; width: 600px; padding: 0; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); position: relative; overflow: hidden; display: flex; flex-direction: column;">
            
            <!-- Header -->
            <div style="padding: 20px 25px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="background: #fef3c7; color: #d97706; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="ph-bold ph-bell-ringing" style="font-size: 20px;"></i>
                    </div>
                    <h2 style="margin: 0; color: #1e293b; font-size: 18px; font-weight: 700;">Kirim Pengingat Manual</h2>
                </div>
                <button onclick="closeReminderModal()" style="background: none; border: none; cursor: pointer; color: #94a3b8; transition: color 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">
                    <i class="ph-bold ph-x" style="font-size: 20px;"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div style="padding: 25px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px; letter-spacing: 0.5px;">PILIH TEMPLATE PESAN</label>
                <select id="reminderTemplate" style="width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 20px; color: #1e293b; font-size: 14px; outline: none; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onchange="toggleMessageMode()" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)'">
                    <option value="" disabled selected>Pilih Template Pengingat</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->kategori }}</option>
                    @endforeach
                </select>

                <div style="display: flex; align-items: center; margin-bottom: 20px; background: #f1f5f9; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <input type="checkbox" id="checkCustom" onchange="toggleMessageMode()" style="margin-right: 12px; width: 18px; height: 18px; cursor: pointer; accent-color: #3b82f6;">
                    <label for="checkCustom" style="font-size: 14px; font-weight: 600; color: #334155; cursor: pointer; user-select: none;">Apakah anda ingin menambahkan/mengedit pesan bawaan?</label>
                </div>

                <label style="display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px; letter-spacing: 0.5px;">ISI PESAN</label>
                <textarea id="reminderMessage" disabled style="width: 100%; height: 120px; padding: 15px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 10px; resize: none; font-size: 14px; color: #1e293b; outline: none; transition: all 0.2s; background: #f8fafc;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'; this.style.background='#ffffff'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'; if(this.disabled) this.style.background='#f8fafc'"></textarea>
            </div>

            <!-- Footer -->
            <div style="padding: 20px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 12px;">
                <button onclick="closeReminderModal()" style="padding: 10px 24px; background: white; color: #64748b; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#475569'" onmouseout="this.style.background='white'; this.style.color='#64748b'">Batal</button>
                <button onclick="sendReminder()" id="btnSendManual" style="padding: 10px 24px; background: #f59e0b; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.2), 0 2px 4px -1px rgba(245, 158, 11, 0.1);" onmouseover="this.style.background='#d97706'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#f59e0b'; this.style.transform='translateY(0)'">
                    <i class="ph-bold ph-paper-plane-right"></i> Kirim
                </button>
            </div>

        </div>
    </div>

    <!-- CONFIRM MODAL -->
    <div id="confirmModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2200; justify-content: center; align-items: center;">
        <div class="confirm-modal-content">
            <div class="confirm-modal-icon">
                <i class="ph-fill ph-check-circle" style="font-size: 48px; color: #10b981;"></i>
            </div>
            <h3 class="confirm-modal-title">Konfirmasi Tugas</h3>
            <p class="confirm-modal-text">Apakah Anda yakin sudah mengajukan KGB untuk:</p>
            <p class="confirm-modal-name" id="confirmPegawaiName">-</p>
            <div class="confirm-modal-actions">
                <button class="confirm-btn-cancel" onclick="closeConfirmModal()">Batal</button>
                <button class="confirm-btn-yes" id="confirmYesBtn" onclick="submitConfirm()">Ya, Sudah Diproses</button>
            </div>
        </div>
    </div>

    <!-- UKOM MODAL -->
    <div id="ukomModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2200; justify-content: center; align-items: center;">
        <div class="confirm-modal-content">
            <div class="confirm-modal-icon" style="background:#dbeafe; width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:20px;">
                <i class="ph-fill ph-medal" style="font-size: 48px; color: #1e3a8a;"></i>
            </div>
            <h3 class="confirm-modal-title">Daftarkan Uji Kompetensi</h3>
            <p class="confirm-modal-text">Pindahkan pegawai berikut ke kategori Uji Kompetensi (UKOM) dan kirimkan notifikasi pemberitahuan kepadanya via email?</p>
            <p class="confirm-modal-name" id="ukomPegawaiName">-</p>
            <div class="confirm-modal-actions">
                <button class="confirm-btn-cancel" onclick="closeUkomModal()">Batal</button>
                <button class="confirm-btn-yes" id="ukomYesBtn" onclick="submitUkom()" style="background:#1e3a8a; color:white;">Ya, Daftarkan UKOM</button>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL DIKLAT -->
    <div id="diklatModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2200; justify-content:center; align-items:center;">
        <div style="background:#fff; width:900px; max-width:92vw; max-height:85vh; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.15); overflow:hidden; display:flex; flex-direction:column;">
            <div style="padding:20px 25px; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                <div>
                    <h3 id="diklatModalTitle" style="margin:0; font-size:17px; font-weight:700; color:#1e3a8a;"></h3>
                    <p id="diklatModalSub" style="margin:4px 0 0; font-size:13px; color:#64748b;"></p>
                </div>
                <button onclick="closeDiklatModal()" style="background:none; border:none; cursor:pointer; padding:5px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div style="padding:15px 25px 25px; overflow-y:auto; flex:1;">
                <div id="diklatModalLoading" style="text-align:center; padding:30px; color:#64748b;">
                    <p>Memuat data...</p>
                </div>
                <div style="overflow-x:auto;">
                    <table id="diklatModalTable" class="custom-table" style="display:none; min-width:750px;">
                        <thead>
                            <tr>
                                <th style="width:35px; text-align:center;">No</th>
                                <th style="min-width:200px;">Nama Diklat</th>
                                <th style="min-width:120px;">Periode</th>
                                <th style="width:80px; text-align:center;">Jenis</th>
                                <th style="min-width:150px;">Sertifikat</th>
                                <th style="width:80px; text-align:center;">Arsip</th>
                            </tr>
                        </thead>
                        <tbody id="diklatModalBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function openDiklatModal(nip, kategori) {
        const modal = document.getElementById('diklatModal');
        const loading = document.getElementById('diklatModalLoading');
        const table = document.getElementById('diklatModalTable');
        const body = document.getElementById('diklatModalBody');

        modal.style.display = 'flex';
        loading.style.display = 'block';
        table.style.display = 'none';
        body.innerHTML = '';

        const label = kategori === 'DIKLAT_HUTANG' ? 'Sertifikat Belum Diupload' : 'Dokumen Belum Lengkap';

        fetch(`/dashboard/diklat-detail/${nip}/${kategori}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('diklatModalTitle').textContent = data.pegawai;
                document.getElementById('diklatModalSub').textContent = `NIP: ${data.nip} — ${data.total} diklat (${label})`;

                data.data.forEach((d, i) => {
                    const arsipClass = d.arsip === 'Ada'
                        ? 'style="color:#166534; font-weight:600; white-space:nowrap; text-align:center;"'
                        : 'style="color:#dc2626; font-weight:600; white-space:nowrap; text-align:center;"';
                    body.innerHTML += `
                        <tr>
                            <td>${i + 1}</td>
                            <td style="max-width:200px; font-weight:500;">${d.nama_diklat}</td>
                            <td style="white-space:nowrap; font-size:12px;">${d.tanggal_mulai}<br>s/d ${d.tanggal_selesai}</td>
                            <td><span style="background:#e0e7ff; color:#3730a3; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600;">${d.jenis}</span></td>
                            <td style="font-size:12px;">${d.sertifikat}</td>
                            <td ${arsipClass}>${d.arsip}</td>
                        </tr>`;
                });

                loading.style.display = 'none';
                table.style.display = 'table';
            })
            .catch(() => {
                loading.innerHTML = '<p style="color:#dc2626;">Gagal memuat data.</p>';
            });
    }

    function closeDiklatModal() {
        document.getElementById('diklatModal').style.display = 'none';
    }

    document.getElementById('diklatModal').addEventListener('click', function(e) {
        if (e.target === this) closeDiklatModal();
    });
    </script>

    <!-- SURAT PENGAJUAN MODAL -->
    <div id="suratModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2500; justify-content:center; align-items:center;">
        <div style="background:#fff; width:800px; max-width:94vw; max-height:90vh; border-radius:14px; box-shadow:0 20px 60px rgba(0,0,0,0.2); overflow:hidden; display:flex; flex-direction:column;">
            
            <!-- Header -->
            <div style="padding:20px 25px; border-bottom:1px solid #e2e8f0; background:#f8fafc; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="background:#dbeafe; color:#1e3a8a; width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                        <i class="ph-bold ph-file-text" style="font-size:22px;"></i>
                    </div>
                    <div>
                        <h3 id="suratModalTitle" style="margin:0; font-size:17px; font-weight:700; color:#1e293b;">Cetak Surat Pengajuan</h3>
                        <p id="suratModalSub" style="margin:2px 0 0; font-size:12px; color:#64748b;">Pilih pegawai dan isi data surat</p>
                    </div>
                </div>
                <button onclick="closeSuratModal()" style="background:none; border:none; cursor:pointer; padding:5px; color:#94a3b8; transition:color 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">
                    <i class="ph-bold ph-x" style="font-size:22px;"></i>
                </button>
            </div>

            <!-- Body -->
            <div style="padding:20px 25px; overflow-y:auto; flex:1;">

                <!-- Loading -->
                <div id="suratLoading" style="text-align:center; padding:40px; color:#64748b;">
                    <i class="ph-bold ph-spinner" style="font-size:32px; color:#1e3a8a; animation:spin 1s linear infinite;"></i>
                    <p style="margin-top:10px;">Mengambil data pegawai...</p>
                </div>

                <!-- Content (hidden until loaded) -->
                <div id="suratContent" style="display:none;">

                    <!-- STEP 1: Pilih Pegawai -->
                    <div style="margin-bottom:20px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                            <h4 style="margin:0; font-size:14px; font-weight:700; color:#1e293b;">1. Pilih Pegawai</h4>
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; font-weight:600; color:#3b82f6;">
                                <input type="checkbox" id="suratSelectAll" onchange="suratToggleAll()" style="width:16px; height:16px; accent-color:#1e3a8a; cursor:pointer;">
                                Pilih Semua
                            </label>
                        </div>

                        <div id="suratGroupsContainer">
                            <!-- Groups injected by JS -->
                        </div>
                    </div>

                    <!-- STEP 2: Data Surat -->
                    <div style="border-top:1px solid #e2e8f0; padding-top:20px;">
                        <h4 style="margin:0 0 15px; font-size:14px; font-weight:700; color:#1e293b;">2. Data Surat</h4>
                        
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:5px; letter-spacing:0.3px;">NOMOR SURAT</label>
                                <input type="text" id="suratNomor" placeholder="Contoh: B-123/KP.01/04/2026" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; outline:none; transition:border 0.2s; box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:5px; letter-spacing:0.3px;">TANGGAL SURAT</label>
                                <input type="date" id="suratTanggal" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; outline:none; transition:border 0.2s; box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:5px; letter-spacing:0.3px;">TUJUAN SURAT (KEPADA YTH.)</label>
                                <input type="text" id="suratTujuan" placeholder="Contoh: Kepala Biro Kepegawaian" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; outline:none; transition:border 0.2s; box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:5px; letter-spacing:0.3px;">NAMA PENANDATANGAN</label>
                                <input type="text" id="suratNamaTTD" placeholder="Nama lengkap pejabat" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; outline:none; transition:border 0.2s; box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:5px; letter-spacing:0.3px;">NIP PENANDATANGAN</label>
                                <input type="text" id="suratNipTTD" placeholder="NIP pejabat" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; outline:none; transition:border 0.2s; box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:5px; letter-spacing:0.3px;">JABATAN PENANDATANGAN</label>
                                <input type="text" id="suratJabatanTTD" placeholder="Contoh: Kepala Sub Bagian Kepegawaian" value="Kepala Sub Bagian Kepegawaian" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; outline:none; transition:border 0.2s; box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div id="suratFooter" style="display:none; padding:16px 25px; border-top:1px solid #e2e8f0; background:#f8fafc; justify-content:space-between; align-items:center; flex-shrink:0;">
                <span id="suratSelectedCount" style="font-size:13px; font-weight:600; color:#64748b;">0 pegawai terpilih</span>
                <div style="display:flex; gap:10px;">
                    <button onclick="closeSuratModal()" style="padding:10px 22px; background:white; color:#64748b; border:1px solid #cbd5e1; border-radius:8px; cursor:pointer; font-weight:600; font-size:13px; transition:all 0.2s; font-family:'Poppins',sans-serif;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">Batal</button>
                    <button id="btnGenerateSurat" onclick="generateSurat()" style="padding:10px 22px; background:linear-gradient(135deg,#1e3a8a,#2563eb); color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:13px; display:flex; align-items:center; gap:8px; transition:all 0.2s; box-shadow:0 4px 6px -1px rgba(30,58,138,0.2); font-family:'Poppins',sans-serif;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                        <i class="ph-bold ph-download-simple"></i> Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('partials.sync_loading')

    <script src="{{ asset('js/app-common.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}?v={{ time() }}"></script>
    <!-- JS Loaded via external file -->
    @include('partials.change_password_modal')
</body>
</html>
