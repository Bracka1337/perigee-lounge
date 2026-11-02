@extends('layouts.staff')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="logo-font text-3xl md:text-4xl mb-2 drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
            {{ __('staff-promo-title') }}
        </h1>
        <p class="text-sm text-white/70">
            {{ __('staff-promo-subtitle') }}
        </p>
    </div>

<a href="{{ route('staff.promo-codes.create', ['locale' => app()->getLocale()]) }}"
   class="shrink-0 md:self-center
          inline-flex items-center justify-center gap-2
          h-10 px-4
          rounded-full border border-white/60
          text-[10px] uppercase tracking-[0.18em]
          text-white hover:bg-white hover:text-black transition">
    <x-icon category="Edit" name="Add_Plus_Circle" class="w-5 h-5 shrink-0"/>
    <span class="leading-none whitespace-nowrap">{{ __('staff-promo-add-button') }}</span>
</a>


</div>


    @if(session('success'))
        <div class="mb-4 text-xs text-emerald-300 border border-emerald-500/60 bg-emerald-500/10 px-4 py-2 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-3">
        @forelse($promoCodes as $promo)
            <div class="rounded-2xl bg-black/40 border border-white/10 px-4 py-3 text-xs text-white/80 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <div class="text-sm text-white">
                        {{ $promo->public_code ?? $promo->code }}
                    </div>
                    <div class="text-[11px] text-white/60 mt-1">
                        {{ __('staff-promo-discount-type-'.$promo->discount_type, ['value' => $promo->discount_value]) }}
                        · {{ __('staff-promo-scope-'.$promo->scope) }}
                    </div>
                    <div class="text-[11px] text-white/50 mt-1">
                        {{ __('staff-promo-uses-label') }}:
                        @if($promo->max_uses)
                            {{ $promo->used_count }} / {{ $promo->max_uses }}
                        @else
                            {{ $promo->used_count }} / ∞
                        @endif
                    </div>
                    <div class="text-[11px] text-white/50 mt-1">
                        {{ __('staff-promo-period-label') }}:
                        @if($promo->valid_from)
                            {{ $promo->valid_from->format('d.m.Y H:i') }}
                        @else
                            –
                        @endif
                        –
                        @if($promo->valid_until)
                            {{ $promo->valid_until->format('d.m.Y H:i') }}
                        @else
                            –
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full border text-[10px] uppercase tracking-[0.18em]
                        {{ $promo->is_active ? 'border-emerald-400/70 text-emerald-200' : 'border-white/30 text-white/60' }}">
                        {{ $promo->is_active ? __('staff-promo-badge-active') : __('staff-promo-badge-inactive') }}
                    </span>

                    <a href="{{ route('staff.promo-codes.edit', ['locale' => app()->getLocale(), 'promo_code' => $promo]) }}"
                       class="px-3 py-1 rounded-full border border-white/40 text-[10px] uppercase tracking-[0.18em] text-white hover:bg-white hover:text-black transition">
                        {{-- {{ __('staff-promo-edit-button') }} --}}
                        <x-icon category="Edit" name="Edit_Pencil_Line_01" class="w-5 h-5"/>
                    </a>

                    <form method="POST"
                          id="delete-promo-{{ $promo->id }}"
                          action="{{ route('staff.promo-codes.destroy', ['locale' => app()->getLocale(), 'promo_code' => $promo]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                                class="px-3 py-1 rounded-full border border-red-500/70 text-[10px] uppercase tracking-[0.18em] text-red-300 hover:bg-red-500/20 hover:text-red-100 transition js-delete-promo-btn"
                                data-promo-code="{{ $promo->public_code ?? $promo->code }}"
                                data-form-id="delete-promo-{{ $promo->id }}">
                            {{-- {{ __('staff-promo-delete-button') }} --}}
                            <x-icon category="Interface" name="Trash_Empty" class="w-5 h-5"/>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-white/60">
                {{ __('staff-promo-empty') }}
            </p>
        @endforelse
    </div>

    <div id="deletePromoModal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur">
        <div class="max-w-md w-full mx-4 bg-black/95 border border-red-500/50 rounded-2xl shadow-[0_0_40px_rgba(248,113,113,0.5)] p-6">
            <div class="mb-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full border border-red-500/70 flex items-center justify-center">
                    <span class="text-red-400 text-lg">!</span>
                </div>
                <h2 class="text-base md:text-lg font-semibold text-white tracking-wide">
                    {{ __('staff-promo-delete-modal-title') }}
                </h2>
            </div>

            <p class="text-sm text-white/70 mb-4">
                {{ __('staff-promo-delete-modal-text-prefix') }}
                <span id="deletePromoCode" class="font-semibold text-white"></span>?
            </p>
            <p class="text-xs text-white/40 mb-5">
                {{ __('staff-promo-delete-modal-text-note') }}
            </p>

            <div class="flex flex-wrap justify-end gap-3">
                <button type="button"
                        id="deletePromoCancelBtn"
                        class="px-5 py-2 rounded-full border border-white/30
                               text-[11px] tracking-[0.22em] uppercase text-white/70
                               hover:text-white hover:border-white/70 hover:bg-white/5 transition">
                    {{ __('staff-promo-delete-modal-cancel') }}
                </button>
                <button type="button"
                        id="deletePromoConfirmBtn"
                        class="px-5 py-2 rounded-full border border-red-500/80 bg-red-600/90
                               text-[11px] tracking-[0.22em] uppercase text-white
                               shadow-[0_0_14px_rgba(248,113,113,0.7)]
                               hover:bg-red-500 transition">
                    {{ __('staff-promo-delete-modal-confirm') }}
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('deletePromoModal');
        const codeSpan = document.getElementById('deletePromoCode');
        const cancelBtn = document.getElementById('deletePromoCancelBtn');
        const confirmBtn = document.getElementById('deletePromoConfirmBtn');

        let currentFormId = null;

        const openModal = (code, formId) => {
            codeSpan.textContent = code || '';
            currentFormId = formId;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            currentFormId = null;
        };

        document.querySelectorAll('.js-delete-promo-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const code = btn.getAttribute('data-promo-code');
                const formId = btn.getAttribute('data-form-id');
                openModal(code, formId);
            });
        });

        cancelBtn?.addEventListener('click', closeModal);

        modal?.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        confirmBtn?.addEventListener('click', () => {
            if (!currentFormId) return;
            const form = document.getElementById(currentFormId);
            if (form) form.submit();
        });
    });
</script>
@endpush
