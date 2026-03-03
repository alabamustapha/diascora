<div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Exchange Board') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Browse peer-to-peer currency exchange requests from the community.') }}</flux:text>
        </div>
        @auth
            <flux:button variant="primary" wire:click="openCreateModal" icon="plus">
                {{ __('Post Exchange Request') }}
            </flux:button>
        @endauth
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <flux:select wire:model.live="filterFrom" class="w-48">
            <flux:select.option value="">{{ __('From: all currencies') }}</flux:select.option>
            @foreach($this->currencies as $value => $label)
                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filterTo" class="w-48">
            <flux:select.option value="">{{ __('To: all currencies') }}</flux:select.option>
            @foreach($this->currencies as $value => $label)
                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($filterFrom || $filterTo)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                {{ __('Clear') }}
            </flux:button>
        @endif
    </div>

    {{-- Empty state --}}
    @if($this->requests->isEmpty())
        <div class="flex flex-1 items-center justify-center rounded-xl border border-zinc-200 py-20 text-center">
            <div>
                <flux:icon name="arrows-right-left" class="mx-auto mb-3 size-10 text-zinc-300" />
                <flux:heading>{{ __('No exchange requests found') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ $filterFrom || $filterTo ? __('Try adjusting your currency filters.') : __('Be the first to post a currency exchange request.') }}
                </flux:text>
                <div class="mt-4 flex justify-center gap-3">
                    @if($filterFrom || $filterTo)
                        <flux:button wire:click="clearFilters" variant="ghost">{{ __('Clear filters') }}</flux:button>
                    @endif
                    @auth
                        <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                            {{ __('Post Exchange Request') }}
                        </flux:button>
                    @endauth
                </div>
            </div>
        </div>

    {{-- Table --}}
    @else
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100 bg-zinc-50 text-left">
                            <th class="px-4 py-3 font-semibold text-zinc-600">{{ __('Pair') }}</th>
                            <th class="px-4 py-3 font-semibold text-zinc-600">{{ __('Posted by') }}</th>
                            <th class="px-4 py-3 font-semibold text-zinc-600">{{ __('Sending → Receiving') }}</th>
                            <th class="px-4 py-3 font-semibold text-zinc-600">{{ __('Rate') }}</th>
                            <th class="px-4 py-3 font-semibold text-zinc-600">{{ __('Payment') }}</th>
                            <th class="px-4 py-3 text-center font-semibold text-zinc-600">{{ __('Interests') }}</th>
                            <th class="px-4 py-3 font-semibold text-zinc-600">{{ __('Posted') }}</th>
                            <th class="px-4 py-3 text-right font-semibold text-zinc-600">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach($this->requests as $request)
                            @php
                                $officialRate = $displayedRates[$request->from_currency] ?? null;
                                $offeredRate  = (float) $request->offered_rate;
                                $rateDiff     = $officialRate ? (($offeredRate - $officialRate) / $officialRate) * 100 : null;
                                $isOwn        = auth()->check() && auth()->id() === $request->user_id;
                            @endphp
                            <tr wire:key="request-{{ $request->id }}" class="transition-colors odd:bg-white even:bg-zinc-50/50 hover:bg-navy-50">

                                {{-- Pair --}}
                                <td class="px-4 py-3">
                                    <span class="font-bold text-zinc-900">{{ $request->from_currency }} → {{ $request->to_currency }}</span>
                                </td>

                                {{-- Poster --}}
                                <td class="px-4 py-3">
                                    @if($request->is_anonymous)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-500">
                                            Community Member
                                        </span>
                                    @else
                                        <span class="text-zinc-700">{{ $request->user->name }}</span>
                                    @endif
                                </td>

                                {{-- Amounts --}}
                                <td class="px-4 py-3 text-zinc-700">
                                    <div class="font-medium">{{ number_format((float) $request->from_amount, 2) }} <span class="text-zinc-400">{{ $request->from_currency }}</span></div>
                                    <div class="text-xs text-zinc-400">→ {{ number_format((float) $request->to_amount, 2) }} {{ $request->to_currency }}</div>
                                </td>

                                {{-- Rate + diff --}}
                                <td class="px-4 py-3">
                                    <div class="font-mono text-zinc-700">{{ number_format($offeredRate, 4) }}</div>
                                    @if($rateDiff !== null)
                                        <div class="text-xs font-medium {{ $rateDiff >= 0 ? 'text-green-600' : 'text-red-500' }}">
                                            {{ $rateDiff >= 0 ? '+' : '' }}{{ number_format($rateDiff, 2) }}% vs live
                                        </div>
                                    @endif
                                </td>

                                {{-- Payment --}}
                                <td class="px-4 py-3 text-sm text-zinc-600">
                                    <div>↑ {{ $request->payment_method_sending }}</div>
                                    <div class="text-zinc-400">↓ {{ $request->payment_method_receiving }}</div>
                                </td>

                                {{-- Interests --}}
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-zinc-600">
                                        <flux:icon name="users" class="size-3.5 text-zinc-400" />
                                        {{ $request->interests->count() }}
                                    </span>
                                </td>

                                {{-- Posted --}}
                                <td class="px-4 py-3 text-sm text-zinc-500">
                                    {{ $request->created_at->diffForHumans() }}
                                </td>

                                {{-- Action --}}
                                <td class="px-4 py-3 text-right">
                                    @auth
                                        @if($isOwn)
                                            <flux:button :href="route('exchange.my-requests')" size="sm" variant="ghost" wire:navigate>
                                                {{ __('Manage') }}
                                            </flux:button>
                                        @else
                                            <flux:button wire:click="openInterestModal({{ $request->id }})" size="sm" variant="primary">
                                                {{ __("I'm Interested") }}
                                            </flux:button>
                                        @endif
                                    @else
                                        <flux:button :href="route('login')" size="sm" variant="ghost">
                                            {{ __('Log in') }}
                                        </flux:button>
                                    @endauth
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($this->requests->hasPages())
            <div>{{ $this->requests->links() }}</div>
        @endif
    @endif

    {{-- Interest Modal --}}
    <flux:modal wire:model="showInterestModal" class="max-w-lg">
        <div class="space-y-5 p-1">
            <div>
                <flux:heading size="lg">{{ __('Express Interest') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Let the poster know you are interested. They will see your name and details.') }}</flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('Your message') }}</flux:label>
                <flux:textarea wire:model="interestComment" rows="3" placeholder="{{ __('Tell the poster about your exchange needs...') }}" />
                <flux:error name="interestComment" />
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('You will send via') }}</flux:label>
                    <flux:select wire:model="interestPaymentSending">
                        <flux:select.option value="">{{ __('Select method') }}</flux:select.option>
                        @foreach($this->paymentMethods as $value => $label)
                            <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="interestPaymentSending" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('You will receive via') }}</flux:label>
                    <flux:select wire:model="interestPaymentReceiving">
                        <flux:select.option value="">{{ __('Select method') }}</flux:select.option>
                        @foreach($this->paymentMethods as $value => $label)
                            <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="interestPaymentReceiving" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="$set('showInterestModal', false)" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="submitInterest" variant="primary" wire:loading.attr="disabled">
                    {{ __('Submit Interest') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Create Request Modal --}}
    <flux:modal wire:model="showCreateModal" class="max-w-2xl">
        @if($showCreateModal)
            <livewire:exchange.create-request :key="'create-request-modal'" />
        @endif
    </flux:modal>

</div>
