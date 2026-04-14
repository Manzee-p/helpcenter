<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $status->title }} - Status HelpCenter</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg?v=20260413') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg?v=20260413') }}">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --text: #0f172a;
            --text-muted: #64748b;
            --bg: #f8fafc;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
            --shadow: 0 4px 16px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.12);
            --radius: 16px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            color: var(--text); min-height: 100vh;
        }

        /* â-€â-€ NAVBAR â-€â-€ */
        .navbar {
            position: sticky; top: 0; z-index: 999;
            background: rgba(248,250,252,0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 0.875rem 0;
        }
        .nav-inner {
            max-width: 1200px; margin: 0 auto;
            padding: 0 2rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .logo {
            display: flex; align-items: center; gap: 0.625rem;
            font-size: 1.25rem; font-weight: 800; color: var(--text);
            text-decoration: none;
        }
        .logo-icon {
            width: 36px; height: 36px;
            background: var(--gradient); border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.125rem; color: white;
        }
        .nav-links { display: flex; align-items: center; gap: 1.5rem; }
        .nav-link {
            display: flex; align-items: center; gap: 0.375rem;
            color: var(--text-muted); text-decoration: none;
            font-weight: 500; font-size: 0.9375rem; transition: color 0.2s;
        }
        .nav-link:hover { color: var(--primary); }
        .btn-login {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.625rem 1.375rem;
            background: var(--gradient); color: white;
            text-decoration: none; border-radius: 10px;
            font-weight: 600; font-size: 0.9375rem;
            box-shadow: 0 4px 14px rgba(79,70,229,0.3);
            transition: all 0.3s;
        }
        .btn-login:hover { transform: translateY(-2px); }

        /* â-€â-€ PAGE WRAP â-€â-€ */
        .page-wrap {
            max-width: 1200px; margin: 0 auto;
            padding: 2rem 2rem 4rem;
        }

        /* â-€â-€ NAV BAR (back button) â-€â-€ */
        .nav-bar {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 1.5rem;
        }
        .btn-back {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.75rem 1.375rem;
            background: white; border: 1.5px solid var(--border);
            border-radius: 12px; color: var(--text-muted);
            font-weight: 600; font-size: 0.9375rem;
            text-decoration: none; transition: all 0.3s;
        }
        .btn-back:hover {
            border-color: var(--primary); color: var(--primary);
            transform: translateX(-3px);
        }
        .nav-actions { display: flex; gap: 0.625rem; }
        .btn-action {
            width: 42px; height: 42px;
            border: 1.5px solid var(--border); background: white;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.125rem; color: var(--text-muted);
            cursor: pointer; text-decoration: none;
            transition: all 0.3s;
        }
        .btn-action:hover {
            border-color: var(--primary); color: var(--primary);
            transform: translateY(-2px);
        }

        /* â-€â-€ AUTH NOTICE â-€â-€ */
        .auth-notice {
            background: var(--gradient);
            border-radius: 20px; padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            display: flex; align-items: center;
            justify-content: space-between; gap: 2rem;
            box-shadow: 0 8px 30px rgba(79,70,229,0.25);
        }
        .notice-content { display: flex; align-items: center; gap: 1.25rem; flex: 1; }
        .notice-icon {
            width: 52px; height: 52px;
            background: rgba(255,255,255,0.2); border-radius: 50%;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .notice-icon i { font-size: 1.75rem; color: white; }
        .notice-text h3 { font-size: 1.125rem; font-weight: 700; color: white; margin-bottom: 0.25rem; }
        .notice-text p  { font-size: 0.9375rem; color: rgba(255,255,255,0.9); }
        .btn-notice-login {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            background: white; color: var(--primary);
            border-radius: 12px; font-weight: 700; font-size: 0.9375rem;
            text-decoration: none; white-space: nowrap;
            transition: all 0.3s; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-notice-login:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.2); }

        /* â-€â-€ MAIN CARD â-€â-€ */
        .main-card {
            background: white; border-radius: var(--radius);
            padding: 2rem; margin-bottom: 1.5rem;
            box-shadow: var(--shadow); border: 1px solid var(--border);
        }

        .card-badges { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
        .badge {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.45rem 0.875rem; border-radius: 6px;
            font-weight: 700; font-size: 0.8125rem;
        }
        .badge-incident {
            background: var(--gradient); color: white;
            font-family: monospace; letter-spacing: 0.5px;
        }
        .badge-investigating { background: #f59e0b; color: white; }
        .badge-identified, .badge-monitoring { background: #6366f1; color: white; }
        .badge-resolved { background: #10b981; color: white; }
        .severity-critical { background: rgba(239,68,68,0.1); color: #dc2626; }
        .severity-high     { background: rgba(245,158,11,0.1); color: #d97706; }
        .severity-medium   { background: rgba(59,130,246,0.1); color: #2563eb; }
        .severity-low      { background: rgba(107,114,128,0.1); color: #6b7280; }

        .status-title {
            font-size: 1.75rem; font-weight: 800;
            color: var(--text); margin-bottom: 1.5rem; line-height: 1.3;
        }

        /* Info Row */
        .info-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem; margin-bottom: 1.5rem;
        }
        .info-item {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.875rem 1rem;
            background: var(--bg); border-radius: 10px;
            border: 1px solid var(--border);
        }
        .info-item > i { font-size: 1.375rem; color: var(--primary); flex-shrink: 0; }
        .info-text { display: flex; flex-direction: column; gap: 0.125rem; }
        .info-label {
            font-size: 0.6875rem; font-weight: 700; color: #9ca3af;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .info-value { font-size: 0.9375rem; font-weight: 600; color: var(--text); }

        /* Current Status Banner */
        .current-status {
            padding: 1.375rem 1.5rem; border-radius: 12px; border: 2px solid;
            display: flex; align-items: center; gap: 1.25rem;
        }
        .cs-icon {
            width: 56px; height: 56px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.875rem; flex-shrink: 0;
        }
        .cs-label {
            display: block; font-size: 0.6875rem; font-weight: 700;
            opacity: 0.65; text-transform: uppercase; letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        .cs-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.25rem; }
        .cs-desc { font-size: 0.9375rem; opacity: 0.85; }

        .cs-investigating { background: rgba(245,158,11,0.06); border-color: #f59e0b; color: #d97706; }
        .cs-investigating .cs-icon { background: rgba(245,158,11,0.15); }
        .cs-identified, .cs-monitoring { background: rgba(99,102,241,0.06); border-color: #6366f1; color: #6366f1; }
        .cs-identified .cs-icon, .cs-monitoring .cs-icon { background: rgba(99,102,241,0.15); }
        .cs-resolved { background: rgba(16,185,129,0.06); border-color: #10b981; color: #059669; }
        .cs-resolved .cs-icon { background: rgba(16,185,129,0.15); }

        /* â-€â-€ CONTENT BOXES â-€â-€ */
        .content-section { display: flex; flex-direction: column; gap: 1.25rem; }
        .content-box {
            background: white; border-radius: var(--radius);
            box-shadow: var(--shadow); border: 1px solid var(--border);
            overflow: hidden;
        }
        .box-header {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 1.125rem 1.5rem;
            background: var(--bg); border-bottom: 1px solid var(--border);
        }
        .box-header i { font-size: 1.25rem; color: var(--primary); }
        .box-header h3 {
            font-size: 1rem; font-weight: 700; color: var(--text);
            flex: 1; margin: 0;
        }
        .update-count {
            padding: 0.3rem 0.75rem;
            background: var(--gradient); color: white;
            border-radius: 10px; font-size: 0.75rem; font-weight: 700;
        }
        .box-body { padding: 1.5rem; }

        .description-text {
            font-size: 0.9375rem; line-height: 1.75;
            color: #374151; white-space: pre-wrap;
        }

        /* â-€â-€ TIMELINE â-€â-€ */
        .no-updates {
            text-align: center; padding: 2.5rem; color: #9ca3af;
        }
        .no-updates i { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; }

        .timeline { display: flex; flex-direction: column; gap: 1.25rem; }
        .timeline-item { display: flex; gap: 1rem; position: relative; }
        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute; left: 18px; top: 40px; bottom: -20px;
            width: 2px; background: var(--border);
        }
        .t-marker {
            width: 38px; height: 38px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; color: white; flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        .marker-investigating { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .marker-update        { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .marker-resolved      { background: linear-gradient(135deg, #10b981, #059669); }

        .t-content {
            flex: 1; background: var(--bg);
            padding: 1rem 1.25rem; border-radius: 10px;
            border: 1px solid var(--border);
        }
        .t-header {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 0.625rem; gap: 1rem;
        }
        .t-author { display: flex; align-items: center; gap: 0.625rem; }
        .t-avatar {
            width: 28px; height: 28px;
            background: var(--gradient); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.6875rem; color: white;
        }
        .t-name { font-weight: 700; font-size: 0.875rem; color: var(--text); }
        .t-time { font-size: 0.75rem; color: #9ca3af; font-weight: 500; }
        .t-message {
            font-size: 0.9375rem; line-height: 1.6; color: #374151;
            margin-bottom: 0.625rem; white-space: pre-wrap;
        }
        .t-type {
            display: inline-block; padding: 0.25rem 0.625rem;
            border-radius: 10px; font-size: 0.6875rem;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .type-investigating { background: rgba(245,158,11,0.1); color: #d97706; }
        .type-update        { background: rgba(59,130,246,0.1); color: #2563eb; }
        .type-resolved      { background: rgba(16,185,129,0.1); color: #059669; }

        /* â-€â-€ RESPONSIVE â-€â-€ */
        @media (max-width: 768px) {
            .page-wrap { padding: 1.5rem 1rem 3rem; }
            .nav-bar { flex-direction: column; gap: 1rem; align-items: stretch; }
            .btn-back { justify-content: center; }
            .nav-actions { justify-content: center; }
            .auth-notice { flex-direction: column; }
            .notice-content { flex-direction: column; text-align: center; }
            .btn-notice-login { width: 100%; justify-content: center; }
            .main-card { padding: 1.375rem; }
            .status-title { font-size: 1.375rem; }
            .current-status { flex-direction: column; align-items: flex-start; }
            .info-row { grid-template-columns: 1fr; }
            .nav-link span { display: none; }
        }
    </style>
</head>
<body>

{{-- â•â•â• NAVBAR â•â•â• --}}
<nav class="navbar">
    <div class="nav-inner">
        <a href="{{ url('/') }}" class="logo">
            <div class="logo-icon"><i class='bx bx-support'></i></div>
            HelpCenter
        </a>
        <div class="nav-links">
            <a href="{{ url('/') }}" class="nav-link"><i class='bx bx-home'></i><span>Home</span></a>
            <a href="{{ route('status') }}" class="nav-link"><i class='bx bx-info-circle'></i><span>Status Layanan</span></a>
            @auth
                <a href="{{ url('/home') }}" class="btn-login"><i class='bx bxs-dashboard'></i> Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn-login"><i class='bx bx-log-in'></i> Login</a>
            @endauth
        </div>
    </div>
</nav>

<div class="page-wrap">

    {{-- Back + Actions --}}
    <div class="nav-bar">
        <a href="{{ route('status') }}" class="btn-back">
            <i class='bx bx-arrow-left'></i> Kembali
        </a>
        <div class="nav-actions">
            <a href="{{ route('status.detail', $status->id) }}" class="btn-action" title="Refresh">
                <i class='bx bx-refresh'></i>
            </a>
        </div>
    </div>

    {{-- Auth Notice --}}
    @guest
    <div class="auth-notice">
        <div class="notice-content">
            <div class="notice-icon"><i class='bx bx-shield-check'></i></div>
            <div class="notice-text">
                <h3>Punya masalah serupa?</h3>
                <p>Login untuk membuat tiket dan dapatkan bantuan dari tim support kami</p>
            </div>
        </div>
        <a href="{{ route('login') }}" class="btn-notice-login">
            <i class='bx bx-log-in'></i> Login Sekarang
        </a>
    </div>
    @endguest

    {{-- â•â•â• MAIN CARD â•â•â• --}}
    <div class="main-card">

        {{-- Badges --}}
        @php
            $sevLabels    = ['critical'=>'Kritis','high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'];
            $catLabels    = ['power_outage'=>'Gangguan Listrik','technical_issue'=>'Masalah Teknis','facility_issue'=>'Masalah Fasilitas','network_issue'=>'Gangguan Jaringan','other'=>'Lainnya'];
            $statusLabels = ['investigating'=>'Sedang Diselidiki','identified'=>'Teridentifikasi','monitoring'=>'Pemantauan','resolved'=>'Selesai'];
            $statusIcons  = ['investigating'=>'bx-search-alt','identified'=>'bx-check-shield','monitoring'=>'bx-radar','resolved'=>'bx-check-circle'];
            $statusTexts  = ['investigating'=>'Sedang Diselidiki','identified'=>'Masalah Teridentifikasi','monitoring'=>'Dalam Pemantauan','resolved'=>'Selesai'];
            $statusDescs  = ['investigating'=>'Tim kami sedang menyelidiki masalah ini','identified'=>'Penyebab masalah telah ditemukan dan sedang ditangani','monitoring'=>'Perbaikan sedang dipantau untuk memastikan masalah teratasi','resolved'=>'Masalah telah terselesaikan dan sistem kembali normal'];
        @endphp

        <div class="card-badges">
            <span class="badge badge-incident">
                <i class='bx bx-hash'></i> {{ $status->incident_number }}
            </span>
            <span class="badge badge-{{ $status->status }}">
                <i class='bx bx-error'></i>
                {{ $statusLabels[$status->status] ?? $status->status }}
            </span>
            <span class="badge severity-{{ $status->severity }}">
                <i class='bx bx-flag'></i>
                {{ $sevLabels[$status->severity] ?? $status->severity }}
            </span>
        </div>

        <h1 class="status-title">{{ $status->title }}</h1>

        {{-- Info Row --}}
        <div class="info-row">
            <div class="info-item">
                <i class='bx bx-category'></i>
                <div class="info-text">
                    <span class="info-label">Kategori</span>
                    <span class="info-value">{{ $catLabels[$status->category] ?? $status->category }}</span>
                </div>
            </div>
            @if($status->affected_area)
            <div class="info-item">
                <i class='bx bx-map-pin'></i>
                <div class="info-text">
                    <span class="info-label">Area Terdampak</span>
                    <span class="info-value">{{ $status->affected_area }}</span>
                </div>
            </div>
            @endif
            <div class="info-item">
                <i class='bx bx-time'></i>
                <div class="info-text">
                    <span class="info-label">Dimulai</span>
                    <span class="info-value">
                        {{ $status->started_at ? \Carbon\Carbon::parse($status->started_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') : '-' }}
                    </span>
                </div>
            </div>
            @if($status->resolved_at)
            <div class="info-item">
                <i class='bx bx-check-circle'></i>
                <div class="info-text">
                    <span class="info-label">Diselesaikan</span>
                    <span class="info-value">
                        {{ \Carbon\Carbon::parse($status->resolved_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}
                    </span>
                </div>
            </div>
            @endif
        </div>

        {{-- Current Status Banner --}}
        <div class="current-status cs-{{ $status->status }}">
            <div class="cs-icon">
                <i class='bx {{ $statusIcons[$status->status] ?? "bx-info-circle" }}'></i>
            </div>
            <div>
                <span class="cs-label">Status Saat Ini</span>
                <div class="cs-title">{{ $statusTexts[$status->status] ?? $status->status }}</div>
                <div class="cs-desc">{{ $statusDescs[$status->status] ?? '' }}</div>
            </div>
        </div>
    </div>

    {{-- â•â•â• CONTENT SECTION â•â•â• --}}
    <div class="content-section">

        {{-- Description --}}
        <div class="content-box">
            <div class="box-header">
                <i class='bx bx-detail'></i>
                <h3>Deskripsi Masalah</h3>
            </div>
            <div class="box-body">
                <p class="description-text">{{ $status->description }}</p>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="content-box">
            <div class="box-header">
                <i class='bx bx-history'></i>
                <h3>Timeline Update</h3>
                <span class="update-count">{{ $updates->count() }} Update</span>
            </div>
            <div class="box-body">

                @if($updates->isEmpty())
                    <div class="no-updates">
                        <i class='bx bx-info-circle'></i>
                        <p>Belum ada update untuk insiden ini</p>
                    </div>
                @else
                    <div class="timeline">
                        @php
                            $updateTypeLabels = ['investigating'=>'Penyelidikan','update'=>'Update','resolved'=>'Selesai'];
                            $updateIcons      = ['investigating'=>'bx-search-alt','update'=>'bx-info-circle','resolved'=>'bx-check-circle'];
                        @endphp

                        @foreach($updates as $update)
                        <div class="timeline-item">
                            <div class="t-marker marker-{{ $update->update_type }}">
                                <i class='bx {{ $updateIcons[$update->update_type] ?? "bx-message-square-detail" }}'></i>
                            </div>
                            <div class="t-content">
                                <div class="t-header">
                                    <div class="t-author">
                                        <div class="t-avatar">
                                            {{ strtoupper(substr($update->user?->name ?? 'A', 0, 1)) }}{{ strtoupper(substr(explode(' ', $update->user?->name ?? 'Ad')[1] ?? '', 0, 1)) }}
                                        </div>
                                        <span class="t-name">{{ $update->user?->name ?? 'Admin' }}</span>
                                    </div>
                                    <span class="t-time">
                                        {{ \Carbon\Carbon::parse($update->created_at)->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="t-message">{{ $update->message }}</p>
                                <span class="t-type type-{{ $update->update_type }}">
                                    {{ $updateTypeLabels[$update->update_type] ?? $update->update_type }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>

    </div>

</div>

</body>
</html>



