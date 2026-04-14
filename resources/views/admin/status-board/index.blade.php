@extends('layouts.app')

@section('title', 'Papan Status')
@section('page_title', 'Papan Status')
@section('breadcrumb', 'Home / Papan Status')



@section('content')
{{-- Toast --}}
<div class="sb-toast" id="sbToast">
    <i class="bx" id="sbToastIcon"></i>
    <span id="sbToastMsg"></span>
</div>

{{-- Delete Modal --}}
<div class="sb-modal-backdrop" id="deleteModal">
    <div class="sb-modal">
        <div class="sb-modal-head">
            <h3><i class="bx bx-error"></i> Konfirmasi Hapus</h3>
            <button class="sb-modal-close" onclick="closeDeleteModal()"><i class="bx bx-x"></i></button>
        </div>
        <div class="sb-modal-body">
            <p class="sb-delete-msg">Apakah Anda yakin ingin menghapus status ini?</p>
            <div class="sb-delete-info">
                <strong id="deleteTargetTitle">-</strong><br>
                <small id="deleteTargetNumber">-</small>
            </div>
            <p class="sb-delete-warn">Tindakan ini tidak dapat dibatalkan!</p>
        </div>
        <div class="sb-modal-foot">
            <button class="sb-btn-cancel" onclick="closeDeleteModal()">Batal</button>
            <button class="sb-btn-danger" onclick="executeDelete()"><i class="bx bx-trash"></i> Hapus Status</button>
        </div>
    </div>
</div>

<div class="sb-page">
<section class="sb-hero">
    <div class="sb-hero-left">
        <div class="sb-hero-icon"><i class="bx bx-info-circle"></i></div>
        <div>
            <h1 class="sb-hero-title">Papan Status</h1>
            <p class="sb-hero-sub">Kelola informasi status gangguan dan pemeliharaan</p>
        </div>
    </div>
    <a href="{{ route('admin.status-board.create') }}" class="sb-btn-create">
        <i class="bx bx-plus-circle"></i> Buat Status Baru
    </a>
</section>

<div class="sb-stats">
    <div class="sb-stat sb-stat-primary">
        <div class="sb-stat-ico"><i class="bx bx-file"></i></div>
        <div><div class="sb-stat-num">{{ $total }}</div><div class="sb-stat-lbl">Total Incident</div></div>
    </div>
    <div class="sb-stat sb-stat-warning">
        <div class="sb-stat-ico"><i class="bx bx-error"></i></div>
        <div><div class="sb-stat-num">{{ $active }}</div><div class="sb-stat-lbl">Incident Aktif</div></div>
    </div>
    <div class="sb-stat sb-stat-success">
        <div class="sb-stat-ico"><i class="bx bx-check-circle"></i></div>
        <div><div class="sb-stat-num">{{ $resolved }}</div><div class="sb-stat-lbl">Terselesaikan</div></div>
    </div>
    <div class="sb-stat sb-stat-danger">
        <div class="sb-stat-ico"><i class="bx bx-error-circle"></i></div>
        <div><div class="sb-stat-num">{{ $critical }}</div><div class="sb-stat-lbl">Kritis</div></div>
    </div>
</div>

<form method="GET" action="{{ route('admin.status-board.index') }}" class="sb-filters">
    <div class="sb-search-wrap">
        <i class="bx bx-search sb-search-ico"></i>
        <input type="text" class="sb-search-input" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan judul, nomor incident, atau area..."/>
    </div>
    <div class="sb-filters-row">
        <div class="sb-filter-item">
            <label>Status</label>
            <select class="sb-select" name="status">
                <option value="">Semua Status</option>
                <option value="investigating" {{ request('status')==='investigating' ? 'selected' : '' }}>Sedang Diselidiki</option>
                <option value="identified" {{ request('status')==='identified' ? 'selected' : '' }}>Teridentifikasi</option>
                <option value="monitoring" {{ request('status')==='monitoring' ? 'selected' : '' }}>Pemantauan</option>
                <option value="resolved" {{ request('status')==='resolved' ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>
        <div class="sb-filter-item">
            <label>Kategori</label>
            <select class="sb-select" name="category">
                <option value="">Semua Kategori</option>
                <option value="power_outage" {{ request('category')==='power_outage' ? 'selected' : '' }}>Gangguan Listrik</option>
                <option value="technical_issue" {{ request('category')==='technical_issue' ? 'selected' : '' }}>Masalah Teknis</option>
                <option value="facility_issue" {{ request('category')==='facility_issue' ? 'selected' : '' }}>Masalah Fasilitas</option>
                <option value="network_issue" {{ request('category')==='network_issue' ? 'selected' : '' }}>Gangguan Jaringan</option>
                <option value="other" {{ request('category')==='other' ? 'selected' : '' }}>Lainnya</option>
            </select>
        </div>
        <div class="sb-filter-item">
            <label>Tingkat</label>
            <select class="sb-select" name="severity">
                <option value="">Semua Tingkat</option>
                <option value="critical" {{ request('severity')==='critical' ? 'selected' : '' }}>Kritis</option>
                <option value="high" {{ request('severity')==='high' ? 'selected' : '' }}>Tinggi</option>
                <option value="medium" {{ request('severity')==='medium' ? 'selected' : '' }}>Sedang</option>
                <option value="low" {{ request('severity')==='low' ? 'selected' : '' }}>Rendah</option>
            </select>
        </div>
        <button type="submit" class="sb-btn-reset"><i class="bx bx-search"></i> Terapkan</button>
        <a href="{{ route('admin.status-board.index') }}" class="sb-btn-reset" style="text-decoration:none;"><i class="bx bx-refresh"></i> Reset Filter</a>
    </div>
</form>

<div class="sb-table-card">
    <div class="sb-table-head">
        <h3><i class="bx bx-list-ul"></i> Daftar Status <span class="sb-count-badge">{{ $statuses->total() }} status</span></h3>
    </div>

    <div class="sb-table-responsive">
        <table class="sb-table">
            <thead>
                <tr>
                    <th>No. Incident</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Area Terdampak</th>
                    <th>Tingkat</th>
                    <th>Status</th>
                    <th>Waktu Mulai</th>
                    <th style="text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($statuses as $s)
                @php
                    $catLabels = ['power_outage'=>'Gangguan Listrik','technical_issue'=>'Masalah Teknis','facility_issue'=>'Masalah Fasilitas','network_issue'=>'Gangguan Jaringan','other'=>'Lainnya'];
                    $catClass = ['power_outage'=>'sb-cat-power','technical_issue'=>'sb-cat-technical','facility_issue'=>'sb-cat-facility','network_issue'=>'sb-cat-network','other'=>'sb-cat-other'];
                    $sevLabels = ['critical'=>'Kritis','high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'];
                    $statusLabels = ['investigating'=>'Sedang Diselidiki','identified'=>'Teridentifikasi','monitoring'=>'Pemantauan','resolved'=>'Selesai'];
                    $statusIcons = ['investigating'=>'bx-search','identified'=>'bx-target-lock','monitoring'=>'bx-show','resolved'=>'bx-check-circle'];
                @endphp
                <tr>
                    <td>
                        <div class="sb-incident-id"><i class="bx bx-hash"></i>{{ $s->incident_number }}</div>
                        @if($s->is_pinned)
                        <span class="sb-pinned-tag"><i class="bx bxs-pin"></i>Pinned</span>
                        @endif
                    </td>
                    <td><div class="sb-title-cell" title="{{ $s->title }}">{{ $s->title }}</div></td>
                    <td><span class="sb-tag {{ $catClass[$s->category] ?? 'sb-cat-other' }}">{{ $catLabels[$s->category] ?? $s->category }}</span></td>
                    <td style="font-size:.875rem;color:#6b7280">{{ $s->affected_area ?: '-' }}</td>
                    <td><span class="sb-tag sb-sev-{{ $s->severity }}">{{ $sevLabels[$s->severity] ?? $s->severity }}</span></td>
                    <td><span class="sb-tag sb-status-{{ $s->status }}"><i class="bx {{ $statusIcons[$s->status] ?? 'bx-circle' }}"></i> {{ $statusLabels[$s->status] ?? $s->status }}</span></td>
                    <td>
                        <div class="sb-date-cell">
                            <span class="sb-date-main">{{ optional($s->started_at)->format('d M Y') }}</span>
                            <span class="sb-date-time">{{ optional($s->started_at)->format('H:i') }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="sb-actions-cell">
                            <a href="{{ route('admin.status-board.show', $s->id) }}" class="sb-action-btn sb-action-view" title="Lihat Detail"><i class="bx bx-show"></i></a>
                            <button class="sb-action-btn sb-action-vis" title="{{ $s->is_public ? 'Sembunyikan' : 'Tampilkan' }}" onclick="toggleVisibility({{ $s->id }}, {{ $s->is_public ? 'true' : 'false' }})"><i class="bx {{ $s->is_public ? 'bx-hide' : 'bx-show' }}"></i></button>
                            <button class="sb-action-btn sb-action-pin" title="{{ $s->is_pinned ? 'Unpin' : 'Pin' }}" onclick="togglePin({{ $s->id }}, {{ $s->is_pinned ? 'true' : 'false' }})"><i class="bx {{ $s->is_pinned ? 'bxs-pin' : 'bx-pin' }}"></i></button>
                            <button class="sb-action-btn sb-action-del" title="Hapus" onclick="confirmDelete({{ $s->id }}, @js($s->title), @js($s->incident_number))"><i class="bx bx-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="sb-empty"><i class="bx bx-folder-open"></i><h4>Tidak Ada Status</h4><p>Belum ada status yang ditemukan</p></div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($statuses->hasPages())
    <div class="sb-pagination">
        <div class="sb-page-info">Menampilkan {{ $statuses->firstItem() }}-{{ $statuses->lastItem() }} dari {{ $statuses->total() }} status</div>
        <div>{{ $statuses->appends(request()->query())->links() }}</div>
    </div>
    @endif
</div>
</div>
@endsection

<script>
const CSRF = '{{ csrf_token() }}';
let deleteTargetId = null;

function showToast(type, msg) {
    const t = document.getElementById('sbToast');
    const icon = document.getElementById('sbToastIcon');
    document.getElementById('sbToastMsg').textContent = msg;
    icon.className = 'bx ' + (type === 'success' ? 'bx-check-circle' : 'bx-error-circle');
    t.className = 'sb-toast ' + type + ' show';
    setTimeout(() => t.classList.remove('show'), 4000);
}

function toggleVisibility(id, isPublic) {
    fetch(`/admin/status-board/${id}`, {
        method: 'PUT',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_public: !isPublic })
    }).then(r => r.json()).then(d => {
        if (d.success) {
            showToast('success', isPublic ? 'Status disembunyikan' : 'Status ditampilkan');
            setTimeout(() => window.location.reload(), 400);
        } else {
            showToast('error', d.message || 'Gagal mengubah visibilitas');
        }
    }).catch(() => showToast('error', 'Terjadi kesalahan'));
}

function togglePin(id, isPinned) {
    fetch(`/admin/status-board/${id}`, {
        method: 'PUT',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_pinned: !isPinned })
    }).then(r => r.json()).then(d => {
        if (d.success) {
            showToast('success', isPinned ? 'Status di-unpin' : 'Status di-pin');
            setTimeout(() => window.location.reload(), 400);
        } else {
            showToast('error', d.message || 'Gagal mengubah pin');
        }
    }).catch(() => showToast('error', 'Terjadi kesalahan'));
}

function confirmDelete(id, title, number) {
    deleteTargetId = id;
    document.getElementById('deleteTargetTitle').textContent = title;
    document.getElementById('deleteTargetNumber').textContent = number;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    deleteTargetId = null;
}

function executeDelete() {
    if (!deleteTargetId) return;
    fetch(`/admin/status-board/${deleteTargetId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => {
        closeDeleteModal();
        if (d.success) {
            showToast('success', 'Status berhasil dihapus');
            setTimeout(() => window.location.reload(), 400);
        } else {
            showToast('error', d.message || 'Gagal menghapus');
        }
    }).catch(() => {
        closeDeleteModal();
        showToast('error', 'Terjadi kesalahan');
    });
}
</script>

<style>
:root {
    --sb-primary: #667eea;
    --sb-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --sb-border: #e5e7eb;
    --sb-bg: #f8f9fa;
}

.sb-page { display: flex; flex-direction: column; gap: 1.25rem; }

/* â-€â-€ Hero Header â-€â-€ */
.sb-hero {
    background: var(--sb-gradient);
    border-radius: 16px;
    padding: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    box-shadow: 0 4px 20px rgba(102,126,234,.25);
}
.sb-hero-left { display: flex; align-items: center; gap: 1.25rem; }
.sb-hero-icon { width: 60px; height: 60px; background: rgba(255,255,255,.2); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; }
.sb-hero-title { font-size: 1.875rem; font-weight: 700; color: white; margin: 0 0 .375rem; }
.sb-hero-sub   { color: rgba(255,255,255,.85); font-size: 1rem; margin: 0; }
.sb-btn-create { display: inline-flex; align-items: center; gap: .5rem; padding: .875rem 1.75rem; background: white; color: #667eea; text-decoration: none; border-radius: 12px; font-weight: 600; transition: all .3s; box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.sb-btn-create:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.15); text-decoration: none; color: #667eea; }

/* â-€â-€ Stat Cards â-€â-€ */
.sb-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.sb-stat  { background: white; border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,.06); border-left: 4px solid; transition: all .3s; }
.sb-stat:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.1); }
.sb-stat-ico { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
.sb-stat-num { font-size: 1.75rem; font-weight: 700; color: #1f2937; line-height: 1; }
.sb-stat-lbl { color: #6b7280; font-size: .813rem; font-weight: 500; }
.sb-stat-primary { border-left-color: #667eea; } .sb-stat-primary .sb-stat-ico { background: rgba(102,126,234,.1); color: #667eea; }
.sb-stat-warning { border-left-color: #f59e0b; } .sb-stat-warning .sb-stat-ico { background: rgba(245,158,11,.1); color: #f59e0b; }
.sb-stat-success { border-left-color: #10b981; } .sb-stat-success .sb-stat-ico { background: rgba(16,185,129,.1); color: #10b981; }
.sb-stat-danger  { border-left-color: #ef4444; } .sb-stat-danger  .sb-stat-ico { background: rgba(239,68,68,.1); color: #ef4444; }

/* â-€â-€ Filters Card â-€â-€ */
.sb-filters { background: white; border-radius: 14px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
.sb-search-wrap { position: relative; margin-bottom: 1.25rem; }
.sb-search-ico  { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 1.25rem; }
.sb-search-input { width: 100%; padding: .875rem 1rem .875rem 3rem; border: 2px solid var(--sb-border); border-radius: 10px; font-size: .938rem; transition: all .3s; }
.sb-search-input:focus { outline: none; border-color: var(--sb-primary); box-shadow: 0 0 0 3px rgba(102,126,234,.1); }
.sb-filters-row { display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end; }
.sb-filter-item { flex: 1; min-width: 160px; }
.sb-filter-item label { display: block; font-size: .875rem; font-weight: 600; color: #374151; margin-bottom: .5rem; }
.sb-select { width: 100%; padding: .75rem 1rem; border: 2px solid var(--sb-border); border-radius: 10px; font-size: .938rem; background: white; color: #374151; cursor: pointer; transition: all .3s; }
.sb-select:focus { outline: none; border-color: var(--sb-primary); box-shadow: 0 0 0 3px rgba(102,126,234,.1); }
.sb-btn-reset { background: #f3f4f6; border: 1px solid #d1d5db; color: #374151; padding: .75rem 1.5rem; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: .5rem; transition: all .3s; white-space: nowrap; }
.sb-btn-reset:hover { background: #e5e7eb; transform: translateY(-2px); }

/* â-€â-€ Table Card â-€â-€ */
.sb-table-card { background: white; border-radius: 14px; box-shadow: 0 2px 8px rgba(0,0,0,.06); overflow: hidden; }
.sb-table-head { padding: 1.5rem; border-bottom: 1px solid var(--sb-border); display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; justify-content: space-between; }
.sb-table-head h3 { margin: 0; font-size: 1.25rem; font-weight: 700; color: #1f2937; display: flex; align-items: center; gap: .5rem; }
.sb-count-badge { background: #e0e7ff; color: #4338ca; font-size: .75rem; font-weight: 600; padding: .25rem .75rem; border-radius: 9999px; }
.sb-table-responsive { overflow-x: auto; }
.sb-table { width: 100%; border-collapse: collapse; }
.sb-table thead { background: #f9fafb; }
.sb-table th { text-align: left; padding: 1rem; font-size: .875rem; font-weight: 600; color: #6b7280; border-bottom: 2px solid var(--sb-border); white-space: nowrap; }
.sb-table tbody tr { transition: background .2s; }
.sb-table tbody tr:hover { background: #f9fafb; }
.sb-table td { padding: 1rem; border-bottom: 1px solid var(--sb-border); vertical-align: middle; }

/* â-€â-€ Table Cell Styles â-€â-€ */
.sb-incident-id { display: flex; align-items: center; gap: .375rem; font-weight: 600; color: #667eea; font-size: .875rem; }
.sb-pinned-tag  { display: inline-flex; align-items: center; gap: .25rem; font-size: .72rem; font-weight: 600; padding: .2rem .5rem; background: rgba(102,126,234,.1); color: #667eea; border-radius: 5px; margin-top: .25rem; }
.sb-title-cell   { font-weight: 500; color: #4b5563; font-size: .938rem; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sb-date-cell    { display: flex; flex-direction: column; gap: .2rem; }
.sb-date-main    { font-weight: 600; color: #374151; font-size: .875rem; }
.sb-date-time    { font-size: .75rem; color: #6b7280; }

.sb-actions-cell { display: flex; justify-content: center; gap: .375rem; }
.sb-action-btn { background: #f3f4f6; border: none; padding: .5rem; border-radius: 8px; cursor: pointer; font-size: 1.125rem; transition: all .2s; display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; text-decoration: none; }
.sb-action-btn:hover { transform: translateY(-2px); }
.sb-action-view  { color: #3b82f6; } .sb-action-view:hover  { background: #dbeafe; }
.sb-action-vis   { color: #8b5cf6; } .sb-action-vis:hover   { background: #ede9fe; }
.sb-action-pin   { color: #667eea; } .sb-action-pin:hover   { background: #e0e7ff; }
.sb-action-del   { color: #ef4444; } .sb-action-del:hover   { background: #fee2e2; }

/* â-€â-€ Tags â-€â-€ */
.sb-tag { display: inline-flex; align-items: center; gap: .25rem; font-size: .75rem; font-weight: 600; padding: .375rem .75rem; border-radius: 9999px; white-space: nowrap; }
.sb-cat-power    { background: #fef3c7; color: #92400e; }
.sb-cat-technical { background: #dbeafe; color: #1e40af; }
.sb-cat-facility  { background: #e0e7ff; color: #4338ca; }
.sb-cat-network   { background: #d1fae5; color: #065f46; }
.sb-cat-other     { background: #f3f4f6; color: #4b5563; }
.sb-sev-critical  { background: #fee2e2; color: #991b1b; }
.sb-sev-high      { background: #fef3c7; color: #92400e; }
.sb-sev-medium    { background: #dbeafe; color: #1e40af; }
.sb-sev-low       { background: #f3f4f6; color: #4b5563; }
.sb-status-investigating { background: #fef3c7; color: #92400e; }
.sb-status-identified    { background: #dbeafe; color: #1e40af; }
.sb-status-monitoring    { background: #e0e7ff; color: #4338ca; }
.sb-status-resolved      { background: #d1fae5; color: #065f46; }

/* â-€â-€ Empty State â-€â-€ */
.sb-empty { text-align: center; padding: 3rem 1.5rem; color: #9ca3af; }
.sb-empty i { font-size: 3rem; margin-bottom: 1rem; color: #d1d5db; }
.sb-empty h4 { margin: 0 0 .5rem; font-size: 1.125rem; color: #6b7280; }

/* â-€â-€ Pagination â-€â-€ */
.sb-pagination { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-top: 1px solid var(--sb-border); flex-wrap: wrap; gap: 1rem; }
.sb-page-info  { font-size: .875rem; color: #6b7280; }
.sb-page-btns  { display: flex; align-items: center; gap: .75rem; }
.sb-page-btn   { background: #f3f4f6; border: 1px solid var(--sb-border); padding: .5rem; border-radius: 8px; cursor: pointer; font-size: 1.125rem; transition: all .2s; display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; color: #374151; text-decoration: none; }
.sb-page-btn:hover:not(.disabled) { background: #e5e7eb; transform: translateY(-2px); }
.sb-page-btn.disabled { opacity: .4; cursor: not-allowed; pointer-events: none; }
.sb-page-indicator { font-size: .875rem; color: #374151; font-weight: 600; padding: 0 .5rem; }

/* â-€â-€ Modal â-€â-€ */
.sb-modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,.5); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 1rem; opacity: 0; pointer-events: none; transition: opacity .3s; }
.sb-modal-backdrop.show { opacity: 1; pointer-events: all; }
.sb-modal { background: white; border-radius: 16px; width: 90%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,.1); overflow: hidden; transform: translateY(-40px); transition: transform .3s; }
.sb-modal-backdrop.show .sb-modal { transform: translateY(0); }
.sb-modal-head { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; background: linear-gradient(135deg,#ef4444,#dc2626); }
.sb-modal-head h3 { margin: 0; font-size: 1.25rem; font-weight: 700; color: white; display: flex; align-items: center; gap: .5rem; }
.sb-modal-close { background: rgba(255,255,255,.2); border: none; font-size: 1.5rem; color: white; cursor: pointer; border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; transition: background .2s; }
.sb-modal-close:hover { background: rgba(255,255,255,.3); }
.sb-modal-body { padding: 1.5rem; }
.sb-modal-foot { display: flex; justify-content: flex-end; gap: .75rem; padding: 1.5rem; background: #f9fafb; border-top: 1px solid var(--sb-border); }
.sb-delete-msg  { font-size: 1rem; font-weight: 600; color: #991b1b; margin-bottom: 1rem; }
.sb-delete-info { background: #fef2f2; padding: .75rem 1rem; border-radius: 8px; font-size: .938rem; color: #374151; margin-bottom: 1rem; border-left: 3px solid #ef4444; }
.sb-delete-warn { font-size: .875rem; color: #6b7280; font-style: italic; }
.sb-btn-cancel  { background: white; border: 2px solid var(--sb-border); color: #374151; padding: .75rem 1.5rem; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all .2s; }
.sb-btn-cancel:hover { background: #f9fafb; }
.sb-btn-danger  { background: #ef4444; color: white; border: none; padding: .75rem 1.5rem; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: .5rem; transition: all .2s; }
.sb-btn-danger:hover { background: #dc2626; transform: translateY(-2px); }

/* â-€â-€ Toast â-€â-€ */
.sb-toast { position: fixed; top: 1.5rem; right: 1.5rem; z-index: 99999; min-width: 260px; padding: .875rem 1.125rem; border-radius: 12px; display: flex; align-items: center; gap: .75rem; font-size: .9rem; font-weight: 600; box-shadow: 0 8px 24px rgba(0,0,0,.15); transform: translateX(120%); transition: transform .4s cubic-bezier(.34,1.56,.64,1); }
.sb-toast.show { transform: translateX(0); }
.sb-toast.success { background: #f0fdf4; color: #065f46; border: 1px solid #bbf7d0; }
.sb-toast.error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.sb-toast i { font-size: 1.1rem; }

/* Responsive */
@media (max-width: 768px) {
    .sb-hero { flex-direction: column; align-items: flex-start; }
    .sb-btn-create { width: 100%; justify-content: center; }
    .sb-filters-row { flex-direction: column; }
    .sb-filter-item { width: 100%; }
    .sb-btn-reset { width: 100%; justify-content: center; }
    .sb-table { min-width: 860px; }
    .sb-pagination { flex-direction: column; align-items: flex-start; }
}
</style>