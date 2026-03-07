<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Kata Sandi - DashboardAlert</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
      body { font-family: 'Poppins', sans-serif; }
      .icon-input {
          position: absolute;
          left: 12px;
          top: 50%;
          transform: translateY(-50%);
          font-size: 24px;
          color: #94a3b8; /* Slate-400 */
      }
  </style>
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo_PU.png') }}">
</head>
<body class="bg-gray-50 overflow-hidden">
  <div class="flex h-screen justify-center items-center bg-gray-50">
    <div class="flex bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden" style="width: 800px; min-height: 500px;">
      
      <div class="w-1/4 bg-cover bg-center" style="width: 100%; background-image: url('{{ asset('assets/login_sebelah.png') }}'); background-size: contain; background-repeat: no-repeat; background-position: left;">
      </div>

      <div class="flex flex-col items-center justify-center p-8" style="margin-left: -100px; margin-right: 70px; width: 400px; flex-shrink: 0;">
        
        <div class="flex flex-col items-center mb-6">
          <img src="{{ asset('assets/Logo_PU.png') }}" alt="Logo" class="h-20 mb-2">
          <h2 class="text-2xl" style="font-weight: 700; letter-spacing: -0.5px;">
            <span style="color: #FFC928;">Dashboard</span> <span style="color: #142B6F;">Alert</span>
          </h2>
        </div>
        
        <div class="w-full text-left mb-6 max-w-[340px] mx-auto">
          <h3 class="font-bold text-[#142B6F] text-xl" style="color: #142B6F;">Atur Ulang Password</h3>
          <p class="text-xs text-gray-500 mt-1">
            Masukkan email akun PU kamu untuk atur ulang kata sandi.
          </p>
        </div>

        @if (session('status'))
          <div class="w-full max-w-[340px] mx-auto" style="background-color: #d1fae5; border: 1px solid #34d399; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; display: flex; align-items: start; gap: 10px; font-size: 14px;">
            <i class="ph-bold ph-check-circle" style="font-size: 20px; flex-shrink: 0; margin-top: 2px;"></i>
            <span style="flex: 1; word-break: break-word;">Link reset password telah dikirim ke email Anda.</span>
          </div>
        @endif

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

        <form action="{{ route('password.email') }}" method="POST" class="space-y-4 w-full max-w-[340px] mx-auto">
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
                       required>
            </div>
          </div>

          <div>
            <button type="submit" class="w-full py-3 font-semibold rounded-md transition-colors duration-200" 
                    style="background-color: #142B6F; color: #FFFFFF;" 
                    onmouseover="this.style.backgroundColor='#0F1F55'" 
                    onmouseout="this.style.backgroundColor='#142B6F'">
                Atur Ulang Kata Sandi
            </button>
          </div>

          <div class="text-left mt-2">
            <a href="{{ route('login') }}" class="text-xs font-bold hover:underline" style="color: #142B6F;">
                &lt; Kembali Ke Login
            </a>
          </div>

        </form>
      </div>
    </div>
  </div>
</body>
</html>
