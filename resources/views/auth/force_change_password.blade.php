<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ubah Password Wajib - Dashboard Kepegawaian</title>

  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>

  <style>
      body {
          font-family: 'Outfit', sans-serif;
          background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 50%, #e0e7ff 100%);
          min-height: 100vh;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          position: relative;
          overflow: hidden;
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
          background: #ffffff;
          border-radius: 16px;
          box-shadow: 0 20px 40px -15px rgba(20,43,111,0.15), 0 0 1px rgba(20,43,111,0.1);
          width: 100%;
          max-width: 400px;
          padding: 40px 32px;
          animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
      }

      @keyframes slideUp {
          from { opacity: 0; transform: translateY(24px); }
          to   { opacity: 1; transform: translateY(0); }
      }

      /* Warning banner */
      .warning-banner {
          background: #fffbeb;
          border: 1.5px solid #fde68a;
          border-radius: 10px;
          padding: 10px 14px;
          display: flex;
          align-items: flex-start;
          gap: 10px;
          font-size: 13px;
          color: #92400e;
          font-weight: 500;
          margin-bottom: 20px;
      }

      .icon-input {
          position: absolute; left: 14px; top: 50%;
          transform: translateY(-50%);
          font-size: 18px; color: #94a3b8;
          pointer-events: none;
      }

      .icon-toggle {
          position: absolute; right: 14px; top: 50%;
          transform: translateY(-50%);
          font-size: 18px; color: #94a3b8;
          cursor: pointer; transition: color 0.2s;
      }
      .icon-toggle:hover { color: #142B6F; }

      .form-input {
          width: 100%;
          padding: 10px 42px 10px 42px;
          border: 1.5px solid #e2e8f0;
          border-radius: 10px;
          font-size: 14px; color: #1e293b;
          transition: all 0.2s ease;
          background-color: #f8fafc;
          font-family: 'Outfit', sans-serif;
      }
      .form-input:focus {
          outline: none;
          border-color: #142B6F;
          background-color: #ffffff;
          box-shadow: 0 0 0 4px rgba(20,43,111,0.1);
      }
      .form-input::placeholder { color: #94a3b8; }

      .btn-submit {
          background-color: #142B6F; color: white;
          width: 100%; padding: 13px;
          border-radius: 10px;
          font-weight: 600; font-size: 15px;
          font-family: 'Outfit', sans-serif;
          transition: all 0.2s ease;
          display: flex; justify-content: center; align-items: center; gap: 8px;
          box-shadow: 0 4px 6px -1px rgba(20,43,111,0.2);
      }
      .btn-submit:hover {
          background-color: #0F1F55;
          transform: translateY(-1px);
          box-shadow: 0 6px 12px rgba(20,43,111,0.3);
      }
      .btn-submit:active { transform: translateY(0); }

      .footer-text {
          margin-top: 24px; font-size: 12px;
          color: #64748b; font-weight: 500; z-index: 10;
      }
  </style>
  <link rel="icon" type="image/png" href="{{ asset('assets/Logo_PU.png') }}">
</head>
<body>

  <div class="blob-1"></div>
  <div class="blob-2"></div>

  <div class="auth-wrapper">
      <div class="auth-card">

          <!-- Header / Logo -->
          <div class="flex flex-col items-center mb-7 text-center">
              <img src="{{ asset('assets/Logo_PU.png') }}" alt="Logo PU" class="h-16 mb-3">
              <h2 class="text-xl font-bold text-[#142B6F] tracking-tight">Ubah Password Wajib</h2>
              <p class="text-[13px] text-slate-500 mt-1 max-w-[260px] leading-relaxed">
                  Password default Anda masih menggunakan NIP. Segera ganti sebelum melanjutkan.
              </p>
          </div>

          <!-- Warning Banner -->
          @if (session('warning'))
          <div class="warning-banner">
              <i class="ph-fill ph-warning" style="font-size:18px;flex-shrink:0;margin-top:1px;"></i>
              <span>{{ session('warning') }}</span>
          </div>
          @endif

          <!-- Error Validation -->
          @if ($errors->any())
          <div style="background:#fef2f2;border:1.5px solid #fca5a5;border-radius:10px;padding:10px 14px;display:flex;align-items:flex-start;gap:10px;font-size:13px;color:#b91c1c;font-weight:500;margin-bottom:20px;">
              <i class="ph-fill ph-warning-circle" style="font-size:18px;flex-shrink:0;margin-top:1px;"></i>
              <div>
                  @foreach ($errors->all() as $error)
                      <p style="margin:0 0 2px;">{{ $error }}</p>
                  @endforeach
              </div>
          </div>
          @endif

          <!-- Form -->
          <form method="POST" action="{{ route('password.force-change.update') }}" class="space-y-4">
              @csrf

              <!-- Password Saat Ini (NIP) -->
              <div>
                  <label for="current_password" class="block text-[12px] font-bold text-slate-700 mb-1.5">
                      Password Saat Ini (NIP)
                  </label>
                  <div class="relative">
                      <i class="ph-bold ph-lock-simple icon-input"></i>
                      <input type="password" id="current_password" name="current_password"
                             placeholder="Masukkan NIP Anda"
                             class="form-input"
                             required>
                      <i class="ph-bold ph-eye-slash icon-toggle" id="toggleIcon0"></i>
                  </div>
              </div>

              <!-- Password Baru -->
              <div>
                  <label for="password" class="block text-[12px] font-bold text-slate-700 mb-1.5">
                      Password Baru
                  </label>
                  <div class="relative">
                      <i class="ph-bold ph-lock-key icon-input"></i>
                      <input type="password" id="password" name="password"
                             placeholder="Minimal 8 karakter"
                             class="form-input"
                             required>
                      <i class="ph-bold ph-eye-slash icon-toggle" id="toggleIcon1"></i>
                  </div>
              </div>

              <!-- Konfirmasi Password -->
              <div>
                  <label for="password_confirmation" class="block text-[12px] font-bold text-slate-700 mb-1.5">
                      Konfirmasi Password Baru
                  </label>
                  <div class="relative">
                      <i class="ph-bold ph-lock-key icon-input"></i>
                      <input type="password" id="password_confirmation" name="password_confirmation"
                             placeholder="Ketik ulang password baru"
                             class="form-input"
                             required>
                      <i class="ph-bold ph-eye-slash icon-toggle" id="toggleIcon2"></i>
                  </div>
              </div>

              <!-- Submit -->
              <div class="pt-2">
                  <button type="submit" class="btn-submit">
                      <i class="ph-bold ph-check-circle"></i> Simpan Password
                  </button>
              </div>
          </form>

          <!-- Logout Link -->
          <div class="mt-5 text-center">
              <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit"
                          class="text-[13px] font-semibold text-slate-400 hover:text-[#142B6F] transition-colors flex items-center gap-1 mx-auto">
                      <i class="ph-bold ph-sign-out"></i> Keluar dari Akun
                  </button>
              </form>
          </div>

      </div>

      <div class="footer-text">
          &copy; {{ date('Y') }} PUSDATIN Kementerian PU.
      </div>
  </div>

  <script>
    function setupPasswordToggle(toggleId, inputId) {
        const icon  = document.getElementById(toggleId);
        const input = document.getElementById(inputId);
        if (!icon || !input) return;
        icon.addEventListener('click', function () {
            const isPass = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPass ? 'text' : 'password');
            this.classList.toggle('ph-eye-slash', !isPass);
            this.classList.toggle('ph-eye', isPass);
        });
    }
    setupPasswordToggle('toggleIcon0', 'current_password');
    setupPasswordToggle('toggleIcon1', 'password');
    setupPasswordToggle('toggleIcon2', 'password_confirmation');
  </script>

</body>
</html>
