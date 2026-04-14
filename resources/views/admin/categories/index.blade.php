@extends('layouts.app')

@section('title', 'Manajemen Kategori')
@section('page_title', 'Manajemen Kategori')
@section('breadcrumb', 'Home / Kategori')



@section('content')
<div class="categories-wrap">

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
    <div class="flash-success">
        <i class='bx bx-check-circle'></i>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="flash-error">
        <i class='bx bx-error-circle'></i>
        {{ session('error') }}
    </div>
    @endif

    {{-- HERO --}}
    <section class="cat-hero">
        <div class="cat-hero-text">
            <h4>Manajemen Kategori</h4>
            <p>Kelola kategori tiket agar alur penanganan lebih rapi dan mudah dipantau.</p>
        </div>
        <button class="btn-add-cat" onclick="openAddModal()">
            <i class='bx bx-plus'></i> Tambah Kategori
        </button>
    </section>

    {{-- CATEGORY LIST --}}
    <article class="cat-card">
        @if($categories->isEmpty())
            <div class="cat-empty">
                <i class='bx bx-category'></i>
                <h6>Belum ada kategori</h6>
                <p>Mulai dengan menambahkan kategori pertama Anda</p>
            </div>
        @else
            <div class="category-list">
                @foreach($categories as $category)
                <div class="category-item">

                    {{-- Icon + Info --}}
                    <div class="cat-item-left">
                        <div class="cat-icon">
                            <i class='bx bx-category'></i>
                        </div>
                        <div class="cat-details">
                            <h6 class="cat-name">{{ $category->name }}</h6>
                            <p class="cat-desc">
                                {{ $category->description ?: 'Tidak ada deskripsi' }}
                            </p>
                        </div>
                    </div>

                    {{-- Badges + Actions --}}
                    <div class="cat-item-right">
                        <div class="cat-badges">
                            <span class="badge-ticket">
                                <i class='bx bx-receipt'></i>
                                {{ $category->tickets_count ?? 0 }} tiket
                            </span>
                            <span class="badge-status {{ $category->is_active ? 'active' : 'inactive' }}">
                                <span class="badge-dot"></span>
                                {{ $category->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>

                        <div class="cat-actions">
                            <button
                                class="cat-btn-icon cat-btn-edit"
                                title="Edit kategori"
                                onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}', {{ $category->is_active ? 'true' : 'false' }})"
                            >
                                <i class='bx bx-edit'></i>
                            </button>

                            @if(($category->tickets_count ?? 0) > 0)
                                <button
                                    class="cat-btn-icon cat-btn-delete"
                                    title="Tidak bisa dihapus - ada tiket aktif"
                                    disabled
                                >
                                    <i class='bx bx-trash'></i>
                                </button>
                            @else
                                <button
                                    class="cat-btn-icon cat-btn-delete"
                                    title="Hapus kategori"
                                    onclick="openDeleteModal({{ $category->id }}, '{{ addslashes($category->name) }}')"
                                >
                                    <i class='bx bx-trash'></i>
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
                @endforeach
            </div>
        @endif
    </article>

</div>

{{--â•â•
     MODAL: ADD / EDIT CATEGORYâ•â• --}}
<div class="cat-modal-backdrop" id="categoryModalBackdrop">
    <div class="cat-modal" id="categoryModal" role="dialog" aria-modal="true">

        <div class="cat-modal-header">
            <div class="cat-modal-header-text">
                <h5 id="modalTitle">Tambah Kategori Baru</h5>
                <p id="modalSubtitle">Buat kategori untuk mengorganisir tiket</p>
            </div>
            <button class="cat-modal-close" onclick="closeModal()" aria-label="Tutup">
                <i class='bx bx-x'></i>
            </button>
        </div>

        <form id="categoryForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="category_id" id="categoryId">

            <div class="cat-modal-body">

                {{-- Name --}}
                <div class="cat-form-group">
                    <label class="cat-label" for="catName">
                        Nama Kategori <span class="req">*</span>
                    </label>
                    <input
                        type="text"
                        class="cat-input"
                        id="catName"
                        name="name"
                        required
                        placeholder="Contoh: Sound System"
                        maxlength="255"
                    >
                </div>

                {{-- Description --}}
                <div class="cat-form-group">
                    <label class="cat-label" for="catDesc">Deskripsi</label>
                    <textarea
                        class="cat-textarea"
                        id="catDesc"
                        name="description"
                        rows="3"
                        placeholder="Jelaskan kategori ini secara singkat..."
                    ></textarea>
                </div>

                {{-- Active Toggle --}}
                <div class="cat-switch-row">
                    <div class="cat-switch-info">
                        <span class="cat-switch-label">Status Aktif</span>
                        <p class="cat-switch-desc">Kategori aktif dapat dipilih saat membuat tiket baru</p>
                    </div>
                    <label class="cat-toggle">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="catIsActive" value="1" checked>
                        <span class="cat-toggle-track"></span>
                    </label>
                </div>

            </div>

            <div class="cat-modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-save" id="saveBtn">
                    <i class='bx bx-check'></i>
                    <span id="saveBtnText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{--â•â•
     MODAL: DELETE CONFIRMATIONâ•â• --}}
<div class="cat-modal-backdrop" id="deleteModalBackdrop">
    <div class="cat-modal cat-delete-modal" role="dialog" aria-modal="true">

        <div class="cat-modal-header">
            <div class="cat-modal-header-text">
                <h5>Hapus Kategori?</h5>
                <p>Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <button class="cat-modal-close" onclick="closeDeleteModal()" aria-label="Tutup">
                <i class='bx bx-x'></i>
            </button>
        </div>

        <div class="cat-delete-warning">
            <p>Anda yakin ingin menghapus kategori berikut?</p>
            <div class="cat-delete-detail" id="deleteTargetName">-</div>
        </div>

        <div class="cat-modal-footer">
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Batal</button>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-delete-confirm">
                    <i class='bx bx-trash'></i> Ya, Hapus
                </button>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
/*â•
   CATEGORY MODAL CONTROLLER
   Vanilla JS - no framework dependencyâ• */

const backdrop    = document.getElementById('categoryModalBackdrop')
const delBackdrop = document.getElementById('deleteModalBackdrop')

/* â-€â-€ Helpers â-€â-€ */
function showBackdrop(el) {
    el.classList.add('show')
    document.body.style.overflow = 'hidden'
}

function hideBackdrop(el) {
    el.classList.remove('show')
    document.body.style.overflow = ''
}

/* â-€â-€ Add Modal â-€â-€ */
function openAddModal() {
    document.getElementById('modalTitle').textContent    = 'Tambah Kategori Baru'
    document.getElementById('modalSubtitle').textContent = 'Buat kategori untuk mengorganisir tiket'
    document.getElementById('saveBtnText').textContent   = 'Simpan'
    document.getElementById('formMethod').value          = 'POST'
    document.getElementById('categoryId').value          = ''
    document.getElementById('catName').value             = ''
    document.getElementById('catDesc').value             = ''
    document.getElementById('catIsActive').checked       = true

    const form = document.getElementById('categoryForm')
    form.action = '{{ route("admin.categories.store") }}'

    showBackdrop(backdrop)
    setTimeout(() => document.getElementById('catName').focus(), 300)
}

/* â-€â-€ Edit Modal â-€â-€ */
function openEditModal(id, name, description, isActive) {
    document.getElementById('modalTitle').textContent    = 'Edit Kategori'
    document.getElementById('modalSubtitle').textContent = 'Perbarui informasi kategori'
    document.getElementById('saveBtnText').textContent   = 'Perbarui'
    document.getElementById('formMethod').value          = 'PUT'
    document.getElementById('categoryId').value          = id
    document.getElementById('catName').value             = name
    document.getElementById('catDesc').value             = description
    document.getElementById('catIsActive').checked       = isActive

    const form = document.getElementById('categoryForm')
    form.action = `/admin/categories/${id}`

    showBackdrop(backdrop)
    setTimeout(() => document.getElementById('catName').focus(), 300)
}

/* â-€â-€ Close â-€â-€ */
function closeModal() {
    hideBackdrop(backdrop)
}

/* â-€â-€ Delete Modal â-€â-€ */
function openDeleteModal(id, name) {
    document.getElementById('deleteTargetName').textContent = name
    document.getElementById('deleteForm').action = `/admin/categories/${id}`
    showBackdrop(delBackdrop)
}

function closeDeleteModal() {
    hideBackdrop(delBackdrop)
}

/* â-€â-€ Close on backdrop click â-€â-€ */
backdrop.addEventListener('click', function(e) {
    if (e.target === backdrop) closeModal()
})

delBackdrop.addEventListener('click', function(e) {
    if (e.target === delBackdrop) closeDeleteModal()
})

/* â-€â-€ Close on Escape â-€â-€ */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal()
        closeDeleteModal()
    }
})

/* â-€â-€ Form submit: show loading state â-€â-€ */
document.getElementById('categoryForm').addEventListener('submit', function() {
    const btn  = document.getElementById('saveBtn')
    const text = document.getElementById('saveBtnText')
    btn.disabled = true
    text.textContent = 'Menyimpan...'
    btn.insertAdjacentHTML('afterbegin', '<span class="cat-spinner"></span> ')
})
</script>
@endpush

@push('styles')
<style>
/*â•
   CATEGORIES PAGE - BLADE VERSION
   Converted from Vue.js componentâ• */

/* â-€â-€ Variables (inherited from global or defined locally) â-€â-€ */
:root {
    --cat-primary: #4f46e5;
    --cat-primary-light: #eef2ff;
    --cat-success: #22c55e;
    --cat-success-light: #dcfce7;
    --cat-danger: #ef4444;
    --cat-danger-light: #fee2e2;
    --cat-warning: #f59e0b;
    --cat-warning-light: #fef3c7;
    --cat-border: #e2e8f0;
    --cat-text: #1e293b;
    --cat-text-muted: #64748b;
    --cat-text-light: #94a3b8;
    --cat-bg: #f8fafc;
    --cat-shadow: 0 4px 20px rgba(15, 23, 42, 0.07);
    --cat-shadow-lg: 0 18px 40px rgba(15, 23, 42, 0.10);
    --cat-radius: 16px;
    --cat-radius-sm: 10px;
    --cat-radius-xs: 8px;
}

.categories-wrap {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* â-€â-€ Hero Section â-€â-€ */
.cat-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    padding: 1.75rem 2rem;
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid var(--cat-border);
    box-shadow: var(--cat-shadow-lg);
    position: relative;
    overflow: hidden;
}

.cat-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--cat-primary), #818cf8, #a78bfa);
}

.cat-hero-text h4 {
    font-size: 1.375rem;
    font-weight: 800;
    color: var(--cat-text);
    margin: 0 0 0.375rem;
}

.cat-hero-text p {
    color: var(--cat-text-muted);
    margin: 0;
    font-size: 0.9rem;
    max-width: 520px;
}

.btn-add-cat {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.375rem;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    border: none;
    border-radius: var(--cat-radius-sm);
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.25s;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
    white-space: nowrap;
    flex-shrink: 0;
    text-decoration: none;
}

.btn-add-cat:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(79, 70, 229, 0.45);
    color: white;
}

/* â-€â-€ Flash Messages â-€â-€ */
.flash-success,
.flash-error {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-radius: var(--cat-radius-sm);
    font-weight: 600;
    font-size: 0.9rem;
}

.flash-success {
    background: var(--cat-success-light);
    color: #15803d;
    border: 1px solid rgba(34, 197, 94, 0.25);
}

.flash-error {
    background: var(--cat-danger-light);
    color: #b91c1c;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

/* â-€â-€ Card â-€â-€ */
.cat-card {
    background: #ffffff;
    border-radius: var(--cat-radius);
    border: 1px solid var(--cat-border);
    box-shadow: var(--cat-shadow);
    overflow: hidden;
}

/* â-€â-€ Empty State â-€â-€ */
.cat-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    gap: 0.75rem;
    color: var(--cat-text-light);
    text-align: center;
}

.cat-empty i {
    font-size: 3.5rem;
    color: var(--cat-border);
}

.cat-empty h6 {
    font-weight: 700;
    color: var(--cat-text-muted);
    margin: 0;
}

.cat-empty p {
    font-size: 0.875rem;
    margin: 0;
    color: var(--cat-text-light);
}

/* â-€â-€ Category List â-€â-€ */
.category-list {
    display: flex;
    flex-direction: column;
}

.category-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--cat-bg);
    transition: background 0.2s;
}

.category-item:last-child {
    border-bottom: none;
}

.category-item:hover {
    background: var(--cat-bg);
}

/* Left: Icon + info */
.cat-item-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
    min-width: 0;
}

.cat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.cat-icon i {
    font-size: 1.375rem;
    color: white;
}

.cat-details {
    flex: 1;
    min-width: 0;
}

.cat-name {
    font-weight: 700;
    font-size: 0.9875rem;
    color: var(--cat-text);
    margin: 0 0 0.2rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.cat-desc {
    font-size: 0.8125rem;
    color: var(--cat-text-light);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Right: Badges + actions */
.cat-item-right {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-shrink: 0;
}

.cat-badges {
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.badge-ticket {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.35rem 0.75rem;
    background: #e0f2fe;
    color: #0284c7;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
}

.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.75rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
}

.badge-status.active {
    background: var(--cat-success-light);
    color: #15803d;
}

.badge-status.inactive {
    background: var(--cat-danger-light);
    color: #b91c1c;
}

.badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    flex-shrink: 0;
}

/* Action buttons */
.cat-actions {
    display: flex;
    gap: 0.4rem;
}

.cat-btn-icon {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: var(--cat-radius-xs);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.2s;
    text-decoration: none;
}

.cat-btn-edit {
    background: #f8f8ff;
    color: #5e5873;
}

.cat-btn-edit:hover {
    background: #e7e7ff;
    color: var(--cat-primary);
    transform: translateY(-1px);
    color: var(--cat-primary);
}

.cat-btn-delete {
    background: #fff5f5;
    color: var(--cat-danger);
}

.cat-btn-delete:hover:not(:disabled) {
    background: var(--cat-danger);
    color: white;
    transform: translateY(-1px);
}

.cat-btn-delete:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/*â•
   MODAL STYLESâ• */
.cat-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 1rem;
    backdrop-filter: blur(4px);
    opacity: 0;
    visibility: hidden;
    transition: all 0.25s;
}

.cat-modal-backdrop.show {
    opacity: 1;
    visibility: visible;
}

.cat-modal {
    background: white;
    border-radius: 20px;
    width: 100%;
    max-width: 480px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.18);
    transform: translateY(20px) scale(0.97);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    overflow: hidden;
}

.cat-modal-backdrop.show .cat-modal {
    transform: translateY(0) scale(1);
}

.cat-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1.5rem 1.5rem 0;
}

.cat-modal-header-text h5 {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--cat-text);
    margin: 0 0 0.25rem;
}

.cat-modal-header-text p {
    font-size: 0.825rem;
    color: var(--cat-text-muted);
    margin: 0;
}

.cat-modal-close {
    width: 32px;
    height: 32px;
    border: 1px solid var(--cat-border);
    background: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1.125rem;
    color: var(--cat-text-muted);
    transition: all 0.2s;
    flex-shrink: 0;
    margin-top: -2px;
}

.cat-modal-close:hover {
    background: var(--cat-bg);
    color: var(--cat-text);
}

.cat-modal-body {
    padding: 1.25rem 1.5rem;
}

.cat-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.625rem;
    padding: 0 1.5rem 1.5rem;
}

/* Form fields */
.cat-form-group {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
    margin-bottom: 1rem;
}

.cat-form-group:last-child {
    margin-bottom: 0;
}

.cat-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--cat-text-muted);
}

.cat-label .req {
    color: var(--cat-danger);
    margin-left: 2px;
}

.cat-input,
.cat-textarea {
    padding: 0.75rem 1rem;
    border: 2px solid var(--cat-border);
    border-radius: var(--cat-radius-sm);
    font-size: 0.875rem;
    font-family: inherit;
    color: var(--cat-text);
    background: white;
    transition: border-color 0.2s, box-shadow 0.2s;
    width: 100%;
    box-sizing: border-box;
}

.cat-input:focus,
.cat-textarea:focus {
    outline: none;
    border-color: var(--cat-primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
}

.cat-textarea {
    resize: vertical;
    min-height: 80px;
}

/* Toggle switch */
.cat-switch-row {
    display: flex;
    align-items: flex-start;
    gap: 0.875rem;
    padding: 0.875rem 1rem;
    background: var(--cat-bg);
    border-radius: var(--cat-radius-xs);
    border: 1px solid var(--cat-border);
}

.cat-switch-info {
    flex: 1;
}

.cat-switch-label {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--cat-text);
    display: block;
    margin-bottom: 0.2rem;
}

.cat-switch-desc {
    font-size: 0.775rem;
    color: var(--cat-text-light);
    margin: 0;
}

/* Custom checkbox toggle */
.cat-toggle {
    position: relative;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
    margin-top: 2px;
}

.cat-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}

.cat-toggle-track {
    position: absolute;
    inset: 0;
    background: #cbd5e1;
    border-radius: 999px;
    cursor: pointer;
    transition: background 0.3s;
}

.cat-toggle-track::after {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    background: white;
    border-radius: 50%;
    top: 3px;
    left: 3px;
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.cat-toggle input:checked + .cat-toggle-track {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
}

.cat-toggle input:checked + .cat-toggle-track::after {
    transform: translateX(20px);
}

/* Buttons */
.btn-cancel {
    padding: 0.7rem 1.125rem;
    background: var(--cat-bg);
    color: var(--cat-text-muted);
    border: 1px solid var(--cat-border);
    border-radius: var(--cat-radius-xs);
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: #f1f5f9;
    color: var(--cat-text);
}

.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.7rem 1.375rem;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    border: none;
    border-radius: var(--cat-radius-xs);
    font-weight: 700;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.25s;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.btn-save:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
}

.btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Delete modal */
.cat-delete-modal .cat-modal-header {
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--cat-border);
    margin-bottom: 0;
}

.cat-delete-warning {
    padding: 1.25rem 1.5rem;
}

.cat-delete-warning p {
    font-size: 0.9rem;
    color: var(--cat-text-muted);
    margin: 0 0 1rem;
}

.cat-delete-detail {
    background: var(--cat-danger-light);
    border-radius: var(--cat-radius-xs);
    padding: 0.875rem 1rem;
    border-left: 3px solid var(--cat-danger);
    font-size: 0.875rem;
    font-weight: 700;
    color: #b91c1c;
}

.btn-delete-confirm {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.7rem 1.375rem;
    background: var(--cat-danger);
    color: white;
    border: none;
    border-radius: var(--cat-radius-xs);
    font-weight: 700;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-delete-confirm:hover {
    background: #dc2626;
    transform: translateY(-1px);
}

/* Spinner */
.cat-spinner {
    display: inline-block;
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255,255,255,0.4);
    border-top-color: white;
    border-radius: 50%;
    animation: catSpin 0.7s linear infinite;
}

@keyframes catSpin {
    to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 767px) {
    .cat-hero {
        flex-direction: column;
        align-items: flex-start;
        padding: 1.25rem;
    }

    .btn-add-cat {
        width: 100%;
        justify-content: center;
    }

    .category-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.875rem;
    }

    .cat-item-right {
        width: 100%;
        justify-content: space-between;
    }
}
</style>
@endpush

