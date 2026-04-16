<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - HelpCenter</title>
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

    <!-- Background blobs -->
    <div class="bg-blob-1"></div>
    <div class="bg-blob-2"></div>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-inner">
            <a href="{{ url('/') }}" class="logo">
                <div class="logo-icon"><i class='bx bx-support'></i></div>
                HelpCenter
            </a>
            <a href="{{ url('/') }}" class="nav-back">
                <i class='bx bx-arrow-back'></i>
                Kembali ke Beranda
            </a>
        </div>
    </nav>

    <!-- Main -->
    <div class="main-wrapper">
        <div class="login-container">
            <div class="card">
                <div class="card-top-bar"></div>
                <div class="card-body">

                    <!-- Brand -->
                    <div class="login-brand">
                        <div class="brand-icon">
                            <i class='bx bx-support'></i>
                        </div>
                        <h1>Selamat Datang! 👋</h1>
                        <p>Masuk ke akun HelpCenter Anda</p>
                    </div>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="alert alert-success">
                            <i class='bx bx-check-circle'></i>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    <!-- Error Alert -->
                    @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
                        <div class="alert alert-danger">
                            <i class='bx bx-error-circle'></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email -->
                        <div class="form-group">
                            <label class="form-label" for="email">Email</label>
                            <div class="input-wrap">
                                <i class='bx bx-envelope input-icon'></i>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                    value="{{ old('email') }}"
                                    placeholder="nama@email.com"
                                    required
                                    autocomplete="email"
                                    autofocus
                                />
                            </div>
                            @error('email')
                                <div class="invalid-feedback">
                                    <i class='bx bx-info-circle'></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-wrap">
                                <i class='bx bx-lock-alt input-icon'></i>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control has-toggle {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                    placeholder="masukan password"
                                    required
                                    autocomplete="current-password"
                                />
                                <button type="button" class="password-toggle" onclick="togglePassword()" id="toggle-btn">
                                    <i class='bx bx-hide' id="toggle-icon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">
                                    <i class='bx bx-info-circle'></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>

                        <!-- Remember + Forgot -->
                        <div class="form-footer">
                            <label class="check-wrap">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    id="remember"
                                    {{ old('remember') ? 'checked' : '' }}
                                />
                                <span>Ingat saya</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="forgot-link">
                                    Lupa password?
                                </a>
                            @endif
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn-submit">
                            <i class='bx bx-log-in'></i>
                            <span>Masuk</span>
                        </button>
                    </form>

                    <!-- Google Login Divider -->
                    <div class="divider">
                        <span>atau lanjutkan dengan</span>
                    </div>

                    <!-- Google Button -->
                    <div
                        id="g_id_onload"
                        data-client_id="{{ config('services.google.client_id') }}"
                        data-context="signin"
                        data-ux_mode="popup"
                        data-callback="onGoogleSignIn"
                        data-auto_prompt="false">
                    </div>

                    <button type="button" class="btn-google" onclick="triggerGoogleSignIn()">
                        <!-- Google SVG logo -->
                        <svg width="20" height="20" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M47.532 24.552c0-1.636-.147-3.2-.418-4.695H24.48v8.879h12.93c-.558 3.002-2.246 5.548-4.787 7.254v6.03h7.753c4.537-4.18 7.156-10.333 7.156-17.468z" fill="#4285F4"/>
                            <path d="M24.48 48c6.483 0 11.926-2.15 15.9-5.82l-7.753-6.03c-2.15 1.44-4.899 2.29-8.147 2.29-6.26 0-11.563-4.228-13.459-9.908H3.02v6.228C6.98 42.967 15.14 48 24.48 48z" fill="#34A853"/>
                            <path d="M11.021 28.532a14.37 14.37 0 0 1-.752-4.532c0-1.572.272-3.1.752-4.532V13.24H3.02A23.97 23.97 0 0 0 .48 24c0 3.868.926 7.532 2.54 10.76l7.001-6.228z" fill="#FBBC05"/>
                            <path d="M24.48 9.56c3.527 0 6.69 1.213 9.18 3.594l6.882-6.882C36.4 2.376 30.963 0 24.48 0 15.14 0 6.98 5.033 3.02 13.24l7.001 6.228C11.917 13.788 17.22 9.56 24.48 9.56z" fill="#EA4335"/>
                        </svg>
                        Masuk dengan Google
                    </button>

                    

                </div><!-- /.card-body -->
            </div><!-- /.card -->
        </div><!-- /.login-container -->
    </div><!-- /.main-wrapper -->

    <!-- Google GSI Script -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <script>
        // â-€â-€â-€ Password Toggle â-€â-€â-€
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('toggle-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bx-hide', 'bx-show');
            } else {
                input.type = 'password';
                icon.classList.replace('bx-show', 'bx-hide');
            }
        }

        // â-€â-€â-€ Fill Demo Credentials â-€â-€â-€
        function fillCredentials(email) {
            document.getElementById('email').value    = email;
            document.getElementById('password').value = 'password';

            // Brief visual feedback on the card
            const emailInput = document.getElementById('email');
            emailInput.style.borderColor = '#4f46e5';
            emailInput.style.background  = 'rgba(79,70,229,0.04)';
            setTimeout(() => {
                emailInput.style.borderColor = '';
                emailInput.style.background  = '';
            }, 1000);
        }

        // â-€â-€â-€ Google Sign-In Callback â-€â-€â-€
        window.onGoogleSignIn = function(response) {
            const form  = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("auth.google.callback") }}'; // Adjust to your route

            const csrf = document.createElement('input');
            csrf.type  = 'hidden';
            csrf.name  = '_token';
            csrf.value = '{{ csrf_token() }}';

            const token = document.createElement('input');
            token.type  = 'hidden';
            token.name  = 'credential';
            token.value = response.credential;

            form.appendChild(csrf);
            form.appendChild(token);
            document.body.appendChild(form);
            form.submit();
        }

        // â-€â-€â-€ Trigger Google Sign-In â-€â-€â-€
        function triggerGoogleSignIn() {
            if (typeof google !== 'undefined' && google.accounts) {
                google.accounts.id.prompt();
            }
        }
    </script>

        <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #818cf8;
            --secondary: #7c3aed;
            --accent: #06b6d4;
            --success: #10b981;
            --danger: #ef4444;
            --text: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --bg: #f8fafc;
            --bg-card: #ffffff;
            --border: #e2e8f0;
            --gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow: 0 4px 16px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.12);
            --shadow-colored: 0 12px 40px rgba(79,70,229,0.25);
            --radius: 16px;
            --radius-lg: 24px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* â-€â-€â-€â-€â-€ BACKGROUND BLOBS â-€â-€â-€â-€â-€ */
        .bg-blob-1 {
            position: fixed;
            top: -200px; right: -200px;
            width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(79,70,229,0.10) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        .bg-blob-2 {
            position: fixed;
            bottom: -100px; left: -100px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(6,182,212,0.08) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* â-€â-€â-€â-€â-€ NAVBAR â-€â-€â-€â-€â-€ */
        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 999;
            background: rgba(248,250,252,0.92);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 0.875rem 0;
        }

        .nav-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-size: 1.375rem;
            font-weight: 800;
            color: var(--text);
            text-decoration: none;
        }

        .logo-icon {
            width: 38px; height: 38px;
            background: var(--gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
        }

        .nav-back {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9375rem;
            transition: color 0.2s;
        }

        .nav-back:hover { color: var(--primary); }

        /* â-€â-€â-€â-€â-€ MAIN LAYOUT â-€â-€â-€â-€â-€ */
        .main-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 7rem 1.5rem 3rem;
            position: relative;
            z-index: 1;
        }

        .login-container {
            width: 100%;
            max-width: 480px;
            animation: fadeUp 0.6s ease both;
        }

        /* â-€â-€â-€â-€â-€ CARD â-€â-€â-€â-€â-€ */
        .card {
            background: white;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .card-top-bar {
            height: 5px;
            background: var(--gradient);
        }

        .card-body {
            padding: 2.5rem 2.25rem;
        }

        /* â-€â-€â-€â-€â-€ HEADER â-€â-€â-€â-€â-€ */
        .login-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
        }

        .brand-icon {
            width: 64px; height: 64px;
            background: var(--gradient);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 1.125rem;
            box-shadow: var(--shadow-colored);
        }

        .login-brand h1 {
            font-size: 1.625rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 0.375rem;
        }

        .login-brand p {
            font-size: 0.9375rem;
            color: var(--text-muted);
        }

        /* â-€â-€â-€â-€â-€ ALERTS â-€â-€â-€â-€â-€ */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 10px;
            font-size: 0.9rem;
            margin-bottom: 1.25rem;
        }

        .alert i { font-size: 1.125rem; flex-shrink: 0; margin-top: 1px; }

        .alert-danger {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.25);
            color: #b91c1c;
        }

        .alert-success {
            background: rgba(16,185,129,0.08);
            border: 1px solid rgba(16,185,129,0.25);
            color: #065f46;
        }

        /* â-€â-€â-€â-€â-€ FORM â-€â-€â-€â-€â-€ */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.125rem;
            color: var(--text-light);
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 0.875rem 0.75rem 2.625rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            color: var(--text);
            background: var(--bg);
            transition: all 0.2s;
            outline: none;
        }

        .form-control::placeholder { color: var(--text-light); }

        .form-control:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
        }

        .form-control:focus ~ .input-icon,
        .input-wrap:focus-within .input-icon {
            color: var(--primary);
        }

        .form-control.is-invalid {
            border-color: var(--danger);
            background: rgba(239,68,68,0.03);
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(239,68,68,0.12);
        }

        .invalid-feedback {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            margin-top: 0.4rem;
            font-size: 0.8125rem;
            color: var(--danger);
        }

        .invalid-feedback i { font-size: 0.9rem; }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-light);
            font-size: 1.125rem;
            padding: 0;
            transition: color 0.2s;
            display: flex;
            align-items: center;
        }

        .password-toggle:hover { color: var(--primary); }

        /* Password field: icon on right too */
        .form-control.has-toggle {
            padding-right: 2.75rem;
        }

        /* â-€â-€â-€â-€â-€ REMEMBER + FORGOT â-€â-€â-€â-€â-€ */
        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .check-wrap {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .check-wrap input[type="checkbox"] {
            width: 16px; height: 16px;
            border: 1.5px solid var(--border);
            border-radius: 4px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .check-wrap span {
            font-size: 0.875rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .forgot-link {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .forgot-link:hover { opacity: 0.75; }

        /* â-€â-€â-€â-€â-€ SUBMIT BUTTON â-€â-€â-€â-€â-€ */
        .btn-submit {
            width: 100%;
            padding: 0.875rem;
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            transition: all 0.3s;
            box-shadow: var(--shadow-colored);
            margin-bottom: 1.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(79,70,229,0.35);
        }

        .btn-submit:active { transform: translateY(0); }

        /* â-€â-€â-€â-€â-€ DIVIDER â-€â-€â-€â-€â-€ */
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .divider span {
            font-size: 0.8125rem;
            color: var(--text-light);
            font-weight: 500;
            white-space: nowrap;
        }

        /* â-€â-€â-€â-€â-€ GOOGLE BUTTON â-€â-€â-€â-€â-€ */
        .btn-google {
            width: 100%;
            padding: 0.8125rem;
            background: white;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.3s;
            text-decoration: none;
            margin-bottom: 1.75rem;
        }

        .btn-google:hover {
            border-color: var(--primary-light);
            background: var(--bg);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-google img {
            width: 20px; height: 20px;
        }

        /* â-€â-€â-€â-€â-€ DEMO CREDENTIALS â-€â-€â-€â-€â-€ */
        .demo-section {
            background: linear-gradient(135deg, rgba(79,70,229,0.04) 0%, rgba(124,58,237,0.04) 100%);
            border: 1.5px solid rgba(79,70,229,0.15);
            border-radius: 12px;
            padding: 1.25rem;
        }

        .demo-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.75px;
            margin-bottom: 0.875rem;
        }

        .demo-header i { font-size: 1rem; }

        .demo-grid {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 0.875rem;
        }

        .demo-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.875rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .demo-item:hover {
            background: white;
            border-color: rgba(79,70,229,0.2);
            box-shadow: var(--shadow-sm);
        }

        .demo-badge {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
            flex-shrink: 0;
        }

        .demo-badge.admin  { background: linear-gradient(135deg,#667eea,#764ba2); }
        .demo-badge.vendor { background: linear-gradient(135deg,#4facfe,#00f2fe); }
        .demo-badge.client { background: linear-gradient(135deg,#43e97b,#38f9d7); }

        .demo-info {
            flex: 1;
        }

        .demo-role {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text);
        }

        .demo-email {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .demo-click {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            background: rgba(79,70,229,0.1);
            color: var(--primary);
        }

        .demo-password {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.8125rem;
            color: var(--text-muted);
            padding-top: 0.5rem;
            border-top: 1px solid rgba(79,70,229,0.1);
        }

        .demo-password i { color: var(--primary); font-size: 0.875rem; }
        .demo-password strong { color: var(--text); }

        /* â-€â-€â-€â-€â-€ ANIMATIONS â-€â-€â-€â-€â-€ */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* â-€â-€â-€â-€â-€ RESPONSIVE â-€â-€â-€â-€â-€ */
        @media (max-width: 520px) {
            .card-body { padding: 2rem 1.5rem; }
            .form-footer { flex-direction: column; gap: 0.75rem; align-items: flex-start; }
        }
    </style>

</body>
</html>




