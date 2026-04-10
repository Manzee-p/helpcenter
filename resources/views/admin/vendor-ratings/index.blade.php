@extends('layouts.app')

@section('title', 'Rating Vendor')
@section('page_title', 'Rating Vendor')
@section('breadcrumb', 'Home / Rating Vendor')

@push('styles')
<style>
.ratings-wrap { display: flex; flex-direction: column; gap: 1.5rem; }

/* ── HERO ── */
.ratings-hero {
    display: flex; justify-content: space-between; align-items: flex-start;
    gap: 1.25rem; padding: 1.875rem;
    background: radial-gradient(circle at top right, rgba(251,191,36,.18), transparent 28%),
                linear-gradient(135deg, #fff8ef 0%, #ffffff 55%, #fff3e0 100%);
    border: 1px solid rgba(217,119,6,.14); border-radius: 28px;
    box-shadow: 0 18px 40px rgba(148,64,0,.08);
}
.hero-kicker {
    display: inline-flex; padding: .35rem .7rem; border-radius: 999px;
    background: rgba(194,65,12,.08); color: #c2410c;
    font-size: .74rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
}
.ratings-hero h4 { margin: .9rem 0 .4rem; font-size: 1.75rem; font-weight: 800; color: #1f2937; }
.ratings-hero > div > p { margin: 0; color: #6b7280; max-width: 680px; font-size: .9375rem; }
.hero-badges { display: grid; grid-template-columns: repeat(2, minmax(180px,1fr)); gap: .85rem; }
.hero-badge {
    border-radius: 18px; padding: 1rem 1.1rem;
    background: rgba(255,255,255,.88); border: 1px solid rgba(217,119,6,.12);
}
.hero-badge--alert { background: linear-gradient(135deg,#fff1f2 0%,#fff7ed 100%); border-color: rgba(225,29,72,.16); }
.hero-badge span  { display: block; font-size: .8rem; color: #6b7280; }
.hero-badge strong{ display: block; margin-top: .4rem; font-size: 1.8rem; color: #1f2937; line-height: 1; }

/* ── STATS GRID ── */
.stats-grid-6 {
    display: grid; grid-template-columns: repeat(6, minmax(0,1fr)); gap: 1rem;
}
.stat-orange {
    padding: 1.15rem 1.2rem; border-radius: 22px;
    background: linear-gradient(135deg,#fff7ed 0%,#fff 100%);
    border: 1px solid rgba(217,119,6,.14);
    box-shadow: 0 8px 20px rgba(148,64,0,.06);
}
.stat-orange--danger { background: linear-gradient(135deg,#fff1f2 0%,#fff 100%); }
.stat-orange span   { display: block; font-size: .8rem; color: #6b7280; font-weight: 700; }
.stat-orange strong { display: block; font-size: 1.625rem; font-weight: 800; color: #1f2937; margin-top: .3rem; }

/* ── CONTENT GRID ── */
.content-grid-2 { display: grid; grid-template-columns: 1.3fr 1fr; gap: 1rem; align-items: start; }

/* ── PANEL CARD ── */
.panel-orange {
    background: linear-gradient(180deg,#fffdf7 0%,#fff 100%);
    border: 1px solid rgba(217,119,6,.14); border-radius: 24px;
    padding: 1.375rem; box-shadow: 0 18px 40px rgba(148,64,0,.06);
}
.panel-orange--filter {
    background: radial-gradient(circle at bottom left,rgba(251,191,36,.14),transparent 30%), #fff;
}
.panel-head-o { margin-bottom: 1rem; }
.panel-head-o h5 { margin: 0 0 .35rem; font-size: 1.0625rem; font-weight: 800; color: #1f2937; }
.panel-head-o p  { margin: 0; color: #6b7280; font-size: .875rem; line-height: 1.6; }

/* ── FILTER FORM ── */
.filter-grid-o { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group-o   { display: flex; flex-direction: column; gap: .45rem; }
.form-group-o--wide { grid-column: span 2; }
.form-group-o label { font-size: .82rem; font-weight: 700; color: #374151; }
.form-control-o, .form-select-o {
    width: 100%; min-height: 52px;
    border: 1px solid #fed7aa; border-radius: 16px;
    padding: .85rem 1rem; color: #1f2937;
    background: rgba(255,255,255,.92); font-size: .9rem;
}
.form-control-o:focus, .form-select-o:focus {
    outline: none; border-color: #fb923c;
    box-shadow: 0 0 0 4px rgba(251,146,60,.12);
}
.btn-ghost-o {
    padding: .8rem 1.2rem; background: #fff7ed; color: #9a3412;
    border: none; border-radius: 14px; font-weight: 700;
    cursor: pointer; text-decoration: none; display: inline-flex;
    align-items: center; gap: .4rem; font-size: .875rem;
    transition: all .2s;
}
.btn-ghost-o:hover { transform: translateY(-1px); background: #ffedd5; }
.filter-action-row { grid-column: span 2; display: flex; justify-content: flex-end; gap: .75rem; flex-wrap: wrap; }

/* ── VENDOR SUMMARY LIST ── */
.vendor-sum-list { display: flex; flex-direction: column; gap: .875rem; max-height: 380px; overflow-y: auto; padding-right: .25rem; }
.vendor-sum-list::-webkit-scrollbar { width: 6px; }
.vendor-sum-list::-webkit-scrollbar-thumb { background: rgba(217,119,6,.28); border-radius: 999px; }

.vendor-sum-item {
    display: flex; justify-content: space-between; gap: 1rem;
    border: 1px solid #f3e8d8; border-radius: 20px; padding: 1rem;
    background: #fffdfa;
}
.vendor-sum-item--alert { border-color: rgba(225,29,72,.2); background: linear-gradient(135deg,#fff8f8 0%,#fffdf8 100%); }
.vendor-sum-main { flex: 1; min-width: 0; }
.vendor-sum-main h6 { margin: 0; font-weight: 800; color: #1f2937; font-size: .9375rem; }
.vendor-sum-main p  { margin: .3rem 0 0; color: #6b7280; font-size: .85rem; }
.vendor-sum-metrics { display: flex; flex-wrap: wrap; gap: .65rem; margin-top: .75rem; color: #4b5563; font-size: .83rem; }
.vendor-sum-score   { min-width: 160px; text-align: right; }
.vendor-sum-score strong { display: block; font-size: 1.1rem; color: #c2410c; font-weight: 800; }
.vendor-sum-score small  { color: #6b7280; font-size: .82rem; }

.attention-badge {
    display: inline-flex; align-items: center; border-radius: 999px;
    padding: .4rem .85rem; font-size: .74rem; font-weight: 800;
}
.attention-badge--alert { background: #fff1f2; color: #be123c; }
.attention-badge--good  { background: #ecfdf3; color: #15803d; }

.btn-warning-o {
    padding: .55rem .875rem; background: #ffe4e6; color: #be123c;
    border: none; border-radius: 12px; font-weight: 700; font-size: .8rem;
    cursor: pointer; margin-top: .5rem; transition: all .2s;
}
.btn-warning-o:hover { background: #fecdd3; transform: translateY(-1px); }

/* ── RATINGS LIST ── */
.ratings-list { display: flex; flex-direction: column; gap: 1rem; }
.rating-item {
    border: 1px solid #f3e8d8; border-radius: 20px; padding: 1.125rem;
    background: #fffdfa;
}
.rating-item--alert { border-color: rgba(225,29,72,.2); background: linear-gradient(135deg,#fff8f8 0%,#fffdf8 100%); }

.rating-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
.ticket-line { font-weight: 800; color: #1f2937; font-size: .9375rem; }
.meta-line   { display: flex; flex-wrap: wrap; gap: .65rem; margin-top: .4rem; color: #6b7280; font-size: .83rem; }
.rating-actions { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; justify-content: flex-end; flex-shrink: 0; }
.stars-o { display: inline-flex; gap: .15rem; color: #d1d5db; font-size: 1rem; }
.stars-o .star-active { color: #f59e0b; }
.comment-box-o {
    margin: .875rem 0 0; padding: 1rem;
    background: rgba(255,255,255,.94); border: 1px solid #f3e8d8;
    border-radius: 16px; color: #374151; line-height: 1.7; font-size: .9rem;
}
.btn-del-rating {
    padding: .4rem .875rem; background: #ffe4e6; color: #be123c;
    border: none; border-radius: 12px; font-weight: 700; font-size: .8rem;
    cursor: pointer; display: inline-flex; align-items: center; gap: .35rem;
    transition: all .2s;
}
.btn-del-rating:hover { background: #fecdd3; }

/* ── PAGINATION ── */
.pagination-o { display: flex; align-items: center; justify-content: center; gap: .75rem; margin-top: 1rem; flex-wrap: wrap; }
.btn-page-o {
    padding: .65rem 1.125rem; background: #fff1e6; color: #9a3412;
    border: none; border-radius: 14px; font-weight: 700; font-size: .875rem;
    cursor: pointer; transition: all .2s;
}
.btn-page-o:hover:not(:disabled) { transform: translateY(-1px); background: #ffedd5; }
.btn-page-o:disabled { opacity: .5; cursor: not-allowed; }

/* ── EMPTY ── */
.empty-o { text-align: center; padding: 3rem; color: #6b7280; }
.empty-o i { font-size: 2.5rem; color: #d1d5db; display: block; margin-bottom: .75rem; }

/* ── RESPONSIVE ── */
@media (max-width: 1200px) {
    .stats-grid-6 { grid-template-columns: repeat(3, minmax(0,1fr)); }
    .content-grid-2 { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .ratings-hero, .vendor-sum-item, .rating-top { flex-direction: column; }
    .hero-badges, .stats-grid-6, .filter-grid-o { grid-template-columns: 1fr; }
    .form-group-o--wide, .filter-action-row { grid-column: span 1; }
    .vendor-sum-score { text-align: left; }
    .rating-actions { justify-content: flex-start; }
}
</style>
@endpush

@section('content')
<div class="ratings-wrap">

    {{-- ── HERO ── --}}
    <section class="ratings-hero">
        <div>
            <span class="hero-kicker">Monitoring kualitas vendor</span>
            <h4>Kelola Rating Vendor</h4>
            <p>Pantau vendor dengan rating rendah, cari feedback bermasalah lebih cepat, dan tindak lanjuti tiket yang butuh perhatian.</p>
        </div>
        <div class="hero-badges">
            <div class="hero-badge hero-badge--alert">
                <span>Vendor perlu perhatian</span>
                <strong>{{ $summary['low_rating_vendors'] ?? 0 }}</strong>
            </div>
            <div class="hero-badge">
                <span>Rata-rata platform</span>
                <strong>{{ number_format($summary['average_rating'] ?? 0, 2) }} / 5</strong>
            </div>
        </div>
    </section>

    {{-- ── STATS ── --}}
    <div class="stats-grid-6">
        <div class="stat-orange">
            <span>Total Rating</span><strong>{{ $summary['total_feedbacks'] ?? 0 }}</strong>
        </div>
        <div class="stat-orange">
            <span>Tiket Selesai</span><strong>{{ $summary['completed_tickets'] ?? 0 }}</strong>
        </div>
        <div class="stat-orange">
            <span>Belum Dinilai</span><strong>{{ $summary['pending_ratings'] ?? 0 }}</strong>
        </div>
        <div class="stat-orange stat-orange--danger">
            <span>Vendor Rating Rendah</span><strong>{{ $summary['low_rating_vendors'] ?? 0 }}</strong>
        </div>
        <div class="stat-orange stat-orange--danger">
            <span>Warning Sistem</span><strong>{{ $summary['system_warning_count'] ?? 0 }}</strong>
        </div>
        <div class="stat-orange">
            <span>Warning Admin</span><strong>{{ $summary['admin_warning_count'] ?? 0 }}</strong>
        </div>
    </div>

    {{-- ── CONTENT GRID ── --}}
    <div class="content-grid-2">

        {{-- Filter Panel --}}
        <div class="panel-orange panel-orange--filter">
            <div class="panel-head-o">
                <h5>Kontrol Rating</h5>
                <p>Filter vendor, level perhatian, dan urutan tampilan untuk menemukan rating penting lebih cepat.</p>
            </div>
            <form method="GET" action="{{ route('admin.vendor-ratings.index') }}" class="filter-grid-o">
                <div class="form-group-o">
                    <label>Vendor</label>
                    <select name="vendor_id" class="form-select-o">
                        <option value="">Semua Vendor</option>
                        @foreach($vendorOptions as $vo)
                            <option value="{{ $vo['id'] }}" {{ request('vendor_id') == $vo['id'] ? 'selected' : '' }}>
                                {{ $vo['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-o">
                    <label>Status Perhatian</label>
                    <select name="attention" class="form-select-o">
                        <option value="">Semua Kondisi</option>
                        <option value="needs_attention" {{ request('attention') === 'needs_attention' ? 'selected' : '' }}>Perlu Perhatian</option>
                        <option value="system_warning"  {{ request('attention') === 'system_warning'  ? 'selected' : '' }}>Warning Sistem</option>
                        <option value="admin_warning"   {{ request('attention') === 'admin_warning'   ? 'selected' : '' }}>Perlu Teguran Admin</option>
                        <option value="stable"          {{ request('attention') === 'stable'          ? 'selected' : '' }}>Stabil</option>
                        <option value="excellent"       {{ request('attention') === 'excellent'       ? 'selected' : '' }}>Sangat Baik</option>
                    </select>
                </div>
                <div class="form-group-o">
                    <label>Urutkan</label>
                    <select name="sort" class="form-select-o">
                        <option value="latest"         {{ request('sort','latest') === 'latest'         ? 'selected' : '' }}>Rating Terbaru</option>
                        <option value="lowest_rating"  {{ request('sort') === 'lowest_rating'  ? 'selected' : '' }}>Rating Terendah</option>
                        <option value="highest_rating" {{ request('sort') === 'highest_rating' ? 'selected' : '' }}>Rating Tertinggi</option>
                        <option value="oldest"         {{ request('sort') === 'oldest'         ? 'selected' : '' }}>Rating Terlama</option>
                    </select>
                </div>
                <div class="form-group-o">
                    <label>Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="form-control-o" placeholder="Cari tiket, vendor, client…">
                </div>
                <div class="filter-action-row">
                    @if(request()->hasAny(['vendor_id','attention','sort','search']))
                        <a href="{{ route('admin.vendor-ratings.index') }}" class="btn-ghost-o">
                            <i class='bx bx-reset'></i> Reset
                        </a>
                    @endif
                    <button type="submit" class="btn-ghost-o" style="background:var(--primary);color:white;">
                        <i class='bx bx-filter-alt'></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>

        {{-- Vendor Summary Panel --}}
        <div class="panel-orange">
            <div class="panel-head-o">
                <h5>Peringatan Vendor</h5>
                <p>Vendor dengan rating rata-rata rendah atau feedback 1–2 bintang akan ditandai otomatis.</p>
            </div>
            @if(empty($vendorStats))
                <div class="empty-o"><i class='bx bx-group'></i><p>Belum ada data vendor untuk rating.</p></div>
            @else
                <div class="vendor-sum-list">
                    @foreach($vendorStats as $vs)
                    <div class="vendor-sum-item {{ $vs['needs_attention'] ? 'vendor-sum-item--alert' : '' }}">
                        <div class="vendor-sum-main">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;">
                                <div>
                                    <h6>{{ $vs['name'] }}</h6>
                                    <p>{{ $vs['company_name'] ?: $vs['email'] }}</p>
                                </div>
                                <span class="attention-badge {{ $vs['needs_attention'] ? 'attention-badge--alert' : 'attention-badge--good' }}">
                                    @if($vs['warning_level'] === 'admin') Warning admin
                                    @elseif($vs['warning_level'] === 'system') Warning sistem
                                    @elseif($vs['needs_attention']) Perlu perhatian
                                    @else Sehat
                                    @endif
                                </span>
                            </div>
                            <div class="vendor-sum-metrics">
                                <span>{{ $vs['completed_tickets'] }} selesai</span>
                                <span>{{ $vs['rated_tickets'] }} dinilai</span>
                                <span>{{ $vs['pending_ratings'] }} pending</span>
                                <span>{{ $vs['low_rating_count'] ?? 0 }} rating rendah</span>
                            </div>
                            @if(!empty($vs['warning_message']))
                                <p style="margin:.75rem 0 0;padding:.75rem;background:rgba(255,255,255,.94);border:1px solid #f3e8d8;border-radius:12px;color:#374151;font-size:.85rem;line-height:1.6;">
                                    {{ $vs['warning_message'] }}
                                </p>
                            @endif
                        </div>
                        <div class="vendor-sum-score">
                            <strong>{{ number_format($vs['average_rating'], 2) }} / 5</strong>
                            <small>{{ $vs['needs_attention'] ? 'Butuh tindak lanjut admin' : 'Performa masih aman' }}</small>
                            @if($vs['should_receive_admin_warning'])
                                <form method="POST" action="{{ route('admin.vendor-ratings.warning', $vs['id']) }}" style="text-align:right;">
                                    @csrf
                                    <button type="submit" class="btn-warning-o"
                                        onclick="return confirm('Kirim warning admin ke {{ addslashes($vs['name']) }}?')">
                                        <i class='bx bx-error'></i> Kirim Warning
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ── RATINGS LIST ── --}}
    <div class="panel-orange">
        <div class="panel-head-o" style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;">
            <div>
                <h5>Daftar Rating Masuk</h5>
                <p>Feedback dengan rating rendah diberi penanda khusus agar admin bisa melakukan follow up lebih cepat.</p>
            </div>
            <span style="color:var(--text-muted);font-size:.875rem;">{{ $ratings->total() }} rating</span>
        </div>

        @if($ratings->isEmpty())
            <div class="empty-o">
                <i class='bx bx-star'></i>
                <p>Belum ada rating yang cocok dengan filter saat ini.</p>
            </div>
        @else
            <div class="ratings-list">
                @foreach($ratings as $item)
                @php
                    $isLow   = ($item->rating ?? 5) <= 2;
                    $ticket  = $item->ticket;
                    $vendor  = $ticket?->assignedTo;
                    $client  = $ticket?->user;
                @endphp
                <div class="rating-item {{ $isLow ? 'rating-item--alert' : '' }}">
                    <div class="rating-top">
                        <div>
                            <div class="ticket-line">{{ $ticket?->ticket_number ?? '-' }} | {{ $ticket?->title ?? '-' }}</div>
                            <div class="meta-line">
                                <span>Vendor: {{ $vendor?->name ?? '-' }}</span>
                                <span>Client: {{ $client?->name ?? '-' }}</span>
                                <span>{{ \Carbon\Carbon::parse($item->created_at)->locale('id')->isoFormat('D MMM YYYY, HH:mm') }}</span>
                            </div>
                        </div>
                        <div class="rating-actions">
                            @if($isLow)
                                <span class="attention-badge attention-badge--alert">Rating rendah</span>
                            @endif
                            <div class="stars-o">
                                @for($s = 1; $s <= 5; $s++)
                                    <i class="bx bxs-star {{ $s <= ($item->rating ?? 0) ? 'star-active' : '' }}"></i>
                                @endfor
                            </div>
                            <form method="POST" action="{{ route('admin.vendor-ratings.destroy', $item->id) }}" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-del-rating"
                                    onclick="return confirm('Hapus rating dari {{ addslashes($client?->name ?? 'client') }}?')">
                                    <i class='bx bx-trash'></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                    <p class="comment-box-o">{{ $item->comment ?: 'Client tidak menambahkan komentar.' }}</p>
                </div>
                @endforeach
            </div>

            {{-- PAGINATION --}}
            @if($ratings->hasPages())
            <div class="pagination-o" style="margin-top:1.25rem;">
                @if($ratings->onFirstPage())
                    <button class="btn-page-o" disabled>Sebelumnya</button>
                @else
                    <a class="btn-page-o" href="{{ $ratings->appends(request()->query())->previousPageUrl() }}">Sebelumnya</a>
                @endif
                <span style="color:var(--text-muted);font-size:.875rem;">
                    Halaman {{ $ratings->currentPage() }} dari {{ $ratings->lastPage() }}
                </span>
                @if($ratings->hasMorePages())
                    <a class="btn-page-o" href="{{ $ratings->appends(request()->query())->nextPageUrl() }}">Berikutnya</a>
                @else
                    <button class="btn-page-o" disabled>Berikutnya</button>
                @endif
            </div>
            @endif
        @endif
    </div>

</div>
@endsection