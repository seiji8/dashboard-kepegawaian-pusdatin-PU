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

                    <div class="doc-section">
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
    <div id="modalReminder" class="modal-overlay" style="z-index: 2000;">
        <div class="modal-box-reminder">
            <h3 class="reminder-title">Pengingat Manual</h3>

            <div class="form-group">
                <label class="form-label">Pilih Template</label>
                <select id="reminderTemplate" class="form-select">
                    <option value="" disabled selected>Pilih</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->kategori }}</option>
                    @endforeach
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
        let currentDetailNip = null; // Added for reminder context

        // === FETCH DETAIL PEGAWAI ===
        function openDetailModal(nip) {
            currentDetailNip = nip; // Store NIP
            document.getElementById('modalDetailPegawai').style.display = 'flex';
            document.getElementById('detailLoading').style.display = 'block';
            document.getElementById('detailContent').style.display = 'none';

            fetch(`/data-pegawai/${nip}`)
                .then(response => response.json())
                .then(res => {
                    const data = res.data;
                    document.getElementById('detNama').innerText = data.nama;
                    document.getElementById('detNIP').innerText = data.nip;
                    // Format Jabatan properly, maybe allow multiline if long
                    document.getElementById('detJabatan').innerText = data.jabatan;
                    document.getElementById('detTipeJabatan').innerText = data.tipe_jabatan;
                    document.getElementById('detPangkat').innerText = data.pangkat;
                    document.getElementById('detJenjang').innerText = data.jenjang;
                    document.getElementById('detTmt').innerText = data.tmt_cpns;
                    document.getElementById('detKredit').innerText = data.angka_kredit;
                    document.getElementById('detHP').innerText = data.no_hp;
                    document.getElementById('detEmail').innerText = data.email;
                    
                    // Update Initials Avatar
                    const initials = data.nama.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
                    document.getElementById('detAvatar').innerText = initials;

                    // Update Next KGB
                    document.getElementById('detNextKGB').innerText = data.next_kgb ? data.next_kgb : '-';

                    // Update Document Status
                    const docContainer = document.getElementById('docStatusContainer');
                    if (data.missing_documents && data.missing_documents.length > 0) {
                        let docListHtml = `
                            <div class="doc-warning-box">
                                <div class="doc-warning-header">
                                    <span>STATUS DOKUMEN</span>
                                    <span>TIDAK LENGKAP</span>
                                </div>
                        `;
                        data.missing_documents.forEach((doc, index) => {
                            docListHtml += `
                                <div class="doc-list-item">
                                    <div class="doc-number">${index + 1}</div>
                                    <div style="flex: 1;">${doc.nama_dokumen}</div>
                                </div>
                            `;
                        });
                        docListHtml += `</div>`;
                        docContainer.innerHTML = docListHtml;
                    } else {
                        docContainer.innerHTML = `
                            <div class="doc-success-box">
                                <div style="background: #10b981; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="ph-bold ph-check" style="color: white; font-size: 14px;"></i>
                                </div>
                                Semua Dokumen Lengkap
                            </div>
                        `;
                    }

                    document.getElementById('detailLoading').style.display = 'none';
                    document.getElementById('detailContent').style.display = 'flex';
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal mengambil data pegawai.',
                        confirmButtonColor: '#dc2626'
                    });
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Pegawai berhasil dihapus!',
                        confirmButtonColor: '#1e3a8a'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message || 'Gagal menghapus pegawai!',
                        confirmButtonColor: '#dc2626'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: 'Terjadi kesalahan!',
                    confirmButtonColor: '#dc2626'
                });
            });
        }

        // === REMINDER LOGIC ===
        function openReminderModal() {
            // Reset Form Fields
            document.getElementById('reminderTemplate').value = "";
            document.getElementById('checkCustom').checked = false;
            toggleMessageMode(); // This will reset disabled states and clear textarea

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
            if (!currentDetailNip) return;

            const isCustom = document.getElementById('checkCustom').checked;
            const templateId = document.getElementById('reminderTemplate').value;
            const customMessage = document.getElementById('reminderMessage').value;

            let payload = {};

            if (isCustom) {
                if (!customMessage) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Harap isi pesan custom!',
                        confirmButtonColor: '#1e3a8a'
                    });
                    return;
                }
                payload = { custom_message: customMessage };
            } else {
                if (!templateId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Harap pilih template!',
                        confirmButtonColor: '#1e3a8a'
                    });
                    return;
                }
                payload = { template_id: templateId };
            }

            // Button Loading State
            const btnSend = document.querySelector('.btn-send-soft');
            const originalText = btnSend.innerText;
            btnSend.innerText = 'Mengirim...';
            btnSend.disabled = true;

            fetch(`/data-pegawai/${currentDetailNip}/send-manual`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Email berhasil dikirim!',
                        confirmButtonColor: '#1e3a8a'
                    });
                    closeReminderModal();
                } else {
                     Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message || 'Gagal mengirim email.',
                        confirmButtonColor: '#dc2626'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: 'Terjadi kesalahan saat mengirim email.',
                    confirmButtonColor: '#dc2626'
                });
            })
            .finally(() => {
                btnSend.innerText = originalText;
                btnSend.disabled = false;
            });
        }

        // === SYNC TOAST ===
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

        // Close Modal on Click Outside + Navbar Dropdowns
        window.onclick = function(event) {
            const detailModal = document.getElementById('modalDetailPegawai');
            const deleteModal = document.getElementById('modalHapusPegawai');
            const reminderModal = document.getElementById('modalReminder');

            if (event.target == detailModal) detailModal.style.display = "none";
            if (event.target == deleteModal) deleteModal.style.display = "none";
            if (event.target == reminderModal) reminderModal.style.display = "none";

            // Navbar: tutup profile dropdown
            if (!event.target.closest('.profile-btn')) {
                var dropdowns = document.getElementsByClassName("dropdown-menu");
                for (var i = 0; i < dropdowns.length; i++) {
                    if (dropdowns[i].style.display === "block") dropdowns[i].style.display = "none";
                }
            }
            // Navbar: tutup notif dropdown
            if (!event.target.closest('.notif-wrapper')) {
                var notifDropdown = document.getElementById('notifDropdown');
                if (notifDropdown) notifDropdown.classList.remove('active');
            }
        }
    </script>
    <style>
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
    @include('partials.change_password_modal')
</body>
</html>
