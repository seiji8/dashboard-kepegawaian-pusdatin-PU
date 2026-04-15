<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Keterangan Kenaikan Gaji Berkala</title>
    <style>
        /* Margin kertas diperlebar sedikit di atas/bawah agar TTD lega dan pasti 1 halaman */
        @page {
            size: A4;
            margin: 1.25cm 2cm 1.25cm 2cm; 
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.15;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* --- KOP SURAT --- */
        .tabel-kop {
            width: 100%;
            border-bottom: 3px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .tabel-kop td {
            vertical-align: middle;
        }
        .logo-pu {
            width: 80px;
            height: auto;
        }
        .teks-kop {
            text-align: center;
        }
        .kop-instansi {
            font-size: 13pt;
            margin: 0;
            line-height: 1.2;
        }
        .kop-alamat {
            font-size: 9pt;
            margin: 3px 0 0 0;
            /* PERBAIKAN: Memaksa teks agar tetap 1 baris */
            white-space: nowrap; 
        }

        /* --- GLOBAL TABEL DATA --- */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            vertical-align: top;
            padding: 1px 0;
        }

        /* --- TEKS PARAGRAF --- */
        .paragraf {
            text-align: justify;
            margin: 0 0 8px 0;
        }
        .paragraf-spasi {
            text-align: left;
            margin: 8px 0;
        }

        /* --- TEMBUSAN & FOOTER --- */
        .tembusan {
            margin-top: 20px;
            font-size: 11pt;
        }
        .tembusan p { margin: 0 0 2px 0; }
        .tembusan ol {
            margin: 0;
            padding-left: 20px;
        }
        
        .footer-bssn {
            margin-top: 15px;
            font-size: 8pt;
            text-align: justify;
            line-height: 1.2;
        }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('assets/Logo_PU.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        // Fallback Data Pegawai
        $p = (isset($data['pegawai_list']) && count($data['pegawai_list']) > 0) ? $data['pegawai_list'][0] : [];
        $namaLengkap = $p['nama'] ?? '....................................';
        $nip = $p['nip'] ?? '....................................';
        $pangkatGol = $p['pangkat_golongan'] ?? '....................................';
        $unitKerja = $p['unit_kerja'] ?? '....................................';
        
        $gajiLamaAngka = isset($p['gaji_pokok_lama']) ? number_format($p['gaji_pokok_lama'], 0, ',', '.') : '';
        $gajiLamaTerbilang = $p['gaji_pokok_lama_terbilang'] ?? 'Tiga Juta Seratus Delapan Puluh Enam Ribu Enam Ratus Rupiah';
        
        $gajiBaruAngka = isset($p['gaji_pokok_baru']) ? number_format($p['gaji_pokok_baru'], 0, ',', '.') : '3.287.000';
        $gajiBaruTerbilang = $p['gaji_pokok_baru_terbilang'] ?? 'Tiga Juta Dua Ratus Delapan Puluh Tujuh Ribu Rupiah';
    @endphp

    <table class="tabel-kop">
        <tr>
            <td style="width: 12%; text-align: center;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo-pu" alt="Logo PU">
                @endif
            </td>
            <td class="teks-kop" style="width: 76%;">
                <p class="kop-instansi">KEMENTERIAN PEKERJAAN UMUM</p>
                <p class="kop-instansi">SEKRETARIAT JENDERAL</p>
                <p class="kop-instansi">PUSAT DATA DAN TEKNOLOGI INFORMASI</p>
                <p class="kop-alamat">Jalan Pattimura Nomor 20, Kebayoran Baru, Jakarta 12110, Telepon 7232366 Faksimili 7220219</p>
            </td>
            <td style="width: 12%;"></td>
        </tr>
    </table>

    <table style="margin-bottom: 15px;">
        <tr>
            <td style="width: 12%;">Nomor</td>
            <td style="width: 3%; text-align: center;">:</td>
            <td style="width: 50%;">{{ $data['nomor_surat'] ?? '' }}</td>
            <td style="width: 35%; text-align: right;">Jakarta, {{ $data['tanggal_surat'] ?? '09 Maret 2026' }}</td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td style="text-align: center;">:</td>
            <td colspan="2">-</td>
        </tr>
        <tr>
            <td>Hal</td>
            <td style="text-align: center;">:</td>
            <td colspan="2">Kenaikan Gaji Berkala a.n. {{ $namaLengkap }}</td>
        </tr>
    </table>

    <div style="margin-bottom: 12px; line-height: 1.15;">
        Yth.<br>
        Kepala Kantor Pelayanan Perbendaharaan Negara<br>
        di-<br>
        <span style="padding-left: 20px;">Jakarta</span>
    </div>

    <p class="paragraf">
        Dengan hormat, kami sampaikan pemberitahuan bahwa berhubung dengan telah dipenuhinya masa kerja dan syarat-syarat lainnya oleh seorang Pegawai/Pejabat berikut dibawah ini:
    </p>
    
    <table style="margin-bottom: 8px;">
        <tr>
            <td style="width: 4%;">1.</td>
            <td style="width: 30%;">Nama</td>
            <td style="width: 3%; text-align: center;">:</td>
            <td style="text-align: justify;">{{ $namaLengkap }}</td>
        </tr>
        <tr>
            <td>2.</td>
            <td>NIP</td>
            <td style="text-align: center;">:</td>
            <td style="text-align: justify;">{{ $nip }}</td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Pangkat. Gol/Ruang</td>
            <td style="text-align: center;">:</td>
            <td style="text-align: justify;">{{ $pangkatGol }}</td>
        </tr>
        <tr>
            <td>4.</td>
            <td>Kantor/Tempat</td>
            <td style="text-align: center;">:</td>
            <td style="text-align: justify;">{{ $unitKerja }}</td>
        </tr>
        <tr>
            <td>5.</td>
            <td>Gaji Pokok Lama</td>
            <td style="text-align: center;">:</td>
            <td style="text-align: justify;">{{ $gajiLamaAngka != '' ? $gajiLamaAngka . ',- ' : '' }}({{ $gajiLamaTerbilang }})</td>
        </tr>
    </table>

    <p class="paragraf-spasi">
        (Atas dasar Surat Keputusan terakhir tentang GAJI BERKALA/PANGKAT/CPNS/PENYESUAIAN GAJI POKOK Pegawai Negeri Sipil yang ditetapkan):
    </p>

    <table style="margin-bottom: 8px;">
        <tr>
            <td style="width: 4%;">a.</td>
            <td style="width: 30%;">Oleh Pejabat</td>
            <td style="width: 3%; text-align: center;">:</td>
            <td style="text-align: justify;">{{ $p['sk_lama_pejabat'] ?? 'Kepala Biro Kepegawaian, Organisasi dan Tata Laksana' }}</td>
        </tr>
        <tr>
            <td>b.</td>
            <td>Nomor/Tanggal</td>
            <td style="text-align: center;">:</td>
            <td style="text-align: justify;">{{ $p['sk_lama_nomor'] ?? '318/KPTS/M/2026' }} &nbsp;tgl. {{ $p['sk_lama_tanggal'] ?? '20 Februari 2026' }}</td>
        </tr>
        <tr>
            <td>c.</td>
            <td>Tgl mulai berlaku gaji tersebut</td>
            <td style="text-align: center;">:</td>
            <td style="text-align: justify;">{{ $p['sk_lama_tmt'] ?? '01 Maret 2026' }}</td>
        </tr>
        <tr>
            <td>d.</td>
            <td>Masa kerja golongan</td>
            <td style="text-align: center;">:</td>
            <td style="text-align: justify;">{{ $p['sk_lama_masa_kerja'] ?? '07 tahun 10 bulan' }}</td>
        </tr>
    </table>

    <p class="paragraf-spasi">
        DIBERIKAN GAJI BERKALA HINGGA MEMPEROLEH :
    </p>

    <table style="margin-bottom: 10px;">
        <tr>
            <td style="width: 4%;">1.</td>
            <td style="width: 30%;">Gaji Pokok Baru</td>
            <td style="width: 3%; text-align: center;">:</td>
            <td style="text-align: justify;">{{ $gajiBaruAngka }},- ({{ $gajiBaruTerbilang }})</td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Berdasarkan masa kerja</td>
            <td style="text-align: center;">:</td>
            <td style="text-align: justify;">{{ $p['gaji_baru_masa_kerja'] ?? '08 tahun 00 bulan' }}</td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Dalam Pangkat. Gol/Ruang</td>
            <td style="text-align: center;">:</td>
            <td style="text-align: justify;">{{ $pangkatGol ?? 'III/a' }}</td>
        </tr>
        <tr>
            <td>4.</td>
            <td>Terhitung mulai tanggal</td>
            <td style="text-align: center;">:</td>
            <td style="text-align: justify;">{{ $p['gaji_baru_tmt'] ?? '01 Mei 2026' }}</td>
        </tr>
    </table>

    <p class="paragraf">
        Sesuai dengan Peraturan Pemerintah Nomor 5 Tahun 2024, mohon kepada Pegawai Negeri Sipil tersebut dapat dibayarkan penghasilannya berdasarkan Gaji Pokok Baru.
    </p>

    <table style="margin-top: 15px;">
        <tr>
            <td style="width: 55%;"></td> <td style="width: 45%; text-align: left;">
                {{ $data['jabatan_ttd'] ?? 'Kepala Pusat Data dan Teknologi Informasi,' }}
                <br><br><br><br><br> <span style="font-weight: bold; text-decoration: underline;">
                    {{ $data['nama_ttd'] ?? 'Komang Sri Hartini' }}
                </span>
            </td>
        </tr>
    </table>

    <div class="tembusan">
        <p>Tembusan:</p>
        <ol>
            <li>Kepala BKN di Jakarta;</li>
            <li>Kepala Biro Keuangan Kementerian PU;</li>
            <li>Pembuat Daftar Gaji Pusdatin Kementerian PU;</li>
            <li>Pegawai/Pejabat yang bersangkutan.</li>
        </ol>
    </div>

    <div class="footer-bssn">
        Dokumen ini telah ditandatangani menggunakan sertifikasi elektronik yang diterbitkan oleh Balai Sertifikasi (BSrE) BSSN.<br>
        Untuk memastikan keaslian tanda tangan elektronik, silahkan unggah dokumen pada laman https://tte.komdigi.go.id/verifyPDF.
    </div>

</body>
</html>