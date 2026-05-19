<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Orders')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'status')]
    public string $statusFilter = '';

    #[Url(as: 'q')]
    public string $search = '';

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $term = trim($this->search);

        $orders = Order::query()
            ->with('customer')
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($term !== '', function ($q) use ($term) {
                $like = '%'.$term.'%';
                $q->where(function ($q) use ($like) {
                    $q->where('reference', 'ilike', $like)
                        ->orWhere('invoice_number', 'ilike', $like)
                        ->orWhereHas('customer', fn ($c) => $c
                            ->where('name', 'ilike', $like)
                            ->orWhere('email', 'ilike', $like));
                });
            })
            ->latest('placed_at')
            ->paginate(10);

        return view('livewire.orders.index', [
            'orders' => $orders,
            'statuses' => Order::STATUSES,
        ]);
    }
}
