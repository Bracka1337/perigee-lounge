@props([
    'name',
    'category' => null,
    'class' => 'w-5 h-5',
])

@php
    $base = public_path('icons/coolicons');

    $path = $category
        ? $base . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $name . '.svg'
        : null;

    if (!$path || !file_exists($path)) {
        $matches = glob($base . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $name . '.svg');
        $path = $matches[0] ?? null;
    }
@endphp

@if($path && file_exists($path))
    @php
        $svg = file_get_contents($path);

        $svg = preg_replace('/<svg\b/', '<svg class="'.$class.'"', $svg, 1);

        echo $svg;
    @endphp
@else
    <!-- icon not found: {{ $category ? $category.'/' : '' }}{{ $name }} -->
@endif
