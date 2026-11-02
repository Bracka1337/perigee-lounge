<header class="fixed top-0 left-0 w-full z-50 bg-black/90 backdrop-blur border-b border-red-500/40 overflow-x-hidden">
    @push('styles')
        <style>
            #adminNavToggle{position:relative;width:40px;height:40px;-webkit-tap-highlight-color:transparent}
            #adminNavToggle span{
                position:absolute;left:50%;width:22px;height:2px;background:#fff;border-radius:9999px;
                transform:translateX(-50%);transition:transform .25s ease,opacity .25s ease
            }
            #adminNavToggle span:nth-child(1){top:13px}
            #adminNavToggle span:nth-child(2){top:19px}
            #adminNavToggle span:nth-child(3){top:25px}
            #adminNavToggle.open span:nth-child(1){transform:translate(-50%,6px) rotate(45deg)}
            #adminNavToggle.open span:nth-child(2){opacity:0}
            #adminNavToggle.open span:nth-child(3){transform:translate(-50%,-6px) rotate(-45deg)}

            #adminNavMenu{
                opacity:0;transform:translateY(-6px);
                transition:opacity .2s ease,transform .2s ease;
                will-change:opacity,transform;
                pointer-events:none;
            }
            #adminNavMenu.open{opacity:1;transform:translateY(0);pointer-events:auto}
        </style>
    @endpush

    <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group min-w-0">
                <img src="{{ asset('images/logo.png') }}" alt="PERIGEE logo"
                     class="h-7 w-auto object-contain group-hover:opacity-80 transition shrink-0">
                <div class="flex flex-col leading-tight min-w-0">
                    <span class="text-[10px] tracking-[0.35em] uppercase text-white/40 truncate">{{ __('perigee-rest-name') }}</span>
                    <span class="logo-font text-2xl text-white drop-shadow-[0_0_8px_rgba(255,255,255,0.6)] truncate">
                        {{ __('perigee-name') }}
                    </span>
                </div>
            </a>

            {{-- admin panel badge --}}
            {{-- <div class="hidden sm:inline-flex ml-3 px-3 py-1 rounded-full border border-red-500/60 text-[10px] tracking-[0.25em] uppercase text-red-400 bg-red-950/40">
                {{ __('header-admin') }}
            </div> --}}
        </div>

        {{-- DESKTOP --}}
        <nav class="hidden md:flex items-center gap-5 text-[11px]">
            <a href="{{ route('admin.dashboard') }}"
               class="inline-flex items-center gap-2 tracking-[0.18em] uppercase transition
               {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.dashboard.*') ? 'text-white' : 'text-white/70 hover:text-white' }}">
                <x-icon category="System" name="Window_Terminal" class="w-5 h-5 shrink-0"/>
                {{ __('admin-header-dashboard') }}
            </a>

            <a href="{{ route('admin.users.index') ?? '#' }}"
               class="inline-flex items-center gap-2 tracking-[0.18em] uppercase transition
               {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-white/60 hover:text-white' }}">
                <x-icon category="User" name="Users_Group" class="w-5 h-5 shrink-0"/>
                {{ __('admin-header-users') }}
            </a>

            <a href="{{ route('admin.tables.index') ?? '#' }}"
               class="inline-flex items-center gap-2 tracking-[0.18em] uppercase transition
               {{ request()->routeIs('admin.tables.*') ? 'text-white' : 'text-white/60 hover:text-white' }}">
                <x-icon category="System" name="Window" class="w-5 h-5 shrink-0"/>
                {{ __('admin-header-tables') }}
            </a>

            <a href="{{ route('admin.translations.index') ?? '#' }}"
               class="inline-flex items-center gap-2 tracking-[0.18em] uppercase transition
               {{ request()->routeIs('admin.translations.*') ? 'text-white' : 'text-white/60 hover:text-white' }}">
                <x-icon category="File" name="File_Edit" class="w-5 h-5 shrink-0"/>
                {{ __('admin-header-translations') }}
            </a>

            <a href="{{ route('admin.logs.index') ?? '#' }}"
               class="inline-flex items-center gap-2 tracking-[0.18em] uppercase transition
               {{ request()->routeIs('admin.logs.*') ? 'text-white' : 'text-white/60 hover:text-white' }}">
                <x-icon category="Calendar" name="Clock" class="w-5 h-5 shrink-0"/>
                {{ __('admin-header-logs') }}
            </a>

            <span class="w-px h-5 bg-white/20 mx-1"></span>

            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 tracking-[0.18em] uppercase text-xs text-white/50 hover:text-white transition">
                <x-icon category="Navigation" name="House_01" class="w-5 h-5 shrink-0"/>
                {{-- {{ __('admin-header-site') }} --}}
            </a>

            @auth
                <span class="text-[11px] text-white/40 ml-2 whitespace-nowrap">
                    {{ auth()->user()->name }}
                </span>

                <form method="POST" action="{{ route('logout') }}" class="ml-2">
                    @csrf
                    <button type="submit"
                            class="h-9 inline-flex items-center justify-center px-3 rounded-full
                                   border border-white/70 bg-transparent
                                   text-white hover:bg-white hover:text-black transition"
                            aria-label="{{ __('header-logout') }}">
                        <x-icon category="Interface" name="Log_Out" class="w-5 h-5 shrink-0"/>
                        {{-- {{ __('header-logout') }} --}}
                    </button>
                </form>
            @endauth
        </nav>

        {{-- MOBILE --}}
        <div class="md:hidden flex items-center gap-3 shrink-0">
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 text-[10px] tracking-[0.18em] uppercase text-white/60 hover:text-white transition">
                <x-icon category="Navigation" name="House_01" class="w-5 h-5 shrink-0"/>
                {{-- {{ __('admin-header-site') }} --}}
            </a>

            <button id="adminNavToggle" type="button" class="text-white" aria-label="Toggle menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    {{-- ВАЖНО: hidden по умолчанию --}}
    <div id="adminNavMenu" hidden class="md:hidden border-t border-red-500/30 bg-black/95">
        <nav class="px-6 py-4 flex flex-col gap-3 text-xs">
            <a href="{{ route('admin.dashboard') }}"
                    class="inline-flex items-center gap-2 transition tracking-[0.18em] uppercase
                    {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.dashboard.*') ? 'text-white' : 'text-white/80 hover:text-white' }}">
                <x-icon category="System" name="Window_Terminal" class="w-5 h-5 shrink-0"/>
                {{ __('admin-header-dashboard') }}
            </a>

            <a href="{{ route('admin.users.index') ?? '#' }}"
                    class="inline-flex items-center gap-2 transition tracking-[0.18em] uppercase
                    {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-white/80 hover:text-white' }}">
                <x-icon category="User" name="Users_Group" class="w-5 h-5 shrink-0"/>
                {{ __('admin-header-users') }}
            </a>

            <a href="{{ route('admin.tables.index') ?? '#' }}"
                    class="inline-flex items-center gap-2 transition tracking-[0.18em] uppercase
                    {{ request()->routeIs('admin.tables.*') ? 'text-white' : 'text-white/80 hover:text-white' }}">
                <x-icon category="System" name="Window" class="w-5 h-5 shrink-0"/>
                {{ __('admin-header-tables') }}
            </a>

            <a href="{{ route('admin.translations.index') ?? '#' }}"
                    class="inline-flex items-center gap-2 transition tracking-[0.18em] uppercase
                    {{ request()->routeIs('admin.translations.*') ? 'text-white' : 'text-white/80 hover:text-white' }}">
                <x-icon category="File" name="File_Edit" class="w-5 h-5 shrink-0"/>
                {{ __('admin-header-translations') }}
            </a>

            <a href="{{ route('admin.logs.index') ?? '#' }}"
                    class="inline-flex items-center gap-2 transition tracking-[0.18em] uppercase
                    {{ request()->routeIs('admin.logs.*') ? 'text-white' : 'text-white/80 hover:text-white' }}">
                <x-icon category="Calendar" name="Clock" class="w-5 h-5 shrink-0"/>
                {{ __('admin-header-logs') }}
            </a>

            <span class="h-px w-full bg-white/15 my-1"></span>

<a href="{{ route('home') }}"
   class="inline-flex items-center gap-2 text-white/70 hover:text-white transition tracking-[0.18em] uppercase">
    <x-icon category="Navigation" name="House_01" class="w-5 h-5 shrink-0"/>
    {{ __('admin-header-site') }}
</a>


            @auth
                <span class="mt-2 inline-flex items-center gap-2 text-[11px] text-white/40 break-words">
                    <x-icon category="User" name="User_01" class="w-5 h-5 shrink-0"/>
                    {{ auth()->user()->name }}
                </span>

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit"
                            class="h-10 w-full inline-flex items-center justify-center gap-2 pl-5 pr-7 rounded-full
                                   border border-white/70 bg-transparent
                                   text-[10px] tracking-[0.18em] uppercase text-white
                                   hover:bg-white hover:text-black transition">
                        <x-icon category="Interface" name="Log_Out" class="w-5 h-5 shrink-0"/>
                        <span class="leading-none">{{ __('header-logout') }}</span>
                    </button>
                </form>
            @endauth
        </nav>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const btn  = document.getElementById('adminNavToggle');
                const menu = document.getElementById('adminNavMenu');
                if (!btn || !menu) return;

                let t = null;

                const openMenu = () => {
                    if (t) { clearTimeout(t); t = null; }
                    menu.hidden = false;
                    requestAnimationFrame(() => {
                        menu.classList.add('open');
                        btn.classList.add('open');
                        btn.setAttribute('aria-expanded', 'true');
                    });
                };

                const closeMenu = (instant = false) => {
                    if (t) { clearTimeout(t); t = null; }
                    menu.classList.remove('open');
                    btn.classList.remove('open');
                    btn.setAttribute('aria-expanded', 'false');
                    if (instant) {
                        menu.hidden = true;
                        return;
                    }
                    t = setTimeout(() => { menu.hidden = true; t = null; }, 220);
                };

                const toggle = () => (menu.hidden ? openMenu() : closeMenu(true));

                menu.hidden = true;
                menu.classList.remove('open');
                btn.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');

                btn.addEventListener('click', toggle);
                menu.querySelectorAll('a, button').forEach(el => el.addEventListener('click', () => closeMenu(true)));

                window.addEventListener('pageshow', () => closeMenu(true));
                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 768 && !menu.hidden) closeMenu(true);
                });
                window.addEventListener('orientationchange', () => closeMenu(true));
            });
        </script>
    @endpush
</header>
