<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExchangeRequest>
 */
class ExchangeRequestFactory extends Factory
{
    public function definition(): array
    {
        $diasporaCurrencies = Currency::diasporaCurrencies();
        $fromCurrency = fake()->randomElement($diasporaCurrencies);
        $fromAmount = fake()->randomFloat(2, 100, 10000);

        // Base rate for RWF: NGN~0.63, KES~6.84, TZS~0.038, UGX~0.27, XOF~1.54, XAF~1.54
        $baseRates = [
            'NGN' => 0.63,
            'KES' => 6.84,
            'TZS' => 0.038,
            'UGX' => 0.27,
            'XOF' => 1.54,
            'XAF' => 1.54,
        ];

        $officialRate = $baseRates[$fromCurrency] ?? 1.0;
        // Offered rate within ±5% of official
        $offeredRate = $officialRate * (1 + fake()->randomFloat(4, -0.05, 0.05));
        $toAmount = round($fromAmount * $offeredRate, 2);

        $paymentMethods = array_column(PaymentMethod::cases(), 'value');

        return [
            'user_id' => User::factory(),
            'is_anonymous' => false,
            'from_currency' => $fromCurrency,
            'to_currency' => Currency::RWF->value,
            'from_amount' => $fromAmount,
            'to_amount' => $toAmount,
            'official_rate_at_posting' => $officialRate,
            'offered_rate' => $offeredRate,
            'payment_method_sending' => fake()->randomElement($paymentMethods),
            'payment_method_receiving' => fake()->randomElement($paymentMethods),
            'notes' => fake()->optional()->sentence(),
            'status' => 'open',
            'accepted_interest_id' => null,
            'expires_at' => fake()->optional()->dateTimeBetween('+1 day', '+30 days'),
        ];
    }

    public function matched(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'matched']);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'closed']);
    }

    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => ['is_anonymous' => true]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
            'expires_at' => now()->subDay(),
        ]);
    }
}
