<?php

use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Dashboard;
use App\Livewire\Orders\Create as OrdersCreate;
use App\Livewire\Orders\Index as OrdersIndex;
use App\Livewire\Orders\Show as OrdersShow;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Route::get('orders', OrdersIndex::class)->name('orders.index');
    Route::get('orders/create', OrdersCreate::class)->name('orders.create');
    Route::get('orders/{order}', OrdersShow::class)->name('orders.show');

    Route::get('customers', CustomersIndex::class)->name('customers.index');
});

require __DIR__.'/auth.php';
