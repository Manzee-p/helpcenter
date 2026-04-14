@extends('layouts.client')

@section('title', 'Laporan Saya')
@section('page_title', 'Laporan Saya')
@section('breadcrumb', 'Home / Laporan Saya')



@section('content')
<div class="tickets-page">

    {{-- PAGE HEADER --}}
    <div class="page-header-card">
        <div>
            <h1 class="page-header-title">Laporan Saya</h1>
            <p class="page-header-sub">Pantau semua laporan aktif, vendor yang menangani, dan pelayanan yang belum Anda nilai.</p>
        </div>
        <a href="{{ route('client.tickets.create') }}" class="btn-create">
            <i class='bx bx-plus-circle'></i> Buat Laporan
        </a>
    </div>

    {{-- PENDING FEEDBACK PANEL --}}
    @if($pendingFeedbackTickets->count())
    <div class="pending-panel">
        <div class="pending-panel-head">
            <div>
                <span class="panel-chip">Pelayanan Belum Dinilai</span>
                <h2>{{ $pendingFeedbackTickets->count() }} laporan selesai menunggu rating</h2>
            </div>
            <a href="{{ route('client.history') }}" class="link-inline">Buka riwayat &rarr;</a>
        </div>
        <div class="pending-grid">
            @foreach($pendingFeedbackTickets->take(3) as $t)
            <a href="{{ route('client.history') }}" class="pending-item">
                <div>
                    <strong>#{{ $t->ticket_number }}</strong>
                    <p>{{ $t->title }}</p>
                </div>
                <span class="badge-rating">Beri Rating</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- FILTERS --}}
    <div class="filters-card">
        <form method="GET" action="{{ route('client.tickets.index') }}" id="filterForm">
            <div class="filters-row">
                <div>
                    <label class="filter-label"><i class='bx bx-filter'></i> Status</label>
                    <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Semua Status</option>
                        <option value="new"              {{ request('status') === 'new'              ? 'selected' : '' }}>Baru</option>
                        <option value="in_progress"      {{ request('status') === 'in_progress'      ? 'selected' : '' }}>Dalam Proses</option>
                        <option value="waiting_response" {{ request('status') === 'waiting_response' ? 'selected' : '' }}>Menunggu Respons</option>
                        <option value="resolved"         {{ request('status') === 'resolved'         ? 'selected' : '' }}>Resolved</option>
                        <option value="closed"           {{ request('status') === 'closed'           ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div>
                    <label class="filter-label"><i class='bx bx-search'></i> Search</label>
                    <input type="text" name="search" class="filter-search"
                        placeholder="Cari nomor tiket, judul, atau vendor..."
                        value="{{ request('search') }}"
                        onkeyup="debounceSubmit()"
                    />
                </div>
            </div>
        </form>
    </div>

    {{-- TICKETS --}}
    @if($tickets->isEmpty())
        <div class="state-card">
            <div class="state-icon"><i class='bx bx-search-alt'></i></div>
            <h3 class="state-title">Tidak ada laporan ditemukan</h3>
            <p class="state-text">
                @if(request('status') || request('search'))
                    Coba ubah filter pencarian Anda.
                @else
                    Buat laporan pertama untuk mulai meminta bantuan.
                @endif
            </p>
            @unless(request('status') || request('search'))
                <a href="{{ route('client.tickets.create') }}" class="btn-create" style="display:inline-flex;">
                    <i class='bx bx-plus-circle'></i> Buat Laporan
                </a>
            @endunless
        </div>
    @else
        <div class="tickets-grid">
            @foreach($tickets as $ticket)
            @php
                $statusLabels = [
                    'new'              => 'Baru',
                    'in_progress'      => 'Diproses',
                    'waiting_response' => 'Menunggu',
                    'resolved'         => 'Selesai',
                    'closed'           => 'Ditutup',
                ];
                $priorityLabels = [
                    'low'    => 'Rendah',
                    'medium' => 'Sedang',
                    'high'   => 'Tinggi',
                    'urgent' => 'Mendesak',
                ];
            @endphp
            <a href="{{ route('client.tickets.show', $ticket->id) }}" class="ticket-card">
                <div class="ticket-card-head">
                    <span class="ticket-num">#{{ $ticket->ticket_number }}</span>
                    <span class="status-badge status-{{ $ticket->status }}">
                        {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                    </span>
                </div>

                <h3 class="ticket-card-title">{{ $ticket->title }}</h3>

                <div class="ticket-meta-row">
                    <span class="meta-chip">
                        <i class='bx bx-category-alt'></i>
                        {{ $ticket->category->name ?? 'N/A' }}
                    </span>
                    @if($ticket->priority)
                    <span class="meta-chip priority-{{ $ticket->priority }}">
                        <i class='bx bx-flag'></i>
                        {{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}
                    </span>
                    @endif
                </div>

                <div class="ticket-foot">
                    <div class="ticket-date">
                        <i class='bx bx-time'></i>
                        {{ \Carbon\Carbon::parse($ticket->created_at)->locale('id')->diffForHumans() }}
                    </div>
                    @if($ticket->assignedVendor)
                    <div class="assignee-avatar">
                        {{ strtoupper(substr($ticket->assignedVendor->name, 0, 2)) }}
                    </div>
                    @endif
                </div>

                @if(in_array($ticket->status, ['resolved','closed']) && !$ticket->feedback)
                    <span class="rating-alert"><i class='bx bx-star'></i> Vendor belum dinilai</span>
                @endif
                @if($ticket->latestDeletionRequest && $ticket->latestDeletionRequest->status === 'pending')
                    <span class="rating-alert" style="background:#fff1f2;color:#be123c;">
                        <i class='bx bx-time-five'></i> Menunggu persetujuan hapus admin
                    </span>
                @endif
            </a>
            @endforeach
        </div>

        {{-- PAGINATION --}}
        @if($tickets->lastPage() > 1)
        <div class="pagination-wrap">
            @if($tickets->onFirstPage())
                <button class="page-btn" disabled><i class='bx bx-chevron-left'></i> Prev</button>
            @else
                <a href="{{ $tickets->appends(request()->query())->previousPageUrl() }}" class="page-btn"><i class='bx bx-chevron-left'></i> Prev</a>
            @endif

            @foreach(range(1, $tickets->lastPage()) as $page)
                @if(abs($page - $tickets->currentPage()) <= 2)
                    <a href="{{ $tickets->appends(request()->query())->url($page) }}" class="page-btn {{ $page === $tickets->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($tickets->hasMorePages())
                <a href="{{ $tickets->appends(request()->query())->nextPageUrl() }}" class="page-btn">Next <i class='bx bx-chevron-right'></i></a>
            @else
                <button class="page-btn" disabled>Next <i class='bx bx-chevron-right'></i></button>
            @endif
        </div>
        @endif
    @endif

</div>
@endsection

@push('scripts')
<script>
let searchTimeout = null;
function debounceSubmit() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { document.getElementById('filterForm').submit(); }, 500);
}
</script>
@endpush

@push('styles')
<style>
.tickets-page { display: flex; flex-direction: column; gap: 1.25rem; }

/* â-€â-€â-€â-€â-€ PAGE HEADER â-€â-€â-€â-€â-€ */
.page-header-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 1.75rem;
    background: white;
    border: 1px solid rgba(148,163,184,.12);
    border-radius: 24px;
    box-shadow: 0 18px 36px rgba(15,23,42,.05);
}
.page-header-title { font-size: clamp(1.8rem,3vw,2.6rem); font-weight: 800; color: #1f2937; margin: 0 0 .45rem; }
.page-header-sub   { color: #64748b; margin: 0; max-width: 680px; }
.btn-create {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .875rem 1.75rem;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: white;
    text-decoration: none;
    border-radius: 16px;
    font-weight: 700;
    white-space: nowrap;
    box-shadow: 0 8px 20px rgba(99,102,241,.25);
    transition: all .25s;
}
.btn-create:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(99,102,241,.35); color: white; }
.btn-create i { font-size: 1.25rem; }

/* â-€â-€â-€â-€â-€ PENDING FEEDBACK PANEL â-€â-€â-€â-€â-€ */
.pending-panel {
    background: white;
    border: 1px solid rgba(245,158,11,.2);
    border-radius: 24px;
    padding: 1.35rem;
    box-shadow: 0 18px 36px rgba(15,23,42,.05);
}
.pending-panel-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}
.panel-chip {
    display: inline-flex;
    padding: .35rem .8rem;
    border-radius: 999px;
    background: rgba(245,158,11,.14);
    color: #b7791f;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.pending-panel-head h2 { margin: .6rem 0 0; font-size: 1.15rem; color: #1f2937; }
.link-inline { color: #4f46e5; font-weight: 700; text-decoration: none; font-size: .875rem; }
.link-inline:hover { text-decoration: underline; }

.pending-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 1rem;
}
.pending-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
    padding: 1rem 1.1rem;
    border-radius: 18px;
    border: 1px solid rgba(245,158,11,.18);
    background: linear-gradient(135deg,#fffaf0,#fff);
    text-decoration: none;
    transition: all .2s;
}
.pending-item:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(245,158,11,.12); }
.pending-item strong { display: block; color: #b7791f; font-weight: 800; }
.pending-item p { margin: .25rem 0 0; color: #334155; font-weight: 600; font-size: .9rem; }
.badge-rating {
    padding: .38rem .7rem;
    border-radius: 999px;
    background: #fff1d6;
    color: #b7791f;
    font-size: .74rem;
    font-weight: 700;
    white-space: nowrap;
}

/* â-€â-€â-€â-€â-€ FILTERS â-€â-€â-€â-€â-€ */
.filters-card {
    background: white;
    border: 1px solid rgba(148,163,184,.12);
    border-radius: 24px;
    padding: 1.5rem;
    box-shadow: 0 18px 36px rgba(15,23,42,.05);
}
.filters-row {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 1.25rem;
    align-items: end;
}
.filter-label {
    display: flex;
    align-items: center;
    gap: .4rem;
    font-size: .875rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: .4rem;
}
.filter-label i { color: #6366f1; }
.filter-select,
.filter-search {
    width: 100%;
    padding: .75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: .9375rem;
    color: #495057;
    background: white;
    transition: all .2s;
}
.filter-select:focus,
.filter-search:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }

/* â-€â-€â-€â-€â-€ TICKETS GRID â-€â-€â-€â-€â-€ */
.tickets-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 1.25rem;
}
.ticket-card {
    background: white;
    border: 1px solid rgba(148,163,184,.12);
    border-radius: 24px;
    padding: 1.5rem;
    box-shadow: 0 18px 36px rgba(15,23,42,.05);
    cursor: pointer;
    transition: all .25s;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    gap: .75rem;
}
.ticket-card:hover { transform: translateY(-4px); box-shadow: 0 24px 40px rgba(15,23,42,.08); }

.ticket-card-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.ticket-num {
    font-size: .85rem;
    font-weight: 800;
    color: #4f46e5;
    background: rgba(79,70,229,.1);
    padding: .3rem .8rem;
    border-radius: 999px;
}
.ticket-card-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #1f2937;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
}
.ticket-meta-row {
    display: flex;
    gap: .6rem;
    flex-wrap: wrap;
    padding-bottom: .75rem;
    border-bottom: 1px solid #f0f0f0;
}
.meta-chip {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    font-size: .78rem;
    color: #64748b;
    font-weight: 600;
    padding: .38rem .7rem;
    border-radius: 999px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}
.meta-chip i { font-size: .9rem; }
.priority-low    { color: #15803d; }
.priority-medium { color: #a16207; }
.priority-high   { color: #c2410c; }
.priority-urgent { color: #b91c1c; }

.ticket-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.ticket-date { display: flex; align-items: center; gap: .35rem; font-size: .8rem; color: #6c757d; }
.assignee-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg,#6366f1,#7c3aed);
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: .72rem; font-weight: 800;
}
.rating-alert {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    margin-top: .25rem;
    padding: .4rem .75rem;
    border-radius: 999px;
    background: #fff1d6;
    color: #b7791f;
    font-size: .75rem;
    font-weight: 700;
}

/* Status badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: .3rem .7rem;
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 800;
}
.status-new              { background: rgba(249,115,22,.12); color: #c2410c; }
.status-in_progress      { background: rgba(59,130,246,.12); color: #1d4ed8; }
.status-waiting_response { background: rgba(168,85,247,.12); color: #7e22ce; }
.status-resolved         { background: rgba(34,197,94,.12);  color: #15803d; }
.status-closed           { background: rgba(148,163,184,.14);color: #475569; }

/* â-€â-€â-€â-€â-€ EMPTY / LOADING â-€â-€â-€â-€â-€ */
.state-card {
    background: white;
    border: 1px solid rgba(148,163,184,.12);
    border-radius: 24px;
    padding: 4rem 2rem;
    text-align: center;
    box-shadow: 0 18px 36px rgba(15,23,42,.05);
}
.state-icon { font-size: 5rem; color: #dee2e6; margin-bottom: 1.25rem; }
.state-title { font-size: 1.45rem; font-weight: 700; color: #2c3e50; margin-bottom: .75rem; }
.state-text  { font-size: .95rem; color: #6c757d; margin-bottom: 1.5rem; }

/* â-€â-€â-€â-€â-€ PAGINATION â-€â-€â-€â-€â-€ */
.pagination-wrap {
    display: flex;
    justify-content: center;
    gap: .5rem;
    margin-top: .5rem;
    flex-wrap: wrap;
}
.page-btn {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .6rem 1rem;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    color: #2c3e50;
    font-weight: 600;
    font-size: .875rem;
    cursor: pointer;
    transition: all .2s;
    text-decoration: none;
}
.page-btn:hover { border-color: #6366f1; color: #6366f1; }
.page-btn.active { background: linear-gradient(135deg,#6366f1,#7c3aed); color: white; border-color: transparent; }
.page-btn:disabled { opacity: .5; cursor: not-allowed; }

@media (max-width: 1199px) {
    .tickets-grid, .pending-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
}
@media (max-width: 767px) {
    .page-header-card { flex-direction: column; align-items: flex-start; }
    .btn-create { width: 100%; justify-content: center; }
    .filters-row { grid-template-columns: 1fr; }
    .tickets-grid, .pending-grid { grid-template-columns: 1fr; }
    .pending-panel-head { flex-direction: column; }
}
</style>
@endpush




