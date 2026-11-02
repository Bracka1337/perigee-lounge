<header id="siteHeader" class="fixed top-0 left-0 w-full z-50 bg-black/70 backdrop-blur border-b border-white/10">
    @once
        @push('styles')
            <style>
                #siteHeader{max-width:100vw;transform:translateZ(0);-webkit-transform:translateZ(0);overflow:visible}
                #navToggle{position:relative;width:40px;height:40px;-webkit-tap-highlight-color:transparent}
                #navToggle span{
                    position:absolute;left:50%;width:22px;height:2px;background-color:#fff;border-radius:9999px;
                    transform:translateX(-50%);transition:transform .25s ease,opacity .25s ease
                }
                #navToggle span:nth-child(1){top:13px}
                #navToggle span:nth-child(2){top:19px}
                #navToggle span:nth-child(3){top:25px}
                #navToggle.open span:nth-child(1){transform:translate(-50%,6px) rotate(45deg)}
                #navToggle.open span:nth-child(2){opacity:0}
                #navToggle.open span:nth-child(3){transform:translate(-50%,-6px) rotate(-45deg)}

                #navMenu{
                    opacity:0;
                    transform:translateY(-6px);
                    transition:opacity .2s ease, transform .2s ease;
                    will-change:opacity,transform;
                    pointer-events:none;
                    overflow: visible !important;
                }
                #navMenu.open{
                    opacity:1;
                    transform:translateY(0);
                    pointer-events:auto;
                }

                #langMenuDesktop{z-index:70}
                #langMenuMobile{z-index:70}
            </style>
        @endpush
    @endonce

    @php
        $availableLocales = config('site.locales', ['ru']);
        $currentLocale = app()->getLocale();
        $route = request()->route();
        $routeName = $route?->getName();
        $routeParams = $route?->parameters() ?? [];
        unset($routeParams['locale'], $routeParams['view'], $routeParams['status']);
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
        <a href="{{ route('home', ['locale' => $currentLocale]) }}" class="flex items-center gap-3 min-w-0">
            <img src="{{ asset('images/logo.png') }}" class="h-6 md:h-7 shrink-0 object-contain" alt="PERIGEE">
            <div class="flex flex-col leading-tight min-w-0">
                <span class="text-[10px] tracking-[0.35em] uppercase text-white/60 truncate">{{ __('perigee-rest-name') }}</span>
                <span class="logo-font text-2xl md:text-3xl text-white drop-shadow-[0_0_8px_rgba(255,255,255,0.6)] truncate">{{ __('perigee-name') }}</span>
            </div>
        </a>

          <nav class="hidden md:flex items-center gap-6 text-xs">
                <a href="{{ route('home', ['locale' => $currentLocale]) }}"
                    class="transition tracking-[0.18em] uppercase {{ request()->routeIs('home') ? 'text-white' : 'text-white/70 hover:text-white' }}">{{ __('header-home') }}</a>
                <a href="{{ route('about', ['locale' => $currentLocale]) }}"
                    class="transition tracking-[0.18em] uppercase {{ request()->routeIs('about') ? 'text-white' : 'text-white/70 hover:text-white' }}">{{ __('header-about') }}</a>
                <a href="{{ route('menu', ['locale' => $currentLocale]) }}"
                    class="transition tracking-[0.18em] uppercase {{ request()->routeIs('menu') ? 'text-white' : 'text-white/70 hover:text-white' }}">{{ __('header-menu') }}</a>
                <a href="{{ route('contacts', ['locale' => $currentLocale]) }}"
                    class="transition tracking-[0.18em] uppercase {{ request()->routeIs('contacts') ? 'text-white' : 'text-white/70 hover:text-white' }}">{{ __('header-contacts') }}</a>

            <div class="relative ml-2">
                <button id="langToggleDesktop" type="button"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full border border-white/30 bg-white/10 text-[10px] tracking-[0.2em] uppercase text-white/80 hover:bg-white/20 transition"
                        aria-haspopup="menu" aria-expanded="false">
                    <span>{{ strtoupper($currentLocale) }}</span>
                    <svg class="w-3 h-3 text-white/70" viewBox="0 0 20 20" fill="none"><path d="M5 7l5 6 5-6" stroke="currentColor" stroke-width="1.6"/></svg>
                </button>

                <div id="langMenuDesktop"
                     class="absolute right-0 top-full mt-2 w-24 rounded-xl bg-black/95 border border-white/15 shadow-lg py-1 hidden">
                    @foreach($availableLocales as $loc)
                        @php
                            $params = array_merge(['locale' => $loc], $routeParams);
                            $url = $routeName ? route($routeName, $params) : route('home', ['locale' => $loc]);
                        @endphp
                        <a href="{{ $url }}"
                           class="block px-3 py-1 text-[11px] tracking-[0.18em] uppercase transition
                                  {{ $loc === $currentLocale ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                            {{ strtoupper($loc) }}
                        </a>
                    @endforeach
                </div>
            </div>

            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard', ['locale' => $currentLocale]) }}" class="text-white/70 hover:text-white uppercase tracking-[0.18em]">{{ __('header-admin') }}</a>
                @elseif(auth()->user()->isWorker())
                    <a href="{{ route('staff.dashboard', ['locale' => $currentLocale]) }}" class="text-white/70 hover:text-white uppercase tracking-[0.18em]">{{ __('header-staff') }}</a>
                @else
                    <a href="{{ route('dashboard', ['locale' => $currentLocale]) }}" class="text-white/70 hover:text-white uppercase tracking-[0.18em]">{{ __('header-client') }}</a>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="ml-2">
                    @csrf
                    <button type="submit"
                            class="h-9 inline-flex items-center justify-center gap-2 px-5 rounded-full
                                   border border-white/70 bg-transparent
                                   text-[10px] tracking-[0.18em] uppercase text-white
                                   hover:bg-white hover:text-black transition">
                        <x-icon category="Interface" name="Log_Out" class="w-5 h-5 shrink-0"/>
                    </button>
                </form>
            @endauth

            @guest
                <a href="{{ route('login', ['locale' => $currentLocale]) }}"
                   class="ml-4 px-4 py-2 rounded-full border border-white/80 text-[10px] tracking-[0.18em] uppercase text-white hover:bg-white hover:text-black transition">
                    {{ __('header-login') }}
                </a>
            @endguest
        </nav>

        <button id="navToggle" type="button" class="md:hidden text-white shrink-0" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>

    <div id="navMenu" hidden class="md:hidden border-t border-white/10 bg-black/90">
        <nav class="px-4 sm:px-6 py-4 flex flex-col gap-3 text-xs max-h-[calc(100svh-64px)] overflow-y-auto" style="overflow-x: visible;">
            <a data-nav-close="1" href="{{ route('home', ['locale' => $currentLocale]) }}" class="text-white/80 hover:text-white tracking-[0.18em] uppercase">{{ __('header-home') }}</a>
            <a data-nav-close="1" href="{{ route('about', ['locale' => $currentLocale]) }}" class="text-white/80 hover:text-white tracking-[0.18em] uppercase">{{ __('header-about') }}</a>
            <a data-nav-close="1" href="{{ route('menu', ['locale' => $currentLocale]) }}" class="text-white/80 hover:text-white tracking-[0.18em] uppercase">{{ __('header-menu') }}</a>
            <a data-nav-close="1" href="{{ route('contacts', ['locale' => $currentLocale]) }}" class="text-white/80 hover:text-white tracking-[0.18em] uppercase">{{ __('header-contacts') }}</a>

<div class="relative mt-2 self-start">
    <button id="langToggleMobile" type="button"
            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full border border-white/30 bg-white/10
                   text-[10px] tracking-[0.2em] uppercase text-white/80 hover:bg-white/20 transition"
            aria-haspopup="menu" aria-expanded="false">
        <span>{{ strtoupper($currentLocale) }}</span>
        <svg class="w-3 h-3 text-white/70" viewBox="0 0 20 20" fill="none">
            <path d="M5 7l5 6 5-6" stroke="currentColor" stroke-width="1.6"/>
        </svg>
    </button>

    <div id="langMenuMobile"
         class="absolute left-0 top-full mt-2 w-24 rounded-xl bg-black/95 border border-white/15 shadow-lg py-1 hidden z-[70]">
        @foreach($availableLocales as $loc)
            @php
                $params = array_merge(['locale' => $loc], $routeParams);
                $url = $routeName ? route($routeName, $params) : route('home', ['locale' => $loc]);
            @endphp
            <a href="{{ $url }}"
               class="block px-3 py-1 text-[11px] tracking-[0.18em] uppercase transition
                      {{ $loc === $currentLocale ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                {{ strtoupper($loc) }}
            </a>
        @endforeach
    </div>
</div>


            @auth
                @if(auth()->user()->isAdmin())
                    <a data-nav-close="1" href="{{ route('admin.dashboard', ['locale' => $currentLocale]) }}" class="mt-1 text-white/80 hover:text-white tracking-[0.18em] uppercase">{{ __('header-admin') }}</a>
                @elseif(auth()->user()->isWorker())
                    <a data-nav-close="1" href="{{ route('staff.dashboard', ['locale' => $currentLocale]) }}" class="mt-1 text-white/80 hover:text-white tracking-[0.18em] uppercase">{{ __('header-staff') }}</a>
                @else
                    <a data-nav-close="1" href="{{ route('dashboard', ['locale' => $currentLocale]) }}" class="mt-1 text-white/80 hover:text-white tracking-[0.18em] uppercase">{{ __('header-client') }}</a>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit"
                            data-nav-close="1"
                            class="h-10 w-full inline-flex items-center justify-center gap-2 pl-5 pr-7 rounded-full
                                   border border-white/70 bg-transparent
                                   text-[10px] tracking-[0.18em] uppercase text-white
                                   hover:bg-white hover:text-black transition">
                        <x-icon category="Interface" name="Log_Out" class="w-5 h-5 shrink-0"/>
                        <span class="leading-none">{{ __('header-logout') }}</span>
                    </button>
                </form>
            @endauth

            @guest
                <a data-nav-close="1"
                   href="{{ route('login', ['locale' => $currentLocale]) }}"
                   class="mt-2 inline-flex justify-center px-4 py-2 rounded-full border border-white/80 text-[10px] tracking-[0.18em] uppercase text-white hover:bg-white hover:text-black transition">
                    {{ __('header-login') }}
                </a>
            @endguest
        </nav>
    </div>

    @once
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const header = document.getElementById('siteHeader');
                    if (!header) return;

                    const navBtn  = header.querySelector('#navToggle');
                    const navMenu = header.querySelector('#navMenu');
                    if (!navBtn || !navMenu) return;

                    const langBtnD = header.querySelector('#langToggleDesktop');
                    const langMenuD = header.querySelector('#langMenuDesktop');
                    const langBtnM = header.querySelector('#langToggleMobile');
                    const langMenuM = header.querySelector('#langMenuMobile');

                    let t = null;

                    const closeLang = () => {
                        if (langMenuD && !langMenuD.classList.contains('hidden')) langMenuD.classList.add('hidden');
                        if (langMenuM && !langMenuM.classList.contains('hidden')) langMenuM.classList.add('hidden');
                        if (langBtnD) langBtnD.setAttribute('aria-expanded', 'false');
                        if (langBtnM) langBtnM.setAttribute('aria-expanded', 'false');
                    };

                    const openNav = () => {
                        if (t) { clearTimeout(t); t = null; }
                        navMenu.hidden = false;
                        requestAnimationFrame(() => {
                            navMenu.classList.add('open');
                            navBtn.classList.add('open');
                            navBtn.setAttribute('aria-expanded', 'true');
                        });
                    };

                    const closeNav = (instant = false) => {
                        if (t) { clearTimeout(t); t = null; }
                        navMenu.classList.remove('open');
                        navBtn.classList.remove('open');
                        navBtn.setAttribute('aria-expanded', 'false');
                        if (instant) {
                            navMenu.hidden = true;
                            return;
                        }
                        t = setTimeout(() => { navMenu.hidden = true; t = null; }, 220);
                    };

                    const toggleNav = () => (navMenu.hidden ? openNav() : closeNav(true));

                    const bindLang = (btn, menu) => {
                        if (!btn || !menu) return;
                        btn.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            const willOpen = menu.classList.contains('hidden');
                            closeLang();
                            if (willOpen) {
                                menu.classList.remove('hidden');
                                btn.setAttribute('aria-expanded', 'true');
                            }
                        });
                    };

                    navMenu.hidden = true;
                    navMenu.classList.remove('open');
                    navBtn.classList.remove('open');
                    navBtn.setAttribute('aria-expanded', 'false');
                    closeLang();

                    navBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        closeLang();
                        toggleNav();
                    });

                    navMenu.querySelectorAll('[data-nav-close="1"]').forEach(el => {
                        el.addEventListener('click', () => {
                            closeLang();
                            closeNav(true);
                        });
                    });

                    bindLang(langBtnD, langMenuD);
                    bindLang(langBtnM, langMenuM);

                    document.addEventListener('click', closeLang);

                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            closeLang();
                            closeNav(true);
                        }
                    });

                    window.addEventListener('pageshow', () => { closeLang(); closeNav(true); });
                    window.addEventListener('resize', () => {
                        closeLang();
                        if (window.innerWidth >= 768 && !navMenu.hidden) closeNav(true);
                    });
                    window.addEventListener('orientationchange', () => { closeLang(); closeNav(true); });
                });
            </script>
        @endpush
    @endonce
</header>
