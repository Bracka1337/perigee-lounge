@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-nebula py-3 ps-3 pe-4 text-start font-medium text-ink'
            : 'block w-full border-l-4 border-transparent py-3 ps-3 pe-4 text-start font-medium text-ink-muted transition hover:text-ink';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
