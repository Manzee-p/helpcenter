@extends('layouts.app')

@section('title', 'Buat Status Baru')
@section('page_title', 'Buat Status Baru')
@section('breadcrumb', 'Home / Papan Status / Buat')

@push('styles')
<style>
:root {
    --sc-primary: #667eea;
    --sc-gradient: linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    --sc-border: #e5e7eb;
}

.sc-wrap { max-width: 960px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem; }

/* ── Back ── */
.sc-back { display: inline-flex; align-items: center; gap: .5rem; padding: .75rem 1.25rem; background: white; border: 2px solid var(--sc-border); border-radius: 10px; font-size: .875rem; font-weight: 600; color: #6b7280; cursor: pointer; transition: all .3s; text-decoration: none; }
.sc-back:hover { background: #f9fafb; border-color: var(--sc-primary); color: var(--sc-primary); transform: translateX(-4px); text-decoration: none; }

/* ── Header ── */
.sc-header { text-align: center; }
.sc-header h1 { font-size: 2rem; font-weight: 700; color: #2c3e50; margin: 0 0 .5rem; }
.sc-header p  { font-size: 1rem; color: #6c757d; margin: 0; }

/* ── Alert ── */
.sc-alert { display: flex; align-items: center; gap: .75rem; padding: 1rem 1.25rem; border-radius: 12px; font-size: .9375rem; transition: all .3s; }
.sc-alert.success { background: linear-gradient(135deg,#d4f4dd,#c3f0cf); color: #059669; border: 1px solid #6ee7b7; }
.sc-alert.error   { background: linear-gradient(135deg,#fee2e2,#fecaca); color: #dc2626; border: 1px solid #fca5a5; }
.sc-alert i { font-size: 1.25rem; }
.sc-alert-close { margin-left: auto; background: transparent; border: none; cursor: pointer; color: inherit; opacity: .7; font-size: 1.1rem; transition: opacity .2s; }
.sc-alert-close:hover { opacity: 1; }
.sc-alert-hide { display: none !important; }

/* ── Form container ── */
.sc-form-card { background: white; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.08); padding: 2rem; }

/* ── Section ── */
.sc-section { margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 2px solid #f3f4f6; }
.sc-section:last-of-type { border-bottom: none; margin-bottom: 0; }
.sc-section-head { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.5rem; }
.sc-section-head i { font-size: 1.75rem; color: var(--sc-primary); }
.sc-section-head h2 { font-size: 1.25rem; font-weight: 700; color: #2c3e50; margin: 0; }

/* ── Form row & group ── */
.sc-row  { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
.sc-row:last-of-type { margin-bottom: 0; }
.sc-group { display: flex; flex-direction: column; }
.sc-group.full-width { grid-column: 1/-1; }
.sc-label { display: flex; align-items: center; gap: .5rem; font-size: .9375rem; font-weight: 600; color: #374151; margin-bottom: .625rem; }
.sc-label i { font-size: 1.125rem; color: var(--sc-primary); }
.req { color: #ef4444; }

.sc-input, .sc-select, .sc-textarea {
    width: 100%; padding: .875rem 1rem; border: 2px solid var(--sc-border);
    border-radius: 10px; font-size: .9375rem; font-family: inherit;
    transition: all .3s; background: white;
}
.sc-input:focus, .sc-select:focus, .sc-textarea:focus {
    outline: none; border-color: var(--sc-primary);
    box-shadow: 0 0 0 4px rgba(102,126,234,.1);
}
.sc-input.is-invalid, .sc-select.is-invalid, .sc-textarea.is-invalid { border-color: #ef4444; }
.sc-textarea { resize: vertical; min-height: 140px; line-height: 1.6; }
.sc-hint  { display: block; font-size: .8125rem; color: #9ca3af; margin-top: .375rem; }
.sc-err   { display: flex; align-items: center; gap: .375rem; color: #ef4444; font-size: .8125rem; font-weight: 500; margin-top: .375rem; }
.sc-err i { font-size: 1rem; }
.sc-input-info { display: flex; justify-content: space-between; align-items: center; margin-top: .5rem; }
.sc-char-count { font-size: .8125rem; color: #9ca3af; font-weight: 500; }

/* ── Toggle ── */
.sc-toggle-group { display: flex; flex-direction: column; gap: 1rem; }
.sc-toggle-item  { display: flex; align-items: center; gap: .75rem; cursor: pointer; padding: .75rem 1rem; background: #f9fafb; border-radius: 10px; border: 2px solid var(--sc-border); transition: all .3s; }
.sc-toggle-item:hover { background: #f3f4f6; border-color: #d1d5db; }
.sc-toggle-item input { display: none; }
.sc-toggle-slider { position: relative; width: 48px; height: 26px; background: #d1d5db; border-radius: 26px; transition: all .3s; flex-shrink: 0; }
.sc-toggle-slider::before { content: ''; position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; background: white; border-radius: 50%; transition: all .3s; }
.sc-toggle-item input:checked + .sc-toggle-slider { background: var(--sc-gradient) !important; }
.sc-toggle-item input:checked + .sc-toggle-slider::before { transform: translateX(22px); }
.sc-toggle-label { font-size: .9375rem; font-weight: 500; color: #374151; display: flex; align-items: center; gap: .5rem; }

/* ── Preview ── */
.sc-preview-card { background: linear-gradient(135deg,#f9fafb,#f3f4f6); border: 2px solid var(--sc-border); border-radius: 16px; padding: 1.75rem; }
.sc-preview-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.sc-preview-badge { padding: .375rem .875rem; background: rgba(102,126,234,.1); color: #667eea; border-radius: 8px; font-size: .75rem; font-weight: 700; text-transform: uppercase; }
.sc-preview-sev   { padding: .375rem .875rem; border-radius: 8px; font-size: .75rem; font-weight: 700; }
.sc-pv-title { font-size: 1.5rem; font-weight: 700; color: #2c3e50; margin: 0 0 1rem; }
.sc-pv-meta  { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
.sc-pv-meta-item { display: flex; align-items: center; gap: .5rem; font-size: .875rem; color: #6c757d; font-weight: 500; }
.sc-pv-desc  { font-size: .9375rem; line-height: 1.6; color: #6c757d; margin: 0; white-space: pre-wrap; }
.sev-critical { background: #fee2e2; color: #991b1b; }
.sev-high     { background: #fef3c7; color: #92400e; }
.sev-medium   { background: #dbeafe; color: #1e40af; }
.sev-low      { background: #f3f4f6; color: #4b5563; }

/* ── Form Actions ── */
.sc-actions { display: flex; justify-content: flex-end; gap: 1rem; padding-top: 2rem; border-top: 2px solid #f3f4f6; flex-wrap: wrap; }
.sc-btn { display: inline-flex; align-items: center; gap: .5rem; padding: .875rem 1.75rem; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all .3s; border: none; font-size: .9375rem; }
.sc-btn:disabled { opacity: .6; cursor: not-allowed; transform: none !important; }
.sc-btn-secondary { background: white; color: #6b7280; border: 2px solid var(--sc-border); text-decoration: none; }
.sc-btn-secondary:hover:not(:disabled) { background: #f9fafb; border-color: #d1d5db; text-decoration: none; color: #6b7280; }
.sc-btn-primary { background: var(--sc-gradient); color: white; box-shadow: 0 4px 12px rgba(102,126,234,.3); }
.sc-btn-primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(102,126,234,.4); }

/* Spin */
.sc-spin { animation: scSpin 1s linear infinite; display: inline-block; }
@keyframes scSpin { to { transform: rotate(360deg); } }

/* Responsive */
@media (max-width: 768px) {
    .sc-form-card { padding: 1.5rem; }
    .sc-row { grid-template-columns: 1fr; }
    .sc-actions { flex-direction: column-reverse; }
    .sc-btn { width: 100%; justify-content: center; }
}
</style>
@endpush

@section('content')
<div class="sc-wrap">

    {{-- Back --}}
    <a href="{{ route('admin.status-board.index') }}" class="sc-back">
        <i class="bx bx-arrow-left"></i> Kembali
    </a>

    {{-- Header --}}
    <div class="sc-header">
        <h1>Buat Status Baru</h1>
        <p>Buat informasi status gangguan atau pemeliharaan sistem</p>
    </div>

    {{-- Alert --}}
    <div class="sc-alert sc-alert-hide" id="scAlert">
        <i class="bx" id="scAlertIcon"></i>
        <span id="scAlertMsg"></span>
        <button class="sc-alert-close" onclick="hideAlert()"><i class="bx bx-x"></i></button>
    </div>

    {{-- Form --}}
    <div class="sc-form-card">
        <form id="createForm" onsubmit="submitForm(event)" novalidate>
            @csrf

            {{-- ── Informasi Dasar ── --}}
            <div class="sc-section">
                <div class="sc-section-head">
                    <i class="bx bx-info-circle"></i>
                    <h2>Informasi Dasar</h2>
                </div>

                <div class="sc-row">
                    <div class="sc-group full-width">
                        <label class="sc-label"><i class="bx bx-heading"></i> Judul Status <span class="req">*</span></label>
                        <input type="text" class="sc-input" id="f_title" placeholder="Contoh: Gangguan Listrik di Hall A" maxlength="255" oninput="clearErr('e_title');updatePreview()" required/>
                        <span class="sc-err sc-alert-hide" id="e_title"><i class="bx bx-error-circle"></i></span>
                        <span class="sc-hint">Judul yang jelas dan deskriptif</span>
                    </div>
                </div>

                <div class="sc-row">
                    <div class="sc-group">
                        <label class="sc-label"><i class="bx bx-category"></i> Kategori <span class="req">*</span></label>
                        <select class="sc-select" id="f_category" onchange="clearErr('e_category');updatePreview()" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="power_outage">Gangguan Listrik</option>
                            <option value="technical_issue">Masalah Teknis</option>
                            <option value="facility_issue">Masalah Fasilitas</option>
                            <option value="network_issue">Gangguan Jaringan</option>
                            <option value="other">Lainnya</option>
                        </select>
                        <span class="sc-err sc-alert-hide" id="e_category"><i class="bx bx-error-circle"></i></span>
                    </div>
                    <div class="sc-group">
                        <label class="sc-label"><i class="bx bx-flag"></i> Tingkat Keparahan <span class="req">*</span></label>
                        <select class="sc-select" id="f_severity" onchange="clearErr('e_severity');updatePreview()" required>
                            <option value="">-- Pilih Tingkat --</option>
                            <option value="critical">🔴 Kritis</option>
                            <option value="high">🟠 Tinggi</option>
                            <option value="medium">🟡 Sedang</option>
                            <option value="low">🟢 Rendah</option>
                        </select>
                        <span class="sc-err sc-alert-hide" id="e_severity"><i class="bx bx-error-circle"></i></span>
                    </div>
                </div>

                <div class="sc-row">
                    <div class="sc-group">
                        <label class="sc-label"><i class="bx bx-map-pin"></i> Area Terdampak</label>
                        <input type="text" class="sc-input" id="f_area" placeholder="Contoh: Hall A, Ruang VIP" maxlength="255" oninput="updatePreview()"/>
                        <span class="sc-hint">Lokasi atau area yang terpengaruh (opsional)</span>
                    </div>
                    <div class="sc-group">
                        <label class="sc-label"><i class="bx bx-calendar"></i> Waktu Mulai <span class="req">*</span></label>
                        <input type="datetime-local" class="sc-input" id="f_started_at" onchange="clearErr('e_started_at')" required/>
                        <span class="sc-err sc-alert-hide" id="e_started_at"><i class="bx bx-error-circle"></i></span>
                    </div>
                </div>

                <div class="sc-row">
                    <div class="sc-group full-width">
                        <label class="sc-label"><i class="bx bx-detail"></i> Deskripsi Masalah <span class="req">*</span></label>
                        <textarea class="sc-textarea" id="f_description" rows="6" placeholder="Jelaskan masalah atau gangguan secara detail..." maxlength="2000"
                            oninput="clearErr('e_description');document.getElementById('descCharCount').textContent=this.value.length+'/2000';updatePreview()" required></textarea>
                        <div class="sc-input-info">
                            <span class="sc-err sc-alert-hide" id="e_description"><i class="bx bx-error-circle"></i></span>
                            <span class="sc-char-count" id="descCharCount">0/2000</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Pengaturan ── --}}
            <div class="sc-section">
                <div class="sc-section-head">
                    <i class="bx bx-cog"></i>
                    <h2>Pengaturan</h2>
                </div>
                <div class="sc-row">
                    <div class="sc-group">
                        <label class="sc-label"><i class="bx bx-user-check"></i> Ditugaskan Kepada</label>
                        <select class="sc-select" id="f_assigned_to">
                            <option value="">-- Tidak Ditugaskan --</option>
                            @foreach($admins ?? [] as $admin)
                                <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->role }})</option>
                            @endforeach
                        </select>
                        <span class="sc-hint">Admin yang bertanggung jawab (opsional)</span>
                    </div>
                    <div class="sc-group">
                        <label class="sc-label"><i class="bx bx-show"></i> Visibilitas</label>
                        <div class="sc-toggle-group" style="margin-top:.25rem">
                            <label class="sc-toggle-item">
                                <input type="checkbox" id="f_is_public" checked/>
                                <span class="sc-toggle-slider"></span>
                                <span class="sc-toggle-label">Tampilkan ke Publik</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="sc-toggle-group">
                    <label class="sc-toggle-item">
                        <input type="checkbox" id="f_is_pinned"/>
                        <span class="sc-toggle-slider"></span>
                        <span class="sc-toggle-label"><i class="bx bxs-pin"></i> Pin Status (Tampilkan di Atas)</span>
                    </label>
                </div>
            </div>

            {{-- ── Preview ── --}}
            <div class="sc-section">
                <div class="sc-section-head">
                    <i class="bx bx-show"></i>
                    <h2>Preview</h2>
                </div>
                <div class="sc-preview-card">
                    <div class="sc-preview-head">
                        <span class="sc-preview-badge">Preview</span>
                        <span class="sc-preview-sev" id="pvSev" style="display:none"></span>
                    </div>
                    <h3 class="sc-pv-title" id="pvTitle">Judul Status</h3>
                    <div class="sc-pv-meta" id="pvMeta"></div>
                    <p class="sc-pv-desc" id="pvDesc">Deskripsi masalah akan ditampilkan di sini...</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="sc-actions">
                <a href="{{ route('admin.status-board.index') }}" class="sc-btn sc-btn-secondary"><i class="bx bx-x"></i> Batal</a>
                <button type="submit" class="sc-btn sc-btn-primary" id="btnSubmit">
                    <i class="bx bx-save"></i> Buat Status
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

// ── Alert ──
function showAlert(type, msg) {
    const el = document.getElementById('scAlert');
    document.getElementById('scAlertIcon').className = 'bx ' + (type==='success'?'bx-check-circle':'bx-error-circle');
    document.getElementById('scAlertMsg').textContent = msg;
    el.className = 'sc-alert ' + type;
    el.scrollIntoView({ behavior:'smooth', block:'nearest' });
    if (type==='success') setTimeout(hideAlert, 5000);
}
function hideAlert() { document.getElementById('scAlert').classList.add('sc-alert-hide'); }

// ── Validation ──
function clearErr(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('sc-alert-hide'); el.querySelector?.('i'); }
    const field = id.replace('e_','f_');
    document.getElementById(field)?.classList.remove('is-invalid');
}

function showErr(fieldId, errId, msg) {
    const fEl = document.getElementById(fieldId);
    const eEl = document.getElementById(errId);
    if (fEl) fEl.classList.add('is-invalid');
    if (eEl) {
        eEl.classList.remove('sc-alert-hide');
        eEl.innerHTML = `<i class="bx bx-error-circle"></i> ${msg}`;
    }
}

function validateForm() {
    let valid = true;
    if (!document.getElementById('f_title').value.trim()) { showErr('f_title','e_title','Judul harus diisi'); valid=false; }
    if (!document.getElementById('f_category').value) { showErr('f_category','e_category','Kategori harus dipilih'); valid=false; }
    if (!document.getElementById('f_severity').value) { showErr('f_severity','e_severity','Tingkat keparahan harus dipilih'); valid=false; }
    if (!document.getElementById('f_started_at').value) { showErr('f_started_at','e_started_at','Waktu mulai harus diisi'); valid=false; }
    const desc = document.getElementById('f_description').value.trim();
    if (!desc) { showErr('f_description','e_description','Deskripsi harus diisi'); valid=false; }
    else if (desc.length < 20) { showErr('f_description','e_description','Deskripsi minimal 20 karakter'); valid=false; }
    return valid;
}

// ── Submit ──
function submitForm(e) {
    e.preventDefault();
    if (!validateForm()) { showAlert('error','Mohon lengkapi form dengan benar'); return; }

    const payload = {
        title:       document.getElementById('f_title').value.trim(),
        category:    document.getElementById('f_category').value,
        severity:    document.getElementById('f_severity').value,
        affected_area: document.getElementById('f_area').value.trim() || null,
        started_at:  document.getElementById('f_started_at').value,
        description: document.getElementById('f_description').value.trim(),
        assigned_to: document.getElementById('f_assigned_to').value || null,
        is_public:   document.getElementById('f_is_public').checked,
        is_pinned:   document.getElementById('f_is_pinned').checked,
    };

    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerHTML = '<i class="bx bx-loader-alt sc-spin"></i> Menyimpan...';

    fetch('/admin/status-board', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json', 'Content-Type':'application/json' },
        body: JSON.stringify(payload)
    }).then(r => r.json()).then(d => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-save"></i> Buat Status';
        if (d.success) {
            showAlert('success', 'Status berhasil dibuat!');
            setTimeout(() => { window.location.href = '/admin/status-board'; }, 1200);
        } else {
            if (d.errors) {
                const errMap = { title:'e_title', category:'e_category', severity:'e_severity', started_at:'e_started_at', description:'e_description' };
                Object.entries(d.errors).forEach(([field, msgs]) => {
                    if (errMap[field]) showErr('f_'+field, errMap[field], Array.isArray(msgs)?msgs[0]:msgs);
                });
                showAlert('error','Terdapat kesalahan pada form');
            } else { showAlert('error', d.message||'Gagal membuat status'); }
        }
    }).catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-save"></i> Buat Status';
        showAlert('error','Terjadi kesalahan. Silakan coba lagi.');
    });
}

// ── Preview ──
const catLabels = { power_outage:'Gangguan Listrik', technical_issue:'Masalah Teknis', facility_issue:'Masalah Fasilitas', network_issue:'Gangguan Jaringan', other:'Lainnya' };
const sevLabels = { critical:'Kritis', high:'Tinggi', medium:'Sedang', low:'Rendah' };
const sevClass  = { critical:'sev-critical', high:'sev-high', medium:'sev-medium', low:'sev-low' };

function updatePreview() {
    const title = document.getElementById('f_title').value || 'Judul Status';
    const cat   = document.getElementById('f_category').value;
    const sev   = document.getElementById('f_severity').value;
    const area  = document.getElementById('f_area').value;
    const desc  = document.getElementById('f_description').value || 'Deskripsi masalah akan ditampilkan di sini...';

    document.getElementById('pvTitle').textContent = title;
    document.getElementById('pvDesc').textContent  = desc;

    const sevEl = document.getElementById('pvSev');
    if (sev) { sevEl.textContent = sevLabels[sev]; sevEl.className = 'sc-preview-sev '+sevClass[sev]; sevEl.style.display=''; }
    else { sevEl.style.display = 'none'; }

    let metaHtml = '';
    if (cat)  metaHtml += `<span class="sc-pv-meta-item"><i class="bx bx-category"></i>${catLabels[cat]||cat}</span>`;
    if (area) metaHtml += `<span class="sc-pv-meta-item"><i class="bx bx-map-pin"></i>${area}</span>`;
    document.getElementById('pvMeta').innerHTML = metaHtml;
}

// ── Init ──
document.addEventListener('DOMContentLoaded', () => {
    const now = new Date();
    const offset = now.getTimezoneOffset() * 60000;
    document.getElementById('f_started_at').value = new Date(now - offset).toISOString().slice(0,16);
    updatePreview();
});
</script>
@endpush
