<div class="space-y-5 p-1">
    <div>
        <flux:heading size="lg">{{ __('Post Exchange Request') }}</flux:heading>
        <flux:text class="mt-1">{{ __('List a currency exchange you want to make. Other members can express interest.') }}</flux:text>
    </div>

    {{-- Currency Pair --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <flux:field>
            <flux:label>{{ __('From currency') }}</flux:label>
            <flux:select wire:model.live="from_currency">
                @foreach($this->currencies as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="from_currency" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('To currency') }}</flux:label>
            <flux:select wire:model.live="to_currency">
                @foreach($this->currencies as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="to_currency" />
        </flux:field>
    </div>

    {{-- Official Rate Display --}}
    @if($official_rate)
        <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm">
            <span class="text-zinc-500">{{ __('Live official rate') }}: </span>
            <span class="font-semibold text-zinc-900">1 {{ $from_currency }} = {{ number_format((float) $official_rate, 4) }} {{ $to_currency }}</span>
            <span class="ml-2 text-xs text-zinc-400">({{ __('cached, updates hourly') }})</span>
        </div>
    @endif

    {{-- Amounts --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <flux:field>
            <flux:label>{{ __('Amount to send') }} ({{ $from_currency }})</flux:label>
            <flux:input type="number" wire:model.live.debounce.400ms="from_amount" min="0.01" step="0.01" placeholder="e.g. 50000" />
            <flux:error name="from_amount" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Amount to receive') }} ({{ $to_currency }})</flux:label>
            <flux:input type="number" wire:model.live.debounce.400ms="to_amount" min="0.01" step="0.01" placeholder="{{ __('Auto-calculated') }}" />
            <flux:error name="to_amount" />
        </flux:field>
    </div>

    {{-- Offered Rate vs Official --}}
    @if($offered_rate && $official_rate)
        <div class="rounded-lg border px-4 py-3 text-sm {{ $this->rateDiffPercent >= 0 ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
            <div class="flex items-center justify-between">
                <span class="text-zinc-600">{{ __('Your offered rate') }}: <span class="font-semibold text-zinc-900">{{ number_format((float) $offered_rate, 4) }}</span></span>
                @if($this->rateDiffPercent !== null)
                    <span class="{{ $this->rateDiffPercent >= 0 ? 'text-green-700' : 'text-red-600' }} font-medium">
                        {{ $this->rateDiffPercent >= 0 ? '+' : '' }}{{ number_format($this->rateDiffPercent, 2) }}% {{ __('vs official') }}
                    </span>
                @endif
            </div>
        </div>
    @endif

    {{-- Payment Methods --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <flux:field>
            <flux:label>{{ __('Sending via') }}</flux:label>
            <flux:select wire:model="payment_method_sending">
                <flux:select.option value="">{{ __('Select method') }}</flux:select.option>
                @foreach($this->paymentMethods as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="payment_method_sending" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Receiving via') }}</flux:label>
            <flux:select wire:model="payment_method_receiving">
                <flux:select.option value="">{{ __('Select method') }}</flux:select.option>
                @foreach($this->paymentMethods as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="payment_method_receiving" />
        </flux:field>
    </div>

    {{-- Notes --}}
    <flux:field>
        <flux:label>{{ __('Notes') }} <span class="text-zinc-400">({{ __('optional') }})</span></flux:label>
        <flux:textarea wire:model="notes" rows="2" placeholder="{{ __('Any extra details for interested peers...') }}" />
        <flux:error name="notes" />
    </flux:field>

    {{-- Options --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <flux:field>
            <flux:label>{{ __('Expires in') }}</flux:label>
            <flux:select wire:model="expires_in_days">
                <flux:select.option value="1">{{ __('1 day') }}</flux:select.option>
                <flux:select.option value="3">{{ __('3 days') }}</flux:select.option>
                <flux:select.option value="7">{{ __('7 days') }}</flux:select.option>
                <flux:select.option value="14">{{ __('14 days') }}</flux:select.option>
                <flux:select.option value="30">{{ __('30 days') }}</flux:select.option>
            </flux:select>
        </flux:field>

        <div class="flex items-end pb-1">
            <flux:checkbox wire:model="is_anonymous" :label="__('Post anonymously')" />
        </div>
    </div>

    <div class="flex justify-end gap-3 border-t border-zinc-100 pt-4">
        <flux:button wire:click="$parent.showCreateModal = false" variant="ghost">{{ __('Cancel') }}</flux:button>
        <flux:button wire:click="create" variant="primary" wire:loading.attr="disabled">
            {{ __('Post Request') }}
        </flux:button>
    </div>
</div>
