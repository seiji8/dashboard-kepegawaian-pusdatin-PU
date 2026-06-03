<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - DashboardAlert</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Modular CSS via Vite -->
    @vite(['resources/css/app.css'])

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo_PU.png') }}">

    <!-- Tour Styles -->
    @include('partials.tour_styles')

    <!-- Per-page head (CSS/JS tambahan khusus halaman tertentu) -->
    @yield('head')
</head>
<body>
    <div class="app-container">
        @include('partials.sidebar')

        <main class="main-content">
            @include('partials.navbar')

            <div class="content-area">
                @yield('content')
            </div>

            {{-- Instant skeleton reveal (inline = no CDN wait) --}}
            <script>
                document.querySelectorAll('.skeleton-layer').forEach(function(el){el.classList.add('hidden')});
                document.querySelectorAll('.real-content').forEach(function(el){el.classList.remove('hidden')});
            </script>
        </main>
    </div>

    <!-- Sync Loading Overlay -->
    @include('partials.sync_loading')

    <!-- App Common JS -->
    <script src="{{ asset('js/app-common.js') }}"></script>

    <!-- Change Password Modal -->
    @include('partials.change_password_modal')

    <!-- Backup Database Confirmation Modal -->
    @include('partials.modal_backup_database')


    <!-- Per-page scripts -->
    @yield('scripts')

    <!-- Driver.js (Tour Interaktif) -->
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>

    <!-- Per-page tour config -->
    @yield('tour')
</body>
</html>
