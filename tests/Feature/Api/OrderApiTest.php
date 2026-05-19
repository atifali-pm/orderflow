<?php

use App\Models\AutomationLog;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('n8n.api_token', 'unit-test-token');
    Redis::flushdb();
});

it('updates an order with invoice number and external id', function () {
    $order = Order::factory()->for(Customer::factory())->create([
        'status' => 'pending',
        'invoice_number' => null,
    ]);

    $this->withToken('unit-test-token')
        ->withHeader('Idempotency-Key', 'patch-1')
        ->patchJson("/api/orders/{$order->id}", [
            'invoice_number' => 'INV-2026-0001',
            'external_id' => 'stripe_ch_test',
            'status' => 'paid',
        ])
        ->assertOk()
        ->assertJsonPath('order.invoice_number', 'INV-2026-0001')
        ->assertJsonPath('order.status', 'paid');

    expect($order->fresh()->invoice_number)->toBe('INV-2026-0001')
        ->and($order->fresh()->external_id)->toBe('stripe_ch_test')
        ->and($order->fresh()->status)->toBe('paid');
});

it('rejects an unknown status value', function () {
    $order = Order::factory()->for(Customer::factory())->create();

    $this->withToken('unit-test-token')
        ->withHeader('Idempotency-Key', 'patch-bad')
        ->patchJson("/api/orders/{$order->id}", ['status' => 'exploded'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

it('appends an automation log entry', function () {
    $order = Order::factory()->for(Customer::factory())->create();

    $this->withToken('unit-test-token')
        ->withHeader('Idempotency-Key', 'log-1')
        ->postJson("/api/orders/{$order->id}/automation-log", [
            'step' => 'email.sent',
            'status' => 'ok',
            'payload' => ['to' => 'ops@acme.test'],
        ])
        ->assertCreated()
        ->assertJsonPath('log.step', 'email.sent');

    expect(AutomationLog::where('order_id', $order->id)->count())->toBe(1);
});

it('requires Idempotency-Key on writes', function () {
    $order = Order::factory()->for(Customer::factory())->create();

    $this->withToken('unit-test-token')
        ->patchJson("/api/orders/{$order->id}", ['status' => 'paid'])
        ->assertStatus(400)
        ->assertJsonPath('message', 'Idempotency-Key header is required for this endpoint.');
});

it('replays the cached response when the same Idempotency-Key is reused', function () {
    $order = Order::factory()->for(Customer::factory())->create();

    $first = $this->withToken('unit-test-token')
        ->withHeader('Idempotency-Key', 'replay-1')
        ->postJson("/api/orders/{$order->id}/automation-log", [
            'step' => 'webhook.received',
        ])
        ->assertCreated();

    $second = $this->withToken('unit-test-token')
        ->withHeader('Idempotency-Key', 'replay-1')
        ->postJson("/api/orders/{$order->id}/automation-log", [
            'step' => 'webhook.received',
        ])
        ->assertCreated()
        ->assertHeader('X-Idempotency-Replay', 'true');

    expect($first->json('log.id'))->toBe($second->json('log.id'))
        ->and(AutomationLog::where('order_id', $order->id)->count())->toBe(1);
});
