<div class="space-y-5 p-1">
    {{-- Step indicator --}}
    <div class="mb-6 flex items-center justify-center gap-0">
        @for ($i = 1; $i <= 2; $i++)
            <div class="flex items-center">
                <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold
                    {{ $step >= $i ? 'bg-navy-800 text-white' : 'bg-zinc-200 text-zinc-500' }}">
                    {{ $i }}
                </div>
                @if ($i < 2)
                    <div class="h-px w-10 {{ $step > $i ? 'bg-navy-800' : 'bg-zinc-200' }}"></div>
                @endif
            </div>
        @endfor
    </div>

    {{-- Step 1: Package & Payment --}}
    @if ($step === 1)
        <div>
            <flux:heading size="lg">{{ __('Package & payment') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Where is the package going and how will you pay?') }}</flux:text>
        </div>

        <flux:field>
            <flux:label>{{ __('Destination country') }}</flux:label>
            <flux:select wire:model.live="destination_country">
                @foreach($this->countries as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="destination_country" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Package weight (kg)') }}</flux:label>
            <flux:input type="number" wire:model="weight_kg" min="0.01" step="0.01" placeholder="e.g. 2.5" />
            <flux:error name="weight_kg" />
        </flux:field>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('Payment amount') }}</flux:label>
                <flux:input type="number" wire:model="payment_amount" min="0.01" step="0.01" placeholder="e.g. 50" />
                <flux:error name="payment_amount" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Currency') }}</flux:label>
                <flux:select wire:model="payment_currency">
                    @foreach($this->currencies as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="payment_currency" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>{{ __('Payment method') }}</flux:label>
            <flux:select wire:model="payment_method">
                <flux:select.option value="">{{ __('Select method') }}</flux:select.option>
                @foreach($this->paymentMethods as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="payment_method" />
        </flux:field>

        <div class="flex justify-end gap-3 border-t border-zinc-100 pt-4">
            <flux:button wire:click="$parent.showCreateModal = false" variant="ghost">{{ __('Cancel') }}</flux:button>
            <flux:button wire:click="nextStep" variant="primary">{{ __('Next') }} →</flux:button>
        </div>
    @endif

    {{-- Step 2: Describe your package --}}
    @if ($step === 2)
        <div>
            <flux:heading size="lg">{{ __('Describe your package') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Help travelers understand what they will be carrying.') }}</flux:text>
        </div>

        <flux:field>
            <flux:label>{{ __('Description') }}</flux:label>
            <flux:textarea
                wire:model="description"
                rows="5"
                placeholder="Describe your item and provide logistics details. Include:
• Pickup address (city / area)
• Drop-off address in the destination country
• What the item is
• Any special handling notes"
            />
            <flux:error name="description" />
        </flux:field>

        <div>
            <flux:label>{{ __('Item photo') }} <span class="text-zinc-400">({{ __('optional') }})</span></flux:label>
            <div class="mt-1">
                <input
                    type="file"
                    wire:model="item_image"
                    accept="image/*"
                    class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded-lg file:border-0 file:bg-navy-800 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-navy-700"
                />
                <flux:error name="item_image" />
            </div>
            @if($item_image)
                <div class="mt-2">
                    <img src="{{ $item_image->temporaryUrl() }}" alt="{{ __('Preview') }}" class="h-24 rounded-lg object-cover" />
                </div>
            @endif
        </div>

        <flux:field>
            <flux:label>{{ __('Needed by') }}</flux:label>
            <flux:select wire:model="expires_in_days">
                <flux:select.option value="1">{{ __('1 day') }}</flux:select.option>
                <flux:select.option value="3">{{ __('3 days') }}</flux:select.option>
                <flux:select.option value="7">{{ __('7 days') }}</flux:select.option>
                <flux:select.option value="14">{{ __('14 days') }}</flux:select.option>
                <flux:select.option value="30">{{ __('30 days') }}</flux:select.option>
            </flux:select>
        </flux:field>

        <div class="flex justify-between gap-3 border-t border-zinc-100 pt-4">
            <flux:button wire:click="prevStep" variant="ghost">← {{ __('Back') }}</flux:button>
            <flux:button wire:click="create" variant="primary" wire:loading.attr="disabled">
                {{ __('Post Request') }}
            </flux:button>
        </div>
    @endif
</div>
