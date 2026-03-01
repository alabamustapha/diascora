<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Diascora — Connect. Exchange. Belong.</title>
    @fluxAppearance
    @vite(['resources/css/app.css'])
    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.1.0/dist/typed.umd.js"></script>
    <style>
        .typed-cursor { color: #1d3461; font-weight: 300; }
    </style>
</head>
<body class="antialiased bg-white text-zinc-900">

    {{-- Navigation --}}
    <nav
        class="fixed inset-x-0 top-0 z-50 border-b border-zinc-100 bg-white transition-all duration-300"
        x-data="{
            open: false,
            scrolled: false,
            init() {
                window.addEventListener('scroll', () => {
                    this.scrolled = window.scrollY > 20
                }, { passive: true })
            }
        }"
        :class="scrolled ? 'shadow-md' : 'shadow-none'"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                {{-- Wordmark --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <div class="flex size-8 items-center justify-center rounded-md bg-navy-800">
                        <svg class="size-5 fill-white" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="20" cy="20" r="6"/>
                            <circle cx="6" cy="10" r="4"/>
                            <circle cx="34" cy="10" r="4"/>
                            <circle cx="6" cy="30" r="4"/>
                            <circle cx="34" cy="30" r="4"/>
                            <line x1="20" y1="14" x2="6" y2="10" stroke="white" stroke-width="2" fill="none"/>
                            <line x1="20" y1="14" x2="34" y2="10" stroke="white" stroke-width="2" fill="none"/>
                            <line x1="20" y1="26" x2="6" y2="30" stroke="white" stroke-width="2" fill="none"/>
                            <line x1="20" y1="26" x2="34" y2="30" stroke="white" stroke-width="2" fill="none"/>
                            <line x1="6" y1="10" x2="6" y2="30" stroke="white" stroke-width="1.5" fill="none"/>
                            <line x1="34" y1="10" x2="34" y2="30" stroke="white" stroke-width="1.5" fill="none"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold tracking-tight text-navy-800">Diascora</span>
                </a>

                {{-- Desktop nav links --}}
                <div class="hidden items-center gap-6 md:flex">
                    <a href="{{ route('exchange.board') }}" class="text-sm font-semibold text-zinc-700 transition-colors hover:text-navy-800">Exchange</a>
                </div>

                {{-- Desktop auth links --}}
                <div class="hidden items-center gap-3 md:flex">
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="rounded-lg bg-navy-800 px-4 py-2 text-sm font-medium text-white shadow-[0_4px_12px_rgba(0,102,245,0.35)] transition-colors hover:bg-navy-700">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="px-4 py-2 text-sm font-medium text-navy-800 transition-colors hover:text-navy-600">
                            Log in
                        </a>
                        <a href="{{ route('register') }}"
                           class="rounded-lg bg-navy-800 px-4 py-2 text-sm font-medium text-white shadow-[0_4px_12px_rgba(0,102,245,0.35)] transition-colors hover:bg-navy-700">
                            Get Started
                        </a>
                    @endauth
                </div>

                {{-- Mobile hamburger --}}
                <button
                    class="flex items-center justify-center rounded-md p-2 text-zinc-500 hover:text-zinc-700 md:hidden"
                    @click="open = !open"
                    aria-label="Toggle menu"
                >
                    <svg x-show="!open" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                    <svg x-show="open" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile menu panel --}}
        <div
            x-show="open"
            x-transition:enter="transition duration-150 ease-out"
            x-transition:enter-start="-translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition duration-100 ease-in"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="-translate-y-2 opacity-0"
            class="border-t border-zinc-100 bg-white px-4 pb-4 pt-2 md:hidden"
        >
            <div class="flex flex-col gap-1">
                <a href="{{ route('exchange.board') }}" class="rounded-md px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Exchange</a>
                <div class="my-2 border-t border-zinc-100"></div>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-md bg-navy-800 px-3 py-2 text-center text-sm font-medium text-white hover:bg-navy-700">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-navy-800 px-3 py-2 text-center text-sm font-medium text-white hover:bg-navy-700">Get Started</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-[#f8f9fc]">
        <div class="mx-auto flex min-h-[85svh] max-w-7xl items-center justify-center px-4 pb-20 pt-28 text-center sm:px-6 lg:px-8">
            <div class="w-full">
                {{-- Badge --}}
                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-600">
                    🇷🇼 For diaspora communities in Rwanda
                </div>

                {{-- Headline --}}
                <h1
                    class="text-5xl font-bold leading-tight text-navy-800 sm:text-6xl lg:text-7xl"
                    style="letter-spacing: -0.04em"
                >
                    Connect with fellow<br>
                    <span id="typed-nationality"></span> in Rwanda
                </h1>

                {{-- Subtitle --}}
                <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-zinc-500 sm:text-xl">
                    Connect with trusted community members for peer-to-peer currency exchange and package delivery
                </p>

                {{-- CTAs --}}
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center rounded-xl bg-navy-800 px-8 py-3.5 text-base font-semibold text-white shadow-[0_4px_14px_rgba(0,102,245,0.4)] transition-colors hover:bg-navy-700">
                        Join Free
                    </a>
                    <a href="{{ route('exchange.board') }}"
                       class="inline-flex items-center rounded-xl border border-navy-800 px-8 py-3.5 text-base font-semibold text-navy-800 transition-colors hover:bg-navy-50">
                        Browse Requests
                    </a>
                </div>

                {{-- Community badges --}}
                <div class="mt-10 flex flex-wrap justify-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-600">🇳🇬 Nigerians in 🇷🇼 Rwanda</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-600">🇰🇪 Kenyans in 🇷🇼 Rwanda</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-600">🇹🇿 Tanzanians in 🇷🇼 Rwanda</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-600">🇺🇬 Ugandans in 🇷🇼 Rwanda</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-600">🇧🇯 Beninois in 🇷🇼 Rwanda</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-600">🇨🇲 Cameroonians in 🇷🇼 Rwanda</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="bg-white py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2
                class="mb-3 text-center text-3xl font-bold text-navy-800 sm:text-4xl"
                style="letter-spacing: -0.03em"
            >Everything your community needs</h2>
            <p class="mx-auto mb-12 max-w-xl text-center text-zinc-500">Built for diaspora communities, by people who understand the challenges of living abroad.</p>
            <div class="grid gap-6 md:grid-cols-3">
                {{-- Card 1 --}}
                <div class="rounded-3xl bg-navy-50 p-7 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-white shadow-sm">
                        <svg class="size-6 text-navy-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                        </svg>
                    </div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-emerald-600">Live rates from 7 currencies</p>
                    <h3 class="mb-2 text-lg font-semibold text-navy-800">P2P Currency Exchange</h3>
                    <p class="text-sm leading-relaxed text-zinc-500">
                        Post and find currency exchange requests within your diaspora community. No middlemen, no hidden fees — just community trust.
                    </p>
                </div>

                {{-- Card 2 --}}
                <div class="rounded-3xl bg-navy-50 p-7 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-white shadow-sm">
                        <svg class="size-6 text-navy-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                        </svg>
                    </div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-emerald-600">Contact revealed only after match</p>
                    <h3 class="mb-2 text-lg font-semibold text-navy-800">Verified Members</h3>
                    <p class="text-sm leading-relaxed text-zinc-500">
                        Admin-verified members give you confidence about who you're dealing with. Contact details revealed only after both sides agree.
                    </p>
                </div>

                {{-- Card 3 --}}
                <div class="rounded-3xl bg-navy-50 p-7 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-white shadow-sm">
                        <svg class="size-6 text-navy-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-emerald-600">Rates updated every hour</p>
                    <h3 class="mb-2 text-lg font-semibold text-navy-800">Live Exchange Rates</h3>
                    <p class="text-sm leading-relaxed text-zinc-500">
                        Compare your offered rate against the live official rate. Always know if you're getting a fair deal before you commit.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="bg-navy-50 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2
                class="mb-3 text-center text-3xl font-bold text-navy-800 sm:text-4xl"
                style="letter-spacing: -0.03em"
            >Get started in minutes</h2>
            <p class="mx-auto mb-12 max-w-xl text-center text-zinc-500">Simple, transparent, and community-driven.</p>
            <div class="grid gap-6 md:grid-cols-3">
                <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm">
                    <div class="absolute top-4 right-5 text-6xl font-black leading-none text-navy-800 opacity-[0.07]">1</div>
                    <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-gold-50">
                        <svg class="size-5 text-gold-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-navy-800" style="letter-spacing: -0.02em">Create your account</h3>
                    <p class="text-sm leading-relaxed text-zinc-500">
                        Sign up with Google or email. Takes 30 seconds — no lengthy forms, no waiting.
                    </p>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm">
                    <div class="absolute top-4 right-5 text-6xl font-black leading-none text-navy-800 opacity-[0.07]">2</div>
                    <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-gold-50">
                        <svg class="size-5 text-gold-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-navy-800" style="letter-spacing: -0.02em">Post or browse requests</h3>
                    <p class="text-sm leading-relaxed text-zinc-500">
                        List what you want to exchange, or find an existing request that matches your needs. Compare rates in real time.
                    </p>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm">
                    <div class="absolute top-4 right-5 text-6xl font-black leading-none text-navy-800 opacity-[0.07]">3</div>
                    <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-gold-50">
                        <svg class="size-5 text-gold-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-navy-800" style="letter-spacing: -0.02em">Match and exchange</h3>
                    <p class="text-sm leading-relaxed text-zinc-500">
                        Express interest, accept a peer, and get each other's contact details. Complete the exchange your way.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Community strip --}}
    <section class="border-y border-zinc-100 bg-white py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="mb-6 text-center text-xs font-semibold uppercase tracking-widest text-zinc-400">Serving 6 diaspora communities across Rwanda</p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-zinc-50 px-4 py-1.5 text-sm font-medium text-zinc-600">🇳🇬 Nigerians in 🇷🇼 Rwanda</span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-zinc-50 px-4 py-1.5 text-sm font-medium text-zinc-600">🇰🇪 Kenyans in 🇷🇼 Rwanda</span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-zinc-50 px-4 py-1.5 text-sm font-medium text-zinc-600">🇹🇿 Tanzanians in 🇷🇼 Rwanda</span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-zinc-50 px-4 py-1.5 text-sm font-medium text-zinc-600">🇺🇬 Ugandans in 🇷🇼 Rwanda</span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-zinc-50 px-4 py-1.5 text-sm font-medium text-zinc-600">🇧🇯 Beninois in 🇷🇼 Rwanda</span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-zinc-50 px-4 py-1.5 text-sm font-medium text-zinc-600">🇨🇲 Cameroonians in 🇷🇼 Rwanda</span>
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="bg-gradient-to-br from-navy-700 to-navy-950 py-20">
        <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
            <h2
                class="mb-4 text-3xl font-bold text-white sm:text-4xl"
                style="letter-spacing: -0.03em"
            >
                Ready to find your community?
            </h2>
            <p class="mx-auto mb-8 max-w-xl text-lg text-navy-100">
                Join diaspora members already exchanging on Diascora.
            </p>
            <a href="{{ route('register') }}"
               class="inline-flex items-center rounded-xl bg-navy-800 px-8 py-4 text-base font-semibold text-white shadow-[0_4px_14px_rgba(0,102,245,0.4)] transition-colors hover:bg-navy-700">
                Get Started Free
            </a>
            <div class="mt-4">
                <a href="#how-it-works" class="text-sm text-navy-300 transition-colors hover:text-white">See how it works ↓</a>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-zinc-200 bg-white pb-8 pt-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 sm:grid-cols-3">
                {{-- Brand column --}}
                <div>
                    <a href="{{ route('home') }}" class="mb-4 flex items-center gap-2">
                        <div class="flex size-8 items-center justify-center rounded-md bg-navy-800">
                            <svg class="size-5 fill-white" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="20" cy="20" r="6"/>
                                <circle cx="6" cy="10" r="4"/>
                                <circle cx="34" cy="10" r="4"/>
                                <circle cx="6" cy="30" r="4"/>
                                <circle cx="34" cy="30" r="4"/>
                                <line x1="20" y1="14" x2="6" y2="10" stroke="white" stroke-width="2" fill="none"/>
                                <line x1="20" y1="14" x2="34" y2="10" stroke="white" stroke-width="2" fill="none"/>
                                <line x1="20" y1="26" x2="6" y2="30" stroke="white" stroke-width="2" fill="none"/>
                                <line x1="20" y1="26" x2="34" y2="30" stroke="white" stroke-width="2" fill="none"/>
                                <line x1="6" y1="10" x2="6" y2="30" stroke="white" stroke-width="1.5" fill="none"/>
                                <line x1="34" y1="10" x2="34" y2="30" stroke="white" stroke-width="1.5" fill="none"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold tracking-tight text-navy-800">Diascora</span>
                    </a>
                    <p class="max-w-xs text-sm leading-relaxed text-zinc-500">The exchange platform for diaspora communities in Rwanda.</p>
                </div>

                {{-- Exchange column --}}
                <div>
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-widest text-zinc-400">Exchange</h3>
                    <ul class="flex flex-col gap-2.5">
                        <li><a href="{{ route('exchange.board') }}" class="text-sm text-zinc-600 transition-colors hover:text-zinc-900">Browse Exchange</a></li>
                        <li><a href="{{ route('login') }}" class="text-sm text-zinc-600 transition-colors hover:text-zinc-900">My Requests</a></li>
                    </ul>
                </div>

                {{-- Account column --}}
                <div>
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-widest text-zinc-400">Account</h3>
                    <ul class="flex flex-col gap-2.5">
                        <li><a href="{{ route('login') }}" class="text-sm text-zinc-600 transition-colors hover:text-zinc-900">Log in</a></li>
                        <li><a href="{{ route('register') }}" class="text-sm text-zinc-600 transition-colors hover:text-zinc-900">Register</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 border-t border-zinc-100 pt-6">
                <p class="text-xs text-zinc-400">© 2026 Diascora. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        new Typed('#typed-nationality', {
            strings: ['Nigerians', 'Kenyans', 'Tanzanians', 'Ugandans', 'Beninois', 'Cameroonians'],
            typeSpeed: 75,
            backSpeed: 45,
            backDelay: 1800,
            loop: true,
            showCursor: true,
            cursorChar: '|',
        });
    </script>
</body>
</html>
