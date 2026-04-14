<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HelpCenter - Solusi Cepat untuk Setiap Masalah</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg?v=20260413') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg?v=20260413') }}">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

</head>
<body>

    {{-- â•â•â• NAVBAR â•â•â• --}}
    <nav class="navbar" id="navbar">
        <div class="nav-inner">
            <a href="/" class="logo">
                <div class="logo-icon"><i class='bx bx-support'></i></div>
                HelpCenter
            </a>
            <div class="nav-links">
                <a href="{{ route('status') ?? '#' }}" class="nav-link">
                    <i class='bx bx-info-circle'></i>
                    Status Layanan
                </a>
                @auth
                    <a href="{{ url('/home') }}" class="btn-nav-login">
                        <i class='bx bxs-dashboard'></i>
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-nav-login">
                        <i class='bx bx-log-in'></i>
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- â•â•â• HERO â•â•â• --}}
    <section class="hero">
        <div class="hero-inner">
            <div class="hero-badge">
                <i class='bx bx-check-shield'></i>
                Sistem Support Terpercaya
            </div>

            <h1 class="hero-title">
                Solusi Cepat untuk
                <span class="gradient-text">Setiap Masalah</span>
            </h1>

            <p class="hero-desc">
                Platform helpcenter modern yang memudahkan Anda melaporkan masalah,
                melacak progress, dan berkomunikasi dengan tim support secara real-time.
            </p>

            <div class="hero-actions">
                <a href="{{ route('login') }}" class="btn-primary">
                    <span>Mulai Sekarang</span>
                    <i class='bx bx-right-arrow-alt'></i>
                </a>
                <a href="{{ route('status') ?? '#' }}" class="btn-secondary">
                    <i class='bx bx-info-circle'></i>
                    <span>Lihat Status</span>
                </a>
            </div>

            <div class="hero-stats">
                <div class="stat-card">
                    <div class="stat-icon"><i class='bx bx-check-circle'></i></div>
                    <div>
                        <div class="stat-num">1250+</div>
                        <div class="stat-lbl">Tiket Terselesaikan</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class='bx bx-time'></i></div>
                    <div>
                        <div class="stat-num">2j</div>
                        <div class="stat-lbl">Rata-rata Respons</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class='bx bx-happy'></i></div>
                    <div>
                        <div class="stat-num">98%</div>
                        <div class="stat-lbl">Kepuasan User</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- â•â•â• HOW IT WORKS â•â•â• --}}
    <section class="how-it-works">
        <div class="section-wrap">
            <div class="section-header">
                <span class="section-eyebrow">Cara Kerja</span>
                <h2 class="section-title">Cara Kerja Sistem</h2>
                <p class="section-sub">4 langkah mudah untuk mendapatkan bantuan</p>
            </div>

            @php
                $steps = [
                    ['icon' => 'bx bx-log-in', 'title' => 'Login ke Sistem', 'desc' => 'Akses dashboard dengan kredensial Anda', 'color' => 'linear-gradient(135deg,#667eea,#764ba2)'],
                    ['icon' => 'bx bx-message-square-add', 'title' => 'Buat Tiket', 'desc' => 'Laporkan masalah dengan detail lengkap', 'color' => 'linear-gradient(135deg,#f093fb,#f5576c)'],
                    ['icon' => 'bx bx-conversation', 'title' => 'Komunikasi', 'desc' => 'Chat dengan teknisi untuk solusi cepat', 'color' => 'linear-gradient(135deg,#4facfe,#00f2fe)'],
                    ['icon' => 'bx bx-check-circle', 'title' => 'Selesai', 'desc' => 'Masalah terselesaikan, tiket ditutup', 'color' => 'linear-gradient(135deg,#43e97b,#38f9d7)'],
                ];
            @endphp

            <div class="steps-grid">
                @foreach($steps as $i => $step)
                <div class="step-card">
                    <div class="step-num">{{ $i + 1 }}</div>
                    <div class="step-icon-wrap" style="background: {{ $step['color'] }}">
                        <i class='{{ $step['icon'] }}'></i>
                    </div>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- â•â•â• FEATURES â•â•â• --}}
    <section class="features">
        <div class="section-wrap">
            <div class="section-header">
                <span class="section-eyebrow">Fitur</span>
                <h2 class="section-title">Fitur Unggulan</h2>
                <p class="section-sub">Semua yang Anda butuhkan untuk mengelola support dengan efisien</p>
            </div>

            @php
                $features = [
                    ['icon' => 'bx bx-rocket', 'title' => 'Respon Cepat', 'desc' => 'Tim support siap membantu dengan waktu respons rata-rata 2 jam', 'color' => 'linear-gradient(135deg,#667eea,#764ba2)'],
                    ['icon' => 'bx bx-globe', 'title' => 'Status Real-time', 'desc' => 'Pantau status layanan dan gangguan sistem secara real-time', 'color' => 'linear-gradient(135deg,#f093fb,#f5576c)'],
                    ['icon' => 'bx bx-conversation', 'title' => 'Komunikasi Mudah', 'desc' => 'Chat langsung dengan teknisi dan vendor untuk solusi cepat', 'color' => 'linear-gradient(135deg,#4facfe,#00f2fe)'],
                    ['icon' => 'bx bx-history', 'title' => 'Riwayat Lengkap', 'desc' => 'Akses semua riwayat tiket dan komunikasi kapan saja', 'color' => 'linear-gradient(135deg,#43e97b,#38f9d7)'],
                    ['icon' => 'bx bx-mobile', 'title' => 'Mobile Friendly', 'desc' => 'Akses dari mana saja, kapan saja melalui smartphone Anda', 'color' => 'linear-gradient(135deg,#fa709a,#fee140)'],
                    ['icon' => 'bx bx-shield-check', 'title' => 'Aman & Terpercaya', 'desc' => 'Data Anda dijamin aman dengan enkripsi tingkat enterprise', 'color' => 'linear-gradient(135deg,#30cfd0,#330867)'],
                ];
            @endphp

            <div class="features-grid">
                @foreach($features as $feat)
                <div class="feature-card">
                    <div class="feature-icon" style="background: {{ $feat['color'] }}">
                        <i class='{{ $feat['icon'] }}'></i>
                    </div>
                    <h3>{{ $feat['title'] }}</h3>
                    <p>{{ $feat['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- â•â•â• DASHBOARD PREVIEW â•â•â• --}}
    <section class="dashboard-preview">
        <div class="section-wrap">
            <div class="section-header">
                <span class="section-eyebrow">Preview</span>
                <h2 class="section-title">Dashboard yang Intuitif</h2>
                <p class="section-sub">Kelola semua tiket dan komunikasi dalam satu tempat</p>
            </div>

            <div class="preview-layout">
                <div class="mockup-wrap">
                    <div class="mockup-bar">
                        <div class="mockup-dots">
                            <span></span><span></span><span></span>
                        </div>
                        <div class="mockup-addr">dashboard</div>
                    </div>
                    <div class="mockup-body">
                        <div class="mock-side">
                            <div class="mock-nav-dot active"></div>
                            <div class="mock-nav-dot"></div>
                            <div class="mock-nav-dot"></div>
                            <div class="mock-nav-dot"></div>
                        </div>
                        <div class="mock-content">
                            <div class="mock-topbar"></div>
                            <div class="mock-cards-row">
                                <div class="mock-stat"></div>
                                <div class="mock-stat"></div>
                                <div class="mock-stat"></div>
                                <div class="mock-stat"></div>
                            </div>
                            <div class="mock-rows">
                                <div class="mock-row"></div>
                                <div class="mock-row"></div>
                                <div class="mock-row"></div>
                                <div class="mock-row"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="preview-features">
                    <div class="pf-item">
                        <div class="pf-icon"><i class='bx bx-bar-chart-alt-2'></i></div>
                        <div>
                            <h4>Analytics Real-time</h4>
                            <p>Pantau performa tim dan metrik tiket secara langsung</p>
                        </div>
                    </div>
                    <div class="pf-item">
                        <div class="pf-icon"><i class='bx bx-filter-alt'></i></div>
                        <div>
                            <h4>Filter &amp; Sorting</h4>
                            <p>Temukan tiket dengan cepat menggunakan filter canggih</p>
                        </div>
                    </div>
                    <div class="pf-item">
                        <div class="pf-icon"><i class='bx bx-bell'></i></div>
                        <div>
                            <h4>Notifikasi Instan</h4>
                            <p>Dapatkan update real-time untuk setiap perubahan status</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- â•â•â• BENEFITS â•â•â• --}}
    <section class="benefits">
        <div class="section-wrap">
            <div class="section-header">
                <span class="section-eyebrow">Keuntungan</span>
                <h2 class="section-title">Mengapa Memilih Helpcenter?</h2>
                <p class="section-sub">Tingkatkan produktivitas dan kepuasan pelanggan Anda</p>
            </div>

            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon-wrap"><i class='bx bx-trending-up'></i></div>
                    <h3>Tingkatkan Efisiensi</h3>
                    <p>Kurangi waktu resolusi tiket hingga 60% dengan workflow otomatis dan assignment cerdas</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon-wrap"><i class='bx bx-group'></i></div>
                    <h3>Kolaborasi Tim</h3>
                    <p>Tingkatkan kerja sama antar departemen dengan tools komunikasi terintegrasi</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon-wrap"><i class='bx bx-line-chart'></i></div>
                    <h3>Analisis Mendalam</h3>
                    <p>Dapatkan insight berharga dari data tiket untuk perbaikan berkelanjutan</p>
                </div>
            </div>
        </div>
    </section>

    {{-- â•â•â• INTEGRATIONS â•â•â• --}}
    <section class="integrations">
        <div class="section-wrap">
            <div class="section-header">
                <span class="section-eyebrow">Integrasi</span>
                <h2 class="section-title">Terintegrasi dengan Tools Favorit</h2>
                <p class="section-sub">Hubungkan dengan aplikasi yang sudah Anda gunakan</p>
            </div>

            @php
                $integrations = [
                    ['name' => 'Slack', 'icon' => 'bx bxl-slack'],
                    ['name' => 'Google', 'icon' => 'bx bxl-google'],
                    ['name' => 'Microsoft', 'icon' => 'bx bxl-microsoft'],
                    ['name' => 'Trello', 'icon' => 'bx bxl-trello'],
                    ['name' => 'Gmail', 'icon' => 'bx bx-envelope'],
                    ['name' => 'Zoom', 'icon' => 'bx bx-video'],
                ];
            @endphp

            <div class="integrations-grid">
                @foreach($integrations as $int)
                <div class="integration-card">
                    <i class='{{ $int['icon'] }}'></i>
                    <span>{{ $int['name'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- â•â•â• TESTIMONIALS â•â•â• --}}
    <section class="testimonials">
        <div class="section-wrap">
            <div class="section-header">
                <span class="section-eyebrow">Testimoni</span>
                <h2 class="section-title">Apa Kata Mereka?</h2>
                <p class="section-sub">Dipercaya oleh ratusan pengguna</p>
            </div>

            @php
                $testimonials = [
                    ['name' => 'Budi Santoso', 'role' => 'IT Manager', 'init' => 'BS', 'text' => 'Sistem yang sangat membantu tim kami dalam mengelola support. Response time sangat cepat!'],
                    ['name' => 'Siti Nurhaliza', 'role' => 'Operations Head', 'init' => 'SN', 'text' => 'Interface yang mudah digunakan. Tim kami langsung bisa menggunakan tanpa training panjang.'],
                    ['name' => 'Ahmad Dahlan', 'role' => 'Facility Manager', 'init' => 'AD', 'text' => 'Tracking tiket jadi sangat mudah. Semua history tersimpan dengan rapi dan mudah diakses.'],
                ];
            @endphp

            <div class="testimonials-grid">
                @foreach($testimonials as $t)
                <div class="testi-card">
                    <div class="testi-stars">
                        @for($i = 0; $i < 5; $i++)<i class='bx bxs-star'></i>@endfor
                    </div>
                    <p class="testi-text">"{{ $t['text'] }}"</p>
                    <div class="testi-author">
                        <div class="testi-avatar">{{ $t['init'] }}</div>
                        <div>
                            <div class="testi-name">{{ $t['name'] }}</div>
                            <div class="testi-role">{{ $t['role'] }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- â•â•â• FAQ â•â•â• --}}
    <section class="faq">
        <div class="section-wrap">
            <div class="section-header">
                <span class="section-eyebrow">FAQ</span>
                <h2 class="section-title">Pertanyaan yang Sering Diajukan</h2>
                <p class="section-sub">Temukan jawaban untuk pertanyaan umum</p>
            </div>

            @php
                $faqs = [
                    ['q' => 'Bagaimana cara membuat tiket baru?', 'a' => 'Setelah login, klik tombol "Buat Tiket" di dashboard. Isi detail masalah, pilih kategori, dan submit. Tim support akan segera merespons.'],
                    ['q' => 'Berapa lama waktu respons rata-rata?', 'a' => 'Kami berkomitmen untuk merespons setiap tiket dalam waktu maksimal 2 jam pada jam kerja. Untuk masalah urgent, respons bisa lebih cepat.'],
                    ['q' => 'Apakah saya bisa melacak status tiket?', 'a' => 'Ya, Anda dapat melacak status tiket secara real-time melalui dashboard. Anda juga akan menerima notifikasi email untuk setiap update.'],
                    ['q' => 'Apakah data saya aman?', 'a' => 'Keamanan data adalah prioritas kami. Semua data dienkripsi dan disimpan dengan standar keamanan enterprise-level.'],
                    ['q' => 'Apakah bisa diakses melalui mobile?', 'a' => 'Ya, sistem kami fully responsive dan dapat diakses melalui browser mobile maupun desktop dengan pengalaman yang optimal.'],
                ];
            @endphp

            <div class="faq-list">
                @foreach($faqs as $faq)
                <div class="faq-item">
                    <div class="faq-q">
                        <h4>{{ $faq['q'] }}</h4>
                        <i class='bx bx-plus'></i>
                    </div>
                    <div class="faq-a">
                        <p>{{ $faq['a'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- â•â•â• CTA â•â•â• --}}
    <section class="cta">
        <div class="cta-inner">
            <div class="cta-icon"><i class='bx bx-rocket'></i></div>
            <h2>Siap untuk Memulai?</h2>
            <p>Bergabunglah dengan ribuan pengguna yang sudah mempercayai sistem kami</p>
            <a href="{{ route('login') }}" class="btn-cta">
                <span>Login Sekarang</span>
                <i class='bx bx-right-arrow-alt'></i>
            </a>
        </div>
    </section>

    <script>
        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 20);
        });

        // FAQ toggle
        document.querySelectorAll('.faq-q').forEach(q => {
            q.addEventListener('click', () => {
                const item = q.parentElement;
                const answer = item.querySelector('.faq-a');
                const icon = q.querySelector('i');
                const isOpen = answer.style.display === 'block';

                // close all
                document.querySelectorAll('.faq-a').forEach(a => a.style.display = 'none');
                document.querySelectorAll('.faq-q i').forEach(i => {
                    i.classList.remove('bx-minus');
                    i.classList.add('bx-plus');
                });

                if (!isOpen) {
                    answer.style.display = 'block';
                    icon.classList.remove('bx-plus');
                    icon.classList.add('bx-minus');
                }
            });
        });

        // Close all FAQ answers by default (only show on click)
        document.querySelectorAll('.faq-a').forEach(a => a.style.display = 'none');
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
            --gradient-soft: linear-gradient(135deg, rgba(79,70,229,0.08) 0%, rgba(124,58,237,0.08) 100%);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow: 0 4px 16px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.12);
            --shadow-colored: 0 12px 40px rgba(79,70,229,0.25);
            --radius: 16px;
            --radius-lg: 24px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            overflow-x: hidden;
        }

        /* â-€â-€â-€â-€â-€ TYPOGRAPHY â-€â-€â-€â-€â-€ */
        h1, h2, h3, h4 { font-family: 'Plus Jakarta Sans', sans-serif; }

        .gradient-text {
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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
            transition: box-shadow 0.3s;
        }

        .navbar.scrolled {
            box-shadow: var(--shadow);
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
            font-family: 'Syne', sans-serif;
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

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9375rem;
            transition: color 0.2s;
        }

        .nav-link:hover { color: var(--primary); }

        .btn-nav-login {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.375rem;
            background: var(--gradient);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9375rem;
            transition: all 0.3s;
            box-shadow: 0 4px 14px rgba(79,70,229,0.3);
        }

        .btn-nav-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79,70,229,0.4);
        }

        /* â-€â-€â-€â-€â-€ HERO â-€â-€â-€â-€â-€ */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 8rem 2rem 5rem;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -200px; right: -200px;
            width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(79,70,229,0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -100px; left: -100px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(6,182,212,0.10) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-inner {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.125rem;
            background: white;
            border: 1.5px solid rgba(79,70,229,0.25);
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 2rem;
            animation: fadeUp 0.6s ease both;
        }

        .hero-badge i { font-size: 1rem; }

        .hero-title {
            font-size: clamp(2rem, 4vw, 3.5rem);
            font-weight: 800;
            line-height: 1.2;
            color: var(--text);
            margin-bottom: 1.5rem;
            animation: fadeUp 0.6s ease 0.1s both;
        }

        .hero-title span {
            display: block;
        }

        .hero-desc {
            font-size: 1.175rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto 2.5rem;
            line-height: 1.75;
            font-weight: 400;
            animation: fadeUp 0.6s ease 0.2s both;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeUp 0.6s ease 0.3s both;
            margin-bottom: 4rem;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.9rem 2rem;
            background: var(--gradient);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.0625rem;
            transition: all 0.3s;
            box-shadow: var(--shadow-colored);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(79,70,229,0.4);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.9rem 2rem;
            background: white;
            border: 2px solid var(--border);
            color: var(--text);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.0625rem;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }

        /* â-€â-€â-€â-€â-€ HERO STATS â-€â-€â-€â-€â-€ */
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            animation: fadeUp 0.6s ease 0.4s both;
        }

        .stat-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem 1.625rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow);
            border-color: rgba(79,70,229,0.2);
        }

        .stat-icon {
            width: 52px; height: 52px;
            background: var(--gradient);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
        }

        .stat-num {
            font-family: 'Syne', sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
        }

        .stat-lbl {
            font-size: 0.8125rem;
            color: var(--text-muted);
            font-weight: 500;
            margin-top: 2px;
        }

        /* â-€â-€â-€â-€â-€ SECTIONS â-€â-€â-€â-€â-€ */
        section { padding: 6rem 2rem; }

        .section-wrap {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3.5rem;
        }

        .section-eyebrow {
            display: inline-block;
            font-size: 0.8125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--primary);
            background: rgba(79,70,229,0.08);
            padding: 0.375rem 1rem;
            border-radius: 50px;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: clamp(1.625rem, 2.5vw, 2.25rem);
            font-weight: 800;
            color: var(--text);
            margin-bottom: 0.75rem;
        }

        .section-sub {
            font-size: 1.0625rem;
            color: var(--text-muted);
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.65;
        }

        /* â-€â-€â-€â-€â-€ HOW IT WORKS â-€â-€â-€â-€â-€ */
        .how-it-works { background: white; }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
        }

        .step-card {
            background: var(--bg);
            border: 2px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2.25rem 1.875rem;
            text-align: center;
            position: relative;
            transition: all 0.3s;
        }

        .step-card:hover {
            border-color: var(--primary-light);
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
            background: white;
        }

        .step-num {
            position: absolute;
            top: -16px;
            left: 50%;
            transform: translateX(-50%);
            width: 34px; height: 34px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 0.9375rem;
            color: white;
        }

        .step-icon-wrap {
            width: 72px; height: 72px;
            border-radius: 18px;
            margin: 0 auto 1.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }

        .step-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.625rem;
            color: var(--text);
        }

        .step-card p {
            font-size: 0.9375rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* â-€â-€â-€â-€â-€ FEATURES â-€â-€â-€â-€â-€ */
        .features { background: var(--bg); }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .feature-card {
            background: white;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2.25rem;
            transition: all 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            border-color: rgba(79,70,229,0.3);
            box-shadow: var(--shadow-lg);
        }

        .feature-icon {
            width: 68px; height: 68px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 1.375rem;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.625rem;
        }

        .feature-card p {
            font-size: 0.9375rem;
            color: var(--text-muted);
            line-height: 1.65;
        }

        /* â-€â-€â-€â-€â-€ DASHBOARD PREVIEW â-€â-€â-€â-€â-€ */
        .dashboard-preview { background: white; }

        .preview-layout {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .mockup-wrap {
            background: var(--bg);
            border: 2px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .mockup-bar {
            background: white;
            padding: 0.875rem 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .mockup-dots {
            display: flex;
            gap: 6px;
        }

        .mockup-dots span {
            width: 11px; height: 11px;
            border-radius: 50%;
            background: #d1d5db;
        }

        .mockup-dots span:first-child { background: #ef4444; }
        .mockup-dots span:nth-child(2) { background: #f59e0b; }
        .mockup-dots span:nth-child(3) { background: #10b981; }

        .mockup-addr {
            flex: 1;
            background: var(--bg);
            border-radius: 6px;
            padding: 0.375rem 0.875rem;
            font-size: 0.8125rem;
            color: var(--text-muted);
            font-family: monospace;
        }

        .mockup-body {
            display: flex;
            min-height: 340px;
        }

        .mock-side {
            width: 70px;
            background: white;
            border-right: 1px solid var(--border);
            padding: 1rem 0.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }

        .mock-nav-dot {
            height: 36px;
            background: rgba(79,70,229,0.08);
            border-radius: 8px;
        }

        .mock-nav-dot.active {
            background: var(--gradient);
        }

        .mock-content {
            flex: 1;
            padding: 1.25rem;
        }

        .mock-topbar {
            height: 48px;
            background: var(--border);
            border-radius: 8px;
            margin-bottom: 1.25rem;
        }

        .mock-cards-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .mock-stat {
            height: 80px;
            background: rgba(79,70,229,0.08);
            border-radius: 8px;
        }

        .mock-rows {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }

        .mock-row {
            height: 44px;
            background: var(--border);
            border-radius: 6px;
        }

        .preview-features {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .pf-item {
            display: flex;
            gap: 1.25rem;
            align-items: flex-start;
        }

        .pf-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.625rem;
            color: white;
            flex-shrink: 0;
        }

        .pf-item h4 {
            font-size: 1.0625rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.375rem;
        }

        .pf-item p {
            font-size: 0.9375rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* â-€â-€â-€â-€â-€ BENEFITS â-€â-€â-€â-€â-€ */
        .benefits { background: var(--bg); }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .benefit-card {
            text-align: center;
            padding: 2rem;
        }

        .benefit-icon-wrap {
            width: 100px; height: 100px;
            margin: 0 auto 1.5rem;
            border-radius: 28px;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            box-shadow: var(--shadow-colored);
        }

        .benefit-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.875rem;
        }

        .benefit-card p {
            font-size: 1rem;
            color: var(--text-muted);
            line-height: 1.7;
        }

        /* â-€â-€â-€â-€â-€ INTEGRATIONS â-€â-€â-€â-€â-€ */
        .integrations { background: white; }

        .integrations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1.25rem;
        }

        .integration-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.875rem;
            padding: 1.875rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            background: var(--bg);
            transition: all 0.3s;
            cursor: default;
        }

        .integration-card:hover {
            border-color: var(--primary-light);
            transform: translateY(-4px);
            box-shadow: var(--shadow);
            background: white;
        }

        .integration-card i {
            font-size: 2.25rem;
            color: var(--primary);
        }

        .integration-card span {
            font-weight: 600;
            font-size: 0.9375rem;
            color: var(--text);
        }

        /* â-€â-€â-€â-€â-€ TESTIMONIALS â-€â-€â-€â-€â-€ */
        .testimonials { background: var(--bg); }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .testi-card {
            background: white;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            transition: all 0.3s;
        }

        .testi-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow);
        }

        .testi-stars {
            color: #f59e0b;
            font-size: 1.125rem;
            margin-bottom: 1rem;
            display: flex;
            gap: 2px;
        }

        .testi-text {
            font-size: 1rem;
            color: #374151;
            line-height: 1.75;
            margin-bottom: 1.5rem;
            font-style: italic;
        }

        .testi-author {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .testi-avatar {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            color: white;
        }

        .testi-name {
            font-weight: 700;
            color: var(--text);
            font-size: 0.9375rem;
        }

        .testi-role {
            font-size: 0.8125rem;
            color: var(--text-muted);
        }

        /* â-€â-€â-€â-€â-€ FAQ â-€â-€â-€â-€â-€ */
        .faq { background: white; }

        .faq-list {
            max-width: 780px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
        }

        .faq-item {
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            transition: border-color 0.3s;
        }

        .faq-item:hover {
            border-color: var(--primary-light);
        }

        .faq-q {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.375rem 1.625rem;
            cursor: pointer;
            user-select: none;
            background: white;
            transition: background 0.2s;
        }

        .faq-q:hover { background: var(--bg); }

        .faq-q h4 {
            font-size: 1.0625rem;
            font-weight: 600;
            color: var(--text);
        }

        .faq-q i {
            font-size: 1.375rem;
            color: var(--primary);
            flex-shrink: 0;
        }

        .faq-a {
            padding: 0 1.625rem 1.375rem;
            font-size: 0.9375rem;
            color: var(--text-muted);
            line-height: 1.7;
            background: white;
        }

        /* â-€â-€â-€â-€â-€ CTA â-€â-€â-€â-€â-€ */
        .cta {
            background: var(--gradient);
            padding: 5rem 2rem;
        }

        .cta-inner {
            max-width: 700px;
            margin: 0 auto;
            text-align: center;
        }

        .cta-icon {
            width: 88px; height: 88px;
            background: rgba(255,255,255,0.18);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.75rem;
            color: white;
            margin: 0 auto 1.875rem;
        }

        .cta h2 {
            font-size: 2.25rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.875rem;
        }

        .cta p {
            font-size: 1.125rem;
            color: rgba(255,255,255,0.9);
            margin-bottom: 2.5rem;
            line-height: 1.7;
        }

        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            padding: 1.0625rem 2.75rem;
            background: white;
            color: var(--primary);
            text-decoration: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1.0625rem;
            transition: all 0.3s;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        }

        .btn-cta:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.28);
        }

        /* â-€â-€â-€â-€â-€ ANIMATIONS â-€â-€â-€â-€â-€ */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(28px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* â-€â-€â-€â-€â-€ RESPONSIVE â-€â-€â-€â-€â-€ */
        @media (max-width: 1024px) {
            .preview-layout { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            section { padding: 4.5rem 1.25rem; }

            .nav-links .nav-link { display: none; }

            .hero { padding: 6.5rem 1.25rem 4rem; }

            .hero-stats { flex-direction: column; align-items: center; }
            .stat-card { width: 100%; max-width: 320px; }

            .preview-layout { grid-template-columns: 1fr; }

            .cta h2 { font-size: 2rem; }

            .mock-cards-row { grid-template-columns: repeat(2, 1fr); }
        }
    </style>

</body>
</html>



