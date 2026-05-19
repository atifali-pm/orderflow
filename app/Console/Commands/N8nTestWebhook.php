<?php

namespace App\Console\Commands;

use App\Events\OrderCancelled;
use App\Events\OrderPaid;
use App\Events\OrderPlaced;
use App\Events\OrderShipped;
use App\Models\Order;
use Illuminate\Console\Command;

class N8nTestWebhook extends Command
{
    protected $signature = 'n8n:test-webhook {event=order.placed} {--order=}';

    protected $description = 'Fire an order event to verify the n8n webhook round trip';

    public function handle(): int
    {
        $eventMap = [
            'order.placed' => OrderPlaced::class,
            'order.paid' => OrderPaid::class,
            'order.shipped' => OrderShipped::class,
            'order.cancelled' => OrderCancelled::class,
        ];

        $eventName = (string) $this->argument('event');

        if (! isset($eventMap[$eventName])) {
            $this->error("Unknown event {$eventName}. Use one of: ".implode(', ', array_keys($eventMap)));

            return self::FAILURE;
        }

        $orderId = $this->option('order');
        $order = $orderId
            ? Order::find($orderId)
            : Order::query()->latest('id')->first();

        if (! $order) {
            $this->error('No order found. Pass --order=<id> or seed the database first.');

            return self::FAILURE;
        }

        $this->info("Dispatching {$eventName} for order {$order->reference}.");
        $eventMap[$eventName]::dispatch($order);
        $this->info('Event dispatched. Watch Horizon at /horizon for the queued job.');

        return self::SUCCESS;
    }
}
