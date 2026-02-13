<!DOCTYPE html>
<html>
<head>
    <title>Notifikasi KGB</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">

    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        
        <!-- Header Section (Right Aligned + vertical Gradient) -->
        <div style="background: linear-gradient(180deg, #142B6F 0%, #6176B3 100%); padding: 30px 40px; color: white; border-top-left-radius: 8px; border-top-right-radius: 8px; text-align: center;">
            <div style="display: inline-flex; align-items: center; justify-content: center;">
                <!-- Logo (CID Embedded) -->
                <img src="{{ $message->embed(public_path('assets/Logo_PU.png')) }}" 
                     alt="Logo PUPR" 
                     style="height: 60px; width: auto; display: block; margin-right: 20px; margin-top: 10px;">
                
                <div style="text-align: left;">
                    <h1 style="margin: 0; font-size: 26px; font-weight: 800; letter-spacing: 0.5px; font-family: 'Arial', sans-serif;">
                        <span style="color: #fbbf24;">Dashboard</span>Alert
                    </h1>
                    <p style="margin: 5px 0 0; font-size: 13px; opacity: 0.9; line-height: 1.4;">
                        Email ini dikirim otomatis dari sistem<br>notifikasi DashAlert PUSDATIN
                    </p>
                </div>
            </div>
        </div>

        <div style="padding: 40px;">
            <!-- Greeting -->
            <h2 style="color: #1e3a8a; font-size: 22px; font-weight: 700; margin-top: 0;">Halo, {{ $tracker->pegawai->nama }}</h2>

            <!-- Content Box -->
            <div style="background-color: #f8f9fa; border-radius: 8px; padding: 25px; margin: 25px 0; border-left: 5px solid #fbbf24;">
                <p style="margin: 0; font-size: 16px; font-weight: 600; color: #1f2937; line-height: 1.6;">
                    Surat Keterangan KGB {{ $bulanTahun }} Anda sudah terbit.<br>
                    Mohon segera unggah dokumen tersebut ke E-HRM terima kasih.
                </p>
            </div>

            <!-- Footer Signature -->
            <!-- Footer Signature -->
            <div style="margin-top: 40px; padding-top: 25px; border-top: 1px solid #e5e7eb;">
                
                <!-- Signature -->
                <div style="margin-bottom: 20px;">
                    <p style="margin: 0 0 4px; font-size: 14px; color: #374151;">Salam,</p>
                    <p style="margin: 0; font-weight: 700; color: #1e3a8a; font-size: 15px;">Admin Kepegawaian</p>
                    <p style="margin: 2px 0 0; color: #6b7280; font-size: 13px;">Pusat Data dan Teknologi Informasi</p>
                </div>

                <!-- Zona Integritas Banner -->
                <div style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 15px;">
                    <p style="margin: 0; font-size: 11px; line-height: 1.6; color: #0369a1; text-align: justify; font-style: italic;">
                        "Dalam menunjang pembangunan zona integritas menuju wilayah birokrasi bersih dan melayani, 
                        <strong>PUSDATIN</strong> berkomitmen meningkatkan kualitas pelayanan publik yang bebas dari korupsi 
                        dan memberikan pelayanan prima serta <strong>tidak dipungut biaya apapun</strong>."
                    </p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
