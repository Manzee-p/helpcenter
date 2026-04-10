{{-- resources/views/layouts/navbar.blade.php --}}

<nav class="layout-navbar" id="layout-navbar">

    <!-- Mobile toggle -->
    <button class="sidebar-toggle-btn d-md-none" onclick="toggleSidebar()">
        <i class='bx bx-menu'></i>
    </button>

    <!-- Page Title -->
    <div class="navbar-title">
        <h4>@yield('page_title', 'Dashboard')</h4>
        @hasSection('breadcrumb')
            <p>@yield('breadcrumb')</p>
        @endif
    </div>

    <!-- Right actions -->
    <ul class="navbar-actions">

        <!-- Search (desktop) -->
        <li class="d-none d-lg-block">
            <div class="search-wrap" id="search-wrap">
                <i class='bx bx-search'></i>
                <input
                    type="text"
                    id="nav-search"
                    class="search-input"
                    placeholder="Cari tiket, user..."
                    autocomplete="off"
                    oninput="handleNavSearch(this.value)"
                    onfocus="showSearchDrop()"
                />
                <div class="search-drop" id="search-drop" style="display:none;">
                    <div id="search-results-inner">
                        <div class="search-empty">
                            <i class='bx bx-search-alt'></i>
                            <span>Ketik untuk mencari...</span>
                        </div>
                    </div>
                </div>
            </div>
        </li>

        <!-- Fullscreen (desktop) -->
        <li class="d-none d-lg-block">
            <button class="action-btn" onclick="toggleFullscreen()" title="Layar Penuh">
                <i class='bx bx-fullscreen' id="fullscreen-icon"></i>
            </button>
        </li>

        <!-- Shortcuts (desktop) -->
        <li class="d-none d-lg-block" style="position:relative;">
            <button class="action-btn" onclick="toggleDrop('shortcuts-drop')" title="Akses Cepat">
                <i class='bx bx-grid-alt'></i>
            </button>
            <div class="nav-drop shortcuts-drop" id="shortcuts-drop" style="display:none;">
                <div class="drop-header">Akses Cepat</div>
                <div class="shortcuts-grid">
                    @if(Auth::user()->role === 'client' || Auth::user()->role === 'admin')
                    <a href="{{ Auth::user()->role === 'client' ? route('client.tickets.create') : route('admin.tickets.index') }}" class="shortcut-item">
                        <div class="sc-icon" style="background:#dbeafe;"><i class='bx bx-plus-circle' style="color:#2563eb;"></i></div>
                        <span>Buat Tiket</span>
                    </a>
                    @endif
                    <a href="{{ Auth::user()->role === 'admin' ? route('admin.tickets.index') : (Auth::user()->role === 'vendor' ? route('vendor.tickets.index') : route('client.tickets.index')) }}" class="shortcut-item">
                        <div class="sc-icon" style="background:#fef3c7;"><i class='bx bx-file' style="color:#d97706;"></i></div>
                        <span>Tiket Saya</span>
                    </a>
                    @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.analytics') }}" class="shortcut-item">
                        <div class="sc-icon" style="background:#d1fae5;"><i class='bx bx-bar-chart-alt-2' style="color:#059669;"></i></div>
                        <span>Analitik</span>
                    </a>
                    @endif
                    <a href="{{ Auth::user()->role === 'admin' ? route('admin.settings') : (Auth::user()->role === 'vendor' ? route('vendor.settings') : route('client.settings')) }}" class="shortcut-item">
                        <div class="sc-icon" style="background:#e9d5ff;"><i class='bx bx-cog' style="color:#7c3aed;"></i></div>
                        <span>Pengaturan</span>
                    </a>
                </div>
            </div>
        </li>

        {{-- ── NOTIFIKASI ── include partial yang sudah ada script-nya --}}
        <li style="position:relative;">
            @include('layouts.notifications-dropdown')
        </li>

        <!-- User dropdown -->
        <li style="position:relative;">
            <button class="user-btn" onclick="toggleDrop('user-drop')">
                <div class="user-av">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="user-av-img" />
                    @else
                        <span>{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                    @endif
                    <span class="online-dot"></span>
                </div>
                <div class="user-text d-none d-lg-flex">
                    <span class="u-name">{{ Str::limit(Auth::user()->name, 16) }}</span>
                    <span class="u-role">
                        @if(Auth::user()->role === 'admin') Administrator
                        @elseif(Auth::user()->role === 'vendor') Vendor
                        @else Klien
                        @endif
                    </span>
                </div>
                <i class='bx bx-chevron-down chev'></i>
            </button>

            <div class="nav-drop user-drop" id="user-drop" style="display:none;">
                <!-- User info header -->
                <div class="user-drop-header">
                    <div class="user-av-lg">
                        @if(Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" />
                        @else
                            <span>{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                        @endif
                        <span class="online-dot-lg"></span>
                    </div>
                    <div>
                        <div class="udh-name">{{ Auth::user()->name }}</div>
                        <div class="udh-role">
                            @if(Auth::user()->role === 'admin') Administrator
                            @elseif(Auth::user()->role === 'vendor') Vendor
                            @else Klien
                            @endif
                        </div>
                    </div>
                </div>
                <div class="drop-divider"></div>
                <a href="{{ Auth::user()->role === 'admin' ? route('admin.settings') : (Auth::user()->role === 'vendor' ? route('vendor.settings') : route('client.settings')) }}" class="drop-item">
                    <i class='bx bx-user'></i><span>Profil Saya</span>
                </a>
                <a href="{{ Auth::user()->role === 'admin' ? route('admin.settings') : (Auth::user()->role === 'vendor' ? route('vendor.settings') : route('client.settings')) }}" class="drop-item">
                    <i class='bx bx-cog'></i><span>Pengaturan</span>
                </a>
                <div class="drop-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="drop-item drop-item--danger" style="width:100%;text-align:left;">
                        <i class='bx bx-log-out'></i><span>Keluar</span>
                    </button>
                </form>
            </div>
        </li>

    </ul>
</nav>

<style>
/* ───── NAVBAR ───── */
.layout-navbar {
    position: fixed;
    top: 0;
    left: var(--sidebar-width);
    right: 0;
    height: var(--navbar-height);
    background: white;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    padding: 0 1.5rem;
    gap: 1rem;
    z-index: 997;
    box-shadow: var(--shadow-sm);
    transition: left 0.3s ease;
}

@media (max-width: 991px) {
    .layout-navbar { left: 0; }
}

.sidebar-toggle-btn {
    background: none; border: none; cursor: pointer;
    width: 38px; height: 38px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.375rem; color: var(--text-muted);
    transition: all 0.2s; flex-shrink: 0;
}
.sidebar-toggle-btn:hover { background: var(--bg); color: var(--text); }

.navbar-title { flex: 1; min-width: 0; }
.navbar-title h4 {
    font-size: 1.125rem; font-weight: 700; color: var(--text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.navbar-title p { font-size: 0.75rem; color: var(--text-light); margin-top: 1px; }

.navbar-actions {
    display: flex; align-items: center; gap: 0.375rem;
    list-style: none; margin: 0; flex-shrink: 0;
}

/* Action button */
.action-btn {
    width: 38px; height: 38px;
    background: none; border: none; cursor: pointer;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; color: var(--text-muted);
    transition: all 0.2s; position: relative;
}
.action-btn:hover { background: var(--bg); color: var(--primary); }

/* Search */
.search-wrap { position: relative; width: 260px; }
.search-wrap > i {
    position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);
    font-size: 1.125rem; color: var(--text-light); pointer-events: none;
}
.search-input {
    width: 100%; padding: 0.5625rem 0.875rem 0.5625rem 2.375rem;
    border: 1.5px solid var(--border); border-radius: 9px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.875rem; color: var(--text); background: var(--bg);
    transition: all 0.2s; outline: none;
}
.search-input:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
.search-input::placeholder { color: var(--text-light); }

.search-drop {
    position: absolute; top: calc(100% + 6px); left: 0; right: 0;
    background: white; border: 1px solid var(--border); border-radius: 10px;
    box-shadow: var(--shadow-lg); z-index: 1001; max-height: 280px; overflow-y: auto;
}
.search-result-item {
    display: flex; align-items: center; gap: 0.625rem;
    padding: 0.75rem 1rem; cursor: pointer; transition: background 0.15s;
    font-size: 0.875rem; color: var(--text-muted);
}
.search-result-item:hover { background: var(--bg); }
.search-result-item i { font-size: 1rem; color: var(--primary); }
.search-empty {
    display: flex; flex-direction: column; align-items: center;
    gap: 0.5rem; padding: 1.5rem; color: var(--text-light); font-size: 0.875rem;
}
.search-empty i { font-size: 1.75rem; }

/* Notification dot (dipakai oleh partial notifications-dropdown) */
.notif-btn { position: relative; }
.badge-dot {
    position: absolute; top: 7px; right: 7px;
    width: 8px; height: 8px; background: var(--danger); border-radius: 50%;
    border: 2px solid white;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%,100% { transform: scale(1); }
    50%      { transform: scale(1.15); }
}

/* Dropdowns */
.nav-drop {
    position: absolute; top: calc(100% + 8px); right: 0;
    background: white; border: 1px solid var(--border); border-radius: 12px;
    box-shadow: var(--shadow-lg); z-index: 1001; min-width: 280px;
    animation: dropIn 0.15s ease;
}
@keyframes dropIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.drop-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.875rem 1.125rem;
    font-size: 0.9375rem; font-weight: 700; color: var(--text);
    background: var(--bg); border-radius: 12px 12px 0 0;
}
.badge-count {
    background: var(--primary); color: white;
    font-size: 0.6875rem; padding: 0.2rem 0.5rem;
    border-radius: 20px; font-weight: 700;
}
.drop-divider { height: 1px; background: var(--border); }

/* Shortcuts */
.shortcuts-drop { min-width: 300px; }
.shortcuts-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 0.625rem; padding: 0.875rem;
}
.shortcut-item {
    display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
    padding: 0.875rem 0.5rem; border-radius: 9px;
    background: var(--bg); text-decoration: none;
    transition: all 0.2s; cursor: pointer;
}
.shortcut-item:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
.sc-icon {
    width: 44px; height: 44px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center; font-size: 1.375rem;
}
.shortcut-item span { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-align: center; }

/* Notifications dropdown — min-width diatur di partial */
.notif-drop { min-width: 340px; }

/* User button */
.user-btn {
    display: flex; align-items: center; gap: 0.625rem;
    background: none; border: none; cursor: pointer;
    padding: 0.375rem 0.625rem; border-radius: 10px;
    transition: all 0.2s;
}
.user-btn:hover { background: var(--bg); }
.user-av {
    position: relative; width: 34px; height: 34px;
    border-radius: 50%; background: var(--gradient);
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 0.8125rem; font-weight: 700;
    overflow: hidden; box-shadow: 0 2px 8px rgba(79,70,229,0.25);
}
.user-av-img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
.online-dot {
    position: absolute; bottom: 0; right: 0;
    width: 9px; height: 9px; background: var(--success);
    border: 2px solid white; border-radius: 50%; z-index: 1;
}
.user-text { display: flex; flex-direction: column; align-items: flex-start; }
.u-name { font-size: 0.8125rem; font-weight: 700; color: var(--text); line-height: 1.2; }
.u-role { font-size: 0.71rem; color: var(--text-muted); }
.chev { font-size: 1.125rem; color: var(--text-muted); }

/* User dropdown */
.user-drop { min-width: 260px; }
.user-drop-header {
    display: flex; align-items: center; gap: 0.875rem;
    padding: 1.125rem;
    background: linear-gradient(135deg, rgba(79,70,229,0.04), rgba(124,58,237,0.04));
    border-radius: 12px 12px 0 0;
}
.user-av-lg {
    position: relative; width: 52px; height: 52px;
    border-radius: 50%; background: var(--gradient);
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 1.25rem; font-weight: 700;
    overflow: hidden; flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(79,70,229,0.25);
}
.user-av-lg img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
.online-dot-lg {
    position: absolute; bottom: 1px; right: 1px;
    width: 13px; height: 13px; background: var(--success);
    border: 3px solid white; border-radius: 50%; z-index: 1;
}
.udh-name { font-size: 0.9375rem; font-weight: 700; color: var(--text); }
.udh-role { font-size: 0.8rem; color: var(--text-muted); margin-top: 1px; }
.drop-item {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.75rem 1.125rem; font-size: 0.875rem; font-weight: 500;
    color: var(--text-muted); text-decoration: none;
    transition: all 0.15s; cursor: pointer;
    background: none; border: none;
}
.drop-item:hover { background: var(--bg); padding-left: 1.375rem; color: var(--text); }
.drop-item i { font-size: 1.125rem; }
.drop-item--danger { color: var(--danger) !important; }
.drop-item--danger:hover { background: rgba(239,68,68,0.06) !important; }

/* View all link (dipakai partial notif) */
.view-all-link {
    display: block; text-align: center; padding: 0.75rem;
    font-size: 0.875rem; font-weight: 700; color: var(--primary);
    text-decoration: none; transition: background 0.15s;
    border-radius: 0 0 12px 12px;
}
.view-all-link:hover { background: var(--bg); }
</style>

<script>
// ─── Generic dropdown toggle ───────────────────────────────────────────────
function toggleDrop(id) {
    const target    = document.getElementById(id);
    const isVisible = target.style.display === 'block';
    // tutup semua dulu
    ['user-drop','notif-drop','shortcuts-drop','search-drop'].forEach(d => {
        const el = document.getElementById(d);
        if (el) el.style.display = 'none';
    });
    target.style.display = isVisible ? 'none' : 'block';
}

// Tutup dropdown saat klik di luar
document.addEventListener('click', function (e) {
    ['user-drop','notif-drop','shortcuts-drop'].forEach(id => {
        const drop = document.getElementById(id);
        if (drop && !drop.closest('li')?.contains(e.target)) {
            drop.style.display = 'none';
        }
    });
    // search
    const sw = document.getElementById('search-wrap');
    const sd = document.getElementById('search-drop');
    if (sd && sw && !sw.contains(e.target)) sd.style.display = 'none';
});

// ─── Fullscreen ───────────────────────────────────────────────────────────
function toggleFullscreen() {
    const icon = document.getElementById('fullscreen-icon');
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
        icon?.classList.replace('bx-fullscreen', 'bx-exit-fullscreen');
    } else {
        document.exitFullscreen();
        icon?.classList.replace('bx-exit-fullscreen', 'bx-fullscreen');
    }
}

// ─── Search ───────────────────────────────────────────────────────────────
let navSearchTimer = null;

function showSearchDrop() {
    document.getElementById('search-drop').style.display = 'block';
}

function handleNavSearch(val) {
    clearTimeout(navSearchTimer);
    const inner = document.getElementById('search-results-inner');
    if (!val.trim()) {
        inner.innerHTML = `<div class="search-empty"><i class='bx bx-search-alt'></i><span>Ketik untuk mencari...</span></div>`;
        return;
    }
    inner.innerHTML = `<div class="search-empty"><i class='bx bx-loader-alt bx-spin'></i><span>Mencari...</span></div>`;
    document.getElementById('search-drop').style.display = 'block';

    navSearchTimer = setTimeout(() => {
        // TODO: ganti dengan AJAX ke endpoint pencarian tiket/user yang sesungguhnya
        inner.innerHTML = `
            <div class="search-result-item"><i class='bx bx-file'></i><span>Tiket #TKT-${val.toUpperCase()}</span></div>
            <div class="search-result-item"><i class='bx bx-user'></i><span>User: ${val}</span></div>
        `;
    }, 500);
}
</script>
