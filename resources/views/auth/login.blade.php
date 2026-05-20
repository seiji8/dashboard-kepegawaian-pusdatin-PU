<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Dashboard Kepegawaian</title>
  
  <!-- Google Fonts: Outfit -->
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  
  <!-- Phosphor Icons -->
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  
  <style>
      :root {
          --primary: #142B6F; /* PU Blue */
          --primary-hover: #0F1F55;
          --accent: #FFC928; /* PU Yellow */
          --bg-color: #f8fafc;
          --card-bg: #ffffff;
      }

      * {
          font-family: 'Outfit', sans-serif;
      }

      body { 
          background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 50%, #e0e7ff 100%);
          min-height: 100vh;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          position: relative;
          overflow: hidden;
      }

      /* Decorative blurred blobs */
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
          border-radius: 12px;
          box-shadow: 0 10px 30px -5px rgba(20,43,111,0.15), 0 0 1px rgba(0,0,0,0.1);
          width: 100%;
          max-width: 900px;
          display: flex;
          overflow: hidden;
          animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
      }

      @keyframes slideUp {
          from { opacity: 0; transform: translateY(30px); }
          to { opacity: 1; transform: translateY(0); }
      }

      .image-section {
          width: 35%;
          min-height: 540px;
          background-image: url('{{ asset('assets/login_sebelah.png') }}');
          background-size: cover;
          background-position: left top;
          background-repeat: no-repeat;
          position: relative;
      }

      .image-overlay {
          position: absolute;
          inset: 0;
          /* Removed overlay to match original format */
      }

      .form-section {
          width: 65%;
          padding: 48px 64px;
          display: flex;
          flex-direction: column;
          justify-content: center;
      }

      .icon-input {
          position: absolute;
          left: 14px;
          top: 50%;
          transform: translateY(-50%);
          font-size: 18px;
          color: #94a3b8;
          pointer-events: none;
          transition: color 0.2s;
      }

      .icon-toggle {
          position: absolute;
          right: 14px;
          top: 50%;
          transform: translateY(-50%);
          font-size: 18px;
          color: #94a3b8;
          cursor: pointer;
          transition: color 0.2s;
      }
      
      .icon-toggle:hover {
          color: var(--primary);
      }

      .form-input {
          width: 100%;
          padding: 12px 14px 12px 42px;
          border: 1.5px solid #e2e8f0;
          border-radius: 10px;
          font-size: 14px;
          color: #1e293b;
          transition: all 0.2s ease;
          background-color: #f8fafc;
      }

      .form-input:focus {
          outline: none;
          border-color: var(--primary);
          background-color: #ffffff;
          box-shadow: 0 0 0 4px rgba(20, 43, 111, 0.1);
      }

      .form-input:focus ~ .icon-input {
          color: var(--primary);
      }
      
      .form-input::placeholder {
          color: #94a3b8;
      }

      .btn-submit {
          background-color: var(--primary); 
          color: white;
          width: 100%;
          padding: 14px;
          border-radius: 10px;
          font-weight: 600;
          font-size: 15px;
          transition: all 0.2s ease;
          display: flex;
          justify-content: center;
          align-items: center;
          gap: 8px;
          box-shadow: 0 4px 6px -1px rgba(20, 43, 111, 0.2);
      }

      .btn-submit:hover {
          background-color: var(--primary-hover);
          transform: translateY(-1px);
          box-shadow: 0 6px 12px rgba(20, 43, 111, 0.3);
      }
      
      .btn-submit:active {
          transform: translateY(0);
      }

      .footer-text {
          margin-top: 32px;
          font-size: 13px;
          color: #64748b;
          font-weight: 500;
          z-index: 10;
      }

      /* Responsive adjustments */
      @media (max-width: 768px) {
          .auth-card {
              flex-direction: column;
              max-width: 440px;
              height: auto;
          }
          .image-section {
              width: 100%;
              height: 160px;
          }
          .form-section {
              width: 100%;
              padding: 32px 24px;
          }
      }
  </style>
  <link rel="icon" type="image/png" href="{{ asset('assets/Logo_PU.png') }}">
</head>
<body>
  
  <div class="blob-1"></div>
  <div class="blob-2"></div>

  <div class="auth-wrapper">
      <div class="auth-card">
          
          <!-- Left: Image Section -->
          <div class="image-section">
              <div class="image-overlay"></div>
          </div>

          <!-- Right: Form Section -->
          <div class="form-section">
              
              <div class="flex flex-col items-center mb-8">
                  <img src="{{ asset('assets/Logo_PU.png') }}" alt="Logo" class="h-16 mb-3">
                  <h2 class="text-2xl font-bold tracking-tight">
                      <span class="text-[#FFC928]">Dashboard</span> <span class="text-[#142B6F]">Alert</span>
                  </h2>
                  <p class="text-[14px] text-slate-500 mt-1">
                      Silakan masuk untuk mengelola data kepegawaian.
                  </p>
              </div>

              <!-- Flash Message untuk sukses ganti password / logout dll -->
              @if (session('status'))
                  <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-start gap-2 text-[13px]">
                      <i class="ph-fill ph-check-circle text-green-500 text-lg mt-0.5"></i>
                      <p class="mb-0">{{ session('status') }}</p>
                  </div>
              @endif

              <form action="{{ route('login') }}" method="POST" class="space-y-5">
                  @csrf
                  
                  <div>
                      <label for="email" class="block text-[13px] font-bold text-slate-700 mb-1.5 ml-1">Alamat Email</label>
                      <div class="relative">
                          <input type="email" id="email" name="email" 
                                 placeholder="admin@pusdatin.go.id" 
                                 value="{{ old('email') }}"
                                 class="form-input @error('email') border-red-500 ring-1 ring-red-500 @enderror" 
                                 required autofocus>
                          <i class="ph-bold ph-envelope-simple icon-input"></i>
                      </div>
                      @error('email')
                          <p class="text-red-500 text-xs mt-1.5 ml-1 font-medium">{{ $message }}</p>
                      @enderror
                  </div>

                  <div>
                      <label for="password" class="block text-[13px] font-bold text-slate-700 mb-1.5 ml-1">Kata Sandi</label>
                      <div class="relative">
                          <input type="password" id="password" name="password" 
                                 placeholder="Masukkan kata sandi Anda" 
                                 class="form-input pr-10 @error('password') border-red-500 ring-1 ring-red-500 @enderror" 
                                 required>
                          <i class="ph-bold ph-lock-key icon-input"></i>
                          <i class="ph-bold ph-eye-slash icon-toggle" id="toggleIcon"></i>
                      </div>
                      @error('password')
                          <p class="text-red-500 text-xs mt-1.5 ml-1 font-medium">{{ $message }}</p>
                      @enderror
                  </div>

                  <div class="flex justify-end pt-1">
                      <a href="{{ route('password.request') }}" class="text-[13px] font-semibold text-[#142B6F] hover:text-[#FFC928] hover:underline transition-colors">
                          Lupa Kata Sandi?
                      </a>
                  </div>

                  <div class="pt-2">
                      <button type="submit" class="btn-submit">
                          Masuk Sekarang <i class="ph-bold ph-arrow-right"></i>
                      </button>
                  </div>
              </form>

          </div>
      </div>

      <!-- Footer Text -->
      <div class="footer-text">
          &copy; {{ date('Y') }} PUSDATIN Kementerian PU.
      </div>
  </div>

  <script>
    const toggleIcon = document.getElementById('toggleIcon');
    const passwordInput = document.getElementById('password');
    
    toggleIcon.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        if (type === 'text') {
            this.classList.remove('ph-eye-slash');
            this.classList.add('ph-eye');
        } else {
            this.classList.remove('ph-eye');
            this.classList.add('ph-eye-slash');
        }
    });
  </script>
</body>
</html>
