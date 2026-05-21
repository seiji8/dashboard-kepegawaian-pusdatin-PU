@extends('layouts.app')

@section('title', 'Konfigurasi Pesan')

@section('page_css')
    <link rel="stylesheet" href="{{ asset('css/pages/konfigurasi.css') }}">
@endsection

@section('content')
        <div class="content-header">
            <h2 class="page-title">Konfigurasi Pesan</h2>
            <div class="header-actions">
                <select id="filterJenis" class="select-filter" onchange="filterCards()">
                    <option value="all">Semua Jenis</option>
                    <option value="otomatis">Otomatis</option>
                    <option value="manual">Manual / Template</option>
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
                    let visibleCount = 0;

                    cards.forEach(card => {
                        const interval = parseInt(card.getAttribute('data-interval'));
                        const isSystem = card.getAttribute('data-is-system') === 'true';
                        let shouldShow = false;

                        if (filter === 'all') {
                            shouldShow = true;
                        } else if (filter === 'otomatis') {
                            // Masuk otomatis: Semua pesan bawaan sistem (trigger) ATAU yg punya interval > 0 (jadwal berulang)
                            shouldShow = (isSystem || interval > 0);
                        } else if (filter === 'manual') {
                            // Masuk manual: Bukan pesan sistem bawaan DAN tidak punya jadwal (interval <= 0)
                            shouldShow = (!isSystem && interval <= 0);
                        }

                        if (shouldShow) visibleCount++;
                        card.style.display = shouldShow ? 'grid' : 'none';
                    });

                    const emptyState = document.getElementById('emptyFilterState');
                    if (emptyState) {
                        emptyState.style.display = (visibleCount === 0) ? 'block' : 'none';
                    }

                    // Remove focus to flip the arrow back immediately
                    document.getElementById('filterJenis').blur();
                }
            </script>

            {{-- Skeleton: Config Cards --}}
            <div class="skeleton-layer">
                @for ($i = 0; $i < 4; $i++)
                <div class="config-grid" style="padding: 16px 20px;">
                    <div><div class="skeleton-box" style="height:16px; width:70%; margin-bottom:8px;"></div><div class="skeleton-box" style="height:12px; width:50%;"></div></div>
                    <div><div class="skeleton-box" style="height:14px; width:95%; margin-bottom:6px;"></div><div class="skeleton-box" style="height:14px; width:60%;"></div></div>
                    <div style="text-align:center;"><div class="skeleton-box" style="height:28px; width:80px; border-radius:20px; margin:0 auto;"></div></div>
                    <div style="text-align:center;"><div class="skeleton-box" style="height:36px; width:36px; border-radius:8px; margin:0 auto;"></div></div>
                </div>
                @endfor
            </div>

            {{-- Real: Config Cards --}}
            <div class="real-content hidden">
            @forelse($rules as $rule)
            @php
                $systemCategories = [
                    'KGB', 'KGB Penjadwalan', 'KGB Upload Dokumen', 'KP_Reguler', 
                    'KP_Struktural', 'DIKLAT_HUTANG', 'DIKLAT_ANOMALI', 'KJ_Jafung', 
                    'KP_Jafung', 'UKOM', 'Notifikasi Triwulan', 'Notifikasi Tahunan', 
                    'Info Kenaikan Pangkat'
                ];
                $isSystem = in_array($rule->kategori, $systemCategories);
            @endphp
            <div class="message-card" data-id="{{ $rule->id }}" data-kategori="{{ $rule->kategori }}" data-interval="{{ $rule->interval_hari }}" data-is-system="{{ $isSystem ? 'true' : 'false' }}">
                <div class="notif-name">
                    {{ $rule->kategori }}
                    @if($isSystem)
                        <i class="ph-fill ph-lock-key" style="color: #9ca3af; font-size: 14px; margin-left: 4px;" title="Pesan Sistem Bawaan"></i>
                    @endif
                </div>
                <div class="message-content">
                    {!! nl2br(e($rule->template_pesan)) !!}
                </div>
                <div class="schedule-col">
                    @php
                        $interval = $rule->interval_hari;
                        $format = '';
                        if ($interval >= 365 && $interval % 365 == 0) {
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
                        <span class="badge-schedule" style="background-color: #dbeafe; color: #1e40af; white-space: nowrap;">Otomatis</span>
                        <span class="badge-schedule" style="background-color: #fef3c7; color: #d97706; margin-left: 4px; white-space: nowrap;">H-{{ $rule->interval_hari }} Hari</span>
                    @elseif($isSystem && $interval <= 0)
                        <span class="badge-schedule" style="background-color: #dbeafe; color: #1e40af; white-space: nowrap;">Otomatis</span>
                    @elseif($isSystem && $interval > 0)
                        <span class="badge-schedule" style="background-color: #dbeafe; color: #1e40af; white-space: nowrap;">Otomatis</span>
                        <span class="badge-schedule" style="background-color: #fef3c7; color: #d97706; margin-left: 4px; white-space: nowrap;">{{ $format }}</span>
                    @elseif(!$isSystem && $interval <= 0)
                        <span class="badge-schedule" style="background-color: #f3f4f6; color: #6b7280; white-space: nowrap;">Template Manual</span>
                    @else
                        <span class="badge-schedule" style="background-color: #ecfdf5; color: #047857; white-space: nowrap;">Penjadwalan ({{ $format }})</span>
                    @endif
                </div>
                <div class="action-col">
                    <button class="btn-icon btn-edit" onclick="openEditModal(this)">
                        <i class="ph-bold ph-pencil-simple"></i>
                    </button>
                    @if(!$isSystem)
                    <button class="btn-icon btn-delete" onclick="openDeletePesanModal('{{ $rule->id }}')">
                        <i class="ph-bold ph-trash"></i>
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <div style="grid-column: 1 / -1; padding: 40px 0;">
                <div class="empty-state-container" style="padding: 0; border: none; background: transparent; box-shadow: none;">
                    <div class="empty-state-content">
                        <div class="empty-state-icon">
                            <i class="ph-duotone ph-chat-teardrop-slash"></i>
                        </div>
                        <h4 class="empty-state-title">Belum Ada Pesan</h4>
                        <p class="empty-state-desc">Sistem belum memiliki konfigurasi pesan sama sekali.<br>Silakan tambahkan pesan template pertama Anda.</p>
                        <button onclick="openModal()" class="btn-reset-search" style="border: none; cursor: pointer;">
                            <i class="ph-bold ph-plus"></i>
                            Tambah Pesan Baru
                        </button>
                    </div>
                </div>
            </div>
            @endforelse
            </div>

            <!-- EMPTY FILTER STATE (Hidden by default) -->
            <div id="emptyFilterState" style="display: none; grid-column: 1 / -1; padding: 40px 0;">
                <div class="empty-state-container" style="padding: 0; border: none; background: transparent; box-shadow: none;">
                    <div class="empty-state-content">
                        <div class="empty-state-icon">
                            <i class="ph-duotone ph-tray"></i>
                        </div>
                        <h4 class="empty-state-title">Pesan Tidak Ditemukan</h4>
                        <p class="empty-state-desc">Belum ada konfigurasi pesan untuk jenis filter yang Anda pilih.<br>Silakan pilih jenis filter lainnya atau tambahkan pesan baru.</p>
                        <button onclick="document.getElementById('filterJenis').value = 'all'; filterCards();" class="btn-reset-search" style="border: none; cursor: pointer;">
                            <i class="ph-bold ph-arrow-counter-clockwise"></i>
                            Tampilkan Semua
                        </button>
                    </div>
                </div>
            </div>

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
    <!-- STYLES UNTUK MODAL PESAN -->
    <style>
        /* Overlay & Animasi */
        .tm-overlay {
            position: fixed; inset: 0; z-index: 2400;
            background: rgba(10, 18, 40, 0.55);
            backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }
        .tm-overlay.open { opacity: 1; visibility: visible; }
        
        /* Modal Card */
        .tm-card {
            background: #ffffff; border-radius: 20px;
            box-shadow: 0 32px 64px -16px rgba(20,43,111,0.25), 0 0 0 1px rgba(20,43,111,0.06);
            width: 100%; max-width: 600px;
            display: flex; flex-direction: column; max-height: 92vh;
            overflow: hidden;
            transform: translateY(20px) scale(0.97);
            transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);
        }
        .tm-overlay.open .tm-card { transform: translateY(0) scale(1); }
        
        /* Header */
        .tm-header {
            background: linear-gradient(135deg, #142B6F 0%, #1e3a8a 100%);
            padding: 24px 28px; position: relative; overflow: hidden;
            display: flex; justify-content: space-between; align-items: flex-start;
            flex-shrink: 0;
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
        .tm-icon-wrap.edit { background: rgba(255,201,40,0.15); border-color: rgba(255,201,40,0.3); color: #FFC928; }
        .tm-title-wrap h2 { margin: 0 0 2px 0; color: #ffffff; font-size: 18px; font-weight: 700; }
        .tm-title-wrap p { margin: 0; color: rgba(255,255,255,0.7); font-size: 13px; }
        
        .tm-close-btn {
            background: rgba(255,255,255,0.1); border: none; border-radius: 50%;
            width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
            color: #ffffff; cursor: pointer; transition: all 0.2s; position: relative; z-index: 1;
        }
        .tm-close-btn:hover { background: rgba(255,255,255,0.2); transform: rotate(90deg); }

        /* Body */
        .tm-body { padding: 24px 28px; overflow-y: auto; flex: 1; }
        .tm-form-group { margin-bottom: 20px; }
        .tm-label {
            display: block; font-size: 11px; font-weight: 700; color: #64748b;
            margin-bottom: 8px; letter-spacing: 0.5px; text-transform: uppercase;
        }
        .tm-input, .tm-select, .tm-textarea {
            width: 100%; padding: 12px 14px; border: 1.5px solid #e2e8f0;
            border-radius: 10px; font-size: 14px; color: #1e293b; background: #f8fafc;
            transition: all 0.2s ease; outline: none; box-sizing: border-box; font-family: inherit;
        }
        .tm-textarea { resize: vertical; min-height: 120px; line-height: 1.5; }
        .tm-input:focus, .tm-select:focus, .tm-textarea:focus {
            border-color: #142B6F; background: #ffffff; box-shadow: 0 0 0 4px rgba(20,43,111,0.08);
        }
        .tm-select { cursor: pointer; appearance: auto; }
        
        /* Guide Box */
        .tm-guide-box {
            background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px;
            padding: 16px; margin-top: 4px;
        }
        .tm-guide-header { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
        .tm-guide-header i { color: #142B6F; font-size: 18px; }
        .tm-guide-header span { font-size: 13px; font-weight: 700; color: #1e293b; }
        
        .tm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .tm-grid-item {
            background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;
        }
        .tm-grid-item code {
            color: #142B6F; font-size: 13px; font-weight: 700; background: #f0f4ff;
            padding: 2px 6px; border-radius: 4px; display: inline-block; margin-bottom: 4px;
        }
        .tm-grid-item span { color: #64748b; font-size: 11px; display: block; line-height: 1.3; }
        
        /* Footer */
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
        .tm-btn-submit:active { transform: translateY(0); }
        .tm-btn-submit.green { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); box-shadow: 0 4px 12px rgba(22,163,74,0.25); }
        .tm-btn-submit.green:hover { box-shadow: 0 6px 16px rgba(22,163,74,0.35); }
    </style>

    <!-- MODAL TAMBAH PESAN -->
    <div id="modalTambahPesan" class="tm-overlay" onclick="if(event.target===this) closeModal()">
        <form id="formTambah" class="tm-card">
            
            <!-- Header -->
            <div class="tm-header">
                <div class="tm-header-left">
                    <div class="tm-icon-wrap">
                        <i class="ph-bold ph-plus"></i>
                    </div>
                    <div class="tm-title-wrap">
                        <h2>Tambah Template Pesan</h2>
                        <p>Buat template pengingat manual baru</p>
                    </div>
                </div>
                <button type="button" class="tm-close-btn" onclick="closeModal()">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="tm-body">
                <input type="hidden" name="interval_hari" value="0">
                
                <div class="tm-form-group">
                    <label class="tm-label">Nama Template Pesan</label>
                    <input type="text" name="kategori" class="tm-input" placeholder="Contoh: Pengingat Berkas SKP" required>
                </div>

                <div class="tm-form-group">
                    <label class="tm-label">Isi Pesan</label>
                    <textarea name="template_pesan" class="tm-textarea" placeholder="Tulis kerangka template pesan di sini..." required></textarea>
                </div>

                <!-- Panduan Placeholder -->
                <div class="tm-guide-box">
                    <div class="tm-guide-header">
                        <i class="ph-fill ph-info"></i>
                        <span>Variabel Placeholder yang Tersedia</span>
                    </div>
                    <div class="tm-grid">
                        <div class="tm-grid-item"><code>{nama}</code><span>Nama lengkap pegawai</span></div>
                        <div class="tm-grid-item"><code>{nip}</code><span>NIP pegawai bersangkutan</span></div>
                        <div class="tm-grid-item"><code>{deadline}</code><span>Tanggal jatuh tempo</span></div>
                        <div class="tm-grid-item"><code>{poin}</code><span>Angka kredit / poin</span></div>
                        <div class="tm-grid-item" style="grid-column: 1 / -1;"><code>{next_pangkat}</code><span>Pangkat / golongan tujuan berikutnya</span></div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="tm-footer">
                <button type="button" class="tm-btn-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="tm-btn-submit green">
                    <i class="ph-bold ph-check-circle"></i> Simpan Template
                </button>
            </div>
        </form>
    </div>

    <!-- MODAL EDIT PESAN -->
    <div id="modalEditPesan" class="tm-overlay" onclick="if(event.target===this) closeEditModal()">
        <form id="formEdit" class="tm-card" style="max-width: 680px;">
            <input type="hidden" id="editId" name="id">

            <!-- Header -->
            <div class="tm-header">
                <div class="tm-header-left">
                    <div class="tm-icon-wrap edit">
                        <i class="ph-bold ph-pencil-simple"></i>
                    </div>
                    <div class="tm-title-wrap">
                        <h2>Edit Pengaturan Pesan</h2>
                        <p>Ubah konten atau konfigurasi jadwal notifikasi</p>
                    </div>
                </div>
                <button type="button" class="tm-close-btn" onclick="closeEditModal()">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="tm-body">
                
                <div style="display:grid; grid-template-columns: 2fr 1.5fr 1fr; gap:16px; margin-bottom: 20px;">
                    <div>
                        <label class="tm-label">Nama Notifikasi</label>
                        <input type="text" id="editNama" name="kategori" class="tm-input" required>
                    </div>
                    <div>
                        <label class="tm-label">Jenis Notifikasi</label>
                        <select id="editJenis" name="jenis" class="tm-select" onchange="toggleJadwalEdit()">
                            <option value="Penjadwalan">Otomatis Terjadwal</option>
                            <option value="Template">Manual / Template</option>
                        </select>
                    </div>
                    <div>
                        <label class="tm-label">Jeda (Hari)</label>
                        <input type="number" id="editJadwal" name="interval_hari" class="tm-input" placeholder="0" required>
                    </div>
                </div>

                <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:12px 16px; margin-bottom:20px; display:flex; align-items:flex-start; gap:10px;">
                    <i class="ph-fill ph-warning" style="color:#d97706; font-size:18px; margin-top:2px;"></i>
                    <span style="font-size:12.5px; color:#92400e; line-height: 1.5;">
                        <b>Jeda Otomatis:</b> Isi <b>1</b> = Harian, <b>7</b> = Mingguan, <b>30</b> = Bulanan, <b>365</b> = Tahunan.<br>
                        Isi <b>0</b> jika memilih tipe Manual / Template.
                    </span>
                </div>

                <div class="tm-form-group">
                    <label class="tm-label">Isi Pesan</label>
                    <textarea id="editIsi" name="template_pesan" class="tm-textarea" required></textarea>
                </div>

                <!-- Panduan Placeholder -->
                <div class="tm-guide-box">
                    <div class="tm-guide-header">
                        <i class="ph-fill ph-info"></i>
                        <span>Variabel Placeholder yang Tersedia</span>
                    </div>
                    <div class="tm-grid" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="tm-grid-item"><code>{nama}</code><span>Nama lengkap</span></div>
                        <div class="tm-grid-item"><code>{nip}</code><span>NIP pegawai</span></div>
                        <div class="tm-grid-item"><code>{deadline}</code><span>Jatuh tempo</span></div>
                        <div class="tm-grid-item"><code>{poin}</code><span>Angka kredit</span></div>
                        <div class="tm-grid-item" style="grid-column: span 2;"><code>{next_pangkat}</code><span>Pangkat tujuan berikutnya</span></div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="tm-footer">
                <button type="button" class="tm-btn-cancel" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="tm-btn-submit">
                    <i class="ph-bold ph-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>    </div>

    <!-- MODAL HAPUS PESAN - AWWWARDS CLASS -->
    <style>
        #modalHapusPesan {
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
        #modalHapusPesan.open {
            display: flex;
        }
        .dm-pesan-card {
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
        #modalHapusPesan.open .dm-pesan-card {
            transform: scale(1) translateY(0);
            opacity: 1;
        }
        .dm-pesan-card::after {
            content: '';
            position: absolute;
            bottom: -60px; right: -60px;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(239,68,68,0.05) 0%, transparent 70%);
            pointer-events: none;
        }
        .dm-pesan-icon-wrap {
            position: relative;
            width: 96px;
            height: 96px;
            margin: 0 auto 24px;
        }
        .dm-pesan-icon-wrap::before {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.1);
            animation: dm-pesan-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes dm-pesan-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.08); }
        }
        .dm-pesan-icon-inner {
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
        .dm-pesan-icon-inner i {
            font-size: 48px;
            color: #ef4444;
        }
        .dm-pesan-danger-tag {
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
        .dm-pesan-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 12px;
            letter-spacing: -0.3px;
        }
        .dm-pesan-desc {
            font-size: 14px;
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 28px;
        }
        .dm-pesan-actions {
            display: flex;
            gap: 12px;
        }
        .dm-pesan-btn-cancel {
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
            font-family: 'Poppins', sans-serif;
        }
        .dm-pesan-btn-cancel:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #374151;
            transform: translateY(-1px);
        }
        .dm-pesan-btn-confirm {
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
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .dm-pesan-btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
        }
        .dm-pesan-btn-confirm:active {
            transform: translateY(0);
        }
    </style>

    <div id="modalHapusPesan" onclick="if(event.target===this) closeDeletePesanModal()">
        <div class="dm-pesan-card">
            <div class="dm-pesan-icon-wrap">
                <div class="dm-pesan-icon-inner">
                    <i class="ph-fill ph-warning-circle"></i>
                </div>
            </div>
            <div class="dm-pesan-danger-tag">⚠ Aksi Tidak Dapat Dibatalkan</div>
            <h3 class="dm-pesan-title">Hapus Pesan Ini?</h3>
            <p class="dm-pesan-desc">
                Pesan yang dipilih akan <strong>dihapus permanen</strong> dari sistem
                dan tidak dapat dikembalikan.
            </p>
            <div class="dm-pesan-actions">
                <button class="dm-pesan-btn-cancel" onclick="closeDeletePesanModal()">
                    Batal
                </button>
                <button class="dm-pesan-btn-confirm" onclick="confirmDeletePesan()">
                    <i class="ph-bold ph-trash"></i>
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div id="syncToast" class="toast-notification">
        <i class="ph-bold ph-check-circle" style="font-size: 20px;"></i>
        <span>Sinkronisasi Data Berhasil!</span>
    </div>


@endsection

@section('scripts')
    <script src="{{ asset('js/data-pegawai.js') }}"></script>
    <script>
    let currentDeleteId = null;

    // === TAMBAH ===
    function openModal() {
        document.getElementById('formTambah').reset();
        document.getElementById('modalTambahPesan').classList.add('open');
    }
    function closeModal() {
        document.getElementById('modalTambahPesan').classList.remove('open');
        setTimeout(() => document.getElementById('formTambah').reset(), 250);
    }

    document.getElementById('formTambah').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);

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
        const isSystem = card.getAttribute('data-is-system') === 'true';
        const isi = card.querySelector('.message-content').innerText;

        document.getElementById('editId').value = id;
        
        const editNama = document.getElementById('editNama');
        editNama.value = kategori;
        
        // Kunci Nama Notifikasi jika ini adalah pesan bawaan sistem
        if (isSystem) {
            editNama.readOnly = true;
            editNama.style.backgroundColor = '#f3f4f6';
            editNama.title = 'Nama notifikasi sistem tidak dapat diubah';
        } else {
            editNama.readOnly = false;
            editNama.style.backgroundColor = 'white';
            editNama.title = '';
        }

        document.getElementById('editIsi').value = isi;

        const jenisSelect = document.getElementById('editJenis');
        const jadwalSelect = document.getElementById('editJadwal');

        if (isSystem) {
            // Pesan sistem selalu Otomatis
            jenisSelect.value = 'Penjadwalan'; 
            jenisSelect.disabled = true;
            jenisSelect.style.backgroundColor = '#f3f4f6';
            jenisSelect.title = 'Jenis notifikasi sistem tidak dapat diubah';
            
            // Jika interval 0 pada sistem, berarti ini tipe 'Triggered Once'
            if (interval == 0 || interval == '') {
                jadwalSelect.disabled = true;
                jadwalSelect.style.backgroundColor = "#f3f4f6";
                jadwalSelect.value = "0"; 
                jadwalSelect.title = 'Pesan ini dipicu otomatis oleh sistem (sekali kirim)';
            } else {
                jadwalSelect.disabled = false;
                jadwalSelect.style.backgroundColor = "white";
                jadwalSelect.value = interval;
                jadwalSelect.title = '';
            }

            // tambahkan input hidden karena select yang disabled tidak akan dikirim di form submit
            let hiddenJenis = document.getElementById('hiddenJenisSystem');
            if(!hiddenJenis) {
                hiddenJenis = document.createElement('input');
                hiddenJenis.type = 'hidden';
                hiddenJenis.id = 'hiddenJenisSystem';
                hiddenJenis.name = 'jenis';
                document.getElementById('formEdit').appendChild(hiddenJenis);
            }
            hiddenJenis.value = jenisSelect.value;

        } else {
            // Pesan Non-Sistem (Buatan User)
            if (interval == 0 || interval == '') {
                jenisSelect.value = 'Template';
                jadwalSelect.disabled = true;
                jadwalSelect.style.backgroundColor = "#f3f4f6";
                jadwalSelect.value = "0"; 
            } else {
                jenisSelect.value = 'Penjadwalan';
                jadwalSelect.disabled = false;
                jadwalSelect.style.backgroundColor = "white";
                jadwalSelect.value = interval;
            }

            jenisSelect.disabled = false;
            jenisSelect.style.backgroundColor = 'white';
            jenisSelect.title = '';
            jadwalSelect.title = '';
            
            const hiddenJenis = document.getElementById('hiddenJenisSystem');
            if(hiddenJenis) hiddenJenis.remove();
        }

        document.getElementById('modalEditPesan').classList.add('open');
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
        document.getElementById('modalEditPesan').classList.remove('open');
        setTimeout(() => document.getElementById('formEdit').reset(), 250);
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
        document.getElementById('modalHapusPesan').classList.add('open');
    }

    function closeDeletePesanModal() {
        document.getElementById('modalHapusPesan').classList.remove('open');
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
                            title: 'Area Profil & Notifikasi',
                            description: 'Akses notifikasi dan pengaturan akun Anda di sini.',
                            side: "bottom",
                            align: 'end'
                        }
                    },
                    {
                        element: '.header-actions',
                        popover: {
                            title: 'Manajemen Pesan',
                            description: 'Gunakan fitur ini untuk mencari filter jenis pesan atau menambahkan template pesan baru.',
                            side: "bottom",
                            align: 'center'
                        }
                    },
                    {
                        element: '.config-container',
                        popover: {
                            title: 'Daftar Template Pesan',
                            description: 'Kumpulan notifikasi yang otomatis berjalan atau yang dapat Anda gunakan sebagai template manual.',
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


