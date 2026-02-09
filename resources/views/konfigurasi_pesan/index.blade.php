<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfigurasi Pesan - DashboardAlert</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/konfigurasi_pesan.css') }}">
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
                    @if($rule->interval_hari > 0)
                        <span class="badge-schedule">{{ $rule->interval_hari }} Hari</span>
                    @else
                        <span class="badge-dash">-</span>
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

        </div>
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
                    <label class="info-label">Frekuensi Jadwal</label>
                    <select id="pilihJadwal" name="interval_hari" class="form-select" disabled>
                        <option value="" selected>-- Pilih Waktu --</option>
                        <option value="30">1 Bulan Sekali</option>
                        <option value="90">3 Bulan Sekali (Triwulan)</option>
                        <option value="180">6 Bulan Sekali (Semester)</option>
                        <option value="365">1 Tahun Sekali</option>
                    </select>
                </div>

                <div class="input-group">
                    <label class="info-label">Isi Pesan</label>
                    <textarea name="template_pesan" class="form-input text-area-pesan" placeholder="Tulis template pesan di sini..." required></textarea>
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
                    <label class="info-label">Jenis & Jadwal</label>
                    <div style="display: flex; gap: 10px;">
                        <select id="editJenis" class="form-select" onchange="toggleJadwalEdit()">
                            <option value="Penjadwalan">Penjadwalan</option>
                            <option value="Template">Template</option>
                        </select>
                        <select id="editJadwal" name="interval_hari" class="form-select">
                            <option value="30">1 Bulan</option>
                            <option value="90">3 Bulan</option>
                            <option value="180">6 Bulan</option>
                            <option value="365">1 Tahun</option>
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <label class="info-label">Isi Pesan</label>
                    <textarea id="editIsi" name="template_pesan" class="form-input text-area-pesan" required></textarea>
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
        var jadwalDropdown = document.getElementById("pilihJadwal");
        if (jenis === "Template") {
            jadwalDropdown.disabled = true;
            jadwalDropdown.value = "";
            jadwalDropdown.style.backgroundColor = "#f3f4f6";
        } else {
            jadwalDropdown.disabled = false;
            jadwalDropdown.style.backgroundColor = "white";
        }
    }

    document.getElementById('formTambah').addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch("{{ route('konfigurasi-pesan.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(Object.fromEntries(new FormData(this)))
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
    </script>

</body>
</html>
