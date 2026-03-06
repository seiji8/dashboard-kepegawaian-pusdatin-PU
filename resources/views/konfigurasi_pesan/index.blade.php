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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

                    // Remove focus to flip the arrow back immediately
                    document.getElementById('filterJenis').blur();
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
                    <button class="btn-icon btn-delete" onclick="openDeletePesanModal('{{ $rule->id }}')">
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
        <form id="formTambah" class="modal-box-compact">
            <div class="modal-header">
                <h2>Tambah Pesan</h2>
            </div>
            
            <div class="modal-body">
                
                <div style="display: grid; grid-template-columns: 3fr 2fr 1fr; gap: 16px; align-items: start;">
                    <div>
                        <label class="info-label">Nama Notifikasi</label>
                        <input type="text" name="kategori" class="form-input" placeholder="Contoh: SKP" required>
                    </div>
                    <div>
                        <label class="info-label">Jenis</label>
                        <select id="pilihJenis" class="form-select" onchange="toggleJadwal()" required>
                            <option value="" disabled selected>Pilih</option>
                            <option value="Penjadwalan">Jadwal</option>
                            <option value="Template">Template</option>
                        </select>
                    </div>
                    <div>
                         <label class="info-label">Jeda (Hari)</label>
                        <input type="number" id="inputInterval" name="interval_hari" class="form-input" placeholder="0" required>
                    </div>
                </div>
                <div style="margin-top: 4px; margin-bottom: 16px;">
                    <p class="helper-text" style="margin: 0;">
                        <span style="color: #d97706; font-weight: 600;">* Info:</span> Isi <b>1</b> (Harian), <b>7</b> (Mingguan), <b>30</b> (Bulanan).
                    </p>
                </div>

                <div class="input-group">
                    <label class="info-label">Isi Pesan</label>
                    <textarea name="template_pesan" class="form-input text-area-pesan" placeholder="Tulis template pesan di sini..." required></textarea>
                    <p class="helper-text">
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
        <form id="formEdit" class="modal-box-compact">
            <input type="hidden" id="editId" name="id">
            <div class="modal-header">
                <h2>Edit Pesan</h2>
            </div>
            
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 3fr 2fr 1fr; gap: 16px; align-items: start;">
                    <div>
                        <label class="info-label">Nama Notifikasi</label>
                        <input type="text" id="editNama" name="kategori" class="form-input" required>
                    </div>
                    <div>
                        <label class="info-label">Jenis</label>
                        <select id="editJenis" name="jenis" class="form-select" onchange="toggleJadwalEdit()">
                            <option value="Penjadwalan">Jadwal</option>
                            <option value="Template">Template</option>
                        </select>
                    </div>
                    <div>
                        <label class="info-label">Jeda (Hari)</label>
                        <input type="number" id="editJadwal" name="interval_hari" class="form-input" placeholder="0" required>
                    </div>
                </div>
                <div style="margin-top: 4px; margin-bottom: 16px;">
                    <p class="helper-text" style="margin: 0;">
                         <span style="color: #d97706; font-weight: 600;">* Info:</span> Isi <b>1</b> (Harian), <b>7</b> (Mingguan), <b>30</b> (Bulanan).
                    </p>
                </div>

                <div class="input-group">
                    <label class="info-label">Isi Pesan</label>
                    <textarea id="editIsi" name="template_pesan" class="form-input text-area-pesan" required></textarea>
                     <p class="helper-text">
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
                    <button class="btn-pill confirm-delete" onclick="confirmDeletePesan()">Yakin</button>
                    <button class="btn-pill" onclick="closeDeletePesanModal()">Batal</button>
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
                closeModal();
                showCustomToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showCustomToast(data.message || 'Terjadi kesalahan saat menyimpan data.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showCustomToast('Terjadi kesalahan pada server. Cek console atau hubungi admin.', 'error');
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
        .then(data => {
            if(data.success) {
                closeEditModal();
                showCustomToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showCustomToast(data.message || 'Terjadi kesalahan saat menyimpan data.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showCustomToast('Terjadi kesalahan pada server.', 'error');
        });
    });

    // === HAPUS ===
    function openDeletePesanModal(id) {
        currentDeleteId = id;
        document.getElementById('modalHapusPesan').style.display = 'flex';
    }

    function closeDeletePesanModal() {
        document.getElementById('modalHapusPesan').style.display = 'none';
        currentDeleteId = null;
    }

    function confirmDeletePesan() {
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
                closeDeletePesanModal();
                showCustomToast('Pesan berhasil dihapus!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showCustomToast(data.message || 'Gagal menghapus pesan!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showCustomToast('Terjadi kesalahan server!', 'error');
        });
    }
    </script>

    <script src="{{ asset('js/app-common.js') }}"></script>
    <script src="{{ asset('js/data-pegawai.js') }}"></script>
    @include('partials.change_password_modal')
</body>
</html>
