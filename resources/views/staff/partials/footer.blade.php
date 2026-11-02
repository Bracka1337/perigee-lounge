<footer class="w-full border-t border-line bg-ground/95 mt-6">
    <div class="max-w-5xl mx-auto px-4 py-4 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-ink-muted">
        <p>
            {{ __('staff-footer-left', ['year' => now()->year]) }}
        </p>
        <p class="text-ink-muted md:text-right">
            {{ __('staff-footer-right') }}
        </p>
    </div>
</footer>
