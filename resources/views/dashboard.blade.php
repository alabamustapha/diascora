<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        {{-- Welcome --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Welcome back, :name!', ['name' => auth()->user()->name]) }}</flux:heading>
                <flux:text class="mt-1">{{ __("Here's what's happening in your community today.") }}</flux:text>
            </div>
            <flux:button :href="route('exchange.board')" variant="primary" icon="arrows-right-left" wire:navigate>
                {{ __('Browse Exchange') }}
            </flux:button>
        </div>

        {{-- Profile completion nudge --}}
        @php
            $user = auth()->user();
            $missingProfile = ! $user->phone_number || ! $user->country_of_origin || ! $user->country_of_residence;
        @endphp
        @if($missingProfile)
            <div class="flex items-center gap-3 rounded-xl border border-gold-200 bg-gold-50 px-5 py-4 dark:border-gold-700 dark:bg-gold-900/20">
                <flux:icon name="information-circle" class="size-5 shrink-0 text-gold-600" />
                <div class="flex-1 text-sm text-gold-800">
                    {{ __('Complete your profile to help community members reach you after a match.') }}
                </div>
                <flux:button :href="route('profile.edit')" size="sm" variant="ghost" wire:navigate>
                    {{ __('Complete profile') }}
                </flux:button>
            </div>
        @endif

        {{-- Stats --}}
        @php
            $myOpenRequests   = $user->exchangeRequests()->where('status', 'open')->count();
            $interestsReceived = \App\Models\ExchangeInterest::whereIn(
                'exchange_request_id',
                $user->exchangeRequests()->pluck('id')
            )->where('status', 'pending')->count();
            $communityRequests = \App\Models\ExchangeRequest::query()->open()->where('user_id', '!=', $user->id)->count();
        @endphp
        <div class="grid gap-4 sm:grid-cols-3">

            {{-- My Open Requests --}}
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-navy-50">
                    <flux:icon name="arrows-right-left" class="size-5 text-navy-800" />
                </div>
                <div class="flex flex-col gap-1">
                    <flux:text class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('My Open Requests') }}</flux:text>
                    <span class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $myOpenRequests }}</span>
                    <flux:button :href="route('exchange.my-requests')" variant="ghost" size="sm" class="mt-1 self-start !px-0" wire:navigate>
                        {{ __('Manage →') }}
                    </flux:button>
                </div>
            </div>

            {{-- Interests Received --}}
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
                    <flux:icon name="users" class="size-5 text-emerald-600" />
                </div>
                <div class="flex flex-col gap-1">
                    <flux:text class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Interests Received') }}</flux:text>
                    <span class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $interestsReceived }}</span>
                    <flux:button :href="route('exchange.my-requests')" variant="ghost" size="sm" class="mt-1 self-start !px-0" wire:navigate>
                        {{ __('Review →') }}
                    </flux:button>
                </div>
            </div>

            {{-- Community Requests --}}
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-gold-50">
                    <flux:icon name="globe-alt" class="size-5 text-gold-600" />
                </div>
                <div class="flex flex-col gap-1">
                    <flux:text class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Community Requests') }}</flux:text>
                    <span class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $communityRequests }}</span>
                    <flux:button :href="route('exchange.board')" variant="ghost" size="sm" class="mt-1 self-start !px-0" wire:navigate>
                        {{ __('Browse →') }}
                    </flux:button>
                </div>
            </div>
        </div>

        {{-- My Recent Requests --}}
        @php
            $myRecentRequests = $user->exchangeRequests()
                ->with('interests')
                ->latest()
                ->take(5)
                ->get();
        @endphp
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('My Recent Requests') }}</flux:heading>
                <flux:button :href="route('exchange.my-requests')" variant="ghost" size="sm" wire:navigate>
                    {{ __('View all') }}
                </flux:button>
            </div>

            @if($myRecentRequests->isEmpty())
                <div class="px-5 py-10 text-center">
                    <flux:icon name="clipboard-document-list" class="mx-auto mb-3 size-8 text-zinc-300 dark:text-zinc-600" />
                    <flux:text class="text-zinc-400 dark:text-zinc-500">{{ __("You haven't posted any requests yet.") }}</flux:text>
                    <div class="mt-3">
                        <flux:button :href="route('exchange.board')" variant="primary" size="sm" wire:navigate>
                            {{ __('Post a Request') }}
                        </flux:button>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-100 bg-zinc-50 text-left dark:border-zinc-700 dark:bg-zinc-700">
                                <th class="px-5 py-3 font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Pair') }}</th>
                                <th class="px-5 py-3 font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Sending → Receiving') }}</th>
                                <th class="px-5 py-3 font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-center font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Interests') }}</th>
                                <th class="px-5 py-3 font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Posted') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                            @foreach($myRecentRequests as $request)
                                @php
                                    $isExpired  = $request->status === 'open' && $request->expires_at?->isPast();
                                    $dispStatus = $isExpired ? 'expired' : $request->status;
                                    $badgeColor = match($dispStatus) {
                                        'open'      => 'green',
                                        'matched'   => 'blue',
                                        'closed'    => 'zinc',
                                        'expired'   => 'yellow',
                                        'completed' => 'purple',
                                        default     => 'zinc',
                                    };
                                @endphp
                                <tr wire:key="my-{{ $request->id }}" class="transition-colors odd:bg-white even:bg-zinc-50/50 hover:bg-navy-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-700/30 dark:hover:bg-zinc-700">
                                    <td class="px-5 py-3 font-bold text-zinc-900 dark:text-white">{{ $request->from_currency }} → {{ $request->to_currency }}</td>
                                    <td class="px-5 py-3 text-zinc-700 dark:text-zinc-200">
                                        <div class="font-medium">{{ number_format((float) $request->from_amount, 2) }} <span class="text-zinc-400 dark:text-zinc-500">{{ $request->from_currency }}</span></div>
                                        <div class="text-xs text-zinc-400 dark:text-zinc-500">→ {{ number_format((float) $request->to_amount, 2) }} {{ $request->to_currency }}</div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <flux:badge :color="$badgeColor" size="sm">{{ ucfirst($dispStatus) }}</flux:badge>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-flex items-center gap-1.5 text-sm font-medium text-zinc-600 dark:text-zinc-300">
                                            <flux:icon name="users" class="size-3.5 text-zinc-400 dark:text-zinc-500" />
                                            {{ $request->interests->count() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-zinc-500 dark:text-zinc-400">{{ $request->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Recent Community Requests --}}
        @php
            $recentRequests = \App\Models\ExchangeRequest::query()
                ->open()
                ->with('user', 'interests')
                ->where('user_id', '!=', $user->id)
                ->latest()
                ->take(5)
                ->get();
        @endphp
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Recent Community Requests') }}</flux:heading>
                <flux:button :href="route('exchange.board')" variant="ghost" size="sm" wire:navigate>
                    {{ __('View all') }}
                </flux:button>
            </div>

            @if($recentRequests->isEmpty())
                <div class="px-5 py-10 text-center">
                    <flux:icon name="arrows-right-left" class="mx-auto mb-3 size-8 text-zinc-300 dark:text-zinc-600" />
                    <flux:text class="text-zinc-400 dark:text-zinc-500">{{ __('No community exchange requests yet.') }}</flux:text>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-100 bg-zinc-50 text-left dark:border-zinc-700 dark:bg-zinc-700">
                                <th class="px-5 py-3 font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Pair') }}</th>
                                <th class="px-5 py-3 font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Posted by') }}</th>
                                <th class="px-5 py-3 font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Sending → Receiving') }}</th>
                                <th class="px-5 py-3 font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Rate') }}</th>
                                <th class="px-5 py-3 text-center font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Interests') }}</th>
                                <th class="px-5 py-3 font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Posted') }}</th>
                                <th class="px-5 py-3 text-right font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                            @foreach($recentRequests as $request)
                                <tr wire:key="community-{{ $request->id }}" class="transition-colors odd:bg-white even:bg-zinc-50/50 hover:bg-navy-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-700/30 dark:hover:bg-zinc-700">
                                    <td class="px-5 py-3 font-bold text-zinc-900 dark:text-white">{{ $request->from_currency }} → {{ $request->to_currency }}</td>
                                    <td class="px-5 py-3">
                                        @if($request->is_anonymous)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400">Community Member</span>
                                        @else
                                            <span class="text-zinc-700 dark:text-zinc-200">{{ $request->user->name }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-zinc-700 dark:text-zinc-200">
                                        <div class="font-medium">{{ number_format((float) $request->from_amount, 2) }} <span class="text-zinc-400 dark:text-zinc-500">{{ $request->from_currency }}</span></div>
                                        <div class="text-xs text-zinc-400 dark:text-zinc-500">→ {{ number_format((float) $request->to_amount, 2) }} {{ $request->to_currency }}</div>
                                    </td>
                                    <td class="px-5 py-3 font-mono text-zinc-700 dark:text-zinc-200">{{ number_format((float) $request->offered_rate, 4) }}</td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-flex items-center gap-1.5 text-sm font-medium text-zinc-600 dark:text-zinc-300">
                                            <flux:icon name="users" class="size-3.5 text-zinc-400 dark:text-zinc-500" />
                                            {{ $request->interests->count() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-zinc-500 dark:text-zinc-400">{{ $request->created_at->diffForHumans() }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <flux:button :href="route('exchange.board')" size="sm" variant="primary" wire:navigate>
                                            {{ __("I'm Interested") }}
                                        </flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</x-layouts::app>
