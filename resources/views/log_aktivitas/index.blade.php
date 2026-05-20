@extends('layouts.app')

@section('title', 'Log Aktivitas')

@section('page_css')
    <link rel="stylesheet" href="{{ asset('css/pages/log-aktivitas.css') }}">
@endsection

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
                    <tbody class="skeleton-layer">
                        @for ($i = 0; $i < 8; $i++)
                        <tr>
                            <td><div class="skeleton-box" style="height:14px; width:80%;"></div></td>
                            <td><div class="skeleton-box" style="height:28px; width:90px; border-radius:20px;"></div></td>
                            <td><div class="skeleton-box" style="height:14px; width:70%;"></div></td>
                            <td><div class="skeleton-box" style="height:14px; width:95%;"></div></td>
                        </tr>
                        @endfor
                    </tbody>

                    {{-- Real: Table Rows --}}
                    <tbody class="real-content hidden">
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
                                @php
                                    $desc = $log->deskripsi ?? '';
                                    $tipe = $log->tipe ?? '';
                                @endphp
                                @if($tipe == 'API_SYNC')
                                    <span style="background:#f0fdf4; color:#166534; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600; border:1px solid #bbf7d0; display:inline-block; white-space:nowrap;">
                                        <i class="ph-bold ph-arrows-clockwise" style="margin-right:3px;"></i>Sinkronisasi
                                    </span>
                                @elseif($tipe == 'NOTIF_SENT')
                                    <span style="background:#faf5ff; color:#7e22ce; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600; border:1px solid #e9d5ff; display:inline-block; white-space:nowrap;">
                                        <i class="ph-bold ph-bell" style="margin-right:3px;"></i>Notifikasi
                                    </span>
                                @elseif(str_contains($desc, 'Login'))
                                    <span style="background:#eff6ff; color:#1e40af; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600; border:1px solid #bfdbfe; display:inline-block; white-space:nowrap;">
                                        <i class="ph-bold ph-sign-in" style="margin-right:3px;"></i>Login
                                    </span>
                                @elseif(str_contains($desc, 'Menambahkan admin') || str_contains($desc, 'Menghapus admin') || str_contains($desc, 'Mengubah role'))
                                    <span style="background:#fefce8; color:#a16207; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600; border:1px solid #fef08a; display:inline-block; white-space:nowrap;">
                                        <i class="ph-bold ph-user-gear" style="margin-right:3px;"></i>Kelola Admin
                                    </span>
                                @elseif(str_contains($desc, 'konfirmasi') || str_contains($desc, 'Konfirmasi'))
                                    <span style="background:#f0fdf4; color:#166534; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600; border:1px solid #bbf7d0; display:inline-block; white-space:nowrap;">
                                        <i class="ph-bold ph-check-circle" style="margin-right:3px;"></i>Konfirmasi
                                    </span>
                                @elseif(str_contains($desc, 'Logout'))
                                    <span style="background:#fef2f2; color:#991b1b; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600; border:1px solid #fecaca; display:inline-block; white-space:nowrap;">
                                        <i class="ph-bold ph-sign-out" style="margin-right:3px;"></i>Logout
                                    </span>
                                @else
                                    <span style="background:#f1f5f9; color:#475569; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600; border:1px solid #cbd5e1; display:inline-block; white-space:nowrap;">
                                        <i class="ph-bold ph-gear" style="margin-right:3px;"></i>Aksi Lain
                                    </span>
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
                            title: 'Filter Pencarian ðŸ”',
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


