@extends('layouts.app')

@section('title', 'Riwayat Tiket')
@section('page_title', 'Riwayat Tiket')
@section('breadcrumb', 'Home / Riwayat')



@section('content')
@php
    $statusLabels   = ['new'=>'Baru','in_progress'=>'Diproses','waiting_response'=>'Menunggu','resolved'=>'Selesai','closed'=>'Ditutup'];
    $priorityLabels = ['low'=>'Rendah','medium'=>'Sedang','high'=>'Tinggi','urgent'=>'Mendesak','critical'=>'Kritis'];
@endphp

<div class="history-page">

    {{-- ‚ïê‚ïê‚ïê HERO ‚ïê‚ïê‚ïê --}}
    <section class="h-hero">
        <div class="h-hero-icon"><i class='bx bx-history'></i></div>
        <div>
            <h1>Riwayat Tiket</h1>
            <p>Lihat tiket yang sudah selesai dan ditutup - lengkap dengan detail SLA dan waktu penyelesaian.</p>
        </div>
    </section>

    {{-- ‚ïê‚ïê‚ïê SUMMARY ‚ïê‚ïê‚ïê --}}
    <section class="sum-grid">
        <div class="sum-card">
            <div class="sum-icon si-indigo"><i class='bx bx-collection'></i></div>
            <div>
                <div class="sum-label">Total Riwayat</div>
                <div class="sum-value">{{ $summary['total'] }}</div>
            </div>
        </div>
        <div class="sum-card">
            <div class="sum-icon si-green"><i class='bx bx-check-circle'></i></div>
            <div>
                <div class="sum-label">Tiket Selesai</div>
                <div class="sum-value">{{ $summary['resolved'] }}</div>
            </div>
        </div>
        <div class="sum-card">
            <div class="sum-icon si-slate"><i class='bx bx-lock-alt'></i></div>
            <div>
                <div class="sum-label">Tiket Ditutup</div>
                <div class="sum-value">{{ $summary['closed'] }}</div>
            </div>
        </div>
    </section>

    {{-- ‚ïê‚ïê‚ïê FILTER ‚ïê‚ïê‚ïê --}}
    <div class="filter-card">
        <div class="filter-head"><i class='bx bx-filter-alt'></i> Filter Riwayat</div>
        <form method="GET" action="{{ route('vendor.history') }}" class="filter-body">
            <div class="fg">
                <label><i class='bx bx-calendar'></i> Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}">
            </div>
            <div class="fg">
                <label><i class='bx bx-calendar-check'></i> Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}">
            </div>
            <div class="fg">
                <label><i class='bx bx-flag'></i> Prioritas</label>
                <select name="priority">
                    <option value="">Semua Prioritas</option>
                    @foreach(['low'=>'Rendah','medium'=>'Sedang','high'=>'Tinggi','critical'=>'Kritis'] as $val => $lbl)
                        <option value="{{ $val }}" {{ request('priority')===$val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fg" style="align-self:flex-end">
                <label>&nbsp;</label>
                <button type="submit" class="btn-apply">
                    <i class='bx bx-search-alt'></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    {{-- ‚ïê‚ïê‚ïê TICKETS ‚ïê‚ïê‚ïê --}}
    <div class="tickets-card">
        <div class="tc-header">
            <div class="tc-title">
                <i class='bx bx-check-double'></i>
                <h5>Tiket Selesai &amp; Ditutup</h5>
            </div>
            <span class="tc-badge">{{ $tickets->total() }} total</span>
        </div>

        <div class="tc-body">
            @if($tickets->isEmpty())
                <div class="state-box">
                    <i class='bx bx-folder-open'></i>
                    <h5>Belum ada riwayat tiket</h5>
                    <p>Tiket yang telah selesai akan muncul di sini</p>
                </div>
            @elseif(request('view')==='grid')
                {{-- GRID VIEW --}}
                <div class="history-grid">
                    @foreach($tickets as $t)
                    <article class="hg-item">
                        <div class="hg-top">
                            <span class="hg-num">{{ $t->ticket_number }}</span>
                            <span class="badge-pill s-{{ $t->status }}">{{ $statusLabels[$t->status] ?? $t->status }}</span>
                        </div>
                        <h6 class="hg-title">{{ Str::limit($t->title, 70) }}</h6>
                        <div class="hg-meta">
                            <span><i class='bx bx-user'></i>{{ $t->user->name ?? '-' }}</span>
                            <span><i class='bx bx-category'></i>{{ $t->category->name ?? '-' }}</span>
                            @if($t->resolved_at)
                            <span><i class='bx bx-calendar-check'></i>{{ \Carbon\Carbon::parse($t->resolved_at)->format('d M Y') }}</span>
                            @endif
                        </div>
                        <div class="hg-footer">
                            <span class="badge-pill p-{{ $t->priority }}">{{ $priorityLabels[$t->priority] ?? $t->priority }}</span>
                            <a href="{{ route('vendor.tickets.show', $t->id) }}" class="btn-detail"><i class='bx bx-show'></i> Detail</a>
                        </div>
                    </article>
                    @endforeach
                </div>
            @else
                {{-- LIST VIEW --}}
                <div class="history-list">
                    @foreach($tickets as $t)
                    <div class="h-item">
                        <div class="h-dot-wrap">
                            <div class="h-dot hd-{{ $t->status }}"></div>
                        </div>
                        <div class="h-content">
                            <div class="h-top-row">
                                <div>
                                    <div class="h-num">{{ $t->ticket_number }}</div>
                                    <div class="h-title">{{ $t->title }}</div>
                                </div>
                                <div class="h-badges">
                                    <span class="badge-pill p-{{ $t->priority }}">{{ $priorityLabels[$t->priority] ?? $t->priority }}</span>
                                    <span class="badge-pill s-{{ $t->status }}">{{ $statusLabels[$t->status] ?? $t->status }}</span>
                                </div>
                            </div>
                            <div class="h-meta-row">
                                <div class="h-meta-group">
                                    <div class="h-meta-item">
                                        <i class='bx bx-user'></i>
                                        <span class="meta-lbl">Klien:</span>
                                        <span class="meta-val">{{ $t->user->name ?? '-' }}</span>
                                    </div>
                                    <div class="h-meta-item">
                                        <i class='bx bx-category'></i>
                                        <span class="meta-lbl">Kategori:</span>
                                        <span class="meta-val">{{ $t->category->name ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="h-meta-group">
                                    @if($t->resolved_at)
                                    <div class="h-meta-item">
                                        <i class='bx bx-calendar-check'></i>
                                        <span class="meta-lbl">Selesai:</span>
                                        <span class="meta-val">{{ \Carbon\Carbon::parse($t->resolved_at)->format('d M Y H:i') }}</span>
                                        <span class="meta-ago">({{ \Carbon\Carbon::parse($t->resolved_at)->diffForHumans() }})</span>
                                    </div>
                                    @endif
                                    @if($t->slaTracking && $t->slaTracking->actual_resolution_time)
                                    <div class="h-meta-item">
                                        <i class='bx bx-timer'></i>
                                        <span class="meta-lbl">Waktu Penyelesaian:</span>
                                        <span class="meta-val">{{ $t->slaTracking->actual_resolution_time }} mnt</span>
                                        @if($t->slaTracking->resolution_sla_met)
                                            <span class="sla-met"><i class='bx bx-check-circle'></i>SLA Tercapai</span>
                                        @else
                                            <span class="sla-missed"><i class='bx bx-x-circle'></i>SLA Terlewati</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;padding-top:.5rem">
                            <a href="{{ route('vendor.tickets.show', $t->id) }}" class="btn-detail">
                                <i class='bx bx-show'></i> Detail
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ‚ïê‚ïê‚ïê PAGINATION ‚ïê‚ïê‚ïê --}}
        @if($tickets->lastPage() > 1)
        <div class="tc-footer">
            <div class="pg-info">
                <span>Menampilkan {{ $tickets->firstItem() }}--{{ $tickets->lastItem() }} dari {{ $tickets->total() }}</span>
            </div>
            <div class="pg-controls">
                @if($tickets->onFirstPage())
                    <button class="pg-btn" disabled><i class='bx bx-chevron-left'></i></button>
                @else
                    <a href="{{ $tickets->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="pg-btn"><i class='bx bx-chevron-left'></i></a>
                @endif

                @foreach($tickets->getUrlRange(1, $tickets->lastPage()) as $page => $url)
                    @if($page == $tickets->currentPage())
                        <button class="pg-btn active">{{ $page }}</button>
                    @elseif(abs($page - $tickets->currentPage()) <= 2 || $page == 1 || $page == $tickets->lastPage())
                        <a href="{{ $url }}&{{ http_build_query(request()->except('page')) }}" class="pg-btn">{{ $page }}</a>
                    @elseif(abs($page - $tickets->currentPage()) == 3)
                        <button class="pg-btn" disabled style="border:none;opacity:.5">-¶</button>
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

@push('scripts')
<script>
function setView(v) {
    document.getElementById('viewInput').value = v;
    document.querySelectorAll('.vs-btn').forEach(b => b.classList.remove('active'));
    event.currentTarget.classList.add('active');
}
</script>
@endpush

@push('styles')
<style>
/* ‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê
   VENDOR HISTORY - BLADE
‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê */
.history-page { display:flex; flex-direction:column; gap:1.5rem; }

/* ‚-Ä‚-Ä HERO ‚-Ä‚-Ä */
.h-hero {
    display:grid; grid-template-columns:auto 1fr; gap:1.25rem;
    align-items:center; padding:1.5rem; border-radius:28px;
    background:linear-gradient(135deg,#eef2ff 0%,#fff 55%,#f0fdf4 100%);
    border:1px solid rgba(99,102,241,.1);
    box-shadow:0 18px 40px rgba(15,23,42,.05);
}
.h-hero-icon {
    width:60px; height:60px; border-radius:18px;
    background:linear-gradient(135deg,#eef2ff,#dbeafe);
    display:flex; align-items:center; justify-content:center;
    color:#4338ca; font-size:1.75rem;
    box-shadow:0 10px 20px rgba(79,70,229,.12);
}
.h-hero h1 { margin:.35rem 0 .25rem; font-size:clamp(1.5rem,2.5vw,2rem); font-weight:800; color:#0f172a; }
.h-hero p  { margin:0; color:#64748b; }

/* ‚-Ä‚-Ä SUMMARY CARDS ‚-Ä‚-Ä */
.sum-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem; }
.sum-card {
    display:flex; align-items:center; gap:.9rem;
    padding:1rem 1.1rem; border-radius:18px;
    background:#fff; border:1px solid rgba(99,102,241,.1);
    box-shadow:0 12px 24px rgba(15,23,42,.05);
    transition:all .25s;
}
.sum-card:hover { transform:translateY(-2px); box-shadow:0 18px 36px rgba(79,70,229,.08); }
.sum-icon {
    width:44px; height:44px; border-radius:12px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:1.1rem;
}
.si-indigo { background:#eef2ff; color:#4338ca; }
.si-green  { background:#ecfdf5; color:#047857; }
.si-slate  { background:#f1f5f9; color:#334155; }
.sum-label { font-size:.78rem; color:#64748b; }
.sum-value { font-size:1.2rem; font-weight:800; color:#0f172a; margin:.1rem 0 0; }

/* ‚-Ä‚-Ä FILTER CARD ‚-Ä‚-Ä */
.filter-card {
    background:#fff; border-radius:18px;
    border:1px solid rgba(99,102,241,.1);
    box-shadow:0 12px 28px rgba(15,23,42,.05); overflow:hidden;
}
.filter-head {
    display:flex; align-items:center; gap:.65rem;
    padding:1rem 1.35rem;
    border-bottom:1px solid rgba(148,163,184,.1);
    background:linear-gradient(180deg,rgba(248,250,252,.88),rgba(255,255,255,.98));
    font-size:.95rem; font-weight:800; color:#0f172a;
}
.filter-body {
    padding:1.35rem; display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1.25rem;
}
.fg label { font-size:.82rem; font-weight:700; color:#334155; margin-bottom:.4rem; display:flex; align-items:center; gap:.35rem; }
.fg input, .fg select {
    width:100%; padding:.75rem 1rem; border:2px solid #e2e8f0;
    border-radius:12px; font-size:.9rem; background:#fff; color:#0f172a;
    transition:all .2s;
}
.fg input:focus, .fg select:focus { outline:none; border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.btn-apply {
    padding:.78rem 1.35rem; background:#eef2ff;
    border:1px solid rgba(79,70,229,.15); border-radius:12px;
    color:#4338ca; font-weight:700; font-size:.88rem;
    display:inline-flex; align-items:center; gap:.5rem;
    cursor:pointer; transition:all .2s; white-space:nowrap;
}
.btn-apply:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(79,70,229,.15); }

/* ‚-Ä‚-Ä VIEW SWITCH ‚-Ä‚-Ä */
.view-switch {
    display:inline-flex; background:#f8fafc; border-radius:12px;
    padding:.2rem; border:1px solid rgba(148,163,184,.18);
}
.vs-btn {
    width:40px; height:40px; border:0; border-radius:10px;
    background:transparent; color:#64748b; cursor:pointer; transition:all .2s;
    display:flex; align-items:center; justify-content:center; font-size:1rem;
}
.vs-btn.active { background:#fff; color:#0f172a; box-shadow:0 4px 12px rgba(15,23,42,.08); }

/* ‚-Ä‚-Ä TICKETS CARD ‚-Ä‚-Ä */
.tickets-card {
    background:#fff; border-radius:22px;
    border:1px solid rgba(99,102,241,.1);
    box-shadow:0 18px 40px rgba(15,23,42,.05); overflow:hidden;
}
.tc-header {
    display:flex; justify-content:space-between; align-items:center;
    padding:1.15rem 1.35rem;
    border-bottom:1px solid rgba(148,163,184,.1);
    background:linear-gradient(180deg,rgba(248,250,252,.88),rgba(255,255,255,.98));
}
.tc-title { display:flex; align-items:center; gap:.7rem; }
.tc-title i { font-size:1.4rem; color:#0f766e; }
.tc-title h5 { font-size:1rem; font-weight:800; color:#0f172a; margin:0; }
.tc-badge {
    padding:.45rem 1.1rem; border-radius:999px;
    background:#eef2ff; color:#4338ca; font-size:.8rem; font-weight:800;
}
.tc-body { padding:1.35rem; min-height:320px; }

/* ‚-Ä‚-Ä LIST VIEW ‚-Ä‚-Ä */
.history-list { display:flex; flex-direction:column; gap:1.1rem; }
.h-item {
    display:grid; grid-template-columns:auto 1fr auto;
    gap:1.25rem; padding:1.35rem;
    border:2px solid #e2e8f0; border-radius:16px;
    background:linear-gradient(135deg,#ffffff,#f7fafc);
    transition:all .25s;
}
.h-item:hover { border-color:#6366f1; box-shadow:0 6px 20px rgba(99,102,241,.12); transform:translateY(-2px); }
.h-dot-wrap { padding-top:.45rem; }
.h-dot {
    width:13px; height:13px; border-radius:50%; position:relative;
}
.h-dot::before {
    content:''; position:absolute; inset:-4px; border-radius:50%;
    border:2px solid currentColor; opacity:.3;
}
.hd-resolved { background:#10b981; color:#10b981; }
.hd-closed   { background:#6b7280; color:#6b7280; }
.h-content   { flex:1; display:flex; flex-direction:column; gap:.85rem; }
.h-top-row   { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; }
.h-num  { font-size:.8rem; font-weight:800; color:#6366f1; }
.h-title { font-size:1rem; font-weight:700; color:#0f172a; margin:.15rem 0 0; }
.h-badges { display:flex; gap:.65rem; flex-wrap:wrap; }
.h-meta-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:1rem; }
.h-meta-group { display:flex; flex-direction:column; gap:.6rem; }
.h-meta-item {
    display:flex; align-items:center; gap:.4rem;
    font-size:.82rem; color:#475569; flex-wrap:wrap;
}
.h-meta-item i { color:#6366f1; font-size:1rem; }
.meta-lbl { font-weight:700; color:#64748b; }
.meta-val { color:#1e293b; font-weight:600; }
.meta-ago { color:#94a3b8; font-size:.75rem; }

/* ‚-Ä‚-Ä GRID VIEW ‚-Ä‚-Ä */
.history-grid {
    display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem;
}
.hg-item {
    border:2px solid #e2e8f0; border-radius:16px; padding:1rem;
    background:linear-gradient(135deg,#ffffff,#f7fafc);
    display:flex; flex-direction:column; gap:.85rem; transition:all .25s;
}
.hg-item:hover { border-color:#6366f1; box-shadow:0 6px 20px rgba(99,102,241,.1); transform:translateY(-2px); }
.hg-top { display:flex; justify-content:space-between; align-items:center; gap:.5rem; }
.hg-num { font-size:.8rem; font-weight:800; color:#6366f1; }
.hg-title { font-size:.95rem; font-weight:700; color:#0f172a; margin:0; }
.hg-meta { display:flex; flex-direction:column; gap:.35rem; font-size:.82rem; color:#64748b; }
.hg-meta span { display:flex; align-items:center; gap:.35rem; }
.hg-footer { display:flex; justify-content:space-between; align-items:center; gap:.75rem; margin-top:auto; }

/* ‚-Ä‚-Ä SLA INDICATOR ‚-Ä‚-Ä */
.sla-met    { background:#d1fae5; color:#065f46; display:inline-flex; align-items:center; gap:.25rem; padding:.25rem .65rem; border-radius:6px; font-size:.72rem; font-weight:700; }
.sla-missed { background:#fee2e2; color:#991b1b; display:inline-flex; align-items:center; gap:.25rem; padding:.25rem .65rem; border-radius:6px; font-size:.72rem; font-weight:700; }

/* ‚-Ä‚-Ä BADGES ‚-Ä‚-Ä */
.badge-pill { display:inline-flex; align-items:center; padding:.3rem .7rem; border-radius:999px; font-size:.72rem; font-weight:800; }
.p-low      { background:rgba(107,114,128,.12); color:#374151; }
.p-medium   { background:rgba(59,130,246,.12);  color:#1e40af; }
.p-high     { background:rgba(245,158,11,.12);  color:#92400e; }
.p-critical,.p-urgent { background:rgba(239,68,68,.12); color:#991b1b; }
.s-resolved { background:#d1fae5; color:#065f46; }
.s-closed   { background:#f3f4f6; color:#374151; }

/* ‚-Ä‚-Ä DETAIL BTN ‚-Ä‚-Ä */
.btn-detail {
    padding:.45rem 1rem; border:1.5px solid rgba(99,102,241,.3); border-radius:10px;
    font-size:.78rem; font-weight:700; color:#4f46e5; background:#fff;
    text-decoration:none; white-space:nowrap; transition:all .2s; flex-shrink:0;
    display:inline-flex; align-items:center; gap:.4rem;
}
.btn-detail:hover { background:#eef2ff; color:#4f46e5; transform:translateY(-1px); }

/* ‚-Ä‚-Ä EMPTY / LOADING ‚-Ä‚-Ä */
.state-box { display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:260px; gap:.9rem; text-align:center; color:#64748b; }
.state-box i { font-size:3rem; color:#cbd5e0; }
.state-box h5 { color:#475569; font-weight:700; margin:0; }
.state-box p  { margin:0; color:#94a3b8; }

/* ‚-Ä‚-Ä PAGINATION ‚-Ä‚-Ä */
.tc-footer {
    padding:1.1rem 1.35rem;
    background:#f8fafc; border-top:1px solid #e2e8f0;
    display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;
}
.pg-info { display:flex; align-items:center; gap:.75rem; font-size:.88rem; color:#475569; }
.pg-info select { padding:.4rem .65rem; border:2px solid #e2e8f0; border-radius:8px; font-size:.85rem; }
.pg-controls { display:flex; gap:.4rem; }
.pg-btn {
    min-width:36px; height:36px; padding:0 .5rem;
    background:#fff; border:2px solid #e2e8f0; border-radius:8px;
    color:#475569; font-weight:700; cursor:pointer; transition:all .2s;
    display:flex; align-items:center; justify-content:center;
}
.pg-btn:not(:disabled):hover { border-color:#6366f1; background:#6366f1; color:#fff; transform:translateY(-1px); }
.pg-btn.active { background:linear-gradient(135deg,#6366f1,#4f46e5); border-color:transparent; color:#fff; }
.pg-btn:disabled { opacity:.4; cursor:not-allowed; }

/* ‚-Ä‚-Ä RESPONSIVE ‚-Ä‚-Ä */
@media(max-width:1100px){ .history-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:768px){
    .sum-grid { grid-template-columns:1fr; }
    .history-grid { grid-template-columns:1fr; }
    .h-item { grid-template-columns:1fr; }
    .h-dot-wrap { display:none; }
    .h-top-row { flex-direction:column; }
    .h-meta-row { grid-template-columns:1fr; }
    .tc-footer { flex-direction:column; align-items:stretch; }
    .pg-info, .pg-controls { justify-content:center; }
}
</style>
@endpush



