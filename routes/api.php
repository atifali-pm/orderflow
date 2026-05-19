<?php

use App\Http\Controllers\Api\AutomationLogController;
use App\Http\Controllers\Api\OrderApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('n8n.token')->prefix('orders')->group(function () {
    Route::get('{order}', [OrderApiController::class, 'show'])->name('api.orders.show');

    Route::middleware('idempotency')->group(function () {
        Route::patch('{order}', [OrderApiController::class, 'update'])->name('api.orders.update');
        Route::post('{order}/automation-log', [AutomationLogController::class, 'store'])->name('api.orders.automation-log');
    });
});
