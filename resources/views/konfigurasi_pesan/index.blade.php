<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfigurasi Pesan - DashboardAlert</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
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
            <a href="{{ route('data-pegawai') }}" class="nav-item">
                <i class="ph-fill ph-users nav-icon"></i>
                <span class="nav-text">Data Pegawai</span>
            </a>
            <a href="{{ route('konfigurasi-pesan') }}" class="nav-item active">
                <i class="ph-fill ph-chat-dots nav-icon"></i>
                <span class="nav-text">Konfigurasi Pesan</span>
            </a>
            <a href="{{ route('log-aktivitas') }}" class="nav-item">
                <i class="ph-fill ph-clock-counter-clockwise nav-icon"></i>
                <span class="nav-text">Log Aktivitas</span>
            </a>
            <a href="{{ route('daftar-admin') }}" class="nav-item">
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
            <h2 class="page-title">Konfigurasi Pesan</h2>
            <div class="header-actions">
                <select id="filterJenis" class="select-filter" onchange="filterCards()">
                    <option value="all">Semua Jenis</option>
                    <option value="schedule">Penjadwalan</option>
                    <option value="template">Template</option>
                </select>
                <button class="btn-tambah" onclick="openModal()">+ Tambah</button>
            </div>
        </div>

        <div class="config-container">
            
            <div class="config-grid table-header">
                <div>Nama Notifikasi</div>
                <div>Isi Pesan</div>
                <div class="header-center">Jadwal</div>
                <div class="header-center">Aksi</div>
            </div>

            <script>
                function filterCards() {
                    const filter = document.getElementById('filterJenis').value;
                    const cards = document.querySelectorAll('.message-card');

                    cards.forEach(card => {
                        const interval = parseInt(card.getAttribute('data-interval'));
                        let shouldShow = false;

                        if (filter === 'all') {
                            shouldShow = true;
                        } else if (filter === 'schedule') {
                            shouldShow = (interval > 0);
                        } else if (filter === 'template') {
                            shouldShow = (interval == 0);
                        }

                        card.style.display = shouldShow ? 'grid' : 'none';
                    });
                }
            </script>

            @forelse($rules as $rule)
            <div class="message-card" data-id="{{ $rule->id }}" data-kategori="{{ $rule->kategori }}" data-interval="{{ $rule->interval_hari }}">
                <div class="notif-name">{{ $rule->kategori }}</div>
                <div class="message-content">
                    {!! nl2br(e($rule->template_pesan)) !!}
                </div>
                <div class="schedule-col">
                    @php
                        $interval = $rule->interval_hari;
                        $format = '';
                        if ($interval <= 0) {
                            $format = 'Manual';
                        } elseif ($interval >= 365 && $interval % 365 == 0) {
                            $format = ($interval / 365) . ' Tahun';
                        } elseif ($interval >= 30 && $interval % 30 == 0) {
                            $format = ($interval / 30) . ' Bulan';
                        } elseif ($interval >= 7 && $interval % 7 == 0) {
                            $format = ($interval / 7) . ' Minggu';
                        } else {
                            $format = $interval . ' Hari';
                        }
                    @endphp

                    @if($rule->kategori == 'KGB Penjadwalan')
                        <span class="badge-schedule" style="background-color: #fef3c7; color: #d97706;">H-{{ $rule->interval_hari }} Hari</span>
                    @elseif($interval <= 0)
                        <span class="badge-schedule" style="background-color: #f3f4f6; color: #6b7280;">Manual</span>
                    @else
                        <span class="badge-schedule" style="background-color: #f3e8ff; color: #7e22ce;">{{ $format }}</span>
                    @endif
                </div>
                <div class="action-col">
                    <button class="btn-icon btn-edit" onclick="openEditModal(this)">
                        <i class="ph-bold ph-pencil-simple"></i>
                    </button>
                    <button class="btn-icon btn-delete" onclick="openDeleteModal('{{ $rule->id }}')">
                        <i class="ph-bold ph-trash"></i>
                    </button>
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 40px; color: #9ca3af; grid-column: 1 / -1;">
                Belum ada konfigurasi pesan.
            </div>
            @endforelse

            <!-- PAGINATION -->
            <div style="grid-column: 1 / -1;">
                @if($rules->hasPages())
                <div class="pagination">
                    @if($rules->onFirstPage())
                        <span class="pagination-text" style="opacity: 0.5;">Prev</span>
                    @else
                        <a href="{{ $rules->previousPageUrl() }}" class="pagination-text">Prev</a>
                    @endif

                    @php
                        $start = max(1, $rules->currentPage() - 2);
                        $end = min($start + 4, $rules->lastPage());
                        $start = max(1, $end - 4);
                    @endphp

                    @foreach($rules->getUrlRange($start, $end) as $page => $url)
                        <a href="{{ $url }}" class="pagination-btn {{ $page == $rules->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                    @endforeach

                    @if($rules->hasMorePages())
                        <a href="{{ $rules->nextPageUrl() }}" class="pagination-text">Next</a>
                    @else
                        <span class="pagination-text" style="opacity: 0.5;">Next</span>
                    @endif
                </div>
                @endif
            </div>

        </div>
        </div><!-- end content-area -->
    </main>

    <!-- MODAL TAMBAH PESAN -->
    <div id="modalTambahPesan" class="modal-overlay">
        <form id="formTambah" class="modal-box">
            <div class="modal-header">
                <h2>Tambah Pesan</h2>
            </div>
            
            <div class="modal-body">
                
                <div class="input-group">
                    <label class="info-label">Nama Notifikasi</label>
                    <input type="text" name="kategori" class="form-input" placeholder="Contoh: Peringatan SKP Triwulan" required>
                </div>

                <div class="input-group">
                    <label class="info-label">Jenis Notifikasi</label>
                    <select id="pilihJenis" class="form-select" onchange="toggleJadwal()" required>
                        <option value="" disabled selected>Pilih Jenis</option>
                        <option value="Penjadwalan">Penjadwalan (Otomatis)</option>
                        <option value="Template">Template (Manual)</option>
                    </select>
                </div>

                <div class="input-group">
                    <label class="info-label">Interval / Jeda Waktu (Hari)</label>
                    <input type="number" id="inputInterval" name="interval_hari" class="form-input" placeholder="Contoh: 1, 30, 60" required>
                    <p class="text-xs text-gray-500 mt-1" style="font-size: 11px; color: #6b7280; margin-top: 4px;">
                       * <b>Jadwal:</b> Masukkan dalam hari (Contoh: 1 = Harian, 7 = Mingguan, 30 = Bulanan).
                    </p>
                </div>

                <div class="input-group">
                    <label class="info-label">Isi Pesan</label>
                    <textarea name="template_pesan" class="form-input text-area-pesan" placeholder="Tulis template pesan di sini..." required></textarea>
                    <p class="text-xs text-gray-500 mt-1" style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                        Gunakan placeholder: <b>{nama}</b>, <b>{nip}</b>, <b>{deadline}</b>, <b>{poin}</b>, <b>{next_pangkat}</b>
                    </p>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-modal-save">Simpan</button>
            </div>
        </form>
    </div>

    <!-- MODAL EDIT PESAN -->
    <div id="modalEditPesan" class="modal-overlay">
        <form id="formEdit" class="modal-box">
            <input type="hidden" id="editId" name="id">
            <div class="modal-header">
                <h2>Edit Pesan</h2>
            </div>
            
            <div class="modal-body">
                <div class="input-group">
                    <label class="info-label">Nama Notifikasi</label>
                    <input type="text" id="editNama" name="kategori" class="form-input" required>
                </div>

                <div class="input-group">
                <div class="input-group">
                    <label class="info-label">Jenis & Interval (Hari)</label>
                    <div style="display: flex; gap: 10px;">
                        <select id="editJenis" name="jenis" class="form-select" style="width: 130px;" onchange="toggleJadwalEdit()">
                            <option value="Penjadwalan">Jadwal Rutin</option>
                            <option value="Template">Template</option>
                        </select>
                        <input type="number" id="editJadwal" name="interval_hari" class="form-input" placeholder="Jml Hari" required>
                    </div>
                    <p class="text-xs text-gray-500 mt-1" style="font-size: 11px; color: #6b7280; margin-top: 4px;">
                        Set ke 0 jika Tipe = Template. (Tips: 7 = 1 Minggu, 30 = 1 Bulan).
                    </p>
                </div>
                </div>

                <div class="input-group">
                    <label class="info-label">Isi Pesan</label>
                    <textarea id="editIsi" name="template_pesan" class="form-input text-area-pesan" required></textarea>
                     <p class="text-xs text-gray-500 mt-1" style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                        Gunakan placeholder: <b>{nama}</b>, <b>{nip}</b>, <b>{deadline}</b>, <b>{poin}</b>, <b>{next_pangkat}</b>
                    </p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn-modal-save">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <!-- MODAL HAPUS PESAN -->
    <div id="modalHapusPesan" class="modal-overlay">
        <div class="modal-box modal-delete-size">
            <div class="delete-content">
                <h3 class="delete-title">
                    Hapus Pesan Ini?
                    <i class="ph-fill ph-warning" style="color: #fbbf24; font-size: 24px;"></i>
                </h3>
                <div class="delete-actions">
                    <button class="btn-pill confirm-delete" onclick="confirmDelete()">Yakin</button>
                    <button class="btn-pill" onclick="closeDeleteModal()">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div id="syncToast" class="toast-notification">
        <i class="ph-bold ph-check-circle" style="font-size: 20px;"></i>
        <span>Sinkronisasi Data Berhasil!</span>
    </div>

    <script>
    let currentDeleteId = null;

    // === TAMBAH ===
    function openModal() {
        document.getElementById('modalTambahPesan').style.display = 'flex';
    }
    function closeModal() {
        document.getElementById('modalTambahPesan').style.display = 'none';
        document.getElementById('formTambah').reset();
    }
    function toggleJadwal() {
        var jenis = document.getElementById("pilihJenis").value;
        var intervalInput = document.getElementById("inputInterval");
        
        if (jenis === "Template") {
            intervalInput.disabled = true;
            intervalInput.value = "";
            intervalInput.style.backgroundColor = "#f3f4f6";
            intervalInput.removeAttribute('required');
        } else {
            intervalInput.disabled = false;
            intervalInput.style.backgroundColor = "white";
            intervalInput.setAttribute('required', 'required');
        }
    }

    document.getElementById('formTambah').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        if (document.getElementById('pilihJenis').value === 'Template') {
            formData.set('interval_hari', '0');
        }

        fetch("{{ route('konfigurasi-pesan.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload();
            }
        });
    });

    // === EDIT ===
    function openEditModal(button) {
        const card = button.closest('.message-card');
        const id = card.getAttribute('data-id');
        const kategori = card.getAttribute('data-kategori');
        const interval = card.getAttribute('data-interval');
        const isi = card.querySelector('.message-content').innerText;

        document.getElementById('editId').value = id;
        document.getElementById('editNama').value = kategori;
        document.getElementById('editIsi').value = isi;

        const jenisSelect = document.getElementById('editJenis');
        const jadwalSelect = document.getElementById('editJadwal');

        if (interval == 0 || interval == '') {
            jenisSelect.value = 'Template';
            jadwalSelect.disabled = true;
            jadwalSelect.style.backgroundColor = "#f3f4f6";
            jadwalSelect.value = "30"; // Default reset
        } else {
            jenisSelect.value = 'Penjadwalan';
            jadwalSelect.disabled = false;
            jadwalSelect.style.backgroundColor = "white";
            jadwalSelect.value = interval;
        }

        document.getElementById('modalEditPesan').style.display = 'flex';
    }

    function toggleJadwalEdit() {
        const jenis = document.getElementById("editJenis").value;
        const jadwalSelect = document.getElementById("editJadwal");
        
        if (jenis === "Template") {
            jadwalSelect.disabled = true;
            jadwalSelect.style.backgroundColor = "#f3f4f6";
            jadwalSelect.value = ""; // Clear value for template
        } else {
            jadwalSelect.disabled = false;
            jadwalSelect.style.backgroundColor = "white";
            if (!jadwalSelect.value) jadwalSelect.value = "30"; // Default back
        }
    }

    function closeEditModal() {
        document.getElementById('modalEditPesan').style.display = 'none';
    }

    document.getElementById('formEdit').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('editId').value;
        
        // Handle interval logic for edit
        const formData = new FormData(this);
        if (document.getElementById('editJenis').value === 'Template') {
            formData.set('interval_hari', '0');
        }

        fetch(`/konfigurasi-pesan/${id}`, {
            method: "POST", // Method spoofing via _method is better, but JSON works too usually if handled
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                _method: 'PUT',
                ...Object.fromEntries(formData)
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload();
            }
        });
    });

    // === HAPUS ===
    function openDeleteModal(id) {
        currentDeleteId = id;
        document.getElementById('modalHapusPesan').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('modalHapusPesan').style.display = 'none';
        currentDeleteId = null;
    }

    function confirmDelete() {
        if (!currentDeleteId) return;

        fetch(`/konfigurasi-pesan/${currentDeleteId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pesan berhasil dihapus!');
                location.reload();
            }
        });
    }

    // === GLOBAL ===
    window.onclick = function(event) {
        const modals = [
            document.getElementById('modalTambahPesan'),
            document.getElementById('modalEditPesan'),
            document.getElementById('modalHapusPesan')
        ];
        modals.forEach(modal => {
            if (event.target == modal) modal.style.display = "none";
        });
    }

    function showSyncToast() {
        var toast = document.getElementById("syncToast");
        toast.className = "toast-notification show";
        setTimeout(function(){ 
            toast.className = toast.className.replace("show", ""); 
        }, 3000);
    }

    // === NAVBAR: DROPDOWN PROFILE ===
    function toggleDropdown() {
        var dropdown = document.getElementById("profileDropdown");
        if (dropdown.style.display === "block") {
            dropdown.style.display = "none";
        } else {
            dropdown.style.display = "block";
        }
    }

    // === NAVBAR: NOTIFIKASI ===
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
            var clickAction = n.read ? '' : ' onclick="markNotifRead(\'' + n.id + '\')"}'; 
            html += '<div class="notif-item' + unreadClass + '"' + clickAction + '>' +
                '<div class="notif-content">' +
                    '<p class="notif-title">' + n.title + '</p>' +
                    '<p class="notif-message">' + n.message + '</p>' +
                    '<span class="notif-time">' + n.time + '</span>' +
                '</div></div>';
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
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => { if (data.success) fetchNotifications(); })
        .catch(err => console.error('Gagal mark read:', err));
    }

    function markNotifRead(notifId) {
        fetch('/notifications/' + notifId + '/read', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => { if (data.success) fetchNotifications(); })
        .catch(err => console.error('Gagal mark notif:', err));
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchNotifications();
    });

    // Close Dropdowns on outside click
    window.addEventListener('click', function(e){
        // Navbar: tutup profile dropdown
        if (!e.target.closest('.profile-btn')) {
            var dropdowns = document.getElementsByClassName("dropdown-menu");
            for (var i = 0; i < dropdowns.length; i++) {
                if (dropdowns[i].style.display === "block") dropdowns[i].style.display = "none";
            }
        }
        // Navbar: tutup notif dropdown
        if (!e.target.closest('.notif-wrapper')) {
            var notifDropdown = document.getElementById('notifDropdown');
            if (notifDropdown) notifDropdown.classList.remove('active');
        }
    });
    </script>

    @include('partials.change_password_modal')
</body>
</html>
