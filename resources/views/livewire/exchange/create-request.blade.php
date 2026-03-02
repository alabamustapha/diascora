<div class="space-y-5 p-1">
    {{-- Step indicator --}}
    <div class="flex items-center justify-center gap-0 mb-6">
        @for ($i = 1; $i <= 3; $i++)
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold
                    {{ $step >= $i ? 'bg-navy-800 text-white' : 'bg-zinc-200 text-zinc-500' }}">
                    {{ $i }}
                </div>
                @if ($i < 3)
                    <div class="h-px w-10 {{ $step > $i ? 'bg-navy-800' : 'bg-zinc-200' }}"></div>
                @endif
            </div>
        @endfor
    </div>

    {{-- Step 1: Currencies --}}
    @if ($step === 1)
        <div>
            <flux:heading size="lg">{{ __('Which currencies?') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Select the currencies you want to exchange.') }}</flux:text>
        </div>

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

        @if ($official_rate)
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm">
                <span class="text-zinc-500">{{ __('Live official rate') }}: </span>
                <span class="font-semibold text-zinc-900">1 {{ $from_currency }} = {{ number_format((float) $official_rate, 4) }} {{ $to_currency }}</span>
                <span class="ml-2 text-xs text-zinc-400">({{ __('cached, updates hourly') }})</span>
            </div>
        @endif

        <div class="flex justify-end gap-3 border-t border-zinc-100 pt-4">
            <flux:button wire:click="$parent.showCreateModal = false" variant="ghost">{{ __('Cancel') }}</flux:button>
            <flux:button wire:click="nextStep" variant="primary">{{ __('Next') }} →</flux:button>
        </div>
    @endif

    {{-- Step 2: Amounts & Payment --}}
    @if ($step === 2)
        <div>
            <flux:heading size="lg">{{ __('Amounts & payment') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Enter the amounts and how you\'ll send and receive.') }}</flux:text>
        </div>

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

        @if ($offered_rate && $official_rate)
            <div class="rounded-lg border px-4 py-3 text-sm {{ $this->rateDiffPercent >= 0 ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                <div class="flex items-center justify-between">
                    <span class="text-zinc-600">{{ __('Your offered rate') }}: <span class="font-semibold text-zinc-900">{{ number_format((float) $offered_rate, 4) }}</span></span>
                    @if ($this->rateDiffPercent !== null)
                        <span class="{{ $this->rateDiffPercent >= 0 ? 'text-green-700' : 'text-red-600' }} font-medium">
                            {{ $this->rateDiffPercent >= 0 ? '+' : '' }}{{ number_format($this->rateDiffPercent, 2) }}% {{ __('vs official') }}
                        </span>
                    @endif
                </div>
            </div>
        @endif

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

        <div class="flex justify-between gap-3 border-t border-zinc-100 pt-4">
            <flux:button wire:click="prevStep" variant="ghost">← {{ __('Back') }}</flux:button>
            <flux:button wire:click="nextStep" variant="primary">{{ __('Next') }} →</flux:button>
        </div>
    @endif

    {{-- Step 3: Review & Details --}}
    @if ($step === 3)
        <div>
            <flux:heading size="lg">{{ __('Review & post') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Check your request details before posting.') }}</flux:text>
        </div>

        {{-- Summary card --}}
        <div class="rounded-lg border border-zinc-200 bg-zinc-50 divide-y divide-zinc-200 text-sm">
            <div class="flex items-center justify-between px-4 py-2">
                <span class="text-zinc-500">{{ __('Currency pair') }}</span>
                <span class="font-semibold text-zinc-900">{{ $from_currency }} → {{ $to_currency }}</span>
            </div>
            <div class="flex items-center justify-between px-4 py-2">
                <span class="text-zinc-500">{{ __('Amounts') }}</span>
                <span class="font-semibold text-zinc-900">{{ number_format((float) $from_amount, 2) }} {{ $from_currency }} → {{ number_format((float) $to_amount, 2) }} {{ $to_currency }}</span>
            </div>
            @if ($official_rate)
                <div class="flex items-center justify-between px-4 py-2">
                    <span class="text-zinc-500">{{ __('Official rate') }}</span>
                    <span class="font-medium text-zinc-700">{{ number_format((float) $official_rate, 4) }}</span>
                </div>
            @endif
            @if ($offered_rate)
                <div class="flex items-center justify-between px-4 py-2">
                    <span class="text-zinc-500">{{ __('Your offered rate') }}</span>
                    <span class="font-semibold text-zinc-900">
                        {{ number_format((float) $offered_rate, 4) }}
                        @if ($this->rateDiffPercent !== null)
                            <span class="ml-1 text-xs {{ $this->rateDiffPercent >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                ({{ $this->rateDiffPercent >= 0 ? '+' : '' }}{{ number_format($this->rateDiffPercent, 2) }}%)
                            </span>
                        @endif
                    </span>
                </div>
            @endif
            <div class="flex items-center justify-between px-4 py-2">
                <span class="text-zinc-500">{{ __('Payment') }}</span>
                <span class="font-medium text-zinc-700">{{ $payment_method_sending }} → {{ $payment_method_receiving }}</span>
            </div>
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

        <div class="flex justify-between gap-3 border-t border-zinc-100 pt-4">
            <flux:button wire:click="prevStep" variant="ghost">← {{ __('Back') }}</flux:button>
            <flux:button wire:click="create" variant="primary" wire:loading.attr="disabled">
                {{ __('Post Request') }}
            </flux:button>
        </div>
    @endif
</div>
