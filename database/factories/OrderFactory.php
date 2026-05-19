<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'reference' => 'ORD-'.strtoupper(Str::random(8)),
            'status' => $this->faker->randomElement(Order::STATUSES),
            'total' => 0,
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'placed_at' => now(),
        ];
    }
}
