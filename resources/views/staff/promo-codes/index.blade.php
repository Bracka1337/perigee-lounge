@extends('layouts.staff')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="logo-font text-3xl md:text-4xl mb-2">
            {{ __('staff-promo-title') }}
        </h1>
        <p class="text-sm text-ink-muted">
            {{ __('staff-promo-subtitle') }}
        </p>
    </div>

<a href="{{ route('staff.promo-codes.create', ['locale' => app()->getLocale()]) }}"
   class="shrink-0 md:self-center
          inline-flex items-center justify-center gap-2
          h-10 px-4
          rounded-full border border-line-strong
          text-xs
          text-ink hover:bg-ink hover:text-on-nebula transition">
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
            <div class="rounded-2xl bg-ground/40 border border-line px-4 py-3 text-xs text-ink-soft flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <div class="text-sm text-ink">
                        {{ $promo->public_code ?? $promo->code }}
                    </div>
                    <div class="text-xs text-ink-muted mt-1">
                        {{ __('staff-promo-discount-type-'.$promo->discount_type, ['value' => $promo->discount_value]) }}
                        · {{ __('staff-promo-scope-'.$promo->scope) }}
                    </div>
                    <div class="text-xs text-ink-muted mt-1">
                        {{ __('staff-promo-uses-label') }}:
                        @if($promo->max_uses)
                            {{ $promo->used_count }} / {{ $promo->max_uses }}
                        @else
                            {{ $promo->used_count }} / ∞
                        @endif
                    </div>
                    <div class="text-xs text-ink-muted mt-1">
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
                    <span class="px-3 py-1 rounded-full border text-xs
                        {{ $promo->is_active ? 'border-emerald-400/70 text-emerald-200' : 'border-line-strong text-ink-muted' }}">
                        {{ $promo->is_active ? __('staff-promo-badge-active') : __('staff-promo-badge-inactive') }}
                    </span>

                    <a href="{{ route('staff.promo-codes.edit', ['locale' => app()->getLocale(), 'promo_code' => $promo]) }}"
                       class="px-3 py-1 rounded-full border border-line-strong text-xs text-ink hover:bg-ink hover:text-on-nebula transition">
                        {{-- {{ __('staff-promo-edit-button') }} --}}
                        <x-icon category="Edit" name="Edit_Pencil_Line_01" class="w-5 h-5"/>
                    </a>

                    <form method="POST"
                          id="delete-promo-{{ $promo->id }}"
                          action="{{ route('staff.promo-codes.destroy', ['locale' => app()->getLocale(), 'promo_code' => $promo]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                                class="px-3 py-1 rounded-full border border-red-500/70 text-xs text-red-300 hover:bg-red-500/20 hover:text-red-100 transition js-delete-promo-btn"
                                data-promo-code="{{ $promo->public_code ?? $promo->code }}"
                                data-form-id="delete-promo-{{ $promo->id }}">
                            {{-- {{ __('staff-promo-delete-button') }} --}}
                            <x-icon category="Interface" name="Trash_Empty" class="w-5 h-5"/>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-ink-muted">
                {{ __('staff-promo-empty') }}
            </p>
        @endforelse
    </div>

    <div id="deletePromoModal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-ground/70 backdrop-blur">
        <div class="max-w-md w-full mx-4 bg-ground/95 border border-red-500/50 rounded-2xl p-6">
            <div class="mb-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full border border-red-500/70 flex items-center justify-center">
                    <span class="text-red-400 text-lg">!</span>
                </div>
                <h2 class="text-base md:text-lg font-semibold text-ink tracking-wide">
                    {{ __('staff-promo-delete-modal-title') }}
                </h2>
            </div>

            <p class="text-sm text-ink-muted mb-4">
                {{ __('staff-promo-delete-modal-text-prefix') }}
                <span id="deletePromoCode" class="font-semibold text-ink"></span>?
            </p>
            <p class="text-xs text-ink-muted mb-5">
                {{ __('staff-promo-delete-modal-text-note') }}
            </p>

            <div class="flex flex-wrap justify-end gap-3">
                <button type="button"
                        id="deletePromoCancelBtn"
                        class="px-5 py-2 rounded-full border border-line-strong
                               text-xs text-ink-muted
                               hover:text-ink hover:border-line-strong hover:bg-surface transition">
                    {{ __('staff-promo-delete-modal-cancel') }}
                </button>
                <button type="button"
                        id="deletePromoConfirmBtn"
                        class="px-5 py-2 rounded-full border border-red-500/80 bg-red-600/90
                               text-xs text-ink
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
