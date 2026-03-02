<?php

namespace App\Livewire\Exchange;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Models\ExchangeRequest;
use App\Services\ExchangeRateService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateRequest extends Component
{
    public string $from_currency = '';

    public string $to_currency = 'RWF';

    public string $from_amount = '';

    public string $to_amount = '';

    public string $official_rate = '';

    public string $offered_rate = '';

    public string $payment_method_sending = '';

    public string $payment_method_receiving = '';

    public string $notes = '';

    public bool $is_anonymous = false;

    public int $expires_in_days = 7;

    public int $step = 1;

    public bool $submitting = false;

    public function mount(): void
    {
        $this->from_currency = Currency::NGN->value;
        $this->loadOfficialRate();
    }

    #[Computed]
    public function currencies(): array
    {
        return Currency::options();
    }

    #[Computed]
    public function paymentMethods(): array
    {
        return PaymentMethod::options();
    }

    #[Computed]
    public function rateDiffPercent(): ?float
    {
        $official = (float) $this->official_rate;
        $offered = (float) $this->offered_rate;

        if ($official <= 0 || $offered <= 0) {
            return null;
        }

        return (($offered - $official) / $official) * 100;
    }

    public function updatedFromCurrency(): void
    {
        $this->loadOfficialRate();
    }

    public function updatedToCurrency(): void
    {
        $this->loadOfficialRate();
    }

    public function updatedFromAmount(): void
    {
        if ($this->official_rate && (float) $this->from_amount > 0) {
            $this->to_amount = (string) round((float) $this->from_amount * (float) $this->official_rate, 2);
            $this->offered_rate = $this->official_rate;
        }
    }

    public function updatedToAmount(): void
    {
        if ((float) $this->from_amount > 0 && (float) $this->to_amount > 0) {
            $this->offered_rate = (string) round((float) $this->to_amount / (float) $this->from_amount, 6);
        }
    }

    public function loadOfficialRate(): void
    {
        if (! $this->from_currency || ! $this->to_currency) {
            return;
        }

        $service = app(ExchangeRateService::class);
        $rate = $service->getRate($this->from_currency, $this->to_currency);

        if ($rate !== null) {
            $this->official_rate = (string) round($rate, 6);
            if ((float) $this->from_amount > 0) {
                $this->to_amount = (string) round((float) $this->from_amount * $rate, 2);
                $this->offered_rate = $this->official_rate;
            }
        } else {
            $this->official_rate = '';
        }
    }

    /** @return array<string, array<string>> */
    private function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'from_currency' => ['required', 'string', 'size:3'],
                'to_currency' => ['required', 'string', 'size:3'],
            ],
            2 => [
                'from_amount' => ['required', 'numeric', 'min:0.01'],
                'to_amount' => ['required', 'numeric', 'min:0.01'],
                'official_rate' => ['required', 'numeric', 'min:0'],
                'offered_rate' => ['required', 'numeric', 'min:0'],
                'payment_method_sending' => ['required', 'string'],
                'payment_method_receiving' => ['required', 'string'],
            ],
            default => [
                'notes' => ['nullable', 'string', 'max:1000'],
                'is_anonymous' => ['boolean'],
                'expires_in_days' => ['required', 'integer', 'min:1', 'max:30'],
            ],
        };
    }

    public function nextStep(): void
    {
        $this->validate($this->rulesForStep($this->step));
        $this->step++;
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function create(): void
    {
        if ($this->submitting) {
            return;
        }

        $this->submitting = true;

        $this->validate([
            'from_currency' => ['required', 'string', 'size:3'],
            'to_currency' => ['required', 'string', 'size:3'],
            'from_amount' => ['required', 'numeric', 'min:0.01'],
            'to_amount' => ['required', 'numeric', 'min:0.01'],
            'official_rate' => ['required', 'numeric', 'min:0'],
            'offered_rate' => ['required', 'numeric', 'min:0'],
            'payment_method_sending' => ['required', 'string'],
            'payment_method_receiving' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_anonymous' => ['boolean'],
            'expires_in_days' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        ExchangeRequest::create([
            'user_id' => Auth::id(),
            'is_anonymous' => $this->is_anonymous,
            'from_currency' => $this->from_currency,
            'to_currency' => $this->to_currency,
            'from_amount' => (float) $this->from_amount,
            'to_amount' => (float) $this->to_amount,
            'official_rate_at_posting' => (float) $this->official_rate,
            'offered_rate' => (float) $this->offered_rate,
            'payment_method_sending' => $this->payment_method_sending,
            'payment_method_receiving' => $this->payment_method_receiving,
            'notes' => $this->notes ?: null,
            'status' => 'open',
            'expires_at' => now()->addDays($this->expires_in_days),
        ]);

        $this->dispatch('exchange-request-created');
    }

    public function render()
    {
        return view('livewire.exchange.create-request');
    }
}
