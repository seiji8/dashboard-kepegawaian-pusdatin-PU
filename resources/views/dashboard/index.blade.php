<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DashboardAlert</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons for fallback since assets are missing -->
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"> -->
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo"><span class="logo-highlight">Dashboard</span>Alert</h1>
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
                            <span class="badge" id="notifBadge" style="display: none;">0</span>
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
                            <i class="ph-fill ph-warning-circle" style="font-size: 24px; color: #fbbf24;"></i>
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
                                                    <th>NAMA</th>
                                                    <th>Jabatan</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($kpStruktural as $item)
                                                <tr>
                                                    <td>{{ $item->tanggal_target ?? '-' }}</td>
                                                    <td>{{ $item->pegawai->nama }}</td>
                                                    <td>{{ $item->pegawai->jabatan_saat_ini }}</td>
                                                    <td><span class="status-badge status-warning">{{ $item->status_saat_ini }}</span></td>
                                                    <td>
                                                        <button class="btn-action-view" onclick="openDetailModal('{{ $item->pegawai->nip }}')">
                                                            <i class="ph-bold ph-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="5" style="text-align:center;">Tidak ada data.</td></tr>
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
                                                    <th>Tanggal</th>
                                                    <th>NAMA</th>
                                                    <th>Pangkat</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($kpFungsional as $item)
                                                <tr>
                                                    <td>{{ $item->tanggal_target ?? '-' }}</td>
                                                    <td>{{ $item->pegawai->nama }}</td>
                                                    <td>{{ $item->pegawai->pangkat_saat_ini }}</td>
                                                    <td>
                                                        @if($item->status_saat_ini == 'Mendekati')
                                                            <span class="status-badge status-warning">Mendekati</span>
                                                        @elseif($item->status_saat_ini == 'Usulan')
                                                            <span class="status-badge status-ok">Usulan</span>
                                                        @else
                                                            <span class="status-badge status-missing">{{ $item->status_saat_ini }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button class="btn-action-view" onclick="openDetailModal('{{ $item->pegawai->nip }}')">
                                                            <i class="ph-bold ph-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="5" style="text-align:center;">Tidak ada data.</td></tr>
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

                        <!-- TASK: KENAIKAN JENJANG (Placeholder) -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-jenjang', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">0</div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Kenaikan Jenjang</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div id="task-jenjang" class="task-sub-container">
                                <div style="padding: 25px; text-align: center; color: #64748b; font-style: italic; background-color: #f8fafc; border-radius: 0 0 8px 8px;">
                                    Belum ada tugas untuk saat ini
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

                         <!-- TASK: KOMPETENSI (Placeholder) -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-kompetensi', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">0</div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Uji Kompetensi</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div id="task-kompetensi" class="task-sub-container">
                                <div style="padding: 25px; text-align: center; color: #64748b; font-style: italic; background-color: #f8fafc; border-radius: 0 0 8px 8px;">
                                    Belum ada tugas untuk saat ini
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- DETAIL MODAL -->
    <div id="detailModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center;">
        <div class="modal-content" style="background: white; width: 800px; max-height: 90vh; overflow-y: auto; border-radius: 12px; padding: 0;">
            
            <div style="padding: 20px; border-bottom: 1px solid #e5e7eb;">
                <h2 style="font-size: 18px; color: #1e3a8a; font-weight: 700; text-align: center;">Data Lengkap Pegawai</h2>
            </div>

            <div style="padding: 30px; display: flex; gap: 40px;">
                <div style="width: 150px; height: 180px; background: #e5e7eb; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="#9ca3af" stroke="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                
                <div id="modalContentBody" style="flex: 1; display: grid; grid-template-columns: 180px 1fr; gap: 10px; font-size: 14px;">
                    <!-- Content injected by JS -->
                    <p>Loading...</p>
                </div>
            </div>

            <div style="padding: 20px 30px; display: flex; justify-content: flex-end; gap: 10px;">
                <button onclick="openReminderModal()" style="padding: 10px 15px; background: #fbbf24; border: none; border-radius: 8px; cursor: pointer; color: white;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                </button>
                <button onclick="closeDetailModal()" style="padding: 10px 25px; background: #1e3a8a; color: white; border: none; border-radius: 8px; cursor: pointer;">Kembali</button>
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

    <!-- LOADING MODAL -->
    <div id="loadingModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2300; justify-content: center; align-items: center;">
        <div class="loading-modal-content">
            <div class="loading-spinner"></div>
            <div>
                <p class="loading-text">Sedang Sinkronisasi Data...</p>
                <p class="loading-subtext">Mohon tunggu, proses ini mungkin memakan waktu beberapa saat.</p>
            </div>
        </div>
    </div>

    <div id="syncToast" class="toast-notification">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span>Sinkronisasi Data Berhasil!</span>
    </div>

    <script>
        // --- 1. DROPDOWN PROFILE ---
        function toggleDropdown() {
            var dropdown = document.getElementById("profileDropdown");
            if (dropdown.style.display === "block") {
                dropdown.style.display = "none";
            } else {
                dropdown.style.display = "block";
            }
        }

        // --- 2. ACCORDION TASK ---
        function toggleMainTask(targetId, headerElement) {
            const targetContent = document.getElementById(targetId);
            if (targetContent.classList.contains('active')) {
                targetContent.classList.remove('active');
                headerElement.querySelector('.arrow-icon').style.transform = 'rotate(0deg)';
            } else {
                targetContent.classList.add('active');
                headerElement.querySelector('.arrow-icon').style.transform = 'rotate(180deg)';
            }
        }

        function toggleSubTask(targetId) {
            const targetTable = document.getElementById(targetId);
            if (targetTable.classList.contains('active')) {
                targetTable.classList.remove('active');
            } else {
                targetTable.classList.add('active');
            }
        }

        // --- 3. MODALS ---
        const detailModal = document.getElementById('detailModal');
        const reminderModal = document.getElementById('reminderModal');
        let currentDetailNip = null;

        function openDetailModal(nip) {
            currentDetailNip = nip;
            detailModal.style.display = 'flex';
            const contentBody = document.getElementById('modalContentBody');
            
            // Show loading state
            contentBody.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 20px;"><i class="ph-bold ph-spinner ph-spin" style="font-size: 32px; color: #1e3a8a;"></i><p>Memuat data pegawai...</p></div>';

            fetch(`/data-pegawai/${nip}`)
                .then(response => response.json())
                .then(res => {
                    if(res.success) {
                        const data = res.data;
                        let docsHtml = '';
                        if (data.missing_documents && data.missing_documents.length > 0) {
                            docsHtml = `
                                <div style="grid-column: 1 / -1; margin-top: 20px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                    <div style="display: flex; background-color: #f1f5f9; padding: 15px; border-bottom: 1px solid #e2e8f0;">
                                        <div style="width: 50px; font-weight: 700; color: #0f172a; text-align: center;">No</div>
                                        <div style="flex: 1; font-weight: 500; color: #dc2626; padding-left: 15px;">Dokumen Yang Perlu Diunggah!</div>
                                    </div>
                                    <div style="background-color: #fff;">
                                        ${data.missing_documents.map((doc, index) => `
                                            <div style="display: flex; padding: 15px; border-bottom: index < data.missing_documents.length - 1 ? '1px solid #f1f5f9' : 'none'; align-items: center;">
                                                <div style="width: 50px; font-weight: 700; color: #334155; text-align: center;">${index + 1}</div>
                                                <div style="flex: 1; color: #ef4444; font-weight: 500; padding-left: 15px;">
                                                    ${doc.nama_dokumen}
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        } else {
                            docsHtml = `
                                <div style="grid-column: 1 / -1; margin-top: 15px; color: #059669; font-weight: 600; display: flex; align-items: center; background: #ecfdf5; padding: 15px; border-radius: 8px; border: 1px solid #a7f3d0;">
                                    <i class="ph-fill ph-check-circle" style="margin-right: 10px; font-size: 20px;"></i>
                                    Semua Dokumen Lengkap
                                </div>
                            `;
                        }

                        contentBody.innerHTML = `
                            <div style="font-weight: 600;">Nama :</div><div>${data.nama}</div>
                            <div style="font-weight: 600;">NIP :</div><div>${data.nip}</div>
                            <div style="font-weight: 600;">Jabatan :</div><div>${data.jabatan}</div>
                            <div style="font-weight: 600;">Tipe Jabatan :</div><div>${data.tipe_jabatan}</div>
                            <div style="font-weight: 600;">Pangkat :</div><div>${data.pangkat}</div>
                            <div style="font-weight: 600;">Jenjang :</div><div>${data.jenjang}</div>
                            <div style="font-weight: 600;">TMT CPNS :</div><div>${data.tmt_cpns}</div>
                            <div style="font-weight: 600;">Angka Kredit :</div><div>${data.angka_kredit}</div>
                            <div style="font-weight: 600;">No HP :</div><div>${data.no_hp}</div>
                            <div style="font-weight: 600;">Email :</div><div>${data.email}</div>
                            <div style="font-weight: 600; color: #d97706;">Proyeksi KGB :</div><div style="font-weight: 600; color: #d97706;">${data.next_kgb}</div>
                            ${docsHtml}
                        `;
                    } else {
                        contentBody.innerHTML = '<p style="color: red;">Gagal memuat data.</p>';
                    }
                })
                .catch(err => {
                    console.error(err);
                    contentBody.innerHTML = '<p style="color: red;">Terjadi kesalahan koneksi.</p>';
                });
        }

        function closeDetailModal() {
            detailModal.style.display = 'none';
        }

        function openReminderModal() {
            reminderModal.style.display = 'flex';
        }

        function closeReminderModal() {
            reminderModal.style.display = 'none';
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
                    alert("Harap isi pesan custom!");
                    return;
                }
                payload = { custom_message: customMessage };
            } else {
                if (!templateId) {
                    alert("Harap pilih template!");
                    return;
                }
                payload = { template_id: templateId };
            }

            const btnSend = document.getElementById('btnSendManual');
            const originalText = btnSend.innerText;
            btnSend.innerText = 'Mengirim...';
            btnSend.disabled = true;

            fetch(`/data-pegawai/${currentDetailNip}/send-manual`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Email berhasil dikirim!');
                    closeReminderModal();
                } else {
                     alert(data.message || 'Gagal mengirim email.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengirim email.');
            })
            .finally(() => {
                btnSend.innerText = originalText;
                btnSend.disabled = false;
            });
        }

        // --- GLOBAL CLICK LISTENER (GABUNGAN) ---
        window.onclick = function(event) {
            
            // Logika Dropdown Profile
            if (!event.target.closest('.profile-btn')) {
                var dropdowns = document.getElementsByClassName("dropdown-menu");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }

            // Logika Notif Dropdown (tutup jika klik di luar)
            if (!event.target.closest('.notif-wrapper')) {
                var notifDropdown = document.getElementById('notifDropdown');
                if (notifDropdown) notifDropdown.classList.remove('active');
            }

            // Logika Menutup Modal jika klik di area gelap (overlay)
            if (event.target == detailModal) {
                closeDetailModal();
            }
            if (event.target == reminderModal) {
                closeReminderModal();
            }
        }

        // --- 4. SINKRONISASI TOAST & LOGIC ---
        function triggerSync() {
            var loadingModal = document.getElementById('loadingModal');

            // 1. Show Loading Modal
            loadingModal.style.display = 'flex';

            // 2. AJAX Request
            fetch('{{ route("sync.now") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                loadingModal.style.display = 'none'; // Hide modal

                if (data.success) {
                    showSyncToast(data.message); // Tampilkan pesan dari server
                    
                    // Reload setelah 2 detik agar toast terbaca
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    alert('Gagal: ' + data.message);
                }
            })
            .catch(error => {
                loadingModal.style.display = 'none'; // Hide modal
                console.error('Error:', error);
                alert('Terjadi kesalahan koneksi.');
            });
        }

        function resetSyncBtn() {
            var btn = document.getElementById('btnSync');
            var icon = document.getElementById('iconSync');
            var text = document.getElementById('textSync');

            btn.disabled = false;
            btn.style.opacity = "1";
            icon.classList.remove('ph-spin');
            text.textContent = 'Sinkronisasi';
        }

        function showSyncToast(message) {
            var toast = document.getElementById("syncToast");
            if (message) {
                toast.querySelector('span').textContent = message;
            }
            
            // Tambahkan class 'show' untuk memunculkan
            toast.className = "toast-notification show";
            
            // Hilangkan setelah 3 detik (3000ms)
            setTimeout(function(){ 
                toast.className = toast.className.replace("show", ""); 
            }, 3000);
        }
        // --- 5. NOTIFIKASI DROPDOWN ---
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
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
            fetch('/notifications/' + notifId + '/read', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
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

        // Auto-fetch saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            fetchNotifications();
        });

        // --- 6. KONFIRMASI TUGAS ---
        var confirmTrackerId = null;
        var confirmModal = document.getElementById('confirmModal');

        function openConfirmModal(trackerId, pegawaiName) {
            confirmTrackerId = trackerId;
            document.getElementById('confirmPegawaiName').textContent = pegawaiName;
            confirmModal.style.display = 'flex';
        }

        function closeConfirmModal() {
            confirmModal.style.display = 'none';
            confirmTrackerId = null;
        }

        function submitConfirm() {
            if (!confirmTrackerId) return;

            var btn = document.getElementById('confirmYesBtn');
            btn.textContent = 'Memproses...';
            btn.disabled = true;

            fetch('/tracker/' + confirmTrackerId + '/confirm', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeConfirmModal();
                    // Reload halaman agar list terupdate
                    window.location.reload();
                }
            })
            .catch(err => {
                console.error('Gagal konfirmasi:', err);
                btn.textContent = 'Ya, Sudah Diproses';
                btn.disabled = false;
            });
        }

        // Tutup confirm modal jika klik overlay
        window.addEventListener('click', function(event) {
            if (event.target == confirmModal) {
                closeConfirmModal();
            }
        });
    </script>
    @include('partials.change_password_modal')
</body>
</html>