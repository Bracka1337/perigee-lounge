{{-- One-click availability switch. Sends the target state, so repeated clicks are harmless. --}}
<form method="POST" action="{{ $action }}" class="shrink-0">
    @csrf
    @method('PATCH')
    <input type="hidden" name="is_available" value="{{ $available ? '0' : '1' }}">
    <button type="submit"
            title="{{ $name }}"
            @class([
                'inline-flex min-h-11 items-center gap-1.5 rounded-full border px-3 text-xs whitespace-nowrap transition',
                'border-line-strong text-ink-soft hover:border-amber-400/70 hover:text-amber-200' => $available,
                'border-amber-400/70 text-amber-200 hover:border-ink hover:text-ink' => ! $available,
            ])>
        <span aria-hidden="true" @class(['h-2 w-2 rounded-full', 'bg-nebula' => $available, 'bg-amber-400' => ! $available])></span>
        {{ __($available ? 'staff-menu-make-unavailable' : 'staff-menu-make-available') }}
    </button>
</form>
