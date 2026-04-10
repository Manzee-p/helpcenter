@extends('layouts.app')

@section('title', 'Vendor Dashboard')
@section('page_title', 'Vendor Dashboard')
@section('breadcrumb', 'Home / Dashboard')



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
        if (!$min) return '—';
        if ($min < 60) return round($min) . ' mnt';
        return number_format($min / 60, 1) . ' jam';
    }
@endphp

<div class="vendor-dashboard">

    {{-- ═══ HEADER ═══ --}}
    <div class="vd-header">
        <div>
            <h2>Vendor Dashboard</h2>
            <p>
                Selamat datang kembali, <strong>{{ Auth::user()->name }}</strong>
                &mdash; {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </p>
        </div>
        <a href="{{ route('vendor.settings') }}" class="btn-profile">
            <i class='bx bx-user' style="font-size:14px"></i>
            Profile Settings
        </a>
    </div>

    {{-- ═══ STAT CARDS ROW 1 ═══ --}}
    <div class="stat-grid-4">

        <div class="stat-card">
            <div class="stat-card__inner">
                <div>
                    <div class="stat-card__label">Active Tickets</div>
                    <div class="stat-card__value">{{ $stats['active_tickets'] }}</div>
                    <div class="stat-card__sub text-blue">Sedang berjalan</div>
                </div>
                <div class="stat-icon si-blue">
                    <i class='bx bx-file' style="font-size:16px"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card__inner">
                <div>
                    <div class="stat-card__label">New Tickets</div>
                    <div class="stat-card__value">{{ $stats['new_tickets'] }}</div>
                    <div class="stat-card__sub text-amber">Perlu perhatian</div>
                </div>
                <div class="stat-icon si-amber">
                    <i class='bx bx-bell' style="font-size:16px"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card__inner">
                <div>
                    <div class="stat-card__label">Resolved This Week</div>
                    <div class="stat-card__value">{{ $stats['resolved_this_week'] }}</div>
                    <div class="stat-card__sub text-green">Minggu ini</div>
                </div>
                <div class="stat-icon si-green">
                    <i class='bx bx-check-circle' style="font-size:16px"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card__inner">
                <div>
                    <div class="stat-card__label">SLA Compliance</div>
                    <div class="stat-card__value">{{ $stats['sla_compliance'] }}%</div>
                    <div class="stat-card__sub text-teal">Bulan ini</div>
                </div>
                <div class="stat-icon si-teal">
                    <i class='bx bx-time' style="font-size:16px"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══ STAT CARDS ROW 2 (Performance) ═══ --}}
    <div class="stat-grid-3">

        <div class="stat-card">
            <div class="stat-card__inner">
                <div>
                    <div class="stat-card__label">Resolved This Month</div>
                    <div class="stat-card__value">{{ $performance['resolved_this_month'] }}</div>
                    <div class="stat-card__sub text-green">Bulan ini</div>
                </div>
                <div class="stat-icon si-green">
                    <i class='bx bx-trending-up' style="font-size:16px"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card__inner">
                <div>
                    <div class="stat-card__label">Avg Response Time</div>
                    <div class="stat-card__value" style="font-size:1.15rem">
                        {{ fmtMinutes($performance['avg_response_time']) }}
                    </div>
                    <div class="stat-card__sub text-muted">Rata-rata respons pertama</div>
                </div>
                <div class="stat-icon si-amber">
                    <i class='bx bx-reply' style="font-size:16px"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card__inner">
                <div>
                    <div class="stat-card__label">Avg Resolution Time</div>
                    <div class="stat-card__value" style="font-size:1.15rem">
                        {{ fmtMinutes($performance['avg_resolution_time']) }}
                    </div>
                    <div class="stat-card__sub text-muted">Rata-rata penyelesaian</div>
                </div>
                <div class="stat-icon si-teal">
                    <i class='bx bx-timer' style="font-size:16px"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══ CHARTS ROW (Trend + Donut) ═══ --}}
    <div class="row g-3">

        {{-- Trend Bar Chart --}}
        <div class="col-lg-8">
            <div class="chart-card h-100">
                <div class="chart-card__header">
                    <div>
                        <div class="chart-card__title">Tren Tiket</div>
                        <div class="d-flex gap-3 mt-1">
                            <small class="d-flex align-items-center gap-1 text-muted">
                                <span class="legend-dot" style="background:#378ADD"></span>Total Masuk
                            </small>
                            <small class="d-flex align-items-center gap-1 text-muted">
                                <span class="legend-dot" style="background:#639922"></span>Selesai
                            </small>
                        </div>
                    </div>
                    <div class="btn-grp">
                        <button class="pg-btn" id="btn-weekly"
                            onclick="switchPeriod('weekly')">Mingguan</button>
                        <button class="pg-btn active" id="btn-monthly"
                            onclick="switchPeriod('monthly')">Bulanan</button>
                    </div>
                </div>
                <div class="chart-card__body" style="position:relative;min-height:200px">
                    <div id="trend-spinner" class="text-center py-5" style="display:none">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </div>
                    <canvas id="trendChart" height="120"></canvas>
                </div>
            </div>
        </div>

        {{-- Donut Status --}}
        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="chart-card__header">
                    <div class="chart-card__title">Status Tiket</div>
                </div>
                <div class="chart-card__body d-flex align-items-center">
                    <div class="donut-wrap w-100">
                        <div style="width:110px;flex-shrink:0">
                            <canvas id="donutChart"></canvas>
                        </div>
                        <div class="donut-legend">
                            @php
                                $donutItems = [
                                    ['label' => 'New',              'key' => 'new',              'color' => '#378ADD'],
                                    ['label' => 'In Progress',      'key' => 'in_progress',      'color' => '#BA7517'],
                                    ['label' => 'Waiting Response', 'key' => 'waiting_response', 'color' => '#E24B4A'],
                                    ['label' => 'Resolved',         'key' => 'resolved',         'color' => '#639922'],
                                    ['label' => 'Closed',           'key' => 'closed',           'color' => '#888780'],
                                ];
                            @endphp
                            @foreach($donutItems as $item)
                            <div class="dl-row">
                                <div class="dl-label">
                                    <span class="legend-dot" style="background:{{ $item['color'] }}"></span>
                                    {{ $item['label'] }}
                                </div>
                                <span class="dl-val">
                                    {{ $performance['tickets_by_status'][$item['key']] ?? 0 }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══ MONTHLY LINE CHART ═══ --}}
    <div class="chart-card">
        <div class="chart-card__header">
            <div>
                <div class="chart-card__title">Performa Bulanan (6 bulan)</div>
                <div class="chart-card__sub">Jumlah tiket resolved per bulan</div>
            </div>
            <a href="{{ route('vendor.reports') }}" class="btn-sm-label">Lihat Laporan</a>
        </div>
        <div class="chart-card__body" style="position:relative;height:160px">
            <canvas id="lineChart"></canvas>
        </div>
    </div>

    {{-- ═══ URGENT + RECENT ═══ --}}
    <div class="row g-3">

        {{-- Urgent Tickets --}}
        <div class="col-md-6">
            <div class="ticket-card h-100">
                <div class="ticket-card__header">
                    <h5 class="ticket-card__title">Urgent Tickets</h5>
                    <span class="badge-count">{{ $urgentTickets->count() }}</span>
                </div>

                @if($urgentTickets->isEmpty())
                    <div class="empty-state">
                        <i class='bx bx-check-circle text-success'></i>
                        <p>Tidak ada tiket urgent</p>
                    </div>
                @else
                    @foreach($urgentTickets as $t)
                    <div class="ts-row">
                        <div style="min-width:0;flex:1">
                            <div class="ts-number">{{ $t->ticket_number }}</div>
                            <div class="ts-title">{{ $t->title }}</div>
                            <div class="ts-meta">
                                <span class="badge-pill p-{{ $t->priority }}">
                                    {{ $priorityLabels[$t->priority] ?? $t->priority }}
                                </span>
                                <span>{{ \Carbon\Carbon::parse($t->created_at)->diffForHumans() }}</span>
                            </div>
                        </div>
                        <a href="{{ route('vendor.tickets.show', $t->id) }}" class="btn-sm-outline">
                            Lihat
                        </a>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Recent Tickets --}}
        <div class="col-md-6">
            <div class="ticket-card h-100">
                <div class="ticket-card__header">
                    <h5 class="ticket-card__title">Tiket Terbaru</h5>
                    <a href="{{ route('vendor.tickets.index') }}" class="btn-sm-label">Lihat Semua</a>
                </div>

                @if($recentTickets->isEmpty())
                    <div class="empty-state">
                        <i class='bx bx-folder-open'></i>
                        <p>Belum ada tiket terbaru</p>
                    </div>
                @else
                    @foreach($recentTickets as $t)
                    <div class="ts-row">
                        <div style="min-width:0;flex:1">
                            <div class="ts-number">{{ $t->ticket_number }}</div>
                            <div class="ts-title">{{ $t->title }}</div>
                            <div class="ts-meta">
                                <span>{{ $t->user->name ?? '-' }}</span>
                                <span class="badge-pill s-{{ $t->status }}">
                                    {{ $statusLabels[$t->status] ?? $t->status }}
                                </span>
                                <span>{{ \Carbon\Carbon::parse($t->created_at)->diffForHumans() }}</span>
                            </div>
                        </div>
                        <a href="{{ route('vendor.tickets.show', $t->id) }}" class="btn-sm-outline">
                            Lihat
                        </a>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/* ── Data dari server ── */
const trendDataMonthly = @json($trendData);
const donutData        = @json($donutData);
const lineData         = @json($performance['monthly_performance']);

/* ── Helper warna sesuai dark-mode ── */
const chartColors = () => ({
    grid: matchMedia('(prefers-color-scheme:dark)').matches
        ? 'rgba(255,255,255,0.07)'
        : 'rgba(0,0,0,0.06)',
    tick: matchMedia('(prefers-color-scheme:dark)').matches ? '#aaa' : '#888',
});

/* ════════════════════════════════════
   TREND BAR CHART
════════════════════════════════════ */
let trendInst = null;

function buildTrendChart(items) {
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;
    if (trendInst) { trendInst.destroy(); trendInst = null; }

    const { grid, tick } = chartColors();
    const hasData  = items.length > 0;
    const labels   = hasData ? items.map(i => i.period)   : ['Tidak ada data'];
    const total    = hasData ? items.map(i => i.total)    : [0];
    const resolved = hasData ? items.map(i => i.resolved) : [0];

    trendInst = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Total Masuk',
                    data: total,
                    backgroundColor: '#378ADD',
                    borderRadius: 4,
                    barPercentage: .55,
                },
                {
                    label: 'Selesai',
                    data: resolved,
                    backgroundColor: '#639922',
                    borderRadius: 4,
                    barPercentage: .55,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 600, easing: 'easeOutQuart' },
            animations: {
                y: {
                    from: (ctx) => ctx.chart.scales.y.getPixelForValue(0),
                },
            },
            transitions: {
                active: { animation: { duration: 400 } },
                resize: { animation: { duration: 0 } },
            },
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: tick, font: { size: 11 }, maxRotation: 0 },
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
}

/* ── Period switch ── */
function switchPeriod(p) {
    document.querySelectorAll('.pg-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('btn-' + p).classList.add('active');

    if (p === 'monthly') {
        buildTrendChart(trendDataMonthly);
        return;
    }

    /* weekly — fetch AJAX */
    const spinner = document.getElementById('trend-spinner');
    const canvas  = document.getElementById('trendChart');
    spinner.style.display = 'block';
    canvas.style.display  = 'none';

    fetch('{{ route("vendor.ticket-stats") }}?period=weekly')
        .then(r => r.json())
        .then(d => {
            spinner.style.display = 'none';
            canvas.style.display  = 'block';
            buildTrendChart(d.data ?? []);
        })
        .catch(() => {
            spinner.style.display = 'none';
            canvas.style.display  = 'block';
            buildTrendChart([]);
        });
}

/* ════════════════════════════════════
   DONUT CHART
════════════════════════════════════ */
(function () {
    const ctx = document.getElementById('donutChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: donutData.labels,
            datasets: [{
                data: donutData.values,
                backgroundColor: donutData.colors,
                borderWidth: 0,
                hoverOffset: 4,
            }],
        },
        options: {
            responsive: true,
            cutout: '68%',
            plugins: { legend: { display: false } },
        },
    });
})();

/* ════════════════════════════════════
   LINE CHART
════════════════════════════════════ */
(function () {
    const ctx = document.getElementById('lineChart');
    if (!ctx) return;
    const { grid, tick } = chartColors();
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: lineData.map(m => m.month),
            datasets: [{
                label: 'Resolved',
                data: lineData.map(m => m.resolved),
                borderColor: '#639922',
                backgroundColor: 'rgba(99,153,34,0.08)',
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: '#639922',
                tension: .3,
                fill: true,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false },
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

/* ── Init ── */
buildTrendChart(trendDataMonthly);
</script>
@endpush

@push('styles')
<style>
/* ══════════════════════════════════════
   VENDOR DASHBOARD — selaras Vue version
══════════════════════════════════════ */

/* ── Layout ── */
.vendor-dashboard { display: flex; flex-direction: column; gap: 1rem; }

/* ── Header ── */
.vd-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: .75rem;
}
.vd-header h2 { font-size: 1.15rem; font-weight: 500; margin-bottom: 3px; color: #0f172a; }
.vd-header p  { font-size: .82rem; color: #64748b; margin: 0; }

.btn-profile {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border: 1px solid rgba(0,0,0,.13);
    border-radius: 8px;
    background: #fff;
    color: #0f172a;
    font-size: .82rem;
    text-decoration: none;
    transition: background .15s;
}
.btn-profile:hover { background: #f8fafc; color: #0f172a; }

/* ── Stat grid ── */
.stat-grid-4 { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: .75rem; }
.stat-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: .75rem; }

.stat-card {
    background: #f8fafc;
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 12px;
    padding: 1rem 1.1rem;
}
.stat-card__inner { display: flex; justify-content: space-between; align-items: flex-start; }
.stat-card__label { font-size: .75rem; color: #64748b; margin-bottom: 5px; }
.stat-card__value { font-size: 1.4rem; font-weight: 500; color: #0f172a; line-height: 1; margin-bottom: 4px; }
.stat-card__sub   { font-size: .72rem; }

.stat-icon {
    width: 38px; height: 38px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.si-blue   { background: #E6F1FB; color: #185FA5; }
.si-amber  { background: #FAEEDA; color: #854F0B; }
.si-green  { background: #EAF3DE; color: #3B6D11; }
.si-teal   { background: #E1F5EE; color: #0F6E56; }

.text-blue  { color: #185FA5; }
.text-amber { color: #854F0B; }
.text-green { color: #3B6D11; }
.text-teal  { color: #0F6E56; }
.text-muted { color: #64748b; }

/* ── Chart card ── */
.chart-card {
    background: #fff;
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 12px;
    overflow: hidden;
}
.chart-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .85rem 1.1rem;
    border-bottom: 1px solid rgba(0,0,0,.06);
}
.chart-card__title { font-size: .88rem; font-weight: 500; color: #0f172a; margin-bottom: 0; }
.chart-card__sub   { font-size: .72rem; color: #64748b; margin-top: 2px; }
.chart-card__body  { padding: 1rem 1.1rem; }

/* ── Period button group ── */
.btn-grp {
    display: inline-flex;
    gap: 3px;
    background: #f1f5f9;
    padding: 3px;
    border-radius: 8px;
}
.pg-btn {
    padding: 4px 12px;
    border: none;
    border-radius: 6px;
    font-size: .75rem;
    font-weight: 500;
    cursor: pointer;
    background: transparent;
    color: #64748b;
    transition: all .15s;
}
.pg-btn.active {
    background: #fff;
    color: #185FA5;
    border: 1px solid rgba(0,0,0,.07);
}

/* ── Legend dot ── */
.legend-dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 2px;
    flex-shrink: 0;
}

/* ── Donut legend ── */
.donut-wrap   { display: flex; align-items: center; gap: 1rem; }
.donut-legend { flex: 1; display: flex; flex-direction: column; gap: 7px; }
.dl-row       { display: flex; justify-content: space-between; align-items: center; }
.dl-label     { display: flex; align-items: center; gap: 6px; font-size: .75rem; color: #64748b; }
.dl-val       { font-size: .75rem; font-weight: 500; color: #0f172a; }

/* ── Ticket list ── */
.ticket-card {
    background: #fff;
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 12px;
    overflow: hidden;
}
.ticket-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .85rem 1.1rem;
    border-bottom: 1px solid rgba(0,0,0,.06);
}
.ticket-card__title { font-size: .88rem; font-weight: 500; color: #0f172a; margin: 0; }

.ts-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
    padding: .75rem 1.1rem;
    border-bottom: 1px solid rgba(0,0,0,.04);
}
.ts-row:last-child { border-bottom: none; }

.ts-number { font-size: .72rem; color: #185FA5; font-weight: 500; margin-bottom: 3px; }
.ts-title  {
    font-size: .82rem; font-weight: 500; color: #0f172a;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 220px;
}
.ts-meta   {
    display: flex; gap: 6px; flex-wrap: wrap; align-items: center;
    font-size: .72rem; color: #64748b; margin-top: 3px;
}

.btn-sm-outline {
    padding: 4px 12px;
    border: 1px solid rgba(0,0,0,.12);
    border-radius: 8px;
    font-size: .75rem;
    background: #fff;
    color: #0f172a;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
    transition: background .15s;
}
.btn-sm-outline:hover { background: #f8fafc; color: #0f172a; }

.btn-sm-label {
    padding: 4px 12px;
    border: 1px solid #B5D4F4;
    border-radius: 8px;
    font-size: .75rem;
    background: #fff;
    color: #185FA5;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
}
.btn-sm-label:hover { background: #E6F1FB; color: #185FA5; }

/* ── Badges ── */
.badge-pill {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: .7rem;
    font-weight: 500;
}

/* Status */
.s-new              { background: #E6F1FB; color: #185FA5; }
.s-in_progress      { background: #FAEEDA; color: #854F0B; }
.s-waiting_response { background: #FCEBEB; color: #A32D2D; }
.s-resolved         { background: #EAF3DE; color: #3B6D11; }
.s-closed           { background: #F1EFE8; color: #5F5E5A; }

/* Priority */
.p-low      { background: #EAF3DE; color: #3B6D11; }
.p-medium   { background: #FAEEDA; color: #854F0B; }
.p-high     { background: #FAEEDA; color: #854F0B; }
.p-urgent   { background: #FCEBEB; color: #A32D2D; }
.p-critical { background: #FCEBEB; color: #A32D2D; }

/* Count badge */
.badge-count {
    background: #FCEBEB;
    color: #A32D2D;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 500;
}

/* ── Empty state ── */
.empty-state { text-align: center; color: #94a3b8; padding: 2.5rem 1rem; }
.empty-state i { font-size: 2rem; }
.empty-state p { font-size: .8rem; margin-top: .5rem; }

/* ── Responsive ── */
@media (max-width: 991px) {
    .stat-grid-4 { grid-template-columns: repeat(2, minmax(0,1fr)); }
    .stat-grid-3 { grid-template-columns: repeat(2, minmax(0,1fr)); }
}
@media (max-width: 576px) {
    .stat-grid-4, .stat-grid-3 { grid-template-columns: 1fr; }
    .ts-title { max-width: 160px; }
}
</style>
@endpush
