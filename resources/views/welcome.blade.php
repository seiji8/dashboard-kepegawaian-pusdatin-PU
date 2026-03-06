<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Masuk - Sistem Kepegawaian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f4f6f9; /* Warna abu-abu soft profesional */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }
        .card-login {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08); /* Shadow halus */
        }
        .brand-logo {
            width: 60px;
            height: 60px;
            background: #0d6efd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 24px;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }
        .btn-primary {
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        /* Loading Spinner Hidden by Default */
        .spinner-border { display: none; }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo_PU.png') }}">
</head>
<body>

    <div class="login-container">
        <div class="card card-login bg-white p-4 p-md-5">
            
            <div class="text-center mb-4">
                <div class="brand-logo">
                    <i class="bi bi-building-fill"></i>
                </div>
                <h4 class="fw-bold text-dark">Sistem Kepegawaian</h4>
                <p class="text-muted small">Silakan login untuk melanjutkan</p>
            </div>

            @error('username')
                <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>{{ $message }}</div>
                </div>
            @enderror

            <form action="{{ route('login') }}" method="POST" id="loginForm">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">USERNAME / NIP</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control bg-light border-start-0 ps-0" 
                               value="{{ old('username') }}" placeholder="Masukan NIP Anda" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">PASSWORD</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="passwordInput" 
                               class="form-control bg-light border-start-0 border-end-0 ps-0" 
                               placeholder="Masukan Password" required>
                        <span class="input-group-text bg-light border-start-0 cursor-pointer" style="cursor: pointer;" onclick="togglePassword()">
                            <i class="bi bi-eye-slash" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label text-muted small" for="remember">Ingat Saya</label>
                    </div>
                    <a href="#" class="text-decoration-none small">Lupa Password?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3" id="btnLogin">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    <span id="btnText">MASUK SISTEM</span>
                </button>
            </form>

            <div class="text-center mt-3">
                <small class="text-muted" style="font-size: 0.75rem;">&copy; 2026 Biro Kepegawaian & Teknologi Informasi</small>
            </div>
        </div>
    </div>

    <script>
        // 1. Fitur Toggle Show/Hide Password
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            }
        }

        // 2. Fitur Loading State saat Submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnLogin');
            const spinner = btn.querySelector('.spinner-border');
            const text = document.getElementById('btnText');

            // Matikan tombol biar gak double submit
            btn.disabled = true;
            // Munculkan spinner
            spinner.style.display = 'inline-block';
            text.innerText = 'MEMPROSES...';
        });
    </script>
</body>
</html>
