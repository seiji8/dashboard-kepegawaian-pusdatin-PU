@extends('layouts.app')

@section('title', 'Dashboard')

@section('page_css')
    <link rel="stylesheet" href="{{ asset('css/pages/dashboard.css') }}">
@endsection

@section('head')
    <!-- Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
@endsection

@section('content')
                {{-- Dashboard Header with Last Sync Badge --}}
                <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
                    <h2 class="page-title-dashboard" style="margin-bottom: 0;">Dashboard</h2>
                    
                    {{-- Last Sync Badge (Awwwards Style - Glassmorphism) --}}
                    <div class="last-sync-badge" style="display: flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); padding: 8px 16px; border-radius: 50px; border: 1px solid rgba(0,0,0,0.06); font-size: 13px; color: #6b7280; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        @if($isDataFresh)
                            {{-- Pulsing Green Dot — Data Fresh (< 24 Jam) --}}
                            <span style="position: relative; display: flex; width: 8px; height: 8px;">
                                <span style="animation: syncPing 1.5s cubic-bezier(0, 0, 0.2, 1) infinite; position: absolute; display: inline-flex; height: 100%; width: 100%; border-radius: 50%; background-color: #34d399; opacity: 0.7;"></span>
                                <span style="position: relative; display: inline-flex; border-radius: 50%; height: 8px; width: 8px; background-color: #10b981;"></span>
                            </span>
                        @else
                            {{-- Gray Dot — Data Lama (> 24 Jam) --}}
                            <span style="display: inline-flex; border-radius: 50%; height: 8px; width: 8px; background-color: #d1d5db;"></span>
                        @endif
                        <span style="font-weight: 500;">
                            <i class="ph ph-arrows-clockwise" style="margin-right: 2px;"></i>
                            Sync: <strong style="color: #1f2937;">{{ $lastSyncTime }}</strong>
                        </span>
                    </div>
                </div>
                <style>@keyframes syncPing { 75%, 100% { transform: scale(2.2); opacity: 0; } }</style>

                {{-- Skeleton: Summary Cards --}}
                <div class="skeleton-layer">
                    <div class="skeleton-summary-cards">
                        @for ($i = 0; $i < 4; $i++)
                        <div class="skeleton-summary-card">
                            <div class="skeleton-box skel-label"></div>
                            <div class="skeleton-box skel-value"></div>
                            <div class="skeleton-box skel-tag"></div>
                        </div>
                        @endfor
                    </div>
                </div>

                {{-- Real: Summary Cards --}}
                <div class="real-content hidden">
                <div class="dashboard-cards">
                    <div class="summary-card">
                        <div class="card-text">
                            <div class="card-header-group">
                                <span class="card-label" style="display:flex; align-items:center;">Total Pegawai
                                    <div class="tooltip">
                                        <span class="icon-help">?</span>
                                        <span class="tooltiptext">Jumlah keseluruhan pegawai aktif yang terdata di sistem.</span>
                                    </div>
                                </span>
                                <h3 class="card-value">{{ $totalPegawai }}</h3>
                            </div>
                            <span class="card-tag">Aktif</span>
                        </div>
                        <div class="card-icon-box">
                            <i class="ph-fill ph-users-three" style="font-size: 24px; color: #fbbf24;"></i>
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="card-text">
                            <div class="card-header-group">
                                <span class="card-label" style="display:flex; align-items:center;">Tingkat Kepatuhan
                                    <div class="tooltip">
                                        <span class="icon-help">?</span>
                                        <span class="tooltiptext">Persentase dokumen wajib persyaratan yang sukses terunggah ke sistem.</span>
                                    </div>
                                </span>
                                <h3 class="card-value">{{ $tingkatKepatuhan }}%</h3>
                            </div>
                            <span class="card-tag">Bulan ini</span>
                        </div>
                        <div class="card-icon-box">
                            <i class="ph-fill ph-chart-bar" style="font-size: 24px; color: #fbbf24;"></i>
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="card-text">
                            <div class="card-header-group">
                                <span class="card-label" style="display:flex; align-items:center;">Tenggat Mendesak
                                    <div class="tooltip">
                                        <span class="icon-help">?</span>
                                        <span class="tooltiptext">Jumlah usulan pegawai yang paling mendekati batas waktu (deadline) pemrosesan.</span>
                                    </div>
                                </span>
                                <h3 class="card-value">{{ $tenggatMendesak }}</h3>
                            </div>
                            <span class="card-tag">Perlu Atensi</span>
                        </div>
                        <div class="card-icon-box">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="13" r="7" />
                                <polyline points="12 10 12 14 15 14" />
                                <line x1="7" y1="5.5" x2="5.5" y2="7" />
                                <line x1="17" y1="5.5" x2="18.5" y2="7" />
                                <path d="M12 4v0" stroke-width="3" stroke-linecap="round" />
                            </svg>
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="card-text">
                            <div class="card-header-group">
                                <span class="card-label" style="display:flex; align-items:center;">Jumlah Usulan
                                    <div class="tooltip">
                                        <span class="icon-help">?</span>
                                        <span class="tooltiptext">Jumlah pegawai yang telah dicetak surat pengajuannya (Proses TTE) dan sedang menunggu di-upload ke E-HRM.</span>
                                    </div>
                                </span>
                                <h3 class="card-value">{{ $jumlahUsulan }}</h3>
                            </div>
                            <span class="card-tag">Sedang Proses</span>
                        </div>
                        <div class="card-icon-box">
                            <i class="ph-fill ph-file-text" style="font-size: 24px; color: #fbbf24;"></i>
                        </div>
                    </div>
                </div>
                </div>

                <div class="task-section">
                    <h3 class="task-section-title" style="margin-bottom: 20px; font-weight: 700; color: #111;">Daftar Tugas yang harus diselesaikan</h3>
                    
                    <div class="task-list">

                        <!-- TASK: KENAIKAN PANGKAT -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-pangkat', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">
                                    {{ $listKenaikanPangkat->count() }}
                                </div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Kenaikan Pangkat</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>

                            <div id="task-pangkat" class="task-sub-container">
                                
                                <!-- Sub: Struktural -->
                                <div class="sub-item">
                                    <div class="sub-task-btn" onclick="toggleSubTask('sub-struktural')">
                                        <span class="sub-badge">{{ $kpStruktural->count() }}</span>
                                        <span style="flex:1;">Jabatan Struktural</span>
                                    </div>
                                    <div id="sub-struktural" class="sub-table-container">

                                        <table class="custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Mulai Notifikasi</th>
                                                    <th>Nama</th>
                                                    <th>Eselon</th>
                                                    <th>Pangkat Saat Ini</th>
                                                    <th>Status</th>
                                                    <th>Dokumen</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($kpStruktural as $item)
                                                <tr>
                                                    <td>{{ $item->tanggal_target ? \Carbon\Carbon::parse($item->tanggal_target)->format('d M Y') : '-' }}</td>
                                                    <td>{{ $item->pegawai->nama }}</td>
                                                    <td>{{ $item->pegawai->nama_eselon }}</td>
                                                    <td>{{ $item->pegawai->pangkat_golongan ?? '-' }}</td>
                                                    <td>
                                                        @if($item->status_saat_ini == 'Usulan')
                                                            <span class="status-badge status-missing">Usulan Pengajuan</span>
                                                        @elseif($item->status_saat_ini == 'Mendekati')
                                                            <span class="status-badge status-warning">Mendekati</span>
                                                        @elseif($item->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">Proses TTE</span>
                                                        @elseif($item->status_saat_ini == 'Upload E-HRM')
                                                            <span class="status-badge status-ok">Upload E-HRM</span>
                                                        @else
                                                            <span class="status-badge status-secondary">{{ $item->status_saat_ini }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php $sisa = $item->dokumen_total - $item->dokumen_terupload; @endphp
                                                        @if($sisa <= 0)
                                                            <span style="color: #16a34a; font-weight: 600;">Lengkap</span>
                                                        @else
                                                            <span style="color: #dc2626; font-weight: 600;">{{ $sisa }} Belum</span>
                                                        @endif
                                                    </td>
                                                    <td style="display: flex; gap: 6px;">
                                                        <button class="btn-action-view" onclick="openDashboardDetail('{{ $item->pegawai->nip }}', '{{ $item->kategori }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                        @if(in_array($item->status_saat_ini, ['Usulan', 'Mendekati']))
                                                        <button class="btn-action-confirm" data-id="{{ $item->id }}" data-nama="{{ $item->pegawai->nama }}" data-kategori="{{ $item->kategori }}" onclick="konfirmasiPerBaris(this, this.dataset.id, this.dataset.nama, this.dataset.kategori)" title="Konfirmasi Usulan ke Proses TTE">
                                                            <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                        </button>
                                                        @elseif($item->status_saat_ini == 'Proses')
                                                        <button class="btn-action-confirm" data-id="{{ $item->id }}" data-nama="{{ $item->pegawai->nama }}" onclick="openConfirmModal(this, this.dataset.id, this.dataset.nama)" title="Konfirmasi TTE Selesai">
                                                            <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                        </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="7" style="padding:0; border:none;"><x-empty-state title="Tugas Struktural Beres!" message="Tidak ada antrean pengajuan Surat Keputusan untuk Jabatan Struktural saat ini." icon="ph-check-circle" /></td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Sub: Fungsional -->
                                <div class="sub-item">
                                    <div class="sub-task-btn" onclick="toggleSubTask('sub-fungsional')">
                                        <span class="sub-badge">{{ $kpFungsional->count() }}</span>
                                        <span style="flex:1;">Jabatan Fungsional</span>
                                    </div>
                                    <div id="sub-fungsional" class="sub-table-container">

                                        <table class="custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Mulai Notifikasi</th>
                                                    <th>Nama</th>
                                                    <th>Pangkat Saat Ini</th>
                                                    <th>Status</th>
                                                    <th>Dokumen</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($kpFungsional as $item)
                                                <tr>
                                                    <td>{{ $item->tanggal_target ? \Carbon\Carbon::parse($item->tanggal_target)->format('d M Y') : '-' }}</td>
                                                    <td>{{ $item->pegawai->nama }}</td>
                                                    <td>{{ $item->pegawai->pangkat_golongan ?? '-' }}</td>
                                                    <td>
                                                        @if($item->status_saat_ini == 'Usulan')
                                                            <span class="status-badge status-missing">Usulan Pengajuan</span>
                                                        @elseif($item->status_saat_ini == 'Mendekati')
                                                            <span class="status-badge status-warning">Mendekati</span>
                                                        @elseif($item->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">Proses TTE</span>
                                                        @elseif($item->status_saat_ini == 'Upload E-HRM')
                                                            <span class="status-badge status-ok">Upload E-HRM</span>
                                                        @else
                                                            <span class="status-badge status-secondary">{{ $item->status_saat_ini }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php $sisa = $item->dokumen_total - $item->dokumen_terupload; @endphp
                                                        @if($sisa <= 0)
                                                            <span style="color: #16a34a; font-weight: 600;">Lengkap</span>
                                                        @else
                                                            <span style="color: #dc2626; font-weight: 600;">{{ $sisa }} Belum</span>
                                                        @endif
                                                    </td>
                                                    <td style="display: flex; gap: 6px;">
                                                        <button class="btn-action-view" onclick="openDashboardDetail('{{ $item->pegawai->nip }}', '{{ $item->kategori }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                        @if(in_array($item->status_saat_ini, ['Usulan', 'Mendekati']))
                                                        <button class="btn-action-confirm" data-id="{{ $item->id }}" data-nama="{{ $item->pegawai->nama }}" data-kategori="{{ $item->kategori }}" onclick="konfirmasiPerBaris(this, this.dataset.id, this.dataset.nama, this.dataset.kategori)" title="Konfirmasi Usulan ke Proses TTE">
                                                            <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                        </button>
                                                        @elseif($item->status_saat_ini == 'Proses')
                                                        <button class="btn-action-confirm" data-id="{{ $item->id }}" data-nama="{{ $item->pegawai->nama }}" onclick="openConfirmModal(this, this.dataset.id, this.dataset.nama)" title="Konfirmasi TTE Selesai">
                                                            <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                        </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="6" style="padding:0; border:none;"><x-empty-state title="Tugas Fungsional Beres!" message="Belum ada fungsional yang harus diproses cetak surat keputusannya." icon="ph-check-circle" /></td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Sub: Reguler -->
                                <div class="sub-item">
                                    <div class="sub-task-btn" onclick="toggleSubTask('sub-reguler')">
                                        <span class="sub-badge">{{ $kpReguler->count() }}</span>
                                        <span style="flex:1;">Reguler</span>
                                    </div>
                                    <div id="sub-reguler" class="sub-table-container">

                                        <table class="custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Mulai Notifikasi</th>
                                                    <th>Nama</th>
                                                    <th>Pangkat Saat Ini</th>
                                                    <th>TMT Pangkat Terakhir</th>
                                                    <th>Status</th>
                                                    <th>Dokumen</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($kpReguler as $item)
                                                <tr>
                                                    <td>{{ $item->tanggal_target ? \Carbon\Carbon::parse($item->tanggal_target)->format('d M Y') : '-' }}</td>
                                                    <td>{{ $item->pegawai->nama }}</td>
                                                    <td>{{ $item->pegawai->pangkat_golongan ?? '-' }}</td>
                                                    <td>{{ $item->pegawai->tmt_pangkat_terakhir ? \Carbon\Carbon::parse($item->pegawai->tmt_pangkat_terakhir)->format('d M Y') : '-' }}</td>
                                                     <td>
                                                        @if($item->status_saat_ini == 'Usulan')
                                                            <span class="status-badge status-missing">Usulan Pengajuan</span>
                                                        @elseif($item->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">Proses TTE</span>
                                                        @elseif($item->status_saat_ini == 'Upload E-HRM')
                                                            <span class="status-badge status-ok">Upload E-HRM</span>
                                                        @else
                                                            <span class="status-badge status-secondary">{{ $item->status_saat_ini }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php $sisa = $item->dokumen_total - $item->dokumen_terupload; @endphp
                                                        @if($sisa <= 0)
                                                            <span style="color: #16a34a; font-weight: 600;">Lengkap</span>
                                                        @else
                                                            <span style="color: #dc2626; font-weight: 600;">{{ $sisa }} Belum</span>
                                                        @endif
                                                    </td>
                                                    <td style="display: flex; gap: 6px;">
                                                        <button class="btn-action-view" onclick="openDashboardDetail('{{ $item->pegawai->nip }}', '{{ $item->kategori }}')" title="Lihat Profil">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                        @if(in_array($item->status_saat_ini, ['Usulan', 'Mendekati']))
                                                        <button class="btn-action-confirm" data-id="{{ $item->id }}" data-nama="{{ $item->pegawai->nama }}" data-kategori="{{ $item->kategori }}" onclick="konfirmasiPerBaris(this, this.dataset.id, this.dataset.nama, this.dataset.kategori)" title="Konfirmasi Usulan ke Proses TTE">
                                                            <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                        </button>
                                                        @elseif($item->status_saat_ini == 'Proses')
                                                        <button class="btn-action-confirm" data-id="{{ $item->id }}" data-nama="{{ $item->pegawai->nama }}" onclick="openConfirmModal(this, this.dataset.id, this.dataset.nama)" title="Konfirmasi TTE Selesai">
                                                            <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                        </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="7" style="padding:0; border:none;"><x-empty-state title="Reguler Aman Terkendali" message="Tidak ada usulan kenaikan pangkat reguler yang antre." icon="ph-check-circle" /></td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- TASK: KENAIKAN JENJANG -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-jenjang', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">{{ $listKenaikanJenjang->count() }}</div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Kenaikan Jenjang</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div id="task-jenjang" class="task-sub-container">
                                <div class="sub-table-container active" style="display:block;">
                                    <div class="surat-btn-row">
                                        <button class="btn-cetak-surat" onclick="openSuratModal('KJ_Jafung')">
                                            <i class="ph-bold ph-file-text"></i> Cetak Surat Pengajuan
                                        </button>
                                    </div>
                                    <table class="custom-table">
                                        <thead>
                                            <tr>
                                                <th>Mulai Notifikasi</th>
                                                <th>Nama</th>
                                                <th>Jenjang Saat Ini</th>
                                                <th>Status</th>
                                                <th>Dokumen</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($listKenaikanJenjang as $item)
                                            <tr>
                                                <td>{{ $item->tanggal_target ? \Carbon\Carbon::parse($item->tanggal_target)->format('d M Y') : '-' }}</td>
                                                <td>{{ $item->pegawai->nama }}</td>
                                                <td>{{ $item->pegawai->jenjang ?? '-' }}</td>
                                                <td>
                                                    @if($item->status_saat_ini == 'Usulan')
                                                        <span class="status-badge status-missing">Usulan Pengajuan</span>
                                                    @elseif($item->status_saat_ini == 'Mendekati')
                                                        <span class="status-badge status-warning">Mendekati</span>
                                                    @elseif($item->status_saat_ini == 'Menunggu UKOM')
                                                        <span class="status-badge status-warning">Pengajuan UKOM</span>
                                                    @elseif($item->status_saat_ini == 'Proses')
                                                        <span class="status-badge status-warning">Proses TTE</span>
                                                    @elseif($item->status_saat_ini == 'Upload E-HRM')
                                                        <span class="status-badge status-ok">Upload E-HRM</span>
                                                    @else
                                                        <span class="status-badge status-secondary">{{ $item->status_saat_ini }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php $sisa = $item->dokumen_total - $item->dokumen_terupload; @endphp
                                                    @if($sisa <= 0)
                                                        <span style="color: #16a34a; font-weight: 600;">Lengkap</span>
                                                    @else
                                                        <span style="color: #dc2626; font-weight: 600;">{{ $sisa }} Belum</span>
                                                    @endif
                                                </td>
                                                <td style="display: flex; gap: 6px; align-items: center;">
                                                    <button class="btn-action-view" onclick="openDashboardDetail('{{ $item->pegawai->nip }}', '{{ $item->kategori }}')" title="Lihat Detail">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                    </button>

                                                    @if($item->status_saat_ini == 'Mendekati' || $item->status_saat_ini == 'Menunggu UKOM')
                                                    <button class="btn-action-confirm" data-id="{{ $item->id }}" data-nama="{{ $item->pegawai->nama }}" onclick="openUkomModal(this.dataset.id, this.dataset.nama)" title="Kirim ke Modul UKOM">
                                                        <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                    </button>
                                                    @endif
                                                    @if($item->status_saat_ini == 'Proses')
                                                    <button class="btn-action-confirm" data-id="{{ $item->id }}" data-nama="{{ $item->pegawai->nama }}" onclick="openUkomModal(this, this.dataset.id, this.dataset.nama)" title="Konfirmasi TTE Selesai">
                                                        <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                    </button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="6" style="padding:0; border:none;"><x-empty-state title="Antrean Jenjang Kosong" message="Tidak ada pegawai yang menanti proses Uji Kompetensi Kenaikan Jenjang." icon="ph-check-circle" /></td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                         <!-- TASK: KGB -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-gaji', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">
                                    {{ $listKGB->count() }}
                                </div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Kenaikan Gaji Berkala</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div id="task-gaji" class="task-sub-container">
                                <div class="sub-table-container active" style="display:block;">
                                    @if($listKGB->count() > 0)

                                        <table class="custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Mulai Notifikasi</th>
                                                    <th>Nama</th>
                                                    <th>TMT KGB Terakhir</th>
                                                    <th>Status</th>
                                                    <th>Dokumen</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($listKGB as $kgb)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($kgb->tanggal_target)->format('d M Y') }}</td>
                                                    <td>{{ $kgb->pegawai->nama }}</td>
                                                    <td>{{ optional($kgb->pegawai->tmt_kgb_terakhir)->format('d M Y') ?? '-' }}</td>
                                                    <td>
                                                        @if($kgb->status_saat_ini == 'Usulan')
                                                            <span class="status-badge status-missing">Usulan Pengajuan</span>
                                                        @elseif($kgb->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">Proses TTE</span>
                                                        @elseif($kgb->status_saat_ini == 'Upload E-HRM')
                                                            <span class="status-badge status-ok">Upload E-HRM</span>
                                                        @else
                                                            <span class="status-badge status-secondary">{{ $kgb->status_saat_ini }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php $sisa = $kgb->dokumen_total - $kgb->dokumen_terupload; @endphp
                                                        @if($sisa <= 0)
                                                            <span style="color: #16a34a; font-weight: 600;">Lengkap</span>
                                                        @else
                                                            <span style="color: #dc2626; font-weight: 600;">{{ $sisa }} Belum</span>
                                                        @endif
                                                    </td>
                                                    <td style="display: flex; gap: 6px;">
                                                        <button class="btn-action-view" onclick="openDashboardDetail('{{ $kgb->pegawai->nip }}', '{{ $kgb->kategori }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                        @if(in_array($kgb->status_saat_ini, ['Usulan', 'Mendekati']))
                                                        <button class="btn-action-confirm" data-id="{{ $kgb->id }}" data-nama="{{ $kgb->pegawai->nama }}" onclick="konfirmasiPerBaris(this, this.dataset.id, this.dataset.nama, 'KGB')" title="Konfirmasi Usulan ke Proses TTE">
                                                            <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                        </button>
                                                        @elseif($kgb->status_saat_ini == 'Proses')
                                                        <button class="btn-action-confirm" data-id="{{ $kgb->id }}" data-nama="{{ $kgb->pegawai->nama }}" onclick="openConfirmModal(this, this.dataset.id, this.dataset.nama)" title="Konfirmasi TTE Selesai">
                                                            <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                        </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <table class="custom-table">
                                            <tr><td style="padding:0; border:none;"><x-empty-state title="KGB Terpantau Sepi" message="Tidak ada jadwal Kenaikan Gaji Berkala untuk bulan ini. Selamat bersantai!" icon="ph-coffee" /></td></tr>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        </div>

                         <!-- TASK: TUBEL -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-tubel', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">{{ $listTubel->count() }}</div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Tugas Belajar dan Pengaktifan Kembali Tubel</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div id="task-tubel" class="task-sub-container">
                                <div class="sub-table-container active" style="display:block;">
                                    <div class="surat-btn-row">
                                        <button class="btn-cetak-surat" onclick="openSuratModal('TUBEL')">
                                            <i class="ph-bold ph-file-text"></i> Cetak Surat Pengajuan
                                        </button>
                                    </div>
                                    <table class="custom-table">
                                        <thead>
                                            <tr>
                                                <th>Tanggal Selesai</th>
                                                <th>Nama</th>
                                                <th>Pangkat / Gol</th>
                                                <th>Keterangan</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($listTubel as $item)
                                            <tr>
                                                <td>{{ $item->tanggal_target ? \Carbon\Carbon::parse($item->tanggal_target)->format('d M Y') : '-' }}</td>
                                                <td>{{ $item->pegawai->nama }}</td>
                                                <td>{{ $item->pegawai->pangkat_golongan ?? '-' }}</td>
                                                <td style="max-width:240px; font-size:12px; color:#6b7280;">{{ $item->keterangan }}</td>
                                                <td>
                                                    @if($item->status_saat_ini == 'Sedang Tubel')
                                                        <span class="status-badge" style="background:#dbeafe; color:#1e40af;">Sedang Tubel</span>
                                                    @elseif($item->status_saat_ini == 'Proses Pengembalian' || $item->status_saat_ini == 'Proses Pengaktifan Kembali' || $item->status_saat_ini == 'Proses Pengaktifan' || $item->status_saat_ini == 'Proses')
                                                        <span class="status-badge status-missing">Proses Pengaktifan Kembali</span>
                                                    @else
                                                        <span class="status-badge status-secondary">{{ $item->status_saat_ini }}</span>
                                                    @endif
                                                </td>
                                                <td style="display:flex; gap:6px;">
                                                    <button class="btn-action-view" onclick="openDashboardDetail('{{ $item->pegawai->nip }}', 'TUBEL')" title="Lihat Detail">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                    </button>
                                                    @if($item->status_saat_ini == 'Proses Pengembalian' || $item->status_saat_ini == 'Proses Pengaktifan Kembali' || $item->status_saat_ini == 'Proses Pengaktifan' || $item->status_saat_ini == 'Proses')
                                                    {{-- Konfirmasi selesai Ã¢â€ â€™ hilang dari dashboard --}}
                                                    <button class="btn-action-confirm" data-id="{{ $item->id }}" data-nama="{{ $item->pegawai->nama }}" onclick="konfirmasiSelesaiTubel(this.dataset.id, this.dataset.nama)" title="Konfirmasi Pengaktifan Kembali Selesai">
                                                        <i class="ph-bold ph-check" style="font-size:15px;"></i>
                                                    </button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="6" style="padding:0; border:none;"><x-empty-state title="Tidak Ada Pegawai Tubel Aktif" message="Tidak ada pegawai yang sedang menjalani atau mendekati selesai Tugas Belajar saat ini." icon="ph-graduation-cap" /></td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                         <!-- TASK: PENDIDIKAN DAN KEAHLIAN (Monitoring Kompetensi) -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-sertifikat', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">
                                    {{ $listMonitoringDiklat->count() }}
                                </div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Pendidikan dan Keahlian</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div id="task-sertifikat" class="task-sub-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Nama</th>
                                            <th>Keterangan</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($listMonitoringDiklat as $item)
                                        <tr>
                                            <td>{{ $item->tanggal_target ? \Carbon\Carbon::parse($item->tanggal_target)->format('d M Y') : '-' }}</td>
                                            <td>{{ $item->pegawai->nama }}</td>
                                            <td style="max-width: 280px;">{{ $item->keterangan }}</td>
                                            <td>
                                                @if($item->kategori == 'DIKLAT_HUTANG')
                                                <span style="font-weight: 600; color: #dc2626;">
                                                @else
                                                <span style="font-weight: 600; color: #d97706;">
                                                @endif
                                                    {{ $item->dokumen_total }} Diklat
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge" style="background: #dcfce7; color: #166534;">Upload E-HRM</span>
                                            </td>
                                            <td>
                                                <button class="btn-action-view" onclick="openDiklatModal('{{ $item->pegawai->nip }}', '{{ $item->kategori }}')" title="Lihat Detail Diklat">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" style="padding:0; border:none;"><x-empty-state title="Diklat Tertib & Bersih" message="Semua jajaran pegawai sudah melapor. Tidak ada temuan hutang atau anomali diklat bulan ini!" icon="ph-certificate" /></td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                         <!-- TASK: KOMPETENSI -->
                        <div class="task-card-wrapper">
                            <div class="task-header" onclick="toggleMainTask('task-kompetensi', this)">
                                <div style="background:#1e3a8a; color:white; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">{{ $listUkom ? $listUkom->count() : 0 }}</div>
                                <span style="font-weight:600; font-size:16px; flex:1;">Uji Kompetensi</span>
                                <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div id="task-kompetensi" class="task-sub-container">
                                <!-- Sub: UKOM Biasa -->
                                <div class="sub-item">
                                    <div class="sub-task-btn" onclick="toggleSubTask('sub-ukom-biasa')">
                                        <span class="sub-badge">{{ isset($ukomBiasa) ? $ukomBiasa->count() : 0 }}</span>
                                        <span style="flex:1;">UKOM</span>
                                    </div>
                                    <div id="sub-ukom-biasa" class="sub-table-container">
                                        <div class="surat-btn-row">
                                            <button class="btn-cetak-surat" onclick="openSuratModal('UKOM')">
                                                <i class="ph-bold ph-file-text"></i> Cetak Surat Pengajuan
                                            </button>
                                        </div>
                                        <table class="custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Nama</th>
                                                    <th>Jenjang Saat Ini</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($ukomBiasa ?? [] as $ukom)
                                                <tr>
                                                    <td>{{ $ukom->pegawai->nama }}</td>
                                                    <td>{{ $ukom->pegawai->jenjang ?? '-' }}</td>
                                                    <td>
                                                        @if($ukom->status_saat_ini == 'Usulan')
                                                            <span class="status-badge status-warning">Proses UKOM</span>
                                                        @elseif($ukom->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">Proses TTE</span>
                                                        @elseif($ukom->status_saat_ini == 'Upload E-HRM')
                                                            <span class="status-badge status-ok">Upload E-HRM</span>
                                                        @else
                                                            <span class="status-badge status-warning">{{ $ukom->status_saat_ini }}</span>
                                                        @endif
                                                    </td>
                                                    <td style="display: flex; gap: 6px;">
                                                        <button class="btn-action-view" onclick="openDashboardDetail('{{ $ukom->pegawai->nip }}', '{{ $ukom->kategori }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                        @if($ukom->status_saat_ini == 'Usulan')
                                                        <button class="btn-action-confirm" data-id="{{ $ukom->id }}" data-nama="{{ $ukom->pegawai->nama }}" onclick="setKelulusanUkom(this.dataset.id, true, this.dataset.nama)" title="Set Lulus UKOM">
                                                            <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                        </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="4" style="padding:0; border:none;"><x-empty-state title="Antrean UKOM Kosong" message="Belum ada satupun antrean peserta Uji Kompetensi yang memerlukan proses pengajuan saat ini." icon="ph-medal" /></td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Sub: UKOM Madya -->
                                <div class="sub-item">
                                    <div class="sub-task-btn" onclick="toggleSubTask('sub-ukom-madya')">
                                        <span class="sub-badge">{{ isset($ukomMadya) ? $ukomMadya->count() : 0 }}</span>
                                        <span style="flex:1;">UKOM Madya</span>
                                    </div>
                                    <div id="sub-ukom-madya" class="sub-table-container">
                                        <div class="surat-btn-row">
                                            <button class="btn-cetak-surat" onclick="openSuratModal('UKOM')">
                                                <i class="ph-bold ph-file-text"></i> Cetak Surat Pengajuan
                                            </button>
                                        </div>
                                        <table class="custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Nama</th>
                                                    <th>Jenjang Saat Ini</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($ukomMadya ?? [] as $ukom)
                                                <tr>
                                                    <td>{{ $ukom->pegawai->nama }}</td>
                                                    <td>{{ $ukom->pegawai->jenjang ?? '-' }}</td>
                                                    <td>
                                                        @if($ukom->status_saat_ini == 'Usulan')
                                                            <span class="status-badge status-warning">Proses UKOM</span>
                                                        @elseif($ukom->status_saat_ini == 'Proses')
                                                            <span class="status-badge status-warning">Proses TTE</span>
                                                        @elseif($ukom->status_saat_ini == 'Upload E-HRM')
                                                            <span class="status-badge status-ok">Upload E-HRM</span>
                                                        @else
                                                            <span class="status-badge status-warning">{{ $ukom->status_saat_ini }}</span>
                                                        @endif
                                                    </td>
                                                    <td style="display: flex; gap: 6px;">
                                                        <button class="btn-action-view" onclick="openDashboardDetail('{{ $ukom->pegawai->nip }}', '{{ $ukom->kategori }}')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </button>
                                                        @if($ukom->status_saat_ini == 'Usulan')
                                                        <button class="btn-action-confirm" data-id="{{ $ukom->id }}" data-nama="{{ $ukom->pegawai->nama }}" onclick="setKelulusanUkom(this.dataset.id, true, this.dataset.nama)" title="Set Lulus UKOM">
                                                            <i class="ph-bold ph-check" style="font-size: 16px;"></i>
                                                        </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="4" style="padding:0; border:none;"><x-empty-state title="UKOM Madya Aman" message="Tidak ada antrean Uji Kompetensi tingkat Madya. Meja kerja Anda terpantau bersih!" icon="ph-medal" /></td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

    <!-- DETAIL MODAL MODERN -->
    <div id="detailModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center;">
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

            <!-- SKELETON LOADING -->
            <link rel="stylesheet" href="{{ asset('css/partials/skeleton.css') }}">
            
            <div id="detailSkeleton" class="modal-modern-body" style="display: none;">
                <!-- Left Skeleton -->
                <div class="skeleton-profile-sidebar">
                    <div class="skeleton-box skeleton-avatar"></div>
                    <div class="skeleton-box skeleton-title" style="margin-top: 15px;"></div>
                    <div class="skeleton-box skeleton-subtitle"></div>
                    <div class="skeleton-box skeleton-text" style="width: 80%; height: 36px; margin-top: 20px; border-radius: 20px;"></div>
                </div>

                <!-- Right Skeleton -->
                <div class="skeleton-info-section">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        @for ($i = 0; $i < 8; $i++)
                        <div>
                            <div class="skeleton-box skeleton-text" style="width: 30%; height: 10px; margin-bottom: 5px;"></div>
                            <div class="skeleton-box skeleton-text" style="width: 70%; height: 16px;"></div>
                        </div>
                        @endfor
                    </div>
                    <div class="skeleton-box skeleton-text" style="width: 100%; height: 100px; border-radius: 8px;"></div>
                </div>
            </div>

            <!-- Fallback Spinner (Optional, hidden by JS logic usually) -->
            <div id="detailLoading" style="text-align: center; padding: 50px; display: none;">
                <i class="ph-bold ph-spinner" style="font-size: 40px; color: #0f172a; animation: spin 1s linear infinite;"></i>
                <p style="margin-top: 10px; color: #6b7280;">Memuat data...</p>
            </div>

<div id="modalContentBody" class="modal-modern-body" style="display: none;">
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
                        <div class="info-item"><label>NIP / ID</label><span id="detNIP">-</span></div>
                        <div class="info-item"><label>EMAIL</label><span id="detEmail">-</span></div>
                        <div class="info-item"><label>NO. HP</label><span id="detHP">-</span></div>
                        <div class="info-item"><label>TIPE JABATAN</label><span id="detTipeJabatan">-</span></div>
                        <div class="info-item"><label>PANGKAT / GOLONGAN</label><span id="detPangkat">-</span></div>
                        <div class="info-item"><label>JENJANG</label><span id="detJenjang">-</span></div>
                        <div class="info-item"><label>TMT CPNS</label><span id="detTmt">-</span></div>
                        <div class="info-item"><label>ANGKA KREDIT</label><span id="detKredit">-</span></div>
                    </div>

                    <div class="doc-section borderless">
                        <div class="doc-section-title">
                            <i class="ph-fill ph-file-text" style="color: #4b5563;"></i>
                            Dokumen Wajib
                        </div>
                        <div id="docStatusContainer">
                            <!-- Injected by JS -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL DASHBOARD DETAIL (KHUSUS KATEGORI) -->
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
            background: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 20px 20px;
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
    <div id="dashboardDetailModal" class="tm-overlay" onclick="if(event.target===this) closeDashboardDetail()">
        <div class="tm-card" style="max-width: 860px; width: 95vw;">
            
            <!-- Header -->
            <div class="tm-header" style="padding-bottom: 20px;">
                <div class="tm-header-left">
                    <div id="dashModalAvatar" class="tm-icon-wrap" style="width: 50px; height: 50px; border-radius: 50%; font-size: 18px; font-weight: 700; background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.3);">
                    </div>
                    <div class="tm-title-wrap">
                        <h2 id="dashModalNama" style="font-size: 18px; margin-bottom: 6px; margin-top: 0; color: #ffffff;">Memuat...</h2>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span id="dashModalKategori" style="font-size:11px; font-weight:700; color:#1e3a8a; background:rgba(255,255,255,0.9); padding:4px 10px; border-radius:12px; line-height:1; letter-spacing: 0.5px; text-transform: uppercase;">-</span>
                            <div style="width:4px; height:4px; background-color:rgba(255,255,255,0.5); border-radius:50%;"></div>
                            <span id="dashModalNip" style="font-size:13px; font-weight:600; color:rgba(255,255,255,0.85); line-height:1; font-family: monospace;">-</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="tm-close-btn" onclick="closeDashboardDetail()">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>
            
            <!-- PROGRESS TRACKER STYLES -->
            <style>
                .tracker-step { display:flex; flex-direction:column; align-items:center; justify-content:flex-start; position:relative; z-index:1; flex:1; min-width:0; }
                .tracker-step .circle { width:30px; height:30px; border-radius:50%; background:#eff6ff; display:flex; align-items:center; justify-content:center; border:2px solid #bfdbfe; z-index:2; position:relative; transition:all 0.3s; flex-shrink:0; }
                .tracker-step.done .circle { background:#3b82f6; border-color:#3b82f6; }
                .tracker-step.done .circle::after { content:''; width:9px; height:9px; background:#fff; border-radius:50%; }
                .tracker-step.active .circle { background:#bfdbfe; border-color:#3b82f6; box-shadow:0 0 0 4px #eff6ff; }
                .tracker-step.active-inner .circle::after { content:''; width:11px; height:11px; background:#3b82f6; border-radius:50%; }
                
                .tracker-step .label { font-size:11px; font-weight:700; color:#1e293b; margin-top:8px; text-align:center; transition:color 0.3s; line-height:1.2; }
                .tracker-step .sub-label { font-size:9.5px; color:#64748b; text-align:center; margin-top:3px; line-height:1.3; padding:0 3px; }
                
                .tracker-step:not(.done):not(.active) .label { color:#94a3b8; }
                .tracker-step:not(.done):not(.active) .sub-label { color:#cbd5e1; }
                
                .tracker-line { height:3px; flex:1; align-self:flex-start; margin-top:13px; z-index:0; transition:all 0.3s; min-width:20px; }
                .tracker-line.done { background:#3b82f6; }
                .tracker-line.dashed { background:repeating-linear-gradient(90deg, #bfdbfe 0, #bfdbfe 6px, transparent 6px, transparent 14px); }
            </style>

            <!-- Body -->
            <div class="tm-body" style="padding: 24px 28px; overflow-y: auto; max-height: 55vh; background: #ffffff;">
                
                <!-- Progress Tracker moved inside body for scroll -->
                <div id="dashModalTrackerContainer" style="display:none; width: 100%; margin-bottom: 25px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                    <h4 style="font-size:12px; font-weight:700; color:#64748b; margin:0 0 15px 0; text-align:center; text-transform: uppercase; letter-spacing: 0.5px;">Progres Status Usulan</h4>
                    <div id="dashModalTracker" style="display:flex; align-items:flex-start; width:100%; margin:0 auto;">
                        <!-- Tracker steps will be injected by JS -->
                    </div>
                </div>

                <div id="dashModalLoading" style="text-align:center; padding:40px; color:#64748b;">
                    <i class="ph-bold ph-spinner" style="font-size:32px; color:#1e3a8a; animation:spin 1s linear infinite;"></i>
                    <p style="margin-top:12px; font-weight: 500;">Mengambil detail usulan...</p>
                </div>

                <div id="dashModalContentBody" style="display:none;">
                    <!-- Info Grid -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:25px; background:#ffffff; padding:15px; border-radius:12px; border:1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <div>
                            <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">EMAIL</div>
                            <div id="dashModalEmail" style="font-size:14px; color:#0f172a; font-weight:500;">-</div>
                        </div>
                        
                        <!-- Dynamic fields based on category -->
                        <div id="dashModalAKWrapper" style="display:none;">
                            <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">ANGKA KREDIT</div>
                            <div id="dashModalAK" style="font-size:14px; color:#0f172a; font-weight:500;">-</div>
                        </div>
                        <div id="dashModalKGBWrapper" style="display:none;">
                            <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">PROYEKSI KGB</div>
                            <div id="dashModalKGB" style="font-size:14px; color:#0f172a; font-weight:500;">-</div>
                        </div>
                        <div id="dashModalPangkatWrapper" style="display:none;">
                            <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">PANGKAT/GOLONGAN</div>
                            <div id="dashModalPangkat" style="font-size:14px; color:#0f172a; font-weight:500;">-</div>
                        </div>
                        <div id="dashModalKeteranganWrapper" style="display:none; grid-column: 1 / -1; margin-top: 5px;">
                            <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">KETERANGAN PENGUSULAN</div>
                            <div id="dashModalKeterangan" style="font-size:13px; color:#166534; font-weight:600; background:#dcfce7; padding:10px 15px; border-radius:8px; border:1px solid #bbf7d0;">-</div>
                        </div>
                    </div>
                    
                    <!-- KGB Extra Info -->
                    <div id="dashModalKgbInfoWrapper" style="display:none; margin-bottom:25px;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                            <i class="ph-fill ph-wallet" style="color:#1e40af; font-size:18px;"></i>
                            <h4 style="margin:0; font-size:15px; font-weight:700; color:#1e293b;">Informasi Kenaikan Gaji Berkala</h4>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; background:#f1f5f9; padding:15px; border-radius:8px; border:1px solid #e2e8f0;">
                            <div>
                                <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">TMT KGB TERAKHIR</div>
                                <div id="dashModalKgbTmtLama" style="font-size:14px; color:#0f172a; font-weight:600; display:flex; align-items:center; gap:6px;">
                                    <i class="ph-fill ph-calendar-check" style="color:#10b981; font-size:14px;"></i> -
                                </div>
                            </div>
                            <div>
                                <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">TARGET KGB BERIKUTNYA</div>
                                <div id="dashModalKgbTmtBaru" style="font-size:14px; color:#0f172a; font-weight:600; display:flex; align-items:center; gap:6px;">
                                    <i class="ph-fill ph-calendar-plus" style="color:#3b82f6; font-size:14px;"></i> -
                                </div>
                            </div>
                            <div>
                                <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">PANGKAT / GOLONGAN</div>
                                <div id="dashModalKgbGolongan" style="font-size:14px; color:#0f172a; font-weight:600; display:flex; align-items:center; gap:6px;">
                                    <i class="ph-fill ph-medal" style="color:#f59e0b; font-size:14px;"></i> -
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TUBEL Extra Info (hidden by default, shown via JS for TUBEL) -->
                    <div id="dashModalTubelWrapper" style="display:none; margin-bottom:25px;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                            <i class="ph-fill ph-graduation-cap" style="color:#1e40af; font-size:18px;"></i>
                            <h4 style="margin:0; font-size:15px; font-weight:700; color:#1e293b;">Informasi Tugas Belajar</h4>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; background:#f1f5f9; padding:15px; border-radius:8px; border:1px solid #e2e8f0;">
                            <div>
                                <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">TANGGAL MULAI</div>
                                <div id="dashModalTubelMulai" style="font-size:14px; color:#0f172a; font-weight:600; display:flex; align-items:center; gap:6px;">
                                    <i class="ph-fill ph-calendar-check" style="color:#16a34a; font-size:14px;"></i> -
                                </div>
                            </div>
                            <div>
                                <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">TANGGAL SELESAI</div>
                                <div id="dashModalTubelSelesai" style="font-size:14px; color:#0f172a; font-weight:600; display:flex; align-items:center; gap:6px;">
                                    <i class="ph-fill ph-calendar-x" style="color:#dc2626; font-size:14px;"></i> -
                                </div>
                            </div>
                            <div>
                                <div style="font-size:11px; color:#64748b; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">PENDIDIKAN</div>
                                <div id="dashModalTubelPendidikan" style="font-size:14px; color:#0f172a; font-weight:600; display:flex; align-items:center; gap:6px;">
                                    <i class="ph-fill ph-book-open" style="color:#7c3aed; font-size:14px;"></i> -
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Document Requirements -->
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:15px;">
                        <i class="ph-fill ph-check-square-offset" style="color:#1e40af; font-size:18px;"></i>
                        <h4 style="margin:0; font-size:15px; font-weight:700; color:#1e293b;">Dokumen Persyaratan</h4>
                    </div>
                    
                    <div id="dashModalDocsContainer" style="display:flex; flex-direction:column; gap:10px;">
                        <!-- Injected by JS -->
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div id="dashModalFooter" class="tm-footer" style="display:none; justify-content:flex-end;">
                <button class="tm-btn-submit" onclick="openReminderModal()" style="width:auto; display:flex; align-items:center; gap:8px;">
                    <i class="ph-bold ph-bell-ringing"></i> Kirim Pengingat
                </button>
            </div>

        </div>
    </div>

    <!-- REMINDER MODAL -->
    <div id="reminderModal" class="tm-overlay" onclick="if(event.target===this) closeReminderModal()">
        <div class="tm-card" style="max-width: 600px; max-height: 90vh;">
            
            <!-- Header -->
            <div class="tm-header">
                <div class="tm-header-left">
                    <div class="tm-icon-wrap" style="background: rgba(255,255,255,0.2); border-color: rgba(255,255,255,0.4);">
                        <i class="ph-bold ph-bell-ringing"></i>
                    </div>
                    <div class="tm-title-wrap">
                        <h2>Kirim Pengingat Manual</h2>
                        <p>Kirimkan notifikasi langsung ke pegawai terkait</p>
                    </div>
                </div>
                <button type="button" class="tm-close-btn" onclick="closeReminderModal()">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="tm-body" style="overflow-y: auto;">
                <style>
                    #reminderModal .ts-wrapper { margin-bottom: 20px; }
                    #reminderModal .ts-control { border-radius: 10px !important; border-color: #cbd5e1 !important; padding: 12px 15px !important; font-size: 14px !important; background: #f8fafc !important; }
                    #reminderModal .ts-control:focus-within { border-color: #3b82f6 !important; box-shadow: 0 0 0 3px rgba(59,130,246,0.1) !important; background: #ffffff !important; }
                    #reminderModal .ts-dropdown { border-radius: 10px !important; border-color: #cbd5e1 !important; box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
                </style>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 8px; letter-spacing: 0.5px; text-transform: uppercase;">PILIH TEMPLATE PESAN</label>
                <select id="reminderTemplate" style="width: 100%; padding: 12px 15px; border: 1.5px solid #cbd5e1; border-radius: 10px; margin-bottom: 20px; color: #1e293b; font-size: 14px; outline: none; transition: all 0.2s; background: #f8fafc;" onchange="toggleMessageMode()" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'; this.style.background='#ffffff'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'; this.style.background='#f8fafc'">
                    <option value="" disabled selected>Pilih Template Pengingat</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->kategori }}</option>
                    @endforeach
                </select>

                <div style="display: flex; align-items: center; margin-bottom: 24px; background: #eff6ff; padding: 14px 16px; border-radius: 10px; border: 1px solid #dbeafe;">
                    <input type="checkbox" id="checkCustom" onchange="toggleMessageMode()" style="margin-right: 12px; width: 18px; height: 18px; cursor: pointer; accent-color: #3b82f6;">
                    <label for="checkCustom" style="font-size: 13.5px; font-weight: 600; color: #1e40af; cursor: pointer; user-select: none;">Gunakan pesan custom atau edit manual?</label>
                </div>

                <label style="display: block; font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 8px; letter-spacing: 0.5px; text-transform: uppercase;">ISI PESAN</label>
                <textarea id="reminderMessage" disabled style="width: 100%; height: 140px; padding: 15px; border: 1.5px solid #cbd5e1; border-radius: 10px; margin-bottom: 10px; resize: none; font-size: 14px; color: #1e293b; outline: none; transition: all 0.2s; background: #f1f5f9; font-family: inherit; line-height: 1.5;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'; this.style.background='#ffffff'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'; if(this.disabled) this.style.background='#f1f5f9'"></textarea>
            </div>

            <!-- Footer -->
            <div class="tm-footer">
                <button type="button" class="tm-btn-cancel" onclick="closeReminderModal()">Batal</button>
                <button type="button" class="tm-btn-submit" id="btnSendManual" onclick="sendReminder()">
                    <i class="ph-bold ph-paper-plane-right"></i> Kirim Pesan
                </button>
            </div>

        </div>
    </div>

    <!-- CONFIRM MODAL -->
    <div id="confirmModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2200; justify-content: center; align-items: center;">
        <div class="confirm-modal-content">
            <div class="confirm-modal-icon">
                <i class="ph-fill ph-check-circle" style="font-size: 48px; color: #10b981;"></i>
            </div>
            <h3 class="confirm-modal-title">Konfirmasi Tugas</h3>
            <p class="confirm-modal-text">Apakah Anda yakin sudah mengajukan KGB untuk:</p>
            <p class="confirm-modal-name" id="confirmPegawaiName">-</p>
            <div class="confirm-modal-actions">
                <button class="confirm-btn-cancel" onclick="closeConfirmModal()">Batal</button>
                <button class="confirm-btn-yes" id="confirmYesBtn" onclick="submitConfirm()">Ya, Sudah Diproses</button>
            </div>
        </div>
    </div>

    <!-- UKOM MODAL -->
    <div id="ukomModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2200; justify-content: center; align-items: center;">
        <div class="confirm-modal-content" style="text-align: center;">
            <div class="confirm-modal-icon" style="background:#dbeafe; width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin: 0 auto 20px auto;">
                <i class="ph-fill ph-medal" style="font-size: 48px; color: #2563eb;"></i>
            </div>
            <h3 class="confirm-modal-title">Daftarkan Uji Kompetensi</h3>
            <p class="confirm-modal-text">Pindahkan pegawai berikut ke kategori Uji Kompetensi (UKOM) dan kirimkan notifikasi pemberitahuan kepadanya via email?</p>
            <p class="confirm-modal-name" id="ukomPegawaiName" style="color:#0f172a; font-weight:700; margin-top:10px;">-</p>
            <div class="confirm-modal-actions">
                <button class="confirm-btn-cancel" onclick="closeUkomModal()">Batal</button>
                <button class="confirm-btn-yes" id="ukomYesBtn" onclick="submitUkom()" style="background:#16a34a; color:white;">Ya, Daftarkan UKOM</button>
            </div>
        </div>
    </div>

    @include('dashboard.partials.modal_diklat')

    <script>
    function openDiklatModal(nip, kategori) {
        if (typeof currentDetailNip !== 'undefined') currentDetailNip = nip;
        
        const modal = document.getElementById('diklatModal');
        const loading = document.getElementById('diklatModalLoading');
        const table = document.getElementById('diklatModalTable');
        const body = document.getElementById('diklatModalBody');

        modal.style.display = 'flex';
        loading.style.display = 'block';
        table.style.display = 'none';
        body.innerHTML = '';

        const label = kategori === 'DIKLAT_HUTANG' ? 'Sertifikat Belum Diupload' : 'Dokumen Belum Lengkap';

        fetch(`/dashboard/diklat-detail/${nip}/${kategori}`)
            .then(res => res.json())
            .then(data => {
                loading.style.display = 'none';
                table.style.display = 'table';
                document.getElementById('diklatModalTitle').textContent = data.pegawai;
                document.getElementById('diklatModalSub').textContent = `NIP: ${data.nip} - ${data.total} diklat (${label})`;

                data.data.forEach((d, i) => {
                    const arsipClass = d.arsip === 'Ada'
                        ? 'style="color:#166534; font-weight:600; white-space:nowrap; text-align:center;"'
                        : 'style="color:#dc2626; font-weight:600; white-space:nowrap; text-align:center;"';
                    body.innerHTML += `
                        <tr>
                            <td>${i + 1}</td>
                            <td style="max-width:200px; font-weight:500;">${d.nama_diklat}</td>
                            <td style="white-space:nowrap; font-size:12px;">${d.tanggal_mulai}<br>s/d ${d.tanggal_selesai}</td>
                            <td><span style="background:#e0e7ff; color:#3730a3; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600;">${d.jenis}</span></td>
                            <td style="font-size:12px;">${d.sertifikat}</td>
                            <td ${arsipClass}>${d.arsip}</td>
                        </tr>`;
                });

                loading.style.display = 'none';
                table.style.display = 'table';
            })
            .catch(() => {
                loading.innerHTML = '<p style="color:#dc2626;">Gagal memuat data.</p>';
            });
    }


    function closeDiklatModal() {
        document.getElementById('diklatModal').style.display = 'none';
    }

    document.getElementById('diklatModal').addEventListener('click', function(e) {
        if (e.target === this) closeDiklatModal();
    });
    </script>

    @include('dashboard.partials.modal_surat')
    @include('dashboard.partials.modal_konfirmasi')
@endsection

@section('scripts')
    <script src="{{ asset('js/dashboard-ui.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/dashboard-tracker-builder.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/dashboard-modals-data.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/dashboard-actions.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/dashboard-surat.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/dashboard-lampiran.js') }}?v={{ time() }}"></script>
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
                            description: 'Dari sudut sini, Anda bisa mengecek Lonceng Notifikasi yang masuk, mengganti kata sandi, atau mengakses tombol [?] ini lagi jika butuh panduan.',
                            side: "bottom",
                            align: 'end'
                        }
                    },
                    {
                        element: '.dashboard-cards',
                        popover: {
                            title: 'Statistik Instan 📊',
                            description: 'Empat kartu ini memberikan Anda pandangan terhadap ringkasan status administrasi seluruh pegawai saat ini.',
                            side: "bottom",
                            align: 'center'
                        }
                    },
                    {
                        element: '.task-section',
                        popover: {
                            title: 'Daftar Tugas Utama 📋',
                            description: 'Di sinilah pusat operasi Anda. Seluruh antrean pegawai yang butuh pemrosesan berkas (KGB, KP, KJ, & dll) akan dikumpulkan rapi di berbagai tabel ini.',
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


