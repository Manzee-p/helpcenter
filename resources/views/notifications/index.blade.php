@extends('layouts.client')

@section('title', 'Notifikasi')
@section('page_title', 'Notifikasi')
@section('breadcrumb', 'Home / Notifikasi')



@section('content')
<div class="notif-page">

    {{-- â-€â-€ Header â-€â-€ --}}
    <div class="notif-page-header">
        <div>
            <h1 class="notif-page-title">
                <i class='bx bx-bell'></i> Notifikasi
            </h1>
            <p class="notif-page-sub">Pantau pembaruan tiket dan laporan Anda</p>
        </div>
        <div class="notif-header-btns">
            <button class="np-btn np-btn-primary" id="btn-mark-all" onclick="npMarkAllRead()" style="display:none;">
                <i class='bx bx-check-double'></i> Tandai Semua Dibaca
            </button>
            <button class="np-btn np-btn-outline" onclick="npRefresh()" title="Muat ulang">
                <i class='bx bx-refresh' id="btn-refresh-icon"></i>
            </button>
        </div>
    </div>

    {{-- â-€â-€ Stats â-€â-€ --}}
    <div class="notif-stats">
        <div class="notif-stat-card">
            <div class="ns-icon ns-icon-primary"><i class='bx bx-bell'></i></div>
            <div><div class="ns-val" id="stat-total">0</div><div class="ns-lbl">Total Notifikasi</div></div>
        </div>
        <div class="notif-stat-card">
            <div class="ns-icon ns-icon-warning"><i class='bx bx-time-five'></i></div>
            <div><div class="ns-val" id="stat-unread">0</div><div class="ns-lbl">Belum Dibaca</div></div>
        </div>
        <div class="notif-stat-card">
            <div class="ns-icon ns-icon-success"><i class='bx bx-check-circle'></i></div>
            <div><div class="ns-val" id="stat-read">0</div><div class="ns-lbl">Sudah Dibaca</div></div>
        </div>
    </div>

    {{-- â-€â-€ Filter tabs â-€â-€ --}}
    <div class="notif-filter">
        <button class="nf-tab active" data-filter="all"    onclick="npSetFilter(this,'all')">Semua Notifikasi</button>
        <button class="nf-tab"        data-filter="unread" onclick="npSetFilter(this,'unread')">
            Belum Dibaca <span class="nf-badge" id="filter-badge" style="display:none;">0</span>
        </button>
        <button class="nf-tab"        data-filter="read"   onclick="npSetFilter(this,'read')">Sudah Dibaca</button>
    </div>

    {{-- â-€â-€ Container â-€â-€ --}}
    <div class="notif-container">

        {{-- Loading --}}
        <div class="notif-loading-state" id="np-loading">
            <div class="spin"></div>
            <p>Memuat notifikasi...</p>
        </div>

        {{-- Empty --}}
        <div class="notif-empty-state" id="np-empty" style="display:none;">
            <i class='bx bx-bell-off'></i>
            <h3>Tidak ada notifikasi</h3>
            <p>Pembaruan tiket dan laporan akan muncul di sini</p>
        </div>

        {{-- List --}}
        <div class="notif-list-wrapper" id="np-list" style="display:none;"></div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    /* â-€â-€ state â-€â-€ */
    var allNotifs = [];
    var currentFilter = 'all';

    /* â-€â-€ helpers â-€â-€ */
    var $ = function(id) { return document.getElementById(id); };
    var CS = function() { return document.querySelector('meta[name="csrf-token"]')?.content ?? ''; };

    function timeAgo(d) {
        var s = Math.floor((Date.now() - new Date(d)) / 1000);
        if (s < 60)    return 'Baru saja';
        if (s < 3600)  return Math.floor(s/60) + ' menit lalu';
        if (s < 86400) return Math.floor(s/3600) + ' jam lalu';
        return Math.floor(s/86400) + ' hari lalu';
    }

    function formatDate(d) {
        if (!d) return '';
        return new Date(d).toLocaleDateString('id-ID', {
            day:'2-digit', month:'short', year:'numeric',
            hour:'2-digit', minute:'2-digit'
        });
    }

    function iconCls(t) {
        var map = {
            ticket_created:'primary', ticket_assigned:'info',
            ticket_status_changed:'warning', ticket_updated:'warning',
            ticket_resolved:'success', priority_updated:'danger',
            laporan_created:'primary', laporan_status_updated:'info',
            laporan_assigned:'success'
        };
        return 'np-icon-' + (map[t] ?? 'secondary');
    }

    function iconName(t) {
        var map = {
            ticket_created:'bx-file-blank', ticket_assigned:'bx-user-check',
            ticket_status_changed:'bx-refresh', ticket_updated:'bx-edit',
            ticket_resolved:'bx-check-circle', priority_updated:'bx-flag',
            laporan_created:'bx-notepad', laporan_status_updated:'bx-refresh',
            laporan_assigned:'bx-user-plus'
        };
        return map[t] ?? 'bx-bell';
    }

    function ticketRoute(relatedId, relatedType) {
        var role = '{{ Auth::user()->role }}';
        if (relatedType === 'laporan') {
            if (role === 'admin')  return '/admin/laporan/' + relatedId;
            if (role === 'vendor') return '/vendor/laporan/' + relatedId;
            return '/client/reports/' + relatedId;
        }
        if (role === 'admin')  return '/admin/tickets/' + relatedId;
        if (role === 'vendor') return '/vendor/tickets/' + relatedId;
        return '/client/tickets/' + relatedId;
    }

    /* â-€â-€ fetch â-€â-€ */
    function apiFetch(url, opts) {
        opts = opts || {};
        return fetch(url, Object.assign({ credentials: 'same-origin' }, opts, {
            headers: Object.assign({
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CS()
            }, opts.headers || {})
        }));
    }

    /* â-€â-€ render list â-€â-€ */
    function filtered() {
        if (currentFilter === 'unread') return allNotifs.filter(function(n) { return !n.read_at && !n.is_read; });
        if (currentFilter === 'read')   return allNotifs.filter(function(n) { return  n.read_at ||  n.is_read; });
        return allNotifs;
    }

    function renderList() {
        var list = filtered();
        var container = $('np-list');
        container.innerHTML = '';

        if (!list.length) {
            $('np-loading').style.display = 'none';
            $('np-list').style.display    = 'none';
            $('np-empty').style.display   = 'flex';
            return;
        }

        $('np-empty').style.display  = 'none';
        $('np-loading').style.display = 'none';
        container.style.display = 'block';

        list.forEach(function(n) {
            container.appendChild(buildCard(n));
        });
    }

    function buildCard(n) {
        var isUnread   = !n.read_at && !n.is_read;
        var isClickable = !!n.related_id || !!n.ticket_id;
        var div = document.createElement('div');
        div.className = 'np-card' +
            (isUnread   ? ' is-unread'   : '') +
            (isClickable ? ' is-clickable' : '');
        div.dataset.id = n.id;

        div.innerHTML =
            '<div class="np-icon ' + iconCls(n.type) + '">' +
                '<i class="bx ' + iconName(n.type) + '"></i>' +
            '</div>' +
            '<div class="np-body">' +
                '<div class="np-head">' +
                    '<h4 class="np-title">' + (n.title ?? 'Notifikasi') + '</h4>' +
                    '<span class="np-reltime">' + timeAgo(n.created_at) + '</span>' +
                '</div>' +
                '<p class="np-msg">' + (n.message ?? '') + '</p>' +
                '<div class="np-foot">' +
                    '<span class="np-date"><i class="bx bx-time-five"></i>' + formatDate(n.created_at) + '</span>' +
                    (isClickable ? '<span class="np-view"><i class="bx bx-link-external"></i> Lihat Detail</span>' : '') +
                '</div>' +
            '</div>' +
            '<div class="np-actions">' +
                (isUnread
                    ? '<button class="np-act-btn read btn-read" title="Tandai dibaca"><i class="bx bx-check"></i></button>'
                    : '') +
                '<button class="np-act-btn del btn-del" title="Hapus"><i class="bx bx-trash"></i></button>' +
            '</div>';

        /* click card â†’ navigate */
        div.addEventListener('click', async function(e) {
            if (e.target.closest('.np-actions')) return;
            if (isUnread) await doMarkRead(n, div);
            if (isClickable) {
                var rid = n.related_id || n.ticket_id;
                window.location.href = ticketRoute(rid, n.related_type);
            }
        });

        var btnRead = div.querySelector('.btn-read');
        if (btnRead) {
            btnRead.addEventListener('click', async function(e) {
                e.stopPropagation();
                await doMarkRead(n, div);
            });
        }

        div.querySelector('.btn-del').addEventListener('click', async function(e) {
            e.stopPropagation();
            var res = await Swal.fire({
                title: 'Hapus notifikasi?',
                text: 'Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            });
            if (res.isConfirmed) await doDelete(n, div);
        });

        return div;
    }

    /* â-€â-€ API actions â-€â-€ */
    async function doMarkRead(n, div) {
        try {
            var res = await apiFetch('/notifications/' + n.id + '/read', { method: 'POST' });
            if (!res.ok) return;
            n.read_at = new Date().toISOString();
            n.is_read = true;
            updateStats();
            renderList();
        } catch(e) { console.error(e); }
    }

    async function doDelete(n, div) {
        try {
            var res = await apiFetch('/notifications/' + n.id, { method: 'DELETE' });
            if (!res.ok) return;
            allNotifs = allNotifs.filter(function(x) { return x.id !== n.id; });
            updateStats();
            renderList();
            Swal.fire({ icon:'success', title:'Dihapus!', text:'Notifikasi berhasil dihapus.', timer:2000, showConfirmButton:false });
        } catch(e) { console.error(e); }
    }

    /* â-€â-€ mark all â-€â-€ */
    window.npMarkAllRead = async function() {
        try {
            var res = await apiFetch('/notifications/mark-all-read', { method: 'POST' });
            if (!res.ok) return;
            allNotifs.forEach(function(n) { n.read_at = new Date().toISOString(); n.is_read = true; });
            updateStats();
            renderList();
            Swal.fire({ icon:'success', title:'Berhasil!', text:'Semua notifikasi ditandai dibaca.', timer:2000, showConfirmButton:false });
        } catch(e) { console.error(e); }
    };

    /* â-€â-€ stats â-€â-€ */
    function updateStats() {
        var total   = allNotifs.length;
        var unread  = allNotifs.filter(function(n) { return !n.read_at && !n.is_read; }).length;
        var read    = total - unread;
        $('stat-total').textContent  = total;
        $('stat-unread').textContent = unread;
        $('stat-read').textContent   = read;
        /* mark-all button */
        $('btn-mark-all').style.display = unread > 0 ? 'inline-flex' : 'none';
        /* filter badge */
        var badge = $('filter-badge');
        if (unread > 0) { badge.textContent = unread; badge.style.display = 'inline'; }
        else { badge.style.display = 'none'; }
    }

    /* â-€â-€ filter â-€â-€ */
    window.npSetFilter = function(btn, filter) {
        document.querySelectorAll('.nf-tab').forEach(function(t) { t.classList.remove('active'); });
        btn.classList.add('active');
        currentFilter = filter;
        renderList();
    };

    /* â-€â-€ refresh â-€â-€ */
    window.npRefresh = async function() {
        var icon = $('btn-refresh-icon');
        icon.style.animation = 'np-spin 1s linear infinite';
        await loadNotifications();
        icon.style.animation = '';
    };

    /* â-€â-€ load â-€â-€ */
    async function loadNotifications() {
        $('np-loading').style.display = 'flex';
        $('np-empty').style.display   = 'none';
        $('np-list').style.display    = 'none';
        try {
            var res = await apiFetch('/notifications?per_page=100');
            if (!res.ok) { throw new Error('HTTP ' + res.status); }
            var body = await res.json();
            allNotifs = body.data ?? body;
            if (!Array.isArray(allNotifs)) allNotifs = [];
            updateStats();
            renderList();
        } catch(e) {
            console.error('loadNotifications:', e);
            $('np-loading').style.display = 'none';
            $('np-empty').style.display   = 'flex';
        }
    }

    document.addEventListener('DOMContentLoaded', loadNotifications);
})();
</script>
@endpush

@push('styles')
<style>
/* â-€â-€ PAGE â-€â-€ */
.notif-page { max-width: 900px; margin: 0 auto; }

/* â-€â-€ PAGE HEADER â-€â-€ */
.notif-page-header {
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;
}
.notif-page-title {
    font-size: 1.5rem; font-weight: 800; color: var(--text);
    display: flex; align-items: center; gap: .625rem; margin: 0;
}
.notif-page-title i { color: var(--primary); font-size: 1.625rem; }
.notif-page-sub { font-size: .875rem; color: var(--text-muted); margin-top: .25rem; }
.notif-header-btns { display: flex; gap: .625rem; }

.np-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5625rem 1.125rem; border-radius: 9px;
    font-size: .875rem; font-weight: 700; cursor: pointer;
    border: none; transition: all .2s;
}
.np-btn-primary {
    background: var(--gradient); color: white;
    box-shadow: 0 4px 12px rgba(79,70,229,.25);
}
.np-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(79,70,229,.35); }
.np-btn-primary:disabled { opacity:.5; cursor:not-allowed; transform:none; }
.np-btn-outline {
    background: white; color: var(--text-muted);
    border: 1.5px solid var(--border);
}
.np-btn-outline:hover { border-color: var(--primary); color: var(--primary); }

/* â-€â-€ STATS â-€â-€ */
.notif-stats {
    display: grid; grid-template-columns: repeat(3,1fr);
    gap: 1.25rem; margin-bottom: 1.75rem;
}
.notif-stat-card {
    background: white; border-radius: 14px; padding: 1.25rem 1.5rem;
    display: flex; align-items: center; gap: 1rem;
    border: 1.5px solid var(--border); transition: all .2s;
}
.notif-stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
.ns-icon {
    width: 52px; height: 52px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
    flex-shrink: 0;
}
.ns-icon-primary { background: rgba(79,70,229,.1); color: var(--primary); }
.ns-icon-warning { background: rgba(245,158,11,.1); color: var(--warning); }
.ns-icon-success { background: rgba(16,185,129,.1); color: var(--success); }
.ns-val { font-size: 1.875rem; font-weight: 800; color: var(--text); line-height: 1; }
.ns-lbl { font-size: .8125rem; color: var(--text-muted); margin-top: .25rem; }

/* â-€â-€ FILTER TABS â-€â-€ */
.notif-filter {
    display: flex; gap: .5rem; background: white;
    padding: .375rem; border-radius: 12px;
    border: 1.5px solid var(--border); margin-bottom: 1.5rem;
}
.nf-tab {
    flex: 1; padding: .625rem 1rem; border: none; background: transparent;
    border-radius: 9px; font-size: .875rem; font-weight: 600;
    color: var(--text-muted); cursor: pointer; transition: all .2s;
    display: flex; align-items: center; justify-content: center; gap: .4rem;
}
.nf-tab:hover { background: var(--bg); color: var(--primary); }
.nf-tab.active { background: var(--gradient); color: white; box-shadow: 0 4px 12px rgba(79,70,229,.2); }
.nf-badge {
    background: rgba(255,255,255,.3); color: inherit;
    font-size: .6875rem; padding: .15rem .45rem; border-radius: 20px; font-weight: 700;
}
.nf-tab:not(.active) .nf-badge { background: rgba(79,70,229,.12); color: var(--primary); }

/* â-€â-€ CONTAINER â-€â-€ */
.notif-container {
    background: white; border-radius: 16px;
    border: 1.5px solid var(--border); overflow: hidden;
}

/* â-€â-€ STATES â-€â-€ */
.notif-loading-state, .notif-empty-state {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 4rem 2rem; gap: 1rem;
    color: var(--text-muted);
}
.notif-loading-state .spin {
    width: 44px; height: 44px; border: 4px solid var(--border);
    border-top-color: var(--primary); border-radius: 50%;
    animation: np-spin 1s linear infinite;
}
@keyframes np-spin { to { transform: rotate(360deg); } }
.notif-empty-state i { font-size: 3rem; color: var(--text-light); }
.notif-empty-state h3 { font-size: 1.25rem; font-weight: 700; color: var(--text); margin: 0; }
.notif-empty-state p  { margin: 0; font-size: .875rem; }

/* â-€â-€ NOTIFICATION CARDS â-€â-€ */
.notif-list-wrapper { padding: .5rem; }

.np-card {
    display: flex; gap: 1rem; padding: 1.125rem;
    border-radius: 12px; margin-bottom: .375rem;
    transition: all .2s; position: relative;
    border: 2px solid transparent; cursor: default;
}
.np-card:last-child { margin-bottom: 0; }
.np-card:hover { background: var(--bg); }
.np-card.is-clickable { cursor: pointer; }
.np-card.is-clickable:hover { border-color: var(--primary); transform: translateX(3px); }
.np-card.is-unread { background: rgba(79,70,229,.03); }
.np-card.is-unread::before {
    content: ''; position: absolute; left: 0; top: 10%; bottom: 10%;
    width: 3px; background: var(--gradient); border-radius: 0 3px 3px 0;
}

/* icon */
.np-icon {
    width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 1.375rem;
}
.np-icon-primary   { background: rgba(79,70,229,.1);  color: var(--primary); }
.np-icon-info      { background: rgba(6,182,212,.1);   color: var(--accent); }
.np-icon-warning   { background: rgba(245,158,11,.1);  color: var(--warning); }
.np-icon-success   { background: rgba(16,185,129,.1);  color: var(--success); }
.np-icon-danger    { background: rgba(239,68,68,.1);   color: var(--danger); }
.np-icon-secondary { background: rgba(100,116,139,.1); color: var(--text-muted); }

/* body */
.np-body { flex: 1; min-width: 0; }
.np-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: .375rem; }
.np-title { font-size: .9375rem; font-weight: 700; color: var(--text); margin: 0; }
.np-reltime { font-size: .8rem; color: var(--text-light); white-space: nowrap; }
.np-msg { font-size: .875rem; color: var(--text-muted); line-height: 1.5; margin: 0 0 .5rem; }
.np-foot { display: flex; justify-content: space-between; align-items: center; }
.np-date { font-size: .78rem; color: var(--text-light); display: flex; align-items: center; gap: .25rem; }
.np-view { font-size: .78rem; color: var(--primary); font-weight: 700; display: flex; align-items: center; gap: .25rem; }

/* actions */
.np-actions { display: flex; flex-direction: column; gap: .375rem; flex-shrink: 0; }
.np-act-btn {
    width: 32px; height: 32px; border-radius: 8px; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; transition: all .15s;
}
.np-act-btn.read  { background: rgba(16,185,129,.1);  color: var(--success); }
.np-act-btn.read:hover  { background: var(--success);  color: white; }
.np-act-btn.del   { background: rgba(239,68,68,.1);    color: var(--danger); }
.np-act-btn.del:hover   { background: var(--danger);   color: white; }

/* â-€â-€ RESPONSIVE â-€â-€ */
@media (max-width: 640px) {
    .notif-stats { grid-template-columns: 1fr; }
    .notif-filter { flex-direction: column; }
    .np-card { flex-direction: column; gap: .75rem; }
    .np-actions { flex-direction: row; justify-content: flex-end; }
    .notif-page-header { flex-direction: column; align-items: flex-start; }
}
</style>
@endpush

