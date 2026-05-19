<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            ['sku' => 'SKU-1001', 'description' => 'Hand-bound notebook', 'price' => 18.50],
            ['sku' => 'SKU-1002', 'description' => 'Linen tote bag', 'price' => 24.00],
            ['sku' => 'SKU-1003', 'description' => 'Ceramic mug, navy', 'price' => 14.75],
            ['sku' => 'SKU-1004', 'description' => 'Brass desk lamp', 'price' => 89.00],
            ['sku' => 'SKU-1005', 'description' => 'Cold brew kit', 'price' => 42.00],
            ['sku' => 'SKU-1006', 'description' => 'Walnut coaster set', 'price' => 32.50],
        ];

        $customers = Customer::factory(6)->create();

        foreach ($customers as $customer) {
            $orderCount = random_int(1, 3);

            for ($i = 0; $i < $orderCount; $i++) {
                $status = collect(Order::STATUSES)->random();

                $order = Order::create([
                    'customer_id' => $customer->id,
                    'reference' => 'ORD-'.strtoupper(Str::random(8)),
                    'status' => $status,
                    'total' => 0,
                    'notes' => null,
                    'placed_at' => now()->subDays(random_int(0, 14)),
                ]);

                $picks = collect($catalog)->shuffle()->take(random_int(1, 3));

                foreach ($picks as $product) {
                    $quantity = random_int(1, 4);
                    OrderItem::create([
                        'order_id' => $order->id,
                        'sku' => $product['sku'],
                        'description' => $product['description'],
                        'quantity' => $quantity,
                        'unit_price' => $product['price'],
                        'line_total' => round($quantity * $product['price'], 2),
                    ]);
                }

                $order->recalculateTotal();
            }
        }
    }
}
