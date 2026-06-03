<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Dalam Pemeliharaan</title>
    <!-- Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }
        body {
            /* Latar belakang dengan warna kalem */
            background: linear-gradient(135deg, #f0fdf4 0%, #e0e7ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #1f2937;
            overflow: hidden;
            position: relative;
        }
        /* Efek cahaya/blob melayang di belakang layar */
        .blob {
            position: absolute;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.6;
            animation: float-blob 10s ease-in-out infinite alternate;
        }
        .blob-1 {
            background: #93c5fd; /* Soft Blue */
            width: 400px;
            height: 400px;
            border-radius: 50%;
            top: -100px;
            left: -100px;
        }
        .blob-2 {
            background: #fde68a; /* Soft Yellow PU */
            width: 350px;
            height: 350px;
            border-radius: 50%;
            bottom: -50px;
            right: -50px;
            animation-delay: -5s;
        }
        
        @keyframes float-blob {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, 60px) scale(1.05); }
        }

        .container {
            text-align: center;
            /* Efek Kaca (Glassmorphism) */
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(12px);
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.06);
            border: 1px solid rgba(255,255,255,0.7);
            max-width: 500px;
            width: 90%;
            border-top: 6px solid #1e3a8a;
            z-index: 10;

            /* Animasi Kotak Masuk (Fade in Slide Up) */
            animation: slideUpFade 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        @keyframes slideUpFade {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            width: 90px;
            margin-bottom: 28px;
        }

        .icon-wrapper {
            background-color: #eff6ff;
            color: #1e40af;
            width: 76px;
            height: 76px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px auto;
            position: relative;
        }

        /* Animasi Gear Berputar Tanpa Henti */
        .gear-icon {
            animation: spinGear 8s linear infinite;
        }

        @keyframes spinGear {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 16px;
        }
        
        /* Animasi Titik 3 pada judul */
        .loading-dots::after {
            content: '.';
            animation: dots 1.8s steps(5, end) infinite;
        }

        @keyframes dots {
            0%, 20% { content: ''; }
            40% { content: '.'; }
            60% { content: '..'; }
            80%, 100% { content: '...'; }
        }

        p {
            font-size: 15px;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .badge-pulse {
            display: inline-block;
            background-color: #fef3c7;
            color: #b45309;
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.3px;
            
            /* Animasi Denyut Jantung (Radar Pulse) keliling tombol */
            animation: pulseBadge 2.5s infinite;
        }

        @keyframes pulseBadge {
            0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
            70% { box-shadow: 0 0 0 12px rgba(245, 158, 11, 0); }
            100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }
    </style>
</head>
<body>
    <!-- Elemen Dekoratif Background (Blob) -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div class="container">
        <!-- Logo Instansi Melayang -->
        <img src="{{ asset('assets/Logo_PU.png') }}" alt="Logo Instansi" class="logo">
        
        <!-- Icon Gear Mewah Berputar -->
        <div class="icon-wrapper">
            <svg class="gear-icon" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 256 256">
                <!-- Single Clean Solid Gear -->
                <path d="M128,80a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,80Zm0,80a32,32,0,1,1,32-32A32,32,0,0,1,128,160Zm88-29.84q.06-2.16,0-4.32l14.92-18.64a8,8,0,0,0,1.48-7.06,107.21,107.21,0,0,0-10.88-26.25,8,8,0,0,0-6-3.93l-23.72-2.64q-1.48-1.56-3-3L186,40.54a8,8,0,0,0-3.94-6,107.71,107.71,0,0,0-26.25-10.87,8,8,0,0,0-7.06,1.49L130.16,40Q128,40,125.84,40L107.2,25.11a8,8,0,0,0-7.06-1.48A107.6,107.6,0,0,0,73.89,34.51a8,8,0,0,0-3.93,6L67.32,64.27q-1.56,1.49-3,3L40.54,70a8,8,0,0,0-6,3.94,107.71,107.71,0,0,0-10.87,26.25,8,8,0,0,0,1.49,7.06L40,125.84Q40,128,40,130.16L25.11,148.8a8,8,0,0,0-1.48,7.06,107.21,107.21,0,0,0,10.88,26.25,8,8,0,0,0,6,3.93l23.72,2.64q1.49,1.56,3,3L70,215.46a8,8,0,0,0,3.94,6,107.71,107.71,0,0,0,26.25,10.87,8,8,0,0,0,7.06-1.49L125.84,216q2.16.06,4.32,0l18.64,14.92a8,8,0,0,0,7.06,1.48,107.21,107.21,0,0,0,26.25-10.88,8,8,0,0,0,3.93-6l2.64-23.72q1.56-1.48,3-3L215.46,186a8,8,0,0,0,6-3.94,107.71,107.71,0,0,0,10.87-26.25,8,8,0,0,0-1.49-7.06ZM128,216a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Z"></path>
            </svg>
        </div>
        
        <!-- Judul dengan Animasi Titik Loader -->
        <h1>Sistem Dalam Pemeliharaan<span class="loading-dots"></span></h1>
        
        <p>Mohon maaf, aplikasi sementara tidak dapat diakses. Kami sedang melaksanakan pembaruan sistem rutin untuk meningkatkan kualitas layanan.</p>
        
        <!-- Badge dengan efek radar denyut -->
        <div class="badge-pulse">
            Sistem akan segera kembali normal
        </div>
    </div>
</body>
</html>
