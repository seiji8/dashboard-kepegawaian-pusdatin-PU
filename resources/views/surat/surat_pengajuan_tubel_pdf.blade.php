<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Nota Dinas Pengaktifan Kembali Tugas Belajar</title>
    <style>
        @page {
            margin: 30px 1.9cm 0.5cm 2.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5pt;
            line-height: 1.2;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* KOP SURAT */
        .kop-surat {
            width: 100%;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
        }
        .kop-logo {
            width: 80px;
            height: auto;
        }
        .kop-text {
            text-align: center;
        }
        .kop-text .kementerian {
            font-size: 14pt;
        }
        .kop-text .sekjen {
            font-size: 14pt;
        }
        .kop-text .pusdatin {
            font-size: 16pt;
            font-weight: bold;
        }
        .kop-text .alamat {
            font-size: 8.5pt;
            margin-top: 2px;
            white-space: nowrap;
        }
        .kop-text .alamat a {
            color: #0000FF;
            text-decoration: none;
        }

        /* JUDUL NOTA DINAS */
        .judul-nota {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 8px;
        }
        .judul-nota p {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* HEADER NOTA DINAS */
        .header-nota {
            width: 100%;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .header-nota table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-nota td {
            vertical-align: top;
            padding: 2px 0;
            font-size: 11pt;
        }
        .td-label {
            width: 70px;
        }
        .td-colon {
            width: 15px;
            text-align: center;
        }

        /* ISI SURAT */
        .isi-surat {
            text-align: justify;
            font-size: 11pt;
            line-height: 1.2;
        }
        .isi-surat p {
            margin: 0 0 5px 0;
            text-indent: 40px;
        }
        .isi-surat ol.main-list {
            margin: 0 0 7px 0;
            padding-left: 25px;
        }
        .isi-surat ol.main-list > li {
            margin-bottom: 4px;
            padding-left: 5px;
            line-height: 1.2;
            list-style-type: decimal;
        }
        .isi-surat ul.sub-list {
            margin: 3px 0 3px 0;
            padding-left: 20px;
            list-style-type: none;
        }
        .isi-surat ul.sub-list li {
            margin-bottom: 2px;
            position: relative;
            padding-left: 15px;
        }
        .isi-surat ul.sub-list li::before {
            content: "-";
            position: absolute;
            left: 0;
        }

        /* TANDA TANGAN */
        .ttd-section {
            margin-top: 10px;
            width: 100%;
            page-break-inside: avoid;
        }
        .ttd-kiri {
            width: 50%;
        }
        .ttd-kanan {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .ttd-jabatan {
            font-size: 11pt;
            margin: 0 0 4px 0;
            line-height: 1.2;
        }
        .ttd-qr-box {
            width: 125px;
            height: 125px;
            margin: 12px auto;
            display: block;
            border: none;
            background: none;
        }
        .ttd-nama {
            font-size: 11pt;
            margin: 0;
        }
        .ttd-nip {
            font-size: 11pt;
            margin: 0;
        }

        /* FOOTER BSrE */
        .footer-bsre {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7.5pt;
            color: #000;
            padding-bottom: 5px;
            border-top: 1px solid #ddd;
            padding-top: 4px;
        }
        .footer-bsre p {
            margin: 1px 0;
        }

        .bold { font-weight: bold; }
        .page {
            position: relative;
            height: 100%;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('assets/Logo_PU.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
    @endphp

    @foreach($data['pegawai_list'] as $index => $p)
    <div class="page" style="{{ $index > 0 ? 'page-break-before: always;' : '' }}">
        <!-- KOP SURAT -->
        <table class="kop-surat">
            <tr>
                <td style="width: 90px; text-align: center; vertical-align: middle;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" class="kop-logo" alt="Logo PU">
                    @endif
                </td>
                <td class="kop-text">
                    <div class="kementerian">KEMENTERIAN PEKERJAAN UMUM</div>
                    <div class="sekjen">SEKRETARIAT JENDERAL</div>
                    <div class="pusdatin">PUSAT DATA DAN TEKNOLOGI INFORMASI</div>
                    <div class="alamat">
                        Jl. Pattimura Nomor 20, Kebayoran Baru, Jakarta 12110, Telepon (021) 7392262, surel <a href="mailto:pusdatin@pu.go.id">pusdatin@pu.go.id</a>
                    </div>
                </td>
            </tr>
        </table>

        <!-- JUDUL -->
        <div class="judul-nota">
            <p>NOTA DINAS</p>
            <p>NOMOR {{ !empty($data['nomor_surat']) ? $data['nomor_surat'] : '............................................' }}</p>
        </div>

        <!-- HEADER NOTA -->
        <div class="header-nota">
            <table>
                <tr>
                    <td class="td-label">Yth.</td>
                    <td class="td-colon">:</td>
                    <td>{{ $data['tujuan_surat'] ?? 'Kepala Biro Kepegawaian, Organisasi dan Tata Laksana' }}</td>
                </tr>
                <tr>
                    <td class="td-label">Dari</td>
                    <td class="td-colon">:</td>
                    <td>{{ $data['jabatan_ttd'] ?? 'Kepala Pusat Data dan Teknologi Informasi' }}</td>
                </tr>
                <tr>
                    <td class="td-label">Hal</td>
                    <td class="td-colon">:</td>
                    <td>
                        Permohonan Pengangkatan Kembali dalam Jabatan Fungsional<br>
                        {{ $p['jabatan'] }}
                    </td>
                </tr>
                <tr>
                    <td class="td-label">Tanggal</td>
                    <td class="td-colon">:</td>
                    <td>{{ $data['tanggal_surat'] ?? \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
                </tr>
            </table>
        </div>

        <!-- ISI SURAT -->
        <div class="isi-surat">
            <p>
                Menindaklanjuti Nota Dinas Kepala Biro Kepegawaian, Organisasi, dan Tata Laksana Nomor {{ $data['ref_nota_dinas'] ?? 'SM04/B/Sp/2026/474' }} tanggal {{ $data['tgl_nota_dinas'] ?? '26 Maret 2026' }} Hal Pengembalian Peserta Tugas Belajar, bersama ini kami sampaikan hal sebagai berikut:
            </p>

            <ol class="main-list">
                <li>
                    Pusdatin mengajukan permohonan Pengangkatan Kembali dalam Jabatan Fungsional {{ $p['jabatan'] }} untuk pegawai Pusdatin yang telah menyelesaikan {{ $p['tubel_pendidikan'] }} atas nama <span class="bold">{{ $p['nama'] }}</span>;
                </li>
                <li>
                    Sebagai bahan pertimbangan kami lampirkan kelengkapan dokumen yang dibutuhkan berupa:
                    <ul class="sub-list">
                        <li>SK Pengangkatan Jabatan Fungsional;</li>
                        <li>SK Tugas Belajar;</li>
                        <li>SK Pemberhentian Jabatan Fungsional;</li>
                        <li>Surat Pengembalian Tugas Belajar;</li>
                        <li>PAK Terakhir</li>
                    </ul>
                </li>
                <li>
                    Kami menugaskan narahubung {{ $data['narahubung_nama'] ?? 'Sdri. Julia' }} (HP. {{ $data['narahubung_hp'] ?? '0822-9824-6907' }}) surel <a href="mailto:{{ $data['narahubung_email'] ?? 'julia.pujilestari@pu.go.id' }}" style="color: #0000FF; text-decoration: underline;">{{ $data['narahubung_email'] ?? 'julia.pujilestari@pu.go.id' }}</a> jika terdapat hal-hal yang perlu dikoordinasikan.
                </li>
            </ol>

            <p>
                Selanjutnya, dalam menunjang pembangunan Zona Integritas menuju Wilayah Birokrasi Bersih dan Melayani, PUSDATIN Kementerian PU berkomitmen meningkatkan kualitas pelayanan publik yang bebas dari korupsi dan memberikan pelayanan prima.
            </p>

            <p style="text-indent: 0;">
                Demikian kami sampaikan. Atas perhatian dan kerja sama Ibu diucapkan terima kasih.
            </p>
        </div>

        <!-- TANDA TANGAN -->
        <div class="ttd-section">
            <table width="100%">
                <tr>
                    <td class="ttd-kiri"></td>
                    <td class="ttd-kanan">
                        <p class="ttd-jabatan">{{ $data['jabatan_ttd'] ?? 'Kepala Pusat Data dan Teknologi Informasi' }},</p>
                        <!-- Space untuk QR Code TTE BSrE -->
                        <div class="ttd-qr-box"></div>
                        <p class="ttd-nama">{{ $data['nama_ttd'] ?? 'Komang Sri Hartini' }}</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- FOOTER BSrE -->
        <div class="footer-bsre">
            <p>Dokumen ini telah ditandatangani menggunakan sertifikasi elektronik yang diterbitkan oleh Balai Sertifikasi (BSrE) BSSN.</p>
            <p>Untuk memastikan keaslian tanda tangan elektronik, silahkan unggah dokumen pada laman https://tte.komdigi.go.id/VerifyPDF.</p>
        </div>
    </div>
    @endforeach
</body>
</html>
