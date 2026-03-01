<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\ExchangeRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExchangeInterest>
 */
class ExchangeInterestFactory extends Factory
{
    public function definition(): array
    {
        $paymentMethods = array_column(PaymentMethod::cases(), 'value');

        return [
            'exchange_request_id' => ExchangeRequest::factory(),
            'user_id' => User::factory(),
            'comment' => fake()->sentence(),
            'payment_method_sending' => fake()->randomElement($paymentMethods),
            'payment_method_receiving' => fake()->randomElement($paymentMethods),
            'status' => 'pending',
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'accepted']);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'rejected']);
    }
}
