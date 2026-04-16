@extends('layouts.client')

@section('title', 'Pengaturan Akun')
@section('page_title', 'Pengaturan Akun')
@section('breadcrumb', 'Home / Pengaturan')

@section('content')
@php
    $user = Auth::user();
    $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('');
    $avatarUrl = $user->avatar ? Storage::url($user->avatar) : null;
@endphp

{{-- Toast --}}
<div class="cs-toast" id="csToast">
    <i class="bx" id="csToastIcon"></i>
    <span id="csToastMsg"></span>
    <button class="cs-toast-close" onclick="hideToast()"><i class="bx bx-x"></i></button>
</div>

<div class="cs-wrap">

    {{-- ══ HERO ══ --}}
    <section class="cs-hero">
        <div class="cs-hero-left">
            <div class="cs-avatar-area">
                <div class="cs-avatar-ring">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" class="cs-avatar-img" id="heroAvatar" alt="Avatar"/>
                    @else
                        <div class="cs-avatar-txt" id="heroAvatarTxt">{{ $initials }}</div>
                    @endif
                    <span class="cs-avatar-online"></span>
                </div>
                <button class="cs-avatar-upload-btn" onclick="document.getElementById('avatarInput').click()" title="Ganti Foto">
                    <i class="bx bx-camera"></i>
                </button>
            </div>
            <div class="cs-hero-info">
                <div class="cs-hero-name" id="heroName">{{ $user->name }}</div>
                <div class="cs-hero-email">{{ $user->email }}</div>
                <div class="cs-hero-badges">
                    <span class="cs-badge indigo"><i class="bx bx-user-circle"></i> Client</span>
                    <span class="cs-badge green"><i class="bx bx-radio-circle-marked"></i> Aktif</span>
                </div>
            </div>
        </div>
        <div class="cs-hero-right">
            <div class="cs-hero-stats">
                <div class="cs-hstat">
                    <span class="cs-hstat-val">Baru saja</span>
                    <span class="cs-hstat-lbl">Login Terakhir</span>
                </div>
                <div class="cs-hstat-div"></div>
                <div class="cs-hstat">
                    <span class="cs-hstat-val">1</span>
                    <span class="cs-hstat-lbl">Sesi Aktif</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ══ TABS ══ --}}
    <div class="cs-tabs">
        <button class="cs-tab active" onclick="switchTab('profile', this)">
            <i class="bx bx-user"></i><span>Profil</span>
        </button>
        <button class="cs-tab" onclick="switchTab('security', this)">
            <i class="bx bx-lock-alt"></i><span>Keamanan</span>
        </button>
        <button class="cs-tab" onclick="switchTab('help', this)">
            <i class="bx bx-help-circle"></i><span>Bantuan</span>
        </button>
    </div>

    {{-- ══ TAB: PROFIL ══ --}}
    <div class="tab-panel active" id="panel-profile">
        <div class="cs-two-col">

            {{-- Form Profil --}}
            <div class="cs-card">
                <div class="cs-card-head h-indigo">
                    <div class="cs-card-ico"><i class="bx bx-user"></i></div>
                    <div><h6>Informasi Profil</h6><p>Update informasi dan foto profil Anda</p></div>
                </div>
                <div class="cs-card-body">

                    {{-- Avatar --}}
                    <div class="cs-avatar-section">
                        <div class="cs-avatar-lg">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" id="avatarPreview" alt="Avatar"/>
                            @else
                                <div class="cs-avatar-lg-txt" id="avatarInitials">{{ $initials }}</div>
                            @endif
                        </div>
                        <div class="cs-avatar-actions">
                            <button type="button" class="cs-btn indigo-outline" onclick="document.getElementById('avatarInput').click()">
                                <i class="bx bx-upload"></i> Upload Foto
                            </button>
                            @if($avatarUrl)
                            <form method="POST" action="{{ route('client.settings.avatar.delete') }}" style="margin:0">
                                @csrf
                                <button type="submit" class="cs-btn red-outline" onclick="return confirm('Hapus foto profil?')">
                                    <i class="bx bx-trash"></i> Hapus Foto
                                </button>
                            </form>
                            @endif
                            <p class="cs-avatar-hint"><i class="bx bx-info-circle"></i> JPG, PNG atau GIF. Maks 2MB</p>
                        </div>
                        <input type="file" id="avatarInput" accept="image/*" style="display:none" onchange="handleAvatarChange(this)"/>
                    </div>

                    <form id="profileForm" onsubmit="submitProfile(event)">
                        @csrf

                        <p class="cs-section-lbl"><i class="bx bx-id-card"></i> Data Diri</p>
                        <div class="cs-grid-2">
                            <div class="cs-field">
                                <label>Nama Lengkap <span class="req">*</span></label>
                                <input class="cs-input" type="text" name="name" value="{{ $user->name }}" required placeholder="Nama lengkap"/>
                            </div>
                            <div class="cs-field">
                                <label>Email <span class="req">*</span></label>
                                <input class="cs-input" type="email" name="email" value="{{ $user->email }}" required placeholder="email@example.com"/>
                            </div>
                            <div class="cs-field">
                                <label>Nomor Telepon</label>
                                <input class="cs-input" type="tel" name="phone" value="{{ $user->phone ?? '' }}" placeholder="+62xxx"/>
                            </div>
                            <div class="cs-field">
                                <label>Tanggal Lahir</label>
                                <input class="cs-input" type="date" name="birth_date" value="{{ $user->birth_date ?? '' }}"/>
                            </div>
                            <div class="cs-field">
                                <label>Jenis Kelamin</label>
                                <select name="gender" class="cs-input">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="male"   {{ ($user->gender ?? '') === 'male'   ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="female" {{ ($user->gender ?? '') === 'female' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div class="cs-field">
                                <label>NIK</label>
                                <input class="cs-input" type="text" name="nik" value="{{ $user->nik ?? '' }}" placeholder="16 digit NIK" maxlength="16"/>
                                <span class="cs-hint">Data dilindungi dan tidak ditampilkan publik.</span>
                            </div>
                        </div>

                        <p class="cs-section-lbl" style="margin-top:1.25rem"><i class="bx bx-map"></i> Alamat</p>
                        <div class="cs-field" style="margin-bottom:.75rem">
                            <label>Alamat Lengkap</label>
                            <textarea name="address" class="cs-input cs-textarea" placeholder="Jalan, Nomor, RT/RW...">{{ $user->address ?? '' }}</textarea>
                        </div>
                        <div class="cs-grid-2">
                            <div class="cs-field">
                                <label>Kota / Kabupaten</label>
                                <input class="cs-input" type="text" name="city" value="{{ $user->city ?? '' }}" placeholder="Contoh: Surabaya"/>
                            </div>
                            <div class="cs-field">
                                <label>Provinsi</label>
                                <select name="province" class="cs-input">
                                    <option value="">Pilih Provinsi</option>
                                    @foreach(['Aceh','Sumatera Utara','Sumatera Barat','Riau','Jambi','Sumatera Selatan','Bengkulu','Lampung','Kepulauan Bangka Belitung','Kepulauan Riau','DKI Jakarta','Jawa Barat','Jawa Tengah','DI Yogyakarta','Jawa Timur','Banten','Bali','Nusa Tenggara Barat','Nusa Tenggara Timur','Kalimantan Barat','Kalimantan Tengah','Kalimantan Selatan','Kalimantan Timur','Kalimantan Utara','Sulawesi Utara','Sulawesi Tengah','Sulawesi Selatan','Sulawesi Tenggara','Gorontalo','Sulawesi Barat','Maluku','Maluku Utara','Papua Barat','Papua'] as $prov)
                                        <option value="{{ $prov }}" {{ ($user->province ?? '') === $prov ? 'selected' : '' }}>{{ $prov }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="cs-field">
                                <label>Kode Pos</label>
                                <input class="cs-input" type="text" name="postal_code" value="{{ $user->postal_code ?? '' }}" placeholder="40xxx" maxlength="5"/>
                            </div>
                        </div>

                        <p class="cs-section-lbl" style="margin-top:1.25rem"><i class="bx bx-phone-call"></i> Kontak Darurat</p>
                        <div class="cs-grid-2">
                            <div class="cs-field">
                                <label>Nama Kontak Darurat</label>
                                <input class="cs-input" type="text" name="emergency_contact_name" value="{{ $user->emergency_contact_name ?? '' }}" placeholder="Nama lengkap"/>
                            </div>
                            <div class="cs-field">
                                <label>Nomor Kontak Darurat</label>
                                <input class="cs-input" type="text" name="emergency_contact" value="{{ $user->emergency_contact ?? '' }}" placeholder="+62xxx"/>
                            </div>
                            <div class="cs-field">
                                <label>Hubungan</label>
                                <input class="cs-input" type="text" name="emergency_contact_relation" value="{{ $user->emergency_contact_relation ?? '' }}" placeholder="Orang tua, Pasangan..."/>
                            </div>
                        </div>

                        <p class="cs-section-lbl" style="margin-top:1.25rem"><i class="bx bx-notepad"></i> Lainnya</p>
                        <div class="cs-field">
                            <label>Bio / Catatan Tambahan</label>
                            <textarea name="bio" class="cs-input cs-textarea" rows="3" placeholder="Ceritakan tentang diri Anda...">{{ $user->bio ?? '' }}</textarea>
                        </div>

                        <div class="cs-actions">
                            <button type="reset" class="cs-btn gray"><i class="bx bx-reset"></i> Reset</button>
                            <button type="submit" class="cs-btn indigo" id="btnSaveProfile">
                                <i class="bx bx-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Ringkasan Akun --}}
            <div class="cs-card">
                <div class="cs-card-head h-teal">
                    <div class="cs-card-ico"><i class="bx bx-bar-chart-alt-2"></i></div>
                    <div><h6>Ringkasan Akun</h6><p>Status & informasi akun Anda</p></div>
                </div>
                <div class="cs-card-body">
                    <div class="cs-stat-grid">
                        <div class="cs-stat-item green">
                            <i class="bx bx-devices"></i>
                            <span class="cs-stat-v">1</span>
                            <span class="cs-stat-l">Perangkat Aktif</span>
                        </div>
                        <div class="cs-stat-item blue">
                            <i class="bx bx-time-five"></i>
                            <span class="cs-stat-v">Baru saja</span>
                            <span class="cs-stat-l">Login Terakhir</span>
                        </div>
                        <div class="cs-stat-item purple">
                            <i class="bx bx-ticket"></i>
                            <span class="cs-stat-v">—</span>
                            <span class="cs-stat-l">Total Tiket</span>
                        </div>
                        <div class="cs-stat-item amber">
                            <i class="bx bx-star"></i>
                            <span class="cs-stat-v">—</span>
                            <span class="cs-stat-l">Rating Diberikan</span>
                        </div>
                    </div>

                    <div class="cs-divider"></div>

                    <p class="cs-sec-title">Kelengkapan Profil</p>
                    @php
                        $score = 0;
                        if($user->name && $user->email) $score += 25;
                        if($user->phone) $score += 25;
                        if($user->avatar) $score += 25;
                        if($user->address) $score += 25;
                    @endphp
                    <div class="cs-security-score">
                        <div class="cs-score-circle">
                            <svg viewBox="0 0 36 36" class="cs-score-svg">
                                <path class="cs-score-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                <path class="cs-score-fill" stroke-dasharray="{{ $score }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            </svg>
                            <div class="cs-score-val">{{ $score }}<span>%</span></div>
                        </div>
                        <div class="cs-score-checks">
                            <div class="cs-scheck {{ ($user->name && $user->email) ? 'ok' : 'warn' }}">
                                <i class="bx {{ ($user->name && $user->email) ? 'bx-check-circle' : 'bx-error-circle' }}"></i>
                                <span>Profil dasar lengkap</span>
                            </div>
                            <div class="cs-scheck {{ $user->phone ? 'ok' : 'warn' }}">
                                <i class="bx {{ $user->phone ? 'bx-check-circle' : 'bx-error-circle' }}"></i>
                                <span>Nomor telepon</span>
                            </div>
                            <div class="cs-scheck {{ $user->avatar ? 'ok' : 'warn' }}">
                                <i class="bx {{ $user->avatar ? 'bx-check-circle' : 'bx-error-circle' }}"></i>
                                <span>Foto profil</span>
                            </div>
                            <div class="cs-scheck {{ $user->address ? 'ok' : 'warn' }}">
                                <i class="bx {{ $user->address ? 'bx-check-circle' : 'bx-error-circle' }}"></i>
                                <span>Alamat lengkap</span>
                            </div>
                        </div>
                    </div>

                    <div class="cs-divider"></div>

                    <p class="cs-sec-title">Informasi Akun</p>
                    <div class="cs-info-rows">
                        <div class="cs-info-row">
                            <span class="cs-info-lbl">Role</span>
                            <span class="cs-badge-sm indigo">Client</span>
                        </div>
                        <div class="cs-info-row">
                            <span class="cs-info-lbl">Status</span>
                            <span class="cs-badge-sm green"><i class="bx bx-radio-circle-marked"></i> Aktif</span>
                        </div>
                        @if($user->city)
                        <div class="cs-info-row">
                            <span class="cs-info-lbl">Kota</span>
                            <span class="cs-info-val">{{ $user->city }}</span>
                        </div>
                        @endif
                        @if($user->phone)
                        <div class="cs-info-row">
                            <span class="cs-info-lbl">Telepon</span>
                            <span class="cs-info-val">{{ $user->phone }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="cs-divider"></div>

                    <p class="cs-sec-title">Akses Cepat</p>
                    <div class="cs-quick-links">
                        <a class="cs-qlink" href="{{ route('client.dashboard') }}">
                            <span class="cs-qico" style="background:#dbeafe;color:#2563eb"><i class="bx bx-home-circle"></i></span>
                            <span>Dashboard</span><i class="bx bx-chevron-right chevron"></i>
                        </a>
                        <a class="cs-qlink" href="{{ route('client.tickets.index') }}">
                            <span class="cs-qico" style="background:#ede9fe;color:#7c3aed"><i class="bx bx-support"></i></span>
                            <span>Laporan Saya</span><i class="bx bx-chevron-right chevron"></i>
                        </a>
                        <a class="cs-qlink" href="{{ route('client.history') }}">
                            <span class="cs-qico" style="background:#fef3c7;color:#d97706"><i class="bx bx-history"></i></span>
                            <span>Riwayat Tiket</span><i class="bx bx-chevron-right chevron"></i>
                        </a>
                        {{-- FIXED: was client.ratings.index, correct route is client.pending-ratings --}}
                        <a class="cs-qlink" href="{{ route('client.pending-ratings') }}">
                            <span class="cs-qico" style="background:#ffe4e6;color:#e11d48"><i class="bx bx-star"></i></span>
                            <span>Belum Dirating</span><i class="bx bx-chevron-right chevron"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ TAB: KEAMANAN ══ --}}
    <div class="tab-panel" id="panel-security">
        <div class="cs-two-col">

            {{-- Ubah Password --}}
            <div class="cs-card">
                <div class="cs-card-head h-amber">
                    <div class="cs-card-ico"><i class="bx bx-lock-alt"></i></div>
                    <div><h6>Ubah Password</h6><p>Gunakan password yang kuat untuk keamanan akun</p></div>
                </div>
                <div class="cs-card-body">
                    <form id="passwordForm" onsubmit="submitPassword(event)">
                        @csrf
                        <div style="display:flex;flex-direction:column;gap:1rem">
                            <div class="cs-field">
                                <label>Password Saat Ini <span class="req">*</span></label>
                                <div class="cs-eye">
                                    <input type="password" class="cs-input" name="current_password" id="pwCurrent" placeholder="Password saat ini" required/>
                                    <button type="button" class="cs-eye-btn" onclick="togglePw('pwCurrent',this)"><i class="bx bx-show"></i></button>
                                </div>
                            </div>
                            <div class="cs-field">
                                <label>Password Baru <span class="req">*</span></label>
                                <div class="cs-eye">
                                    <input type="password" class="cs-input" name="new_password" id="pwNew" placeholder="Min. 8 karakter" required minlength="8" oninput="checkPwStrength(this.value)"/>
                                    <button type="button" class="cs-eye-btn" onclick="togglePw('pwNew',this)"><i class="bx bx-show"></i></button>
                                </div>
                                <div class="cs-strength" id="pwStrengthBar" style="display:none">
                                    <div class="cs-bars"><div class="cs-bar-fill" id="pwBarFill"></div></div>
                                    <span class="cs-str-txt" id="pwStrText"></span>
                                </div>
                            </div>
                            <div class="cs-field">
                                <label>Konfirmasi Password Baru <span class="req">*</span></label>
                                <div class="cs-eye">
                                    <input type="password" class="cs-input" name="new_password_confirmation" id="pwConfirm" placeholder="Ulangi password baru" required oninput="checkPwMatch()"/>
                                    <button type="button" class="cs-eye-btn" onclick="togglePw('pwConfirm',this)"><i class="bx bx-show"></i></button>
                                </div>
                                <p class="cs-err-msg" id="pwMismatch" style="display:none"><i class="bx bx-error-circle"></i> Password tidak cocok</p>
                                <p class="cs-ok-txt"  id="pwMatch"    style="display:none"><i class="bx bx-check-circle"></i> Password cocok</p>
                            </div>
                        </div>
                        <div class="cs-actions">
                            <button type="button" class="cs-btn gray" onclick="document.getElementById('passwordForm').reset();document.getElementById('pwStrengthBar').style.display='none'">
                                <i class="bx bx-reset"></i> Reset
                            </button>
                            <button type="submit" class="cs-btn amber" id="btnSavePassword">
                                <i class="bx bx-key"></i> Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tips Keamanan + Login Terakhir --}}
            <div class="cs-card">
                <div class="cs-card-head h-rose">
                    <div class="cs-card-ico"><i class="bx bx-shield-alt-2"></i></div>
                    <div><h6>Keamanan Akun</h6><p>Tips dan aktivitas login akun Anda</p></div>
                </div>
                <div class="cs-card-body">
                    <p class="cs-sec-title">Tips Keamanan Password</p>
                    <div class="cs-tips">
                        <div class="cs-tip">
                            <span class="cs-tip-ico" style="background:#dbeafe;color:#2563eb"><i class="bx bx-lock"></i></span>
                            <span>Gunakan minimal 8 karakter dengan kombinasi huruf, angka, dan simbol</span>
                        </div>
                        <div class="cs-tip">
                            <span class="cs-tip-ico" style="background:#d1fae5;color:#059669"><i class="bx bx-refresh"></i></span>
                            <span>Ganti password secara berkala, minimal setiap 3 bulan sekali</span>
                        </div>
                        <div class="cs-tip">
                            <span class="cs-tip-ico" style="background:#fef3c7;color:#d97706"><i class="bx bx-shield"></i></span>
                            <span>Jangan gunakan password yang sama untuk akun lain</span>
                        </div>
                        <div class="cs-tip">
                            <span class="cs-tip-ico" style="background:#fce7f3;color:#be185d"><i class="bx bx-hide"></i></span>
                            <span>Jangan bagikan password kepada siapapun</span>
                        </div>
                    </div>

                    <div class="cs-divider"></div>

                    <p class="cs-sec-title">Aktivitas Login Terakhir</p>
                    @if($lastLogin)
                    <div class="cs-login-item">
                        <div class="cs-list-ico success"><i class="bx bx-desktop"></i></div>
                        <div class="cs-list-info">
                            <p class="cs-list-title">{{ $lastLogin->device ?? 'Desktop' }} • {{ $lastLogin->browser ?? 'Browser' }}</p>
                            <p class="cs-list-sub">{{ $lastLogin->ip_address ?? '-' }}</p>
                            <span class="cs-list-time">
                                {{ \Carbon\Carbon::parse($lastLogin->logged_in_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}
                            </span>
                        </div>
                        <span class="cs-status-badge success"><i class="bx bx-check"></i> Berhasil</span>
                    </div>
                    @else
                    <div class="cs-empty">
                        <i class="bx bx-history"></i>
                        <p>Belum ada data login</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- ══ TAB: BANTUAN ══ --}}
    <div class="tab-panel" id="panel-help">
        <div class="cs-two-col">

            {{-- FAQ --}}
            <div class="cs-card">
                <div class="cs-card-head h-emerald">
                    <div class="cs-card-ico"><i class="bx bx-help-circle"></i></div>
                    <div><h6>Pertanyaan Umum (FAQ)</h6><p>Jawaban untuk pertanyaan yang sering diajukan</p></div>
                </div>
                <div class="cs-card-body">
                    @php
                    $faqs = [
                        ['q'=>'Bagaimana cara membuat tiket baru?',
                         'a'=>'Klik tombol "Buat Tiket" di dashboard atau halaman Laporan Saya, lalu isi formulir dengan judul, kategori, dan deskripsi masalah. Tiket akan langsung masuk ke sistem kami.'],
                        ['q'=>'Berapa lama waktu penanganan tiket?',
                         'a'=>'Waktu penanganan bervariasi tergantung tingkat urgensi. Tiket kritis biasanya ditangani dalam 1–24 jam, sedangkan tiket biasa 2–5 hari kerja.'],
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
                    <div class="cs-faq-list">
                        @foreach($faqs as $i => $faq)
                        <div class="cs-faq-item" id="faq-{{ $i }}">
                            <button class="cs-faq-trigger" onclick="toggleFAQ({{ $i }})">
                                <div class="cs-faq-num">{{ $i + 1 }}</div>
                                <span>{{ $faq['q'] }}</span>
                                <i class="bx bx-chevron-down cs-faq-arrow"></i>
                            </button>
                            <div class="cs-faq-answer">
                                <p>{{ $faq['a'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Panduan & Kontak --}}
            <div class="cs-card">
                <div class="cs-card-head h-purple">
                    <div class="cs-card-ico"><i class="bx bx-book-open"></i></div>
                    <div><h6>Panduan & Kontak</h6><p>Sumber bantuan tambahan untuk Anda</p></div>
                </div>
                <div class="cs-card-body">
                    <p class="cs-sec-title">Panduan Penggunaan</p>
                    <div class="cs-guide-list">
                        <div class="cs-guide-item">
                            <div class="cs-guide-ico" style="background:#dbeafe;color:#2563eb"><i class="bx bx-file-find"></i></div>
                            <div>
                                <p class="cs-guide-title">Cara Membuat & Melacak Tiket</p>
                                <p class="cs-guide-desc">Pelajari langkah-langkah membuat tiket dan memantau statusnya secara real-time</p>
                            </div>
                        </div>
                        <div class="cs-guide-item">
                            <div class="cs-guide-ico" style="background:#d1fae5;color:#059669"><i class="bx bx-star"></i></div>
                            <div>
                                <p class="cs-guide-title">Cara Memberikan Rating</p>
                                <p class="cs-guide-desc">Panduan memberikan penilaian vendor setelah tiket diselesaikan</p>
                            </div>
                        </div>
                        <div class="cs-guide-item">
                            <div class="cs-guide-ico" style="background:#fef3c7;color:#d97706"><i class="bx bx-bell"></i></div>
                            <div>
                                <p class="cs-guide-title">Notifikasi & Update Tiket</p>
                                <p class="cs-guide-desc">Cara mendapatkan pembaruan status tiket secara otomatis</p>
                            </div>
                        </div>
                    </div>

                    <div class="cs-divider"></div>

                    <p class="cs-sec-title">Hubungi Kami</p>
                    <div class="cs-contact-list">
                        <div class="cs-contact-item">
                            <div class="cs-contact-ico" style="background:#ede9fe;color:#7c3aed"><i class="bx bx-envelope"></i></div>
                            <div>
                                <p class="cs-contact-title">Email Support</p>
                                <p class="cs-contact-val">support@helpcenter.id</p>
                            </div>
                        </div>
                        <div class="cs-contact-item">
                            <div class="cs-contact-ico" style="background:#dbeafe;color:#2563eb"><i class="bx bx-phone"></i></div>
                            <div>
                                <p class="cs-contact-title">Telepon (Jam Kerja)</p>
                                <p class="cs-contact-val">+62 21-XXXX-XXXX</p>
                            </div>
                        </div>
                        <div class="cs-contact-item">
                            <div class="cs-contact-ico" style="background:#d1fae5;color:#059669"><i class="bx bx-time"></i></div>
                            <div>
                                <p class="cs-contact-title">Jam Operasional</p>
                                <p class="cs-contact-val">Senin – Jumat, 08.00–17.00 WIB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
const CSRF = '{{ csrf_token() }}';

// Toast
function showToast(type, msg) {
    const t = document.getElementById('csToast');
    document.getElementById('csToastIcon').className = 'bx ' + (type === 'success' ? 'bx-check-circle' : 'bx-error-circle');
    document.getElementById('csToastMsg').textContent = msg;
    t.className = 'cs-toast ' + type + ' show';
    setTimeout(() => t.classList.remove('show'), 4000);
}
function hideToast() { document.getElementById('csToast').classList.remove('show'); }

function setLoading(btnId, loading, originalHtml) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    btn.disabled = loading;
    btn.innerHTML = loading ? '<i class="bx bx-loader-alt spin"></i> Menyimpan...' : originalHtml;
}

// Tabs
function switchTab(tab, el) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.cs-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('panel-' + tab).classList.add('active');
    el.classList.add('active');
}

// Avatar
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
        if (prev) prev.src = e.target.result;
        else if (initEl) initEl.outerHTML = '<img src="' + e.target.result + '" id="avatarPreview" alt="Avatar" style="width:100%;height:100%;object-fit:cover"/>';
        if (heroImg) heroImg.src = e.target.result;
        else if (heroTxt) heroTxt.outerHTML = '<img src="' + e.target.result + '" class="cs-avatar-img" id="heroAvatar" alt="Avatar"/>';
    };
    reader.readAsDataURL(file);
}

// Submit Profile
async function submitProfile(e) {
    e.preventDefault();
    const form = document.getElementById('profileForm');
    const fd = new FormData(form);
    if (selectedAvatarFile) fd.append('avatar', selectedAvatarFile);
    setLoading('btnSaveProfile', true);
    try {
        const resp = await fetch('{{ route("client.settings.profile") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: fd
        });
        const data = await resp.json();
        if (data.success) {
            showToast('success', data.message || 'Profil berhasil diperbarui');
            if (data.data?.user?.name) document.getElementById('heroName').textContent = data.data.user.name;
            if (data.data?.user?.avatar_url) {
                const bust = data.data.user.avatar_url + '?v=' + Date.now();
                const img = document.getElementById('avatarPreview');
                if (img) img.src = bust;
                const hero = document.getElementById('heroAvatar');
                if (hero) hero.src = bust;
            }
            selectedAvatarFile = null;
        } else {
            const msg = data.errors ? Object.values(data.errors).flat()[0] : (data.message || 'Terjadi kesalahan');
            showToast('error', msg);
        }
    } catch { showToast('error', 'Gagal menghubungi server'); }
    finally { setLoading('btnSaveProfile', false, '<i class="bx bx-save"></i> Simpan Perubahan'); }
}

// Password
function togglePw(id, btn) {
    const inp = document.getElementById(id);
    const icon = btn.querySelector('i');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'text' ? 'bx bx-hide' : 'bx bx-show';
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
    if (score <= 1) { fill.className = 'cs-bar-fill weak'; txt.className = 'cs-str-txt weak'; txt.textContent = 'Lemah'; }
    else if (score <= 2) { fill.className = 'cs-bar-fill medium'; txt.className = 'cs-str-txt medium'; txt.textContent = 'Sedang'; }
    else { fill.className = 'cs-bar-fill strong'; txt.className = 'cs-str-txt strong'; txt.textContent = 'Kuat'; }
}

function checkPwMatch() {
    const a = document.getElementById('pwNew').value;
    const b = document.getElementById('pwConfirm').value;
    document.getElementById('pwMismatch').style.display = (b && a !== b) ? 'flex' : 'none';
    document.getElementById('pwMatch').style.display    = (b && a === b) ? 'flex' : 'none';
}

async function submitPassword(e) {
    e.preventDefault();
    const a = document.getElementById('pwNew').value;
    const b = document.getElementById('pwConfirm').value;
    if (a !== b) { showToast('error', 'Password tidak cocok'); return; }
    setLoading('btnSavePassword', true);
    try {
        const fd = new FormData(document.getElementById('passwordForm'));
        const resp = await fetch('{{ route("client.settings.password") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: fd
        });
        const data = await resp.json();
        if (data.success) {
            showToast('success', data.message || 'Password berhasil diubah');
            document.getElementById('passwordForm').reset();
            document.getElementById('pwStrengthBar').style.display = 'none';
            document.getElementById('pwMismatch').style.display = 'none';
            document.getElementById('pwMatch').style.display = 'none';
        } else {
            const msg = data.errors ? Object.values(data.errors).flat()[0] : (data.message || 'Gagal mengubah password');
            showToast('error', msg);
        }
    } catch { showToast('error', 'Gagal menghubungi server'); }
    finally { setLoading('btnSavePassword', false, '<i class="bx bx-key"></i> Ubah Password'); }
}

// FAQ
function toggleFAQ(i) {
    const item = document.getElementById('faq-' + i);
    const isOpen = item.classList.contains('open');
    document.querySelectorAll('.cs-faq-item.open').forEach(el => el.classList.remove('open'));
    if (!isOpen) item.classList.add('open');
}

// Spinner
const spin = document.createElement('style');
spin.textContent = '@keyframes spin{to{transform:rotate(360deg)}}.spin{animation:spin 1s linear infinite;display:inline-block}';
document.head.appendChild(spin);
</script>

<style>
/* ═══════════════════════════════════════
   SVG Score Gradient
═══════════════════════════════════════ */
svg defs { display: none; }

/* ═══════════════════════════════════════
   TOKENS
═══════════════════════════════════════ */
:root {
    --cs-primary:   #667eea;
    --cs-secondary: #764ba2;
    --cs-gradient:  linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    --cs-amber:     linear-gradient(135deg,#f59e0b,#d97706);
    --cs-teal:      linear-gradient(135deg,#0d9488,#0891b2);
    --cs-rose:      linear-gradient(135deg,#f43f5e,#ec4899);
    --cs-purple:    linear-gradient(135deg,#8b5cf6,#7c3aed);
    --cs-emerald:   linear-gradient(135deg,#10b981,#059669);
    --cs-border:    #e2e8f0;
    --cs-bg:        #f8fafc;
    --cs-text:      #1e293b;
    --cs-muted:     #64748b;
    --cs-light:     #94a3b8;
}

/* ═══════════════════════════════════════
   LAYOUT
═══════════════════════════════════════ */
.cs-wrap       { display:flex;flex-direction:column;gap:1.25rem; }
.cs-two-col    { display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;align-items:start; }

/* ═══════════════════════════════════════
   HERO
═══════════════════════════════════════ */
.cs-hero {
    background:var(--cs-gradient);
    border-radius:20px;
    padding:1.75rem 2rem;
    display:flex;align-items:center;justify-content:space-between;
    box-shadow:0 8px 24px rgba(102,126,234,.3);
    flex-wrap:wrap;gap:1rem;
}
.cs-hero-left  { display:flex;align-items:center;gap:1.25rem; }
.cs-hero-right { display:flex;align-items:center; }

.cs-avatar-area  { position:relative;flex-shrink:0; }
.cs-avatar-ring  { position:relative;width:76px;height:76px; }
.cs-avatar-img   { width:76px;height:76px;border-radius:50%;object-fit:cover;border:4px solid rgba(255,255,255,.5); }
.cs-avatar-txt   { width:76px;height:76px;border-radius:50%;border:4px solid rgba(255,255,255,.5);background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:800;color:white; }
.cs-avatar-online{ position:absolute;bottom:4px;right:4px;width:16px;height:16px;background:#10b981;border:3px solid white;border-radius:50%; }
.cs-avatar-upload-btn{ position:absolute;bottom:-4px;right:-4px;width:28px;height:28px;background:white;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.2);transition:all .2s;border:none; }
.cs-avatar-upload-btn:hover{ transform:scale(1.1);background:#f5f3ff; }
.cs-avatar-upload-btn i{ font-size:.9rem;color:#667eea; }

.cs-hero-name  { font-size:1.35rem;font-weight:800;color:white;margin-bottom:.2rem; }
.cs-hero-email { font-size:.85rem;color:rgba(255,255,255,.8);margin-bottom:.625rem; }
.cs-hero-badges{ display:flex;gap:.5rem;flex-wrap:wrap; }
.cs-badge      { display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .75rem;border-radius:20px;font-size:.75rem;font-weight:700; }
.cs-badge.indigo{ background:rgba(255,255,255,.25);color:white; }
.cs-badge.green { background:rgba(16,185,129,.25);color:#d1fae5; }

.cs-hero-stats { display:flex;align-items:center;gap:1rem;background:rgba(255,255,255,.15);padding:.75rem 1.25rem;border-radius:14px; }
.cs-hstat      { display:flex;flex-direction:column;align-items:center;gap:.1rem; }
.cs-hstat-val  { font-size:.9rem;font-weight:700;color:white; }
.cs-hstat-lbl  { font-size:.68rem;color:rgba(255,255,255,.7); }
.cs-hstat-div  { width:1px;height:28px;background:rgba(255,255,255,.25); }

/* ═══════════════════════════════════════
   TABS
═══════════════════════════════════════ */
.cs-tabs { display:flex;gap:.5rem;background:white;border-radius:14px;padding:.5rem;box-shadow:0 2px 12px rgba(0,0,0,.06); }
.cs-tab  { flex:1;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.75rem 1rem;border-radius:10px;border:none;background:transparent;cursor:pointer;font-size:.9rem;font-weight:600;color:#64748b;transition:all .25s;font-family:inherit; }
.cs-tab:hover  { background:#f8fafc;color:#1e293b; }
.cs-tab.active { background:var(--cs-gradient);color:white;box-shadow:0 4px 14px rgba(102,126,234,.3); }
.cs-tab i { font-size:1.1rem; }

/* ═══════════════════════════════════════
   TAB PANELS
═══════════════════════════════════════ */
.tab-panel        { display:none; }
.tab-panel.active { display:block;animation:fadeUp .25s ease both; }
@keyframes fadeUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

/* ═══════════════════════════════════════
   CARDS
═══════════════════════════════════════ */
.cs-card { background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.06); }
.cs-card-head { padding:1.125rem 1.5rem;color:white;display:flex;align-items:center;gap:.875rem; }
.cs-card-ico  { width:40px;height:40px;background:rgba(255,255,255,.2);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0; }
.cs-card-head h6 { font-size:1rem;font-weight:700;margin:0;flex:1;color:white!important; }
.cs-card-head p  { font-size:.78rem;margin:0;opacity:.88;color:white!important; }
.cs-card-body { padding:1.5rem; }

.h-indigo  { background:var(--cs-gradient); }
.h-amber   { background:var(--cs-amber); }
.h-rose    { background:var(--cs-rose); }
.h-purple  { background:var(--cs-purple); }
.h-teal    { background:var(--cs-teal); }
.h-emerald { background:var(--cs-emerald); }

/* ═══════════════════════════════════════
   AVATAR SECTION
═══════════════════════════════════════ */
.cs-avatar-section { display:flex;gap:1.5rem;align-items:flex-start;margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid #f1f5f9; }
.cs-avatar-lg { width:100px;height:100px;border-radius:50%;overflow:hidden;flex-shrink:0;box-shadow:0 4px 12px rgba(0,0,0,.1);background:var(--cs-gradient);display:flex;align-items:center;justify-content:center; }
.cs-avatar-lg img { width:100%;height:100%;object-fit:cover; }
.cs-avatar-lg-txt { font-size:2.25rem;font-weight:700;color:white; }
.cs-avatar-actions { display:flex;flex-direction:column;gap:.625rem; }
.cs-avatar-hint { font-size:.78rem;color:#94a3b8;margin:0;display:flex;align-items:center;gap:.3rem; }

/* ═══════════════════════════════════════
   FORM
═══════════════════════════════════════ */
.cs-section-lbl { font-size:.72rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin:0 0 .875rem;display:flex;align-items:center;gap:.4rem;padding-bottom:.6rem;border-bottom:1px solid #f1f5f9; }
.cs-section-lbl i { color:#667eea;font-size:.95rem; }
.cs-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:.75rem; }
.cs-field  { display:flex;flex-direction:column;gap:.35rem; }
.cs-field label { font-size:.83rem;font-weight:600;color:#475569; }
.req { color:#ef4444; }
.cs-hint { font-size:.73rem;color:#94a3b8; }
.cs-input { padding:.75rem .95rem;border:2px solid #e2e8f0;border-radius:10px;font-size:.875rem;color:#1e293b;background:white;transition:all .25s;font-family:inherit;width:100%;box-sizing:border-box; }
.cs-input:focus { outline:none;border-color:#667eea;box-shadow:0 0 0 3px rgba(102,126,234,.1); }
.cs-input::placeholder { color:#94a3b8; }
textarea.cs-input { resize:vertical;min-height:80px; }
select.cs-input { cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-repeat:no-repeat;background-position:right .75rem center;background-size:1.25em;padding-right:2.5rem; }

/* ═══════════════════════════════════════
   BUTTONS
═══════════════════════════════════════ */
.cs-actions { margin-top:1.25rem;padding-top:1.125rem;border-top:1px solid #f1f5f9;display:flex;gap:.625rem;flex-wrap:wrap;justify-content:flex-end; }
.cs-btn { display:inline-flex;align-items:center;gap:.45rem;padding:.7rem 1.375rem;border-radius:10px;font-weight:600;font-size:.875rem;cursor:pointer;border:none;transition:all .25s;font-family:inherit;text-decoration:none; }
.cs-btn:disabled { opacity:.6;cursor:not-allowed; }
.cs-btn.indigo        { background:var(--cs-gradient);color:white; }
.cs-btn.indigo:hover:not(:disabled) { transform:translateY(-2px);box-shadow:0 6px 18px rgba(102,126,234,.35); }
.cs-btn.amber         { background:var(--cs-amber);color:white; }
.cs-btn.amber:hover:not(:disabled)  { transform:translateY(-2px);box-shadow:0 6px 18px rgba(245,158,11,.35); }
.cs-btn.gray          { background:#f3f4f6;color:#6b7280; }
.cs-btn.gray:hover:not(:disabled)   { background:#e5e7eb; }
.cs-btn.indigo-outline { background:transparent;color:#667eea;border:2px solid #667eea; }
.cs-btn.indigo-outline:hover { background:#f5f3ff; }
.cs-btn.red-outline   { background:transparent;color:#ef4444;border:2px solid #fca5a5; }
.cs-btn.red-outline:hover { background:#fff1f2; }

/* ═══════════════════════════════════════
   STATS
═══════════════════════════════════════ */
.cs-stat-grid { display:grid;grid-template-columns:1fr 1fr;gap:.75rem; }
.cs-stat-item { display:flex;flex-direction:column;align-items:center;gap:.25rem;padding:.875rem;border-radius:12px;text-align:center; }
.cs-stat-item.green  { background:#f0fdf4; } .cs-stat-item.green  i { color:#10b981; }
.cs-stat-item.blue   { background:#eff6ff; } .cs-stat-item.blue   i { color:#3b82f6; }
.cs-stat-item.purple { background:#faf5ff; } .cs-stat-item.purple i { color:#8b5cf6; }
.cs-stat-item.amber  { background:#fffbeb; } .cs-stat-item.amber  i { color:#f59e0b; }
.cs-stat-item i  { font-size:1.4rem; }
.cs-stat-v { font-size:.85rem;font-weight:700;color:#1e293b; }
.cs-stat-l { font-size:.7rem;color:#94a3b8; }

/* ═══════════════════════════════════════
   MISC COMPONENTS
═══════════════════════════════════════ */
.cs-divider   { height:1px;background:#f1f5f9;margin:1.125rem 0; }
.cs-sec-title { font-size:.72rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin:0 0 .75rem; }

/* Security Score */
.cs-security-score { display:flex;align-items:center;gap:1.25rem;padding:.75rem;background:#f8fafc;border-radius:12px; }
.cs-score-circle   { position:relative;width:72px;height:72px;flex-shrink:0; }
.cs-score-svg      { width:72px;height:72px;transform:rotate(-90deg); }
.cs-score-bg       { fill:none;stroke:#e2e8f0;stroke-width:3.5; }
.cs-score-fill     { fill:none;stroke:#667eea;stroke-width:3.5;stroke-linecap:round; }
.cs-score-val      { position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:.95rem;font-weight:800;color:#1e293b;display:flex;align-items:baseline;gap:1px; }
.cs-score-val span { font-size:.55rem;font-weight:600;color:#94a3b8; }
.cs-score-checks   { display:flex;flex-direction:column;gap:.4rem;flex:1; }
.cs-scheck         { display:flex;align-items:center;gap:.5rem;font-size:.78rem;font-weight:600; }
.cs-scheck.ok   i  { color:#10b981;font-size:1rem; }
.cs-scheck.warn i  { color:#f59e0b;font-size:1rem; }

/* Info rows */
.cs-info-rows { display:flex;flex-direction:column;gap:.5rem; }
.cs-info-row  { display:flex;align-items:center;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f8fafc; }
.cs-info-lbl  { font-size:.83rem;color:#64748b; }
.cs-info-val  { font-size:.83rem;font-weight:600;color:#1e293b; }
.cs-badge-sm  { display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .625rem;border-radius:20px;font-size:.72rem;font-weight:700; }
.cs-badge-sm.indigo { background:#ede9fe;color:#5b21b6; }
.cs-badge-sm.green  { background:#d1fae5;color:#065f46; }

/* Quick links */
.cs-quick-links { display:flex;flex-direction:column;gap:.4rem; }
.cs-qlink { display:flex;align-items:center;gap:.75rem;padding:.75rem .875rem;border-radius:10px;text-decoration:none;color:#1e293b;background:#f8fafc;transition:all .2s;border:1px solid transparent; }
.cs-qlink:hover { background:#f1f5f9;border-color:#e2e8f0;transform:translateX(3px);text-decoration:none; }
.cs-qlink > span { flex:1;font-size:.85rem;font-weight:600; }
.cs-qlink > i.chevron { color:#94a3b8; }
.cs-qico { width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0; }

/* Password Eye */
.cs-eye { position:relative; }
.cs-eye .cs-input { padding-right:2.75rem; }
.cs-eye-btn { position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;font-size:1.05rem;display:flex;padding:0; }
.cs-err-msg { color:#ef4444;font-size:.78rem;display:flex;align-items:center;gap:.3rem;margin:.25rem 0 0; }
.cs-ok-txt  { color:#10b981;font-size:.78rem;display:flex;align-items:center;gap:.3rem;margin:.25rem 0 0; }

/* Strength bar */
.cs-strength { display:flex;align-items:center;gap:.5rem;margin-top:.3rem; }
.cs-bars     { flex:1;height:4px;background:#e2e8f0;border-radius:4px;overflow:hidden; }
.cs-bar-fill { height:100%;border-radius:4px;transition:all .3s; }
.cs-bar-fill.weak   { background:#ef4444;width:33%; }
.cs-bar-fill.medium { background:#f59e0b;width:66%; }
.cs-bar-fill.strong { background:#10b981;width:100%; }
.cs-str-txt         { font-size:.72rem;font-weight:600;min-width:50px; }
.cs-str-txt.weak    { color:#ef4444; }
.cs-str-txt.medium  { color:#f59e0b; }
.cs-str-txt.strong  { color:#10b981; }

/* Tips */
.cs-tips { display:flex;flex-direction:column;gap:.5rem; }
.cs-tip  { display:flex;align-items:center;gap:.75rem;padding:.625rem .875rem;background:#f8fafc;border-radius:10px;font-size:.82rem;color:#475569; }
.cs-tip-ico { width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0; }

/* Login item */
.cs-login-item { display:flex;align-items:center;gap:.875rem;padding:.875rem 1rem;background:#f8fafc;border-radius:12px; }
.cs-list-ico   { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;color:white;flex-shrink:0; }
.cs-list-ico.success { background:var(--cs-gradient); }
.cs-list-info  { flex:1;min-width:0; }
.cs-list-title { font-size:.875rem;font-weight:600;color:#1e293b;margin:0 0 .15rem; }
.cs-list-sub   { font-size:.78rem;color:#64748b;margin:0 0 .15rem; }
.cs-list-time  { font-size:.72rem;color:#94a3b8; }
.cs-status-badge { padding:.3rem .75rem;border-radius:6px;font-size:.75rem;font-weight:600;display:flex;align-items:center;gap:.25rem;flex-shrink:0; }
.cs-status-badge.success { background:#d1fae5;color:#059669; }

/* FAQ */
.cs-faq-list  { display:flex;flex-direction:column;gap:.5rem; }
.cs-faq-item  { border:1.5px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s; }
.cs-faq-item.open { border-color:#a5b4fc; }
.cs-faq-trigger { width:100%;display:flex;align-items:center;gap:.85rem;padding:1rem 1.1rem;background:white;border:none;cursor:pointer;font-size:.9rem;font-weight:700;color:#1e293b;text-align:left;font-family:inherit;transition:background .2s; }
.cs-faq-trigger:hover { background:#f8fafc;color:#4f46e5; }
.cs-faq-item.open .cs-faq-trigger { background:#f8fafc;color:#4f46e5; }
.cs-faq-num  { width:26px;height:26px;flex-shrink:0;border-radius:8px;background:#eef2ff;color:#4f46e5;font-size:.78rem;font-weight:800;display:flex;align-items:center;justify-content:center; }
.cs-faq-item.open .cs-faq-num { background:#4f46e5;color:white; }
.cs-faq-trigger span { flex:1; }
.cs-faq-arrow { font-size:1.15rem;color:#94a3b8;flex-shrink:0;transition:transform .25s ease; }
.cs-faq-item.open .cs-faq-arrow { transform:rotate(180deg);color:#4f46e5; }
.cs-faq-answer { max-height:0;overflow:hidden;transition:max-height .3s ease,padding .3s ease;background:#f8fafc;padding:0 1.1rem; }
.cs-faq-item.open .cs-faq-answer { max-height:200px;padding:.75rem 1.1rem 1.1rem; }
.cs-faq-answer p { margin:0;font-size:.875rem;color:#64748b;line-height:1.7; }

/* Guide list */
.cs-guide-list { display:flex;flex-direction:column;gap:.75rem; }
.cs-guide-item { display:flex;align-items:flex-start;gap:.875rem;padding:.875rem;background:#f8fafc;border-radius:12px; }
.cs-guide-ico  { width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0; }
.cs-guide-title { font-size:.875rem;font-weight:700;color:#1e293b;margin:0 0 .2rem; }
.cs-guide-desc  { font-size:.78rem;color:#64748b;margin:0; }

/* Contact list */
.cs-contact-list { display:flex;flex-direction:column;gap:.5rem; }
.cs-contact-item { display:flex;align-items:center;gap:.875rem;padding:.75rem;background:#f8fafc;border-radius:10px; }
.cs-contact-ico  { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0; }
.cs-contact-title { font-size:.78rem;color:#64748b;margin:0 0 .1rem; }
.cs-contact-val   { font-size:.875rem;font-weight:700;color:#1e293b;margin:0; }

/* Empty */
.cs-empty { display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem;gap:.75rem; }
.cs-empty i { font-size:2.5rem;color:#d1d5db; }
.cs-empty p { font-size:.875rem;color:#9ca3af;margin:0; }

/* Toast */
.cs-toast { position:fixed;top:1.5rem;right:1.5rem;z-index:9999;min-width:280px;max-width:400px;padding:1rem 1.25rem;border-radius:14px;display:flex;align-items:center;gap:.75rem;font-size:.9rem;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.15);transform:translateX(120%);transition:transform .4s cubic-bezier(.34,1.56,.64,1); }
.cs-toast.show    { transform:translateX(0); }
.cs-toast.success { background:#f0fdf4;color:#065f46;border:1px solid #bbf7d0; }
.cs-toast.error   { background:#fef2f2;color:#991b1b;border:1px solid #fecaca; }
.cs-toast i { font-size:1.25rem;flex-shrink:0; }
.cs-toast-close { margin-left:auto;background:none;border:none;cursor:pointer;color:inherit;opacity:.6;font-size:1.1rem; }
.cs-toast-close:hover { opacity:1; }

/* ═══════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════ */
@media (max-width:992px) {
    .cs-two-col { grid-template-columns:1fr; }
    .cs-hero-stats { display:none; }
}
@media (max-width:768px) {
    .cs-hero { flex-direction:column; }
    .cs-grid-2 { grid-template-columns:1fr; }
    .cs-avatar-section { flex-direction:column;align-items:center;text-align:center; }
    .cs-tabs { flex-wrap:wrap; }
    .cs-tab { flex:0 0 calc(33.33% - .35rem);font-size:.8rem; }
}
@media (max-width:480px) {
    .cs-card-body { padding:1.125rem; }
    .cs-actions { flex-direction:column; }
    .cs-btn { width:100%;justify-content:center; }
}
</style>
@endsection