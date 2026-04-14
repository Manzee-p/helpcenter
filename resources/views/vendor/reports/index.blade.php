@extends('layouts.app')

@section('title', 'Laporan Vendor')
@section('page_title', 'Laporan Vendor')
@section('breadcrumb', 'Home / Laporan')

@section('content')
<div class="rp-root">

    {{-- â•â•â• HEADER â•â•â• --}}
    <div class="rp-header">
        <div class="rp-header__left">
            <div class="rp-header__icon-wrap">
                <i class='bx bx-bar-chart-alt-2'></i>
            </div>
            <div>
                <h1 class="rp-header__title">Laporan Vendor</h1>
                <p class="rp-header__sub">Pantau performa tiket secara mingguan &amp; bulanan.</p>
            </div>
        </div>
        <div class="rp-header__actions">
            <div class="rp-period-switch">
                <button class="rp-ps-btn {{ $periodType==='weekly' ? 'is-active' : '' }}" onclick="switchPeriod('weekly')">
                    <i class='bx bx-calendar'></i> Mingguan
                </button>
                <button class="rp-ps-btn {{ $periodType==='monthly' ? 'is-active' : '' }}" onclick="switchPeriod('monthly')">
                    <i class='bx bx-calendar-alt'></i> Bulanan
                </button>
            </div>
            <a href="{{ route('vendor.reports') }}" class="rp-btn-refresh">
                <i class='bx bx-refresh'></i>
                Muat Ulang
            </a>
        </div>
    </div>

    {{-- â•â•â• CURRENT PERIOD â•â•â• --}}
    @if($currentReport)
    <div class="rp-section">

        {{-- Section title --}}
        <div class="rp-section-head">
            <div class="rp-section-head__left">
                <div class="rp-period-pill">
                    <i class='bx bx-radio-circle-marked'></i>
                    Periode Aktif
                </div>
                <h2 class="rp-section-head__title">
                    Performa {{ $periodType==='weekly' ? 'Minggu' : 'Bulan' }} Ini
                </h2>
                <span class="rp-section-head__range">
                    <i class='bx bx-calendar'></i>
                    {{ \Carbon\Carbon::parse($currentReport->period_start)->format('d M Y') }} -
                    {{ \Carbon\Carbon::parse($currentReport->period_end)->format('d M Y') }}
                </span>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="rp-kpi-grid">

            <div class="rp-kpi rp-kpi--blue">
                <div class="rp-kpi__bar"></div>
                <div class="rp-kpi__icon"><i class='bx bx-file-blank'></i></div>
                <div class="rp-kpi__body">
                    <div class="rp-kpi__label">Total Tiket</div>
                    <div class="rp-kpi__value">{{ $currentReport->total_tickets }}</div>
                    <div class="rp-kpi__hint">Periode ini</div>
                </div>
            </div>

            <div class="rp-kpi rp-kpi--green">
                <div class="rp-kpi__bar"></div>
                <div class="rp-kpi__icon"><i class='bx bx-check-circle'></i></div>
                <div class="rp-kpi__body">
                    <div class="rp-kpi__label">Selesai</div>
                    <div class="rp-kpi__value">{{ $currentReport->resolved_tickets }}</div>
                    <div class="rp-kpi__hint">Tiket diselesaikan</div>
                </div>
            </div>

            <div class="rp-kpi rp-kpi--amber">
                <div class="rp-kpi__bar"></div>
                <div class="rp-kpi__icon"><i class='bx bx-time-five'></i></div>
                <div class="rp-kpi__body">
                    <div class="rp-kpi__label">Rata-rata Respons</div>
                    <div class="rp-kpi__value">
                        {{ $currentReport->avg_response_time ?? 0 }}<small>m</small>
                    </div>
                    <div class="rp-kpi__hint">Waktu respons pertama</div>
                </div>
            </div>

            <div class="rp-kpi rp-kpi--teal">
                <div class="rp-kpi__bar"></div>
                <div class="rp-kpi__icon"><i class='bx bx-shield-quarter'></i></div>
                <div class="rp-kpi__body">
                    <div class="rp-kpi__label">Kepatuhan SLA</div>
                    <div class="rp-kpi__value">
                        {{ $currentReport->sla_compliance_rate ?? 0 }}<small>%</small>
                    </div>
                    <div class="rp-kpi__hint">Target pemenuhan SLA</div>
                </div>
            </div>

        </div>

        {{-- Charts Row --}}
        <div class="rp-charts-row">

            <div class="rp-chart-card">
                <div class="rp-chart-card__header">
                    <div class="rp-chart-card__title">
                        <i class='bx bx-flag'></i>
                        Berdasarkan Prioritas
                    </div>
                </div>
                <div class="rp-chart-card__body">
                    <div class="rp-donut-wrap" style="position:relative;height:220px">
                        <canvas id="priorityChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="rp-chart-card">
                <div class="rp-chart-card__header">
                    <div class="rp-chart-card__title">
                        <i class='bx bx-category'></i>
                        Berdasarkan Kategori
                    </div>
                </div>
                <div class="rp-chart-card__body">
                    <div style="position:relative;height:220px">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="rp-chart-card rp-chart-card--wide">
                <div class="rp-chart-card__header">
                    <div class="rp-chart-card__title">
                        <i class='bx bx-line-chart'></i>
                        Tren 6 Bulan Terakhir
                    </div>
                    <div class="rp-chart-legend">
                        <span class="rp-legend-dot" style="background:#639922"></span>
                        Resolved
                    </div>
                </div>
                <div class="rp-chart-card__body">
                    <div style="position:relative;height:200px">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>

        </div>

    </div>
    @endif

    {{-- â•â•â• HISTORY TABLE â•â•â• --}}
    <div class="rp-table-card">
        <div class="rp-table-card__header">
            <div class="rp-table-card__title">
                <i class='bx bx-history'></i>
                Riwayat Laporan
            </div>
            @if(!$reports->isEmpty())
            <span class="rp-table-count">{{ $reports->total() }} laporan</span>
            @endif
        </div>

        @if($reports->isEmpty())
        <div class="rp-empty">
            <div class="rp-empty__icon"><i class='bx bx-folder-open'></i></div>
            <p>Belum ada riwayat laporan</p>
        </div>
        @else
        <div class="rp-table-scroll">
            <table class="rp-table">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th class="tc">Total</th>
                        <th class="tc">Selesai</th>
                        <th class="tc">Menunggu</th>
                        <th class="tc">Avg Respons</th>
                        <th class="tc">Avg Selesai</th>
                        <th class="tc">SLA</th>
                        <th class="tc">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    @php $sla = $report->sla_compliance_rate ?? 0; @endphp
                    <tr>
                        <td>
                            <div class="rp-period-main">{{ \Carbon\Carbon::parse($report->period_start)->format('d M Y') }}</div>
                            <div class="rp-period-sub">s/d {{ \Carbon\Carbon::parse($report->period_end)->format('d M Y') }}</div>
                        </td>
                        <td class="tc">
                            <span class="rp-num-bold">{{ $report->total_tickets }}</span>
                        </td>
                        <td class="tc">
                            <span class="rp-pill rp-pill--green">{{ $report->resolved_tickets }}</span>
                        </td>
                        <td class="tc">
                            <span class="rp-pill rp-pill--amber">{{ $report->pending_tickets }}</span>
                        </td>
                        <td class="tc">
                            <span class="rp-muted">{{ $report->avg_response_time ?? '-' }}{{ $report->avg_response_time ? 'm' : '' }}</span>
                        </td>
                        <td class="tc">
                            <span class="rp-muted">{{ $report->avg_resolution_time ?? '-' }}{{ $report->avg_resolution_time ? 'm' : '' }}</span>
                        </td>
                        <td class="tc">
                            <span class="rp-sla {{ $sla >= 80 ? 'rp-sla--good' : ($sla >= 60 ? 'rp-sla--warn' : 'rp-sla--bad') }}">
                                {{ $sla }}%
                            </span>
                        </td>
                        <td class="tc">
                            <button class="rp-btn-detail" onclick="openModal({{ $report->id }})">
                                <i class='bx bx-bar-chart-alt-2'></i> Detail
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
        <div class="rp-pagination">
            {{ $reports->links() }}
        </div>
        @endif

        @endif
    </div>

</div>

{{-- â•â•â• MODAL â•â•â• --}}
<div class="rp-modal-overlay" id="reportModal" style="display:none" onclick="closeModalOnOverlay(event)">
    <div class="rp-modal">

        <div class="rp-modal__head">
            <div class="rp-modal__head-left">
                <div class="rp-modal__icon"><i class='bx bx-bar-chart-alt-2'></i></div>
                <div>
                    <div class="rp-modal__title">Detail Laporan</div>
                    <div class="rp-modal__period" id="modalPeriod">-</div>
                </div>
            </div>
            <button class="rp-modal__close" onclick="closeModal()">
                <i class='bx bx-x'></i>
            </button>
        </div>

        <div class="rp-modal__body" id="modalBody">
            <div class="rp-empty">
                <div class="rp-spinner"></div>
                <p>Memuat data...</p>
            </div>
        </div>

        <div class="rp-modal__foot">
            <button class="rp-btn-close" onclick="closeModal()">
                <i class='bx bx-x'></i> Tutup
            </button>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const priorityData  = @json($currentReport->tickets_by_priority ?? []);
const categoryData  = @json($currentReport->tickets_by_category ?? []);
const trendLineData = @json($monthlyPerformance);

const PALETTE = ['#378ADD','#639922','#BA7517','#E24B4A','#1D9E75','#8b5cf6','#ec4899','#14b8a6'];

const isDark = () => document.documentElement.classList.contains('dark')
    || matchMedia('(prefers-color-scheme:dark)').matches;

const chartTheme = () => ({
    grid: isDark() ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)',
    tick: isDark() ? '#9ca3af' : '#94a3b8',
});

function buildDonut(id, labels, values) {
    const ctx = document.getElementById(id);
    if (!ctx) return null;
    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: PALETTE.slice(0, values.length),
                borderWidth: 0,
                hoverOffset: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        font: { size: 11 },
                        boxWidth: 10,
                        padding: 10,
                        color: chartTheme().tick,
                    }
                },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#0f172a',
                    bodyColor: '#64748b',
                    borderColor: 'rgba(0,0,0,0.08)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8,
                },
            },
        },
    });
}

@if($currentReport && count($currentReport->tickets_by_priority ?? []))
buildDonut('priorityChart',
    Object.keys(priorityData).map(k => ({low:'Rendah',medium:'Sedang',high:'Tinggi',urgent:'Mendesak',critical:'Kritis'}[k] || k)),
    Object.values(priorityData)
);
@endif

@if($currentReport && count($currentReport->tickets_by_category ?? []))
buildDonut('categoryChart', Object.keys(categoryData), Object.values(categoryData));
@endif

/* Trend Line */
(function(){
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;
    const { grid, tick } = chartTheme();
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendLineData.map(m => m.month),
            datasets: [{
                label: 'Resolved',
                data: trendLineData.map(m => m.resolved),
                borderColor: '#639922',
                backgroundColor: ctx2 => {
                    const c = ctx2.chart.ctx;
                    const g = c.createLinearGradient(0, 0, 0, 200);
                    g.addColorStop(0, 'rgba(99,153,34,0.18)');
                    g.addColorStop(1, 'rgba(99,153,34,0)');
                    return g;
                },
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: '#639922',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                tension: 0.4,
                fill: true,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index', intersect: false,
                    backgroundColor: '#fff',
                    titleColor: '#0f172a',
                    bodyColor: '#64748b',
                    borderColor: 'rgba(0,0,0,0.08)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8,
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: tick, font: { size: 11 } },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: grid },
                    ticks: { color: tick, font: { size: 11 }, precision: 0 },
                    border: { display: false },
                },
            },
        },
    });
})();

/* Period Switch */
function switchPeriod(p) {
    window.location.href = '{{ route("vendor.reports") }}?period_type=' + p;
}

/* Modal */
const reportsJson = @json($reports->items());

function openModal(id) {
    const r = reportsJson.find(r => r.id == id);
    if (!r) return;

    document.getElementById('reportModal').style.display = 'flex';
    document.getElementById('modalPeriod').textContent =
        `${fmtDate(r.period_start)} - ${fmtDate(r.period_end)}`;

    const priLabels = Object.keys(r.tickets_by_priority || {})
        .map(k => ({low:'Rendah',medium:'Sedang',high:'Tinggi',urgent:'Mendesak',critical:'Kritis'}[k] || k));
    const priValues = Object.values(r.tickets_by_priority || {});
    const catLabels = Object.keys(r.tickets_by_category || {});
    const catValues = Object.values(r.tickets_by_category || {});

    const slaClass = (r.sla_compliance_rate ?? 0) >= 80 ? 'rp-sla--good' : (r.sla_compliance_rate ?? 0) >= 60 ? 'rp-sla--warn' : 'rp-sla--bad';

    document.getElementById('modalBody').innerHTML = `
        <div class="rp-modal-kpi">
            <div class="rp-modal-kpi__item rp-modal-kpi__item--blue">
                <i class='bx bx-file-blank'></i>
                <div class="rp-modal-kpi__val">${r.total_tickets}</div>
                <div class="rp-modal-kpi__lbl">Total Tiket</div>
            </div>
            <div class="rp-modal-kpi__item rp-modal-kpi__item--green">
                <i class='bx bx-check-circle'></i>
                <div class="rp-modal-kpi__val">${r.resolved_tickets}</div>
                <div class="rp-modal-kpi__lbl">Selesai</div>
            </div>
            <div class="rp-modal-kpi__item rp-modal-kpi__item--amber">
                <i class='bx bx-time-five'></i>
                <div class="rp-modal-kpi__val">${r.pending_tickets}</div>
                <div class="rp-modal-kpi__lbl">Menunggu</div>
            </div>
            <div class="rp-modal-kpi__item rp-modal-kpi__item--teal">
                <i class='bx bx-shield-quarter'></i>
                <div class="rp-modal-kpi__val rp-sla ${slaClass}">${r.sla_compliance_rate ?? 0}%</div>
                <div class="rp-modal-kpi__lbl">Kepatuhan SLA</div>
            </div>
        </div>

        <div class="rp-modal-charts">
            <div class="rp-modal-chart-card">
                <div class="rp-modal-chart-title"><i class='bx bx-flag'></i> Distribusi Prioritas</div>
                <div style="position:relative;height:200px"><canvas id="mPriorityChart"></canvas></div>
            </div>
            <div class="rp-modal-chart-card">
                <div class="rp-modal-chart-title"><i class='bx bx-category'></i> Distribusi Kategori</div>
                <div style="position:relative;height:200px"><canvas id="mCategoryChart"></canvas></div>
            </div>
        </div>

        <div class="rp-modal-perf">
            <div class="rp-modal-perf__title">
                <i class='bx bx-timer'></i> Ringkasan Kinerja
            </div>
            <div class="rp-modal-perf__grid">
                <div class="rp-modal-perf__item">
                    <div class="rp-modal-perf__lbl"><i class='bx bx-time-five'></i> Rata-rata Waktu Respons</div>
                    <div class="rp-modal-perf__val">${r.avg_response_time ?? '-'}${r.avg_response_time ? ' menit' : ''}</div>
                </div>
                <div class="rp-modal-perf__item">
                    <div class="rp-modal-perf__lbl"><i class='bx bx-check-double'></i> Rata-rata Waktu Penyelesaian</div>
                    <div class="rp-modal-perf__val">${r.avg_resolution_time ?? '-'}${r.avg_resolution_time ? ' menit' : ''}</div>
                </div>
            </div>
        </div>
    `;

    if (priValues.length) buildDonut('mPriorityChart', priLabels, priValues);
    if (catValues.length) buildDonut('mCategoryChart', catLabels, catValues);
}

function closeModal() { document.getElementById('reportModal').style.display = 'none'; }
function closeModalOnOverlay(e) { if (e.target === document.getElementById('reportModal')) closeModal(); }
function fmtDate(d) {
    return new Date(d).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' });
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
@endpush

@push('styles')
<style>
/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   VENDOR REPORTS - Redesign
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
*, *::before, *::after { box-sizing: border-box; }

.rp-root {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding-bottom: 2rem;
    /* FIX: Cegah horizontal overflow */
    width: 100%;
    min-width: 0;
    overflow-x: hidden;
}

/* â-€â-€ HEADER (style Riwayat Tiket - light) â-€â-€ */
.rp-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    background: #f0f4ff;           /* light lavender, sama seperti Riwayat Tiket */
    border: 1px solid #e2e8f0;
    border-radius: 14px;
}

.rp-header__left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.rp-header__icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #e8eeff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #4361ee;               /* biru indigo seperti icon Riwayat Tiket */
    flex-shrink: 0;
}

.rp-header__title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 3px 0;
    line-height: 1.2;
}
.rp-header__sub {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0;
}

.rp-header__actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Period Switch */
.rp-period-switch {
    display: inline-flex;
    gap: 2px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 3px;
}
.rp-ps-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 14px;
    border: none;
    border-radius: 7px;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    background: transparent;
    color: #64748b;
    transition: all 0.18s;
    font-family: inherit;
}
.rp-ps-btn.is-active {
    background: #4361ee;
    color: #fff;
    box-shadow: 0 2px 8px rgba(67,97,238,0.25);
}

.rp-btn-refresh {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #fff;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.18s;
}
.rp-btn-refresh:hover {
    background: #f1f5f9;
    color: #0f172a;
}

/* â-€â-€ CURRENT PERIOD SECTION â-€â-€ */
.rp-section {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.07);
    border-radius: 16px;
    overflow: hidden;
    /* FIX: Cegah overflow dari children */
    min-width: 0;
}

.rp-section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    background: #fafbfc;
}
.rp-section-head__left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.rp-period-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    background: #dcfce7;
    color: #16a34a;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    flex-shrink: 0;
}
.rp-section-head__title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}
.rp-section-head__range {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.75rem;
    color: #94a3b8;
    font-weight: 500;
}
.rp-section-head__range i { font-size: 13px; }

/* â-€â-€ KPI GRID â-€â-€ */
.rp-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0,1fr));
    gap: 0;
    border-bottom: 1px solid #f1f5f9;
}

.rp-kpi {
    position: relative;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: background 0.15s;
    border-right: 1px solid #f1f5f9;
    /* FIX: Cegah overflow teks */
    min-width: 0;
}
.rp-kpi:last-child { border-right: none; }
.rp-kpi:hover { background: #fafbfc; }

.rp-kpi__bar {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
}
.rp-kpi--blue  .rp-kpi__bar { background: linear-gradient(90deg, #378ADD, #85B7EB); }
.rp-kpi--green .rp-kpi__bar { background: linear-gradient(90deg, #639922, #97C459); }
.rp-kpi--amber .rp-kpi__bar { background: linear-gradient(90deg, #BA7517, #EF9F27); }
.rp-kpi--teal  .rp-kpi__bar { background: linear-gradient(90deg, #1D9E75, #5DCAA5); }

.rp-kpi__icon {
    width: 42px; height: 42px;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.rp-kpi--blue  .rp-kpi__icon { background: #E6F1FB; color: #185FA5; }
.rp-kpi--green .rp-kpi__icon { background: #EAF3DE; color: #3B6D11; }
.rp-kpi--amber .rp-kpi__icon { background: #FAEEDA; color: #854F0B; }
.rp-kpi--teal  .rp-kpi__icon { background: #E1F5EE; color: #0F6E56; }

.rp-kpi__body { min-width: 0; }

.rp-kpi__label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 3px;
}
.rp-kpi__value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1;
    letter-spacing: -0.02em;
    margin-bottom: 3px;
}
.rp-kpi__value small {
    font-size: 0.9rem;
    font-weight: 500;
    color: #64748b;
}
.rp-kpi__hint {
    font-size: 0.7rem;
    color: #94a3b8;
}

/* â-€â-€ CHARTS ROW â-€â-€ */
.rp-charts-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1.6fr;
    gap: 1px;
    background: #f1f5f9;
    /* FIX: Pastikan tidak overflow */
    min-width: 0;
}

.rp-chart-card {
    background: #fff;
    padding: 1.25rem;
    /* FIX: Cegah overflow canvas */
    min-width: 0;
    overflow: hidden;
}

.rp-chart-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.rp-chart-card__title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.82rem;
    font-weight: 700;
    color: #0f172a;
}
.rp-chart-card__title i { font-size: 14px; color: #64748b; }

.rp-chart-legend {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.72rem;
    color: #64748b;
}
.rp-legend-dot {
    width: 8px; height: 8px;
    border-radius: 2px;
    flex-shrink: 0;
}

/* â-€â-€ TABLE CARD â-€â-€ */
.rp-table-card {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.07);
    border-radius: 16px;
    overflow: hidden;
    /* FIX */
    min-width: 0;
}
.rp-table-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    background: #fafbfc;
}
.rp-table-card__title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 0.9rem;
    font-weight: 700;
    color: #0f172a;
}
.rp-table-card__title i { font-size: 16px; color: #64748b; }
.rp-table-count {
    font-size: 0.72rem;
    font-weight: 600;
    color: #94a3b8;
    background: #f1f5f9;
    padding: 2px 10px;
    border-radius: 999px;
}

.rp-table-scroll {
    overflow-x: auto;
    /* FIX: Batasi lebar scroll hanya di tabel, bukan halaman */
    max-width: 100%;
}

.rp-table {
    width: 100%;
    border-collapse: collapse;
    /* FIX: Pastikan tabel tidak paksa lebar melebihi container */
    table-layout: auto;
}
.rp-table thead th {
    padding: 10px 16px;
    text-align: left;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}
.rp-table thead th.tc { text-align: center; }
.rp-table tbody td {
    padding: 11px 16px;
    border-bottom: 1px solid #f8fafc;
    font-size: 0.85rem;
    color: #334155;
    vertical-align: middle;
}
.rp-table tbody td.tc { text-align: center; }
.rp-table tbody tr:last-child td { border-bottom: none; }
.rp-table tbody tr:hover td { background: #fafbfc; }

.rp-period-main { font-size: 0.85rem; font-weight: 600; color: #0f172a; }
.rp-period-sub  { font-size: 0.71rem; color: #94a3b8; margin-top: 2px; }

.rp-num-bold { font-weight: 700; color: #0f172a; font-size: 0.9rem; }
.rp-muted    { color: #64748b; font-size: 0.82rem; }

.rp-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
}
.rp-pill--green { background: #EAF3DE; color: #3B6D11; }
.rp-pill--amber { background: #FAEEDA; color: #854F0B; }

.rp-sla { font-weight: 700; font-size: 0.85rem; }
.rp-sla--good { color: #16a34a; }
.rp-sla--warn { color: #d97706; }
.rp-sla--bad  { color: #dc2626; }

.rp-btn-detail {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.76rem;
    font-weight: 600;
    color: #334155;
    background: #fff;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
    font-family: inherit;
}
.rp-btn-detail:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a;
}

.rp-pagination {
    padding: 1rem 1.5rem;
    border-top: 1px solid #f1f5f9;
}

/* â-€â-€ EMPTY STATE â-€â-€ */
.rp-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3.5rem 2rem;
    gap: 0.75rem;
    text-align: center;
}
.rp-empty__icon {
    width: 56px; height: 56px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px;
    color: #94a3b8;
}
.rp-empty p { font-size: 0.82rem; color: #94a3b8; margin: 0; }

.rp-spinner {
    width: 24px; height: 24px;
    border: 2px solid rgba(55,138,221,0.2);
    border-top-color: #378ADD;
    border-radius: 50%;
    animation: rp-spin 0.7s linear infinite;
}
@keyframes rp-spin { to { transform: rotate(360deg); } }

/* â-€â-€ MODAL â-€â-€ */
.rp-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(2,6,23,0.55);
    backdrop-filter: blur(6px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}
.rp-modal {
    background: #fff;
    border-radius: 18px;
    width: 100%;
    max-width: 860px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 40px 80px rgba(15,23,42,0.25);
    animation: rp-modal-in 0.22s cubic-bezier(0.34,1.56,0.64,1);
}
@keyframes rp-modal-in {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}

.rp-modal__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    background: #fafbfc;
    flex-shrink: 0;
}
.rp-modal__head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.rp-modal__icon {
    width: 36px; height: 36px;
    background: #0f172a;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    color: #fff;
    flex-shrink: 0;
}
.rp-modal__title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 2px;
}
.rp-modal__period {
    font-size: 0.75rem;
    color: #94a3b8;
}
.rp-modal__close {
    width: 32px; height: 32px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
    color: #64748b;
    transition: all 0.15s;
}
.rp-modal__close:hover { background: #f1f5f9; color: #0f172a; }

.rp-modal__body {
    padding: 1.5rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    flex: 1;
}

/* Modal KPI */
.rp-modal-kpi {
    display: grid;
    grid-template-columns: repeat(4, minmax(0,1fr));
    gap: 0.75rem;
}
.rp-modal-kpi__item {
    padding: 1rem;
    border-radius: 12px;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.rp-modal-kpi__item::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
}
.rp-modal-kpi__item--blue  { background: #F0F7FE; }
.rp-modal-kpi__item--green { background: #F0FAF2; }
.rp-modal-kpi__item--amber { background: #FEF9F0; }
.rp-modal-kpi__item--teal  { background: #EFFAF6; }
.rp-modal-kpi__item--blue::before  { background: linear-gradient(90deg,#378ADD,#85B7EB); }
.rp-modal-kpi__item--green::before { background: linear-gradient(90deg,#639922,#97C459); }
.rp-modal-kpi__item--amber::before { background: linear-gradient(90deg,#BA7517,#EF9F27); }
.rp-modal-kpi__item--teal::before  { background: linear-gradient(90deg,#1D9E75,#5DCAA5); }

.rp-modal-kpi__item i { font-size: 20px; display: block; margin-bottom: 6px; }
.rp-modal-kpi__item--blue  i { color: #185FA5; }
.rp-modal-kpi__item--green i { color: #3B6D11; }
.rp-modal-kpi__item--amber i { color: #854F0B; }
.rp-modal-kpi__item--teal  i { color: #0F6E56; }

.rp-modal-kpi__val {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1;
    margin-bottom: 4px;
    letter-spacing: -0.02em;
}
.rp-modal-kpi__lbl {
    font-size: 0.72rem;
    color: #64748b;
    font-weight: 600;
}

/* Modal Charts */
.rp-modal-charts {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.875rem;
}
.rp-modal-chart-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
    /* FIX: Cegah canvas overflow */
    min-width: 0;
    overflow: hidden;
}
.rp-modal-chart-title {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.78rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.75rem;
}
.rp-modal-chart-title i { font-size: 13px; color: #64748b; }

/* Modal Perf */
.rp-modal-perf {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
}
.rp-modal-perf__title {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.78rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.875rem;
}
.rp-modal-perf__title i { font-size: 13px; color: #64748b; }
.rp-modal-perf__grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.625rem;
}
.rp-modal-perf__item {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.875rem 1rem;
}
.rp-modal-perf__lbl {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.72rem;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 5px;
}
.rp-modal-perf__lbl i { font-size: 12px; }
.rp-modal-perf__val {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
}

.rp-modal__foot {
    padding: 1rem 1.5rem;
    border-top: 1px solid #f1f5f9;
    background: #fafbfc;
    display: flex;
    justify-content: flex-end;
    flex-shrink: 0;
}
.rp-btn-close {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border: 1.5px solid #e2e8f0;
    border-radius: 9px;
    background: #fff;
    color: #475569;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
}
.rp-btn-close:hover { background: #f1f5f9; color: #0f172a; }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   RESPONSIVE
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
@media (max-width: 1100px) {
    .rp-kpi-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
    .rp-kpi { border-bottom: 1px solid #f1f5f9; }
    .rp-kpi:nth-child(2) { border-right: none; }
    .rp-kpi:nth-child(3), .rp-kpi:nth-child(4) { border-bottom: none; }
    .rp-charts-row { grid-template-columns: 1fr 1fr; }
    .rp-chart-card--wide { grid-column: 1 / -1; }
}

@media (max-width: 768px) {
    .rp-header { padding: 1rem 1.25rem; border-radius: 12px; }
    .rp-header__title { font-size: 1rem; }
    .rp-kpi-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
    .rp-charts-row { grid-template-columns: 1fr; }
    .rp-chart-card--wide { grid-column: auto; }
    .rp-modal-kpi { grid-template-columns: repeat(2, minmax(0,1fr)); }
    .rp-modal-charts { grid-template-columns: 1fr; }
    .rp-modal-perf__grid { grid-template-columns: 1fr; }
}

@media (max-width: 540px) {
    .rp-header { flex-direction: column; align-items: flex-start; }
    .rp-kpi-grid { grid-template-columns: 1fr; }
    .rp-kpi { border-right: none !important; }
    .rp-modal { border-radius: 14px 14px 0 0; align-self: flex-end; max-height: 92vh; }
    .rp-modal-overlay { align-items: flex-end; padding: 0; }
}
</style>
@endpush
