<?php

namespace App\Livewire\Delivery;

use App\Enums\Currency;
use App\Enums\DeliveryCountry;
use App\Enums\PaymentMethod;
use App\Models\DeliveryRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateRequest extends Component
{
    use WithFileUploads;

    public string $destination_country = '';

    public string $weight_kg = '';

    public string $payment_amount = '';

    public string $payment_currency = '';

    public string $payment_method = '';

    public string $description = '';

    public $item_image = null;

    public int $expires_in_days = 7;

    public int $step = 1;

    public bool $submitting = false;

    public function mount(): void
    {
        $this->destination_country = DeliveryCountry::Nigeria->value;
        $this->payment_currency = Currency::NGN->value;
    }

    #[Computed]
    public function countries(): array
    {
        return DeliveryCountry::options();
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

    /** @return array<string, array<string>> */
    private function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'destination_country' => ['required', 'string'],
                'weight_kg' => ['required', 'numeric', 'min:0.01'],
                'payment_amount' => ['required', 'numeric', 'min:0.01'],
                'payment_currency' => ['required', 'string', 'size:3'],
                'payment_method' => ['required', 'string'],
            ],
            default => [
                'description' => ['required', 'string', 'min:20', 'max:2000'],
                'item_image' => ['nullable', 'image', 'max:2048'],
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
            'destination_country' => ['required', 'string'],
            'weight_kg' => ['required', 'numeric', 'min:0.01'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_currency' => ['required', 'string', 'size:3'],
            'payment_method' => ['required', 'string'],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'item_image' => ['nullable', 'image', 'max:2048'],
            'expires_in_days' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $imagePath = $this->item_image?->store('delivery-items', 'public');

        DeliveryRequest::create([
            'user_id' => Auth::id(),
            'destination_country' => $this->destination_country,
            'weight_kg' => (float) $this->weight_kg,
            'payment_amount' => (float) $this->payment_amount,
            'payment_currency' => $this->payment_currency,
            'payment_method' => $this->payment_method,
            'description' => $this->description,
            'item_image_path' => $imagePath ?: null,
            'status' => 'open',
            'expires_at' => now()->addDays($this->expires_in_days),
        ]);

        $this->dispatch('delivery-request-created');
    }

    public function render()
    {
        return view('livewire.delivery.create-request');
    }
}
