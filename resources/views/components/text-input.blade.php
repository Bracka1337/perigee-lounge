@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-11 rounded-xl border border-line-strong bg-ground px-4 text-ink placeholder:text-ink-muted focus:border-nebula focus:outline-none']) }}>
