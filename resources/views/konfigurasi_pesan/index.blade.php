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
    <!-- MODAL TAMBAH PESAN -->
    <div id="modalTambahPesan" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2400; justify-content:center; align-items:center;">
        <form id="formTambah" style="background:white; width:600px; max-width:95vw; padding:0; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.2); overflow:hidden; display:flex; flex-direction:column; max-height:90vh;">

            <!-- Header -->
            <div style="padding:20px 25px; border-bottom:1px solid #e2e8f0; background:#f8fafc; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="background:#dcfce7; color:#16a34a; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                        <i class="ph-bold ph-plus" style="font-size:20px;"></i>
                    </div>
                    <div>
                        <h2 style="margin:0; color:#1e293b; font-size:17px; font-weight:700;">Tambah Template Pesan</h2>
                        <p style="margin:2px 0 0; font-size:12px; color:#64748b;">Buat template baru untuk pengingat manual</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal()" style="background:none; border:none; cursor:pointer; color:#94a3b8; transition:color 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">
                    <i class="ph-bold ph-x" style="font-size:20px;"></i>
                </button>
            </div>

            <!-- Body -->
            <div style="padding:25px; overflow-y:auto; flex:1;">
                <input type="hidden" name="interval_hari" value="0">

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; letter-spacing:0.5px;">NAMA TEMPLATE PESAN</label>
                    <input type="text" name="kategori" placeholder="Contoh: Pengingat Berkas SKP" required
                        style="width:100%; padding:11px 14px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:14px; color:#1e293b; outline:none; transition:border 0.2s; box-sizing:border-box; font-family:'Poppins',sans-serif;"
                        onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'"
                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; letter-spacing:0.5px;">ISI PESAN</label>
                    <textarea name="template_pesan" rows="6" placeholder="Tulis template pesan di sini..." required
                        style="width:100%; padding:12px 14px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:14px; color:#1e293b; outline:none; transition:border 0.2s; resize:vertical; box-sizing:border-box; font-family:'Poppins',sans-serif; min-height:130px;"
                        onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'"
                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"></textarea>
                </div>

                <!-- Panduan Placeholder -->
                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:14px 16px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                        <i class="ph-fill ph-info" style="color:#3b82f6; font-size:16px;"></i>
                        <span style="font-size:13px; font-weight:700; color:#1e40af;">Panduan Variabel Placeholder</span>
                    </div>
                    <p style="font-size:12px; color:#3b82f6; margin:0 0 10px 0;">Sisipkan variabel berikut dalam isi pesan Ã¢â‚¬â€ akan otomatis diganti data pegawai saat dikirim:</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px;">
                        <div style="background:white; border-radius:6px; padding:8px 10px; border:1px solid #dbeafe;">
                            <code style="color:#1d4ed8; font-size:12px; font-weight:700;">{nama}</code>
                            <span style="color:#64748b; font-size:11px; display:block; margin-top:2px;">Nama lengkap pegawai</span>
                        </div>
                        <div style="background:white; border-radius:6px; padding:8px 10px; border:1px solid #dbeafe;">
                            <code style="color:#1d4ed8; font-size:12px; font-weight:700;">{nip}</code>
                            <span style="color:#64748b; font-size:11px; display:block; margin-top:2px;">NIP pegawai</span>
                        </div>
                        <div style="background:white; border-radius:6px; padding:8px 10px; border:1px solid #dbeafe;">
                            <code style="color:#1d4ed8; font-size:12px; font-weight:700;">{deadline}</code>
                            <span style="color:#64748b; font-size:11px; display:block; margin-top:2px;">Tanggal jatuh tempo</span>
                        </div>
                        <div style="background:white; border-radius:6px; padding:8px 10px; border:1px solid #dbeafe;">
                            <code style="color:#1d4ed8; font-size:12px; font-weight:700;">{poin}</code>
                            <span style="color:#64748b; font-size:11px; display:block; margin-top:2px;">Angka kredit / poin</span>
                        </div>
                        <div style="background:white; border-radius:6px; padding:8px 10px; border:1px solid #dbeafe; grid-column:1/-1;">
                            <code style="color:#1d4ed8; font-size:12px; font-weight:700;">{next_pangkat}</code>
                            <span style="color:#64748b; font-size:11px; display:block; margin-top:2px;">Pangkat / golongan berikutnya</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div style="padding:18px 25px; border-top:1px solid #e2e8f0; background:#f8fafc; display:flex; justify-content:flex-end; gap:10px; flex-shrink:0;">
                <button type="button" onclick="closeModal()"
                    style="padding:10px 22px; background:white; color:#64748b; border:1.5px solid #e2e8f0; border-radius:8px; cursor:pointer; font-weight:600; font-size:14px; font-family:'Poppins',sans-serif; transition:all 0.2s;"
                    onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">Batal</button>
                <button type="submit"
                    style="padding:10px 22px; background:#16a34a; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:14px; font-family:'Poppins',sans-serif; display:flex; align-items:center; gap:8px; transition:all 0.2s;"
                    onmouseover="this.style.background='#15803d'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#16a34a'; this.style.transform='translateY(0)'">
                    <i class="ph-bold ph-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>

    <!-- MODAL EDIT PESAN -->
    <div id="modalEditPesan" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2400; justify-content:center; align-items:center;">
        <form id="formEdit" style="background:white; width:640px; max-width:95vw; padding:0; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.2); overflow:hidden; display:flex; flex-direction:column; max-height:92vh;">
            <input type="hidden" id="editId" name="id">

            <!-- Header -->
            <div style="padding:20px 25px; border-bottom:1px solid #e2e8f0; background:#f8fafc; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="background:#dbeafe; color:#1e40af; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                        <i class="ph-bold ph-pencil-simple" style="font-size:20px;"></i>
                    </div>
                    <div>
                        <h2 style="margin:0; color:#1e293b; font-size:17px; font-weight:700;">Edit Template Pesan</h2>
                        <p style="margin:2px 0 0; font-size:12px; color:#64748b;">Ubah konten atau jadwal notifikasi</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal()" style="background:none; border:none; cursor:pointer; color:#94a3b8; transition:color 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">
                    <i class="ph-bold ph-x" style="font-size:20px;"></i>
                </button>
            </div>

            <!-- Body -->
            <div style="padding:25px; overflow-y:auto; flex:1;">

                <!-- Row: Nama + Jenis + Jeda -->
                <div style="display:grid; grid-template-columns:3fr 2fr 1fr; gap:14px; margin-bottom:8px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; letter-spacing:0.5px;">NAMA NOTIFIKASI</label>
                        <input type="text" id="editNama" name="kategori" required
                            style="width:100%; padding:11px 14px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:13px; color:#1e293b; outline:none; transition:border 0.2s; box-sizing:border-box; font-family:'Poppins',sans-serif;"
                            onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; letter-spacing:0.5px;">JENIS</label>
                        <select id="editJenis" name="jenis" onchange="toggleJadwalEdit()"
                            style="width:100%; padding:11px 14px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:13px; color:#1e293b; outline:none; background:white; box-sizing:border-box; font-family:'Poppins',sans-serif; cursor:pointer;">
                            <option value="Penjadwalan">Otomatis</option>
                            <option value="Template">Manual / Template</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; letter-spacing:0.5px;">JEDA (HARI)</label>
                        <input type="number" id="editJadwal" name="interval_hari" placeholder="0" required
                            style="width:100%; padding:11px 14px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:13px; color:#1e293b; outline:none; transition:border 0.2s; box-sizing:border-box; font-family:'Poppins',sans-serif;"
                            onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                    </div>
                </div>

                <!-- Info jeda -->
                <div style="background:#fefce8; border:1px solid #fde68a; border-radius:6px; padding:8px 12px; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
                    <i class="ph-fill ph-warning" style="color:#d97706; font-size:14px; flex-shrink:0;"></i>
                    <span style="font-size:12px; color:#92400e;">Isi <b>1</b> = Harian &nbsp;|&nbsp; <b>7</b> = Mingguan &nbsp;|&nbsp; <b>30</b> = Bulanan &nbsp;|&nbsp; <b>365</b> = Tahunan. Isi <b>0</b> untuk Manual / Template.</span>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; letter-spacing:0.5px;">ISI PESAN</label>
                    <textarea id="editIsi" name="template_pesan" rows="6" required
                        style="width:100%; padding:12px 14px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:14px; color:#1e293b; outline:none; transition:border 0.2s; resize:vertical; box-sizing:border-box; font-family:'Poppins',sans-serif; min-height:130px;"
                        onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'"
                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"></textarea>
                </div>

                <!-- Panduan Placeholder -->
                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:14px 16px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                        <i class="ph-fill ph-info" style="color:#3b82f6; font-size:16px;"></i>
                        <span style="font-size:13px; font-weight:700; color:#1e40af;">Panduan Variabel Placeholder</span>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px;">
                        <div style="background:white; border-radius:6px; padding:8px 10px; border:1px solid #dbeafe;">
                            <code style="color:#1d4ed8; font-size:12px; font-weight:700;">{nama}</code>
                            <span style="color:#64748b; font-size:11px; display:block; margin-top:2px;">Nama pegawai</span>
                        </div>
                        <div style="background:white; border-radius:6px; padding:8px 10px; border:1px solid #dbeafe;">
                            <code style="color:#1d4ed8; font-size:12px; font-weight:700;">{nip}</code>
                            <span style="color:#64748b; font-size:11px; display:block; margin-top:2px;">NIP pegawai</span>
                        </div>
                        <div style="background:white; border-radius:6px; padding:8px 10px; border:1px solid #dbeafe;">
                            <code style="color:#1d4ed8; font-size:12px; font-weight:700;">{deadline}</code>
                            <span style="color:#64748b; font-size:11px; display:block; margin-top:2px;">Jatuh tempo</span>
                        </div>
                        <div style="background:white; border-radius:6px; padding:8px 10px; border:1px solid #dbeafe;">
                            <code style="color:#1d4ed8; font-size:12px; font-weight:700;">{poin}</code>
                            <span style="color:#64748b; font-size:11px; display:block; margin-top:2px;">Angka kredit</span>
                        </div>
                        <div style="background:white; border-radius:6px; padding:8px 10px; border:1px solid #dbeafe; grid-column:span 2;">
                            <code style="color:#1d4ed8; font-size:12px; font-weight:700;">{next_pangkat}</code>
                            <span style="color:#64748b; font-size:11px; display:block; margin-top:2px;">Pangkat / golongan berikutnya</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div style="padding:18px 25px; border-top:1px solid #e2e8f0; background:#f8fafc; display:flex; justify-content:flex-end; gap:10px; flex-shrink:0;">
                <button type="button" onclick="closeEditModal()"
                    style="padding:10px 22px; background:white; color:#64748b; border:1.5px solid #e2e8f0; border-radius:8px; cursor:pointer; font-weight:600; font-size:14px; font-family:'Poppins',sans-serif; transition:all 0.2s;"
                    onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">Batal</button>
                <button type="submit"
                    style="padding:10px 22px; background:#1e40af; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:14px; font-family:'Poppins',sans-serif; display:flex; align-items:center; gap:8px; transition:all 0.2s;"
                    onmouseover="this.style.background='#1e3a8a'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#1e40af'; this.style.transform='translateY(0)'">
                    <i class="ph-bold ph-floppy-disk"></i> Simpan Perubahan
                </button>
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


@endsection

@section('scripts')
    <script src="{{ asset('js/data-pegawai.js') }}"></script>
    <script>
    let currentDeleteId = null;

    // === TAMBAH ===
    function openModal() {
        document.getElementById('formTambah').reset();
        document.getElementById('modalTambahPesan').style.display = 'flex';
    }
    function closeModal() {
        document.getElementById('modalTambahPesan').style.display = 'none';
        document.getElementById('formTambah').reset();
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

