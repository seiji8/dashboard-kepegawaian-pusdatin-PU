<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DashboardAlert</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons for fallback since assets are missing -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo"><span class="logo-highlight">Dashboard</span>Alert</h1>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="bi bi-people-fill nav-icon"></i>
                    <span class="nav-text">Data Pegawai</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="bi bi-chat-dots-fill nav-icon"></i>
                    <span class="nav-text">Konfigurasi Pesan</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="bi bi-clock-history nav-icon"></i>
                    <span class="nav-text">Log Aktivitas</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="bi bi-shield-lock-fill nav-icon"></i>
                    <span class="nav-text">Daftar Admin</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <form action="{{ url('/sync-now') }}" method="POST" id="syncForm" style="display:none;">@csrf</form>
                <button class="sync-btn" onclick="showSyncToast()">
                    <i class="bi bi-arrow-repeat sync-img"></i>
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
                        <button class="btn-icon-header">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1e3a8a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span class="badge">{{ $tenggatMendesak }}</span>
                        </button>
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
                            <svg class="chevron-down" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </button>

                        <div id="profileDropdown" class="dropdown-menu">
                            <a href="#" class="dropdown-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                Ganti Kata Sandi
                            </a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-red" style="width:100%; border:none; background:none; cursor:pointer;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
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
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
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
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
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
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
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
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
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
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
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
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
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
                                                        @if($kgb->status_saat_ini == 'Mendekati')
                                                            <span class="status-badge status-warning">Mendekati</span>
                                                        @elseif($kgb->status_saat_ini == 'Usulan')
                                                            <span class="status-badge status-ok">Usulan</span>
                                                        @else
                                                            <span class="status-badge status-missing">{{ $kgb->status_saat_ini }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span style="color: #dc2626; font-weight: 600;">
                                                            {{ $kgb->dokumen_total - $kgb->dokumen_terupload }} Belum
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn-action-view" onclick="openDetailModal('{{ $kgb->pegawai->nip }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
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
            <select style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 20px; color: #9ca3af;">
                <option>Pilih</option>
                <option>Template 1: Dokumen Kurang</option>
                <option>Template 2: Segera Lengkapi</option>
            </select>

            <div style="display: flex; align-items: center; margin-bottom: 20px;">
                <input type="checkbox" id="customCheck" style="margin-right: 10px; width: 18px; height: 18px;">
                <label for="customCheck">Apakah anda ingin menambahkan pesan custom?</label>
            </div>

            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Isi Pesan</label>
            <textarea style="width: 100%; height: 120px; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 30px; resize: none;"></textarea>

            <div style="display: flex; justify-content: flex-end; gap: 15px;">
                <button onclick="closeReminderModal()" style="padding: 10px 30px; background: #fca5a5; color: #991b1b; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Batal</button>
                <button onclick="closeReminderModal()" style="padding: 10px 30px; background: #cbd5e1; color: #1e3a8a; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Kirim</button>
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

        function openDetailModal(nip) {
            detailModal.style.display = 'flex';
            // Simple mockup logic, in real app, fetch data by NIP
            const contentBody = document.getElementById('modalContentBody');
            contentBody.innerHTML = '<div style="font-weight: 600;">NIP :</div><div>' + nip + '</div>' +
                                  '<div style="font-weight: 600;">Status :</div><div>Loading data...</div>';
                                  
            // In a real implementation, you would AJAX fetch details here
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

            // Logika Menutup Modal jika klik di area gelap (overlay)
            if (event.target == detailModal) {
                closeDetailModal();
            }
            if (event.target == reminderModal) {
                closeReminderModal();
            }
        }

        // --- 4. SINKRONISASI TOAST ---
        function showSyncToast() {
            var toast = document.getElementById("syncToast");
            
            // Tambahkan class 'show' untuk memunculkan
            toast.className = "toast-notification show";
            
            // Hilangkan setelah 3 detik (3000ms)
            setTimeout(function(){ 
                toast.className = toast.className.replace("show", ""); 
            }, 3000);
        }
    </script>
</body>
</html>