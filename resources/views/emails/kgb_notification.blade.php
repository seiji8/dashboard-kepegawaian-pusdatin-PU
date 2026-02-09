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

        <!-- Content -->
        <p style="font-size: 16px; line-height: 1.5;">
            Masa <strong>Kenaikan Gaji Berkala (KGB)</strong> Anda sudah tiba atau mendekati batas waktu.
        </p>

        <p style="font-size: 16px; line-height: 1.5;">
            Mohon segera melengkapi dokumen persyaratan berikut:
            <ul>
                <li>SK Pangkat Terakhir</li>
                <li>SKP (Sasaran Kinerja Pegawai)</li>
            </ul>
        </p>

        <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
            <p style="margin: 0; font-weight: bold;">
                Silakan kirimkan dokumen tersebut ke Admin Kepegawaian secepatnya.
            </p>
        </div>

        <!-- Footer -->
        <p style="margin-top: 30px; font-size: 14px; color: #6c757d;">
            Terima kasih,<br>
            <strong>Admin Kepegawaian</strong><br>
            Pusdatin Kementerian PU
        </p>

    </div>

</body>
</html>
