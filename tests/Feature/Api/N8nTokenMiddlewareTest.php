<?php

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('n8n.api_token', 'unit-test-token');
});

it('rejects api requests without a bearer token', function () {
    $order = Order::factory()->for(Customer::factory())->create();

    $this->getJson("/api/orders/{$order->id}")
        ->assertStatus(401)
        ->assertJson(['message' => 'Invalid n8n API token.']);
});

it('rejects api requests with a wrong bearer token', function () {
    $order = Order::factory()->for(Customer::factory())->create();

    $this->withToken('not-the-right-token')
        ->getJson("/api/orders/{$order->id}")
        ->assertStatus(401);
});

it('accepts api requests with the configured bearer token', function () {
    $order = Order::factory()->for(Customer::factory())->create();

    $this->withToken('unit-test-token')
        ->getJson("/api/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('order.id', $order->id)
        ->assertJsonPath('order.reference', $order->reference);
});
