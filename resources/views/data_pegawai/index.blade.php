<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pegawai - DashboardAlert</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/data_pegawai.css') }}">
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
            <a href="{{ route('data-pegawai') }}" class="nav-item active">
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
            <h2 class="page-title">Data Pegawai</h2>
            <form id="searchForm" method="GET" action="{{ route('data-pegawai') }}" class="search-box">
                <i class="ph-bold ph-magnifying-glass search-icon-inside"></i>
                <input type="search" id="searchInput" name="search" placeholder="Cari pegawai" class="search-input" value="{{ request('search') }}">
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
                            <td colspan="5" style="text-align: center; padding: 40px; color: #9ca3af;">
                                Tidak ada data pegawai.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                {{ $pegawais->withQueryString()->links('pagination::simple-default') }}
            </div>
        </div>
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

    <!-- MODAL DETAIL PEGAWAI -->
    <div id="modalDetailPegawai" class="modal-overlay">
        <div class="modal-box-large">
            
            <div class="detail-header">
                <h2>Data Lengkap Pegawai</h2>
            </div>
            
            <!-- Loading State inside Modal -->
            <div id="detailLoading" style="text-align: center; padding: 50px; display: none;">
                <i class="ph-bold ph-spinner" style="font-size: 40px; animation: spin 1s linear infinite;"></i>
                <p>Memuat data...</p>
            </div>

            <div id="detailContent" class="detail-layout">
                <div class="profile-area">
                    <div class="profile-placeholder">
                        <i class="ph-fill ph-user" style="font-size: 80px; opacity: 0.3;"></i>
                    </div>
                </div>

                <div class="info-area">
                    <div class="info-row">
                        <span class="label">Nama</span><span class="separator">:</span>
                        <span class="value" id="detNama">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">NIP</span><span class="separator">:</span>
                        <span class="value" id="detNIP">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Jabatan</span><span class="separator">:</span>
                        <span class="value" id="detJabatan">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Pangkat</span><span class="separator">:</span>
                        <span class="value" id="detPangkat">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Jenjang</span><span class="separator">:</span>
                        <span class="value" id="detGolongan">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Tanggal Masuk (CPNS)</span><span class="separator">:</span>
                        <span class="value" id="detTmt">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Angka Kredit saat ini</span><span class="separator">:</span>
                        <span class="value" id="detKredit">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">No HP</span><span class="separator">:</span>
                        <span class="value" id="detHP">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email</span><span class="separator">:</span>
                        <span class="value" id="detEmail">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Alamat</span><span class="separator">:</span>
                        <span class="value" id="detAlamat">-</span>
                    </div>

                    <div class="detail-actions">
                        <button class="btn-bell" onclick="openReminderModal()">
                            <i class="ph-fill ph-bell-ringing" style="font-size: 20px;"></i>
                        </button>
                        <button class="btn-back" onclick="closeDetailModal()">Kembali</button>
                    </div>
                </div>
            </div>

            <div class="doc-section">
                <div class="doc-header">
                    <div class="col-no">No</div>
                    <div class="col-name">Dokumen Yang Perlu diunggah!</div>
                </div>
                <div class="doc-row">
                    <div class="col-no">1</div>
                    <div class="col-name">PAK ( Penilaian Angka Kredit) Konversi SKP (Sasaran Kinerja Pegawai)</div>
                </div>
                <div class="doc-row">
                    <div class="col-no">2</div>
                    <div class="col-name">SK (Surat Keputusan) Jabatan Fungsional</div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL REMINDER -->
    <div id="modalReminder" class="modal-overlay" style="z-index: 2000;">
        <div class="modal-box-reminder">
            <h3 class="reminder-title">Pengingat Manual</h3>

            <div class="form-group">
                <label class="form-label">Pilih Template</label>
                <select id="reminderTemplate" class="form-select">
                    <option value="" disabled selected>Pilih</option>
                    <option value="skp">Peringatan SKP Triwulan</option>
                    <option value="berkas">Pengingat Kelengkapan Berkas</option>
                    <option value="kenaikan">Info Kenaikan Pangkat</option>
                </select>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="checkCustom" onchange="toggleMessageMode()">
                <label for="checkCustom">Apakah anda ingin menambahkan pesan custom?</label>
            </div>

            <div class="form-group">
                <label class="form-label">Isi Pesan</label>
                <textarea id="reminderMessage" class="form-textarea" disabled placeholder="Tulis pesan custom anda di sini..."></textarea>
            </div>

            <div class="reminder-actions">
                <button class="btn-cancel-soft" onclick="closeReminderModal()">Batal</button>
                <button class="btn-send-soft" onclick="sendReminder()">Kirim</button>
            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div id="syncToast" class="toast-notification">
        <i class="ph-bold ph-check-circle" style="font-size: 20px;"></i>
        <span>Sinkronisasi Data Berhasil!</span>
    </div>

    <script>
        let currentDeleteNip = null;

        // === FETCH DETAIL PEGAWAI ===
        function openDetailModal(nip) {
            document.getElementById('modalDetailPegawai').style.display = 'flex';
            document.getElementById('detailLoading').style.display = 'block';
            document.getElementById('detailContent').style.display = 'none';

            fetch(`/data-pegawai/${nip}`)
                .then(response => response.json())
                .then(res => {
                    const data = res.data;
                    document.getElementById('detNama').innerText = data.nama;
                    document.getElementById('detNIP').innerText = data.nip;
                    document.getElementById('detJabatan').innerText = data.jabatan;
                    document.getElementById('detPangkat').innerText = data.pangkat;
                    document.getElementById('detGolongan').innerText = data.golongan;
                    document.getElementById('detTmt').innerText = data.tmt_cpns;
                    document.getElementById('detKredit').innerText = data.angka_kredit;
                    document.getElementById('detHP').innerText = data.no_hp;
                    document.getElementById('detEmail').innerText = data.email;
                    document.getElementById('detAlamat').innerText = data.alamat;

                    document.getElementById('detailLoading').style.display = 'none';
                    document.getElementById('detailContent').style.display = 'flex';
                })
                .catch(err => {
                    console.error(err);
                    alert("Gagal mengambil data pegawai.");
                    closeDetailModal();
                });
        }

        function closeDetailModal() {
            document.getElementById('modalDetailPegawai').style.display = 'none';
        }

        // === DELETE LOGIC ===
        function openDeleteModal(nip, nama) {
            currentDeleteNip = nip;
            document.getElementById('modalHapusPegawai').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('modalHapusPegawai').style.display = 'none';
            currentDeleteNip = null;
        }

        function confirmDelete() {
            if (!currentDeleteNip) return;

            fetch(`/data-pegawai/${currentDeleteNip}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Pegawai berhasil dihapus!');
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus pegawai!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan!');
            });
        }

        // === REMINDER LOGIC ===
        function openReminderModal() {
            document.getElementById('modalReminder').style.display = 'flex';
        }

        function closeReminderModal() {
            document.getElementById('modalReminder').style.display = 'none';
        }

        function toggleMessageMode() {
            const isCustom = document.getElementById('checkCustom').checked;
            const selectTemplate = document.getElementById('reminderTemplate');
            const txtMessage = document.getElementById('reminderMessage');

            if (isCustom) {
                selectTemplate.disabled = true;
                selectTemplate.value = "";
                txtMessage.disabled = false;
                txtMessage.focus();
            } else {
                selectTemplate.disabled = false;
                txtMessage.disabled = true;
                txtMessage.value = "";
            }
        }

        function sendReminder() {
            alert("Pesan simulasi berhasil dikirim!");
            closeReminderModal();
        }

        // === SYNC TOAST ===
        function showSyncToast() {
            var toast = document.getElementById("syncToast");
            toast.className = "toast-notification show";
            setTimeout(function(){ 
                toast.className = toast.className.replace("show", ""); 
            }, 3000);
        }

        // Close Modal on Click Outside
        window.onclick = function(event) {
            const detailModal = document.getElementById('modalDetailPegawai');
            const deleteModal = document.getElementById('modalHapusPegawai');
            const reminderModal = document.getElementById('modalReminder');

            if (event.target == detailModal) detailModal.style.display = "none";
            if (event.target == deleteModal) deleteModal.style.display = "none";
            if (event.target == reminderModal) reminderModal.style.display = "none";
        }
    </script>
    <style>
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</body>
</html>
