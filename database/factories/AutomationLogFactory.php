<?php

namespace Database\Factories;

use App\Models\AutomationLog;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class AutomationLogFactory extends Factory
{
    protected $model = AutomationLog::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'step' => $this->faker->randomElement([
                'webhook.received',
                'email.sent',
                'invoice.generated',
                'crm.synced',
            ]),
            'status' => $this->faker->randomElement(['ok', 'failed']),
            'payload' => ['note' => $this->faker->sentence()],
            'received_at' => now(),
        ];
    }
}
