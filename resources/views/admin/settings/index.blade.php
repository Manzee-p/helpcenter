@extends('layouts.app')

@section('title', 'Pengaturan Admin')
@section('page_title', 'Pengaturan Akun')
@section('breadcrumb', 'Home / Pengaturan')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css"/>
<style>
/* ═══════════════════════════════════════════
   ADMIN SETTINGS — BLADE VERSION
═══════════════════════════════════════════ */
:root {
    --as-primary: #667eea;
    --as-secondary: #764ba2;
    --as-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --as-amber: linear-gradient(135deg, #f59e0b, #d97706);
    --as-teal: linear-gradient(135deg, #0d9488, #0891b2);
    --as-rose: linear-gradient(135deg, #f43f5e, #ec4899);
    --as-purple: linear-gradient(135deg, #8b5cf6, #7c3aed);
    --as-border: #e2e8f0;
    --as-bg: #f8fafc;
    --as-text: #1e293b;
    --as-muted: #64748b;
    --as-light: #94a3b8;
}

.as-wrap { display: flex; flex-direction: column; gap: 1.25rem; }

/* ── Hero ── */
.as-hero {
    background: var(--as-gradient);
    border-radius: 20px;
    padding: 1.75rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 8px 24px rgba(102,126,234,.3);
    flex-wrap: wrap;
    gap: 1rem;
}
.as-hero-left  { display: flex; align-items: center; gap: 1.25rem; }
.as-hero-right { display: flex; align-items: center; gap: .875rem; flex-wrap: wrap; }

.as-avatar-area  { position: relative; flex-shrink: 0; }
.as-avatar-ring  { position: relative; width: 76px; height: 76px; }
.as-avatar-img   { width: 76px; height: 76px; border-radius: 50%; object-fit: cover; border: 4px solid rgba(255,255,255,.5); }
.as-avatar-txt   { width: 76px; height: 76px; border-radius: 50%; border: 4px solid rgba(255,255,255,.5); background: rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; color: white; }
.as-avatar-online { position: absolute; bottom: 4px; right: 4px; width: 16px; height: 16px; background: #10b981; border: 3px solid white; border-radius: 50%; }
.as-avatar-upload-btn { position: absolute; bottom: -4px; right: -4px; width: 28px; height: 28px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,.2); transition: all .2s; border: none; }
.as-avatar-upload-btn:hover { transform: scale(1.1); background: #f5f3ff; }
.as-avatar-upload-btn i { font-size: .9rem; color: #667eea; }

.as-hero-name  { font-size: 1.35rem; font-weight: 800; color: white; margin-bottom: .2rem; }
.as-hero-email { font-size: .85rem; color: rgba(255,255,255,.8); margin-bottom: .625rem; }
.as-hero-badges { display: flex; gap: .5rem; flex-wrap: wrap; }
.as-badge { display: inline-flex; align-items: center; gap: .3rem; padding: .3rem .75rem; border-radius: 20px; font-size: .75rem; font-weight: 700; }
.as-badge.indigo { background: rgba(255,255,255,.25); color: white; }
.as-badge.green  { background: rgba(16,185,129,.25); color: #d1fae5; }
.as-badge.gray   { background: rgba(255,255,255,.15); color: rgba(255,255,255,.9); }

.as-hero-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .55rem 1.125rem; border-radius: 10px; font-size: .83rem; font-weight: 600; cursor: pointer; border: none; transition: all .2s; text-decoration: none; }
.as-hero-btn.save   { background: rgba(255,255,255,.25); color: white; border: 1px solid rgba(255,255,255,.4); }
.as-hero-btn.remove { background: rgba(239,68,68,.25); color: white; border: 1px solid rgba(239,68,68,.4); }
.as-hero-btn:hover  { transform: translateY(-1px); }
.as-hero-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }

.as-hero-stats { display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,.15); padding: .75rem 1.25rem; border-radius: 14px; }
.as-hstat      { display: flex; flex-direction: column; align-items: center; gap: .1rem; }
.as-hstat-val  { font-size: .9rem; font-weight: 700; color: white; }
.as-hstat-lbl  { font-size: .68rem; color: rgba(255,255,255,.7); }
.as-hstat-div  { width: 1px; height: 28px; background: rgba(255,255,255,.25); }

/* ── Tabs ── */
.as-tabs { display: flex; gap: .5rem; background: white; border-radius: 14px; padding: .5rem; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
.as-tab { flex: 1; display: flex; align-items: center; justify-content: center; gap: .5rem; padding: .75rem 1rem; border-radius: 10px; border: none; background: transparent; cursor: pointer; font-size: .9rem; font-weight: 600; color: #64748b; transition: all .25s; text-decoration: none; }
.as-tab:hover  { background: #f8fafc; color: #1e293b; text-decoration: none; }
.as-tab.active { background: var(--as-gradient); color: white !important; box-shadow: 0 4px 14px rgba(102,126,234,.3); }
.as-tab i { font-size: 1.1rem; }

/* ── Layout ── */
.as-two-col       { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
.as-two-col-equal { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; align-items: start; }
.as-mt { margin-top: 1.25rem; }

/* ── Cards ── */
.as-card { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.06); }
.as-card-head { padding: 1.125rem 1.5rem; color: white; display: flex; align-items: center; gap: .875rem; }
.as-card-ico  { width: 40px; height: 40px; background: rgba(255,255,255,.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
.as-card-head h6 { font-size: 1rem; font-weight: 700; margin: 0; flex: 1; color: white !important; }
.as-card-head p  { font-size: .78rem; margin: 0; opacity: .88; color: white !important; }
.as-card-body { padding: 1.5rem; }

.h-indigo { background: var(--as-gradient); }
.h-amber  { background: var(--as-amber); }
.h-rose   { background: var(--as-rose); }
.h-purple { background: var(--as-purple); }
.h-teal   { background: var(--as-teal); }

/* ── Avatar section ── */
.as-avatar-section { display: flex; gap: 1.5rem; align-items: flex-start; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9; }
.as-avatar-lg { width: 100px; height: 100px; border-radius: 50%; overflow: hidden; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,.1); background: var(--as-gradient); display: flex; align-items: center; justify-content: center; }
.as-avatar-lg img { width: 100%; height: 100%; object-fit: cover; }
.as-avatar-lg-txt { font-size: 2.25rem; font-weight: 700; color: white; }
.as-avatar-actions { display: flex; flex-direction: column; gap: .625rem; }
.as-avatar-hint { font-size: .78rem; color: #94a3b8; margin: 0; }

/* ── Form ── */
.as-grid-2    { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.as-field     { display: flex; flex-direction: column; gap: .35rem; }
.as-field-full { grid-column: 1/-1; }
.as-field label { font-size: .83rem; font-weight: 600; color: #475569; }
.req { color: #ef4444; }

.as-input { padding: .75rem .95rem; border: 2px solid #e2e8f0; border-radius: 10px; font-size: .875rem; color: #1e293b; background: white; transition: all .25s; font-family: inherit; width: 100%; box-sizing: border-box; resize: vertical; }
.as-input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,.1); }
.as-input.is-invalid { border-color: #ef4444 !important; }
.as-err-msg { color: #ef4444; font-size: .78rem; display: flex; align-items: center; gap: .3rem; margin: .25rem 0 0; }

/* ── Password ── */
.as-eye { position: relative; }
.as-eye .as-input { padding-right: 2.75rem; }
.as-eye-btn { position: absolute; right: .75rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 1.05rem; display: flex; padding: 0; }

/* ── Switch ── */
.as-switch { position: relative; display: inline-flex; align-items: center; cursor: pointer; }
.as-switch input { position: absolute; opacity: 0; width: 0; height: 0; }
.as-track { position: relative; width: 50px; height: 28px; background: #cbd5e1; border-radius: 28px; transition: background .3s; display: block; }
.as-track.sm { width: 40px; height: 22px; }
.as-thumb { position: absolute; background: white; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,.2); transition: transform .3s; display: block; top: 3px; left: 3px; width: 22px; height: 22px; }
.as-track.sm .as-thumb { width: 16px; height: 16px; }
.as-switch input:checked ~ .as-track { background: var(--as-gradient) !important; }
.as-switch input:checked ~ .as-track .as-thumb { transform: translateX(22px); }
.as-switch input:checked ~ .as-track.sm .as-thumb { transform: translateX(18px); }

/* ── Stat grid ── */
.as-stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
.as-stat-item { display: flex; flex-direction: column; align-items: center; gap: .25rem; padding: .875rem; border-radius: 12px; text-align: center; }
.as-stat-item.green  { background: #f0fdf4; } .as-stat-item.green  i { color: #10b981; }
.as-stat-item.blue   { background: #eff6ff; } .as-stat-item.blue   i { color: #3b82f6; }
.as-stat-item.purple { background: #faf5ff; } .as-stat-item.purple i { color: #8b5cf6; }
.as-stat-item.amber  { background: #fffbeb; } .as-stat-item.amber  i { color: #f59e0b; }
.as-stat-item i  { font-size: 1.4rem; }
.as-stat-v { font-size: .85rem; font-weight: 700; color: #1e293b; }
.as-stat-l { font-size: .7rem; color: #94a3b8; }

/* ── Divider / Labels ── */
.as-divider   { height: 1px; background: #f1f5f9; margin: 1.125rem 0; }
.as-sec-title { font-size: .72rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; margin: 0 0 .75rem; }
.as-sec-title.danger-txt { color: #ef4444; }

/* ── Security Score ── */
.as-security-score { display: flex; align-items: center; gap: 1.25rem; padding: .75rem; background: #f8fafc; border-radius: 12px; }
.as-score-circle { position: relative; width: 72px; height: 72px; flex-shrink: 0; }
.as-score-svg { width: 72px; height: 72px; transform: rotate(-90deg); }
.as-score-bg   { fill: none; stroke: #e2e8f0; stroke-width: 3.5; }
.as-score-fill { fill: none; stroke: url(#scoreGrad); stroke-width: 3.5; stroke-linecap: round; transition: stroke-dasharray .6s ease; }
.as-score-val { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); font-size: .95rem; font-weight: 800; color: #1e293b; display: flex; align-items: baseline; gap: 1px; }
.as-score-val span { font-size: .55rem; font-weight: 600; color: #94a3b8; }
.as-score-checks { display: flex; flex-direction: column; gap: .4rem; flex: 1; }
.as-scheck { display: flex; align-items: center; gap: .5rem; font-size: .78rem; font-weight: 600; }
.as-scheck.ok   i { color: #10b981; font-size: 1rem; }
.as-scheck.warn i { color: #f59e0b; font-size: 1rem; }

/* ── Info rows ── */
.as-info-rows  { display: flex; flex-direction: column; gap: .5rem; }
.as-info-row   { display: flex; align-items: center; justify-content: space-between; padding: .5rem 0; border-bottom: 1px solid #f8fafc; }
.as-info-lbl   { font-size: .83rem; color: #64748b; }
.as-info-val   { font-size: .83rem; font-weight: 600; color: #1e293b; }
.as-badge-sm   { display: inline-flex; align-items: center; gap: .25rem; padding: .25rem .625rem; border-radius: 20px; font-size: .72rem; font-weight: 700; }
.as-badge-sm.indigo { background: #ede9fe; color: #5b21b6; }
.as-badge-sm.green  { background: #d1fae5; color: #065f46; }

/* ── Quick links ── */
.as-quick-links { display: flex; flex-direction: column; gap: .4rem; }
.as-qlink { display: flex; align-items: center; gap: .75rem; padding: .75rem .875rem; border-radius: 10px; text-decoration: none; color: #1e293b; background: #f8fafc; transition: all .2s; border: 1px solid transparent; }
.as-qlink:hover { background: #f1f5f9; border-color: #e2e8f0; transform: translateX(3px); text-decoration: none; }
.as-qlink > span { flex: 1; font-size: .85rem; font-weight: 600; }
.as-qlink > i.chevron { color: #94a3b8; }
.as-qico { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }

/* ── Tips ── */
.as-tips { display: flex; flex-direction: column; gap: .5rem; }
.as-tip  { display: flex; align-items: center; gap: .75rem; padding: .625rem .875rem; background: #f8fafc; border-radius: 10px; font-size: .82rem; color: #475569; }
.as-tip-ico { width: 28px; height: 28px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0; }

/* ── List items ── */
.as-list { display: flex; flex-direction: column; gap: .5rem; }
.as-activity-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
.as-list-item { display: flex; align-items: center; gap: .875rem; padding: .875rem 1rem; background: #f8fafc; border-radius: 12px; border: 1px solid transparent; transition: all .2s; }
.as-list-item:hover { background: #f1f5f9; border-color: #e2e8f0; }
.as-list-ico  { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: white; flex-shrink: 0; }
.as-list-ico.success { background: var(--as-gradient); }
.as-list-ico.danger  { background: linear-gradient(135deg,#ef4444,#dc2626); }
.as-list-ico.indigo  { background: var(--as-gradient); }
.as-list-ico.warning { background: var(--as-amber); }
.as-list-info { flex: 1; min-width: 0; }
.as-list-title { font-size: .875rem; font-weight: 600; color: #1e293b; margin: 0 0 .15rem; }
.as-list-sub   { font-size: .78rem; color: #64748b; margin: 0 0 .15rem; }
.as-list-time  { font-size: .72rem; color: #94a3b8; }
.as-status-badge { padding: .3rem .75rem; border-radius: 6px; font-size: .75rem; font-weight: 600; display: flex; align-items: center; gap: .25rem; flex-shrink: 0; }
.as-status-badge.success { background: #d1fae5; color: #059669; }
.as-status-badge.danger  { background: #fee2e2; color: #dc2626; }
.as-end-btn { display: flex; align-items: center; gap: .35rem; padding: .5rem 1rem; background: #fee2e2; color: #dc2626; border: none; border-radius: 8px; font-size: .78rem; font-weight: 600; cursor: pointer; flex-shrink: 0; transition: all .2s; }
.as-end-btn:hover { background: #fecaca; }
.as-current-badge { padding: .4rem .875rem; background: #d1fae5; color: #059669; border-radius: 6px; font-size: .75rem; font-weight: 600; display: flex; align-items: center; gap: .3rem; flex-shrink: 0; }
.as-refresh-btn { width: 34px; height: 34px; background: rgba(255,255,255,.2); border: none; border-radius: 8px; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; transition: all .25s; flex-shrink: 0; margin-left: auto; }
.as-refresh-btn:hover { background: rgba(255,255,255,.3); transform: rotate(90deg); }

/* ── Loading / Empty ── */
.as-loading,.as-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2.5rem; gap: .75rem; }
.as-loading i,.as-empty i { font-size: 2.5rem; }
.as-loading i { color: #667eea; }
.as-empty i { color: #d1d5db; }
.as-loading p,.as-empty p { font-size: .875rem; color: #9ca3af; margin: 0; }

/* ── Password strength ── */
.as-strength { display: flex; align-items: center; gap: .5rem; margin-top: .3rem; }
.as-bars { flex: 1; height: 4px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
.as-bar-fill { height: 100%; border-radius: 4px; transition: all .3s; }
.as-bar-fill.weak   { background: #ef4444; width: 33%; }
.as-bar-fill.medium { background: #f59e0b; width: 66%; }
.as-bar-fill.strong { background: #10b981; width: 100%; }
.as-str-txt { font-size: .72rem; font-weight: 600; min-width: 70px; }
.as-str-txt.weak   { color: #ef4444; }
.as-str-txt.medium { color: #f59e0b; }
.as-str-txt.strong { color: #10b981; }
.as-ok-txt { color: #10b981; font-size: .78rem; display: flex; align-items: center; gap: .3rem; margin: .25rem 0 0; }

/* ── 2FA ── */
.as-2fa-row  { display: flex; align-items: center; gap: .875rem; padding: .875rem 1rem; background: #f8fafc; border-radius: 12px; margin-bottom: 1rem; }
.as-2fa-left { display: flex; align-items: center; gap: .875rem; flex: 1; }
.as-2fa-ico  { width: 42px; height: 42px; background: #ede9fe; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #7c3aed; flex-shrink: 0; }
.as-2fa-title { font-size: .9rem; font-weight: 700; color: #1e293b; margin: 0; }
.as-2fa-desc  { font-size: .78rem; color: #64748b; margin: 0; }
.as-info-box  { display: flex; gap: .875rem; padding: .875rem 1rem; border-radius: 12px; font-size: .83rem; margin-bottom: 1rem; }
.as-info-box.blue { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
.as-info-box i { font-size: 1.2rem; color: #2563eb; flex-shrink: 0; }
.as-info-box strong { display: block; font-weight: 700; margin-bottom: .15rem; }
.as-info-box p { margin: 0; }

/* ── Notifications ── */
.as-notif-list  { display: flex; flex-direction: column; gap: .75rem; }
.as-notif-item  { display: flex; align-items: center; gap: 1rem; padding: .875rem 1rem; background: #f8fafc; border-radius: 12px; border: 1px solid transparent; transition: all .2s; }
.as-notif-item:hover { background: #f1f5f9; border-color: #e2e8f0; }
.as-notif-ico   { width: 42px; height: 42px; background: var(--as-gradient); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: white; flex-shrink: 0; }
.as-notif-info  { flex: 1; min-width: 0; }
.as-notif-title { font-size: .9rem; font-weight: 600; color: #1e293b; margin: 0 0 .15rem; }
.as-notif-desc  { font-size: .78rem; color: #64748b; margin: 0; }
.as-notif-toggles { display: flex; gap: 1.25rem; flex-shrink: 0; }
.as-toggle-opt  { display: flex; flex-direction: column; align-items: center; gap: .5rem; }
.as-toggle-opt > span { font-size: .7rem; font-weight: 600; color: #94a3b8; }

.as-notif-stats { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
.as-nstat { display: flex; align-items: center; gap: .75rem; padding: .875rem; background: #f8fafc; border-radius: 12px; }
.as-nstat-ico { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.as-nstat-val { font-size: .9rem; font-weight: 700; color: #1e293b; margin: 0; }
.as-nstat-lbl { font-size: .7rem; color: #94a3b8; margin: 0; }

/* ── Theme Options ── */
.as-theme-opts { display: grid; grid-template-columns: repeat(3,1fr); gap: .75rem; }
.as-theme-opt  { display: flex; flex-direction: column; align-items: center; gap: .5rem; padding: 1.125rem .875rem; background: #f9fafb; border: 2px solid #e5e7eb; border-radius: 12px; cursor: pointer; transition: all .25s; }
.as-theme-opt:hover,.as-theme-opt.active { border-color: #667eea; background: #f5f3ff; }
.as-theme-opt input { display: none; }
.as-theme-opt i { font-size: 1.75rem; color: #6b7280; }
.as-theme-opt.active i { color: #667eea; }
.as-theme-opt span { font-size: .83rem; font-weight: 600; color: #374151; }

/* ── Select ── */
.as-sel-wrap { position: relative; display: flex; align-items: center; }
.as-sel-ico  { position: absolute; left: .875rem; font-size: 1.1rem; color: #94a3b8; pointer-events: none; z-index: 1; }
.as-select   { width: 100%; padding: .75rem 2.5rem .75rem 2.5rem; border: 2px solid #e2e8f0; border-radius: 10px; font-size: .875rem; color: #1e293b; background: white; appearance: none; cursor: pointer; transition: all .25s; font-family: inherit; }
.as-select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,.1); }
.as-sel-wrap::after { content: '⌄'; position: absolute; right: 1rem; color: #94a3b8; pointer-events: none; font-weight: 700; }

/* ── Export ── */
.as-export-box  { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.125rem; background: #f8fafc; border-radius: 12px; border: 1px solid #f1f5f9; flex-wrap: wrap; }
.as-export-info { display: flex; align-items: flex-start; gap: .875rem; flex: 1; }
.as-export-ico  { width: 44px; height: 44px; background: #ccfbf1; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; color: #0d9488; flex-shrink: 0; }
.as-export-title { font-size: .9rem; font-weight: 700; color: #1e293b; margin: 0 0 .2rem; }
.as-export-desc  { font-size: .78rem; color: #64748b; margin: 0; }

/* ── Policy ── */
.as-policy-list { display: flex; flex-direction: column; gap: .5rem; }
.as-policy-row  { display: flex; align-items: flex-start; gap: .75rem; padding: .75rem; background: #f8fafc; border-radius: 10px; }
.as-policy-ico  { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.as-policy-title { font-size: .83rem; font-weight: 700; color: #1e293b; margin: 0 0 .15rem; }
.as-policy-desc  { font-size: .75rem; color: #64748b; margin: 0; }

/* ── Danger ── */
.as-danger-box  { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1rem 1.125rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; flex-wrap: wrap; }
.as-danger-title { font-size: .9rem; font-weight: 700; color: #991b1b; margin: 0 0 .25rem; }
.as-danger-desc  { font-size: .78rem; color: #b91c1c; margin: 0; }

/* ── Actions / Buttons ── */
.as-actions { margin-top: 1.25rem; padding-top: 1.125rem; border-top: 1px solid #f1f5f9; display: flex; gap: .625rem; flex-wrap: wrap; }
.as-btn { display: inline-flex; align-items: center; gap: .45rem; padding: .7rem 1.375rem; border-radius: 10px; font-weight: 600; font-size: .875rem; cursor: pointer; border: none; transition: all .25s; text-decoration: none !important; }
.as-btn:disabled { opacity: .6; cursor: not-allowed; transform: none !important; }
.as-btn.indigo        { background: var(--as-gradient); color: white; }
.as-btn.indigo:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(102,126,234,.35); }
.as-btn.amber         { background: var(--as-amber); color: white; }
.as-btn.amber:hover:not(:disabled)  { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(245,158,11,.35); }
.as-btn.teal          { background: var(--as-teal); color: white; }
.as-btn.teal:hover:not(:disabled)   { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(13,148,136,.35); }
.as-btn.red           { background: linear-gradient(135deg,#ef4444,#dc2626); color: white; }
.as-btn.red:hover:not(:disabled)    { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(239,68,68,.35); }
.as-btn.gray          { background: #f3f4f6; color: #6b7280; }
.as-btn.gray:hover:not(:disabled)   { background: #e5e7eb; }
.as-btn.indigo-outline { background: transparent; color: #667eea; border: 2px solid #667eea; }
.as-btn.indigo-outline:hover { background: #f5f3ff; }
.as-btn.red-outline    { background: transparent; color: #ef4444; border: 2px solid #fca5a5; }
.as-btn.red-outline:hover { background: #fff1f2; }

/* ── Spinner ── */
.spin { animation: spin 1s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Tab Panels ── */
.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* ── Responsive ── */
@media (max-width: 992px) {
    .as-two-col, .as-two-col-equal { grid-template-columns: 1fr; }
    .as-hero-stats { display: none; }
}
@media (max-width: 768px) {
    .as-hero { flex-direction: column; }
    .as-grid-2 { grid-template-columns: 1fr; }
    .as-theme-opts { grid-template-columns: 1fr; }
    .as-activity-grid { grid-template-columns: 1fr; }
    .as-notif-item { flex-wrap: wrap; }
    .as-avatar-section { flex-direction: column; align-items: center; text-align: center; }
    .as-tabs { flex-wrap: wrap; }
    .as-tab { flex: 0 0 calc(50% - .25rem); font-size: .82rem; }
}
@media (max-width: 480px) {
    .as-card-body { padding: 1.125rem; }
    .as-actions { flex-direction: column; }
    .as-btn { width: 100%; justify-content: center; }
}

/* ── Alert Toast ── */
.as-toast {
    position: fixed;
    top: 1.5rem;
    right: 1.5rem;
    z-index: 9999;
    min-width: 280px;
    max-width: 400px;
    padding: 1rem 1.25rem;
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: .75rem;
    font-size: .9rem;
    font-weight: 600;
    box-shadow: 0 8px 24px rgba(0,0,0,.15);
    transform: translateX(120%);
    transition: transform .4s cubic-bezier(.34,1.56,.64,1);
}
.as-toast.show { transform: translateX(0); }
.as-toast.success { background: #f0fdf4; color: #065f46; border: 1px solid #bbf7d0; }
.as-toast.error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.as-toast i { font-size: 1.25rem; flex-shrink: 0; }
.as-toast-close { margin-left: auto; background: none; border: none; cursor: pointer; color: inherit; opacity: .6; font-size: 1.1rem; }
.as-toast-close:hover { opacity: 1; }
</style>
@endpush

@section('content')
@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('');
    $avatarUrl = $user->avatar ? asset('storage/'.$user->avatar) : null;
    $secScore = 0;
    if($user->name && $user->email) $secScore += 25;
    if($user->phone) $secScore += 25;
    if($user->avatar) $secScore += 25;
    if($user->two_factor_enabled) $secScore += 25;
@endphp

{{-- SVG Gradient defs --}}
<svg width="0" height="0" style="position:absolute">
    <defs>
        <linearGradient id="scoreGrad" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" style="stop-color:#667eea"/>
            <stop offset="100%" style="stop-color:#10b981"/>
        </linearGradient>
    </defs>
</svg>

{{-- Toast --}}
<div class="as-toast" id="asToast">
    <i class="bx" id="asToastIcon"></i>
    <span id="asToastMsg"></span>
    <button class="as-toast-close" onclick="hideToast()"><i class="bx bx-x"></i></button>
</div>

<div class="as-wrap">

{{-- ═══ HERO ═══ --}}
<section class="as-hero">
    <div class="as-hero-left">
        <div class="as-avatar-area">
            <div class="as-avatar-ring">
                @if($avatarUrl)
                    <img src="{{ $avatarUrl }}" class="as-avatar-img" id="heroAvatar" alt="Avatar"/>
                @else
                    <div class="as-avatar-txt" id="heroAvatarTxt">{{ $initials }}</div>
                @endif
                <span class="as-avatar-online"></span>
            </div>
            <button class="as-avatar-upload-btn" onclick="document.getElementById('avatarInput').click()" title="Ganti Foto">
                <i class="bx bx-camera"></i>
            </button>
        </div>
        <div class="as-hero-info">
            <div class="as-hero-name" id="heroName">{{ $user->name }}</div>
            <div class="as-hero-email">{{ $user->email }}</div>
            <div class="as-hero-badges">
                <span class="as-badge indigo"><i class="bx bx-shield-check"></i> Administrator</span>
                <span class="as-badge green"><i class="bx bx-radio-circle-marked"></i> Aktif</span>
                @if($user->position)
                    <span class="as-badge gray"><i class="bx bx-briefcase"></i> {{ $user->position }}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="as-hero-right">
        <div class="as-hero-stats">
            <div class="as-hstat">
                <span class="as-hstat-val">Baru saja</span>
                <span class="as-hstat-lbl">Login Terakhir</span>
            </div>
            <div class="as-hstat-div"></div>
            <div class="as-hstat">
                <span class="as-hstat-val">1</span>
                <span class="as-hstat-lbl">Sesi Aktif</span>
            </div>
            <div class="as-hstat-div"></div>
            <div class="as-hstat">
                <span class="as-hstat-val" id="heroActivityCount">0</span>
                <span class="as-hstat-lbl">Aktivitas</span>
            </div>
        </div>
    </div>
</section>

{{-- ═══ TABS ═══ --}}
<div class="as-tabs">
    <button class="as-tab active" onclick="switchTab('profile', this)">
        <i class="bx bx-user"></i><span>Profil</span>
    </button>
    <button class="as-tab" onclick="switchTab('security', this)">
        <i class="bx bx-lock-alt"></i><span>Keamanan</span>
    </button>
    <button class="as-tab" onclick="switchTab('notifications', this)">
        <i class="bx bx-bell"></i><span>Notifikasi</span>
    </button>
    <button class="as-tab" onclick="switchTab('preferences', this)">
        <i class="bx bx-cog"></i><span>Preferensi</span>
    </button>
</div>

{{-- ═══ TAB: PROFIL ═══ --}}
<div class="tab-panel active" id="panel-profile">
    <div class="as-two-col-equal">

        {{-- Form Profil --}}
        <div class="as-card">
            <div class="as-card-head h-indigo">
                <div class="as-card-ico"><i class="bx bx-user"></i></div>
                <div><h6>Informasi Profil</h6><p>Perbarui data profil administrator</p></div>
            </div>
            <div class="as-card-body">

                {{-- Avatar --}}
                <div class="as-avatar-section">
                    <div class="as-avatar-lg">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" id="avatarPreview" alt="Avatar"/>
                        @else
                            <div class="as-avatar-lg-txt" id="avatarInitials">{{ $initials }}</div>
                        @endif
                    </div>
                    <div class="as-avatar-actions">
                        <button type="button" class="as-btn indigo-outline" onclick="document.getElementById('avatarInput').click()">
                            <i class="bx bx-upload"></i> Upload Foto
                        </button>
                        @if($avatarUrl)
                        <button type="button" class="as-btn red-outline" id="btnRemoveAvatar" onclick="removeAvatar()">
                            <i class="bx bx-trash"></i> Hapus Foto
                        </button>
                        @endif
                        <p class="as-avatar-hint">JPG, PNG atau GIF. Maksimal 2MB</p>
                    </div>
                    <input type="file" id="avatarInput" accept="image/*" style="display:none" onchange="handleAvatarChange(this)"/>
                </div>

                <form id="profileForm" onsubmit="submitProfile(event)">
                    @csrf
                    <div class="as-grid-2">
                        <div class="as-field">
                            <label>Nama Lengkap <span class="req">*</span></label>
                            <input class="as-input" type="text" name="name" value="{{ $user->name }}" required/>
                        </div>
                        <div class="as-field">
                            <label>Email <span class="req">*</span></label>
                            <input class="as-input" type="email" name="email" value="{{ $user->email }}" required/>
                        </div>
                        <div class="as-field">
                            <label>Nomor Telepon</label>
                            <input class="as-input" type="tel" name="phone" value="{{ $user->phone }}"/>
                        </div>
                        <div class="as-field">
                            <label>Jabatan</label>
                            <input class="as-input" type="text" name="position" value="{{ $user->position }}"/>
                        </div>
                        <div class="as-field">
                            <label>Perusahaan</label>
                            <input class="as-input" type="text" name="company" value="{{ $user->company }}"/>
                        </div>
                        <div class="as-field as-field-full">
                            <label>Bio</label>
                            <textarea class="as-input" name="bio" rows="3">{{ $user->bio }}</textarea>
                        </div>
                    </div>
                    <div class="as-actions">
                        <button type="button" class="as-btn gray" onclick="resetProfileForm()">
                            <i class="bx bx-reset"></i> Reset
                        </button>
                        <button type="submit" class="as-btn indigo" id="btnSaveProfile">
                            <i class="bx bx-save"></i> Simpan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Ringkasan Akun --}}
        <div class="as-card">
            <div class="as-card-head h-teal">
                <div class="as-card-ico"><i class="bx bx-bar-chart-alt-2"></i></div>
                <div><h6>Ringkasan Akun</h6><p>Status & keamanan akun Anda</p></div>
            </div>
            <div class="as-card-body">
                <div class="as-stat-grid">
                    <div class="as-stat-item green">
                        <i class="bx bx-devices"></i>
                        <span class="as-stat-v">1</span>
                        <span class="as-stat-l">Perangkat Aktif</span>
                    </div>
                    <div class="as-stat-item blue">
                        <i class="bx bx-time-five"></i>
                        <span class="as-stat-v">Baru saja</span>
                        <span class="as-stat-l">Login Terakhir</span>
                    </div>
                    <div class="as-stat-item purple">
                        <i class="bx bx-list-ul"></i>
                        <span class="as-stat-v" id="activityCountStat">0</span>
                        <span class="as-stat-l">Log Aktivitas</span>
                    </div>
                    <div class="as-stat-item amber">
                        <i class="bx bx-history"></i>
                        <span class="as-stat-v" id="loginHistoryCount">0</span>
                        <span class="as-stat-l">Riwayat Login</span>
                    </div>
                </div>

                <div class="as-divider"></div>

                <p class="as-sec-title">Skor Keamanan Akun</p>
                <div class="as-security-score">
                    <div class="as-score-circle">
                        <svg viewBox="0 0 36 36" class="as-score-svg">
                            <path class="as-score-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path class="as-score-fill" stroke-dasharray="{{ $secScore }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                        <div class="as-score-val">{{ $secScore }}<span>%</span></div>
                    </div>
                    <div class="as-score-checks">
                        <div class="as-scheck {{ ($user->name && $user->email) ? 'ok' : 'warn' }}">
                            <i class="bx {{ ($user->name && $user->email) ? 'bx-check-circle' : 'bx-error-circle' }}"></i>
                            <span>Profil lengkap</span>
                        </div>
                        <div class="as-scheck {{ $user->phone ? 'ok' : 'warn' }}">
                            <i class="bx {{ $user->phone ? 'bx-check-circle' : 'bx-error-circle' }}"></i>
                            <span>Nomor telepon</span>
                        </div>
                        <div class="as-scheck {{ $user->avatar ? 'ok' : 'warn' }}">
                            <i class="bx {{ $user->avatar ? 'bx-check-circle' : 'bx-error-circle' }}"></i>
                            <span>Foto profil</span>
                        </div>
                        <div class="as-scheck {{ $user->two_factor_enabled ? 'ok' : 'warn' }}">
                            <i class="bx {{ $user->two_factor_enabled ? 'bx-check-circle' : 'bx-error-circle' }}"></i>
                            <span>2FA aktif</span>
                        </div>
                    </div>
                </div>

                <div class="as-divider"></div>

                <p class="as-sec-title">Informasi Akun</p>
                <div class="as-info-rows">
                    <div class="as-info-row">
                        <span class="as-info-lbl">Role</span>
                        <span class="as-badge-sm indigo">Administrator</span>
                    </div>
                    <div class="as-info-row">
                        <span class="as-info-lbl">Status</span>
                        <span class="as-badge-sm green"><i class="bx bx-radio-circle-marked"></i> Aktif</span>
                    </div>
                    @if($user->company)
                    <div class="as-info-row">
                        <span class="as-info-lbl">Perusahaan</span>
                        <span class="as-info-val">{{ $user->company }}</span>
                    </div>
                    @endif
                    @if($user->position)
                    <div class="as-info-row">
                        <span class="as-info-lbl">Jabatan</span>
                        <span class="as-info-val">{{ $user->position }}</span>
                    </div>
                    @endif
                </div>

                <div class="as-divider"></div>

                <p class="as-sec-title">Akses Cepat</p>
                <div class="as-quick-links">
                    <a class="as-qlink" href="{{ route('admin.dashboard') }}">
                        <span class="as-qico" style="background:#dbeafe;color:#2563eb"><i class="bx bx-home-circle"></i></span>
                        <span>Dashboard</span><i class="bx bx-chevron-right chevron"></i>
                    </a>
                    <a class="as-qlink" href="{{ route('admin.users.index') }}">
                        <span class="as-qico" style="background:#ede9fe;color:#7c3aed"><i class="bx bx-user"></i></span>
                        <span>Kelola Users</span><i class="bx bx-chevron-right chevron"></i>
                    </a>
                    <a class="as-qlink" href="{{ route('admin.vendors.index') }}">
                        <span class="as-qico" style="background:#fef3c7;color:#d97706"><i class="bx bx-store"></i></span>
                        <span>Kelola Vendors</span><i class="bx bx-chevron-right chevron"></i>
                    </a>
                    <a class="as-qlink" href="{{ route('admin.tickets.index') }}">
                        <span class="as-qico" style="background:#ffe4e6;color:#e11d48"><i class="bx bx-support"></i></span>
                        <span>Tiket Masuk</span><i class="bx bx-chevron-right chevron"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ═══ TAB: KEAMANAN ═══ --}}
<div class="tab-panel" id="panel-security">
    <div class="as-two-col">

        {{-- Ubah Password --}}
        <div class="as-card">
            <div class="as-card-head h-amber">
                <div class="as-card-ico"><i class="bx bx-lock-alt"></i></div>
                <div><h6>Ubah Password</h6><p>Gunakan password yang kuat untuk keamanan akun</p></div>
            </div>
            <div class="as-card-body">
                <form id="passwordForm" onsubmit="submitPassword(event)">
                    @csrf
                    <div class="as-pw-fields" style="display:flex;flex-direction:column;gap:1rem">
                        <div class="as-field">
                            <label>Password Saat Ini <span class="req">*</span></label>
                            <div class="as-eye">
                                <input type="password" class="as-input" name="current_password" id="pwCurrent" placeholder="Password saat ini" required/>
                                <button type="button" class="as-eye-btn" onclick="togglePw('pwCurrent',this)"><i class="bx bx-show"></i></button>
                            </div>
                        </div>
                        <div class="as-field">
                            <label>Password Baru <span class="req">*</span></label>
                            <div class="as-eye">
                                <input type="password" class="as-input" name="new_password" id="pwNew" placeholder="Min. 8 karakter" required minlength="8" oninput="checkPwStrength(this.value)"/>
                                <button type="button" class="as-eye-btn" onclick="togglePw('pwNew',this)"><i class="bx bx-show"></i></button>
                            </div>
                            <div class="as-strength" id="pwStrengthBar" style="display:none">
                                <div class="as-bars"><div class="as-bar-fill" id="pwBarFill"></div></div>
                                <span class="as-str-txt" id="pwStrText"></span>
                            </div>
                        </div>
                        <div class="as-field">
                            <label>Konfirmasi Password <span class="req">*</span></label>
                            <div class="as-eye">
                                <input type="password" class="as-input" name="new_password_confirmation" id="pwConfirm" placeholder="Ulangi password baru" required oninput="checkPwMatch()"/>
                                <button type="button" class="as-eye-btn" onclick="togglePw('pwConfirm',this)"><i class="bx bx-show"></i></button>
                            </div>
                            <p class="as-err-msg" id="pwMismatch" style="display:none"><i class="bx bx-error-circle"></i> Password tidak cocok</p>
                            <p class="as-ok-txt" id="pwMatch" style="display:none"><i class="bx bx-check-circle"></i> Password cocok</p>
                        </div>
                    </div>
                    <div class="as-actions">
                        <button type="button" class="as-btn gray" onclick="document.getElementById('passwordForm').reset();document.getElementById('pwStrengthBar').style.display='none'">
                            <i class="bx bx-reset"></i> Reset
                        </button>
                        <button type="submit" class="as-btn amber" id="btnSavePassword">
                            <i class="bx bx-key"></i> Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Keamanan Lanjutan --}}
        <div class="as-card">
            <div class="as-card-head h-rose">
                <div class="as-card-ico"><i class="bx bx-shield-alt-2"></i></div>
                <div><h6>Keamanan Lanjutan</h6><p>Fitur keamanan tambahan untuk akun Anda</p></div>
            </div>
            <div class="as-card-body">
                <div class="as-2fa-row">
                    <div class="as-2fa-left">
                        <div class="as-2fa-ico"><i class="bx bx-mobile-alt"></i></div>
                        <div>
                            <p class="as-2fa-title">Autentikasi Dua Faktor (2FA)</p>
                            <p class="as-2fa-desc">Tingkatkan keamanan dengan verifikasi dua langkah</p>
                        </div>
                    </div>
                    <label class="as-switch">
                        <input type="checkbox" id="twoFaToggle" {{ $user->two_factor_enabled ? 'checked' : '' }} onchange="toggle2FA(this)"/>
                        <span class="as-track"><span class="as-thumb"></span></span>
                    </label>
                </div>
                <div class="as-info-box blue" id="twoFaInfo" style="{{ $user->two_factor_enabled ? '' : 'display:none' }}">
                    <i class="bx bx-shield-check"></i>
                    <div><strong>2FA Aktif</strong><p>Akun Anda dilindungi verifikasi dua langkah</p></div>
                </div>

                <div class="as-divider"></div>

                <p class="as-sec-title">Tips Keamanan Password</p>
                <div class="as-tips">
                    <div class="as-tip">
                        <span class="as-tip-ico" style="background:#dbeafe;color:#2563eb"><i class="bx bx-lock"></i></span>
                        <span>Gunakan minimal 8 karakter dengan kombinasi huruf, angka, dan simbol</span>
                    </div>
                    <div class="as-tip">
                        <span class="as-tip-ico" style="background:#d1fae5;color:#059669"><i class="bx bx-refresh"></i></span>
                        <span>Ganti password secara berkala, minimal setiap 3 bulan sekali</span>
                    </div>
                    <div class="as-tip">
                        <span class="as-tip-ico" style="background:#fef3c7;color:#d97706"><i class="bx bx-shield"></i></span>
                        <span>Jangan gunakan password yang sama untuk akun lain</span>
                    </div>
                    <div class="as-tip">
                        <span class="as-tip-ico" style="background:#fce7f3;color:#be185d"><i class="bx bx-hide"></i></span>
                        <span>Jangan bagikan password kepada siapapun, termasuk tim IT</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Login + Sesi Aktif --}}
    <div class="as-two-col as-mt">
        <div class="as-card">
            <div class="as-card-head h-purple">
                <div class="as-card-ico"><i class="bx bx-history"></i></div>
                <div><h6>Riwayat Login</h6><p>Aktivitas login terakhir pada akun Anda</p></div>
                <button class="as-refresh-btn" onclick="loadLoginHistory()" title="Refresh"><i class="bx bx-refresh" id="loginHistoryRefreshIcon"></i></button>
            </div>
            <div class="as-card-body">
                <div class="as-loading" id="loginHistoryLoading" style="display:none">
                    <i class="bx bx-loader-alt spin"></i><p>Memuat...</p>
                </div>
                <div id="loginHistoryList">
                    <div class="as-empty"><i class="bx bx-history"></i><p>Belum ada riwayat login</p></div>
                </div>
            </div>
        </div>

        <div class="as-card">
            <div class="as-card-head h-teal">
                <div class="as-card-ico"><i class="bx bx-devices"></i></div>
                <div><h6>Sesi Aktif</h6><p>Perangkat yang sedang login ke akun Anda</p></div>
            </div>
            <div class="as-card-body">
                <div id="sessionsList">
                    <div class="as-list-item">
                        <div class="as-list-ico indigo"><i class="bx bx-desktop"></i></div>
                        <div class="as-list-info">
                            <p class="as-list-title">Browser ini</p>
                            <p class="as-list-sub">Sesi saat ini</p>
                        </div>
                        <span class="as-current-badge"><i class="bx bx-check-circle"></i> Sesi Ini</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity Log --}}
    <div class="as-card as-mt">
        <div class="as-card-head h-indigo">
            <div class="as-card-ico"><i class="bx bx-list-ul"></i></div>
            <div><h6>Log Aktivitas</h6><p>Riwayat semua tindakan pada akun Anda</p></div>
            <button class="as-refresh-btn" onclick="loadActivityLogs()" title="Refresh"><i class="bx bx-refresh" id="activityRefreshIcon"></i></button>
        </div>
        <div class="as-card-body">
            <div class="as-loading" id="activityLoading" style="display:none">
                <i class="bx bx-loader-alt spin"></i><p>Memuat...</p>
            </div>
            <div id="activityList">
                <div class="as-empty"><i class="bx bx-list-ul"></i><p>Belum ada aktivitas</p></div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ TAB: NOTIFIKASI ═══ --}}
<div class="tab-panel" id="panel-notifications">
    <div class="as-two-col">
        <div class="as-card">
            <div class="as-card-head h-teal">
                <div class="as-card-ico"><i class="bx bx-bell"></i></div>
                <div><h6>Notifikasi Email & Push</h6><p>Atur jenis notifikasi yang ingin diterima</p></div>
            </div>
            <div class="as-card-body">
                @php
                    $notifSettings = $user->notification_settings ? json_decode($user->notification_settings, true) : [];
                    $notifications = [
                        ['id'=>'new_ticket','icon'=>'bx-file','title'=>'Tiket Baru','desc'=>'Notifikasi saat ada tiket baru masuk'],
                        ['id'=>'ticket_assigned','icon'=>'bx-user-check','title'=>'Penugasan Tiket','desc'=>'Notifikasi saat tiket ditugaskan ke vendor'],
                        ['id'=>'ticket_resolved','icon'=>'bx-check-circle','title'=>'Tiket Terselesaikan','desc'=>'Notifikasi saat tiket berhasil diselesaikan'],
                        ['id'=>'sla_warning','icon'=>'bx-error','title'=>'Peringatan SLA','desc'=>'Notifikasi saat SLA hampir terlewat'],
                        ['id'=>'new_feedback','icon'=>'bx-star','title'=>'Feedback Baru','desc'=>'Notifikasi saat klien memberikan feedback'],
                    ];
                @endphp
                <form id="notifForm" onsubmit="saveNotifications(event)">
                    @csrf
                    <div class="as-notif-list">
                        @foreach($notifications as $notif)
                        <div class="as-notif-item">
                            <div class="as-notif-ico"><i class="bx {{ $notif['icon'] }}"></i></div>
                            <div class="as-notif-info">
                                <p class="as-notif-title">{{ $notif['title'] }}</p>
                                <p class="as-notif-desc">{{ $notif['desc'] }}</p>
                            </div>
                            <div class="as-notif-toggles">
                                <div class="as-toggle-opt">
                                    <span>Email</span>
                                    <label class="as-switch">
                                        <input type="checkbox" name="notif[{{ $notif['id'] }}][email]" value="1"
                                            {{ isset($notifSettings[$notif['id']]['email']) && $notifSettings[$notif['id']]['email'] ? 'checked' : '' }}/>
                                        <span class="as-track sm"><span class="as-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="as-toggle-opt">
                                    <span>Push</span>
                                    <label class="as-switch">
                                        <input type="checkbox" name="notif[{{ $notif['id'] }}][push]" value="1"
                                            {{ isset($notifSettings[$notif['id']]['push']) && $notifSettings[$notif['id']]['push'] ? 'checked' : '' }}/>
                                        <span class="as-track sm"><span class="as-thumb"></span></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="as-actions">
                        <button type="submit" class="as-btn indigo" id="btnSaveNotif">
                            <i class="bx bx-save"></i> Simpan Preferensi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="as-card">
            <div class="as-card-head h-amber">
                <div class="as-card-ico"><i class="bx bx-bar-chart-alt-2"></i></div>
                <div><h6>Ringkasan Notifikasi</h6><p>Status notifikasi aktif saat ini</p></div>
            </div>
            <div class="as-card-body">
                <div class="as-notif-stats">
                    <div class="as-nstat">
                        <div class="as-nstat-ico" style="background:#dbeafe;color:#2563eb"><i class="bx bx-bell"></i></div>
                        <div><p class="as-nstat-val" id="totalNotifCount">5</p><p class="as-nstat-lbl">Total Kategori</p></div>
                    </div>
                    <div class="as-nstat">
                        <div class="as-nstat-ico" style="background:#d1fae5;color:#059669"><i class="bx bx-check"></i></div>
                        <div><p class="as-nstat-val" id="activeNotifCount">0</p><p class="as-nstat-lbl">Notifikasi Aktif</p></div>
                    </div>
                    <div class="as-nstat">
                        <div class="as-nstat-ico" style="background:#fef3c7;color:#d97706"><i class="bx bx-envelope"></i></div>
                        <div><p class="as-nstat-val" id="emailNotifCount">0</p><p class="as-nstat-lbl">Email Aktif</p></div>
                    </div>
                    <div class="as-nstat">
                        <div class="as-nstat-ico" style="background:#ede9fe;color:#7c3aed"><i class="bx bx-mobile"></i></div>
                        <div><p class="as-nstat-val" id="pushNotifCount">0</p><p class="as-nstat-lbl">Push Aktif</p></div>
                    </div>
                </div>
                <div class="as-divider"></div>
                <p class="as-sec-title">Panduan Notifikasi</p>
                <div class="as-tips">
                    <div class="as-tip"><i class="bx bx-check-circle" style="color:#10b981;font-size:1rem;flex-shrink:0"></i><span>Aktifkan notifikasi email untuk tiket kritis agar tidak terlewat</span></div>
                    <div class="as-tip"><i class="bx bx-check-circle" style="color:#10b981;font-size:1rem;flex-shrink:0"></i><span>Notifikasi SLA membantu memantau performa vendor secara real-time</span></div>
                    <div class="as-tip"><i class="bx bx-check-circle" style="color:#10b981;font-size:1rem;flex-shrink:0"></i><span>Feedback notifikasi membantu evaluasi kepuasan layanan secara berkala</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ TAB: PREFERENSI ═══ --}}
<div class="tab-panel" id="panel-preferences">
    <div class="as-two-col">
        @php
            $prefs = $user->preferences ? json_decode($user->preferences, true) : [];
        @endphp
        <div class="as-card">
            <div class="as-card-head h-purple">
                <div class="as-card-ico"><i class="bx bx-palette"></i></div>
                <div><h6>Tema & Bahasa</h6><p>Sesuaikan tampilan antarmuka</p></div>
            </div>
            <div class="as-card-body">
                <form id="preferencesForm" onsubmit="savePreferences(event)">
                    @csrf
                    <p style="font-size:.78rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin:0 0 .75rem">Tema Tampilan</p>
                    <div class="as-theme-opts" id="themeOpts">
                        @foreach([['id'=>'light','icon'=>'bx-sun','label'=>'Terang'],['id'=>'dark','icon'=>'bx-moon','label'=>'Gelap'],['id'=>'system','icon'=>'bx-desktop','label'=>'Sistem']] as $theme)
                        <label class="as-theme-opt {{ ($prefs['theme'] ?? 'light') === $theme['id'] ? 'active' : '' }}">
                            <input type="radio" name="theme" value="{{ $theme['id'] }}" {{ ($prefs['theme'] ?? 'light') === $theme['id'] ? 'checked' : '' }} onchange="updateThemeOpt(this)"/>
                            <i class="bx {{ $theme['icon'] }}"></i>
                            <span>{{ $theme['label'] }}</span>
                        </label>
                        @endforeach
                    </div>

                    <p style="font-size:.78rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin:1.25rem 0 .75rem">Bahasa Interface</p>
                    <div class="as-sel-wrap">
                        <i class="bx bx-globe as-sel-ico"></i>
                        <select class="as-select" name="language">
                            <option value="id" {{ ($prefs['language'] ?? 'id') === 'id' ? 'selected' : '' }}>🇮🇩 Bahasa Indonesia</option>
                            <option value="en" {{ ($prefs['language'] ?? 'id') === 'en' ? 'selected' : '' }}>🇬🇧 English</option>
                        </select>
                    </div>

                    <p style="font-size:.78rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin:1.25rem 0 .75rem">Zona Waktu</p>
                    <div class="as-sel-wrap">
                        <i class="bx bx-time as-sel-ico"></i>
                        <select class="as-select" name="timezone">
                            <option value="Asia/Jakarta" {{ ($prefs['timezone'] ?? 'Asia/Jakarta') === 'Asia/Jakarta' ? 'selected' : '' }}>WIB — Jakarta (UTC+7)</option>
                            <option value="Asia/Makassar" {{ ($prefs['timezone'] ?? '') === 'Asia/Makassar' ? 'selected' : '' }}>WITA — Makassar (UTC+8)</option>
                            <option value="Asia/Jayapura" {{ ($prefs['timezone'] ?? '') === 'Asia/Jayapura' ? 'selected' : '' }}>WIT — Jayapura (UTC+9)</option>
                        </select>
                    </div>

                    <p style="font-size:.78rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin:1.25rem 0 .75rem">Format Tanggal</p>
                    <div class="as-sel-wrap">
                        <i class="bx bx-calendar as-sel-ico"></i>
                        <select class="as-select" name="dateFormat">
                            <option value="DD/MM/YYYY" {{ ($prefs['dateFormat'] ?? 'DD/MM/YYYY') === 'DD/MM/YYYY' ? 'selected' : '' }}>DD/MM/YYYY</option>
                            <option value="MM/DD/YYYY" {{ ($prefs['dateFormat'] ?? '') === 'MM/DD/YYYY' ? 'selected' : '' }}>MM/DD/YYYY</option>
                            <option value="YYYY-MM-DD" {{ ($prefs['dateFormat'] ?? '') === 'YYYY-MM-DD' ? 'selected' : '' }}>YYYY-MM-DD</option>
                        </select>
                    </div>

                    <div class="as-actions">
                        <button type="submit" class="as-btn indigo" id="btnSavePrefs">
                            <i class="bx bx-save"></i> Simpan Preferensi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="as-card">
            <div class="as-card-head h-teal">
                <div class="as-card-ico"><i class="bx bx-data"></i></div>
                <div><h6>Data & Akun</h6><p>Ekspor data atau hapus akun Anda</p></div>
            </div>
            <div class="as-card-body">
                <p class="as-sec-title">Ekspor Data</p>
                <div class="as-export-box">
                    <div class="as-export-info">
                        <div class="as-export-ico"><i class="bx bx-download"></i></div>
                        <div>
                            <p class="as-export-title">Download Data Anda</p>
                            <p class="as-export-desc">Semua data profil, log, dan pengaturan dalam format JSON (GDPR)</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.settings') }}?export=1" class="as-btn teal" id="btnExport" onclick="exportData(event)">
                        <i class="bx bx-export"></i> Download Data
                    </a>
                </div>

                <div class="as-divider"></div>

                <p class="as-sec-title">Kebijakan Data</p>
                <div class="as-policy-list">
                    <div class="as-policy-row">
                        <div class="as-policy-ico" style="background:#dbeafe;color:#1e40af"><i class="bx bx-lock"></i></div>
                        <div><p class="as-policy-title">Data Aman</p><p class="as-policy-desc">Data Anda dienkripsi dan disimpan dengan aman</p></div>
                    </div>
                    <div class="as-policy-row">
                        <div class="as-policy-ico" style="background:#d1fae5;color:#065f46"><i class="bx bx-shield"></i></div>
                        <div><p class="as-policy-title">Privasi Terjaga</p><p class="as-policy-desc">Data tidak dibagikan kepada pihak ketiga</p></div>
                    </div>
                    <div class="as-policy-row">
                        <div class="as-policy-ico" style="background:#fef3c7;color:#92400e"><i class="bx bx-file"></i></div>
                        <div><p class="as-policy-title">Hak Akses Data</p><p class="as-policy-desc">Anda berhak mengakses dan menghapus data kapan saja</p></div>
                    </div>
                </div>

                <div class="as-divider"></div>

                <p class="as-sec-title danger-txt">⚠️ Zona Berbahaya</p>
                <div class="as-danger-box">
                    <div>
                        <p class="as-danger-title">Hapus Akun Permanen</p>
                        <p class="as-danger-desc">Hapus akun dan semua data secara permanen. Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <button type="button" class="as-btn red" onclick="confirmDeleteAccount()">
                        <i class="bx bx-trash"></i> Hapus Akun
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

</div>{{-- end as-wrap --}}
@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const API_BASE = '/api/admin/settings';

// ── Tab Switching ──
function switchTab(tab, el) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.as-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('panel-' + tab).classList.add('active');
    el.classList.add('active');
    if (tab === 'security') { loadLoginHistory(); loadActivityLogs(); }
}

// ── Toast ──
function showToast(type, msg) {
    const t = document.getElementById('asToast');
    const icon = document.getElementById('asToastIcon');
    const txt = document.getElementById('asToastMsg');
    icon.className = 'bx ' + (type === 'success' ? 'bx-check-circle' : 'bx-error-circle');
    txt.textContent = msg;
    t.className = 'as-toast ' + type + ' show';
    setTimeout(() => t.classList.remove('show'), 4000);
}
function hideToast() { document.getElementById('asToast').classList.remove('show'); }

// ── Set btn loading ──
function setLoading(btnId, loading, originalHtml) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    if (loading) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bx bx-loader-alt spin"></i> Menyimpan...';
    } else {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

// ── Avatar ──
let selectedAvatarFile = null;
function handleAvatarChange(input) {
    const file = input.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) { showToast('error', 'Ukuran foto maksimal 2MB'); return; }
    selectedAvatarFile = file;
    const reader = new FileReader();
    reader.onload = e => {
        const prev = document.getElementById('avatarPreview');
        const initEl = document.getElementById('avatarInitials');
        const heroImg = document.getElementById('heroAvatar');
        const heroTxt = document.getElementById('heroAvatarTxt');
        if (prev) { prev.src = e.target.result; }
        else if (initEl) {
            initEl.outerHTML = '<img src="' + e.target.result + '" id="avatarPreview" alt="Avatar" style="width:100%;height:100%;object-fit:cover"/>';
        }
        if (heroImg) { heroImg.src = e.target.result; }
        else if (heroTxt) {
            heroTxt.outerHTML = '<img src="' + e.target.result + '" class="as-avatar-img" id="heroAvatar" alt="Avatar"/>';
        }
    };
    reader.readAsDataURL(file);
}

function removeAvatar() {
    if (!confirm('Hapus foto profil?')) return;
    fetch(API_BASE + '/avatar', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        if (data.success) {
            showToast('success', 'Foto berhasil dihapus');
            setTimeout(() => location.reload(), 1000);
        } else { showToast('error', data.message || 'Gagal menghapus foto'); }
    }).catch(() => showToast('error', 'Terjadi kesalahan'));
}

// ── Submit Profile ──
function submitProfile(e) {
    e.preventDefault();
    const form = document.getElementById('profileForm');
    const formData = new FormData(form);
    if (selectedAvatarFile) formData.append('avatar', selectedAvatarFile);
    setLoading('btnSaveProfile', true);
    fetch(API_BASE + '/profile', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: formData
    }).then(r => r.json()).then(data => {
        setLoading('btnSaveProfile', false, '<i class="bx bx-save"></i> Simpan Profil');
        if (data.success) {
            showToast('success', data.message || 'Profil berhasil disimpan');
            document.getElementById('heroName').textContent = data.data?.user?.name || '';
            selectedAvatarFile = null;
        } else {
            if (data.errors) {
                Object.values(data.errors).flat().forEach(msg => showToast('error', msg));
            } else { showToast('error', data.message || 'Gagal menyimpan'); }
        }
    }).catch(() => { setLoading('btnSaveProfile', false, '<i class="bx bx-save"></i> Simpan Profil'); showToast('error', 'Terjadi kesalahan'); });
}

function resetProfileForm() {
    document.getElementById('profileForm').reset();
    selectedAvatarFile = null;
}

// ── Password ──
function togglePw(id, btn) {
    const inp = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (inp.type === 'password') { inp.type = 'text'; icon.className = 'bx bx-hide'; }
    else { inp.type = 'password'; icon.className = 'bx bx-show'; }
}

function checkPwStrength(val) {
    const bar = document.getElementById('pwStrengthBar');
    const fill = document.getElementById('pwBarFill');
    const txt = document.getElementById('pwStrText');
    if (!val) { bar.style.display = 'none'; return; }
    bar.style.display = 'flex';
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    if (score <= 1) { fill.className = 'as-bar-fill weak'; txt.className = 'as-str-txt weak'; txt.textContent = 'Lemah'; }
    else if (score <= 2) { fill.className = 'as-bar-fill medium'; txt.className = 'as-str-txt medium'; txt.textContent = 'Sedang'; }
    else { fill.className = 'as-bar-fill strong'; txt.className = 'as-str-txt strong'; txt.textContent = 'Kuat'; }
}

function checkPwMatch() {
    const a = document.getElementById('pwNew').value;
    const b = document.getElementById('pwConfirm').value;
    document.getElementById('pwMismatch').style.display = (b && a !== b) ? 'flex' : 'none';
    document.getElementById('pwMatch').style.display    = (b && a === b) ? 'flex' : 'none';
}

function submitPassword(e) {
    e.preventDefault();
    const a = document.getElementById('pwNew').value;
    const b = document.getElementById('pwConfirm').value;
    if (a !== b) { showToast('error', 'Password tidak cocok'); return; }
    const formData = new FormData(document.getElementById('passwordForm'));
    setLoading('btnSavePassword', true);
    fetch(API_BASE + '/password', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: formData
    }).then(r => r.json()).then(data => {
        setLoading('btnSavePassword', false, '<i class="bx bx-key"></i> Ubah Password');
        if (data.success) {
            showToast('success', data.message || 'Password berhasil diubah');
            document.getElementById('passwordForm').reset();
            document.getElementById('pwStrengthBar').style.display = 'none';
            document.getElementById('pwMismatch').style.display = 'none';
            document.getElementById('pwMatch').style.display = 'none';
        } else {
            if (data.errors) { Object.values(data.errors).flat().forEach(msg => showToast('error', msg)); }
            else { showToast('error', data.message || 'Gagal mengubah password'); }
        }
    }).catch(() => { setLoading('btnSavePassword', false, '<i class="bx bx-key"></i> Ubah Password'); showToast('error', 'Terjadi kesalahan'); });
}

// ── 2FA ──
function toggle2FA(checkbox) {
    const info = document.getElementById('twoFaInfo');
    info.style.display = checkbox.checked ? '' : 'none';
    fetch(API_BASE + '/preferences', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ two_factor_enabled: checkbox.checked })
    }).then(r => r.json()).then(data => {
        showToast(data.success ? 'success' : 'error', data.success ? (checkbox.checked ? '2FA diaktifkan' : '2FA dinonaktifkan') : 'Gagal mengubah 2FA');
    });
}

// ── Login History ──
function loadLoginHistory() {
    const list = document.getElementById('loginHistoryList');
    const loading = document.getElementById('loginHistoryLoading');
    loading.style.display = 'flex'; list.innerHTML = '';
    fetch(API_BASE + '/login-history', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } })
    .then(r => r.json()).then(data => {
        loading.style.display = 'none';
        const items = data.data?.login_history || [];
        document.getElementById('loginHistoryCount').textContent = items.length;
        if (!items.length) { list.innerHTML = '<div class="as-empty"><i class="bx bx-history"></i><p>Belum ada riwayat login</p></div>'; return; }
        list.innerHTML = '<div class="as-list">' + items.map(l => `
            <div class="as-list-item">
                <div class="as-list-ico ${l.success ? 'success' : 'danger'}"><i class="bx bx-desktop"></i></div>
                <div class="as-list-info">
                    <p class="as-list-title">${l.device || 'Unknown'} • ${l.browser || 'Browser'}</p>
                    <p class="as-list-sub">${l.location || '-'} • ${l.ip_address || '-'}</p>
                    <span class="as-list-time">${l.logged_in_at || ''}</span>
                </div>
                <span class="as-status-badge ${l.success ? 'success' : 'danger'}">
                    <i class="bx ${l.success ? 'bx-check' : 'bx-x'}"></i>${l.success ? 'Berhasil' : 'Gagal'}
                </span>
            </div>`).join('') + '</div>';
    }).catch(() => { loading.style.display = 'none'; list.innerHTML = '<div class="as-empty"><i class="bx bx-history"></i><p>Gagal memuat</p></div>'; });
}

// ── Activity Logs ──
function loadActivityLogs() {
    const list = document.getElementById('activityList');
    const loading = document.getElementById('activityLoading');
    loading.style.display = 'flex'; list.innerHTML = '';
    fetch(API_BASE + '/activity-logs', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } })
    .then(r => r.json()).then(data => {
        loading.style.display = 'none';
        const items = data.data?.activity_logs || [];
        document.getElementById('activityCountStat').textContent = items.length;
        document.getElementById('heroActivityCount').textContent = items.length;
        if (!items.length) { list.innerHTML = '<div class="as-empty"><i class="bx bx-list-ul"></i><p>Belum ada aktivitas</p></div>'; return; }
        list.innerHTML = '<div class="as-activity-grid">' + items.map(a => `
            <div class="as-list-item">
                <div class="as-list-ico indigo"><i class="bx bx-user"></i></div>
                <div class="as-list-info">
                    <p class="as-list-title">${a.description}</p>
                    <p class="as-list-sub">${a.ip_address || '-'} • ${a.created_at || ''}</p>
                </div>
            </div>`).join('') + '</div>';
    }).catch(() => { loading.style.display = 'none'; });
}

// ── Notifications ──
function saveNotifications(e) {
    e.preventDefault();
    const formData = new FormData(document.getElementById('notifForm'));
    const settings = {};
    for (const [key, val] of formData.entries()) {
        const match = key.match(/notif\[(.+?)\]\[(.+?)\]/);
        if (match) {
            if (!settings[match[1]]) settings[match[1]] = {};
            settings[match[1]][match[2]] = true;
        }
    }
    setLoading('btnSaveNotif', true);
    fetch(API_BASE + '/notifications', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ settings })
    }).then(r => r.json()).then(data => {
        setLoading('btnSaveNotif', false, '<i class="bx bx-save"></i> Simpan Preferensi');
        showToast(data.success ? 'success' : 'error', data.message || 'Tersimpan');
    }).catch(() => { setLoading('btnSaveNotif', false, '<i class="bx bx-save"></i> Simpan Preferensi'); showToast('error', 'Terjadi kesalahan'); });
}

// ── Preferences ──
function updateThemeOpt(radio) {
    document.querySelectorAll('.as-theme-opt').forEach(el => el.classList.remove('active'));
    radio.closest('.as-theme-opt').classList.add('active');
}

function savePreferences(e) {
    e.preventDefault();
    const formData = new FormData(document.getElementById('preferencesForm'));
    const prefs = Object.fromEntries(formData.entries());
    delete prefs._token;
    setLoading('btnSavePrefs', true);
    fetch(API_BASE + '/preferences', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify(prefs)
    }).then(r => r.json()).then(data => {
        setLoading('btnSavePrefs', false, '<i class="bx bx-save"></i> Simpan Preferensi');
        showToast(data.success ? 'success' : 'error', data.message || 'Tersimpan');
    }).catch(() => { setLoading('btnSavePrefs', false, '<i class="bx bx-save"></i> Simpan Preferensi'); showToast('error', 'Terjadi kesalahan'); });
}

// ── Export ──
function exportData(e) {
    e.preventDefault();
    setLoading('btnExport', true, '<i class="bx bx-export"></i> Download Data');
    fetch(API_BASE + '/export-data', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } })
    .then(r => r.json()).then(data => {
        setLoading('btnExport', false, '<i class="bx bx-export"></i> Download Data');
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a'); a.href = url;
        a.download = 'admin-data-' + Date.now() + '.json'; a.click();
        URL.revokeObjectURL(url);
        showToast('success', 'Data berhasil diunduh');
    }).catch(() => { setLoading('btnExport', false, '<i class="bx bx-export"></i> Download Data'); showToast('error', 'Gagal mengunduh data'); });
}

// ── Delete Account ──
function confirmDeleteAccount() {
    if (!confirm('⚠️ PERINGATAN!\n\nAnda akan menghapus akun ini secara permanen.\nSemua data tidak dapat dipulihkan.\n\nKetik "HAPUS" untuk konfirmasi:')) return;
    const input = prompt('Ketik HAPUS untuk konfirmasi:');
    if (input !== 'HAPUS') { alert('Konfirmasi tidak valid. Akun tidak dihapus.'); return; }
    showToast('error', 'Fitur hapus akun memerlukan konfirmasi lebih lanjut dari sistem.');
}
</script>
@endpush