<header class="fixed top-0 left-0 w-full z-50 bg-black/90 backdrop-blur border-b border-white/10 overflow-x-hidden">
    @push('styles')
        <style>
            #staffNavToggle { position: relative; width: 40px; height: 40px; -webkit-tap-highlight-color: transparent; }
            #staffNavToggle span {
                position: absolute; left: 50%; width: 22px; height: 2px; background-color: #ffffff;
                border-radius: 9999px; transform: translateX(-50%);
                transition: transform .25s ease, opacity .25s ease;
            }
            #staffNavToggle span:nth-child(1) { top: 13px; }
            #staffNavToggle span:nth-child(2) { top: 19px; }
            #staffNavToggle span:nth-child(3) { top: 25px; }

            #staffNavToggle.open span:nth-child(1) { transform: translate(-50%, 6px) rotate(45deg); }
            #staffNavToggle.open span:nth-child(2) { opacity: 0; }
            #staffNavToggle.open span:nth-child(3) { transform: translate(-50%, -6px) rotate(-45deg); }

            /* Анимация только когда menu НЕ hidden */
            #staffNavMenu {
                opacity: 0;
                transform: translateY(-6px);
                transition: opacity .2s ease, transform .2s ease;
                will-change: opacity, transform;
                pointer-events: none;
            }
            #staffNavMenu.open {
                opacity: 1;
                transform: translateY(0);
                pointer-events: auto;
            }
        </style>
    @endpush

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('staff.dashboard') }}" class="flex items-center gap-3 group min-w-0">
                <img src="{{ asset('images/logo.png') }}" alt="PERIGEE logo"
                     class="h-7 w-auto object-contain group-hover:opacity-80 transition shrink-0">
                <div class="flex flex-col leading-tight min-w-0">
                    <span class="text-[10px] tracking-[0.25em] uppercase text-white/40">lounge</span>
                    <span class="logo-font text-xl text-white truncate">PERIGEE</span>
                </div>
            </a>

            {{-- badge скрыт --}}
            {{-- <div class="hidden sm:inline-flex ml-3 px-3 py-1 rounded-full border border-white/20 text-[10px] tracking-[0.22em] uppercase text-white/70">
                {{ __('staff-header-badge') }}
            </div> --}}
        </div>

        {{-- DESKTOP --}}
        <nav class="hidden md:flex items-center gap-5 text-[11px]">
            <a href="{{ route('staff.dashboard') }}"
               class="inline-flex items-center gap-2 tracking-[0.18em] uppercase
              {{ request()->routeIs('staff.dashboard') || request()->routeIs('staff.dashboard.*')
                  ? 'text-white'
                  : 'text-white/70 hover:text-white' }}
              transition"> <x-icon category="System" name="Window_Terminal" class="w-5 h-5"/>
                {{ __('staff-header-dashboard-link') }}
            </a>

            <a href="{{ route('staff.menu-categories.index') }}"
               class="inline-flex items-center gap-2 tracking-[0.18em] uppercase
              {{ request()->routeIs('staff.menu-categories.*') || request()->routeIs('staff.menu-items.*')
                    ? 'text-white'
                    : 'text-white/70 hover:text-white' }}
              transition">
                <x-icon category="Environment" name="Cupcake" class="w-5 h-5"/>
                {{ __('staff-header-menu-link') }}
            </a>

            <a href="{{ route('staff.reservations.index') }}"
               class="inline-flex items-center gap-2 tracking-[0.18em] uppercase
                {{ request()->routeIs('staff.reservations.*')
                        ? 'text-white'
                        : 'text-white/70 hover:text-white' }}
                transition">
                <x-icon category="Calendar" name="Calendar_Days" class="w-5 h-5"/>
                {{ __('staff-header-reservations-link') }}
            </a>

            <a href="{{ route('staff.orders.index') }}"
               class="inline-flex items-center gap-2 tracking-[0.18em] uppercase
                {{ request()->routeIs('staff.orders.*')
                        ? 'text-white'
                        : 'text-white/70 hover:text-white' }}
                transition">
                <x-icon category="Interface" name="Book" class="w-5 h-5"/>
                {{ __('staff-header-orders-link') }}
            </a>

            <a href="{{ route('staff.promo-codes.index') }}"
               class="inline-flex items-center gap-2 tracking-[0.18em] uppercase
                {{ request()->routeIs('staff.promo-codes.*')
                        ? 'text-white'
                        : 'text-white/70 hover:text-white' }}
                transition">
                <x-icon category="Interface" name="Ticket_Voucher" class="w-5 h-5"/>
                {{ __('staff-header-promo-codes-link') }}
            </a>

            <span class="w-px h-5 bg-white/20 mx-1"></span>

            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 tracking-[0.18em] uppercase text-xs text-white/50 hover:text-white transition">
                <x-icon category="Navigation" name="House_01" class="w-5 h-5"/>
                {{-- {{ __('staff-header-to-site-link') }} --}}
            </a>

            @auth
                <span class="text-[11px] text-white/40 ml-2 whitespace-nowrap">
                    {{ auth()->user()->name ?? __('staff-header-employee-fallback') }}
                </span>

                <form method="POST" action="{{ route('logout') }}" class="ml-2">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center px-3 py-1.5 rounded-full border border-white/60
                                   text-[10px] uppercase tracking-[0.18em] text-white hover:bg-white hover:text-black transition">
                        <x-icon category="Interface" name="Log_Out" class="w-5 h-5"/>
                        {{-- {{ __('staff-header-logout') }} --}}
                    </button>
                </form>
            @endauth
        </nav>

        {{-- MOBILE --}}
        <div class="md:hidden flex items-center gap-2 shrink-0">
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 text-[10px] tracking-[0.18em] uppercase text-white/60 hover:text-white transition">
                <x-icon category="Navigation" name="House_01" class="w-5 h-5"/>
                {{-- {{ __('staff-header-to-site-link') }} --}}
            </a>

            <button id="staffNavToggle" type="button" class="text-white" aria-label="Toggle menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    {{-- ВАЖНО: hidden по умолчанию, чтобы не мигало --}}
    <div id="staffNavMenu" hidden class="md:hidden border-t border-white/10 bg-black/95">
        <nav class="px-4 py-3 flex flex-col gap-3 text-xs">
            <a href="{{ route('staff.dashboard') }}"
               class="inline-flex items-center gap-2 text-white/80 hover:text-white transition tracking-[0.18em] uppercase">
                <x-icon category="System" name="Window_Terminal" class="w-5 h-5"/>
                {{ __('staff-header-dashboard-link') }}
            </a>

            <a href="{{ route('staff.menu-categories.index') }}"
               class="inline-flex items-center gap-2 text-white/80 hover:text-white transition tracking-[0.18em] uppercase">
                <x-icon category="Environment" name="Cupcake" class="w-5 h-5"/>
                {{ __('staff-header-menu-link') }}
            </a>

            <a href="{{ route('staff.reservations.index') }}"
               class="inline-flex items-center gap-2 text-white/80 hover:text-white transition tracking-[0.18em] uppercase">
                <x-icon category="Calendar" name="Calendar_Days" class="w-5 h-5"/>
                {{ __('staff-header-reservations-link') }}
            </a>

            <a href="{{ route('staff.orders.index') }}"
               class="inline-flex items-center gap-2 text-white/80 hover:text-white transition tracking-[0.18em] uppercase">
                <x-icon category="Interface" name="Book" class="w-5 h-5"/>
                {{ __('staff-header-orders-link') }}
            </a>

            <a href="{{ route('staff.promo-codes.index') }}"
               class="inline-flex items-center gap-2 text-white/80 hover:text-white transition tracking-[0.18em] uppercase">
                <x-icon category="Interface" name="Ticket_Voucher" class="w-5 h-5"/>
                {{ __('staff-header-promo-codes-link') }}
            </a>

            <span class="h-px w-full bg-white/15 my-1"></span>

            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 text-white/70 hover:text-white transition tracking-[0.18em] uppercase">
                <x-icon category="Navigation" name="House_01" class="w-5 h-5"/>
                {{ __('staff-header-to-site-link') }}
            </a>

            @auth
<span class="mt-2 inline-flex items-center gap-2 text-[11px] text-white/40 break-words">
    <x-icon category="User" name="User_01" class="w-5 h-5 shrink-0"/>
    {{ auth()->user()->name ?? __('staff-header-employee-fallback') }}
</span>



                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full
                                   border border-white/50 bg-white/5
                                   text-[10px] tracking-[0.18em] uppercase text-white
                                   hover:bg-white hover:text-black hover:border-white transition">
                        <x-icon category="Interface" name="Log_Out" class="w-5 h-5"/>
                        {{ __('staff-header-logout') }}
                    </button>
                </form>
            @endauth
        </nav>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const btn  = document.getElementById('staffNavToggle');
                const menu = document.getElementById('staffNavMenu');
                if (!btn || !menu) return;

                // жёстко закрыто на старте (главное: hidden=true)
                menu.hidden = true;
                menu.classList.remove('open');
                btn.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');

                const openMenu = () => {
                    menu.hidden = false;
                    requestAnimationFrame(() => {
                        menu.classList.add('open');
                        btn.classList.add('open');
                        btn.setAttribute('aria-expanded', 'true');
                    });
                };

                const closeMenu = (instant = false) => {
                    menu.classList.remove('open');
                    btn.classList.remove('open');
                    btn.setAttribute('aria-expanded', 'false');
                    if (instant) {
                        menu.hidden = true;
                        return;
                    }
                    setTimeout(() => { menu.hidden = true; }, 220);
                };

                const toggle = () => menu.hidden ? openMenu() : closeMenu(true);

                btn.addEventListener('click', toggle);
                menu.querySelectorAll('a, button').forEach(el => el.addEventListener('click', () => closeMenu(true)));

                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 768 && !menu.hidden) closeMenu();
                });
            });
        </script>
    @endpush
</header>
