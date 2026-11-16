@props(['src' => null, 'alt' => '', 'category' => '', 'muted' => false])

{{-- A 4:3 photo frame. Items without a photo, or whose file fails to load, get a placeholder
     with the category name (menu.js swaps it in on load errors). --}}
<div {{ $attributes->class('relative aspect-[4/3] overflow-hidden bg-ground') }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" decoding="async" width="400" height="300"
             data-menu-photo
             @class(['absolute inset-0 h-full w-full object-cover', 'grayscale opacity-60' => $muted])>
    @endif

    <div data-menu-photo-placeholder aria-hidden="true" @if ($src) hidden @endif
         @class(['absolute inset-0 flex flex-col items-center justify-center gap-3 text-ink-muted', 'opacity-60' => $muted])>
        <svg viewBox="0 0 120 60" fill="none" class="w-28">
            <ellipse cx="60" cy="30" rx="54" ry="20" stroke="currentColor" stroke-opacity="0.45"/>
            <circle cx="60" cy="30" r="7" stroke="currentColor" stroke-opacity="0.8"/>
            <circle cx="108" cy="21" r="3" @class(['fill-nebula' => ! $muted, 'fill-current' => $muted])/>
        </svg>
        <span class="px-4 text-center text-xs uppercase tracking-[0.2em]">{{ $category }}</span>
    </div>

    {{ $slot }}
</div>
