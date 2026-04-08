<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <!-- Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body {
            /* Latar belakang merah pucat pertanda warning/stop */
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            display: flex; align-items: center; justify-content: center;
            height: 100vh; color: #1e293b; overflow: hidden; position: relative;
        }
        
        /* Elemen Dekoratif Awan Blob */
        .blob { position: absolute; filter: blur(80px); z-index: 0; opacity: 0.6; animation: float-blob 10s ease-in-out infinite alternate; }
        .blob-1 { background: #fca5a5; width: 400px; height: 400px; border-radius: 50%; top: -100px; left: -100px; }
        .blob-2 { background: #fde68a; width: 350px; height: 350px; border-radius: 50%; bottom: -50px; right: -50px; animation-delay: -5s; }
        @keyframes float-blob { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(30px, 40px) scale(1.05); } }
        
        /* Kotak Putih Utama Glassmorphism */
        .container {
            text-align: center; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px);
            padding: 50px 40px; border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.06);
            border: 1px solid rgba(255,255,255,0.7); max-width: 500px; width: 90%;
            border-top: 6px solid #dc2626; /* Merah menyala pertanda dilarang */
            z-index: 10; position: relative; overflow: hidden;
            animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(30px);
        }
        @keyframes slideUpFade { to { opacity: 1; transform: translateY(0); } }

        /* Angka Raksasa Samar-samar dihapus sesuai permintaan agar tidak bertabrakan dengan logo */
        
        .logo { width: 80px; margin-bottom: 25px; }

        /* Icon Gembok Terkunci */
        .icon-wrapper { background-color: #fef2f2; color: #dc2626; width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; }
        
        h1 { font-size: 24px; font-weight: 700; color: #7f1d1d; margin-bottom: 12px; }
        p { font-size: 15px; color: #475569; line-height: 1.6; margin-bottom: 30px; }
        
        /* Tombol Kembali yang Premium warna merah gelap */
        .btn-back { display: inline-flex; align-items: center; gap: 8px; background-color: #b91c1c; color: white; padding: 12px 24px; border-radius: 30px; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.2s ease; box-shadow: 0 4px 6px rgba(185, 28, 28, 0.2); }
        .btn-back:hover { background-color: #991b1b; transform: translateY(-3px); box-shadow: 0 8px 15px rgba(185, 28, 28, 0.3); }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    
    <div class="container">
        <img src="{{ asset('assets/Logo_PU.png') }}" alt="Logo Instansi" class="logo">
        
        <div class="icon-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" viewBox="0 0 256 256">
                <path d="M208,80H176V56a48,48,0,0,0-96,0V80H48A16,16,0,0,0,32,96V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V96A16,16,0,0,0,208,80ZM96,56a32,32,0,0,1,64,0V80H96ZM208,208H48V96H208V208Zm-48-52a12,12,0,0,1-12,12H108a12,12,0,0,1,0-24h40A12,12,0,0,1,160,156Z"></path>
            </svg>
        </div>
        
        <h1>MOHON MAAF, AKSES DITOLAK!</h1>
        <p>Anda tidak memiliki otoritas/izin yang memadai untuk mengakses halaman atau konfigurasi spesifik ini.</p>
        
        <a href="{{ route('dashboard') }}" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 256 256">
                <path d="M224,128a8,8,0,0,1-8,8H59.31l58.35,58.34a8,8,0,0,1-11.32,11.32l-72-72a8,8,0,0,1,0-11.32l72-72a8,8,0,0,1,11.32,11.32L59.31,120H216A8,8,0,0,1,224,128Z"></path>
            </svg>
            Kembali ke Beranda
        </a>
    </div>
</body>
</html>
