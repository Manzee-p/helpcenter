@extends('layouts.app')

@section('title', 'Detail Tiket')
@section('page_title', 'Detail Tiket')
@section('breadcrumb', 'Home / Tiket / Detail')



@section('content')
@php
    $user        = Auth::user();
    $canAct      = $ticket->assigned_to === $user->id && $ticket->status !== 'closed';
    $showQuick   = $canAct && !in_array($ticket->status, ['resolved','closed']);
    $canClose    = $canAct && $ticket->status === 'resolved';
    $showFeedback = in_array($ticket->status, ['resolved','closed']);

    $statusLabels = [
        'new'              => 'Baru',
        'in_progress'      => 'Diproses',
        'waiting_response' => 'Menunggu Respons',
        'resolved'         => 'Selesai',
        'closed'           => 'Ditutup',
    ];
    $priorityLabels = [
        'low'      => 'Rendah',
        'medium'   => 'Sedang',
        'high'     => 'Tinggi',
        'urgent'   => 'Mendesak',
        'critical' => 'Kritis',
    ];
    $progressPct = ['new'=>10,'in_progress'=>45,'waiting_response'=>65,'resolved'=>85,'closed'=>100][$ticket->status] ?? 0;
    $initials = collect(explode(' ', $ticket->user->name ?? 'NA'))
        ->filter()->map(fn($w)=>strtoupper(substr($w,0,1)))->implode('');
    $initials = substr($initials ?: 'NA', 0, 2);

    $statusOrder = ['new','in_progress','waiting_response','resolved','closed'];
    $currentIdx  = array_search($ticket->status, $statusOrder);
    $stepDone    = fn($s) => array_search($s, $statusOrder) <= $currentIdx;

    $statusIcons = [
        'new'              => 'bx-bell',
        'in_progress'      => 'bx-loader-alt',
        'waiting_response' => 'bx-time',
        'resolved'         => 'bx-check-circle',
        'closed'           => 'bx-lock-alt',
    ];
@endphp

<div class="vtd-wrap">

    {{-- -- ACTION BAR ----------------------------------- --}}
    <div class="vtd-card">
        <div class="vtd-actionbar">
            <a href="{{ route('vendor.tickets.index') }}" class="vtd-back"><i class='bx bx-arrow-back'></i></a>
            <span class="vtd-pill pill-ticket"><i class='bx bx-hash' style="font-size:.75rem;margin-right:.2rem"></i>{{ $ticket->ticket_number }}</span>
            <span class="vtd-pill pill-pri-{{ $ticket->priority }}">{{ strtoupper($priorityLabels[$ticket->priority] ?? $ticket->priority) }}</span>
            <span class="vtd-pill pill-st-{{ $ticket->status }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
            <span class="vtd-actionbar-meta"><i class='bx bx-time-five'></i>Dibuat {{ optional($ticket->created_at)->diffForHumans() }}</span>
            @if($canAct)
                <button class="vtd-btn-change" onclick="vtdOpen('statusModal')">
                    <i class='bx bx-edit-alt'></i> Ubah Status
                </button>
            @endif
        </div>
    </div>

    {{-- -- TWO-COLUMN GRID ------------------------------ --}}
    <div class="vtd-grid">

        {{-- LEFT COLUMN --}}
        <div>

            {{-- Critical Alert --}}
            @if($ticket->priority === 'critical')
            <div class="vtd-alert-critical">
                <i class='bx bx-error-circle'></i>
                <div>
                    <strong>PRIORITAS KRITIS!</strong>
                    <p>Tiket ini membutuhkan penanganan segera. Mohon segera ditindaklanjuti.</p>
                </div>
            </div>
            @endif

            {{-- Ticket Detail Card --}}
            <div class="vtd-card">
                <div class="vtd-card-header">
                    <div>
                        <h1 class="vtd-ticket-title">{{ $ticket->title }}</h1>
                        <p class="vtd-ticket-sub">Detail masalah dan konteks tiket</p>
                    </div>
                    <span class="vtd-pill pill-st-{{ $ticket->status }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
                </div>
                <div class="vtd-card-body">

                    {{-- Info grid --}}
                    @if($ticket->event_name || $ticket->venue || $ticket->area || $ticket->category)
                    <div class="vtd-info-grid">
                        @if($ticket->event_name)
                        <div class="vtd-info-box">
                            <i class='bx bx-calendar ic-event'></i>
                            <div><span class="ib-label">Acara</span><span class="ib-val">{{ $ticket->event_name }}</span></div>
                        </div>
                        @endif
                        @if($ticket->venue)
                        <div class="vtd-info-box">
                            <i class='bx bx-building ic-venue'></i>
                            <div><span class="ib-label">Lokasi</span><span class="ib-val">{{ $ticket->venue }}</span></div>
                        </div>
                        @endif
                        @if($ticket->area)
                        <div class="vtd-info-box">
                            <i class='bx bx-map ic-area'></i>
                            <div><span class="ib-label">Area</span><span class="ib-val">{{ $ticket->area }}</span></div>
                        </div>
                        @endif
                        @if($ticket->category)
                        <div class="vtd-info-box">
                            <i class='bx bx-category ic-cat'></i>
                            <div><span class="ib-label">Kategori</span><span class="ib-val">{{ $ticket->category->name }}</span></div>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Description --}}
                    <div class="vtd-desc-box">
                        <div class="db-label">Deskripsi Masalah</div>
                        <p>{{ $ticket->description }}</p>
                    </div>

                    {{-- Attachments --}}
                    @if($ticket->attachments && $ticket->attachments->count())
                    <div>
                        <div class="vtd-attach-label">Lampiran</div>
                        <div class="vtd-attach-grid">
                            @foreach($ticket->attachments as $a)
                            <a href="{{ asset('storage/'.$a->file_path) }}" target="_blank" rel="noopener" class="vtd-attach-item">
                                <i class='bx bx-paperclip'></i>
                                <span>{{ $a->file_name }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Additional Infos --}}
                    @if($ticket->additionalInfos && $ticket->additionalInfos->count())
                    <div class="vtd-addinfo-list">
                        <div class="vtd-attach-label" style="margin-top:.25rem;">Informasi Tambahan dari Klien</div>
                        @foreach($ticket->additionalInfos as $info)
                        <div class="vtd-addinfo-item">
                            <div class="vtd-addinfo-meta">
                                <span class="ai-name">{{ $info->user->name ?? 'Klien' }}</span>
                                <span class="ai-time">{{ optional($info->created_at)->diffForHumans() }}</span>
                            </div>
                            <p>{{ $info->note ?: 'Tanpa catatan tambahan.' }}</p>
                            @if($info->photo_path)
                            <a href="{{ asset('storage/'.$info->photo_path) }}" target="_blank" rel="noopener">
                                <i class='bx bx-paperclip'></i>{{ $info->photo_name ?? 'Lihat Lampiran' }}
                            </a>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                </div>
            </div>

            {{-- Quick Actions --}}
            @if($showQuick)
            <div class="vtd-card" style="overflow:visible;">
                <div class="vtd-qa-header">
                    <h6><i class='bx bx-git-branch'></i>Ubah Status Tiket</h6>
                    <span class="vtd-qa-badge">Aksi Cepat</span>
                </div>
                <div class="vtd-qa-grid">
                    @if($ticket->status === 'new')
                    <form method="POST" action="{{ route('vendor.tickets.update-status', $ticket->id) }}" style="margin:0;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="in_progress">
                        <button type="submit" class="vtd-qa-btn">
                            <div class="vtd-qa-icon ic-bg-blue"><i class='bx bx-play-circle'></i></div>
                            <div class="vtd-qa-text"><strong>Mulai Kerja</strong><small>Mulai mengerjakan tiket ini</small></div>
                            <i class='bx bx-chevron-right qa-arrow'></i>
                        </button>
                    </form>
                    @endif

                    @if($ticket->status === 'in_progress')
                    <form method="POST" action="{{ route('vendor.tickets.update-status', $ticket->id) }}" style="margin:0;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="waiting_response">
                        <button type="submit" class="vtd-qa-btn">
                            <div class="vtd-qa-icon ic-bg-amber"><i class='bx bx-message-dots'></i></div>
                            <div class="vtd-qa-text"><strong>Butuh Info</strong><small>Minta respons dari klien</small></div>
                            <i class='bx bx-chevron-right qa-arrow'></i>
                        </button>
                    </form>
                    <button type="button" class="vtd-qa-btn" onclick="vtdOpen('reportModal')">
                        <div class="vtd-qa-icon ic-bg-green"><i class='bx bx-check-circle'></i></div>
                        <div class="vtd-qa-text"><strong>Lapor Selesai</strong><small>Laporkan tugas telah selesai</small></div>
                        <i class='bx bx-chevron-right qa-arrow'></i>
                    </button>
                    @endif

                    @if($ticket->status === 'waiting_response')
                    <form method="POST" action="{{ route('vendor.tickets.update-status', $ticket->id) }}" style="margin:0;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="in_progress">
                        <button type="submit" class="vtd-qa-btn">
                            <div class="vtd-qa-icon ic-bg-blue"><i class='bx bx-revision'></i></div>
                            <div class="vtd-qa-text"><strong>Lanjutkan Proses</strong><small>Kembali ke status diproses</small></div>
                            <i class='bx bx-chevron-right qa-arrow'></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endif

            {{-- Close Ticket --}}
            @if($canClose)
            <div class="vtd-close-card">
                <div class="close-header"><i class='bx bx-check-double'></i>Tiket Diselesaikan</div>
                <div class="close-body">
                    <i class='bx bx-check-shield close-icon'></i>
                    <p>Tiket ini sudah ditandai selesai. Tutup secara permanen jika proses benar-benar final.</p>
                    <form method="POST" action="{{ route('vendor.tickets.update-status', $ticket->id) }}" style="margin:0;display:inline;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="closed">
                        <button type="submit" class="vtd-close-btn"><i class='bx bx-lock'></i> Tutup Tiket Permanen</button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Feedback --}}
            @if($showFeedback)
            <div class="vtd-feedback-card">
                <div class="vtd-feedback-header"><i class='bx bx-star'></i><h6>Penilaian dari Klien</h6></div>
                <div class="vtd-feedback-body">
                    @if($ticket->feedback)
                        <div class="vtd-stars">
                            @for($s=1;$s<=5;$s++)
                                <i class='bx bxs-star {{ $s <= $ticket->feedback->rating ? "active" : "" }}'></i>
                            @endfor
                        </div>
                        <div class="vtd-feedback-rating">{{ $ticket->feedback->rating }}/5 bintang</div>
                        <p>{{ $ticket->feedback->comment ?: 'Klien tidak menambahkan komentar.' }}</p>
                    @else
                        <p>Klien belum memberikan penilaian untuk tiket ini.</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Stats Summary --}}
            <div class="vtd-card">
                <div class="vtd-card-header" style="background:linear-gradient(135deg,#f0f4ff,#e8f5e9);">
                    <h6><i class='bx bx-bar-chart-alt-2'></i>Ringkasan Statistik Tiket</h6>
                </div>
                <div class="vtd-stats-grid">
                    <div class="vtd-stat-block">
                        <div class="vtd-stat-icon si-time"><i class='bx bx-time-five'></i></div>
                        <div><div class="vtd-stat-label">Durasi Dibuat</div><div class="vtd-stat-val">{{ optional($ticket->created_at)->diffForHumans() }}</div></div>
                    </div>
                    <div class="vtd-stat-block">
                        <div class="vtd-stat-icon si-start"><i class='bx bx-play-circle'></i></div>
                        <div><div class="vtd-stat-label">Mulai Dikerjakan</div><div class="vtd-stat-val">{{ $ticket->assigned_at ? $ticket->assigned_at->format('d M Y H:i') : '—' }}</div></div>
                    </div>
                    <div class="vtd-stat-block">
                        <div class="vtd-stat-icon si-response"><i class='bx bx-message-check'></i></div>
                        <div><div class="vtd-stat-label">Respon Pertama</div><div class="vtd-stat-val">{{ $ticket->first_response_at ? $ticket->first_response_at->format('d M Y H:i') : 'Belum ada' }}</div></div>
                    </div>
                    <div class="vtd-stat-block">
                        <div class="vtd-stat-icon si-status-{{ $ticket->status }}"><i class='bx {{ $statusIcons[$ticket->status] ?? "bx-circle" }}'></i></div>
                        <div><div class="vtd-stat-label">Status Saat Ini</div><div class="vtd-stat-val">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</div></div>
                    </div>
                    <div class="vtd-stat-block">
                        <div class="vtd-stat-icon si-resolve"><i class='bx bx-check-circle'></i></div>
                        <div><div class="vtd-stat-label">Diselesaikan</div><div class="vtd-stat-val">{{ $ticket->resolved_at ? $ticket->resolved_at->format('d M Y H:i') : 'Belum selesai' }}</div></div>
                    </div>
                    <div class="vtd-stat-block">
                        <div class="vtd-stat-icon si-category"><i class='bx bx-category'></i></div>
                        <div><div class="vtd-stat-label">Kategori</div><div class="vtd-stat-val">{{ $ticket->category->name ?? '—' }}</div></div>
                    </div>
                </div>
                <div class="vtd-progress-wrap">
                    <div class="vtd-progress-label">
                        <span>Progress Penanganan</span>
                        <strong>{{ $progressPct }}%</strong>
                    </div>
                    <div class="vtd-progress-bar">
                        <div class="vtd-progress-fill pf-{{ $ticket->status }}"></div>
                    </div>
                    <div class="vtd-progress-steps">
                        @foreach(['new'=>'Baru','in_progress'=>'Diproses','waiting_response'=>'Menunggu','resolved'=>'Selesai','closed'=>'Ditutup'] as $sk=>$sl)
                        <span class="vtd-pstep {{ $stepDone($sk) ? 'done' : '' }}">{{ $sl }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Checklist Guide --}}
            <div class="vtd-card" style="margin-top:1rem;">
                <div class="vtd-card-header" style="background:linear-gradient(135deg,#fffbeb,#fce7f3);">
                    <h6><i class='bx bx-list-check'></i>Panduan Penanganan Tiket</h6>
                    <small style="color:#94a3b8;">Ikuti langkah berikut</small>
                </div>
                <div class="vtd-card-body">
                    <div class="vtd-checklist">
                        <div class="vtd-chk-item {{ $stepDone('new') ? 'done' : 'pending' }}">
                            <div class="vtd-chk-dot">{{ $stepDone('new') ? '' : '1' }}{!! $stepDone('new') ? '<i class="bx bx-check"></i>' : '' !!}</div>
                            <div><div class="vtd-chk-title">Terima & Pelajari Tiket</div><div class="vtd-chk-desc">Baca deskripsi masalah, lokasi, dan prioritas tiket dengan seksama.</div></div>
                        </div>
                        <div class="vtd-chk-item {{ $stepDone('in_progress') ? 'done' : 'pending' }}">
                            <div class="vtd-chk-dot">{{ $stepDone('in_progress') ? '' : '2' }}{!! $stepDone('in_progress') ? '<i class="bx bx-check"></i>' : '' !!}</div>
                            <div><div class="vtd-chk-title">Mulai Kerjakan & Ubah Status</div><div class="vtd-chk-desc">Ubah status ke "Diproses" dan segera menuju lokasi yang ditentukan.</div></div>
                        </div>
                        <div class="vtd-chk-item {{ $stepDone('waiting_response') ? 'done' : 'pending' }}">
                            <div class="vtd-chk-dot">{{ $stepDone('waiting_response') ? '' : '3' }}{!! $stepDone('waiting_response') ? '<i class="bx bx-check"></i>' : '' !!}</div>
                            <div><div class="vtd-chk-title">Komunikasi ke User</div><div class="vtd-chk-desc">Sampaikan progres, kendala, atau kebutuhan informasi tambahan kepada klien.</div></div>
                        </div>
                        <div class="vtd-chk-item {{ $stepDone('resolved') ? 'done' : 'pending' }}">
                            <div class="vtd-chk-dot">{{ $stepDone('resolved') ? '' : '4' }}{!! $stepDone('resolved') ? '<i class="bx bx-check"></i>' : '' !!}</div>
                            <div><div class="vtd-chk-title">Lapor Penyelesaian</div><div class="vtd-chk-desc">Kirim laporan penyelesaian dan ubah status tiket menjadi Selesai.</div></div>
                        </div>
                        <div class="vtd-chk-item {{ $stepDone('closed') ? 'done' : 'pending' }}">
                            <div class="vtd-chk-dot">{{ $stepDone('closed') ? '' : '5' }}{!! $stepDone('closed') ? '<i class="bx bx-check"></i>' : '' !!}</div>
                            <div><div class="vtd-chk-title">Konfirmasi & Tutup Tiket</div><div class="vtd-chk-desc">Tunggu konfirmasi klien lalu tutup tiket secara permanen.</div></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /LEFT --}}

        {{-- RIGHT SIDEBAR --}}
        <div>

            {{-- Client Info --}}
            <div class="vtd-card">
                <div class="vtd-card-header">
                    <h6><i class='bx bx-user'></i>Detail Klien</h6>
                </div>
                <div class="vtd-card-body">
                    <div class="vtd-client-row">
                        <div class="vtd-avatar">{{ $initials }}</div>
                        <div>
                            <p class="vtd-client-name">{{ $ticket->user->name ?? '-' }}</p>
                            <p class="vtd-client-role">Klien</p>
                        </div>
                    </div>
                    @if($ticket->user->email)
                    <div class="vtd-contact-item"><i class='bx bx-envelope'></i><a href="mailto:{{ $ticket->user->email }}">{{ $ticket->user->email }}</a></div>
                    @endif
                    @if($ticket->user->phone ?? null)
                    <div class="vtd-contact-item"><i class='bx bx-phone'></i><a href="tel:{{ $ticket->user->phone }}">{{ $ticket->user->phone }}</a></div>
                    @endif
                </div>
            </div>

            {{-- Ticket Info --}}
            <div class="vtd-card">
                <div class="vtd-card-header">
                    <h6><i class='bx bx-info-circle'></i>Informasi Tiket</h6>
                </div>
                <div class="vtd-card-body">
                    <table class="vtd-meta-table">
                        <tr><td>Status</td><td><span class="vtd-pill pill-st-{{ $ticket->status }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span></td></tr>
                        <tr><td>Prioritas</td><td><span class="vtd-pill pill-pri-{{ $ticket->priority }}">{{ strtoupper($priorityLabels[$ticket->priority] ?? $ticket->priority) }}</span></td></tr>
                        <tr><td>Kategori</td><td>{{ $ticket->category->name ?? '-' }}</td></tr>
                        <tr><td>Dibuat</td><td>{{ $ticket->created_at ? $ticket->created_at->format('d M Y H:i') : '-' }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- SLA --}}
            @if($ticket->slaTracking)
            <div class="vtd-card">
                <div class="vtd-card-header">
                    <h6><i class='bx bx-stopwatch'></i>Performa SLA</h6>
                </div>
                <div class="vtd-card-body">
                    <div class="vtd-sla-block">
                        <div class="vtd-sla-top">
                            <span>Waktu Respons</span>
                            @if($ticket->slaTracking->response_sla_met !== null)
                            <span class="vtd-pill {{ $ticket->slaTracking->response_sla_met ? 'pill-pri-low' : 'pill-pri-high' }}">
                                {{ $ticket->slaTracking->response_sla_met ? 'TERPENUHI' : 'TERLEWATI' }}
                            </span>
                            @endif
                        </div>
                        @if($ticket->slaTracking->actual_response_time)
                        @php $rPct = min(100, ($ticket->slaTracking->actual_response_time / max(1,$ticket->slaTracking->response_time_sla ?? 1)) * 100); @endphp
                        <div class="vtd-sla-bar"><div class="vtd-sla-fill {{ $ticket->slaTracking->response_sla_met ? 'sla-met' : 'sla-miss' }}" style="width:{{ $rPct }}%"></div></div>
                        <div class="vtd-sla-time">{{ $ticket->slaTracking->actual_response_time }} / {{ $ticket->slaTracking->response_time_sla ?? '?' }} menit</div>
                        @else
                        <div class="vtd-sla-wait"><i class='bx bx-time'></i>Menunggu respons pertama</div>
                        @endif
                    </div>
                    <div class="vtd-sla-block">
                        <div class="vtd-sla-top">
                            <span>Waktu Penyelesaian</span>
                            @if($ticket->slaTracking->resolution_sla_met !== null)
                            <span class="vtd-pill {{ $ticket->slaTracking->resolution_sla_met ? 'pill-pri-low' : 'pill-pri-high' }}">
                                {{ $ticket->slaTracking->resolution_sla_met ? 'TERPENUHI' : 'TERLEWATI' }}
                            </span>
                            @endif
                        </div>
                        @if($ticket->slaTracking->actual_resolution_time)
                        @php $resPct = min(100, ($ticket->slaTracking->actual_resolution_time / max(1,$ticket->slaTracking->resolution_time_sla ?? 1)) * 100); @endphp
                        <div class="vtd-sla-bar"><div class="vtd-sla-fill {{ $ticket->slaTracking->resolution_sla_met ? 'sla-met' : 'sla-miss' }}" style="width:{{ $resPct }}%"></div></div>
                        <div class="vtd-sla-time">{{ $ticket->slaTracking->actual_resolution_time }} / {{ $ticket->slaTracking->resolution_time_sla ?? '?' }} menit</div>
                        @else
                        <div class="vtd-sla-wait"><i class='bx bx-time'></i>Sedang diproses</div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- History Timeline --}}
            <div class="vtd-card">
                <div class="vtd-card-header" style="background:linear-gradient(135deg,#ede7f6,#e3f2fd);">
                    <h6><i class='bx bx-git-branch'></i>Riwayat Perubahan Status</h6>
                </div>
                <div class="vtd-card-body">
                    <div class="vtd-timeline">
                        <div class="vtd-t-item">
                            <span class="vtd-t-dot td-created"><i class='bx bx-plus-circle'></i></span>
                            <div>
                                <div class="vtd-t-status">Tiket Dibuat</div>
                                <div class="vtd-t-meta"><i class='bx bx-user'></i>{{ $ticket->user->name ?? 'Klien' }}<span style="color:#d1d5db">·</span><i class='bx bx-calendar'></i>{{ $ticket->created_at ? $ticket->created_at->format('d M Y H:i') : '-' }}</div>
                            </div>
                        </div>
                        @if($ticket->assigned_at)
                        <div class="vtd-t-item">
                            <span class="vtd-t-dot td-assigned"><i class='bx bx-user-pin'></i></span>
                            <div>
                                <div class="vtd-t-status">Ditugaskan ke Vendor</div>
                                <div class="vtd-t-meta"><i class='bx bx-shield-alt-2'></i>Admin<span style="color:#d1d5db">·</span><i class='bx bx-calendar'></i>{{ $ticket->assigned_at->format('d M Y H:i') }}</div>
                            </div>
                        </div>
                        @endif
                        @if($ticket->first_response_at)
                        <div class="vtd-t-item">
                            <span class="vtd-t-dot td-response"><i class='bx bx-message-dots'></i></span>
                            <div>
                                <div class="vtd-t-status">Respon Pertama Dikirim</div>
                                <div class="vtd-t-meta"><i class='bx bx-calendar'></i>{{ $ticket->first_response_at->format('d M Y H:i') }}</div>
                            </div>
                        </div>
                        @endif
                        @if($ticket->resolved_at)
                        <div class="vtd-t-item">
                            <span class="vtd-t-dot td-resolved"><i class='bx bx-check-circle'></i></span>
                            <div>
                                <div class="vtd-t-status">Tiket Diselesaikan</div>
                                <div class="vtd-t-meta"><i class='bx bx-calendar'></i>{{ $ticket->resolved_at->format('d M Y H:i') }}</div>
                            </div>
                        </div>
                        @endif
                        @if($ticket->closed_at)
                        <div class="vtd-t-item">
                            <span class="vtd-t-dot td-closed"><i class='bx bx-lock-alt'></i></span>
                            <div>
                                <div class="vtd-t-status">Tiket Ditutup</div>
                                <div class="vtd-t-meta"><i class='bx bx-calendar'></i>{{ $ticket->closed_at->format('d M Y H:i') }}</div>
                            </div>
                        </div>
                        @endif
                        @if(!$ticket->assigned_at && !$ticket->first_response_at && !$ticket->resolved_at)
                        <div class="vtd-t-empty"><i class='bx bx-time-five'></i><span>Belum ada perubahan status lanjutan</span></div>
                        @endif
                    </div>
                </div>
            </div>

        </div>{{-- /RIGHT --}}

    </div>{{-- /grid --}}
</div>{{-- /wrap --}}

{{-- -- MODALS ------------------------------------------------ --}}

{{-- Report Modal --}}
<div id="reportModal" class="vtd-modal-ov">
    <div class="vtd-modal-box">
        <div class="vtd-modal-header">
            <i class='bx bx-check-circle'></i>
            <h5>Lapor Penyelesaian Tugas</h5>
        </div>
        <div class="vtd-modal-body">
            <p>Apakah Anda yakin tugas ini sudah selesai dikerjakan?</p>
            <p>Status tiket akan diubah menjadi <strong>Selesai</strong> dan menunggu konfirmasi dari admin.</p>
        </div>
        <div class="vtd-modal-footer">
            <button type="button" class="vtd-btn-cancel" onclick="vtdClose('reportModal')">Batal</button>
            <form method="POST" action="{{ route('vendor.tickets.update-status', $ticket->id) }}" style="margin:0;">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="resolved">
                <button type="submit" class="vtd-btn-confirm"><i class='bx bx-check'></i> Ya, Laporkan Selesai</button>
            </form>
        </div>
    </div>
</div>

{{-- Status Modal --}}
<div id="statusModal" class="vtd-modal-ov">
    <div class="vtd-modal-box">
        <div class="vtd-modal-header">
            <i class='bx bx-edit-alt'></i>
            <h5>Ubah Status Tiket</h5>
        </div>
        <div class="vtd-modal-body">
            <p>Pilih status baru untuk tiket ini:</p>
            <form id="statusForm" method="POST" action="{{ route('vendor.tickets.update-status', $ticket->id) }}" style="margin:0;">
                @csrf @method('PATCH')
                <select name="status" class="form-select">
                    <option value="new"              @selected($ticket->status==='new')>Baru</option>
                    <option value="in_progress"      @selected($ticket->status==='in_progress')>Diproses</option>
                    <option value="waiting_response" @selected($ticket->status==='waiting_response')>Menunggu Respons</option>
                    <option value="resolved"         @selected($ticket->status==='resolved')>Selesai</option>
                </select>
        </div>
        <div class="vtd-modal-footer">
            <button type="button" class="vtd-btn-cancel" onclick="vtdClose('statusModal')">Batal</button>
            <button type="submit" form="statusForm" class="vtd-btn-confirm"><i class='bx bx-check'></i> Simpan</button>
        </div>
            </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function vtdOpen(id){ document.getElementById(id).classList.add('open'); }
function vtdClose(id){ document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.vtd-modal-ov').forEach(function(m){
    m.addEventListener('click', function(e){ if(e.target === m) m.classList.remove('open'); });
});
document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') document.querySelectorAll('.vtd-modal-ov.open').forEach(function(m){ m.classList.remove('open'); });
});
</script>
@endpush

@push('styles')
<style>
/* --- Reset & Base --------------------------------------- */
*, *::before, *::after { box-sizing: border-box; }

/* --- Layout ---------------------------------------------- */
.vtd-wrap { display: flex; flex-direction: column; gap: 1rem; animation: vtdFade .35s ease; }
@keyframes vtdFade { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:none; } }

.vtd-grid { display: grid; grid-template-columns: 1fr 320px; gap: 1rem; }
@media (max-width:991px){ .vtd-grid { grid-template-columns: 1fr; } }

/* --- Card ------------------------------------------------ */
.vtd-card {
    background: #fff;
    border: 0.5px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 1rem;
}
.vtd-card-header {
    padding: .875rem 1.125rem;
    border-bottom: 0.5px solid #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.vtd-card-header h6 {
    margin: 0;
    font-size: .875rem;
    font-weight: 600;
    color: #111827;
    display: flex;
    align-items: center;
    gap: .45rem;
}
.vtd-card-header h6 i { font-size: 1rem; color: #6366f1; }
.vtd-card-body { padding: 1.125rem; }

/* --- Action Bar ------------------------------------------ */
.vtd-actionbar { padding: .875rem 1.125rem; display: flex; flex-wrap: wrap; align-items: center; gap: .55rem; }
.vtd-back {
    width: 34px; height: 34px;
    border-radius: 9px;
    border: 0.5px solid #d1d5db;
    background: #fff;
    color: #4b5563;
    display: inline-flex; align-items: center; justify-content: center;
    text-decoration: none;
    transition: all .2s;
    flex-shrink: 0;
}
.vtd-back:hover { background: #f5f5ff; color: #4f46e5; border-color: #a5b4fc; }
.vtd-back i { font-size: 1.1rem; }
.vtd-actionbar-meta { color: #94a3b8; font-size: .82rem; display: flex; align-items: center; gap: .3rem; }
.vtd-actionbar-meta i { font-size: .9rem; }
.vtd-btn-change {
    margin-left: auto;
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .45rem .875rem;
    border-radius: 9px;
    border: 0;
    background: linear-gradient(135deg, #6366f1 0%, #7c3aed 100%);
    color: #fff;
    font-size: .82rem;
    font-weight: 600;
    cursor: pointer;
    transition: filter .2s;
}
.vtd-btn-change:hover { filter: brightness(.93); }

/* --- Badges / Pills --------------------------------------- */
.vtd-pill {
    display: inline-flex; align-items: center;
    border-radius: 999px;
    padding: .22rem .65rem;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .02em;
}
.pill-ticket { background: #eef2ff; color: #4338ca; }
/* Priority */
.pill-pri-low    { background: #dcfce7; color: #166534; }
.pill-pri-medium { background: #fef9c3; color: #854d0e; }
.pill-pri-high,
.pill-pri-urgent,
.pill-pri-critical { background: #fee2e2; color: #991b1b; }
/* Status */
.pill-st-new             { background: #fef9c3; color: #854d0e; }
.pill-st-in_progress     { background: #dbeafe; color: #1e40af; }
.pill-st-waiting_response{ background: #ede9fe; color: #5b21b6; }
.pill-st-resolved        { background: #dcfce7; color: #166534; }
.pill-st-closed          { background: #f1f5f9; color: #374151; }

/* --- Ticket Title ------------------------------------------ */
.vtd-ticket-title { margin: 0 0 .15rem; font-size: 1.15rem; font-weight: 700; color: #111827; line-height: 1.35; }
.vtd-ticket-sub { margin: 0; color: #6b7280; font-size: .83rem; }

/* --- Critical Alert --------------------------------------- */
.vtd-alert-critical {
    display: flex; align-items: center; gap: 1rem;
    padding: .875rem 1.125rem;
    background: #fff1f2;
    border: 0.5px solid #fecdd3;
    border-radius: 12px;
    margin-bottom: 1rem;
}
.vtd-alert-critical i { font-size: 1.75rem; color: #e11d48; flex-shrink: 0; }
.vtd-alert-critical strong { display: block; color: #be123c; font-size: .85rem; margin-bottom: .15rem; }
.vtd-alert-critical p { margin: 0; color: #9f1239; font-size: .82rem; }

/* --- Info Grid (event / venue / area / category) -------- */
.vtd-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: .6rem;
    margin-bottom: .875rem;
}
.vtd-info-box {
    background: #f8fafc;
    border: 0.5px solid #e5e7eb;
    border-radius: 10px;
    padding: .65rem .75rem;
    display: flex;
    align-items: flex-start;
    gap: .55rem;
}
.vtd-info-box i { font-size: 1.1rem; flex-shrink: 0; margin-top: .1rem; }
.vtd-info-box .ib-label { display: block; font-size: .68rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .1rem; }
.vtd-info-box .ib-val { font-size: .84rem; font-weight: 600; color: #1e293b; }
.ic-event { color: #6366f1; }
.ic-venue { color: #0ea5e9; }
.ic-area  { color: #10b981; }
.ic-cat   { color: #f59e0b; }

/* --- Description ------------------------------------------ */
.vtd-desc-box {
    background: #f8fafc;
    border: 0.5px solid #e5e7eb;
    border-radius: 10px;
    padding: .75rem .875rem;
    margin-bottom: .875rem;
}
.vtd-desc-box .db-label { font-size: .72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .45rem; font-weight: 600; }
.vtd-desc-box p { margin: 0; color: #374151; font-size: .88rem; line-height: 1.7; white-space: pre-wrap; }

/* --- Attachments ------------------------------------------ */
.vtd-attach-label { font-size: .72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; font-weight: 600; margin-bottom: .5rem; }
.vtd-attach-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(155px,1fr)); gap: .5rem; }
.vtd-attach-item {
    display: flex; align-items: center; gap: .45rem;
    padding: .55rem .7rem;
    background: #f8fafc;
    border: 0.5px solid #e5e7eb;
    border-radius: 9px;
    color: #334155;
    font-size: .82rem;
    text-decoration: none;
    transition: all .2s;
    overflow: hidden;
}
.vtd-attach-item i { font-size: 1rem; flex-shrink: 0; color: #6366f1; }
.vtd-attach-item span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.vtd-attach-item:hover { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }

/* --- Additional Info -------------------------------------- */
.vtd-addinfo-list { display: flex; flex-direction: column; gap: .65rem; margin-top: 1rem; }
.vtd-addinfo-item {
    border: 0.5px solid #e5e7eb;
    border-radius: 11px;
    padding: .875rem;
    background: #f8fbff;
}
.vtd-addinfo-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: .45rem; gap: .5rem; }
.vtd-addinfo-meta .ai-name { font-size: .75rem; font-weight: 700; color: #6366f1; background: #eef2ff; border-radius: 6px; padding: .15rem .45rem; }
.vtd-addinfo-meta .ai-time { font-size: .75rem; color: #94a3b8; }
.vtd-addinfo-item p { margin: 0 0 .45rem; color: #6b7280; font-size: .84rem; }
.vtd-addinfo-item a {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .8rem; color: #6366f1;
    border: 0.5px solid #c7d2fe;
    border-radius: 7px;
    padding: .3rem .6rem;
    text-decoration: none;
    transition: background .2s;
}
.vtd-addinfo-item a:hover { background: #eef2ff; }

/* --- Status Quick Actions --------------------------------- */
.vtd-qa-header {
    padding: .875rem 1.125rem;
    display: flex; align-items: center; justify-content: space-between;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}
.vtd-qa-header h6 { margin: 0; font-size: .875rem; font-weight: 600; color: #fff; display: flex; align-items: center; gap: .4rem; }
.vtd-qa-badge { font-size: .68rem; border: 0.5px solid rgba(255,255,255,.45); color: #fff; border-radius: 999px; padding: .18rem .55rem; }
.vtd-qa-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(185px,1fr)); gap: .65rem; padding: 1rem 1.125rem; }
.vtd-qa-btn {
    display: flex; align-items: center; gap: .75rem;
    padding: .875rem;
    background: #fff;
    border: 0.5px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: all .25s;
    text-align: left;
    width: 100%;
}
.vtd-qa-btn:hover { border-color: #818cf8; background: #f8f8ff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(99,102,241,.12); }
.vtd-qa-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; box-shadow: none; }
.vtd-qa-icon { width: 44px; height: 44px; border-radius: 11px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.vtd-qa-icon i { font-size: 1.35rem; color: #fff; }
.ic-bg-blue   { background: #0ea5e9; }
.ic-bg-amber  { background: #f59e0b; }
.ic-bg-green  { background: #10b981; }
.vtd-qa-text { flex: 1; }
.vtd-qa-text strong { display: block; font-size: .84rem; color: #1e293b; font-weight: 600; }
.vtd-qa-text small { font-size: .75rem; color: #94a3b8; }
.vtd-qa-btn .qa-arrow { font-size: 1.1rem; color: #d1d5db; transition: all .25s; }
.vtd-qa-btn:hover .qa-arrow { color: #818cf8; transform: translateX(3px); }

/* --- Close Ticket Card ------------------------------------ */
.vtd-close-card {
    border: 0.5px solid #bbf7d0;
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 1rem;
}
.vtd-close-card .close-header {
    padding: .7rem 1.125rem;
    background: #10b981;
    display: flex; align-items: center; gap: .45rem;
    color: #fff; font-size: .875rem; font-weight: 600;
}
.vtd-close-card .close-header i { font-size: 1.1rem; }
.vtd-close-card .close-body { padding: 1.25rem; text-align: center; background: #fff; }
.vtd-close-card .close-icon { font-size: 2.75rem; color: #10b981; display: block; margin-bottom: .6rem; }
.vtd-close-card .close-body p { margin: 0 0 .875rem; color: #475569; font-size: .85rem; }
.vtd-close-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .55rem 1.125rem;
    border: 0.5px solid #d1d5db;
    border-radius: 9px;
    background: #fff;
    font-size: .84rem;
    font-weight: 600;
    color: #1f2937;
    cursor: pointer;
    transition: all .2s;
}
.vtd-close-btn:hover { background: #f8fafc; border-color: #9ca3af; }

/* --- Feedback Card --------------------------------------- */
.vtd-feedback-card { border: 0.5px solid #fde68a; border-radius: 14px; overflow: hidden; margin-bottom: 1rem; }
.vtd-feedback-header { padding: .7rem 1.125rem; background: #fffbeb; border-bottom: 0.5px solid #fde68a; display: flex; align-items: center; gap: .4rem; }
.vtd-feedback-header h6 { margin: 0; font-size: .875rem; font-weight: 600; color: #92400e; }
.vtd-feedback-header i { color: #f59e0b; font-size: 1rem; }
.vtd-feedback-body { padding: 1rem 1.125rem; background: #fff; }
.vtd-stars { display: flex; gap: .2rem; margin-bottom: .4rem; }
.vtd-stars i { font-size: 1rem; color: #d1d5db; }
.vtd-stars i.active { color: #f59e0b; }
.vtd-feedback-rating { font-weight: 700; font-size: .9rem; color: #1f2937; margin-bottom: .3rem; }
.vtd-feedback-body p { margin: 0; color: #6b7280; font-size: .85rem; }

/* --- Stats Summary ---------------------------------------- */
.vtd-stats-grid {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    border-bottom: 0.5px solid #f0f0f0;
}
@media (max-width:767px){ .vtd-stats-grid { grid-template-columns: repeat(2,1fr); } }
.vtd-stat-block {
    display: flex; align-items: center; gap: .7rem;
    padding: .875rem 1rem;
    border-right: 0.5px solid #f0f0f0;
    border-bottom: 0.5px solid #f0f0f0;
    transition: background .2s;
}
.vtd-stat-block:hover { background: #fafbff; }
.vtd-stat-block:nth-child(3n) { border-right: none; }
.vtd-stat-icon { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .95rem; flex-shrink: 0; }
.si-time     { background: #e0f2fe; color: #0369a1; }
.si-start    { background: #dcfce7; color: #166534; }
.si-response { background: #fef9c3; color: #854d0e; }
.si-resolve  { background: #f0fdf4; color: #15803d; }
.si-category { background: #fce7f3; color: #9d174d; }
.si-status-new       { background: #fef9c3; color: #854d0e; }
.si-status-in_progress { background: #dbeafe; color: #1e40af; }
.si-status-waiting_response { background: #ede9fe; color: #5b21b6; }
.si-status-resolved  { background: #dcfce7; color: #166534; }
.si-status-closed    { background: #f1f5f9; color: #374151; }
.vtd-stat-label { font-size: .67rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; font-weight: 600; margin-bottom: .15rem; }
.vtd-stat-val   { font-size: .83rem; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Progress bar area */
.vtd-progress-wrap { padding: 1rem 1.125rem; }
.vtd-progress-label { display: flex; justify-content: space-between; margin-bottom: .35rem; font-size: .78rem; }
.vtd-progress-label span { color: #94a3b8; }
.vtd-progress-bar { height: 7px; border-radius: 8px; background: #f0f0f0; overflow: hidden; }
.vtd-progress-fill { height: 100%; border-radius: 8px; transition: width .6s ease; }
.pf-new              { background: #f59e0b; width:10%; }
.pf-in_progress      { background: #0ea5e9; width:45%; }
.pf-waiting_response { background: #8b5cf6; width:65%; }
.pf-resolved         { background: #10b981; width:85%; }
.pf-closed           { background: #374151; width:100%; }
.vtd-progress-steps { display: flex; justify-content: space-between; margin-top: .45rem; }
.vtd-pstep { font-size: .67rem; color: #d1d5db; font-weight: 500; }
.vtd-pstep.done { color: #6366f1; font-weight: 700; }

/* --- Checklist / Guide ------------------------------------ */
.vtd-checklist { display: flex; flex-direction: column; }
.vtd-chk-item {
    display: flex; align-items: flex-start; gap: .875rem;
    padding: .875rem 0;
    border-bottom: 0.5px dashed #f0f0f0;
    transition: all .2s;
}
.vtd-chk-item:last-child { border-bottom: none; }
.vtd-chk-dot {
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem; font-weight: 700;
    flex-shrink: 0;
    transition: all .3s;
}
.vtd-chk-item.done .vtd-chk-dot { background: linear-gradient(135deg,#10b981,#059669); color: #fff; font-size: 1rem; }
.vtd-chk-item.pending .vtd-chk-dot { background: #f1f5f9; color: #94a3b8; border: 1.5px dashed #cbd5e1; }
.vtd-chk-title { font-size: .84rem; font-weight: 600; margin-bottom: .18rem; }
.vtd-chk-item.done .vtd-chk-title { color: #1e293b; }
.vtd-chk-item.pending .vtd-chk-title { color: #94a3b8; }
.vtd-chk-desc { font-size: .77rem; color: #94a3b8; line-height: 1.5; }
.vtd-chk-item.done .vtd-chk-desc { color: #6b7280; }

/* --- Right Sidebar ---------------------------------------- */

/* Avatar */
.vtd-avatar {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg,#6366f1,#7c3aed);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 1.05rem;
    flex-shrink: 0;
}
.vtd-client-row { display: flex; align-items: center; gap: .875rem; margin-bottom: .875rem; }
.vtd-client-name { font-weight: 700; font-size: .95rem; color: #111827; margin: 0; }
.vtd-client-role { font-size: .77rem; color: #94a3b8; margin: 0; }
.vtd-contact-item { display: flex; align-items: center; gap: .5rem; font-size: .83rem; margin-bottom: .45rem; color: #374151; }
.vtd-contact-item i { color: #94a3b8; font-size: .95rem; }
.vtd-contact-item a { color: #6366f1; text-decoration: none; }
.vtd-contact-item a:hover { text-decoration: underline; }

/* Meta rows */
.vtd-meta-table { width: 100%; font-size: .84rem; border-collapse: collapse; }
.vtd-meta-table td { padding: .45rem 0; }
.vtd-meta-table td:first-child { color: #6b7280; width: 50%; }
.vtd-meta-table td:last-child { text-align: right; font-weight: 600; color: #111827; }
.vtd-meta-table tr:not(:last-child) td { border-bottom: 0.5px solid #f0f0f0; }

/* SLA Card */
.vtd-sla-block {
    background: #f8fafc;
    border: 0.5px solid #e5e7eb;
    border-radius: 10px;
    padding: .75rem .875rem;
    margin-bottom: .7rem;
}
.vtd-sla-block:last-child { margin-bottom: 0; }
.vtd-sla-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: .55rem; font-size: .84rem; font-weight: 600; color: #1e293b; }
.vtd-sla-bar { height: 5px; border-radius: 6px; background: #e5e7eb; overflow: hidden; margin-bottom: .3rem; }
.vtd-sla-fill { height: 100%; border-radius: 6px; transition: width .5s ease; }
.sla-met { background: #10b981; }
.sla-miss { background: #ef4444; }
.vtd-sla-time { font-size: .75rem; color: #94a3b8; }
.vtd-sla-wait { font-size: .8rem; color: #f59e0b; display: flex; align-items: center; gap: .3rem; }

/* History Timeline */
.vtd-timeline { position: relative; padding-left: 2rem; }
.vtd-timeline::before {
    content:''; position: absolute;
    left: 10px; top: 4px; bottom: 4px;
    width: 1.5px;
    background: linear-gradient(to bottom,#6366f1,#e2e8f0);
    border-radius: 2px;
}
.vtd-t-item { position: relative; display: flex; align-items: flex-start; gap: .7rem; padding-bottom: 1.15rem; }
.vtd-t-item:last-child { padding-bottom: 0; }
.vtd-t-dot {
    position: absolute; left: -1.5rem;
    width: 22px; height: 22px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .68rem;
    border: 2px solid #fff;
    box-shadow: 0 1px 5px rgba(0,0,0,.15);
    flex-shrink: 0;
}
.td-created  { background: linear-gradient(135deg,#6366f1,#818cf8); }
.td-assigned { background: linear-gradient(135deg,#0ea5e9,#3b82f6); }
.td-response { background: linear-gradient(135deg,#f59e0b,#fb923c); }
.td-resolved { background: linear-gradient(135deg,#10b981,#34d399); }
.td-closed   { background: linear-gradient(135deg,#374151,#6b7280); }
.vtd-t-status { font-size: .84rem; font-weight: 600; color: #1e293b; margin-bottom: .2rem; }
.vtd-t-meta { font-size: .75rem; color: #94a3b8; display: flex; align-items: center; gap: .3rem; flex-wrap: wrap; }
.vtd-t-meta i { font-size: .8rem; }
.vtd-t-empty { display: flex; align-items: center; gap: .45rem; color: #d1d5db; font-size: .82rem; font-style: italic; }
.vtd-t-empty i { font-size: 1rem; }

/* --- Modals ----------------------------------------------- */
.vtd-modal-ov {
    display: none; position: fixed; inset: 0;
    background: rgba(15,23,42,.5);
    z-index: 2000;
    align-items: center; justify-content: center;
    padding: 1rem;
}
.vtd-modal-ov.open { display: flex; }
.vtd-modal-box {
    background: #fff;
    border-radius: 14px;
    max-width: 420px; width: 100%;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
    overflow: hidden;
    animation: modalIn .25s ease;
}
@keyframes modalIn { from { opacity:0; transform:scale(.95) translateY(8px); } to { opacity:1; transform:none; } }
.vtd-modal-header {
    padding: .875rem 1.125rem;
    border-bottom: 0.5px solid #f0f0f0;
    display: flex; align-items: center; gap: .5rem;
}
.vtd-modal-header h5 { margin: 0; font-size: 1rem; font-weight: 700; color: #111827; }
.vtd-modal-header i { font-size: 1.2rem; color: #6366f1; }
.vtd-modal-body { padding: 1.125rem; }
.vtd-modal-body p { margin: 0 0 .5rem; color: #374151; font-size: .9rem; }
.vtd-modal-body p:last-child { margin-bottom: 0; color: #6b7280; font-size: .82rem; }
.vtd-modal-footer { padding: .875rem 1.125rem; border-top: 0.5px solid #f0f0f0; display: flex; gap: .5rem; justify-content: flex-end; }
.vtd-btn-cancel {
    padding: .45rem .875rem;
    border: 0.5px solid #d1d5db;
    border-radius: 9px;
    background: #fff;
    font-size: .84rem; font-weight: 600;
    color: #374151; cursor: pointer;
    transition: all .2s;
}
.vtd-btn-cancel:hover { background: #f8fafc; }
.vtd-btn-confirm {
    padding: .45rem .875rem;
    border: 0;
    border-radius: 9px;
    background: linear-gradient(135deg,#6366f1,#7c3aed);
    font-size: .84rem; font-weight: 600;
    color: #fff; cursor: pointer;
    transition: filter .2s;
}
.vtd-btn-confirm:hover { filter: brightness(.92); }
.vtd-modal-body .form-select {
    width: 100%;
    padding: .55rem .75rem;
    border: 0.5px solid #d1d5db;
    border-radius: 9px;
    font-size: .88rem;
    color: #1f2937;
    background: #fff;
    appearance: auto;
    margin-top: .45rem;
}
</style>
@endpush
