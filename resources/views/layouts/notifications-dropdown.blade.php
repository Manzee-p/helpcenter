{{-- ============================================================
     resources/views/partials/notifications-dropdown.blade.php
     Dipakai di: layouts/navbar.blade.php
     @include('partials.notifications-dropdown')
     ============================================================ --}}

{{-- Tombol notifikasi --}}
<button class="action-btn notif-btn" onclick="toggleDrop('notif-drop')" title="Notifikasi">
    <i class='bx bx-bell'></i>
    <span class="badge-dot" id="notif-badge" style="display:none;"></span>
</button>

{{-- Dropdown panel --}}
<div class="nav-drop notif-drop" id="notif-drop" style="display:none;">

    {{-- Header --}}
    <div class="drop-header">
        Notifikasi
        <div style="display:flex;align-items:center;gap:.5rem;">
            <span class="badge-count" id="notif-count" style="display:none;">0</span>
            <button
                id="notif-mark-all-btn"
                onclick="markAllNotifRead()"
                title="Tandai semua sudah dibaca"
                style="display:none;background:none;border:none;cursor:pointer;padding:0;color:var(--primary);"
            >
                <i class='bx bx-envelope-open' style="font-size:1.125rem;"></i>
            </button>
        </div>
    </div>

    <div class="drop-divider"></div>

    {{-- List area --}}
    <div id="notif-list" style="max-height:360px;overflow-y:auto;">

        {{-- Skeleton loader --}}
        <div id="notif-loading" style="display:none;padding:1.25rem;">
            @for ($i = 0; $i < 3; $i++)
            <div style="display:flex;gap:.75rem;align-items:flex-start;margin-bottom:1rem;">
                <div class="n-skeleton" style="width:38px;height:38px;border-radius:9px;flex-shrink:0;"></div>
                <div style="flex:1;">
                    <div class="n-skeleton" style="height:13px;width:70%;margin-bottom:6px;border-radius:4px;"></div>
                    <div class="n-skeleton" style="height:11px;width:90%;border-radius:4px;"></div>
                </div>
            </div>
            @endfor
        </div>

        {{-- Empty state --}}
        <div id="notif-empty" class="notif-empty-state" style="display:none;">
            <i class='bx bx-bell-off'></i>
            <span>Tidak ada notifikasi</span>
        </div>

        {{-- Notifications rendered by JS --}}
        <ul id="notif-items" style="list-style:none;margin:0;padding:0;display:none;"></ul>
    </div>

    <div class="drop-divider"></div>
    <a href="../notifications" class="view-all-link">Lihat Semua Notifikasi</a>
</div>

<style>
/* Skeleton shimmer */
.n-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: n-shimmer 1.4s infinite;
    display: block;
}
@keyframes n-shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Notification item */
.notif-item {
    display: flex; gap: .75rem; align-items: flex-start;
    padding: .875rem 1.125rem;
    border-bottom: 1px solid var(--border);
    transition: background .15s; cursor: pointer;
    position: relative;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: var(--bg); }
.notif-item.is-unread { background: rgba(79,70,229,.03); }
.notif-item.is-unread::before {
    content: '';
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 3px; background: var(--primary);
    border-radius: 0 3px 3px 0;
}

.notif-icon {
    width: 38px; height: 38px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.125rem; flex-shrink: 0;
}
.notif-icon.warning   { background: #fef3c7; color: #d97706; }
.notif-icon.primary   { background: #dbeafe; color: #2563eb; }
.notif-icon.info      { background: #e0f2fe; color: #0284c7; }
.notif-icon.secondary { background: #f3f4f6; color: #6b7280; }
.notif-icon.success   { background: #d1fae5; color: #059669; }

.notif-body   { flex: 1; min-width: 0; }
.notif-title  { font-size: .875rem; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.notif-text   { font-size: .8rem; color: var(--text-muted); margin-top: 2px; line-height: 1.4; }
.notif-time   { font-size: .75rem; color: var(--text-light); margin-top: 3px; }

.notif-actions { display: flex; flex-direction: column; gap: .25rem; flex-shrink: 0; }
.notif-action-btn {
    background: none; border: none; cursor: pointer;
    width: 26px; height: 26px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: .875rem; color: var(--text-light); transition: all .15s;
}
.notif-action-btn:hover { background: var(--border); color: var(--text); }

.notif-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.notif-dot.unread { background: var(--primary); }
.notif-dot.read   { background: transparent; border: 1.5px solid var(--border); }

.notif-empty-state {
    display: flex; flex-direction: column; align-items: center; gap: .625rem;
    padding: 2.5rem 1rem; color: var(--text-light); font-size: .875rem;
}
.notif-empty-state i { font-size: 2.5rem; }
</style>

<script>
(function () {
    'use strict';

    const $  = id => document.getElementById(id);
    const CS = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* â-€â-€ helpers â-€â-€ */
    function timeAgo(d) {
        const s = Math.floor((Date.now() - new Date(d)) / 1000);
        if (s < 60)    return 'Baru saja';
        if (s < 3600)  return Math.floor(s / 60)   + ' menit lalu';
        if (s < 86400) return Math.floor(s / 3600)  + ' jam lalu';
        return Math.floor(s / 86400) + ' hari lalu';
    }

    function iconCls(t) {
        const map = {
            ticket_created: 'warning', ticket_assigned: 'primary',
            ticket_status_changed: 'info', ticket_commented: 'secondary',
            ticket_resolved: 'success', ticket_updated: 'warning'
        };
        return map[t] ?? 'secondary';
    }

    function iconName(t) {
        const map = {
            ticket_created: 'bx-file', ticket_assigned: 'bx-user-check',
            ticket_status_changed: 'bx-refresh', ticket_commented: 'bx-message',
            ticket_resolved: 'bx-check-circle', ticket_updated: 'bx-edit'
        };
        return map[t] ?? 'bx-bell';
    }

    function ticketRoute(id) {
        const role = '{{ Auth::user()->role }}';
        if (role === 'admin')  return '/admin/tickets/' + id;
        if (role === 'vendor') return '/vendor/tickets/' + id;
        return '/tickets/' + id;
    }

    /* â-€â-€ fetch helper: credentials:'same-origin' agar session cookie ikut terkirim â-€â-€ */
    function apiFetch(url, opts = {}) {
        return fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept':           'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':     CS(),
                ...(opts.headers ?? {})
            },
            ...opts
        });
    }

    /* â-€â-€ render satu item â-€â-€ */
    function renderItem(n) {
        const li = document.createElement('li');
        li.className = 'notif-item' + (n.is_read ? '' : ' is-unread');
        li.dataset.id = n.id;
        li.innerHTML =
            '<div class="notif-icon ' + iconCls(n.type) + '">' +
                '<i class="bx ' + iconName(n.type) + '"></i>' +
            '</div>' +
            '<div class="notif-body">' +
                '<div class="notif-title">' + (n.title ?? 'Notifikasi') + '</div>' +
                '<div class="notif-text">'  + (n.message ?? '')          + '</div>' +
                '<div class="notif-time">'  + timeAgo(n.created_at)      + '</div>' +
            '</div>' +
            '<div class="notif-actions">' +
                '<button class="notif-action-btn btn-read" title="' + (n.is_read ? 'Sudah dibaca' : 'Tandai dibaca') + '">' +
                    '<span class="notif-dot ' + (n.is_read ? 'read' : 'unread') + '"></span>' +
                '</button>' +
                '<button class="notif-action-btn btn-del" title="Hapus">' +
                    '<i class="bx bx-x"></i>' +
                '</button>' +
            '</div>';

        /* klik item â†’ baca + navigasi */
        li.addEventListener('click', async function(e) {
            if (e.target.closest('.notif-actions')) return;
            if (!n.is_read) await doMarkRead(n, li);
            if (n.ticket_id) window.location.href = ticketRoute(n.ticket_id);
        });

        /* tombol baca */
        li.querySelector('.btn-read').addEventListener('click', async function(e) {
            e.stopPropagation();
            if (!n.is_read) await doMarkRead(n, li);
        });

        /* tombol hapus */
        li.querySelector('.btn-del').addEventListener('click', async function(e) {
            e.stopPropagation();
            await doDelete(n, li);
        });

        return li;
    }

    /* â-€â-€ API: mark read â-€â-€ */
    async function doMarkRead(n, li) {
        try {
            const res = await apiFetch('/notifications/' + n.id + '/read', { method: 'POST' });
            if (!res.ok) return;
            n.is_read = true;
            li.classList.remove('is-unread');
            var dot = li.querySelector('.notif-dot');
            if (dot) { dot.classList.remove('unread'); dot.classList.add('read'); }
            decBadge();
        } catch(e) { console.error('markRead:', e); }
    }

    /* â-€â-€ API: delete â-€â-€ */
    async function doDelete(n, li) {
        try {
            const res = await apiFetch('/notifications/' + n.id, { method: 'DELETE' });
            if (!res.ok) return;
            li.style.opacity = '0';
            li.style.transform = 'translateX(20px)';
            li.style.transition = 'all .2s';
            setTimeout(function() {
                li.remove();
                if (!n.is_read) decBadge();
                if (!$('notif-items').children.length) showEmpty();
            }, 200);
        } catch(e) { console.error('delete:', e); }
    }

    /* â-€â-€ badge count helper â-€â-€ */
    function decBadge() {
        var cnt = $('notif-count');
        var cur = Math.max(0, parseInt(cnt ? cnt.textContent : '0') - 1);
        if (cur === 0) {
            if ($('notif-badge')) $('notif-badge').style.display = 'none';
            if (cnt) cnt.style.display = 'none';
            if ($('notif-mark-all-btn')) $('notif-mark-all-btn').style.display = 'none';
        } else {
            if (cnt) cnt.textContent = cur;
        }
    }

    function showEmpty() {
        $('notif-items').style.display = 'none';
        $('notif-empty').style.display = 'flex';
    }

    /* â-€â-€ load unread count (dipanggil saat load + polling) â-€â-€ */
    async function loadCount() {
        try {
            var res = await apiFetch('/notifications/unread-count');
            if (!res.ok) return;
            var data = await res.json();
            var n = data.count ?? 0;
            if (n > 0) {
                if ($('notif-badge')) $('notif-badge').style.display = 'block';
                var cnt = $('notif-count');
                if (cnt) { cnt.textContent = n; cnt.style.display = 'inline'; }
                if ($('notif-mark-all-btn')) $('notif-mark-all-btn').style.display = 'inline-flex';
            } else {
                if ($('notif-badge')) $('notif-badge').style.display = 'none';
                var cnt2 = $('notif-count');
                if (cnt2) cnt2.style.display = 'none';
            }
        } catch(e) { /* silent */ }
    }

    /* â-€â-€ load list notifikasi (lazy: sekali buka dropdown) â-€â-€ */
    var loaded = false;
    var _origToggle = window.toggleDrop;
    window.toggleDrop = function(id) {
        _origToggle(id);
        if (id === 'notif-drop' && !loaded) {
            loaded = true;
            loadList();
        }
    };

    async function loadList() {
        $('notif-loading').style.display = 'block';
        $('notif-empty').style.display   = 'none';
        $('notif-items').style.display   = 'none';
        try {
            var res = await apiFetch('/notifications?per_page=10');
            $('notif-loading').style.display = 'none';
            if (!res.ok) { showEmpty(); return; }
            var body  = await res.json();
            var items = body.data ?? body;
            if (!Array.isArray(items) || !items.length) { showEmpty(); return; }
            var ul = $('notif-items');
            ul.innerHTML = '';
            items.forEach(function(n) { ul.appendChild(renderItem(n)); });
            ul.style.display = 'block';
        } catch(e) {
            console.error('loadList:', e);
            $('notif-loading').style.display = 'none';
            showEmpty();
        }
    }

    /* â-€â-€ mark all read (dipanggil dari tombol header) â-€â-€ */
    window.markAllNotifRead = async function() {
        try {
            var res = await apiFetch('/notifications/mark-all-read', { method: 'POST' });
            if (!res.ok) return;
            document.querySelectorAll('.notif-item.is-unread').forEach(function(li) {
                li.classList.remove('is-unread');
                var dot = li.querySelector('.notif-dot');
                if (dot) { dot.classList.remove('unread'); dot.classList.add('read'); }
            });
            if ($('notif-badge')) $('notif-badge').style.display = 'none';
            var cnt = $('notif-count');
            if (cnt) cnt.style.display = 'none';
            if ($('notif-mark-all-btn')) $('notif-mark-all-btn').style.display = 'none';
        } catch(e) { console.error('markAll:', e); }
    };

    /* â-€â-€ init â-€â-€ */
    document.addEventListener('DOMContentLoaded', function() {
        loadCount();
        setInterval(loadCount, 30000); // polling tiap 30 detik
    });

})();
</script>
