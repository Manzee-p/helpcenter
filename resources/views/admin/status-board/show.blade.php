@extends('layouts.app')

@section('title', 'Detail Status')
@section('page_title', 'Detail Status')
@section('breadcrumb', 'Home / Papan Status / Detail')

@php
    $catLabels = [
        'power_outage' => 'Gangguan Listrik',
        'technical_issue' => 'Masalah Teknis',
        'facility_issue' => 'Masalah Fasilitas',
        'network_issue' => 'Gangguan Jaringan',
        'other' => 'Lainnya',
    ];
    $sevLabels = [
        'critical' => 'Kritis',
        'high' => 'Tinggi',
        'medium' => 'Sedang',
        'low' => 'Rendah',
    ];
    $statusLabels = [
        'investigating' => 'Sedang Diselidiki',
        'identified' => 'Teridentifikasi',
        'monitoring' => 'Pemantauan',
        'resolved' => 'Selesai',
    ];
    $typeLabels = [
        'investigating' => 'Penyelidikan',
        'update' => 'Update',
        'resolved' => 'Selesai',
    ];
@endphp

@push('styles')
<style>
.sd-wrap { display:grid; grid-template-columns: 1fr 320px; gap: 1.25rem; }
.sd-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 2px 8px rgba(0,0,0,.05); }
.sd-head { padding:1.25rem; border-bottom:1px solid #e5e7eb; }
.sd-body { padding:1.25rem; }
.sd-title { margin:0; font-size:1.5rem; font-weight:700; color:#111827; }
.sd-sub { margin:.5rem 0 0; color:#6b7280; }
.sd-badges { display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:.75rem; }
.sd-badge { display:inline-flex; align-items:center; padding:.3rem .65rem; border-radius:999px; font-size:.75rem; font-weight:600; }
.sd-row { display:flex; flex-wrap:wrap; gap:1rem; color:#4b5563; margin-top:1rem; font-size:.9rem; }
.sd-sev-critical { background:#fee2e2; color:#991b1b; }
.sd-sev-high { background:#fef3c7; color:#92400e; }
.sd-sev-medium { background:#dbeafe; color:#1e40af; }
.sd-sev-low { background:#f3f4f6; color:#374151; }
.sd-status-investigating { background:#fef3c7; color:#92400e; }
.sd-status-identified { background:#dbeafe; color:#1e40af; }
.sd-status-monitoring { background:#e0e7ff; color:#4338ca; }
.sd-status-resolved { background:#d1fae5; color:#065f46; }
.sd-main { display:flex; flex-direction:column; gap:1rem; }
.sd-actions { display:flex; flex-direction:column; gap:.6rem; }
.sd-btn { border:0; border-radius:10px; padding:.65rem .8rem; font-weight:600; cursor:pointer; }
.sd-btn-ghost { background:#eef2ff; color:#4338ca; }
.sd-btn-info { background:#dbeafe; color:#1d4ed8; }
.sd-btn-danger { background:#fee2e2; color:#b91c1c; }
.sd-btn-primary { background:#4f46e5; color:#fff; }
.sd-timeline { display:flex; flex-direction:column; gap:.75rem; }
.sd-tl-item { border:1px solid #e5e7eb; border-radius:10px; padding:.8rem; }
.sd-tl-head { display:flex; justify-content:space-between; gap:.75rem; margin-bottom:.4rem; font-size:.85rem; color:#6b7280; }
.sd-tl-msg { margin:0; color:#374151; white-space:pre-wrap; }
.sd-form-group { margin-bottom:.75rem; }
.sd-form-group label { display:block; margin-bottom:.35rem; font-size:.85rem; color:#374151; font-weight:600; }
.sd-form-group select, .sd-form-group textarea { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:.55rem .65rem; }
@media (max-width: 992px) { .sd-wrap { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div style="margin-bottom:1rem;">
    <a href="{{ route('admin.status-board.index') }}" style="text-decoration:none;color:#4f46e5;font-weight:600;">&larr; Kembali ke Daftar Status</a>
</div>

<div class="sd-wrap">
    <div class="sd-main">
        <div class="sd-card">
            <div class="sd-head">
                <div class="sd-badges">
                    <span class="sd-badge" style="background:#eef2ff;color:#4338ca;">{{ $status->incident_number }}</span>
                    <span class="sd-badge sd-status-{{ $status->status }}">{{ $statusLabels[$status->status] ?? $status->status }}</span>
                    <span class="sd-badge sd-sev-{{ $status->severity }}">{{ $sevLabels[$status->severity] ?? $status->severity }}</span>
                    @if($status->is_public)
                        <span class="sd-badge" style="background:#dcfce7;color:#166534;">Publik</span>
                    @else
                        <span class="sd-badge" style="background:#e5e7eb;color:#374151;">Private</span>
                    @endif
                    @if($status->is_pinned)
                        <span class="sd-badge" style="background:#ede9fe;color:#5b21b6;">Pinned</span>
                    @endif
                </div>
                <h1 class="sd-title">{{ $status->title }}</h1>
                <p class="sd-sub">{{ $status->description }}</p>
                <div class="sd-row">
                    <span>Kategori: <strong>{{ $catLabels[$status->category] ?? $status->category }}</strong></span>
                    <span>Area: <strong>{{ $status->affected_area ?: '-' }}</strong></span>
                    <span>Mulai: <strong>{{ optional($status->started_at)->translatedFormat('d M Y H:i') }}</strong></span>
                    @if($status->resolved_at)
                        <span>Selesai: <strong>{{ optional($status->resolved_at)->translatedFormat('d M Y H:i') }}</strong></span>
                    @endif
                </div>
            </div>
        </div>

        <div class="sd-card">
            <div class="sd-head">
                <h3 style="margin:0; font-size:1.1rem;">Timeline Update</h3>
            </div>
            <div class="sd-body">
                <div class="sd-timeline">
                    @forelse($status->updates->sortByDesc('created_at') as $u)
                        <div class="sd-tl-item">
                            <div class="sd-tl-head">
                                <span>{{ $u->user->name ?? 'Admin' }} - {{ $typeLabels[$u->update_type] ?? $u->update_type }}</span>
                                <span>{{ optional($u->created_at)->diffForHumans() }}</span>
                            </div>
                            <p class="sd-tl-msg">{{ $u->message }}</p>
                        </div>
                    @empty
                        <div class="sd-tl-item" style="text-align:center;color:#6b7280;">Belum ada update.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:1rem;">
        <div class="sd-card">
            <div class="sd-head"><h3 style="margin:0;font-size:1.05rem;">Quick Actions</h3></div>
            <div class="sd-body sd-actions">
                <button class="sd-btn sd-btn-ghost" onclick="toggleVisibility()">{{ $status->is_public ? 'Sembunyikan' : 'Tampilkan' }}</button>
                <button class="sd-btn sd-btn-ghost" onclick="togglePin()">{{ $status->is_pinned ? 'Unpin' : 'Pin Status' }}</button>
                <button class="sd-btn sd-btn-info" onclick="submitStatusUpdate()">Update Status</button>
                <button class="sd-btn sd-btn-danger" onclick="executeDelete()">Hapus Status</button>
            </div>
        </div>

        <div class="sd-card">
            <div class="sd-head"><h3 style="margin:0;font-size:1.05rem;">Update Baru</h3></div>
            <div class="sd-body">
                <div class="sd-form-group">
                    <label for="newStatus">Status</label>
                    <select id="newStatus">
                        <option value="investigating" @selected($status->status === 'investigating')>Sedang Diselidiki</option>
                        <option value="identified" @selected($status->status === 'identified')>Teridentifikasi</option>
                        <option value="monitoring" @selected($status->status === 'monitoring')>Pemantauan</option>
                        <option value="resolved" @selected($status->status === 'resolved')>Selesai</option>
                    </select>
                </div>
                <div class="sd-form-group">
                    <label for="updateType">Tipe Update</label>
                    <select id="updateType">
                        <option value="investigating">Penyelidikan</option>
                        <option value="update">Update Progress</option>
                        <option value="resolved">Selesai</option>
                    </select>
                </div>
                <div class="sd-form-group">
                    <label for="updateMessage">Pesan</label>
                    <textarea id="updateMessage" rows="4" maxlength="1000" placeholder="Tulis update terbaru..."></textarea>
                </div>
                <button class="sd-btn sd-btn-primary" style="width:100%;" onclick="submitUpdate()">Kirim Update</button>
            </div>
        </div>

        <div class="sd-card">
            <div class="sd-head"><h3 style="margin:0;font-size:1.05rem;">Info Penanganan</h3></div>
            <div class="sd-body" style="font-size:.9rem;color:#4b5563;line-height:1.6;">
                <div>Dibuat oleh: <strong>{{ $status->creator->name ?? 'N/A' }}</strong></div>
                <div>Ditugaskan: <strong>{{ $status->assignedTo->name ?? 'Belum ditugaskan' }}</strong></div>
                <div>Update terakhir: <strong>{{ optional($status->updated_at)->translatedFormat('d M Y H:i') }}</strong></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const STATUS_ID = {{ (int) $status->id }};

function request(url, method, payload = null) {
    const options = {
        method,
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    };

    if (payload) {
        options.body = JSON.stringify(payload);
    }

    return fetch(url, options).then(res => res.json());
}

function toggleVisibility() {
    const current = @json((bool) $status->is_public);
    request(`/admin/status-board/${STATUS_ID}`, 'PUT', { is_public: !current })
        .then(d => {
            if (d.success) {
                window.location.reload();
                return;
            }
            alert(d.message || 'Gagal mengubah visibilitas');
        })
        .catch(() => alert('Terjadi kesalahan'));
}

function togglePin() {
    const current = @json((bool) $status->is_pinned);
    request(`/admin/status-board/${STATUS_ID}`, 'PUT', { is_pinned: !current })
        .then(d => {
            if (d.success) {
                window.location.reload();
                return;
            }
            alert(d.message || 'Gagal mengubah pin');
        })
        .catch(() => alert('Terjadi kesalahan'));
}

function submitStatusUpdate() {
    const newStatus = document.getElementById('newStatus').value;
    request(`/admin/status-board/${STATUS_ID}`, 'PUT', { status: newStatus })
        .then(d => {
            if (d.success) {
                window.location.reload();
                return;
            }
            alert(d.message || 'Gagal update status');
        })
        .catch(() => alert('Terjadi kesalahan'));
}

function submitUpdate() {
    const message = document.getElementById('updateMessage').value.trim();
    const type = document.getElementById('updateType').value;

    if (!message) {
        alert('Pesan update wajib diisi.');
        return;
    }

    request(`/admin/status-board/${STATUS_ID}/updates`, 'POST', {
        message,
        update_type: type
    })
        .then(d => {
            if (d.success) {
                window.location.reload();
                return;
            }
            alert(d.message || 'Gagal menambahkan update');
        })
        .catch(() => alert('Terjadi kesalahan'));
}

function executeDelete() {
    if (!confirm('Yakin hapus status ini?')) {
        return;
    }

    request(`/admin/status-board/${STATUS_ID}`, 'DELETE')
        .then(d => {
            if (d.success) {
                window.location.href = '{{ route('admin.status-board.index') }}';
                return;
            }
            alert(d.message || 'Gagal menghapus status');
        })
        .catch(() => alert('Terjadi kesalahan'));
}
</script>
@endpush
