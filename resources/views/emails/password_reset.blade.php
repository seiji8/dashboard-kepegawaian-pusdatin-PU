<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Reset Password</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    <!-- Outer wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f0f4f8; padding: 40px 20px;">
        <tr>
            <td align="center">

                <!-- Email Container -->
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(20, 43, 111, 0.12);">


                    <!-- ===== HEADER: Gradient Brand Bar ===== -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#142B6F 0%,#1e3a8a 60%,#1d4ed8 100%);padding:32px 40px;">
                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="vertical-align:middle;width:60px;">
                                        <img src="{{ $message->embed(public_path('assets/Logo_PU.png')) }}"
                                             alt="Logo PU"
                                             width="52"
                                             style="display:block;height:auto;border:0;">
                                    </td>
                                    <td style="vertical-align:middle;padding-left:16px;">
                                        <p style="margin:0;font-size:22px;font-weight:800;color:#ffffff;letter-spacing:0.3px;line-height:1.2;">
                                            <span style="color:#FFC928;">Dashboard</span>Alert
                                        </p>
                                        <p style="margin:4px 0 0;font-size:12px;color:rgba(255,255,255,0.75);letter-spacing:0.5px;">
                                            PUSDATIN Kementerian PU &mdash; Keamanan Akun
                                        </p>
                                    </td>
                                    <td style="vertical-align:middle;text-align:right;">
                                        <!-- Badge -->
                                        <span style="display:inline-block;background:rgba(255,201,40,0.15);border:1px solid rgba(255,201,40,0.4);color:#FFC928;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:5px 12px;border-radius:20px;">
                                            🔐 Reset Sandi
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ===== Accent Divider ===== -->
                    <tr>
                        <td style="padding:0;line-height:0;">
                            <div style="height:4px;background:linear-gradient(90deg,#FFC928 0%,#142B6F 50%,#FFC928 100%);"></div>
                        </td>
                    </tr>

                    <!-- ===== BODY CONTENT ===== -->
                    <tr>
                        <td style="padding: 40px 48px 32px 48px;">

                            <!-- Badge / Label was moved to header -->
                            <!-- Heading -->
                            <h1 style="margin: 0 0 12px 0; font-size: 24px; font-weight: 700; color: #0f172a; line-height: 1.3;">
                                Permintaan Reset Password
                            </h1>

                            <!-- Divider -->
                            <div style="width: 48px; height: 4px; background: linear-gradient(90deg, #FFC928, #142B6F); border-radius: 2px; margin-bottom: 24px;"></div>

                            <!-- Greeting -->
                            <p style="margin: 0 0 12px 0; font-size: 15px; color: #475569; line-height: 1.7;">
                                Halo <strong style="color: #0f172a;">Admin</strong>,
                            </p>
                            <p style="margin: 0 0 32px 0; font-size: 15px; color: #475569; line-height: 1.7;">
                                Kami menerima permintaan untuk mereset password akun Anda di <strong style="color: #142B6F;">Dashboard Kepegawaian Pusdatin PU</strong>. Klik tombol di bawah untuk melanjutkan proses penggantian password Anda.
                            </p>

                            <!-- CTA Button -->
                            <table cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto 32px auto;">
                                <tr>
                                    <td align="center" style="border-radius: 10px; background: linear-gradient(135deg, #142B6F, #1e3a8a);">
                                        <a href="{{ route('password.validate', $token) }}"
                                           style="display: inline-block; padding: 14px 36px; color: #ffffff !important; text-decoration: none; font-size: 15px; font-weight: 700; border-radius: 10px; letter-spacing: 0.3px;">
                                           🔑 &nbsp; Reset Password Saya
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Warning Box -->
                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 28px;">
                                <tr>
                                    <td style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 18px;">
                                        <p style="margin: 0; font-size: 13px; color: #92400e; line-height: 1.6;">
                                            ⏰ &nbsp;<strong>Link ini akan kadaluarsa dalam 60 menit.</strong><br>
                                            Jika Anda tidak merasa meminta reset password, abaikan email ini. Akun Anda <strong>tetap aman</strong>.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Sign off -->
                            <p style="margin: 0; font-size: 14px; color: #64748b; line-height: 1.7;">
                                Salam hangat,<br>
                                <strong style="color: #142B6F;">Tim IT Pusdatin PU</strong>
                            </p>

                        </td>
                    </tr>

                    <!-- ===== DIVIDER ===== -->
                    <tr>
                        <td style="padding: 0 48px;">
                            <div style="height: 1px; background-color: #e2e8f0;"></div>
                        </td>
                    </tr>

                    <!-- ===== FOOTER ===== -->
                    <tr>
                        <td style="padding: 24px 48px 32px 48px; text-align: center;">
                            <p style="margin: 0 0 8px 0; font-size: 12px; color: #94a3b8;">
                                &copy; {{ date('Y') }} Pusat Data dan Teknologi Informasi &mdash; Kementerian PUPR.<br>
                                Hak cipta dilindungi undang-undang.
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #b0bec5; line-height: 1.6;">
                                Jika tombol tidak berfungsi, salin link berikut ke browser Anda:<br>
                                <a href="{{ route('password.validate', $token) }}"
                                   style="color: #142B6F; word-break: break-all; font-size: 11px;">
                                    {{ route('password.validate', $token) }}
                                </a>
                            </p>
                        </td>
                    </tr>

                </table>
                <!-- End Email Container -->

            </td>
        </tr>
    </table>

</body>
</html>
