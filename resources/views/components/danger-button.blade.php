<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-11 items-center justify-center rounded-full px-6 text-sm font-bold transition disabled:opacity-40 bg-red-400 text-on-nebula hover:bg-red-300']) }}>
    {{ $slot }}
</button>
