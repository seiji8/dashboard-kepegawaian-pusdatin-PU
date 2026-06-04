@extends('layouts.app')

@section('title', 'Log Aktivitas')



@section('content')
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
                    <button type="submit" formaction="{{ route('log-aktivitas.export-pdf') }}" formtarget="_blank" class="btn-export">
                        <i class="ph-fill ph-file-pdf" style="font-size: 20px;"></i>
                        Export PDF
                    </button>
                </div>
            </form>
        </div>

        <!-- DATA TABLE SECTION -->
        <div class="content-section">
            <h3 class="section-title">Data Log</h3>
            
            <div class="timeline-container">
                <div class="timeline-axis"></div>
                
                {{-- Skeleton: Timeline Items --}}
                <div class="timeline-items skeleton-layer">
                    @for ($i = 0; $i < 6; $i++)
                    <div class="timeline-item">
                        <div class="timeline-badge-skeleton skeleton-box"></div>
                        <div class="timeline-card skeleton-card">
                            <div class="skeleton-box" style="height:14px; width:150px; margin-bottom:10px;"></div>
                            <div class="skeleton-box" style="height:12px; width:80px; margin-bottom:12px; border-radius:20px;"></div>
                            <div class="skeleton-box" style="height:14px; width:95%;"></div>
                        </div>
                    </div>
                    @endfor
                </div>

                {{-- Real: Timeline Items --}}
                <div class="timeline-items real-content hidden">
                    @forelse($logs as $log)
                    <div class="timeline-item">
                        @php
                            $desc = $log->deskripsi ?? '';
                            $tipe = $log->tipe ?? '';
                            
                            $iconClass = 'ph-fill ph-gear';
                            $iconColorClass = 'color-lain';
                            $badgeText = 'Aksi Lain';
                            $badgeClass = 'badge-sistem';
                            
                            if ($tipe == 'API_SYNC') {
                                $iconClass = 'ph-fill ph-arrows-clockwise';
                                $iconColorClass = 'color-sync';
                                $badgeText = 'Sinkronisasi';
                                $badgeClass = 'badge-sync';
                            } elseif ($tipe == 'NOTIF_SENT') {
                                $iconClass = 'ph-fill ph-bell';
                                $iconColorClass = 'color-notif';
                                $badgeText = 'Notifikasi';
                                $badgeClass = 'badge-notif';
                            } elseif (str_contains($desc, 'Login')) {
                                $iconClass = 'ph-fill ph-key';
                                $iconColorClass = 'color-login';
                                $badgeText = 'Login';
                                $badgeClass = 'badge-login';
                            } elseif (str_contains($desc, 'Logout')) {
                                $iconClass = 'ph-fill ph-sign-out';
                                $iconColorClass = 'color-logout';
                                $badgeText = 'Logout';
                                $badgeClass = 'badge-logout';
                            } elseif (str_contains($desc, 'Menambahkan admin') || str_contains($desc, 'Menghapus admin') || str_contains($desc, 'Mengubah role')) {
                                $iconClass = 'ph-fill ph-user-gear';
                                $iconColorClass = 'color-admin';
                                $badgeText = 'Kelola Admin';
                                $badgeClass = 'badge-admin-manage';
                            } elseif (str_contains($desc, 'konfirmasi') || str_contains($desc, 'Konfirmasi')) {
                                $iconClass = 'ph-fill ph-check-circle';
                                $iconColorClass = 'color-confirm';
                                $badgeText = 'Konfirmasi';
                                $badgeClass = 'badge-confirm';
                            } elseif (str_contains($desc, 'Mencetak') || str_contains($desc, 'mencetak') || str_contains($desc, 'Cetak') || str_contains($desc, 'cetak')) {
                                $iconClass = 'ph-fill ph-printer';
                                $iconColorClass = 'color-print';
                                $badgeText = 'Cetak Surat';
                                $badgeClass = 'badge-print';
                            } elseif (str_contains($desc, 'Backup') || str_contains($desc, 'backup')) {
                                $iconClass = 'ph-fill ph-database';
                                $iconColorClass = 'color-backup';
                                $badgeText = 'Backup DB';
                                $badgeClass = 'badge-backup';
                            }
                        @endphp
                        
                        <div class="timeline-badge {{ $iconColorClass }}">
                            <i class="{{ $iconClass }}"></i>
                        </div>
                        
                        <div class="timeline-card">
                            <div class="timeline-card-header">
                                <div class="timeline-admin-info">
                                    <span class="timeline-admin-name">
                                        @if($log->admin)
                                            {{ $log->admin->nama_lengkap }}
                                        @else
                                            Sistem
                                        @endif
                                    </span>
                                    <span class="timeline-user-role">
                                        @if($log->admin)
                                            @if($log->admin->role === 'super_admin')
                                                Super Admin
                                            @else
                                                Admin Pegawai
                                            @endif
                                        @else
                                            System Automation
                                        @endif
                                    </span>
                                </div>
                                <div class="timeline-time-group">
                                    <span class="timeline-time-ago">{{ \Carbon\Carbon::parse($log->waktu)->diffForHumans() }}</span>
                                    <span class="timeline-time-exact">{{ \Carbon\Carbon::parse($log->waktu)->format('d M Y, H:i') }} WIB</span>
                                </div>
                            </div>
                            
                            <div class="timeline-card-body">
                                <span class="timeline-badge-pill {{ $badgeClass }}">{{ $badgeText }}</span>
                                <p class="timeline-desc">{{ $log->deskripsi }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state-container" style="border: none; background: transparent; box-shadow: none;">
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
                    @endforelse
                </div>
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
@endsection

@section('scripts')
    <script src="{{ asset('js/dashboard-ui.js') }}"></script>
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
@endsection

@section('tour')
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
                            title: 'Data Log Aktivitas 📄',
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
@endsection


