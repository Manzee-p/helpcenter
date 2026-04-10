@extends('layouts.app')

@section('title', 'Laporan Sistem')
@section('page_title', 'Laporan Sistem')
@section('breadcrumb', 'Home / Laporan')

@push('styles')
<style>
.reports-wrap { display: flex; flex-direction: column; gap: 1.5rem; }

/* ── HERO ── */
.reports-hero {
    padding: 1.5rem 1.875rem;
    background: white; border: 1px solid var(--border);
    border-radius: 28px; box-shadow: var(--shadow-sm);
}
.reports-hero h4 { margin: 0 0 .25rem; font-size: 1.375rem; font-weight: 800; color: var(--text); }
.reports-hero p  { margin: 0; color: var(--text-muted); font-size: .9375rem; max-width: 760px; }

/* ── FILTER CARD ── */
.filter-card {
    background: white; border: 1px solid var(--border);
    border-radius: 22px; padding: 1.25rem 1.5rem;
    box-shadow: var(--shadow-sm);
}
.filter-row { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 1rem; align-items: end; }
.filter-label { font-size: .85rem; font-weight: 700; color: var(--text-muted); margin-bottom: .4rem; display: block; }
.filter-input, .filter-select {
    width: 100%; padding: .7rem 1rem; border: 1px solid var(--border);
    border-radius: 14px; font-size: .9rem; color: var(--text); background: var(--bg);
}
.filter-input:focus, .filter-select:focus {
    outline: none; border-color: var(--primary); background: white;
}
.btn-filter {
    padding: .7rem 1.375rem; background: var(--gradient);
    color: white; border: none; border-radius: 14px;
    font-weight: 700; font-size: .9rem; cursor: pointer;
    display: flex; align-items: center; gap: .4rem;
    box-shadow: var(--shadow-colored); transition: all .2s; white-space: nowrap;
}
.btn-filter:hover { transform: translateY(-2px); }

/* ── SUMMARY GRID ── */
.summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 1rem; }
.sum-card {
    background: white; border: 1px solid var(--border);
    border-radius: 22px; padding: 1.375rem;
    display: flex; justify-content: space-between; align-items: center;
    box-shadow: var(--shadow-sm);
}
.sum-card p     { margin: 0 0 .25rem; color: var(--text-muted); font-size: .875rem; font-weight: 600; }
.sum-card h3    { margin: 0; font-size: 2rem; font-weight: 800; color: var(--text); line-height: 1; }
.sum-card small { display: block; margin-top: .25rem; font-size: .8rem; }
.sum-icon {
    width: 56px; height: 56px; border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; flex-shrink: 0;
}
.sum-icon--primary { background: rgba(79,70,229,.1);  color: var(--primary); }
.sum-icon--success { background: rgba(34,197,94,.1);  color: #16a34a; }
.sum-icon--info    { background: rgba(59,130,246,.1); color: #1d4ed8; }

/* ── MAIN GRID ── */
.main-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

/* ── PANEL CARD ── */
.panel-card {
    background: white; border: 1px solid var(--border);
    border-radius: 26px; box-shadow: var(--shadow-sm); overflow: hidden;
}
.panel-header {
    padding: 1.25rem 1.375rem; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center; gap: 1rem;
}
.panel-header h5 { margin: 0; font-size: 1.0625rem; font-weight: 800; color: var(--text); }
.panel-body { padding: 1.375rem; max-height: 420px; overflow-y: auto; }
.panel-body::-webkit-scrollbar { width: 6px; }
.panel-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

/* ── VENDOR SATISFACTION ── */
.vendor-sat-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: .875rem 0; border-bottom: 1px solid var(--border); gap: 1rem;
}
.vendor-sat-item:last-child { border-bottom: none; padding-bottom: 0; }
.rank-avatar {
    width: 36px; height: 36px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: .9rem; flex-shrink: 0;
}
.rank-1 { background: rgba(79,70,229,.1);  color: var(--primary); }
.rank-2 { background: rgba(34,197,94,.1);  color: #16a34a; }
.rank-3 { background: rgba(59,130,246,.1); color: #1d4ed8; }
.rank-n { background: rgba(249,115,22,.1); color: #c2410c; }
.vendor-sat-info h6 { margin: 0; font-size: .9375rem; font-weight: 700; color: var(--text); }
.vendor-sat-info small { color: var(--text-muted); font-size: .8rem; }
.vendor-sat-score strong { display: block; font-weight: 800; font-size: 1.05rem; color: var(--text); text-align: right; }
.vendor-sat-score small  { color: var(--text-muted); font-size: .78rem; }

/* ── RESOLUTION TREND ── */
.trend-filter-pills { display: flex; flex-wrap: wrap; gap: .5rem; }
.pill-btn {
    padding: .4rem .875rem; border-radius: 999px;
    border: 1.5px solid var(--border); background: white;
    color: var(--text-muted); font-weight: 700; font-size: .8rem;
    cursor: pointer; transition: all .2s;
}
.pill-btn:hover, .pill-btn.active { border-color: var(--primary); background: var(--primary); color: white; }
.pill-btn--success.active { background: #16a34a; border-color: #16a34a; }
.pill-btn--warning.active { background: #d97706; border-color: #d97706; }
.pill-btn--danger.active  { background: #dc2626; border-color: #dc2626; }

.trend-item { margin-bottom: 1.25rem; animation: slideIn .35s ease forwards; opacity: 0; }
@keyframes slideIn { from { opacity:0; transform: translateX(-12px); } to { opacity:1; transform: translateX(0); } }
.trend-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: .5rem; }
.trend-period { font-size: .875rem; font-weight: 700; color: var(--text-muted); }
.trend-badge {
    padding: .3rem .75rem; border-radius: 999px;
    font-size: .78rem; font-weight: 800; color: white;
}
.trend-bar-rail { width: 100%; height: 30px; border-radius: 8px; background: var(--bg); overflow: hidden; }
.trend-bar      { height: 100%; border-radius: 8px; transition: width .5s ease; }
.bar-green  { background: linear-gradient(90deg, #22c55e, #4ade80); }
.bar-yellow { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.bar-red    { background: linear-gradient(90deg, #ef4444, #f87171); }

.trend-summary-row { display: grid; grid-template-columns: repeat(3,1fr); gap: .75rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border); }
.tsum-box { text-align: center; padding: .5rem; }
.tsum-box i { font-size: 1.375rem; display: block; }
.tsum-box small  { color: var(--text-muted); font-size: .78rem; display: block; margin: .25rem 0; }
.tsum-box strong { font-size: 1.05rem; font-weight: 800; display: block; }

/* ── WARNING SUMMARY ── */
.warn-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; }
.warn-box {
    padding: 1.25rem; border-radius: 18px;
    background: var(--bg); border: 1px solid var(--border); text-align: center;
}
.warn-box small  { color: var(--text-muted); font-size: .85rem; display: block; font-weight: 700; }
.warn-box strong { font-size: 2rem; font-weight: 800; display: block; margin-top: .4rem; color: var(--text); }

/* ── EMPTY ── */
.empty-state {
    text-align: center; padding: 3rem; color: var(--text-muted);
}
.empty-state i { font-size: 3rem; color: var(--text-light); display: block; margin-bottom: .75rem; }

/* ── RESPONSIVE ── */
@media (max-width: 1199px) {
    .filter-row { grid-template-columns: 1fr 1fr; }
    .summary-grid, .main-grid { grid-template-columns: 1fr; }
}
@media (max-width: 767px) {
    .filter-row { grid-template-columns: 1fr; }
    .warn-grid  { grid-template-columns: 1fr; }
    .trend-summary-row { grid-template-columns: repeat(3,1fr); }
}
</style>
@endpush

@section('content')
<div class="reports-wrap">

    {{-- ── HERO ── --}}
    <div class="reports-hero">
        <h4>Laporan Sistem</h4>
        <p>Ringkasan performa tiket, kepuasan vendor, dan tren penyelesaian dalam tampilan yang lebih rapi.</p>
    </div>

    {{-- ── FILTER ── --}}
    <form method="GET" action="{{ route('admin.reports') }}" class="filter-card" id="reportsFilterForm">
        <div class="filter-row">
            <div>
                <label class="filter-label">Jenis Periode</label>
                <select name="period_type" class="filter-select">
                    <option value="monthly" {{ request('period_type','monthly') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="weekly"  {{ request('period_type') === 'weekly'  ? 'selected' : '' }}>Mingguan</option>
                </select>
            </div>
            <div>
                <label class="filter-label">Tanggal Mulai</label>
                <input type="date" id="reportsStartDate" name="start_date" class="filter-input"
                    value="{{ request('start_date', now()->subMonths(6)->toDateString()) }}">
            </div>
            <div>
                <label class="filter-label">Tanggal Selesai</label>
                <input type="date" id="reportsEndDate" name="end_date" class="filter-input"
                    value="{{ request('end_date', now()->toDateString()) }}">
            </div>
            <div>
                <div style="display:flex;gap:.5rem;align-items:center;">
                    <button type="submit" class="btn-filter">
                        <i class='bx bx-search'></i> Tampilkan Laporan
                    </button>
                    <a href="{{ route('admin.reports') }}" class="pill-btn">Reset</a>
                </div>
            </div>
        </div>
    </form>

    @if($reportData)

    {{-- ── SUMMARY CARDS ── --}}
    <div class="summary-grid">
        <div class="sum-card">
            <div>
                <p>Total Tiket</p>
                <h3>{{ $reportData['summary']['total_tickets'] ?? 0 }}</h3>
                <small class="text-muted">
                    {{ \Carbon\Carbon::parse(request('start_date', now()->subMonths(6)))->isoFormat('D MMM YYYY') }} —
                    {{ \Carbon\Carbon::parse(request('end_date', now()))->isoFormat('D MMM YYYY') }}
                </small>
            </div>
            <div class="sum-icon sum-icon--primary"><i class='bx bx-file'></i></div>
        </div>
        <div class="sum-card">
            <div>
                <p>Tiket Selesai</p>
                <h3>{{ $reportData['summary']['resolved_tickets'] ?? 0 }}</h3>
                <small style="color:#16a34a;">{{ $reportData['summary']['resolution_rate'] ?? 0 }}% tingkat penyelesaian</small>
            </div>
            <div class="sum-icon sum-icon--success"><i class='bx bx-check-circle'></i></div>
        </div>
        <div class="sum-card">
            <div>
                <p>Rata-rata Kepuasan</p>
                <h3>{{ number_format($reportData['summary']['average_satisfaction'] ?? 0, 2) }}/5</h3>
                <small class="text-muted">{{ $reportData['summary']['low_rating_total'] ?? 0 }} ulasan rendah pada periode ini</small>
            </div>
            <div class="sum-icon sum-icon--info"><i class='bx bx-star'></i></div>
        </div>
    </div>

    {{-- ── MAIN GRID ── --}}
    <div class="main-grid">

        {{-- Vendor Satisfaction --}}
        <div class="panel-card">
            <div class="panel-header">
                <h5>Pemantauan Kepuasan Vendor</h5>
            </div>
            <div class="panel-body">
                @php $vendorSat = $reportData['vendor_satisfaction'] ?? []; @endphp
                @if(empty($vendorSat))
                    <div class="empty-state">
                        <i class='bx bx-user-x'></i>
                        <p>Belum ada data kepuasan vendor</p>
                    </div>
                @else
                    @foreach($vendorSat as $idx => $vs)
                    @php
                        $rankClass = match($idx) { 0 => 'rank-1', 1 => 'rank-2', 2 => 'rank-3', default => 'rank-n' };
                    @endphp
                    <div class="vendor-sat-item">
                        <div style="display:flex;align-items:center;gap:.875rem;flex:1;min-width:0;">
                            <div class="rank-avatar {{ $rankClass }}">{{ $idx + 1 }}</div>
                            <div class="vendor-sat-info">
                                <h6>{{ $vs['name'] }}</h6>
                                <small>{{ $vs['company_name'] ?: 'Tanpa perusahaan' }}</small>
                            </div>
                        </div>
                        <div class="vendor-sat-score">
                            <strong>{{ number_format($vs['average_rating'] ?? 0, 2) }}/5</strong>
                            <small>{{ $vs['total_feedbacks'] }} ulasan &bull; {{ $vs['low_rating_count'] }} rendah</small>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Resolution Trend --}}
        <div class="panel-card">
            <div class="panel-header" style="flex-direction:column;align-items:flex-start;gap:.75rem;">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                    <h5>Tren Waktu Penyelesaian</h5>
                    <span style="font-size:.78rem;font-weight:700;padding:.3rem .75rem;border-radius:999px;background:rgba(79,70,229,.1);color:var(--primary);">Rata-rata (menit)</span>
                </div>
                <div class="trend-filter-pills">
                    <button type="button" class="pill-btn active" data-filter="all" onclick="filterTrend(this,'all')">
                        <i class='bx bx-list-ul'></i> Semua
                    </button>
                    <button type="button" class="pill-btn pill-btn--success" data-filter="fastest" onclick="filterTrend(this,'fastest')">
                        <i class='bx bx-rocket'></i> Cepat
                    </button>
                    <button type="button" class="pill-btn pill-btn--warning" data-filter="average" onclick="filterTrend(this,'average')">
                        <i class='bx bx-timer'></i> Sedang
                    </button>
                    <button type="button" class="pill-btn pill-btn--danger" data-filter="slowest" onclick="filterTrend(this,'slowest')">
                        <i class='bx bx-hourglass'></i> Lambat
                    </button>
                </div>
            </div>
            <div class="panel-body" id="trendBody">
                @php $trendData = $reportData['resolution_trend'] ?? []; @endphp
                @if(empty($trendData))
                    <div class="empty-state">
                        <i class='bx bx-line-chart'></i>
                        <p>Belum ada data tren pada periode yang dipilih</p>
                    </div>
                @else
                    <div id="trendItems">
                        @php
                            $maxTime = collect($trendData)->max('avg_resolution_time') ?: 1;
                        @endphp
                        @foreach($trendData as $i => $t)
                        @php
                            $mins  = abs(floatval($t['avg_resolution_time'] ?? 0));
                            $pct   = $maxTime > 0 ? max(($mins / $maxTime) * 100, 5) : 5;
                            $cat   = $t['category'] ?? ($mins < 720 ? 'fastest' : ($mins < 1440 ? 'average' : 'slowest'));
                            $barcls = match($cat) { 'fastest' => 'bar-green', 'average' => 'bar-yellow', default => 'bar-red' };
                            $bdgbg  = match($cat) { 'fastest' => '#22c55e', 'average' => '#f59e0b', default => '#ef4444' };
                            // Format time
                            if ($mins < 60) { $fmtTime = round($mins) . ' mnt'; }
                            elseif ($mins < 1440) { $h = floor($mins/60); $m = round(fmod($mins,60)); $fmtTime = $m > 0 ? "{$h}j {$m}m" : "{$h}j"; }
                            else { $d = floor($mins/1440); $h = floor(fmod($mins,1440)/60); $fmtTime = $h > 0 ? "{$d}h {$h}j" : "{$d}h"; }
                            // Format period
                            if (preg_match('/^\d{4}-\d{2}$/', $t['period'] ?? '')) {
                                [$yr, $mo] = explode('-', $t['period']);
                                $fmtPeriod = \Carbon\Carbon::createFromDate($yr, $mo, 1)->locale('id')->isoFormat('MMM YYYY');
                            } else { $fmtPeriod = $t['period'] ?? ''; }
                        @endphp
                        <div class="trend-item" data-category="{{ $cat }}" style="animation-delay:{{ $i * 0.05 }}s;">
                            <div class="trend-head">
                                <span class="trend-period">{{ $fmtPeriod }}</span>
                                <span class="trend-badge" style="background:{{ $bdgbg }};">{{ $fmtTime }}</span>
                            </div>
                            <div class="trend-bar-rail">
                                <div class="trend-bar {{ $barcls }}" style="width:{{ $pct }}%;"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div id="trendEmptyByFilter" class="empty-state" style="display:none;padding:1.5rem 0;">
                        <i class='bx bx-info-circle'></i>
                        <p>Tidak ada data untuk filter ini.</p>
                    </div>

                    {{-- Trend Summary --}}
                    @php
                        $times    = collect($trendData)->pluck('avg_resolution_time')->map(fn($v) => abs(floatval($v)))->filter(fn($v) => $v > 0);
                        $fastest  = $times->min() ?? 0;
                        $slowest  = $times->max() ?? 0;
                        $avgTime  = $times->avg() ?? 0;
                        $fmtFn = function($m) {
                            if ($m < 60) return round($m) . ' mnt';
                            if ($m < 1440) { $h=floor($m/60); $mn=round(fmod($m,60)); return $mn > 0 ? "{$h}j {$mn}m" : "{$h}j"; }
                            $d=floor($m/1440); $h=floor(fmod($m,1440)/60); return $h > 0 ? "{$d}h {$h}j" : "{$d}h";
                        };
                    @endphp
                    <div class="trend-summary-row">
                        <div class="tsum-box">
                            <i class='bx bx-rocket' style="color:#16a34a;"></i>
                            <small>Tercepat</small>
                            <strong style="color:#16a34a;">{{ $fmtFn($fastest) }}</strong>
                        </div>
                        <div class="tsum-box">
                            <i class='bx bx-timer' style="color:var(--primary);"></i>
                            <small>Rata-rata</small>
                            <strong style="color:var(--primary);">{{ $fmtFn($avgTime) }}</strong>
                        </div>
                        <div class="tsum-box">
                            <i class='bx bx-hourglass' style="color:#dc2626;"></i>
                            <small>Terlambat</small>
                            <strong style="color:#dc2626;">{{ $fmtFn($slowest) }}</strong>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── WARNING SUMMARY ── --}}
    <div class="panel-card">
        <div class="panel-header">
            <h5>Ringkasan Peringatan Vendor</h5>
        </div>
        <div style="padding:1.375rem;">
            <div class="warn-grid">
                <div class="warn-box">
                    <small>Total Warning</small>
                    <strong>{{ $reportData['warning_summary']['total_warnings'] ?? 0 }}</strong>
                </div>
                <div class="warn-box">
                    <small>Warning Sistem</small>
                    <strong style="color:#d97706;">{{ $reportData['warning_summary']['system_warnings'] ?? 0 }}</strong>
                </div>
                <div class="warn-box">
                    <small>Warning Admin</small>
                    <strong style="color:#dc2626;">{{ $reportData['warning_summary']['admin_warnings'] ?? 0 }}</strong>
                </div>
            </div>
        </div>
    </div>

    @else
    {{-- No data yet --}}
    <div class="panel-card">
        <div style="text-align:center;padding:4rem;">
            <i class='bx bx-bar-chart' style="font-size:3rem;color:var(--text-light);display:block;margin-bottom:.75rem;"></i>
            <h5 style="color:var(--text-muted);">Belum Ada Data</h5>
            <p style="color:var(--text-muted);">Pilih rentang tanggal lalu tekan "Tampilkan Laporan" untuk melihat data.</p>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function filterTrend(btn, filter) {
    document.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    let visibleCount = 0;
    document.querySelectorAll('#trendItems .trend-item').forEach(el => {
        if (filter === 'all' || el.dataset.category === filter) {
            el.style.display = '';
            visibleCount++;
        } else {
            el.style.display = 'none';
        }
    });

    const emptyByFilter = document.getElementById('trendEmptyByFilter');
    if (emptyByFilter) {
        emptyByFilter.style.display = visibleCount === 0 ? '' : 'none';
    }
}

(function () {
    const form = document.getElementById('reportsFilterForm');
    const startInput = document.getElementById('reportsStartDate');
    const endInput = document.getElementById('reportsEndDate');

    if (!form || !startInput || !endInput) return;

    const syncRange = () => {
        if (startInput.value) endInput.min = startInput.value;
        if (endInput.value) startInput.max = endInput.value;
    };

    syncRange();
    startInput.addEventListener('change', syncRange);
    endInput.addEventListener('change', syncRange);

    form.addEventListener('submit', function () {
        if (startInput.value && endInput.value && startInput.value > endInput.value) {
            const tmp = startInput.value;
            startInput.value = endInput.value;
            endInput.value = tmp;
        }
    });
})();
</script>
@endpush
