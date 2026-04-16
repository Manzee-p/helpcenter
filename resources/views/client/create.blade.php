@extends('layouts.client')

@section('title', 'Buat Tiket Baru')
@section('page_title', 'Buat Tiket')
@section('breadcrumb', 'Home / Tiket / Buat Baru')

@section('content')
<div class="ct-page">

    {{-- SIDEBAR KIRI --}}
    <aside class="ct-sidebar ct-sidebar--left">
        <div class="sidebar-tip">
            <div class="sidebar-tip-icon"><i class='bx bx-bulb'></i></div>
            <h4>Tips Pelaporan</h4>
            <ul>
                <li><i class='bx bx-check'></i> Judul yang jelas mempercepat penanganan</li>
                <li><i class='bx bx-check'></i> Sertakan foto atau screenshot jika ada</li>
                <li><i class='bx bx-check'></i> Isi detail event agar vendor tahu lokasi</li>
                <li><i class='bx bx-check'></i> Pilih urgensi sesuai kondisi sebenarnya</li>
            </ul>
        </div>

        <div class="sidebar-stat">
            <div class="stat-row">
                <i class='bx bx-time-five'></i>
                <div>
                    <strong>Rata-rata respons</strong>
                    <span>~2 jam kerja</span>
                </div>
            </div>
            <div class="stat-row">
                <i class='bx bx-support'></i>
                <div>
                    <strong>Tim aktif</strong>
                    <span>Senin–Sabtu, 08–17</span>
                </div>
            </div>
        </div>
    </aside>

    {{-- MAIN FORM AREA --}}
    <div class="ct-main">

        <a href="{{ route('client.dashboard') }}" class="btn-back">
            <i class='bx bx-arrow-back'></i>
            Kembali ke Dashboard
        </a>

        <div class="ct-card">

            {{-- HEADER --}}
            <div class="ct-head">
                <div class="ct-head-inner">
                    <div class="ct-head-icon"><i class='bx bx-file-blank'></i></div>
                    <div>
                        <h2>Buat Tiket Dukungan Baru</h2>
                        <p>Isi detail di bawah ini dan tim kami akan segera menindaklanjuti.</p>
                    </div>
                </div>
                <div class="ct-head-deco"></div>
            </div>

            {{-- FORM --}}
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
                    <div class="select-wrap">
                        <select name="category_id" class="ct-select @error('category_id') is-err @enderror">
                            <option value="">Pilih kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <i class='bx bx-chevron-down select-icon'></i>
                    </div>
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
                        Tingkat urgensi/prioritas <span class="opt-badge">opsional</span>
                    </div>
                    <div class="urg-grid">
                        @php
                        $urgencies = [
                            ['value'=>'low',      'icon'=>'bx bx-check-circle', 'label'=>'Rendah',   'desc'=>'Tidak terlalu mendesak.'],
                            ['value'=>'medium',   'icon'=>'bx bx-time-five',    'label'=>'Sedang',   'desc'=>'Perlu ditangani segera.'],
                            ['value'=>'high',     'icon'=>'bx bx-error',        'label'=>'Tinggi',   'desc'=>'Mengganggu kegiatan.'],
                            ['value'=>'critical', 'icon'=>'bx bx-x-circle',     'label'=>'Kritis',   'desc'=>'Harus langsung ditangani.'],
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
                            Detail lokasi
                            <span class="req">*</span>
                        </div>
                        <i class='bx bx-chevron-down coll-arrow' id="evArrow"></i>
                    </div>
                    <div id="evDetails" style="{{ $errors->has('event_detail') ? 'display:block;' : 'display:none;' }}">
                        @error('event_detail')
                            <div style="color:red; margin-bottom:10px;">
                                {{ $message }}
                            </div>
                        @enderror
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

                {{-- ATTACHMENTS --}}
                <div class="ct-field">
                    <div class="upload-head">
                        <div class="upload-title"><i class='bx bx-paperclip'></i> Lampiran Foto / File</div>
                        <span class="upload-limit">Maks 5 file · 5 MB/file</span>
                    </div>

                    <input type="file" id="fileInput" multiple
                        accept="image/*,.pdf,.doc,.docx"
                        style="display:none;" onchange="handleFiles(this.files)">

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

                    @error('attachments')
                        <div class="field-err" style="display:flex; margin-top:.5rem;">
                            <i class='bx bx-error-circle'></i> {{ $message }}
                        </div>
                    @enderror

                    @error('attachments.*')
                        <div class="field-err" style="display:flex; margin-top:.5rem;">
                            <i class='bx bx-error-circle'></i> {{ $message }}
                        </div>
                    @enderror

                    {{-- ERROR DARI JS --}}
                    <div class="field-err" id="fileErr" style="margin-top:.5rem;">
                        <i class='bx bx-error-circle'></i>
                        <span id="fileErrMsg"></span>
                    </div>

                    <div class="field-err" id="fileErr" style="margin-top:.5rem;">
                        <i class='bx bx-error-circle'></i>
                        <span id="fileErrMsg"></span>
                    </div>
                </div>

            </form>

            {{-- ACTIONS --}}
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

    {{-- SIDEBAR KANAN --}}
    <aside class="ct-sidebar ct-sidebar--right">

        {{-- STATISTIK TIKET USER --}}
        @php
            $myTickets      = auth()->user()->tickets()->get();
            $totalTickets   = $myTickets->count();
            $openTickets    = $myTickets->whereIn('status', ['new', 'in_progress', 'waiting_response'])->count();
            $resolvedTickets= $myTickets->whereIn('status', ['resolved', 'closed'])->count();

            // SLA metrics dari tiket user
            $slaData        = $myTickets->load('slaTracking');
            $slaMet         = $myTickets->filter(fn($t) =>
                                $t->slaTracking && $t->slaTracking->response_sla_met
                              )->count();
            $slaTotal       = $myTickets->filter(fn($t) =>
                                $t->slaTracking && !is_null($t->slaTracking->response_sla_met)
                              )->count();
            $slaPercent     = $slaTotal > 0 ? round(($slaMet / $slaTotal) * 100) : null;

            // Prioritas tiket aktif
            $urgentCount    = $myTickets->whereIn('status', ['new','in_progress'])->where('priority', 'urgent')->count();
            $highCount      = $myTickets->whereIn('status', ['new','in_progress'])->where('priority', 'high')->count();
        @endphp

        {{-- SLA INFO --}}
        <div class="sidebar-sla">
            <h4><i class='bx bx-time'></i> Target Respons</h4>
            <div class="sla-item">
                <div class="sla-dot sla-dot-urgent"></div>
                <div class="sla-meta">
                    <span class="sla-prio">Urgen</span>
                    <span class="sla-time">15 menit · Selesai 4 jam</span>
                </div>
            </div>
            <div class="sla-item">
                <div class="sla-dot sla-dot-high"></div>
                <div class="sla-meta">
                    <span class="sla-prio">Tinggi</span>
                    <span class="sla-time">30 menit · Selesai 8 jam</span>
                </div>
            </div>
            <div class="sla-item">
                <div class="sla-dot sla-dot-medium"></div>
                <div class="sla-meta">
                    <span class="sla-prio">Sedang</span>
                    <span class="sla-time">1 jam · Selesai 24 jam</span>
                </div>
            </div>
            <div class="sla-item">
                <div class="sla-dot sla-dot-low"></div>
                <div class="sla-meta">
                    <span class="sla-prio">Rendah</span>
                    <span class="sla-time">2 jam · Selesai 48 jam</span>
                </div>
            </div>
        </div>

        {{-- FAQ --}}
        <div class="sidebar-faq">
            <h4><i class='bx bx-help-circle'></i> FAQ Singkat</h4>
            <div class="faq-item">
                <strong>Berapa lama respons pertama?</strong>
                <p>Tim kami merespons dalam 1–2 jam di hari kerja.</p>
            </div>
            <div class="faq-item">
                <strong>Bisa buat lebih dari 1 tiket?</strong>
                <p>Ya, pisahkan tiap masalah ke tiket masing-masing.</p>
            </div>
            <div class="faq-item">
                <strong>Lampiran wajib diisi?</strong>
                <p>Tidak wajib, namun sangat membantu tim kami.</p>
            </div>
        </div>

    </aside>

</div>
@endsection

@push('scripts')
<script>

window.onload = function () {
    if (
        @json($errors->has('event_detail'))
    ) {
        document.getElementById("evDetails").style.display = "block";
        document.getElementById("evArrow").classList.add("rotate");
    }
};

/* State */
let selectedFiles = [];

/* Urgency */
function selUrg(val) {
    document.querySelectorAll('.urg-btn').forEach(b => b.classList.remove('active'));
    const el = document.querySelector('.urg-' + val);
    if (el) el.classList.add('active');
    document.getElementById('urgVal').value = val;
}

/* Event collapsible */
function toggleEvent() {
    const d = document.getElementById('evDetails');
    const a = document.getElementById('evArrow');
    const isOpen = d.style.display !== 'none';
    d.style.display = isOpen ? 'none' : 'block';
    a.classList.toggle('open', !isOpen);
}

/* Char counter */
function updateChar() {
    document.getElementById('charNum').textContent =
        document.getElementById('descTA').value.length;
}

/* Helpers */
function fmtSize(bytes) {
    if (bytes < 1024)    return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}
function isImage(file) { return file.type.startsWith('image/'); }
function showFileErr(msg) {
    const el = document.getElementById('fileErr');
    document.getElementById('fileErrMsg').textContent = msg;
    el.style.display = 'flex';
}
function hideFileErr() { document.getElementById('fileErr').style.display = 'none'; }

function handleFiles(incoming) {
    hideFileErr();
    const arr = Array.from(incoming);
    const dupes = arr.filter(f => selectedFiles.some(s => s.name === f.name && s.size === f.size));
    if (dupes.length) { showFileErr(`"${dupes[0].name}" sudah ditambahkan.`); return; }
    const combined = [...selectedFiles, ...arr];
    if (combined.length > 5) { showFileErr('Maksimal 5 file. Hapus beberapa file terlebih dahulu.'); return; }
    const tooBig = arr.find(f => f.size > 5 * 1024 * 1024);
    if (tooBig) { showFileErr(`"${tooBig.name}" melebihi batas 5 MB.`); return; }
    selectedFiles = combined;
    syncHiddenInputs();
    showProgress(arr.length);
}

function syncHiddenInputs() {
    const container = document.getElementById('fileInputsContainer');
    container.innerHTML = '';
    if (!selectedFiles.length) return;
    try {
        const dt = new DataTransfer();
        selectedFiles.forEach(f => dt.items.add(f));
        const inp = document.createElement('input');
        inp.type = 'file'; inp.name = 'attachments[]'; inp.multiple = true; inp.style.display = 'none';
        container.appendChild(inp);
        inp.files = dt.files;
    } catch(e) { console.warn('DataTransfer tidak didukung.'); }
}

function showProgress(count) {
    const pw = document.getElementById('progressWrap');
    const pf = document.getElementById('progressFill');
    const pl = document.getElementById('progressLabel');
    pw.style.display = 'block'; pf.style.width = '0%';
    pl.textContent = 'Memproses ' + count + ' file...';
    let p = 0;
    const iv = setInterval(() => {
        p += Math.random() * 35;
        if (p >= 100) {
            p = 100; clearInterval(iv);
            setTimeout(() => { pw.style.display = 'none'; }, 300);
            renderFileList();
        }
        pf.style.width = Math.min(p, 100) + '%';
    }, 70);
}

function renderFileList() {
    const list = document.getElementById('fileList');
    list.innerHTML = '';
    selectedFiles.forEach((file, idx) => {
        const item = document.createElement('div');
        item.className = 'file-item';
        if (isImage(file)) {
            const reader = new FileReader();
            reader.onload = e => { const img = item.querySelector('.file-thumb'); if (img) img.src = e.target.result; };
            reader.readAsDataURL(file);
            item.innerHTML = `<img class="file-thumb" src="" alt="preview"><div class="file-meta"><div class="file-name">${escHtml(file.name)}</div><div class="file-size">${fmtSize(file.size)} &bull; Gambar</div></div><button type="button" class="file-rm" onclick="removeFile(${idx})" title="Hapus"><i class='bx bx-trash'></i></button>`;
        } else {
            const ext = file.name.split('.').pop().toUpperCase();
            item.innerHTML = `<div class="file-icon"><i class='bx bx-file-blank'></i></div><div class="file-meta"><div class="file-name">${escHtml(file.name)}</div><div class="file-size">${fmtSize(file.size)} &bull; ${ext}</div></div><button type="button" class="file-rm" onclick="removeFile(${idx})" title="Hapus"><i class='bx bx-trash'></i></button>`;
        }
        list.appendChild(item);
    });
    document.getElementById('dropZone').classList.toggle('disabled', selectedFiles.length >= 5);
}

function removeFile(idx) {
    selectedFiles.splice(idx, 1);
    syncHiddenInputs(); renderFileList(); hideFileErr();
    document.getElementById('fileInput').value = '';
}

function escHtml(str) {
    return str.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

document.getElementById('ctForm').addEventListener('submit', function(e) {
    const testInp = document.querySelector('#fileInputsContainer input');
    if (testInp && testInp.files && testInp.files.length === selectedFiles.length) return;
    if (!selectedFiles.length) return;
    e.preventDefault();
    const fd = new FormData(this);
    fd.delete('attachments[]');
    selectedFiles.forEach(f => fd.append('attachments[]', f));
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Mengirim...";
    fetch(this.action, { method: 'POST', body: fd })
        .then(r => { if (r.redirected) window.location.href = r.url; else return r.text(); })
        .then(html => { if (html) { document.open(); document.write(html); document.close(); } })
        .catch(() => { btn.disabled = false; btn.innerHTML = "<i class='bx bx-paper-plane'></i> Kirim Tiket"; });
});

@if(old('urgency_level'))
    selUrg('{{ old('urgency_level') }}');
@endif
</script>
@endpush

@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ══════════════════════════════════════════
   PAGE LAYOUT - 3 KOLOM
══════════════════════════════════════════ */
.ct-page {
    display: grid;
    grid-template-columns: 220px minmax(0,1fr) 220px;
    gap: 1.25rem;
    align-items: start;
    padding: 0;
}

/* ══════════════════════════════════════════
   SIDEBAR
══════════════════════════════════════════ */
.ct-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    position: sticky;
    top: 1.5rem;
}

.sidebar-tip, .sidebar-stat, .sidebar-mystat, .sidebar-sla, .sidebar-faq {
    background: #fff;
    border: 1px solid rgba(99,102,241,.1);
    border-radius: 18px;
    padding: 1.1rem;
    box-shadow: 0 4px 18px rgba(15,23,42,.05);
}

.sidebar-tip h4, .sidebar-mystat h4, .sidebar-sla h4, .sidebar-faq h4 {
    font-size: .8rem;
    font-weight: 800;
    color: #6366f1;
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: .85rem;
    display: flex;
    align-items: center;
    gap: .4rem;
}
.sidebar-tip h4 i, .sidebar-mystat h4 i, .sidebar-sla h4 i, .sidebar-faq h4 i { font-size: 1rem; }

.sidebar-tip-icon {
    width: 40px; height: 40px;
    background: #eef2ff;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; color: #6366f1;
    margin-bottom: .75rem;
}

.sidebar-tip ul { list-style: none; display: flex; flex-direction: column; gap: .55rem; }
.sidebar-tip ul li {
    display: flex; align-items: flex-start; gap: .5rem;
    font-size: .78rem; color: #475569; line-height: 1.45;
}
.sidebar-tip ul li i { color: #22c55e; font-size: .85rem; margin-top: .1rem; flex-shrink: 0; }

.stat-row {
    display: flex; align-items: center; gap: .65rem;
    padding: .55rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.stat-row:last-child { border-bottom: none; padding-bottom: 0; }
.stat-row i { font-size: 1.2rem; color: #6366f1; flex-shrink: 0; }
.stat-row strong { display: block; font-size: .75rem; font-weight: 700; color: #1e293b; }
.stat-row span { font-size: .7rem; color: #94a3b8; }

/* ══════════════════════════════════════════
   MY TICKET STATS
══════════════════════════════════════════ */
.mystat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .5rem;
    margin-bottom: .875rem;
}
.mystat-item {
    border-radius: 10px;
    padding: .6rem .4rem;
    text-align: center;
}
.mystat-total  { background: #eef2ff; }
.mystat-open   { background: #fff7ed; }
.mystat-done   { background: #f0fdf4; }
.mystat-num {
    display: block;
    font-size: 1.4rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: .2rem;
}
.mystat-total .mystat-num  { color: #4338ca; }
.mystat-open  .mystat-num  { color: #c2410c; }
.mystat-done  .mystat-num  { color: #15803d; }
.mystat-lbl {
    display: block;
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.mystat-total .mystat-lbl  { color: #6366f1; }
.mystat-open  .mystat-lbl  { color: #ea580c; }
.mystat-done  .mystat-lbl  { color: #16a34a; }

/* SLA progress bar */
.sla-bar-wrap { margin-bottom: .75rem; }
.sla-bar-label {
    display: flex; justify-content: space-between; align-items: center;
    font-size: .72rem; color: #64748b; margin-bottom: .3rem;
}
.sla-bar-label strong { color: #1e293b; font-weight: 800; }
.sla-bar-track {
    height: 5px; background: #e5e7eb; border-radius: 3px; overflow: hidden;
}
.sla-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #6366f1, #22c55e);
    border-radius: 3px;
    transition: width .4s ease;
}

/* Priority alert */
.prio-alert {
    display: flex; align-items: flex-start; gap: .5rem;
    padding: .6rem .75rem;
    background: #fff7ed; border: 1px solid #fed7aa;
    border-radius: 8px;
    font-size: .75rem; color: #92400e; line-height: 1.45;
}
.prio-alert i { font-size: 1rem; color: #ea580c; flex-shrink: 0; margin-top: .05rem; }
.prio-alert strong { font-weight: 800; }

/* ══════════════════════════════════════════
   SLA TABLE
══════════════════════════════════════════ */
.sla-item {
    display: flex; align-items: flex-start; gap: .6rem;
    padding: .5rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.sla-item:last-child { border-bottom: none; padding-bottom: 0; }
.sla-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: .3rem;
}
.sla-dot-urgent { background: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.2); }
.sla-dot-high   { background: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,.2); }
.sla-dot-medium { background: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.2); }
.sla-dot-low    { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.2); }
.sla-meta { display: flex; flex-direction: column; gap: .1rem; }
.sla-prio { font-size: .75rem; font-weight: 700; color: #1e293b; }
.sla-time { font-size: .68rem; color: #94a3b8; }

/* FAQ */
.faq-item { margin-bottom: .75rem; }
.faq-item:last-child { margin-bottom: 0; }
.faq-item strong { display: block; font-size: .75rem; font-weight: 700; color: #1e293b; margin-bottom: .2rem; }
.faq-item p { font-size: .72rem; color: #64748b; line-height: 1.45; margin: 0; }

/* ══════════════════════════════════════════
   MAIN
══════════════════════════════════════════ */
.ct-main { min-width: 0; }

/* BACK BUTTON */
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

/* CARD */
.ct-card {
    background: #fff;
    border-radius: 20px;
    border: 1px solid rgba(99,102,241,.1);
    box-shadow: 0 4px 24px rgba(0,0,0,.06);
    overflow: hidden;
}

/* ══════════════════════════════════════════
   HEADER — no gap at top, flush to card edge
══════════════════════════════════════════ */
.ct-head {
    padding: 1.75rem 2rem;
    background: linear-gradient(135deg, #6366f1 0%, #7c3aed 100%);
    position: relative;
    overflow: hidden;
    margin: 0;          /* ← hapus semua margin bawaan */
}
.ct-head-deco {
    position: absolute;
    top: -50px; right: -50px;
    width: 180px; height: 180px;
    background: rgba(255,255,255,.07);
    border-radius: 50%;
    pointer-events: none;
}
.ct-head-deco::after {
    content: '';
    position: absolute;
    bottom: -80px; left: -80px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.05);
    border-radius: 50%;
}
.ct-head-inner { display: flex; align-items: center; gap: 1rem; position: relative; z-index: 1; }
.ct-head-icon {
    width: 52px; height: 52px;
    background: rgba(255,255,255,.18);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; color: #fff; flex-shrink: 0;
    border: 1px solid rgba(255,255,255,.2);
}
.ct-head h2 { color: #fff; font-size: 1.3rem; font-weight: 700; margin-bottom: .25rem; }
.ct-head p  { color: rgba(255,255,255,.85); font-size: .875rem; margin: 0; }

/* BODY */
.ct-body { padding: 1.75rem 2rem; }

/* FIELD */
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
    text-transform: none; letter-spacing: 0;
}

/* SELECT WRAPPER */
.select-wrap { position: relative; }
.select-wrap .ct-select { padding-right: 2.5rem; }
.select-icon {
    position: absolute; right: .9rem; top: 50%;
    transform: translateY(-50%);
    color: #6366f1; font-size: 1.1rem;
    pointer-events: none;
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
    font-family: inherit; line-height: 1.6; transition: all .25s;
}
.ct-textarea:focus {
    outline: none; border-color: #6366f1;
    background: #fff; box-shadow: 0 0 0 3px rgba(99,102,241,.1);
}
.ct-textarea.is-err { border-color: #ef4444; background: #fef2f2; }
.char-row { display: flex; justify-content: flex-end; font-size: .75rem; color: #9ca3af; margin-top: .35rem; }

/* ERROR */
.field-err {
    display: none; align-items: center; gap: .35rem;
    color: #ef4444; font-size: .8rem; font-weight: 600; margin-top: .4rem;
}
.field-err i { font-size: .95rem; }

/* URGENCY NOTE */
.urg-note {
    display: flex; gap: .75rem;
    padding: .875rem 1rem;
    background: #fef3c7; border: 1px solid #fbbf24;
    border-radius: 10px; margin-bottom: 1rem;
    font-size: .875rem; color: #78350f; line-height: 1.5;
}
.urg-note i { font-size: 1.2rem; color: #92400e; flex-shrink: 0; margin-top: .1rem; }

/* URGENCY GRID */
.urg-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .75rem; }
.urg-btn {
    padding: .875rem .75rem;
    border: 1.5px solid #e5e7eb; border-radius: 10px;
    background: #f9fafb; cursor: pointer;
    transition: all .2s; text-align: left; user-select: none;
}
.urg-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.06); }
.urg-icon { font-size: 1.25rem; margin-bottom: .35rem; }
.urg-icon i { font-size: 1.28rem; color: #0f172a; }
.urg-name  { font-weight: 700; font-size: .8rem; color: #1e293b; margin-bottom: .2rem; }
.urg-desc  { font-size: .72rem; color: #6b7280; line-height: 1.4; }

.urg-btn.urg-low.active    { background: #dcfce7; border-color: #22c55e; }
.urg-btn.urg-low.active .urg-icon i,
.urg-btn.urg-low.active .urg-name,
.urg-btn.urg-low.active .urg-desc { color: #166534; }

.urg-btn.urg-medium.active { background: #fef3c7; border-color: #f59e0b; }
.urg-btn.urg-medium.active .urg-icon i,
.urg-btn.urg-medium.active .urg-name,
.urg-btn.urg-medium.active .urg-desc { color: #92400e; }

.urg-btn.urg-high.active   { background: #ffedd5; border-color: #fb923c; }
.urg-btn.urg-high.active .urg-icon i,
.urg-btn.urg-high.active .urg-name,
.urg-btn.urg-high.active .urg-desc { color: #9a3412; }

.urg-btn.urg-critical.active { background: #fee2e2; border-color: #ef4444; }
.urg-btn.urg-critical.active .urg-icon i,
.urg-btn.urg-critical.active .urg-name,
.urg-btn.urg-critical.active .urg-desc { color: #991b1b; }

/* DIVIDER */
.ct-divider { height: 1px; background: #f0f0f0; margin: 1.5rem 0; }

/* COLLAPSIBLE EVENT */
.coll-trigger {
    display: flex; justify-content: space-between; align-items: center;
    padding: .75rem 1rem; margin: 0 -1rem;
    border-radius: 10px; cursor: pointer; transition: background .2s;
}
.coll-trigger:hover { background: #f8fafc; }
.coll-left { display: flex; align-items: center; gap: .6rem; font-weight: 700; font-size: .9rem; color: #1e293b; }
.coll-left i { color: #6366f1; font-size: 1.15rem; }
.coll-arrow { font-size: 1.1rem; color: #6366f1; transition: transform .25s; }
.coll-arrow.open { transform: rotate(180deg); }
.event-grid {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: .875rem; padding: 1rem 0 .25rem;
}
.event-grid input { padding: .75rem 1rem; font-size: .9rem; }

/* UPLOAD */
.upload-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.upload-title { display: flex; align-items: center; gap: .6rem; font-weight: 700; font-size: .9rem; color: #1e293b; }
.upload-title i { color: #6366f1; font-size: 1.15rem; }
.upload-limit { font-size: .8rem; color: #9ca3af; }

.drop-zone {
    border: 2px dashed #cbd5e1; border-radius: 14px;
    padding: 2.25rem 1.5rem; text-align: center;
    cursor: pointer; background: #f9fafb; transition: all .25s;
}
.drop-zone:hover, .drop-zone.dragover { border-color: #6366f1; background: #eef2ff; transform: translateY(-2px); }
.drop-zone.disabled { opacity: .45; pointer-events: none; }
.drop-circle {
    width: 56px; height: 56px; background: #eef2ff; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem; color: #6366f1; margin: 0 auto .875rem;
}
.drop-zone h6 { font-size: .95rem; font-weight: 700; color: #1e293b; margin-bottom: .3rem; }
.drop-zone p  { font-size: .8rem; color: #64748b; margin: 0; }

.upload-progress {
    display: flex; align-items: center; gap: .75rem;
    margin-top: .75rem; font-size: .8rem; color: #64748b;
}
.progress-bar  { flex: 1; height: 4px; background: #e5e7eb; border-radius: 2px; overflow: hidden; }
.progress-fill { height: 100%; background: linear-gradient(90deg, #6366f1, #7c3aed); transition: width .25s; }

.file-list { margin-top: .875rem; display: flex; flex-direction: column; gap: .5rem; }
.file-item {
    display: flex; align-items: center; gap: .75rem;
    padding: .625rem .875rem;
    background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px;
    animation: fadeIn .2s ease;
}
@keyframes fadeIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:translateY(0); } }
.file-thumb { width:40px; height:40px; border-radius:8px; object-fit:cover; border:1px solid #e5e7eb; flex-shrink:0; }
.file-icon { width:40px; height:40px; border-radius:8px; background:#eef2ff; display:flex; align-items:center; justify-content:center; color:#6366f1; font-size:1.2rem; flex-shrink:0; }
.file-meta { flex:1; min-width:0; }
.file-name { font-size:.85rem; font-weight:600; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.file-size { font-size:.75rem; color:#9ca3af; margin-top:.1rem; }
.file-rm { width:28px; height:28px; border-radius:7px; border:none; background:none; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:1rem; transition:all .2s; flex-shrink:0; }
.file-rm:hover { background:#fee2e2; color:#ef4444; }

/* ACTIONS */
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
    cursor: pointer; text-decoration: none; transition: all .2s;
}
.btn-cancel:hover { border-color: #cbd5e1; color: #1e293b; transform: translateY(-1px); }

.btn-submit {
    display: flex; align-items: center; gap: .45rem;
    padding: .8rem 1.75rem;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: #fff; border: none; border-radius: 10px;
    font-weight: 700; font-size: .875rem; cursor: pointer;
    box-shadow: 0 4px 14px rgba(99,102,241,.35); transition: all .2s;
}
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(99,102,241,.45); }
.btn-submit:disabled { opacity: .65; cursor: not-allowed; transform: none; }

/* ══════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════ */
@media (max-width: 1200px) {
    .ct-page { grid-template-columns: 190px minmax(0,1fr) 190px; }
}
@media (max-width: 1024px) {
    .ct-page { grid-template-columns: 1fr; }
    .ct-sidebar { position: static; flex-direction: row; flex-wrap: wrap; }
    .ct-sidebar--left, .ct-sidebar--right { display: contents; }
    .sidebar-tip, .sidebar-stat, .sidebar-mystat, .sidebar-sla, .sidebar-faq { flex: 1 1 240px; }
}
@media (max-width: 768px) {
    .ct-page { padding: 0; gap: 1rem; }
    .ct-head { padding: 1.25rem; }
    .ct-head-inner { flex-direction: column; text-align: center; }
    .ct-body { padding: 1.25rem; }
    .urg-grid { grid-template-columns: repeat(2, 1fr); }
    .event-grid { grid-template-columns: 1fr; }
    .ct-actions { flex-direction: column; padding: 1rem 1.25rem 1.5rem; }
    .btn-cancel, .btn-submit { width: 100%; justify-content: center; }
    .ct-sidebar { display: none; }
}
</style>
@endpush
