@extends('layouts.app')

@section('title', 'Tiket Ditugaskan')
@section('page_title', 'Tiket Ditugaskan')
@section('breadcrumb', 'Home / Tiket')



@section('content')
@php
    $statusLabels   = ['new'=>'Baru','in_progress'=>'Diproses','waiting_response'=>'Menunggu Respons','assigned'=>'Ditugaskan','resolved'=>'Selesai','closed'=>'Ditutup'];
    $priorityLabels = ['low'=>'Rendah','medium'=>'Sedang','high'=>'Tinggi','urgent'=>'Mendesak','critical'=>'Kritis'];

    function initials($name) {
        if (!$name) return 'NA';
        $parts = array_filter(explode(' ', $name));
        return strtoupper(substr(implode('', array_map(fn($p)=>$p[0], $parts)), 0, 2));
    }

    $dotMap = [
        'new'              => 'td-new',
        'in_progress'      => 'td-in_progress',
        'waiting_response' => 'td-waiting_response',
        'assigned'         => 'td-assigned',
    ];
@endphp

<div class="tickets-page">

    {{-- ‚ïê‚ïê‚ïê HERO ‚ïê‚ïê‚ïê --}}
    <section class="h-hero">
        <div class="h-hero-icon"><i class='bx bx-list-check'></i></div>
        <div>
            <h1>Tiket Ditugaskan</h1>
            <p>Pantau dan kelola tiket aktif yang ditugaskan kepada Anda - lengkap dengan status dan prioritas terkini.</p>
        </div>
    </section>

    {{-- ‚ïê‚ïê‚ïê SUMMARY CARDS ‚ïê‚ïê‚ïê --}}
    <section class="sum-grid">
        <div class="sum-card">
            <div class="sum-icon si-indigo"><i class='bx bx-collection'></i></div>
            <div>
                <div class="sum-label">Total Tiket</div>
                <div class="sum-value">{{ $tickets->total() }}</div>
            </div>
        </div>
        <div class="sum-card">
            <div class="sum-icon si-amber"><i class='bx bx-time'></i></div>
            <div>
                <div class="sum-label">Diproses</div>
                <div class="sum-value">{{ $stats['in_progress'] }}</div>
            </div>
        </div>
        <div class="sum-card">
            <div class="sum-icon si-sky"><i class='bx bx-hourglass'></i></div>
            <div>
                <div class="sum-label">Menunggu</div>
                <div class="sum-value">{{ $stats['waiting'] }}</div>
            </div>
        </div>
        <div class="sum-card">
            <div class="sum-icon si-green"><i class='bx bx-bell'></i></div>
            <div>
                <div class="sum-label">Baru</div>
                <div class="sum-value">{{ $stats['new'] }}</div>
            </div>
        </div>
    </section>

    {{-- ‚ïê‚ïê‚ïê FILTER ‚ïê‚ïê‚ïê --}}
    <div class="filter-card">
        <div class="filter-head"><i class='bx bx-filter-alt'></i> Filter Tiket</div>
        <form method="GET" action="{{ route('vendor.tickets.index') }}" class="filter-body" id="filterForm">
            <div class="fg">
                <label><i class='bx bx-flag'></i> Status</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="">Semua Status Aktif</option>
                    <option value="new"              {{ request('status')==='new'              ?'selected':'' }}>Baru</option>
                    <option value="in_progress"      {{ request('status')==='in_progress'      ?'selected':'' }}>Diproses</option>
                    <option value="waiting_response" {{ request('status')==='waiting_response' ?'selected':'' }}>Menunggu Respons</option>
                </select>
            </div>
            <div class="fg">
                <label><i class='bx bx-sort-alt-2'></i> Prioritas</label>
                <select name="priority" onchange="this.form.submit()">
                    <option value="">Semua Prioritas</option>
                    <option value="low"    {{ request('priority')==='low'    ?'selected':'' }}>Rendah</option>
                    <option value="medium" {{ request('priority')==='medium' ?'selected':'' }}>Sedang</option>
                    <option value="high"   {{ request('priority')==='high'   ?'selected':'' }}>Tinggi</option>
                    <option value="urgent" {{ request('priority')==='urgent' ?'selected':'' }}>Mendesak</option>
                </select>
            </div>
            <div class="fg">
                <label><i class='bx bx-search'></i> Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text"><i class='bx bx-search'></i></span>
                    <input type="text" name="search"
                        placeholder="Cari nomor tiket atau judul..."
                        value="{{ request('search') }}"
                        onkeyup="debounceFilter()">
                </div>
            </div>
            <div class="fg">
                <label><i class='bx bx-layout'></i> Tampilan</label>
                <div class="view-switch">
                    <button type="button" class="vs-btn {{ request('view','list')==='list' ? 'active' : '' }}"
                        onclick="setView('list', this)" title="List"><i class='bx bx-list-ul'></i></button>
                    <button type="button" class="vs-btn {{ request('view')==='grid' ? 'active' : '' }}"
                        onclick="setView('grid', this)" title="Grid"><i class='bx bx-grid-alt'></i></button>
                </div>
                <input type="hidden" name="view" id="viewInput" value="{{ request('view','list') }}">
            </div>
            <div class="fg">
                <label>&nbsp;</label>
                <a href="{{ route('vendor.tickets.index') }}" class="btn-apply">
                    <i class='bx bx-x'></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ‚ïê‚ïê‚ïê TICKETS ‚ïê‚ïê‚ïê --}}
    <div class="tickets-card">
        <div class="tc-header">
            <div class="tc-title">
                <i class='bx bx-task'></i>
                <h5>Daftar Tiket Aktif</h5>
            </div>
            <span class="tc-badge">{{ $tickets->total() }} total</span>
        </div>

        <div class="tc-body">
            @if($tickets->isEmpty())
                <div class="state-box">
                    <i class='bx bx-folder-open'></i>
                    <h5>Tidak ada tiket ditemukan</h5>
                    <p>Coba ubah filter pencarian</p>
                </div>

            @elseif(request('view')==='grid')
                {{-- ‚-Ä‚-Ä GRID VIEW ‚-Ä‚-Ä --}}
                <div class="ticket-grid">
                    @foreach($tickets as $t)
                    <article class="tg-item">
                        <div class="tg-top">
                            <span class="tg-num">{{ $t->ticket_number }}</span>
                            <span class="badge-pill s-{{ $t->status }}">{{ $statusLabels[$t->status] ?? $t->status }}</span>
                        </div>
                        @if($t->latestReassignRequest && $t->latestReassignRequest->status === 'pending')
                            <div style="margin-top:.45rem;">
                                <span class="badge-pill" style="background:#fffbeb;color:#92400e;border:1px solid #fcd34d;">Menunggu Persetujuan Admin</span>
                            </div>
                        @endif
                        <h6 class="tg-title">{{ Str::limit($t->title, 70) }}</h6>
                        <div class="tg-meta">
                            <span><i class='bx bx-user'></i>{{ $t->user->name ?? '-' }}</span>
                            <span><i class='bx bx-category'></i>{{ $t->category->name ?? '-' }}</span>
                            <span><i class='bx bx-time-five'></i>{{ \Carbon\Carbon::parse($t->created_at)->diffForHumans() }}</span>
                        </div>
                        <div class="tg-footer">
                            <span class="badge-pill p-{{ $t->priority }}">{{ $priorityLabels[$t->priority] ?? $t->priority }}</span>
                            <a href="{{ route('vendor.tickets.show', $t->id) }}" class="btn-detail">
                                <i class='bx bx-show'></i> Detail
                            </a>
                        </div>
                    </article>
                    @endforeach
                </div>

            @else
                {{-- ‚-Ä‚-Ä LIST VIEW ‚-Ä‚-Ä --}}
                <div class="ticket-list">
                    @foreach($tickets as $t)
                    <div class="t-item">
                        <div class="t-dot-wrap">
                            <div class="t-dot {{ $dotMap[$t->status] ?? 'td-new' }}"></div>
                        </div>
                        <div class="t-content">
                            <div class="t-top-row">
                                <div>
                                    <div class="t-num">{{ $t->ticket_number }}</div>
                                    <div class="t-title">{{ $t->title }}</div>
                                </div>
                                <div class="t-badges">
                                    <span class="badge-pill p-{{ $t->priority }}">{{ $priorityLabels[$t->priority] ?? $t->priority }}</span>
                                    <span class="badge-pill s-{{ $t->status }}">{{ $statusLabels[$t->status] ?? $t->status }}</span>
                                    @if($t->latestReassignRequest && $t->latestReassignRequest->status === 'pending')
                                        <span class="badge-pill" style="background:#fffbeb;color:#92400e;border:1px solid #fcd34d;">Menunggu Persetujuan Admin</span>
                                    @endif
                                </div>
                            </div>
                            <div class="t-meta-row">
                                <div class="t-meta-group">
                                    <div class="t-meta-item">
                                        <i class='bx bx-user'></i>
                                        <span class="meta-lbl">Klien:</span>
                                        <span class="meta-val">{{ $t->user->name ?? '-' }}</span>
                                    </div>
                                    <div class="t-meta-item">
                                        <i class='bx bx-category'></i>
                                        <span class="meta-lbl">Kategori:</span>
                                        <span class="meta-val">{{ $t->category->name ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="t-meta-group">
                                    <div class="t-meta-item">
                                        <i class='bx bx-calendar'></i>
                                        <span class="meta-lbl">Dibuat:</span>
                                        <span class="meta-val">{{ \Carbon\Carbon::parse($t->created_at)->format('d M Y H:i') }}</span>
                                        <span class="meta-ago">({{ \Carbon\Carbon::parse($t->created_at)->diffForHumans() }})</span>
                                    </div>
                                    @if($t->updated_at && $t->updated_at != $t->created_at)
                                    <div class="t-meta-item">
                                        <i class='bx bx-refresh'></i>
                                        <span class="meta-lbl">Update:</span>
                                        <span class="meta-val">{{ \Carbon\Carbon::parse($t->updated_at)->diffForHumans() }}</span>
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
                Menampilkan {{ $tickets->firstItem() }}--{{ $tickets->lastItem() }} dari {{ $tickets->total() }}
            </div>
            <div class="pg-controls">
                @if($tickets->onFirstPage())
                    <button class="pg-btn" disabled><i class='bx bx-chevron-left'></i></button>
                @else
                    <a href="{{ $tickets->appends(request()->query())->previousPageUrl() }}" class="pg-btn"><i class='bx bx-chevron-left'></i></a>
                @endif

                @foreach($tickets->getUrlRange(1, $tickets->lastPage()) as $page => $url)
                    @if($page == $tickets->currentPage())
                        <button class="pg-btn active">{{ $page }}</button>
                    @elseif(abs($page - $tickets->currentPage()) <= 2 || $page == 1 || $page == $tickets->lastPage())
                        <a href="{{ $tickets->appends(request()->query())->url($page) }}" class="pg-btn">{{ $page }}</a>
                    @elseif(abs($page - $tickets->currentPage()) == 3)
                        <button class="pg-btn" disabled style="border:none;opacity:.5">-¶</button>
                    @endif
                @endforeach

                @if($tickets->hasMorePages())
                    <a href="{{ $tickets->appends(request()->query())->nextPageUrl() }}" class="pg-btn"><i class='bx bx-chevron-right'></i></a>
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
let vendorTicketsSearchTimer = null;
function debounceFilter() {
    clearTimeout(vendorTicketsSearchTimer);
    vendorTicketsSearchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
}

function setView(v, el) {
    document.getElementById('viewInput').value = v;
    document.querySelectorAll('.vs-btn').forEach(b => b.classList.remove('active'));
    if (el) el.classList.add('active');
    localStorage.setItem('vendorTicketView', v);
    document.getElementById('filterForm').submit();
}

// Restore preference on load
const savedView = localStorage.getItem('vendorTicketView');
if (savedView && savedView !== '{{ request("view","list") }}') {
    document.getElementById('viewInput').value = savedView;
}
</script>
@endpush

@push('styles')
<style>
/* ‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê
   VENDOR TICKETS INDEX - tema selaras Riwayat Tiket
‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê */
.tickets-page { display:flex; flex-direction:column; gap:1.5rem; }

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
.sum-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; }
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
.si-amber  { background:#fffbeb; color:#d97706; }
.si-sky    { background:#f0f9ff; color:#0284c7; }
.si-green  { background:#ecfdf5; color:#047857; }
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
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1.25rem;
    align-items:end;
}
.fg label { font-size:.82rem; font-weight:700; color:#334155; margin-bottom:.4rem; display:flex; align-items:center; gap:.35rem; }
.fg input, .fg select {
    width:100%; padding:.75rem 1rem; border:2px solid #e2e8f0;
    border-radius:12px; font-size:.9rem; background:#fff; color:#0f172a;
    transition:all .2s;
}
.fg input:focus, .fg select:focus { outline:none; border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.fg .input-group { display:flex; }
.fg .input-group-text {
    padding:.75rem .9rem; background:#f8fafc; border:2px solid #e2e8f0;
    border-right:0; border-radius:12px 0 0 12px; color:#64748b; font-size:1rem;
    display:flex; align-items:center;
}
.fg .input-group input {
    border-radius:0 12px 12px 0; border-left:0;
}
.btn-apply {
    padding:.78rem 1.35rem; background:#eef2ff;
    border:1px solid rgba(79,70,229,.15); border-radius:12px;
    color:#4338ca; font-weight:700; font-size:.88rem;
    display:inline-flex; align-items:center; gap:.5rem;
    cursor:pointer; transition:all .2s; white-space:nowrap;
    text-decoration:none;
}
.btn-apply:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(79,70,229,.15); color:#4338ca; }

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
.tc-title i { font-size:1.4rem; color:#4338ca; }
.tc-title h5 { font-size:1rem; font-weight:800; color:#0f172a; margin:0; }
.tc-badge {
    padding:.45rem 1.1rem; border-radius:999px;
    background:#eef2ff; color:#4338ca; font-size:.8rem; font-weight:800;
}
.tc-body { padding:1.35rem; min-height:320px; }

/* ‚-Ä‚-Ä LIST VIEW ‚-Ä‚-Ä */
.ticket-list { display:flex; flex-direction:column; gap:1.1rem; }
.t-item {
    display:grid; grid-template-columns:auto 1fr auto;
    gap:1.25rem; padding:1.35rem;
    border:2px solid #e2e8f0; border-radius:16px;
    background:linear-gradient(135deg,#ffffff,#f7fafc);
    transition:all .25s;
}
.t-item:hover { border-color:#6366f1; box-shadow:0 6px 20px rgba(99,102,241,.12); transform:translateY(-2px); }

/* Dot indicator */
.t-dot-wrap { padding-top:.45rem; }
.t-dot {
    width:13px; height:13px; border-radius:50%; position:relative;
}
.t-dot::before {
    content:''; position:absolute; inset:-4px; border-radius:50%;
    border:2px solid currentColor; opacity:.3;
}
.td-new              { background:#f59e0b; color:#f59e0b; }
.td-in_progress      { background:#3b82f6; color:#3b82f6; }
.td-waiting_response { background:#a855f7; color:#a855f7; }
.td-assigned         { background:#6366f1; color:#6366f1; }

.t-content { flex:1; display:flex; flex-direction:column; gap:.85rem; }
.t-top-row { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; }
.t-num   { font-size:.8rem; font-weight:800; color:#6366f1; }
.t-title { font-size:1rem; font-weight:700; color:#0f172a; margin:.15rem 0 0; }
.t-badges { display:flex; gap:.65rem; flex-wrap:wrap; }
.t-meta-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; }
.t-meta-group { display:flex; flex-direction:column; gap:.6rem; }
.t-meta-item {
    display:flex; align-items:center; gap:.4rem;
    font-size:.82rem; color:#475569; flex-wrap:wrap;
}
.t-meta-item i { color:#6366f1; font-size:1rem; }
.meta-lbl { font-weight:700; color:#64748b; }
.meta-val { color:#1e293b; font-weight:600; }
.meta-ago { color:#94a3b8; font-size:.75rem; }

/* ‚-Ä‚-Ä GRID VIEW ‚-Ä‚-Ä */
.ticket-grid {
    display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem;
}
.tg-item {
    border:2px solid #e2e8f0; border-radius:16px; padding:1rem;
    background:linear-gradient(135deg,#ffffff,#f7fafc);
    display:flex; flex-direction:column; gap:.85rem; transition:all .25s;
}
.tg-item:hover { border-color:#6366f1; box-shadow:0 6px 20px rgba(99,102,241,.1); transform:translateY(-2px); }
.tg-top { display:flex; justify-content:space-between; align-items:center; gap:.5rem; }
.tg-num { font-size:.8rem; font-weight:800; color:#6366f1; }
.tg-title { font-size:.95rem; font-weight:700; color:#0f172a; margin:0; }
.tg-meta { display:flex; flex-direction:column; gap:.35rem; font-size:.82rem; color:#64748b; }
.tg-meta span { display:flex; align-items:center; gap:.35rem; }
.tg-footer { display:flex; justify-content:space-between; align-items:center; gap:.75rem; margin-top:auto; }

/* ‚-Ä‚-Ä AVATAR ‚-Ä‚-Ä */
.av-xs {
    width:28px; height:28px; border-radius:50%;
    background:linear-gradient(135deg,#6366f1,#7c3aed);
    color:#fff; display:inline-flex; align-items:center; justify-content:center;
    font-size:.65rem; font-weight:800; flex-shrink:0;
}

/* ‚-Ä‚-Ä BADGES ‚-Ä‚-Ä */
.badge-pill { display:inline-flex; align-items:center; padding:.3rem .7rem; border-radius:999px; font-size:.72rem; font-weight:800; }
.s-new              { background:rgba(245,158,11,.12);  color:#b45309; }
.s-in_progress      { background:rgba(59,130,246,.12);  color:#1d4ed8; }
.s-waiting_response { background:rgba(168,85,247,.12);  color:#7e22ce; }
.s-resolved         { background:rgba(34,197,94,.12);   color:#15803d; }
.s-closed           { background:rgba(148,163,184,.14); color:#475569; }
.s-assigned         { background:rgba(99,102,241,.12);  color:#4338ca; }
.p-low      { background:rgba(107,114,128,.12); color:#374151; }
.p-medium   { background:rgba(59,130,246,.12);  color:#1e40af; }
.p-high     { background:rgba(245,158,11,.12);  color:#92400e; }
.p-urgent,
.p-critical { background:rgba(239,68,68,.12);   color:#991b1b; }

/* ‚-Ä‚-Ä DETAIL BTN ‚-Ä‚-Ä */
.btn-detail {
    padding:.45rem 1rem; border:1.5px solid rgba(99,102,241,.3); border-radius:10px;
    font-size:.78rem; font-weight:700; color:#4f46e5; background:#fff;
    text-decoration:none; white-space:nowrap; transition:all .2s; flex-shrink:0;
    display:inline-flex; align-items:center; gap:.4rem;
}
.btn-detail:hover { background:#eef2ff; color:#4f46e5; transform:translateY(-1px); }

/* ‚-Ä‚-Ä EMPTY / STATE ‚-Ä‚-Ä */
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
.pg-info { font-size:.88rem; color:#475569; }
.pg-controls { display:flex; gap:.4rem; }
.pg-btn {
    min-width:36px; height:36px; padding:0 .5rem;
    background:#fff; border:2px solid #e2e8f0; border-radius:8px;
    color:#475569; font-weight:700; cursor:pointer; transition:all .2s;
    display:flex; align-items:center; justify-content:center;
    text-decoration:none; font-size:.85rem;
}
.pg-btn:not(:disabled):hover { border-color:#6366f1; background:#6366f1; color:#fff; transform:translateY(-1px); }
.pg-btn.active { background:linear-gradient(135deg,#6366f1,#4f46e5); border-color:transparent; color:#fff; }
.pg-btn:disabled { opacity:.4; cursor:not-allowed; pointer-events:none; }

/* ‚-Ä‚-Ä RESPONSIVE ‚-Ä‚-Ä */
@media(max-width:1100px){ .ticket-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:768px){
    .sum-grid { grid-template-columns:repeat(2,1fr); }
    .ticket-grid { grid-template-columns:1fr; }
    .t-item { grid-template-columns:1fr; }
    .t-dot-wrap { display:none; }
    .t-top-row { flex-direction:column; }
    .t-meta-row { grid-template-columns:1fr; }
    .tc-footer { flex-direction:column; align-items:stretch; }
    .pg-info, .pg-controls { justify-content:center; }
    .filter-body { grid-template-columns:1fr; }
}
</style>
@endpush



