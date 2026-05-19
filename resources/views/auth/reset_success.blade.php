<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Berhasil Diperbarui - Dashboard Kepegawaian</title>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #142B6F; /* PU Blue */
            --primary-hover: #0F1F55;
            --success: #10b981;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body { 
            /* Awwwards style subtle gradient background (White to very light blue/yellow hint) */
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 50%, #e0e7ff 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Decorative blurred blobs for Awwwards feel */
        .blob-1 {
            position: absolute;
            top: -10%;
            left: -10%;
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, rgba(255,201,40,0.1) 0%, rgba(255,201,40,0) 70%);
            z-index: 0;
            pointer-events: none;
        }
        .blob-2 {
            position: absolute;
            bottom: -10%;
            right: -10%;
            width: 60vw;
            height: 60vw;
            background: radial-gradient(circle, rgba(20,43,111,0.08) 0%, rgba(20,43,111,0) 70%);
            z-index: 0;
            pointer-events: none;
        }

        .auth-wrapper {
            z-index: 10;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .auth-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 20px 40px -15px rgba(20,43,111,0.15), 0 0 1px rgba(20,43,111,0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px 32px;
            text-align: center;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-top {
            height: 56px;
            margin: 0 auto 24px;
            display: block;
        }

        .icon-wrapper {
            width: 72px;
            height: 72px;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--success);
            font-size: 36px;
            animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.2s both;
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        h1 {
            color: var(--primary);
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        p {
            color: var(--text-muted);
            font-size: 13.5px;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            width: 100%;
            transition: all 0.2s ease;
        }

        .btn-login:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(20, 43, 111, 0.25);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .footer-text {
            margin-top: 24px;
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
            z-index: 10;
        }
    </style>
</head>
<body>

    <div class="blob-1"></div>
    <div class="blob-2"></div>

    <div class="auth-wrapper">
        <div class="auth-card">
            
            <div class="icon-wrapper">
                <i class="ph-bold ph-check"></i>
            </div>
            
            <h1>Password Diperbarui!</h1>
            <p>Password akun Anda telah berhasil direset dan diperbarui. Silakan gunakan password baru Anda untuk masuk.</p>
            
            <a href="{{ route('login') }}" class="btn-login">
                <i class="ph-bold ph-sign-in"></i> Masuk Sekarang
            </a>

        </div>

        <!-- Footer Text -->
        <div class="footer-text">
            &copy; {{ date('Y') }} PUSDATIN Kementerian PUPR.
        </div>
    </div>

</body>
</html>
