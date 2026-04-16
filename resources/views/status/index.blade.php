<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Status Layanan - HelpCenter</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg?v=20260413') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg?v=20260413') }}">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
</head>
<body>

{{-- NAVBAR --}}
<nav class="navbar">
    <div class="nav-inner">
        <a href="{{ url('/') }}" class="logo">
            <div class="logo-icon"><i class='bx bx-support'></i></div>
            HelpCenter
        </a>
        <div class="nav-links">
            <a href="{{ url('/') }}" class="nav-link">
                <i class='bx bx-home'></i><span>Home</span>
            </a>
            <a href="{{ route('status') }}" class="nav-link active">
                <i class='bx bx-info-circle'></i><span>Status Layanan</span>
            </a>
            @auth
                <a href="{{ url('/home') }}" class="btn-login">
                    <i class='bx bxs-dashboard'></i> Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-login">
                    <i class='bx bx-log-in'></i> Login
                </a>
            @endauth
        </div>
    </div>
</nav>

<div class="page-wrap">

    {{-- HERO HEADER --}}
    <div class="hero-section">
        <div class="hero-main">
            <h1 class="page-title">Papan Informasi Status Layanan</h1>
            <p class="page-subtitle">Pantau status gangguan dan pemeliharaan sistem secara real-time</p>
            <div class="public-badge">
                <i class='bx bx-globe'></i>
                Halaman publik - Tidak perlu login untuk melihat status
            </div>
        </div>

        {{-- Overall Status Box --}}
        <div class="status-box {{ $overallStatus['class'] }}">
            <div class="box-label">STATUS SISTEM</div>
            <div class="box-display">
                <i class='bx {{ $overallStatus['icon'] }}'></i>
                <span class="box-text">{{ $overallStatus['text'] }}</span>
            </div>
            @if($activeIncidents->count() > 0)
                <div class="box-count">{{ $activeIncidents->count() }} Gangguan Aktif</div>
            @endif
        </div>
    </div>

    {{-- AUTH NOTICE (untuk guest) --}}
    @guest
    <div class="auth-notice">
        <div class="notice-content">
            <div class="notice-icon"><i class='bx bx-info-circle'></i></div>
            <div class="notice-text">
                <h3>Ingin melaporkan masalah?</h3>
                <p>Login untuk membuat tiket dan berkomunikasi dengan tim support</p>
            </div>
        </div>
        <a href="{{ route('login') }}" class="btn-notice-login">
            <i class='bx bx-log-in'></i> Login Sekarang
        </a>
    </div>
    @endguest

    {{-- FILTERS --}}
    <div class="filters-card">
        <div class="filter-header">
            <h3 class="filter-title">Filter Status</h3>
            <a href="{{ route('status') }}" class="btn-reset">
                <i class='bx bx-refresh'></i> Reset
            </a>
        </div>
        <form method="GET" action="{{ route('status') }}" id="filterForm">
            <div class="filters-row">
                <div class="filter-group">
                    <label class="filter-label"><i class='bx bx-filter'></i> Status</label>
                    <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Semua Status</option>
                        <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Aktif</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label"><i class='bx bx-category'></i> Kategori</label>
                    <select name="category" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Semua Kategori</option>
                        <option value="power_outage"    {{ request('category') === 'power_outage'    ? 'selected' : '' }}>Gangguan Listrik</option>
                        <option value="technical_issue" {{ request('category') === 'technical_issue' ? 'selected' : '' }}>Masalah Teknis</option>
                        <option value="facility_issue"  {{ request('category') === 'facility_issue'  ? 'selected' : '' }}>Masalah Fasilitas</option>
                        <option value="network_issue"   {{ request('category') === 'network_issue'   ? 'selected' : '' }}>Gangguan Jaringan</option>
                        <option value="other"           {{ request('category') === 'other'           ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label"><i class='bx bx-search'></i> Cari</label>
                    <input type="text" name="search" class="filter-input"
                        placeholder="Cari berdasarkan judul atau area..."
                        value="{{ request('search') }}"
                        onchange="document.getElementById('filterForm').submit()">
                </div>
            </div>
        </form>
    </div>

    {{-- CONTENT --}}
    @if($statuses->isEmpty())
        {{-- Empty State --}}
        <div class="empty-state">
            <div class="empty-icon"><i class='bx bx-check-circle'></i></div>
            <h3>Semua Sistem Normal</h3>
            <p>Tidak ada gangguan atau masalah yang dilaporkan saat ini</p>
            <div class="empty-meta">
                <i class='bx bx-time'></i>
                <span>Terakhir diperbarui: {{ now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</span>
            </div>
        </div>

    @else
        <div class="status-content">

            {{-- Pinned --}}
            @if($pinnedStatuses->count() > 0)
            <div class="status-section">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class='bx bxs-pin'></i> Status Penting
                    </h3>
                    <span class="count-badge">{{ $pinnedStatuses->count() }} Status</span>
                </div>
                <div class="status-list">
                    @foreach($pinnedStatuses as $item)
                    <a href="{{ route('status.detail', $item->id) }}" class="status-card pinned">
                        @if($item->updates->count() > 0)
                        <div class="updates-badge">
                            <i class='bx bx-message-square-detail'></i>
                            {{ $item->updates->count() }} Update
                        </div>
                        @endif

                        <div class="card-badges">
                            <span class="badge badge-pinned"><i class='bx bxs-pin'></i> Penting</span>
                            <span class="badge badge-number">{{ $item->incident_number }}</span>
                            <span class="badge badge-severity severity-{{ $item->severity }}">
                                <i class='bx bx-flag'></i>
                                @php
                                    $sevLabels = ['critical'=>'Kritis','high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'];
                                @endphp
                                {{ $sevLabels[$item->severity] ?? $item->severity }}
                            </span>
                        </div>

                        <h4 class="card-title">{{ $item->title }}</h4>

                        <div class="card-meta">
                            @php
                                $catLabels = ['power_outage'=>'Gangguan Listrik','technical_issue'=>'Masalah Teknis','facility_issue'=>'Masalah Fasilitas','network_issue'=>'Gangguan Jaringan','other'=>'Lainnya'];
                            @endphp
                            <span class="meta-item"><i class='bx bx-category'></i> {{ $catLabels[$item->category] ?? $item->category }}</span>
                            @if($item->affected_area)
                            <span class="meta-item"><i class='bx bx-map-pin'></i> {{ $item->affected_area }}</span>
                            @endif
                        </div>

                        <p class="card-desc">{{ Str::limit($item->description, 100) }}</p>

                        <div class="card-footer">
                            @php
                                $statusLabels = ['investigating'=>'Sedang Diselidiki','identified'=>'Teridentifikasi','monitoring'=>'Pemantauan','resolved'=>'Selesai'];
                            @endphp
                            <span class="status-badge badge-status-{{ $item->status }}">
                                <i class='bx bx-info-circle'></i>
                                {{ $statusLabels[$item->status] ?? $item->status }}
                            </span>
                            <span class="card-time">
                                <i class='bx bx-time'></i>
                                {{ $item->started_at ? \Carbon\Carbon::parse($item->started_at)->diffForHumans() : '-' }}
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Regular --}}
            @if($regularStatuses->count() > 0)
            <div class="status-section">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class='bx bx-clipboard'></i> Semua Status
                    </h3>
                    <span class="count-badge">{{ $regularStatuses->count() }} Status</span>
                </div>
                <div class="status-list">
                    @foreach($regularStatuses as $item)
                    <a href="{{ route('status.detail', $item->id) }}" class="status-card">
                        @if($item->updates->count() > 0)
                        <div class="updates-badge">
                            <i class='bx bx-message-square-detail'></i>
                            {{ $item->updates->count() }} Update
                        </div>
                        @endif

                        <div class="card-badges">
                            <span class="badge badge-{{ $item->status }}">
                                <i class='bx bx-error'></i>
                                {{ $statusLabels[$item->status] ?? $item->status }}
                            </span>
                            <span class="badge badge-number">{{ $item->incident_number }}</span>
                            <span class="badge badge-severity severity-{{ $item->severity }}">
                                <i class='bx bx-flag'></i>
                                {{ $sevLabels[$item->severity] ?? $item->severity }}
                            </span>
                        </div>

                        <h4 class="card-title">{{ $item->title }}</h4>

                        <div class="card-meta">
                            <span class="meta-item"><i class='bx bx-category'></i> {{ $catLabels[$item->category] ?? $item->category }}</span>
                            @if($item->affected_area)
                            <span class="meta-item"><i class='bx bx-map-pin'></i> {{ $item->affected_area }}</span>
                            @endif
                        </div>

                        <p class="card-desc">{{ Str::limit($item->description, 100) }}</p>

                        <div class="card-footer">
                            <span class="status-badge badge-status-{{ $item->status }}">
                                <i class='bx bx-info-circle'></i>
                                {{ $statusLabels[$item->status] ?? $item->status }}
                            </span>
                            <span class="card-time">
                                <i class='bx bx-time'></i>
                                {{ $item->started_at ? \Carbon\Carbon::parse($item->started_at)->diffForHumans() : '-' }}
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- PAGINATION --}}
        @if($statuses->lastPage() > 1)
        <div class="pagination-wrap">
            @if($statuses->onFirstPage())
                <span class="page-btn disabled"><i class='bx bx-chevron-left'></i> Sebelumnya</span>
            @else
                <a href="{{ $statuses->previousPageUrl() }}" class="page-btn">
                    <i class='bx bx-chevron-left'></i> Sebelumnya
                </a>
            @endif

            @foreach($statuses->getUrlRange(1, $statuses->lastPage()) as $page => $url)
                @if($page == $statuses->currentPage())
                    <span class="page-num active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="page-num">{{ $page }}</a>
                @endif
            @endforeach

            @if($statuses->hasMorePages())
                <a href="{{ $statuses->nextPageUrl() }}" class="page-btn">
                    Selanjutnya <i class='bx bx-chevron-right'></i>
                </a>
            @else
                <span class="page-btn disabled">Selanjutnya <i class='bx bx-chevron-right'></i></span>
            @endif
        </div>
        @endif

    @endif

</div>

<script>
    // Debounce search
    var searchTimer;
    document.querySelector('input[name="search"]').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 600);
    });
</script>

<style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --text: #0f172a;
            --text-muted: #64748b;
            --bg: #f8fafc;
            --bg-card: #ffffff;
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
            background: var(--bg);
            color: var(--text);
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
            max-width: 1400px; margin: 0 auto;
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
            font-weight: 500; font-size: 0.9375rem;
            transition: color 0.2s;
        }
        .nav-link:hover, .nav-link.active { color: var(--primary); }
        .btn-login {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.625rem 1.375rem;
            background: var(--gradient); color: white;
            text-decoration: none; border-radius: 10px;
            font-weight: 600; font-size: 0.9375rem;
            transition: all 0.3s;
            box-shadow: 0 4px 14px rgba(79,70,229,0.3);
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(79,70,229,0.4); }

        /* â-€â-€ WRAPPER â-€â-€ */
        .page-wrap {
            max-width: 1400px; margin: 0 auto;
            padding: 2rem 2rem 4rem;
        }

        /* â-€â-€ HERO HEADER â-€â-€ */
        .hero-section {
            display: flex; justify-content: space-between;
            align-items: flex-start; gap: 2rem;
            margin-bottom: 1.5rem;
        }
        .hero-main { flex: 1; }
        .page-title {
            font-size: 1.75rem; font-weight: 800;
            color: var(--text); margin-bottom: 0.375rem;
        }
        .page-subtitle { font-size: 0.9375rem; color: var(--text-muted); margin-bottom: 1rem; }
        .public-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.45rem 1rem;
            background: rgba(16,185,129,0.1);
            border: 1px solid rgba(16,185,129,0.3);
            border-radius: 20px; font-size: 0.8125rem;
            color: #059669; font-weight: 600;
        }

        /* Overall Status Box */
        .status-box {
            min-width: 220px; padding: 1.25rem 1.5rem;
            border-radius: 12px; border: 2px solid; text-align: center;
        }
        .status-box .box-label {
            font-size: 0.6875rem; font-weight: 800; opacity: 0.65;
            margin-bottom: 0.625rem; letter-spacing: 1px; text-transform: uppercase;
        }
        .status-box .box-display {
            display: flex; align-items: center; justify-content: center;
            gap: 0.5rem; margin-bottom: 0.375rem;
        }
        .status-box .box-display i { font-size: 1.5rem; }
        .status-box .box-text { font-size: 1.0625rem; font-weight: 700; }
        .status-box .box-count { font-size: 0.8125rem; font-weight: 600; opacity: 0.8; }

        .status-success { background: rgba(16,185,129,0.08); border-color: rgba(16,185,129,0.4); color: #059669; }
        .status-info    { background: rgba(59,130,246,0.08); border-color: rgba(59,130,246,0.4); color: #2563eb; }
        .status-warning { background: rgba(245,158,11,0.08); border-color: rgba(245,158,11,0.4); color: #d97706; }
        .status-critical{ background: rgba(239,68,68,0.08);  border-color: rgba(239,68,68,0.4);  color: #dc2626; }

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
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
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
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-notice-login:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.2); }

        /* â-€â-€ FILTERS â-€â-€ */
        .filters-card {
            background: white; border-radius: var(--radius);
            padding: 1.5rem; margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
        }
        .filter-header {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 1.25rem;
        }
        .filter-title { font-size: 1rem; font-weight: 700; color: var(--text); }
        .btn-reset {
            display: flex; align-items: center; gap: 0.375rem;
            padding: 0.5rem 1rem;
            background: var(--bg); border: 1px solid var(--border);
            border-radius: 8px; font-weight: 600; font-size: 0.875rem;
            color: var(--text-muted); cursor: pointer; transition: all 0.2s;
            text-decoration: none;
        }
        .btn-reset:hover { border-color: var(--primary); color: var(--primary); }
        .filters-row {
            display: grid;
            grid-template-columns: 200px 200px 1fr;
            gap: 1rem; align-items: end;
        }
        .filter-group { display: flex; flex-direction: column; gap: 0.375rem; }
        .filter-label {
            display: flex; align-items: center; gap: 0.375rem;
            font-size: 0.8125rem; font-weight: 600; color: var(--text-muted);
        }
        .filter-label i { color: var(--primary); }
        .filter-select, .filter-input {
            padding: 0.7rem 1rem;
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 0.9375rem; font-family: inherit;
            background: white; color: var(--text);
            transition: border-color 0.2s;
        }
        .filter-select:focus, .filter-input:focus {
            outline: none; border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }

        /* â-€â-€ EMPTY STATE â-€â-€ */
        .empty-state {
            background: white; border-radius: 20px;
            padding: 5rem 2rem; text-align: center;
            box-shadow: var(--shadow); border: 1px solid var(--border);
        }
        .empty-icon { font-size: 5rem; color: #10b981; margin-bottom: 1.5rem; }
        .empty-state h3 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.625rem; }
        .empty-state p { color: var(--text-muted); font-size: 1rem; margin-bottom: 1.5rem; }
        .empty-meta {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.625rem 1.125rem; background: var(--bg);
            border-radius: 20px; font-size: 0.875rem; color: var(--text-muted);
        }

        /* â-€â-€ STATUS SECTIONS â-€â-€ */
        .status-content { display: flex; flex-direction: column; gap: 2rem; }
        .status-section { display: flex; flex-direction: column; gap: 1rem; }
        .section-header {
            display: flex; justify-content: space-between; align-items: center;
        }
        .section-title {
            display: flex; align-items: center; gap: 0.5rem;
            font-size: 1.0625rem; font-weight: 700; color: var(--text);
        }
        .section-title i { font-size: 1.25rem; color: var(--primary); }
        .count-badge {
            padding: 0.3rem 0.875rem;
            background: rgba(79,70,229,0.08); color: var(--primary);
            border-radius: 20px; font-size: 0.8125rem; font-weight: 700;
        }

        /* â-€â-€ STATUS CARDS â-€â-€ */
        .status-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }
        .status-card {
            background: white; border-radius: 14px;
            padding: 1.375rem; border: 2px solid var(--border);
            transition: all 0.3s; cursor: pointer; position: relative;
            text-decoration: none; display: block; color: inherit;
        }
        .status-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }
        .status-card.pinned {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(79,70,229,0.03) 0%, rgba(124,58,237,0.03) 100%);
        }

        /* Card badges */
        .card-badges { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.875rem; }
        .badge {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.35rem 0.75rem; border-radius: 6px;
            font-weight: 700; font-size: 0.75rem;
        }
        .badge-pinned   { background: var(--primary); color: white; }
        .badge-investigating { background: #f59e0b; color: white; }
        .badge-identified, .badge-monitoring { background: #6366f1; color: white; }
        .badge-resolved { background: #10b981; color: white; }
        .badge-number   { background: #f1f5f9; color: #475569; font-family: monospace; }

        .badge-severity { display: inline-flex; align-items: center; gap: 0.3rem; }
        .severity-critical { background: rgba(239,68,68,0.1); color: #dc2626; }
        .severity-high     { background: rgba(245,158,11,0.1); color: #d97706; }
        .severity-medium   { background: rgba(59,130,246,0.1); color: #2563eb; }
        .severity-low      { background: rgba(107,114,128,0.1); color: #6b7280; }

        .card-title { font-size: 1rem; font-weight: 700; margin-bottom: 0.625rem; line-height: 1.4; }
        .card-meta  { display: flex; flex-wrap: wrap; gap: 0.875rem; margin-bottom: 0.625rem; }
        .meta-item  {
            display: inline-flex; align-items: center; gap: 0.3rem;
            font-size: 0.8125rem; color: var(--text-muted); font-weight: 500;
        }
        .meta-item i { color: var(--primary); font-size: 0.9375rem; }
        .card-desc { font-size: 0.875rem; line-height: 1.55; color: var(--text-muted); margin-bottom: 1rem; }

        .card-footer {
            display: flex; justify-content: space-between; align-items: center;
            padding-top: 0.875rem; border-top: 1px solid var(--border); gap: 0.75rem;
        }
        .status-badge {
            display: inline-flex; align-items: center; gap: 0.375rem;
            padding: 0.35rem 0.75rem; border-radius: 6px;
            font-weight: 600; font-size: 0.75rem;
        }
        .badge-status-investigating { background: rgba(245,158,11,0.1); color: #d97706; }
        .badge-status-identified,
        .badge-status-monitoring    { background: rgba(99,102,241,0.1); color: #6366f1; }
        .badge-status-resolved      { background: rgba(16,185,129,0.1); color: #059669; }

        .card-time {
            display: inline-flex; align-items: center; gap: 0.3rem;
            font-size: 0.75rem; color: #9ca3af; font-weight: 500;
        }
        .updates-badge {
            position: absolute; top: -10px; right: 1.25rem;
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.3rem 0.75rem;
            background: var(--gradient); color: white;
            border-radius: 12px; font-size: 0.6875rem; font-weight: 700;
            box-shadow: 0 2px 8px rgba(79,70,229,0.3);
        }

        /* â-€â-€ PAGINATION â-€â-€ */
        .pagination-wrap {
            display: flex; justify-content: center; align-items: center;
            gap: 0.5rem; margin-top: 3rem; flex-wrap: wrap;
        }
        .page-btn {
            display: flex; align-items: center; gap: 0.375rem;
            padding: 0.625rem 1.125rem;
            border: 1.5px solid var(--border); background: white;
            color: var(--text-muted); border-radius: 10px;
            font-weight: 600; font-size: 0.875rem;
            text-decoration: none; transition: all 0.2s;
        }
        .page-btn:hover { border-color: var(--primary); color: var(--primary); }
        .page-btn.disabled { opacity: 0.4; pointer-events: none; }
        .page-num {
            min-width: 40px; height: 40px;
            border: 1.5px solid var(--border); background: white;
            color: var(--text-muted); border-radius: 8px;
            font-weight: 600; font-size: 0.875rem;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none; transition: all 0.2s;
        }
        .page-num:hover { border-color: var(--primary); color: var(--primary); }
        .page-num.active {
            background: var(--gradient); color: white; border-color: var(--primary);
        }

        /* â-€â-€ RESPONSIVE â-€â-€ */
        @media (max-width: 1024px) {
            .status-list { grid-template-columns: repeat(2, 1fr); }
            .hero-section { flex-direction: column; }
            .status-box { width: 100%; }
        }
        @media (max-width: 768px) {
            .page-wrap { padding: 1.5rem 1rem 3rem; }
            .filters-row { grid-template-columns: 1fr; }
            .status-list { grid-template-columns: 1fr; }
            .auth-notice { flex-direction: column; }
            .notice-content { flex-direction: column; text-align: center; }
            .btn-notice-login { width: 100%; justify-content: center; }
            .nav-link span { display: none; }
        }
    </style>

</body>
</html>



