<footer class="w-full border-t border-white/10 bg-black/95 mt-6">
    <div class="max-w-5xl mx-auto px-4 py-4 flex flex-col md:flex-row items-center justify-between gap-3 text-[11px] text-white/40">
        <p>
            {{ __('staff-footer-left', ['year' => now()->year]) }}
        </p>
        <p class="text-white/50 md:text-right">
            {{ __('staff-footer-right') }}
        </p>
    </div>
</footer>
