<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Nota Dinas Usulan Kenaikan Jenjang</title>
    <style>
        @page {
            margin: 30px 40px 30px 50px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* KOP SURAT */
        .kop-surat {
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
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
            font-size: 9pt;
            margin-top: 2px;
        }
        .kop-text .alamat a {
            color: #0000FF;
            text-decoration: none;
        }

        /* JUDUL NOTA DINAS */
        .judul-nota {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .judul-nota p {
            margin: 0;
            font-size: 11pt;
        }

        /* HEADER NOTA DINAS (Yth, Dari, Hal, Tanggal) */
        .header-nota {
            width: 100%;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
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
        }
        .isi-surat p {
            margin: 0 0 6px 0;
            text-indent: 40px;
        }
        .isi-surat ol {
            margin: 0 0 10px 0;
            padding-left: 40px;
        }
        .isi-surat ol li {
            margin-bottom: 5px;
            padding-left: 5px;
        }
        .isi-surat ol li ol {
            list-style-type: lower-alpha;
            margin-top: 5px;
            margin-bottom: 0;
            padding-left: 20px;
        }

        /* TANDA TANGAN */
        .ttd-section {
            margin-top: 20px;
            width: 100%;
        }
        .ttd-section table {
            width: 100%;
        }
        .ttd-right {
            text-align: center;
            width: 50%;
        }


        /* FOOTER BSrE */
        .footer-bsre {
            margin-top: 40px;
            text-align: center;
            font-size: 7.5pt;
            color: #000;
        }
        .footer-bsre p {
            margin: 2px 0;
        }
        
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('assets/Logo_PU.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
    @endphp

    <!-- KOP SURAT -->
    <table class="kop-surat">
        <tr>
            <td style="width: 100px; text-align: center; vertical-align: middle;">
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
        <p>NOMOR {{ $data['nomor_surat'] ?? 'KP1003/T/Sd/'.date('Y').'/...' }}</p>
    </div>

    <!-- HEADER NOTA -->
    <div class="header-nota">
        <table>
            <tr>
                <td class="td-label">Yth.</td>
                <td class="td-colon">:</td>
                <td>Kepala Biro Kepegawaian, Organisasi dan Tata Laksana</td>
            </tr>
            <tr>
                <td class="td-label">Dari</td>
                <td class="td-colon">:</td>
                <td>Kepala Pusat Data dan Teknologi Informasi</td>
            </tr>
            <tr>
                <td class="td-label">Hal</td>
                <td class="td-colon">:</td>
                <td>
                    Penyampaian Usulan Kenaikan Jenjang Jabatan Fungsional<br>
                    {{ $data['jabatan_fungsional'] ?? 'Analis Sumber Daya Manusia Aparatur' }} {{ $data['jenjang_baru'] ?? 'Ahli Madya' }} a.n.<br>
                    {{ $data['nama_pegawai'] ?? 'Devy Wardhani, SE., MH' }}
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
            Kami mengucapkan terima kasih atas dukungan yang diberikan Biro Kepegawaian, Organisasi dan Tata Laksana dalam pengelolaan kepegawaian di Pusat Data dan Teknologi Informasi. Menindaklanjuti Nota Dinas Kepala Biro Kepegawaian, Organisasi dan Tata Laksana Nomor {{ $data['ref_nota_dinas'] ?? 'KP0303/B/Sp/2026/506' }} tanggal {{ $data['tgl_nota_dinas'] ?? '31 Maret 2026' }} Hal Pengumuman Hasil Uji Kompetensi Jabatan Fungsional di Bidang Manajemen ASN Periode Maret 2026, bersama ini kami sampaikan hal sebagai berikut:
        </p>

        <ol>
            <li>
                Berdasarkan surat Badan Kepegawaian Negara Nomor: {{ $data['nomor_surat_bkn'] ?? '1589/B-BJ.03.02/SD/C/2026' }} tanggal {{ $data['tgl_surat_bkn'] ?? '25 Maret 2026' }}, bahwa pegawai Pusdatin a.n. <span class="bold">{{ $data['nama_pegawai'] ?? 'Devy Wardhani, SE., MH' }}</span> dinyatakan <span class="bold">Lulus</span> Ujian Kompetensi Kenaikan Jenjang <span class="bold">{{ $data['jenjang_baru'] ?? 'Ahli Madya' }}</span>;
            </li>
            <li>
                Berkenaan dengan hal tersebut, mohon perkenan untuk dapat diterbitkan SK Kenaikan Jenjang Jabatan Fungsional {{ $data['jabatan_fungsional'] ?? 'Analis Sumber Daya Manusia Aparatur' }} {{ $data['jenjang_baru'] ?? 'Ahli Madya' }} sebagai syarat utama yang dibutuhkan untuk kelengkapan administrasi Kenaikan Pangkat pegawai yang bersangkutan;
            </li>
            <li>
                Sebagai kelengkapan pengajuan usulan, kami lampirkan beberapa dokumen pendukung berupa :
                <ol>
                    <li>Sertifikat dan Surat Hasil Ujian Kompetensi Jabatan Fungsional {{ $data['jabatan_fungsional'] ?? 'Analis Sumber Daya Manusia Aparatur' }} {{ $data['jenjang_baru'] ?? 'Ahli Madya' }};</li>
                    <li>SK Pangkat Terakhir;</li>
                    <li>SK Jabatan Fungsional;</li>
                    <li>PAK Terakhir.</li>
                </ol>
            </li>
            <li>
                Kami menugaskan narahubung Sdri. {{ $data['narahubung_nama'] ?? 'Julia' }} (HP. {{ $data['narahubung_hp'] ?? '0822-9824-6907' }}) surel <a href="mailto:{{ $data['narahubung_email'] ?? 'julia.pujilestari@pu.go.id' }}" style="color: #0000FF; text-decoration: underline;">{{ $data['narahubung_email'] ?? 'julia.pujilestari@pu.go.id' }}</a> jika terdapat hal-hal yang perlu dikoordinasikan.
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
        <table>
            <tr>
                <td style="width: 50%;"></td>
                <td class="ttd-right">
                    <p style="margin: 0;">Kepala Pusat Data dan Teknologi Informasi,</p>
                    
                    <br><br><br><br>

                    <p style="margin: 0;">Komang Sri Hartini</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER BSrE -->
    <div class="footer-bsre">
        <p>Dokumen ini telah ditandatangani menggunakan sertifikat elektronik yang diterbitkan oleh Balai Sertifikasi (BSrE) BSSN.</p>
        <p>Untuk memastikan keaslian tanda tangan elektronik, silakan unggah dokumen pada laman https://tte.kominfo.go.id/VerifyPDF.</p>
    </div>
</body>
</html>
