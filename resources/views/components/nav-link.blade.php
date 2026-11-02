@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex min-h-11 items-center border-b-2 border-nebula text-sm font-medium text-ink'
            : 'inline-flex min-h-11 items-center border-b-2 border-transparent text-sm font-medium text-ink-muted transition hover:text-ink';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
