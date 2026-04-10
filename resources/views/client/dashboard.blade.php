@extends('layouts.client')

@section('title', 'Dashboard Client')
@section('page_title', 'Dasbor Client')
@section('breadcrumb', 'Home / Dashboard')

@push('styles')
<style>
/* ══════════════════════════════════════════
   CLIENT DASHBOARD — CLEAN BLADE VERSION
══════════════════════════════════════════ */
.client-dashboard { display: flex; flex-direction: column; gap: 1.5rem; }

/* ───── HERO ───── */
.dashboard-hero {
    display: grid;
    grid-template-columns: minmax(0, 1.45fr) minmax(280px, .75fr);
    gap: 1rem;
    padding: 1.5rem;
    border-radius: 28px;
    background: linear-gradient(135deg, #eef2ff 0%, #fff 55%, #f8fafc 100%);
    border: 1px solid rgba(99,102,241,.1);
    box-shadow: 0 18px 40px rgba(15,23,42,.05);
}
.hero-badge {
    display: inline-flex;
    padding: .35rem .75rem;
    border-radius: 999px;
    background: rgba(99,102,241,.1);
    color: #6366f1;
    font-size: .75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .06em;
}
.hero-copy h1 {
    margin: .9rem 0 .45rem;
    font-size: clamp(1.8rem, 4vw, 2.7rem);
    font-weight: 800;
    color: #0f172a;
}
.hero-copy p { margin: 0; max-width: 650px; color: #64748b; }
.hero-actions { display: flex; gap: .8rem; flex-wrap: wrap; margin-top: 1.1rem; }

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    padding: .85rem 1.1rem;
    border-radius: 16px;
    font-weight: 700;
    text-decoration: none;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: #fff;
    transition: all .2s;
}
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(99,102,241,.3); color: #fff; }

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    padding: .85rem 1.1rem;
    border-radius: 16px;
    font-weight: 700;
    text-decoration: none;
    background: #fff;
    color: #4f46e5;
    border: 1px solid rgba(99,102,241,.16);
    transition: all .2s;
}
.btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(99,102,241,.1); color: #4f46e5; }

.focus-card {
    width: 100%;
    border-radius: 24px;
    padding: 1.2rem;
    display: grid;
    gap: .45rem;
    align-content: center;
    background: #fff;
    border: 1px solid rgba(99,102,241,.1);
    box-shadow: 0 18px 40px rgba(15,23,42,.05);
}
.focus-label { color: #64748b; font-weight: 700; font-size: .85rem; }
.focus-card strong { font-size: 1.5rem; font-weight: 800; color: #0f172a; }
.focus-card small { color: #94a3b8; }
.focus-link { color: #4f46e5; font-weight: 700; text-decoration: none; font-size: .875rem; }
.focus-link:hover { text-decoration: underline; }

/* ───── SUMMARY GRID ───── */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}
.summary-card {
    background: #fff;
    border: 1px solid rgba(99,102,241,.1);
    box-shadow: 0 18px 40px rgba(15,23,42,.05);
    border-radius: 22px;
    padding: 1.15rem;
    display: grid;
    gap: .35rem;
    transition: all .25s;
}
.summary-card:hover { transform: translateY(-3px); box-shadow: 0 20px 45px rgba(79,70,229,.08); }
.summary-card > span { color: #64748b; font-weight: 700; font-size: .85rem; }
.summary-card > strong { font-size: 2rem; font-weight: 800; color: #0f172a; line-height: 1; }
.summary-card > small { color: #94a3b8; font-size: .8rem; }
.summary-card--active { background: linear-gradient(180deg, #eff6ff, #fff); border-color: rgba(59,130,246,.2); }
.summary-card--done   { background: linear-gradient(180deg, #f0fdf4, #fff); border-color: rgba(34,197,94,.2); }
.summary-card--rating { background: linear-gradient(180deg, #fff7ed, #fff); border-color: rgba(249,115,22,.2); }

/* ───── PENDING STRIP ───── */
.pending-strip {
    background: #fff;
    border: 1px solid rgba(99,102,241,.1);
    box-shadow: 0 18px 40px rgba(15,23,42,.05);
    border-radius: 28px;
    padding: 1.35rem;
}
.section-kicker {
    display: inline-flex;
    padding: .35rem .75rem;
    border-radius: 999px;
    background: rgba(99,102,241,.1);
    color: #6366f1;
    font-size: .75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .06em;
}
.section-head {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
    margin-bottom: 1rem;
}
.section-head h2 { margin: .35rem 0 0; font-size: 1.75rem; font-weight: 800; color: #0f172a; }
.section-link { color: #4f46e5; font-weight: 700; text-decoration: none; font-size: .875rem; }
.section-link:hover { text-decoration: underline; }

.pending-list {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .9rem;
}
.pending-item {
    background: #fff;
    border: 1px solid rgba(99,102,241,.1);
    box-shadow: 0 18px 40px rgba(15,23,42,.05);
    border-radius: 20px;
    padding: 1rem;
    text-decoration: none;
    display: block;
    transition: all .2s;
}
.pending-item:hover { transform: translateY(-2px); box-shadow: 0 20px 45px rgba(79,70,229,.08); }
.pending-item strong { display: block; color: #d97706; font-weight: 800; }
.pending-item p { margin: .4rem 0; color: #0f172a; font-weight: 700; }
.pending-item small { color: #64748b; font-size: .8rem; }

/* ───── CONTENT GRID ───── */
.content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(280px, .75fr);
    gap: 1rem;
}
.content-card {
    background: #fff;
    border: 1px solid rgba(99,102,241,.1);
    box-shadow: 0 18px 40px rgba(15,23,42,.05);
    border-radius: 28px;
    padding: 1.35rem;
}

/* ───── TICKET LIST ───── */
.ticket-list { display: grid; gap: .9rem; }
.ticket-row {
    background: #fff;
    border: 1px solid rgba(99,102,241,.1);
    box-shadow: 0 18px 40px rgba(15,23,42,.05);
    border-radius: 22px;
    padding: 1rem;
    cursor: pointer;
    text-decoration: none;
    display: block;
    transition: all .18s ease;
}
.ticket-row:hover { transform: translateY(-2px); box-shadow: 0 20px 45px rgba(79,70,229,.08); }
.ticket-row__main .ticket-number { color: #4f46e5; font-weight: 800; font-size: .85rem; }
.ticket-row__main h3 { margin: .3rem 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; }
.ticket-row__main p { margin: 0; color: #64748b; font-size: .9rem; }
.ticket-row__meta {
    display: flex;
    flex-wrap: wrap;
    gap: .55rem;
    align-items: center;
    margin-top: .85rem;
}
.ticket-row__footer {
    display: flex;
    flex-wrap: wrap;
    gap: .55rem;
    align-items: center;
    justify-content: space-between;
    margin-top: .85rem;
    color: #64748b;
    font-size: .85rem;
}

/* ───── CHIPS / PILLS ───── */
.meta-pill {
    display: inline-flex;
    align-items: center;
    padding: .35rem .7rem;
    border-radius: 999px;
    font-size: .78rem;
    font-weight: 700;
    background: #f8fafc;
    color: #475569;
    border: 1px solid rgba(148,163,184,.2);
}
.rating-flag {
    display: inline-flex;
    align-items: center;
    padding: .35rem .7rem;
    border-radius: 999px;
    font-size: .78rem;
    font-weight: 700;
    background: rgba(249,115,22,.12);
    color: #c2410c;
}

/* Status badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: .35rem .7rem;
    border-radius: 999px;
    font-size: .78rem;
    font-weight: 800;
}
.status-new              { background: rgba(249,115,22,.12);  color: #c2410c; }
.status-in_progress      { background: rgba(59,130,246,.12);  color: #1d4ed8; }
.status-waiting_response { background: rgba(168,85,247,.12);  color: #7e22ce; }
.status-resolved         { background: rgba(34,197,94,.12);   color: #15803d; }
.status-closed           { background: rgba(148,163,184,.14); color: #475569; }

/* Priority badges */
.priority-low      { background: rgba(34,197,94,.12);  color: #15803d; }
.priority-medium   { background: rgba(250,204,21,.16);  color: #a16207; }
.priority-high     { background: rgba(249,115,22,.12);  color: #c2410c; }
.priority-urgent,
.priority-critical { background: rgba(239,68,68,.12);   color: #b91c1c; }

/* ───── QUICK ACTIONS ───── */
.quick-actions { display: grid; gap: .9rem; }
.quick-action {
    background: #fff;
    border: 1px solid rgba(99,102,241,.1);
    box-shadow: 0 18px 40px rgba(15,23,42,.05);
    border-radius: 20px;
    padding: 1rem;
    display: flex;
    gap: .9rem;
    align-items: flex-start;
    text-decoration: none;
    transition: all .2s;
}
.quick-action:hover { transform: translateY(-2px); box-shadow: 0 20px 45px rgba(79,70,229,.08); }
.quick-action i { font-size: 1.4rem; color: #6366f1; margin-top: .1rem; flex-shrink: 0; }
.quick-action strong { display: block; color: #0f172a; font-weight: 800; }
.quick-action small { color: #64748b; }

/* ───── EMPTY STATE ───── */
.empty-state {
    border: 1px dashed rgba(148,163,184,.5);
    border-radius: 20px;
    padding: 1.25rem;
    text-align: center;
    color: #64748b;
    font-size: .9rem;
}

/* ───── RESPONSIVE ───── */
@media (max-width: 1199px) {
    .summary-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
    .pending-list { grid-template-columns: repeat(2, minmax(0,1fr)); }
    .content-grid, .dashboard-hero { grid-template-columns: 1fr; }
}
@media (max-width: 767px) {
    .summary-grid, .pending-list, .content-grid { grid-template-columns: 1fr; }
    .section-head, .ticket-row__footer { flex-direction: column; align-items: flex-start; }
    .section-head h2 { font-size: 1.45rem; }
    .hero-copy h1 { font-size: 1.8rem; }
}
</style>
@endpush

@section('content')
<div class="client-dashboard">

    {{-- ═══ HERO ═══ --}}
    <section class="dashboard-hero">
        <div class="hero-copy">
            <span class="hero-badge">Ringkasan Client</span>
            <h1>Halo, {{ Auth::user()->name }}.</h1>
            <p>Pantau tiket aktif, lihat layanan yang belum dinilai, dan buat laporan baru tanpa tampilan yang terlalu padat.</p>
            <div class="hero-actions">
                <a href="{{ route('client.tickets.create') }}" class="btn-primary">
                    <i class='bx bx-plus-circle'></i>
                    <span>Buat Tiket</span>
                </a>
                <a href="{{ route('client.tickets.index') }}" class="btn-secondary">
                    <i class='bx bx-file'></i>
                    <span>Laporan Saya</span>
                </a>
            </div>
        </div>

        <div style="display:flex;">
            <article class="focus-card">
                <span class="focus-label">Butuh perhatian</span>
                <strong>{{ $pendingFeedbackCount }} layanan belum dinilai</strong>
                <small>Berikan rating agar evaluasi vendor lebih akurat.</small>
                <a href="{{ route('client.history') }}" class="focus-link">Buka riwayat →</a>
            </article>
        </div>
    </section>

    {{-- ═══ SUMMARY CARDS ═══ --}}
    <section class="summary-grid">
        <article class="summary-card">
            <span>Total tiket</span>
            <strong>{{ $stats['total'] }}</strong>
            <small>Seluruh laporan yang pernah Anda buat</small>
        </article>
        <article class="summary-card summary-card--active">
            <span>Sedang diproses</span>
            <strong>{{ $stats['in_progress'] }}</strong>
            <small>Masih ditangani vendor</small>
        </article>
        <article class="summary-card summary-card--done">
            <span>Selesai</span>
            <strong>{{ $stats['resolved'] }}</strong>
            <small>Resolved atau closed</small>
        </article>
        <article class="summary-card summary-card--rating">
            <span>Belum dinilai</span>
            <strong>{{ $pendingFeedbackCount }}</strong>
            <small>Tiket selesai yang menunggu rating</small>
        </article>
    </section>

    {{-- ═══ PENDING FEEDBACK STRIP ═══ --}}
    @if($pendingFeedbackItems->count())
    <section class="pending-strip">
        <div class="section-head">
            <div>
                <span class="section-kicker">Perlu Rating</span>
                <h2>Layanan vendor yang belum Anda nilai</h2>
            </div>
            <a href="{{ route('client.history') }}" class="section-link">Lihat semua</a>
        </div>

        <div class="pending-list">
            @foreach($pendingFeedbackItems->take(3) as $ticket)
            <a href="{{ route('client.history') }}" class="pending-item">
                <strong>#{{ $ticket->ticket_number }}</strong>
                <p>{{ $ticket->title }}</p>
                <small>{{ $ticket->assignedVendor->name ?? 'Vendor sudah menyelesaikan tiket ini' }}</small>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ═══ CONTENT GRID ═══ --}}
    <section class="content-grid">

        {{-- Recent Tickets --}}
        <article class="content-card">
            <div class="section-head">
                <div>
                    <span class="section-kicker">Aktivitas Terbaru</span>
                    <h2>Tiket terbaru Anda</h2>
                </div>
                <a href="{{ route('client.history') }}" class="section-link">Riwayat</a>
            </div>

            @if($recentTickets->isEmpty())
                <div class="empty-state">Belum ada tiket yang bisa ditampilkan.</div>
            @else
                <div class="ticket-list">
                    @foreach($recentTickets as $ticket)
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
                        $desc = $ticket->description;
                        $shortDesc = mb_strlen($desc) > 110 ? mb_substr($desc, 0, 110) . '…' : $desc;
                    @endphp
                    <a href="{{ route('client.tickets.index') }}" class="ticket-row">
                        <div class="ticket-row__main">
                            <div class="ticket-number">#{{ $ticket->ticket_number }}</div>
                            <h3>{{ $ticket->title }}</h3>
                            <p>{{ $shortDesc }}</p>
                        </div>

                        <div class="ticket-row__meta">
                            <span class="status-badge status-{{ $ticket->status }}">
                                {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                            </span>
                            <span class="meta-pill">{{ $ticket->category->name ?? 'Tanpa kategori' }}</span>
                            <span class="meta-pill">
                                {{ \Carbon\Carbon::parse($ticket->created_at)->locale('id')->diffForHumans() }}
                            </span>
                            @if($ticket->priority)
                                <span class="meta-pill priority-{{ $ticket->priority }}">
                                    {{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}
                                </span>
                            @endif
                        </div>

                        <div class="ticket-row__footer">
                            <span>{{ $ticket->assignedVendor->name ?? 'Menunggu penugasan vendor' }}</span>
                            @if(in_array($ticket->status, ['resolved','closed']) && !$ticket->feedback)
                                <span class="rating-flag">Perlu rating</span>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
            @endif
        </article>

        {{-- Quick Actions --}}
        <article class="content-card">
            <div class="section-head">
                <div>
                    <span class="section-kicker">Aksi Cepat</span>
                    <h2>Yang bisa Anda lakukan</h2>
                </div>
            </div>

            <div class="quick-actions">
                <a href="{{ route('client.tickets.create') }}" class="quick-action">
                    <i class='bx bx-plus-circle'></i>
                    <div>
                        <strong>Buat tiket baru</strong>
                        <small>Kirim permintaan bantuan baru ke tim.</small>
                    </div>
                </a>

                <a href="{{ route('client.tickets.index') }}" class="quick-action">
                    <i class='bx bx-folder-open'></i>
                    <div>
                        <strong>Laporan saya</strong>
                        <small>Lihat seluruh tiket aktif Anda.</small>
                    </div>
                </a>

                <a href="{{ route('client.history') }}" class="quick-action">
                    <i class='bx bx-star'></i>
                    <div>
                        <strong>Beri rating vendor</strong>
                        <small>Periksa tiket selesai yang belum dinilai.</small>
                    </div>
                </a>
            </div>
        </article>

    </section>

</div>
@endsection