<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Customers')]
class Index extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.customers.index', [
            'customers' => Customer::withCount('orders')->orderBy('name')->paginate(15),
        ]);
    }
}
