<aside class="md:w-56">
    <div class="rounded-3xl border border-white/10 bg-white/5 p-4 shadow-xl shadow-black/60">
        <p class="text-[10px] uppercase tracking-[0.25em] text-white/60 mb-3">
            {{ __('client-sidebar-title') }}
        </p>

        <nav class="space-y-1 text-sm">
            <a href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}"
               class="flex items-center gap-2 px-3 py-2 rounded-2xl
               @if (request()->routeIs('dashboard')) bg-white/15 border border-white/30 @else hover:bg-white/10 @endif">
                <x-icon category="Navigation" name="House_01" class="w-5 h-5 text-white/80 shrink-0"/>
                <span>{{ __('client-sidebar-overview') }}</span>
            </a>

            <a href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}"
               class="flex items-center gap-2 px-3 py-2 rounded-2xl
               @if (request()->routeIs('profile.edit')) bg-white/15 border border-white/30 @else hover:bg-white/10 @endif">
                <x-icon category="User" name="User_02" class="w-5 h-5 text-white/80 shrink-0"/>
                <span>{{ __('client-sidebar-profile') }}</span>
            </a>

            <a href="{{ route('client.reservations.index', ['locale' => app()->getLocale()]) }}"
               class="flex items-center gap-2 px-3 py-2 rounded-2xl
               @if (request()->routeIs('client.reservations.*')) bg-white/15 border border-white/30 @else hover:bg-white/10 @endif">
                <x-icon category="Calendar" name="Calendar_Event" class="w-5 h-5 text-white/80 shrink-0"/>
                <span>{{ __('client-sidebar-reservations') }}</span>
            </a>

            <a href="{{ route('client.orders.index', ['locale' => app()->getLocale()]) }}"
               class="flex items-center gap-2 px-3 py-2 rounded-2xl
               @if (request()->routeIs('client.orders.*')) bg-white/15 border border-white/30 @else hover:bg-white/10 @endif">
                <x-icon category="Interface" name="Shopping_Cart_01" class="w-5 h-5 text-white/80 shrink-0"/>
                <span>{{ __('client-sidebar-orders') }}</span>
            </a>

            <a href="{{ route('client.loyalty.show', ['locale' => app()->getLocale()]) }}"
               class="flex items-center gap-2 px-3 py-2 rounded-2xl
               @if (request()->routeIs('client.loyalty.*')) bg-white/15 border border-white/30 @else hover:bg-white/10 @endif">
                <x-icon category="Interface" name="Star" class="w-5 h-5 text-white/80 shrink-0"/>
                <span>{{ __('client-sidebar-loyalty') }}</span>
            </a>

            <a href="{{ route('client.promo-codes.index', ['locale' => app()->getLocale()]) }}"
               class="flex items-center gap-2 px-3 py-2 rounded-2xl
               @if (request()->routeIs('client.promo-codes.*')) bg-white/15 border border-white/30 @else hover:bg-white/10 @endif">
                <x-icon category="Interface" name="Ticket_Voucher" class="w-5 h-5 text-white/80 shrink-0"/>
                <span>{{ __('client-sidebar-promo-codes') }}</span>
            </a>
        </nav>
    </div>
</aside>
