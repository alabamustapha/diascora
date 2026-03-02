<?php

namespace Database\Factories;

use App\Models\DeliveryRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryOffer>
 */
class DeliveryOfferFactory extends Factory
{
    public function definition(): array
    {
        return [
            'delivery_request_id' => DeliveryRequest::factory(),
            'user_id' => User::factory(),
            'message' => fake()->paragraph(),
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
