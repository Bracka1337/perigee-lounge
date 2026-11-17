{{-- Doors and fixtures of one floor, as an SVG layer in metres over the rooms. --}}
@props(['floor', 'frame'])

<svg class="pointer-events-none absolute inset-0 h-full w-full" viewBox="{{ $frame->viewBox() }}" preserveAspectRatio="none" aria-hidden="true">
    @foreach ($floor->fixtures as $fixture)
        @if ($fixture->type === \App\Enums\FixtureType::BarCounter && $fixture->points)
            <polyline class="plan-bar" points="{{ collect($fixture->points)->map(fn ($point) => $point[0].','.$point[1])->implode(' ') }}" />
        @else
            <g transform="translate({{ $fixture->x }} {{ $fixture->y }}) rotate({{ $fixture->rotation }})" class="plan-fixture plan-fixture--{{ $fixture->type->group() }}">
                <rect x="{{ -$fixture->width / 2 }}" y="{{ -$fixture->depth / 2 }}" width="{{ $fixture->width }}" height="{{ $fixture->depth }}" rx="0.05" />
                @if (in_array($fixture->type, [\App\Enums\FixtureType::GameStation, \App\Enums\FixtureType::Karaoke, \App\Enums\FixtureType::DjBooth], true))
                    {{-- The screen side, so it is clear which way it faces. --}}
                    <line x1="{{ -$fixture->width * 0.4 }}" y1="{{ $fixture->depth / 2 }}" x2="{{ $fixture->width * 0.4 }}" y2="{{ $fixture->depth / 2 }}" class="plan-fixture-front" />
                @endif
            </g>
        @endif
    @endforeach

    @foreach ($floor->doors as $door)
        @php
            $half = $door->width / 2;
            [$x1, $y1, $x2, $y2] = $door->orientation === 'h'
                ? [$door->x - $half, $door->y, $door->x + $half, $door->y]
                : [$door->x, $door->y - $half, $door->x, $door->y + $half];
        @endphp
        <line x1="{{ $x1 }}" y1="{{ $y1 }}" x2="{{ $x2 }}" y2="{{ $y2 }}" class="plan-door-gap" />
        @if ($door->kind !== \App\Enums\DoorKind::Opening)
            <line x1="{{ $x1 }}" y1="{{ $y1 }}" x2="{{ $x2 }}" y2="{{ $y2 }}" class="plan-door plan-door--{{ $door->kind->value }}" />
        @endif
    @endforeach
</svg>
