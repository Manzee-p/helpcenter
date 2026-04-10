<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HelpCenter') — HelpCenter</title>

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #5b6ee1;
            --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --text: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --bg: #f8f9ff;
            --border: #e2e8f0;
            --topbar-height: 74px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(102,126,234,0.10), transparent 35%),
                linear-gradient(180deg, #f8f9ff 0%, #f4f6fb 100%);
            color: var(--text);
            min-height: 100vh;
        }

        /* ───── TOP BAR ───── */
        .client-topbar {
            position: sticky; top: 0; z-index: 1000;
            height: var(--topbar-height);
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(148,163,184,0.16);
            box-shadow: 0 4px 24px rgba(15,23,42,0.06);
            display: flex; align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem; gap: 1rem;
        }

        .topbar-logo {
            display: flex; align-items: center; gap: 0.6rem;
            text-decoration: none; flex-shrink: 0;
        }
        .topbar-logo i { font-size: 2rem; color: var(--primary); }
        .topbar-logo span { font-size: 1.4rem; font-weight: 800; color: var(--primary); letter-spacing: -0.02em; }

        .topbar-center {
            flex: 1; display: flex;
            align-items: center; justify-content: center; gap: 1rem;
        }

        .topbar-nav {
            display: flex; gap: 0.3rem; padding: 0.3rem;
            background: rgba(99,102,241,0.05);
            border: 1px solid rgba(99,102,241,0.08);
            border-radius: 16px;
        }
        .topbar-nav a {
            display: flex; align-items: center; gap: 0.45rem;
            padding: 0.65rem 1.1rem; border-radius: 12px;
            text-decoration: none; color: #6c757d;
            font-weight: 600; font-size: 0.9rem;
            transition: all 0.25s; white-space: nowrap;
        }
        .topbar-nav a i { font-size: 1.2rem; }
        .topbar-nav a:hover { color: var(--primary); background: rgba(255,255,255,0.9); }
        .topbar-nav a.active { color: var(--primary); background: #fff; box-shadow: 0 4px 14px rgba(99,102,241,0.15); }

        .nav-insight {
            display: inline-flex; align-items: center; gap: 0.65rem;
            padding: 0.55rem 0.8rem;
            border: 1px solid rgba(102,126,234,0.1);
            border-radius: 14px;
            background: rgba(255,255,255,0.88);
            box-shadow: 0 4px 16px rgba(102,126,234,0.07);
            text-decoration: none; transition: all 0.25s;
        }
        .nav-insight:hover { box-shadow: 0 6px 20px rgba(102,126,234,0.14); }
        .nav-insight-icon {
            width: 34px; height: 34px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, rgba(102,126,234,0.14), rgba(118,75,162,0.18));
            color: var(--primary); flex-shrink: 0;
        }
        .nav-insight-icon i { font-size: 1rem; }
        .nav-insight-body { display: flex; flex-direction: column; gap: 0.1rem; min-width: 0; }
        .nav-insight-label { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #8a94aa; }
        .nav-insight-body strong { font-size: 0.85rem; color: #1f2937; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .nav-insight-body small { font-size: 0.73rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .topbar-right { display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0; position: relative; }

        .btn-create-ticket {
            display: inline-flex; align-items: center; gap: 0.45rem;
            padding: 0.65rem 1.2rem; background: var(--gradient); color: #fff;
            font-weight: 700; font-size: 0.875rem; border-radius: 13px;
            text-decoration: none; box-shadow: 0 8px 20px rgba(102,126,234,0.25);
            transition: all 0.25s; border: none; cursor: pointer; white-space: nowrap;
        }
        .btn-create-ticket:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(102,126,234,0.32); color: #fff; }
        .btn-create-ticket i { font-size: 1.2rem; }

        .topbar-bell {
            position: relative; width: 42px; height: 42px; border-radius: 13px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(99,102,241,0.06); border: 1px solid rgba(99,102,241,0.1);
            cursor: pointer; transition: all 0.2s; text-decoration: none;
        }
        .topbar-bell:hover { background: rgba(99,102,241,0.12); }
        .topbar-bell i { font-size: 1.4rem; color: #64748b; }

        .topbar-user {
            display: flex; align-items: center; gap: 0.6rem;
            padding: 0.4rem 0.75rem 0.4rem 0.4rem;
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(148,163,184,0.16); border-radius: 16px;
            box-shadow: 0 4px 16px rgba(15,23,42,0.07);
            cursor: pointer; transition: all 0.2s; position: relative;
        }
        .topbar-user:hover { box-shadow: 0 6px 22px rgba(15,23,42,0.1); }

        .user-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: var(--gradient); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.875rem; overflow: hidden; flex-shrink: 0;
            position: relative;
        }
        .user-avatar img {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%; object-fit: cover;
        }
        .user-info { display: flex; flex-direction: column; gap: 0.1rem; }
        .user-info-name { font-weight: 700; font-size: 0.875rem; color: #1f2937; white-space: nowrap; }
        .user-info-role { font-size: 0.75rem; color: #8a94aa; }
        .topbar-user > .bx { font-size: 1.1rem; color: #94a3b8; }

        .topbar-dropdown {
            display: none; position: absolute; top: calc(100% + 0.75rem); right: 0;
            background: #fff; border-radius: 18px;
            box-shadow: 0 16px 48px rgba(15,23,42,0.14);
            min-width: 270px; overflow: hidden; z-index: 3000;
            border: 1px solid rgba(148,163,184,0.12);
        }
        .topbar-dropdown.open { display: block; }

        .dropdown-header-user {
            padding: 1.25rem 1.5rem; background: var(--gradient);
            color: #fff; display: flex; align-items: center; gap: 0.875rem;
        }
        .dropdown-avatar {
            width: 50px; height: 50px; border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1.1rem; flex-shrink: 0;
            overflow: hidden; position: relative;
        }
        .dropdown-avatar img { position: absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; }
        .dropdown-user-name { font-weight: 700; font-size: 0.9375rem; }
        .dropdown-user-email { font-size: 0.8rem; opacity: 0.85; margin-top: 0.15rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width: 180px; }

        .dropdown-divider { height: 1px; background: #f1f5f9; margin: 0.375rem 0; }

        .dropdown-item {
            display: flex; align-items: center; gap: 0.875rem;
            padding: 0.875rem 1.5rem; text-decoration: none; color: #334155;
            font-weight: 600; font-size: 0.875rem; transition: background 0.2s;
            background: none; border: none; cursor: pointer; width: 100%; text-align: left;
        }
        .dropdown-item:hover { background: #f8fafc; }
        .dropdown-item i { font-size: 1.375rem; color: var(--primary); }
        .dropdown-item.danger { color: #ef4444; }
        .dropdown-item.danger i { color: #ef4444; }

        /* Mobile */
        .mobile-toggle {
            display: none; width: 42px; height: 42px;
            align-items: center; justify-content: center;
            border: 1px solid rgba(148,163,184,0.18); border-radius: 12px;
            background: rgba(255,255,255,0.9); color: #475569;
            cursor: pointer; font-size: 1.4rem;
        }
        .mobile-drawer {
            display: none; position: sticky; top: var(--topbar-height); z-index: 990;
            background: rgba(255,255,255,0.97);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 8px 24px rgba(15,23,42,0.1);
            padding: 1rem; flex-direction: column; gap: 0.5rem;
        }
        .mobile-drawer.open { display: flex; }
        .mobile-nav-link {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.875rem 1rem; border-radius: 13px;
            text-decoration: none; color: #334155;
            font-weight: 600; font-size: 0.9rem;
            border: 1px solid #e2e8f0; transition: all 0.2s;
            background: none; cursor: pointer; width: 100%; text-align: left;
        }
        .mobile-nav-link i { font-size: 1.3rem; color: var(--primary); }
        .mobile-nav-link:hover, .mobile-nav-link.active {
            background: rgba(99,102,241,0.06); border-color: rgba(99,102,241,0.15); color: var(--primary);
        }
        .mobile-nav-link.primary { background: var(--gradient); color: #fff; border-color: transparent; }
        .mobile-nav-link.primary i { color: #fff; }

        .client-main { min-height: calc(100vh - var(--topbar-height)); padding: 1.75rem; }
        .client-container { max-width: 1400px; margin: 0 auto; }

        .topbar-overlay { display: none; position: fixed; inset: 0; background: transparent; z-index: 999; }
        .topbar-overlay.active { display: block; }

        @media (max-width: 1200px) { .nav-insight { max-width: 220px; } }
        @media (max-width: 992px) {
            .topbar-nav a span { display: none; }
            .topbar-nav a { padding: 0.65rem; }
            .nav-insight-body { display: none; }
            .nav-insight { padding: 0.55rem; }
        }
        @media (max-width: 768px) {
            .topbar-center { display: none; }
            .topbar-right { display: none; }
            .mobile-toggle { display: inline-flex; }
            .client-main { padding: 1rem; }
        }
    </style>

</head>
<body>

{{-- ─── Hitung insight langsung di PHP (tidak perlu AJAX/route tambahan) ─── --}}
@php
    $authUser   = auth()->user();
    $nameArr    = explode(' ', trim($authUser->name));
    $initials   = strtoupper(substr($nameArr[0], 0, 1) . substr(end($nameArr), 0, 1));

    $_active    = \App\Models\Ticket::where('user_id', $authUser->id)
                    ->whereIn('status', ['new','open','in_progress','assigned','waiting_response'])
                    ->count();
    $_pending   = \App\Models\Ticket::where('user_id', $authUser->id)
                    ->whereIn('status', ['resolved','closed'])
                    ->whereDoesntHave('feedback')
                    ->count();
    $_total     = \App\Models\Ticket::where('user_id', $authUser->id)->count();
    $_latest    = \App\Models\Ticket::where('user_id', $authUser->id)->latest()->value('ticket_number');

    if ($_pending > 0) {
        $_insightPrimary   = $_pending . ' layanan belum dinilai';
        $_insightSecondary = 'Beri rating agar evaluasi vendor lebih akurat.';
    } elseif ($_active > 0) {
        $_insightPrimary   = $_active . ' laporan masih diproses';
        $_insightSecondary = $_latest ? 'Terbaru: ' . $_latest : 'Pantau progres dari sini.';
    } else {
        $_insightPrimary   = $_total > 0 ? 'Semua tiket terkendali' : 'Siap membuat tiket baru';
        $_insightSecondary = $_total > 0 ? $_total . ' tiket sudah tercatat' : 'Gunakan Create Ticket saat butuh bantuan.';
    }
@endphp

<header class="client-topbar">

    <a href="{{ route('client.dashboard') }}" class="topbar-logo">
        <i class='bx bx-help-circle'></i>
        <span>HelpCenter</span>
    </a>

    <div class="topbar-center">
        <nav class="topbar-nav">
            <a href="{{ route('client.dashboard') }}"
               class="{{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                <i class='bx bx-home-circle'></i><span>Dashboard</span>
            </a>
            <a href="{{ route('client.tickets.index') }}"
               class="{{ request()->routeIs('client.tickets.*') ? 'active' : '' }}">
                <i class='bx bx-list-ul'></i><span>Laporan Saya</span>
            </a>
            <a href="{{ route('client.history') }}"
               class="{{ request()->routeIs('client.history') ? 'active' : '' }}">
                <i class='bx bx-history'></i><span>Riwayat</span>
            </a>
            <a href="{{ route('client.pending-ratings') }}"
               class="{{ request()->routeIs('client.pending-ratings') ? 'active' : '' }}">
                <i class='bx bx-star'></i><span>Belum Dirating</span>
            </a>
        </nav>

        <a href="{{ route('client.pending-ratings') }}" class="nav-insight">
            <div class="nav-insight-icon"><i class='bx bx-pulse'></i></div>
            <div class="nav-insight-body">
                <span class="nav-insight-label">Belum Dinilai</span>
                <strong>{{ $_insightPrimary }}</strong>
                <small>{{ $_insightSecondary }}</small>
            </div>
        </a>
    </div>

    <div class="topbar-right">
        <a href="{{ route('client.tickets.create') }}" class="btn-create-ticket">
            <i class='bx bx-plus-circle'></i><span>Create Ticket</span>
        </a>

        <a href="{{ route('notifications.index') }}" class="topbar-bell">
            <i class='bx bx-bell'></i>
        </a>

        <div class="topbar-user" id="user-btn">
            <div class="user-avatar">
                @if($authUser->avatar)
                    <img src="{{ asset('storage/' . $authUser->avatar) }}" alt="" onerror="this.remove()">
                @endif
                {{ $initials }}
            </div>
            <div class="user-info">
                <span class="user-info-name">{{ $authUser->name }}</span>
                <span class="user-info-role">Client</span>
            </div>
            <i class='bx bx-chevron-down' id="user-chevron"></i>
        </div>

        <div class="topbar-dropdown" id="user-dropdown">
            <div class="dropdown-header-user">
                <div class="dropdown-avatar">
                    @if($authUser->avatar)
                        <img src="{{ asset('storage/' . $authUser->avatar) }}" alt="" onerror="this.remove()">
                    @endif
                    {{ $initials }}
                </div>
                <div>
                    <div class="dropdown-user-name">{{ $authUser->name }}</div>
                    <div class="dropdown-user-email">{{ $authUser->email }}</div>
                </div>
            </div>
            <div class="dropdown-divider"></div>
            <a href="{{ route('client.settings') }}" class="dropdown-item"><i class='bx bx-user'></i><span>Profil Saya</span></a>
            <a href="{{ route('client.settings') }}" class="dropdown-item"><i class='bx bx-cog'></i><span>Pengaturan</span></a>
            <a href="{{ route('client.history') }}"  class="dropdown-item"><i class='bx bx-history'></i><span>Riwayat Tiket</span></a>
            <a href="{{ route('client.pending-ratings') }}" class="dropdown-item"><i class='bx bx-star'></i><span>Butuh Rating</span></a>
            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item danger">
                    <i class='bx bx-log-out'></i><span>Logout</span>
                </button>
            </form>
        </div>
    </div>

    <button class="mobile-toggle" id="mobile-toggle" type="button">
        <i class='bx bx-menu' id="mobile-icon"></i>
    </button>
</header>

<nav class="mobile-drawer" id="mobile-drawer">
    <a href="{{ route('client.dashboard') }}"       class="mobile-nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}"><i class='bx bx-home-circle'></i><span>Dashboard</span></a>
    <a href="{{ route('client.tickets.index') }}"   class="mobile-nav-link {{ request()->routeIs('client.tickets.*') ? 'active' : '' }}"><i class='bx bx-list-ul'></i><span>Laporan Saya</span></a>
    <a href="{{ route('client.history') }}"         class="mobile-nav-link {{ request()->routeIs('client.history') ? 'active' : '' }}"><i class='bx bx-history'></i><span>Riwayat</span></a>
    <a href="{{ route('client.pending-ratings') }}" class="mobile-nav-link {{ request()->routeIs('client.pending-ratings') ? 'active' : '' }}"><i class='bx bx-star'></i><span>Belum Dirating</span></a>
    <a href="{{ route('client.tickets.create') }}"  class="mobile-nav-link primary"><i class='bx bx-plus-circle'></i><span>Create Ticket</span></a>
    <a href="{{ route('client.settings') }}"        class="mobile-nav-link"><i class='bx bx-cog'></i><span>Pengaturan</span></a>
    <form method="POST" action="{{ route('logout') }}" style="margin:0">
        @csrf
        <button type="submit" class="mobile-nav-link" style="color:#ef4444">
            <i class='bx bx-log-out' style="color:#ef4444"></i><span>Logout</span>
        </button>
    </form>
</nav>

<div class="topbar-overlay" id="topbar-overlay"></div>

<main class="client-main">
    <div class="client-container">
        @yield('content')
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const userBtn      = document.getElementById('user-btn');
    const userDropdown = document.getElementById('user-dropdown');
    const userChevron  = document.getElementById('user-chevron');
    const overlay      = document.getElementById('topbar-overlay');
    const mobileToggle = document.getElementById('mobile-toggle');
    const mobileDrawer = document.getElementById('mobile-drawer');
    const mobileIcon   = document.getElementById('mobile-icon');

    function closeAll() {
        userDropdown.classList.remove('open');
        overlay.classList.remove('active');
        mobileDrawer.classList.remove('open');
        userChevron.className = 'bx bx-chevron-down';
        mobileIcon.className  = 'bx bx-menu';
    }

    userBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = userDropdown.classList.contains('open');
        closeAll();
        if (!isOpen) {
            userDropdown.classList.add('open');
            overlay.classList.add('active');
            userChevron.className = 'bx bx-chevron-up';
        }
    });

    mobileToggle.addEventListener('click', () => {
        const isOpen = mobileDrawer.classList.contains('open');
        closeAll();
        if (!isOpen) { mobileDrawer.classList.add('open'); mobileIcon.className = 'bx bx-x'; }
    });

    overlay.addEventListener('mousedown', (e) => {
        if (!userDropdown.contains(e.target)) {
            closeAll();
        }
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAll(); });

    @if(session('success'))
    Swal.fire({ icon:'success', title:'Berhasil', text:'{{ addslashes(session("success")) }}', toast:true, position:'top-end', showConfirmButton:false, timer:3000, timerProgressBar:true });
    @endif
    @if(session('error'))
    Swal.fire({ icon:'error', title:'Gagal', text:'{{ addslashes(session("error")) }}', toast:true, position:'top-end', showConfirmButton:false, timer:4000, timerProgressBar:true });
    @endif
    @if(session('warning'))
    Swal.fire({ icon:'warning', title:'Perhatian', text:'{{ addslashes(session("warning")) }}', toast:true, position:'top-end', showConfirmButton:false, timer:4000, timerProgressBar:true });
    @endif
    @if(session('info'))
    Swal.fire({ icon:'info', title:'Info', text:'{{ addslashes(session("info")) }}', toast:true, position:'top-end', showConfirmButton:false, timer:3500, timerProgressBar:true });
    @endif
</script>

@stack('scripts')
@stack('styles')
</body>
</html>
