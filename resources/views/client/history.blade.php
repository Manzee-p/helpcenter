@extends('layouts.client')

@section('title', 'Riwayat Laporan')
@section('page_title', 'Riwayat Laporan')
@section('breadcrumb', 'Home / Riwayat')

@section('content')
<div class="history-page">

    {{-- PAGE HEADER --}}
    <div class="page-header-card">
        <div>
            <h1 class="page-header-title">Riwayat Laporan</h1>
            <p class="page-header-sub">Lihat laporan yang sudah selesai, yang sudah diberi rating, dan pelayanan yang masih menunggu penilaian Anda.</p>
        </div>
        <a href="{{ route('client.tickets.create') }}" class="btn-create">
            <i class='bx bx-plus-circle'></i> Buat Laporan
        </a>
    </div>

    {{-- PENDING HIGHLIGHT --}}
    @if($pendingFeedbackItems->count())
    <div class="pending-highlight">
        <div class="highlight-head">
            <span class="highlight-chip">Butuh Penilaian</span>
            <h2>{{ $pendingFeedbackItems->count() }} pelayanan selesai belum Anda nilai</h2>
        </div>
        <div class="highlight-grid">
            @foreach($pendingFeedbackItems->take(3) as $item)
            <a href="#" class="highlight-item" onclick="openFeedback({{ $item->id }}, '{{ addslashes($item->title) }}'); return false;">
                <strong>#{{ $item->ticket_number }}</strong>
                <span>{{ $item->title }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- FILTERS --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('client.history') }}" id="filterForm">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Semua Status</option>
                        <option value="new"              {{ request('status') === 'new'              ? 'selected' : '' }}>Baru</option>
                        <option value="in_progress"      {{ request('status') === 'in_progress'      ? 'selected' : '' }}>Dalam Proses</option>
                        <option value="resolved"         {{ request('status') === 'resolved'         ? 'selected' : '' }}>Resolved</option>
                        <option value="closed"           {{ request('status') === 'closed'           ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="filter-group grow">
                    <label class="filter-label">Search</label>
                    <div class="search-wrap">
                        <i class='bx bx-search'></i>
                        <input type="text" name="search" class="search-input"
                            placeholder="Cari judul, nomor, atau deskripsi laporan..."
                            value="{{ request('search') }}"
                            onkeyup="debounceSubmit()">
                    </div>
                </div>
                <a href="{{ route('client.history') }}" class="btn-reset">
                    <i class='bx bx-refresh'></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ITEMS --}}
    @if($tickets->isEmpty())
        <div class="state-card">
            <div class="state-icon"><i class='bx bx-inbox'></i></div>
            <h3 class="state-title">Riwayat laporan belum tersedia</h3>
            <p class="state-text">
                @if(request('search') || request('status'))
                    Coba ubah filter pencarian Anda.
                @else
                    Buat laporan pertama untuk mulai meminta bantuan.
                @endif
            </p>
            @unless(request('search') || request('status'))
                <a href="{{ route('client.tickets.create') }}" class="btn-create" style="display:inline-flex;">
                    <i class='bx bx-plus-circle'></i> Buat Laporan
                </a>
            @endunless
        </div>
    @else
        <div class="items-list">
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
                $desc = $ticket->description;
                $shortDesc = mb_strlen($desc) > 150 ? mb_substr($desc, 0, 150) . '-�' : $desc;
            @endphp
            <a href="{{ route('client.tickets.show', $ticket->id) }}" class="item-card">
                <div class="item-main">
                    <div class="item-head">
                        <div class="item-num">#{{ $ticket->ticket_number }}</div>
                        <span class="status-badge status-{{ $ticket->status }}">
                            {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                        </span>
                    </div>

                    <div class="item-title">{{ $ticket->title }}</div>
                    <div class="item-desc">{{ $shortDesc }}</div>

                    <div class="item-meta">
                        <div class="item-meta-chip">
                            <i class='bx bx-category'></i>
                            <span>{{ $ticket->category->name ?? 'N/A' }}</span>
                        </div>
                        @if($ticket->priority)
                        <div class="item-meta-chip">
                            <i class='bx bx-flag'></i>
                            <span class="priority-{{ $ticket->priority }}">{{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}</span>
                        </div>
                        @endif
                        <div class="item-meta-chip">
                            <i class='bx bx-time'></i>
                            <span>{{ \Carbon\Carbon::parse($ticket->created_at)->locale('id')->diffForHumans() }}</span>
                        </div>
                    </div>

                    @if($ticket->assignedVendor)
                    <div class="item-assignee">
                        <div class="assignee-avatar">
                            {{ strtoupper(substr($ticket->assignedVendor->name, 0, 2)) }}
                        </div>
                        <span class="assignee-text">Ditangani <strong>{{ $ticket->assignedVendor->name }}</strong></span>
                    </div>
                    @endif

                    @if(in_array($ticket->status, ['resolved','closed']))
                    <div class="item-footer" onclick="event.preventDefault();">
                        @if($ticket->feedback)
                            <span class="badge-rated">
                                <i class='bx bx-star'></i>
                                Sudah dinilai {{ $ticket->feedback->rating }}/5
                            </span>
                        @else
                            <button class="btn-rate" onclick="openFeedback({{ $ticket->id }}, '{{ addslashes($ticket->title) }}')">
                                <i class='bx bx-comment'></i> Beri Rating
                            </button>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="item-arrow"><i class='bx bx-chevron-right'></i></div>
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

{{-- FEEDBACK MODAL --}}
<div class="modal-overlay" id="feedbackModal">
    <div class="modal-box">
        <div class="modal-title">Beri Rating Layanan</div>
        <div class="modal-sub" id="feedbackTicketTitle">-</div>

        <form method="POST" id="feedbackForm">
            @csrf
            <input type="hidden" name="rating" id="ratingInput" value="0">

            <div class="star-picker" id="starPicker">
                @for($s = 1; $s <= 5; $s++)
                    <button type="button" class="star-btn" data-val="{{ $s }}" onclick="selectStar({{ $s }})"><i class='bx bxs-star'></i></button>
                @endfor
            </div>

            <label class="modal-label">Komentar (opsional)</label>
            <textarea name="comment" class="modal-textarea" placeholder="Ceritakan pengalaman Anda dengan vendor ini..."></textarea>

            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="closeFeedback()">Batal</button>
                <button type="submit" class="btn-modal-submit">Kirim Rating</button>
            </div>
        </form>
    </div>
</div>

<script>
let searchTimeout = null;
function debounceSubmit() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { document.getElementById('filterForm').submit(); }, 500);
}

function openFeedback(ticketId, title) {
    document.getElementById('feedbackTicketTitle').textContent = title;
    document.getElementById('feedbackForm').action = `/client/tickets/${ticketId}/feedback`;
    document.getElementById('ratingInput').value = 0;
    document.querySelectorAll('.star-btn').forEach(b => b.classList.remove('selected'));
    document.getElementById('feedbackModal').classList.add('open');
}

function closeFeedback() {
    document.getElementById('feedbackModal').classList.remove('open');
}

function selectStar(val) {
    document.getElementById('ratingInput').value = val;
    document.querySelectorAll('.star-btn').forEach(b => {
        b.classList.toggle('selected', parseInt(b.dataset.val) <= val);
    });
}

// Close modal on overlay click
document.getElementById('feedbackModal').addEventListener('click', function(e) {
    if (e.target === this) closeFeedback();
});
</script>

<style>
.history-page { display: flex; flex-direction: column; gap: 1.25rem; }

/*  PAGE HEADER  */
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

/*  PENDING HIGHLIGHT  */
.pending-highlight {
    background: white;
    border: 1px solid rgba(245,158,11,.2);
    border-radius: 24px;
    padding: 1.35rem;
    box-shadow: 0 18px 36px rgba(15,23,42,.05);
}
.highlight-head { margin-bottom: 1rem; }
.highlight-chip {
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
.highlight-head h2 { margin: .6rem 0 0; font-size: 1.15rem; color: #1f2937; }
.highlight-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 1rem;
}
.highlight-item {
    display: flex;
    flex-direction: column;
    gap: .3rem;
    padding: 1rem 1.1rem;
    border-radius: 18px;
    border: 1px solid rgba(245,158,11,.18);
    background: linear-gradient(135deg,#fffaf0,#fff);
    cursor: pointer;
    transition: all .2s;
    text-align: left;
    text-decoration: none;
}
.highlight-item:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(245,158,11,.12); }
.highlight-item strong { color: #b7791f; font-weight: 800; }
.highlight-item span   { color: #334155; font-size: .9rem; }

/*  FILTERS  */
.filter-card {
    background: white;
    border: 1px solid rgba(148,163,184,.12);
    border-radius: 24px;
    padding: 1.5rem;
    box-shadow: 0 18px 36px rgba(15,23,42,.05);
}
.filter-row {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
}
.filter-group { display: flex; flex-direction: column; gap: .4rem; }
.filter-group.grow { flex: 1; }
.filter-label { font-size: .875rem; font-weight: 600; color: #495057; }
.filter-select {
    padding: .75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: .9375rem;
    color: #495057;
    background: white;
    min-width: 180px;
    cursor: pointer;
    transition: all .2s;
}
.filter-select:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
.search-wrap { position: relative; }
.search-wrap i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-size: 1.1rem; color: #6c757d; }
.search-input {
    width: 100%;
    padding: .75rem 1rem .75rem 2.75rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: .9375rem;
    transition: all .2s;
}
.search-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
.btn-reset {
    display: flex;
    align-items: center;
    gap: .4rem;
    padding: .75rem 1.25rem;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    color: #6c757d;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
    white-space: nowrap;
}
.btn-reset:hover { border-color: #6366f1; color: #6366f1; }

/*  ITEMS LIST  */
.items-list { display: flex; flex-direction: column; gap: 1rem; }

.item-card {
    display: flex;
    gap: 1.25rem;
    background: white;
    border: 1px solid rgba(148,163,184,.12);
    border-radius: 24px;
    padding: 1.5rem;
    box-shadow: 0 18px 36px rgba(15,23,42,.05);
    cursor: pointer;
    transition: all .25s;
    text-decoration: none;
    color: inherit;
}
.item-card:hover { transform: translateY(-3px); box-shadow: 0 24px 40px rgba(15,23,42,.08); color: inherit; }
.item-main { flex: 1; }
.item-arrow { display: flex; align-items: center; font-size: 1.5rem; color: #dee2e6; transition: all .3s; }
.item-card:hover .item-arrow { color: #6366f1; transform: translateX(4px); }

.item-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: .65rem; }
.item-num  { font-size: .875rem; font-weight: 800; color: #4f46e5; }

.item-title { font-size: 1.1rem; font-weight: 800; color: #1f2937; margin-bottom: .45rem; line-height: 1.4; }
.item-desc  { font-size: .9rem; color: #6c757d; line-height: 1.6; margin-bottom: .85rem; }

.item-meta { display: flex; gap: 1.25rem; flex-wrap: wrap; margin-bottom: .85rem; }
.item-meta-chip {
    display: flex;
    align-items: center;
    gap: .35rem;
    font-size: .875rem;
    color: #6c757d;
}
.item-meta-chip i { font-size: 1rem; }
.priority-low    { color: #15803d; }
.priority-medium { color: #a16207; }
.priority-high   { color: #c2410c; }
.priority-urgent { color: #b91c1c; font-weight: 600; }

.item-assignee {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding-top: .85rem;
    border-top: 1px solid #f0f0f0;
    margin-bottom: .75rem;
}
.assignee-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg,#6366f1,#7c3aed);
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: .72rem; font-weight: 800;
    flex-shrink: 0;
}
.assignee-text { font-size: .875rem; color: #6c757d; }
.assignee-text strong { color: #495057; }

.item-footer { padding-top: .75rem; border-top: 1px solid #f0f0f0; }
.badge-rated {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .9rem;
    background: #d4edda;
    color: #155724;
    border-radius: 10px;
    font-size: .875rem;
    font-weight: 700;
}
.btn-rate {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .9rem;
    background: white;
    border: 2px solid #6366f1;
    border-radius: 10px;
    color: #6366f1;
    font-weight: 700;
    font-size: .875rem;
    cursor: pointer;
    transition: all .2s;
    text-decoration: none;
}
.btn-rate:hover { background: #6366f1; color: white; }

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

/*  STATE CARDS  */
.state-card {
    background: white;
    border: 1px solid rgba(148,163,184,.12);
    border-radius: 24px;
    padding: 4rem 2rem;
    text-align: center;
    box-shadow: 0 18px 36px rgba(15,23,42,.05);
}
.state-icon  { font-size: 5rem; color: #dee2e6; margin-bottom: 1.25rem; }
.state-title { font-size: 1.45rem; font-weight: 700; color: #2c3e50; margin-bottom: .75rem; }
.state-text  { font-size: .95rem; color: #6c757d; margin-bottom: 1.5rem; }

/*  PAGINATION  */
.pagination-wrap {
    display: flex;
    justify-content: center;
    gap: .5rem;
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
    text-decoration: none;
    transition: all .2s;
}
.page-btn:hover { border-color: #6366f1; color: #6366f1; }
.page-btn.active { background: linear-gradient(135deg,#6366f1,#7c3aed); color: white; border-color: transparent; }
.page-btn:disabled { opacity: .5; cursor: not-allowed; }

/*  FEEDBACK MODAL  */
.modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: white;
    border-radius: 24px;
    padding: 2rem;
    width: 100%;
    max-width: 520px;
    margin: 1rem;
    box-shadow: 0 24px 60px rgba(0,0,0,.2);
}
.modal-title { font-size: 1.4rem; font-weight: 800; color: #1f2937; margin-bottom: .4rem; }
.modal-sub   { color: #64748b; font-size: .9rem; margin-bottom: 1.5rem; }
.star-picker { display: flex; gap: .75rem; margin-bottom: 1.5rem; }
.star-btn {
    font-size: 2.2rem;
    background: none;
    border: none;
    cursor: pointer;
    color: #d1d5db;
    transition: all .15s;
    line-height: 1;
}
.star-btn i { font-size: 2.2rem; line-height: 1; }
.star-btn:hover,
.star-btn.selected { color: #f59e0b; transform: scale(1.15); }
.modal-label { font-size: .875rem; font-weight: 700; color: #1e293b; margin-bottom: .5rem; display: block; }
.modal-textarea {
    width: 100%;
    padding: .875rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: .95rem;
    font-family: inherit;
    resize: vertical;
    min-height: 100px;
    transition: all .2s;
    margin-bottom: 1.5rem;
}
.modal-textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
.modal-actions { display: flex; gap: .75rem; justify-content: flex-end; }
.btn-modal-cancel {
    padding: .75rem 1.5rem;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    color: #6b7280;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
}
.btn-modal-cancel:hover { border-color: #cbd5e1; }
.btn-modal-submit {
    padding: .75rem 1.75rem;
    background: linear-gradient(135deg,#6366f1,#7c3aed);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
}
.btn-modal-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,.4); }

@media (max-width: 767px) {
    .page-header-card { flex-direction: column; align-items: flex-start; }
    .btn-create { width: 100%; justify-content: center; }
    .filter-row { flex-direction: column; }
    .highlight-grid { grid-template-columns: 1fr; }
    .item-card { flex-direction: column; }
    .item-arrow { display: none; }
}
</style>
@endsection