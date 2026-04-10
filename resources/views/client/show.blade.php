@extends('layouts.client')

@section('title', 'Detail Tiket #' . $ticket->ticket_number)
@section('page_title', 'Detail Tiket')
@section('breadcrumb', 'Home / Tiket / #' . $ticket->ticket_number)

@push('styles')
<style>
/* ── WRAPPER ── */
.td-wrapper { animation: td-fadein .35s ease; max-width: 1200px; margin: 0 auto; width: 100%; }
@keyframes td-fadein {
    from { opacity:0; transform: translateY(10px); }
    to   { opacity:1; transform: translateY(0); }
}

/* ── TICKET HEADER ── */
.td-header {
    display: flex; align-items: center; gap: 1.25rem;
    background: var(--gradient); color: white;
    border-radius: 16px; padding: 1.5rem 2rem;
    margin-bottom: 1.75rem;
    box-shadow: var(--shadow-colored);
}
.td-header-icon {
    width: 64px; height: 64px; background: rgba(255,255,255,.2);
    border-radius: 16px; display: flex; align-items: center; justify-content: center;
    font-size: 2rem; flex-shrink: 0;
}
.td-header-body { flex: 1; min-width: 0; }
.td-number-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    background: rgba(255,255,255,.2); border-radius: 20px;
    padding: .25rem .75rem; font-size: .8rem; font-weight: 700;
    margin-bottom: .5rem;
}
.td-title { font-size: 1.375rem; font-weight: 800; margin: 0 0 .5rem; line-height: 1.3; }
.td-meta  { display: flex; gap: 1.25rem; flex-wrap: wrap; font-size: .8125rem; opacity: .88; }
.td-meta span { display: flex; align-items: center; gap: .3rem; }

/* ── GRID ── */
.td-grid {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 1.75rem;
    align-items: start;
    width: 100%;
}
.td-grid > div { min-width: 0; }
@media (max-width: 900px) { .td-grid { grid-template-columns: 1fr !important; } }

/* ── CARD ── */
.td-card {
    background: white; border-radius: 16px;
    border: 1.5px solid var(--border); overflow: hidden;
    transition: box-shadow .2s;
    /* hapus margin-bottom supaya sidebar tidak jelek */
    margin-bottom: 0;
}
.td-card:hover { box-shadow: var(--shadow); }

.td-card-header {
    display: flex; align-items: center; gap: .875rem;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-bottom: 1.5px solid var(--border);
}
.td-card-icon {
    width: 38px; height: 38px; background: var(--gradient);
    border-radius: 10px; display: flex; align-items: center; justify-content: center;
    color: white; font-size: 1.125rem;
}
.td-card-title { font-size: 1rem; font-weight: 700; color: var(--text); margin: 0; }
.td-card-body { padding: 1.75rem; }

/* ── INFO SECTION ── */
.td-info-label {
    display: flex; align-items: center; gap: .4rem;
    font-size: .8rem; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .5px; margin-bottom: .625rem;
}
.td-info-label i { color: var(--primary); }

.td-description {
    padding: 1.125rem 1.25rem;
    background: #f8fafc; border-left: 4px solid var(--primary);
    border-radius: 0 10px 10px 0; color: var(--text);
    font-size: .9375rem; line-height: 1.7; margin-bottom: 1.75rem;
}

/* ── DETAILS GRID ── */
.td-details-grid {
    display: grid; grid-template-columns: repeat(2,1fr);
    gap: 1.25rem; margin-bottom: 1.75rem;
}
@media (max-width: 640px) { .td-details-grid { grid-template-columns: 1fr; } }

.td-detail-item {
    padding: 1.125rem; background: #f8fafc;
    border-radius: 12px; border: 1px solid var(--border);
}
.td-detail-label {
    display: flex; align-items: center; gap: .375rem;
    font-size: .78rem; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .3px; margin-bottom: .5rem;
}
.td-detail-label i { color: var(--primary); }
.td-detail-value { font-size: .9375rem; font-weight: 700; color: var(--text); }

.td-priority-item { grid-column: 1 / -1; }

/* priority badges */
.td-priority-badge {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .375rem .875rem; border-radius: 8px; font-weight: 700; font-size: .875rem;
}
.td-priority-badge i { font-size: .55rem; }
.priority-low    { background: #d1fae5; color: #065f46; }
.priority-medium { background: #dbeafe; color: #1e40af; }
.priority-high   { background: #fed7aa; color: #92400e; }
.priority-urgent { background: #fee2e2; color: #991b1b; }
.priority-pending {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .875rem; color: var(--text-muted); font-style: italic;
}

.td-category-badge {
    display: inline-block; padding: .375rem .875rem;
    background: rgba(79,70,229,.1); color: var(--primary);
    border-radius: 8px; font-weight: 700;
}

/* status badge */
.td-status-badge {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .375rem .875rem; border-radius: 8px; font-weight: 700; font-size: .875rem;
}
.status-open        { background: #dbeafe; color: #1e40af; }
.status-in_progress { background: #fef3c7; color: #92400e; }
.status-resolved    { background: #d1fae5; color: #065f46; }
.status-closed      { background: #f3f4f6; color: #374151; }
.status-pending     { background: #ede9fe; color: #6d28d9; }

/* ── URGENCY BANNER ── */
.td-urgency-banner {
    display: flex; align-items: flex-start; gap: 1rem;
    padding: 1.125rem 1.25rem; border-radius: 12px;
    background: rgba(79,70,229,.05); border: 1px solid rgba(79,70,229,.15);
    margin-bottom: 1.75rem;
}
.td-urgency-banner i { font-size: 1.375rem; color: var(--primary); flex-shrink: 0; margin-top: .1rem; }
.td-urgency-title { font-size: .9375rem; font-weight: 700; color: var(--text); margin: 0 0 .25rem; }
.td-urgency-text  { font-size: .875rem; color: var(--text-muted); margin: 0; line-height: 1.6; }
.td-urgency-chip {
    display: inline-flex; align-items: center; gap: .3rem; margin-left: auto; flex-shrink: 0;
    padding: .375rem .8rem; border-radius: 20px; font-size: .8rem; font-weight: 700;
}
.urgency-low      { background: #d1fae5; color: #065f46; }
.urgency-medium   { background: #fef3c7; color: #92400e; }
.urgency-high     { background: #fee2e2; color: #991b1b; }
.urgency-critical { background: #fce7f3; color: #9d174d; }

/* ── EVENT DETAILS ── */
.td-event-grid {
    display: flex; flex-direction: column; gap: .625rem;
    padding: 1.125rem; background: rgba(79,70,229,.04);
    border-radius: 12px; border-left: 4px solid var(--primary);
    margin-bottom: 1.75rem;
}
.td-event-item {
    display: flex; align-items: center; gap: .75rem;
    font-size: .9375rem; color: var(--text); font-weight: 500;
}
.td-event-item i { color: var(--primary); width: 20px; font-size: 1rem; }

/* ── ATTACHMENTS ── */
.td-attachment-list { display: flex; flex-direction: column; gap: .625rem; }
.td-attachment-item {
    display: flex; align-items: center; gap: .875rem;
    padding: .875rem 1rem; background: #f8fafc;
    border-radius: 10px; border: 1px solid var(--border);
    transition: all .2s;
}
.td-attachment-item:hover { background: white; border-color: var(--primary); box-shadow: 0 2px 8px rgba(79,70,229,.1); }
.td-att-icon {
    width: 38px; height: 38px; background: white; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; color: var(--primary);
}
.td-att-info { flex: 1; min-width: 0; }
.td-att-name { font-size: .9rem; font-weight: 700; color: var(--text); margin: 0 0 .15rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.td-att-size { font-size: .78rem; color: var(--text-light); }
.td-att-dl {
    width: 34px; height: 34px; background: var(--primary); color: white;
    border-radius: 8px; display: flex; align-items: center; justify-content: center;
    text-decoration: none; transition: all .2s;
}
.td-att-dl:hover { background: var(--primary-dark); transform: scale(1.1); }

/* ── ADDITIONAL INFO ── */
.td-additional-wrap {
    margin-top: 1.5rem;
    border: 1px solid #e9d5ff;
    background: linear-gradient(135deg, #faf5ff 0%, #ffffff 100%);
    border-radius: 14px;
    padding: 1rem;
}
.td-additional-form textarea {
    width: 100%;
    min-height: 110px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: .85rem .95rem;
    font-size: .9rem;
    font-family: inherit;
}
.td-additional-form textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79,70,229,.12);
}
.td-additional-row {
    display: flex;
    gap: .75rem;
    align-items: center;
    margin-top: .75rem;
    flex-wrap: wrap;
}
.td-file-input {
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: .45rem .65rem;
    background: #fff;
    font-size: .84rem;
}
.td-additional-submit {
    border: none;
    border-radius: 10px;
    padding: .65rem 1rem;
    font-weight: 700;
    color: #fff;
    background: var(--gradient);
    cursor: pointer;
}
.td-additional-list {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: .65rem;
}
.td-additional-item {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    padding: .75rem .85rem;
}
.td-additional-meta {
    font-size: .77rem;
    color: var(--text-muted);
    margin-bottom: .35rem;
}
.td-additional-item p {
    margin: 0;
    color: var(--text);
    white-space: pre-wrap;
    line-height: 1.6;
}
.td-additional-link {
    margin-top: .45rem;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .28rem .55rem;
    border-radius: 7px;
    border: 1px solid #c7d2fe;
    color: #4f46e5;
    text-decoration: none;
    font-size: .8rem;
    font-weight: 600;
}

/* ── DELETE REQUEST ── */
.td-delete-wrap {
    margin-top: 1.5rem;
    border: 1px solid #fecaca;
    background: linear-gradient(135deg, #fff1f2 0%, #ffffff 100%);
    border-radius: 14px;
    padding: 1rem;
}
.td-delete-grid {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: .5rem .8rem;
    margin-top: .75rem;
}
@media (max-width: 700px) { .td-delete-grid { grid-template-columns: 1fr; } }
.td-delete-check {
    display: flex;
    gap: .45rem;
    font-size: .85rem;
    color: #334155;
}
.td-delete-check input { margin-top: .15rem; }
.td-delete-note {
    width: 100%;
    margin-top: .75rem;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: .75rem .9rem;
    min-height: 110px;
    font-size: .88rem;
    font-family: inherit;
}
.td-delete-note:focus {
    outline: none;
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239,68,68,.12);
}
.td-delete-submit {
    margin-top: .75rem;
    border: none;
    border-radius: 10px;
    padding: .62rem .95rem;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg,#ef4444,#dc2626);
    cursor: pointer;
}
.td-delete-status {
    margin-top: .85rem;
    border-radius: 10px;
    padding: .75rem .85rem;
    font-size: .84rem;
    background: #fff;
    border: 1px solid #e2e8f0;
}

/* ── VENDOR RATING ── */
.vr-card {
    padding: 1.4rem;
    background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%);
    border: 1px solid #fed7aa; border-radius: 16px; margin-top: 1.75rem;
}
.vr-card.submitted {
    background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
    border-color: #a7f3d0;
}
.vr-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
.vr-head h4 { margin: 0 0 .3rem; font-size: 1.0625rem; font-weight: 700; color: var(--text); }
.vr-head p  { margin: 0; font-size: .875rem; color: var(--text-muted); line-height: 1.6; }
.vr-badge {
    display: inline-flex; align-items: center; padding: .375rem .75rem;
    border-radius: 999px; font-size: .75rem; font-weight: 700; flex-shrink: 0;
    background: #dcfce7; color: #166534;
}
.vr-badge-pending { background: #fef3c7; color: #92400e; }

.vr-stars { display: flex; gap: .5rem; margin-bottom: .75rem; flex-wrap: wrap; }
.vr-star {
    width: 44px; height: 44px; border: none; border-radius: 12px;
    background: var(--border); color: var(--text-muted);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; cursor: pointer; transition: all .2s;
}
.vr-star.active { background: linear-gradient(135deg, #f59e0b, #f97316); color: white; box-shadow: 0 6px 16px rgba(245,158,11,.28); }
.vr-star:not([disabled]):hover { transform: translateY(-2px); }
.vr-star[disabled] { cursor: default; }

.vr-score { font-size: .9rem; font-weight: 700; color: var(--text); margin: 0 0 .875rem; }
.vr-comment-box {
    padding: 1rem; background: white; border: 1px solid #d1fae5;
    border-radius: 12px; color: var(--text); line-height: 1.7; white-space: pre-line;
}
.vr-comment-empty { font-size: .875rem; color: var(--text-muted); margin: 0; }

.vr-textarea {
    width: 100%; min-height: 110px; padding: 1rem;
    border: 1.5px solid var(--border); border-radius: 12px;
    background: white; color: var(--text); resize: vertical;
    font-family: 'Plus Jakarta Sans', sans-serif; font-size: .9rem;
    transition: all .2s;
}
.vr-textarea:focus { outline: none; border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.15); }

.vr-actions { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 1rem; flex-wrap: wrap; }
.vr-hint { font-size: .78rem; color: var(--text-muted); }
.vr-submit {
    display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
    padding: .75rem 1.25rem; border: none; border-radius: 12px; cursor: pointer;
    background: linear-gradient(135deg, #f59e0b, #f97316); color: white;
    font-weight: 700; font-size: .9rem; transition: all .2s;
    box-shadow: 0 8px 18px rgba(245,158,11,.24);
}
.vr-submit:hover:not(:disabled) { transform: translateY(-2px); }
.vr-submit:disabled { opacity: .6; cursor: not-allowed; box-shadow: none; transform: none; }

/* ── TIMELINE ── */
.td-timeline { display: flex; flex-direction: column; gap: 1.375rem; }
.td-tl-item { display: flex; gap: .875rem; position: relative; }
.td-tl-item:not(:last-child)::after {
    content: ''; position: absolute; left: 18px; top: 38px;
    width: 2px; height: calc(100% + 1.375rem); background: var(--border);
}
.td-tl-icon {
    width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: .9375rem; position: relative; z-index: 1;
}
.tl-created  { background: #dbeafe; color: #1e40af; }
.tl-assigned { background: #ede9fe; color: #6d28d9; }
.tl-response { background: #fef3c7; color: #92400e; }
.tl-resolved { background: #d1fae5; color: #065f46; }
.tl-closed   { background: #f3f4f6; color: #374151; }
.td-tl-label { font-size: .875rem; font-weight: 600; color: var(--text); margin: 0 0 .2rem; }
.td-tl-date  { font-size: .8125rem; color: var(--text-muted); margin: 0; }

/* ── SIDEBAR STICKY ── */
.td-sidebar {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 1rem;
}
@media (max-width: 900px) { .td-sidebar { grid-template-columns: 1fr; } }

/* ── TICKET INFO SIDEBAR CARD ── */
.td-sidebar-info {
    background: white; border-radius: 16px;
    border: 1.5px solid var(--border); overflow: hidden;
    transition: box-shadow .2s;
}
.td-sidebar-info:hover { box-shadow: var(--shadow); }

/* ── ADMIN BADGE ── */
.admin-badge {
    display: inline-block; margin-left: .4rem; padding: .125rem .5rem;
    background: rgba(79,70,229,.1); color: var(--primary);
    border-radius: 6px; font-size: .68rem; font-weight: 700; vertical-align: middle;
}

/* ── SIDEBAR TICKET META (small info items in sidebar) ── */
.td-sidebar-meta {
    display: flex; flex-direction: column; gap: .75rem;
}
.td-sidebar-meta-item {
    display: flex; flex-direction: column; gap: .25rem;
    padding: .875rem 1rem; background: #f8fafc;
    border-radius: 12px; border: 1px solid var(--border);
}
.td-sidebar-meta-item .lbl {
    display: flex; align-items: center; gap: .35rem;
    font-size: .74rem; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .3px;
}
.td-sidebar-meta-item .lbl i { color: var(--primary); font-size: .9rem; }
.td-sidebar-meta-item .val {
    font-size: .9rem; font-weight: 700; color: var(--text);
}
</style>
@endpush

@section('content')
<div class="td-wrapper">

    {{-- ── Ticket Header ── --}}
    <div class="td-header">
        <div class="td-header-icon"><i class='bx bx-support'></i></div>
        <div class="td-header-body">
            <div class="td-number-badge">
                <i class='bx bx-hash'></i>{{ $ticket->ticket_number }}
            </div>
            <h5 class="td-title">{{ $ticket->title }}</h5>
            <div class="td-meta">
                <span><i class='bx bx-user'></i>{{ $ticket->user->name ?? '-' }}</span>
                <span><i class='bx bx-time'></i>{{ $ticket->created_at->format('d M Y, H:i') }}</span>
            </div>
        </div>
    </div>

    {{-- ── Two-column grid ── --}}
    <div class="td-grid">

        {{-- ══════════════════════════════
             KIRI: Main Content
        ══════════════════════════════ --}}
        <div>
            <div class="td-card">
                <div class="td-card-header">
                    <div class="td-card-icon"><i class='bx bx-info-circle'></i></div>
                    <h5 class="td-card-title">Informasi Tiket</h5>
                </div>
                <div class="td-card-body">

                    {{-- Description --}}
                    <div class="td-info-label"><i class='bx bx-align-left'></i> Deskripsi</div>
                    <div class="td-description">{{ $ticket->description }}</div>

                    {{-- Details grid --}}
                    <div class="td-details-grid">

                        {{-- Category --}}
                        <div class="td-detail-item">
                            <div class="td-detail-label"><i class='bx bx-folder'></i> Kategori</div>
                            <div class="td-detail-value">
                                <span class="td-category-badge">{{ $ticket->category->name ?? '-' }}</span>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="td-detail-item">
                            <div class="td-detail-label"><i class='bx bx-task'></i> Status</div>
                            <div class="td-detail-value">
                                @php
                                    $statusMap = [
                                        'open'        => ['label'=>'Terbuka',        'class'=>'status-open'],
                                        'in_progress' => ['label'=>'Sedang Diproses','class'=>'status-in_progress'],
                                        'waiting_response' => ['label'=>'Butuh Respons Anda','class'=>'status-pending'],
                                        'resolved'    => ['label'=>'Selesai',         'class'=>'status-resolved'],
                                        'closed'      => ['label'=>'Ditutup',         'class'=>'status-closed'],
                                        'pending'     => ['label'=>'Tertunda',        'class'=>'status-pending'],
                                    ];
                                    $st = $statusMap[$ticket->status] ?? ['label'=> ucfirst($ticket->status), 'class'=>'status-open'];
                                @endphp
                                <span class="td-status-badge {{ $st['class'] }}">
                                    <i class='bx bx-radio-circle-marked'></i>{{ $st['label'] }}
                                </span>
                            </div>
                        </div>

                        {{-- Client --}}
                        <div class="td-detail-item">
                            <div class="td-detail-label"><i class='bx bx-user-tie'></i> Nama Klien</div>
                            <div class="td-detail-value">{{ $ticket->user->name ?? '-' }}</div>
                        </div>

                        {{-- Priority --}}
                        <div class="td-detail-item td-priority-item">
                            <div class="td-detail-label">
                                <i class='bx bx-flag'></i> Prioritas Resmi
                                <span class="admin-badge">Ditetapkan Admin</span>
                            </div>
                            <div class="td-detail-value">
                                @if($ticket->priority)
                                    @php
                                        $prioMap = [
                                            'low'    => ['label'=>'Rendah',  'class'=>'priority-low'],
                                            'medium' => ['label'=>'Sedang',  'class'=>'priority-medium'],
                                            'high'   => ['label'=>'Tinggi',  'class'=>'priority-high'],
                                            'urgent' => ['label'=>'Mendesak','class'=>'priority-urgent'],
                                        ];
                                        $pr = $prioMap[$ticket->priority] ?? ['label'=>ucfirst($ticket->priority),'class'=>'priority-medium'];
                                    @endphp
                                    <span class="td-priority-badge {{ $pr['class'] }}">
                                        <i class='bx bx-circle'></i>{{ $pr['label'] }}
                                    </span>
                                @else
                                    <span class="priority-pending">
                                        <i class='bx bx-time'></i> Prioritas akan ditetapkan oleh tim kami
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Urgency indication --}}
                    @if($ticket->urgency_level)
                    @php
                        $urgMap = [
                            'low'      => ['label'=>'Rendah','class'=>'urgency-low'],
                            'medium'   => ['label'=>'Sedang','class'=>'urgency-medium'],
                            'high'     => ['label'=>'Tinggi','class'=>'urgency-high'],
                            'critical' => ['label'=>'Kritis','class'=>'urgency-critical'],
                        ];
                        $urg = $urgMap[$ticket->urgency_level] ?? ['label'=>ucfirst($ticket->urgency_level),'class'=>'urgency-medium'];
                    @endphp
                    <div class="td-urgency-banner">
                        <i class='bx bx-info-circle'></i>
                        <div>
                            <p class="td-urgency-title">Indikasi Urgensi Anda</p>
                            <p class="td-urgency-text">
                                Anda menandai masalah ini sebagai <strong>{{ $urg['label'] }}</strong>.
                                Prioritas resmi telah ditetapkan oleh tim admin kami.
                            </p>
                        </div>
                        <span class="td-urgency-chip {{ $urg['class'] }}">
                            <i class='bx bx-error-circle'></i>{{ $urg['label'] }}
                        </span>
                    </div>
                    @endif

                    {{-- Event details --}}
                    @if($ticket->event_name)
                    <div class="td-info-label"><i class='bx bx-calendar-check'></i> Detail Event</div>
                    <div class="td-event-grid">
                        <div class="td-event-item"><i class='bx bx-calendar'></i><span>{{ $ticket->event_name }}</span></div>
                        @if($ticket->venue)
                        <div class="td-event-item"><i class='bx bx-map-pin'></i><span>{{ $ticket->venue }}</span></div>
                        @endif
                        @if($ticket->area)
                        <div class="td-event-item"><i class='bx bx-building'></i><span>{{ $ticket->area }}</span></div>
                        @endif
                    </div>
                    @endif

                    {{-- Attachments --}}
                    @if($ticket->attachments && $ticket->attachments->count())
                    <div class="td-info-label" style="margin-top:1.75rem;">
                        <i class='bx bx-paperclip'></i> Lampiran ({{ $ticket->attachments->count() }})
                    </div>
                    <div class="td-attachment-list">
                        @foreach($ticket->attachments as $file)
                        @php
                            $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                            $fileIcon = in_array($ext,['jpg','jpeg','png','gif','webp']) ? 'bx-image'
                                : (in_array($ext,['pdf']) ? 'bx-file-pdf'
                                : (in_array($ext,['doc','docx']) ? 'bx-file-doc'
                                : (in_array($ext,['xls','xlsx']) ? 'bx-spreadsheet'
                                : 'bx-file-blank')));
                            $size = $file->file_size
                                ? ($file->file_size >= 1048576
                                    ? round($file->file_size/1048576,1).' MB'
                                    : round($file->file_size/1024,1).' KB')
                                : '';
                        @endphp
                        <div class="td-attachment-item">
                            <div class="td-att-icon"><i class='bx {{ $fileIcon }}'></i></div>
                            <div class="td-att-info">
                                <p class="td-att-name">{{ $file->file_name }}</p>
                                @if($size)<span class="td-att-size">{{ $size }}</span>@endif
                            </div>
                            <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="td-att-dl">
                                <i class='bx bx-download'></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($ticket->status === 'waiting_response')
                    <div class="td-additional-wrap">
                        <div class="td-info-label" style="margin-bottom:.5rem;">
                            <i class='bx bx-message-square-dots'></i> Vendor Butuh Informasi Tambahan
                        </div>
                        <p style="font-size:.86rem;color:var(--text-muted);margin:0 0 .7rem;">
                            Isi keterangan tambahan dan upload maksimal 5 foto untuk membantu vendor.
                        </p>
                        <form class="td-additional-form" method="POST" action="{{ route('client.tickets.additional-info', $ticket->id) }}" enctype="multipart/form-data">
                            @csrf
                            <textarea name="note" placeholder="Tulis informasi tambahan untuk vendor...">{{ old('note') }}</textarea>
                            @error('note')
                                <p style="font-size:.8rem;color:#dc2626;margin:.4rem 0 0;">{{ $message }}</p>
                            @enderror
                            <div class="td-additional-row">
                                <input class="td-file-input" type="file" name="photos[]" multiple accept=".jpg,.jpeg,.png,.webp" />
                                <button class="td-additional-submit" type="submit">Kirim Informasi</button>
                                <span style="font-size:.78rem;color:#64748b;">Maks 5 file, 5MB/file</span>
                            </div>
                            @error('photos')
                                <p style="font-size:.8rem;color:#dc2626;margin:.4rem 0 0;">{{ $message }}</p>
                            @enderror
                            @error('photos.*')
                                <p style="font-size:.8rem;color:#dc2626;margin:.4rem 0 0;">{{ $message }}</p>
                            @enderror
                        </form>
                    </div>
                    @endif

                    @if($ticket->additionalInfos && $ticket->additionalInfos->count())
                    <div class="td-additional-list">
                        @foreach($ticket->additionalInfos as $info)
                        <div class="td-additional-item">
                            <div class="td-additional-meta">
                                {{ $info->user->name ?? 'Pengguna' }} • {{ $info->created_at ? $info->created_at->format('d M Y H:i') : '-' }}
                            </div>
                            @if($info->note)
                                <p>{{ $info->note }}</p>
                            @endif
                            @if($info->photo_path)
                                <a class="td-additional-link" href="{{ asset('storage/' . $info->photo_path) }}" target="_blank">
                                    <i class='bx bx-paperclip'></i> Lihat Lampiran
                                </a>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @php
                        $latestDeletionRequest = $ticket->deletionRequests->sortByDesc('id')->first();
                        $deletionReasons = [
                            'duplicate_ticket' => 'Tiket duplikat',
                            'issue_resolved_without_action' => 'Masalah sudah selesai tanpa tindakan vendor',
                            'wrong_category_or_input' => 'Salah kategori / salah input',
                            'ticket_created_by_mistake' => 'Tiket dibuat tidak sengaja',
                            'no_longer_relevant' => 'Tiket sudah tidak relevan',
                        ];
                    @endphp

                    <div class="td-delete-wrap">
                        <div class="td-info-label" style="margin-bottom:.25rem;">
                            <i class='bx bx-trash'></i> Pengajuan Hapus Tiket (Perlu Persetujuan Admin)
                        </div>
                        <p style="margin:0;font-size:.82rem;color:var(--text-muted);">
                            Tiket tidak akan langsung dihapus. Admin akan review alasan Anda terlebih dahulu.
                        </p>

                        @if(!$latestDeletionRequest || $latestDeletionRequest->status !== 'pending')
                            <form method="POST" action="{{ route('client.tickets.deletion-request', $ticket->id) }}">
                                @csrf
                                <div class="td-delete-grid">
                                    @foreach($deletionReasons as $reasonKey => $reasonLabel)
                                        <label class="td-delete-check">
                                            <input type="checkbox" name="reasons[]" value="{{ $reasonKey }}" {{ in_array($reasonKey, old('reasons', [])) ? 'checked' : '' }}>
                                            <span>{{ $reasonLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('reasons')
                                    <p style="font-size:.8rem;color:#dc2626;margin:.45rem 0 0;">{{ $message }}</p>
                                @enderror
                                @error('reasons.*')
                                    <p style="font-size:.8rem;color:#dc2626;margin:.45rem 0 0;">{{ $message }}</p>
                                @enderror

                                <textarea class="td-delete-note" name="custom_reason" placeholder="Tuliskan alasan tambahan Anda (wajib, minimal 10 karakter)...">{{ old('custom_reason') }}</textarea>
                                @error('custom_reason')
                                    <p style="font-size:.8rem;color:#dc2626;margin:.45rem 0 0;">{{ $message }}</p>
                                @enderror

                                <button type="submit" class="td-delete-submit">Ajukan Penghapusan ke Admin</button>
                            </form>
                        @endif

                        @if($latestDeletionRequest)
                            @php
                                $statusLabel = match($latestDeletionRequest->status) {
                                    'pending' => 'Menunggu review admin',
                                    'approved' => 'Disetujui admin',
                                    'rejected' => 'Ditolak admin',
                                    default => $latestDeletionRequest->status,
                                };
                            @endphp
                            <div class="td-delete-status">
                                <strong>Status terakhir:</strong> {{ $statusLabel }}<br>
                                <span style="color:var(--text-muted);">Diajukan {{ $latestDeletionRequest->created_at?->format('d M Y H:i') }}</span>
                                @if($latestDeletionRequest->admin_note)
                                    <div style="margin-top:.4rem;color:#475569;white-space:pre-wrap;">Catatan admin: {{ $latestDeletionRequest->admin_note }}</div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- ── Vendor Rating ── --}}
                    @if($ticket->status === 'resolved' || $ticket->status === 'closed')
                    @php
                        $feedback = $ticket->feedbacks()
                            ->where('user_id', Auth::id())
                            ->latest()->first();
                        $assignedVendor = $ticket->assignedTo;
                    @endphp

                    @if($assignedVendor)
                    <div style="margin-top:1.75rem;">
                        <div class="td-info-label"><i class='bx bx-star'></i> Rating Vendor</div>

                        @if($feedback)
                        <div class="vr-card submitted">
                            <div class="vr-head">
                                <div>
                                    <h4>Penilaian untuk {{ $assignedVendor->name }}</h4>
                                    <p>Rating Anda sudah tersimpan untuk vendor yang menangani tiket ini.</p>
                                </div>
                                <span class="vr-badge">Terkirim</span>
                            </div>
                            <div class="vr-stars">
                                @for($s = 1; $s <= 5; $s++)
                                <button class="vr-star {{ $s <= $feedback->rating ? 'active' : '' }}" disabled type="button">
                                    <i class='bx bxs-star'></i>
                                </button>
                                @endfor
                            </div>
                            <p class="vr-score">{{ $feedback->rating }}/5 bintang</p>
                            @if($feedback->comment)
                                <div class="vr-comment-box">{{ $feedback->comment }}</div>
                            @else
                                <p class="vr-comment-empty">Anda tidak menambahkan komentar untuk rating ini.</p>
                            @endif
                        </div>

                        @else
                        <div class="vr-card" id="vr-form-wrap">
                            <div class="vr-head">
                                <div>
                                    <h4>Beri rating untuk {{ $assignedVendor->name }}</h4>
                                    <p>Tiket sudah selesai. Silakan nilai pengalaman Anda dengan vendor.</p>
                                </div>
                                <span class="vr-badge vr-badge-pending">Menunggu</span>
                            </div>
                            <div class="vr-stars" id="vr-stars">
                                @for($s = 1; $s <= 5; $s++)
                                <button class="vr-star" data-star="{{ $s }}" type="button" onclick="vrSetStar({{ $s }})">
                                    <i class='bx bxs-star'></i>
                                </button>
                                @endfor
                            </div>
                            <p class="vr-score" id="vr-score-label">Pilih bintang untuk memberi rating</p>
                            <textarea
                                id="vr-comment"
                                class="vr-textarea"
                                rows="4"
                                maxlength="1000"
                                placeholder="Ceritakan pengalaman Anda dengan vendor ini..."
                            ></textarea>
                            <div class="vr-actions">
                                <span class="vr-hint" id="vr-char-count">0 / 1000 karakter</span>
                                <button class="vr-submit" id="vr-submit-btn" onclick="vrSubmit()" disabled>
                                    <i class='bx bx-send'></i> Kirim Rating
                                </button>
                            </div>
                        </div>
                        @endif

                    @endif
                    @endif

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════
             KANAN: Sidebar
             - Timeline
             - (optional info tambahan)
        ══════════════════════════════ --}}
        <div class="td-sidebar">

            {{-- ── Timeline Card ── --}}
            <div class="td-card">
                <div class="td-card-header">
                    <div class="td-card-icon"><i class='bx bx-history'></i></div>
                    <h5 class="td-card-title">Timeline</h5>
                </div>
                <div class="td-card-body">
                    <div class="td-timeline">

                        <div class="td-tl-item">
                            <div class="td-tl-icon tl-created"><i class='bx bx-plus'></i></div>
                            <div>
                                <p class="td-tl-label">Dibuat</p>
                                <p class="td-tl-date">{{ $ticket->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>

                        @if($ticket->assigned_at)
                        <div class="td-tl-item">
                            <div class="td-tl-icon tl-assigned"><i class='bx bx-user-check'></i></div>
                            <div>
                                <p class="td-tl-label">Ditugaskan</p>
                                <p class="td-tl-date">{{ \Carbon\Carbon::parse($ticket->assigned_at)->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        @endif

                        @if($ticket->first_response_at)
                        <div class="td-tl-item">
                            <div class="td-tl-icon tl-response"><i class='bx bx-reply'></i></div>
                            <div>
                                <p class="td-tl-label">Respons Pertama</p>
                                <p class="td-tl-date">{{ \Carbon\Carbon::parse($ticket->first_response_at)->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        @endif

                        @if($ticket->resolved_at)
                        <div class="td-tl-item">
                            <div class="td-tl-icon tl-resolved"><i class='bx bx-check-circle'></i></div>
                            <div>
                                <p class="td-tl-label">Diselesaikan</p>
                                <p class="td-tl-date">{{ \Carbon\Carbon::parse($ticket->resolved_at)->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        @endif

                        @if($ticket->status === 'closed' && $ticket->resolved_at)
                        <div class="td-tl-item">
                            <div class="td-tl-icon tl-closed"><i class='bx bx-lock'></i></div>
                            <div>
                                <p class="td-tl-label">Ditutup</p>
                                <p class="td-tl-date">{{ \Carbon\Carbon::parse($ticket->updated_at)->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- ── Ringkasan Tiket Card ── --}}
            <div class="td-card">
                <div class="td-card-header">
                    <div class="td-card-icon" style="background: linear-gradient(135deg,#0ea5e9,#0284c7);">
                        <i class='bx bx-receipt'></i>
                    </div>
                    <h5 class="td-card-title">Ringkasan Tiket</h5>
                </div>
                <div class="td-card-body" style="padding: 1.25rem;">
                    <div class="td-sidebar-meta">

                        <div class="td-sidebar-meta-item">
                            <span class="lbl"><i class='bx bx-hash'></i> Nomor Tiket</span>
                            <span class="val">{{ $ticket->ticket_number }}</span>
                        </div>

                        <div class="td-sidebar-meta-item">
                            <span class="lbl"><i class='bx bx-user'></i> Vendor Ditugaskan</span>
                            <span class="val">{{ $ticket->assignedTo->name ?? 'Belum ditugaskan' }}</span>
                        </div>

                        @if($ticket->category)
                        <div class="td-sidebar-meta-item">
                            <span class="lbl"><i class='bx bx-folder'></i> Kategori</span>
                            <span class="val">{{ $ticket->category->name }}</span>
                        </div>
                        @endif

                        <div class="td-sidebar-meta-item">
                            <span class="lbl"><i class='bx bx-calendar'></i> Dibuat</span>
                            <span class="val">{{ $ticket->created_at->format('d M Y') }}</span>
                        </div>

                        @if($ticket->resolved_at)
                        <div class="td-sidebar-meta-item">
                            <span class="lbl"><i class='bx bx-check-circle'></i> Diselesaikan</span>
                            <span class="val" style="color:#16a34a;">{{ \Carbon\Carbon::parse($ticket->resolved_at)->format('d M Y') }}</span>
                        </div>
                        @endif

                    </div>
                </div>
            </div>

        </div>
        {{-- end sidebar --}}

    </div>
    {{-- end td-grid --}}

</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var selectedRating = 0;
    var ratingLabels = ['','Sangat Buruk','Buruk','Cukup','Baik','Sangat Baik'];

    window.vrSetStar = function(star) {
        selectedRating = star;
        document.querySelectorAll('.vr-star[data-star]').forEach(function(btn) {
            btn.classList.toggle('active', parseInt(btn.dataset.star) <= star);
        });
        var lbl = document.getElementById('vr-score-label');
        if (lbl) lbl.textContent = star + '/5 bintang — ' + (ratingLabels[star] ?? '');
        var btn = document.getElementById('vr-submit-btn');
        if (btn) btn.disabled = false;
    };

    var textarea = document.getElementById('vr-comment');
    if (textarea) {
        textarea.addEventListener('input', function() {
            var cnt = document.getElementById('vr-char-count');
            if (cnt) cnt.textContent = this.value.length + ' / 1000 karakter';
        });
    }

    window.vrSubmit = async function() {
        if (!selectedRating) return;

        var btn     = document.getElementById('vr-submit-btn');
        var comment = document.getElementById('vr-comment')?.value ?? '';

        btn.disabled = true;
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Mengirim...';

        try {
            var res = await fetch('/client/tickets/{{ $ticket->id }}/feedback', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ rating: selectedRating, comment: comment })
            });

            var data = await res.json();

            if (res.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Rating Terkirim!',
                    text: 'Terima kasih atas penilaian Anda.',
                    timer: 2500,
                    showConfirmButton: false
                }).then(function() { location.reload(); });
            } else {
                throw new Error(data.message ?? 'Gagal mengirim rating');
            }
        } catch(e) {
            Swal.fire({ icon:'error', title:'Gagal', text: e.message ?? 'Terjadi kesalahan.' });
            btn.disabled = false;
            btn.innerHTML = '<i class="bx bx-send"></i> Kirim Rating';
        }
    };
})();
</script>
@endpush
