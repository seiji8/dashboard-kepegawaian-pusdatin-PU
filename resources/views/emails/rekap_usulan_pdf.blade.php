<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Usulan Kepegawaian</title>
    <style>
        @page {
            margin: 40px 0px 40px 0px; /* Top margin 40px agar halaman 2 tidak nabrak atas, Kiri/Kanan 0 biar header full */
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header-container {
            background-color: #142B6F; /* Base fallback */
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
            padding: 10px 40px 40px 40px; /* Memberi margin pengganti untuk teks di kiri dan kanan */
        }
        
        .section-title {
            color: #1e3a8a;
            font-size: 18px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
            margin-top: 10px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            page-break-inside: auto;
        }
        table.data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: bold;
            font-size: 12px;
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
            font-size: 14px;
        }
        .category-header span {
            float: right;
            background: #ffffff33;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
        }
        .empty-state {
            text-align: center;
            padding: 30px;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            color: #64748b;
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
    @endphp

    <div style="text-align: center; margin-top: -40px; margin-bottom: 20px;">
        @if($headerBase64)
            <!-- Image Header ditarik 40px ke atas menutupi margin bawaan halaman pertama -->
            <img src="{{ $headerBase64 }}" alt="Header DashboardAlert" style="width: 100%; display: block; margin: 0;">
        @else
            <!-- Fallback Text Jika Gambar Tidak Ditemukan -->
            <h1 style="color: #142B6F; margin: 40px 0 0 0;">DashboardAlert</h1>
        @endif
    </div>

    <div class="content-container">
        
        <h2 class="section-title">Detail Pegawai Berdasarkan Kategori</h2>
        
        @if(isset($data['details']) && count($data['details']) > 0)
            @foreach($data['details'] as $kategori => $pegawais)
                <div class="category-header">
                    {{ str_replace('_', ' ', $kategori) }}
                    <span>{{ count($pegawais) }} Orang</span>
                </div>
                <table class="data-table" style="margin-top: 0; border-top: none;">
                    <thead>
                        <tr>
                            <th style="width: 8%; text-align: center;">No</th>
                            <th style="width: 35%;">Nama Lengkap</th>
                            <th style="width: 25%;">NIP</th>
                            <th style="width: 32%;">Jabatan / Tipe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $noDetail = 1; @endphp
                        @foreach($pegawais as $p)
                            <tr>
                                <td style="text-align: center;">{{ $noDetail++ }}</td>
                                <td style="font-weight: bold; color: #1e293b;">{{ $p['nama'] }}</td>
                                <td>{{ $p['nip'] }}</td>
                                <td style="font-size: 11px;">{{ $p['jabatan'] }}</td>
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
