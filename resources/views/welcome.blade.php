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
                    <a href="#" class="text-sm font-semibold text-zinc-700 transition-colors hover:text-navy-800">Packages</a>
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
                <a href="#" class="rounded-md px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Packages</a>
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
                    For diaspora communities in Rwanda
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
                {{-- <div class="mt-10 flex flex-wrap justify-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-600">🇳🇬 Nigerians in 🇷🇼 Rwanda</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-600">🇰🇪 Kenyans in 🇷🇼 Rwanda</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-600">🇹🇿 Tanzanians in 🇷🇼 Rwanda</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-600">🇺🇬 Ugandans in 🇷🇼 Rwanda</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-600">🇧🇯 Beninois in 🇷🇼 Rwanda</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-600">🇨🇲 Cameroonians in 🇷🇼 Rwanda</span>
                </div> --}}
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
                {{-- Card 1: P2P Exchange --}}
                <div class="relative flex min-h-[340px] flex-col justify-between overflow-hidden rounded-3xl bg-navy-800 p-7 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -bottom-6 -right-6 size-40 animate-float text-white opacity-10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                        </svg>
                    </div>
                    <div>
                        <div class="mb-5 flex size-12 items-center justify-center rounded-xl bg-white/20">
                            <svg class="size-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                            </svg>
                        </div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-white/60">Live rates · No middlemen</p>
                        <h3 class="mb-3 text-xl font-bold text-white">P2P Currency Exchange</h3>
                        <p class="text-sm leading-relaxed text-white/70">
                            Post and find exchange requests within your diaspora community. No fees, just community trust.
                        </p>
                    </div>
                    <a href="{{ route('exchange.board') }}" class="mt-6 inline-flex w-fit items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-navy-800 transition-colors hover:bg-white/90">
                        Browse Exchange
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                </div>

                {{-- Card 2: P2P Package Delivery --}}
                <div class="relative flex min-h-[340px] flex-col justify-between overflow-hidden rounded-3xl bg-violet-700 p-7 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -bottom-6 -right-6 size-40 animate-float text-white opacity-10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                        </svg>
                    </div>
                    <div>
                        <div class="mb-5 flex size-12 items-center justify-center rounded-xl bg-white/20">
                            <svg class="size-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                            </svg>
                        </div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-white/60">Community couriers · Door to door</p>
                        <h3 class="mb-3 text-xl font-bold text-white">P2P Package Delivery</h3>
                        <p class="text-sm leading-relaxed text-white/70">
                            Travelling between countries? Help a neighbour deliver a document or small package — or find someone already on your route.
                        </p>
                    </div>
                    <a href="{{ route('register') }}" class="mt-6 inline-flex w-fit items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-violet-700 transition-colors hover:bg-white/90">
                        Get Started
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                </div>

                {{-- Card 3: Community Marketplace --}}
                <div class="relative flex min-h-[340px] flex-col justify-between overflow-hidden rounded-3xl bg-gold-600 p-7 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -bottom-6 -right-6 size-40 animate-float text-white opacity-10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="mb-5 flex size-12 items-center justify-center rounded-xl bg-white/20">
                            <svg class="size-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                            </svg>
                        </div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-white/60">Buy · Sell · Trade</p>
                        <h3 class="mb-3 text-xl font-bold text-white">Community Marketplace</h3>
                        <p class="text-sm leading-relaxed text-white/70">
                            Sell items you no longer need, or find goods not readily available in your new country.
                        </p>
                    </div>
                    <div class="mt-6">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold text-white/80">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            Coming Soon
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="bg-[#f8f9fc] py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2
                class="mb-3 text-center text-3xl font-bold text-navy-800 sm:text-4xl"
                style="letter-spacing: -0.03em"
            >Get started in minutes</h2>
            <p class="mx-auto mb-12 max-w-xl text-center text-zinc-500">Simple, transparent, and community-driven.</p>
            <div class="grid gap-6 md:grid-cols-3">
                {{-- Step 1 --}}
                <div class="relative flex min-h-[320px] flex-col justify-between overflow-hidden rounded-3xl bg-white p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <div class="absolute -bottom-4 -right-2 select-none text-[9rem] font-black leading-none text-navy-800 opacity-[0.04]">1</div>
                    <div>
                        <p class="mb-5 text-xs font-semibold uppercase tracking-widest text-navy-800/40">Step 01</p>
                        <div class="mb-5 flex size-12 items-center justify-center rounded-xl bg-navy-800">
                            <svg class="size-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-navy-800" style="letter-spacing: -0.02em">Create your account</h3>
                        <p class="text-sm leading-relaxed text-zinc-500">
                            Sign up with Google or email. Takes 30 seconds — no lengthy forms, no waiting.
                        </p>
                    </div>
                    <a href="{{ route('register') }}" class="mt-6 inline-flex w-fit items-center gap-2 text-sm font-semibold text-navy-800 transition-colors hover:text-navy-600">
                        Get started free
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                </div>

                {{-- Step 2 --}}
                <div class="relative flex min-h-[320px] flex-col justify-between overflow-hidden rounded-3xl bg-white p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <div class="absolute -bottom-4 -right-2 select-none text-[9rem] font-black leading-none text-navy-800 opacity-[0.04]">2</div>
                    <div>
                        <p class="mb-5 text-xs font-semibold uppercase tracking-widest text-navy-800/40">Step 02</p>
                        <div class="mb-5 flex size-12 items-center justify-center rounded-xl bg-navy-800">
                            <svg class="size-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-navy-800" style="letter-spacing: -0.02em">Post or browse requests</h3>
                        <p class="text-sm leading-relaxed text-zinc-500">
                            List what you want to exchange, or find an existing request that matches your needs. Compare rates in real time.
                        </p>
                    </div>
                    <a href="{{ route('exchange.board') }}" class="mt-6 inline-flex w-fit items-center gap-2 text-sm font-semibold text-navy-800 transition-colors hover:text-navy-600">
                        Browse exchange
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                </div>

                {{-- Step 3 --}}
                <div class="relative flex min-h-[320px] flex-col justify-between overflow-hidden rounded-3xl bg-navy-800 p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -bottom-4 -right-2 select-none text-[9rem] font-black leading-none text-white opacity-[0.06]">3</div>
                    <div>
                        <p class="mb-5 text-xs font-semibold uppercase tracking-widest text-white/50">Step 03</p>
                        <div class="mb-5 flex size-12 items-center justify-center rounded-xl bg-white/20">
                            <svg class="size-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white" style="letter-spacing: -0.02em">Match and exchange</h3>
                        <p class="text-sm leading-relaxed text-white/70">
                            Express interest, accept a peer, and get each other's contact details. Complete the exchange your way.
                        </p>
                    </div>
                    <a href="{{ route('register') }}" class="mt-6 inline-flex w-fit items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-navy-800 transition-colors hover:bg-white/90">
                        Join Free
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Community strip --}}
    <section class="border-y border-zinc-100 bg-white py-16">
        <p class="mb-8 text-center text-xs font-semibold uppercase tracking-widest text-zinc-400">Serving diaspora communities across Rwanda</p>
        <div
            class="overflow-hidden py-4"
            style="mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent)"
        >
            <div class="flex w-max animate-marquee gap-5 px-5">
                {{-- Original set --}}
                <a href="{{ route('exchange.board') }}" class="flex w-44 flex-shrink-0 flex-col items-center gap-3 rounded-2xl border border-zinc-100 bg-zinc-50 p-6 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <span class="fi fi-ng" style="font-size: 3.5rem; line-height: 1; border-radius: 6px;"></span>
                    <div>
                        <p class="text-sm font-bold text-navy-800">Nigerians</p>
                        <p class="text-xs text-zinc-400">in Rwanda</p>
                    </div>
                </a>
                <a href="{{ route('exchange.board') }}" class="flex w-44 flex-shrink-0 flex-col items-center gap-3 rounded-2xl border border-zinc-100 bg-zinc-50 p-6 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <span class="fi fi-ke" style="font-size: 3.5rem; line-height: 1; border-radius: 6px;"></span>
                    <div>
                        <p class="text-sm font-bold text-navy-800">Kenyans</p>
                        <p class="text-xs text-zinc-400">in Rwanda</p>
                    </div>
                </a>
                <a href="{{ route('exchange.board') }}" class="flex w-44 flex-shrink-0 flex-col items-center gap-3 rounded-2xl border border-zinc-100 bg-zinc-50 p-6 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <span class="fi fi-tz" style="font-size: 3.5rem; line-height: 1; border-radius: 6px;"></span>
                    <div>
                        <p class="text-sm font-bold text-navy-800">Tanzanians</p>
                        <p class="text-xs text-zinc-400">in Rwanda</p>
                    </div>
                </a>
                <a href="{{ route('exchange.board') }}" class="flex w-44 flex-shrink-0 flex-col items-center gap-3 rounded-2xl border border-zinc-100 bg-zinc-50 p-6 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <span class="fi fi-ug" style="font-size: 3.5rem; line-height: 1; border-radius: 6px;"></span>
                    <div>
                        <p class="text-sm font-bold text-navy-800">Ugandans</p>
                        <p class="text-xs text-zinc-400">in Rwanda</p>
                    </div>
                </a>
                <a href="{{ route('exchange.board') }}" class="flex w-44 flex-shrink-0 flex-col items-center gap-3 rounded-2xl border border-zinc-100 bg-zinc-50 p-6 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <span class="fi fi-bj" style="font-size: 3.5rem; line-height: 1; border-radius: 6px;"></span>
                    <div>
                        <p class="text-sm font-bold text-navy-800">Beninois</p>
                        <p class="text-xs text-zinc-400">in Rwanda</p>
                    </div>
                </a>
                <a href="{{ route('exchange.board') }}" class="flex w-44 flex-shrink-0 flex-col items-center gap-3 rounded-2xl border border-zinc-100 bg-zinc-50 p-6 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <span class="fi fi-cm" style="font-size: 3.5rem; line-height: 1; border-radius: 6px;"></span>
                    <div>
                        <p class="text-sm font-bold text-navy-800">Cameroonians</p>
                        <p class="text-xs text-zinc-400">in Rwanda</p>
                    </div>
                </a>
                {{-- Duplicate set for seamless loop --}}
                <a href="{{ route('exchange.board') }}" class="flex w-44 flex-shrink-0 flex-col items-center gap-3 rounded-2xl border border-zinc-100 bg-zinc-50 p-6 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <span class="fi fi-ng" style="font-size: 3.5rem; line-height: 1; border-radius: 6px;"></span>
                    <div>
                        <p class="text-sm font-bold text-navy-800">Nigerians</p>
                        <p class="text-xs text-zinc-400">in Rwanda</p>
                    </div>
                </a>
                <a href="{{ route('exchange.board') }}" class="flex w-44 flex-shrink-0 flex-col items-center gap-3 rounded-2xl border border-zinc-100 bg-zinc-50 p-6 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <span class="fi fi-ke" style="font-size: 3.5rem; line-height: 1; border-radius: 6px;"></span>
                    <div>
                        <p class="text-sm font-bold text-navy-800">Kenyans</p>
                        <p class="text-xs text-zinc-400">in Rwanda</p>
                    </div>
                </a>
                <a href="{{ route('exchange.board') }}" class="flex w-44 flex-shrink-0 flex-col items-center gap-3 rounded-2xl border border-zinc-100 bg-zinc-50 p-6 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <span class="fi fi-tz" style="font-size: 3.5rem; line-height: 1; border-radius: 6px;"></span>
                    <div>
                        <p class="text-sm font-bold text-navy-800">Tanzanians</p>
                        <p class="text-xs text-zinc-400">in Rwanda</p>
                    </div>
                </a>
                <a href="{{ route('exchange.board') }}" class="flex w-44 flex-shrink-0 flex-col items-center gap-3 rounded-2xl border border-zinc-100 bg-zinc-50 p-6 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <span class="fi fi-ug" style="font-size: 3.5rem; line-height: 1; border-radius: 6px;"></span>
                    <div>
                        <p class="text-sm font-bold text-navy-800">Ugandans</p>
                        <p class="text-xs text-zinc-400">in Rwanda</p>
                    </div>
                </a>
                <a href="{{ route('exchange.board') }}" class="flex w-44 flex-shrink-0 flex-col items-center gap-3 rounded-2xl border border-zinc-100 bg-zinc-50 p-6 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <span class="fi fi-bj" style="font-size: 3.5rem; line-height: 1; border-radius: 6px;"></span>
                    <div>
                        <p class="text-sm font-bold text-navy-800">Beninois</p>
                        <p class="text-xs text-zinc-400">in Rwanda</p>
                    </div>
                </a>
                <a href="{{ route('exchange.board') }}" class="flex w-44 flex-shrink-0 flex-col items-center gap-3 rounded-2xl border border-zinc-100 bg-zinc-50 p-6 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <span class="fi fi-cm" style="font-size: 3.5rem; line-height: 1; border-radius: 6px;"></span>
                    <div>
                        <p class="text-sm font-bold text-navy-800">Cameroonians</p>
                        <p class="text-xs text-zinc-400">in Rwanda</p>
                    </div>
                </a>
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
