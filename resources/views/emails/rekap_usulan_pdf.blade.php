<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Usulan Kepegawaian</title>
    <style>
        @page {
            margin: 40px 0px 40px 0px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header-container {
            background-color: #142B6F;
            background: linear-gradient(180deg, #142B6F 0%, #6176B3 100%);
            padding: 20px 40px;
            color: white;
            text-align: center;
        }
        .header-table {
            width: 100%;
            margin: 0 auto;
        }
        .header-logo {
            width: 60px;
            height: auto;
        }
        .header-title-container {
            text-align: left;
            padding-left: 15px;
            vertical-align: middle;
        }
        .header-title {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .header-title .highlight {
            color: #fbbf24;
        }
        .header-subtitle {
            margin: 5px 0 0;
            font-size: 13px;
            opacity: 0.9;
            line-height: 1.4;
        }
        
        .content-container {
            padding: 10px 40px 40px 40px;
        }

        /* Info Box */
        .info-box {
            background-color: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 25px;
        }
        .info-box p {
            margin: 3px 0;
            font-size: 12px;
            color: #374151;
        }
        .info-box strong {
            color: #1e3a8a;
        }

        /* Summary Table */
        .section-title {
            color: #1e3a8a;
            font-size: 16px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
            margin-top: 10px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        table.summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table.summary-table th, table.summary-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            text-align: left;
        }
        table.summary-table th {
            background-color: #1e3a8a;
            color: white;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        table.summary-table td {
            font-size: 12px;
        }
        table.summary-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge-count {
            display: inline-block;
            background-color: #1e3a8a;
            color: white;
            padding: 2px 10px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 12px;
        }

        /* Detail Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            page-break-inside: auto;
        }
        table.data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 10px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .category-header {
            background-color: #1e3a8a;
            color: white;
            padding: 8px 15px;
            font-weight: bold;
            border-radius: 4px 4px 0 0;
            margin-top: 20px;
            margin-bottom: 0;
            font-size: 13px;
        }
        .category-header span {
            float: right;
            background: #ffffff33;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
        }
        .empty-state {
            text-align: center;
            padding: 30px;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            color: #64748b;
        }
        .new-badge {
            display: inline-block;
            background-color: #dc2626;
            color: white;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            vertical-align: middle;
            margin-left: 4px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    @php
        $headerPath = public_path('assets/header_email.png');
        $headerBase64 = file_exists($headerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerPath)) : '';
        
        $kategoriLabels = [
            'KGB' => 'Kenaikan Gaji Berkala',
            'KP_Jafung' => 'Kenaikan Pangkat Fungsional',
            'KJ_Jafung' => 'Kenaikan Jenjang / UKOM Fungsional',
            'KP_Struktural' => 'Kenaikan Pangkat Struktural',
            'KP_Reguler' => 'Kenaikan Pangkat Reguler (Pelaksana)',
        ];

        // Cek daftar NIP pegawai baru (dari $data['new_usulan'])
        $newNips = [];
        if (isset($data['new_usulan'])) {
            foreach ($data['new_usulan'] as $nu) {
                $newNips[$nu['nip'] . '_' . $nu['kategori']] = true;
            }
        }

        $totalSemuaPegawai = 0;
        if (isset($data['details'])) {
            foreach ($data['details'] as $pegawais) {
                $totalSemuaPegawai += count($pegawais);
            }
        }
    @endphp

    <div style="text-align: center; margin-top: -40px; margin-bottom: 20px;">
        @if($headerBase64)
            <img src="{{ $headerBase64 }}" alt="Header DashboardAlert" style="width: 100%; display: block; margin: 0;">
        @else
            <h1 style="color: #142B6F; margin: 40px 0 0 0;">DashboardAlert</h1>
        @endif
    </div>

    <div class="content-container">

        {{-- Info Box --}}
        <div class="info-box">
            <p><strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y - HH:mm') }} WIB</p>
            <p><strong>Total Kategori Usulan:</strong> {{ isset($data['summary']) ? count($data['summary']) : 0 }} Kategori</p>
            <p><strong>Total Pegawai Teregistrasi:</strong> {{ $totalSemuaPegawai }} Orang</p>
            @if(isset($data['new_usulan']) && count($data['new_usulan']) > 0)
                <p><strong>Usulan Baru Hari Ini:</strong> <span style="color: #dc2626; font-weight: bold;">{{ count($data['new_usulan']) }} Pegawai</span></p>
            @endif
        </div>

        {{-- SECTION 1: RINGKASAN TOTAL PER KATEGORI --}}
        <h2 class="section-title">Ringkasan Total Usulan</h2>

        @if(isset($data['summary']) && count($data['summary']) > 0)
            <table class="summary-table">
                <thead>
                    <tr>
                        <th style="width: 8%; text-align: center;">No</th>
                        <th style="width: 52%;">Kategori Usulan</th>
                        <th style="width: 20%; text-align: center;">Jumlah Pegawai</th>
                        <th style="width: 20%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $noSummary = 1; @endphp
                    @foreach($data['summary'] as $kategori => $jumlah)
                        <tr>
                            <td style="text-align: center;">{{ $noSummary++ }}</td>
                            <td style="font-weight: bold;">{{ $kategoriLabels[$kategori] ?? str_replace('_', ' ', $kategori) }}</td>
                            <td style="text-align: center;"><span class="badge-count">{{ $jumlah }}</span></td>
                            <td style="text-align: center; color: #b45309; font-weight: bold;">Menunggu Proses</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <p><strong>Tidak ada antrean usulan saat ini.</strong></p>
            </div>
        @endif

        {{-- SECTION 2: DETAIL RINCI PER KATEGORI --}}
        <h2 class="section-title">Detail Pegawai Berdasarkan Kategori</h2>
        
        @if(isset($data['details']) && count($data['details']) > 0)
            @foreach($data['details'] as $kategori => $pegawais)
                <div class="category-header">
                    {{ $kategoriLabels[$kategori] ?? str_replace('_', ' ', $kategori) }}
                    <span>{{ count($pegawais) }} Orang</span>
                </div>
                <table class="data-table" style="margin-top: 0; border-top: none;">
                    <thead>
                        <tr>
                            <th style="width: 5%; text-align: center;">No</th>
                            <th style="width: 22%;">Nama Lengkap</th>
                            <th style="width: 16%;">NIP</th>
                            <th style="width: 11%;">Pangkat/Gol</th>
                            <th style="width: 20%;">Jabatan</th>
                            <th style="width: 12%;">TMT Target</th>
                            <th style="width: 14%;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $noDetail = 1; @endphp
                        @foreach($pegawais as $p)
                            @php
                                $isNew = isset($newNips[$p['nip'] . '_' . $kategori]);
                            @endphp
                            <tr>
                                <td style="text-align: center;">{{ $noDetail++ }}</td>
                                <td style="font-weight: bold; color: #1e293b;">
                                    {{ $p['nama'] }}
                                    @if($isNew)
                                        <span class="new-badge">BARU</span>
                                    @endif
                                </td>
                                <td style="font-size: 11px;">{{ $p['nip'] }}</td>
                                <td style="font-size: 11px; text-align: center;">{{ $p['pangkat_golongan'] ?? '-' }}</td>
                                <td style="font-size: 10px;">{{ $p['jabatan'] }}</td>
                                <td style="font-size: 11px; text-align: center;">{{ $p['tmt_target'] ?? '-' }}</td>
                                <td style="font-size: 10px;">{{ $p['keterangan'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @else
            <div class="empty-state">
                <p><strong>Tidak ada antrean usulan.</strong></p>
                <p>Saat ini seluruh data pegawai terpantau aman dan tidak ada dokumen yang menunggu verifikasi admin.</p>
            </div>
        @endif

        <div class="footer">
            <p>Dokumen ini dihasilkan secara otomatis oleh sistem <strong>DashboardAlert Kepegawaian</strong>.</p>
            <p>Rahasia & Terbatas - Hanya untuk Penggunaan Internal Kepegawaian</p>
        </div>
    </div>
</body>
</html>
