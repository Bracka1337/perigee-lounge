<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-11 items-center justify-center rounded-full px-6 text-sm font-bold transition disabled:opacity-40 bg-nebula text-on-nebula hover:bg-ink']) }}>
    {{ $slot }}
</button>
