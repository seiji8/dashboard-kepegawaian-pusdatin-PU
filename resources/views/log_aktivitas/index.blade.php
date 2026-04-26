<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - DashboardAlert</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo_PU.png') }}">
    @include('partials.tour_styles')
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
                    <button type="submit" formaction="{{ route('log-aktivitas.export-pdf') }}" formtarget="_blank" class="btn-filter" style="background-color: #dc2626; color: white; margin-left: auto; display: flex; align-items: center; gap: 6px;">
                        <i class="ph-fill ph-file-pdf" style="font-size: 20px;"></i>
                        Export PDF
                    </button>
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
                            <td colspan="4" style="padding: 0; border: none;">
                                <div class="empty-state-container">
                                    <div class="empty-state-content">
                                        <div class="empty-state-icon">
                                            <i class="ph-duotone ph-magnifying-glass-minus"></i>
                                        </div>
                                        <h4 class="empty-state-title">Data Tidak Ditemukan</h4>
                                        <p class="empty-state-desc">Maaf, kami tidak dapat menemukan log aktivitas yang sesuai dengan filter pencarian Anda.<br>Silakan sesuaikan kriteria pencarian atau filter tanggal yang digunakan.</p>
                                        <a href="{{ route('log-aktivitas') }}" class="btn-reset-search" style="cursor: pointer;">
                                            <i class="ph-bold ph-arrow-counter-clockwise"></i>
                                            Reset Filter
                                        </a>
                                    </div>
                                </div>
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
    <script>
        // RESET FILTER
        function resetFilter() {
            document.getElementById('filterForm').reset();
            window.location.href = "{{ route('log-aktivitas') }}";
        }

        // Use addEventListener instead of window.onclick to avoid overriding app-common.js
        window.addEventListener('click', function(event) {
            // Add any view-specific click logic here if needed
        });
    </script>

        </main>
    </div>

    @include('partials.sync_loading')

    <script src="{{ asset('js/app-common.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    @include('partials.change_password_modal')
    <!-- Driver.js (Logika Panduan Tour Interaktif) -->
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
    <script>
        function mulaiTour() {
            const driver = window.driver.js.driver;
            const tour = driver({
                showProgress: true,
                animate: true,
                nextBtnText: 'Lanjut &rarr;',
                prevBtnText: '&larr; Kembali',
                doneBtnText: 'Selesai',
                steps: [
                    {
                        element: '.top-navbar',
                        popover: {
                            title: 'Area Profil & Notifikasi 👋',
                            description: 'Akses notifikasi dan pengaturan akun Anda di sini.',
                            side: "bottom",
                            align: 'end'
                        }
                    },
                    {
                        element: '.filter-section',
                        popover: {
                            title: 'Filter Pencarian 🔍',
                            description: 'Gunakan fitur ini untuk mencari log spesifik berdasarkan jenis pengguna, tanggal, atau aksi tertentu.',
                            side: "bottom",
                            align: 'center'
                        }
                    },
                    {
                        element: '.content-section',
                        popover: {
                            title: 'Data Log Aktivitas 📑',
                            description: 'Tabel ini menampilkan seluruh histori aktivitas yang terjadi di dalam sistem.',
                            side: "top",
                            align: 'center'
                        }
                    }
                ]
            });
            tour.drive();
        }
    </script>
</body>
</html>
