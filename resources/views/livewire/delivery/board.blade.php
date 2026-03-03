<div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Delivery Board') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Browse package delivery requests from the community.') }}</flux:text>
        </div>
        @auth
            <flux:button variant="primary" wire:click="openCreateModal" icon="plus">
                {{ __('Post Delivery Request') }}
            </flux:button>
        @endauth
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <flux:select wire:model.live="filterCountry" class="w-56">
            <flux:select.option value="">{{ __('All destinations') }}</flux:select.option>
            @foreach($this->countries as $value => $label)
                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($filterCountry)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                {{ __('Clear') }}
            </flux:button>
        @endif
    </div>

    {{-- Empty state --}}
    @if($this->requests->isEmpty())
        <div class="flex flex-1 items-center justify-center rounded-xl border border-zinc-200 py-20 text-center dark:border-zinc-700">
            <div>
                <flux:icon name="archive-box" class="mx-auto mb-3 size-10 text-zinc-300 dark:text-zinc-600" />
                <flux:heading>{{ __('No delivery requests found') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ $filterCountry ? __('Try adjusting your destination filter.') : __('Be the first to post a delivery request.') }}
                </flux:text>
                <div class="mt-4 flex justify-center gap-3">
                    @if($filterCountry)
                        <flux:button wire:click="clearFilters" variant="ghost">{{ __('Clear filters') }}</flux:button>
                    @endif
                    @auth
                        <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                            {{ __('Post Delivery Request') }}
                        </flux:button>
                    @endauth
                </div>
            </div>
        </div>

    {{-- Table --}}
    @else
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100 bg-zinc-50 text-left dark:border-zinc-700 dark:bg-zinc-700">
                            <th class="px-4 py-3 font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Destination') }}</th>
                            <th class="px-4 py-3 font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Posted by') }}</th>
                            <th class="px-4 py-3 font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Weight') }}</th>
                            <th class="px-4 py-3 font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Payment') }}</th>
                            <th class="px-4 py-3 text-center font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Offers') }}</th>
                            <th class="px-4 py-3 font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Travel by') }}</th>
                            <th class="px-4 py-3 text-right font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                        @foreach($this->requests as $request)
                            @php $isOwn = auth()->check() && auth()->id() === $request->user_id; @endphp
                            <tr wire:key="request-{{ $request->id }}" class="transition-colors odd:bg-white even:bg-zinc-50/50 hover:bg-navy-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-700/30 dark:hover:bg-zinc-700">

                                {{-- Destination --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @if($request->item_image_path)
                                            <img
                                                src="{{ $request->imageUrl() }}"
                                                alt="{{ __('Item') }}"
                                                class="size-10 rounded object-cover"
                                            />
                                        @else
                                            <div class="flex size-10 items-center justify-center rounded bg-zinc-100 dark:bg-zinc-700">
                                                <flux:icon name="archive-box" class="size-5 text-zinc-400 dark:text-zinc-500" />
                                            </div>
                                        @endif
                                        <span class="font-medium text-zinc-900 dark:text-white">
                                            {{ \App\Enums\DeliveryCountry::tryFrom($request->destination_country)?->label() ?? $request->destination_country }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Poster --}}
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $request->user->name }}</td>

                                {{-- Weight --}}
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ number_format((float) $request->weight_kg, 2) }} kg</td>

                                {{-- Payment --}}
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">
                                    <div class="font-medium">{{ number_format((float) $request->payment_amount, 2) }} {{ $request->payment_currency }}</div>
                                    <div class="text-xs text-zinc-400 dark:text-zinc-500">{{ $request->payment_method }}</div>
                                </td>

                                {{-- Offers --}}
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-zinc-600 dark:text-zinc-300">
                                        <flux:icon name="users" class="size-3.5 text-zinc-400 dark:text-zinc-500" />
                                        {{ $request->offers->count() }}
                                    </span>
                                </td>

                                {{-- Travel by --}}
                                <td class="px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400">
                                    @if($request->expires_at)
                                        {{ $request->expires_at->diffForHumans() }}
                                    @else
                                        <span class="text-zinc-300 dark:text-zinc-600">—</span>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td class="px-4 py-3 text-right">
                                    @auth
                                        @if($isOwn)
                                            <flux:button :href="route('delivery.my-requests')" size="sm" variant="ghost" wire:navigate>
                                                {{ __('Manage') }}
                                            </flux:button>
                                        @else
                                            <flux:button wire:click="openOfferModal({{ $request->id }})" size="sm" variant="primary">
                                                {{ __('I can carry this') }}
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

    {{-- Offer Modal --}}
    <flux:modal wire:model="showOfferModal" class="max-w-lg">
        <div class="space-y-5 p-1">
            <div>
                <flux:heading size="lg">{{ __('Offer to Carry') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Let the requester know you can carry their package. They will see your name and contact details.') }}</flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('Your message') }}</flux:label>
                <flux:textarea wire:model="offerMessage" rows="4" placeholder="{{ __('Describe your travel plans, availability, and any other relevant details...') }}" />
                <flux:error name="offerMessage" />
            </flux:field>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="$set('showOfferModal', false)" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="submitOffer" variant="primary" wire:loading.attr="disabled">
                    {{ __('Submit Offer') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Create Request Modal --}}
    <flux:modal wire:model="showCreateModal" class="max-w-2xl">
        @if($showCreateModal)
            <livewire:delivery.create-request :key="'delivery-create-request-modal'" />
        @endif
    </flux:modal>

</div>
