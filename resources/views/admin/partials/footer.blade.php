<footer class="w-full border-t border-line bg-ground/95 mt-6">
    <div class="max-w-7xl mx-auto px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-ink-muted">
        <p>
            © {{ now()->year }} {{ __('perigee-name') }} {{ __('perigee-rest-name') }} — {{ __('header-admin') }}
        </p>
        <p class="text-ink-muted md:text-right">
            {{ __('admin-footer-rights') }}
        </p>
    </div>
</footer>
