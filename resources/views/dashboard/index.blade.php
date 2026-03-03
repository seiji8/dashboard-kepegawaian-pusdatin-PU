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
                                    {{ $kpStruktural->count() + $kpFungsional->count() }}
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
                                        <table class="custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal Target</th>
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
                                                            <span class="status-badge status-missing">Usulan</span>
                                                        @elseif($item->status_saat_ini == 'Mendekati' || $item->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">{{ $item->status_saat_ini }}</span>
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
                                                        <button class="btn-action-view" onclick="openDetailModal('{{ $item->pegawai->nip }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                        @if($item->status_saat_ini == 'Usulan')
                                                        <button class="btn-action-confirm" onclick="openConfirmModal({{ $item->id }}, '{{ $item->pegawai->nama }}')" title="Konfirmasi Proses">
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
                                                            <span class="status-badge status-missing">Usulan</span>
                                                        @elseif($item->status_saat_ini == 'Mendekati' || $item->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">{{ $item->status_saat_ini }}</span>
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
                                                        <button class="btn-action-view" onclick="openDetailModal('{{ $item->pegawai->nip }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                        @if($item->status_saat_ini == 'Usulan')
                                                        <button class="btn-action-confirm" onclick="openConfirmModal({{ $item->id }}, '{{ $item->pegawai->nama }}')" title="Konfirmasi Proses">
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

                                <!-- Sub: Reguler (Placeholder) -->
                                <div class="sub-item">
                                    <div class="sub-task-btn" onclick="toggleSubTask('sub-reguler')">
                                        <span class="sub-badge">0</span>
                                        <span style="flex:1;">Reguler</span>
                                    </div>
                                    <div id="sub-reguler" class="sub-table-container">
                                        <div style="padding: 10px; text-align: center; color: #888;">Belum ada data</div>
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
                                                        <span class="status-badge status-missing">Usulan</span>
                                                    @elseif($item->status_saat_ini == 'Mendekati' || $item->status_saat_ini == 'Proses')
                                                        <span class="status-badge status-warning">{{ $item->status_saat_ini }}</span>
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
                                                    <button class="btn-action-view" onclick="openDetailModal('{{ $item->pegawai->nip }}')">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                    </button>
                                                    @if($item->status_saat_ini == 'Usulan')
                                                    <button class="btn-action-confirm" onclick="openUkomModal({{ $item->id }}, '{{ $item->pegawai->nama }}')" title="Daftarkan Uji Kompetensi">
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
                                                            <span class="status-badge status-missing">Usulan</span>
                                                        @elseif($kgb->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">Proses</span>
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
                                                        <button class="btn-action-view" onclick="openDetailModal('{{ $kgb->pegawai->nip }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                        {{-- Tombol Confirm hanya muncul saat status Usulan (Merah) --}}
                                                        @if($kgb->status_saat_ini == 'Usulan')
                                                        <button class="btn-action-confirm" onclick="openConfirmModal({{ $kgb->id }}, '{{ $kgb->pegawai->nama }}')" title="Konfirmasi Proses">
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
                                <span style="font-weight:600; font-size:16px; flex:1;">SK Tubel dan Pengembalian Tubel</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div id="task-tubel" class="task-sub-container">
                                <div style="padding: 25px; text-align: center; color: #64748b; font-style: italic; background-color: #f8fafc; border-radius: 0 0 8px 8px;">
                                    Belum ada tugas untuk saat ini
                                </div>
                            </div>
                        </div>

                         <!-- TASK: SERTIFIKAT (Placeholder) -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-sertifikat', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">0</div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Upload Sertifikat Keahlian</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div id="task-sertifikat" class="task-sub-container">
                                <div style="padding: 25px; text-align: center; color: #64748b; font-style: italic; background-color: #f8fafc; border-radius: 0 0 8px 8px;">
                                    Belum ada tugas untuk saat ini
                                </div>
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
                                <div class="sub-table-container active" style="display:block;">
                                    @if(isset($listUkom) && $listUkom->count() > 0)
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
                                                @foreach($listUkom as $ukom)
                                                <tr>
                                                    <td>{{ $ukom->pegawai->nama }}</td>
                                                    <td>{{ $ukom->pegawai->jenjang ?? '-' }}</td>
                                                    <td>
                                                        <span class="status-badge status-warning">{{ $ukom->status_saat_ini }}</span>
                                                    </td>
                                                    <td style="display: flex; gap: 6px;">
                                                        <button class="btn-action-view" onclick="openDetailModal('{{ $ukom->pegawai->nip }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div style="padding: 25px; text-align: center; color: #64748b; font-style: italic; background-color: #f8fafc;">
                                            Belum ada pegawai dalam tahapan Uji Kompetensi
                                        </div>
                                    @endif
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

    <!-- REMINDER MODAL -->
    <div id="reminderModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2100; justify-content: center; align-items: center;">
        <div class="modal-content" style="background: white; width: 600px; padding: 30px; border-radius: 12px; position: relative;">
            <h2 style="text-align: center; color: #1e3a8a; margin-bottom: 30px;">Pengingat Manual</h2>
            
            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Pilih Template</label>
            <select id="reminderTemplate" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 20px; color: #9ca3af;" onchange="toggleMessageMode()">
                <option value="" disabled selected>Pilih</option>
                @foreach($templates as $template)
                    <option value="{{ $template->id }}">{{ $template->kategori }}</option>
                @endforeach
            </select>

            <div style="display: flex; align-items: center; margin-bottom: 20px;">
                <input type="checkbox" id="checkCustom" onchange="toggleMessageMode()" style="margin-right: 10px; width: 18px; height: 18px;">
                <label for="checkCustom">Apakah anda ingin menambahkan pesan custom?</label>
            </div>

            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Isi Pesan</label>
            <textarea id="reminderMessage" disabled style="width: 100%; height: 120px; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 30px; resize: none;"></textarea>

            <div style="display: flex; justify-content: flex-end; gap: 15px;">
                <button onclick="closeReminderModal()" style="padding: 10px 30px; background: #fca5a5; color: #991b1b; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Batal</button>
                <button onclick="sendReminder()" id="btnSendManual" style="padding: 10px 30px; background: #cbd5e1; color: #1e3a8a; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Kirim</button>
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

    @include('partials.sync_loading')

    <script src="{{ asset('js/app-common.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <!-- JS Loaded via external file -->
    @include('partials.change_password_modal')
</body>
</html>