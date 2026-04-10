@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page_title', 'Dasbor Admin')
@section('breadcrumb', 'Home / Dashboard')

@push('styles')
<style>
.dashboard-wrap { display: flex; flex-direction: column; gap: 1.5rem; }

/* ───── HERO CARD ───── */
.hero-card {
    display: flex; justify-content: space-between; align-items: stretch;
    gap: 1.5rem; padding: 1.875rem;
    background: linear-gradient(135deg, #eef2ff 0%, #fff7ed 100%);
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
    margin: 0.875rem 0 0.5rem;
    font-size: clamp(1.5rem, 3vw, 2.25rem);
    font-weight: 800; color: var(--text);
}
.hero-copy > p { color: var(--text-muted); max-width: 680px; font-size: 0.9375rem; }
.hero-actions { display: flex; gap: 0.75rem; margin-top: 1.125rem; flex-wrap: wrap; }
.hero-focus {
    min-width: 200px; border-radius: 20px; padding: 1.25rem;
    background: rgba(255,255,255,0.85); display: grid;
    align-content: center; gap: 0.3rem; flex-shrink: 0;
    border: 1px solid rgba(79,70,229,0.1);
}
.hero-focus > span { color: var(--text-muted); font-weight: 700; font-size: 0.85rem; }
.hero-focus > strong { font-size: 1.875rem; font-weight: 800; color: var(--text); line-height: 1; }
.hero-focus > small { color: var(--text-light); font-size: 0.8rem; }

/* ───── STAT CARDS ───── */
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
.stat-card--warning { background: linear-gradient(180deg,#fff7ed,#fff); border-color: rgba(249,115,22,0.2); }
.stat-card--info    { background: linear-gradient(180deg,#eff6ff,#fff); border-color: rgba(59,130,246,0.2); }
.stat-card--success { background: linear-gradient(180deg,#f0fdf4,#fff); border-color: rgba(34,197,94,0.2); }

/* ───── OVERVIEW GRID ───── */
.overview-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 1rem;
}
.panel-card {
    background: white; border: 1px solid var(--border);
    border-radius: 26px; padding: 1.375rem;
    box-shadow: var(--shadow-sm);
}
.panel-card--priority { background: linear-gradient(180deg,#fff7ed,#fff); border-color: rgba(249,115,22,0.18); }
.panel-card--rating   { background: linear-gradient(180deg,#fefce8,#fff); border-color: rgba(234,179,8,0.2); }

.panel-head {
    display: flex; justify-content: space-between; gap: 1rem;
    align-items: flex-start; margin-bottom: 1.125rem;
}
.panel-head h5 { margin: 0; font-size: 1.0625rem; font-weight: 800; color: var(--text); }
.panel-head p  { margin: 0.25rem 0 0; color: var(--text-muted); font-size: 0.875rem; line-height: 1.5; }

.big-number { font-size: 2.5rem; font-weight: 800; color: #c2410c; line-height: 1; }

.badge-pill {
    display: inline-flex; padding: 0.4rem 0.75rem;
    border-radius: 999px; background: rgba(34,197,94,0.12);
    color: #15803d; font-weight: 800; font-size: 0.9rem;
}

/* Metric row */
.metric-row {
    display: grid; grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 0.75rem;
}
.metric-box {
    border-radius: 18px; padding: 0.875rem;
    background: var(--bg); border: 1px solid var(--border);
    display: grid; gap: 0.25rem;
}
.metric-box > strong { font-size: 1.375rem; font-weight: 800; color: var(--text); }
.metric-box > span   { font-size: 0.8rem; color: var(--text-muted); }
.metric-box--success { background: #f0fdf4; border-color: rgba(34,197,94,0.2); }
.metric-box--danger  { background: #fff7ed; border-color: rgba(249,115,22,0.2); }

.progress-rail {
    margin-top: 1rem; width: 100%; height: 0.7rem;
    border-radius: 999px; background: var(--border); overflow: hidden;
}
.progress-bar {
    height: 100%; border-radius: 999px;
    background: linear-gradient(90deg, #22c55e, #4f46e5);
    transition: width 0.6s ease;
}

/* Rating */
.rating-score { display: flex; align-items: center; gap: 0.875rem; margin-bottom: 1rem; }
.rating-number { font-size: 2.5rem; font-weight: 800; color: var(--text); line-height: 1; }
.rating-stars  { display: flex; gap: 2px; }
.star { font-size: 1.25rem; }
.star--filled { color: #f59e0b; }
.star--empty  { color: var(--border); }
.rating-score small { color: var(--text-light); font-size: 0.85rem; }
.star-bars { display: flex; flex-direction: column; gap: 0.4rem; }
.star-row { display: flex; align-items: center; gap: 0.5rem; }
.star-label { font-size: 0.8rem; font-weight: 700; color: var(--text-muted); min-width: 1.75rem; }
.star-bar { flex: 1; height: 0.5rem; border-radius: 999px; background: var(--border); overflow: hidden; }
.star-fill {
    height: 100%; border-radius: 999px;
    background: linear-gradient(90deg, #f59e0b, #fbbf24);
    transition: width 0.5s ease;
}
.star-count { font-size: 0.8rem; color: var(--text-light); min-width: 1.5rem; text-align: right; }

/* ───── USER GRID ───── */
.user-grid {
    display: grid; grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 0.75rem;
}
.user-card {
    border-radius: 18px; padding: 1rem;
    background: var(--bg); border: 1px solid var(--border);
    display: grid; gap: 0.3rem;
}
.user-card > span   { color: var(--text-muted); font-weight: 700; font-size: 0.85rem; }
.user-card > strong { font-size: 1.625rem; font-weight: 800; color: var(--text); }

/* ───── TICKET GRID ───── */
.ticket-grid {
    display: grid; grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 1rem;
}
.ticket-card {
    background: white; border: 1px solid var(--border);
    border-radius: 22px; padding: 1.125rem;
    display: flex; flex-direction: column; gap: 0.75rem;
    box-shadow: var(--shadow-sm); transition: all 0.25s;
}
.ticket-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
.ticket-card-top {
    display: flex; justify-content: space-between;
    gap: 0.75rem; align-items: flex-start;
}
.ticket-number { color: var(--primary); font-weight: 800; font-size: 0.85rem; display: block; }
.ticket-card-top h6 { margin: 0.25rem 0 0; font-size: 0.9375rem; font-weight: 800; color: var(--text); }
.ticket-meta {
    display: flex; flex-wrap: wrap; gap: 0.375rem;
}
.ticket-meta span {
    font-size: 0.78rem; color: var(--text-muted);
    background: var(--bg); padding: 0.2rem 0.5rem;
    border-radius: 6px; border: 1px solid var(--border);
}
.ticket-footer {
    display: flex; justify-content: space-between;
    align-items: center; flex-wrap: wrap; gap: 0.5rem;
}
.ticket-footer small { font-size: 0.75rem; color: var(--text-light); }
.ticket-link {
    display: inline-block; font-size: 0.8125rem; font-weight: 700;
    color: var(--primary); text-decoration: none;
}
.ticket-link:hover { text-decoration: underline; }

/* Status chips */
.chip {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 0.3rem 0.7rem; border-radius: 999px;
    font-size: 0.75rem; font-weight: 800;
}
.chip-new, .chip-pending      { background: rgba(249,115,22,0.12); color: #c2410c; }
.chip-open, .chip-in_progress { background: rgba(59,130,246,0.12);  color: #1d4ed8; }
.chip-resolved, .chip-closed  { background: rgba(34,197,94,0.12);   color: #15803d; }
.chip-high, .chip-critical    { background: rgba(239,68,68,0.12);   color: #b91c1c; }
.chip-medium                  { background: rgba(249,115,22,0.12);  color: #c2410c; }
.chip-low                     { background: rgba(34,197,94,0.12);   color: #15803d; }
.chip-none                    { background: rgba(148,163,184,0.14); color: #475569; }

/* Buttons */
.btn-primary-sm {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.7rem 1.125rem; background: var(--gradient);
    color: white; border: none; border-radius: 12px;
    font-weight: 700; font-size: 0.9rem;
    text-decoration: none; cursor: pointer;
    transition: all 0.25s; box-shadow: var(--shadow-colored);
}
.btn-primary-sm:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(79,70,229,0.3); color: white; }

.btn-warning {
    width: 100%; padding: 0.9rem; background: #f59e0b; color: white;
    border: none; border-radius: 14px; font-weight: 700; font-size: 0.9375rem;
    cursor: pointer; transition: all 0.2s;
}
.btn-warning:hover:not(:disabled) { background: #d97706; transform: translateY(-1px); }
.btn-warning:disabled { opacity: 0.55; cursor: not-allowed; }

.btn-icon {
    width: 38px; height: 38px; border: 1px solid var(--border);
    background: white; border-radius: 9px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.125rem; color: var(--text-muted);
    transition: all 0.2s;
}
.btn-icon:hover { background: var(--bg); color: var(--primary); }

.head-actions { display: flex; gap: 0.625rem; align-items: center; }
.link-inline { font-weight: 700; text-decoration: none; color: var(--primary); font-size: 0.875rem; }
.link-inline:hover { text-decoration: underline; }

/* Empty state */
.empty-state {
    border: 1px dashed rgba(148,163,184,0.5); border-radius: 18px;
    padding: 1.5rem; color: var(--text-muted); text-align: center;
    font-size: 0.9rem;
}

/* ───── RESPONSIVE ───── */
@media (max-width: 1199px) {
    .stats-grid, .overview-grid, .ticket-grid { grid-template-columns: 1fr 1fr; }
    .metric-row, .user-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
}
@media (max-width: 767px) {
    .hero-card, .panel-head, .ticket-card-top, .ticket-footer { flex-direction: column; align-items: flex-start; }
    .stats-grid, .overview-grid, .metric-row, .ticket-grid, .user-grid { grid-template-columns: 1fr; }
    .hero-focus { min-width: 100%; }
}
</style>
@endpush

@section('content')
<div class="dashboard-wrap">

    {{-- ═══ HERO ═══ --}}
    <section class="hero-card">
        <div class="hero-copy">
            <span class="hero-kicker">Dashboard Admin</span>
            <h3>Ringkasan operasional HelpDesk hari ini</h3>
            <p>Pantau tiket masuk dan performa SLA dari satu layar yang lebih rapi dan responsif.</p>
            <div class="hero-actions">
                <a href="{{ route('admin.tickets.index') }}" class="btn-primary-sm">
                    <i class='bx bx-file'></i> Buka Semua Tiket
                </a>
            </div>
        </div>
        <div class="hero-focus">
            <span>Butuh perhatian</span>
            <strong>{{ $stats['new_tickets'] ?? 0 }} tiket baru</strong>
            <small>{{ $stats['tickets_without_priority'] ?? 0 }} tiket tanpa prioritas perlu tindak lanjut</small>
        </div>
    </section>

    {{-- ═══ STAT CARDS ═══ --}}
    <section class="stats-grid">
        <article class="stat-card">
            <span>Total tiket</span>
            <strong>{{ $stats['total_tickets'] ?? 0 }}</strong>
            <small>Akumulasi seluruh tiket yang masuk</small>
        </article>
        <article class="stat-card stat-card--warning">
            <span>Tiket baru</span>
            <strong>{{ $stats['new_tickets'] ?? 0 }}</strong>
            <small>Perlu triase dan penugasan</small>
        </article>
        <article class="stat-card stat-card--info">
            <span>Dalam proses</span>
            <strong>{{ $stats['in_progress'] ?? 0 }}</strong>
            <small>Masih dikerjakan tim vendor</small>
        </article>
        <article class="stat-card stat-card--success">
            <span>Terselesaikan</span>
            <strong>{{ $stats['resolved'] ?? 0 }}</strong>
            <small>Tiket selesai dan ditutup</small>
        </article>
    </section>

    {{-- ═══ OVERVIEW ═══ --}}
    <section class="overview-grid">

        {{-- Priority --}}
        <article class="panel-card panel-card--priority">
            <div class="panel-head">
                <div>
                    <h5>Prioritas belum ditetapkan</h5>
                    <p>Pastikan tiket tanpa prioritas segera diarahkan agar SLA tidak terganggu.</p>
                </div>
                <span class="big-number">{{ $stats['tickets_without_priority'] ?? 0 }}</span>
            </div>
            @if(($stats['tickets_without_priority'] ?? 0) > 0)
                <form method="GET" action="{{ route('admin.tickets.index') }}">
                    <input type="hidden" name="filter" value="no_priority">
                    <button type="submit" class="btn-warning">Tetapkan Prioritas</button>
                </form>
            @else
                <button class="btn-warning" disabled>Tidak ada tiket tanpa prioritas</button>
            @endif
        </article>

        {{-- SLA --}}
        <article class="panel-card">
            <div class="panel-head">
                <div>
                    <h5>Performa SLA</h5>
                    <p>Persentase tiket yang selesai sesuai target layanan.</p>
                </div>
                <span class="badge-pill">{{ $slaPerformance['percentage'] ?? 0 }}%</span>
            </div>
            <div class="metric-row">
                <div class="metric-box">
                    <strong>{{ $slaPerformance['total'] ?? 0 }}</strong>
                    <span>Total SLA</span>
                </div>
                <div class="metric-box metric-box--success">
                    <strong>{{ $slaPerformance['met'] ?? 0 }}</strong>
                    <span>Tercapai</span>
                </div>
                <div class="metric-box metric-box--danger">
                    <strong>{{ $slaPerformance['missed'] ?? 0 }}</strong>
                    <span>Terlewat</span>
                </div>
            </div>
            <div class="progress-rail">
                <div class="progress-bar" style="width: {{ $slaPerformance['percentage'] ?? 0 }}%"></div>
            </div>
        </article>

        {{-- Rating --}}
        <article class="panel-card panel-card--rating">
            <div class="panel-head">
                <div>
                    <h5>Rating layanan</h5>
                    <p>Penilaian kepuasan dari klien terhadap penyelesaian tiket.</p>
                </div>
                <a href="{{ route('admin.vendor-ratings.index') }}" class="link-inline">Lihat detail</a>
            </div>
            <div class="rating-score">
                <strong class="rating-number">{{ $ratingData['average'] ?? '0.0' }}</strong>
                <div class="rating-stars">
                    @for($s = 1; $s <= 5; $s++)
                        <span class="star {{ $s <= round($ratingData['average'] ?? 0) ? 'star--filled' : 'star--empty' }}">★</span>
                    @endfor
                </div>
                <small>dari {{ $ratingData['total'] ?? 0 }} ulasan</small>
            </div>
            <div class="star-bars">
                @foreach([5,4,3,2,1] as $star)
                    @php
                        $count = $ratingData['distribution'][$star] ?? 0;
                        $pct   = ($ratingData['total'] ?? 0) > 0
                            ? ($count / $ratingData['total']) * 100
                            : 0;
                    @endphp
                    <div class="star-row">
                        <span class="star-label">{{ $star }}★</span>
                        <div class="star-bar"><div class="star-fill" style="width: {{ $pct }}%"></div></div>
                        <span class="star-count">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </article>

    </section>

    {{-- ═══ USER COMPOSITION ═══ --}}
    <section class="panel-card">
        <div class="panel-head">
            <div>
                <h5>Komposisi pengguna</h5>
                <p>Gambaran singkat akun yang aktif di sistem HelpDesk.</p>
            </div>
        </div>
        <div class="user-grid">
            <div class="user-card">
                <span>Total pengguna</span>
                <strong>{{ $stats['total_users'] ?? 0 }}</strong>
            </div>
            <div class="user-card">
                <span>Vendor</span>
                <strong>{{ $stats['vendors'] ?? 0 }}</strong>
            </div>
            <div class="user-card">
                <span>Client</span>
                <strong>{{ $stats['clients'] ?? 0 }}</strong>
            </div>
        </div>
    </section>

    {{-- ═══ RECENT TICKETS ═══ --}}
    <section class="panel-card">
        <div class="panel-head">
            <div>
                <h5>Tiket terbaru</h5>
                <p>Daftar tiket terbaru untuk tindak lanjut cepat oleh admin.</p>
            </div>
            <div class="head-actions">
                <a href="{{ route('admin.dashboard') }}" class="btn-icon" title="Refresh">
                    <i class='bx bx-refresh'></i>
                </a>
                <a href="{{ route('admin.tickets.index') }}" class="btn-primary-sm">Lihat semua</a>
            </div>
        </div>

        @if(empty($recentTickets) || count($recentTickets) === 0)
            <div class="empty-state">Belum ada tiket terbaru.</div>
        @else
            <div class="ticket-grid">
                @foreach($recentTickets as $ticket)
                <article class="ticket-card">
                    <div class="ticket-card-top">
                        <div>
                            <span class="ticket-number">{{ $ticket->ticket_number ?? '#' }}</span>
                            <h6>{{ $ticket->title }}</h6>
                        </div>
                        <span class="chip chip-{{ $ticket->status }}">
                            @php
                                $statusLabels = [
                                    'new'         => 'Baru',
                                    'open'        => 'Terbuka',
                                    'pending'     => 'Menunggu',
                                    'in_progress' => 'Diproses',
                                    'resolved'    => 'Selesai',
                                    'closed'      => 'Ditutup',
                                ];
                            @endphp
                            {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                        </span>
                    </div>
                    <div class="ticket-meta">
                        <span>{{ $ticket->user->name ?? 'Client belum terdeteksi' }}</span>
                        <span>{{ $ticket->category->name ?? 'Tanpa kategori' }}</span>
                        <span>{{ $ticket->assignedTo->name ?? 'Belum ditugaskan' }}</span>
                    </div>
                    <div class="ticket-footer">
                        @php
                            $priorityLabels = [
                                'low'      => 'Rendah',
                                'medium'   => 'Sedang',
                                'high'     => 'Tinggi',
                                'critical' => 'Kritis',
                            ];
                        @endphp
                        <span class="chip chip-{{ $ticket->priority ?? 'none' }}">
                            {{ $priorityLabels[$ticket->priority] ?? 'Belum diatur' }}
                        </span>
                        <small>
                            {{ $ticket->created_at
                                ? \Carbon\Carbon::parse($ticket->created_at)->locale('id')->isoFormat('D MMM YYYY, HH:mm')
                                : '-' }}
                        </small>
                    </div>
                    <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="ticket-link">
                        Buka detail →
                    </a>
                </article>
                @endforeach
            </div>
        @endif
    </section>

</div>
@endsection