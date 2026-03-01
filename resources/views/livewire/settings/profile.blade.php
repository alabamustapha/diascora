<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name, contact info, and preferences')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="cursor-pointer text-sm" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !text-green-600 !dark:text-green-400">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Phone & Messaging --}}
            <div class="space-y-3">
                <flux:input wire:model="phone_number" :label="__('Phone number')" type="tel" autocomplete="tel"
                    placeholder="+250 700 000 000" />
                <div class="flex flex-wrap gap-4">
                    <flux:checkbox wire:model="whatsapp_enabled" :label="__('Available on WhatsApp')" />
                    <flux:checkbox wire:model="telegram_enabled" :label="__('Available on Telegram')" />
                </div>
            </div>

            {{-- Countries --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Country of origin') }}</flux:label>
                    <flux:select wire:model="country_of_origin">
                        <flux:select.option value="">{{ __('Select country') }}</flux:select.option>
                        @foreach($this->availableCountries() as $code => $name)
                            <flux:select.option :value="$code">{{ $name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="country_of_origin" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Country of residence') }}</flux:label>
                    <flux:select wire:model="country_of_residence">
                        <flux:select.option value="">{{ __('Select country') }}</flux:select.option>
                        @foreach($this->availableCountries() as $code => $name)
                            <flux:select.option :value="$code">{{ $name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="country_of_residence" />
                </flux:field>
            </div>

            {{-- Payment Methods --}}
            <div>
                <flux:label class="mb-2 block">{{ __('Preferred payment methods') }}</flux:label>
                <div class="flex flex-wrap gap-3">
                    @foreach($this->availablePaymentMethods() as $value => $label)
                        <flux:checkbox wire:model="payment_methods" :value="$value" :label="$label" />
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:settings.delete-user-form />
        @endif
    </x-settings.layout>
</section>
