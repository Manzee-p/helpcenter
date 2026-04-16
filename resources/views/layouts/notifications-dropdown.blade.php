{{-- Tombol notifikasi --}}
<button class="action-btn" onclick="toggleDrop('notif-drop')" title="Notifikasi" style="position:relative;">
    <i class='bx bx-bell' style="font-size:1.2rem;"></i>
    <span class="badge-dot" id="notif-badge" style="display:none;">0</span>
</button>

{{-- Dropdown panel --}}
<div class="nav-drop notif-drop" id="notif-drop" style="display:none;">

    {{-- Header --}}
    <div class="nd-header">
        <div class="nd-header-left">
            <span class="nd-title">Notifikasi</span>
            <span class="nd-badge" id="notif-count" style="display:none;">0</span>
        </div>
        <div class="nd-header-right">
            <button
                id="notif-mark-all-btn"
                onclick="markAllNotifRead()"
                title="Tandai semua sudah dibaca"
                class="nd-icon-btn"
                style="display:none;"
            >
                <i class='bx bx-check-double'></i>
            </button>
        </div>
    </div>

    <div class="nd-divider"></div>

    {{-- Filter tabs --}}
    <div class="nd-tabs">
        <button class="nd-tab active" onclick="filterNotif('all', this)">Semua</button>
        <button class="nd-tab" onclick="filterNotif('unread', this)">Belum Dibaca</button>
    </div>

    {{-- List area --}}
    <div id="notif-list" style="max-height:340px;overflow-y:auto;">

        {{-- Skeleton loader --}}
        <div id="notif-loading" style="display:none;padding:1rem 1.125rem;">
            @for ($i = 0; $i < 4; $i++)
            <div style="display:flex;gap:.75rem;align-items:flex-start;margin-bottom:1.1rem;">
                <div class="n-skeleton" style="width:40px;height:40px;border-radius:10px;flex-shrink:0;"></div>
                <div style="flex:1;">
                    <div class="n-skeleton" style="height:12px;width:65%;margin-bottom:7px;border-radius:4px;"></div>
                    <div class="n-skeleton" style="height:10px;width:90%;border-radius:4px;margin-bottom:5px;"></div>
                    <div class="n-skeleton" style="height:9px;width:35%;border-radius:4px;"></div>
                </div>
            </div>
            @endfor
        </div>

        {{-- Empty state --}}
        <div id="notif-empty" class="nd-empty" style="display:none;">
            <div class="nd-empty-icon"><i class='bx bx-bell-off'></i></div>
            <span class="nd-empty-title">Tidak ada notifikasi</span>
            <span class="nd-empty-sub">Semua notifikasi sudah dibaca</span>
        </div>

        {{-- Notifications rendered by JS --}}
        <ul id="notif-items" style="list-style:none;margin:0;padding:0.375rem 0;display:none;"></ul>
    </div>

    <div class="nd-divider"></div>

    {{-- Footer --}}
    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'vendor')
        <button onclick="openAllNotifModal()" class="nd-view-all-btn">
            <i class='bx bx-list-ul'></i>
            Lihat Semua Notifikasi
        </button>
    @else
        <a href="{{ route('notifications.index') }}" class="nd-view-all-btn">
            <i class='bx bx-list-ul'></i>
            Lihat Semua Notifikasi
        </a>
    @endif
</div>

{{-- ============================================================
     MODAL: Semua Notifikasi (Admin & Vendor)
     ============================================================ --}}
@if(Auth::user()->role === 'admin' || Auth::user()->role === 'vendor')
<div id="all-notif-modal" class="anm-backdrop" style="display:none;" onclick="closeAllNotifModal(event)">
    <div class="anm-dialog" onclick="event.stopPropagation()">

        {{-- Modal Header --}}
        <div class="anm-header">
            <div class="anm-header-left">
                <div class="anm-header-icon"><i class='bx bx-bell'></i></div>
                <div>
                    <h5 class="anm-title">Semua Notifikasi</h5>
                    <p class="anm-sub" id="anm-sub-text">Memuat...</p>
                </div>
            </div>
            <div class="anm-header-actions">
                <button class="anm-action-btn" onclick="markAllNotifRead(); loadAllNotifModal();" title="Tandai semua dibaca">
                    <i class='bx bx-check-double'></i>
                    <span>Tandai Semua Dibaca</span>
                </button>
                <button class="anm-close-btn" onclick="closeAllNotifModal()">
                    <i class='bx bx-x'></i>
                </button>
            </div>
        </div>

        {{-- Modal Filter --}}
        <div class="anm-filter-bar">
            <div class="anm-tabs">
                <button class="anm-tab active" onclick="filterModal('all', this)">Semua</button>
                <button class="anm-tab" onclick="filterModal('unread', this)">Belum Dibaca</button>
                <button class="anm-tab" onclick="filterModal('read', this)">Sudah Dibaca</button>
            </div>
            <div class="anm-search-wrap">
                <i class='bx bx-search'></i>
                <input type="text" id="anm-search" class="anm-search" placeholder="Cari notifikasi..." oninput="searchModal(this.value)">
            </div>
        </div>

        {{-- Modal List --}}
        <div class="anm-list-wrap" id="anm-list-wrap">

            {{-- Skeleton --}}
            <div id="anm-loading" style="padding:1.5rem;">
                @for($i = 0; $i < 6; $i++)
                <div style="display:flex;gap:.875rem;align-items:flex-start;margin-bottom:1.25rem;">
                    <div class="n-skeleton" style="width:44px;height:44px;border-radius:11px;flex-shrink:0;"></div>
                    <div style="flex:1;">
                        <div class="n-skeleton" style="height:13px;width:55%;margin-bottom:8px;border-radius:4px;"></div>
                        <div class="n-skeleton" style="height:11px;width:85%;margin-bottom:5px;border-radius:4px;"></div>
                        <div class="n-skeleton" style="height:10px;width:28%;border-radius:4px;"></div>
                    </div>
                    <div class="n-skeleton" style="width:60px;height:26px;border-radius:6px;flex-shrink:0;"></div>
                </div>
                @endfor
            </div>

            {{-- Empty --}}
            <div id="anm-empty" class="anm-empty" style="display:none;">
                <div class="anm-empty-icon"><i class='bx bx-bell-off'></i></div>
                <p class="anm-empty-title">Tidak ada notifikasi</p>
                <p class="anm-empty-sub">Belum ada notifikasi yang masuk</p>
            </div>

            {{-- Items --}}
            <ul id="anm-items" style="list-style:none;margin:0;padding:0.5rem 0;display:none;"></ul>
        </div>

        {{-- Modal Footer --}}
        <div class="anm-footer" id="anm-footer" style="display:none;">
            <span class="anm-info" id="anm-info-text"></span>
            <button class="anm-load-more-btn" id="anm-load-more" onclick="loadMoreModal()" style="display:none;">
                <i class='bx bx-refresh'></i> Muat Lebih Banyak
            </button>
        </div>

    </div>
</div>
@endif

<style>
/* ─── SKELETON ───────────────────────────────────────────── */
.n-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: n-shimmer 1.4s infinite;
    display: block;
}
@keyframes n-shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* ─── NOTIFICATION DROPDOWN ──────────────────────────────── */
.notif-drop {
    min-width: 360px;
    right: -8px;
    padding: 0;
    overflow: hidden;
}

/* Header */
.nd-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.125rem 0.875rem;
}
.nd-header-left { display: flex; align-items: center; gap: 0.625rem; }
.nd-title { font-size: 1rem; font-weight: 800; color: var(--text); }
.nd-badge {
    background: #4f46e5; color: white;
    font-size: 0.65rem; font-weight: 800;
    padding: 0.15rem 0.5rem; border-radius: 999px;
    line-height: 1.6;
}
.nd-header-right { display: flex; align-items: center; gap: 0.25rem; }
.nd-icon-btn {
    width: 30px; height: 30px;
    background: none; border: none; cursor: pointer;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: var(--text-muted);
    transition: all 0.15s;
}
.nd-icon-btn:hover { background: var(--bg); color: var(--primary); }

/* Filter tabs */
.nd-tabs {
    display: flex; gap: 0.25rem;
    padding: 0.5rem 1rem 0;
}
.nd-tab {
    padding: 0.35rem 0.875rem;
    font-size: 0.8rem; font-weight: 600;
    border: none; cursor: pointer; border-radius: 7px;
    background: none; color: var(--text-muted);
    transition: all 0.15s;
}
.nd-tab:hover  { background: var(--bg); color: var(--text); }
.nd-tab.active { background: rgba(79,70,229,0.1); color: #4f46e5; }

.nd-divider { height: 1px; background: var(--border); margin: 0.5rem 0 0; }

/* Notification item */
.notif-item {
    display: flex; gap: .75rem; align-items: flex-start;
    padding: .75rem 1.125rem;
    transition: background .15s; cursor: pointer;
    position: relative;
    border-bottom: 1px solid rgba(0,0,0,0.04);
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #fafafa; }
.notif-item.is-unread { background: rgba(79,70,229,0.025); }
.notif-item.is-unread::after {
    content: '';
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 3px; background: #4f46e5;
    border-radius: 0 2px 2px 0;
}

.notif-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0; margin-top: 1px;
}
.notif-icon.warning   { background: #fef3c7; color: #d97706; }
.notif-icon.primary   { background: #dbeafe; color: #2563eb; }
.notif-icon.info      { background: #e0f2fe; color: #0284c7; }
.notif-icon.secondary { background: #f3f4f6; color: #6b7280; }
.notif-icon.success   { background: #d1fae5; color: #059669; }
.notif-icon.danger    { background: #fee2e2; color: #dc2626; }

.notif-body    { flex: 1; min-width: 0; }
.notif-title   { font-size: .8125rem; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 2px; }
.notif-text    { font-size: .775rem; color: var(--text-muted); line-height: 1.45; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.notif-time    { font-size: .72rem; color: var(--text-light); display: flex; align-items: center; gap: 3px; }
.notif-time i  { font-size: .75rem; }

.notif-actions { display: flex; flex-direction: column; gap: .25rem; flex-shrink: 0; opacity: 0; transition: opacity 0.15s; }
.notif-item:hover .notif-actions { opacity: 1; }

.notif-action-btn {
    background: none; border: none; cursor: pointer;
    width: 26px; height: 26px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: .8125rem; color: var(--text-light); transition: all .15s;
}
.notif-action-btn:hover { background: var(--border); color: var(--text); }

/* Empty state */
.nd-empty {
    display: flex; flex-direction: column; align-items: center; gap: .5rem;
    padding: 2.5rem 1rem;
}
.nd-empty-icon {
    width: 60px; height: 60px; border-radius: 50%;
    background: #f1f5f9;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem; color: #94a3b8; margin-bottom: 0.25rem;
}
.nd-empty-title { font-size: .875rem; font-weight: 700; color: var(--text); margin: 0; }
.nd-empty-sub   { font-size: .8rem; color: var(--text-light); margin: 0; }

/* Footer */
.nd-view-all-btn {
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    width: 100%; padding: 0.8rem 1rem;
    font-size: 0.875rem; font-weight: 700; color: #4f46e5;
    background: none; border: none; cursor: pointer;
    text-decoration: none; transition: background 0.15s;
}
.nd-view-all-btn:hover { background: rgba(79,70,229,0.05); }
.nd-view-all-btn i { font-size: 1rem; }

/* ─── ALL NOTIF MODAL (ADMIN & VENDOR) ───────────────────────── */
.anm-backdrop {
    position: fixed; inset: 0;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(3px);
    z-index: 9999;
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
    animation: fadeInBg 0.2s ease;
}
@keyframes fadeInBg {
    from { opacity: 0; }
    to   { opacity: 1; }
}

.anm-dialog {
    background: white;
    border-radius: 18px;
    width: 100%; max-width: 680px;
    max-height: 85vh;
    display: flex; flex-direction: column;
    box-shadow: 0 24px 64px rgba(0,0,0,0.14), 0 4px 16px rgba(0,0,0,0.08);
    animation: slideUpModal 0.22s cubic-bezier(.22,.68,0,1.2);
    overflow: hidden;
}
@keyframes slideUpModal {
    from { opacity: 0; transform: translateY(24px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* Modal Header */
.anm-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.375rem 1.5rem 1.125rem;
    border-bottom: 1px solid #f1f5f9;
    flex-shrink: 0;
}
.anm-header-left  { display: flex; align-items: center; gap: 0.875rem; }
.anm-header-icon  {
    width: 46px; height: 46px; border-radius: 12px;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; color: white;
}
.anm-title { font-size: 1.125rem; font-weight: 800; color: var(--text); margin: 0 0 2px; }
.anm-sub   { font-size: 0.8rem; color: var(--text-light); margin: 0; }

.anm-header-actions { display: flex; align-items: center; gap: 0.5rem; }
.anm-action-btn {
    display: flex; align-items: center; gap: 0.375rem;
    padding: 0.5rem 0.875rem;
    background: rgba(79,70,229,0.06); border: 1px solid rgba(79,70,229,0.15);
    border-radius: 8px; cursor: pointer;
    font-size: 0.8rem; font-weight: 600; color: #4f46e5;
    transition: all 0.15s;
}
.anm-action-btn:hover { background: rgba(79,70,229,0.12); }
.anm-action-btn i { font-size: 0.9375rem; }

.anm-close-btn {
    width: 36px; height: 36px; border-radius: 9px;
    background: none; border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; color: var(--text-muted);
    cursor: pointer; transition: all 0.15s;
}
.anm-close-btn:hover { background: #fee2e2; border-color: #fca5a5; color: #dc2626; }

/* Modal Filter Bar */
.anm-filter-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.875rem 1.5rem;
    background: #fafafa;
    border-bottom: 1px solid #f1f5f9;
    flex-shrink: 0;
    gap: 1rem;
}
.anm-tabs { display: flex; gap: 0.25rem; }
.anm-tab {
    padding: 0.375rem 0.875rem;
    font-size: 0.8125rem; font-weight: 600;
    border: none; cursor: pointer; border-radius: 7px;
    background: none; color: var(--text-muted);
    transition: all 0.15s;
}
.anm-tab:hover  { background: white; color: var(--text); }
.anm-tab.active { background: white; color: #4f46e5; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }

.anm-search-wrap {
    position: relative; flex-shrink: 0;
}
.anm-search-wrap i {
    position: absolute; left: 0.625rem; top: 50%; transform: translateY(-50%);
    font-size: 1rem; color: var(--text-light); pointer-events: none;
}
.anm-search {
    padding: 0.45rem 0.75rem 0.45rem 2rem;
    border: 1.5px solid var(--border); border-radius: 8px;
    font-size: 0.8125rem; color: var(--text); outline: none;
    transition: all 0.2s; width: 200px;
}
.anm-search:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.08); }

/* Modal List */
.anm-list-wrap {
    flex: 1; overflow-y: auto;
}
.anm-list-wrap::-webkit-scrollbar { width: 4px; }
.anm-list-wrap::-webkit-scrollbar-track { background: transparent; }
.anm-list-wrap::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

/* Modal item */
.anm-item {
    display: flex; gap: .875rem; align-items: flex-start;
    padding: 1rem 1.5rem;
    transition: background .15s; cursor: pointer;
    border-bottom: 1px solid #f8fafc;
    position: relative;
}
.anm-item:last-child { border-bottom: none; }
.anm-item:hover { background: #fafafa; }
.anm-item.is-unread { background: rgba(79,70,229,0.02); }
.anm-item.is-unread::after {
    content: '';
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 3px; background: #4f46e5;
    border-radius: 0 2px 2px 0;
}
.anm-item .notif-icon { width: 44px; height: 44px; border-radius: 11px; font-size: 1.2rem; }
.anm-item .notif-title { font-size: .875rem; }
.anm-item .notif-text  { font-size: .8125rem; }
.anm-item-actions { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
.anm-item-read-btn {
    padding: 0.3rem 0.625rem;
    font-size: 0.75rem; font-weight: 600;
    border: 1px solid var(--border); border-radius: 6px;
    background: none; cursor: pointer; color: var(--text-muted);
    transition: all 0.15s; white-space: nowrap;
}
.anm-item-read-btn:hover { background: rgba(79,70,229,0.06); border-color: rgba(79,70,229,0.3); color: #4f46e5; }
.anm-item-del-btn {
    width: 30px; height: 30px; border-radius: 7px;
    border: 1px solid var(--border); background: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem; color: var(--text-light); transition: all 0.15s;
}
.anm-item-del-btn:hover { background: #fee2e2; border-color: #fca5a5; color: #dc2626; }

/* Modal Empty */
.anm-empty {
    display: flex; flex-direction: column; align-items: center; gap: .625rem;
    padding: 4rem 2rem; text-align: center;
}
.anm-empty-icon {
    width: 72px; height: 72px; border-radius: 50%;
    background: #f1f5f9;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; color: #94a3b8; margin-bottom: 0.5rem;
}
.anm-empty-title { font-size: 1rem; font-weight: 700; color: var(--text); margin: 0; }
.anm-empty-sub   { font-size: .875rem; color: var(--text-light); margin: 0; }

/* Modal Footer */
.anm-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.875rem 1.5rem;
    border-top: 1px solid #f1f5f9;
    flex-shrink: 0;
}
.anm-info { font-size: 0.8rem; color: var(--text-light); }
.anm-load-more-btn {
    display: flex; align-items: center; gap: 0.375rem;
    padding: 0.45rem 0.875rem;
    font-size: 0.8125rem; font-weight: 600; color: #4f46e5;
    background: rgba(79,70,229,0.06); border: 1px solid rgba(79,70,229,0.15);
    border-radius: 8px; cursor: pointer; transition: all 0.15s;
}
.anm-load-more-btn:hover { background: rgba(79,70,229,0.12); }
.anm-load-more-btn i { font-size: 1rem; }

/* Scrollbar notif list */
#notif-list::-webkit-scrollbar { width: 3px; }
#notif-list::-webkit-scrollbar-track { background: transparent; }
#notif-list::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 3px; }
</style>

<script>
(function () {
    'use strict';

    const $ = id => document.getElementById(id);
    const CS = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ─── Helpers ─────────────────────────────────────────────── */

    function timeAgo(d) {
        const s = Math.floor((Date.now() - new Date(d)) / 1000);
        if (s < 60)    return 'Baru saja';
        if (s < 3600)  return Math.floor(s / 60) + ' menit lalu';
        if (s < 86400) return Math.floor(s / 3600) + ' jam lalu';
        return Math.floor(s / 86400) + ' hari lalu';
    }

    function iconCls(t) {
        const map = {
            ticket_created: 'warning', ticket_assigned: 'primary',
            ticket_status_changed: 'info', ticket_commented: 'secondary',
            ticket_resolved: 'success', ticket_updated: 'warning',
            ticket_closed: 'danger'
        };
        return map[t] ?? 'secondary';
    }

    function iconName(t) {
        const map = {
            ticket_created: 'bx-file-plus', ticket_assigned: 'bx-user-check',
            ticket_status_changed: 'bx-refresh', ticket_commented: 'bx-message-rounded',
            ticket_resolved: 'bx-check-circle', ticket_updated: 'bx-edit',
            ticket_closed: 'bx-x-circle'
        };
        return map[t] ?? 'bx-bell';
    }

    /**
     * Resolve the correct ticket URL based on role.
     * The role value is rendered server-side once by Blade so it is static.
     */
    const _role = '{{ Auth::user()->role }}';

    function ticketRoute(id) {
        if (_role === 'admin')  return '/admin/tickets/' + id;
        if (_role === 'vendor') return '/vendor/tickets/' + id;
        return '/client/tickets/' + id;
    }

    function apiFetch(url, opts = {}) {
        return fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept':           'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':     CS(),
                ...(opts.headers ?? {}),
            },
            ...opts,
        });
    }

    /* ─── Badge helpers ───────────────────────────────────────── */

    function getBadgeCount() {
        const cnt = $('notif-count');
        return Math.max(0, parseInt(cnt ? cnt.textContent : '0') || 0);
    }

    function setBadgeCount(n) {
        const badge  = $('notif-badge');
        const cnt    = $('notif-count');
        const markBtn = $('notif-mark-all-btn');
        if (n > 0) {
            const label = n > 99 ? '99+' : String(n);
            if (badge)  { badge.textContent = label; badge.style.display = 'flex'; }
            if (cnt)    { cnt.textContent = label; cnt.style.display = 'inline'; }
            if (markBtn) markBtn.style.display = 'inline-flex';
        } else {
            if (badge)  badge.style.display = 'none';
            if (cnt)    cnt.style.display = 'none';
            if (markBtn) markBtn.style.display = 'none';
        }
    }

    function decBadge() {
        setBadgeCount(Math.max(0, getBadgeCount() - 1));
    }

    function showDropEmpty() {
        if ($('notif-items')) $('notif-items').style.display = 'none';
        if ($('notif-empty')) $('notif-empty').style.display = 'flex';
    }

    /* ─── Mark-as-read API call ───────────────────────────────── */
    /**
     * Mark a notification as read via API.
     * Returns the parsed JSON response (which includes ticket_id from backend).
     * @param {string|number} id  – notification id
     * @param {HTMLElement}   li  – list item element to update visually
     * @param {boolean}       wasUnread
     * @param {boolean}       isModal – true when called from modal context
     */
    async function doMarkRead(id, li, wasUnread, isModal = false) {
        try {
            const res = await apiFetch('/notifications/' + id + '/read', { method: 'POST' });
            if (!res.ok) return null;
            const data = await res.json();

            // Update visual state
            li.classList.remove('is-unread');

            // Replace inline "Baca" button with double-check icon (modal)
            const readBtn = li.querySelector('.btn-modal-read, .btn-read');
            if (readBtn) {
                readBtn.outerHTML = '<span style="font-size:.75rem;color:var(--text-light);padding:0 .25rem;"><i class="bx bx-check-double"></i></span>';
            }

            // Decrement badge only if it was unread and we're in the dropdown context
            if (wasUnread && !isModal) decBadge();

            return data;
        } catch (e) {
            console.error('markRead:', e);
            return null;
        }
    }

    /* ─── Delete API call ─────────────────────────────────────── */
    async function doDelete(id, li, wasUnread, isModal = false) {
        try {
            const res = await apiFetch('/notifications/' + id, { method: 'DELETE' });
            if (!res.ok) return;
            li.style.opacity    = '0';
            li.style.transform  = 'translateX(20px)';
            li.style.transition = 'all .2s';
            setTimeout(function () {
                li.remove();
                if (wasUnread && !isModal) decBadge();
                // If dropdown list is now empty
                const dropUl = $('notif-items');
                if (dropUl && !dropUl.children.length) showDropEmpty();
            }, 200);
        } catch (e) {
            console.error('delete:', e);
        }
    }

    /* ─── Navigate to ticket ──────────────────────────────────── */
    function navigateToTicket(ticketId) {
        if (ticketId) window.location.href = ticketRoute(ticketId);
    }

    /* ─── DROPDOWN: render item ───────────────────────────────── */
    function renderDropItem(n) {
        const li = document.createElement('li');
        li.className  = 'notif-item' + (n.is_read ? '' : ' is-unread');
        li.dataset.id = n.id;

        li.innerHTML =
            '<div class="notif-icon ' + iconCls(n.type) + '">' +
                '<i class="bx ' + iconName(n.type) + '"></i>' +
            '</div>' +
            '<div class="notif-body">' +
                '<div class="notif-title">' + (n.title ?? 'Notifikasi') + '</div>' +
                '<div class="notif-text">'  + (n.message ?? '') + '</div>' +
                '<div class="notif-time"><i class="bx bx-time-five"></i>' + timeAgo(n.created_at) + '</div>' +
            '</div>' +
            '<div class="notif-actions">' +
                (!n.is_read
                    ? '<button class="notif-action-btn btn-read" title="Tandai dibaca"><i class="bx bx-check"></i></button>'
                    : '<button class="notif-action-btn" style="opacity:.3;cursor:default;" disabled><i class="bx bx-check-double"></i></button>') +
                '<button class="notif-action-btn btn-del" title="Hapus"><i class="bx bx-trash"></i></button>' +
            '</div>';

        // Click on item body → mark read then navigate
        li.addEventListener('click', async function (e) {
            if (e.target.closest('.notif-actions')) return;

            const wasUnread = !n.is_read;
            if (wasUnread) {
                // Mark read first, then navigate
                await doMarkRead(n.id, li, true, false);
                n.is_read = true;
            }
            navigateToTicket(n.ticket_id);
        });

        // "Mark read" button
        const btnRead = li.querySelector('.btn-read');
        if (btnRead) {
            btnRead.addEventListener('click', async function (e) {
                e.stopPropagation();
                if (!n.is_read) {
                    await doMarkRead(n.id, li, true, false);
                    n.is_read = true;
                }
            });
        }

        // "Delete" button
        li.querySelector('.btn-del').addEventListener('click', async function (e) {
            e.stopPropagation();
            await doDelete(n.id, li, !n.is_read, false);
        });

        return li;
    }

    /* ─── DROPDOWN: filter tab ────────────────────────────────── */
    let _dropFilter = 'all';
    window.filterNotif = function (type, btn) {
        _dropFilter = type;
        document.querySelectorAll('.nd-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        loadList();
    };

    /* ─── DROPDOWN: load unread count from server ─────────────── */
    async function loadCount() {
        try {
            const res = await apiFetch('/notifications/unread-count');
            if (!res.ok) return;
            const data = await res.json();
            setBadgeCount(data.count ?? 0);
        } catch (e) { /* silent */ }
    }

    /* ─── DROPDOWN: load list ─────────────────────────────────── */
    let _dropLoaded = false;

    // Wrap global toggleDrop to lazy-load the list on first open
    const _origToggle = window.toggleDrop;
    window.toggleDrop = function (id) {
        _origToggle(id);
        if (id === 'notif-drop' && !_dropLoaded) {
            _dropLoaded = true;
            loadList();
        }
    };

    async function loadList() {
        if ($('notif-loading')) $('notif-loading').style.display = 'block';
        if ($('notif-empty'))   $('notif-empty').style.display   = 'none';
        if ($('notif-items'))   $('notif-items').style.display   = 'none';

        try {
            let url = '/notifications?per_page=10';
            if (_dropFilter === 'unread') url += '&filter=unread';

            const res = await apiFetch(url);
            if ($('notif-loading')) $('notif-loading').style.display = 'none';
            if (!res.ok) { showDropEmpty(); return; }

            const body  = await res.json();
            const items = body.data ?? body;

            if (!Array.isArray(items) || !items.length) { showDropEmpty(); return; }

            const ul = $('notif-items');
            ul.innerHTML = '';
            items.forEach(n => ul.appendChild(renderDropItem(n)));
            ul.style.display = 'block';
        } catch (e) {
            console.error('loadList:', e);
            if ($('notif-loading')) $('notif-loading').style.display = 'none';
            showDropEmpty();
        }
    }

    /* ─── MARK ALL READ ───────────────────────────────────────── */
    window.markAllNotifRead = async function () {
        try {
            const res = await apiFetch('/notifications/mark-all-read', { method: 'POST' });
            if (!res.ok) return;
            // Update all rendered items visually
            document.querySelectorAll('.notif-item.is-unread, .anm-item.is-unread')
                .forEach(li => li.classList.remove('is-unread'));
            setBadgeCount(0);
        } catch (e) { console.error('markAll:', e); }
    };

    /* ════════════════════════════════════════════════════════════
       MODAL: Semua Notifikasi (Admin & Vendor)
       ════════════════════════════════════════════════════════════ */
    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'vendor')

    let _modalFilter = 'all';
    let _modalSearch = '';
    let _modalPage   = 1;
    let _modalTotal  = 0;

    /* ── Open / Close ── */
    window.openAllNotifModal = function () {
        const nd = $('notif-drop');
        if (nd) nd.style.display = 'none';

        const modal = $('all-notif-modal');
        if (!modal) return;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Reset to page 1 and reload
        _modalPage   = 1;
        _modalFilter = 'all';
        _modalSearch = '';

        // Reset filter tabs
        document.querySelectorAll('.anm-tab').forEach(b => b.classList.remove('active'));
        const firstTab = document.querySelector('.anm-tab');
        if (firstTab) firstTab.classList.add('active');

        // Clear search input
        const searchInput = $('anm-search');
        if (searchInput) searchInput.value = '';

        loadAllNotifModal();
    };

    window.closeAllNotifModal = function (e) {
        // If called from backdrop click, only close when clicking the backdrop itself
        if (e && e.target !== $('all-notif-modal')) return;
        _closeModal();
    };

    // Dedicated close (used by X button directly)
    const closeBtn = document.querySelector('.anm-close-btn');
    if (closeBtn) {
        closeBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            _closeModal();
        });
    }

    function _closeModal() {
        const modal = $('all-notif-modal');
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = '';
        // Refresh dropdown badge & list counts after closing modal
        loadCount();
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const modal = $('all-notif-modal');
            if (modal && modal.style.display !== 'none') _closeModal();
        }
    });

    /* ── Filter & Search ── */
    window.filterModal = function (type, btn) {
        _modalFilter = type;
        document.querySelectorAll('.anm-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        _modalPage = 1;
        loadAllNotifModal();
    };

    let _modalSearchTimer = null;
    window.searchModal = function (val) {
        clearTimeout(_modalSearchTimer);
        _modalSearchTimer = setTimeout(function () {
            _modalSearch = val.trim();
            _modalPage   = 1;
            loadAllNotifModal();
        }, 350);
    };

    /* ── Load modal data ── */
    async function loadAllNotifModal(append = false) {
        const loading = $('anm-loading');
        const empty   = $('anm-empty');
        const ul      = $('anm-items');
        const footer  = $('anm-footer');

        if (!append) {
            if (loading) loading.style.display = 'block';
            if (empty)   empty.style.display   = 'none';
            if (ul)      { ul.innerHTML = ''; ul.style.display = 'none'; }
            if (footer)  footer.style.display  = 'none';
        }

        try {
            let url = '/notifications?per_page=15&page=' + _modalPage;
            if (_modalFilter === 'unread') url += '&filter=unread';
            if (_modalFilter === 'read')   url += '&filter=read';
            if (_modalSearch)              url += '&search=' + encodeURIComponent(_modalSearch);

            const res = await apiFetch(url);
            if (loading) loading.style.display = 'none';
            if (!res.ok) { showModalEmpty(); return; }

            const body  = await res.json();
            const items = body.data ?? body;
            _modalTotal = body.total ?? (Array.isArray(items) ? items.length : 0);

            if (!Array.isArray(items) || (!items.length && !append)) {
                showModalEmpty(); return;
            }

            if (ul) {
                items.forEach(n => ul.appendChild(renderModalItem(n)));
                ul.style.display = 'block';
            }

            const sub = $('anm-sub-text');
            if (sub) sub.textContent = _modalTotal + ' total notifikasi';

            if (footer) {
                footer.style.display = 'flex';
                const info     = $('anm-info-text');
                const loadMore = $('anm-load-more');
                const shown    = ul ? ul.children.length : 0;
                if (info)     info.textContent = 'Menampilkan ' + shown + ' dari ' + _modalTotal + ' notifikasi';
                if (loadMore) loadMore.style.display = shown < _modalTotal ? 'flex' : 'none';
            }
        } catch (e) {
            console.error('loadModal:', e);
            if (loading) loading.style.display = 'none';
            showModalEmpty();
        }
    }

    window.loadMoreModal = function () {
        _modalPage++;
        loadAllNotifModal(true);
    };

    function showModalEmpty() {
        const empty = $('anm-empty');
        const ul    = $('anm-items');
        if (empty) empty.style.display = 'flex';
        if (ul)    ul.style.display   = 'none';
    }

    /* ── Modal: render item ── */
    function renderModalItem(n) {
        const li = document.createElement('li');
        li.className  = 'anm-item' + (n.is_read ? '' : ' is-unread');
        li.dataset.id = n.id;

        li.innerHTML =
            '<div class="notif-icon ' + iconCls(n.type) + '">' +
                '<i class="bx ' + iconName(n.type) + '"></i>' +
            '</div>' +
            '<div class="notif-body">' +
                '<div class="notif-title">' + (n.title ?? 'Notifikasi') + '</div>' +
                '<div class="notif-text">'  + (n.message ?? '') + '</div>' +
                '<div class="notif-time"><i class="bx bx-time-five"></i>' + timeAgo(n.created_at) + '</div>' +
            '</div>' +
            '<div class="anm-item-actions">' +
                (!n.is_read
                    ? '<button class="anm-item-read-btn btn-modal-read"><i class="bx bx-check" style="font-size:.85rem;margin-right:3px;"></i>Baca</button>'
                    : '<span style="font-size:.75rem;color:var(--text-light);padding:0 .25rem;"><i class="bx bx-check-double"></i></span>') +
                '<button class="anm-item-del-btn btn-modal-del" title="Hapus"><i class="bx bx-trash"></i></button>' +
            '</div>';

        // Click on item body → mark read then navigate (closes modal automatically via redirect)
        li.addEventListener('click', async function (e) {
            if (e.target.closest('.anm-item-actions')) return;

            const wasUnread = !n.is_read;
            if (wasUnread) {
                await doMarkRead(n.id, li, true, true);
                n.is_read = true;
            }
            // Navigate to ticket — works for vendor too
            navigateToTicket(n.ticket_id);
        });

        // "Baca" button in modal
        const btnRead = li.querySelector('.btn-modal-read');
        if (btnRead) {
            btnRead.addEventListener('click', async function (e) {
                e.stopPropagation();
                if (!n.is_read) {
                    await doMarkRead(n.id, li, true, true);
                    n.is_read = true;
                }
            });
        }

        // "Hapus" button in modal
        li.querySelector('.btn-modal-del').addEventListener('click', async function (e) {
            e.stopPropagation();
            try {
                const res = await apiFetch('/notifications/' + n.id, { method: 'DELETE' });
                if (!res.ok) return;
                li.style.opacity    = '0';
                li.style.transform  = 'translateX(20px)';
                li.style.transition = 'all .2s';
                setTimeout(function () {
                    li.remove();
                    _modalTotal = Math.max(0, _modalTotal - 1);
                    const sub = $('anm-sub-text');
                    if (sub) sub.textContent = _modalTotal + ' total notifikasi';
                    const ul = $('anm-items');
                    if (ul && !ul.children.length) showModalEmpty();

                    // Update footer count
                    const info = $('anm-info-text');
                    if (info && ul) {
                        info.textContent = 'Menampilkan ' + ul.children.length + ' dari ' + _modalTotal + ' notifikasi';
                    }
                }, 200);
            } catch (e) { console.error('modal delete:', e); }
        });

        return li;
    }

    @endif

    /* ─── INIT ────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        loadCount();
        setInterval(loadCount, 30000);
    });

})();
</script>