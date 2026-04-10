@extends('layouts.client')

@section('title', 'Buat Tiket Baru')
@section('page_title', 'Buat Tiket')
@section('breadcrumb', 'Home / Tiket / Buat Baru')



@section('content')
<div class="ct-wrap">

    <a href="{{ route('client.dashboard') }}" class="btn-back">
        <i class='bx bx-arrow-back'></i>
        Kembali ke Dashboard
    </a>

    <div class="ct-card">

        {{-- ── HEADER ── --}}
        <div class="ct-head">
            <div class="ct-head-inner">
                <div class="ct-head-icon"><i class='bx bx-file-blank'></i></div>
                <div>
                    <h2>Buat Tiket Dukungan Baru</h2>
                    <p>Isi detail di bawah ini dan tim kami akan segera menindaklanjuti.</p>
                </div>
            </div>
        </div>

        {{-- ── FORM ── --}}
        <form method="POST" action="{{ route('client.tickets.store') }}"
              enctype="multipart/form-data" class="ct-body" id="ctForm">
            @csrf

            {{-- TITLE --}}
            <div class="ct-field">
                <div class="ct-label"><i class='bx bx-text'></i> Judul Tiket <span class="req">*</span></div>
                <input type="text" name="title"
                    class="ct-input @error('title') is-err @enderror"
                    placeholder="Deskripsi singkat masalah Anda"
                    value="{{ old('title') }}" maxlength="255">
                @error('title')
                    <div class="field-err" style="display:flex;"><i class='bx bx-error-circle'></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- CATEGORY --}}
            <div class="ct-field">
                <div class="ct-label"><i class='bx bx-folder'></i> Kategori <span class="req">*</span></div>
                <select name="category_id" class="ct-select @error('category_id') is-err @enderror">
                    <option value="">Pilih kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <div class="field-err" style="display:flex;"><i class='bx bx-error-circle'></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- URGENCY --}}
            <div class="ct-field">
                <div class="urg-note">
                    <i class='bx bx-info-circle'></i>
                    <span>Pilihan ini hanya sebagai indikasi.
                        <strong>Prioritas resmi akan ditentukan oleh tim kami</strong>
                        berdasarkan kategori dan deskripsi masalah Anda.</span>
                </div>
                <div class="ct-label"><i class='bx bx-flag'></i>
                    Tingkat urgensi <span class="opt-badge">opsional</span>
                </div>
                <div class="urg-grid">
                    @php
                    $urgencies = [
                        ['value'=>'low',      'icon'=>'bx bx-check-circle', 'label'=>'Rendah',   'desc'=>'Bisa ditangani dalam beberapa hari ke depan.'],
                        ['value'=>'medium',   'icon'=>'bx bx-time-five',    'label'=>'Sedang',   'desc'=>'Perlu ditangani dalam hari ini.'],
                        ['value'=>'high',     'icon'=>'bx bx-error',        'label'=>'Tinggi',   'desc'=>'Mengganggu kegiatan, perlu segera.'],
                        ['value'=>'critical', 'icon'=>'bx bx-x-circle',     'label'=>'Kritis',   'desc'=>'Menghentikan aktivitas sepenuhnya.'],
                    ];
                    @endphp
                    @foreach($urgencies as $u)
                    <div class="urg-btn urg-{{ $u['value'] }} {{ old('urgency_level') === $u['value'] ? 'active' : '' }}"
                         onclick="selUrg('{{ $u['value'] }}')">
                        <div class="urg-icon"><i class="{{ $u['icon'] }}"></i></div>
                        <div class="urg-name">{{ $u['label'] }}</div>
                        <div class="urg-desc">{{ $u['desc'] }}</div>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="urgency_level" id="urgVal" value="{{ old('urgency_level') }}">
            </div>

            {{-- DESCRIPTION --}}
            <div class="ct-field">
                <div class="ct-label"><i class='bx bx-align-left'></i> Deskripsi Detail <span class="req">*</span></div>
                <textarea name="description" id="descTA"
                    class="ct-textarea @error('description') is-err @enderror"
                    rows="6" maxlength="2000"
                    placeholder="Berikan informasi detail. Sertakan langkah-langkah, pesan error, atau konteks yang relevan..."
                    oninput="updateChar()">{{ old('description') }}</textarea>
                <div class="char-row"><span id="charNum">{{ strlen(old('description','')) }}</span> / 2000</div>
                @error('description')
                    <div class="field-err" style="display:flex;"><i class='bx bx-error-circle'></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="ct-divider"></div>

            {{-- EVENT DETAILS (collapsible) --}}
            <div class="ct-field">
                <div class="coll-trigger" onclick="toggleEvent()">
                    <div class="coll-left">
                        <i class='bx bx-calendar-check'></i>
                        Detail Event
                        <span class="opt-badge">opsional</span>
                    </div>
                    <i class='bx bx-chevron-down coll-arrow' id="evArrow"></i>
                </div>
                <div id="evDetails" style="display:none;">
                    <div class="event-grid">
                        <input type="text" name="event_name" class="ct-input"
                            placeholder="Nama Event" value="{{ old('event_name') }}">
                        <input type="text" name="venue" class="ct-input"
                            placeholder="Venue / Lokasi" value="{{ old('venue') }}">
                        <input type="text" name="area" class="ct-input"
                            placeholder="Area / Ruangan" value="{{ old('area') }}">
                    </div>
                </div>
            </div>

            <div class="ct-divider"></div>

            {{-- ATTACHMENTS (multi-upload, maks 5 foto/file) --}}
            <div class="ct-field">
                <div class="upload-head">
                    <div class="upload-title"><i class='bx bx-paperclip'></i> Lampiran Foto / File</div>
                    <span class="upload-limit">Maks 5 file · 5 MB/file</span>
                </div>

                {{-- Hidden real input; JS akan clone & append sebelum submit --}}
                <input type="file" id="fileInput" multiple
                    accept="image/*,.pdf,.doc,.docx"
                    style="display:none;" onchange="handleFiles(this.files)">

                {{-- Container input dinamis yang dikirim ke server --}}
                <div id="fileInputsContainer"></div>

                <div class="drop-zone" id="dropZone"
                    onclick="document.getElementById('fileInput').click()"
                    ondragover="event.preventDefault();this.classList.add('dragover')"
                    ondragleave="this.classList.remove('dragover')"
                    ondrop="event.preventDefault();this.classList.remove('dragover');handleFiles(event.dataTransfer.files)">
                    <div class="drop-circle"><i class='bx bx-cloud-upload'></i></div>
                    <h6>Letakkan file di sini atau klik untuk browse</h6>
                    <p>Mendukung: JPG, PNG, PDF, DOC, DOCX &bull; Maks 5 file</p>
                </div>

                <div id="progressWrap" style="display:none; margin-top:.75rem;">
                    <div class="upload-progress">
                        <span id="progressLabel" style="white-space:nowrap;">Memproses...</span>
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill" style="width:0%"></div>
                        </div>
                    </div>
                </div>

                <div class="file-list" id="fileList"></div>

                <div class="field-err" id="fileErr" style="margin-top:.5rem;">
                    <i class='bx bx-error-circle'></i>
                    <span id="fileErrMsg"></span>
                </div>
            </div>

        </form>

        {{-- ── ACTIONS (outside form, linked via form="ctForm") ── --}}
        <div class="ct-actions">
            <a href="{{ route('client.dashboard') }}" class="btn-cancel">
                <i class='bx bx-x'></i> Batal
            </a>
            <button type="submit" form="ctForm" class="btn-submit" id="submitBtn">
                <i class='bx bx-paper-plane'></i> Kirim Tiket
            </button>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
/* ─── State ─── */
let selectedFiles = [];

/* ─── Urgency ─── */
function selUrg(val) {
    document.querySelectorAll('.urg-btn').forEach(b =>
        b.classList.remove('active')
    );
    const el = document.querySelector('.urg-' + val);
    if (el) el.classList.add('active');
    document.getElementById('urgVal').value = val;
}

/* ─── Event collapsible ─── */
function toggleEvent() {
    const d = document.getElementById('evDetails');
    const a = document.getElementById('evArrow');
    const isOpen = d.style.display !== 'none';
    d.style.display = isOpen ? 'none' : 'block';
    a.classList.toggle('open', !isOpen);
}

/* ─── Char counter ─── */
function updateChar() {
    document.getElementById('charNum').textContent =
        document.getElementById('descTA').value.length;
}

/* ─── Helpers ─── */
function fmtSize(bytes) {
    if (bytes < 1024)        return bytes + ' B';
    if (bytes < 1048576)     return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}
function isImage(file) { return file.type.startsWith('image/'); }
function showFileErr(msg) {
    const el = document.getElementById('fileErr');
    document.getElementById('fileErrMsg').textContent = msg;
    el.style.display = 'flex';
}
function hideFileErr() {
    document.getElementById('fileErr').style.display = 'none';
}

/* ─── Handle dropped/selected files ─── */
function handleFiles(incoming) {
    hideFileErr();
    const arr = Array.from(incoming);

    /* Duplicate check */
    const dupes = arr.filter(f =>
        selectedFiles.some(s => s.name === f.name && s.size === f.size)
    );
    if (dupes.length) {
        showFileErr(`"${dupes[0].name}" sudah ditambahkan.`);
        return;
    }

    /* Over-limit check */
    const combined = [...selectedFiles, ...arr];
    if (combined.length > 5) {
        showFileErr('Maksimal 5 file. Hapus beberapa file terlebih dahulu.');
        return;
    }

    /* Size check */
    const tooBig = arr.find(f => f.size > 5 * 1024 * 1024);
    if (tooBig) {
        showFileErr(`"${tooBig.name}" melebihi batas 5 MB.`);
        return;
    }

    selectedFiles = combined;
    syncHiddenInputs();
    showProgress(arr.length);
}

/* ─── Sync File objects ke hidden inputs agar terkirim ke server ─── */
function syncHiddenInputs() {
    /* Pendekatan: render DataTransfer ke single multi-input */
    const container = document.getElementById('fileInputsContainer');
    container.innerHTML = '';

    if (!selectedFiles.length) return;

    try {
        const dt = new DataTransfer();
        selectedFiles.forEach(f => dt.items.add(f));

        const inp = document.createElement('input');
        inp.type     = 'file';
        inp.name     = 'attachments[]';
        inp.multiple = true;
        inp.style.display = 'none';
        container.appendChild(inp);
        inp.files = dt.files;   /* modern browsers only */
    } catch(e) {
        /* Fallback: individual hidden clone per file (tidak ideal tapi aman) */
        console.warn('DataTransfer tidak didukung, gunakan FormData manual.');
    }
}

/* ─── Animated progress bar ─── */
function showProgress(count) {
    const pw  = document.getElementById('progressWrap');
    const pf  = document.getElementById('progressFill');
    const pl  = document.getElementById('progressLabel');
    pw.style.display = 'block';
    pf.style.width   = '0%';
    pl.textContent   = 'Memproses ' + count + ' file…';
    let p = 0;
    const iv = setInterval(() => {
        p += Math.random() * 35;
        if (p >= 100) {
            p = 100;
            clearInterval(iv);
            setTimeout(() => { pw.style.display = 'none'; }, 300);
            renderFileList();
        }
        pf.style.width = Math.min(p, 100) + '%';
    }, 70);
}

/* ─── Render file preview list ─── */
function renderFileList() {
    const list = document.getElementById('fileList');
    list.innerHTML = '';

    selectedFiles.forEach((file, idx) => {
        const item = document.createElement('div');
        item.className = 'file-item';

        if (isImage(file)) {
            /* Thumbnail for images */
            const reader = new FileReader();
            reader.onload = e => {
                const img = item.querySelector('.file-thumb');
                if (img) img.src = e.target.result;
            };
            reader.readAsDataURL(file);
            item.innerHTML = `
                <img class="file-thumb" src="" alt="preview">
                <div class="file-meta">
                    <div class="file-name">${escHtml(file.name)}</div>
                    <div class="file-size">${fmtSize(file.size)} &bull; Gambar</div>
                </div>
                <button type="button" class="file-rm" onclick="removeFile(${idx})" title="Hapus">
                    <i class='bx bx-trash'></i>
                </button>`;
        } else {
            /* Icon for non-images */
            const ext = file.name.split('.').pop().toUpperCase();
            item.innerHTML = `
                <div class="file-icon"><i class='bx bx-file-blank'></i></div>
                <div class="file-meta">
                    <div class="file-name">${escHtml(file.name)}</div>
                    <div class="file-size">${fmtSize(file.size)} &bull; ${ext}</div>
                </div>
                <button type="button" class="file-rm" onclick="removeFile(${idx})" title="Hapus">
                    <i class='bx bx-trash'></i>
                </button>`;
        }
        list.appendChild(item);
    });

    /* Disable drop zone if full */
    const dz = document.getElementById('dropZone');
    dz.classList.toggle('disabled', selectedFiles.length >= 5);
}

function removeFile(idx) {
    selectedFiles.splice(idx, 1);
    syncHiddenInputs();
    renderFileList();
    hideFileErr();
    document.getElementById('fileInput').value = '';
}

function escHtml(str) {
    return str.replace(/[&<>"']/g, c => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));
}

/* ─── Fallback: gunakan FormData jika DataTransfer tidak kompatibel ─── */
document.getElementById('ctForm').addEventListener('submit', function(e) {
    /* Jika browser tidak mendukung dt.files assignment, intercept & kirim via fetch */
    const testInp = document.querySelector('#fileInputsContainer input');
    if (testInp && testInp.files && testInp.files.length === selectedFiles.length) {
        return; /* Normal submit OK */
    }
    if (!selectedFiles.length) return; /* No files, normal submit */

    e.preventDefault();
    const fd = new FormData(this);
    /* Remove placeholder & re-add files properly */
    fd.delete('attachments[]');
    selectedFiles.forEach(f => fd.append('attachments[]', f));

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Mengirim…";
    
    fetch(this.action, { method: 'POST', body: fd })
        .then(r => { if (r.redirected) window.location.href = r.url; else return r.text(); })
        .then(html => { if (html) document.open(); document.write(html); document.close(); })
        .catch(() => { btn.disabled = false; btn.innerHTML = "<i class='bx bx-paper-plane'></i> Kirim Tiket"; });
});

/* ─── Restore urgency on validation fail ─── */
@if(old('urgency_level'))
    selUrg('{{ old('urgency_level') }}');
@endif
</script>
@endpush

@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.ct-wrap { max-width: 820px; margin: 0 auto; padding: 1.5rem; }

/* ── BACK BUTTON ── */
.btn-back {
    display: inline-flex; align-items: center; gap: .6rem;
    padding: .65rem 1rem;
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 10px; color: #6b7280;
    font-size: .875rem; font-weight: 600;
    text-decoration: none; cursor: pointer;
    transition: all .2s; margin-bottom: 1.25rem;
}
.btn-back:hover { border-color: #6366f1; color: #6366f1; transform: translateX(-3px); }
.btn-back i { font-size: 1.1rem; }

/* ── CARD ── */
.ct-card {
    background: #fff;
    border-radius: 20px;
    border: 1px solid rgba(99,102,241,.1);
    box-shadow: 0 4px 24px rgba(0,0,0,.06);
    overflow: hidden;
}

/* ── HEADER ── */
.ct-head {
    padding: 1.75rem 2rem;
    background: linear-gradient(135deg, #6366f1 0%, #7c3aed 100%);
    position: relative; overflow: hidden;
}
.ct-head::after {
    content: ''; position: absolute;
    top: -60px; right: -60px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,.08); border-radius: 50%;
}
.ct-head-inner { display: flex; align-items: center; gap: 1rem; position: relative; z-index: 1; }
.ct-head-icon {
    width: 52px; height: 52px;
    background: rgba(255,255,255,.15);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; color: #fff; flex-shrink: 0;
}
.ct-head h2 { color: #fff; font-size: 1.3rem; font-weight: 700; margin-bottom: .25rem; }
.ct-head p  { color: rgba(255,255,255,.85); font-size: .875rem; margin: 0; }

/* ── BODY ── */
.ct-body { padding: 1.75rem 2rem; }

/* ── FIELD ── */
.ct-field { margin-bottom: 1.5rem; }
.ct-label {
    display: flex; align-items: center; gap: .4rem;
    font-size: .75rem; font-weight: 700;
    color: #64748b; text-transform: uppercase;
    letter-spacing: .05em; margin-bottom: .5rem;
}
.ct-label i { font-size: .95rem; color: #6366f1; }
.req { color: #ef4444; }
.opt-badge {
    background: #eef2ff; color: #4338ca;
    padding: .1rem .5rem; border-radius: 20px;
    font-size: .7rem; font-weight: 700;
}
.ct-input, .ct-select {
    width: 100%; padding: .8rem 1rem;
    border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: .95rem; color: #1f2937;
    background: #f9fafb; font-weight: 500;
    transition: all .25s; appearance: none;
}
.ct-input:focus, .ct-select:focus {
    outline: none; border-color: #6366f1;
    background: #fff; box-shadow: 0 0 0 3px rgba(99,102,241,.1);
}
.ct-input.is-err, .ct-select.is-err { border-color: #ef4444; background: #fef2f2; }
.ct-textarea {
    width: 100%; padding: .875rem 1rem;
    border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: .95rem; color: #1f2937;
    background: #f9fafb; font-weight: 500;
    resize: vertical; min-height: 130px;
    font-family: inherit; line-height: 1.6;
    transition: all .25s;
}
.ct-textarea:focus {
    outline: none; border-color: #6366f1;
    background: #fff; box-shadow: 0 0 0 3px rgba(99,102,241,.1);
}
.ct-textarea.is-err { border-color: #ef4444; background: #fef2f2; }
.char-row { display: flex; justify-content: flex-end; font-size: .75rem; color: #9ca3af; margin-top: .35rem; }

/* ── ERROR ── */
.field-err {
    display: none; align-items: center; gap: .35rem;
    color: #ef4444; font-size: .8rem; font-weight: 600; margin-top: .4rem;
}
.field-err i { font-size: .95rem; }

/* ── URGENCY INFO ── */
.urg-note {
    display: flex; gap: .75rem;
    padding: .875rem 1rem;
    background: #fef3c7; border: 1px solid #fbbf24;
    border-radius: 10px; margin-bottom: 1rem;
    font-size: .875rem; color: #78350f; line-height: 1.5;
}
.urg-note i { font-size: 1.2rem; color: #92400e; flex-shrink: 0; margin-top: .1rem; }

/* ── URGENCY GRID ── */
.urg-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: .75rem;
}
.urg-btn {
    padding: .875rem .75rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    background: #f9fafb;
    cursor: pointer;
    transition: all .2s;
    text-align: left;
    user-select: none;
}
.urg-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.06); }
.urg-icon  { font-size: 1.25rem; margin-bottom: .35rem; }
.urg-icon i { font-size: 1.28rem; color: #0f172a; }
.urg-name  { font-weight: 700; font-size: .8rem; color: #1e293b; margin-bottom: .2rem; }
.urg-desc  { font-size: .72rem; color: #6b7280; line-height: 1.4; }
.urg-btn.urg-low.active { background: #dcfce7; border-color: #22c55e; }
.urg-btn.urg-low.active .urg-icon i,
.urg-btn.urg-low.active .urg-name,
.urg-btn.urg-low.active .urg-desc { color: #166534; }

.urg-btn.urg-medium.active { background: #fef3c7; border-color: #f59e0b; }
.urg-btn.urg-medium.active .urg-icon i,
.urg-btn.urg-medium.active .urg-name,
.urg-btn.urg-medium.active .urg-desc { color: #92400e; }

.urg-btn.urg-high.active { background: #ffedd5; border-color: #fb923c; }
.urg-btn.urg-high.active .urg-icon i,
.urg-btn.urg-high.active .urg-name,
.urg-btn.urg-high.active .urg-desc { color: #9a3412; }

.urg-btn.urg-critical.active { background: #fee2e2; border-color: #ef4444; }
.urg-btn.urg-critical.active .urg-icon i,
.urg-btn.urg-critical.active .urg-name,
.urg-btn.urg-critical.active .urg-desc { color: #991b1b; }

/* ── DIVIDER ── */
.ct-divider { height: 1px; background: #f0f0f0; margin: 1.5rem 0; }

/* ── COLLAPSIBLE EVENT ── */
.coll-trigger {
    display: flex; justify-content: space-between; align-items: center;
    padding: .75rem 1rem; margin: 0 -1rem;
    border-radius: 10px; cursor: pointer;
    transition: background .2s;
}
.coll-trigger:hover { background: #f8fafc; }
.coll-left { display: flex; align-items: center; gap: .6rem; font-weight: 700; font-size: .9rem; color: #1e293b; }
.coll-left i { color: #6366f1; font-size: 1.15rem; }
.coll-arrow { font-size: 1.1rem; color: #6366f1; transition: transform .25s; }
.coll-arrow.open { transform: rotate(180deg); }
.event-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .875rem;
    padding: 1rem 0 .25rem;
}
.event-grid input { padding: .75rem 1rem; font-size: .9rem; }

/* ── UPLOAD ── */
.upload-head {
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 1rem;
}
.upload-title { display: flex; align-items: center; gap: .6rem; font-weight: 700; font-size: .9rem; color: #1e293b; }
.upload-title i { color: #6366f1; font-size: 1.15rem; }
.upload-limit { font-size: .8rem; color: #9ca3af; }
.drop-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 14px;
    padding: 2.25rem 1.5rem;
    text-align: center;
    cursor: pointer;
    background: #f9fafb;
    transition: all .25s;
}
.drop-zone:hover, .drop-zone.dragover {
    border-color: #6366f1;
    background: #eef2ff;
    transform: translateY(-2px);
}
.drop-zone.disabled { opacity: .45; pointer-events: none; }
.drop-circle {
    width: 56px; height: 56px;
    background: #eef2ff; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem; color: #6366f1; margin: 0 auto .875rem;
}
.drop-zone h6 { font-size: .95rem; font-weight: 700; color: #1e293b; margin-bottom: .3rem; }
.drop-zone p  { font-size: .8rem; color: #64748b; margin: 0; }

/* ── PROGRESS ── */
.upload-progress {
    display: flex; align-items: center; gap: .75rem;
    margin-top: .75rem; font-size: .8rem; color: #64748b;
}
.progress-bar  { flex: 1; height: 4px; background: #e5e7eb; border-radius: 2px; overflow: hidden; }
.progress-fill { height: 100%; background: linear-gradient(90deg, #6366f1, #7c3aed); transition: width .25s; }

/* ── FILE LIST ── */
.file-list { margin-top: .875rem; display: flex; flex-direction: column; gap: .5rem; }
.file-item {
    display: flex; align-items: center; gap: .75rem;
    padding: .625rem .875rem;
    background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px;
    animation: fadeIn .2s ease;
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
.file-thumb {
    width: 40px; height: 40px; border-radius: 8px;
    object-fit: cover; border: 1px solid #e5e7eb; flex-shrink: 0;
}
.file-icon {
    width: 40px; height: 40px; border-radius: 8px;
    background: #eef2ff;
    display: flex; align-items: center; justify-content: center;
    color: #6366f1; font-size: 1.2rem; flex-shrink: 0;
}
.file-meta { flex: 1; min-width: 0; }
.file-name { font-size: .85rem; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.file-size { font-size: .75rem; color: #9ca3af; margin-top: .1rem; }
.file-rm {
    width: 28px; height: 28px; border-radius: 7px;
    border: none; background: none;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: #9ca3af; font-size: 1rem; transition: all .2s; flex-shrink: 0;
}
.file-rm:hover { background: #fee2e2; color: #ef4444; }

/* ── ACTIONS ── */
.ct-actions {
    display: flex; gap: .875rem; justify-content: flex-end;
    padding: 1.25rem 2rem 1.75rem;
    background: #fafbfc; border-top: 1px solid #f0f0f0;
}
.btn-cancel {
    display: flex; align-items: center; gap: .45rem;
    padding: .8rem 1.35rem;
    background: #fff; border: 1.5px solid #e5e7eb;
    border-radius: 10px; color: #475569;
    font-weight: 700; font-size: .875rem;
    cursor: pointer; text-decoration: none;
    transition: all .2s;
}
.btn-cancel:hover { border-color: #cbd5e1; color: #1e293b; transform: translateY(-1px); }
.btn-submit {
    display: flex; align-items: center; gap: .45rem;
    padding: .8rem 1.75rem;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: #fff; border: none; border-radius: 10px;
    font-weight: 700; font-size: .875rem;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(99,102,241,.35);
    transition: all .2s;
}
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(99,102,241,.45); }
.btn-submit:disabled { opacity: .65; cursor: not-allowed; transform: none; }

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .ct-wrap   { padding: 1rem; }
    .ct-head   { padding: 1.25rem; }
    .ct-head-inner { flex-direction: column; text-align: center; }
    .ct-body   { padding: 1.25rem; }
    .urg-grid  { grid-template-columns: repeat(2, 1fr); }
    .event-grid{ grid-template-columns: 1fr; }
    .ct-actions{ flex-direction: column; padding: 1rem 1.25rem 1.5rem; }
    .btn-cancel, .btn-submit { width: 100%; justify-content: center; }
}
@media (max-width: 400px) {
    .urg-grid { grid-template-columns: 1fr 1fr; gap: .5rem; }
}
</style>
@endpush

