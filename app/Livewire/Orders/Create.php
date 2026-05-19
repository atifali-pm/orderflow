<?php

namespace App\Livewire\Orders;

use App\Events\OrderPlaced;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('New order')]
class Create extends Component
{
    public ?int $customer_id = null;

    public string $notes = '';

    public array $items = [];

    public function mount(): void
    {
        $this->items = [
            $this->blankItem(),
        ];
    }

    public function addItem(): void
    {
        $this->items[] = $this->blankItem();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if (empty($this->items)) {
            $this->items[] = $this->blankItem();
        }
    }

    public function getTotalProperty(): float
    {
        return collect($this->items)
            ->sum(fn ($item) => (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0));
    }

    public function save()
    {
        $data = $this->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku' => ['required', 'string', 'max:64'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $order = DB::transaction(function () use ($data) {
            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'reference' => 'ORD-'.strtoupper(Str::random(8)),
                'status' => 'pending',
                'total' => 0,
                'notes' => $data['notes'] ?: null,
                'placed_at' => now(),
            ]);

            foreach ($data['items'] as $row) {
                $quantity = (int) $row['quantity'];
                $price = (float) $row['unit_price'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'sku' => $row['sku'],
                    'description' => $row['description'],
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'line_total' => round($quantity * $price, 2),
                ]);
            }

            $order->recalculateTotal();

            return $order;
        });

        OrderPlaced::dispatch($order);

        session()->flash('status', "Order {$order->reference} created. Webhook queued for n8n.");

        return $this->redirectRoute('orders.show', $order, navigate: true);
    }

    public function render()
    {
        return view('livewire.orders.create', [
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    private function blankItem(): array
    {
        return [
            'sku' => '',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
        ];
    }
}
