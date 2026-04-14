@extends('layouts.app')

@section('title', 'Permintaan Penugasan Ulang Vendor')
@section('page_title', 'Permintaan Penugasan Ulang Vendor')
@section('breadcrumb', 'Beranda / Tiket / Permintaan Penugasan Ulang Vendor')

@section('content')
@php
    // Gunakan $statCounts dari controller jika tersedia (akurat untuk semua halaman).
    // Fallback ke getCollection() jika controller lama belum diupdate.
    $pending  = isset($statCounts) ? $statCounts['pending']  : $requests->getCollection()->where('status', 'pending')->count();
    $approved = isset($statCounts) ? $statCounts['approved'] : $requests->getCollection()->where('status', 'approved')->count();
    $rejected = isset($statCounts) ? $statCounts['rejected'] : $requests->getCollection()->where('status', 'rejected')->count();

    $statusLabel = [
        'pending'  => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];
    $ticketStatusLabel = [
        'new'              => 'Baru',
        'in_progress'      => 'Diproses',
        'waiting_response' => 'Menunggu Respons',
        'resolved'         => 'Selesai',
        'closed'           => 'Ditutup',
    ];
@endphp

{{-- -----------------------------------------------------------
     HERO
----------------------------------------------------------- --}}
<div class="rr-hero">
    <div class="rr-hero-inner">
        <div class="rr-hero-eyebrow">
            <span class="rr-hero-dot"></span> Kelola Penugasan
        </div>
        <h1 class="rr-hero-title">Permintaan Penugasan Ulang <span class="rr-hero-accent">Vendor</span></h1>
        <p class="rr-hero-sub">Tinjau &amp; proses setiap permintaan reassign langsung dari halaman ini.</p>
    </div>
    <div class="rr-hero-ornament" aria-hidden="true">
        <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="100" cy="100" r="80" stroke="rgba(255,255,255,.12)" stroke-width="30"/>
            <circle cx="100" cy="100" r="50" stroke="rgba(255,255,255,.08)" stroke-width="20"/>
            <circle cx="100" cy="100" r="20" fill="rgba(255,255,255,.1)"/>
        </svg>
    </div>
</div>

{{-- -----------------------------------------------------------
     STAT CARDS
----------------------------------------------------------- --}}
<div class="rr-stats">
    <div class="stat-card stat-pending">
        <div class="stat-icon">?</div>
        <div>
            <div class="stat-label">Menunggu</div>
            <div class="stat-value">{{ $pending }}</div>
        </div>
    </div>
    <div class="stat-card stat-approved">
        <div class="stat-icon">?</div>
        <div>
            <div class="stat-label">Disetujui</div>
            <div class="stat-value">{{ $approved }}</div>
        </div>
    </div>
    <div class="stat-card stat-rejected">
        <div class="stat-icon">?</div>
        <div>
            <div class="stat-label">Ditolak</div>
            <div class="stat-value">{{ $rejected }}</div>
        </div>
    </div>
</div>

{{-- -----------------------------------------------------------
     TABLE CARD
----------------------------------------------------------- --}}
<div class="rr-card">
    <div class="rr-card-head">
        <h2>Daftar Permintaan Penugasan Ulang</h2>
        <form method="GET" class="filter-bar">
            <div class="filter-select-wrap">
                <svg class="filter-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L13 10.414V17a1 1 0 01-.553.894l-4-2A1 1 0 018 15v-4.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/></svg>
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Menunggu</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <button type="submit" class="btn-filter">Filter</button>
        </form>
    </div>

    <div class="rr-table-wrap">
        <table class="rr-table">
            <thead>
                <tr>
                    <th>Tiket</th>
                    <th>Vendor</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th>Diajukan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $item)
                @php
                    $badgeMap = ['pending' => 'badge-pending', 'approved' => 'badge-approved', 'rejected' => 'badge-rejected'];
                @endphp
                <tr class="rr-row">
                    <td>
                        <a href="{{ route('admin.tickets.show', $item->ticket_id) }}" class="ticket-link">
                            {{ $item->ticket->ticket_number ?? '-' }}
                        </a>
                        <div class="ticket-title">{{ $item->ticket->title ?? '-' }}</div>
                    </td>
                    <td>
                        <span class="vendor-chip">{{ $item->vendor->name ?? '-' }}</span>
                    </td>
                    <td>
                        <div class="reason-option">{{ str_replace('_', ' ', $item->reason_option) }}</div>
                        <div class="reason-detail">{{ \Illuminate\Support\Str::limit($item->reason_detail, 60) }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $badgeMap[$item->status] ?? 'badge-pending' }}">
                            {{ $statusLabel[$item->status] ?? strtoupper($item->status) }}
                        </span>
                    </td>
                    <td class="date-cell">
                        {{ $item->created_at?->format('d M Y') ?? '-' }}<br>
                        <span class="time-sub">{{ $item->created_at?->format('H:i') ?? '' }}</span>
                    </td>
                    <td>
                        {{-- Tombol buka popup --}}
                        <button
                            class="btn-detail"
                            onclick="openModal({{ $item->id }})"
                            data-id="{{ $item->id }}"
                        >
                            <svg viewBox="0 0 20 20" fill="currentColor" width="13" height="13"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                            Lihat Detail
                        </button>
                        @if($item->status !== 'pending')
                            <div class="processed-by">oleh {{ $item->reviewer->name ?? 'Admin' }}</div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon">??</div>
                            <div>Belum ada permintaan penugasan ulang.</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rr-pagination">
        {{ $requests->links() }}
    </div>
</div>

{{-- -----------------------------------------------------------
     MODAL / POPUP DETAIL
     Data di-embed sebagai JSON per item agar tidak perlu AJAX
----------------------------------------------------------- --}}

{{-- Hidden data store --}}
<div id="modal-data-store" style="display:none">
@foreach($requests as $item)
@php
    $wl = isset($vendorWorkloadMap[$item->id]) ? $vendorWorkloadMap[$item->id] : null;
    // Fallback jika controller tidak menyediakan map (single $vendorWorkload hanya ada di show)
    // Popup akan load via AJAX route show jika tidak ada map
@endphp
<script type="application/json" data-item-id="{{ $item->id }}">
{
    "id": {{ $item->id }},
    "status": "{{ $item->status }}",
    "ticket_number": "{{ $item->ticket->ticket_number ?? '-' }}",
    "ticket_title": "{{ addslashes($item->ticket->title ?? '-') }}",
    "ticket_status": "{{ $item->ticket->status ?? '-' }}",
    "vendor_name": "{{ addslashes($item->vendor->name ?? '-') }}",
    "reason_option": "{{ str_replace('_', ' ', $item->reason_option) }}",
    "reason_detail": "{{ addslashes($item->reason_detail) }}",
    "created_at": "{{ $item->created_at?->format('d M Y H:i') ?? '-' }}",
    "reviewer_name": "{{ addslashes($item->reviewer->name ?? '') }}",
    "reviewed_at": "{{ $item->reviewed_at?->format('d M Y H:i') ?? '-' }}",
    "admin_note": "{{ addslashes($item->admin_note ?? '') }}",
    "process_url": "{{ route('admin.reassign-requests.process', $item->id) }}",
    "detail_url": "{{ route('admin.reassign-requests.show', $item->id) }}"
}
</script>
@endforeach
</div>

{{-- Modal markup --}}
<div id="rr-modal-overlay" class="modal-overlay" aria-hidden="true" onclick="closeModalOutside(event)">
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="modal-title">

        {{-- -- MODAL HEADER -- --}}
        <div class="modal-hero" id="modal-hero-area">
            {{-- Decorative BG shapes --}}
            <div class="mh-shape mh-shape-1" aria-hidden="true"></div>
            <div class="mh-shape mh-shape-2" aria-hidden="true"></div>
            <div class="mh-shape mh-shape-3" aria-hidden="true"></div>

            <div class="modal-hero-left">
                <div class="modal-eyebrow">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="11"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                    Permintaan Penugasan Ulang
                </div>
                <h2 id="modal-title">Permintaan Reassign</h2>
                <p id="modal-subtitle" class="modal-subtitle-text"></p>
            </div>
            <div class="modal-hero-right">
                <span class="badge modal-badge" id="modal-status-badge"></span>
                <button class="modal-close-btn" onclick="closeModal()" aria-label="Tutup">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>
        </div>

        {{-- -- MODAL BODY -- --}}
        <div class="modal-body">
            <div class="modal-cols">

                {{-- -- LEFT PANEL -- --}}
                <div class="modal-panel-left">

                    {{-- Info tiket --}}
                    <div class="msection">
                        <div class="msection-title">
                            <div class="msection-icon msection-icon-blue">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="14"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                            </div>
                            Informasi Tiket &amp; Vendor
                        </div>
                        <div class="info-quad">
                            <div class="iq-cell">
                                <div class="iq-label">Nomor Tiket</div>
                                <div class="iq-val mono-val" id="m-ticket-number">—</div>
                            </div>
                            <div class="iq-cell">
                                <div class="iq-label">Status Tiket</div>
                                <div class="iq-val" id="m-ticket-status">—</div>
                            </div>
                            <div class="iq-cell">
                                <div class="iq-label">Vendor</div>
                                <div class="iq-val" id="m-vendor-name">—</div>
                            </div>
                            <div class="iq-cell">
                                <div class="iq-label">Diajukan Pada</div>
                                <div class="iq-val" id="m-created-at">—</div>
                            </div>
                            <div class="iq-cell iq-full">
                                <div class="iq-label">Alasan Permintaan</div>
                                <div class="iq-val iq-reason-box" id="m-reason">—</div>
                            </div>
                        </div>
                    </div>

                    {{-- Workload section --}}
                    <div class="msection" id="workload-card">
                        <div class="msection-title">
                            <div class="msection-icon msection-icon-purple">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="14"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zm6-4a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zm6-3a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                            </div>
                            Beban Kerja Vendor
                        </div>
                        <div id="workload-body">
                            <div class="workload-loading">
                                <div class="wl-spinner"></div>
                                <span>Memuat data beban kerja…</span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- -- RIGHT PANEL (action/result + divider) -- --}}
                <div class="modal-panel-right">
                    <div class="right-panel-inner">

                        {{-- Panel header --}}
                        <div class="rp-head" id="action-card-head">
                            <svg viewBox="0 0 20 20" fill="currentColor" width="15"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Keputusan Admin
                        </div>

                        {{-- Panel body --}}
                        <div id="action-card-body">
                            {{-- Diisi JS --}}
                        </div>

                        {{-- Bottom decoration --}}
                        <div class="rp-deco" aria-hidden="true">
                            <svg viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <ellipse cx="60" cy="40" rx="55" ry="35" stroke="var(--brand)" stroke-width="1.5" stroke-dasharray="4 4" opacity=".18"/>
                                <ellipse cx="60" cy="40" rx="35" ry="20" stroke="var(--brand-2)" stroke-width="1" stroke-dasharray="3 6" opacity=".14"/>
                                <circle cx="60" cy="40" r="8" fill="var(--brand)" opacity=".08"/>
                            </svg>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

:root {
    --brand:       #5b4fcf;
    --brand-2:     #7c6ef7;
    --brand-light: #ede9ff;
    --text:        #111827;
    --text-2:      #6b7280;
    --border:      #e5e7eb;
    --surface:     #ffffff;
    --bg:          #f5f6ff;
    --shadow-sm:   0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --shadow-md:   0 4px 16px rgba(91,79,207,.12), 0 1px 4px rgba(0,0,0,.06);
    --shadow-lg:   0 20px 60px rgba(91,79,207,.18), 0 8px 24px rgba(0,0,0,.08);
    --radius:      16px;
    --font:        'Plus Jakarta Sans', sans-serif;
    --mono:        'DM Mono', monospace;
}

*, *::before, *::after { box-sizing: border-box; }

/* -- HERO -- */
.rr-hero {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #4338ca 0%, #6d5af5 55%, #9381ff 100%);
    color: #fff;
    border-radius: var(--radius);
    padding: 2rem 2rem 1.8rem;
    margin-bottom: 1.2rem;
    font-family: var(--font);
}
.rr-hero-inner { position: relative; z-index: 1; }
.rr-hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    opacity: .85;
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 999px;
    padding: .25rem .7rem;
    margin-bottom: .65rem;
}
.rr-hero-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #a5f3a5;
    box-shadow: 0 0 6px #a5f3a5;
    animation: blink 1.8s ease-in-out infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
.rr-hero-title {
    font-family: var(--font);
    font-size: clamp(1.35rem, 3vw, 1.8rem);
    font-weight: 800;
    line-height: 1.15;
    margin: 0 0 .5rem;
}
.rr-hero-accent { opacity: .8; }
.rr-hero-sub { font-size: .88rem; opacity: .82; margin: 0; }
.rr-hero-ornament {
    position: absolute;
    right: -30px; top: -30px;
    width: 180px; height: 180px;
    opacity: .6;
    pointer-events: none;
}

/* -- STATS -- */
.rr-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .85rem;
    margin-bottom: 1.2rem;
    font-family: var(--font);
}
.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1rem 1.1rem;
    display: flex;
    align-items: center;
    gap: .85rem;
    box-shadow: var(--shadow-sm);
    transition: transform .2s, box-shadow .2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.stat-icon { font-size: 1.5rem; line-height: 1; }
.stat-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--text-2); }
.stat-value { font-size: 1.65rem; font-weight: 800; color: var(--text); line-height: 1.1; }
.stat-pending  { border-left: 3px solid #f59e0b; }
.stat-approved { border-left: 3px solid #10b981; }
.stat-rejected { border-left: 3px solid #ef4444; }

/* -- TABLE CARD -- */
.rr-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    font-family: var(--font);
}
.rr-card-head {
    padding: 1rem 1.3rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .6rem;
    flex-wrap: wrap;
    background: #fafbff;
}
.rr-card-head h2 { margin: 0; font-size: .95rem; font-weight: 800; color: var(--text); }

.filter-bar { display: flex; gap: .5rem; align-items: center; }
.filter-select-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.filter-icon {
    position: absolute;
    left: .6rem;
    color: var(--text-2);
    pointer-events: none;
    width: 14px;
}
.filter-bar select {
    font-family: var(--font);
    font-size: .82rem;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: .44rem .65rem .44rem 2rem;
    background: var(--surface);
    color: var(--text);
    appearance: none;
    cursor: pointer;
    transition: border-color .15s;
}
.filter-bar select:focus { outline: none; border-color: var(--brand); }
.btn-filter {
    font-family: var(--font);
    font-size: .82rem;
    font-weight: 700;
    border: none;
    border-radius: 10px;
    padding: .46rem 1rem;
    background: var(--brand);
    color: #fff;
    cursor: pointer;
    transition: background .15s, transform .1s;
}
.btn-filter:hover { background: var(--brand-2); transform: translateY(-1px); }

.rr-table-wrap { overflow-x: auto; }
.rr-table { width: 100%; min-width: 860px; border-collapse: collapse; font-family: var(--font); }
.rr-table thead tr { background: #fafbff; }
.rr-table th {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--text-2);
    text-align: left;
    padding: .7rem 1rem;
    white-space: nowrap;
}
.rr-table td {
    border-top: 1px solid var(--border);
    padding: .9rem 1rem;
    vertical-align: middle;
    font-size: .875rem;
    color: var(--text);
}
.rr-row { transition: background .12s; }
.rr-row:hover { background: #fbfaff; }

.ticket-link {
    font-family: var(--mono);
    font-size: .8rem;
    font-weight: 500;
    color: var(--brand);
    text-decoration: none;
    background: var(--brand-light);
    padding: .18rem .45rem;
    border-radius: 6px;
    display: inline-block;
}
.ticket-link:hover { background: #d8d0ff; }
.ticket-title { font-size: .78rem; color: var(--text-2); margin-top: .28rem; max-width: 240px; line-height: 1.35; }

.vendor-chip {
    background: var(--brand-light);
    color: var(--brand);
    padding: .3rem .65rem;
    border-radius: 999px;
    font-size: .76rem;
    font-weight: 700;
}
.reason-option {
    display: inline-block;
    background: #f3f4f6;
    padding: .18rem .45rem;
    border-radius: 6px;
    font-size: .72rem;
    font-weight: 700;
    margin-bottom: .22rem;
    text-transform: capitalize;
    color: var(--text);
}
.reason-detail { color: var(--text-2); font-size: .77rem; max-width: 260px; line-height: 1.35; }

.badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: .28rem .7rem;
    font-size: .68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .06em;
    white-space: nowrap;
}
.badge-pending  { background: #fff7ed; color: #c2410c; }
.badge-approved { background: #ecfdf5; color: #065f46; }
.badge-rejected { background: #fef2f2; color: #991b1b; }

.date-cell { font-size: .8rem; white-space: nowrap; }
.time-sub { font-size: .72rem; color: var(--text-2); }

.btn-detail {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    background: var(--brand-light);
    color: var(--brand);
    border: none;
    border-radius: 9px;
    padding: .38rem .7rem;
    font-family: var(--font);
    font-size: .76rem;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s, transform .1s;
}
.btn-detail:hover { background: #d3caff; transform: translateY(-1px); }
.processed-by { margin-top: .25rem; font-size: .72rem; color: var(--text-2); }

.empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-2); font-family: var(--font); }
.empty-icon { font-size: 2rem; margin-bottom: .5rem; }
.rr-pagination { padding: 1rem 1.3rem; border-top: 1px solid var(--border); background: #fafbff; }

/* -- MODAL OVERLAY -- */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(9,12,28,.6);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    opacity: 0;
    pointer-events: none;
    transition: opacity .28s ease;
}
.modal-overlay.is-open { opacity: 1; pointer-events: all; }

.modal-box {
    background: #f4f5ff;
    border-radius: 24px;
    width: 100%;
    max-width: 1080px;
    max-height: 92vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 32px 80px rgba(67,56,202,.22), 0 8px 32px rgba(0,0,0,.12);
    transform: translateY(30px) scale(.96);
    transition: transform .32s cubic-bezier(.22,.68,0,1.18);
    font-family: var(--font);
}
.modal-overlay.is-open .modal-box { transform: translateY(0) scale(1); }

/* -- MODAL HERO HEADER -- */
.modal-hero {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #312e81 0%, #4f46e5 45%, #7c6ef7 75%, #a78bfa 100%);
    color: #fff;
    padding: 1.5rem 1.75rem 1.35rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-shrink: 0;
}
/* decorative shapes */
.mh-shape {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}
.mh-shape-1 {
    width: 260px; height: 260px;
    background: rgba(255,255,255,.06);
    top: -90px; right: -60px;
}
.mh-shape-2 {
    width: 160px; height: 160px;
    background: rgba(255,255,255,.05);
    bottom: -70px; right: 120px;
}
.mh-shape-3 {
    width: 80px; height: 80px;
    background: rgba(255,255,255,.08);
    top: 10px; right: 220px;
}
.modal-hero-left { position: relative; z-index: 1; }
.modal-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.22);
    border-radius: 999px;
    padding: .22rem .65rem;
    margin-bottom: .55rem;
    opacity: .92;
}
.modal-hero h2 {
    font-family: var(--font);
    font-size: clamp(1.1rem, 2.5vw, 1.4rem);
    font-weight: 800;
    margin: 0 0 .25rem;
    line-height: 1.15;
}
.modal-subtitle-text {
    font-size: .82rem;
    opacity: .82;
    margin: 0;
    font-family: var(--mono);
}
.modal-hero-right {
    display: flex;
    align-items: center;
    gap: .6rem;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
    padding-top: .1rem;
}
.modal-badge { font-size: .7rem; }
.modal-close-btn {
    background: rgba(255,255,255,.15);
    border: 1.5px solid rgba(255,255,255,.3);
    color: #fff;
    border-radius: 10px;
    width: 34px; height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background .15s, transform .12s;
    flex-shrink: 0;
}
.modal-close-btn:hover { background: rgba(255,255,255,.28); transform: scale(1.08); }

/* -- MODAL BODY -- */
.modal-body {
    overflow-y: auto;
    flex: 1;
    padding: 0;
}

/* -- TWO-COLUMN LAYOUT -- */
.modal-cols {
    display: grid;
    grid-template-columns: 1fr 340px;
    min-height: 100%;
}

/* -- LEFT PANEL -- */
.modal-panel-left {
    padding: 1.4rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
    border-right: 1px solid var(--border);
    background: #f4f5ff;
}

/* section block */
.msection {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(67,56,202,.06);
}
.msection-title {
    display: flex;
    align-items: center;
    gap: .55rem;
    padding: .8rem 1.1rem;
    font-size: .8rem;
    font-weight: 800;
    color: var(--text);
    background: #fafbff;
    border-bottom: 1px solid var(--border);
}
.msection-icon {
    width: 26px; height: 26px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.msection-icon-blue   { background: #dbeafe; color: #1d4ed8; }
.msection-icon-purple { background: var(--brand-light); color: var(--brand); }

/* info quad grid */
.info-quad {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0;
    padding: 0;
}
.iq-cell {
    padding: .85rem 1.1rem;
    border-right: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
}
.iq-cell:nth-child(2n) { border-right: 0; }
.iq-cell:last-child,
.iq-cell:nth-last-child(2):not(.iq-full) { border-bottom: 0; }
.iq-full {
    grid-column: 1 / -1;
    border-right: 0;
    border-bottom: 0;
}
.iq-label {
    font-size: .66rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--text-2);
    margin-bottom: .3rem;
}
.iq-val {
    font-size: .88rem;
    color: var(--text);
    font-weight: 500;
    line-height: 1.4;
}
.mono-val {
    font-family: var(--mono);
    font-weight: 600;
    color: var(--brand);
    font-size: .85rem;
    background: var(--brand-light);
    display: inline-block;
    padding: .15rem .45rem;
    border-radius: 6px;
}
.iq-reason-box {
    background: #f8f9ff;
    border: 1px solid #e0e3ff;
    border-radius: 10px;
    padding: .65rem .8rem;
    font-size: .84rem;
    line-height: 1.55;
    color: var(--text);
}

/* workload inner content — injected via JS */
#workload-body { padding: 1rem 1.1rem; }

/* workload loading */
.workload-loading {
    display: flex;
    align-items: center;
    gap: .7rem;
    color: var(--text-2);
    font-size: .83rem;
    padding: .5rem 0;
}
.wl-spinner {
    width: 18px; height: 18px;
    border: 2px solid var(--border);
    border-top-color: var(--brand);
    border-radius: 50%;
    animation: spin .7s linear infinite;
    flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* metrics 2x2 */
.wl-metrics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: .55rem;
    margin-bottom: .9rem;
}
.wl-metric {
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: .65rem .7rem;
    background: linear-gradient(135deg, #fafbff, #f4f5ff);
    text-align: center;
}
.wl-metric span {
    font-size: .65rem;
    color: var(--text-2);
    display: block;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: .2rem;
}
.wl-metric strong { font-size: 1.3rem; font-weight: 800; color: var(--text); }

/* progress */
.wl-progress-wrap { margin-bottom: .85rem; }
.wl-progress-head {
    display: flex;
    justify-content: space-between;
    font-size: .8rem;
    margin-bottom: .35rem;
    color: var(--text);
    font-weight: 600;
}
.wl-progress-head strong { color: var(--brand); }
.wl-progress-track {
    height: 10px;
    border-radius: 999px;
    background: #e9eaff;
    overflow: hidden;
}
.wl-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--brand), var(--brand-2));
    border-radius: 999px;
    transition: width .7s cubic-bezier(.34,1.56,.64,1);
    position: relative;
}

/* alert */
.wl-alert {
    border-radius: 12px;
    padding: .65rem .8rem;
    font-size: .8rem;
    font-weight: 500;
    border: 1px solid;
    margin-bottom: .85rem;
    display: flex;
    align-items: flex-start;
    gap: .5rem;
}
.wl-alert-ok  { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
.wl-alert-bad { background: #fff1f2; color: #9f1239; border-color: #fecdd3; }

/* status grid */
.wl-status-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: .4rem;
    margin-bottom: .85rem;
}
.wl-status-box {
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: .5rem .3rem;
    text-align: center;
    background: #fff;
    transition: border-color .15s, box-shadow .15s;
}
.wl-status-box:hover { border-color: var(--brand-light); box-shadow: 0 0 0 3px var(--brand-light); }
.wl-status-box span {
    font-size: .6rem;
    color: var(--text-2);
    display: block;
    line-height: 1.3;
    margin-bottom: .15rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03em;
}
.wl-status-box strong { font-size: 1rem; font-weight: 800; color: var(--text); }

/* active list */
.wl-list-title {
    font-size: .72rem;
    color: var(--text-2);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    margin-bottom: .4rem;
}
.wl-list {
    list-style: none;
    margin: 0;
    padding: 0;
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    max-height: 200px;
    overflow-y: auto;
}
.wl-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .52rem .75rem;
    border-top: 1px solid var(--border);
    font-size: .8rem;
    transition: background .1s;
}
.wl-list li:first-child { border-top: 0; }
.wl-list li:hover { background: #f8f9ff; }
.wl-list a {
    color: var(--brand);
    text-decoration: none;
    font-weight: 700;
    font-family: var(--mono);
    font-size: .76rem;
}
.wl-list span { color: var(--text-2); font-size: .75rem; }
.wl-empty { color: var(--text-2); font-size: .82rem; font-style: italic; }

/* -- RIGHT PANEL -- */
.modal-panel-right {
    background: var(--surface);
    border-left: 1px solid var(--border);
    display: flex;
    flex-direction: column;
}
.right-panel-inner {
    display: flex;
    flex-direction: column;
    flex: 1;
    position: sticky;
    top: 0;
    max-height: 92vh;
    overflow-y: auto;
}
.rp-head {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .95rem 1.2rem;
    font-size: .82rem;
    font-weight: 800;
    color: var(--text);
    background: #fafbff;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}
.rp-head svg { color: var(--brand); }
#action-card-body { padding: 1.2rem; flex: 1; }

/* action form */
.action-form { display: flex; flex-direction: column; gap: .75rem; }
.action-label {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--text-2);
    display: block;
    margin-bottom: .25rem;
}
.action-textarea {
    font-family: var(--font);
    font-size: .84rem;
    border: 1.5px solid var(--border);
    border-radius: 12px;
    padding: .7rem .85rem;
    resize: vertical;
    min-height: 100px;
    width: 100%;
    transition: border-color .15s, box-shadow .15s;
    color: var(--text);
    background: #fafbff;
    line-height: 1.5;
}
.action-textarea:focus {
    outline: none;
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(91,79,207,.12);
}
.btn-approve, .btn-reject {
    width: 100%;
    border: none;
    border-radius: 12px;
    padding: .7rem;
    color: #fff;
    font-family: var(--font);
    font-size: .85rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .15s, transform .12s, box-shadow .15s;
    letter-spacing: .02em;
}
.btn-approve {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    box-shadow: 0 4px 12px rgba(16,185,129,.25);
}
.btn-reject {
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    box-shadow: 0 4px 12px rgba(239,68,68,.22);
    margin-top: .5rem;
}
.btn-approve:hover, .btn-reject:hover { opacity: .9; transform: translateY(-1px); }

/* result items */
.result-item { margin-bottom: .85rem; }
.result-item:last-child { margin-bottom: 0; }

/* right panel decoration at bottom */
.rp-deco {
    margin-top: auto;
    padding: 1rem 1.2rem .8rem;
    display: flex;
    justify-content: center;
    opacity: .7;
}
.rp-deco svg { width: 100%; max-width: 130px; height: auto; }

/* -- RESPONSIVE -- */
@media (max-width: 960px) {
    .modal-cols { grid-template-columns: 1fr; }
    .modal-panel-left { border-right: 0; border-bottom: 1px solid var(--border); }
    .modal-panel-right { border-left: 0; }
    .right-panel-inner { position: static; max-height: none; }
    .rp-deco { display: none; }
    .wl-metrics { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .rr-stats { grid-template-columns: 1fr 1fr; }
    .rr-hero { padding: 1.4rem 1.2rem; }
    .modal-box { border-radius: 20px; }
    .modal-hero { padding: 1.2rem 1.3rem; }
    .modal-panel-left { padding: 1rem 1.1rem; }
    .wl-status-grid { grid-template-columns: repeat(3, 1fr); }
    .modal-cols { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
    .modal-overlay { padding: .5rem; }
    .modal-box { border-radius: 18px; max-height: 96vh; }
    .info-quad { grid-template-columns: 1fr; }
    .iq-cell { border-right: 0; }
    .wl-metrics { grid-template-columns: repeat(2, 1fr); }
    .wl-status-grid { grid-template-columns: repeat(2, 1fr); }
    .modal-hero h2 { font-size: 1.05rem; }
}
</style>

{{-- -----------------------------------------------------------
     JAVASCRIPT
----------------------------------------------------------- --}}
<script>
const TICKET_STATUS_LABEL = {
    new: 'Baru',
    baru: 'Baru',
    in_progress: 'Diproses',
    waiting_response: 'Menunggu Respons',
    resolved: 'Selesai',
    closed: 'Ditutup',
};
const STATUS_LABEL = { pending: 'Menunggu', approved: 'Disetujui', rejected: 'Ditolak' };
const BADGE_CLASS  = { pending: 'badge-pending', approved: 'badge-approved', rejected: 'badge-rejected' };

// Collect embedded JSON data
const itemDataMap = {};
document.querySelectorAll('#modal-data-store script[data-item-id]').forEach(el => {
    try {
        const d = JSON.parse(el.textContent);
        itemDataMap[d.id] = d;
    } catch(e) {}
});

function openModal(id) {
    const d = itemDataMap[id];
    if (!d) { window.location.href = ''; return; }

    // Hero
    document.getElementById('modal-title').textContent    = `Permintaan Reassign #${d.id}`;
    document.getElementById('modal-subtitle').textContent = `${d.ticket_number} — ${d.ticket_title}`;
    const badge = document.getElementById('modal-status-badge');
    badge.textContent  = STATUS_LABEL[d.status] || d.status.toUpperCase();
    badge.className    = `badge ${BADGE_CLASS[d.status] || 'badge-pending'}`;

    // Info
    document.getElementById('m-ticket-number').textContent = d.ticket_number;
    document.getElementById('m-ticket-status').textContent = TICKET_STATUS_LABEL[d.ticket_status] || d.ticket_status;
    document.getElementById('m-vendor-name').textContent   = d.vendor_name;
    document.getElementById('m-created-at').textContent    = d.created_at;
    document.getElementById('m-reason').textContent        = `${d.reason_option} — ${d.reason_detail}`;

    // Action panel
    const actionHead = document.getElementById('action-card-head');
    const actionBody = document.getElementById('action-card-body');
    if (d.status === 'pending') {
        actionHead.innerHTML = `<svg viewBox="0 0 20 20" fill="currentColor" width="15"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Keputusan Admin`;
        actionBody.innerHTML = `
            <form method="POST" action="${d.process_url}" class="action-form">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]')?.content || ''}">
                <div>
                    <label class="action-label">Catatan admin (opsional)</label>
                    <textarea name="admin_note" class="action-textarea" rows="4" placeholder="Tambahkan catatan untuk vendor…"></textarea>
                </div>
                <button type="submit" name="action" value="approve" class="btn-approve">? Setujui Reassign</button>
                <button type="submit" name="action" value="reject"  class="btn-reject">? Tolak Permintaan</button>
            </form>`;
    } else {
        actionHead.innerHTML = `<svg viewBox="0 0 20 20" fill="currentColor" width="15"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Hasil Review`;
        actionBody.innerHTML = `
            <div class="result-item">
                <div class="iq-label">Status</div>
                <div style="margin-top:.3rem"><span class="badge ${BADGE_CLASS[d.status]}">${STATUS_LABEL[d.status] || d.status}</span></div>
            </div>
            <div class="result-item">
                <div class="iq-label">Reviewer</div>
                <div class="iq-val">${d.reviewer_name || 'Admin'}</div>
            </div>
            <div class="result-item">
                <div class="iq-label">Waktu Review</div>
                <div class="iq-val">${d.reviewed_at || '—'}</div>
            </div>
            <div class="result-item">
                <div class="iq-label">Catatan</div>
                <div class="iq-val" style="background:#f8f9ff;border:1px solid #e0e3ff;border-radius:10px;padding:.6rem .75rem;margin-top:.25rem;font-size:.84rem;line-height:1.5">${d.admin_note || '—'}</div>
            </div>`;
    }

    // Reset workload
    document.getElementById('workload-body').innerHTML = `
        <div class="workload-loading">
            <div class="wl-spinner"></div>
            <span>Memuat data beban kerja…</span>
        </div>`;

    // Show overlay
    const overlay = document.getElementById('rr-modal-overlay');
    overlay.setAttribute('aria-hidden', 'false');
    overlay.classList.add('is-open');
    document.body.style.overflow = 'hidden';

    // Load workload via AJAX (fetch show page as JSON)
    fetchWorkload(id, d.detail_url);
}

function fetchWorkload(id, detailUrl) {
    fetch(detailUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => {
        if (!r.ok) throw new Error('Non-2xx response');
        return r.json();
    })
    .then(data => renderWorkload(data.vendorWorkload))
    .catch(() => {
        // Fallback: tampilkan link ke halaman detail
        document.getElementById('workload-body').innerHTML =
            '<div class="wl-empty">Data beban kerja tidak tersedia secara inline. ' +
            '<a href="' + detailUrl + '" style="color:var(--brand);font-weight:700">Buka halaman detail ?</a></div>';
    });
}

function renderWorkload(wl) {
    if (!wl) {
        document.getElementById('workload-body').innerHTML = '<div class="wl-empty">Data tidak tersedia.</div>';
        return;
    }
    const activeCount     = wl.active_tickets_excluding_related ?? 0;
    const limit           = wl.assignment_limit ?? 5;
    const canAssign       = wl.can_take_new_assignment ?? true;
    const progressPercent = wl.progress_percent ?? 0;

    const activeList = (wl.active_tickets_list ?? []);
    const activeListHtml = activeList.length
        ? `<ul class="wl-list">
            ${activeList.map(t => `
                <li>
                    <a href="/admin/tickets/${t.id}">${t.ticket_number}</a>
                    <span>${TICKET_STATUS_LABEL[t.status] || t.status}</span>
                </li>`).join('')}
           </ul>`
        : `<div class="wl-empty">Tidak ada tiket aktif.</div>`;

    const statusCounts = wl.status_counts || {};

    document.getElementById('workload-body').innerHTML = `
        <div class="wl-metrics">
            <div class="wl-metric"><span>Tiket aktif</span><strong>${wl.active_tickets ?? 0}</strong></div>
            <div class="wl-metric"><span>Total tiket</span><strong>${wl.total_tickets ?? 0}</strong></div>
            <div class="wl-metric"><span>Selesai/ditutup</span><strong>${wl.resolved_tickets ?? 0}</strong></div>
            <div class="wl-metric"><span>Laporan vendor</span><strong>${wl.total_reports ?? 0}</strong></div>
        </div>
        <div class="wl-progress-wrap">
            <div class="wl-progress-head"><span>Progress penyelesaian</span><strong>${progressPercent}%</strong></div>
            <div class="wl-progress-track"><div class="wl-progress-fill" style="width:${progressPercent}%"></div></div>
        </div>
        <div class="wl-alert ${canAssign ? 'wl-alert-ok' : 'wl-alert-bad'}">
            ${canAssign
                ? '? Vendor masih bisa menerima penugasan baru (aktif: ' + activeCount + ' tiket).'
                : '? Vendor menangani <strong>' + activeCount + '</strong> tiket aktif lain. Melebihi batas ' + limit + '.'}
        </div>
        <div class="wl-status-grid">
            <div class="wl-status-box"><span>Baru</span><strong>${statusCounts.new ?? 0}</strong></div>
            <div class="wl-status-box"><span>Diproses</span><strong>${statusCounts.in_progress ?? 0}</strong></div>
            <div class="wl-status-box"><span>Menunggu</span><strong>${statusCounts.waiting_response ?? 0}</strong></div>
            <div class="wl-status-box"><span>Selesai</span><strong>${statusCounts.resolved ?? 0}</strong></div>
            <div class="wl-status-box"><span>Ditutup</span><strong>${statusCounts.closed ?? 0}</strong></div>
        </div>
        <div class="wl-list-title">Tiket aktif vendor</div>
        ${activeListHtml}
    `;
}

function closeModal() {
    const overlay = document.getElementById('rr-modal-overlay');
    overlay.setAttribute('aria-hidden', 'true');
    overlay.classList.remove('is-open');
    document.body.style.overflow = '';
}

function closeModalOutside(e) {
    if (e.target === document.getElementById('rr-modal-overlay')) closeModal();
}

// Escape key
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
@endsection



