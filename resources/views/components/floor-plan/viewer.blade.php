{{-- The 3D view of the lounge, filled by lounge-toggle.js when 3D is chosen. --}}
@props(['active', 'layout'])

<div data-plan-3d class="hidden">
    <div data-lounge-viewer data-active-floor="{{ $active }}"
         data-label-day="{{ __('lounge-3d-day') }}"
         data-label-evening="{{ __('lounge-3d-evening') }}"
         data-label-whole-floor="{{ __('lounge-3d-whole-floor') }}"
         data-label-canvas="{{ __('lounge-3d-canvas') }}"
         data-label-seats="{{ __('lounge-3d-seats', ['count' => ':count']) }}"
         data-label-room-book="{{ __('lounge-3d-room-book') }}"
         data-label-room-taken="{{ __('room-booking-taken') }}"
         class="relative aspect-[16/14] w-full overflow-hidden rounded-2xl border border-line bg-ground">
        <p data-lounge-loading class="absolute inset-0 flex items-center justify-center text-xs text-ink-muted">{{ __('lounge-3d-loading') }}</p>
    </div>
    <p class="mt-2 text-xs text-ink-muted">{{ __('lounge-3d-hint') }}</p>
</div>

<script type="application/json" id="loungeLayoutData">@json($layout)</script>
