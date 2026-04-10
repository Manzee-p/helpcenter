<aside id="layout-menu" class="layout-menu">

    <!-- Brand -->
    <div class="sidebar-brand">
        <a href="{{ url('/home') }}" class="brand-link">
            <div class="brand-icon"><i class='bx bx-support'></i></div>
            <span class="brand-name">HelpDesk</span>
        </a>
        <button class="sidebar-close-btn d-md-none" onclick="closeSidebar()">
            <i class='bx bx-x'></i>
        </button>
    </div>

    <!-- User Profile -->
    <div class="sidebar-profile">
        <div class="profile-avatar">
            @if(Auth::user()->avatar)
                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" />
            @else
                <span>{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
            @endif
        </div>
        <div class="profile-info">
            <div class="profile-name">{{ Auth::user()->name }}</div>
            <div class="profile-role">
                @if(Auth::user()->role === 'admin') Administrator
                @elseif(Auth::user()->role === 'vendor') Vendor
                @else Klien
                @endif
            </div>
        </div>
    </div>

    <div class="sidebar-divider"></div>

    <!-- Nav Items -->
    <nav class="sidebar-nav">
        <ul class="nav-list">

            {{-- ═══ ADMIN MENU ═══ --}}
            @if(Auth::user()->role === 'admin')

                <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-home-circle'></i></div>
                        <span>Dasbor</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.tickets*') ? 'active' : '' }}">
                    <a href="{{ route('admin.tickets.index') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-file'></i></div>
                        <span>Semua Tiket</span>
                    </a>
                </li>

                <li class="nav-section-title"><span>MANAJEMEN</span></li>

                <li class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-user'></i></div>
                        <span>Pengguna</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.vendors*') ? 'active' : '' }}">
                    <a href="{{ route('admin.vendors.index') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-group'></i></div>
                        <span>Vendor</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.vendor-ratings*') ? 'active' : '' }}">
                    <a href="{{ route('admin.vendor-ratings.index') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-star'></i></div>
                        <span>Rating Vendor</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                    <a href="{{ route('admin.categories.index') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-category'></i></div>
                        <span>Kategori</span>
                    </a>
                </li>

                <li class="nav-section-title"><span>ANALISIS & LAPORAN</span></li>

                <li class="nav-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                    <a href="{{ route('admin.analytics') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-bar-chart-alt-2'></i></div>
                        <span>Analitik</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                    <a href="{{ route('admin.reports') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-file-blank'></i></div>
                        <span>Laporan</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.status-board*') ? 'active' : '' }}">
                    <a href="{{ route('admin.status-board.index') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-info-circle'></i></div>
                        <span>Papan Status</span>
                    </a>
                </li>

                <li class="nav-section-title"><span>PENGATURAN</span></li>

                <li class="nav-item {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-cog'></i></div>
                        <span>Pengaturan</span>
                    </a>
                </li>

            {{-- ═══ VENDOR MENU ═══ --}}
            @elseif(Auth::user()->role === 'vendor')

                <li class="nav-item {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('vendor.dashboard') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-home-circle'></i></div>
                        <span>Dasbor</span>
                    </a>
                </li>

                <li class="nav-section-title"><span>TIKET</span></li>

                <li class="nav-item {{ request()->routeIs('vendor.tickets*') ? 'active' : '' }}">
                    <a href="{{ route('vendor.tickets.index') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-file'></i></div>
                        <span>Tiket Ditugaskan</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('vendor.history') ? 'active' : '' }}">
                    <a href="{{ route('vendor.history') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-history'></i></div>
                        <span>Riwayat Tiket</span>
                    </a>
                </li>

                <li class="nav-section-title"><span>LAINNYA</span></li>

                <li class="nav-item {{ request()->routeIs('vendor.reports') ? 'active' : '' }}">
                    <a href="{{ route('vendor.reports') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-bar-chart-alt-2'></i></div>
                        <span>Laporan</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('vendor.ratings') ? 'active' : '' }}">
                    <a href="{{ route('vendor.ratings') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-star'></i></div>
                        <span>Penilaian Klien</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('vendor.settings') ? 'active' : '' }}">
                    <a href="{{ route('vendor.settings') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-cog'></i></div>
                        <span>Pengaturan</span>
                    </a>
                </li>

            {{-- ═══ CLIENT MENU ═══ --}}
            @else

                <li class="nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('client.dashboard') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-home-circle'></i></div>
                        <span>Dasbor</span>
                    </a>
                </li>

                <li class="nav-section-title"><span>TIKET</span></li>

                <li class="nav-item {{ request()->routeIs('client.tickets.create') ? 'active' : '' }}">
                    <a href="{{ route('client.tickets.create') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-plus-circle'></i></div>
                        <span>Buat Tiket</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('client.tickets.index') ? 'active' : '' }}">
                    <a href="{{ route('client.tickets.index') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-file'></i></div>
                        <span>Tiket Saya</span>
                    </a>
                </li>

                <li class="nav-section-title"><span>LAINNYA</span></li>

                <li class="nav-item {{ request()->routeIs('client.history') ? 'active' : '' }}">
                    <a href="{{ route('client.history') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-history'></i></div>
                        <span>Riwayat</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('client.pending-ratings') ? 'active' : '' }}">
                    <a href="{{ route('client.pending-ratings') }}" class="nav-link">
                        <div class="nav-icon"><i class='bx bx-star'></i></div>
                        <span>Belum Dirating</span>
                    </a>
                </li>

            @endif

        </ul>
    </nav>

</aside>

<style>
/* ───── SIDEBAR ───── */
.layout-menu {
    width: var(--sidebar-width);
    height: 100vh;
    position: fixed;
    left: 0; top: 0;
    background: white;
    border-right: 1px solid var(--border);
    box-shadow: 2px 0 12px rgba(0,0,0,0.06);
    display: flex;
    flex-direction: column;
    z-index: 999;
    transition: transform 0.3s ease;
    overflow-y: auto;
    overflow-x: hidden;
}

.layout-menu::-webkit-scrollbar { width: 4px; }
.layout-menu::-webkit-scrollbar-track { background: transparent; }
.layout-menu::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

/* Brand */
.sidebar-brand {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.25rem 1rem;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}

.brand-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
}

.brand-icon {
    width: 40px; height: 40px;
    background: var(--gradient);
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; color: white; flex-shrink: 0;
}

.brand-name {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--text);
}

.sidebar-close-btn {
    background: none; border: none; cursor: pointer;
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: var(--text-muted); font-size: 1.375rem;
    transition: all 0.2s;
}
.sidebar-close-btn:hover { background: var(--bg); color: var(--text); }

/* Profile */
.sidebar-profile {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: rgba(79,70,229,0.05);
    margin: 0.875rem;
    border-radius: 12px;
    border: 1px solid rgba(79,70,229,0.12);
    flex-shrink: 0;
}

.profile-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: var(--gradient);
    display: flex; align-items: center; justify-content: center;
    color: white; font-weight: 700; font-size: 0.875rem;
    overflow: hidden; flex-shrink: 0;
    box-shadow: 0 3px 10px rgba(79,70,229,0.25);
}

.profile-avatar img { width: 100%; height: 100%; object-fit: cover; }

.profile-name {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.profile-role {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 1px;
}

.sidebar-divider {
    height: 1px;
    background: var(--border);
    margin: 0 1rem 0.5rem;
}

/* Nav */
.sidebar-nav { flex: 1; padding: 0 0.875rem 1.5rem; }

.nav-list { list-style: none; display: flex; flex-direction: column; gap: 2px; }

.nav-section-title {
    padding: 1rem 0.75rem 0.375rem;
    margin-top: 0.25rem;
}

.nav-section-title span {
    font-size: 0.6875rem;
    font-weight: 700;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.nav-item { border-radius: 10px; overflow: visible; }

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6875rem 0.875rem;
    text-decoration: none;
    color: var(--text-muted);
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s;
    position: relative;
}

.nav-link::before {
    content: '';
    position: absolute;
    left: 0; top: 0; height: 100%; width: 3px;
    background: var(--gradient);
    transform: scaleY(0);
    transition: transform 0.2s;
    border-radius: 0 2px 2px 0;
}

.nav-link:hover {
    background: rgba(79,70,229,0.06);
    color: var(--text);
}

.nav-item.active .nav-link {
    background: rgba(79,70,229,0.1);
    color: var(--primary);
    font-weight: 600;
}

.nav-item.active .nav-link::before { transform: scaleY(1); }

.nav-icon {
    width: 34px; height: 34px;
    border-radius: 8px;
    background: rgba(79,70,229,0.07);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.125rem;
    color: var(--text-muted);
    flex-shrink: 0;
    transition: all 0.2s;
}

.nav-item.active .nav-icon {
    background: var(--gradient);
    color: white;
    box-shadow: 0 2px 8px rgba(79,70,229,0.3);
}

/* Responsive */
@media (max-width: 991px) {
    .layout-menu {
        transform: translateX(-100%);
        z-index: 1000;
    }
    .layout-menu.open {
        transform: translateX(0);
    }
}
</style>