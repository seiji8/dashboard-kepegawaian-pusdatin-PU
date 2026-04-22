<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Pengajuan {{ $data['kategori_label'] }} - Hal 2</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 50px 60px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .lampiran-header-table { width: 100%; margin-bottom: 10px; }
        .lampiran-header-table td {
            font-size: 11pt;
            vertical-align: top;
            padding: 1px 0;
        }

        .lampiran-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 20px;
            margin-top: 15px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            table-layout: fixed;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .data-table th {
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            background-color: #f8f9fa;
        }
        .data-table td { font-size: 8pt; }

        .ttd-section {
            margin-top: 40px;
            page-break-inside: avoid;
            width: 100%;
        }
        .ttd-container {
            float: right;
            text-align: center;
            width: 300px;
        }
        .ttd-jabatan {
            margin: 0;
            font-style: italic;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }
    </style>
</head>
<body>
    @php
        $jenisMekanisme = 'Reguler';
        if ($data['kategori'] == 'KP_Jafung') $jenisMekanisme = 'Pilihan Jabatan Fungsional';
        if ($data['kategori'] == 'KP_Struktural') $jenisMekanisme = 'Pilihan Jabatan Struktural';

        $kppn = $data['kppn'] ?? '';
    @endphp

    <table class="lampiran-header-table">
        <tr>
            <td style="width: 80px;"><strong>Nomor</strong></td>
            <td style="width: 15px;">:</td>
            <td>{{ $data['nomor_surat'] }}</td>
        </tr>
        <tr>
            <td><strong>Tanggal</strong></td>
            <td>:</td>
            <td>{{ $data['tanggal_surat'] }}</td>
        </tr>
    </table>

    <div class="lampiran-title">
        Daftar Usul Kenaikan Pangkat {{ $jenisMekanisme }} Pegawai Negeri Sipil
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 18%;">NAMA</th>
                <th style="width: 14%;">NIP</th>
                <th style="width: 10%;">PANGKAT/GOL.</th>
                <th style="width: 10%;">MASA KERJA<br>(TH/BLN)</th>
                <th style="width: 20%;">JABATAN</th>
                <th style="width: 16%;">UNIT KERJA</th>
                <th style="width: 8%;">KPPN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['pegawai_list'] as $index => $p)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $p['nama'] }}</td>
                <td style="text-align: center; font-size: 7pt;">{{ $p['nip'] }}</td>
                <td style="text-align: center;">{{ $p['pangkat_golongan'] }}</td>
                <td style="text-align: center;">{{ $p['masa_kerja'] ?? '' }}</td>
                <td>{{ $p['jabatan'] }}</td>
                <td style="text-align: center;">Pusat Data dan Teknologi Informasi</td>
                <td style="text-align: center;">{{ $kppn }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="ttd-section">
        <div class="ttd-container">
            <p style="margin: 0;"><strong>Jakarta , {{ $data['tanggal_surat'] }}</strong></p>
            <p class="ttd-jabatan">{{ $data['jabatan_ttd'] }},</p>
            <br><br><br><br>
            <p class="ttd-name">{{ $data['nama_ttd'] }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
