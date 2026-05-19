<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $price = $this->faker->randomFloat(2, 5, 250);

        return [
            'order_id' => Order::factory(),
            'sku' => strtoupper($this->faker->bothify('SKU-####')),
            'description' => $this->faker->words(3, true),
            'quantity' => $quantity,
            'unit_price' => $price,
            'line_total' => $quantity * $price,
        ];
    }
}
