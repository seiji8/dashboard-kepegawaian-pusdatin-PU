<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Admin - DashboardAlert</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo_PU.png') }}">
    
    <!-- TomSelect CSS (Dropdown Pencarian) -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.css" rel="stylesheet">
    <style>
        /* Penyesuaian Style TomSelect agar membaur dengan desain mewah */
        .ts-control {
            padding: 12px 14px !important;
            border: 2px solid #e2e8f0 !important;
            border-radius: 8px !important;
            font-size: 14.5px !important;
            color: #1e293b !important;
            font-family: 'Poppins', sans-serif !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
            transition: all 0.2s ease !important;
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
    </style>
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
            <h2 class="page-title">Daftar Admin</h2>
            <div class="header-actions">
                <form method="GET" action="{{ route('daftar-admin') }}" class="search-box">
                    <i class="ph-bold ph-magnifying-glass search-icon"></i>
                    <input type="text" name="search" placeholder="Cari Admin" class="search-input" value="{{ request('search') }}">
                </form>
                <a href="#" class="btn-tambah" onclick="openAddModal()">Tambah</a>
            </div>
        </div>

        <div class="content-section">
            <h3 class="section-title">Data Admin</h3>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Nama</th>
                            <th style="width: 25%;">Peran</th>
                            <th style="width: 25%;">NIP</th>
                            <th style="width: 10%; text-align: center;">Edit</th>
                            <th style="width: 10%; text-align: center;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                        <tr data-id="{{ $admin->id }}" data-nama="{{ $admin->nama_lengkap }}" data-nip="{{ $admin->username }}" data-role="{{ $admin->role }}" data-is-super="{{ $admin->isSuperAdmin() ? '1' : '0' }}">
                            <td>{{ $admin->nama_lengkap }}</td>
                            <td>
                                <span class="badge {{ $admin->isSuperAdmin() ? 'badge-super' : 'badge-pegawai' }}">
                                    {{ $admin->isSuperAdmin() ? 'Admin Super' : 'Admin Kepegawaian' }}
                                </span>
                            </td>
                            <td>{{ $admin->username }}</td>
                            <td class="text-center">
                                <button class="btn-icon btn-edit" onclick="openEditModal(this)">
                                    <i class="ph-bold ph-pencil-simple" style="font-size: 18px;"></i>
                                </button>
                            </td>
                            <td class="text-center">
                                <button class="btn-icon btn-delete" onclick="openDeleteModal({{ $admin->id }}, '{{ $admin->nama_lengkap }}')">
                                    <i class="ph-fill ph-trash" style="font-size: 18px;"></i>
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
                                        <p class="empty-state-desc">Maaf, kami tidak dapat menemukan data admin dengan kata kunci <br><strong>"{{ request('search') }}"</strong>.<br>Silakan periksa kembali ejaan Anda atau gunakan kata kunci yang berbeda.</p>
                                        <a href="{{ route('daftar-admin') }}" class="btn-reset-search">
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

            <!-- PAGINATION -->
            @if($admins->hasPages())
            <div class="pagination">
                @if($admins->onFirstPage())
                    <span class="pagination-text" style="opacity: 0.5;">Prev</span>
                @else
                    <a href="{{ $admins->previousPageUrl() }}" class="pagination-text">Prev</a>
                @endif

                @foreach($admins->getUrlRange(1, $admins->lastPage()) as $page => $url)
                    <a href="{{ $url }}" class="pagination-btn {{ $page == $admins->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                @endforeach

                @if($admins->hasMorePages())
                    <a href="{{ $admins->nextPageUrl() }}" class="pagination-text">Next</a>
                @else
                    <span class="pagination-text" style="opacity: 0.5;">Next</span>
                @endif
            </div>
            @endif
        </div>
        </div><!-- end content-area -->
    </main>

    <!-- MODAL EDIT ADMIN -->
    <div id="modalEditAdmin" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="ph-bold ph-pencil-simple" style="font-size: 24px; color: #1e3a8a;"></i>
                <h2 style="font-size: 20px; color: #1e293b; margin: 0;">Edit Peran Admin</h2>
            </div>
            
            <div class="modal-body">
                <!-- Info Identitas Modern -->
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; border-radius: 8px; margin-bottom: 24px;">
                    <p style="font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; margin-top: 0;">Identitas Pegawai</p>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; align-items: flex-start;">
                            <span style="font-size: 14px; color: #475569; width: 60px; font-weight: 500;">Nama</span>
                            <span style="font-size: 14px; color: #1e293b; font-weight: 600;">: <span id="modalNama">-</span></span>
                        </div>
                        <div style="display: flex; align-items: flex-start;">
                            <span style="font-size: 14px; color: #475569; width: 60px; font-weight: 500;">NIP</span>
                            <span style="font-size: 14px; color: #1e293b; font-weight: 600;">: <span id="modalNip">-</span></span>
                        </div>
                    </div>
                </div>

                <div class="input-group" style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color: #334155; font-size: 14px;">Edit Peran (Role):</label>
                    <div style="position: relative;">
                        <!-- Menggunakan TomSelect agar seragam, menghilangkan class form-select agar tidak tabrakan border -->
                        <select id="modalSelectPeran">
                            <option value="0">Admin Kepegawaian</option>
                            <option value="1">Admin Super</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 30px; border-top: none; padding-top: 0;">
                <button onclick="closeEditModal()" style="padding: 10px 20px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif; transition: all 0.2s; font-size: 14px;">Batal</button>
                <button onclick="saveRole()" style="display: flex; align-items: center; gap: 8px; padding: 10px 24px; background: #1e3a8a; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif; transition: all 0.2s; box-shadow: 0 4px 6px rgba(30, 58, 138, 0.2); font-size: 14px;">
                    <i class="ph-bold ph-floppy-disk"></i> Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH ADMIN -->
    <div id="modalTambahAdmin" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="ph-bold ph-user-plus" style="font-size: 24px; color: #1e3a8a;"></i>
                <h2 style="font-size: 20px; color: #1e293b; margin: 0;">Tambah Admin Baru</h2>
            </div>
            
            <div class="modal-body">
                <!-- Info Peringatan Modern -->
                <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 14px 16px; border-radius: 4px 8px 8px 4px; margin-bottom: 24px;">
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <i class="ph-fill ph-info" style="color: #3b82f6; font-size: 20px; margin-top: 2px;"></i>
                        <p style="font-size: 13.5px; color: #1e293b; line-height: 1.6; margin: 0;">
                            Pilih pegawai yang akan diberikan akses ke <strong>Dashboard</strong>.<br>
                            <span style="display: block; margin-top: 6px;">
                                &bull; <strong>Email Login:</strong> Email Pegawai<br>
                                &bull; <strong>Kata Sandi Default:</strong> <span style="font-family: monospace; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; color: #0f172a; font-weight: 600; white-space: nowrap;">NIP Pegawai</span>
                            </span>
                        </p>
                    </div>
                </div>
                
                <div class="input-group" style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color: #334155; font-size: 14px;">Pilih Calon Admin:</label>
                    <div style="position: relative;">
                        <!-- Dihapus class="form-select" agar tidak tercopy oleh TomSelect yang menyebabkan border dobel -->
                        <select id="selectPegawai">
                            <option value="">-- Cari Pegawai --</option>
                            @foreach($candidates as $candidate)
                                <option value="{{ $candidate->nip }}">
                                    {{ $candidate->nama }} ({{ $candidate->nip }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 30px; border-top: none; padding-top: 0;">
                <button onclick="closeAddModal()" style="padding: 10px 20px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif; transition: all 0.2s; font-size: 14px;">Batal</button>
                <button onclick="saveNewAdmin()" style="display: flex; align-items: center; gap: 8px; padding: 10px 24px; background: #1e3a8a; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif; transition: all 0.2s; box-shadow: 0 4px 6px rgba(30, 58, 138, 0.2); font-size: 14px;">
                    <i class="ph-bold ph-floppy-disk"></i> Simpan Akses
                </button>
            </div>
        </div>
    </div>
    
    <!-- MODAL HAPUS ADMIN -->
    <div id="modalHapusAdmin" class="modal-overlay">
        <div class="modal-box modal-delete-size">
            <div class="delete-content">
                <h3 class="delete-title">
                    Yakin Hapus Admin Ini?
                    <i class="ph-fill ph-warning" style="color: #fbbf24; font-size: 24px;"></i>
                </h3>
                <div class="delete-actions">
                    <button class="btn-pill confirm-delete" onclick="confirmDelete()">Yakin</button>
                    <button class="btn-pill" onclick=" closeDeleteModal()">Batal</button>
                </div>
            </div>
        </div>
    </div>

        </main>
    </div>
    <script>
        let currentEditAdminId = null;
        let currentDeleteAdminId = null;

        // === LOGIKA EDIT ADMIN ===
        function openEditModal(buttonElement) {
            var row = buttonElement.closest('tr');
            
            currentEditAdminId = row.getAttribute('data-id');
            var nama = row.getAttribute('data-nama');
            var nip = row.getAttribute('data-nip');
            var isSuper = row.getAttribute('data-is-super');

            document.getElementById('modalNama').innerText = nama;
            document.getElementById('modalNip').innerText = nip;
            document.getElementById('modalSelectPeran').value = isSuper;

            document.getElementById('modalEditAdmin').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('modalEditAdmin').style.display = 'none';
            currentEditAdminId = null;
        }

        function saveRole() {
            if (!currentEditAdminId) return;

            const isSuperAdmin = document.getElementById('modalSelectPeran').value;

            fetch(`/daftar-admin/${currentEditAdminId}/update-role`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    is_super_admin: parseInt(isSuperAdmin)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('modalEditAdmin').style.display = 'none';
                    showCustomToast('Role berhasil diubah!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showCustomToast(data.message || 'Gagal mengubah role!', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showCustomToast('Terjadi kesalahan!', 'error');
            });
        }

        // === LOGIKA TAMBAH ADMIN ===
        function openAddModal() {
            document.getElementById('modalTambahAdmin').style.display = 'flex';
        }

        function closeAddModal() {
            document.getElementById('modalTambahAdmin').style.display = 'none';
        }

        function saveNewAdmin() {
            const nipPegawai = document.getElementById('selectPegawai').value;

            if (!nipPegawai) {
                showCustomToast('Silakan pilih pegawai terlebih dahulu!', 'error');
                return;
            }

            fetch("{{ route('daftar-admin.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    nip_pegawai: nipPegawai
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAddModal();
                    showCustomToast(data.message, 'success');
                    setTimeout(() => location.reload(), 2000); // Reload setelah toast muncul
                } else {
                    showCustomToast(data.message || 'Gagal menambahkan admin!', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showCustomToast('Terjadi kesalahan! Pastikan Anda Super Admin.', 'error');
            });
        }

        // === LOGIKA HAPUS ADMIN ===
        function openDeleteModal(adminId, adminName) {
            currentDeleteAdminId = adminId;
            document.getElementById('modalHapusAdmin').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('modalHapusAdmin').style.display = 'none';
            currentDeleteAdminId = null;
        }

        function confirmDelete() {
            if (!currentDeleteAdminId) return;

            fetch(`/daftar-admin/${currentDeleteAdminId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeDeleteModal();
                    showCustomToast('Admin berhasil dihapus!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showCustomToast(data.message || 'Gagal menghapus admin!', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showCustomToast('Terjadi kesalahan server!', 'error');
            });
        }

        // Use addEventListener instead of window.onclick to avoid overriding app-common.js
        window.addEventListener('click', function(event) {
            var editModal = document.getElementById('modalEditAdmin');
            var deleteModal = document.getElementById('modalHapusAdmin');
            var addModal = document.getElementById('modalTambahAdmin');

            if (event.target == editModal) editModal.style.display = "none";
            if (event.target == deleteModal) deleteModal.style.display = "none";
            if (event.target == addModal) addModal.style.display = "none";
        });


    </script>

    @include('partials.sync_loading')

    <!-- TomSelect JS (Logika Pencarian Inti) -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Inisialisasi TomSelect pada kolom dropdown Tambah Admin
            new TomSelect("#selectPegawai", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "Ketik Nama atau NIP untuk mencari..."
            });

            // Inisialisasi TomSelect pada dropdown Edit Peran (Tanpa kotak pencarian)
            new TomSelect("#modalSelectPeran", {
                create: false,
                controlInput: null, // Mematikan keyboard/input ketik karena isiannya hanya 2 opsi statis
            });
        });
    </script>

    <script src="{{ asset('js/app-common.js') }}"></script>
    @include('partials.change_password_modal')
</body>
</html>
