<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Tidak Ditemukan</title>
    <!-- Font Poppins untuk kesan sejuk dan korporat -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body {
            /* Latar belakang abustract soft biru/abu */
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            display: flex; align-items: center; justify-content: center;
            height: 100vh; color: #1e293b; overflow: hidden; position: relative;
        }
        
        /* Elemen Dekoratif Awan Blob */
        .blob { position: absolute; filter: blur(80px); z-index: 0; opacity: 0.6; animation: float-blob 10s ease-in-out infinite alternate; }
        .blob-1 { background: #cbd5e1; width: 400px; height: 400px; border-radius: 50%; top: -100px; left: -100px; }
        .blob-2 { background: #fde68a; width: 350px; height: 350px; border-radius: 50%; bottom: -50px; right: -50px; animation-delay: -5s; }
        @keyframes float-blob { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(40px, 60px) scale(1.05); } }
        
        /* Kotak Putih Utama Glassmorphism */
        .container {
            text-align: center; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px);
            padding: 50px 40px; border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.06);
            border: 1px solid rgba(255,255,255,0.7); max-width: 500px; width: 90%;
            border-top: 6px solid #fbbf24; /* Garis kuning khas PU */
            z-index: 10; position: relative; overflow: hidden;
            animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(30px);
        }
        @keyframes slideUpFade { to { opacity: 1; transform: translateY(0); } }

        /* Angka Raksasa Samar-samar dihapus sesuai permintaan agar tidak bertabrakan dengan logo */
        
        .logo { width: 80px; margin-bottom: 25px; }

        /* Icon Kaca Pembesar Unik */
        .icon-wrapper { background-color: #fffbeb; color: #d97706; width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; }
        
        h1 { font-size: 24px; font-weight: 700; color: #1e3a8a; margin-bottom: 12px; }
        p { font-size: 15px; color: #475569; line-height: 1.6; margin-bottom: 30px; }
        
        /* Tombol Kembali yang Premium */
        .btn-back { display: inline-flex; align-items: center; gap: 8px; background-color: #1e3a8a; color: white; padding: 12px 24px; border-radius: 30px; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.2s ease; box-shadow: 0 4px 6px rgba(30, 58, 138, 0.2); }
        .btn-back:hover { background-color: #1e40af; transform: translateY(-3px); box-shadow: 0 8px 15px rgba(30, 58, 138, 0.3); }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    
    <div class="container">
        <img src="{{ asset('assets/Logo_PU.png') }}" alt="Logo Instansi" class="logo">
        
        <div class="icon-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" viewBox="0 0 256 256">
                <!-- Inner Base -->
                <path d="M112,40a72,72,0,1,0,72,72A72.08,72.08,0,0,0,112,40Zm0,128a56,56,0,1,1,56-56A56.06,56.06,0,0,1,112,168Z" opacity="0.2"></path>
                <!-- Line Draw Magnifier -->
                <path d="M168,112a56,56,0,1,1-56-56A56.06,56.06,0,0,1,168,112Zm61.66,117.66a8,8,0,0,1-11.32,0l-50.06-50.07a88,88,0,1,1,11.32-11.31l50.06,50.06A8,8,0,0,1,229.66,229.66ZM112,200a88,88,0,1,0-88-88A88.1,88.1,0,0,0,112,200Zm0-160a72,72,0,1,1-72,72A72.08,72.08,0,0,1,112,40Z"></path>
                <path d="M129.66,94.34a8,8,0,0,0-11.32,11.32l16,16a8,8,0,0,0,11.32-11.32Z"></path>
            </svg>
        </div>
        
        <h1>Halaman Tidak Ditemukan</h1>
        <p>Mohon maaf, URL yang Anda sasar sepertinya tidak ada di dalam sistem atau tautannya sudah dipindahkan.</p>
        
        <a href="{{ url('/') }}" class="btn-back">
            <!-- Arrow Left Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 256 256">
                <path d="M224,128a8,8,0,0,1-8,8H59.31l58.35,58.34a8,8,0,0,1-11.32,11.32l-72-72a8,8,0,0,1,0-11.32l72-72a8,8,0,0,1,11.32,11.32L59.31,120H216A8,8,0,0,1,224,128Z"></path>
            </svg>
            Kembali ke Beranda
        </a>
    </div>
</body>
</html>
