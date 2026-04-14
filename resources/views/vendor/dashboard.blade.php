@extends('layouts.app')

@section('title', 'Dasbor Vendor')
@section('page_title', 'Dasbor Vendor')
@section('breadcrumb', 'Beranda / Dasbor')

@section('content')
@php
    $statusLabels = [
        'new'              => 'Baru',
        'in_progress'      => 'Diproses',
        'waiting_response' => 'Menunggu',
        'resolved'         => 'Selesai',
        'closed'           => 'Ditutup',
    ];
    $priorityLabels = [
        'low'      => 'Rendah',
        'medium'   => 'Sedang',
        'high'     => 'Tinggi',
        'urgent'   => 'Mendesak',
        'critical' => 'Kritis',
    ];

    function fmtMinutes($min) {
        if (!$min) return '-';
        if ($min < 60) return round($min) . ' mnt';
        return number_format($min / 60, 1) . ' jam';
    }
@endphp

<div class="vd-root">

    {{-- ═══ HEADER ═══ --}}
    <div class="vd-header">
        <div class="vd-header__left">
            <div class="vd-greeting">
                <span class="vd-greeting__day">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                <h1 class="vd-greeting__title">Selamat datang, <span class="vd-greeting__name">{{ Auth::user()->name }}</span> ??</h1>
            </div>
        </div>
    </div>

    {{-- ═══ STAT CARDS ROW 1 ═══ --}}
    <div class="vd-stats-grid">

        <div class="vd-stat-card vd-stat-card--blue">
            <div class="vd-stat-card__top">
                <span class="vd-stat-card__label">Tiket Aktif</span>
                <div class="vd-stat-card__icon">
                    <i class='bx bx-file-blank'></i>
                </div>
            </div>
            <div class="vd-stat-card__value">{{ $stats['active_tickets'] }}</div>
            <div class="vd-stat-card__footer">
                <span class="vd-dot vd-dot--blue"></span>
                Sedang berjalan
            </div>
        </div>

        <div class="vd-stat-card vd-stat-card--amber">
            <div class="vd-stat-card__top">
                <span class="vd-stat-card__label">Tiket Baru</span>
                <div class="vd-stat-card__icon">
                    <i class='bx bx-bell'></i>
                </div>
            </div>
            <div class="vd-stat-card__value">{{ $stats['new_tickets'] }}</div>
            <div class="vd-stat-card__footer">
                <span class="vd-dot vd-dot--amber"></span>
                Perlu perhatian
            </div>
        </div>

        <div class="vd-stat-card vd-stat-card--green">
            <div class="vd-stat-card__top">
                <span class="vd-stat-card__label">Selesai Minggu Ini</span>
                <div class="vd-stat-card__icon">
                    <i class='bx bx-check-circle'></i>
                </div>
            </div>
            <div class="vd-stat-card__value">{{ $stats['resolved_this_week'] }}</div>
            <div class="vd-stat-card__footer">
                <span class="vd-dot vd-dot--green"></span>
                Minggu ini
            </div>
        </div>

        <div class="vd-stat-card vd-stat-card--teal">
            <div class="vd-stat-card__top">
                <span class="vd-stat-card__label">Kepatuhan SLA</span>
                <div class="vd-stat-card__icon">
                    <i class='bx bx-shield-quarter'></i>
                </div>
            </div>
            <div class="vd-stat-card__value">{{ $stats['sla_compliance'] }}<small>%</small></div>
            <div class="vd-stat-card__footer">
                <span class="vd-dot vd-dot--teal"></span>
                Bulan ini
            </div>
        </div>

    </div>

    {{-- ═══ PERFORMANCE STATS ROW ═══ --}}
    <div class="vd-perf-grid">

        <div class="vd-perf-card">
            <div class="vd-perf-card__icon vd-perf-card__icon--green">
                <i class='bx bx-trending-up'></i>
            </div>
            <div>
                <div class="vd-perf-card__label">Selesai Bulan Ini</div>
                <div class="vd-perf-card__value">{{ $performance['resolved_this_month'] }}</div>
                <div class="vd-perf-card__sub">Bulan ini</div>
            </div>
        </div>

        <div class="vd-perf-card">
            <div class="vd-perf-card__icon vd-perf-card__icon--amber">
                <i class='bx bx-time-five'></i>
            </div>
            <div>
                <div class="vd-perf-card__label">Rata-rata Waktu Respons</div>
                <div class="vd-perf-card__value vd-perf-card__value--sm">{{ fmtMinutes($performance['avg_response_time']) }}</div>
                <div class="vd-perf-card__sub">Rata-rata respons pertama</div>
            </div>
        </div>

        <div class="vd-perf-card">
            <div class="vd-perf-card__icon vd-perf-card__icon--teal">
                <i class='bx bx-timer'></i>
            </div>
            <div>
                <div class="vd-perf-card__label">Rata-rata Waktu Penyelesaian</div>
                <div class="vd-perf-card__value vd-perf-card__value--sm">{{ fmtMinutes($performance['avg_resolution_time']) }}</div>
                <div class="vd-perf-card__sub">Rata-rata penyelesaian</div>
            </div>
        </div>

    </div>

    {{-- ═══ CHARTS ROW (Trend + Donut) ═══ --}}
    <div class="vd-charts-row">

        {{-- Trend Bar Chart --}}
        <div class="vd-chart-card vd-chart-card--wide">
            <div class="vd-chart-card__header">
                <div>
                    <h3 class="vd-chart-card__title">Tren Tiket</h3>
                    <div class="vd-legend">
                        <span class="vd-legend__item">
                            <span class="vd-legend__dot" style="background:#378ADD"></span>Total Masuk
                        </span>
                        <span class="vd-legend__item">
                            <span class="vd-legend__dot" style="background:#639922"></span>Selesai
                        </span>
                    </div>
                </div>
                <div class="vd-period-switch">
                    <button class="vd-period-btn" id="btn-weekly" onclick="switchPeriod('weekly')">Mingguan</button>
                    <button class="vd-period-btn vd-period-btn--active" id="btn-monthly" onclick="switchPeriod('monthly')">Bulanan</button>
                </div>
            </div>
            <div class="vd-chart-card__body" style="position:relative;min-height:220px">
                <div id="trend-spinner" class="vd-spinner-wrap" style="display:none">
                    <div class="vd-spinner"></div>
                </div>
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        {{-- Donut Status --}}
        <div class="vd-chart-card">
            <div class="vd-chart-card__header">
                <h3 class="vd-chart-card__title">Status Tiket</h3>
            </div>
            <div class="vd-chart-card__body vd-donut-body">
                <div class="vd-donut-canvas-wrap">
                    <canvas id="donutChart" width="120" height="120"></canvas>
                </div>
                <div class="vd-donut-legend">
                    @php
                        $donutItems = [
                            ['label' => 'Baru',             'key' => 'new',              'color' => '#378ADD'],
                            ['label' => 'Diproses',         'key' => 'in_progress',      'color' => '#BA7517'],
                            ['label' => 'Menunggu',         'key' => 'waiting_response', 'color' => '#E24B4A'],
                            ['label' => 'Selesai',          'key' => 'resolved',         'color' => '#639922'],
                            ['label' => 'Ditutup',          'key' => 'closed',           'color' => '#888780'],
                        ];
                    @endphp
                    @foreach($donutItems as $item)
                    <div class="vd-dl-row">
                        <div class="vd-dl-label">
                            <span class="vd-legend__dot" style="background:{{ $item['color'] }}"></span>
                            {{ $item['label'] }}
                        </div>
                        <span class="vd-dl-val">{{ $performance['tickets_by_status'][$item['key']] ?? 0 }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- ═══ MONTHLY LINE CHART ═══ --}}
    <div class="vd-chart-card">
        <div class="vd-chart-card__header">
            <div>
                <h3 class="vd-chart-card__title">Performa Bulanan</h3>
                <p class="vd-chart-card__sub">Jumlah tiket selesai 6 bulan terakhir</p>
            </div>
            <a href="{{ route('vendor.reports') }}" class="vd-btn-text">
                Lihat Laporan <i class='bx bx-right-arrow-alt'></i>
            </a>
        </div>
        <div class="vd-chart-card__body" style="position:relative;height:170px">
            <canvas id="lineChart"></canvas>
        </div>
    </div>

    {{-- ═══ URGENT + RECENT ═══ --}}
    <div class="vd-tickets-row">

        {{-- Urgent Tickets --}}
        <div class="vd-ticket-panel">
            <div class="vd-ticket-panel__header">
                <h3 class="vd-ticket-panel__title">
                    <i class='bx bx-error-circle vd-icon-urgent'></i>
                    Tiket Mendesak
                </h3>
                <span class="vd-badge-count">{{ $urgentTickets->count() }}</span>
            </div>

            <div class="vd-ticket-list">
            @if($urgentTickets->isEmpty())
                <div class="vd-empty">
                    <div class="vd-empty__icon">
                        <i class='bx bx-check-shield'></i>
                    </div>
                    <p>Tidak ada tiket mendesak saat ini</p>
                </div>
            @else
                @foreach($urgentTickets as $t)
                <div class="vd-ticket-row">
                    <div class="vd-ticket-row__info">
                        <div class="vd-ticket-row__number">{{ $t->ticket_number }}</div>
                        <div class="vd-ticket-row__title">{{ $t->title }}</div>
                        <div class="vd-ticket-row__meta">
                            <span class="vd-pill vd-pill--p-{{ $t->priority }}">{{ $priorityLabels[$t->priority] ?? $t->priority }}</span>
                            <span class="vd-ticket-row__time">
                                <i class='bx bx-time-five'></i>
                                {{ \Carbon\Carbon::parse($t->created_at)->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('vendor.tickets.show', $t->id) }}" class="vd-btn-view">Lihat</a>
                </div>
                @endforeach
            @endif
            </div>
        </div>

        {{-- Recent Tickets --}}
        <div class="vd-ticket-panel">
            <div class="vd-ticket-panel__header">
                <h3 class="vd-ticket-panel__title">
                    <i class='bx bx-list-ul vd-icon-recent'></i>
                    Tiket Terbaru
                </h3>
                <a href="{{ route('vendor.tickets.index') }}" class="vd-btn-text vd-btn-text--sm">
                    Lihat Semua Tiket <i class='bx bx-right-arrow-alt'></i>
                </a>
            </div>

            <div class="vd-ticket-list">
            @if($recentTickets->isEmpty())
                <div class="vd-empty">
                    <div class="vd-empty__icon">
                        <i class='bx bx-folder-open'></i>
                    </div>
                    <p>Belum ada tiket terbaru</p>
                </div>
            @else
                @foreach($recentTickets as $t)
                <div class="vd-ticket-row">
                    <div class="vd-ticket-row__info">
                        <div class="vd-ticket-row__number">{{ $t->ticket_number }}</div>
                        <div class="vd-ticket-row__title">{{ $t->title }}</div>
                        <div class="vd-ticket-row__meta">
                            <span class="vd-ticket-row__author">{{ $t->user->name ?? '-' }}</span>
                            <span class="vd-pill vd-pill--s-{{ $t->status }}">{{ $statusLabels[$t->status] ?? $t->status }}</span>
                            <span class="vd-ticket-row__time">
                                <i class='bx bx-time-five'></i>
                                {{ \Carbon\Carbon::parse($t->created_at)->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('vendor.tickets.show', $t->id) }}" class="vd-btn-view">Lihat</a>
                </div>
                @endforeach
            @endif
            </div>
        </div>

    </div>

</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const trendDataMonthly = @json($trendData);
const donutData        = @json($donutData);
const lineData         = @json($performance['monthly_performance']);

const isDark = () => document.documentElement.classList.contains('dark')
    || matchMedia('(prefers-color-scheme:dark)').matches;

const chartTheme = () => ({
    grid : isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)',
    tick : isDark() ? '#9ca3af'                : '#94a3b8',
});

/* Trend Bar Chart */
let trendInst = null;

function buildTrendChart(items) {
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;
    if (trendInst) { trendInst.destroy(); trendInst = null; }

    const { grid, tick } = chartTheme();
    const hasData  = items.length > 0;
    const labels   = hasData ? items.map(i => i.period)   : ['Tidak ada data'];
    const total    = hasData ? items.map(i => i.total)    : [0];
    const resolved = hasData ? items.map(i => i.resolved) : [0];

    trendInst = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Total Masuk', data: total,    backgroundColor: '#378ADD', borderRadius: 5, barPercentage: .6 },
                { label: 'Selesai',     data: resolved, backgroundColor: '#639922', borderRadius: 5, barPercentage: .6 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 500, easing: 'easeOutQuart' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index', intersect: false,
                    backgroundColor: isDark() ? '#1e2332' : '#fff',
                    titleColor: isDark() ? '#e5e7eb' : '#111827',
                    bodyColor: isDark() ? '#9ca3af' : '#6b7280',
                    borderColor: isDark() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8,
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: tick, font: { size: 11 }, maxRotation: 0 } },
                y: { beginAtZero: true, grid: { color: grid }, ticks: { color: tick, font: { size: 11 }, precision: 0 }, border: { display: false } },
            },
        },
    });
}

function switchPeriod(p) {
    document.querySelectorAll('.vd-period-btn').forEach(b => b.classList.remove('vd-period-btn--active'));
    document.getElementById('btn-' + p).classList.add('vd-period-btn--active');

    if (p === 'monthly') { buildTrendChart(trendDataMonthly); return; }

    const spinner = document.getElementById('trend-spinner');
    const canvas  = document.getElementById('trendChart');
    spinner.style.display = 'flex';
    canvas.style.display  = 'none';

    fetch('{{ route("vendor.ticket-stats") }}?period=weekly')
        .then(r => r.json())
        .then(d => { spinner.style.display = 'none'; canvas.style.display = 'block'; buildTrendChart(d.data ?? []); })
        .catch(() => { spinner.style.display = 'none'; canvas.style.display = 'block'; buildTrendChart([]); });
}

/* Donut Chart */
(function () {
    const ctx = document.getElementById('donutChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: donutData.labels,
            datasets: [{ data: donutData.values, backgroundColor: donutData.colors, borderWidth: 0, hoverOffset: 5 }],
        },
        options: {
            responsive: false,
            cutout: '70%',
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } } },
        },
    });
})();

/* Line Chart */
(function () {
    const ctx = document.getElementById('lineChart');
    if (!ctx) return;
    const { grid, tick } = chartTheme();
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: lineData.map(m => m.month),
            datasets: [{
                label: 'Selesai',
                data: lineData.map(m => m.resolved),
                borderColor: '#639922',
                backgroundColor: (ctx) => {
                    const c = ctx.chart.ctx;
                    const g = c.createLinearGradient(0, 0, 0, 170);
                    g.addColorStop(0, 'rgba(99,153,34,0.18)');
                    g.addColorStop(1, 'rgba(99,153,34,0)');
                    return g;
                },
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: '#639922',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                tension: .4,
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
                    backgroundColor: isDark() ? '#1e2332' : '#fff',
                    titleColor: isDark() ? '#e5e7eb' : '#111827',
                    bodyColor: isDark() ? '#9ca3af' : '#6b7280',
                    borderColor: isDark() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8,
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: tick, font: { size: 11 } } },
                y: { beginAtZero: true, grid: { color: grid }, ticks: { color: tick, font: { size: 11 }, precision: 0 }, border: { display: false } },
            },
        },
    });
})();

buildTrendChart(trendDataMonthly);
</script>

<style>

#lineChart {
    max-width: 100%;
}

/* Root & Layout */
.vd-root {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    padding-bottom: 2rem;
}

/* Header */
.vd-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 0.25rem 0;
}

.vd-greeting__day {
    display: block;
    font-size: 0.72rem;
    font-weight: 500;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 4px;
}

.vd-greeting__title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #0f172a;
    margin: 0;
    line-height: 1.3;
}

.vd-greeting__name {
    color: #185FA5;
}

/* Stat Cards (Row 1) */
.vd-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0,1fr));
    gap: 0.875rem;
}

.vd-stat-card {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.07);
    border-radius: 14px;
    padding: 1.1rem 1.2rem;
    position: relative;
    overflow: hidden;
    transition: transform 0.18s, box-shadow 0.18s;
}

.vd-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 14px 14px 0 0;
}

.vd-stat-card--blue::before   { background: linear-gradient(90deg, #378ADD, #85B7EB); }
.vd-stat-card--amber::before  { background: linear-gradient(90deg, #BA7517, #EF9F27); }
.vd-stat-card--green::before  { background: linear-gradient(90deg, #639922, #97C459); }
.vd-stat-card--teal::before   { background: linear-gradient(90deg, #1D9E75, #5DCAA5); }

.vd-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}

.vd-stat-card__top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.vd-stat-card__label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
}

.vd-stat-card__icon {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.vd-stat-card--blue  .vd-stat-card__icon { background: #E6F1FB; color: #185FA5; }
.vd-stat-card--amber .vd-stat-card__icon { background: #FAEEDA; color: #854F0B; }
.vd-stat-card--green .vd-stat-card__icon { background: #EAF3DE; color: #3B6D11; }
.vd-stat-card--teal  .vd-stat-card__icon { background: #E1F5EE; color: #0F6E56; }

.vd-stat-card__value {
    font-size: 2rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1;
    margin-bottom: 0.5rem;
    letter-spacing: -0.02em;
}

.vd-stat-card__value small {
    font-size: 1rem;
    font-weight: 500;
    color: #64748b;
}

.vd-stat-card__footer {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.73rem;
    color: #64748b;
}

/* Perf Cards (Row 2) */
.vd-perf-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 0.875rem;
}

.vd-perf-card {
    background: #f8fafc;
    border: 1px solid rgba(0,0,0,0.06);
    border-radius: 14px;
    padding: 1rem 1.1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.18s, box-shadow 0.18s;
}

.vd-perf-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.vd-perf-card__icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.vd-perf-card__icon--green { background: #EAF3DE; color: #3B6D11; }
.vd-perf-card__icon--amber { background: #FAEEDA; color: #854F0B; }
.vd-perf-card__icon--teal  { background: #E1F5EE; color: #0F6E56; }

.vd-perf-card__label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; margin-bottom: 2px; }
.vd-perf-card__value { font-size: 1.4rem; font-weight: 700; color: #0f172a; line-height: 1.1; letter-spacing: -0.01em; }
.vd-perf-card__value--sm { font-size: 1.1rem; }
.vd-perf-card__sub   { font-size: 0.71rem; color: #94a3b8; margin-top: 2px; }

/* Dot indicator */
.vd-dot {
    display: inline-block;
    width: 7px; height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}
.vd-dot--blue  { background: #378ADD; }
.vd-dot--amber { background: #BA7517; }
.vd-dot--green { background: #639922; }
.vd-dot--teal  { background: #1D9E75; }

/* Charts Row */
.vd-charts-row {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 0.875rem;
    align-items: stretch;
}

.vd-chart-card {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.07);
    border-radius: 14px;
    overflow: hidden;
}

.vd-chart-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.vd-chart-card__title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #0f172a;
    margin: 0 0 4px 0;
}

.vd-chart-card__sub {
    font-size: 0.72rem;
    color: #94a3b8;
    margin: 0;
}

.vd-chart-card__body {
    padding: 1rem 1.25rem;
}

/* Period switch */
.vd-period-switch {
    display: inline-flex;
    gap: 2px;
    background: #f1f5f9;
    padding: 3px;
    border-radius: 10px;
}

.vd-period-btn {
    padding: 5px 13px;
    border: none;
    border-radius: 7px;
    font-size: 0.74rem;
    font-weight: 500;
    cursor: pointer;
    background: transparent;
    color: #64748b;
    transition: all 0.15s;
}

.vd-period-btn--active {
    background: #fff;
    color: #185FA5;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Legend */
.vd-legend {
    display: flex;
    gap: 12px;
    margin-top: 4px;
}

.vd-legend__item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.73rem;
    color: #64748b;
}

.vd-legend__dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 2px;
    flex-shrink: 0;
}

/* Donut */
.vd-donut-body {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.vd-donut-canvas-wrap {
    flex-shrink: 0;
    width: 120px;
    height: 120px;
}

.vd-donut-legend {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.vd-dl-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.vd-dl-label {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 0.77rem;
    color: #64748b;
}

.vd-dl-val {
    font-size: 0.82rem;
    font-weight: 600;
    color: #0f172a;
    min-width: 24px;
    text-align: right;
}

/* Spinner */
.vd-spinner-wrap {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.vd-spinner {
    width: 22px;
    height: 22px;
    border: 2px solid rgba(55,138,221,0.2);
    border-top-color: #378ADD;
    border-radius: 50%;
    animation: vd-spin 0.7s linear infinite;
}

@keyframes vd-spin { to { transform: rotate(360deg); } }

/* Buttons */
.vd-btn-text {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.78rem;
    font-weight: 500;
    color: #185FA5;
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 8px;
    transition: background 0.15s;
}

.vd-btn-text:hover { background: #E6F1FB; color: #185FA5; }
.vd-btn-text--sm   { font-size: 0.76rem; }

/* Tickets Row */
.vd-tickets-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.875rem;
    align-items: start;
}

.vd-ticket-panel {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.07);
    border-radius: 14px;
    overflow: hidden;
}

.vd-ticket-panel__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.vd-ticket-panel__title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 7px;
}

.vd-icon-urgent { color: #E24B4A; font-size: 16px; }
.vd-icon-recent { color: #378ADD; font-size: 16px; }

.vd-ticket-list { /* container */ }

.vd-ticket-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.875rem;
    padding: 0.875rem 1.25rem;
    border-bottom: 1px solid rgba(0,0,0,0.04);
    transition: background 0.12s;
}

.vd-ticket-row:last-child { border-bottom: none; }
.vd-ticket-row:hover { background: #f8fafc; }

.vd-ticket-row__info { min-width: 0; flex: 1; }

.vd-ticket-row__number {
    font-size: 0.7rem;
    font-weight: 600;
    color: #185FA5;
    margin-bottom: 3px;
    letter-spacing: 0.02em;
}

.vd-ticket-row__title {
    font-size: 0.83rem;
    font-weight: 500;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 240px;
    margin-bottom: 4px;
}

.vd-ticket-row__meta {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
}

.vd-ticket-row__time {
    display: flex;
    align-items: center;
    gap: 3px;
    font-size: 0.7rem;
    color: #94a3b8;
}

.vd-ticket-row__author {
    font-size: 0.71rem;
    color: #64748b;
    font-weight: 500;
}

.vd-btn-view {
    padding: 5px 14px;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 8px;
    font-size: 0.76rem;
    font-weight: 500;
    background: #fff;
    color: #334155;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
    transition: all 0.15s;
}

.vd-btn-view:hover {
    background: #f1f5f9;
    border-color: rgba(0,0,0,0.15);
    color: #0f172a;
}

/* Badges / Pills */
.vd-pill {
    display: inline-flex;
    align-items: center;
    padding: 2px 9px;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.02em;
}

/* Status pills */
.vd-pill--s-new              { background: #E6F1FB; color: #185FA5; }
.vd-pill--s-in_progress      { background: #FAEEDA; color: #854F0B; }
.vd-pill--s-waiting_response { background: #FCEBEB; color: #A32D2D; }
.vd-pill--s-resolved         { background: #EAF3DE; color: #3B6D11; }
.vd-pill--s-closed           { background: #F1EFE8; color: #5F5E5A; }

/* Priority pills */
.vd-pill--p-low      { background: #EAF3DE; color: #3B6D11; }
.vd-pill--p-medium   { background: #FAEEDA; color: #854F0B; }
.vd-pill--p-high     { background: #FFF0DC; color: #7D3B08; }
.vd-pill--p-urgent   { background: #FCEBEB; color: #A32D2D; }
.vd-pill--p-critical { background: #FCEBEB; color: #7A1B1B; }

/* Count badge */
.vd-badge-count {
    background: #FCEBEB;
    color: #A32D2D;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    min-width: 24px;
    text-align: center;
}

/* Empty state */
.vd-empty {
    text-align: center;
    padding: 2.5rem 1rem;
}

.vd-empty__icon {
    width: 52px;
    height: 52px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    font-size: 22px;
    color: #94a3b8;
}

.vd-empty p {
    font-size: 0.8rem;
    color: #94a3b8;
    margin: 0;
}

/* RESPONSIVE */

@media (max-width: 1100px) {
    .vd-charts-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 991px) {
    .vd-stats-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
    .vd-perf-grid  { grid-template-columns: repeat(2, minmax(0,1fr)); }
}

@media (max-width: 768px) {
    .vd-tickets-row { grid-template-columns: 1fr; }
    .vd-charts-row  { grid-template-columns: 1fr; }
}

@media (max-width: 576px) {
    .vd-stats-grid { grid-template-columns: 1fr 1fr; }
    .vd-perf-grid  { grid-template-columns: 1fr; }
    .vd-stat-card__value { font-size: 1.6rem; }
    .vd-ticket-row__title { max-width: 150px; }
    .vd-header { flex-direction: column; align-items: flex-start; }
}
</style>

