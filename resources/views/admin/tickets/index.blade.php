@extends('layouts.app')

@section('title', 'Semua Tiket')
@section('page_title', 'Manajemen Tiket')
@section('breadcrumb', 'Home / Tiket / Semua Tiket')

@push('styles')
<style>
/* ───── PAGE WRAP ───── */
.tickets-wrap { display: flex; flex-direction: column; gap: 1.5rem; }

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
    display: inline-flex; align-items: center;
    padding: 0.35rem 0.8rem; border-radius: 999px;
    background: rgba(79,70,229,0.12); color: var(--primary);
    font-weight: 800; font-size: 0.75rem;
    letter-spacing: 0.06em; text-transform: uppercase;
}
.hero-copy h3 {
    margin: 0.75rem 0 0.4rem;
    font-size: clamp(1.4rem, 3vw, 2rem);
    font-weight: 800; color: var(--text);
}
.hero-copy > p { color: var(--text-muted); font-size: 0.9375rem; max-width: 600px; }

/* ───── STATS ───── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0,1fr));
    gap: 1rem;
}
.stat-card {
    background: white; border: 1px solid var(--border);
    border-radius: 22px; padding: 1.25rem;
    display: grid; gap: 0.35rem;
    box-shadow: var(--shadow-sm);
    transition: all 0.25s;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
.stat-card > span { color: var(--text-muted); font-weight: 700; font-size: 0.85rem; }
.stat-card > strong { font-size: 2rem; font-weight: 800; color: var(--text); line-height: 1; }
.stat-card > small { color: var(--text-light); font-size: 0.8rem; }
.stat-card--new      { background: linear-gradient(180deg,#fff7ed,#fff); border-color: rgba(249,115,22,0.2); }
.stat-card--progress { background: linear-gradient(180deg,#eff6ff,#fff); border-color: rgba(59,130,246,0.2); }
.stat-card--assigned { background: linear-gradient(180deg,#f0fdf4,#fff); border-color: rgba(34,197,94,0.2); }

/* ───── FILTER CARD ───── */
.filter-card {
    background: white; border: 1px solid var(--border);
    border-radius: 26px; padding: 1.375rem;
    box-shadow: var(--shadow-sm);
}
.filter-search {
    position: relative; margin-bottom: 1rem;
}
.filter-search i {
    position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
    color: var(--text-light); font-size: 1.1rem; pointer-events: none;
}
.filter-search input {
    width: 100%; padding: 0.875rem 1rem 0.875rem 2.75rem;
    border: 1.5px solid var(--border); border-radius: 16px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.9rem; color: var(--text); background: var(--bg);
    transition: all 0.2s; outline: none;
}
.filter-search input:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
.filter-search input::placeholder { color: var(--text-light); }

.filter-row { display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap; }
.filter-field { display: grid; gap: 0.45rem; min-width: 180px; flex: 1; }
.filter-field label {
    font-size: 0.75rem; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: 0.05em;
}
.filter-field select {
    border: 1.5px solid var(--border); border-radius: 14px;
    padding: 0.8rem 1rem; background: var(--bg);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.875rem; color: var(--text);
    outline: none; transition: all 0.2s; cursor: pointer;
}
.filter-field select:focus { border-color: var(--primary); background: white; }

.view-switch {
    display: inline-flex; background: var(--bg);
    border-radius: 14px; padding: 0.25rem;
    border: 1px solid var(--border);
    gap: 2px;
}
.view-switch button {
    width: 2.8rem; height: 2.8rem;
    background: transparent; border: none;
    border-radius: 11px; color: var(--text-muted);
    cursor: pointer; font-size: 1.125rem;
    transition: all 0.2s;
}
.view-switch button.active {
    background: white; color: var(--text);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.btn-reset {
    background: #fff1f2; color: #e11d48;
    border: none; padding: 0.8rem 1.1rem;
    border-radius: 14px; font-weight: 700;
    font-size: 0.875rem; cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif;
    transition: all 0.2s; white-space: nowrap;
}
.btn-reset:hover { background: #ffe4e6; }

/* ───── CONTENT CARD ───── */
.content-card {
    background: white; border: 1px solid var(--border);
    border-radius: 26px; padding: 1.375rem;
    box-shadow: var(--shadow-sm);
}
.content-head {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 1.25rem; gap: 1rem;
}
.content-head h5 { margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--text); }
.content-head p { margin: 0.25rem 0 0; color: var(--text-muted); font-size: 0.85rem; }

/* ───── TABLE ───── */
.table-shell {
    overflow: auto;
    border: 1px solid var(--border);
    border-radius: 18px;
}
.tickets-table { width: 100%; border-collapse: collapse; }
.tickets-table th {
    padding: 0.875rem 1rem;
    font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
    color: var(--text-muted); background: var(--bg);
    letter-spacing: 0.05em; text-align: left; white-space: nowrap;
    border-bottom: 1px solid var(--border);
}
.tickets-table th.text-center { text-align: center; }
.tickets-table td {
    padding: 0.9rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.875rem; color: var(--text-muted);
    vertical-align: middle;
}
.tickets-table tbody tr:last-child td { border-bottom: none; }
.tickets-table tbody tr {
    cursor: pointer; transition: background 0.15s;
}
.tickets-table tbody tr:hover { background: #f8fafc; }

.ticket-main { display: grid; gap: 0.2rem; }
.ticket-main strong { color: var(--primary); font-size: 0.8rem; font-weight: 700; }
.ticket-main span { color: var(--text); font-weight: 600; font-size: 0.875rem; }

/* ───── BADGES ───── */
.badge {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 0.3rem 0.7rem; border-radius: 999px;
    font-size: 0.72rem; font-weight: 800; white-space: nowrap;
}
.badge-new, .badge-waiting_response { background: rgba(249,115,22,0.12); color: #c2410c; }
.badge-in_progress  { background: rgba(59,130,246,0.12);  color: #1d4ed8; }
.badge-resolved     { background: rgba(34,197,94,0.12);   color: #15803d; }
.badge-closed       { background: rgba(100,116,139,0.12); color: #475569; }
.badge-urgent       { background: rgba(239,68,68,0.12);   color: #b91c1c; }
.badge-high         { background: rgba(249,115,22,0.12);  color: #c2410c; }
.badge-medium       { background: rgba(250,204,21,0.16);  color: #a16207; }
.badge-low          { background: rgba(34,197,94,0.12);   color: #15803d; }
.badge-none         { background: rgba(148,163,184,0.14); color: #475569; }

/* ───── ACTIONS ───── */
.td-actions { text-align: center; white-space: nowrap; }
.icon-btn {
    width: 2.25rem; height: 2.25rem;
    border: none; border-radius: 10px;
    background: var(--bg); color: var(--text-muted);
    cursor: pointer; font-size: 1rem;
    display: inline-flex; align-items: center; justify-content: center;
    margin: 0 0.1rem; transition: all 0.2s;
}
.icon-btn:hover { background: #eef2ff; color: var(--primary); }
.icon-btn--danger { background: #fff1f2; color: #e11d48; }
.icon-btn--danger:hover { background: #ffe4e6; }

/* ───── GRID VIEW ───── */
.ticket-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 1rem;
}
.ticket-card {
    background: white; border: 1px solid var(--border);
    border-radius: 20px; padding: 1.125rem;
    display: flex; flex-direction: column; gap: 0.75rem;
    cursor: pointer; transition: all 0.25s;
    box-shadow: var(--shadow-sm);
}
.ticket-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); border-color: rgba(79,70,229,0.2); }
.ticket-card__top { display: flex; justify-content: space-between; gap: 0.75rem; align-items: flex-start; }
.ticket-card__top strong { color: var(--primary); font-size: 0.8rem; font-weight: 700; }
.ticket-card h3 { margin: 0; font-size: 0.9375rem; font-weight: 700; color: var(--text); line-height: 1.4; }
.ticket-card p { margin: 0; color: var(--text-muted); font-size: 0.8rem; line-height: 1.5; }
.ticket-card__meta { display: grid; gap: 0.3rem; }
.ticket-card__meta span { font-size: 0.78rem; color: var(--text-light); }
.ticket-card__footer { display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; }
.card-actions { display: flex; gap: 0.35rem; }

/* ───── EMPTY / LOADING ───── */
.state-box {
    border: 1px dashed rgba(148,163,184,0.45);
    border-radius: 18px; padding: 2.5rem;
    text-align: center; color: var(--text-muted); font-size: 0.9rem;
}

/* ───── PAGINATION ───── */
.pagination-wrap {
    display: flex; justify-content: space-between; align-items: center;
    margin-top: 1.25rem; gap: 1rem; flex-wrap: wrap;
}
.pagination-wrap button {
    border: 1px solid var(--border); background: white;
    border-radius: 10px; padding: 0.6rem 1rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.875rem; font-weight: 600; color: var(--text-muted);
    cursor: pointer; transition: all 0.2s;
}
.pagination-wrap button:hover:not(:disabled) { background: var(--bg); color: var(--primary); border-color: rgba(79,70,229,0.3); }
.pagination-wrap button:disabled { opacity: 0.45; cursor: not-allowed; }
.page-numbers { display: flex; gap: 0.35rem; }
.page-numbers button.active { background: var(--primary); color: white; border-color: var(--primary); }

/* ───── RESPONSIVE ───── */
@media (max-width: 1199px) {
    .stats-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
    .ticket-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
}
@media (max-width: 767px) {
    .hero-card { flex-direction: column; align-items: flex-start; }
    .stats-grid, .ticket-grid { grid-template-columns: 1fr; }
    .filter-row { flex-direction: column; align-items: stretch; }
    .content-head { flex-direction: column; align-items: flex-start; }
}
</style>
@endpush

@section('content')
<div class="tickets-wrap">

    {{-- ═══ HERO ═══ --}}
    <section class="hero-card">
        <div class="hero-copy">
            <span class="hero-kicker">Manajemen Tiket</span>
            <h3>Semua tiket dalam satu layar</h3>
            <p>Admin fokus pada pemantauan, penugasan, dan penghapusan tiket tanpa alur membuat tiket baru untuk client.</p>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
            <a href="{{ route('admin.ticket-deletion-requests.index') }}" class="btn-outline-sm">
                <i class='bx bx-trash-alt'></i> Permintaan Hapus
            </a>
            <a href="{{ route('admin.tickets.index') }}" class="btn-primary-sm">
                <i class='bx bx-refresh'></i> Muat Ulang
            </a>
        </div>
    </section>

    {{-- ═══ STATS ═══ --}}
    <section class="stats-grid">
        <article class="stat-card">
            <span>Total tiket</span>
            <strong>{{ $tickets->total() }}</strong>
            <small>Keseluruhan tiket yang tercatat</small>
        </article>
        <article class="stat-card stat-card--new">
            <span>Tiket baru</span>
            <strong>{{ $stats['new_count'] ?? 0 }}</strong>
            <small>Perlu ditinjau admin</small>
        </article>
        <article class="stat-card stat-card--progress">
            <span>Dalam proses</span>
            <strong>{{ $stats['in_progress_count'] ?? 0 }}</strong>
            <small>Masih dikerjakan vendor</small>
        </article>
        <article class="stat-card stat-card--assigned">
            <span>Sudah ditugaskan</span>
            <strong>{{ $stats['assigned_count'] ?? 0 }}</strong>
            <small>Sudah punya vendor penanggung jawab</small>
        </article>
    </section>

    {{-- ═══ FILTER ═══ --}}
    <section class="filter-card">
        <form method="GET" action="{{ route('admin.tickets.index') }}" id="filter-form">
            <div class="filter-search">
                <i class='bx bx-search'></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nomor tiket, judul, atau nama client..."
                    id="search-input"
                    autocomplete="off"
                >
            </div>
            <div class="filter-row">
                <div class="filter-field">
                    <label>Status</label>
                    <select name="status" onchange="this.form.submit()">
                        <option value="">Semua status</option>
                        <option value="new"              {{ request('status') === 'new'              ? 'selected' : '' }}>Baru</option>
                        <option value="in_progress"      {{ request('status') === 'in_progress'      ? 'selected' : '' }}>Dalam proses</option>
                        <option value="waiting_response" {{ request('status') === 'waiting_response' ? 'selected' : '' }}>Menunggu respon</option>
                        <option value="resolved"         {{ request('status') === 'resolved'         ? 'selected' : '' }}>Terselesaikan</option>
                        <option value="closed"           {{ request('status') === 'closed'           ? 'selected' : '' }}>Ditutup</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label>Prioritas</label>
                    <select name="priority" onchange="this.form.submit()">
                        <option value="">Semua prioritas</option>
                        <option value="low"    {{ request('priority') === 'low'    ? 'selected' : '' }}>Rendah</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Sedang</option>
                        <option value="high"   {{ request('priority') === 'high'   ? 'selected' : '' }}>Tinggi</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Mendesak</option>
                    </select>
                </div>
                <input type="hidden" name="view" value="{{ request('view', 'table') }}" id="view-input">
                <div class="view-switch">
                    <button type="button" class="{{ request('view', 'table') === 'table' ? 'active' : '' }}" onclick="switchView('table')" title="Tampilan tabel">
                        <i class='bx bx-list-ul'></i>
                    </button>
                    <button type="button" class="{{ request('view') === 'grid' ? 'active' : '' }}" onclick="switchView('grid')" title="Tampilan grid">
                        <i class='bx bx-grid-alt'></i>
                    </button>
                </div>
                @if(request()->hasAny(['status','priority','search']))
                    <a href="{{ route('admin.tickets.index') }}" class="btn-reset">Reset filter</a>
                @endif
            </div>
        </form>
    </section>

    {{-- ═══ CONTENT ═══ --}}
    <section class="content-card">
        <div class="content-head">
            <div>
                <h5>Daftar tiket</h5>
                <p>{{ $tickets->count() }} tiket tampil pada halaman ini</p>
            </div>
        </div>

        @if($tickets->isEmpty())
            <div class="state-box">Belum ada tiket yang cocok dengan filter saat ini.</div>
        @elseif(request('view') === 'grid')
            {{-- ── GRID VIEW ── --}}
            <div class="ticket-grid">
                @foreach($tickets as $ticket)
                <article class="ticket-card" onclick="window.location='{{ route('admin.tickets.show', $ticket->id) }}'">
                    <div class="ticket-card__top">
                        <strong>{{ $ticket->ticket_number }}</strong>
                        <span class="badge badge-{{ $ticket->status }}">{{ formatTicketStatus($ticket->status) }}</span>
                    </div>
                    <h3>{{ $ticket->title }}</h3>
                    <p>{{ Str::limit($ticket->description, 110) }}</p>
                    <div class="ticket-card__meta">
                        <span><i class='bx bx-user'></i> {{ $ticket->user->name ?? 'Client tidak diketahui' }}</span>
                        <span><i class='bx bx-wrench'></i> {{ $ticket->assignedTo->name ?? 'Belum ditugaskan' }}</span>
                        <span><i class='bx bx-calendar'></i> {{ $ticket->created_at?->format('d M Y') ?? '-' }}</span>
                    </div>
                    <div class="ticket-card__footer">
                        <span class="badge badge-{{ $ticket->priority ?? 'none' }}">{{ formatTicketPriority($ticket->priority) }}</span>
                        <div class="card-actions" onclick="event.stopPropagation()">
                            <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="icon-btn" title="Lihat detail"><i class='bx bx-show'></i></a>
                            <button class="icon-btn" title="Tugaskan vendor" onclick="openAssignModal({{ $ticket->id }}, '{{ $ticket->ticket_number }}')"><i class='bx bx-user-plus'></i></button>
                            <button class="icon-btn icon-btn--danger" title="Hapus tiket" onclick="confirmDelete({{ $ticket->id }}, '{{ $ticket->ticket_number }}')"><i class='bx bx-trash'></i></button>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
        @else
            {{-- ── TABLE VIEW ── --}}
            <div class="table-shell">
                <table class="tickets-table">
                    <thead>
                        <tr>
                            <th>Tiket</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>Prioritas</th>
                            <th>Vendor</th>
                            <th>Dibuat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                        <tr onclick="window.location='{{ route('admin.tickets.show', $ticket->id) }}'">
                            <td>
                                <div class="ticket-main">
                                    <strong>{{ $ticket->ticket_number }}</strong>
                                    <span>{{ Str::limit($ticket->title, 55) }}</span>
                                </div>
                            </td>
                            <td>{{ $ticket->user->name ?? '-' }}</td>
                            <td><span class="badge badge-{{ $ticket->status }}">{{ $ticket->status_label }}</span></td>
                            <td><span class="badge badge-{{ $ticket->priority ?? 'none' }}">{{ $ticket->priority_label }}</span></td>
                            <td>{{ $ticket->assignedTo->name ?? 'Belum ditugaskan' }}</td>
                            <td>{{ $ticket->created_at?->format('d M Y') ?? '-' }}</td>
                            <td class="td-actions" onclick="event.stopPropagation()">
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="icon-btn" title="Lihat detail"><i class='bx bx-show'></i></a>
                                <button class="icon-btn" title="Tugaskan vendor" onclick="openAssignModal({{ $ticket->id }}, '{{ $ticket->ticket_number }}')"><i class='bx bx-user-plus'></i></button>
                                <button class="icon-btn icon-btn--danger" title="Hapus tiket" onclick="confirmDelete({{ $ticket->id }}, '{{ $ticket->ticket_number }}')"><i class='bx bx-trash'></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- ── PAGINATION ── --}}
        @if($tickets->lastPage() > 1)
        <div class="pagination-wrap">
            <button {{ $tickets->onFirstPage() ? 'disabled' : '' }} onclick="goPage({{ $tickets->currentPage() - 1 }})">Sebelumnya</button>
            <div class="page-numbers">
                @foreach($tickets->getUrlRange(max(1, $tickets->currentPage()-2), min($tickets->lastPage(), $tickets->currentPage()+2)) as $page => $url)
                    <button class="{{ $page === $tickets->currentPage() ? 'active' : '' }}" onclick="goPage({{ $page }})">{{ $page }}</button>
                @endforeach
            </div>
            <button {{ $tickets->currentPage() === $tickets->lastPage() ? 'disabled' : '' }} onclick="goPage({{ $tickets->currentPage() + 1 }})">Berikutnya</button>
        </div>
        @endif
    </section>
</div>

{{-- ═══ ASSIGN MODAL ═══ --}}
<div id="assign-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.5); z-index:1050; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:24px; padding:1.75rem; width:100%; max-width:480px; margin:1rem; box-shadow:0 25px 60px rgba(0,0,0,0.2); animation: fadeIn 0.2s ease;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
            <h5 style="margin:0; font-weight:800; color:var(--text);">Tugaskan Vendor</h5>
            <button onclick="closeAssignModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--text-muted);">&times;</button>
        </div>
        <p style="color:var(--text-muted); margin-bottom:1.25rem; font-size:0.9rem;">Pilih vendor untuk tiket <strong id="assign-ticket-number"></strong></p>
        <div style="margin-bottom:1rem;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); display:block; margin-bottom:0.5rem;">Vendor</label>
            <select id="assign-vendor-select" style="width:100%; border:1.5px solid var(--border); border-radius:14px; padding:0.875rem 1rem; font-family:'Plus Jakarta Sans',sans-serif; font-size:0.9rem; outline:none; background:var(--bg);">
                <option value="">-- Pilih vendor --</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; gap:0.75rem; justify-content:flex-end;">
            <button onclick="closeAssignModal()" class="btn-reset">Batal</button>
            <button onclick="submitAssign()" class="btn-primary-sm">Tugaskan</button>
        </div>
    </div>
</div>

{{-- ═══ DELETE FORM ═══ --}}
<form id="delete-form" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
// ── View Switch ──
function switchView(mode) {
    document.getElementById('view-input').value = mode;
    document.getElementById('filter-form').submit();
}

// ── Pagination ──
function goPage(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    window.location = url.toString();
}

// ── Search Debounce ──
let searchTimer;
document.getElementById('search-input').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => document.getElementById('filter-form').submit(), 500);
});

// ── Assign Modal ──
let assignTicketId = null;
function openAssignModal(id, number) {
    assignTicketId = id;
    document.getElementById('assign-ticket-number').textContent = number;
    document.getElementById('assign-vendor-select').value = '';
    document.getElementById('assign-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeAssignModal() {
    document.getElementById('assign-modal').style.display = 'none';
    document.body.style.overflow = '';
}
function submitAssign() {
    const vendorId = document.getElementById('assign-vendor-select').value;
    if (!vendorId) {
        Swal.fire({ icon:'warning', title:'Pilih vendor dulu', toast:true, position:'top-end', showConfirmButton:false, timer:2000 });
        return;
    }
    fetch(`/api/tickets/${assignTicketId}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ assigned_to: vendorId })
    })
    .then(r => r.json())
    .then(() => {
        closeAssignModal();
        Swal.fire({ icon:'success', title:'Vendor berhasil ditugaskan', toast:true, position:'top-end', showConfirmButton:false, timer:2000 });
        setTimeout(() => location.reload(), 1500);
    })
    .catch(() => {
        Swal.fire({ icon:'error', title:'Gagal menugaskan vendor', toast:true, position:'top-end', showConfirmButton:false, timer:2500 });
    });
}

// ── Delete Confirm ──
function confirmDelete(id, number) {
    Swal.fire({
        title: 'Hapus tiket?',
        html: `Tiket <strong>${number}</strong> akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
    }).then(result => {
        if (!result.isConfirmed) return;
        const form = document.getElementById('delete-form');
        form.action = `/admin/tickets/${id}`;
        form.submit();
    });
}

// Close modal on backdrop click
document.getElementById('assign-modal').addEventListener('click', function(e) {
    if (e.target === this) closeAssignModal();
});
</script>
@endpush
