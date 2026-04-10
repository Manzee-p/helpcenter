{{-- ============================================================
     resources/views/partials/footer.blade.php
     Include di layout utama: @include('partials.footer')
     ============================================================ --}}

<footer class="content-footer">
    <div class="footer-inner">
        <span class="footer-copy">
            &copy; {{ date('Y') }}
            <a href="#" class="footer-brand">Web Helpdesk</a>
        </span>
        <div class="footer-links">
            <a href="#" class="footer-link">Dokumentasi</a>
            <a href="#" class="footer-link">Bantuan</a>
        </div>
    </div>
</footer>

<style>
/* ── FOOTER ── */
.content-footer {
    padding: .875rem 1.5rem;
    background: white;
    border-top: 1px solid var(--border);
    margin-top: auto;
}

.footer-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .5rem;
}

.footer-copy {
    font-size: .8125rem;
    color: var(--text-muted);
}

.footer-brand {
    font-weight: 700;
    color: var(--primary);
    text-decoration: none;
    transition: color .15s;
}
.footer-brand:hover { color: var(--primary-dark, #4338ca); }

.footer-links {
    display: flex;
    gap: 1.25rem;
}

.footer-link {
    font-size: .8125rem;
    color: var(--text-muted);
    text-decoration: none;
    font-weight: 500;
    transition: color .15s;
}
.footer-link:hover { color: var(--primary); }

@media (max-width: 576px) {
    .footer-inner { justify-content: center; text-align: center; }
}
</style>