@props(['eyebrow' => null, 'title', 'lead' => null])

<div {{ $attributes->merge(['class' => 'mb-12']) }}>
    @if ($eyebrow)
        <p class="mb-4 text-xs font-bold uppercase tracking-[0.22em] text-nebula">{{ $eyebrow }}</p>
    @endif
    <h1 class="font-display text-[clamp(2rem,4vw,3.25rem)] font-light leading-tight tracking-tight">{{ $title }}</h1>
    @if ($lead)
        <p class="mt-4 max-w-2xl text-lg leading-relaxed text-ink-soft">{{ $lead }}</p>
    @endif
</div>
