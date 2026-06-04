@extends('layouts.app')

@section('title', 'Daftar Admin')



@section('head')
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
            font-family: 'Inter', sans-serif !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
            transition: all 0.2s ease !important;
        }
        .ts-control.focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }
        .ts-dropdown {
            font-family: 'Inter', sans-serif !important;
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
@endsection

@section('content')
        <div class="content-header">
            <h2 class="page-title">Daftar Admin</h2>
            <div class="header-actions">
                <form method="GET" action="{{ route('daftar-admin') }}" class="search-box">
                    <i class="ph-bold ph-magnifying-glass search-icon"></i>
                    <input type="text" name="search" placeholder="Cari Admin" class="search-input" value="{{ request('search') }}">
                </form>
                <a href="#" class="btn-tambah" onclick="openAddModal()"><i class="ph-bold ph-user-plus" style="font-size: 16px; margin-right: 6px;"></i>Tambah</a>
            </div>
        </div>

        <div class="content-section">
            <h3 class="section-title">Data Admin</h3>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Profil Admin</th>
                            <th style="width: 30%;">Kontak / Email</th>
                            <th style="width: 25%;">Peran</th>
                            <th style="width: 15%; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="skeleton-layer">
                        @for ($i = 0; $i < 4; $i++)
                        <tr>
                            <td><div class="skeleton-box" style="height:16px; width:65%; margin-bottom:6px;"></div><div class="skeleton-box" style="height:12px; width:40%;"></div></td>
                            <td><div class="skeleton-box" style="height:14px; width:80%;"></div></td>
                            <td><div class="skeleton-box" style="height:28px; width:100px; border-radius:20px;"></div></td>
                            <td style="text-align:center;"><div style="display:flex; gap:8px; justify-content:center;"><div class="skeleton-box" style="height:36px; width:36px; border-radius:8px;"></div><div class="skeleton-box" style="height:36px; width:36px; border-radius:8px;"></div></div></td>
                        </tr>
                        @endfor
                    </tbody>

                    {{-- Real: Table Rows --}}
                    <tbody class="real-content hidden">
                        @forelse($admins as $admin)
                        <tr data-id="{{ $admin->id }}" data-nama="{{ $admin->nama_lengkap }}" data-nip="{{ $admin->username }}" data-role="{{ $admin->role }}" data-is-super="{{ $admin->isSuperAdmin() ? '1' : '0' }}">
                            <td>
                                <div style="font-weight: 600; color: #1e293b;">{{ $admin->nama_lengkap }}</div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 4px; font-family: monospace;">{{ $admin->username }}</div>
                            </td>
                            <td>
                                <div style="color: #475569; font-size: 14px;">{{ $admin->email ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $admin->isSuperAdmin() ? 'badge-super' : 'badge-pegawai' }}">
                                    {{ $admin->isSuperAdmin() ? 'Admin Super' : 'Admin Kepegawaian' }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <button class="btn-icon btn-edit" onclick="openEditModal(this)" title="Edit Peran">
                                        <i class="ph-bold ph-pencil-simple" style="font-size: 18px;"></i>
                                    </button>
                                    <button class="btn-icon btn-delete" onclick="openDeleteModal({{ $admin->id }}, '{{ $admin->nama_lengkap }}')" title="Hapus Admin">
                                        <i class="ph-fill ph-trash" style="font-size: 18px;"></i>
                                    </button>
                                </div>
                            </td>
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

    <!-- STYLES UNTUK MODAL ADMIN -->
    <style>
        .tm-overlay {
            position: fixed; inset: 0; z-index: 2500;
            background: rgba(10, 18, 40, 0.55);
            backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }
        .tm-overlay.open { opacity: 1; visibility: visible; }
        
        .tm-card {
            background: #ffffff; border-radius: 20px;
            box-shadow: 0 32px 64px -16px rgba(20,43,111,0.25), 0 0 0 1px rgba(20,43,111,0.06);
            width: 100%; max-width: 500px;
            display: flex; flex-direction: column; max-height: 92vh;
            transform: translateY(20px) scale(0.97);
            transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);
        }
        .tm-overlay.open .tm-card { transform: translateY(0) scale(1); }
        
        .tm-header {
            background: linear-gradient(135deg, #142B6F 0%, #1e3a8a 100%);
            padding: 24px 28px; position: relative; overflow: hidden;
            display: flex; justify-content: space-between; align-items: flex-start;
            flex-shrink: 0; border-radius: 20px 20px 0 0;
        }
        .tm-header::before {
            content: ''; position: absolute; top: -30px; right: -30px;
            width: 140px; height: 140px; background: rgba(255,201,40,0.08); border-radius: 50%;
        }
        .tm-header::after {
            content: ''; position: absolute; bottom: -50px; left: -20px;
            width: 160px; height: 160px; background: rgba(255,255,255,0.04); border-radius: 50%;
        }
        .tm-header-left { display: flex; align-items: center; gap: 14px; position: relative; z-index: 1; }
        .tm-icon-wrap {
            width: 48px; height: 48px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.1); border: 1.5px solid rgba(255,255,255,0.2);
            font-size: 24px; color: #ffffff;
        }
        .tm-title-wrap h2 { margin: 0 0 2px 0; color: #ffffff; font-size: 18px; font-weight: 700; }
        .tm-title-wrap p { margin: 0; color: rgba(255,255,255,0.7); font-size: 13px; }
        
        .tm-close-btn {
            background: rgba(255,255,255,0.1); border: none; border-radius: 50%;
            width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
            color: #ffffff; cursor: pointer; transition: all 0.2s; position: relative; z-index: 1;
        }
        .tm-close-btn:hover { background: rgba(255,255,255,0.2); transform: rotate(90deg); }

        .tm-body { padding: 24px 28px; flex: 1; }
        
        .tm-footer {
            padding: 16px 28px 24px; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;
        }
        .tm-btn-cancel {
            padding: 12px 24px; border-radius: 10px; border: 1.5px solid #e2e8f0;
            background: #f8fafc; font-size: 14px; font-weight: 600; color: #64748b;
            cursor: pointer; transition: all 0.2s ease; font-family: inherit;
        }
        .tm-btn-cancel:hover { background: #f1f5f9; border-color: #cbd5e1; color: #374151; }
        
        .tm-btn-submit {
            padding: 12px 24px; border-radius: 10px; border: none;
            background: linear-gradient(135deg, #142B6F 0%, #1e3a8a 100%);
            font-size: 14px; font-weight: 700; color: #ffffff;
            cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 12px rgba(20,43,111,0.25); font-family: inherit;
        }
        .tm-btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(20,43,111,0.35); }
    </style>

    <!-- MODAL EDIT ADMIN -->
    <div id="modalEditAdmin" class="tm-overlay" onclick="if(event.target===this) closeEditModal()">
        <div class="tm-card">
            <div class="tm-header">
                <div class="tm-header-left">
                    <div class="tm-icon-wrap" style="background: rgba(255,201,40,0.15); border-color: rgba(255,201,40,0.3); color: #FFC928;">
                        <i class="ph-bold ph-pencil-simple"></i>
                    </div>
                    <div class="tm-title-wrap">
                        <h2>Edit Peran Admin</h2>
                        <p>Kelola hak akses administratif</p>
                    </div>
                </div>
                <button type="button" class="tm-close-btn" onclick="closeEditModal()">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>
            
            <div class="tm-body">
                <div style="background-color: #f8fafc; border: 1.5px solid #e2e8f0; padding: 14px 16px; border-radius: 12px; margin-bottom: 20px;">
                    <p style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; margin-top: 0;">Identitas Pegawai</p>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; align-items: flex-start;">
                            <span style="font-size: 13px; color: #64748b; width: 60px;">Nama</span>
                            <span style="font-size: 14px; color: #1e293b; font-weight: 700;">: <span id="modalNama">-</span></span>
                        </div>
                        <div style="display: flex; align-items: flex-start;">
                            <span style="font-size: 13px; color: #64748b; width: 60px;">NIP</span>
                            <span style="font-size: 14px; color: #1e293b; font-weight: 700;">: <span id="modalNip">-</span></span>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 5px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Edit Peran (Role)</label>
                    <select id="modalSelectPeran">
                        <option value="0">Admin Kepegawaian</option>
                        <option value="1">Admin Super</option>
                    </select>
                </div>
            </div>

            <div class="tm-footer">
                <button type="button" class="tm-btn-cancel" onclick="closeEditModal()">Batal</button>
                <button type="button" class="tm-btn-submit" onclick="saveRole()">
                    <i class="ph-bold ph-floppy-disk"></i> Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH ADMIN -->
    <div id="modalTambahAdmin" class="tm-overlay" onclick="if(event.target===this) closeAddModal()">
        <div class="tm-card">
            <div class="tm-header">
                <div class="tm-header-left">
                    <div class="tm-icon-wrap">
                        <i class="ph-bold ph-user-plus"></i>
                    </div>
                    <div class="tm-title-wrap">
                        <h2>Tambah Admin Baru</h2>
                        <p>Berikan akses Dashboard ke pegawai</p>
                    </div>
                </div>
                <button type="button" class="tm-close-btn" onclick="closeAddModal()">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>
            
            <div class="tm-body">
                <div style="background-color: #eff6ff; border: 1.5px solid #bfdbfe; padding: 14px 16px; border-radius: 12px; margin-bottom: 24px; display: flex; gap: 12px;">
                    <i class="ph-fill ph-info" style="color: #3b82f6; font-size: 20px; flex-shrink: 0;"></i>
                    <p style="font-size: 13px; color: #1e3a8a; line-height: 1.6; margin: 0;">
                        Pilih pegawai yang akan diberikan akses.<br>
                        <span style="display: block; margin-top: 8px; font-size: 12px;">
                            <strong style="color: #1e40af;">Email Login:</strong> Email Pegawai<br>
                            <strong style="color: #1e40af;">Kata Sandi:</strong> <span style="font-family: monospace; background: rgba(59, 130, 246, 0.15); padding: 2px 6px; border-radius: 4px; font-weight: 700; color: #1d4ed8;">NIP Pegawai</span>
                        </span>
                    </p>
                </div>
                
                <div style="margin-bottom: 5px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Pilih Calon Admin</label>
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

            <div class="tm-footer">
                <button type="button" class="tm-btn-cancel" onclick="closeAddModal()">Batal</button>
                <button type="button" class="tm-btn-submit" onclick="saveNewAdmin()">
                    <i class="ph-bold ph-floppy-disk"></i> Simpan Akses
                </button>
            </div>
        </div>
    </div>
    
    <!-- MODAL HAPUS ADMIN - AWWWARDS CLASS -->
    <style>
        #modalHapusAdmin {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(10, 18, 40, 0.55);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 2500;
            display: none;
            justify-content: center;
            align-items: center;
        }
        #modalHapusAdmin.open {
            display: flex;
        }
        .dm-admin-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow:
                0 32px 64px -12px rgba(239, 68, 68, 0.2),
                0 0 0 1px rgba(239, 68, 68, 0.06);
            width: 100%;
            max-width: 440px;
            padding: 40px 36px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
            transform: scale(0.9) translateY(20px);
            opacity: 0;
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                        opacity 0.3s ease;
        }
        #modalHapusAdmin.open .dm-admin-card {
            transform: scale(1) translateY(0);
            opacity: 1;
        }
        .dm-admin-card::after {
            content: '';
            position: absolute;
            bottom: -60px; right: -60px;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(239,68,68,0.05) 0%, transparent 70%);
            pointer-events: none;
        }
        .dm-admin-icon-wrap {
            position: relative;
            width: 96px;
            height: 96px;
            margin: 0 auto 24px;
        }
        .dm-admin-icon-wrap::before {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.1);
            animation: dm-admin-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes dm-admin-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.08); }
        }
        .dm-admin-icon-inner {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(239, 68, 68, 0.15);
            position: relative;
            z-index: 1;
        }
        .dm-admin-icon-inner i {
            font-size: 48px;
            color: #ef4444;
        }
        .dm-admin-danger-tag {
            display: inline-block;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 3px 10px;
            border-radius: 20px;
            border: 1px solid rgba(220, 38, 38, 0.15);
            margin-bottom: 12px;
        }
        .dm-admin-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 12px;
            letter-spacing: -0.3px;
        }
        .dm-admin-desc {
            font-size: 14px;
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 28px;
        }
        .dm-admin-name-highlight {
            color: #dc2626;
            font-weight: 700;
        }
        .dm-admin-actions {
            display: flex;
            gap: 12px;
        }
        .dm-admin-btn-cancel {
            flex: 1;
            padding: 12px 20px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
        }
        .dm-admin-btn-cancel:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #374151;
            transform: translateY(-1px);
        }
        .dm-admin-btn-confirm {
            flex: 1;
            padding: 12px 20px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .dm-admin-btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
        }
        .dm-admin-btn-confirm:active {
            transform: translateY(0);
        }
    </style>

    <div id="modalHapusAdmin" onclick="if(event.target===this) closeDeleteModal()">
        <div class="dm-admin-card">
            <div class="dm-admin-icon-wrap">
                <div class="dm-admin-icon-inner">
                    <i class="ph-fill ph-warning-circle"></i>
                </div>
            </div>
            <div class="dm-admin-danger-tag">⚠ Aksi Tidak Dapat Dibatalkan</div>
            <h3 class="dm-admin-title">Hapus Akses Admin?</h3>
            <p class="dm-admin-desc">
                Pegawai <strong class="dm-admin-name-highlight" id="deleteAdminName"></strong>
                akan <strong>kehilangan akses</strong> masuk ke halaman admin dashboard.
            </p>
            <div class="dm-admin-actions">
                <button class="dm-admin-btn-cancel" onclick="closeDeleteModal()">
                    Batal
                </button>
                <button class="dm-admin-btn-confirm" onclick="confirmDelete()" id="btnConfirmDeleteAdmin">
                    <i class="ph-bold ph-trash"></i>
                    Ya, Hapus
                </button>
            </div>
        </div>
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
            
            var selectControl = document.getElementById('modalSelectPeran').tomselect;
            if (selectControl) { selectControl.setValue(isSuper); }

            document.getElementById('modalEditAdmin').classList.add('open');
        }

        function closeEditModal() {
            document.getElementById('modalEditAdmin').classList.remove('open');
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
                    closeEditModal();
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
            document.getElementById('modalTambahAdmin').classList.add('open');
        }

        function closeAddModal() {
            document.getElementById('modalTambahAdmin').classList.remove('open');
            var selectControl = document.getElementById('selectPegawai').tomselect;
            if (selectControl) { selectControl.clear(); }
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
            const nameEl = document.getElementById('deleteAdminName');
            if (nameEl) nameEl.textContent = adminName;
            document.getElementById('modalHapusAdmin').classList.add('open');
        }

        function closeDeleteModal() {
            document.getElementById('modalHapusAdmin').classList.remove('open');
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
            var deleteModal = document.getElementById('modalHapusAdmin');
            if (event.target == deleteModal) closeDeleteModal();
            // Note: close actions for Edit and Tambah modals are handled via inline onclick in their overlays.
        });


    </script>


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
                        element: '.header-actions',
                        popover: {
                            title: 'Manajemen Admin 🛡️',
                            description: 'Anda bisa mencari admin spesifik atau menambahkan admin baru jika memiliki hak akses Super Admin.',
                            side: "bottom",
                            align: 'center'
                        }
                    },
                    {
                        element: '.data-table',
                        popover: {
                            title: 'Daftar Role Admin 📋',
                            description: 'Menampilkan semua admin beserta peran mereka. Anda dapat mengubah peran atau menghapus admin pada tabel ini.',
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


