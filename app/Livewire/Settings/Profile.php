<?php

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Profile settings')]
class Profile extends Component
{
    use ProfileValidationRules;

    public string $name = '';

    public string $email = '';

    public ?string $phone_number = null;

    public bool $whatsapp_enabled = false;

    public bool $telegram_enabled = false;

    public ?string $country_of_origin = null;

    public ?string $country_of_residence = null;

    /** @var list<string> */
    public array $payment_methods = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone_number = $user->phone_number;
        $this->whatsapp_enabled = (bool) $user->whatsapp_enabled;
        $this->telegram_enabled = (bool) $user->telegram_enabled;
        $this->country_of_origin = $user->country_of_origin;
        $this->country_of_residence = $user->country_of_residence;
        $this->payment_methods = (array) ($user->payment_methods ?? []);
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            ...$this->profileRules($user->id),
            'phone_number' => ['nullable', 'string', 'max:20'],
            'whatsapp_enabled' => ['boolean'],
            'telegram_enabled' => ['boolean'],
            'country_of_origin' => ['nullable', 'string', 'size:2'],
            'country_of_residence' => ['nullable', 'string', 'size:2'],
            'payment_methods' => ['nullable', 'array'],
            'payment_methods.*' => ['string'],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }

    /** @return array<string, string> */
    public function availableCountries(): array
    {
        return [
            'NG' => 'Nigeria',
            'KE' => 'Kenya',
            'TZ' => 'Tanzania',
            'UG' => 'Uganda',
            'BJ' => 'Benin',
            'CM' => 'Cameroon',
            'RW' => 'Rwanda',
            'GH' => 'Ghana',
            'ZA' => 'South Africa',
            'ET' => 'Ethiopia',
            'SN' => 'Senegal',
            'CI' => "Côte d'Ivoire",
            'CD' => 'DR Congo',
            'TG' => 'Togo',
            'BF' => 'Burkina Faso',
            'ML' => 'Mali',
            'NE' => 'Niger',
            'GN' => 'Guinea',
            'MG' => 'Madagascar',
            'AO' => 'Angola',
        ];
    }

    /** @return array<string, string> */
    public function availablePaymentMethods(): array
    {
        return \App\Enums\PaymentMethod::options();
    }
}
