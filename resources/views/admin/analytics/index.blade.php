@extends('layouts.app')

@section('title', 'Analitik Tiket')
@section('page_title', 'Analitik')
@section('breadcrumb', 'Home / Analitik')



@section('content')

{{-- Inject PHP → JS hanya kalau ada data --}}
@if($hasData)
<script>
    window.ANALYTICS_DATA = @json($analytics);
</script>
@endif

<div class="analytics-wrap">

    {{--HERO--}}
    <section class="analytics-hero">
        <div>
            <span class="hero-kicker">Analitik Tiket</span>
            <h3>Pantau performa HelpCenter secara menyeluruh</h3>
            <p>Volume tiket, tren status, kategori teratas, dan kecepatan penyelesaian dalam satu tampilan.</p>
        </div>
    </section>

    {{--FILTER--}}
    <form method="GET" action="{{ route('admin.analytics') }}" id="analyticsFilterForm">
        <div class="filter-card">
            <div class="analytics-filter-grid">
                <div class="filter-field">
                    <label class="f-label">Tanggal Mulai</label>
                    <input type="date" id="analyticsStartDate" name="start_date" class="f-input"
                           value="{{ $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : $startDate }}"
                           max="{{ $endDate instanceof \Carbon\Carbon ? $endDate->format('Y-m-d') : $endDate }}">
                </div>
                <div class="filter-field">
                    <label class="f-label">Tanggal Berakhir</label>
                    <input type="date" id="analyticsEndDate" name="end_date" class="f-input"
                           value="{{ $endDate instanceof \Carbon\Carbon ? $endDate->format('Y-m-d') : $endDate }}"
                           min="{{ $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : $startDate }}">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-primary-sm btn-filter-submit">
                        <i class='bx bx-search'></i> Hasilkan Laporan
                    </button>
                    <a href="{{ route('admin.analytics') }}" class="btn-outline-sm">Reset</a>
                </div>
            </div>
        </div>
    </form>

    @if($hasData)

    {{--SUMMARY CARDS--}}
    <section class="summary-grid">
        <article class="summary-card">
            <span class="sc-label">Total Tiket</span>
            <strong class="sc-value">{{ $totalTickets }}</strong>
            <span class="sc-sub">Akumulasi seluruh tiket dalam rentang ini</span>
        </article>
        <article class="summary-card sc-orange">
            <span class="sc-label">Waktu Resolusi Rata-rata</span>
            <strong class="sc-value" style="font-size:1.6rem;">
                {{ number_format($analytics['avg_resolution_time_minutes'], 0) }}
                <span style="font-size:1rem; font-weight:600;">min</span>
            </strong>
            <span class="sc-sub">Rata-rata dari data SLA tracking</span>
        </article>
        <article class="summary-card sc-green">
            <span class="sc-label">Kategori Teratas</span>
            <strong class="sc-value" style="font-size:1.2rem; word-break:break-word;">{{ $mostUsedCategory }}</strong>
            <span class="sc-sub">Kategori dengan tiket terbanyak</span>
        </article>
        <article class="summary-card sc-yellow">
            <span class="sc-label">Prioritas Paling Umum</span>
            <strong class="sc-value" style="text-transform:capitalize; font-size:1.5rem;">{{ $mostUsedPriority }}</strong>
            <span class="sc-sub">Berdasarkan jumlah tiket per prioritas</span>
        </article>
    </section>

    {{--CHART ROW--}}
    <div class="chart-row">

        {{-- Kiri: Status Doughnut --}}
        <div class="panel-card">
            <div class="panel-head">
                <div>
                    <h5>Statistik Tiket</h5>
                    <p>{{ $totalTickets }} total tiket dalam rentang ini</p>
                </div>
            </div>
            <div class="status-inner">

                <div class="doughnut-wrap">
                    <canvas id="statusChart"></canvas>
                    <div class="doughnut-center">
                        <h4>{{ $newTicketsCount }}</h4>
                        <small>Baru ({{ $newTicketsPct }}%)</small>
                    </div>
                </div>

                @php
                    $sCfg = [
                        'new'              => ['hex'=>'#696cff','bg'=>'rgba(105,108,255,0.12)','icon'=>'bx bx-file',        'desc'=>'Menunggu peninjauan'],
                        'in_progress'      => ['hex'=>'#03c3ec','bg'=>'rgba(3,195,236,0.12)',  'icon'=>'bx bx-time-five',    'desc'=>'Sedang ditangani'],
                        'waiting_response' => ['hex'=>'#ffab00','bg'=>'rgba(255,171,0,0.12)',  'icon'=>'bx bx-message-dots', 'desc'=>'Menunggu respons'],
                        'resolved'         => ['hex'=>'#71dd37','bg'=>'rgba(113,221,55,0.12)', 'icon'=>'bx bx-check-circle', 'desc'=>'Sudah diselesaikan'],
                        'closed'           => ['hex'=>'#8592a3','bg'=>'rgba(133,146,163,0.12)','icon'=>'bx bx-x-circle',    'desc'=>'Ditutup'],
                    ];
                    $statusNames = [
                        'new' => 'Baru',
                        'in_progress' => 'Sedang Ditangani',
                        'waiting_response' => 'Menunggu Respons',
                        'resolved' => 'Selesai',
                        'closed' => 'Ditutup',
                    ];
                @endphp

                <div class="status-list">
                    @foreach($analytics['tickets_by_status'] as $item)
                    @php
                        $sk  = $item['status'];
                        $cfg = $sCfg[$sk] ?? ['hex'=>'#8592a3','bg'=>'rgba(133,146,163,0.12)','icon'=>'bx bx-circle','desc'=>''];
                        $cnt = $item['count'] >= 1000 ? number_format($item['count']/1000,1).'k' : $item['count'];
                    @endphp
                    <div class="status-item">
                        <div class="si-left">
                            <div class="si-icon-box" style="background:{{ $cfg['bg'] }}">
                                <i class="{{ $cfg['icon'] }}" style="color:{{ $cfg['hex'] }}"></i>
                            </div>
                            <div class="si-text">
                                <h6>{{ $statusNames[$sk] ?? str_replace('_',' ',$sk) }}</h6>
                                <small>{{ $cfg['desc'] }}</small>
                            </div>
                        </div>
                        <span class="si-count">{{ $cnt }}</span>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>

        {{-- Kanan: Monthly Line Chart --}}
        <div class="panel-card">
            <div class="panel-head">
                <div>
                    <h5>Ringkasan Tiket Bulanan</h5>
                    <p>Jumlah tiket masuk per bulan</p>
                </div>
            </div>
            <div class="monthly-meta">
                <div class="mm-item">
                    <h3>{{ $monthlyAverage }}</h3>
                    <small>Rata-rata per Bulan</small>
                </div>
                <div class="mm-item">
                    <h4>{{ $peakMonthCount }}</h4>
                    <small>Bulan Tertinggi</small>
                </div>
                <div class="trend-badge {{ $trendIndicator >= 0 ? 'up' : 'down' }}">
                    <i class='bx {{ $trendIndicator >= 0 ? "bx-trending-up" : "bx-trending-down" }}'></i>
                    <span>{{ abs($trendIndicator) }}%</span>
                </div>
            </div>
            <div style="position:relative; height:250px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

    </div>

    {{--CATEGORY BAR--}}
    <div class="panel-card">
        <div class="panel-head">
            <div>
                <h5>Tiket Berdasarkan Kategori</h5>
                <p>Distribusi tiket ke setiap kategori layanan</p>
            </div>
        </div>
        <div class="cat-chart-wrap">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    {{--EXPORT--}}
    @else

    <div class="empty-state">
        <i class='bx bx-bar-chart-alt-2'></i>
        <p class="mb-1 fw-bold">Belum ada data analitik untuk rentang tanggal yang dipilih.</p>
        <small>Coba pilih rentang tanggal yang berbeda lalu klik Hasilkan Laporan.</small>
    </div>

    @endif

</div>
@endsection
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>

let statusChartInstance = null;
let monthlyChartInstance = null;
let categoryChartInstance = null;

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    if (window.__analyticsLoaded) return;
    window.__analyticsLoaded = true;

    if (!window.ANALYTICS_DATA) return;

    const D = window.ANALYTICS_DATA;
    const MID = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const fmtM = ym => { const p = ym.split('-'); return MID[+p[1]-1]+' '+p[0]; };

    /* 1 STATUS DOUGHNUT */
    const SHX = { new:'#696cff', baru:'#696cff', in_progress:'#03c3ec', waiting_response:'#ffab00', resolved:'#71dd37', closed:'#8592a3' };
    const STATUS_NAMES = {
        new: 'Baru',
        baru: 'Baru',
        in_progress: 'Sedang Ditangani',
        waiting_response: 'Menunggu Respons',
        resolved: 'Selesai',
        closed: 'Ditutup',
    };
    const sd  = D.tickets_by_status || [];
    const sc  = document.getElementById('statusChart');
    if (sd.length && sc) {
        if (statusChartInstance) {
            statusChartInstance.destroy();
        }

        statusChartInstance = new Chart(sc.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: sd.map(s => STATUS_NAMES[s.status] || s.status.replace(/_/g,' ')),
                datasets: [{ data: sd.map(s=>s.count), backgroundColor: sd.map(s=>SHX[s.status]||'#8592a3'), borderWidth:0, cutout:'75%' }]
            },
            options: {
                responsive:true, maintainAspectRatio:false,
                plugins: {
                    legend:{ display:false },
                    tooltip:{ callbacks:{ label(c){ const t=c.dataset.data.reduce((a,b)=>a+b,0); return c.label+': '+c.parsed+' ('+(c.parsed/t*100).toFixed(1)+'%)'; } } }
                }
            }
        });
    }

    /* 2 MONTHLY LINE */
    const mo  = D.monthly_tickets || {};
    const mks = Object.keys(mo).sort();
    const mct = mks.map(m=>mo[m]);
    const mc  = document.getElementById('monthlyChart');
    if (mks.length && mc) {
        if (monthlyChartInstance) {
            monthlyChartInstance.destroy();
        }

        monthlyChartInstance = new Chart(mc.getContext('2d'), {
            type:'line',
            data:{
                labels: mks.map(fmtM),
                datasets:[{ label:'Tiket per Bulan', data:mct, borderColor:'#696cff', backgroundColor:'rgba(105,108,255,0.10)', fill:true, tension:0.4, pointRadius:5, pointHoverRadius:7, pointBackgroundColor:'#696cff', pointBorderColor:'#fff', pointBorderWidth:2, borderWidth:3 }]
            },
            options:{
                responsive:true, maintainAspectRatio:false,
                interaction:{ intersect:false, mode:'index' },
                plugins:{ legend:{display:false}, tooltip:{ backgroundColor:'rgba(15,23,42,0.85)', padding:12, callbacks:{ label(c){ return c.parsed.y+' tiket'; } } } },
                scales:{
                    y:{ beginAtZero:true, ticks:{ stepSize: Math.ceil(Math.max(...mct,1)/5) }, grid:{ color:'rgba(0,0,0,0.05)', drawBorder:false } },
                    x:{ grid:{display:false}, ticks:{maxRotation:45, minRotation:0} }
                }
            }
        });
    }

    /* 3 CATEGORY BAR */
    const cd  = D.tickets_by_category || {};
    const cl  = Object.keys(cd);
    const cv  = Object.values(cd);
    const cc  = document.getElementById('categoryChart');
    if (cl.length && cc) {
        if (categoryChartInstance) {
            categoryChartInstance.destroy();
        }

            categoryChartInstance = new Chart(cc.getContext('2d'), {
            type:'bar',
            data:{ labels:cl, datasets:[{ label:'Jumlah Tiket', data:cv, backgroundColor:'#696cff', borderRadius:8, maxBarThickness:28 }] },
            options:{
                responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label(c){ return 'Jumlah: '+c.parsed.y+' tiket'; } } } },
                scales:{
                    y:{ beginAtZero:true, ticks:{stepSize:5}, grid:{color:'rgba(0,0,0,0.05)', drawBorder:false} },
                    x:{ grid:{display:false} }
                }
            }
        });
    }

    /* 4 EXPORT */
    window.exportReport = function () {
        typeof Swal !== 'undefined'
            ? Swal.fire({ icon:'info', title:'Fitur Ekspor', text:'Fitur ekspor analitik akan tersedia pada pembaruan berikutnya.', confirmButtonColor:'#696cff' })
            : alert('Fitur ekspor analitik akan tersedia pada pembaruan berikutnya.');
    };

    const filterForm = document.getElementById('analyticsFilterForm');
    if (filterForm) {
        const startInput = document.getElementById('analyticsStartDate');
        const endInput = document.getElementById('analyticsEndDate');

        const syncRange = () => {
            if (startInput && startInput.value && endInput) endInput.min = startInput.value;
            if (endInput && endInput.value && startInput) startInput.max = endInput.value;
        };
        syncRange();
        if (startInput) startInput.addEventListener('change', syncRange);
        if (endInput) endInput.addEventListener('change', syncRange);

        filterForm.addEventListener('submit', function (e) {
            const startInput = filterForm.querySelector('input[name=\"start_date\"]');
            const endInput = filterForm.querySelector('input[name=\"end_date\"]');
            if (!startInput || !endInput) return;

            if (startInput.value && endInput.value && startInput.value > endInput.value) {
                const tmp = startInput.value;
                startInput.value = endInput.value;
                endInput.value = tmp;
            }
        });
    }
})();
</script>

<style>

    .analytics-wrap { display: flex; flex-direction: column; gap: 1.5rem; overflow-x: hidden; }

    /* HERO */
    .analytics-hero {
        display: flex; justify-content: space-between; align-items: center;
        gap: 1rem; padding: 1.25rem 1.375rem;
        background: linear-gradient(135deg, #eef2ff 0%, #fff7ed 100%);
        border: 1px solid rgba(79,70,229,0.12);
        border-radius: 20px;
        box-shadow: 0 6px 18px rgba(79,70,229,0.07);
    }
    .hero-kicker {
        display: inline-flex; align-items: center;
        padding: 0.35rem 0.8rem; border-radius: 999px;
        background: rgba(79,70,229,0.12); color: var(--primary);
        font-weight: 800; font-size: 0.75rem;
        letter-spacing: 0.06em; text-transform: uppercase;
    }
    .analytics-hero h3 {
        margin: 0.625rem 0 0.35rem;
        font-size: clamp(1.15rem, 2vw, 1.55rem);
        font-weight: 800; color: var(--text);
    }
    .analytics-hero > div > p { color: var(--text-muted); font-size: 0.9rem; margin: 0; max-width: 620px; }

    /* FILTER CARD */
    .filter-card {
        background: white; border: 1px solid var(--border);
        border-radius: 18px; padding: 1rem; box-shadow: var(--shadow-sm);
    }
    .analytics-filter-grid {
        display: grid;
        grid-template-columns: minmax(0,1fr) minmax(0,1fr) auto;
        gap: 0.75rem;
        align-items: end;
    }
    .filter-field { min-width: 0; }
    .filter-actions {
        display: flex;
        gap: 0.5rem;
        align-items: end;
        justify-content: flex-end;
    }
    .filter-card .f-label {
        font-weight: 700; font-size: 0.8rem; color: var(--text-muted);
        margin-bottom: 0.3rem; display: block;
    }
    .filter-card .f-input {
        border-radius: 12px; border: 1px solid var(--border);
        font-size: 0.86rem; padding: 0.52rem 0.75rem; width: 100%;
        outline: none; transition: border-color 0.2s; font-family: inherit;
    }
    .filter-card .f-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
    .btn-filter-submit { min-width: 220px; justify-content: center; }

    /* SUMMARY CARDS */
    .summary-grid { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 1rem; }
    .summary-card {
        background: white; border: 1px solid var(--border);
        border-radius: 18px; padding: 1rem;
        display: grid; gap: 0.35rem;
        box-shadow: var(--shadow-sm); transition: all 0.25s;
    }
    .summary-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
    .summary-card > .sc-label  { color: var(--text-muted); font-weight: 700; font-size: 0.82rem; }
    .summary-card > .sc-value  { font-size: 1.7rem; font-weight: 800; color: var(--text); line-height: 1; }
    .summary-card > .sc-sub    { color: #94a3b8; font-size: 0.8rem; }
    .sc-orange { background: linear-gradient(180deg,#fff7ed,#fff); border-color: rgba(249,115,22,0.2); }
    .sc-green  { background: linear-gradient(180deg,#f0fdf4,#fff); border-color: rgba(34,197,94,0.2); }
    .sc-yellow { background: linear-gradient(180deg,#fefce8,#fff); border-color: rgba(234,179,8,0.2); }

    /* PANEL CARD (sama dgn dashboard) */
    .panel-card {
        background: white; border: 1px solid var(--border);
        border-radius: 20px; padding: 1rem; box-shadow: var(--shadow-sm);
        min-width: 0;
        overflow: hidden;
    }
    .panel-head {
        display: flex; justify-content: space-between; gap: 1rem;
        align-items: flex-start; margin-bottom: 0.8rem;
    }
    .panel-head h5 { margin: 0; font-size: 0.98rem; font-weight: 800; color: var(--text); }
    .panel-head p  { margin: 0.2rem 0 0; color: var(--text-muted); font-size: 0.82rem; }

    /* CHART ROW */
    .chart-row { display: grid; grid-template-columns: minmax(0,5fr) minmax(0,7fr); gap: 1rem; }
    .chart-row > .panel-card { min-width: 0; }

    /* Doughnut */
    .status-inner { display: grid; grid-template-columns: auto 1fr; gap: 1.25rem; align-items: center; }
    .doughnut-wrap { position: relative; width: 150px; height: 150px; flex-shrink: 0; }
    .doughnut-center {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%,-50%); text-align: center; pointer-events: none;
    }
    .doughnut-center h4    { font-size: 1.5rem; font-weight: 800; color: var(--text); margin: 0; line-height: 1; }
    .doughnut-center small { font-size: 0.7rem; color: var(--text-muted); display: block; margin-top: 0.2rem; }

    /* Status legend list */
    .status-list  { display: flex; flex-direction: column; gap: 0.625rem; }
    .status-item  { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; }
    .si-left      { display: flex; align-items: center; gap: 0.625rem; }
    .si-icon-box  { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.075rem; }
    .si-text h6   { font-size: 0.875rem; font-weight: 700; color: var(--text); margin: 0; text-transform: capitalize; }
    .si-text small{ font-size: 0.73rem; color: var(--text-muted); }
    .si-count     { font-size: 1rem; font-weight: 800; color: var(--text); white-space: nowrap; }

    /* Monthly meta */
    .monthly-meta { display: flex; align-items: center; gap: 1.5rem; margin-bottom: 0.875rem; flex-wrap: wrap; }
    .mm-item h3   { font-size: 1.75rem; font-weight: 800; color: var(--text); margin: 0; line-height: 1; }
    .mm-item h4   { font-size: 1.375rem; font-weight: 800; color: var(--text); margin: 0; line-height: 1; }
    .mm-item small{ font-size: 0.78rem; color: var(--text-muted); display: block; margin-top: 0.2rem; }
    .trend-badge       { display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 800; font-size: 0.9rem; }
    .trend-badge.up    { color: #16a34a; }
    .trend-badge.down  { color: #dc2626; }

    /* Category chart */
    .cat-chart-wrap { position: relative; height: 260px; overflow: hidden; max-width: 100%; }
    .cat-chart-wrap canvas { display: block; max-width: 100% !important; }

    /* BUTTONS */
    .btn-primary-sm {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.62rem 1rem; background: var(--gradient); color: white;
        border: none; border-radius: 10px; font-weight: 700; font-size: 0.84rem;
        text-decoration: none; cursor: pointer;
        transition: all 0.25s; box-shadow: var(--shadow-colored);
    }
    .btn-primary-sm:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(79,70,229,0.3); color: white; }
    .btn-primary-sm.w-full { width: 100%; justify-content: center; }
    .btn-outline-sm {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.62rem 1rem; background: transparent; color: var(--primary);
        border: 1.5px solid var(--primary); border-radius: 10px; font-weight: 700;
        font-size: 0.84rem; cursor: pointer; transition: all 0.25s;
    }
    .btn-outline-sm:hover { background: var(--primary); color: white; }

    /* EMPTY */
    .empty-state {
        border: 1px dashed rgba(148,163,184,0.5); border-radius: 18px;
        padding: 3rem; color: var(--text-muted); text-align: center;
    }
    .empty-state i { font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 0.75rem; }
    .export-center { display: flex; justify-content: center; padding: 0.5rem 0; }

    /* RESPONSIVE */
    @media (max-width: 1199px) {
        .summary-grid { grid-template-columns: repeat(2,minmax(0,1fr)); }
        .chart-row    { grid-template-columns: 1fr; }
        .analytics-filter-grid { grid-template-columns: 1fr 1fr; }
        .filter-actions { grid-column: span 2; justify-content: flex-start; }
    }
    @media (max-width: 767px) {
        .analytics-hero { flex-direction: column; align-items: flex-start; padding: 1rem; border-radius: 16px; }
        .summary-grid   { grid-template-columns: 1fr; }
        .status-inner   { grid-template-columns: 1fr; justify-items: center; }
        .doughnut-wrap  { width: 150px; height: 150px; }
        .monthly-meta   { gap: 0.75rem; }
        .analytics-filter-grid { grid-template-columns: 1fr; }
        .filter-actions { grid-column: auto; width: 100%; }
        .btn-filter-submit, .filter-actions .btn-outline-sm { width: 100%; justify-content: center; }
    }
</style>


