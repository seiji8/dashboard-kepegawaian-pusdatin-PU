<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Pengajuan {{ $data['kategori_label'] }}</title>
    <style>
        @page {
            margin: 30px 50px 40px 50px;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* =========================================
           KOP SURAT
           ========================================= */
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .kop-surat .instansi {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .kop-surat .sub-instansi {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-surat .alamat {
            font-size: 9pt;
            color: #333;
            margin-top: 2px;
        }
        .kop-logo-table {
            width: 100%;
            margin-bottom: 5px;
        }
        .kop-logo-table td {
            vertical-align: middle;
        }
        .kop-logo {
            width: 70px;
            height: auto;
        }

        /* =========================================
           HEADER SURAT
           ========================================= */
        .surat-header {
            margin-bottom: 20px;
        }
        .surat-header table {
            width: 100%;
        }
        .surat-header td {
            vertical-align: top;
            padding: 2px 0;
            font-size: 12pt;
        }
        .surat-header .label {
            width: 100px;
        }
        .surat-header .separator {
            width: 15px;
            text-align: center;
        }

        /* =========================================
           BODY SURAT
           ========================================= */
        .surat-body {
            margin-bottom: 20px;
            text-align: justify;
        }
        .surat-body p {
            margin: 0 0 10px 0;
            text-indent: 40px;
        }

        /* =========================================
           TABEL DATA PEGAWAI
           ========================================= */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }
        .data-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
        }
        .data-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 10pt;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #fafafa;
        }

        /* =========================================
           TANDA TANGAN
           ========================================= */
        .ttd-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .ttd-section table {
            width: 100%;
        }
        .ttd-right {
            text-align: center;
            width: 45%;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
        }

        /* =========================================
           FOOTER / TEMBUSAN
           ========================================= */
        .tembusan {
            margin-top: 30px;
            font-size: 11pt;
        }
        .tembusan p {
            margin: 0;
            font-weight: bold;
        }
        .tembusan ol {
            margin: 5px 0 0 20px;
            padding: 0;
        }
        .tembusan li {
            margin-bottom: 3px;
        }

        /* Placeholder notice */
        .placeholder-notice {
            background-color: #fff3cd;
            border: 1px dashed #ffc107;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 10pt;
            color: #856404;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('assets/Logo_PU.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
    @endphp

    {{-- ==========================================
         KOP SURAT
         ========================================== --}}
    <div class="kop-surat">
        <table class="kop-logo-table">
            <tr>
                <td style="width: 80px; text-align: center;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" class="kop-logo" alt="Logo">
                    @endif
                </td>
                <td style="text-align: center;">
                    <div class="instansi">Kementerian Pekerjaan Umum</div>
                    <div class="sub-instansi">Pusat Data dan Teknologi Informasi</div>
                    <div class="alamat">
                        Jl. Pattimura No. 20 Kebayoran Baru, Jakarta Selatan 12110<br>
                        Telp. (021) 727 97500 — Email: pusdatin@pu.go.id
                    </div>
                </td>
                <td style="width: 80px;"></td>
            </tr>
        </table>
    </div>

    {{-- ==========================================
         HEADER SURAT (Nomor, Lampiran, Perihal)
         ========================================== --}}
    <div class="surat-header">
        <table>
            <tr>
                <td class="label">Nomor</td>
                <td class="separator">:</td>
                <td>{{ $data['nomor_surat'] }}</td>
            </tr>
            <tr>
                <td class="label">Jumlah Terlampir</td>
                <td class="separator">:</td>
                <td>{{ $data['total_pegawai'] }} orang (PNS terlampir)</td>
            </tr>
            <tr>
                <td class="label">Perihal</td>
                <td class="separator">:</td>
                <td><strong>Pengajuan {{ $data['kategori_label'] }}</strong></td>
            </tr>
        </table>
    </div>

    {{-- Tujuan Surat --}}
    <div style="margin-bottom: 20px;">
        <p style="margin: 0;">Kepada Yth.</p>
        <p style="margin: 0; font-weight: bold;">{{ $data['tujuan_surat'] }}</p>
        <p style="margin: 0;">Di Tempat</p>
    </div>

    {{-- ==========================================
         BODY SURAT
         ========================================== --}}
    <div class="surat-body">
        <p>
            Dengan hormat, bersama ini kami mengajukan permohonan <strong>{{ $data['kategori_label'] }}</strong> 
            untuk pegawai di lingkungan Pusat Data dan Teknologi Informasi, Kementerian Pekerjaan Umum
            sebagaimana tercantum dalam daftar terlampir berikut:
        </p>
    </div>

    {{-- ==========================================
         TABEL DATA PEGAWAI (Dinamis per Kategori)
         ========================================== --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Nama Lengkap</th>
                <th style="width: 16%;">NIP</th>

                @if(in_array($data['kategori'], ['KGB']))
                    <th style="width: 12%;">Pangkat/Gol</th>
                    <th style="width: 20%;">Jabatan</th>
                    <th style="width: 12%;">TMT KGB Baru</th>
                @elseif(in_array($data['kategori'], ['KP_Jafung', 'KP_Struktural', 'KP_Reguler', 'KP']))
                    <th style="width: 12%;">Pangkat/Gol</th>
                    <th style="width: 20%;">Jabatan</th>
                    <th style="width: 12%;">TMT Target</th>
                @elseif(in_array($data['kategori'], ['KJ_Jafung']))
                    <th style="width: 12%;">Pangkat/Gol</th>
                    <th style="width: 14%;">Jenjang</th>
                    <th style="width: 12%;">TMT Target</th>
                @elseif($data['kategori'] === 'UKOM')
                    <th style="width: 12%;">Pangkat/Gol</th>
                    <th style="width: 14%;">Jenjang</th>
                    <th style="width: 20%;">Jabatan</th>
                @else
                    <th style="width: 12%;">Pangkat/Gol</th>
                    <th style="width: 20%;">Jabatan</th>
                    <th style="width: 12%;">TMT Target</th>
                @endif

                <th style="width: 10%;">Ket</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['pegawai_list'] as $index => $p)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $p['nama'] }}</td>
                <td style="font-size: 9pt;">{{ $p['nip'] }}</td>

                @if(in_array($data['kategori'], ['KGB']))
                    <td style="text-align: center;">{{ $p['pangkat_golongan'] }}</td>
                    <td style="font-size: 9pt;">{{ $p['jabatan'] }}</td>
                    <td style="text-align: center;">{{ $p['tmt_target'] }}</td>
                @elseif(in_array($data['kategori'], ['KP_Jafung', 'KP_Struktural', 'KP_Reguler', 'KP']))
                    <td style="text-align: center;">{{ $p['pangkat_golongan'] }}</td>
                    <td style="font-size: 9pt;">{{ $p['jabatan'] }}</td>
                    <td style="text-align: center;">{{ $p['tmt_target'] }}</td>
                @elseif(in_array($data['kategori'], ['KJ_Jafung']))
                    <td style="text-align: center;">{{ $p['pangkat_golongan'] }}</td>
                    <td style="text-align: center;">{{ $p['jenjang'] }}</td>
                    <td style="text-align: center;">{{ $p['tmt_target'] }}</td>
                @elseif($data['kategori'] === 'UKOM')
                    <td style="text-align: center;">{{ $p['pangkat_golongan'] }}</td>
                    <td style="text-align: center;">{{ $p['jenjang'] }}</td>
                    <td style="font-size: 9pt;">{{ $p['jabatan'] }}</td>
                @else
                    <td style="text-align: center;">{{ $p['pangkat_golongan'] }}</td>
                    <td style="font-size: 9pt;">{{ $p['jabatan'] }}</td>
                    <td style="text-align: center;">{{ $p['tmt_target'] }}</td>
                @endif

                <td style="font-size: 9pt;">{{ $p['keterangan'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- PENUTUP --}}
    <div class="surat-body">
        <p>
            Demikian surat pengajuan ini kami sampaikan. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.
        </p>
    </div>

    {{-- ==========================================
         TANDA TANGAN
         ========================================== --}}
    <div class="ttd-section">
        <table>
            <tr>
                <td style="width: 55%;"></td>
                <td class="ttd-right">
                    <p style="margin: 0;">Jakarta, {{ $data['tanggal_surat'] }}</p>
                    <p style="margin: 0;">{{ $data['jabatan_ttd'] }}</p>
                    <br><br><br><br>
                    <p class="ttd-name" style="margin: 0;">{{ $data['nama_ttd'] }}</p>
                    <p style="margin: 0;">NIP. {{ $data['nip_ttd'] }}</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- TEMBUSAN --}}
    <div class="tembusan">
        <p>Tembusan:</p>
        <ol>
            <li>Kepala Biro Kepegawaian</li>
            <li>Arsip</li>
        </ol>
    </div>

    {{-- PLACEHOLDER NOTICE (hapus nanti ketika template final) --}}
    <div class="placeholder-notice" style="margin-top: 30px;">
        <strong>CATATAN PENGEMBANGAN:</strong> Template surat ini masih bersifat placeholder/generik. 
        Format dan konten surat akan disesuaikan dengan template resmi setelah tersedia.
    </div>
</body>
</html>
