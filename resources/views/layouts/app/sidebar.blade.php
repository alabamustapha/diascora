<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-900" data-push-enabled="true">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-100 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header class="py-5 px-4">
                <x-app-logo :sidebar="true" href="{{ route('home') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="px-3 gap-6">
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Exchange')" class="grid">
                    <flux:sidebar.item icon="arrows-right-left" :href="route('exchange.board')" :current="request()->routeIs('exchange.board')" wire:navigate>
                        {{ __('Browse Exchange') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('exchange.my-requests')" :current="request()->routeIs('exchange.my-requests')" wire:navigate>
                        {{ __('My Requests') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Delivery')" class="grid">
                    <flux:sidebar.item icon="archive-box" :href="route('delivery.board')" :current="request()->routeIs('delivery.board')" wire:navigate>
                        {{ __('Browse Deliveries') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('delivery.my-requests')" :current="request()->routeIs('delivery.my-requests')" wire:navigate>
                        {{ __('My Requests') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <div class="flex items-center gap-1">
                <livewire:notification-bell />

                <div x-data>
                    <flux:button
                        variant="ghost"
                        square
                        x-on:click="$flux.appearance = $flux.dark ? 'light' : 'dark'"
                        aria-label="Toggle appearance"
                    >
                        <flux:icon x-show="$flux.dark" name="sun" variant="outline" class="size-5" />
                        <flux:icon x-show="!$flux.dark" name="moon" variant="outline" class="size-5" />
                    </flux:button>
                </div>
            </div>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden border-b border-zinc-100 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <livewire:notification-bell />

            <div x-data>
                <flux:button
                    variant="ghost"
                    square
                    x-on:click="$flux.appearance = $flux.dark ? 'light' : 'dark'"
                    aria-label="Toggle appearance"
                >
                    <flux:icon x-show="$flux.dark" name="sun" variant="outline" class="size-5" />
                    <flux:icon x-show="!$flux.dark" name="moon" variant="outline" class="size-5" />
                </flux:button>
            </div>

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
