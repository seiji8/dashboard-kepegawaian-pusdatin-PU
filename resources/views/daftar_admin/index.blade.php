<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Admin - DashboardAlert</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/daftar_admin.css') }}">
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
            <a href="{{ route('log-aktivitas') }}" class="nav-item">
                <i class="ph-fill ph-clock-counter-clockwise nav-icon"></i>
                <span class="nav-text">Log Aktivitas</span>
            </a>
            <a href="{{ route('daftar-admin') }}" class="nav-item active">
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
            <h2 class="page-title">Daftar Admin</h2>
            <div class="header-actions">
                <form method="GET" action="{{ route('daftar-admin') }}" class="search-box">
                    <i class="ph-bold ph-magnifying-glass search-icon"></i>
                    <input type="text" name="search" placeholder="Cari Admin" class="search-input" value="{{ request('search') }}">
                </form>
                <a href="#" class="btn-tambah">Tambah</a>
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
                                    <i class="ph-bold ph-pencil-simple" style="color: #3b82f6; font-size: 18px;"></i>
                                </button>
                            </td>
                            <td class="text-center">
                                <button class="btn-icon btn-delete" onclick="openDeleteModal({{ $admin->id }}, '{{ $admin->nama_lengkap }}')">
                                    <i class="ph-fill ph-trash" style="color: #ef4444; font-size: 18px;"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #9ca3af;">
                                Tidak ada data admin
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

    <!-- SYNC TOAST NOTIFICATION -->
    <div id="syncToast" class="toast-notification">
        <i class="ph-bold ph-check-circle" style="font-size: 20px;"></i>
        <span>Sinkronisasi Data Berhasil!</span>
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
                    alert('Role berhasil diubah!');
                    location.reload();
                } else {
                    alert(data.message || 'Gagal mengubah role!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan!');
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
                    alert('Admin berhasil dihapus!');
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus admin!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan!');
            });
        }

        // Menutup modal jika klik di luar area box
        window.onclick = function(event) {
            var editModal = document.getElementById('modalEditAdmin');
            var deleteModal = document.getElementById('modalHapusAdmin');

            if (event.target == editModal) {
                editModal.style.display = "none";
            }
            if (event.target == deleteModal) {
                deleteModal.style.display = "none";
            }
        }

        // SINKRONISASI TOAST
        function showSyncToast() {
            var toast = document.getElementById("syncToast");
            toast.className = "toast-notification show";
            setTimeout(function(){ 
                toast.className = toast.className.replace("show", ""); 
            }, 3000);
        }
    </script>
</body>
</html>
