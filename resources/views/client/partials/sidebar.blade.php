<aside class="min-w-0 md:w-56 md:shrink-0">
    <div class="rounded-2xl border border-line bg-surface p-2 md:p-4">
        <p class="mb-3 hidden text-xs text-ink-muted md:block">
            {{ __('client-sidebar-title') }}
        </p>

        <nav class="flex gap-1 overflow-x-auto text-sm md:block md:space-y-1">
            <a href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}"
               class="flex min-h-11 shrink-0 items-center gap-2 rounded-2xl px-3 py-2
               @if (request()->routeIs('dashboard')) bg-line border border-line-strong @else hover:bg-line @endif">
                <x-icon category="Navigation" name="House_01" class="w-5 h-5 text-ink-soft shrink-0"/>
                <span>{{ __('client-sidebar-overview') }}</span>
            </a>

            <a href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}"
               class="flex min-h-11 shrink-0 items-center gap-2 rounded-2xl px-3 py-2
               @if (request()->routeIs('profile.edit')) bg-line border border-line-strong @else hover:bg-line @endif">
                <x-icon category="User" name="User_02" class="w-5 h-5 text-ink-soft shrink-0"/>
                <span>{{ __('client-sidebar-profile') }}</span>
            </a>

            <a href="{{ route('client.reservations.index', ['locale' => app()->getLocale()]) }}"
               class="flex min-h-11 shrink-0 items-center gap-2 rounded-2xl px-3 py-2
               @if (request()->routeIs('client.reservations.*')) bg-line border border-line-strong @else hover:bg-line @endif">
                <x-icon category="Calendar" name="Calendar_Event" class="w-5 h-5 text-ink-soft shrink-0"/>
                <span>{{ __('client-sidebar-reservations') }}</span>
            </a>

            <a href="{{ route('client.orders.index', ['locale' => app()->getLocale()]) }}"
               class="flex min-h-11 shrink-0 items-center gap-2 rounded-2xl px-3 py-2
               @if (request()->routeIs('client.orders.*')) bg-line border border-line-strong @else hover:bg-line @endif">
                <x-icon category="Interface" name="Shopping_Cart_01" class="w-5 h-5 text-ink-soft shrink-0"/>
                <span>{{ __('client-sidebar-orders') }}</span>
            </a>

            <a href="{{ route('client.loyalty.show', ['locale' => app()->getLocale()]) }}"
               class="flex min-h-11 shrink-0 items-center gap-2 rounded-2xl px-3 py-2
               @if (request()->routeIs('client.loyalty.*')) bg-line border border-line-strong @else hover:bg-line @endif">
                <x-icon category="Interface" name="Star" class="w-5 h-5 text-ink-soft shrink-0"/>
                <span>{{ __('client-sidebar-loyalty') }}</span>
            </a>

            <a href="{{ route('client.promo-codes.index', ['locale' => app()->getLocale()]) }}"
               class="flex min-h-11 shrink-0 items-center gap-2 rounded-2xl px-3 py-2
               @if (request()->routeIs('client.promo-codes.*')) bg-line border border-line-strong @else hover:bg-line @endif">
                <x-icon category="Interface" name="Ticket_Voucher" class="w-5 h-5 text-ink-soft shrink-0"/>
                <span>{{ __('client-sidebar-promo-codes') }}</span>
            </a>
        </nav>
    </div>
</aside>
