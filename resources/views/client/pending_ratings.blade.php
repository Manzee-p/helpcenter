@extends('layouts.client')

@section('title', 'Belum Dirating')
@section('page_title', 'Belum Dirating')
@section('breadcrumb', 'Home / Belum Dirating')

@section('content')
@php
    $total       = $tickets->count();
    $pending     = $tickets->whereNull('feedback')->count();
    $rated       = 0;
    $progress    = 0;
    // Re-compute from all closed
    $allClosed   = \App\Models\Ticket::where('user_id', Auth::id())
                        ->whereIn('status', ['resolved','closed'])->count();
    $allRated    = \App\Models\Ticket::where('user_id', Auth::id())
                        ->whereIn('status', ['resolved','closed'])->has('feedback')->count();
    $progressPct = $allClosed > 0 ? round(($allRated / $allClosed) * 100) : 0;
@endphp

<div class="pending-ratings-page">

    {{-- HERO --}}
    <div class="pr-hero">
        <div class="pr-hero-text">
            <h1>⭐ Belum Dirating</h1>
            <p>Tiket yang sudah selesai tetapi masih menunggu penilaian dari Anda.</p>
        </div>
        <a href="{{ route('client.pending-ratings') }}" class="btn-refresh">
            <i class='bx bx-refresh'></i> Muat Ulang
        </a>
    </div>

    {{-- STATS --}}
    <div class="pr-stats">
        <div class="stat-card">
            <div class="stat-icon stat-icon--blue"><i class='bx bx-file'></i></div>
            <div class="stat-info">
                <span>Total Selesai</span>
                <strong>{{ $allClosed }}</strong>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--amber"><i class='bx bx-star'></i></div>
            <div class="stat-info">
                <span>Belum Dirating</span>
                <strong>{{ $tickets->count() }}</strong>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--green"><i class='bx bx-check-circle'></i></div>
            <div class="stat-info">
                <span>Sudah Dirating</span>
                <strong>{{ $allRated }}</strong>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--purple"><i class='bx bx-bar-chart-alt-2'></i></div>
            <div class="stat-info">
                <span>Progress Rating</span>
                <strong>{{ $progressPct }}%</strong>
            </div>
        </div>
    </div>

    {{-- PROGRESS BAR --}}
    <div class="progress-wrap">
        <span class="progress-label">Progress Penilaian</span>
        <div class="progress-bar-track">
            <div class="progress-bar-fill" style="width: {{ $progressPct }}%"></div>
        </div>
        <span class="progress-pct">{{ $progressPct }}%</span>
    </div>

    {{-- TABLE --}}
    <div class="table-card">
        <div class="table-card-head">
            <h2><i class='bx bx-star'></i> Tiket Menunggu Rating</h2>
            <span class="count-badge">{{ $tickets->count() }} tiket</span>
        </div>

        @if($tickets->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class='bx bx-happy-heart-eyes'></i></div>
                <h3 class="empty-title">Semua tiket sudah dirating!</h3>
                <p class="empty-text">Terima kasih, feedback Anda sangat membantu evaluasi vendor kami.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="pr-table">
                <thead>
                    <tr>
                        <th>Tiket</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Vendor</th>
                        <th>Selesai</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    <tr>
                        <td>
                            <div class="ticket-cell-num">#{{ $ticket->ticket_number }}</div>
                            <div class="ticket-cell-title">{{ Str::limit($ticket->title, 45) }}</div>
                        </td>
                        <td>
                            <span class="badge-cat">{{ $ticket->category->name ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="badge-status {{ $ticket->status === 'resolved' ? 'badge-resolved' : 'badge-closed' }}">
                                {{ $ticket->status === 'resolved' ? 'Selesai' : 'Ditutup' }}
                            </span>
                        </td>
                        <td>
                            <span class="vendor-name">{{ $ticket->assignedVendor->name ?? 'Tidak ada vendor' }}</span>
                        </td>
                        <td>
                            <span class="time-text">
                                {{ \Carbon\Carbon::parse($ticket->resolved_at ?? $ticket->updated_at)->locale('id')->diffForHumans() }}
                            </span>
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="{{ route('client.tickets.show', $ticket->id) }}" class="btn-detail">
                                    <i class='bx bx-show'></i> Detail
                                </a>
                                <button class="btn-rate" onclick="openModal({{ $ticket->id }}, '{{ $ticket->ticket_number }}', '{{ addslashes($ticket->title) }}')">
                                    <i class='bx bx-star'></i> Beri Rating
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

{{-- FEEDBACK MODAL --}}
<div class="modal-overlay" id="feedbackModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3>Beri Rating Layanan</h3>
            <button class="modal-close" onclick="closeModal()"><i class='bx bx-x'></i></button>
        </div>

        <div class="modal-ticket-info">
            <strong id="modal-ticket-num"></strong>
            <p id="modal-ticket-title"></p>
        </div>

        <form id="feedbackForm" method="POST" action="">
            @csrf
            <div class="star-group" id="starGroup">
                @for($i = 1; $i <= 5; $i++)
                <button type="button" class="star-btn" data-value="{{ $i }}" onclick="setRating({{ $i }})"><i class='bx bxs-star'></i></button>
                @endfor
            </div>
            <div class="star-label" id="starLabel">Pilih bintang untuk memberi rating</div>
            <input type="hidden" name="rating" id="ratingInput" value="0">

            <textarea name="comment" class="modal-textarea" placeholder="Ceritakan pengalaman Anda dengan layanan ini... (opsional)"></textarea>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-submit-rate" id="submitBtn" disabled>
                    <i class='bx bx-send'></i> Kirim Rating
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const starLabels = ['','Sangat Buruk','Kurang Baik','Cukup','Bagus','Luar Biasa!'];
let currentRating = 0;

function openModal(ticketId, ticketNum, ticketTitle) {
    document.getElementById('modal-ticket-num').textContent = '#' + ticketNum;
    document.getElementById('modal-ticket-title').textContent = ticketTitle;
    document.getElementById('feedbackForm').action = '/client/tickets/' + ticketId + '/feedback';
    setRating(0);
    document.getElementById('feedbackModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('feedbackModal').classList.remove('open');
    document.body.style.overflow = '';
}

function setRating(val) {
    currentRating = val;
    document.getElementById('ratingInput').value = val;
    document.getElementById('starLabel').textContent = val > 0 ? starLabels[val] : 'Pilih bintang untuk memberi rating';
    document.querySelectorAll('.star-btn').forEach((btn, i) => {
        btn.classList.toggle('active', i < val);
    });
    document.getElementById('submitBtn').disabled = val === 0;
}

// Close on overlay click
document.getElementById('feedbackModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endpush

@push('styles')
<style>
/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   PENDING RATINGS PAGE
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.pending-ratings-page {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    margin-top: 0;
    padding-top: 0;
}

/* â-€â-€â-€â-€â-€ HERO â-€â-€â-€â-€â-€ */
.pr-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 1.75rem 2rem;
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
    border-radius: 24px;
    box-shadow: 0 20px 40px rgba(15,23,42,.2);
    position: relative;
    overflow: hidden;
}
.pr-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(99,102,241,.25) 0%, transparent 70%);
    pointer-events: none;
}
.pr-hero::after {
    content: '';
    position: absolute;
    bottom: -40px; left: 30%;
    width: 140px; height: 140px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(124,58,237,.18) 0%, transparent 70%);
    pointer-events: none;
}
.pr-hero-text h1 {
    font-size: clamp(1.6rem, 3vw, 2.2rem);
    font-weight: 800;
    color: #fff;
    margin: 0 0 .4rem;
}
.pr-hero-text p { color: rgba(255,255,255,.65); margin: 0; }
.btn-refresh {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .75rem 1.5rem;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 14px;
    color: #fff;
    font-weight: 700;
    font-size: .875rem;
    text-decoration: none;
    backdrop-filter: blur(8px);
    transition: all .25s;
    cursor: pointer;
}
.btn-refresh:hover { background: rgba(255,255,255,.18); color: #fff; transform: translateY(-1px); }

/* â-€â-€â-€â-€â-€ STAT CARDS â-€â-€â-€â-€â-€ */
.pr-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0,1fr));
    gap: 1rem;
}
.stat-card {
    background: #fff;
    border: 1px solid rgba(148,163,184,.12);
    border-radius: 22px;
    padding: 1.25rem 1.35rem;
    box-shadow: 0 18px 36px rgba(15,23,42,.05);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all .25s;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 22px 42px rgba(15,23,42,.08); }
.stat-icon {
    width: 52px; height: 52px;
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}
.stat-icon--blue   { background: rgba(59,130,246,.1);  color: #2563eb; }
.stat-icon--amber  { background: rgba(245,158,11,.1);  color: #d97706; }
.stat-icon--green  { background: rgba(34,197,94,.1);   color: #15803d; }
.stat-icon--purple { background: rgba(99,102,241,.1);  color: #4f46e5; }
.stat-info span { font-size: .82rem; font-weight: 600; color: #64748b; }
.stat-info strong { display: block; font-size: 1.85rem; font-weight: 800; color: #0f172a; line-height: 1.1; }

/* â-€â-€â-€â-€â-€ PROGRESS BAR â-€â-€â-€â-€â-€ */
.progress-wrap {
    background: #fff;
    border: 1px solid rgba(148,163,184,.12);
    border-radius: 22px;
    padding: 1.35rem 1.5rem;
    box-shadow: 0 18px 36px rgba(15,23,42,.05);
    display: flex;
    align-items: center;
    gap: 1.5rem;
}
.progress-label { font-size: .875rem; font-weight: 700; color: #475569; white-space: nowrap; }
.progress-bar-track {
    flex: 1;
    height: 10px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
}
.progress-bar-fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #6366f1, #7c3aed);
    transition: width .6s ease;
}
.progress-pct { font-size: 1rem; font-weight: 800; color: #4f46e5; white-space: nowrap; }

/* â-€â-€â-€â-€â-€ TABLE CARD â-€â-€â-€â-€â-€ */
.table-card {
    background: #fff;
    border: 1px solid rgba(148,163,184,.12);
    border-radius: 24px;
    box-shadow: 0 18px 36px rgba(15,23,42,.05);
    overflow: hidden;
}
.table-card-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.35rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
}
.table-card-head h2 {
    font-size: 1.1rem;
    font-weight: 800;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.table-card-head h2 i { color: #6366f1; }
.count-badge {
    padding: .35rem .9rem;
    border-radius: 999px;
    background: rgba(245,158,11,.12);
    color: #b7791f;
    font-size: .8rem;
    font-weight: 700;
}
.table-responsive { overflow-x: auto; }
table.pr-table { width: 100%; border-collapse: collapse; }
.pr-table thead th {
    background: #f8fafc;
    padding: .9rem 1.25rem;
    font-size: .8rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .05em;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}
.pr-table tbody tr { border-bottom: 1px solid #f8fafc; transition: background .15s; }
.pr-table tbody tr:last-child { border-bottom: none; }
.pr-table tbody tr:hover { background: #fafafa; }
.pr-table td { padding: 1rem 1.25rem; vertical-align: middle; }

.ticket-cell-num { color: #4f46e5; font-weight: 800; font-size: .88rem; }
.ticket-cell-title { color: #334155; font-weight: 600; font-size: .9rem; margin: .2rem 0 0; }

.badge-cat {
    padding: .32rem .75rem;
    border-radius: 999px;
    background: rgba(59,130,246,.1);
    color: #1d4ed8;
    font-size: .76rem;
    font-weight: 700;
}
.badge-resolved { background: rgba(34,197,94,.1);  color: #15803d; }
.badge-closed   { background: rgba(148,163,184,.14); color: #475569; }
.badge-status {
    display: inline-flex;
    align-items: center;
    padding: .32rem .75rem;
    border-radius: 999px;
    font-size: .76rem;
    font-weight: 700;
}
.vendor-name { color: #475569; font-size: .9rem; }
.time-text { color: #94a3b8; font-size: .82rem; }

/* Action buttons */
.action-group { display: flex; gap: .5rem; justify-content: center; flex-wrap: wrap; }
.btn-detail {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .45rem 1rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    color: #475569;
    font-size: .8rem;
    font-weight: 700;
    text-decoration: none;
    background: #fff;
    transition: all .2s;
}
.btn-detail:hover { border-color: #6366f1; color: #6366f1; }
.btn-rate {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .45rem 1rem;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: #fff;
    font-size: .8rem;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
}
.btn-rate:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,.3); }

/* â-€â-€â-€â-€â-€ EMPTY STATE â-€â-€â-€â-€â-€ */
.empty-state {
    padding: 4rem 2rem;
    text-align: center;
}
.empty-icon { font-size: 4.5rem; color: #34d399; margin-bottom: 1rem; }
.empty-title { font-size: 1.3rem; font-weight: 700; color: #1f2937; margin-bottom: .5rem; }
.empty-text { color: #64748b; }

/* â-€â-€â-€â-€â-€ MODAL â-€â-€â-€â-€â-€ */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,.55);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    opacity: 0;
    pointer-events: none;
    transition: opacity .25s;
}
.modal-overlay.open { opacity: 1; pointer-events: all; }
.modal-box {
    background: #fff;
    border-radius: 28px;
    width: 100%;
    max-width: 520px;
    padding: 2rem;
    box-shadow: 0 32px 64px rgba(15,23,42,.2);
    transform: translateY(20px) scale(.97);
    transition: transform .3s;
}
.modal-overlay.open .modal-box { transform: translateY(0) scale(1); }
.modal-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.modal-head h3 { font-size: 1.25rem; font-weight: 800; color: #1f2937; margin: 0; }
.modal-close {
    background: #f1f5f9;
    border: none;
    border-radius: 10px;
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    color: #64748b;
    font-size: 1.2rem;
    transition: all .2s;
}
.modal-close:hover { background: #e2e8f0; color: #1f2937; }
.modal-ticket-info {
    background: #f8fafc;
    border-radius: 16px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}
.modal-ticket-info strong { display: block; color: #4f46e5; font-weight: 800; font-size: .88rem; }
.modal-ticket-info p { margin: .25rem 0 0; color: #334155; font-weight: 700; }

/* Star Rating */
.star-group { display: flex; gap: .5rem; justify-content: center; margin: 1rem 0; }
.star-btn {
    background: none;
    border: none;
    font-size: 2.5rem;
    cursor: pointer;
    color: #e2e8f0;
    transition: color .15s, transform .15s;
    line-height: 1;
}
.star-btn i { font-size: 2.5rem; line-height: 1; }
.star-btn.active, .star-btn:hover { color: #f59e0b; transform: scale(1.15); }
.star-label { text-align: center; font-size: .88rem; font-weight: 600; color: #64748b; margin-bottom: 1rem; min-height: 1.2em; }

.modal-textarea {
    width: 100%;
    padding: .875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    font-size: .9375rem;
    color: #334155;
    resize: vertical;
    min-height: 100px;
    font-family: inherit;
    transition: border-color .2s;
    box-sizing: border-box;
}
.modal-textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
.modal-actions { display: flex; gap: .75rem; margin-top: 1.25rem; }
.btn-cancel {
    flex: 1;
    padding: .875rem;
    background: #f1f5f9;
    border: none;
    border-radius: 14px;
    color: #475569;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
}
.btn-cancel:hover { background: #e2e8f0; }
.btn-submit-rate {
    flex: 2;
    padding: .875rem;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    border: none;
    border-radius: 14px;
    color: #fff;
    font-weight: 700;
    cursor: pointer;
    font-size: 1rem;
    transition: all .2s;
}
.btn-submit-rate:hover { box-shadow: 0 8px 20px rgba(99,102,241,.3); transform: translateY(-1px); }
.btn-submit-rate:disabled { opacity: .6; cursor: not-allowed; transform: none; }

@media (max-width: 1199px) { .pr-stats { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 767px) {
    .pr-hero { flex-direction: column; align-items: flex-start; }
    .pr-stats { grid-template-columns: repeat(2,1fr); }
    .progress-wrap { flex-direction: column; align-items: flex-start; gap: .75rem; }
    .progress-bar-track { width: 100%; }
}
</style>
@endpush

