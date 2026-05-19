<?php

use App\Jobs\SyncToN8nJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('builds payload, signs it, and posts to the matching webhook url', function () {
    Http::fake([
        '*' => Http::response(['ok' => true], 200),
    ]);

    config()->set('n8n.webhook_url_base', 'http://n8n.test/webhook');
    config()->set('n8n.hmac_secret', 'test-secret');

    $customer = Customer::factory()->create(['name' => 'Acme Co', 'email' => 'ops@acme.test']);
    $order = Order::factory()->for($customer)->create([
        'reference' => 'ORD-ABC123',
        'status' => 'pending',
        'total' => 0,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'sku' => 'SKU-9',
        'description' => 'A widget',
        'quantity' => 2,
        'unit_price' => 12.50,
        'line_total' => 25.00,
    ]);
    $order->recalculateTotal();

    (new SyncToN8nJob('order.placed', $order->id))
        ->handle(app(App\Support\N8n\WebhookDispatcher::class));

    Http::assertSent(function ($request) use ($order) {
        $body = json_decode($request->body(), true);

        return $request->url() === 'http://n8n.test/webhook/order-placed'
            && $body['event'] === 'order.placed'
            && $body['order']['reference'] === $order->reference
            && $body['order']['customer']['email'] === 'ops@acme.test'
            && $body['order']['items'][0]['sku'] === 'SKU-9'
            && (float) $body['order']['total'] === 25.00;
    });
});

it('throws when n8n returns a failure status so the job retries', function () {
    Http::fake(['*' => Http::response('boom', 500)]);

    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create();

    $job = new SyncToN8nJob('order.placed', $order->id);

    expect(fn () => $job->handle(app(App\Support\N8n\WebhookDispatcher::class)))
        ->toThrow(RuntimeException::class);
});

it('skips when the order has been deleted before the job runs', function () {
    Http::fake();

    $job = new SyncToN8nJob('order.placed', 999999);
    $job->handle(app(App\Support\N8n\WebhookDispatcher::class));

    Http::assertNothingSent();
});
