<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Pengajuan {{ $data['kategori_label'] }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 60px 60px 50px 70px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .tanggal-kanan {
            text-align: right;
            margin-bottom: 30px;
        }

        .surat-header table { width: 100%; }
        .surat-header td {
            vertical-align: top;
            padding: 1px 0;
            font-size: 11pt;
        }
        .label-cell { width: 90px; }
        .separator-cell { width: 15px; text-align: center; }

        .tujuan-surat {
            margin-top: 25px;
            margin-bottom: 25px;
        }
        .tujuan-surat p {
            margin: 0;
            line-height: 1.5;
        }

        .surat-body {
            text-align: justify;
            margin-bottom: 10px;
        }
        .surat-body p {
            text-indent: 50px;
            margin: 0 0 15px 0;
        }
        .surat-body .penutup {
            text-indent: 0;
            text-align: left;
        }

        /* TTD: lebar cukup agar 1 baris */
        .ttd-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .ttd-container {
            float: right;
            text-align: center;
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

        /* HALAMAN 2 */
        .page-break { page-break-before: always; }

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
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px 4px;
            vertical-align: middle;
        }
        .data-table th {
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
        }
        .data-table td { font-size: 8pt; }
    </style>
</head>
<body>
    @php
        $sampleName = count($data['pegawai_list']) > 0 ? $data['pegawai_list'][0]['nama'] : '..................';
        $sampleNip = count($data['pegawai_list']) > 0 ? $data['pegawai_list'][0]['nip'] : '..................';
        $totalPegawai = count($data['pegawai_list']);

        $jenisMekanisme = 'Reguler';
        if ($data['kategori'] == 'KP_Jafung') $jenisMekanisme = 'Pilihan Jabatan Fungsional';
        if ($data['kategori'] == 'KP_Struktural') $jenisMekanisme = 'Pilihan Jabatan Struktural';

        $periodeText = '';
        if (count($data['pegawai_list']) > 0 && $data['pegawai_list'][0]['tmt_target'] != '-') {
            try {
                $periodeText = \Carbon\Carbon::createFromFormat('d-m-Y', $data['pegawai_list'][0]['tmt_target'])->isoFormat('D MMMM Y');
            } catch (\Exception $e) {
                $periodeText = '';
            }
        }

        $kppn = $data['kppn'] ?? '';
    @endphp

    {{-- HALAMAN 1: SURAT PENGANTAR --}}

    <div class="tanggal-kanan">Jakarta , {{ $data['tanggal_surat'] }}</div>

    <div class="surat-header">
        <table>
            <tr>
                <td class="label-cell"><strong>Nomor</strong></td>
                <td class="separator-cell">:</td>
                <td>{{ $data['nomor_surat'] }}</td>
            </tr>
            <tr>
                <td class="label-cell"><strong>Sifat</strong></td>
                <td class="separator-cell">:</td>
                <td>Biasa</td>
            </tr>
            <tr>
                <td class="label-cell"><strong>Lampiran</strong></td>
                <td class="separator-cell">:</td>
                <td>1 Berkas</td>
            </tr>
            <tr>
                <td class="label-cell"><strong>Hal</strong></td>
                <td class="separator-cell">:</td>
                <td>Penyampaian Usul Kenaikan Pangkat {{ $jenisMekanisme }} Pegawai Negeri Sipil Periode {{ $periodeText }}</td>
            </tr>
        </table>
    </div>

    <div class="tujuan-surat">
        <p><strong>Kepada Yth:</strong></p>
        <p>{{ $data['tujuan_surat'] }}</p>
        <p>di Jakarta</p>
    </div>

    <div class="surat-body">
        <p>
            Berdasarkan Keputusan Kepala Badan Kepegawaian Negara Nomor 12 Tahun 2002 tentang Ketentuan
            Pelaksanaan Peraturan Pemerintah Nomor 99 Tahun 2000 tentang Kenaikan Pangkat Pegawai Negeri Sipil
            Sebagaimana Telah Diubah Dengan Peraturan Pemerintah Nomor 12 Tahun 2002, bersama ini dengan hormat
            kami sampaikan Usul Kenaikan Pangkat {{ $jenisMekanisme }} Pegawai Negeri Sipil periode {{ $periodeText }} a.n {{ $sampleName }},
            NIP {{ $sampleNip }}@if($totalPegawai > 1), dkk dengan jumlah PNS ({{ $totalPegawai }} Daftar nama Terlampir)@endif
        </p>
        <p class="penutup">Demikian atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>
    </div>

    <div class="ttd-section">
        <div class="ttd-container">
            <p class="ttd-jabatan">{{ $data['jabatan_ttd'] }},</p>
            <br><br><br><br>
            <p class="ttd-name">{{ $data['nama_ttd'] }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>


    {{-- HALAMAN 2: TABEL DAFTAR USULAN --}}

    <div class="page-break"></div>

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
                <th style="width: 17%;">NAMA</th>
                <th style="width: 13%;">NIP</th>
                <th style="width: 10%;">PANGKAT/GOL.</th>
                <th style="width: 11%;">MASA KERJA<br>(TH/BLN)</th>
                <th style="width: 17%;">JABATAN</th>
                <th style="width: 18%;">UNIT KERJA</th>
                <th style="width: 10%;">KPPN</th>
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

    <div class="ttd-section" style="margin-top: 40px;">
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
