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
        <div class="content-header">
            <h2 class="page-title">Log Aktivitas</h2>
        </div>

        <!-- FILTER SECTION -->
        <div class="filter-section">
            <form id="filterForm" method="GET" action="{{ route('log-aktivitas') }}">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Tipe Log</label>
                        <select name="tipe" class="filter-input" style="color: {{ request('tipe') ? '#374151' : '#A9ACB1' }};">
                            <option value="">Semua</option>
                            <option value="ADMIN_ACTION" {{ request('tipe') == 'ADMIN_ACTION' ? 'selected' : '' }}>Admin Action</option>
                            <option value="NOTIF_SENT" {{ request('tipe') == 'NOTIF_SENT' ? 'selected' : '' }}>Notifikasi Terkirim</option>
                            <option value="API_SYNC" {{ request('tipe') == 'API_SYNC' ? 'selected' : '' }}>Sinkronisasi API</option>
                            <option value="SYSTEM_LOG" {{ request('tipe') == 'SYSTEM_LOG' ? 'selected' : '' }}>Log Sistem</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Cari Deskripsi</label>
                        <div class="input-with-icon-left">
                            <i class="ph-bold ph-magnifying-glass icon-input-left"></i>
                            <input type="text" name="search" placeholder="Cari di deskripsi..." class="filter-input pl-icon" value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="filter-group">
                        <label>Dari Tanggal</label>
                        <div class="date-wrapper">
                            <input type="date" name="dari_tanggal" class="filter-input date-input" value="{{ request('dari_tanggal') }}">
                        </div>
                    </div>

                    <div class=" filter-group">
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
                            <th style="width: 18%;">Waktu</th>
                            <th style="width: 15%;">Tipe</th>
                            <th style="width: 15%;">Admin</th>
                            <th style="width: 15%;">Target Pegawai</th>
                            <th style="width: 27%;">Deskripsi</th>
                            <th style="width: 10%;">Hapus</th>
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
                                <span class="badge 
                                    @if($log->tipe == 'ADMIN_ACTION') badge-admin-super
                                    @elseif($log->tipe == 'NOTIF_SENT') badge-pegawai
                                    @elseif($log->tipe == 'API_SYNC') badge-sistem
                                    @else badge-sistem
                                    @endif
                                ">
                                    @if($log->tipe == 'ADMIN_ACTION') Admin Action
                                    @elseif($log->tipe == 'NOTIF_SENT') Notifikasi
                                    @elseif($log->tipe == 'API_SYNC') API Sync
                                    @else System
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($log->admin)
                                    <span style="font-size: 13px;">{{ $log->admin->nama_lengkap }}</span>
                                @else
                                    <span style="font-size: 12px; color: #9ca3af;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($log->pegawai)
                                    <span style="font-size: 13px;">{{ $log->pegawai->nama }}</span>
                                @else
                                    <span style="font-size: 12px; color: #9ca3af;">-</span>
                                @endif
                            </td>
                            <td>{{ $log->deskripsi }}</td>
                            <td>
                                <button class="btn-delete" onclick="deleteLog({{ $log->id }})">
                                    <i class="ph-fill ph-trash" style="color: #ef4444; font-size: 18px;"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #9ca3af;">
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

                @foreach($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
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

        // DELETE LOG
        function deleteLog(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus log ini?')) {
                return;
            }

            fetch(`/log-aktivitas/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Log berhasil dihapus!');
                    location.reload();
                } else {
                    alert('Gagal menghapus log!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan!');
            });
        }
    </script>
</body>
</html>
