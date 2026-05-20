<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - Dashboard Kepegawaian</title>
  
  <!-- Google Fonts: Outfit -->
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Tailwind CSS (Standard classes only to avoid CDN JIT issues) -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  
  <!-- Phosphor Icons -->
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  
  <style>
      body { 
          font-family: 'Outfit', sans-serif; 
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
          background: #ffffff;
          border-radius: 16px;
          box-shadow: 0 20px 40px -15px rgba(20,43,111,0.15), 0 0 1px rgba(20,43,111,0.1);
          width: 100%;
          max-width: 400px; /* Fixed standard width */
          padding: 40px 32px;
      }

      .icon-input {
          position: absolute;
          left: 14px;
          top: 50%;
          transform: translateY(-50%);
          font-size: 18px;
          color: #94a3b8;
          pointer-events: none;
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
          color: #142B6F;
      }

      .form-input {
          width: 100%;
          padding: 10px 14px 10px 40px;
          border: 1px solid #cbd5e1;
          border-radius: 8px;
          font-size: 13.5px;
          color: #1e293b;
          transition: all 0.2s ease;
          background-color: #fafafa;
      }

      .form-input:focus {
          outline: none;
          border-color: #142B6F;
          background-color: #ffffff;
          box-shadow: 0 0 0 3px rgba(20, 43, 111, 0.1);
      }
      
      .form-input::placeholder {
          color: #94a3b8;
      }

      .btn-submit {
          background-color: #142B6F; /* PU Blue */
          color: white;
          width: 100%;
          padding: 12px;
          border-radius: 8px;
          font-weight: 600;
          font-size: 14px;
          transition: all 0.2s ease;
          display: flex;
          justify-content: center;
          align-items: center;
          gap: 8px;
      }

      .btn-submit:hover {
          background-color: #0F1F55;
          transform: translateY(-1px);
          box-shadow: 0 4px 12px rgba(20, 43, 111, 0.25);
      }
      
      .btn-submit:active {
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
  <link rel="icon" type="image/png" href="{{ asset('assets/Logo_PU.png') }}">
</head>
<body>
  
  <div class="blob-1"></div>
  <div class="blob-2"></div>

  <div class="auth-wrapper">
      <div class="auth-card">
          
          <!-- Header / Logo -->
          <div class="flex flex-col items-center mb-8 text-center">
              <img src="{{ asset('assets/Logo_PU.png') }}" alt="Logo PU" class="h-16 mb-4">
              <h2 class="text-xl font-bold text-[#142B6F] tracking-tight">
                  Admin PUSDATIN
              </h2>
              <p class="text-[13px] text-slate-500 mt-1">
                  Silakan masukkan password baru Anda
              </p>
          </div>

          <!-- Error Validation Box -->
          @if ($errors->any())
            <div class="bg-red-50 border border-red-100 text-red-600 px-3 py-2 rounded-lg mb-5 flex items-start gap-2 text-[13px]">
              <i class="ph-fill ph-warning-circle text-red-500 text-base mt-0.5"></i>
              <div>
                @foreach ($errors->all() as $error)
                  <p class="mb-0.5 last:mb-0">{{ $error }}</p>
                @endforeach
              </div>
            </div>
          @endif

          <!-- Form -->
          <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
              @csrf
              
              <!-- Email Field -->
              <div>
                  <label for="email" class="block text-[12px] font-bold text-slate-700 mb-1.5">Email Address</label>
                  <div class="relative">
                      <i class="ph-bold ph-envelope-simple icon-input"></i>
                      <input type="email" id="email" name="email" 
                             placeholder="admin@pusdatin.go.id" 
                             value="{{ old('email', $email ?? '') }}"
                             class="form-input" 
                             required readonly>
                  </div>
              </div>

              <!-- Password Baru Field -->
              <div>
                  <label for="password" class="block text-[12px] font-bold text-slate-700 mb-1.5">Password Baru</label>
                  <div class="relative">
                      <i class="ph-bold ph-lock-key icon-input"></i>
                      <input type="password" id="password" name="password" 
                             placeholder="Minimal 8 karakter" 
                             class="form-input pr-10" 
                             required>
                      <i class="ph-bold ph-eye-slash icon-toggle" id="toggleIcon1"></i>
                  </div>
              </div>

              <!-- Konfirmasi Password Field -->
              <div>
                  <label for="password_confirmation" class="block text-[12px] font-bold text-slate-700 mb-1.5">Konfirmasi Password</label>
                  <div class="relative">
                      <i class="ph-bold ph-lock-key icon-input"></i>
                      <input type="password" id="password_confirmation" name="password_confirmation" 
                             placeholder="Ketik ulang password" 
                             class="form-input pr-10" 
                             required>
                      <i class="ph-bold ph-eye-slash icon-toggle" id="toggleIcon2"></i>
                  </div>
              </div>

              <!-- Submit Button -->
              <div class="pt-3">
                  <button type="submit" class="btn-submit">
                      <i class="ph-bold ph-sign-in"></i> Simpan Password
                  </button>
              </div>
          </form>
          
      </div>

      <!-- Footer Text -->
      <div class="footer-text">
          &copy; {{ date('Y') }} PUSDATIN Kementerian PU.
      </div>
  </div>

  <script>
    function setupPasswordToggle(toggleId, inputId) {
        const toggleIcon = document.getElementById(toggleId);
        const inputField = document.getElementById(inputId);
        
        toggleIcon.addEventListener('click', function() {
            const type = inputField.getAttribute('type') === 'password' ? 'text' : 'password';
            inputField.setAttribute('type', type);
            
            if (type === 'text') {
                this.classList.remove('ph-eye-slash');
                this.classList.add('ph-eye');
            } else {
                this.classList.remove('ph-eye');
                this.classList.add('ph-eye-slash');
            }
        });
    }

    setupPasswordToggle('toggleIcon1', 'password');
    setupPasswordToggle('toggleIcon2', 'password_confirmation');
    
    const emailInput = document.getElementById('email');
    if (!emailInput.value) {
        emailInput.removeAttribute('readonly');
    } else {
        emailInput.style.backgroundColor = '#f1f5f9';
        emailInput.style.color = '#64748b';
    }
  </script>
</body>
</html>
