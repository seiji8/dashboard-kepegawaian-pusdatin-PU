<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f3f4f6;
            color: #374151;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .error-container {
            text-align: center;
            padding: 40px;
            max-width: 600px;
            width: 90%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .decorative-circle {
            position: absolute;
            background: #eff6ff;
            border-radius: 50%;
            z-index: 0;
        }

        .circle-1 {
            width: 200px;
            height: 200px;
            top: -50px;
            left: -50px;
        }

        .circle-2 {
            width: 150px;
            height: 150px;
            bottom: -30px;
            right: -30px;
            background: #fef3c7;
        }

        .content {
            position: relative;
            z-index: 10;
        }

        .error-code {
            font-size: 8rem;
            font-weight: 800;
            line-height: 1;
            color: #1e3a8a; /* Primary Blue */
            margin-bottom: 10px;
            text-shadow: 4px 4px 0px rgba(30, 58, 138, 0.1);
            animation: pulse 3s infinite ease-in-out;
        }

        .icon-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 50%;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.2);
            animation: float 3s ease-in-out infinite;
        }

        .error-title {
            font-size: 24px;
            font-weight: 700;
            color: #111;
            margin-bottom: 12px;
        }

        .error-message {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background-color: #1e3a8a;
            color: white;
            padding: 12px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(30, 58, 138, 0.3);
        }

        .btn-home:hover {
            background-color: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(30, 58, 138, 0.4);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        /* Mobile Responsive */
        @media (max-width: 640px) {
            .error-code {
                font-size: 6rem;
            }
            .error-container {
                padding: 30px 20px;
                width: 95%;
            }
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo_PU.png') }}">
</head>
<body>

    <div class="error-container">
        <div class="decorative-circle circle-1"></div>
        <div class="decorative-circle circle-2"></div>

        <div class="content">
            <div class="icon-box">
                <i class="ph-fill ph-lock-key" style="font-size: 40px;"></i>
            </div>
            
            <h1 class="error-title">Akses Dibatasi</h1>
            
            <p class="error-message">
                Maaf, Anda tidak memiliki izin untuk mengakses halaman <b>Daftar Admin</b>.<br>
                Halaman ini khusus untuk Super Admin.
            </p>

            <a href="{{ route('dashboard') }}" class="btn-home">
                <i class="ph-bold ph-arrow-left"></i>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

</body>
</html>
