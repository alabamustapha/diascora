<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\DeliveryCountry;
use App\Enums\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryRequest>
 */
class DeliveryRequestFactory extends Factory
{
    public function definition(): array
    {
        $paymentMethods = array_column(PaymentMethod::cases(), 'value');
        $currencies = array_column(Currency::cases(), 'value');
        $countries = array_column(DeliveryCountry::cases(), 'value');

        return [
            'user_id' => User::factory(),
            'destination_country' => fake()->randomElement($countries),
            'weight_kg' => fake()->randomFloat(2, 0.5, 25.0),
            'payment_amount' => fake()->randomFloat(2, 10, 500),
            'payment_currency' => fake()->randomElement($currencies),
            'payment_method' => fake()->randomElement($paymentMethods),
            'description' => fake()->paragraph(3),
            'item_image_path' => null,
            'status' => 'open',
            'accepted_offer_id' => null,
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

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
            'expires_at' => now()->subDay(),
        ]);
    }

    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_image_path' => 'delivery-items/fake-image.jpg',
        ]);
    }
}
