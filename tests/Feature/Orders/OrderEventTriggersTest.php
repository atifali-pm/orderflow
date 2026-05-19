<?php

use App\Events\OrderCancelled;
use App\Events\OrderPaid;
use App\Events\OrderPlaced;
use App\Events\OrderShipped;
use App\Jobs\SyncToN8nJob;
use App\Livewire\Orders\Create as OrdersCreate;
use App\Livewire\Orders\Show as OrdersShow;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('dispatches OrderPlaced after a Livewire create succeeds', function () {
    Event::fake([OrderPlaced::class]);

    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    actingAs($user);

    Livewire::test(OrdersCreate::class)
        ->set('customer_id', $customer->id)
        ->set('items.0.sku', 'SKU-EVT')
        ->set('items.0.description', 'Triggers OrderPlaced')
        ->set('items.0.quantity', 1)
        ->set('items.0.unit_price', 10.0)
        ->call('save')
        ->assertHasNoErrors();

    Event::assertDispatched(OrderPlaced::class, function ($event) use ($customer) {
        return $event->order->customer_id === $customer->id;
    });
});

it('fires the correct event for each status-change button on Show', function () {
    Event::fake([OrderPaid::class, OrderShipped::class, OrderCancelled::class]);

    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    actingAs($user);

    $paidOrder = Order::factory()->for($customer)->create(['status' => 'pending']);
    Livewire::test(OrdersShow::class, ['order' => $paidOrder])->call('markPaid');
    Event::assertDispatched(OrderPaid::class);

    $shippedOrder = Order::factory()->for($customer)->create(['status' => 'paid']);
    Livewire::test(OrdersShow::class, ['order' => $shippedOrder])->call('markShipped');
    Event::assertDispatched(OrderShipped::class);

    $cancelledOrder = Order::factory()->for($customer)->create(['status' => 'pending']);
    Livewire::test(OrdersShow::class, ['order' => $cancelledOrder])->call('cancel');
    Event::assertDispatched(OrderCancelled::class);
});

it('queues SyncToN8nJob via the listener for every order event', function () {
    Bus::fake([SyncToN8nJob::class]);

    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create();

    OrderPlaced::dispatch($order);

    Bus::assertDispatched(SyncToN8nJob::class, function ($job) use ($order) {
        return $job->event === 'order.placed' && $job->orderId === $order->id;
    });
});
