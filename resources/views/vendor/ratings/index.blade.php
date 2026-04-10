@extends('layouts.app')

@section('title', 'Rating dari Client')
@section('page_title', 'Rating dari Client')
@section('breadcrumb', 'Home / Rating')

@push('styles')
<style>
/* ══════════════════════════════════════
   VENDOR RATINGS — BLADE
══════════════════════════════════════ */
.ratings-page {
    display:flex; flex-direction:column; gap:1.5rem;
    --teal:#0f766e; --teal-light:#ecfdf5; --teal-border:rgba(15,118,110,.14);
    --warning-bg:#fff7ed; --warning-text:#c2410c;
    --danger-bg:#fff1f2; --danger-text:#be123c;
}

/* ── HERO ── */
.rt-hero {
    display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap;
    padding:1.5rem; border-radius:28px;
    background:radial-gradient(circle at right top, rgba(45,212,191,.22), transparent 28%),
               linear-gradient(135deg,#effcf7 0%,#ffffff 58%,#f1fdfa 100%);
    border:1px solid var(--teal-border);
    box-shadow:0 18px 40px rgba(15,23,42,.05);
}
.rt-hero-copy { flex:1; min-width:0; }
.hero-kicker {
    display:inline-flex; padding:.35rem .75rem; border-radius:999px;
    background:rgba(13,148,136,.1); color:var(--teal);
    font-size:.72rem; font-weight:800; letter-spacing:.05em; text-transform:uppercase;
}
.rt-hero h1 { margin:.85rem 0 0; font-size:clamp(1.5rem,2.5vw,2rem); font-weight:800; color:#0f172a; }
.rt-hero p  { margin:.5rem 0 0; max-width:680px; color:#64748b; line-height:1.7; }
.rt-score-box {
    min-width:200px; padding:1rem 1.15rem; border-radius:20px;
    background:rgba(255,255,255,.88); border:1px solid rgba(15,118,110,.12);
    display:flex; flex-direction:column; gap:.2rem;
}
.rt-score-box > span { font-size:.78rem; color:#64748b; }
.rt-score-val { font-size:1.9rem; font-weight:800; color:#0f172a; margin:.25rem 0; }
.rt-score-sub { font-size:.78rem; color:#94a3b8; }

/* ── WARNING BANNER ── */
.warn-banner {
    display:flex; align-items:center; gap:1rem; flex-wrap:wrap;
    padding:1rem 1.2rem; border-radius:22px;
    background:linear-gradient(135deg,#fff7ed,#fffaf2);
    border:1px solid rgba(194,65,12,.18);
    box-shadow:0 8px 24px rgba(194,65,12,.06);
}
.warn-icon {
    width:48px; height:48px; border-radius:16px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    background:rgba(194,65,12,.12); color:var(--warning-text); font-size:1.4rem;
}
.warn-copy strong { color:var(--warning-text); font-weight:800; display:block; }
.warn-copy p { margin:.2rem 0 0; color:#9a3412; font-size:.9rem; }
.warn-count {
    margin-left:auto; white-space:nowrap;
    padding:.5rem .85rem; border-radius:999px;
    background:rgba(194,65,12,.12); color:var(--warning-text);
    font-size:.78rem; font-weight:800;
}

/* ── WARNING HISTORY ── */
.panel-card {
    background:#fff; border-radius:22px;
    border:1px solid rgba(99,102,241,.1);
    box-shadow:0 18px 40px rgba(15,23,42,.05);
}
.panel-head {
    display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;
    padding:1.15rem 1.35rem; border-bottom:1px solid rgba(148,163,184,.1);
    background:linear-gradient(180deg,rgba(248,250,252,.88),rgba(255,255,255,.98));
    border-radius:22px 22px 0 0;
}
.panel-head h5 { font-size:1rem; font-weight:800; color:#0f172a; margin:0 0 .2rem; }
.panel-head p  { font-size:.83rem; color:#64748b; margin:0; line-height:1.5; }
.panel-body { padding:1.35rem; }

/* ── STATS GRID ── */
.stats-grid { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:.9rem; }
.st-card {
    padding:1rem 1.1rem; border-radius:18px;
    background:#fff; border:1px solid rgba(99,102,241,.1);
    box-shadow:0 8px 20px rgba(15,23,42,.04); transition:all .2s;
}
.st-card:hover { transform:translateY(-2px); box-shadow:0 14px 28px rgba(79,70,229,.08); }
.st-card--good    { background:linear-gradient(135deg,#f0fdf4,#fff); }
.st-card--warning { background:linear-gradient(135deg,#fff7ed,#fff); }
.st-label { font-size:.75rem; color:#64748b; font-weight:600; display:block; }
.st-value { font-size:1.4rem; font-weight:800; color:#0f172a; margin:.2rem 0 0; display:block; }

/* ── FILTER CARD ── */
.filter-grid { display:grid; grid-template-columns:1fr 1fr 2fr; gap:1rem; }
.fg { display:flex; flex-direction:column; gap:.4rem; }
.fg label { font-size:.82rem; font-weight:700; color:#334155; }
.fg select, .fg input {
    width:100%; padding:.75rem 1rem; border:2px solid #99f6e4;
    border-radius:14px; font-size:.9rem; background:#fff; color:#0f172a; transition:all .2s;
}
.fg select:focus, .fg input:focus { outline:none; border-color:#14b8a6; box-shadow:0 0 0 3px rgba(20,184,166,.14); }
.btn-reset {
    padding:.65rem 1.1rem; background:#ecfeff; border:1px solid rgba(15,118,110,.15);
    border-radius:12px; color:var(--teal); font-weight:700; font-size:.85rem;
    cursor:pointer; transition:all .2s; white-space:nowrap;
    display:inline-flex; align-items:center; gap:.4rem;
}
.btn-reset:hover { transform:translateY(-1px); box-shadow:0 6px 14px rgba(15,118,110,.12); }

/* ── TICKET RATING LIST ── */
.tr-list { display:flex; flex-direction:column; gap:1rem; }
.tr-item {
    border:1px solid #d9f5f1; border-radius:20px; padding:1rem 1.25rem;
    background:linear-gradient(135deg,#fbfffe,#fff); transition:all .2s;
}
.tr-item:hover { box-shadow:0 6px 20px rgba(15,118,110,.1); transform:translateY(-1px); }
.tr-item--alert {
    border-color:rgba(225,29,72,.18);
    background:linear-gradient(135deg,#fff8f8,#fff);
}
.tr-top { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; }
.tr-num   { font-size:.8rem; font-weight:800; color:var(--teal); }
.tr-title { font-size:1rem; font-weight:700; color:#0f172a; margin:.2rem 0 0; }
.tr-meta  { font-size:.83rem; color:#64748b; margin:.3rem 0 0; }
.tr-badges { display:flex; gap:.6rem; flex-wrap:wrap; align-items:flex-start; }
.tr-meta-pills { display:flex; gap:.5rem; flex-wrap:wrap; margin-top:.9rem; }
.meta-pill { display:inline-flex; align-items:center; border-radius:999px; padding:.4rem .85rem; font-size:.74rem; font-weight:800; background:#ecfeff; color:var(--teal); }

/* ── STATUS BADGES ── */
.rb-rated   { background:#dcfce7; color:#166534; }
.rb-pending { background:#fef3c7; color:#92400e; }
.rb-alert   { background:var(--danger-bg); color:var(--danger-text); }
.rb { display:inline-flex; align-items:center; border-radius:999px; padding:.35rem .8rem; font-size:.73rem; font-weight:800; }

/* ── PRIORITY/STATUS BADGES ── */
.badge-pill { display:inline-flex; align-items:center; padding:.3rem .75rem; border-radius:999px; font-size:.72rem; font-weight:800; }
.p-low      { background:rgba(107,114,128,.12); color:#374151; }
.p-medium   { background:rgba(59,130,246,.12);  color:#1e40af; }
.p-high     { background:rgba(245,158,11,.12);  color:#92400e; }
.p-critical,.p-urgent { background:rgba(239,68,68,.12); color:#991b1b; }

/* ── RATING RESULT ── */
.rating-result {
    margin-top:1rem; padding:1rem 1.15rem; border-radius:14px;
    background:rgba(255,255,255,.95); border:1px solid #e2f3ef;
}
.rating-result-head { display:flex; justify-content:space-between; align-items:center; gap:.75rem; }
.stars { display:inline-flex; gap:.1rem; font-size:.95rem; }
.star-on  { color:#f59e0b; }
.star-off { color:#d1d5db; }
.rating-result-head strong { color:var(--teal); font-weight:800; }
.rating-comment { margin:.6rem 0 0; color:#334155; line-height:1.7; font-size:.88rem; }
.rating-pending {
    margin-top:1rem; padding:1rem 1.15rem; border-radius:14px;
    background:rgba(255,255,255,.95); border:1px solid #e2f3ef;
    color:#64748b; font-size:.88rem;
}

/* ── LOADING / EMPTY ── */
.state-box { display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:240px; gap:1rem; text-align:center; color:#64748b; }
.state-box i { font-size:2.5rem; color:#cbd5e0; }
.state-box p { margin:0; }

/* ── PAGINATION ── */
.pg-wrap { display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; padding:1rem 1.35rem; border-top:1px solid rgba(148,163,184,.1); }
.pg-info { font-size:.85rem; color:#64748b; }
.pg-controls { display:flex; gap:.4rem; }
.pg-btn {
    min-width:34px; height:34px; padding:0 .45rem; background:#fff;
    border:2px solid #e2e8f0; border-radius:8px; color:#475569; font-weight:700;
    cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; justify-content:center; font-size:.82rem;
    text-decoration:none;
}
.pg-btn:not([disabled]):hover { border-color:var(--teal); background:var(--teal); color:#fff; transform:translateY(-1px); }
.pg-btn.active { background:var(--teal); border-color:var(--teal); color:#fff; }
.pg-btn[disabled] { opacity:.4; cursor:not-allowed; }

/* ── RESPONSIVE ── */
@media(max-width:1200px){ .stats-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
@media(max-width:992px) { .stats-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .filter-grid { grid-template-columns:1fr 1fr; } }
@media(max-width:768px) {
    .rt-hero { flex-direction:column; }
    .stats-grid { grid-template-columns:1fr; }
    .filter-grid { grid-template-columns:1fr; }
    .warn-count { margin-left:0; }
    .pg-wrap { flex-direction:column; align-items:stretch; }
    .pg-controls { justify-content:center; }
}
</style>
@endpush

@section('content')
@php
    $statusLabels   = ['resolved'=>'Selesai','closed'=>'Ditutup'];
    $priorityLabels = ['low'=>'Rendah','medium'=>'Sedang','high'=>'Tinggi','urgent'=>'Mendesak','critical'=>'Kritis'];
    $avgRating      = number_format($stats['average_rating'] ?? 0, 2);
@endphp

<div class="ratings-page">

    {{-- ═══ HERO ═══ --}}
    <section class="rt-hero">
        <div class="rt-hero-copy">
            <span class="hero-kicker">Insight rating client</span>
            <h1>Rating dari Client</h1>
            <p>Pantau tiket yang sudah diberi rating, temukan penilaian rendah, dan perbaiki kualitas layanan sebelum ada komplain berikutnya.</p>
        </div>
        <div class="rt-score-box">
            <span>Rata-rata Anda</span>
            <div class="rt-score-val">{{ $avgRating }} / 5</div>
            <div class="rt-score-sub">{{ $stats['rated_tickets'] ?? 0 }} tiket sudah dinilai</div>
        </div>
    </section>

    {{-- ═══ WARNING BANNER ═══ --}}
    @if($stats['needs_attention'] ?? false)
    <div class="warn-banner">
        <div class="warn-icon"><i class='bx bx-error-circle'></i></div>
        <div class="warn-copy">
            <strong>{{ ($stats['warning_level'] ?? '') === 'admin' ? 'Teguran admin untuk vendor' : 'Peringatan sistem untuk vendor' }}</strong>
            <p>{{ $stats['warning_message'] ?? 'Ada rating rendah yang perlu segera Anda tindak lanjuti.' }}</p>
        </div>
        <span class="warn-count">{{ $stats['low_rating_count'] ?? 0 }} rating rendah</span>
    </div>
    @endif

    {{-- ═══ WARNING HISTORY ═══ --}}
    @if(isset($stats['warnings']) && count($stats['warnings']) > 0)
    <div class="panel-card">
        <div class="panel-head">
            <div>
                <h5>Riwayat Peringatan</h5>
                <p>Sistem memberi warning otomatis setelah performa buruk memenuhi ambang, lalu admin dapat memberi teguran langsung jika pola buruk berulang.</p>
            </div>
        </div>
        <div class="panel-body">
            <div class="tr-list">
                @foreach($stats['warnings'] as $warning)
                <div class="tr-item {{ ($warning['warning_type'] ?? '') === 'admin' ? 'tr-item--alert' : '' }}">
                    <div class="tr-top">
                        <div>
                            <div class="tr-num">{{ ($warning['warning_type'] ?? '') === 'admin' ? 'Peringatan Admin' : 'Peringatan Sistem' }}</div>
                            <div class="tr-title">{{ $warning['message'] ?? '' }}</div>
                            <div class="tr-meta">{{ \Carbon\Carbon::parse($warning['created_at'])->isoFormat('D MMMM Y, HH:mm') }}</div>
                        </div>
                        <div class="tr-badges">
                            <span class="rb {{ ($warning['warning_type'] ?? '') === 'admin' ? 'rb-alert' : 'rb-pending' }}">
                                {{ ($warning['warning_type'] ?? '') === 'admin' ? 'Admin' : 'Sistem' }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ═══ STATS GRID ═══ --}}
    <div class="stats-grid">
        <div class="st-card">
            <span class="st-label">Tiket Selesai</span>
            <span class="st-value">{{ $stats['completed_tickets'] ?? 0 }}</span>
        </div>
        <div class="st-card st-card--good">
            <span class="st-label">Sudah Dinilai</span>
            <span class="st-value">{{ $stats['rated_tickets'] ?? 0 }}</span>
        </div>
        <div class="st-card">
            <span class="st-label">Menunggu Rating</span>
            <span class="st-value">{{ $stats['pending_ratings'] ?? 0 }}</span>
        </div>
        <div class="st-card st-card--warning">
            <span class="st-label">Rating Rendah</span>
            <span class="st-value">{{ $stats['low_rating_count'] ?? 0 }}</span>
        </div>
        <div class="st-card">
            <span class="st-label">Warning Sistem</span>
            <span class="st-value">{{ $stats['system_warning_count'] ?? 0 }}</span>
        </div>
        <div class="st-card st-card--warning">
            <span class="st-label">Warning Admin</span>
            <span class="st-value">{{ $stats['admin_warning_count'] ?? 0 }}</span>
        </div>
    </div>

    {{-- ═══ FILTER PANEL ═══ --}}
    <div class="panel-card">
        <div class="panel-head">
            <div>
                <h5>Atur Tampilan Rating</h5>
                <p>Filter status rating, atur urutan tiket, dan cari client atau tiket tertentu.</p>
            </div>
            <a href="{{ route('vendor.ratings') }}" class="btn-reset"><i class='bx bx-reset'></i> Reset Filter</a>
        </div>
        <div class="panel-body">
            <form method="GET" action="{{ route('vendor.ratings') }}" class="filter-grid">
                <div class="fg">
                    <label>Status Rating</label>
                    <select name="feedback_status">
                        <option value="">Semua</option>
                        <option value="rated"   {{ request('feedback_status')==='rated'   ? 'selected' : '' }}>Sudah Dinilai</option>
                        <option value="pending" {{ request('feedback_status')==='pending' ? 'selected' : '' }}>Belum Dinilai</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Urutkan</label>
                    <select name="sort">
                        <option value="latest"        {{ request('sort','latest')==='latest'        ? 'selected' : '' }}>Tiket Terbaru</option>
                        <option value="lowest_rating" {{ request('sort')==='lowest_rating' ? 'selected' : '' }}>Rating Terendah</option>
                        <option value="pending_first" {{ request('sort')==='pending_first' ? 'selected' : '' }}>Belum Dinilai Dulu</option>
                        <option value="oldest"        {{ request('sort')==='oldest'        ? 'selected' : '' }}>Tiket Terlama</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari tiket atau nama client...">
                </div>
                <div class="fg" style="align-self:flex-end">
                    <button type="submit" class="btn-reset" style="justify-content:center">
                        <i class='bx bx-search-alt'></i> Cari
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ RATING LIST ═══ --}}
    <div class="panel-card">
        <div class="panel-head">
            <div>
                <h5>Daftar Hasil Rating</h5>
                <p>Tiket dengan rating 1–2 bintang akan diberi penanda agar vendor bisa segera mengevaluasi penanganannya.</p>
            </div>
        </div>
        <div class="panel-body">
            @if($tickets->isEmpty())
                <div class="state-box">
                    <i class='bx bx-star'></i>
                    <p>Belum ada tiket yang cocok dengan filter saat ini.</p>
                </div>
            @else
            <div class="tr-list">
                @foreach($tickets as $t)
                <div class="tr-item {{ $t->feedback && $t->feedback->rating <= 2 ? 'tr-item--alert' : '' }}">
                    <div class="tr-top">
                        <div>
                            <div class="tr-num">{{ $t->ticket_number }}</div>
                            <div class="tr-title">{{ $t->title }}</div>
                            <div class="tr-meta">
                                {{ $t->user->name ?? '-' }} |
                                {{ $t->closed_at ? \Carbon\Carbon::parse($t->closed_at)->format('d M Y H:i') : ($t->resolved_at ? \Carbon\Carbon::parse($t->resolved_at)->format('d M Y H:i') : 'Belum ada tanggal') }}
                            </div>
                        </div>
                        <div class="tr-badges">
                            @if($t->feedback)
                                <span class="rb rb-rated">Sudah Dinilai</span>
                                @if($t->feedback->rating <= 2)
                                    <span class="rb rb-alert">Rating rendah</span>
                                @endif
                            @else
                                <span class="rb rb-pending">Belum Dinilai</span>
                            @endif
                        </div>
                    </div>

                    <div class="tr-meta-pills">
                        <span class="meta-pill">{{ $statusLabels[$t->status] ?? $t->status }}</span>
                        <span class="meta-pill">{{ $t->category->name ?? 'Tanpa kategori' }}</span>
                        <span class="meta-pill badge-pill p-{{ $t->priority }}">{{ $priorityLabels[$t->priority] ?? $t->priority }}</span>
                    </div>

                    @if($t->feedback)
                    <div class="rating-result">
                        <div class="rating-result-head">
                            <div class="stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class='bx bxs-star {{ $i <= $t->feedback->rating ? 'star-on' : 'star-off' }}'></i>
                                @endfor
                            </div>
                            <strong>{{ $t->feedback->rating }}/5</strong>
                        </div>
                        <p class="rating-comment">{{ $t->feedback->comment ?: 'Client tidak menambahkan komentar.' }}</p>
                    </div>
                    @else
                    <div class="rating-pending">Client belum memberikan rating untuk tiket ini.</div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ═══ PAGINATION ═══ --}}
        @if($tickets->lastPage() > 1)
        <div class="pg-wrap">
            <div class="pg-info">Halaman {{ $tickets->currentPage() }} dari {{ $tickets->lastPage() }}</div>
            <div class="pg-controls">
                @if($tickets->onFirstPage())
                    <button class="pg-btn" disabled><i class='bx bx-chevron-left'></i></button>
                @else
                    <a href="{{ $tickets->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="pg-btn"><i class='bx bx-chevron-left'></i></a>
                @endif

                @foreach(range(1, $tickets->lastPage()) as $page)
                    @if($page == $tickets->currentPage())
                        <button class="pg-btn active">{{ $page }}</button>
                    @elseif(abs($page - $tickets->currentPage()) <= 2 || $page == 1 || $page == $tickets->lastPage())
                        <a href="{{ $tickets->url($page) }}&{{ http_build_query(request()->except('page')) }}" class="pg-btn">{{ $page }}</a>
                    @elseif(abs($page - $tickets->currentPage()) == 3)
                        <button class="pg-btn" disabled style="border:none;opacity:.5">…</button>
                    @endif
                @endforeach

                @if($tickets->hasMorePages())
                    <a href="{{ $tickets->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="pg-btn"><i class='bx bx-chevron-right'></i></a>
                @else
                    <button class="pg-btn" disabled><i class='bx bx-chevron-right'></i></button>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>
@endsection