<footer class="w-full border-t border-white/10 bg-black/95 mt-6">
    <div class="max-w-6xl mx-auto px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-3 text-[11px] text-white/40">
        <p>
            © {{ now()->year }} {{ __('perigee-name') }} {{ __('perigee-rest-name') }} — {{ __('header-admin') }}
        </p>
        <p class="text-white/50 md:text-right">
            {{ __('admin-footer-rights') }}
        </p>
    </div>
</footer>
