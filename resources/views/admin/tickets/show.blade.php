@extends('layouts.app')

@section('title', 'Detail Tiket ' . $ticket->ticket_number)
@section('page_title', 'Detail Tiket')
@section('breadcrumb', 'Home / Tiket / ' . $ticket->ticket_number)



@section('content')
<div class="ticket-detail-wrap">

    {{-- TOP BAR --}}
    <div class="top-bar">
        <div class="top-bar-left">
            <a href="{{ route('admin.tickets.index') }}" class="btn-back">
                <i class='bx bx-arrow-back'></i> Kembali
            </a>
            <span class="breadcrumb-text">/ Detail Tiket</span>
        </div>
        <div class="top-bar-right">
            @if($ticket->status !== 'closed')
            <button class="btn-assign" onclick="openModal('assign-modal')">
                <i class='bx bx-user-plus'></i>
                {{ $ticket->assigned_to ? 'Tugaskan Ulang' : 'Tugaskan' }}
            </button>
            @endif
            <button class="btn-delete" onclick="confirmDelete()">
                <i class='bx bx-trash'></i> Hapus
            </button>
        </div>
    </div>

    {{-- DETAIL GRID --}}
    <div class="detail-grid">

        {{-- â-€â-€â-€ LEFT COLUMN â-€â-€â-€ --}}
        <div>
            {{-- Main Ticket Card --}}
            <div class="d-card">
                {{-- Hero Header --}}
                <div class="ticket-hero-header">
                    <div class="ticket-num">
                        <i class='bx bx-file'></i>
                        {{ $ticket->ticket_number ?? 'N/A' }}
                    </div>
                    <div class="ticket-hero-badges">
                        <span class="badge badge-light">{{ $ticket->status_label }}</span>
                        @if($ticket->priority)
                            <span class="badge badge-light">{{ $ticket->priority_label }}</span>
                        @else
                            <span class="badge badge-light">Tanpa Prioritas</span>
                        @endif
                    </div>
                </div>

                <div class="d-card__body">
                    <h5 style="margin:0 0 1.25rem; font-size:1.125rem; font-weight:800; color:var(--text);">
                        {{ $ticket->title ?? 'Tanpa Judul' }}
                    </h5>

                    {{-- Event Info --}}
                    @if($ticket->event_name || $ticket->venue || $ticket->area)
                    <div class="event-box" style="margin-bottom:1.25rem;">
                        @if($ticket->event_name)
                        <div class="event-item">
                            <i class='bx bx-calendar' style="color:#2563eb;"></i>
                            <div><small>Acara</small><span>{{ $ticket->event_name }}</span></div>
                        </div>
                        @endif
                        @if($ticket->venue)
                        <div class="event-item">
                            <i class='bx bx-building' style="color:#0891b2;"></i>
                            <div><small>Tempat</small><span>{{ $ticket->venue }}</span></div>
                        </div>
                        @endif
                        @if($ticket->area)
                        <div class="event-item">
                            <i class='bx bx-map' style="color:#d97706;"></i>
                            <div><small>Area</small><span>{{ $ticket->area }}</span></div>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Description --}}
                    <div style="margin-bottom:1.25rem;">
                        <span class="sec-label">Deskripsi</span>
                        <div class="desc-text">{{ $ticket->description ?? 'Tidak ada deskripsi' }}</div>
                    </div>

                    {{-- Attachments --}}
                    @if($ticket->attachments && $ticket->attachments->count() > 0)
                    <div style="margin-bottom:1.25rem;">
                        <span class="sec-label">Lampiran</span>
                        <div class="attachments-wrap">
                            @foreach($ticket->attachments as $attachment)
                            <a href="{{ route('attachments.ticket.view', $attachment->id) }}" target="_blank" class="attach-btn">
                                <i class='bx bx-paperclip'></i> {{ $attachment->file_name }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($ticket->completion_photo_path || $ticket->completion_note)
                    <div style="margin-bottom:1.25rem;">
                        <span class="sec-label">Bukti Penyelesaian Vendor</span>
                        <div class="desc-text" style="background:#f0fdf4;border-color:#bbf7d0;">
                            @if($ticket->completion_note)
                                <p style="margin:0 0 .6rem;color:#166534;">{{ $ticket->completion_note }}</p>
                            @endif
                            @if($ticket->completion_photo_path)
                                <a href="{{ route('attachments.completion-proof.view', $ticket->id) }}" target="_blank" class="attach-btn" style="background:#fff;">
                                    <i class='bx bx-image'></i> {{ $ticket->completion_photo_name ?? 'Lihat Bukti Foto' }}
                                </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Timeline --}}
                    <div>
                        <span class="sec-label">Timeline</span>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-dot dot-primary"><i class='bx bx-plus' style="font-size:9px;"></i></div>
                                <div class="timeline-body">
                                    <strong>Tiket Dibuat</strong>
                                    <small>{{ $ticket->created_at?->locale('id')->isoFormat('D MMMM YYYY, HH:mm') ?? '-' }}</small>
                                    @if($ticket->urgency_level)
                                    <small style="color:#0891b2;">Urgensi klien: {{ $ticket->urgency_label }}</small>
                                    @endif
                                </div>
                            </div>
                            @if($ticket->assigned_to && $ticket->assigned_at)
                            <div class="timeline-item">
                                <div class="timeline-dot dot-info"><i class='bx bx-user-check' style="font-size:9px;"></i></div>
                                <div class="timeline-body">
                                    <strong>Ditugaskan ke {{ $ticket->assignedTo?->name ?? 'Vendor' }}</strong>
                                    <small>{{ \Carbon\Carbon::parse($ticket->assigned_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</small>
                                </div>
                            </div>
                            @endif
                            @if($ticket->assigned_to && $ticket->first_response_at)
                            <div class="timeline-item">
                                <div class="timeline-dot dot-warning"><i class='bx bx-message' style="font-size:9px;"></i></div>
                                <div class="timeline-body">
                                    <strong>Respon Pertama</strong>
                                    <small>{{ \Carbon\Carbon::parse($ticket->first_response_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</small>
                                </div>
                            </div>
                            @endif
                            @if($ticket->resolved_at)
                            <div class="timeline-item">
                                <div class="timeline-dot dot-success"><i class='bx bx-check' style="font-size:9px;"></i></div>
                                <div class="timeline-body">
                                    <strong>Tiket Diselesaikan</strong>
                                    <small>{{ \Carbon\Carbon::parse($ticket->resolved_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</small>
                                </div>
                            </div>
                            @endif
                            @if($ticket->closed_at)
                            <div class="timeline-item last">
                                <div class="timeline-dot dot-dark"><i class='bx bx-lock' style="font-size:9px;"></i></div>
                                <div class="timeline-body">
                                    <strong>Tiket Ditutup</strong>
                                    <small>{{ \Carbon\Carbon::parse($ticket->closed_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Activity Summary Card --}}
            <div class="d-card" style="margin-top:1.25rem;">
                <div class="d-card__head">
                    <h6><i class='bx bx-chart' style="color:var(--primary);"></i> Ringkasan Aktivitas</h6>
                </div>
                <div class="d-card__body">
                    <div class="activity-grid">
                        <div class="act-stat">
                            <div class="act-icon icon-created"><i class='bx bx-calendar-plus'></i></div>
                            <div><div class="act-label">Tanggal Dibuat</div><div class="act-value">{{ $ticket->created_at?->format('d M Y') ?? "" }}</div></div>
                        </div>
                        <div class="act-stat">
                            <div class="act-icon icon-category"><i class='bx bx-category'></i></div>
                            <div><div class="act-label">Kategori</div><div class="act-value">{{ $ticket->category?->name ?? "" }}</div></div>
                        </div>
                        <div class="act-stat">
                            <div class="act-icon icon-priority"><i class='bx bx-flag'></i></div>
                            <div><div class="act-label">Prioritas</div><div class="act-value">{{ $ticket->priority_label ?? "" }}</div></div>
                        </div>
                        @if($ticket->urgency_level)
                        <div class="act-stat">
                            <div class="act-icon icon-urgency"><i class='bx bx-error-circle'></i></div>
                            <div><div class="act-label">Urgensi Klien</div><div class="act-value">{{ $ticket->urgency_label ?? "" }}</div></div>
                        </div>
                        @endif
                        @if($ticket->slaTracking)
                        <div class="act-stat">
                            <div class="act-icon {{ $ticket->slaTracking->response_sla_met ? 'icon-sla-ok' : 'icon-sla-warn' }}"><i class='bx bx-time'></i></div>
                            <div>
                                <div class="act-label">SLA Respon</div>
                                <div class="act-value">
                                    @if($ticket->slaTracking->actual_response_time)
                                        {{ $ticket->slaTracking->actual_response_time }} / {{ $ticket->slaTracking->response_time_sla }} mnt
                                    @else
                                        Menunggu
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="act-stat">
                            <div class="act-icon icon-vendor"><i class='bx bx-wrench'></i></div>
                            <div><div class="act-label">Vendor</div><div class="act-value">{{ $ticket->assignedTo?->name ?? 'Belum ditugaskan' }}</div></div>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    @php
                        $progressMap = ['new'=>20,'in_progress'=>50,'waiting_response'=>65,'resolved'=>85,'closed'=>100];
                        $progress = $progressMap[$ticket->status] ?? 0;
                        $barClass = 'bar-' . ($ticket->status === 'waiting_response' ? 'waiting' : $ticket->status);
                        $statusOrder = ['new','in_progress','waiting_response','resolved','closed'];
                        $currentIndex = array_search($ticket->status, $statusOrder);
                    @endphp
                    <div class="progress-label">
                        <span>Progress Penanganan</span>
                        <span>{{ $progress }}%</span>
                    </div>
                    <div class="progress-rail">
                        <div class="progress-bar {{ $barClass }}" style="width:{{ $progress }}%"></div>
                    </div>
                    <div class="status-steps">
                        @foreach(['new'=>'Baru','in_progress'=>'Diproses','resolved'=>'Selesai','closed'=>'Ditutup'] as $key => $label)
                            @php $done = array_search($ticket->status, $statusOrder) >= array_search($key, $statusOrder); @endphp
                            <span class="step {{ $done ? 'done' : '' }}">{{ $label }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Checklist Tindak Lanjut --}}
            <div class="d-card" style="margin-top:1.25rem;">
                <div class="d-card__head">
                    <h6><i class='bx bx-task' style="color:#16a34a;"></i> Checklist Tindak Lanjut</h6>
                </div>
                <div class="d-card__body">
                    @php
                        $isAssigned = !empty($ticket->assigned_to);
                        $hasFirstResponse = $isAssigned && !empty($ticket->first_response_at);
                        $isResolved = in_array($ticket->status, ['resolved', 'closed'], true);
                        $hasAdditionalInfos = ($ticket->additionalInfos?->count() ?? 0) > 0;
                        $showCommunicationFollowup = $ticket->status === 'waiting_response' || $hasAdditionalInfos;
                    @endphp
                    <div class="followup-list">
                        <div class="followup-item {{ $isAssigned ? 'done' : '' }}">
                            <i class='bx {{ $isAssigned ? 'bx-check-circle' : 'bx-radio-circle' }}'></i>
                            <div>
                                <strong>Penugasan vendor</strong>
                                <small>{{ $isAssigned ? 'Vendor sudah ditugaskan.' : 'Belum ada vendor yang menangani tiket ini.' }}</small>
                            </div>
                        </div>
                        <div class="followup-item {{ $hasFirstResponse ? 'done' : '' }}">
                            <i class='bx {{ $hasFirstResponse ? 'bx-check-circle' : 'bx-radio-circle' }}'></i>
                            <div>
                                <strong>Respon pertama</strong>
                                <small>{{ $hasFirstResponse ? 'Vendor sudah memberi respon awal.' : 'Menunggu respon awal dari vendor.' }}</small>
                            </div>
                        </div>
                        @if($showCommunicationFollowup)
                        <div class="followup-item {{ $hasAdditionalInfos ? 'done' : '' }}">
                            <i class='bx {{ $hasAdditionalInfos ? 'bx-check-circle' : 'bx-radio-circle' }}'></i>
                            <div>
                                <strong>Komunikasi ke user</strong>
                                <small>{{ $hasAdditionalInfos ? 'Informasi tambahan dari klien sudah diterima.' : 'Vendor sedang menunggu informasi tambahan dari klien.' }}</small>
                            </div>
                        </div>
                        @endif
                        <div class="followup-item {{ $isResolved ? 'done' : '' }}">
                            <i class='bx {{ $isResolved ? 'bx-check-circle' : 'bx-radio-circle' }}'></i>
                            <div>
                                <strong>Penyelesaian tiket</strong>
                                <small>{{ $isResolved ? 'Tiket sudah masuk tahap selesai/ditutup.' : 'Tiket masih dalam proses penanganan.' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="followup-actions">
                        @if(!$isAssigned && $ticket->status !== 'closed')
                            <button type="button" class="btn-assign" onclick="openModal('assign-modal')">
                                <i class='bx bx-user-plus'></i> Tugaskan Vendor
                            </button>
                        @endif
                        @if($ticket->status !== 'closed')
                            <button type="button" class="btn-full" onclick="openModal('status-modal')" style="margin-bottom:0;">
                                <i class='bx bx-refresh'></i> Perbarui Status
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- â-€â-€â-€ RIGHT COLUMN â-€â-€â-€ --}}
        <div>

            {{-- Client Info --}}
            <div class="d-card">
                <div class="d-card__head">
                    <h6><i class='bx bx-user' style="color:var(--primary);"></i> Informasi Klien</h6>
                </div>
                <div class="d-card__body">
                    <div style="display:flex; align-items:center; gap:0.875rem; margin-bottom:0.75rem;">
                        <div class="side-avatar av-primary">
                            {{ strtoupper(substr($ticket->user?->name ?? 'U', 0, 2)) }}
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:0.9rem; color:var(--text);">{{ $ticket->user?->name ?? 'Klien Tidak Dikenal' }}</div>
                            <small style="color:var(--text-muted);">{{ $ticket->user?->email ?? 'N/A' }}</small>
                        </div>
                    </div>
                    @if($ticket->user?->phone)
                    <div class="info-row"><i class='bx bx-phone'></i> {{ $ticket->user->phone }}</div>
                    @endif
                </div>
            </div>

            {{-- Assignment --}}
            <div class="d-card" style="margin-top:1rem;">
                <div class="d-card__head">
                    <h6><i class='bx bx-user-check' style="color:#0891b2;"></i> Penugasan</h6>
                </div>
                <div class="d-card__body">
                    @if($ticket->assignedTo)
                        <div style="display:flex; align-items:center; gap:0.875rem; margin-bottom:1rem;">
                            <div class="side-avatar av-info">
                                {{ strtoupper(substr($ticket->assignedTo->name, 0, 2)) }}
                            </div>
                            <div>
                                <div style="font-weight:700; font-size:0.9rem; color:var(--text);">{{ $ticket->assignedTo->name }}</div>
                                <small style="color:var(--text-muted);">{{ $ticket->assignedTo->email ?? 'N/A' }}</small>
                            </div>
                        </div>
                        <button class="btn-full" onclick="openModal('assign-modal')">
                            <i class='bx bx-refresh'></i> Tugaskan Ulang
                        </button>
                    @else
                        <div style="background:#fff7ed; border:1px solid rgba(249,115,22,0.25); border-radius:10px; padding:0.75rem; margin-bottom:1rem; font-size:0.85rem; color:#c2410c; display:flex; align-items:center; gap:0.5rem;">
                            <i class='bx bx-error-circle'></i> Belum ditugaskan
                        </div>
                        <button class="btn-assign" style="width:100%; justify-content:center;" onclick="openModal('assign-modal')">
                            <i class='bx bx-user-plus'></i> Tugaskan Sekarang
                        </button>
                    @endif
                </div>
            </div>

            @if($ticket->latestReassignRequest && $ticket->latestReassignRequest->status === 'pending')
            <div class="d-card" style="margin-top:1rem;">
                <div class="d-card__head">
                    <h6><i class='bx bx-transfer-alt' style="color:#d97706;"></i> Permintaan Penugasan Ulang Vendor</h6>
                </div>
                <div class="d-card__body">
                    <div style="font-size:.85rem;color:var(--text-muted);margin-bottom:.5rem;">
                        Vendor: <strong style="color:var(--text);">{{ $ticket->latestReassignRequest->vendor->name ?? '-' }}</strong>
                    </div>
                    <div style="font-size:.85rem;color:var(--text-muted);margin-bottom:.5rem;">
                        Alasan: <strong style="color:var(--text);">{{ str_replace('_', ' ', $ticket->latestReassignRequest->reason_option) }}</strong>
                    </div>
                    <div style="font-size:.84rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:.6rem;margin-bottom:.7rem;">
                        {{ $ticket->latestReassignRequest->reason_detail }}
                    </div>
                    <form method="POST" action="{{ route('admin.reassign-requests.process', $ticket->latestReassignRequest->id) }}">
                        @csrf
                        <textarea name="admin_note" rows="2" placeholder="Catatan admin (opsional)" style="width:100%;border:1px solid var(--border);border-radius:10px;padding:.6rem;margin-bottom:.6rem;"></textarea>
                        <div style="display:flex;gap:.5rem;">
                            <button type="submit" name="action" value="approve" class="btn-assign" style="flex:1;justify-content:center;">Setujui</button>
                            <button type="submit" name="action" value="reject" class="btn-delete" style="flex:1;justify-content:center;">Tolak</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- Klasifikasi & Status --}}
            <div class="d-card" style="margin-top:1rem;">
                <div class="d-card__head">
                    <h6><i class='bx bx-category' style="color:#d97706;"></i> Klasifikasi & Status</h6>
                </div>
                <div class="d-card__body">
                    <div class="classify-row">
                        <div class="classify-label">Kategori</div>
                        <span class="badge badge-none">{{ $ticket->category?->name ?? 'Tidak Dikategorikan' }}</span>
                    </div>
                    <div class="classify-row">
                        <div class="classify-row__top">
                            <span class="classify-label">Prioritas</span>
                            @if($ticket->status !== 'closed')
                            <button class="btn-edit-xs" onclick="openModal('priority-modal')"><i class='bx bx-edit-alt'></i> Ubah</button>
                            @endif
                        </div>
                        <span class="badge badge-{{ $ticket->priority ?? 'none' }}">{{ $ticket->priority_label ?? "" }}</span>
                        @if($ticket->urgency_level)
                        <div style="margin-top:0.5rem; padding:0.5rem; background:var(--bg); border-radius:8px;">
                            <small style="color:var(--text-muted); font-size:0.72rem; font-weight:700; display:block; margin-bottom:0.2rem;">Urgensi Klien:</small>
                            <span class="badge" style="background:rgba(6,182,212,0.1);color:#0891b2;">{{ $ticket->urgency_label ?? "" }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="classify-row">
                        <div class="classify-row__top">
                            <span class="classify-label">Status</span>
                            @if($ticket->status !== 'closed')
                            <button class="btn-edit-xs" onclick="openModal('status-modal')"><i class='bx bx-edit-alt'></i> Perbarui</button>
                            @endif
                        </div>
                        <span class="badge badge-{{ $ticket->status }}">{{ $ticket->status_label }}</span>
                    </div>
                </div>
            </div>

            {{-- SLA Tracking --}}
            @if($ticket->slaTracking)
            <div class="d-card" style="margin-top:1rem;">
                <div class="d-card__head">
                    <h6><i class='bx bx-time-five' style="color:#16a34a;"></i> Pelacakan SLA</h6>
                </div>
                <div class="d-card__body">
                    <div class="sla-metric">
                        <div class="sla-metric__head">
                            <small>Waktu Respon</small>
                            @if($ticket->slaTracking->response_sla_met !== null)
                                @if($ticket->slaTracking->response_sla_met)
                                    <span class="sla-badge-ok">TERPENUHI</span>
                                @else
                                    <span class="sla-badge-miss">TERLAMPAUI</span>
                                @endif
                            @endif
                        </div>
                        @if($ticket->slaTracking->actual_response_time)
                            <strong>{{ $ticket->slaTracking->actual_response_time }} menit</strong>
                            <small style="color:var(--text-muted);"> / {{ $ticket->slaTracking->response_time_sla }} menit</small>
                        @else
                            <small style="color:var(--text-muted);">Menunggu (Target: {{ $ticket->slaTracking->response_time_sla }} menit)</small>
                        @endif
                    </div>
                    <div class="sla-metric">
                        <div class="sla-metric__head">
                            <small>Waktu Penyelesaian</small>
                            @if($ticket->slaTracking->resolution_sla_met !== null)
                                @if($ticket->slaTracking->resolution_sla_met)
                                    <span class="sla-badge-ok">TERPENUHI</span>
                                @else
                                    <span class="sla-badge-miss">TERLAMPAUI</span>
                                @endif
                            @endif
                        </div>
                        @if($ticket->slaTracking->actual_resolution_time)
                            <strong>{{ $ticket->slaTracking->actual_resolution_time }} menit</strong>
                            <small style="color:var(--text-muted);"> / {{ $ticket->slaTracking->resolution_time_sla }} menit</small>
                        @else
                            <small style="color:var(--text-muted);">Menunggu (Target: {{ $ticket->slaTracking->resolution_time_sla }} menit)</small>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Quick Actions --}}
            <div class="d-card" style="margin-top:1rem;">
                <div class="d-card__head">
                    <h6><i class='bx bx-cog' style="color:var(--text-muted);"></i> Aksi Cepat</h6>
                </div>
                <div class="d-card__body">
                    <button class="btn-full" onclick="window.print()"><i class='bx bx-printer'></i> Cetak Tiket</button>
                    <button class="btn-full" onclick="exportTicket()"><i class='bx bx-download'></i> Ekspor Detail</button>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- ASSIGN MODAL --}}
<div id="assign-modal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-head">
            <h5>Tugaskan Vendor</h5>
            <button class="modal-close" onclick="closeModal('assign-modal')">&times;</button>
        </div>
        <p style="color:var(--text-muted); margin-bottom:1.25rem; font-size:0.9rem;">
            Pilih vendor untuk menangani tiket <strong>{{ $ticket->ticket_number }}</strong>
        </p>
        <input type="text" id="vendor-search" placeholder="Cari vendor..." style="width:100%;margin-bottom:.8rem;border:1px solid var(--border);border-radius:10px;padding:.6rem .7rem;">
        <div id="vendor-load-list" style="max-height:140px;overflow:auto;border:1px solid var(--border);border-radius:10px;padding:.5rem;margin-bottom:.8rem;background:var(--bg);">
            @foreach($vendors as $vendor)
                @php
                    $activeCount = (int) ($vendorLoads[$vendor->id] ?? 0);
                    if ($ticket->assigned_to === $vendor->id && $activeCount > 0) {
                        $activeCount--;
                    }
                @endphp
                <div class="vendor-load-item" data-name="{{ strtolower($vendor->name) }}" style="display:flex;justify-content:space-between;gap:.5rem;padding:.35rem .45rem;border-radius:8px;{{ $activeCount >= 5 ? 'background:#fff1f2;' : '' }}">
                    <span style="font-size:.82rem;color:var(--text);">{{ $vendor->name }}</span>
                    <span style="font-size:.75rem;font-weight:700;{{ $activeCount >= 5 ? 'color:#b91c1c;' : 'color:#475569;' }}">{{ $activeCount }}/5 tiket aktif</span>
                </div>
            @endforeach
        </div>
        <form method="POST" action="{{ route('admin.tickets.assign', $ticket->id) }}">
            @csrf
            @method('PATCH')
            <div class="modal-field">
                <label>Vendor</label>
                <select name="assigned_to" id="assign-vendor-select" required>
                    <option value="">-- Pilih vendor --</option>
                    @foreach($vendors as $vendor)
                        @php
                            $activeCount = (int) ($vendorLoads[$vendor->id] ?? 0);
                            if ($ticket->assigned_to === $vendor->id && $activeCount > 0) {
                                $activeCount--;
                            }
                        @endphp
                        <option value="{{ $vendor->id }}" data-active-count="{{ $activeCount }}" {{ $ticket->assigned_to === $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->name }} ({{ $activeCount }}/5 aktif)
                        </option>
                    @endforeach
                </select>
                <small id="vendor-busy-warning" style="display:none;color:#b91c1c;font-weight:700;margin-top:.45rem;">Vendor sedang sibuk (maksimal 5 tiket aktif).</small>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-back" onclick="closeModal('assign-modal')">Batal</button>
                <button type="submit" class="btn-assign" id="assign-submit-btn">Tugaskan</button>
            </div>
        </form>
    </div>
</div>

{{-- STATUS MODAL --}}
<div id="status-modal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-head">
            <h5>Perbarui Status</h5>
            <button class="modal-close" onclick="closeModal('status-modal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.tickets.update-status', $ticket->id) }}">
            @csrf
            @method('PATCH')
            <div class="modal-field">
                <label>Status Tiket</label>
                <select name="status" required>
                    <option value="new"              {{ $ticket->status === 'new'              ? 'selected' : '' }}>Baru</option>
                    <option value="in_progress"      {{ $ticket->status === 'in_progress'      ? 'selected' : '' }}>Dalam Proses</option>
                    <option value="waiting_response" {{ $ticket->status === 'waiting_response' ? 'selected' : '' }}>Menunggu Respon</option>
                    <option value="resolved"         {{ $ticket->status === 'resolved'         ? 'selected' : '' }}>Selesai</option>
                    <option value="closed"           {{ $ticket->status === 'closed'           ? 'selected' : '' }}>Ditutup</option>
                </select>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-back" onclick="closeModal('status-modal')">Batal</button>
                <button type="submit" class="btn-assign">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- PRIORITY MODAL --}}
<div id="priority-modal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-head">
            <h5>Ubah Prioritas</h5>
            <button class="modal-close" onclick="closeModal('priority-modal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.tickets.update-priority', $ticket->id) }}">
            @csrf
            @method('PATCH')
            <div class="modal-field">
                <label>Prioritas</label>
                <select name="priority" required>
                    <option value="low"    {{ $ticket->priority === 'low'    ? 'selected' : '' }}>Rendah</option>
                    <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Sedang</option>
                    <option value="high"   {{ $ticket->priority === 'high'   ? 'selected' : '' }}>Tinggi</option>
                    <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Mendesak</option>
                </select>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-back" onclick="closeModal('priority-modal')">Batal</button>
                <button type="submit" class="btn-assign">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- DELETE FORM --}}
<form id="delete-form" method="POST" action="{{ route('admin.tickets.destroy', $ticket->id) }}" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function openModal(id)  { document.getElementById(id).classList.add('open');    document.body.style.overflow = 'hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }

// Close on backdrop click
document.querySelectorAll('.modal-backdrop').forEach(el => {
    el.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

function confirmDelete() {
    Swal.fire({
        title: 'Hapus Tiket?',
        html: `Apakah Anda yakin ingin menghapus tiket <strong>{{ $ticket->ticket_number }}</strong>?<br><small style="color:#94a3b8;">Tindakan ini tidak dapat dibatalkan.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) document.getElementById('delete-form').submit();
    });
}

function exportTicket() {
    Swal.fire({ icon:'info', title:'Segera Hadir', text:'Fitur ekspor sedang dalam pengembangan.', confirmButtonColor:'#4f46e5' });
}

const vendorSearchInput = document.getElementById('vendor-search');
if (vendorSearchInput) {
    vendorSearchInput.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('#vendor-load-list .vendor-load-item').forEach(function (item) {
            const name = item.getAttribute('data-name') || '';
            item.style.display = name.includes(q) ? '' : 'none';
        });
    });
}

const assignVendorSelect = document.getElementById('assign-vendor-select');
const vendorBusyWarning = document.getElementById('vendor-busy-warning');
const assignSubmitBtn = document.getElementById('assign-submit-btn');
if (assignVendorSelect && vendorBusyWarning) {
    const checkBusy = () => {
        const opt = assignVendorSelect.options[assignVendorSelect.selectedIndex];
        const activeCount = parseInt(opt?.getAttribute('data-active-count') || '0', 10);
        const isBusy = activeCount >= 5;
        vendorBusyWarning.style.display = isBusy ? 'block' : 'none';
        if (assignSubmitBtn) {
            assignSubmitBtn.disabled = isBusy;
            assignSubmitBtn.style.opacity = isBusy ? '0.6' : '1';
            assignSubmitBtn.style.cursor = isBusy ? 'not-allowed' : 'pointer';
        }
    };
    assignVendorSelect.addEventListener('change', checkBusy);
    checkBusy();
}
</script>
@endpush

@push('styles')
<style>
    
.ticket-detail-wrap {
    display: flex; flex-direction: column; gap: 1.25rem;
    animation: fadeIn 0.25s ease-out;
}
@keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }

/*TOP BAR*/
.top-bar {
    display: flex; justify-content: space-between; align-items: center;
    gap: 1rem; flex-wrap: wrap;
}
.top-bar-left { display: flex; align-items: center; gap: 0.75rem; }
.top-bar-right { display: flex; gap: 0.625rem; align-items: center; }

.breadcrumb-text { color: var(--text-muted); font-size: 0.875rem; }

/*BUTTONS*/
.btn-back {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.6rem 1rem; border: 1px solid var(--border);
    background: white; border-radius: 12px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.875rem; font-weight: 600; color: var(--text-muted);
    text-decoration: none; cursor: pointer; transition: all 0.2s;
}
.btn-back:hover { background: var(--bg); color: var(--text); }
.btn-assign {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.6rem 1.1rem; background: var(--gradient);
    color: white; border: none; border-radius: 12px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.875rem; font-weight: 700; cursor: pointer;
    transition: all 0.2s; box-shadow: 0 4px 14px rgba(79,70,229,0.25);
    text-decoration: none;
}
.btn-assign:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(79,70,229,0.3); color: white; }
.btn-delete {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.6rem 1.1rem; background: #fff1f2;
    color: #e11d48; border: none; border-radius: 12px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.875rem; font-weight: 700; cursor: pointer; transition: all 0.2s;
}
.btn-delete:hover { background: #ffe4e6; }

/*LAYOUT*/
.detail-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.25rem;
    align-items: start;
}

/*CARDS*/
.d-card {
    background: white; border: 1px solid var(--border);
    border-radius: 24px; overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.d-card + .d-card { margin-top: 1.25rem; }

.d-card__head {
    padding: 0.875rem 1.25rem;
    background: var(--bg); border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    gap: 0.75rem;
}
.d-card__head h6 {
    margin: 0; font-size: 0.825rem; font-weight: 700;
    color: var(--text-muted); text-transform: uppercase;
    letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.5rem;
}
.d-card__head h6 i { font-size: 1rem; }
.d-card__body { padding: 1.375rem; }

/* Main card hero header */
.ticket-hero-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    padding: 1.125rem 1.375rem;
    display: flex; justify-content: space-between; align-items: center;
    gap: 1rem; flex-wrap: wrap;
}
.ticket-hero-header .ticket-num {
    display: flex; align-items: center; gap: 0.5rem;
    color: rgba(255,255,255,0.9); font-size: 0.9rem; font-weight: 700;
}
.ticket-hero-header .ticket-num i { font-size: 1.1rem; }
.ticket-hero-badges { display: flex; gap: 0.5rem; flex-wrap: wrap; }

/*BADGES*/
.badge {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 0.3rem 0.75rem; border-radius: 999px;
    font-size: 0.72rem; font-weight: 800; white-space: nowrap;
    text-transform: uppercase; letter-spacing: 0.03em;
}
/* On dark bg (hero) */
.badge-light { background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); }

/* Status */
.badge-new, .badge-waiting_response { background: rgba(249,115,22,0.12); color: #c2410c; }
.badge-in_progress  { background: rgba(59,130,246,0.12);  color: #1d4ed8; }
.badge-resolved     { background: rgba(34,197,94,0.12);   color: #15803d; }
.badge-closed       { background: rgba(100,116,139,0.12); color: #475569; }
/* Priority */
.badge-urgent       { background: rgba(239,68,68,0.12);   color: #b91c1c; }
.badge-high         { background: rgba(249,115,22,0.12);  color: #c2410c; }
.badge-medium       { background: rgba(250,204,21,0.16);  color: #a16207; }
.badge-low          { background: rgba(34,197,94,0.12);   color: #15803d; }
.badge-none         { background: rgba(148,163,184,0.14); color: #475569; }

/*EVENT INFO BOX*/
.event-box {
    background: #f0f8ff; border: 1px solid #bee3f8;
    border-radius: 12px; padding: 0.875rem 1rem;
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem;
    margin-bottom: 1.25rem;
}
.event-item { display: flex; align-items: flex-start; gap: 0.5rem; }
.event-item i { font-size: 1.1rem; margin-top: 0.1rem; flex-shrink: 0; }
.event-item small { display: block; font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.1rem; }
.event-item span  { font-size: 0.875rem; font-weight: 600; color: var(--text); }

/*SECTION LABEL*/
.sec-label {
    display: block; margin-bottom: 0.5rem;
    font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.06em; color: var(--text-muted);
}

/*DESCRIPTION*/
.desc-text {
    color: var(--text-muted); line-height: 1.7; font-size: 0.9rem;
    padding: 0.875rem; background: var(--bg); border-radius: 12px;
    border: 1px solid var(--border);
}

/*ATTACHMENTS*/
.attachments-wrap { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.attach-btn {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.45rem 0.875rem; border: 1px solid var(--border);
    border-radius: 10px; background: var(--bg);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.8rem; font-weight: 600; color: var(--text-muted);
    text-decoration: none; transition: all 0.2s;
}
.attach-btn:hover { background: #eef2ff; color: var(--primary); border-color: rgba(79,70,229,0.3); }

/*TIMELINE*/
.timeline { position: relative; padding-left: 1.75rem; }
.timeline::before {
    content: ''; position: absolute; left: 7px; top: 4px; bottom: 4px;
    width: 2px; background: var(--border); border-radius: 2px;
}
.timeline-item { position: relative; padding-bottom: 1.25rem; display: flex; gap: 0.75rem; }
.timeline-item.last { padding-bottom: 0; }
.timeline-dot {
    position: absolute; left: -1.75rem; width: 18px; height: 18px;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    color: white; font-size: 10px; top: 2px; flex-shrink: 0;
}
.dot-primary { background: #4f46e5; }
.dot-info    { background: #0891b2; }
.dot-warning { background: #d97706; }
.dot-success { background: #16a34a; }
.dot-dark    { background: #374151; }
.timeline-body { font-size: 0.875rem; }
.timeline-body strong { color: var(--text); font-weight: 700; }
.timeline-body small { color: var(--text-muted); display: block; margin-top: 2px; }

/*ACTIVITY GRID*/
.activity-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.875rem;
    margin-bottom: 1.25rem;
}
.act-stat {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.75rem; background: var(--bg); border-radius: 12px;
    border: 1px solid var(--border);
}
.act-icon {
    width: 36px; height: 36px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.icon-created  { background: #e8f4fd; color: #1a73e8; }
.icon-category { background: #fef3e2; color: #f57c00; }
.icon-priority { background: #fce4ec; color: #c62828; }
.icon-sla-ok   { background: #e8f5e9; color: #2e7d32; }
.icon-sla-warn { background: #fff3e0; color: #e65100; }
.icon-urgency  { background: #fff8e1; color: #f9a825; }
.icon-vendor   { background: #ede7f6; color: #6a1b9a; }
.act-label { font-size: 0.68rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; font-weight: 700; margin-bottom: 0.15rem; }
.act-value { font-size: 0.82rem; font-weight: 700; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* Progress */
.progress-label { display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.8rem; color: var(--text-muted); font-weight: 600; }
.progress-rail  { height: 6px; background: var(--border); border-radius: 999px; overflow: hidden; }
.progress-bar   { height: 100%; border-radius: 999px; transition: width 0.6s ease; }
.bar-new        { background: #f59e0b; }
.bar-in_progress{ background: #3b82f6; }
.bar-waiting    { background: #94a3b8; }
.bar-resolved   { background: #22c55e; }
.bar-closed     { background: #374151; }
.status-steps   { display: flex; justify-content: space-between; margin-top: 0.5rem; }
.step { font-size: 0.68rem; color: #94a3b8; font-weight: 600; }
.step.done { color: var(--primary); font-weight: 800; }

/*SIDEBAR CARDS*/
.side-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.875rem; font-weight: 800; flex-shrink: 0;
}
.av-primary { background: #eef2ff; color: var(--primary); }
.av-info    { background: #e0f7fa; color: #0097a7; }

.classify-row { padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0; }
.classify-row:last-child { border-bottom: none; padding-bottom: 0; }
.classify-row__top {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 0.4rem;
}
.classify-label { font-size: 0.78rem; color: var(--text-muted); font-weight: 600; }
.btn-edit-xs {
    display: inline-flex; align-items: center; gap: 0.25rem;
    padding: 0.2rem 0.55rem; border: 1px solid var(--border);
    border-radius: 7px; background: white;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.7rem; font-weight: 700; color: var(--primary);
    cursor: pointer; transition: all 0.15s;
}
.btn-edit-xs:hover { background: #eef2ff; border-color: rgba(79,70,229,0.3); }

.sla-metric {
    padding: 0.75rem; background: var(--bg); border-radius: 10px;
    font-size: 0.875rem;
    border: 1px solid var(--border);
    margin-bottom: 0.75rem;
}
.sla-metric:last-child { margin-bottom: 0; }
.sla-metric__head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem; }
.sla-metric small { color: var(--text-muted); font-size: 0.75rem; font-weight: 600; }

.sla-badge-ok   { background: rgba(34,197,94,0.12);  color: #15803d; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.7rem; font-weight: 800; }
.sla-badge-miss { background: rgba(239,68,68,0.12);  color: #b91c1c; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.7rem; font-weight: 800; }

.info-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--text-muted); margin-top: 0.4rem; }

.btn-full {
    display: flex; align-items: center; justify-content: center; gap: 0.4rem;
    width: 100%; padding: 0.75rem;
    border: 1px solid var(--border); border-radius: 12px;
    background: white; font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.875rem; font-weight: 700; color: var(--text-muted);
    cursor: pointer; transition: all 0.2s; text-decoration: none;
    margin-bottom: 0.5rem;
}
.btn-full:last-child { margin-bottom: 0; }
.btn-full:hover { background: var(--bg); color: var(--primary); border-color: rgba(79,70,229,0.3); }

/* Follow-up checklist */
.followup-list {
    display: grid;
    gap: 0.75rem;
}
.followup-item {
    display: flex;
    align-items: flex-start;
    gap: 0.7rem;
    padding: 0.8rem;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: var(--bg);
}
.followup-item i {
    font-size: 1.1rem;
    color: #94a3b8;
    margin-top: 2px;
}
.followup-item strong {
    display: block;
    font-size: 0.86rem;
    color: var(--text);
    font-weight: 700;
}
.followup-item small {
    display: block;
    margin-top: 2px;
    font-size: 0.79rem;
    color: var(--text-muted);
}
.followup-item.done {
    border-color: rgba(34,197,94,0.2);
    background: #f0fdf4;
}
.followup-item.done i { color: #16a34a; }
.followup-actions {
    display: flex;
    gap: 0.6rem;
    margin-top: 0.9rem;
    flex-wrap: wrap;
}

/*MODALS*/
.modal-backdrop {
    display: none; position: fixed; inset: 0;
    background: rgba(15,23,42,0.5); z-index: 1050;
    align-items: center; justify-content: center;
}
.modal-backdrop.open { display: flex; }
.modal-box {
    background: white; border-radius: 24px; padding: 1.75rem;
    width: 100%; max-width: 460px; margin: 1rem;
    box-shadow: 0 25px 60px rgba(0,0,0,0.2);
    animation: fadeIn 0.2s ease;
}
.modal-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.modal-head h5 { margin: 0; font-weight: 800; color: var(--text); font-size: 1.05rem; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); line-height: 1; }
.modal-field { margin-bottom: 1rem; }
.modal-field label { display: block; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 0.45rem; }
.modal-field select, .modal-field textarea {
    width: 100%; border: 1.5px solid var(--border); border-radius: 12px;
    padding: 0.8rem 1rem; font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.9rem; background: var(--bg); outline: none;
    transition: border-color 0.2s;
}
.modal-field select:focus, .modal-field textarea:focus { border-color: var(--primary); background: white; }
.modal-foot { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.25rem; }

/*RESPONSIVE*/
@media (max-width: 1199px) {
    .detail-grid { grid-template-columns: 1fr 280px; }
    .activity-grid { grid-template-columns: repeat(2, 1fr); }
    .event-box { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 991px) {
    .detail-grid { grid-template-columns: 1fr; }
}
@media (max-width: 767px) {
    .top-bar, .ticket-hero-header { flex-direction: column; align-items: flex-start; }
    .activity-grid, .event-box { grid-template-columns: 1fr; }
    .top-bar-right { width: 100%; }
}
</style>
@endpush




