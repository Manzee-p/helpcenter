@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page_title', 'Manajemen User')
@section('breadcrumb', 'Home / Manajemen / Pengguna')

@push('styles')
<style>
/* ───── PAGE WRAP ───── */
.users-wrap { display: flex; flex-direction: column; gap: 1.5rem; animation: fadeIn 0.25s ease; }
@keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }

/* ───── HERO ───── */
.hero-card {
    display: flex; justify-content: space-between; align-items: center;
    gap: 1.5rem; padding: 1.875rem;
    background: linear-gradient(135deg, #eef2ff 0%, #faf5ff 100%);
    border: 1px solid rgba(79,70,229,0.12);
    border-radius: 28px;
    box-shadow: 0 8px 30px rgba(79,70,229,0.08);
}
.hero-kicker {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.35rem 0.8rem; border-radius: 999px;
    background: rgba(79,70,229,0.12); color: var(--primary);
    font-weight: 800; font-size: 0.75rem;
    letter-spacing: 0.06em; text-transform: uppercase;
}
.hero-copy h3 {
    margin: 0.75rem 0 0.4rem;
    font-size: clamp(1.4rem,3vw,2rem);
    font-weight: 800; color: var(--text);
}
.hero-copy p { color: var(--text-muted); font-size: 0.9375rem; max-width: 580px; margin: 0; }

/* ───── STATS ───── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0,1fr));
    gap: 1rem;
}
.stat-card {
    background: white; border: 1px solid var(--border);
    border-radius: 22px; padding: 1.25rem;
    display: flex; justify-content: space-between; align-items: flex-start;
    box-shadow: var(--shadow-sm); transition: all 0.25s;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
.stat-info > span  { display: block; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; margin-bottom: 0.35rem; }
.stat-info > strong { font-size: 2rem; font-weight: 800; color: var(--text); line-height: 1; }
.stat-icon {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.375rem; flex-shrink: 0;
}
.stat-icon--primary  { background: #eef2ff; color: var(--primary); }
.stat-icon--danger   { background: #fff1f2; color: #e11d48; }
.stat-icon--info     { background: #e0f7fa; color: #0891b2; }
.stat-icon--success  { background: #f0fdf4; color: #16a34a; }

/* ───── TABLE CARD ───── */
.table-card {
    background: white; border: 1px solid var(--border);
    border-radius: 26px; overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.table-card__head {
    display: flex; justify-content: space-between; align-items: center;
    gap: 1rem; padding: 1.25rem 1.375rem;
    border-bottom: 1px solid var(--border); flex-wrap: wrap;
}
.table-card__head h5 { margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--text); }
.table-card__filters { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }

/* ───── FILTER INPUTS ───── */
.search-wrap { position: relative; }
.search-wrap i {
    position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%);
    color: var(--text-light); font-size: 1rem; pointer-events: none;
}
.search-wrap input {
    padding: 0.7rem 0.875rem 0.7rem 2.5rem;
    border: 1.5px solid var(--border); border-radius: 14px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.875rem; color: var(--text); background: var(--bg);
    outline: none; width: 220px; transition: all 0.2s;
}
.search-wrap input:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(79,70,229,0.08); }
.filter-select {
    padding: 0.7rem 0.875rem; border: 1.5px solid var(--border);
    border-radius: 14px; font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.875rem; color: var(--text); background: var(--bg);
    outline: none; cursor: pointer; transition: all 0.2s;
}
.filter-select:focus { border-color: var(--primary); background: white; }

/* ───── TABLE ───── */
.table-shell { overflow-x: auto; }
.users-table { width: 100%; border-collapse: collapse; }
.users-table th {
    padding: 0.875rem 1.125rem;
    font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.05em; color: var(--text-muted);
    background: var(--bg); border-bottom: 1px solid var(--border);
    text-align: left; white-space: nowrap;
}
.users-table th.text-center { text-align: center; }
.users-table td {
    padding: 0.95rem 1.125rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle; font-size: 0.875rem; color: var(--text-muted);
}
.users-table tbody tr:last-child td { border-bottom: none; }
.users-table tbody tr { transition: background 0.15s; }
.users-table tbody tr:hover { background: #f8fafc; }

/* ───── USER CELL ───── */
.user-cell { display: flex; align-items: center; gap: 0.875rem; }
.u-avatar {
    width: 42px; height: 42px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem; font-weight: 800; flex-shrink: 0; overflow: hidden;
}
.u-avatar img { width: 100%; height: 100%; object-fit: cover; }
.av-admin   { background: #fff1f2; color: #e11d48; }
.av-vendor  { background: #e0f7fa; color: #0891b2; }
.av-client  { background: #f0fdf4; color: #16a34a; }
.u-name     { font-weight: 700; color: var(--text); font-size: 0.875rem; display: block; }
.u-id       { font-size: 0.75rem; color: var(--text-light); }

/* ───── CONTACT CELL ───── */
.contact-cell { display: grid; gap: 0.25rem; }
.contact-cell span { display: flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: var(--text-muted); }
.contact-cell i { font-size: 0.875rem; color: var(--text-light); }

/* ───── BADGES ───── */
.badge {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.35rem 0.75rem; border-radius: 999px;
    font-size: 0.72rem; font-weight: 800; white-space: nowrap;
}
.badge-admin   { background: #fff1f2; color: #e11d48; }
.badge-vendor  { background: #e0f7fa; color: #0891b2; }
.badge-client  { background: #f0fdf4; color: #16a34a; }
.badge-active  { background: rgba(22,163,74,0.1); color: #16a34a; }
.badge-inactive{ background: rgba(100,116,139,0.1); color: #475569; }

/* ───── ACTIONS DROPDOWN ───── */
.td-actions { text-align: center; position: relative; }
.dropdown-wrap { position: relative; display: inline-block; }
.btn-dots {
    width: 2.25rem; height: 2.25rem; border-radius: 10px;
    background: var(--bg); border: 1px solid var(--border);
    color: var(--text-muted); cursor: pointer; font-size: 1.125rem;
    display: inline-flex; align-items: center; justify-content: center;
    transition: all 0.2s;
}
.btn-dots:hover { background: #eef2ff; color: var(--primary); border-color: rgba(79,70,229,0.25); }
.dropdown-menu {
    position: absolute; right: 0; top: calc(100% + 6px);
    background: white; border: 1px solid var(--border);
    border-radius: 16px; box-shadow: 0 16px 40px rgba(0,0,0,0.12);
    min-width: 170px; z-index: 100; overflow: hidden;
    opacity: 0; visibility: hidden; transform: translateY(-6px);
    transition: all 0.18s ease;
    /* keep dropdown above table */
}
.dropdown-menu.open { opacity: 1; visibility: visible; transform: translateY(0); }
.dropdown-item {
    display: flex; align-items: center; gap: 0.625rem;
    padding: 0.7rem 1rem; font-size: 0.875rem; font-weight: 600;
    color: var(--text-muted); cursor: pointer; transition: background 0.15s;
    border: none; background: none; width: 100%; text-align: left;
    font-family: 'Plus Jakarta Sans', sans-serif;
    text-decoration: none;
}
.dropdown-item:hover { background: var(--bg); color: var(--text); }
.dropdown-item.danger { color: #e11d48; }
.dropdown-item.danger:hover { background: #fff1f2; }
.dropdown-divider { height: 1px; background: var(--border); margin: 0.25rem 0; }

/* ───── EMPTY / LOADING ───── */
.state-box {
    padding: 3.5rem 1.5rem; text-align: center;
    color: var(--text-muted);
}
.state-box i { font-size: 3rem; color: var(--text-light); display: block; margin-bottom: 0.75rem; }

/* ───── PAGINATION ───── */
.pagination-wrap {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1.125rem 1.375rem; border-top: 1px solid var(--border);
    gap: 1rem; flex-wrap: wrap;
}
.pagination-info { font-size: 0.825rem; color: var(--text-muted); }
.page-numbers { display: flex; gap: 0.35rem; }
.page-numbers button, .page-btn {
    border: 1px solid var(--border); background: white;
    border-radius: 10px; padding: 0.45rem 0.75rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.825rem; font-weight: 600; color: var(--text-muted);
    cursor: pointer; transition: all 0.2s; min-width: 2.25rem;
    display: inline-flex; align-items: center; justify-content: center;
}
.page-numbers button:hover:not(:disabled), .page-btn:hover:not(:disabled) {
    background: var(--bg); color: var(--primary); border-color: rgba(79,70,229,0.3);
}
.page-numbers button.active { background: var(--primary); color: white; border-color: var(--primary); }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* ───── MODALS ───── */
.modal-backdrop {
    display: none; position: fixed; inset: 0;
    background: rgba(15,23,42,0.5); z-index: 1050;
    align-items: center; justify-content: center;
    padding: 1rem;
}
.modal-backdrop.open { display: flex; }
.modal-box {
    background: white; border-radius: 26px; padding: 1.875rem;
    width: 100%; max-width: 500px;
    box-shadow: 0 30px 70px rgba(0,0,0,0.2);
    animation: fadeIn 0.2s ease;
    max-height: 90vh; overflow-y: auto;
}
.modal-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.375rem; }
.modal-head h5 { margin: 0; font-weight: 800; color: var(--text); font-size: 1.1rem; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); line-height: 1; padding: 0; }
.modal-close:hover { color: var(--text); }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-field { margin-bottom: 1rem; }
.form-field.full { grid-column: 1 / -1; }
.form-field label { display: block; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 0.45rem; }
.form-field input, .form-field select {
    width: 100%; border: 1.5px solid var(--border); border-radius: 14px;
    padding: 0.8rem 1rem; font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.9rem; color: var(--text); background: var(--bg);
    outline: none; transition: all 0.2s;
}
.form-field input:focus, .form-field select:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(79,70,229,0.08); }
.form-field .hint { font-size: 0.75rem; color: var(--text-light); margin-top: 0.35rem; display: block; }

.modal-foot { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border); }

/* ───── BUTTONS ───── */
.btn-primary-sm {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.7rem 1.25rem; background: var(--gradient);
    color: white; border: none; border-radius: 14px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.875rem; font-weight: 700; cursor: pointer;
    transition: all 0.2s; box-shadow: 0 4px 14px rgba(79,70,229,0.25);
    text-decoration: none;
}
.btn-primary-sm:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(79,70,229,0.3); color: white; }
.btn-secondary-sm {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.7rem 1.25rem; background: var(--bg);
    color: var(--text-muted); border: 1px solid var(--border);
    border-radius: 14px; font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.875rem; font-weight: 700; cursor: pointer;
    transition: all 0.2s;
}
.btn-secondary-sm:hover { background: #f1f5f9; color: var(--text); }
.btn-saving { opacity: 0.75; cursor: not-allowed; }

/* ───── FLASH ───── */
.flash {
    padding: 0.875rem 1.25rem; border-radius: 14px;
    display: flex; align-items: center; gap: 0.625rem;
    font-weight: 600; font-size: 0.875rem; margin-bottom: 0;
}
.flash-success { background: #f0fdf4; color: #16a34a; border: 1px solid rgba(22,163,74,0.2); }
.flash-error   { background: #fff1f2; color: #e11d48; border: 1px solid rgba(225,29,72,0.2); }

/* ───── RESPONSIVE ───── */
@media (max-width: 1199px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 767px) {
    .hero-card { flex-direction: column; align-items: flex-start; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .table-card__head { flex-direction: column; align-items: flex-start; }
    .table-card__filters { width: 100%; }
    .search-wrap input { width: 100%; }
    .form-row { grid-template-columns: 1fr; }
    .pagination-wrap { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="users-wrap">

    {{-- ── FLASH ── --}}
    @if(session('success'))
    <div class="flash flash-success"><i class='bx bx-check-circle'></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="flash flash-error"><i class='bx bx-error-circle'></i> {{ session('error') }}</div>
    @endif

    {{-- ═══ HERO ═══ --}}
    <section class="hero-card">
        <div class="hero-copy">
            <span class="hero-kicker"><i class='bx bx-user-circle'></i> Manajemen User</span>
            <h3>Kelola pengguna sistem</h3>
            <p>Mengelola pengguna sistem, peran, serta izin akses seluruh anggota tim.</p>
        </div>
        <button class="btn-primary-sm" onclick="openModal('user-modal'); setAddMode()">
            <i class='bx bx-plus'></i> Tambah User Baru
        </button>
    </section>

    {{-- ═══ STATS ═══ --}}
    <section class="stats-grid">
        <article class="stat-card">
            <div class="stat-info">
                <span>Total User</span>
                <strong>{{ $stats['total'] }}</strong>
            </div>
            <div class="stat-icon stat-icon--primary"><i class='bx bx-user'></i></div>
        </article>
        <article class="stat-card">
            <div class="stat-info">
                <span>Admin</span>
                <strong>{{ $stats['admin'] }}</strong>
            </div>
            <div class="stat-icon stat-icon--danger"><i class='bx bx-shield'></i></div>
        </article>
        <article class="stat-card">
            <div class="stat-info">
                <span>Vendor</span>
                <strong>{{ $stats['vendor'] }}</strong>
            </div>
            <div class="stat-icon stat-icon--info"><i class='bx bx-wrench'></i></div>
        </article>
        <article class="stat-card">
            <div class="stat-info">
                <span>Client</span>
                <strong>{{ $stats['client'] }}</strong>
            </div>
            <div class="stat-icon stat-icon--success"><i class='bx bx-group'></i></div>
        </article>
    </section>

    {{-- ═══ TABLE CARD ═══ --}}
    <div class="table-card">
        {{-- Head / Filter --}}
        <div class="table-card__head">
            <h5>Daftar Pengguna</h5>
            <form method="GET" action="{{ route('admin.users.index') }}" id="filter-form" class="table-card__filters">
                <div class="search-wrap">
                    <i class='bx bx-search'></i>
                    <input
                        type="text" name="search" id="search-input"
                        value="{{ request('search') }}"
                        placeholder="Cari user..."
                        autocomplete="off"
                    >
                </div>
                <select name="role" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Role</option>
                    <option value="admin"  {{ request('role') === 'admin'  ? 'selected' : '' }}>Admin</option>
                    <option value="vendor" {{ request('role') === 'vendor' ? 'selected' : '' }}>Vendor</option>
                    <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>Client</option>
                </select>
                @if(request()->hasAny(['search','role']))
                    <a href="{{ route('admin.users.index') }}" style="font-size:0.875rem; font-weight:700; color:#e11d48; text-decoration:none; white-space:nowrap;">Reset</a>
                @endif
            </form>
        </div>

        {{-- Body --}}
        @if($users->isEmpty())
            <div class="state-box">
                <i class='bx bx-user-x'></i>
                Tidak ada pengguna yang ditemukan.
            </div>
        @else
            <div class="table-shell">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Kontak</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Join</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            {{-- User --}}
                            <td>
                                <div class="user-cell">
                                    <div class="u-avatar av-{{ $user->role }}">
                                        @if($user->avatar)
                                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}">
                                        @else
                                            {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strstr($user->name, ' ') ?: ' ', 1, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <span class="u-name">{{ $user->name }}</span>
                                        <span class="u-id">ID: #{{ $user->id }}</span>
                                    </div>
                                </div>
                            </td>
                            {{-- Kontak --}}
                            <td>
                                <div class="contact-cell">
                                    <span><i class='bx bx-envelope'></i> {{ $user->email }}</span>
                                    @if($user->phone)
                                    <span><i class='bx bx-phone'></i> {{ $user->phone }}</span>
                                    @endif
                                </div>
                            </td>
                            {{-- Role --}}
                            <td>
                                <span class="badge badge-{{ $user->role }}">
                                    @if($user->role === 'admin') <i class='bx bx-shield'></i> Admin
                                    @elseif($user->role === 'vendor') <i class='bx bx-wrench'></i> Vendor
                                    @else <i class='bx bx-user'></i> Client
                                    @endif
                                </span>
                            </td>
                            {{-- Status --}}
                            <td>
                                @if($user->is_active)
                                    <span class="badge badge-active"><i class='bx bx-check-circle'></i> Aktif</span>
                                @else
                                    <span class="badge badge-inactive"><i class='bx bx-x-circle'></i> Tidak Aktif</span>
                                @endif
                            </td>
                            {{-- Join --}}
                            <td style="font-size:0.825rem; color:var(--text-muted);">
                                {{ $user->created_at?->format('d M Y') ?? '-' }}
                            </td>
                            {{-- Aksi --}}
                            <td class="td-actions">
                                <div class="dropdown-wrap">
                                    <button class="btn-dots" type="button" onclick="toggleDropdown({{ $user->id }})">
                                        <i class='bx bx-dots-vertical-rounded'></i>
                                    </button>
                                    <div class="dropdown-menu" id="dropdown-{{ $user->id }}">
                                        <button class="dropdown-item" type="button"
                                            onclick="openEditModal(
                                                {{ $user->id }},
                                                '{{ addslashes($user->name) }}',
                                                '{{ $user->email }}',
                                                '{{ $user->phone ?? '' }}',
                                                '{{ $user->role }}'
                                            )">
                                            <i class='bx bx-edit'></i> Edit User
                                        </button>
                                        <button class="dropdown-item" type="button"
                                            onclick="confirmToggleStatus({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->is_active ? 'true' : 'false' }})">
                                            <i class='bx bx-{{ $user->is_active ? 'x-circle' : 'check-circle' }}'></i>
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                        <div class="dropdown-divider"></div>
                                        <button class="dropdown-item danger" type="button"
                                            onclick="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                            <i class='bx bx-trash'></i> Hapus User
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($users->lastPage() > 1)
            <div class="pagination-wrap">
                <span class="pagination-info">
                    Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna
                </span>
                <div style="display:flex; gap:0.35rem; align-items:center;">
                    <button class="page-btn" {{ $users->onFirstPage() ? 'disabled' : '' }} onclick="goPage({{ $users->currentPage() - 1 }})">
                        <i class='bx bx-chevron-left'></i>
                    </button>
                    <div class="page-numbers">
                        @foreach($users->getUrlRange(max(1,$users->currentPage()-2), min($users->lastPage(),$users->currentPage()+2)) as $page => $url)
                            <button class="{{ $page === $users->currentPage() ? 'active' : '' }}" onclick="goPage({{ $page }})">{{ $page }}</button>
                        @endforeach
                    </div>
                    <button class="page-btn" {{ $users->currentPage() === $users->lastPage() ? 'disabled' : '' }} onclick="goPage({{ $users->currentPage() + 1 }})">
                        <i class='bx bx-chevron-right'></i>
                    </button>
                </div>
            </div>
            @endif
        @endif
    </div>

</div>

{{-- ═══ ADD / EDIT USER MODAL ═══ --}}
<div id="user-modal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-head">
            <h5 id="modal-title">Tambahkan User Baru</h5>
            <button class="modal-close" type="button" onclick="closeModal('user-modal')">&times;</button>
        </div>

        <form id="user-form" method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <span id="method-field"></span>{{-- PATCH override diisi via JS --}}

            <div class="form-row">
                <div class="form-field full">
                    <label>Nama Lengkap <span style="color:#e11d48;">*</span></label>
                    <input type="text" name="name" id="f-name" required placeholder="Masukkan nama lengkap">
                </div>
                <div class="form-field">
                    <label>Email <span style="color:#e11d48;">*</span></label>
                    <input type="email" name="email" id="f-email" required placeholder="email@domain.com">
                </div>
                <div class="form-field">
                    <label>No. Telepon</label>
                    <input type="text" name="phone" id="f-phone" placeholder="08xx-xxxx-xxxx">
                </div>
                <div class="form-field" id="password-field">
                    <label>Password <span style="color:#e11d48;">*</span></label>
                    <input type="password" name="password" id="f-password" minlength="8" placeholder="Min. 8 karakter">
                    <span class="hint" id="password-hint"></span>
                </div>
                <div class="form-field">
                    <label>Role <span style="color:#e11d48;">*</span></label>
                    <select name="role" id="f-role" required>
                        <option value="client">Client</option>
                        <option value="vendor">Vendor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn-secondary-sm" onclick="closeModal('user-modal')">Batal</button>
                <button type="submit" class="btn-primary-sm" id="save-btn">
                    <i class='bx bx-save'></i> Simpan User
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ HIDDEN FORMS ═══ --}}
<form id="delete-form"        method="POST" style="display:none;"><input type="hidden" name="_method" value="DELETE">@csrf</form>
<form id="toggle-status-form" method="POST" style="display:none;">@csrf</form>
@endsection

@push('scripts')
<script>
// ── Pagination ──
function goPage(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    window.location = url.toString();
}

// ── Search debounce ──
let st;
document.getElementById('search-input').addEventListener('input', function() {
    clearTimeout(st);
    st = setTimeout(() => document.getElementById('filter-form').submit(), 500);
});

// ── Dropdown toggle ──
let activeDropdown = null;
function toggleDropdown(id) {
    const el = document.getElementById('dropdown-' + id);
    if (activeDropdown && activeDropdown !== el) activeDropdown.classList.remove('open');
    el.classList.toggle('open');
    activeDropdown = el.classList.contains('open') ? el : null;
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown-wrap')) {
        document.querySelectorAll('.dropdown-menu.open').forEach(d => d.classList.remove('open'));
        activeDropdown = null;
    }
});

// ── Modal helpers ──
function openModal(id)  { document.getElementById(id).classList.add('open');    document.body.style.overflow = 'hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }
document.querySelectorAll('.modal-backdrop').forEach(el => {
    el.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); });
});

// ── Add mode ──
function setAddMode() {
    document.getElementById('modal-title').textContent = 'Tambahkan User Baru';
    document.getElementById('user-form').action = '{{ route("admin.users.store") }}';
    document.getElementById('method-field').innerHTML = '';
    document.getElementById('f-name').value  = '';
    document.getElementById('f-email').value = '';
    document.getElementById('f-phone').value = '';
    document.getElementById('f-role').value  = 'client';
    document.getElementById('f-password').value = '';
    document.getElementById('f-password').required = true;
    document.getElementById('password-hint').textContent = '';
    document.getElementById('password-field').style.display = '';
}

// ── Edit mode ──
function openEditModal(id, name, email, phone, role) {
    document.getElementById('modal-title').textContent = 'Edit User';
    document.getElementById('user-form').action = `/admin/users/${id}`;
    document.getElementById('method-field').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('f-name').value  = name;
    document.getElementById('f-email').value = email;
    document.getElementById('f-phone').value = phone;
    document.getElementById('f-role').value  = role;
    document.getElementById('f-password').value = '';
    document.getElementById('f-password').required = false;
    document.getElementById('password-hint').textContent = 'Kosongkan jika tidak ingin mengubah password';
    openModal('user-modal');
    // close dropdown
    document.querySelectorAll('.dropdown-menu.open').forEach(d => d.classList.remove('open'));
}

// ── Toggle Status ──
function confirmToggleStatus(id, name, isActive) {
    document.querySelectorAll('.dropdown-menu.open').forEach(d => d.classList.remove('open'));
    const action = isActive ? 'Menonaktifkan' : 'Mengaktifkan';
    const color  = isActive ? '#ef4444' : '#16a34a';
    Swal.fire({
        title: `${action} User?`,
        html: `Apakah Anda yakin ingin ${action.toLowerCase()} <strong>${name}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: color,
        cancelButtonColor: '#94a3b8',
        confirmButtonText: `Ya, ${action}`,
        cancelButtonText: 'Batal'
    }).then(result => {
        if (!result.isConfirmed) return;
        const form = document.getElementById('toggle-status-form');
        form.action = `/admin/users/${id}/toggle-status`;
        form.submit();
    });
}

// ── Delete ──
function confirmDelete(id, name) {
    document.querySelectorAll('.dropdown-menu.open').forEach(d => d.classList.remove('open'));
    Swal.fire({
        title: 'Hapus User?',
        html: `Apakah Anda yakin ingin menghapus <strong>${name}</strong>?<br><small style="color:#94a3b8;">Tindakan ini tidak dapat dibatalkan.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (!result.isConfirmed) return;
        const form = document.getElementById('delete-form');
        form.action = `/admin/users/${id}`;
        form.submit();
    });
}
</script>
@endpush