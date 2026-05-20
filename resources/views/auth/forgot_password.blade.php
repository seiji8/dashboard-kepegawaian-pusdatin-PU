<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Kata Sandi - Dashboard Kepegawaian</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
      :root {
          --primary: #142B6F;
          --primary-hover: #0F1F55;
          --accent: #FFC928;
          --card-bg: #ffffff;
      }
      * { font-family: 'Outfit', sans-serif; }

      body {
          background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 50%, #e0e7ff 100%);
          min-height: 100vh;
          display: flex; flex-direction: column;
          align-items: center; justify-content: center;
          position: relative; overflow: hidden;
      }
      .blob-1 {
          position: absolute; top: -10%; left: -10%;
          width: 50vw; height: 50vw;
          background: radial-gradient(circle, rgba(255,201,40,0.1) 0%, rgba(255,201,40,0) 70%);
          z-index: 0; pointer-events: none;
      }
      .blob-2 {
          position: absolute; bottom: -10%; right: -10%;
          width: 60vw; height: 60vw;
          background: radial-gradient(circle, rgba(20,43,111,0.08) 0%, rgba(20,43,111,0) 70%);
          z-index: 0; pointer-events: none;
      }
      .auth-wrapper {
          z-index: 10; width: 100%;
          display: flex; flex-direction: column;
          align-items: center; padding: 20px;
      }
      .auth-card {
          background: var(--card-bg);
          border-radius: 12px;
          box-shadow: 0 10px 30px -5px rgba(20,43,111,0.15), 0 0 1px rgba(0,0,0,0.1);
          width: 100%; max-width: 900px;
          height: 540px; /* Fixed — sama persis login page, TIDAK berubah */
          display: flex;
          overflow: hidden;
          animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
      }
      @keyframes slideUp {
          from { opacity: 0; transform: translateY(30px); }
          to   { opacity: 1; transform: translateY(0); }
      }
      .image-section {
          width: 35%;
          background-image: url('{{ asset('assets/login_sebelah.png') }}');
          background-size: cover;
          background-position: left top;
          background-repeat: no-repeat;
      }
      .form-section {
          width: 65%; padding: 40px 64px;
          display: flex; flex-direction: column;
          justify-content: center;
          overflow: hidden; /* Konten tidak jebol keluar card */
      }

      /* ===== NOTIFICATION SLOT =====
         Slot ini SELALU ada di DOM dengan tinggi tetap 52px.
         Kalau kosong = transparent. Kalau ada notif = muncul smooth.
         Card TIDAK akan berubah ukuran sama sekali. */
      .notif-slot {
          height: 52px;
          margin-bottom: 14px;
          display: flex;
          align-items: center;
      }
      .notif-success {
          width: 100%;
          background-color: #f0fdf4;
          border: 1.5px solid #86efac;
          border-radius: 10px;
          padding: 10px 14px;
          display: flex; align-items: center; gap: 10px;
          font-size: 13px; color: #15803d; font-weight: 500;
          animation: fadeInDown 0.35s ease both;
      }
      .notif-error {
          width: 100%;
          background-color: #fef2f2;
          border: 1.5px solid #fca5a5;
          border-radius: 10px;
          padding: 10px 14px;
          display: flex; align-items: center; gap: 10px;
          font-size: 13px; color: #b91c1c; font-weight: 500;
          animation: fadeInDown 0.35s ease both;
      }
      @keyframes fadeInDown {
          from { opacity: 0; transform: translateY(-8px); }
          to   { opacity: 1; transform: translateY(0); }
      }

      .icon-input {
          position: absolute; left: 14px; top: 50%;
          transform: translateY(-50%);
          font-size: 18px; color: #94a3b8; pointer-events: none;
      }
      .form-input {
          width: 100%; padding: 12px 14px 12px 42px;
          border: 1.5px solid #e2e8f0; border-radius: 10px;
          font-size: 14px; color: #1e293b;
          transition: all 0.2s ease; background-color: #f8fafc;
      }
      .form-input:focus {
          outline: none; border-color: var(--primary);
          background-color: #fff;
          box-shadow: 0 0 0 4px rgba(20,43,111,0.1);
      }
      .form-input::placeholder { color: #94a3b8; }
      .btn-submit {
          background-color: var(--primary); color: white;
          width: 100%; padding: 14px; border-radius: 10px;
          font-weight: 600; font-size: 15px;
          transition: all 0.2s ease;
          display: flex; justify-content: center; align-items: center;
          box-shadow: 0 4px 6px -1px rgba(20,43,111,0.2);
      }
      .btn-submit:hover {
          background-color: var(--primary-hover);
          transform: translateY(-1px);
          box-shadow: 0 6px 12px rgba(20,43,111,0.3);
      }
      .btn-submit:active { transform: translateY(0); }
      .footer-text {
          margin-top: 32px; font-size: 13px;
          color: #64748b; font-weight: 500; z-index: 10;
      }

      @media (max-width: 768px) {
          .auth-card { flex-direction: column; max-width: 440px; height: auto; }
          .image-section { width: 100%; height: 160px; }
          .form-section { width: 100%; padding: 32px 24px; overflow: visible; }
          .notif-slot { height: auto; }
      }
  </style>
  <link rel="icon" type="image/png" href="{{ asset('assets/Logo_PU.png') }}">
</head>
<body>

  <div class="blob-1"></div>
  <div class="blob-2"></div>

  <div class="auth-wrapper">
      <div class="auth-card">

          <!-- Left: Image (proporsi tetap karena card height fixed) -->
          <div class="image-section"></div>

          <!-- Right: Form -->
          <div class="form-section">

              <!-- Header Logo & Title -->
              <div class="flex flex-col items-center mb-4">
                  <img src="{{ asset('assets/Logo_PU.png') }}" alt="Logo" class="h-14 mb-3">
                  <h2 class="text-2xl font-bold tracking-tight">
                      <span class="text-[#FFC928]">Dashboard</span> <span class="text-[#142B6F]">Alert</span>
                  </h2>
                  <div class="mt-3 text-center">
                      <h3 class="text-lg font-bold text-[#142B6F] mb-1">Atur Ulang Password</h3>
                      <p class="text-[13px] text-slate-500 leading-relaxed max-w-[260px]">
                          Masukkan email akun PU kamu untuk atur ulang kata sandi.
                      </p>
                  </div>
              </div>

              {{-- ===== NOTIFICATION SLOT (FIXED HEIGHT 52px) =====
                   Kosong = tak kelihatan, Ada session/error = muncul smooth.
                   Ukuran CARD TIDAK BERUBAH sama sekali. --}}
              <div class="notif-slot">
                  @if (session('status'))
                      <div class="notif-success">
                          <i class="ph-fill ph-check-circle" style="font-size:18px;flex-shrink:0;"></i>
                          <span>Link reset password telah dikirim. Silakan cek email inbox Anda.</span>
                      </div>
                  @elseif ($errors->any())
                      <div class="notif-error">
                          <i class="ph-fill ph-warning-circle" style="font-size:18px;flex-shrink:0;"></i>
                          <span>{{ $errors->first() }}</span>
                      </div>
                  @endif
              </div>

              <!-- Form -->
              <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
                  @csrf
                  <div>
                      <label for="email" class="block text-[13px] font-bold text-slate-700 mb-1.5 ml-1">Email</label>
                      <div class="relative">
                          <input type="email" id="email" name="email"
                                 placeholder="Masukkan Email"
                                 value="{{ old('email') }}"
                                 class="form-input @error('email') border-red-500 ring-1 ring-red-500 @enderror"
                                 required autofocus>
                          <i class="ph-bold ph-envelope-simple icon-input"></i>
                      </div>
                  </div>

                  <div class="pt-1">
                      <button type="submit" class="btn-submit">
                          Atur Ulang Kata Sandi
                      </button>
                  </div>

                  <div class="flex justify-start pt-1">
                      <a href="{{ route('login') }}" class="flex items-center gap-1.5 text-[13px] font-bold text-[#142B6F] hover:text-[#FFC928] transition-colors">
                          <i class="ph-bold ph-caret-left"></i> Kembali Ke Login
                      </a>
                  </div>
              </form>

          </div>
      </div>

      <div class="footer-text">
          &copy; {{ date('Y') }} PUSDATIN Kementerian PU.
      </div>
  </div>

</body>
</html>
