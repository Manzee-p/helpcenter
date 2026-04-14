@extends('layouts.client')

@section('title', 'Detail Tiket #' . $ticket->ticket_number)
@section('page_title', 'Detail Tiket')
@section('breadcrumb', 'Home / Tiket / #' . $ticket->ticket_number)

@section('content')
<div class="td-wrapper">

    {{-- â-€â-€ Ticket Header â-€â-€ --}}
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

    {{-- â-€â-€ Two-column grid â-€â-€ --}}
    <div class="td-grid">

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             KIRI: Main Content
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
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
                        <div class="td-detail-item">
                            <div class="td-detail-label"><i class='bx bx-folder'></i> Kategori</div>
                            <div class="td-detail-value">
                                <span class="td-category-badge">{{ $ticket->category->name ?? '-' }}</span>
                            </div>
                        </div>
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
                        <div class="td-detail-item">
                            <div class="td-detail-label"><i class='bx bx-user-tie'></i> Nama Klien</div>
                            <div class="td-detail-value">{{ $ticket->user->name ?? '-' }}</div>
                        </div>
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

                    {{-- Urgency --}}
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
                            $isImg = in_array($ext,['jpg','jpeg','png','gif','webp']);
                            $fileIcon = $isImg ? 'bx-image'
                                : ($ext==='pdf' ? 'bx-file-pdf'
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
                            @if($isImg)
                                <button type="button" class="td-att-dl" onclick="openStoredImage('{{ route('attachments.ticket.view', $file->id) }}')">
                                    <i class='bx bx-show'></i>
                                </button>
                            @else
                                <a href="{{ route('attachments.ticket.view', $file->id) }}" target="_blank" class="td-att-dl">
                                    <i class='bx bx-download'></i>
                                </a>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
                         SECTION: VENDOR BUTUH INFORMASI TAMBAHAN
                    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
                    @if($ticket->status === 'waiting_response')
                    @php
                        $hasAdditionalInfo = $ticket->additionalInfos && $ticket->additionalInfos->count() > 0;
                        $showAdditionalInfoForm = !$hasAdditionalInfo || $errors->has('note') || $errors->has('photos') || $errors->has('photos.*') || old('note');
                    @endphp
                    <div class="ai-wrap">

                        {{-- Header --}}
                        <div class="ai-header">
                            <div class="ai-header__icon">
                                <i class='bx bx-message-square-dots'></i>
                            </div>
                            <div class="ai-header__text">
                                <div class="ai-header__title">Vendor Butuh Informasi Tambahan</div>
                                <div class="ai-header__sub">Berikan keterangan dan foto untuk membantu vendor menyelesaikan masalah Anda.</div>
                            </div>
                        </div>

                        @if($hasAdditionalInfo)
                        <div class="ai-status-ok">
                            <i class='bx bx-check-circle'></i>
                            <span>Informasi tambahan sudah dikirim. Jika perlu, Anda bisa menambahkan informasi lagi.</span>
                            <button type="button" class="ai-reopen-btn" id="aiReopenBtn" onclick="toggleAdditionalInfoForm()">
                                Tambahkan lagi?
                            </button>
                        </div>
                        @endif

                        <form class="ai-form {{ $showAdditionalInfoForm ? '' : 'is-hidden' }}" id="aiForm"
                              method="POST"
                              action="{{ route('client.tickets.additional-info', $ticket->id) }}"
                              enctype="multipart/form-data">
                            @csrf

                            {{-- Textarea --}}
                            <div class="ai-field">
                                <label class="ai-label">
                                    <i class='bx bx-edit-alt'></i>
                                    Keterangan Tambahan
                                </label>
                                <div class="ai-textarea-wrap">
                                    <textarea name="note" id="aiNote"
                                        class="ai-textarea @error('note') is-err @enderror"
                                        rows="4" maxlength="1000"
                                        placeholder="Tulis informasi tambahan untuk vendor..."
                                        oninput="aiUpdateChar()">{{ old('note') }}</textarea>
                                    <div class="ai-char">
                                        <span id="aiCharNum">{{ strlen(old('note','')) }}</span>/1000
                                    </div>
                                </div>
                                @error('note')
                                    <div class="ai-err"><i class='bx bx-error-circle'></i> {{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Upload --}}
                            <div class="ai-field">
                                <label class="ai-label">
                                    <i class='bx bx-image-add'></i>
                                    Foto / Bukti
                                    <span class="ai-limit-badge">Maks 5 foto Â· 5 MB/foto</span>
                                </label>

                                <input type="file" id="aiFileInput" multiple
                                    accept="image/*" style="display:none;"
                                    onchange="aiHandleFiles(this.files)">
                                <div id="aiFileInputsContainer"></div>

                                {{-- Drop zone (hidden when files exist) --}}
                                <div class="ai-dropzone" id="aiDropZone"
                                    onclick="document.getElementById('aiFileInput').click()"
                                    ondragover="event.preventDefault();this.classList.add('is-over')"
                                    ondragleave="this.classList.remove('is-over')"
                                    ondrop="event.preventDefault();this.classList.remove('is-over');aiHandleFiles(event.dataTransfer.files)">
                                    <div class="ai-dropzone__icon">
                                        <i class='bx bx-cloud-upload'></i>
                                    </div>
                                    <div class="ai-dropzone__text">
                                        <strong>Klik atau seret foto ke sini</strong>
                                    </div>
                                    <div class="ai-dropzone__hint">JPG, PNG, WEBP - Maks 5 foto</div>
                                </div>

                                {{-- Progress bar --}}
                                <div id="aiProgressWrap" style="display:none; margin-top:.75rem;">
                                    <div class="ai-progress">
                                        <span id="aiProgressLabel">Memproses...</span>
                                        <div class="ai-progress__track">
                                            <div class="ai-progress__fill" id="aiProgressFill" style="width:0%"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Error --}}
                                <div class="ai-err" id="aiFileErr" style="display:none; margin-top:.5rem;">
                                    <i class='bx bx-error-circle'></i>
                                    <span id="aiFileErrMsg"></span>
                                </div>

                                {{-- Thumbnail grid --}}
                                <div class="ai-thumb-grid" id="aiThumbGrid"></div>

                                {{-- Add more button (visible after first upload) --}}
                                <button type="button" class="ai-add-more" id="aiAddMore"
                                    style="display:none;"
                                    onclick="document.getElementById('aiFileInput').click()">
                                    <i class='bx bx-plus'></i>
                                    Tambah Foto
                                    <span id="aiFileCount" class="ai-add-more__count">0/5</span>
                                </button>
                            </div>

                            @error('photos') <div class="ai-err"><i class='bx bx-error-circle'></i> {{ $message }}</div> @enderror
                            @error('photos.*') <div class="ai-err"><i class='bx bx-error-circle'></i> {{ $message }}</div> @enderror

                            {{-- Actions --}}
                            <div class="ai-actions">
                                <button type="submit" class="ai-submit" id="aiSubmitBtn">
                                    <i class='bx bx-send'></i>
                                    Kirim Informasi
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif

                    {{-- Previous Additional Infos --}}
                    @if($ticket->additionalInfos && $ticket->additionalInfos->count())
                    <div class="ai-history">
                        <div class="td-info-label" style="margin-bottom:.875rem;">
                            <i class='bx bx-history'></i> Riwayat Informasi Tambahan
                        </div>
                        @foreach($ticket->additionalInfos as $info)
                        <div class="ai-history-item">
                            <div class="ai-history-item__meta">
                                <span class="ai-history-item__author">
                                    <i class='bx bx-user-circle'></i>
                                    {{ $info->user->name ?? 'Pengguna' }}
                                </span>
                                <span class="ai-history-item__time">
                                    {{ $info->created_at ? $info->created_at->format('d M Y, H:i') : '-' }}
                                </span>
                            </div>
                            @if($info->note)
                                <p class="ai-history-item__note">{{ $info->note }}</p>
                            @endif
                            @if($info->photo_path)
                                @php
                                    $infoExt = strtolower(pathinfo((string) ($info->photo_name ?: $info->photo_path), PATHINFO_EXTENSION));
                                    $isInfoImg = in_array($infoExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                @endphp
                                @if($isInfoImg)
                                    <button type="button" class="ai-history-item__link" onclick="openStoredImage('{{ route('attachments.additional-info.view', $info->id) }}')">
                                        <i class='bx bx-image'></i> Lihat Lampiran
                                    </button>
                                @else
                                    <a class="ai-history-item__link" href="{{ route('attachments.additional-info.view', $info->id) }}" target="_blank">
                                        <i class='bx bx-file'></i> Unduh Lampiran
                                    </a>
                                @endif
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Delete Request --}}
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
                            <button type="button" class="td-delete-submit" onclick="openDeleteModal()">
                                Ajukan Penghapusan ke Admin
                            </button>
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

                    @if(!$latestDeletionRequest || $latestDeletionRequest->status !== 'pending')
                    <div class="td-modal-backdrop" id="deleteRequestModal">
                        <div class="td-modal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
                            <div class="td-modal-head">
                                <h4 id="deleteModalTitle">Ajukan Penghapusan Tiket</h4>
                                <button type="button" class="td-modal-close" onclick="closeDeleteModal()">&times;</button>
                            </div>
                            <form method="POST" action="{{ route('client.tickets.deletion-request', $ticket->id) }}">
                                @csrf
                                <div class="td-modal-body">
                                    <p class="td-modal-text">Pilih alasan penghapusan tiket, lalu jelaskan detail tambahan.</p>
                                    <div class="td-delete-grid">
                                        @foreach($deletionReasons as $reasonKey => $reasonLabel)
                                            <label class="td-delete-check">
                                                <input type="checkbox" name="reasons[]" value="{{ $reasonKey }}" {{ in_array($reasonKey, old('reasons', [])) ? 'checked' : '' }}>
                                                <span>{{ $reasonLabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('reasons') <p style="font-size:.8rem;color:#dc2626;margin:.45rem 0 0;">{{ $message }}</p> @enderror
                                    @error('reasons.*') <p style="font-size:.8rem;color:#dc2626;margin:.45rem 0 0;">{{ $message }}</p> @enderror
                                    <textarea class="td-delete-note" name="custom_reason" placeholder="Tuliskan alasan tambahan Anda (wajib, minimal 10 karakter)...">{{ old('custom_reason') }}</textarea>
                                    @error('custom_reason') <p style="font-size:.8rem;color:#dc2626;margin:.45rem 0 0;">{{ $message }}</p> @enderror
                                </div>
                                <div class="td-modal-foot">
                                    <button type="button" class="td-modal-cancel" onclick="closeDeleteModal()">Batal</button>
                                    <button type="submit" class="td-delete-submit" style="margin-top:0;">Kirim Pengajuan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- Vendor Rating --}}
                    @if($ticket->status === 'resolved' || $ticket->status === 'closed')
                    @php
                        $feedback = $ticket->feedbacks()->where('user_id', Auth::id())->latest()->first();
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
                            <textarea id="vr-comment" class="vr-textarea" rows="4" maxlength="1000"
                                placeholder="Ceritakan pengalaman Anda dengan vendor ini..."></textarea>
                            <div class="vr-actions">
                                <span class="vr-hint" id="vr-char-count">0 / 1000 karakter</span>
                                <button class="vr-submit" id="vr-submit-btn" onclick="vrSubmit()" disabled>
                                    <i class='bx bx-send'></i> Kirim Rating
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                    @endif

                </div>
            </div>
        </div>

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             KANAN: Sidebar
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        <div class="td-sidebar">
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

    </div>
</div>

{{-- â-€â-€ Lightbox Overlay â-€â-€ --}}
<div class="ai-lightbox" id="aiLightbox" onclick="aiCloseLightbox()">
    <button class="ai-lightbox__close" onclick="aiCloseLightbox()">
        <i class='bx bx-x'></i>
    </button>
    <img class="ai-lightbox__img" id="aiLightboxImg" src="" alt="Preview">
</div>

@endsection

@push('scripts')
<script>
/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   ADDITIONAL INFO - File Upload
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
let aiFiles = [];
const AI_MAX = 5;
const AI_MAX_SIZE = 5 * 1024 * 1024;

function aiUpdateChar() {
    const ta = document.getElementById('aiNote');
    const el = document.getElementById('aiCharNum');
    if (ta && el) el.textContent = ta.value.length;
}
function openStoredImage(url) {
    const lightbox = document.getElementById('aiLightbox');
    const img = document.getElementById('aiLightboxImg');
    if (!lightbox || !img || !url) return;
    img.src = url;
    lightbox.classList.add('is-open');
    document.body.style.overflow = 'hidden';
}

function toggleAdditionalInfoForm() {
    const form = document.getElementById('aiForm');
    if (!form) return;
    form.classList.toggle('is-hidden');
}

function aiShowErr(msg) {
    const el = document.getElementById('aiFileErr');
    document.getElementById('aiFileErrMsg').textContent = msg;
    el.style.display = 'flex';
}
function aiHideErr() {
    document.getElementById('aiFileErr').style.display = 'none';
}

function aiHandleFiles(incoming) {
    aiHideErr();
    const arr = Array.from(incoming);

    const dupes = arr.filter(f => aiFiles.some(s => s.name === f.name && s.size === f.size));
    if (dupes.length) { aiShowErr(`"${dupes[0].name}" sudah ditambahkan.`); return; }

    const combined = [...aiFiles, ...arr];
    if (combined.length > AI_MAX) { aiShowErr(`Maksimal ${AI_MAX} foto.`); return; }

    const tooBig = arr.find(f => f.size > AI_MAX_SIZE);
    if (tooBig) { aiShowErr(`"${tooBig.name}" melebihi 5 MB.`); return; }

    aiFiles = combined;
    aiSyncInputs();
    aiShowProgress(arr.length);
}

function aiSyncInputs() {
    const container = document.getElementById('aiFileInputsContainer');
    container.innerHTML = '';
    if (!aiFiles.length) return;
    try {
        const dt = new DataTransfer();
        aiFiles.forEach(f => dt.items.add(f));
        const inp = document.createElement('input');
        inp.type = 'file'; inp.name = 'photos[]'; inp.multiple = true; inp.style.display = 'none';
        container.appendChild(inp);
        inp.files = dt.files;
    } catch(e) { console.warn('DataTransfer not supported'); }
}

function aiShowProgress(count) {
    const pw = document.getElementById('aiProgressWrap');
    const pf = document.getElementById('aiProgressFill');
    const pl = document.getElementById('aiProgressLabel');
    pw.style.display = 'block';
    pf.style.width = '0%';
    pl.textContent = `Memproses ${count} foto-¦`;
    let p = 0;
    const iv = setInterval(() => {
        p += Math.random() * 40;
        if (p >= 100) {
            p = 100; clearInterval(iv);
            setTimeout(() => { pw.style.display = 'none'; }, 350);
            aiRenderThumbs();
        }
        pf.style.width = Math.min(p, 100) + '%';
    }, 55);
}

function aiRenderThumbs() {
    const grid = document.getElementById('aiThumbGrid');
    const dz   = document.getElementById('aiDropZone');
    const addMore = document.getElementById('aiAddMore');
    const counter = document.getElementById('aiFileCount');

    grid.innerHTML = '';

    aiFiles.forEach((file, idx) => {
        const item = document.createElement('div');
        item.className = 'ai-thumb';

        if (file.type.startsWith('image/')) {
            item.innerHTML = `
                <img class="ai-thumb__img" src="" alt="${escHtml(file.name)}" onclick="aiPreview(${idx})">
                <button type="button" class="ai-thumb__rm" onclick="aiRemove(${idx})" title="Hapus">
                    <i class='bx bx-x'></i>
                </button>
                <div class="ai-thumb__overlay" onclick="aiPreview(${idx})">
                    <i class='bx bx-zoom-in'></i>
                </div>`;
            const reader = new FileReader();
            reader.onload = e => {
                const img = item.querySelector('.ai-thumb__img');
                if (img) img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            const ext = file.name.split('.').pop().toUpperCase();
            item.innerHTML = `
                <div class="ai-thumb__file">
                    <i class='bx bx-file-blank'></i>
                    <span>${ext}</span>
                </div>
                <button type="button" class="ai-thumb__rm" onclick="aiRemove(${idx})" title="Hapus">
                    <i class='bx bx-x'></i>
                </button>`;
        }

        // File name tooltip
        const nameEl = document.createElement('div');
        nameEl.className = 'ai-thumb__name';
        nameEl.textContent = file.name;
        item.appendChild(nameEl);

        grid.appendChild(item);
    });

    // Toggle visibility
    const hasFiles = aiFiles.length > 0;
    dz.style.display = hasFiles ? 'none' : 'block';
    addMore.style.display = hasFiles ? 'inline-flex' : 'none';
    addMore.disabled = aiFiles.length >= AI_MAX;
    addMore.classList.toggle('is-full', aiFiles.length >= AI_MAX);
    if (counter) counter.textContent = `${aiFiles.length}/${AI_MAX}`;
}

function aiRemove(idx) {
    aiFiles.splice(idx, 1);
    aiSyncInputs();
    aiRenderThumbs();
    aiHideErr();
    document.getElementById('aiFileInput').value = '';
}

/* â-€â-€ Lightbox â-€â-€ */
function aiPreview(idx) {
    const file = aiFiles[idx];
    if (!file || !file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('aiLightboxImg').src = e.target.result;
        document.getElementById('aiLightbox').classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };
    reader.readAsDataURL(file);
}
function aiCloseLightbox() {
    document.getElementById('aiLightbox').classList.remove('is-open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') aiCloseLightbox(); });

/* â-€â-€ Form submit fallback â-€â-€ */
const aiFormEl = document.getElementById('aiForm');
if (aiFormEl) {
    aiFormEl.addEventListener('submit', function(e) {
        const testInp = document.querySelector('#aiFileInputsContainer input');
        if (testInp && testInp.files && testInp.files.length === aiFiles.length) return;
        if (!aiFiles.length) return;
        e.preventDefault();
        const fd = new FormData(this);
        fd.delete('photos[]');
        aiFiles.forEach(f => fd.append('photos[]', f));
        const btn = document.getElementById('aiSubmitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Mengirim...";
        }
        fetch(this.action, { method: 'POST', body: fd })
            .then(r => { if (r.redirected) window.location.href = r.url; else return r.text(); })
            .then(html => { if (html) { document.open(); document.write(html); document.close(); } })
            .catch(() => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = "<i class='bx bx-send'></i> Kirim Informasi";
                }
            });
    });
}

function openDeleteModal() {
    const modal = document.getElementById('deleteRequestModal');
    if (!modal) return;
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteRequestModal');
    if (!modal) return;
    modal.classList.remove('open');
    document.body.style.overflow = '';
}

document.addEventListener('click', function (event) {
    const modal = document.getElementById('deleteRequestModal');
    if (modal && event.target === modal) {
        closeDeleteModal();
    }
});

@if($errors->has('reasons') || $errors->has('reasons.*') || $errors->has('custom_reason'))
document.addEventListener('DOMContentLoaded', function () {
    openDeleteModal();
});
@endif

function escHtml(str) {
    return str.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   VENDOR RATING
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
(function () {
    'use strict';
    var selectedRating = 0;
    var ratingLabels = ['','Sangat Buruk','Buruk','Cukup','Baik','Sangat Baik'];

    window.vrSetStar = function(star) {
        selectedRating = star;
        document.querySelectorAll('.vr-star[data-star]').forEach(function(btn) {
            const isActive = parseInt(btn.dataset.star) <= star;
            btn.classList.toggle('active', isActive);
            const icon = btn.querySelector('i');
            if (icon) {
                icon.classList.toggle('bx-star', !isActive);
                icon.classList.toggle('bxs-star', isActive);
            }
        });
        var lbl = document.getElementById('vr-score-label');
        if (lbl) lbl.textContent = star + '/5 bintang - ' + (ratingLabels[star] ?? '');
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
                Swal.fire({ icon:'success', title:'Rating Terkirim!', text:'Terima kasih atas penilaian Anda.', timer:2500, showConfirmButton:false })
                    .then(function() { location.reload(); });
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

@push('styles')
<style>
/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   BASE (unchanged from original)
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.td-wrapper { animation: td-fadein .35s ease; max-width: 1200px; margin: 0 auto; width: 100%; }
@keyframes td-fadein { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }

.td-header {
    display: flex; align-items: center; gap: 1.25rem;
    background: var(--gradient); color: white;
    border-radius: 16px; padding: 1.5rem 2rem;
    margin-bottom: 1.75rem; box-shadow: var(--shadow-colored);
}
.td-header-icon { width:64px; height:64px; background:rgba(255,255,255,.2); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:2rem; flex-shrink:0; }
.td-header-body { flex:1; min-width:0; }
.td-number-badge { display:inline-flex; align-items:center; gap:.3rem; background:rgba(255,255,255,.2); border-radius:20px; padding:.25rem .75rem; font-size:.8rem; font-weight:700; margin-bottom:.5rem; }
.td-title { font-size:1.375rem; font-weight:800; margin:0 0 .5rem; line-height:1.3; }
.td-meta { display:flex; gap:1.25rem; flex-wrap:wrap; font-size:.8125rem; opacity:.88; }
.td-meta span { display:flex; align-items:center; gap:.3rem; }

.td-grid { display:grid !important; grid-template-columns:minmax(0,1fr) !important; gap:1.75rem; align-items:start; width:100%; }
.td-grid > div { min-width:0; }
@media (max-width:900px) { .td-grid { grid-template-columns:1fr !important; } }

.td-card { background:white; border-radius:16px; border:1.5px solid var(--border); overflow:hidden; transition:box-shadow .2s; margin-bottom:0; }
.td-card:hover { box-shadow:var(--shadow); }
.td-card-header { display:flex; align-items:center; gap:.875rem; padding:1.25rem 1.5rem; background:linear-gradient(135deg,#f8fafc,#f1f5f9); border-bottom:1.5px solid var(--border); }
.td-card-icon { width:38px; height:38px; background:var(--gradient); border-radius:10px; display:flex; align-items:center; justify-content:center; color:white; font-size:1.125rem; }
.td-card-title { font-size:1rem; font-weight:700; color:var(--text); margin:0; }
.td-card-body { padding:1.75rem; }

.td-info-label { display:flex; align-items:center; gap:.4rem; font-size:.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; margin-bottom:.625rem; }
.td-info-label i { color:var(--primary); }
.td-description { padding:1.125rem 1.25rem; background:#f8fafc; border-left:4px solid var(--primary); border-radius:0 10px 10px 0; color:var(--text); font-size:.9375rem; line-height:1.7; margin-bottom:1.75rem; }

.td-details-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:1.25rem; margin-bottom:1.75rem; }
@media (max-width:640px) { .td-details-grid { grid-template-columns:1fr; } }
.td-detail-item { padding:1.125rem; background:#f8fafc; border-radius:12px; border:1px solid var(--border); }
.td-detail-label { display:flex; align-items:center; gap:.375rem; font-size:.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.3px; margin-bottom:.5rem; }
.td-detail-label i { color:var(--primary); }
.td-detail-value { font-size:.9375rem; font-weight:700; color:var(--text); }
.td-priority-item { grid-column:1/-1; }
.td-priority-badge { display:inline-flex; align-items:center; gap:.4rem; padding:.375rem .875rem; border-radius:8px; font-weight:700; font-size:.875rem; }
.td-priority-badge i { font-size:.55rem; }
.priority-low    { background:#d1fae5; color:#065f46; }
.priority-medium { background:#dbeafe; color:#1e40af; }
.priority-high   { background:#fed7aa; color:#92400e; }
.priority-urgent { background:#fee2e2; color:#991b1b; }
.priority-pending { display:inline-flex; align-items:center; gap:.4rem; font-size:.875rem; color:var(--text-muted); font-style:italic; }
.td-category-badge { display:inline-block; padding:.375rem .875rem; background:rgba(79,70,229,.1); color:var(--primary); border-radius:8px; font-weight:700; }
.td-status-badge { display:inline-flex; align-items:center; gap:.4rem; padding:.375rem .875rem; border-radius:8px; font-weight:700; font-size:.875rem; }
.status-open { background:#dbeafe; color:#1e40af; }
.status-in_progress { background:#fef3c7; color:#92400e; }
.status-resolved { background:#d1fae5; color:#065f46; }
.status-closed { background:#f3f4f6; color:#374151; }
.status-pending { background:#ede9fe; color:#6d28d9; }

.td-urgency-banner { display:flex; align-items:flex-start; gap:1rem; padding:1.125rem 1.25rem; border-radius:12px; background:rgba(79,70,229,.05); border:1px solid rgba(79,70,229,.15); margin-bottom:1.75rem; }
.td-urgency-banner i { font-size:1.375rem; color:var(--primary); flex-shrink:0; margin-top:.1rem; }
.td-urgency-title { font-size:.9375rem; font-weight:700; color:var(--text); margin:0 0 .25rem; }
.td-urgency-text { font-size:.875rem; color:var(--text-muted); margin:0; line-height:1.6; }
.td-urgency-chip { display:inline-flex; align-items:center; gap:.3rem; margin-left:auto; flex-shrink:0; padding:.375rem .8rem; border-radius:20px; font-size:.8rem; font-weight:700; }
.urgency-low { background:#d1fae5; color:#065f46; }
.urgency-medium { background:#fef3c7; color:#92400e; }
.urgency-high { background:#fee2e2; color:#991b1b; }
.urgency-critical { background:#fce7f3; color:#9d174d; }

.td-event-grid { display:flex; flex-direction:column; gap:.625rem; padding:1.125rem; background:rgba(79,70,229,.04); border-radius:12px; border-left:4px solid var(--primary); margin-bottom:1.75rem; }
.td-event-item { display:flex; align-items:center; gap:.75rem; font-size:.9375rem; color:var(--text); font-weight:500; }
.td-event-item i { color:var(--primary); width:20px; font-size:1rem; }

.td-attachment-list { display:flex; flex-direction:column; gap:.625rem; }
.td-attachment-item { display:flex; align-items:center; gap:.875rem; padding:.875rem 1rem; background:#f8fafc; border-radius:10px; border:1px solid var(--border); transition:all .2s; }
.td-attachment-item:hover { background:white; border-color:var(--primary); box-shadow:0 2px 8px rgba(79,70,229,.1); }
.td-att-icon { width:38px; height:38px; background:white; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1.25rem; color:var(--primary); }
.td-att-info { flex:1; min-width:0; }
.td-att-name { font-size:.9rem; font-weight:700; color:var(--text); margin:0 0 .15rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.td-att-size { font-size:.78rem; color:var(--text-light); }
.td-att-dl { width:34px; height:34px; background:var(--primary); color:white; border-radius:8px; display:flex; align-items:center; justify-content:center; text-decoration:none; transition:all .2s; border:none; cursor:pointer; }
.td-att-dl:hover { background:var(--primary-dark); transform:scale(1.1); }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   ADDITIONAL INFO - NEW DESIGN
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.ai-wrap {
    margin-top: 1.75rem;
    background: #fff;
    border: 1.5px solid #c7d2fe;
    border-radius: 16px;
    overflow: hidden;
}

/* Header */
.ai-header {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
    border-bottom: 1px solid #c7d2fe;
}
.ai-header__icon {
    width: 40px; height: 40px;
    border-radius: 11px;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: #fff; flex-shrink: 0;
}
.ai-header__title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #3730a3;
    margin-bottom: 2px;
}
.ai-header__sub {
    font-size: 0.78rem;
    color: #6366f1;
    line-height: 1.4;
}

/* Form body */
.ai-status-ok {
    margin: 1rem 1.25rem 0;
    padding: 0.75rem 0.9rem;
    border-radius: 10px;
    border: 1px solid #bbf7d0;
    background: #f0fdf4;
    color: #166534;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.82rem;
}
.ai-status-ok i { font-size: 1rem; color: #16a34a; }
.ai-reopen-btn {
    margin-left: auto;
    border: 1px solid #86efac;
    background: #fff;
    color: #166534;
    border-radius: 8px;
    padding: 0.3rem 0.65rem;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
}
.ai-form { padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem; }
.ai-form.is-hidden { display: none; }

.ai-field { display: flex; flex-direction: column; gap: 5px; }

.ai-label {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.ai-label i { font-size: 13px; color: #6366f1; }
.ai-limit-badge {
    margin-left: auto;
    font-size: 0.68rem;
    font-weight: 500;
    color: #94a3b8;
    text-transform: none;
    letter-spacing: 0;
}

/* Textarea */
.ai-textarea-wrap { position: relative; }
.ai-textarea {
    width: 100%;
    padding: 10px 12px;
    padding-bottom: 24px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.875rem;
    font-family: inherit;
    color: #0f172a;
    background: #f8fafc;
    resize: vertical;
    min-height: 100px;
    line-height: 1.6;
    transition: all 0.18s;
}
.ai-textarea:focus {
    outline: none;
    border-color: #6366f1;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}
.ai-textarea::placeholder { color: #94a3b8; }
.ai-textarea.is-err { border-color: #ef4444; background: #fff5f5; }
.ai-char {
    position: absolute;
    bottom: 8px; right: 10px;
    font-size: 0.68rem;
    color: #94a3b8;
    pointer-events: none;
    font-weight: 500;
}

/* Error */
.ai-err {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #dc2626;
    font-size: 0.78rem;
    font-weight: 500;
}
.ai-err i { font-size: 13px; }

/* Drop zone */
.ai-dropzone {
    border: 2px dashed #c7d2fe;
    border-radius: 12px;
    padding: 1.5rem 1rem;
    text-align: center;
    cursor: pointer;
    background: #f5f3ff;
    transition: all 0.2s;
}
.ai-dropzone:hover, .ai-dropzone.is-over {
    border-color: #6366f1;
    background: #eef2ff;
}
.ai-dropzone__icon {
    width: 46px; height: 46px;
    background: #e0e7ff;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: #6366f1;
    margin: 0 auto 0.625rem;
    transition: all 0.18s;
}
.ai-dropzone:hover .ai-dropzone__icon,
.ai-dropzone.is-over .ai-dropzone__icon {
    background: #c7d2fe;
}
.ai-dropzone__text { font-size: 0.85rem; color: #334155; margin-bottom: 3px; }
.ai-dropzone__text strong { color: #6366f1; font-weight: 600; }
.ai-dropzone__hint { font-size: 0.73rem; color: #94a3b8; }

/* Progress */
.ai-progress {
    display: flex; align-items: center; gap: 10px;
    font-size: 0.76rem; color: #64748b; font-weight: 500;
}
.ai-progress__track { flex:1; height:4px; background:#e2e8f0; border-radius:2px; overflow:hidden; }
.ai-progress__fill { height:100%; background:linear-gradient(90deg,#6366f1,#7c3aed); transition:width .2s ease; }

/* â-€â-€ Thumbnail Grid â-€â-€ */
.ai-thumb-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    gap: 0.625rem;
    margin-top: 0.625rem;
}

.ai-thumb {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    background: #f1f5f9;
    border: 1.5px solid #e2e8f0;
    aspect-ratio: 1;
    animation: ai-pop 0.2s cubic-bezier(0.34,1.56,0.64,1);
    cursor: pointer;
}

@keyframes ai-pop {
    from { opacity:0; transform:scale(0.85); }
    to   { opacity:1; transform:scale(1); }
}

.ai-thumb__img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.2s;
}
.ai-thumb:hover .ai-thumb__img { transform: scale(1.05); }

.ai-thumb__overlay {
    position: absolute;
    inset: 0;
    background: rgba(99,102,241,0.4);
    display: flex; align-items: center; justify-content: center;
    opacity: 0;
    transition: opacity 0.18s;
    color: #fff;
    font-size: 20px;
}
.ai-thumb:hover .ai-thumb__overlay { opacity: 1; }

.ai-thumb__file {
    width: 100%; height: 100%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 4px;
    color: #6366f1;
    font-size: 24px;
}
.ai-thumb__file span {
    font-size: 0.6rem;
    font-weight: 700;
    background: #6366f1;
    color: #fff;
    padding: 1px 5px;
    border-radius: 4px;
    letter-spacing: 0.03em;
}

/* Remove button */
.ai-thumb__rm {
    position: absolute;
    top: 5px; right: 5px;
    width: 22px; height: 22px;
    border-radius: 6px;
    border: none;
    background: rgba(15,23,42,0.7);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.15s;
    z-index: 2;
}
.ai-thumb__rm:hover { background: #ef4444; }

/* File name tooltip */
.ai-thumb__name {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    background: rgba(15,23,42,0.75);
    color: #fff;
    font-size: 0.6rem;
    padding: 3px 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    opacity: 0;
    transition: opacity 0.15s;
}
.ai-thumb:hover .ai-thumb__name { opacity: 1; }

/* Add more button */
.ai-add-more {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border: 1.5px dashed #c7d2fe;
    border-radius: 10px;
    background: #f5f3ff;
    color: #6366f1;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.18s;
    margin-top: 4px;
}
.ai-add-more:hover:not(:disabled) {
    border-color: #6366f1;
    background: #eef2ff;
}
.ai-add-more.is-full {
    opacity: 0.5;
    cursor: not-allowed;
}
.ai-add-more__count {
    background: #6366f1;
    color: #fff;
    padding: 1px 7px;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 700;
}

/* Submit button */
.ai-actions { display: flex; justify-content: flex-end; padding-top: 4px; }
.ai-submit {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 20px;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.18s;
    box-shadow: 0 4px 14px rgba(99,102,241,0.3);
    font-family: inherit;
}
.ai-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(99,102,241,0.4);
}
.ai-submit:disabled {
    opacity: 0.65; cursor: not-allowed; transform: none;
}

/* â-€â-€ Lightbox â-€â-€ */
.ai-lightbox {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.88);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
    backdrop-filter: blur(4px);
}
.ai-lightbox.is-open {
    opacity: 1;
    pointer-events: all;
}
.ai-lightbox__img {
    max-width: 90vw;
    max-height: 88vh;
    border-radius: 12px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.5);
    transform: scale(0.95);
    transition: transform 0.2s;
}
.ai-lightbox.is-open .ai-lightbox__img { transform: scale(1); }
.ai-lightbox__close {
    position: absolute;
    top: 16px; right: 16px;
    width: 38px; height: 38px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,0.15);
    color: #fff;
    font-size: 20px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.15s;
}
.ai-lightbox__close:hover { background: rgba(255,255,255,0.25); }

/* â-€â-€ History (previous submissions) â-€â-€ */
.ai-history { margin-top: 1.5rem; }
.ai-history-item {
    padding: 0.875rem 1rem;
    background: #fafafa;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 0.625rem;
    transition: background 0.15s;
}
.ai-history-item:hover { background: #f8fafc; }
.ai-history-item__meta {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.75rem; margin-bottom: 6px;
}
.ai-history-item__author {
    display: flex; align-items: center; gap: 4px;
    font-weight: 600; color: #334155;
}
.ai-history-item__author i { font-size: 14px; color: #6366f1; }
.ai-history-item__time { color: #94a3b8; }
.ai-history-item__note {
    margin: 0 0 6px;
    font-size: 0.875rem;
    color: #334155;
    line-height: 1.6;
    white-space: pre-wrap;
}
.ai-history-item__link {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.78rem; font-weight: 600;
    color: #6366f1;
    padding: 3px 10px;
    border: 1px solid #c7d2fe;
    border-radius: 7px;
    text-decoration: none;
    transition: all 0.15s;
    background: #fff;
    cursor: pointer;
}
.ai-history-item__link:hover { background: #eef2ff; }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   REMAINING ORIGINAL STYLES
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.td-delete-wrap { margin-top:1.5rem; border:1px solid #fecaca; background:linear-gradient(135deg,#fff1f2,#fff); border-radius:14px; padding:1rem; }
.td-delete-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.5rem .8rem; margin-top:.75rem; }
@media (max-width:700px) { .td-delete-grid { grid-template-columns:1fr; } }
.td-delete-check { display:flex; gap:.45rem; font-size:.85rem; color:#334155; }
.td-delete-check input { margin-top:.15rem; }
.td-delete-note { width:100%; margin-top:.75rem; border:1.5px solid var(--border); border-radius:10px; padding:.75rem .9rem; min-height:110px; font-size:.88rem; font-family:inherit; }
.td-delete-note:focus { outline:none; border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.12); }
.td-delete-submit { margin-top:.75rem; border:none; border-radius:10px; padding:.62rem .95rem; font-weight:700; color:#fff; background:linear-gradient(135deg,#ef4444,#dc2626); cursor:pointer; }
.td-delete-status { margin-top:.85rem; border-radius:10px; padding:.75rem .85rem; font-size:.84rem; background:#fff; border:1px solid #e2e8f0; }
.td-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1100;
    background: rgba(15, 23, 42, 0.55);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.td-modal-backdrop.open { display: flex; }
.td-modal {
    width: 100%;
    max-width: 720px;
    background: #fff;
    border-radius: 14px;
    border: 1px solid var(--border);
    box-shadow: 0 20px 48px rgba(15, 23, 42, 0.3);
}
.td-modal-head {
    padding: 0.9rem 1rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.td-modal-head h4 { margin: 0; font-size: 1rem; color: var(--text); }
.td-modal-close {
    border: none;
    background: transparent;
    font-size: 1.5rem;
    line-height: 1;
    color: var(--text-muted);
    cursor: pointer;
}
.td-modal-body { padding: 1rem; }
.td-modal-text { margin: 0 0 0.75rem; font-size: 0.84rem; color: var(--text-muted); }
.td-modal-foot {
    padding: 0.85rem 1rem;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 0.6rem;
}
.td-modal-cancel {
    border: 1px solid var(--border);
    border-radius: 10px;
    background: #fff;
    color: var(--text);
    padding: 0.62rem .95rem;
    font-weight: 700;
    cursor: pointer;
}

.vr-card { padding:1.4rem; background:linear-gradient(135deg,#fff7ed,#fff); border:1px solid #fed7aa; border-radius:16px; margin-top:1.75rem; }
.vr-card.submitted { background:linear-gradient(135deg,#ecfdf5,#fff); border-color:#a7f3d0; }
.vr-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1rem; }
.vr-head h4 { margin:0 0 .3rem; font-size:1.0625rem; font-weight:700; color:var(--text); }
.vr-head p  { margin:0; font-size:.875rem; color:var(--text-muted); line-height:1.6; }
.vr-badge { display:inline-flex; align-items:center; padding:.375rem .75rem; border-radius:999px; font-size:.75rem; font-weight:700; flex-shrink:0; background:#dcfce7; color:#166534; }
.vr-badge-pending { background:#fef3c7; color:#92400e; }
.vr-stars { display:flex; gap:.5rem; margin-bottom:.75rem; flex-wrap:wrap; }
.vr-star { width:44px; height:44px; border:1px solid transparent; border-radius:12px; background:var(--border); color:var(--text-muted); display:flex; align-items:center; justify-content:center; font-size:1rem; cursor:pointer; transition:all .2s; }
.vr-star.active { background:#fff7d6; border-color:#f59e0b; color:#f59e0b; box-shadow:0 6px 16px rgba(245,158,11,.18); }
.vr-star:not([disabled]):hover { transform:translateY(-2px); }
.vr-star[disabled] { cursor:default; }
.vr-score { font-size:.9rem; font-weight:700; color:var(--text); margin:0 0 .875rem; }
.vr-comment-box { padding:1rem; background:white; border:1px solid #d1fae5; border-radius:12px; color:var(--text); line-height:1.7; white-space:pre-line; }
.vr-comment-empty { font-size:.875rem; color:var(--text-muted); margin:0; }
.vr-textarea { width:100%; min-height:110px; padding:1rem; border:1.5px solid var(--border); border-radius:12px; background:white; color:var(--text); resize:vertical; font-family:inherit; font-size:.9rem; transition:all .2s; }
.vr-textarea:focus { outline:none; border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.15); }
.vr-actions { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-top:1rem; flex-wrap:wrap; }
.vr-hint { font-size:.78rem; color:var(--text-muted); }
.vr-submit { display:inline-flex; align-items:center; justify-content:center; gap:.5rem; padding:.75rem 1.25rem; border:none; border-radius:12px; cursor:pointer; background:linear-gradient(135deg,#f59e0b,#f97316); color:white; font-weight:700; font-size:.9rem; transition:all .2s; box-shadow:0 8px 18px rgba(245,158,11,.24); }
.vr-submit:hover:not(:disabled) { transform:translateY(-2px); }
.vr-submit:disabled { opacity:.6; cursor:not-allowed; box-shadow:none; transform:none; }

.td-timeline { display:flex; flex-direction:column; gap:1.375rem; }
.td-tl-item { display:flex; gap:.875rem; position:relative; }
.td-tl-item:not(:last-child)::after { content:''; position:absolute; left:18px; top:38px; width:2px; height:calc(100% + 1.375rem); background:var(--border); }
.td-tl-icon { width:38px; height:38px; border-radius:10px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:.9375rem; position:relative; z-index:1; }
.tl-created { background:#dbeafe; color:#1e40af; }
.tl-assigned { background:#ede9fe; color:#6d28d9; }
.tl-response { background:#fef3c7; color:#92400e; }
.tl-resolved { background:#d1fae5; color:#065f46; }
.tl-closed { background:#f3f4f6; color:#374151; }
.td-tl-label { font-size:.875rem; font-weight:600; color:var(--text); margin:0 0 .2rem; }
.td-tl-date  { font-size:.8125rem; color:var(--text-muted); margin:0; }

.td-sidebar { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; }
@media (max-width:900px) { .td-sidebar { grid-template-columns:1fr; } }

.admin-badge { display:inline-block; margin-left:.4rem; padding:.125rem .5rem; background:rgba(79,70,229,.1); color:var(--primary); border-radius:6px; font-size:.68rem; font-weight:700; vertical-align:middle; }

.td-sidebar-meta { display:flex; flex-direction:column; gap:.75rem; }
.td-sidebar-meta-item { display:flex; flex-direction:column; gap:.25rem; padding:.875rem 1rem; background:#f8fafc; border-radius:12px; border:1px solid var(--border); }
.td-sidebar-meta-item .lbl { display:flex; align-items:center; gap:.35rem; font-size:.74rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.3px; }
.td-sidebar-meta-item .lbl i { color:var(--primary); font-size:.9rem; }
.td-sidebar-meta-item .val { font-size:.9rem; font-weight:700; color:var(--text); }
</style>
@endpush


