<div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('My Exchange Requests') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Track your requests, review interest, and relist completed exchanges.') }}</flux:text>
        </div>
        <flux:button variant="primary" :href="route('exchange.board')" icon="plus" wire:navigate>
            {{ __('New Request') }}
        </flux:button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="min-w-48 flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search by currency…') }}"
                icon="magnifying-glass"
                clearable
            />
        </div>
        <flux:select wire:model.live="filterStatus" class="w-44">
            <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
            <flux:select.option value="open">{{ __('Open') }}</flux:select.option>
            <flux:select.option value="matched">{{ __('Matched') }}</flux:select.option>
            <flux:select.option value="closed">{{ __('Closed') }}</flux:select.option>
            <flux:select.option value="expired">{{ __('Expired') }}</flux:select.option>
        </flux:select>
        @if($search || $filterStatus)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                {{ __('Clear') }}
            </flux:button>
        @endif
    </div>

    {{-- Empty state --}}
    @if($this->myRequests->isEmpty())
        <div class="flex flex-1 items-center justify-center rounded-xl border border-zinc-200 py-20 text-center">
            <div>
                <flux:icon name="clipboard-document-list" class="mx-auto mb-3 size-10 text-zinc-300" />
                <flux:heading>
                    {{ $search || $filterStatus ? __('No requests match your filters') : __('No requests yet') }}
                </flux:heading>
                <flux:text class="mt-1">
                    {{ $search || $filterStatus ? __('Try adjusting your search or filters.') : __('Post your first exchange request on the board.') }}
                </flux:text>
                <div class="mt-4 flex justify-center gap-3">
                    @if($search || $filterStatus)
                        <flux:button wire:click="clearFilters" variant="ghost">{{ __('Clear filters') }}</flux:button>
                    @else
                        <flux:button :href="route('exchange.board')" variant="primary" wire:navigate>
                            {{ __('Browse Exchange Board') }}
                        </flux:button>
                    @endif
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
                            <th class="px-4 py-3 font-semibold text-zinc-600">{{ __('Sending → Receiving') }}</th>
                            <th class="px-4 py-3 font-semibold text-zinc-600">{{ __('Rate') }}</th>
                            <th class="px-4 py-3 font-semibold text-zinc-600">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-center font-semibold text-zinc-600">{{ __('Interests') }}</th>
                            <th class="px-4 py-3 font-semibold text-zinc-600">{{ __('Posted') }}</th>
                            <th class="px-4 py-3 font-semibold text-zinc-600">{{ __('Expires') }}</th>
                            <th class="px-4 py-3 text-right font-semibold text-zinc-600">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach($this->myRequests as $request)
                            @php
                                $displayStatus = $this->displayStatus($request);
                                $isExpired = $displayStatus === 'expired';
                                $badgeColor = match($displayStatus) {
                                    'open'      => 'green',
                                    'matched'   => 'blue',
                                    'closed'    => 'zinc',
                                    'expired'   => 'yellow',
                                    'completed' => 'purple',
                                    default     => 'zinc',
                                };
                            @endphp
                            <tr wire:key="request-{{ $request->id }}" class="transition-colors odd:bg-white even:bg-zinc-50/50 hover:bg-navy-50">

                                {{-- Pair --}}
                                <td class="px-4 py-3">
                                    <span class="font-bold text-zinc-900">{{ $request->from_currency }} → {{ $request->to_currency }}</span>
                                    @if($request->is_anonymous)
                                        <div class="mt-0.5 text-xs text-zinc-400">{{ __('Anonymous') }}</div>
                                    @endif
                                </td>

                                {{-- Amounts --}}
                                <td class="px-4 py-3 text-zinc-700">
                                    <div class="font-medium">{{ number_format((float) $request->from_amount, 2) }} <span class="text-zinc-400">{{ $request->from_currency }}</span></div>
                                    <div class="text-xs text-zinc-400">→ {{ number_format((float) $request->to_amount, 2) }} {{ $request->to_currency }}</div>
                                </td>

                                {{-- Rate --}}
                                <td class="px-4 py-3 font-mono text-zinc-700">
                                    {{ number_format((float) $request->offered_rate, 4) }}
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3">
                                    <flux:badge :color="$badgeColor" size="sm">{{ ucfirst($displayStatus) }}</flux:badge>
                                </td>

                                {{-- Interests --}}
                                <td class="px-4 py-3 text-center">
                                    <button
                                        wire:click="viewDetails({{ $request->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-sm font-medium text-zinc-600 transition-colors hover:bg-zinc-100"
                                    >
                                        <flux:icon name="users" class="size-3.5 text-zinc-400" />
                                        {{ $request->interests->count() }}
                                    </button>
                                </td>

                                {{-- Posted --}}
                                <td class="px-4 py-3 text-xs text-zinc-500">
                                    {{ $request->created_at->diffForHumans() }}
                                </td>

                                {{-- Expires --}}
                                <td class="px-4 py-3 text-xs {{ $isExpired ? 'font-medium text-amber-600' : 'text-zinc-500' }}">
                                    @if($request->expires_at)
                                        {{ $request->expires_at->diffForHumans() }}
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <flux:button
                                            wire:click="viewDetails({{ $request->id }})"
                                            size="sm" variant="ghost" icon="eye"
                                            title="{{ __('View details') }}"
                                        />
                                        <flux:button
                                            wire:click="relistRequest({{ $request->id }})"
                                            wire:confirm="{{ __('Relist this request? A new open copy will be created.') }}"
                                            size="sm" variant="ghost" icon="document-duplicate"
                                            title="{{ __('Relist') }}"
                                        />
                                        @if($request->status === 'open' && ! $isExpired)
                                            <flux:button
                                                wire:click="closeRequest({{ $request->id }})"
                                                wire:confirm="{{ __('Close this request? It will no longer appear on the board.') }}"
                                                size="sm" variant="ghost" icon="x-circle"
                                                title="{{ __('Close') }}"
                                            />
                                        @endif
                                        @if($isExpired && $request->interests->isEmpty())
                                            <flux:button
                                                wire:click="deleteRequest({{ $request->id }})"
                                                wire:confirm="{{ __('Permanently delete this expired request? This cannot be undone.') }}"
                                                size="sm" variant="ghost" icon="trash"
                                                title="{{ __('Delete') }}"
                                                class="text-red-500 hover:text-red-600"
                                            />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($this->myRequests->hasPages())
            <div>{{ $this->myRequests->links() }}</div>
        @endif
    @endif

    {{-- Details Modal --}}
    <flux:modal wire:model="showDetailsModal" class="max-w-2xl w-full">
        @if($this->selectedRequest)
            @php
                $req = $this->selectedRequest;
                $detailStatus = $this->displayStatus($req);
                $detailExpired = $detailStatus === 'expired';
                $detailBadgeColor = match($detailStatus) {
                    'open'      => 'green',
                    'matched'   => 'blue',
                    'closed'    => 'zinc',
                    'expired'   => 'yellow',
                    'completed' => 'purple',
                    default     => 'zinc',
                };
            @endphp
            <div class="space-y-5 p-1">

                {{-- Modal header --}}
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <flux:heading size="lg">{{ $req->from_currency }} → {{ $req->to_currency }}</flux:heading>
                        <flux:text class="mt-0.5 text-sm">{{ __('Posted') }} {{ $req->created_at->diffForHumans() }}</flux:text>
                    </div>
                    <flux:badge :color="$detailBadgeColor">{{ ucfirst($detailStatus) }}</flux:badge>
                </div>

                {{-- Request summary --}}
                <div class="grid gap-3 rounded-lg bg-zinc-50 p-4 text-sm sm:grid-cols-2">
                    <div>
                        <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Sending') }}</div>
                        <div class="font-semibold text-zinc-900">{{ number_format((float) $req->from_amount, 2) }} {{ $req->from_currency }}</div>
                    </div>
                    <div>
                        <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Receiving') }}</div>
                        <div class="font-semibold text-zinc-900">{{ number_format((float) $req->to_amount, 2) }} {{ $req->to_currency }}</div>
                    </div>
                    <div>
                        <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Offered Rate') }}</div>
                        <div class="font-mono text-zinc-900">{{ number_format((float) $req->offered_rate, 4) }}</div>
                    </div>
                    <div>
                        <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Payment Methods') }}</div>
                        <div class="text-zinc-700">{{ $req->payment_method_sending }} / {{ $req->payment_method_receiving }}</div>
                    </div>
                    @if($req->expires_at)
                        <div>
                            <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Expires') }}</div>
                            <div class="{{ $detailExpired ? 'font-medium text-amber-600' : 'text-zinc-700' }}">{{ $req->expires_at->diffForHumans() }}</div>
                        </div>
                    @endif
                    @if($req->notes)
                        <div class="sm:col-span-2">
                            <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Notes') }}</div>
                            <div class="text-zinc-700">{{ $req->notes }}</div>
                        </div>
                    @endif
                </div>

                {{-- Matched contact --}}
                @if($req->status === 'matched' && $this->canSeeContact($req))
                    @php $peer = $req->acceptedInterest?->user; @endphp
                    @if($peer)
                        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
                            <div class="mb-2 flex items-center gap-2">
                                <flux:icon name="check-circle" class="size-4 text-blue-600" />
                                <span class="text-sm font-semibold text-blue-800">{{ __('Matched! Contact details:') }}</span>
                            </div>
                            <div class="grid gap-1 text-sm text-blue-900">
                                <div><span class="font-medium">{{ __('Name') }}:</span> {{ $peer->name }}</div>
                                <div><span class="font-medium">{{ __('Email') }}:</span> {{ $peer->email }}</div>
                                @if($peer->phone_number)
                                    <div>
                                        <span class="font-medium">{{ __('Phone') }}:</span> {{ $peer->phone_number }}
                                        @if($peer->whatsapp_enabled)
                                            <span class="ml-1 text-xs font-medium text-green-700">WhatsApp ✓</span>
                                        @endif
                                        @if($peer->telegram_enabled)
                                            <span class="ml-1 text-xs font-medium text-blue-700">Telegram ✓</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Interests list --}}
                <div>
                    <div class="mb-3 flex items-center gap-2">
                        <flux:heading size="sm">{{ __('Interested peers') }}</flux:heading>
                        <flux:badge size="sm" color="zinc">{{ $req->interests->count() }}</flux:badge>
                    </div>

                    @if($req->interests->isEmpty())
                        <flux:text class="text-sm text-zinc-400">{{ __('No one has expressed interest yet.') }}</flux:text>
                    @else
                        <div class="space-y-3">
                            @foreach($req->interests as $interest)
                                <div
                                    wire:key="modal-interest-{{ $interest->id }}"
                                    class="flex items-start gap-4 rounded-lg border p-4 {{ $interest->status === 'accepted' ? 'border-green-200 bg-green-50' : ($interest->status === 'rejected' ? 'border-zinc-100 bg-zinc-50 opacity-60' : 'border-zinc-200') }}"
                                >
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-medium text-zinc-900">{{ $interest->user->name }}</span>
                                            <flux:badge size="sm" color="{{ match($interest->status) { 'accepted' => 'green', 'rejected' => 'zinc', default => 'yellow' } }}">
                                                {{ ucfirst($interest->status) }}
                                            </flux:badge>
                                        </div>
                                        <p class="mt-1 text-sm text-zinc-600">{{ $interest->comment }}</p>
                                        <div class="mt-1 text-xs text-zinc-400">
                                            {{ __('Sends via') }}: {{ $interest->payment_method_sending }}
                                            · {{ __('Receives via') }}: {{ $interest->payment_method_receiving }}
                                        </div>
                                    </div>
                                    @if($req->status === 'open' && $interest->status === 'pending')
                                        <flux:button
                                            wire:click="acceptInterest({{ $interest->id }})"
                                            wire:confirm="{{ __('Accept this interest? All others will be rejected and contact info will be revealed.') }}"
                                            size="sm" variant="primary"
                                        >
                                            {{ __('Accept') }}
                                        </flux:button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Modal footer --}}
                <div class="flex items-center justify-between gap-3 border-t border-zinc-100 pt-4">
                    <div class="flex gap-2">
                        <flux:button
                            wire:click="relistRequest({{ $req->id }})"
                            wire:confirm="{{ __('Relist this request? A new open copy will be created.') }}"
                            variant="ghost" icon="document-duplicate"
                        >
                            {{ __('Relist') }}
                        </flux:button>
                        @if($req->status === 'open' && ! $detailExpired)
                            <flux:button
                                wire:click="closeRequest({{ $req->id }})"
                                wire:confirm="{{ __('Close this request? It will no longer appear on the board.') }}"
                                variant="ghost"
                            >
                                {{ __('Close Request') }}
                            </flux:button>
                        @endif
                        @if($detailExpired && $req->interests->isEmpty())
                            <flux:button
                                wire:click="deleteRequest({{ $req->id }})"
                                wire:confirm="{{ __('Permanently delete this expired request? This cannot be undone.') }}"
                                variant="ghost"
                                icon="trash"
                                class="text-red-500 hover:text-red-600"
                            >
                                {{ __('Delete') }}
                            </flux:button>
                        @endif
                    </div>
                    <flux:button wire:click="$set('showDetailsModal', false)" variant="ghost">
                        {{ __('Done') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

</div>
