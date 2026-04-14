@extends('layouts.app')

@section('title', 'Permintaan Penghapusan Tiket')
@section('page_title', 'Permintaan Hapus Tiket')
@section('breadcrumb', 'Home / Tiket / Permintaan Hapus')

@section('content')
<div class="td-wrapper">

    {{-- Hero --}}
    <div class="td-hero">
        <div>
            <div class="td-hero-label">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                Manajemen Tiket
            </div>
            <h1>Permintaan<br>Penghapusan Tiket</h1>
            <p>Client tidak dapat menghapus tiket langsung. Setiap permintaan harus ditinjau dan disetujui oleh admin.</p>
        </div>
    </div>

    {{-- Stats --}}
    @php
        $col      = $requests->getCollection();
        $pending  = $col->where('status','pending')->count();
        $approved = $col->where('status','approved')->count();
        $rejected = $col->where('status','rejected')->count();
    @endphp
    <div class="td-stats">
        <div class="stat-card">
            <div class="stat-icon warn">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div>
                <div class="stat-label">Pending</div>
                <div class="stat-value">{{ $pending }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon ok">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <div class="stat-label">Disetujui</div>
                <div class="stat-value">{{ $approved }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bad">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <div>
                <div class="stat-label">Ditolak</div>
                <div class="stat-value">{{ $rejected }}</div>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="td-card">

        {{-- Header --}}
        <div class="td-card-head">
            <div class="td-card-head-info">
                <h2>Daftar Permintaan Penghapusan</h2>
                <p>Tinjau setiap permintaan sebelum mengambil keputusan.</p>
            </div>
            <form method="GET" class="filter-wrap">
                <select name="status" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </form>
        </div>

        {{-- Table / Empty --}}
        @if($requests->isEmpty())
        <div class="empty-state">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            <p>Belum ada permintaan penghapusan tiket.</p>
        </div>
        @else
        <div class="td-table-wrap">
            <table class="td-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tiket</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Diajukan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $item)
                    <tr>
                        <td><span class="row-id">#{{ $item->id }}</span></td>

                        <td>
                            <div class="ticket-num">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                                {{ $item->ticket->ticket_number ?? '-' }}
                            </div>
                            <div class="ticket-title">{{ $item->ticket->title ?? 'Tiket sudah terhapus' }}</div>
                        </td>

                        <td>
                            <span class="client-chip">
                                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ $item->user->name ?? '-' }}
                            </span>
                        </td>

                        <td>
                            @php
                                $badgeClass = match($item->status) {
                                    'pending'  => 'badge-pending',
                                    'approved' => 'badge-approved',
                                    'rejected' => 'badge-rejected',
                                    default    => 'badge-default',
                                };
                                $badgeLabel = match($item->status) {
                                    'pending'  => 'Pending',
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    default    => ucfirst($item->status),
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                        </td>

                        <td>
                            <div class="date-text">
                                {{ $item->created_at?->format('d M Y') }}<br>
                                <span class="date-time">{{ $item->created_at?->format('H:i') }}</span>
                            </div>
                        </td>

                        <td>
                            <a href="{{ route('admin.ticket-deletion-requests.show', $item->id) }}" class="btn-detail">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="td-pagination">
            {{ $requests->links() }}
        </div>
        @endif

    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
        --brand:        #6C5CE7;
        --brand-light:  #EDE9FF;
        --brand-mid:    #A29BFE;
        --success:      #00B894;
        --success-bg:   #E6F9F4;
        --danger:       #D63031;
        --danger-bg:    #FDEAEA;
        --warn-color:   #D97706;
        --warn-bg:      #FFF8E7;
        --text:         #1A1A2E;
        --text-2:       #6B7280;
        --border:       #E5E7EB;
        --surface:      #FFFFFF;
        --bg-page:      #F5F4FF;
        --radius-card:  20px;
        --radius-sm:    10px;
        --shadow-card:  0 2px 16px 0 rgba(108,92,231,.07);
        --shadow-hover: 0 8px 32px 0 rgba(108,92,231,.13);
        --font:         'Plus Jakarta Sans', sans-serif;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: var(--font);
        background: var(--bg-page);
        color: var(--text);
        -webkit-font-smoothing: antialiased;
    }

    /* Page wrapper */
    .td-wrapper {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    /* Hero */
    .td-hero {
        background: linear-gradient(135deg, #D63031 0%, #FF7675 100%);
        border-radius: var(--radius-card);
        padding: 2rem 2.2rem 2.2rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        animation: fadeUp .45s ease both;
    }
    .td-hero::before {
        content: '';
        position: absolute;
        width: 300px; height: 300px;
        border-radius: 50%;
        background: rgba(255,255,255,.07);
        top: -90px; right: -70px;
        pointer-events: none;
    }
    .td-hero::after {
        content: '';
        position: absolute;
        width: 150px; height: 150px;
        border-radius: 50%;
        background: rgba(255,255,255,.05);
        bottom: -50px; right: 130px;
        pointer-events: none;
    }
    .td-hero-label {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        opacity: .75;
        display: flex;
        align-items: center;
        gap: .4rem;
        margin-bottom: .6rem;
    }
    .td-hero h1 {
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -.02em;
    }
    .td-hero p {
        font-size: .875rem;
        opacity: .8;
        margin-top: .35rem;
        max-width: 400px;
        line-height: 1.55;
    }

    /* Stats */
    .td-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        animation: fadeUp .5s ease .07s both;
    }

    .stat-card {
        background: var(--surface);
        border-radius: var(--radius-card);
        padding: 1.25rem 1.4rem;
        box-shadow: var(--shadow-card);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: box-shadow .2s, transform .2s;
        border: 1px solid var(--border);
    }
    .stat-card:hover { box-shadow: var(--shadow-hover); transform: translateY(-2px); }

    .stat-icon {
        width: 50px; height: 50px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon.warn { background: var(--warn-bg);    color: var(--warn-color); }
    .stat-icon.ok   { background: var(--success-bg); color: var(--success); }
    .stat-icon.bad  { background: var(--danger-bg);  color: var(--danger); }

    .stat-label {
        font-size: .7rem;
        font-weight: 700;
        color: var(--text-2);
        margin-bottom: .2rem;
        text-transform: uppercase;
        letter-spacing: .07em;
    }
    .stat-value { font-size: 1.75rem; font-weight: 800; line-height: 1; color: var(--text); }

    /* Main Card */
    .td-card {
        background: var(--surface);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-card);
        border: 1px solid var(--border);
        overflow: hidden;
        animation: fadeUp .5s ease .14s both;
    }

    /* Card Header */
    .td-card-head {
        padding: 1.2rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
        flex-wrap: wrap;
        background: var(--bg-page);
    }
    .td-card-head-info h2 { font-size: 1rem; font-weight: 800; }
    .td-card-head-info p  { font-size: .78rem; color: var(--text-2); margin-top: .15rem; }

    /* Filter */
    .filter-wrap { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
    .filter-wrap select {
        font-family: var(--font);
        font-size: .82rem;
        font-weight: 600;
        color: var(--text);
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .5rem .9rem;
        outline: none;
        cursor: pointer;
        transition: border-color .2s;
    }
    .filter-wrap select:focus { border-color: var(--brand-mid); }

    /* Table */
    .td-table-wrap { overflow-x: auto; }
    table.td-table { width: 100%; border-collapse: collapse; }
    .td-table thead tr { background: var(--bg-page); }
    .td-table th {
        padding: .75rem 1.1rem;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .09em;
        color: var(--text-2);
        text-align: left;
        white-space: nowrap;
        border-bottom: 1px solid var(--border);
    }
    .td-table tbody tr {
        border-top: 1px solid var(--border);
        transition: background .15s;
    }
    .td-table tbody tr:hover { background: #FFF5F5; }
    .td-table td { padding: 1rem 1.1rem; font-size: .855rem; vertical-align: middle; }

    /* ID cell */
    .row-id {
        font-size: .75rem;
        font-weight: 700;
        color: var(--text-2);
        background: var(--bg-page);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: .25rem .6rem;
        display: inline-block;
    }

    /* Ticket cell */
    .ticket-num {
        font-weight: 700;
        color: var(--danger);
        display: flex; align-items: center; gap: .35rem;
        font-size: .9rem;
    }
    .ticket-title { font-size: .78rem; color: var(--text-2); margin-top: .2rem; }

    /* Client chip */
    .client-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: #EEF2FF;
        color: #4338CA;
        border-radius: 8px;
        padding: .3rem .7rem;
        font-size: .78rem;
        font-weight: 700;
    }

    /* Badge */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 50px;
        padding: .35rem .8rem;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .badge::before {
        content: '';
        width: 6px; height: 6px;
        border-radius: 50%;
        background: currentColor;
        flex-shrink: 0;
    }
    .badge-pending  { background: var(--warn-bg);    color: var(--warn-color); }
    .badge-approved { background: var(--success-bg); color: var(--success); }
    .badge-rejected { background: var(--danger-bg);  color: var(--danger); }
    .badge-default  { background: #F3F4F6;            color: var(--text-2); }

    /* Date cell */
    .date-text { font-size: .82rem; color: var(--text); font-weight: 600; white-space: nowrap; }
    .date-time { font-size: .75rem; color: var(--text-2); font-weight: 400; }

    /* Detail button */
    .btn-detail {
        font-family: var(--font);
        font-size: .78rem;
        font-weight: 700;
        color: var(--brand);
        background: var(--brand-light);
        border: none;
        border-radius: 8px;
        padding: .48rem .9rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex; align-items: center; gap: .4rem;
        transition: background .2s, transform .2s;
        white-space: nowrap;
    }
    .btn-detail:hover { background: #D8D2FF; transform: translateY(-1px); color: var(--brand); }

    /* Empty state */
    .empty-state {
        padding: 4rem 1rem;
        text-align: center;
        color: var(--text-2);
    }
    .empty-state svg { width: 52px; height: 52px; opacity: .25; margin-bottom: .9rem; }
    .empty-state p { font-size: .9rem; font-weight: 500; }

    /* Pagination */
    .td-pagination { padding: 1rem 1.5rem; border-top: 1px solid var(--border); }

    /* Animation */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .td-hero h1 { font-size: 1.45rem; }
        .td-hero { padding: 1.5rem; }
        .td-card-head { flex-direction: column; align-items: flex-start; }
        .td-stats { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 480px) {
        .td-stats { grid-template-columns: 1fr; }
        .td-hero h1 { font-size: 1.25rem; }
    }
</style>
@endsection