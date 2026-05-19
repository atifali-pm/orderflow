<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Order detail')]
class Show extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load(['customer', 'items']);
    }

    public function render()
    {
        return view('livewire.orders.show');
    }
}
