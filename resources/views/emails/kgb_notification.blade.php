<!DOCTYPE html>
<html>
<head>
    <title>Notifikasi KGB</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">

    <div style="max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
        
        <!-- Header Image -->
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="{{ $message->embed(public_path('img/email_header.png')) }}" alt="Header" style="width: 100%; max-width: 600px; height: auto; border-radius: 4px;">
        </div>

        <!-- Greeting -->
        <h2 style="color: #004085;">Halo, {{ $tracker->pegawai->nama }}</h2>

        <!-- Content dari Template Konfigurasi Pesan -->
        @if(isset($pesanTemplate))
        <div style="font-size: 16px; line-height: 1.6; white-space: pre-line;">{{ $pesanTemplate }}</div>
        @else
        <p style="font-size: 16px; line-height: 1.5;">
            Surat <strong> Keterangan Kenaikan Gaji Berkala (KGB) "Bulan" "Tahun" (TMT KGB 2 tahun)</strong> Anda sudah terbit.
        </p>

        <p style="font-size: 16px; line-height: 1.5;">
            Mohon segera melengkapi dokumen persyaratan berikut:
            <ul>
                <li>Dokumen Arsip KGB yang sudah ditandatangani</li>
            </ul>
        </p>

        <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
            <p style="margin: 0; font-weight: bold;">
                Mohon segera unggah dokumen tersebut ke E-HRM secepatnya.
            </p>
        </div>
        @endif

        <!-- Footer -->
        <p style="margin-top: 30px; font-size: 14px; color: #6c757d;">
            Terima kasih,<br>
            <strong>Admin Tim Kepegawaian dan JF</strong><br>
            Pusdatin
        </p>

    </div>

</body>
</html>
