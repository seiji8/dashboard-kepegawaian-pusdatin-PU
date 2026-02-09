<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - DashboardAlert</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="login.css">
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
      
      <div class="w-1/4 bg-cover bg-center" style="width: 100%; background-image: url('assets/login_sebelah.png'); background-size: contain; background-repeat: no-repeat; background-position: left;">
      </div>

      <div class="flex flex-col items-center justify-center p-8" style="margin-left: -100px; margin-right: 70px; width: 400px; flex-shrink: 0;">
        <div class="flex justify-center mb-8">
          <img src="assets/Logo_PU.png" alt="Logo" class="h-20">
        </div>
        <div class="text-center mb-6">
          <h2 class="text-2xl" style="font-weight: 700; letter-spacing: -0.5px;"><span style="color: #FFC928;">Dashboard</span> <span style="color: #142B6F;">Alert</span></h2>
          
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4 w-full max-w-[340px] mx-auto">
          @csrf
          
          <div>
            <label for="email" class="block text-sm font-bold text-gray-800 mb-2 text-left">Email</label>
            <div style="position: relative;">
                <i class="ph-bold ph-envelope-simple icon-input"></i>
                <input type="email" id="email" name="email" 
                       placeholder="Masukkan Email" 
                       value="{{ old('email') }}"
                       class="w-full py-2.5 px-3 border border-[#CBD5E1] rounded-md focus:outline-none focus:ring-2 focus:ring-[#FFC928] text-sm" 
                       style="padding-left: 48px;"
                       required autofocus>
            </div>
          </div>

          <div>
            <label for="password" class="block text-sm font-bold text-gray-800 mb-2 text-left">Kata Sandi</label>
            <div style="position: relative;">
                <i class="ph-bold ph-lock-key icon-input"></i>
                <input type="password" id="password" name="password" 
                       placeholder="Masukkan Kata Sandi" 
                       class="w-full py-2.5 px-3 border border-[#CBD5E1] rounded-md focus:outline-none focus:ring-2 focus:ring-[#FFC928] text-sm" 
                       style="padding-left: 48px; padding-right: 48px;"
                       required>
                <i class="ph-bold ph-eye-slash icon-toggle" id="toggleIcon"></i>
            </div>
          </div>

          <div class="text-right">
            <a href="{{ route('password.request') }}" class="text-sm font-semibold hover:underline transition-all" style="color: #142B6F;">Lupa Kata Sandi?</a>
          </div>

          <div>
            <button type="submit" class="w-full py-3 font-semibold rounded-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200" style="background-color: #FFC928; color: #142B6F;" onmouseover="this.style.backgroundColor='#FFB700'" onmouseout="this.style.backgroundColor='#FFC928'">LOGIN</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    const toggleIcon = document.getElementById('toggleIcon');
    const passwordInput = document.getElementById('password');

    toggleIcon.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        if (type === 'text') {
            toggleIcon.classList.remove('ph-eye-slash');
            toggleIcon.classList.add('ph-eye');
        } else {
            toggleIcon.classList.remove('ph-eye');
            toggleIcon.classList.add('ph-eye-slash');
        }
    });
  </script>
</body>
</html>
