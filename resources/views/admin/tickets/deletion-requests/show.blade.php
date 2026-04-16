@extends('layouts.app')

@section('title', 'Detail Permintaan Hapus')
@section('page_title', 'Detail Permintaan Hapus')
@section('breadcrumb', 'Home / Tiket / Permintaan Hapus / Detail')

@php
    $reasonLabels = [
        'duplicate_ticket'               => 'Tiket duplikat',
        'issue_resolved_without_action'  => 'Masalah sudah selesai tanpa tindakan vendor',
        'wrong_category_or_input'        => 'Salah kategori atau salah input data',
        'ticket_created_by_mistake'      => 'Tiket dibuat tidak sengaja',
        'no_longer_relevant'             => 'Tiket sudah tidak relevan',
    ];
@endphp

@section('content')
<div class="detail-wrapper">

    {{-- -- Hero strip (full width) -- --}}
    <div class="detail-hero">
        <div>
            <div class="hero-label">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                Permintaan Penghapusan
            </div>
            <div class="hero-title">Permintaan #{{ $requestItem->id }}</div>
            <div class="hero-sub">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                {{ $requestItem->ticket->ticket_number ?? '-' }} � {{ $requestItem->ticket->title ?? 'Tiket sudah terhapus' }}
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:.8rem;flex-wrap:wrap;">
            @php
                $heroStatus = match($requestItem->status) {
                    'pending'  => 'Menunggu Review',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    default    => ucfirst($requestItem->status),
                };
            @endphp
            <span class="hero-badge">{{ $heroStatus }}</span>
            <a href="{{ route('admin.ticket-deletion-requests.index') }}" class="btn-back">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Kembali
            </a>
        </div>
    </div>

    {{-- -------------- LEFT COLUMN -------------- --}}
    <div class="detail-left">

        {{-- Info Tiket yang Diminta Hapus --}}
        <div class="d-card">
            <div class="d-card-head">
                <div class="d-card-head-icon blue">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                </div>
                <h3>Informasi Tiket</h3>
            </div>
            <div class="d-card-body">
                <div class="ticket-info-grid">
                    <div class="tinfo-item">
                        <div class="tinfo-label">Nomor Tiket</div>
                        <div class="tinfo-value tinfo-num">{{ $requestItem->ticket->ticket_number ?? '-' }}</div>
                    </div>
                    <div class="tinfo-item">
                        <div class="tinfo-label">Status Tiket</div>
                        <div class="tinfo-value">
                            @php
                                $sLabel = [
                                    'new'         => ['Baru',     'badge-new'],
                                    'open'        => ['Terbuka',  'badge-open'],
                                    'in_progress' => ['Diproses', 'badge-progress'],
                                    'resolved'    => ['Selesai',  'badge-resolved'],
                                    'closed'      => ['Ditutup',  'badge-closed'],
                                    'pending'     => ['Pending',  'badge-pend'],
                                ];
                                $ts  = $requestItem->ticket->status ?? 'open';
                                $slb = $sLabel[$ts] ?? [ucfirst($ts), 'badge-pend'];
                            @endphp
                            <span class="tinfo-badge {{ $slb[1] }}">{{ $slb[0] }}</span>
                        </div>
                    </div>
                    <div class="tinfo-item">
                        <div class="tinfo-label">Prioritas</div>
                        <div class="tinfo-value">
                            @php
                                $pLabel = [
                                    'low'      => ['Rendah', 'pri-low'],
                                    'medium'   => ['Sedang', 'pri-medium'],
                                    'high'     => ['Tinggi', 'pri-high'],
                                    'critical' => ['Kritis', 'pri-critical'],
                                ];
                                $tp  = $requestItem->ticket->priority ?? null;
                                $plb = $pLabel[$tp] ?? ['Belum diatur', 'pri-none'];
                            @endphp
                            <span class="tinfo-badge {{ $plb[1] }}">{{ $plb[0] }}</span>
                        </div>
                    </div>
                    <div class="tinfo-item">
                        <div class="tinfo-label">Kategori</div>
                        <div class="tinfo-value">{{ $requestItem->ticket->category->name ?? 'Tanpa kategori' }}</div>
                    </div>
                    <div class="tinfo-item tinfo-full">
                        <div class="tinfo-label">Judul Tiket</div>
                        <div class="tinfo-value tinfo-title-text">{{ $requestItem->ticket->title ?? 'Tiket sudah terhapus' }}</div>
                    </div>
                    <div class="tinfo-item">
                        <div class="tinfo-label">Ditugaskan ke</div>
                        <div class="tinfo-value">{{ $requestItem->ticket->assignedTo->name ?? 'Belum ditugaskan' }}</div>
                    </div>
                    <div class="tinfo-item">
                        <div class="tinfo-label">Dibuat pada</div>
                        <div class="tinfo-value">{{ $requestItem->ticket->created_at?->format('d M Y, H:i') ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reason card --}}
        <div class="d-card">
            <div class="d-card-head">
                <div class="d-card-head-icon red">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3>Alasan Permintaan</h3>
            </div>
            <div class="d-card-body">
                <div class="reason-list">
                    @foreach(($requestItem->reasons ?? []) as $reason)
                    <div class="reason-item">
                        <div class="reason-check">
                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        {{ $reasonLabels[$reason] ?? $reason }}
                    </div>
                    @endforeach
                </div>

                @if($requestItem->custom_reason)
                <div class="custom-reason-box">
                    <div class="custom-reason-label">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        Alasan Tambahan
                    </div>
                    <div class="custom-reason-text">{{ $requestItem->custom_reason }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Timeline aktivitas --}}
        <div class="d-card">
            <div class="d-card-head">
                <div class="d-card-head-icon amber">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3>Timeline Aktivitas</h3>
            </div>
            <div class="d-card-body">
                <div class="timeline">
                    {{-- Permintaan dibuat --}}
                    <div class="tl-item tl-create">
                        <div class="tl-dot">
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div class="tl-body">
                            <div class="tl-title">Permintaan diajukan</div>
                            <div class="tl-meta">
                                <span class="tl-actor">{{ $requestItem->user->name ?? '-' }}</span>
                                <span class="tl-sep">�</span>
                                <span class="tl-time">{{ $requestItem->created_at?->format('d M Y, H:i') ?? '-' }}</span>
                            </div>
                            <div class="tl-desc">Client mengajukan permintaan penghapusan tiket {{ $requestItem->ticket->ticket_number ?? '' }}.</div>
                        </div>
                    </div>

                    {{-- Status: menunggu atau sudah diproses --}}
                    @if($requestItem->status === 'pending')
                    <div class="tl-item tl-pending">
                        <div class="tl-dot tl-dot--pending">
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/></svg>
                        </div>
                        <div class="tl-body">
                            <div class="tl-title">Menunggu review admin</div>
                            <div class="tl-meta">
                                <span class="tl-time">Belum ada tindakan</span>
                            </div>
                            <div class="tl-desc">Permintaan ini sedang menunggu keputusan dari admin.</div>
                        </div>
                    </div>
                    @else
                    <div class="tl-item {{ $requestItem->status === 'approved' ? 'tl-approve' : 'tl-reject' }}">
                        <div class="tl-dot tl-dot--{{ $requestItem->status }}">
                            @if($requestItem->status === 'approved')
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @else
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            @endif
                        </div>
                        <div class="tl-body">
                            <div class="tl-title">
                                {{ $requestItem->status === 'approved' ? 'Permintaan disetujui & tiket dihapus' : 'Permintaan ditolak' }}
                            </div>
                            <div class="tl-meta">
                                <span class="tl-actor">{{ $requestItem->reviewer->name ?? 'Admin' }}</span>
                                <span class="tl-sep">�</span>
                                <span class="tl-time">{{ $requestItem->reviewed_at?->format('d M Y, H:i') ?? '-' }}</span>
                            </div>
                            @if($requestItem->admin_note)
                            <div class="tl-note">"{{ $requestItem->admin_note }}"</div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Reviewed result (if not pending) --}}
        @if($requestItem->status !== 'pending')
        <div class="d-card">
            <div class="d-card-head">
                <div class="d-card-head-icon {{ $requestItem->status === 'approved' ? 'green' : 'red' }}">
                    @if($requestItem->status === 'approved')
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    @else
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    @endif
                </div>
                <h3>Hasil Review</h3>
            </div>
            <div class="d-card-body">
                <div class="status-result">
                    <span class="status-big {{ $requestItem->status }}">
                        {{ $requestItem->status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                    </span>
                    <div class="meta-row">
                        <div class="meta-icon">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <div class="meta-label">Reviewer</div>
                            <div class="meta-value">{{ $requestItem->reviewer->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="meta-row">
                        <div class="meta-icon">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <div class="meta-label">Waktu Review</div>
                            <div class="meta-value">{{ $requestItem->reviewed_at?->format('d M Y, H:i') ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                @if($requestItem->admin_note)
                <div class="admin-note-box">{{ $requestItem->admin_note }}</div>
                @endif
            </div>
        </div>
        @endif

    </div>

    {{-- -------------- RIGHT COLUMN -------------- --}}
    <div class="detail-right">

        {{-- Submitter info --}}
        <div class="d-card">
            <div class="d-card-head">
                <div class="d-card-head-icon purple">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <h3>Informasi Pengaju</h3>
            </div>
            <div class="d-card-body">
                {{-- Avatar --}}
                <div class="submitter-avatar-row">
                    <div class="submitter-avatar">
                        {{ strtoupper(substr($requestItem->user->name ?? 'U', 0, 2)) }}
                    </div>
                    <div>
                        <div class="submitter-name">{{ $requestItem->user->name ?? '-' }}</div>
                        <div class="submitter-role">Client</div>
                    </div>
                </div>
                <div class="meta-row">
                    <div class="meta-icon">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div class="meta-label">Email</div>
                        <div class="meta-value">{{ $requestItem->user->email ?? '-' }}</div>
                    </div>
                </div>
                <div class="meta-row">
                    <div class="meta-icon">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div class="meta-label">Diajukan Pada</div>
                        <div class="meta-value">{{ $requestItem->created_at?->format('d M Y, H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ringkasan permintaan --}}
        <div class="d-card">
            <div class="d-card-head">
                <div class="d-card-head-icon teal">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3>Ringkasan</h3>
            </div>
            <div class="d-card-body">
                <div class="summary-stat-grid">
                    <div class="summary-stat">
                        <div class="summary-stat-num">{{ count($requestItem->reasons ?? []) }}</div>
                        <div class="summary-stat-label">Alasan dipilih</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-stat-num">{{ $requestItem->custom_reason ? '1' : '0' }}</div>
                        <div class="summary-stat-label">Alasan tambahan</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-stat-num summary-stat-num--{{ $requestItem->status }}">
                            @if($requestItem->status === 'pending') ?
                            @elseif($requestItem->status === 'approved') ?
                            @else ?
                            @endif
                        </div>
                        <div class="summary-stat-label">
                            @if($requestItem->status === 'pending') Menunggu
                            @elseif($requestItem->status === 'approved') Disetujui
                            @else Ditolak
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Durasi sejak diajukan --}}
                <div class="duration-box">
                    <div class="duration-label">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Durasi sejak diajukan
                    </div>
                    <div class="duration-value">
                        @php
                            $diffBase  = $requestItem->reviewed_at ?? now();
                            $diffHours = $requestItem->created_at?->diffInHours($diffBase) ?? 0;
                            if ($diffHours < 1) {
                                    $minutes = (int) $requestItem->created_at?->diffInMinutes($diffBase);
                                    $diffStr = $minutes . ' menit yang lalu';
                            } elseif ($diffHours < 24) {
                                $diffStr = $diffHours . ' jam';
                            } else {
                                $diffStr = round($diffHours / 24) . ' hari';
                            }
                        @endphp
                        {{ $diffStr ?? '-' }}
                        @if($requestItem->status === 'pending')
                            <span class="duration-tag duration-tag--pending">Masih berjalan</span>
                        @else
                            <span class="duration-tag duration-tag--done">Selesai diproses</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Action form (pending only) --}}
        @if($requestItem->status === 'pending')
        <div class="d-card">
            <div class="d-card-head">
                <div class="d-card-head-icon gray">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3>Proses Permintaan</h3>
            </div>
            <div class="d-card-body">
                <form method="POST" action="{{ route('admin.ticket-deletion-requests.process', $requestItem->id) }}">
                    @csrf

                    <div class="warn-note">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Jika disetujui, tiket akan <strong>dihapus permanen</strong> dan tidak dapat dipulihkan.
                    </div>

                    <label class="action-label">Catatan Admin (opsional)</label>
                    <textarea class="action-textarea" name="admin_note" rows="4" placeholder="Tambahkan catatan untuk keputusan Anda..."></textarea>

                    <div class="action-btns">
                        <button type="submit" name="action" value="approve" class="btn-approve">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Approve & Hapus Tiket
                        </button>
                        <button type="submit" name="action" value="reject" class="btn-reject">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Reject Permintaan
                        </button>
                    </div>
                </form>
            </div>
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
        --warn:         #FDCB6E;
        --warn-bg:      #FFF8E7;
        --teal:         #0984e3;
        --teal-bg:      #EAF4FD;
        --amber:        #e17055;
        --amber-bg:     #FFF3EF;
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

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: var(--font); background: var(--bg-page); color: var(--text); -webkit-font-smoothing: antialiased; }

    /* -- Layout -- */
    .detail-wrapper {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 1.25rem;
    }
    .detail-left  { display: flex; flex-direction: column; gap: 1.25rem; }
    .detail-right { display: flex; flex-direction: column; gap: 1.25rem; }

    @media (max-width: 900px) {
        .detail-wrapper { grid-template-columns: 1fr; }
    }

    /* -- Shared card -- */
    .d-card {
        background: var(--surface);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-card);
        overflow: hidden;
    }
    .d-card-head {
        padding: 1.1rem 1.4rem;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: .6rem;
    }
    .d-card-head-icon {
        width: 34px; height: 34px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .d-card-head-icon.red    { background: var(--danger-bg);  color: var(--danger); }
    .d-card-head-icon.green  { background: var(--success-bg); color: var(--success); }
    .d-card-head-icon.purple { background: var(--brand-light); color: var(--brand); }
    .d-card-head-icon.gray   { background: #F3F4F6;            color: var(--text-2); }
    .d-card-head-icon.blue   { background: #EFF6FF;            color: #1d4ed8; }
    .d-card-head-icon.teal   { background: var(--teal-bg);     color: var(--teal); }
    .d-card-head-icon.amber  { background: var(--amber-bg);    color: var(--amber); }
    .d-card-head h3 { font-size: .9rem; font-weight: 800; }
    .d-card-body { padding: 1.3rem 1.4rem; }

    /* -- Hero strip -- */
    .detail-hero {
        grid-column: 1 / -1;
        background: linear-gradient(135deg, #D63031 0%, #FF7675 100%);
        border-radius: var(--radius-card);
        padding: 1.8rem 2rem;
        color: #fff;
        display: flex; align-items: center; justify-content: space-between;
        gap: 1rem; flex-wrap: wrap;
        position: relative; overflow: hidden;
        animation: fadeUp .4s ease both;
    }
    .detail-hero::before {
        content: ''; position: absolute;
        width: 260px; height: 260px; border-radius: 50%;
        background: rgba(255,255,255,.07);
        top: -80px; right: -50px; pointer-events: none;
    }
    .hero-label {
        font-size: .7rem; font-weight: 700; letter-spacing: .12em;
        text-transform: uppercase; opacity: .75;
        display: flex; align-items: center; gap: .4rem; margin-bottom: .5rem;
    }
    .hero-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.02em; line-height: 1.2; }
    .hero-sub   { font-size: .82rem; opacity: .8; margin-top: .35rem; display: flex; align-items: center; gap: .4rem; }
    .hero-badge {
        display: inline-flex; align-items: center; gap: .4rem;
        background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3);
        border-radius: 50px; padding: .45rem 1rem;
        font-size: .75rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; backdrop-filter: blur(4px);
    }
    .hero-badge::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #fff; }
    .btn-back {
        font-family: var(--font); font-size: .82rem; font-weight: 700;
        color: rgba(255,255,255,.85); background: rgba(255,255,255,.15);
        border: 1.5px solid rgba(255,255,255,.3); border-radius: var(--radius-sm);
        padding: .5rem 1rem; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: .4rem; transition: background .2s; white-space: nowrap;
    }
    .btn-back:hover { background: rgba(255,255,255,.25); color: #fff; }

    /* -- Ticket Info Grid -- */
    .ticket-info-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: .75rem;
    }
    .tinfo-item { display: flex; flex-direction: column; gap: .2rem; }
    .tinfo-full { grid-column: 1 / -1; }
    .tinfo-label {
        font-size: .68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .07em; color: var(--text-2);
    }
    .tinfo-value { font-size: .875rem; font-weight: 600; color: var(--text); }
    .tinfo-num   { font-family: monospace; color: var(--danger); font-weight: 700; }
    .tinfo-title-text { color: var(--text); }
    .tinfo-badge {
        display: inline-flex; align-items: center;
        padding: .22rem .65rem; border-radius: 50px;
        font-size: .72rem; font-weight: 700;
    }
    .badge-new      { background: rgba(249,115,22,.12); color: #c2410c; }
    .badge-open     { background: rgba(59,130,246,.12);  color: #1d4ed8; }
    .badge-progress { background: rgba(99,102,241,.12);  color: #4338ca; }
    .badge-resolved { background: rgba(34,197,94,.12);   color: #15803d; }
    .badge-closed   { background: rgba(148,163,184,.14); color: #475569; }
    .badge-pend     { background: rgba(234,179,8,.12);   color: #92400e; }
    .pri-low        { background: rgba(34,197,94,.12);   color: #15803d; }
    .pri-medium     { background: rgba(249,115,22,.12);  color: #c2410c; }
    .pri-high       { background: rgba(239,68,68,.12);   color: #b91c1c; }
    .pri-critical   { background: rgba(127,29,29,.15);   color: #7f1d1d; }
    .pri-none       { background: rgba(148,163,184,.14); color: #475569; }

    /* -- Reason list -- */
    .reason-list { display: flex; flex-direction: column; gap: .55rem; margin-bottom: 1.2rem; }
    .reason-item {
        display: flex; align-items: center; gap: .75rem;
        background: var(--bg-page); border-radius: var(--radius-sm);
        padding: .7rem .9rem; font-size: .84rem; font-weight: 600; color: var(--text);
    }
    .reason-check {
        width: 22px; height: 22px; border-radius: 50%;
        background: var(--danger-bg); color: var(--danger);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .custom-reason-box {
        background: var(--bg-page); border: 1.5px solid var(--border);
        border-radius: var(--radius-sm); padding: 1rem 1.1rem;
    }
    .custom-reason-label {
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: var(--text-2); margin-bottom: .45rem;
        display: flex; align-items: center; gap: .35rem;
    }
    .custom-reason-text { font-size: .855rem; color: var(--text); line-height: 1.6; white-space: pre-wrap; }

    /* -- Timeline -- */
    .timeline { display: flex; flex-direction: column; gap: 0; }
    .tl-item {
        display: flex; gap: 1rem; padding: .75rem 0;
        border-left: 2px solid var(--border); margin-left: .75rem;
        padding-left: 1.25rem; position: relative;
    }
    .tl-item:last-child { border-left-color: transparent; }
    .tl-dot {
        position: absolute; left: -13px; top: .9rem;
        width: 24px; height: 24px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: #EFF6FF; color: #1d4ed8; border: 2px solid #bfdbfe;
        flex-shrink: 0;
    }
    .tl-dot--pending  { background: var(--warn-bg);    color: #92400e;        border-color: var(--warn); }
    .tl-dot--approved { background: var(--success-bg); color: var(--success); border-color: #6ee7b7; }
    .tl-dot--rejected { background: var(--danger-bg);  color: var(--danger);  border-color: #fca5a5; }
    .tl-body { display: flex; flex-direction: column; gap: .2rem; flex: 1; }
    .tl-title { font-size: .875rem; font-weight: 700; color: var(--text); }
    .tl-meta  { display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; }
    .tl-actor { font-size: .78rem; font-weight: 700; color: var(--brand); }
    .tl-sep   { color: var(--text-2); font-size: .78rem; }
    .tl-time  { font-size: .78rem; color: var(--text-2); }
    .tl-desc  { font-size: .8rem; color: var(--text-2); line-height: 1.5; margin-top: .1rem; }
    .tl-note  {
        margin-top: .35rem; font-size: .8rem; color: var(--text);
        background: var(--bg-page); border-left: 3px solid var(--brand-mid);
        padding: .45rem .7rem; border-radius: 0 6px 6px 0;
        font-style: italic; line-height: 1.5;
    }

    /* -- Meta info -- */
    .meta-row {
        display: flex; align-items: center; gap: .6rem;
        padding: .65rem 0; border-bottom: 1px solid var(--border); font-size: .83rem;
    }
    .meta-row:last-child { border-bottom: none; }
    .meta-icon { width: 30px; height: 30px; border-radius: 8px; background: var(--bg-page); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .meta-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-2); }
    .meta-value { font-weight: 600; margin-top: .1rem; font-size: .84rem; }

    /* -- Submitter avatar -- */
    .submitter-avatar-row {
        display: flex; align-items: center; gap: .875rem;
        background: var(--bg-page); border-radius: var(--radius-sm);
        padding: .875rem 1rem; margin-bottom: .75rem;
    }
    .submitter-avatar {
        width: 44px; height: 44px; border-radius: 50%;
        background: var(--brand-light); color: var(--brand);
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: .9rem; flex-shrink: 0;
    }
    .submitter-name { font-weight: 700; font-size: .9rem; color: var(--text); }
    .submitter-role {
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .07em; color: var(--text-2); margin-top: .1rem;
    }

    /* -- Summary stats -- */
    .summary-stat-grid {
        display: grid; grid-template-columns: repeat(3,1fr);
        gap: .75rem; margin-bottom: 1rem;
    }
    .summary-stat {
        background: var(--bg-page); border-radius: var(--radius-sm);
        padding: .75rem; text-align: center;
        border: 1px solid var(--border);
    }
    .summary-stat-num { font-size: 1.5rem; font-weight: 800; color: var(--text); line-height: 1; }
    .summary-stat-label { font-size: .7rem; color: var(--text-2); margin-top: .25rem; font-weight: 600; }
    .summary-stat-num--approved { color: var(--success); }
    .summary-stat-num--rejected { color: var(--danger); }
    .summary-stat-num--pending  { font-size: 1.25rem; }

    .duration-box {
        background: var(--bg-page); border-radius: var(--radius-sm);
        padding: .875rem 1rem; border: 1px solid var(--border);
    }
    .duration-label {
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .07em; color: var(--text-2);
        display: flex; align-items: center; gap: .35rem; margin-bottom: .3rem;
    }
    .duration-value {
        font-size: 1rem; font-weight: 800; color: var(--text);
        display: flex; align-items: center; gap: .5rem; flex-wrap: wrap;
    }
    .duration-tag {
        font-size: .7rem; font-weight: 700; padding: .2rem .55rem;
        border-radius: 50px; text-transform: uppercase; letter-spacing: .05em;
    }
    .duration-tag--pending { background: var(--warn-bg); color: #92400e; }
    .duration-tag--done    { background: var(--success-bg); color: var(--success); }

    /* -- Status card (reviewed) -- */
    .status-result { display: flex; flex-direction: column; gap: .1rem; }
    .status-big {
        display: inline-flex; align-items: center; gap: .5rem;
        border-radius: 50px; padding: .4rem .9rem;
        font-size: .78rem; font-weight: 800;
        letter-spacing: .06em; text-transform: uppercase; margin-bottom: .8rem;
    }
    .status-big::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .status-big.approved { background: var(--success-bg); color: var(--success); }
    .status-big.rejected { background: var(--danger-bg);  color: var(--danger); }
    .admin-note-box {
        background: var(--bg-page); border: 1.5px solid var(--border);
        border-radius: var(--radius-sm); padding: .9rem 1rem;
        font-size: .84rem; color: var(--text); line-height: 1.6;
        white-space: pre-wrap; margin-top: .9rem;
    }

    /* -- Action form -- */
    .action-label {
        font-family: var(--font); font-size: .75rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em; color: var(--text-2);
        display: block; margin-bottom: .45rem;
    }
    .action-textarea {
        font-family: var(--font); font-size: .84rem; color: var(--text);
        background: var(--bg-page); border: 1.5px solid var(--border);
        border-radius: var(--radius-sm); padding: .7rem .9rem;
        width: 100%; resize: vertical; outline: none;
        transition: border-color .2s; margin-bottom: 1rem;
    }
    .action-textarea:focus { border-color: var(--brand-mid); }
    .action-btns { display: flex; flex-direction: column; gap: .55rem; }
    .btn-approve, .btn-reject {
        font-family: var(--font); font-size: .84rem; font-weight: 700;
        border: none; border-radius: var(--radius-sm); padding: .7rem 1rem;
        cursor: pointer; display: flex; align-items: center; justify-content: center; gap: .5rem;
        transition: opacity .2s, transform .2s; width: 100%;
    }
    .btn-approve { background: var(--success); color: #fff; }
    .btn-reject  { background: var(--danger);  color: #fff; }
    .btn-approve:hover, .btn-reject:hover { opacity: .88; transform: translateY(-1px); }
    .warn-note {
        background: var(--warn-bg); border: 1.5px solid #F6D860;
        border-radius: var(--radius-sm); padding: .75rem .9rem;
        font-size: .78rem; color: #7D5A0A;
        display: flex; gap: .5rem; align-items: flex-start;
        margin-bottom: 1rem; line-height: 1.5;
    }

    /* -- Animations -- */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .detail-hero  { animation: fadeUp .4s ease both; }
    .detail-left  { animation: fadeUp .45s ease .07s both; }
    .detail-right { animation: fadeUp .45s ease .12s both; }

    @media (max-width: 768px) {
        .detail-hero { padding: 1.5rem; }
        .ticket-info-grid { grid-template-columns: 1fr; }
        .tinfo-full { grid-column: 1; }
    }
</style>
@endsection

