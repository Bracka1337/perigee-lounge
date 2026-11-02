@extends('layouts.staff')

@section('content')
    <div class="mb-8">
        <h1 class="logo-font text-3xl md:text-4xl mb-3 drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
            {{ __('staff-dashboard-title') }}
        </h1>
        <p class="text-sm text-white/70">
            {{ __('staff-dashboard-subtitle') }}
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="space-y-1">
                    <h2 class="text-xs uppercase tracking-[0.22em] text-white/60">
                        {{ __('staff-dashboard-upcoming-title') }}
                    </h2>
                    <div class="inline-flex items-center gap-2 text-[11px] text-white/70">
                        <span class="px-2 py-0.5 rounded-full border border-white/25 bg-white/5">
                            {{ __('staff-dashboard-upcoming-date', ['date' => $tomorrow->format('d.m.Y')]) }}
                        </span>
                    </div>
                </div>
                <span class="text-[11px] text-white/60">
                    {{ $tomorrowReservations->count() }}
                </span>
            </div>

            @if($tomorrowReservations->isEmpty())
                <p class="text-xs text-white/60">
                    {{ __('staff-dashboard-upcoming-empty') }}
                </p>
            @else
                <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                    @foreach($tomorrowReservations as $res)
                        <div class="rounded-xl bg-black/40 border border-white/10 px-3 py-2 text-xs text-white/80">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="text-[11px] text-white/60">
                                        {{ $res->start_at->format('d.m H:i') }}
                                    </div>
                                    <div class="text-white">
                                        {{ __('staff-reservations-table-label') }} {{ $res->table->name ?? '#' }}
                                    </div>
                                    <div class="text-[11px] text-white/60">
                                        {{ $res->guest_name ?? ($res->user->name ?? 'Гость') }}
                                    </div>
                                    <div class="mt-1 text-[10px] text-white/50">
                                        {{ __('staff-reservations-status-label') }}: {{ __('staff-reservations-status-'.$res->status) }}
                                    </div>
                                </div>
                                <div class="text-right text-[10px] text-white/60">
                                    {{ __('staff-reservations-field-guests') }}: {{ $res->guests_count }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
