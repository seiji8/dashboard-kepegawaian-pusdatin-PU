<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; margin: 0; padding: 40px 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background-color: #1e3a8a; padding: 30px 20px; text-align: center; }
        .header img { height: 60px; margin-bottom: 10px; }
        .header h1 { color: white; margin: 0; font-size: 22px; }
        .content { padding: 40px 30px; color: #334155; line-height: 1.6; }
        .content p { margin-top: 0; }
        .button-container { text-align: center; margin: 30px 0; }
        .button { display: inline-block; background-color: #3b82f6; color: white !important; font-weight: bold; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-size: 16px; }
        .footer { background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 13px; color: #64748b; }
        .footer a { color: #3b82f6; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistem Kepegawaian Pusdatin PU</h1>
        </div>
        <div class="content">
            <h2 style="margin-top: 0; color: #0f172a; font-size: 20px;">Permintaan Reset Password</h2>
            <p>Halo Admin,</p>
            <p>Kami menerima permintaan untuk mereset password akun Anda di Dashboard Kepegawaian Pusdatin PU. Jika Anda merasa meminta reset password, silakan klik tombol di bawah ini:</p>
            
            <div class="button-container">
                <a href="{{ route('password.validate', $token) }}" class="button">Reset Password Saya</a>
            </div>
            
            <p style="font-size: 14px; color: #64748b;">Link reset password ini akan kadaluarsa dalam waktu 60 menit.</p>
            <p>Jika Anda tidak pernah meminta reset password, abaikan email ini. Akun Anda tetap aman.</p>
            <br>
            <p>Salam hangat,<br><strong>Tim IT Pusdatin PU</strong></p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Pusat Data dan Teknologi Informasi PU. Hak cipta dilindungi.</p>
            <p>Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut ke browser Anda:<br>
            <a href="{{ route('password.validate', $token) }}">{{ route('password.validate', $token) }}</a></p>
        </div>
    </div>
</body>
</html>
