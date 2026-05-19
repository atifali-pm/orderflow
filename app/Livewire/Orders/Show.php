<?php

namespace App\Livewire\Orders;

use App\Events\OrderCancelled;
use App\Events\OrderPaid;
use App\Events\OrderShipped;
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
        $this->order = $order->load(['customer', 'items', 'automationLogs']);
    }

    public function refreshTimeline(): void
    {
        $this->order = $this->order->fresh(['customer', 'items', 'automationLogs']);
    }

    public function markPaid(): void
    {
        if ($this->order->status !== 'pending') {
            return;
        }

        $this->order->update(['status' => 'paid']);
        OrderPaid::dispatch($this->order->fresh(['customer', 'items']));
        $this->refresh('Marked paid. Webhook queued for n8n.');
    }

    public function markShipped(): void
    {
        if (! in_array($this->order->status, ['pending', 'paid'], true)) {
            return;
        }

        $this->order->update(['status' => 'shipped']);
        OrderShipped::dispatch($this->order->fresh(['customer', 'items']));
        $this->refresh('Marked shipped. Webhook queued for n8n.');
    }

    public function cancel(): void
    {
        if ($this->order->status === 'cancelled') {
            return;
        }

        $this->order->update(['status' => 'cancelled']);
        OrderCancelled::dispatch($this->order->fresh(['customer', 'items']));
        $this->refresh('Order cancelled. Webhook queued for n8n.');
    }

    public function render()
    {
        return view('livewire.orders.show');
    }

    private function refresh(string $message): void
    {
        $this->order = $this->order->fresh(['customer', 'items', 'automationLogs']);
        session()->flash('status', $message);
    }
}
