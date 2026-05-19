<?php

namespace App\Jobs;

use App\Models\Order;
use App\Support\N8n\WebhookDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncToN8nJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public int $backoff = 10;

    public function __construct(
        public string $event,
        public int $orderId,
    ) {
        $this->onQueue('n8n');
    }

    public function handle(WebhookDispatcher $dispatcher): void
    {
        $order = Order::with(['customer', 'items'])->find($this->orderId);

        if (! $order) {
            Log::warning('orderflow.n8n.sync skipped', [
                'event' => $this->event,
                'order_id' => $this->orderId,
                'reason' => 'order_not_found',
            ]);

            return;
        }

        $payload = $this->buildPayload($order);

        $response = $dispatcher->send($this->event, $payload);

        Log::info('orderflow.n8n.sync sent', [
            'event' => $this->event,
            'order_id' => $order->id,
            'status' => $response->status(),
        ]);

        if ($response->failed()) {
            throw new \RuntimeException(sprintf(
                'n8n webhook for %s returned HTTP %d',
                $this->event,
                $response->status()
            ));
        }
    }

    private function buildPayload(Order $order): array
    {
        return [
            'event' => $this->event,
            'emitted_at' => now()->toIso8601String(),
            'order' => [
                'id' => $order->id,
                'reference' => $order->reference,
                'status' => $order->status,
                'total' => (float) $order->total,
                'notes' => $order->notes,
                'placed_at' => optional($order->placed_at)->toIso8601String(),
                'customer' => [
                    'id' => $order->customer->id,
                    'name' => $order->customer->name,
                    'email' => $order->customer->email,
                    'company' => $order->customer->company,
                ],
                'items' => $order->items->map(fn ($item) => [
                    'sku' => $item->sku,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                ])->all(),
            ],
        ];
    }
}
