@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator|\App\Models\Pegawai[] $pegawais */
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pegawai - DashboardAlert</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- TomSelect CSS (Dropdown Pencarian) -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo_PU.png') }}">
    <style>
        .ts-control {
            padding: 12px 14px !important;
            border: 2px solid #e2e8f0 !important;
            border-radius: 8px !important;
            font-size: 14.5px !important;
            color: #1e293b !important;
            font-family: 'Poppins', sans-serif !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
            transition: all 0.2s ease !important;
            min-height: 48px;
        }
        .ts-control.focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }
        .ts-dropdown {
            font-family: 'Poppins', sans-serif !important;
            font-size: 14px !important;
            border-radius: 8px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1) !important;
            margin-top: 5px !important;
        }
        .ts-dropdown .option {
            padding: 10px 14px !important;
            border-bottom: 1px solid #f1f5f9;
        }
        .ts-dropdown .option:last-child {
            border-bottom: none;
        }
        .ts-dropdown .active {
            background-color: #eff6ff !important;
            color: #1e3a8a !important;
        }
        .ts-control input::placeholder {
            color: #94a3b8 !important;
        }
        .ts-wrapper.multi .ts-control > div {
            background: #dbeafe !important;
            color: #1e3a8a !important;
            border-radius: 6px !important;
            padding: 3px 8px !important;
            margin: 2px 4px 2px 0 !important;
        }
    </style>
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
            <h2 class="page-title">Data Pegawai</h2>
            <form id="searchForm" method="GET" action="{{ route('data-pegawai') }}" style="display:flex; gap:12px; align-items:center;">
                <!-- Filter Dropdown -->
                <select name="filter_tipe" id="filterTipe" class="select-filter" style="width: 170px;" onchange="document.getElementById('searchForm').submit();">
                    <option value="">Semua Jabatan</option>
                    <option value="struktural" {{ request('filter_tipe') == 'struktural' ? 'selected' : '' }}>Struktural</option>
                    <option value="fungsional" {{ request('filter_tipe') == 'fungsional' ? 'selected' : '' }}>Fungsional</option>
                    <option value="pelaksana" {{ request('filter_tipe') == 'pelaksana' ? 'selected' : '' }}>Pelaksana</option>
                </select>

                <!-- Search Box -->
                <div class="search-box" style="margin:0;">
                    <i class="ph-bold ph-magnifying-glass search-icon-inside"></i>
                    <input type="search" id="searchInput" name="search" placeholder="Cari pegawai" class="search-input" value="{{ request('search') }}">
                </div>
            </form>
        </div>

        <script>
            // Auto-submit saat kolom pencarian dikosongkan
            document.getElementById('searchInput').addEventListener('input', function(e) {
                if (this.value === '') {
                    document.getElementById('searchForm').submit();
                }
            });
        </script>

        <div class="content-section">
            <h3 class="section-title">Semua Data Pegawai</h3>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Nama</th>
                            <th style="width: 25%;">Jabatan</th>
                            <th style="width: 20%;">NIP</th>
                            <th style="width: 10%; text-align: center;">Lihat Detail</th>
                            <th style="width: 10%; text-align: center;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pegawais as $pegawai)
                        <tr>
                            <td>{{ $pegawai->nama }}</td>
                            <td>{{ $pegawai->jabatan_saat_ini ?? '-' }}</td>
                            <td>{{ $pegawai->nip }}</td>
                            <td style="text-align: center;">
                                <button class="btn-view" onclick="openDetailModal('{{ $pegawai->nip }}')" style="margin: 0 auto;">
                                    <i class="ph-bold ph-eye" style="font-size: 20px;"></i>
                                </button>
                            </td>
                            <td style="text-align: center;">
                                <button class="btn-delete" onclick="openDeleteModal('{{ $pegawai->nip }}', '{{ $pegawai->nama }}')" style="margin: 0 auto;">
                                    <i class="ph-fill ph-trash" style="font-size: 20px;"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="padding: 0; border: none;">
                                <div class="empty-state-container">
                                    <div class="empty-state-content">
                                        <div class="empty-state-icon">
                                            <i class="ph-duotone ph-magnifying-glass-minus"></i>
                                        </div>
                                        <h4 class="empty-state-title">Data Tidak Ditemukan</h4>
                                        <p class="empty-state-desc">Maaf, kami tidak dapat menemukan data pegawai dengan kata kunci <br><strong>"{{ request('search') }}"</strong>.<br>Silakan periksa kembali ejaan Anda atau gunakan kata kunci yang berbeda.</p>
                                        <a href="{{ route('data-pegawai') }}" class="btn-reset-search">
                                            <i class="ph-bold ph-arrow-counter-clockwise"></i>
                                            Reset Pencarian
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($pegawais->hasPages())
            <div class="pagination">
                @if($pegawais->onFirstPage())
                    <span class="pagination-text" style="opacity: 0.5;">Prev</span>
                @else
                    <a href="{{ $pegawais->previousPageUrl() }}" class="pagination-text">Prev</a>
                @endif

                {{-- Numbers (Max 5 items sliding) --}}
                @php
                    $start = max(1, $pegawais->currentPage() - 2);
                    $end = min($start + 4, $pegawais->lastPage());
                    $start = max(1, $end - 4);
                @endphp

                @foreach($pegawais->getUrlRange($start, $end) as $page => $url)
                    @if ($page == $pegawais->currentPage())
                        <span class="pagination-btn active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                    @endif
                @endforeach

                @if($pegawais->hasMorePages())
                    <a href="{{ $pegawais->nextPageUrl() }}" class="pagination-text">Next</a>
                @else
                    <span class="pagination-text" style="opacity: 0.5;">Next</span>
                @endif
            </div>
            @endif
        </div>
        </div><!-- end content-area -->
    </main>

    <!-- MODAL HAPUS PEGAWAI -->
    <div id="modalHapusPegawai" class="modal-overlay">
        <div class="modal-box modal-delete-size">
            <div class="delete-content">
                <h3 class="delete-title">
                    Hapus Pegawai Ini?
                    <i class="ph-fill ph-warning" style="color: #fbbf24; font-size: 24px;"></i>
                </h3>
                <div class="delete-actions">
                    <button class="btn-pill confirm-delete" onclick="confirmDelete()">Yakin</button> 
                    <button class="btn-pill" onclick="closeDeleteModal()">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL PEGAWAI MODERN -->
    <div id="modalDetailPegawai" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center;">
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

            <div id="detailContent" class="modal-modern-body" style="display: none;">
                
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
                        <div class="info-item">
                            <label>NIP / ID</label>
                            <span id="detNIP">-</span>
                        </div>
                        <div class="info-item">
                            <label>EMAIL</label>
                            <span id="detEmail">-</span>
                        </div>
                        <div class="info-item">
                            <label>NO. HP</label>
                            <span id="detHP">-</span>
                        </div>
                        <div class="info-item">
                            <label>TIPE JABATAN</label>
                            <span id="detTipeJabatan">-</span>
                        </div>
                        <div class="info-item">
                            <label>PANGKAT / GOLONGAN</label>
                            <span id="detPangkat">-</span>
                        </div>
                        <div class="info-item">
                            <label>JENJANG</label>
                            <span id="detJenjang">-</span>
                        </div>
                        <div class="info-item">
                            <label>TMT CPNS</label>
                            <span id="detTmt">-</span>
                        </div>
                        <div class="info-item">
                            <label>ANGKA KREDIT</label>
                            <span id="detKredit">-</span>
                        </div>
                    </div>

                    <div class="doc-section borderless">
                        <div class="doc-section-title">
                            <i class="ph-fill ph-file-text" style="color: #4b5563;"></i>
                            Dokumen Wajib
                        </div>
                        
                        <!-- Container for dynamic doc status -->
                        <div id="docStatusContainer">
                            <!-- Injected by JS -->
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- MODAL REMINDER -->
    <div id="modalReminder" class="modal-overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2400; justify-content: center; align-items: center;">
        <div style="background: white; width: 600px; max-width: 95vw; padding: 0; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); position: relative; overflow: hidden; display: flex; flex-direction: column;">

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
                <style>
                    #modalReminder .ts-wrapper { margin-bottom: 20px; }
                    #modalReminder .ts-control { border-radius: 8px !important; border-color: #cbd5e1 !important; padding: 10px 15px !important; font-size: 14px !important; }
                    #modalReminder .ts-control:focus-within { border-color: #3b82f6 !important; box-shadow: 0 0 0 3px rgba(59,130,246,0.1) !important; }
                    #modalReminder .ts-dropdown { border-radius: 8px !important; border-color: #cbd5e1 !important; box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important; }
                </style>

                <label style="display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px; letter-spacing: 0.5px;">PILIH TEMPLATE PESAN</label>
                <select id="reminderTemplate" style="width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 20px; color: #1e293b; font-size: 14px; outline: none; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
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
                <textarea id="reminderMessage" disabled style="width: 100%; height: 120px; padding: 15px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 10px; resize: none; font-size: 14px; color: #1e293b; outline: none; transition: all 0.2s; background: #f8fafc; font-family: 'Poppins', sans-serif; box-sizing: border-box;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'; this.style.background='#ffffff'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'; if(this.disabled) this.style.background='#f8fafc'"></textarea>
            </div>

            <!-- Footer -->
            <div style="padding: 20px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 12px;">
                <button onclick="closeReminderModal()" style="padding: 10px 24px; background: white; color: #64748b; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; font-family: 'Poppins', sans-serif;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#475569'" onmouseout="this.style.background='white'; this.style.color='#64748b'">Batal</button>
                <button onclick="sendReminder()" id="btnSendManual" style="padding: 10px 24px; background: #f59e0b; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(245,158,11,0.2); font-family: 'Poppins', sans-serif;" onmouseover="this.style.background='#d97706'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#f59e0b'; this.style.transform='translateY(0)'">
                    <i class="ph-bold ph-paper-plane-right"></i> Kirim
                </button>
            </div>
        </div>
    </div>
        </main>
    </div>
    @include('partials.sync_loading')

    <script src="{{ asset('js/app-common.js') }}"></script>
    <script src="{{ asset('js/data-pegawai.js') }}"></script>
    <style>
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
    @include('partials.change_password_modal')
    <!-- Driver.js (Logika Panduan Tour Interaktif) -->
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
    <!-- TomSelect JS (Dropdown Pencarian) -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        // Init Tom Select setelah semua resource (CDN) pasti loaded
        window.addEventListener('load', function() {
            const el = document.getElementById('reminderTemplate');
            if (el && typeof TomSelect !== 'undefined') {
                reminderTomSelectDP = new TomSelect(el, {
                    allowEmptyOption: true,
                    maxOptions: null,
                    placeholder: 'Pilih Template Pengingat'
                });
            }
        });

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
                            description: 'Di sini Anda dapat mengecek notifikasi, mengubah profil, dan mengganti kata sandi.',
                            side: "bottom",
                            align: 'end'
                        }
                    },
                    {
                        element: '.content-header',
                        popover: {
                            title: 'Pencarian & Filter 🔍',
                            description: 'Gunakan fitur ini untuk mencari pegawai berdasarkan nama atau memfilter berdasarkan tipe jabatan.',
                            side: "bottom",
                            align: 'center'
                        }
                    },
                    {
                        element: '.data-table',
                        popover: {
                            title: 'Data Pegawai 📑',
                            description: 'Tabel ini menampilkan daftar seluruh pegawai. Anda dapat melihat detail atau menghapus data dari sini.',
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
