<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HelpCenter') - HelpCenter</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg?v=20260413') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg?v=20260413') }}">
    <script>
        (function () {
            var href = "{{ asset('favicon.svg?v=20260413') }}" + "&t=" + Date.now();
            document.querySelectorAll("link[rel*='icon']").forEach(function (el) { el.remove(); });
            var icon = document.createElement('link');
            icon.rel = 'icon';
            icon.type = 'image/svg+xml';
            icon.href = href;
            document.head.appendChild(icon);
        })();
    </script>

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

</head>
<body>
<div class="layout-wrapper">

    {{--  Sidebar  --}}
    @include('layouts.sidebar')

    {{--  Overlay mobile  --}}
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

    {{--  Main content area  --}}
    <div class="layout-content" id="layout-content">

        {{-- Navbar (notif sudah di-include di dalam navbar.blade.php) --}}
        @include('layouts.navbar')

        {{-- Page content --}}
        <main class="layout-page">
            @yield('content')
        </main>

        {{-- Footer --}}
        @include('layouts.footer')

    </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Sidebar toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('layout-menu');
        const overlay = document.getElementById('sidebar-overlay');
        const isOpen  = sidebar.classList.contains('open');
        sidebar.classList.toggle('open',  !isOpen);
        overlay.classList.toggle('active', !isOpen);
    }

    function closeSidebar() {
        document.getElementById('layout-menu').classList.remove('open');
        document.getElementById('sidebar-overlay').classList.remove('active');
    }

    // Flash messages via SweetAlert2
    @if(session('success'))
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ addslashes(session("success")) }}',
                toast: true, position: 'top-end',
                showConfirmButton: false,
                timer: 3000, timerProgressBar: true,
            });
        });
    @endif

    @if(session('error'))
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '{{ addslashes(session("error")) }}',
                toast: true, position: 'top-end',
                showConfirmButton: false,
                timer: 4000, timerProgressBar: true,
            });
        });
    @endif

    @if(session('warning'))
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: '{{ addslashes(session("warning")) }}',
                toast: true, position: 'top-end',
                showConfirmButton: false,
                timer: 4000, timerProgressBar: true,
            });
        });
    @endif

    @if(session('info'))
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'info',
                title: 'Info',
                text: '{{ addslashes(session("info")) }}',
                toast: true, position: 'top-end',
                showConfirmButton: false,
                timer: 3500, timerProgressBar: true,
            });
        });
    @endif
</script>

    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #818cf8;
            --secondary: #7c3aed;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --text: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --bg: #f8fafc;
            --bg-card: #ffffff;
            --border: #e2e8f0;
            --gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
            --shadow: 0 4px 16px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.12);
            --shadow-colored: 0 12px 40px rgba(79,70,229,0.25);
            --sidebar-width: 280px;
            --navbar-height: 68px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* LAYOUT WRAPPER */
        .layout-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* MAIN CONTENT */
        .layout-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .layout-page {
            flex: 1;
            padding: calc(var(--navbar-height) + 1.5rem) 1.75rem 2rem;
        }

        /* OVERLAY (mobile) */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 998;
        }
        .sidebar-overlay.active { display: block; }

        /* RESPONSIVE */
        @media (max-width: 991px) {
            .layout-content { margin-left: 0; }
        }
    </style>

@stack('scripts')
@stack('styles')
</body>
</html>




