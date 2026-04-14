@extends('layouts.app')

@section('title', 'Manajemen Vendor')
@section('page_title', 'Manajemen Vendor')
@section('breadcrumb', 'Home / Vendor')



@section('content')
<div class="vendor-wrap">

    {{-- ‚-Ä‚-Ä FLASH MESSAGES ‚-Ä‚-Ä --}}
    @if(session('success'))
        <div class="alert-success-custom"><i class='bx bx-check-circle'></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-danger-custom"><i class='bx bx-error-circle'></i> {{ session('error') }}</div>
    @endif

    {{-- ‚-Ä‚-Ä HERO ‚-Ä‚-Ä --}}
    <div class="vendor-hero">
        <div>
            <h4>Manajemen Vendor</h4>
            <p>Kelola dan pantau performa seluruh vendor yang terdaftar di sistem.</p>
        </div>
        <button class="btn-add-vendor" onclick="openAddModal()">
            <i class='bx bx-plus'></i> Tambah Vendor
        </button>
    </div>

    {{-- ‚-Ä‚-Ä STAT CARDS ‚-Ä‚-Ä --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon--primary"><i class='bx bx-group'></i></div>
            <div><span>Total Vendor</span><strong>{{ $totalVendors }}</strong></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--success"><i class='bx bx-check-circle'></i></div>
            <div><span>Vendor Aktif</span><strong>{{ $activeVendors }}</strong></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--warning"><i class='bx bx-pause-circle'></i></div>
            <div><span>Tidak Aktif</span><strong>{{ $inactiveVendors }}</strong></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--info"><i class='bx bx-file'></i></div>
            <div><span>Total Tiket</span><strong>{{ $totalTickets }}</strong></div>
        </div>
    </div>

    {{-- ‚-Ä‚-Ä FILTER BAR ‚-Ä‚-Ä --}}
    <form method="GET" action="{{ route('admin.vendors.index') }}" class="filter-bar">
        <div class="search-wrap">
            <i class='bx bx-search'></i>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari nama, email, atau perusahaan-¶">
        </div>
        <select name="is_active" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
        </select>
        <button type="submit" class="btn-primary-sm" style="padding:.65rem 1.1rem;">
            <i class='bx bx-search'></i> Cari
        </button>
        @if(request()->hasAny(['search','is_active']))
            <a href="{{ route('admin.vendors.index') }}" class="btn-cancel" style="padding:.65rem 1rem;">Reset</a>
        @endif
    </form>

    {{-- ‚-Ä‚-Ä VENDOR GRID ‚-Ä‚-Ä --}}
    @if($vendors->isEmpty())
        <div class="empty-state">
            <i class='bx bx-user-x'></i>
            <strong>Tidak Ada Vendor</strong>
            <p style="margin:.5rem 0 0; font-size:.9rem;">Belum ada vendor yang sesuai filter. Tambahkan vendor baru untuk memulai.</p>
        </div>
    @else
        <div class="vendor-grid">
            @foreach($vendors as $vendor)
            @php
                $initials = strtoupper(substr($vendor->name, 0, 2));
                $perf     = $vendor->performance ?? null;
            @endphp
            <div class="vendor-card">
                <div class="vendor-card-head">
                    <div style="display:flex;gap:.875rem;align-items:flex-start;flex:1;min-width:0;">
                        <div class="vendor-avatar">{{ $initials }}</div>
                        <div class="vendor-info">
                            <h6>{{ $vendor->name }}</h6>
                            <small>{{ $vendor->email }}</small>
                        </div>
                    </div>
                    <span class="badge-role {{ $vendor->is_active ? 'badge-active' : 'badge-inactive' }}">
                        <i class='bx {{ $vendor->is_active ? "bx-check-circle" : "bx-x-circle" }}'></i>
                        {{ $vendor->is_active ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </div>

                <div class="vendor-meta">
                    <div class="meta-row">
                        <i class='bx bx-buildings'></i>
                        <span>{{ $vendor->company_name ?: 'Tidak ada perusahaan' }}</span>
                    </div>
                    @if($vendor->company_phone)
                    <div class="meta-row">
                        <i class='bx bx-phone'></i>
                        <span>{{ $vendor->company_phone }}</span>
                    </div>
                    @endif
                    @if($vendor->specialization)
                    <div class="meta-row">
                        <i class='bx bx-briefcase'></i>
                        <span>{{ $vendor->specialization }}</span>
                    </div>
                    @endif
                </div>

                @if($perf)
                <div class="perf-row">
                    <div class="perf-item">
                        <span>Tiket</span>
                        <strong>{{ $perf['total_tickets'] }}</strong>
                    </div>
                    <div class="perf-item">
                        <span>Selesai</span>
                        <strong style="color:#16a34a;">{{ $perf['resolved_tickets'] }}</strong>
                    </div>
                    <div class="perf-item">
                        <span>SLA</span>
                        <strong style="color:var(--primary);">{{ $perf['sla_compliance_rate'] }}%</strong>
                    </div>
                </div>
                @endif

                <div class="vendor-actions">
                    <button class="btn-detail" onclick="openDetailModal({{ $vendor->id }})">
                        <i class='bx bx-show'></i> Lihat Detail
                    </button>
                    <button class="btn-icon-sm" title="Edit" onclick="openEditModal({{ $vendor->id }})">
                        <i class='bx bx-edit'></i>
                    </button>
                    <form method="POST" action="{{ route('admin.users.toggle-status', $vendor->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-icon-sm"
                            title="{{ $vendor->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                            onclick="return confirm('{{ $vendor->is_active ? 'Nonaktifkan' : 'Aktifkan' }} vendor ini?')">
                            <i class='bx {{ $vendor->is_active ? "bx-block" : "bx-check" }}'></i>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.destroy', $vendor->id) }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-icon-sm btn-icon-sm--danger" title="Hapus"
                            onclick="return confirm('Hapus vendor {{ addslashes($vendor->name) }}? Tindakan ini tidak dapat dibatalkan.')">
                            <i class='bx bx-trash'></i>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        {{-- PAGINATION --}}
        @if($vendors->hasPages())
        <div class="page-wrap">
            {{ $vendors->appends(request()->query())->links() }}
        </div>
        @endif
    @endif

</div>

{{-- ‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê
     ‚-Ä‚-Ä ADD / EDIT VENDOR MODAL (Custom)
‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê --}}
<div class="vmodal-overlay" id="vendorModalOverlay" onclick="handleOverlayClick(event, 'vendorModalOverlay')">
    <div class="vmodal-box vmodal-box--lg" id="vendorModalBox">

        {{-- Header --}}
        <div class="vmodal-header">
            <div class="vmodal-header-left">
                <div class="vmodal-header-icon" id="addEditModalIcon">
                    <i class='bx bx-user-plus'></i>
                </div>
                <div>
                    <p class="vmodal-title" id="vendorModalTitle">Tambah Vendor Baru</p>
                    <p class="vmodal-subtitle" id="vendorModalSubtitle">Isi data vendor untuk menambahkan ke sistem</p>
                </div>
            </div>
            <button class="vmodal-close" onclick="closeModal('vendorModalOverlay')">
                <i class='bx bx-x'></i>
            </button>
        </div>

        {{-- Form --}}
        <form method="POST" id="vendorForm">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="vmodal-body">

                {{-- Section: Data Pribadi --}}
                <p class="vmodal-section-label">Data Pribadi</p>
                <div class="vform-grid-2">
                    <div class="vform-group" style="grid-column: span 2;">
                        <label class="vform-label">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" name="name" id="inp_name" class="vform-input"
                            placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="vform-group">
                        <label class="vform-label">Email <span class="req">*</span></label>
                        <input type="email" name="email" id="inp_email" class="vform-input"
                            placeholder="email@domain.com" required>
                    </div>
                    <div class="vform-group">
                        <label class="vform-label">No. Telepon</label>
                        <input type="text" name="phone" id="inp_phone" class="vform-input"
                            placeholder="08xx-xxxx-xxxx">
                    </div>
                    <div class="vform-group" id="passwordGroup">
                        <label class="vform-label">Password <span class="req">*</span></label>
                        <input type="password" name="password" id="inp_password" class="vform-input"
                            placeholder="Min. 8 karakter" minlength="8">
                    </div>
                </div>

                <div class="vmodal-divider"></div>

                {{-- Section: Data Perusahaan --}}
                <p class="vmodal-section-label">Data Perusahaan</p>
                <div class="vform-grid-2">
                    <div class="vform-group">
                        <label class="vform-label">Nama Perusahaan</label>
                        <input type="text" name="company_name" id="inp_company_name" class="vform-input"
                            placeholder="PT. Nama Perusahaan">
                    </div>
                    <div class="vform-group">
                        <label class="vform-label">No. Telepon Perusahaan</label>
                        <input type="text" name="company_phone" id="inp_company_phone" class="vform-input"
                            placeholder="021-xxxx-xxxx">
                    </div>
                    <div class="vform-group" style="grid-column: span 2;">
                        <label class="vform-label">Alamat Perusahaan</label>
                        <textarea name="company_address" id="inp_company_address" class="vform-textarea"
                            placeholder="Jl. Alamat lengkap perusahaan..."></textarea>
                    </div>
                    <div class="vform-group" style="grid-column: span 2;">
                        <label class="vform-label">Spesialisasi</label>
                        <input type="text" name="specialization" id="inp_specialization" class="vform-input"
                            placeholder="Contoh: Audio System, Lighting, Stage Setup">
                        <p class="vform-hint">Pisahkan dengan koma untuk beberapa spesialisasi</p>
                    </div>
                </div>

                <input type="hidden" name="role" value="vendor">
            </div>

            <div class="vmodal-footer">
                <button type="button" class="vbtn-cancel" onclick="closeModal('vendorModalOverlay')">Batal</button>
                <button type="submit" class="vbtn-primary" id="submitBtn">
                    <i class='bx bx-save'></i> Simpan Vendor
                </button>
            </div>
        </form>

    </div>
</div>

{{-- ‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê
     ‚-Ä‚-Ä DETAIL VENDOR MODAL (Custom)
‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê --}}
<div class="vmodal-overlay" id="detailModalOverlay" onclick="handleOverlayClick(event, 'detailModalOverlay')">
    <div class="vmodal-box vmodal-box--xl" id="detailModalBox">

        {{-- Header --}}
        <div class="vmodal-header">
            <div class="vmodal-header-left">
                <div class="vmodal-header-icon" style="background:rgba(16,185,129,.1);color:#059669;">
                    <i class='bx bx-id-card'></i>
                </div>
                <div>
                    <p class="vmodal-title">Detail Vendor</p>
                    <p class="vmodal-subtitle">Informasi lengkap dan performa vendor</p>
                </div>
            </div>
            <button class="vmodal-close" onclick="closeModal('detailModalOverlay')">
                <i class='bx bx-x'></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="vmodal-body" id="detailBody">
            <div style="text-align:center;padding:3rem;color:#94a3b8;">
                <div style="width:40px;height:40px;border:3px solid #e2e8f0;border-top-color:#6366f1;border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 1rem;"></div>
                <p style="margin:0;font-size:.9rem;">Memuat detail vendor-¶</p>
            </div>
        </div>

    </div>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

{{-- Vendor data JSON for JS --}}
<script>
const VENDORS_DATA = @json($vendorsJson);
const ROUTES = {
    store:         "{{ route('admin.users.store') }}",
    update_base:   "{{ url('admin/users') }}",
    vendors_index: "{{ route('admin.vendors.index') }}",
};
</script>
@endsection

@push('scripts')
<script>
/* ‚-Ä‚-Ä Overlay helpers ‚-Ä‚-Ä */
function openModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = 'flex';
    requestAnimationFrame(() => el.classList.add('active'));
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('active');
    setTimeout(() => {
        el.style.display = 'none';
        document.body.style.overflow = '';
    }, 200);
}

function handleOverlayClick(e, id) {
    if (e.target.id === id) closeModal(id);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('vendorModalOverlay');
        closeModal('detailModalOverlay');
    }
});

/* ‚-Ä‚-Ä Add Modal ‚-Ä‚-Ä */
function openAddModal() {
    document.getElementById('vendorModalTitle').textContent   = 'Tambah Vendor Baru';
    document.getElementById('vendorModalSubtitle').textContent = 'Isi data vendor untuk menambahkan ke sistem';
    document.getElementById('addEditModalIcon').innerHTML     = '<i class="bx bx-user-plus"></i>';
    document.getElementById('vendorForm').action              = ROUTES.store;
    document.getElementById('formMethod').value               = 'POST';
    document.getElementById('passwordGroup').style.display    = '';
    document.getElementById('inp_password').required          = true;
    document.getElementById('submitBtn').innerHTML            = '<i class="bx bx-save"></i> Simpan Vendor';
    clearForm();
    openModal('vendorModalOverlay');
}

/* ‚-Ä‚-Ä Edit Modal ‚-Ä‚-Ä */
function openEditModal(id) {
    const v = VENDORS_DATA.find(x => x.id == id);
    if (!v) return;

    document.getElementById('vendorModalTitle').textContent   = 'Edit Vendor';
    document.getElementById('vendorModalSubtitle').textContent = 'Perbarui informasi vendor';
    document.getElementById('addEditModalIcon').innerHTML     = '<i class="bx bx-edit"></i>';
    document.getElementById('vendorForm').action              = ROUTES.update_base + '/' + id;
    document.getElementById('formMethod').value               = 'PUT';
    document.getElementById('passwordGroup').style.display    = 'none';
    document.getElementById('inp_password').required          = false;
    document.getElementById('submitBtn').innerHTML            = '<i class="bx bx-save"></i> Simpan Perubahan';

    document.getElementById('inp_name').value            = v.name            || '';
    document.getElementById('inp_email').value           = v.email           || '';
    document.getElementById('inp_phone').value           = v.phone           || '';
    document.getElementById('inp_company_name').value    = v.company_name    || '';
    document.getElementById('inp_company_phone').value   = v.company_phone   || '';
    document.getElementById('inp_company_address').value = v.company_address || '';
    document.getElementById('inp_specialization').value  = v.specialization  || '';

    openModal('vendorModalOverlay');
}

/* ‚-Ä‚-Ä Clear Form ‚-Ä‚-Ä */
function clearForm() {
    ['name','email','phone','password','company_name','company_phone','company_address','specialization']
        .forEach(f => {
            const el = document.getElementById('inp_' + f);
            if (el) el.value = '';
        });
}

/* ‚-Ä‚-Ä Detail Modal ‚-Ä‚-Ä */
function openDetailModal(id) {
    const fallbackVendor = VENDORS_DATA.find(x => x.id == id);
    if (!fallbackVendor) return;

    document.getElementById('detailBody').innerHTML = `
        <div style="text-align:center;padding:3rem;color:#94a3b8;">
            <div style="width:40px;height:40px;border:3px solid #e2e8f0;border-top-color:#6366f1;border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 1rem;"></div>
            <p style="margin:0;font-size:.9rem;">Memuat detail vendor...</p>
        </div>
    `;
    openModal('detailModalOverlay');

    fetch(`/admin/vendors/${id}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => {
        if (!r.ok) throw new Error('Gagal memuat detail vendor');
        return r.json();
    })
    .then(res => {
        const v = res.vendor || fallbackVendor;
        const perf = res.performance || v.performance || {};
        const recent = perf.recentTickets || perf.recent_tickets || [];
        const doneTickets = recent.filter(t => ['resolved', 'closed'].includes(String(t.status)));
        const initials = (v.name || '').substring(0, 2).toUpperCase();

        const statusBadge = v.is_active
            ? `<span class="badge-role badge-active" style="font-size:.78rem;"><i class="bx bx-check-circle"></i> Aktif</span>`
            : `<span class="badge-role badge-inactive" style="font-size:.78rem;"><i class="bx bx-x-circle"></i> Tidak Aktif</span>`;

        const doneHtml = doneTickets.length
            ? doneTickets.map(t => `
                <div style="padding:.8rem;border:1px solid #eef2f7;border-radius:12px;margin-bottom:.6rem;">
                    <div style="display:flex;justify-content:space-between;gap:.75rem;">
                        <div>
                            <strong style="font-size:.85rem;">${escapeHtml(t.ticket_number || '-')}</strong>
                            <div style="font-size:.82rem;color:#64748b;">${escapeHtml(truncateText(t.title || '-', 52))}</div>
                        </div>
                        <small style="color:#94a3b8;white-space:nowrap;">${timeAgo(t.created_at)}</small>
                    </div>
                    <div style="margin-top:.4rem;display:flex;gap:.35rem;flex-wrap:wrap;">
                        <span class="badge-role badge-active" style="font-size:.68rem;">${formatStatus(t.status)}</span>
                        <span class="badge-role" style="font-size:.68rem;background:#eef2ff;color:#4338ca;">${formatPriority(t.priority)}</span>
                    </div>
                </div>
            `).join('')
            : `<div style="padding:1rem;background:#f8fafc;border-radius:12px;color:#94a3b8;font-size:.85rem;">Belum ada tiket selesai</div>`;

        document.getElementById('detailBody').innerHTML = `
            <div class="detail-layout">
                <div>
                    <div class="detail-vendor-card">
                        <div class="detail-avatar-lg">${initials}</div>
                        <div style="flex:1;min-width:0;">
                            <p class="detail-vendor-name">${escapeHtml(v.name || '-')}</p>
                            <p class="detail-vendor-email">${escapeHtml(v.email || '-')}</p>
                            ${statusBadge}
                        </div>
                    </div>

                    <p class="vmodal-section-label">Informasi Perusahaan</p>
                    <div class="detail-info-grid">
                        <div class="detail-info-item"><span class="lbl">Nama Perusahaan</span><span class="val">${escapeHtml(v.company_name || '-')}</span></div>
                        <div class="detail-info-item"><span class="lbl">Telepon Perusahaan</span><span class="val">${escapeHtml(v.company_phone || '-')}</span></div>
                        <div class="detail-info-item"><span class="lbl">Alamat</span><span class="val">${escapeHtml(v.company_address || '-')}</span></div>
                        <div class="detail-info-item"><span class="lbl">Spesialisasi</span><span class="val">${escapeHtml(v.specialization || '-')}</span></div>
                    </div>

                    <div class="vmodal-divider"></div>
                    <p class="vmodal-section-label">Ringkasan Performa</p>
                    <div class="perf-stat-grid">
                        <div class="perf-stat-box"><span class="num">${perf.total_tickets ?? 0}</span><span class="lbl">Total Tiket</span></div>
                        <div class="perf-stat-box"><span class="num num--green">${perf.resolved_tickets ?? 0}</span><span class="lbl">Terselesaikan</span></div>
                        <div class="perf-stat-box"><span class="num num--purple">${perf.sla_compliance_rate ?? 0}%</span><span class="lbl">SLA Rate</span></div>
                        <div class="perf-stat-box"><span class="num">${perf.pending_tickets ?? 0}</span><span class="lbl">Pending</span></div>
                    </div>
                </div>

                <aside class="detail-side">
                    <p class="detail-side-title">Tiket Selesai</p>
                    <div class="done-tickets-list">${doneHtml}</div>
                </aside>
            </div>
        `;
        document.getElementById('detailBody').scrollTop = 0;
    })
    .catch(() => {
        document.getElementById('detailBody').innerHTML = `
            <div style="padding:1.25rem;border:1px solid #fee2e2;border-radius:12px;background:#fef2f2;color:#b91c1c;">
                Gagal memuat detail vendor. Silakan coba lagi.
            </div>
        `;
    });
}

function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
}

function truncateText(text, max) {
    const t = String(text || '');
    return t.length > max ? `${t.slice(0, max)}-¶` : t;
}

function formatStatus(status) {
    const map = {
        new: 'Baru',
        baru: 'Baru',
        in_progress: 'Diproses',
        waiting_response: 'Menunggu Respons',
        resolved: 'Selesai',
        closed: 'Ditutup'
    };
    return map[status] || status || '-';
}

function formatPriority(priority) {
    const map = { low: 'Low', medium: 'Medium', high: 'High', urgent: 'Urgent' };
    return map[priority] || (priority || '-');
}

function timeAgo(dateStr) {
    if (!dateStr) return '-';
    const diffMin = Math.floor((Date.now() - new Date(dateStr).getTime()) / 60000);
    if (diffMin < 1) return 'Baru saja';
    if (diffMin < 60) return `${diffMin} menit lalu`;
    const h = Math.floor(diffMin / 60);
    if (h < 24) return `${h} jam lalu`;
    return `${Math.floor(h / 24)} hari lalu`;
}
</script>
@endpush

@push('styles')
<style>
/* ‚-Ä‚-Ä Google Font ‚-Ä‚-Ä */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root {
    --modal-radius: 24px;
    --input-radius: 14px;
}

.vendor-wrap { display: flex; flex-direction: column; gap: 1.5rem; }

/* ‚-Ä‚-Ä HERO ‚-Ä‚-Ä */
.vendor-hero {
    display: flex; justify-content: space-between; align-items: center;
    gap: 1rem; padding: 1.5rem 1.875rem;
    background: white; border: 1px solid var(--border);
    border-radius: 28px; box-shadow: var(--shadow-sm);
}
.vendor-hero h4 { margin: 0 0 .25rem; font-size: 1.375rem; font-weight: 800; color: var(--text); }
.vendor-hero p  { margin: 0; color: var(--text-muted); font-size: .9375rem; }

/* ‚-Ä‚-Ä ADD VENDOR BUTTON (match kategori style) ‚-Ä‚-Ä */
.btn-add-vendor {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.375rem;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.25s;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
    white-space: nowrap;
    flex-shrink: 0;
    text-decoration: none;
}
.btn-add-vendor:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(79, 70, 229, 0.45);
    color: white;
}

/* ‚-Ä‚-Ä STAT CARDS ‚-Ä‚-Ä */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0,1fr));
    gap: 1rem;
}
.stat-card {
    background: white; border: 1px solid var(--border);
    border-radius: 22px; padding: 1.25rem;
    display: flex; align-items: center; gap: 1rem;
    box-shadow: var(--shadow-sm); transition: all .25s;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
.stat-icon {
    width: 50px; height: 50px; border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.375rem; flex-shrink: 0;
}
.stat-icon--primary { background: rgba(79,70,229,.1); color: var(--primary); }
.stat-icon--success { background: rgba(34,197,94,.1);  color: #16a34a; }
.stat-icon--warning { background: rgba(249,115,22,.1); color: #c2410c; }
.stat-icon--info    { background: rgba(59,130,246,.1);  color: #1d4ed8; }
.stat-card span  { display: block; color: var(--text-muted); font-size: .85rem; font-weight: 700; }
.stat-card strong{ display: block; font-size: 1.875rem; font-weight: 800; color: var(--text); line-height: 1; }

/* ‚-Ä‚-Ä FILTER BAR ‚-Ä‚-Ä */
.filter-bar {
    background: white; border: 1px solid var(--border);
    border-radius: 22px; padding: 1.125rem 1.375rem;
    display: flex; gap: .875rem; align-items: center; flex-wrap: wrap;
    box-shadow: var(--shadow-sm);
}
.search-wrap { position: relative; flex: 1; min-width: 220px; }
.search-wrap i {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%); color: var(--text-light); font-size: 1.125rem;
}
.search-wrap input {
    width: 100%; padding: .7rem 1rem .7rem 2.5rem;
    border: 1px solid var(--border); border-radius: 14px;
    font-size: .9rem; color: var(--text); background: var(--bg);
    transition: border-color .2s;
}
.search-wrap input:focus { outline: none; border-color: var(--primary); background: white; }
.filter-select {
    padding: .7rem 1rem; border: 1px solid var(--border);
    border-radius: 14px; font-size: .9rem; color: var(--text);
    background: var(--bg); cursor: pointer; min-width: 160px;
}
.filter-select:focus { outline: none; border-color: var(--primary); }

/* ‚-Ä‚-Ä VENDOR GRID ‚-Ä‚-Ä */
.vendor-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 1rem;
}
.vendor-card {
    background: white; border: 1px solid var(--border);
    border-radius: 26px; padding: 1.375rem;
    display: flex; flex-direction: column; gap: .875rem;
    box-shadow: var(--shadow-sm); transition: all .25s;
}
.vendor-card:hover { transform: translateY(-4px); box-shadow: var(--shadow); }

.vendor-card-head {
    display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem;
}
.vendor-avatar {
    width: 58px; height: 58px; border-radius: 18px;
    background: var(--gradient); display: flex;
    align-items: center; justify-content: center;
    color: white; font-weight: 800; font-size: 1.25rem; flex-shrink: 0;
}
.vendor-info { flex: 1; min-width: 0; }
.vendor-info h6 { margin: 0; font-size: 1rem; font-weight: 800; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.vendor-info small { color: var(--text-muted); font-size: .8rem; }

.badge-active   { background: rgba(34,197,94,.12); color: #15803d; }
.badge-inactive { background: rgba(239,68,68,.1);  color: #b91c1c; }
.badge-role {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .35rem .75rem; border-radius: 999px;
    font-size: .75rem; font-weight: 800;
}

.vendor-meta { display: flex; flex-direction: column; gap: .5rem; }
.meta-row {
    display: flex; align-items: center; gap: .5rem;
    font-size: .85rem; color: var(--text-muted);
}
.meta-row i { font-size: 1rem; width: 18px; color: var(--text-light); }
.meta-row span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.perf-row {
    display: grid; grid-template-columns: repeat(3,1fr);
    gap: .5rem; padding: .875rem; border-radius: 16px;
    background: rgba(79,70,229,.04); border: 1px solid rgba(79,70,229,.08);
}
.perf-item { display: flex; flex-direction: column; align-items: center; gap: .15rem; }
.perf-item span   { font-size: .7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.perf-item strong { font-size: 1.15rem; font-weight: 800; color: var(--text); }

.vendor-actions { display: flex; gap: .5rem; }
.btn-detail {
    flex: 1; padding: .65rem; border-radius: 12px;
    border: 1.5px solid var(--primary); background: transparent;
    color: var(--primary); font-weight: 700; font-size: .875rem;
    cursor: pointer; transition: all .2s; text-decoration: none;
    display: flex; align-items: center; justify-content: center; gap: .35rem;
}
.btn-detail:hover { background: var(--primary); color: white; }
.btn-icon-sm {
    width: 38px; height: 38px; border-radius: 10px;
    border: 1px solid var(--border); background: white;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1rem; color: var(--text-muted);
    transition: all .2s; text-decoration: none;
}
.btn-icon-sm:hover { background: var(--bg); }
.btn-icon-sm--danger:hover { background: rgba(239,68,68,.1); color: #dc2626; border-color: rgba(239,68,68,.2); }

/* ‚-Ä‚-Ä PAGINATION ‚-Ä‚-Ä */
.page-wrap {
    display: flex; justify-content: center; gap: .5rem;
    flex-wrap: wrap;
}
.page-wrap .page-link {
    padding: .5rem .875rem; border-radius: 10px;
    border: 1px solid var(--border); font-weight: 600;
    color: var(--text-muted); font-size: .875rem;
    text-decoration: none; transition: all .2s;
}
.page-wrap .page-link:hover,
.page-wrap .page-item.active .page-link {
    background: var(--primary); color: white; border-color: var(--primary);
}

/* ‚-Ä‚-Ä EMPTY ‚-Ä‚-Ä */
.empty-state {
    text-align: center; padding: 3rem; color: var(--text-muted);
    border: 1.5px dashed rgba(148,163,184,.5); border-radius: 22px;
}
.empty-state i { font-size: 3rem; color: var(--text-light); display: block; margin-bottom: .75rem; }

/* ‚-Ä‚-Ä ALERT ‚-Ä‚-Ä */
.alert-success-custom {
    padding: .875rem 1.25rem; border-radius: 14px;
    background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.2);
    color: #15803d; font-weight: 600; display: flex; align-items: center; gap: .5rem;
}
.alert-danger-custom {
    padding: .875rem 1.25rem; border-radius: 14px;
    background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.2);
    color: #dc2626; font-weight: 600; display: flex; align-items: center; gap: .5rem;
}

/* ‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê
   ‚-Ä‚-Ä NEW REDESIGNED MODAL STYLES ‚-Ä‚-Ä
‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê‚ïê */
.vmodal-overlay {
    display: none;
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(15, 15, 35, 0.55);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
    padding: 1.25rem;
    animation: fadeInOverlay .2s ease;
}
.vmodal-overlay.active { display: flex; }

@keyframes fadeInOverlay {
    from { opacity: 0; }
    to   { opacity: 1; }
}
@keyframes slideUpModal {
    from { opacity: 0; transform: translateY(28px) scale(.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.vmodal-box {
    background: #fff;
    border-radius: var(--modal-radius);
    width: 100%; max-width: 560px;
    max-height: 90vh; overflow-y: auto;
    box-shadow: 0 32px 80px rgba(15,15,35,.22), 0 2px 12px rgba(15,15,35,.08);
    animation: slideUpModal .28s cubic-bezier(.22,.9,.36,1) both;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.vmodal-box--lg { max-width: 680px; }
.vmodal-box--xl {
    max-width: 1080px;
    max-height: 88vh;
    overflow: hidden;
}

/* scrollbar in modal */
.vmodal-box::-webkit-scrollbar { width: 6px; }
.vmodal-box::-webkit-scrollbar-track { background: transparent; }
.vmodal-box::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 999px; }

/* ‚-Ä‚-Ä Modal Header ‚-Ä‚-Ä */
.vmodal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.5rem 1.75rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    position: sticky; top: 0; background: #fff; z-index: 2;
    border-radius: var(--modal-radius) var(--modal-radius) 0 0;
}
.vmodal-header-left { display: flex; align-items: center; gap: .875rem; }
.vmodal-header-icon {
    width: 42px; height: 42px; border-radius: 13px;
    background: rgba(79,70,229,.1);
    display: flex; align-items: center; justify-content: center;
    color: var(--primary); font-size: 1.25rem; flex-shrink: 0;
}
.vmodal-title { font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0; }
.vmodal-subtitle { font-size: .8rem; color: #94a3b8; margin: 0; font-weight: 500; }
.vmodal-close {
    width: 36px; height: 36px; border-radius: 10px;
    border: 1px solid #e2e8f0; background: #f8fafc;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: #64748b; font-size: 1.1rem;
    transition: all .18s; flex-shrink: 0;
}
.vmodal-close:hover { background: #fee2e2; border-color: #fca5a5; color: #dc2626; }

/* ‚-Ä‚-Ä Modal Body ‚-Ä‚-Ä */
.vmodal-body { padding: 1.5rem 1.75rem; }
#detailBody {
    max-height: calc(88vh - 100px);
    overflow-y: auto;
    overflow-x: hidden;
}
#detailBody::-webkit-scrollbar { width: 8px; }
#detailBody::-webkit-scrollbar-thumb { background: #d7dce5; border-radius: 10px; }

/* ‚-Ä‚-Ä Section label inside modal ‚-Ä‚-Ä */
.vmodal-section-label {
    font-size: .7rem; font-weight: 800; letter-spacing: .08em;
    text-transform: uppercase; color: #94a3b8;
    margin: 0 0 .875rem;
}
.vmodal-divider {
    height: 1px; background: #f1f5f9;
    margin: 1.375rem 0;
}

/* ‚-Ä‚-Ä Form fields ‚-Ä‚-Ä */
.vform-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.vform-group { display: flex; flex-direction: column; gap: .4rem; }
.vform-label {
    font-size: .8rem; font-weight: 700; color: #475569;
    display: flex; align-items: center; gap: .3rem;
}
.vform-label .req { color: #ef4444; }
.vform-input, .vform-select, .vform-textarea {
    width: 100%; padding: .7rem 1rem;
    border: 1.5px solid #e2e8f0; border-radius: var(--input-radius);
    font-size: .9rem; color: #0f172a; background: #f8fafc;
    transition: all .18s; font-family: inherit;
    appearance: none; -webkit-appearance: none;
}
.vform-input::placeholder, .vform-textarea::placeholder { color: #cbd5e1; }
.vform-input:focus, .vform-select:focus, .vform-textarea:focus {
    outline: none; border-color: #818cf8;
    background: #fff; box-shadow: 0 0 0 3.5px rgba(99,102,241,.12);
}
.vform-textarea { resize: vertical; min-height: 76px; }
.vform-hint { font-size: .76rem; color: #94a3b8; margin: 0; }

/* ‚-Ä‚-Ä Modal Footer ‚-Ä‚-Ä */
.vmodal-footer {
    padding: 1.125rem 1.75rem;
    border-top: 1px solid #f1f5f9;
    display: flex; justify-content: flex-end; gap: .75rem;
    position: sticky; bottom: 0; background: #fff; z-index: 2;
    border-radius: 0 0 var(--modal-radius) var(--modal-radius);
}
.vbtn-cancel {
    padding: .65rem 1.25rem; background: #f8fafc;
    color: #64748b; border: 1.5px solid #e2e8f0;
    border-radius: 12px; font-weight: 700; font-size: .875rem;
    cursor: pointer; transition: all .18s; font-family: inherit;
}
.vbtn-cancel:hover { background: #fff; color: #0f172a; border-color: #cbd5e1; }
.vbtn-primary {
    padding: .65rem 1.5rem; background: var(--gradient, linear-gradient(135deg,#6366f1,#4f46e5));
    color: white; border: none; border-radius: 12px;
    font-weight: 700; font-size: .875rem; cursor: pointer;
    transition: all .18s; display: flex; align-items: center; gap: .45rem;
    font-family: inherit; box-shadow: 0 4px 14px rgba(79,70,229,.28);
}
.vbtn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(79,70,229,.36); }
.vbtn-primary:active { transform: translateY(0); }

/* ‚-Ä‚-Ä DETAIL MODAL specific ‚-Ä‚-Ä */
.detail-vendor-card {
    display: flex; align-items: center; gap: 1.125rem;
    padding: 1.25rem; border-radius: 18px;
    background: linear-gradient(135deg, rgba(99,102,241,.06) 0%, rgba(79,70,229,.03) 100%);
    border: 1px solid rgba(99,102,241,.12); margin-bottom: 1.375rem;
}
.detail-avatar-lg {
    width: 72px; height: 72px; border-radius: 20px;
    background: var(--gradient, linear-gradient(135deg,#6366f1,#4f46e5));
    display: flex; align-items: center; justify-content: center;
    color: white; font-weight: 800; font-size: 1.625rem; flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(79,70,229,.28);
}
.detail-vendor-name { font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0 0 .2rem; }
.detail-vendor-email { font-size: .875rem; color: #64748b; margin: 0 0 .4rem; }

.detail-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.detail-info-item { display: flex; flex-direction: column; gap: .2rem; }
.detail-info-item .lbl {
    font-size: .7rem; font-weight: 800; letter-spacing: .07em;
    text-transform: uppercase; color: #94a3b8;
}
.detail-info-item .val { font-size: .9rem; font-weight: 600; color: #0f172a; }

.perf-stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: .75rem; }
.perf-stat-box {
    background: #f8fafc; border: 1.5px solid #f1f5f9;
    border-radius: 16px; padding: 1rem .75rem;
    text-align: center; transition: all .2s;
}
.perf-stat-box:hover { border-color: rgba(99,102,241,.2); background: rgba(99,102,241,.04); }
.perf-stat-box .num {
    font-size: 1.625rem; font-weight: 800; line-height: 1;
    color: #0f172a; display: block; margin-bottom: .25rem;
}
.perf-stat-box .num--green { color: #16a34a; }
.perf-stat-box .num--purple { color: #4f46e5; }
.perf-stat-box .lbl { font-size: .72rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }

.detail-layout {
    display: grid;
    grid-template-columns: 1.6fr .9fr;
    gap: 1rem;
}
.detail-side {
    border: 1px solid #e8ecf3;
    border-radius: 14px;
    padding: .9rem;
    background: #fbfcfe;
}
.detail-side-title {
    margin: 0 0 .65rem;
    font-size: .78rem;
    font-weight: 800;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.done-tickets-list {
    max-height: 420px;
    overflow-y: auto;
    padding-right: .25rem;
}
.done-tickets-list::-webkit-scrollbar { width: 6px; }
.done-tickets-list::-webkit-scrollbar-thumb { background: #d8dde7; border-radius: 10px; }

/* ‚-Ä‚-Ä RESPONSIVE ‚-Ä‚-Ä */
@media (max-width: 1199px) {
    .stats-grid, .vendor-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
}
@media (max-width: 767px) {
    .stats-grid, .vendor-grid { grid-template-columns: 1fr; }
    .vendor-hero { flex-direction: column; align-items: flex-start; }
    .vform-grid-2, .detail-info-grid { grid-template-columns: 1fr; }
    .perf-stat-grid { grid-template-columns: repeat(2,1fr); }
    .vmodal-box { margin: .5rem; }
    .detail-layout { grid-template-columns: 1fr; }
    .done-tickets-list { max-height: 220px; }
}
</style>
@endpush





