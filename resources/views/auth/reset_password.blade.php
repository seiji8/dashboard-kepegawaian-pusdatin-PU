<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - DashboardAlert</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
      body { font-family: 'Poppins', sans-serif; }
      .hidden { display: none; }
      .icon-input {
          position: absolute;
          left: 12px;
          top: 50%;
          transform: translateY(-50%);
          font-size: 24px;
          color: #94a3b8; /* Slate-400 */
      }
      .icon-toggle {
          position: absolute;
          right: 12px;
          top: 50%;
          transform: translateY(-50%);
          font-size: 24px;
          color: #64748B;
          cursor: pointer;
      }
  </style>
</head>
<body class="bg-gray-50 overflow-hidden">
  <div class="flex h-screen justify-center items-center bg-gray-50">
    <div class="flex bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden" style="width: 800px; height: 500px;">
      
      <!-- Left: Image (40%) -->
      <div class="w-2/5 h-full bg-cover" style="background-image: url('{{ asset('assets/login_sebelah.png') }}'); background-size: 100% 100%; background-position: center;">
      </div>

      <!-- Right: Form (60%) -->
      <div class="w-3/5 h-full flex flex-col justify-center items-center p-8 bg-white overflow-y-auto">
        
        <div class="flex flex-col items-center mb-8">
          <img src="{{ asset('assets/Logo_PU.png') }}" alt="Logo" class="h-20 mb-2">
          <h2 class="text-2xl" style="font-weight: 700; letter-spacing: -0.5px;">
            <span style="color: #FFC928;">Dashboard</span> <span style="color: #142B6F;">Alert</span>
          </h2>
        </div>
        
        <div class="w-full text-left mb-2 max-w-[340px] mx-auto">
          <h3 class="font-bold text-[#142B6F] text-xl" style="color: #142B6F;">Reset Password</h3>
          <p class="text-xs text-gray-500 mt-2">
            Masukkan password baru untuk akun Anda.
          </p>
        </div>

        @if ($errors->any())
          <div class="w-full max-w-[340px] mx-auto" style="background-color: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; display: flex; align-items: start; gap: 10px; font-size: 14px;">
            <i class="ph-bold ph-warning-circle" style="font-size: 20px; color: #dc2626;"></i>
            <div style="flex: 1;">
              @foreach ($errors->all() as $error)
                <p style="margin: 0 0 4px 0;">{{ $error }}</p>
              @endforeach
            </div>
          </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="space-y-4 w-full max-w-[340px] mx-auto">
          @csrf
          <input type="hidden" name="token" value="{{ $token }}">
          
          <div>
            <label for="email" class="block text-sm font-bold text-gray-800 mb-2 text-left">Email</label>
            <div style="position: relative;">
                <i class="ph-bold ph-envelope-simple icon-input"></i>
                <input type="email" id="email" name="email" 
                       placeholder="Masukkan Email" 
                       value="{{ old('email', $email ?? '') }}"
                       class="w-full py-2.5 px-3 border border-[#CBD5E1] rounded-md focus:outline-none focus:ring-2 focus:ring-[#FFC928] text-sm" 
                       style="padding-left: 48px;"
                       required>
            </div>
          </div>

          <div>
            <label for="password" class="block text-sm font-bold text-gray-800 mb-2 text-left">Password Baru</label>
            <div style="position: relative;">
                <i class="ph-bold ph-lock-key icon-input"></i>
                <input type="password" id="password" name="password" 
                       placeholder="Masukkan Password Baru" 
                       class="w-full py-2.5 px-3 border border-[#CBD5E1] rounded-md focus:outline-none focus:ring-2 focus:ring-[#FFC928] text-sm" 
                       style="padding-left: 48px; padding-right: 48px;"
                       required>
                <i class="ph-bold ph-eye-slash icon-toggle" id="toggleIcon1"></i>
            </div>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-bold text-gray-800 mb-2 text-left">Konfirmasi Password</label>
            <div style="position: relative;">
                <i class="ph-bold ph-lock-key icon-input"></i>
                <input type="password" id="password_confirmation" name="password_confirmation" 
                       placeholder="Konfirmasi Password Baru" 
                       class="w-full py-2.5 px-3 border border-[#CBD5E1] rounded-md focus:outline-none focus:ring-2 focus:ring-[#FFC928] text-sm" 
                       style="padding-left: 48px; padding-right: 48px;"
                       required>
                <i class="ph-bold ph-eye-slash icon-toggle" id="toggleIcon2"></i>
            </div>
          </div>

          <div>
            <button type="submit" class="w-full py-3 font-semibold rounded-md transition-colors duration-200" 
                    style="background-color: #142B6F; color: #FFFFFF;" 
                    onmouseover="this.style.backgroundColor='#0F1F55'" 
                    onmouseout="this.style.backgroundColor='#142B6F'">
                Reset Password
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>

  <script>
    // Toggle password visibility for password field
    document.getElementById('toggleIcon1').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
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

    // Toggle password visibility for confirmation field
    document.getElementById('toggleIcon2').addEventListener('click', function() {
        const passwordInput = document.getElementById('password_confirmation');
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
