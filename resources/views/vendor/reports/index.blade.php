@extends('layouts.app')

@section('title', 'Laporan Vendor')
@section('page_title', 'Laporan Vendor')
@section('breadcrumb', 'Home / Laporan')



@section('content')
<div class="reports-page">

    {{-- ═══ HERO ═══ --}}
    <section class="rp-hero">
        <div>
            <h1>Laporan Vendor</h1>
            <p>Pantau performa tiket dengan tampilan yang lebih ringkas dan nyaman.</p>
        </div>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
            <div class="period-switcher">
                <button class="ps-btn {{ $periodType==='weekly' ? 'active' : '' }}" onclick="switchPeriod('weekly')">
                    <i class='bx bx-calendar'></i> Mingguan
                </button>
                <button class="ps-btn {{ $periodType==='monthly' ? 'active' : '' }}" onclick="switchPeriod('monthly')">
                    <i class='bx bx-calendar-alt'></i> Bulanan
                </button>
            </div>
            <a href="{{ route('vendor.reports') }}" class="btn-cancel" style="text-decoration:none;display:inline-flex;align-items:center;gap:.4rem">
                <i class='bx bx-refresh'></i> Muat Ulang
            </a>
        </div>
    </section>

    {{-- ═══ GUIDE ═══ --}}
    <div class="guide-card">
        <h6><i class='bx bx-info-circle'></i> Alur Vendor Menyelesaikan Laporan dari Admin/Client</h6>
        <ol class="guide-list">
            <li>Buka menu <strong>Tiket Ditugaskan</strong> lalu pilih tiket dari admin.</li>
            <li>Ubah status tiket ke <strong>Diproses</strong> saat pekerjaan mulai ditangani.</li>
            <li>Jika data belum lengkap, ubah status ke <strong>Menunggu Respons</strong>.</li>
            <li>Setelah pekerjaan selesai, klik <strong>Lapor Selesai</strong> agar status menjadi <strong>Selesai</strong>.</li>
            <li>Data performa otomatis masuk ke halaman <strong>Laporan Vendor</strong> (mingguan/bulanan).</li>
            <li>Gunakan menu <strong>Detail</strong> pada riwayat laporan untuk rekap prioritas, kategori, SLA, dan waktu respons.</li>
        </ol>
    </div>

    {{-- ═══ CURRENT PERIOD ═══ --}}
    @if($currentReport)
    <div class="current-card">
        <div class="current-card__header">
            <div>
                <h5><i class='bx bx-trending-up me-2'></i>Performa {{ $periodType==='weekly' ? 'Minggu' : 'Bulan' }} Ini</h5>
                <small>{{ \Carbon\Carbon::parse($currentReport->period_start)->format('d M Y') }} – {{ \Carbon\Carbon::parse($currentReport->period_end)->format('d M Y') }}</small>
            </div>
            <span class="badge-active">Periode Aktif</span>
        </div>
        <div class="current-card__body">
            {{-- Stat Mini Cards --}}
            <div class="stat-mini-grid">
                <div class="stat-mini sm-primary">
                    <div class="sm-icon primary"><i class='bx bx-file'></i></div>
                    <div>
                        <div class="sm-label">Total Tiket</div>
                        <div class="sm-value">{{ $currentReport->total_tickets }}</div>
                    </div>
                </div>
                <div class="stat-mini sm-success">
                    <div class="sm-icon success"><i class='bx bx-check-circle'></i></div>
                    <div>
                        <div class="sm-label">Selesai</div>
                        <div class="sm-value">{{ $currentReport->resolved_tickets }}</div>
                    </div>
                </div>
                <div class="stat-mini sm-warning">
                    <div class="sm-icon warning"><i class='bx bx-time'></i></div>
                    <div>
                        <div class="sm-label">Rata-rata Respons</div>
                        <div class="sm-value">{{ $currentReport->avg_response_time ?? 0 }}<span class="sm-unit">m</span></div>
                    </div>
                </div>
                <div class="stat-mini sm-info">
                    <div class="sm-icon info"><i class='bx bx-chart'></i></div>
                    <div>
                        <div class="sm-label">Kepatuhan SLA</div>
                        <div class="sm-value">{{ $currentReport->sla_compliance_rate ?? 0 }}<span class="sm-unit">%</span></div>
                    </div>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="chart-row">
                <div class="chart-inner">
                    <h6><i class='bx bx-flag'></i> Tiket Berdasarkan Prioritas</h6>
                    <div class="chart-wrap"><canvas id="priorityChart"></canvas></div>
                </div>
                <div class="chart-inner">
                    <h6><i class='bx bx-category'></i> Tiket Berdasarkan Kategori</h6>
                    <div class="chart-wrap"><canvas id="categoryChart"></canvas></div>
                </div>
            </div>

            {{-- Trend Line Chart --}}
            <div class="line-chart-card">
                <h6><i class='bx bx-line-chart'></i> Tren Performa 6 Bulan Terakhir</h6>
                <div class="line-chart-wrap"><canvas id="trendChart"></canvas></div>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══ HISTORY TABLE ═══ --}}
    <div class="history-card">
        <div class="hc-header">
            <i class='bx bx-history'></i>
            <h5>Riwayat Laporan</h5>
        </div>

        @if($reports->isEmpty())
        <div class="state-box">
            <i class='bx bx-folder-open'></i>
            <p>Belum ada riwayat laporan</p>
        </div>
        @else
        <div style="overflow-x:auto">
            <table class="rp-table">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Selesai</th>
                        <th class="text-center">Menunggu</th>
                        <th class="text-center">Rata-rata Respons</th>
                        <th class="text-center">Rata-rata Penyelesaian</th>
                        <th class="text-center">Kepatuhan SLA</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td>
                            <div class="period-main">{{ \Carbon\Carbon::parse($report->period_start)->format('d M Y') }}</div>
                            <div class="period-sub">sampai {{ \Carbon\Carbon::parse($report->period_end)->format('d M Y') }}</div>
                        </td>
                        <td class="text-center"><strong>{{ $report->total_tickets }}</strong></td>
                        <td class="text-center"><span class="badge-pill bp-success">{{ $report->resolved_tickets }}</span></td>
                        <td class="text-center"><span class="badge-pill bp-warning">{{ $report->pending_tickets }}</span></td>
                        <td class="text-center"><span style="color:#64748b">{{ $report->avg_response_time ?? '-' }}m</span></td>
                        <td class="text-center"><span style="color:#64748b">{{ $report->avg_resolution_time ?? '-' }}m</span></td>
                        <td class="text-center">
                            @php $sla = $report->sla_compliance_rate ?? 0; @endphp
                            <span class="{{ $sla >= 80 ? 'sla-good' : ($sla >= 60 ? 'sla-warn' : 'sla-bad') }}">{{ $sla }}%</span>
                        </td>
                        <td class="text-center">
                            <button class="btn-detail-sm" onclick="openModal({{ $report->id }})">
                                <i class='bx bx-show'></i> Detail
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

{{-- ═══ MODAL ═══ --}}
<div class="modal-overlay" id="reportModal" style="display:none" onclick="closeModalOnOverlay(event)">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h5><i class='bx bx-bar-chart-alt-2 me-2'></i>Detail Laporan</h5>
                <small id="modalPeriod">—</small>
            </div>
            <button class="btn-close-modal" onclick="closeModal()"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body" id="modalBody">
            <div class="state-box"><div class="spinner-border text-primary" role="status"></div><p>Memuat...</p></div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" onclick="closeModal()">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ── Chart Data from PHP ─────────────────────────────────────
const priorityData   = @json($currentReport->tickets_by_priority ?? []);
const categoryData   = @json($currentReport->tickets_by_category ?? []);
const trendLineData  = @json($monthlyPerformance);

const COLORS = ['#6366f1','#16a34a','#f59e0b','#ef4444','#0ea5e9','#8b5cf6','#ec4899','#14b8a6'];

function buildDonut(id, labels, values) {
    const ctx = document.getElementById(id);
    if (!ctx) return null;
    return new Chart(ctx, {
        type:'doughnut',
        data:{
            labels,
            datasets:[{ data:values, backgroundColor:COLORS.slice(0, values.length), borderWidth:0, hoverOffset:4 }]
        },
        options:{ responsive:true, maintainAspectRatio:false, cutout:'65%', plugins:{ legend:{ position:'right', labels:{ font:{size:11}, boxWidth:10, padding:10 } } } },
    });
}

// Priority Chart
@if($currentReport && count($currentReport->tickets_by_priority ?? []))
buildDonut('priorityChart',
    Object.keys(priorityData).map(k => ({ low:'Rendah', medium:'Sedang', high:'Tinggi', urgent:'Mendesak', critical:'Kritis' }[k] || k)),
    Object.values(priorityData)
);
@endif

// Category Chart
@if($currentReport && count($currentReport->tickets_by_category ?? []))
buildDonut('categoryChart', Object.keys(categoryData), Object.values(categoryData));
@endif

// Trend Line
(function(){
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;
    new Chart(ctx, {
        type:'line',
        data:{
            labels: trendLineData.map(m => m.month),
            datasets:[{
                label:'Resolved', data: trendLineData.map(m => m.resolved),
                borderColor:'#16a34a', backgroundColor:'rgba(22,163,74,.08)',
                borderWidth:2, pointRadius:4, pointBackgroundColor:'#16a34a', tension:.35, fill:true,
            }],
        },
        options:{
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false }, tooltip:{ mode:'index', intersect:false } },
            scales:{
                x:{ grid:{ display:false }, ticks:{ font:{size:11}, color:'#888' } },
                y:{ beginAtZero:true, ticks:{ font:{size:11}, color:'#888', precision:0 }, border:{ display:false } },
            },
        },
    });
})();

// ── Period Switch ────────────────────────────────────────────
function switchPeriod(p) {
    window.location.href = '{{ route("vendor.reports") }}?period_type=' + p;
}

// ── Modal ────────────────────────────────────────────────────
let modalPriorityChart = null, modalCategoryChart = null;
const reportsJson = @json($reports->items());

function openModal(id) {
    const report = reportsJson.find(r => r.id == id);
    if (!report) return;

    document.getElementById('reportModal').style.display = 'flex';
    document.getElementById('modalPeriod').textContent =
        `${formatDate(report.period_start)} – ${formatDate(report.period_end)}`;

    const priLabels = Object.keys(report.tickets_by_priority || {}).map(k => ({
        low:'Rendah', medium:'Sedang', high:'Tinggi', urgent:'Mendesak', critical:'Kritis'
    }[k] || k));
    const priValues = Object.values(report.tickets_by_priority || {});
    const catLabels = Object.keys(report.tickets_by_category || {});
    const catValues = Object.values(report.tickets_by_category || {});

    document.getElementById('modalBody').innerHTML = `
        <div class="modal-stat-grid">
            <div class="modal-stat"><i class='bx bx-file' style="color:#6366f1"></i><h4>${report.total_tickets}</h4><small>Total Tiket</small></div>
            <div class="modal-stat"><i class='bx bx-check-circle' style="color:#16a34a"></i><h4>${report.resolved_tickets}</h4><small>Selesai</small></div>
            <div class="modal-stat"><i class='bx bx-time' style="color:#f59e0b"></i><h4>${report.pending_tickets}</h4><small>Menunggu</small></div>
            <div class="modal-stat"><i class='bx bx-chart' style="color:#0ea5e9"></i><h4>${report.sla_compliance_rate ?? 0}%</h4><small>Kepatuhan SLA</small></div>
        </div>
        <div class="modal-chart-row">
            <div class="modal-chart-inner"><h6>Distribusi Prioritas</h6><div class="modal-chart-wrap"><canvas id="mPriorityChart"></canvas></div></div>
            <div class="modal-chart-inner"><h6>Distribusi Kategori</h6><div class="modal-chart-wrap"><canvas id="mCategoryChart"></canvas></div></div>
        </div>
        <div class="modal-chart-inner" style="background:#f8fafc">
            <h6 style="font-size:.85rem;font-weight:800;color:#0f172a;margin:0 0 .75rem;display:flex;align-items:center;gap:.4rem">
                <i class='bx bx-timer' style="color:#6366f1"></i> Ringkasan Kinerja
            </h6>
            <div class="modal-perf-row">
                <div class="perf-item"><span><i class='bx bx-time-five'></i>Rata-rata Waktu Respons</span><strong>${report.avg_response_time ?? '-'} menit</strong></div>
                <div class="perf-item"><span><i class='bx bx-check-double'></i>Rata-rata Waktu Penyelesaian</span><strong>${report.avg_resolution_time ?? '-'} menit</strong></div>
            </div>
        </div>
    `;

    if (priValues.length) buildDonut('mPriorityChart', priLabels, priValues);
    if (catValues.length) buildDonut('mCategoryChart', catLabels, catValues);
}

function closeModal() { document.getElementById('reportModal').style.display = 'none'; }
function closeModalOnOverlay(e) { if (e.target === document.getElementById('reportModal')) closeModal(); }
function formatDate(d) {
    return new Date(d).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' });
}
</script>
@endpush

@push('styles')
<style>
/* ══════════════════════════════════════
   VENDOR REPORTS — BLADE
══════════════════════════════════════ */
.reports-page { display:flex; flex-direction:column; gap:1.5rem; }

/* ── HERO ── */
.rp-hero {
    display:grid; grid-template-columns:1fr auto; gap:1rem;
    align-items:center; padding:1.5rem; border-radius:28px;
    background:linear-gradient(135deg,#eef2ff 0%,#fff 55%,#f0fdf4 100%);
    border:1px solid rgba(99,102,241,.1);
    box-shadow:0 18px 40px rgba(15,23,42,.05);
}
.rp-hero h1 { margin:.35rem 0 .25rem; font-size:clamp(1.5rem,2.5vw,2rem); font-weight:800; color:#0f172a; }
.rp-hero p  { margin:0; color:#64748b; }
.period-switcher { display:inline-flex; background:#f8fafc; border-radius:14px; padding:.25rem; border:1px solid rgba(148,163,184,.18); gap:.2rem; }
.ps-btn {
    padding:.6rem 1.25rem; border:none; border-radius:11px; font-size:.85rem; font-weight:700;
    color:#64748b; background:transparent; cursor:pointer; transition:all .2s;
    display:inline-flex; align-items:center; gap:.4rem;
}
.ps-btn.active { background:#fff; color:#4f46e5; box-shadow:0 4px 12px rgba(15,23,42,.1); }

/* ── GUIDE CARD ── */
.guide-card {
    padding:1.35rem; border-radius:22px;
    background:linear-gradient(135deg,#f8fbff,#f6fffb);
    border:1px solid rgba(148,163,184,.2);
    box-shadow:0 10px 24px rgba(15,23,42,.04);
}
.guide-card h6 { font-weight:800; color:#0f172a; margin:0 0 .75rem; display:flex; align-items:center; gap:.5rem; }
.guide-card h6 i { color:#6366f1; }
.guide-list { padding-left:1.1rem; display:grid; gap:.35rem; margin:0; color:#475569; font-size:.9rem; }
.guide-list li { padding:.1rem 0; }
.guide-list strong { color:#0f172a; }

/* ── CURRENT PERIOD CARD ── */
.current-card {
    background:#fff; border-radius:24px;
    border:1px solid rgba(99,102,241,.12);
    box-shadow:0 18px 40px rgba(15,23,42,.06); overflow:hidden;
}
.current-card__header {
    padding:1.25rem 1.5rem;
    background:linear-gradient(180deg,rgba(248,250,252,.88),rgba(255,255,255,.98));
    border-bottom:1px solid rgba(148,163,184,.1);
    display:flex; justify-content:space-between; align-items:center;
}
.current-card__header h5 { font-size:1rem; font-weight:800; color:#0f172a; margin:0 0 .2rem; }
.current-card__header small { color:#64748b; }
.badge-active {
    padding:.45rem 1rem; border-radius:999px;
    background:#eef2ff; color:#4338ca; font-size:.78rem; font-weight:800;
}
.current-card__body { padding:1.5rem; display:flex; flex-direction:column; gap:1.5rem; }

/* ── STAT MINI CARDS ── */
.stat-mini-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; }
.stat-mini {
    padding:1rem 1.15rem; border-radius:18px; background:#fff;
    border:1px solid rgba(99,102,241,.1); box-shadow:0 8px 20px rgba(15,23,42,.05);
    display:flex; align-items:center; gap:.9rem; position:relative; overflow:hidden; transition:all .25s;
}
.stat-mini:hover { transform:translateY(-2px); box-shadow:0 14px 30px rgba(79,70,229,.08); }
.stat-mini::before { content:''; position:absolute; inset:0 auto 0 0; width:5px; border-radius:4px 0 0 4px; }
.sm-primary::before { background:linear-gradient(180deg,#6366f1,#4f46e5); }
.sm-success::before { background:linear-gradient(180deg,#22c55e,#16a34a); }
.sm-warning::before { background:linear-gradient(180deg,#f59e0b,#ea580c); }
.sm-info::before    { background:linear-gradient(180deg,#0ea5e9,#0284c7); }
.sm-icon {
    width:42px; height:42px; border-radius:12px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:1.2rem;
}
.sm-icon.primary { background:rgba(99,102,241,.1); color:#6366f1; }
.sm-icon.success { background:rgba(34,197,94,.1);  color:#16a34a; }
.sm-icon.warning { background:rgba(245,158,11,.1); color:#d97706; }
.sm-icon.info    { background:rgba(14,165,233,.1);  color:#0284c7; }
.sm-label { font-size:.78rem; color:#64748b; font-weight:600; }
.sm-value { font-size:1.6rem; font-weight:800; color:#0f172a; line-height:1.1; }
.sm-unit  { font-size:.85rem; font-weight:600; color:#94a3b8; }

/* ── CHART ROW ── */
.chart-row { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; }
.chart-inner {
    padding:1.15rem; border-radius:18px;
    background:#f9fafb; border:1px solid rgba(148,163,184,.12);
}
.chart-inner h6 { font-size:.9rem; font-weight:800; color:#0f172a; margin:0 0 1rem; display:flex; align-items:center; gap:.4rem; }
.chart-inner h6 i { color:#6366f1; }
.chart-wrap { position:relative; height:260px; }

/* ── LINE CHART FULL ── */
.line-chart-card {
    padding:1.15rem; border-radius:18px;
    background:#f9fafb; border:1px solid rgba(148,163,184,.12);
}
.line-chart-card h6 { font-size:.9rem; font-weight:800; color:#0f172a; margin:0 0 1rem; display:flex; align-items:center; gap:.4rem; }
.line-chart-card h6 i { color:#16a34a; }
.line-chart-wrap { position:relative; height:300px; }

/* ── HISTORY TABLE CARD ── */
.history-card {
    background:#fff; border-radius:24px;
    border:1px solid rgba(99,102,241,.1);
    box-shadow:0 18px 40px rgba(15,23,42,.05); overflow:hidden;
}
.hc-header {
    padding:1.15rem 1.35rem;
    border-bottom:1px solid rgba(148,163,184,.1);
    background:linear-gradient(180deg,rgba(248,250,252,.88),rgba(255,255,255,.98));
    display:flex; align-items:center; gap:.6rem;
}
.hc-header i { font-size:1.3rem; color:#6366f1; }
.hc-header h5 { font-size:1rem; font-weight:800; color:#0f172a; margin:0; }

/* ── TABLE ── */
.rp-table { width:100%; border-collapse:collapse; }
.rp-table thead th {
    padding:.85rem 1rem; text-align:left; font-size:.72rem;
    font-weight:800; text-transform:uppercase; letter-spacing:.06em;
    color:#64748b; background:#f8fafc; border-bottom:2px solid #e2e8f0;
}
.rp-table thead th.text-center { text-align:center; }
.rp-table tbody td { padding:.9rem 1rem; border-bottom:1px solid rgba(148,163,184,.08); font-size:.88rem; color:#1e293b; }
.rp-table tbody td.text-center { text-align:center; }
.rp-table tbody tr:last-child td { border-bottom:none; }
.rp-table tbody tr:hover td { background:#f8fbff; }
.period-main { font-weight:700; color:#0f172a; }
.period-sub  { font-size:.75rem; color:#64748b; margin-top:.15rem; }
.badge-pill { display:inline-flex; align-items:center; padding:.35rem .75rem; border-radius:999px; font-size:.75rem; font-weight:800; }
.bp-success { background:rgba(34,197,94,.12);  color:#15803d; }
.bp-warning { background:rgba(245,158,11,.12); color:#b45309; }
.sla-good   { color:#15803d; font-weight:800; }
.sla-warn   { color:#b45309; font-weight:800; }
.sla-bad    { color:#b91c1c; font-weight:800; }
.btn-detail-sm {
    padding:.35rem .85rem; border:1.5px solid rgba(99,102,241,.3); border-radius:10px;
    font-size:.78rem; font-weight:700; color:#4f46e5; background:#fff;
    cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; gap:.35rem;
}
.btn-detail-sm:hover { background:#eef2ff; color:#4f46e5; }

/* ── EMPTY / LOADING ── */
.state-box { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:4rem 2rem; gap:1rem; text-align:center; color:#64748b; }
.state-box i { font-size:3rem; color:#cbd5e0; }
.state-box p { margin:0; }

/* ── MODAL ── */
.modal-overlay { position:fixed; inset:0; background:rgba(2,6,23,.55); backdrop-filter:blur(6px); z-index:9999; display:flex; align-items:center; justify-content:center; padding:1.5rem; }
.modal-box {
    background:#fff; border-radius:24px; width:100%; max-width:900px; max-height:90vh;
    overflow:hidden; display:flex; flex-direction:column;
    box-shadow:0 40px 80px rgba(15,23,42,.25);
}
.modal-head {
    padding:1.25rem 1.5rem;
    background:linear-gradient(180deg,rgba(248,250,252,.88),rgba(255,255,255,.98));
    border-bottom:1px solid rgba(148,163,184,.1);
    display:flex; justify-content:space-between; align-items:center;
}
.modal-head h5 { font-size:1rem; font-weight:800; color:#0f172a; margin:0 0 .15rem; }
.modal-head small { color:#64748b; }
.btn-close-modal {
    width:36px; height:36px; border:none; border-radius:10px; cursor:pointer;
    background:#f1f5f9; color:#64748b; font-size:1.1rem;
    display:flex; align-items:center; justify-content:center; transition:all .2s;
}
.btn-close-modal:hover { background:#e2e8f0; color:#0f172a; }
.modal-body { padding:1.5rem; overflow-y:auto; display:flex; flex-direction:column; gap:1.25rem; }
.modal-stat-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; }
.modal-stat {
    text-align:center; padding:1rem; border-radius:16px; background:#f8fafc;
    border:1px solid rgba(148,163,184,.12);
}
.modal-stat i { font-size:1.75rem; margin-bottom:.5rem; display:block; }
.modal-stat h4 { font-size:1.4rem; font-weight:800; color:#0f172a; margin:0 0 .2rem; }
.modal-stat small { color:#64748b; font-size:.78rem; }
.modal-chart-row { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; }
.modal-chart-inner { padding:1rem; border-radius:14px; background:#f9fafb; border:1px solid rgba(148,163,184,.12); }
.modal-chart-inner h6 { font-size:.85rem; font-weight:800; color:#0f172a; margin:0 0 .75rem; }
.modal-chart-wrap { position:relative; height:220px; }
.modal-perf-row { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
.perf-item {
    display:flex; justify-content:space-between; align-items:center;
    padding:.75rem 1rem; background:#fff; border-radius:12px;
    border:1px solid rgba(148,163,184,.12); font-size:.88rem;
}
.perf-item span { color:#64748b; display:flex; align-items:center; gap:.4rem; }
.perf-item strong { color:#0f172a; font-weight:800; }
.modal-foot { padding:1rem 1.5rem; border-top:1px solid rgba(148,163,184,.1); background:#f8fafc; display:flex; justify-content:flex-end; }
.btn-cancel {
    padding:.65rem 1.35rem; border:1.5px solid #e2e8f0; border-radius:12px;
    background:#fff; color:#64748b; font-weight:700; cursor:pointer; transition:all .2s;
}
.btn-cancel:hover { background:#f1f5f9; }

/* ── RESPONSIVE ── */
@media(max-width:1100px){ .stat-mini-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .chart-row { grid-template-columns:1fr; } }
@media(max-width:768px){
    .rp-hero { grid-template-columns:1fr; }
    .stat-mini-grid { grid-template-columns:1fr; }
    .chart-row { grid-template-columns:1fr; }
    .modal-stat-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .modal-chart-row { grid-template-columns:1fr; }
    .modal-perf-row  { grid-template-columns:1fr; }
}
</style>
@endpush
