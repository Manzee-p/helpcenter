@extends('layouts.client')

@section('title', 'Pengaturan Akun')
@section('page_title', 'Pengaturan Akun')
@section('breadcrumb', 'Home / Pengaturan')

@push('styles')
<style>
/* ══════════════════════════════════════════
   CLIENT SETTINGS PAGE
══════════════════════════════════════════ */
.settings-page { display: flex; flex-direction: column; gap: 1.25rem; }

/* ─── TOAST ─── */
.toast-container {
    position: fixed;
    top: 1.25rem;
    right: 1.25rem;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: .65rem;
    pointer-events: none;
}
.toast {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .9rem 1.25rem;
    border-radius: 16px;
    font-size: .9rem;
    font-weight: 700;
    min-width: 280px;
    box-shadow: 0 12px 32px rgba(15,23,42,.12);
    pointer-events: all;
    transform: translateX(120%);
    transition: transform .35s cubic-bezier(.34,1.56,.64,1);
}
.toast.show { transform: translateX(0); }
.toast-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.toast-error   { background: #fef2f2; color: #7f1d1d; border: 1px solid #fecaca; }
.toast i { font-size: 1.2rem; flex-shrink: 0; }

/* ─── TABS ─── */
.settings-tabs-wrap {
    background: #fff;
    border: 1px solid rgba(148,163,184,.12);
    border-radius: 20px;
    padding: .5rem;
    box-shadow: 0 18px 36px rgba(15,23,42,.05);
    display: flex;
    gap: .25rem;
    overflow-x: auto;
}
.tab-btn {
    display: flex; align-items: center; gap: .5rem;
    padding: .75rem 1.35rem;
    background: transparent;
    border: none; border-radius: 14px;
    color: #64748b; font-size: .9rem; font-weight: 700;
    cursor: pointer; transition: all .2s;
    white-space: nowrap; font-family: inherit;
}
.tab-btn:hover { background: #f8fafc; color: #4f46e5; }
.tab-btn.active { background: linear-gradient(135deg, #6366f1, #7c3aed); color: #fff; box-shadow: 0 6px 16px rgba(99,102,241,.25); }
.tab-btn i { font-size: 1.1rem; }

/* ─── CARD ─── */
.section-card {
    background: #fff;
    border: 1px solid rgba(148,163,184,.12);
    border-radius: 24px;
    box-shadow: 0 18px 36px rgba(15,23,42,.05);
    overflow: hidden;
    margin-bottom: 1.25rem;
}
.section-card:last-child { margin-bottom: 0; }
.card-header {
    padding: 1.35rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    display: flex; justify-content: space-between; align-items: flex-start;
}
.card-header h3 { font-size: 1.1rem; font-weight: 800; color: #1f2937; margin: 0 0 .25rem; }
.card-header p  { font-size: .875rem; color: #64748b; margin: 0; }
.card-body { padding: 1.5rem; }

/* ─── AVATAR ─── */
.avatar-section {
    display: flex; gap: 1.5rem; align-items: flex-start;
    margin-bottom: 2rem; padding-bottom: 2rem;
    border-bottom: 1px solid #f0f0f0;
}
.avatar-ring {
    width: 100px; height: 100px;
    border-radius: 50%; overflow: hidden; flex-shrink: 0;
    box-shadow: 0 6px 20px rgba(99,102,241,.2);
    border: 3px solid #e0e7ff;
    position: relative;
}
.avatar-ring img { width: 100%; height: 100%; object-fit: cover; display: block; }
.avatar-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 2.2rem; font-weight: 800;
}
.avatar-info { display: flex; flex-direction: column; gap: .6rem; justify-content: center; }
.avatar-name  { font-size: 1.2rem; font-weight: 800; color: #1f2937; }
.avatar-email { font-size: .9rem; color: #64748b; }
.avatar-btns  { display: flex; gap: .5rem; flex-wrap: wrap; }
.btn-upload-avatar {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .6rem 1.15rem;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: #fff; border: none; border-radius: 12px;
    font-size: .85rem; font-weight: 700; cursor: pointer;
    transition: all .2s; font-family: inherit;
}
.btn-upload-avatar:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,.3); }
.btn-delete-avatar {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .6rem 1.15rem;
    background: #fee2e2; color: #dc2626;
    border: none; border-radius: 12px;
    font-size: .85rem; font-weight: 700; cursor: pointer;
    transition: all .2s; font-family: inherit;
}
.btn-delete-avatar:hover { background: #fecaca; }
.avatar-hint { font-size: .78rem; color: #94a3b8; margin: 0; }

/* ─── FORM ─── */
.settings-form { display: flex; flex-direction: column; gap: 1.25rem; }
.form-row { display: grid; grid-template-columns: repeat(2,1fr); gap: 1.25rem; }
.form-group { display: flex; flex-direction: column; gap: .45rem; }
.form-group label { font-size: .875rem; font-weight: 700; color: #374151; display: flex; align-items: center; gap: .3rem; }
.form-group label i { color: #6366f1; font-size: 1rem; }
.required { color: #dc2626; }
.form-control {
    padding: .8rem 1rem;
    border: 2px solid #e9ecef; border-radius: 12px;
    font-size: .9375rem; color: #334155; font-family: inherit;
    transition: all .2s; background: #fff; width: 100%; box-sizing: border-box;
}
.form-control:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
textarea.form-control { resize: vertical; min-height: 90px; }
select.form-control { cursor: pointer; }
.form-hint { font-size: .78rem; color: #94a3b8; margin: 0; }

/* ─── PASSWORD ─── */
.pwd-wrap { position: relative; }
.pwd-wrap .form-control { padding-right: 3rem; }
.pwd-toggle {
    position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; color: #94a3b8; cursor: pointer;
    font-size: 1.15rem; padding: 0; transition: color .2s;
}
.pwd-toggle:hover { color: #4f46e5; }
.strength-bar-wrap { margin-top: .5rem; }
.strength-track { height: 5px; background: #f1f5f9; border-radius: 999px; overflow: hidden; margin-bottom: .3rem; }
.strength-fill { height: 100%; border-radius: 999px; transition: all .4s; width: 0; }
.strength-fill.weak   { width: 33%; background: #ef4444; }
.strength-fill.medium { width: 66%; background: #f59e0b; }
.strength-fill.strong { width: 100%; background: #10b981; }
.strength-text.weak   { color: #ef4444; font-size: .78rem; font-weight: 700; }
.strength-text.medium { color: #f59e0b; font-size: .78rem; font-weight: 700; }
.strength-text.strong { color: #10b981; font-size: .78rem; font-weight: 700; }
.msg-ok  { color: #10b981; font-size: .8rem; display: flex; align-items: center; gap: .3rem; }
.msg-err { color: #ef4444; font-size: .8rem; display: flex; align-items: center; gap: .3rem; }

/* ─── BUTTONS ─── */
.form-actions {
    display: flex; gap: .75rem; justify-content: flex-end;
    margin-top: .5rem; padding-top: 1rem; border-top: 1px solid #f0f0f0;
}
.btn-secondary {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .8rem 1.5rem; background: #f1f5f9;
    border: none; border-radius: 12px; color: #475569;
    font-size: .9rem; font-weight: 700; cursor: pointer; font-family: inherit; transition: all .2s;
}
.btn-secondary:hover { background: #e2e8f0; }
.btn-primary {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .8rem 1.75rem;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    border: none; border-radius: 12px; color: #fff;
    font-size: .9rem; font-weight: 700; cursor: pointer; font-family: inherit;
    transition: all .2s; box-shadow: 0 4px 12px rgba(99,102,241,.2);
}
.btn-primary:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(99,102,241,.3); }
.btn-primary:disabled { opacity: .6; cursor: not-allowed; }
.btn-loading i { animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── NOTIFICATION ─── */
.notif-list { display: flex; flex-direction: column; gap: 1rem; }
.notif-item {
    display: flex; align-items: center; gap: 1rem;
    padding: 1.1rem 1.25rem; background: #f8fafc;
    border-radius: 16px; border: 1px solid #e9ecef;
}
.notif-icon {
    width: 46px; height: 46px; border-radius: 14px;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.3rem; flex-shrink: 0;
}
.notif-info { flex: 1; }
.notif-info h4 { font-size: .95rem; font-weight: 700; color: #1f2937; margin: 0 0 .2rem; }
.notif-info p  { font-size: .82rem; color: #64748b; margin: 0; }
.notif-toggles { display: flex; gap: 1.25rem; }
.toggle-opt { display: flex; flex-direction: column; align-items: center; gap: .35rem; }
.toggle-opt span { font-size: .72rem; font-weight: 700; color: #64748b; }
.toggle-switch { position: relative; width: 42px; height: 22px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; position: absolute; }
.toggle-switch label {
    position: absolute; cursor: pointer; inset: 0;
    background: #d1d5db; border-radius: 999px; transition: .3s;
}
.toggle-switch label::before {
    content: ''; position: absolute;
    width: 16px; height: 16px; left: 3px; top: 3px;
    background: #fff; border-radius: 50%; transition: .3s;
    box-shadow: 0 1px 4px rgba(0,0,0,.15);
}
.toggle-switch input:checked + label { background: #6366f1; }
.toggle-switch input:checked + label::before { transform: translateX(20px); }

/* ─── LAST LOGIN ─── */
.login-info-card {
    display: flex; gap: 1rem; align-items: flex-start;
    padding: 1.25rem; background: #f8fafc;
    border-radius: 16px; border: 1px solid #e9ecef;
}
.login-info-card i { font-size: 2.2rem; color: #6366f1; flex-shrink: 0; }
.login-info-card h4 { font-size: .95rem; font-weight: 700; color: #1f2937; margin: 0 0 .25rem; }
.login-info-card p  { font-size: .875rem; color: #64748b; margin: 0 0 .25rem; }
.login-device { font-size: .78rem; color: #94a3b8; }

/* ─── THEME ─── */
.theme-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: .75rem; }
.theme-opt {
    display: flex; flex-direction: column; align-items: center; gap: .6rem;
    padding: 1.25rem 1rem; background: #f8fafc;
    border: 2px solid #e9ecef; border-radius: 16px; cursor: pointer; transition: all .2s;
}
.theme-opt:hover { border-color: #a5b4fc; background: #eef2ff; }
.theme-opt.active { border-color: #6366f1; background: rgba(99,102,241,.08); }
.theme-opt i { font-size: 2rem; color: #64748b; }
.theme-opt.active i { color: #6366f1; }
.theme-opt span { font-size: .875rem; font-weight: 700; color: #475569; }
.pref-group { display: flex; flex-direction: column; gap: .6rem; margin-bottom: 1.25rem; }
.pref-group label { font-size: .875rem; font-weight: 700; color: #374151; display: flex; align-items: center; gap: .3rem; }
.pref-group label i { color: #6366f1; }

/* ─── FAQ ─── */
.faq-list { display: flex; flex-direction: column; gap: .75rem; }
.faq-item { border: 1px solid #e9ecef; border-radius: 16px; overflow: hidden; }
.faq-q {
    width: 100%; display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 1.25rem; background: #fff; border: none; cursor: pointer;
    font-size: .95rem; font-weight: 700; color: #1f2937; text-align: left;
    transition: background .2s; font-family: inherit; gap: 1rem;
}
.faq-q:hover, .faq-q.open { background: #f8fafc; color: #4f46e5; }
.faq-q i { font-size: 1.2rem; flex-shrink: 0; transition: transform .25s; }
.faq-q.open i { transform: rotate(180deg); }
.faq-a { display: none; padding: .75rem 1.25rem 1.25rem; background: #f8fafc; }
.faq-a p { margin: 0; font-size: .9rem; color: #64748b; line-height: 1.65; }
.faq-item.open .faq-a { display: block; }

/* ─── RESPONSIVE ─── */
@media (max-width: 767px) {
    .form-row { grid-template-columns: 1fr; }
    .avatar-section { flex-direction: column; align-items: center; text-align: center; }
    .form-actions { flex-direction: column-reverse; }
    .btn-primary, .btn-secondary { width: 100%; justify-content: center; }
    .theme-grid { grid-template-columns: repeat(2,1fr); }
    .notif-item { flex-wrap: wrap; }
    .tab-btn { padding: .65rem .9rem; font-size: .82rem; }
}
</style>
@endpush

@section('content')
{{-- Toast container --}}
<div class="toast-container" id="toastContainer"></div>

<div class="settings-page">

    {{-- TABS --}}
    <div class="settings-tabs-wrap">
        <button class="tab-btn active" onclick="switchTab('profile', this)">
            <i class='bx bx-user'></i> Profil
        </button>
        <button class="tab-btn" onclick="switchTab('security', this)">
            <i class='bx bx-lock-alt'></i> Keamanan
        </button>
        <button class="tab-btn" onclick="switchTab('notifications', this)">
            <i class='bx bx-bell'></i> Notifikasi
        </button>
        <button class="tab-btn" onclick="switchTab('preferences', this)">
            <i class='bx bx-palette'></i> Preferensi
        </button>
        <button class="tab-btn" onclick="switchTab('help', this)">
            <i class='bx bx-help-circle'></i> Bantuan
        </button>
    </div>

    {{-- ══════════════════ TAB: PROFIL ══════════════════ --}}
    <div id="tab-profile" class="tab-content">
        <div class="section-card">
            <div class="card-header">
                <div>
                    <h3>Informasi Profil</h3>
                    <p>Update informasi dan foto profil Anda</p>
                </div>
            </div>
            <div class="card-body">

                {{-- Avatar --}}
                <div class="avatar-section">
                    <div class="avatar-ring" id="avatarRing">
                        @if(Auth::user()->avatar)
                            <img id="avatarPreview" src="{{ Storage::url(Auth::user()->avatar) }}" alt="Avatar">
                        @else
                            <div class="avatar-placeholder" id="avatarPlaceholder">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="avatar-info">
                        <div class="avatar-name" id="displayName">{{ Auth::user()->name }}</div>
                        <div class="avatar-email">{{ Auth::user()->email }}</div>
                        <div class="avatar-btns">
                            <label class="btn-upload-avatar">
                                <i class='bx bx-upload'></i> Upload Foto
                                <input type="file" id="avatarFileInput" accept="image/jpeg,image/png,image/jpg,image/gif" hidden onchange="previewAndUploadAvatar(this)">
                            </label>
                            @if(Auth::user()->avatar)
                            <button class="btn-delete-avatar" onclick="deleteAvatar(this)" id="btnDeleteAvatar">
                                <i class='bx bx-trash'></i> Hapus
                            </button>
                            @endif
                        </div>
                        <p class="avatar-hint">JPG, PNG atau GIF. Maks 2MB</p>
                    </div>
                </div>

                {{-- Profile Form --}}
                <form class="settings-form" id="profileForm" onsubmit="submitProfile(event)">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class='bx bx-user'></i> Nama Lengkap <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ Auth::user()->name }}" required placeholder="Masukkan nama lengkap">
                        </div>
                        <div class="form-group">
                            <label><i class='bx bx-envelope'></i> Email <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ Auth::user()->email }}" required placeholder="email@example.com">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class='bx bx-phone'></i> Nomor Telepon</label>
                            <input type="text" name="phone" class="form-control" value="{{ Auth::user()->phone ?? '' }}" placeholder="+62xxx">
                        </div>
                        <div class="form-group">
                            <label><i class='bx bx-user-circle'></i> Jenis Kelamin</label>
                            <select name="gender" class="form-control">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="male"   {{ (Auth::user()->gender ?? '') === 'male'   ? 'selected' : '' }}>Laki-laki</option>
                                <option value="female" {{ (Auth::user()->gender ?? '') === 'female' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class='bx bx-map'></i> Alamat Lengkap</label>
                        <textarea name="address" class="form-control" placeholder="Jalan, Nomor, RT/RW, Kelurahan...">{{ Auth::user()->address ?? '' }}</textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class='bx bx-buildings'></i> Kota / Kabupaten</label>
                            <input type="text" name="city" class="form-control" value="{{ Auth::user()->city ?? '' }}" placeholder="Contoh: Bandung">
                        </div>
                        <div class="form-group">
                            <label><i class='bx bx-map-pin'></i> Provinsi</label>
                            <select name="province" class="form-control">
                                <option value="">Pilih Provinsi</option>
                                @foreach(['Aceh','Sumatera Utara','Sumatera Barat','Riau','Jambi','Sumatera Selatan','Bengkulu','Lampung','Kepulauan Bangka Belitung','Kepulauan Riau','DKI Jakarta','Jawa Barat','Jawa Tengah','DI Yogyakarta','Jawa Timur','Banten','Bali','Nusa Tenggara Barat','Nusa Tenggara Timur','Kalimantan Barat','Kalimantan Tengah','Kalimantan Selatan','Kalimantan Timur','Kalimantan Utara','Sulawesi Utara','Sulawesi Tengah','Sulawesi Selatan','Sulawesi Tenggara','Gorontalo','Sulawesi Barat','Maluku','Maluku Utara','Papua Barat','Papua'] as $prov)
                                    <option value="{{ $prov }}" {{ (Auth::user()->province ?? '') === $prov ? 'selected' : '' }}>{{ $prov }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class='bx bx-mail-send'></i> Kode Pos</label>
                            <input type="text" name="postal_code" class="form-control" value="{{ Auth::user()->postal_code ?? '' }}" placeholder="40xxx" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label><i class='bx bx-calendar'></i> Tanggal Lahir</label>
                            <input type="date" name="birth_date" class="form-control" value="{{ Auth::user()->birth_date ?? '' }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class='bx bx-id-card'></i> NIK</label>
                            <input type="text" name="nik" class="form-control" value="{{ Auth::user()->nik ?? '' }}" placeholder="16 digit NIK" maxlength="16">
                            <p class="form-hint">Data dilindungi dan tidak ditampilkan publik.</p>
                        </div>
                        <div class="form-group">
                            <label><i class='bx bx-phone-call'></i> Kontak Darurat</label>
                            <input type="text" name="emergency_contact" class="form-control" value="{{ Auth::user()->emergency_contact ?? '' }}" placeholder="+62xxx">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class='bx bx-user-pin'></i> Nama Kontak Darurat</label>
                            <input type="text" name="emergency_contact_name" class="form-control" value="{{ Auth::user()->emergency_contact_name ?? '' }}" placeholder="Nama lengkap">
                        </div>
                        <div class="form-group">
                            <label><i class='bx bx-group'></i> Hubungan</label>
                            <input type="text" name="emergency_contact_relation" class="form-control" value="{{ Auth::user()->emergency_contact_relation ?? '' }}" placeholder="Contoh: Orang tua, Pasangan">
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class='bx bx-notepad'></i> Bio / Catatan Tambahan</label>
                        <textarea name="bio" class="form-control" rows="3" placeholder="Ceritakan tentang diri Anda...">{{ Auth::user()->bio ?? '' }}</textarea>
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn-secondary"><i class='bx bx-reset'></i> Reset</button>
                        <button type="submit" class="btn-primary" id="profileBtn">
                            <i class='bx bx-save'></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════ TAB: KEAMANAN ══════════════════ --}}
    <div id="tab-security" class="tab-content" style="display:none">
        <div class="section-card">
            <div class="card-header">
                <div>
                    <h3>Ubah Password</h3>
                    <p>Pastikan akun Anda menggunakan password yang kuat</p>
                </div>
            </div>
            <div class="card-body">
                <form class="settings-form" id="passwordForm" onsubmit="submitPassword(event)">
                    @csrf

                    <div class="form-group">
                        <label><i class='bx bx-lock'></i> Password Saat Ini <span class="required">*</span></label>
                        <div class="pwd-wrap">
                            <input type="password" name="current_password" id="currentPwd" class="form-control" placeholder="Masukkan password saat ini" required>
                            <button type="button" class="pwd-toggle" onclick="togglePwd('currentPwd', this)"><i class='bx bx-show'></i></button>
                        </div>
                        <div id="currentPwdError"></div>
                    </div>

                    <div class="form-group">
                        <label><i class='bx bx-key'></i> Password Baru <span class="required">*</span></label>
                        <div class="pwd-wrap">
                            {{-- name HARUS new_password sesuai controller --}}
                            <input type="password" name="new_password" id="newPwd" class="form-control" placeholder="Min. 8 karakter" required minlength="8" oninput="checkStrength(this.value); checkMatch()">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('newPwd', this)"><i class='bx bx-show'></i></button>
                        </div>
                        <div class="strength-bar-wrap" id="strengthWrap" style="display:none">
                            <div class="strength-track"><div class="strength-fill" id="strengthFill"></div></div>
                            <span class="strength-text" id="strengthText"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class='bx bx-check-shield'></i> Konfirmasi Password Baru <span class="required">*</span></label>
                        <div class="pwd-wrap">
                            {{-- name HARUS new_password_confirmation sesuai controller --}}
                            <input type="password" name="new_password_confirmation" id="confirmPwd" class="form-control" placeholder="Konfirmasi password baru" required oninput="checkMatch()">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('confirmPwd', this)"><i class='bx bx-show'></i></button>
                        </div>
                        <div id="matchMsg"></div>
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn-secondary" onclick="resetPwdUI()"><i class='bx bx-reset'></i> Reset</button>
                        <button type="submit" class="btn-primary" id="pwdBtn" disabled>
                            <i class='bx bx-key'></i> Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Last Login --}}
        <div class="section-card">
            <div class="card-header">
                <div>
                    <h3>Aktivitas Login Terakhir</h3>
                    <p>Monitor aktivitas login ke akun Anda</p>
                </div>
            </div>
            <div class="card-body">
                @if($lastLogin)
                <div class="login-info-card">
                    <i class='bx bx-time-five'></i>
                    <div>
                        <h4>Login Terakhir</h4>
                        <p>{{ \Carbon\Carbon::parse($lastLogin->logged_in_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</p>
                        <span class="login-device">
                            {{ $lastLogin->device ?? 'Desktop' }} &bull;
                            {{ $lastLogin->browser ?? 'Unknown' }} &bull;
                            {{ $lastLogin->ip_address ?? '-' }}
                        </span>
                    </div>
                </div>
                @else
                <div style="text-align:center;padding:2rem;color:#94a3b8;">
                    <i class='bx bx-info-circle' style="font-size:2.5rem;display:block;margin-bottom:.5rem;"></i>
                    Tidak ada data login tersedia.
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════════════ TAB: NOTIFIKASI ══════════════════ --}}
    <div id="tab-notifications" class="tab-content" style="display:none">
        <div class="section-card">
            <div class="card-header">
                <div>
                    <h3>Preferensi Notifikasi</h3>
                    <p>Pilih notifikasi yang ingin Anda terima</p>
                </div>
            </div>
            <div class="card-body">
                @php
                $notifs = [
                    ['key'=>'ticket_created',  'icon'=>'bx-file-plus',    'title'=>'Tiket Dibuat',        'desc'=>'Notifikasi saat tiket baru berhasil dibuat'],
                    ['key'=>'ticket_assigned', 'icon'=>'bx-user-check',   'title'=>'Tiket Ditugaskan',    'desc'=>'Notifikasi saat tiket ditugaskan ke vendor'],
                    ['key'=>'ticket_updated',  'icon'=>'bx-refresh',      'title'=>'Update Status Tiket', 'desc'=>'Notifikasi saat status tiket berubah'],
                    ['key'=>'ticket_resolved', 'icon'=>'bx-check-circle', 'title'=>'Tiket Selesai',       'desc'=>'Notifikasi saat tiket diselesaikan'],
                    ['key'=>'feedback_remind', 'icon'=>'bx-star',         'title'=>'Pengingat Rating',    'desc'=>'Pengingat untuk memberikan rating pada tiket selesai'],
                ];
                @endphp
                <div class="notif-list">
                    @foreach($notifs as $notif)
                    <div class="notif-item">
                        <div class="notif-icon"><i class='bx {{ $notif['icon'] }}'></i></div>
                        <div class="notif-info">
                            <h4>{{ $notif['title'] }}</h4>
                            <p>{{ $notif['desc'] }}</p>
                        </div>
                        <div class="notif-toggles">
                            <div class="toggle-opt">
                                <span>Email</span>
                                <div class="toggle-switch">
                                    <input type="checkbox" id="notif-{{ $notif['key'] }}-email" checked>
                                    <label for="notif-{{ $notif['key'] }}-email"></label>
                                </div>
                            </div>
                            <div class="toggle-opt">
                                <span>Push</span>
                                <div class="toggle-switch">
                                    <input type="checkbox" id="notif-{{ $notif['key'] }}-push" checked>
                                    <label for="notif-{{ $notif['key'] }}-push"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="form-actions" style="border-top:1px solid #f0f0f0;padding-top:1rem;margin-top:1rem;">
                    <button class="btn-primary" onclick="submitNotifications(this)">
                        <i class='bx bx-save'></i> Simpan Preferensi
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════ TAB: PREFERENSI ══════════════════ --}}
    <div id="tab-preferences" class="tab-content" style="display:none">
        <div class="section-card">
            <div class="card-header">
                <div>
                    <h3>Preferensi Aplikasi</h3>
                    <p>Sesuaikan tampilan dan pengalaman aplikasi Anda</p>
                </div>
            </div>
            <div class="card-body">
                <div class="pref-group">
                    <label><i class='bx bx-palette'></i> Tema</label>
                    <div class="theme-grid">
                        @foreach([['id'=>'light','icon'=>'bx-sun','label'=>'Terang'],['id'=>'dark','icon'=>'bx-moon','label'=>'Gelap'],['id'=>'system','icon'=>'bx-desktop','label'=>'Sistem']] as $theme)
                        <div class="theme-opt {{ 'light' === $theme['id'] ? 'active' : '' }}" onclick="selectTheme('{{ $theme['id'] }}', this)">
                            <i class='bx {{ $theme['icon'] }}'></i>
                            <span>{{ $theme['label'] }}</span>
                        </div>
                        @endforeach
                    </div>
                    <input type="hidden" id="selectedTheme" value="light">
                </div>
                <div class="form-row" style="margin-top:1.25rem;">
                    <div class="pref-group">
                        <label><i class='bx bx-globe'></i> Bahasa</label>
                        <select id="prefLang" class="form-control">
                            <option value="id" selected>🇮🇩 Bahasa Indonesia</option>
                            <option value="en">🇬🇧 English</option>
                        </select>
                    </div>
                    <div class="pref-group">
                        <label><i class='bx bx-time'></i> Zona Waktu</label>
                        <select id="prefTz" class="form-control">
                            <option value="Asia/Jakarta" selected>WIB — Jakarta (GMT+7)</option>
                            <option value="Asia/Makassar">WITA — Makassar (GMT+8)</option>
                            <option value="Asia/Jayapura">WIT — Jayapura (GMT+9)</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions" style="border-top:1px solid #f0f0f0;padding-top:1rem;margin-top:1rem;">
                    <button class="btn-primary" onclick="submitPreferences(this)">
                        <i class='bx bx-save'></i> Simpan Preferensi
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════ TAB: BANTUAN / FAQ ══════════════════ --}}
    <div id="tab-help" class="tab-content" style="display:none">
        <div class="section-card">
            <div class="card-header">
                <div>
                    <h3>Pertanyaan Umum (FAQ)</h3>
                    <p>Jawaban untuk pertanyaan yang sering diajukan</p>
                </div>
            </div>
            <div class="card-body">
                <div class="faq-list">
                    @php
                    $faqs = [
                        ['q'=>'Bagaimana cara membuat tiket baru?',
                         'a'=>'Klik tombol "Buat Tiket" di dashboard atau halaman Laporan Saya, lalu isi formulir dengan judul, kategori, dan deskripsi masalah. Tiket akan langsung masuk ke sistem kami.'],
                        ['q'=>'Berapa lama waktu penanganan tiket?',
                         'a'=>'Waktu penanganan bervariasi tergantung tingkat urgensi. Tiket kritis biasanya ditangani dalam 1×24 jam, sedangkan tiket biasa 2–5 hari kerja.'],
                        ['q'=>'Bagaimana cara memberikan rating vendor?',
                         'a'=>'Setelah tiket berstatus "Selesai" atau "Ditutup", buka halaman Belum Dirating dan klik tombol "Beri Rating" pada tiket yang sesuai.'],
                        ['q'=>'Apakah saya bisa melampirkan file ke tiket?',
                         'a'=>'Ya, saat membuat tiket Anda bisa melampirkan file berupa gambar (JPG, PNG), PDF, atau dokumen Word dengan ukuran maksimal 5MB per file.'],
                        ['q'=>'Bagaimana cara melihat riwayat tiket?',
                         'a'=>'Buka menu "Riwayat" di sidebar untuk melihat seluruh tiket yang pernah Anda buat beserta detailnya.'],
                        ['q'=>'Apa yang dimaksud dengan status "Menunggu Respons"?',
                         'a'=>'Status ini berarti vendor sudah memberikan respons dan menunggu konfirmasi atau informasi tambahan dari Anda. Segera cek detail tiket dan balas jika diperlukan.'],
                    ];
                    @endphp
                    @foreach($faqs as $i => $faq)
                    <div class="faq-item" id="faq-{{ $i }}">
                        <button class="faq-q" onclick="toggleFAQ({{ $i }})">
                            <span>{{ $faq['q'] }}</span>
                            <i class='bx bx-chevron-down'></i>
                        </button>
                        <div class="faq-a"><p>{{ $faq['a'] }}</p></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
/* ════════════════════════════════════════
   HELPER: Toast notification
════════════════════════════════════════ */
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<i class='bx ${type === 'success' ? 'bx-check-circle' : 'bx-error-circle'}'></i><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 50);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

/* ════════════════════════════════════════
   HELPER: Generic fetch (AJAX)
════════════════════════════════════════ */
async function ajaxPost(url, body, isFormData = false) {
    const headers = { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' };
    if (!isFormData) headers['Content-Type'] = 'application/json';
    const resp = await fetch(url, {
        method: 'POST',
        headers,
        body: isFormData ? body : JSON.stringify(body),
    });
    return resp.json();
}

/* ════════════════════════════════════════
   TABS
════════════════════════════════════════ */
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tab).style.display = '';
    btn.classList.add('active');
}

/* ════════════════════════════════════════
   AVATAR — preview + AJAX upload
════════════════════════════════════════ */
function previewAndUploadAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (file.size > 2 * 1024 * 1024) { showToast('File terlalu besar, maks 2MB', 'error'); return; }

    // Preview immediately
    const reader = new FileReader();
    reader.onload = e => {
        const ring = document.getElementById('avatarRing');
        ring.innerHTML = `<img id="avatarPreview" src="${e.target.result}" alt="Avatar">`;
    };
    reader.readAsDataURL(file);

    // Upload via profile endpoint (FormData)
    const fd = new FormData(document.getElementById('profileForm'));
    fd.append('avatar', file);
    uploadAvatarOnly(fd);
}

async function uploadAvatarOnly(fd) {
    try {
        const data = await ajaxPost('{{ route("client.settings.profile") }}', fd, true);
        if (data.success) {
            showToast('Foto profil diperbarui');
            if (data.data?.user?.avatar_url) {
                document.getElementById('avatarRing').innerHTML = `<img id="avatarPreview" src="${data.data.user.avatar_url}" alt="Avatar">`;
            }
        } else {
            showToast(data.message || 'Gagal upload foto', 'error');
        }
    } catch (e) {
        showToast('Terjadi kesalahan', 'error');
    }
}

async function deleteAvatar(btn) {
    if (!confirm('Hapus foto profil?')) return;
    btn.disabled = true;
    try {
        const data = await ajaxPost('{{ route("client.settings.avatar.delete") }}', { _method: 'DELETE' });
        if (data.success) {
            showToast('Avatar dihapus');
            const initials = '{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}';
            document.getElementById('avatarRing').innerHTML = `<div class="avatar-placeholder" id="avatarPlaceholder">${initials}</div>`;
            btn.style.display = 'none';
        } else {
            showToast(data.message || 'Gagal menghapus', 'error');
            btn.disabled = false;
        }
    } catch (e) {
        showToast('Terjadi kesalahan', 'error');
        btn.disabled = false;
    }
}

/* ════════════════════════════════════════
   PROFILE FORM — AJAX submit
════════════════════════════════════════ */
async function submitProfile(e) {
    e.preventDefault();
    const btn = document.getElementById('profileBtn');
    setLoading(btn, true, 'Menyimpan...');

    try {
        const fd = new FormData(e.target);
        // Use _method trick for FormData
        fd.append('_method', 'POST');

        const data = await ajaxPost('{{ route("client.settings.profile") }}', fd, true);

        if (data.success) {
            showToast(data.message || 'Profil berhasil diperbarui');
            if (data.data?.user?.name) {
                document.getElementById('displayName').textContent = data.data.user.name;
            }
            if (data.data?.user?.avatar_url) {
                document.getElementById('avatarRing').innerHTML = `<img id="avatarPreview" src="${data.data.user.avatar_url}" alt="Avatar">`;
            }
        } else {
            const firstErr = data.errors ? Object.values(data.errors)[0]?.[0] : (data.message || 'Terjadi kesalahan');
            showToast(firstErr, 'error');
        }
    } catch (err) {
        showToast('Gagal menghubungi server', 'error');
    } finally {
        setLoading(btn, false, '<i class=\'bx bx-save\'></i> Simpan Perubahan');
    }
}

/* ════════════════════════════════════════
   PASSWORD FORM — AJAX submit
════════════════════════════════════════ */
async function submitPassword(e) {
    e.preventDefault();
    const np = document.getElementById('newPwd').value;
    const cp = document.getElementById('confirmPwd').value;
    if (np !== cp) { showToast('Password tidak cocok', 'error'); return; }

    const btn = document.getElementById('pwdBtn');
    setLoading(btn, true, 'Mengubah...');

    try {
        const body = {
            current_password:          document.getElementById('currentPwd').value,
            new_password:              np,
            new_password_confirmation: cp,
        };
        const data = await ajaxPost('{{ route("client.settings.password") }}', body);

        if (data.success) {
            showToast(data.message || 'Password berhasil diubah');
            e.target.reset();
            resetPwdUI();
        } else {
            const msg = data.errors?.current_password?.[0] || data.message || 'Gagal mengubah password';
            showToast(msg, 'error');
        }
    } catch (err) {
        showToast('Gagal menghubungi server', 'error');
    } finally {
        setLoading(btn, false, '<i class=\'bx bx-key\'></i> Ubah Password');
    }
}

/* ════════════════════════════════════════
   NOTIFICATIONS — AJAX
════════════════════════════════════════ */
async function submitNotifications(btn) {
    setLoading(btn, true, 'Menyimpan...');
    const settings = {};
    @foreach($notifs as $notif)
    settings['{{ $notif['key'] }}'] = {
        email: document.getElementById('notif-{{ $notif['key'] }}-email')?.checked ?? true,
        push:  document.getElementById('notif-{{ $notif['key'] }}-push')?.checked  ?? true,
    };
    @endforeach

    try {
        const data = await ajaxPost('{{ route("client.settings.notifications") }}', { settings });
        showToast(data.message || 'Preferensi disimpan', data.success ? 'success' : 'error');
    } catch { showToast('Gagal menyimpan', 'error'); }
    finally { setLoading(btn, false, '<i class=\'bx bx-save\'></i> Simpan Preferensi'); }
}

/* ════════════════════════════════════════
   PREFERENCES — AJAX
════════════════════════════════════════ */
function selectTheme(id, el) {
    document.querySelectorAll('.theme-opt').forEach(o => o.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('selectedTheme').value = id;
}

async function submitPreferences(btn) {
    setLoading(btn, true, 'Menyimpan...');
    const body = {
        theme:    document.getElementById('selectedTheme').value,
        language: document.getElementById('prefLang').value,
        timezone: document.getElementById('prefTz').value,
    };
    try {
        const data = await ajaxPost('{{ route("client.settings.preferences") }}', body);
        showToast(data.message || 'Preferensi disimpan', data.success ? 'success' : 'error');
    } catch { showToast('Gagal menyimpan', 'error'); }
    finally { setLoading(btn, false, '<i class=\'bx bx-save\'></i> Simpan Preferensi'); }
}

/* ════════════════════════════════════════
   PASSWORD UI helpers
════════════════════════════════════════ */
function togglePwd(id, btn) {
    const inp = document.getElementById(id);
    const icon = btn.querySelector('i');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'text' ? 'bx bx-hide' : 'bx bx-show';
}

function checkStrength(val) {
    const wrap = document.getElementById('strengthWrap');
    const fill = document.getElementById('strengthFill');
    const text = document.getElementById('strengthText');
    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = '';
    let sc = 0;
    if (val.length >= 8)         sc++;
    if (/[A-Z]/.test(val))       sc++;
    if (/[0-9]/.test(val))       sc++;
    if (/[^A-Za-z0-9]/.test(val)) sc++;
    const lvl = sc <= 1 ? 'weak' : sc <= 2 ? 'weak' : sc === 3 ? 'medium' : 'strong';
    const lbl = { weak: 'Lemah', medium: 'Sedang', strong: 'Kuat' };
    fill.className = 'strength-fill ' + lvl;
    text.className = 'strength-text ' + lvl;
    text.textContent = lbl[lvl];
}

function checkMatch() {
    const np = document.getElementById('newPwd').value;
    const cp = document.getElementById('confirmPwd').value;
    const msg = document.getElementById('matchMsg');
    const btn = document.getElementById('pwdBtn');
    if (!cp) { msg.innerHTML = ''; return; }
    if (np === cp) {
        msg.innerHTML = '<span class="msg-ok"><i class="bx bx-check-circle"></i> Password cocok</span>';
        btn.disabled = false;
    } else {
        msg.innerHTML = '<span class="msg-err"><i class="bx bx-error-circle"></i> Password tidak cocok</span>';
        btn.disabled = true;
    }
}

function resetPwdUI() {
    document.getElementById('matchMsg').innerHTML = '';
    document.getElementById('strengthWrap').style.display = 'none';
    document.getElementById('pwdBtn').disabled = true;
}

/* ════════════════════════════════════════
   FAQ toggle
════════════════════════════════════════ */
function toggleFAQ(i) {
    const item = document.getElementById('faq-' + i);
    const btn  = item.querySelector('.faq-q');
    const isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item').forEach(el => { el.classList.remove('open'); el.querySelector('.faq-q').classList.remove('open'); });
    if (!isOpen) { item.classList.add('open'); btn.classList.add('open'); }
}

/* ════════════════════════════════════════
   Loading state helper
════════════════════════════════════════ */
function setLoading(btn, loading, label) {
    btn.disabled = loading;
    btn.innerHTML = loading ? `<i class='bx bx-loader-alt bx-spin'></i> ${label}` : label;
}

/* Init: expose notifs to inline handlers */
const notifs = @json($notifs ?? []);
</script>
@endpush