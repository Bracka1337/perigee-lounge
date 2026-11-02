<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex min-h-11 items-center justify-center rounded-full px-6 text-sm font-bold transition disabled:opacity-40 border border-line-strong text-ink hover:border-ink']) }}>
    {{ $slot }}
</button>
