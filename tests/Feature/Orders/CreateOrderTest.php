<?php

use App\Jobs\SyncToN8nJob;
use App\Livewire\Orders\Create;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('creates an order with line items end to end', function () {
    Bus::fake([SyncToN8nJob::class]);

    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    actingAs($user);

    Livewire::test(Create::class)
        ->set('customer_id', $customer->id)
        ->set('notes', 'Rush ship if possible')
        ->set('items.0.sku', 'SKU-2001')
        ->set('items.0.description', 'Sample item')
        ->set('items.0.quantity', 3)
        ->set('items.0.unit_price', 19.50)
        ->call('addItem')
        ->set('items.1.sku', 'SKU-2002')
        ->set('items.1.description', 'Another item')
        ->set('items.1.quantity', 2)
        ->set('items.1.unit_price', 10.00)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect(Order::count())->toBe(1);

    $order = Order::with('items')->first();

    expect($order->customer_id)->toBe($customer->id)
        ->and($order->status)->toBe('pending')
        ->and($order->notes)->toBe('Rush ship if possible')
        ->and((float) $order->total)->toBe(78.50)
        ->and($order->items)->toHaveCount(2)
        ->and((float) $order->items->firstWhere('sku', 'SKU-2001')->line_total)->toBe(58.50)
        ->and((float) $order->items->firstWhere('sku', 'SKU-2002')->line_total)->toBe(20.00);
});

it('rejects creation when no customer is chosen', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test(Create::class)
        ->set('items.0.sku', 'SKU-3001')
        ->set('items.0.description', 'Lonely item')
        ->set('items.0.quantity', 1)
        ->set('items.0.unit_price', 5.00)
        ->call('save')
        ->assertHasErrors(['customer_id']);

    expect(Order::count())->toBe(0);
});
