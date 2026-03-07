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
            <div class="modal-header">
                <h2>Edit Admin</h2>
            </div>
            
            <div class="modal-body">
                <div class="info-group">
                    <h3>Identitas Pegawai</h3>
                    <div class="info-row">
                        <span class="info-label">Nama :</span>
                        <span id="modalNama" class="info-value">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">NIP :</span>
                        <span id="modalNip" class="info-value">-</span>
                    </div>
                </div>

                <div class="input-group">
                    <h3>Edit Peran</h3>
                    <div class="select-wrapper">
                        <select id="modalSelectPeran" class="form-select">
                            <option value="0">Admin Kepegawaian</option>
                            <option value="1">Admin Super</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-modal-cancel" onclick="closeEditModal()">Batal</button>
                <button class="btn-modal-save" onclick="saveRole()">Simpan</button>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH ADMIN -->
    <div id="modalTambahAdmin" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Tambah Admin Baru</h2>
            </div>
            
            <div class="modal-body">
                <div class="info-group">
                    <p style="font-size: 14px; color: #6b7280; margin-bottom: 15px;">
                        Pilih pegawai untuk dijadikan admin. <br>
                        <strong>Username:</strong> NIP Pegawai <br>
                        <strong>Password Default:</strong> NIP Pegawai
                    </p>
                    
                    <div class="select-wrapper">
                        <label for="selectPegawai" style="display:block; margin-bottom:5px; font-weight:500;">Pilih Pegawai:</label>
                        <select id="selectPegawai" class="form-select" style="width: 100%;">
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

            <div class="modal-footer">
                <button class="btn-modal-cancel" onclick="closeAddModal()">Batal</button>
                <button class="btn-modal-save" onclick="saveNewAdmin()">Simpan</button>
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

    <script src="{{ asset('js/app-common.js') }}"></script>
    @include('partials.change_password_modal')
</body>
</html>
