<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    public function show(Order $order): JsonResponse
    {
        $order->load(['customer', 'items']);

        return response()->json([
            'order' => $this->transform($order),
        ]);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', 'string', 'in:'.implode(',', Order::STATUSES)],
            'invoice_number' => ['sometimes', 'nullable', 'string', 'max:64'],
            'external_id' => ['sometimes', 'nullable', 'string', 'max:128'],
        ]);

        $order->fill($data);
        $order->save();

        return response()->json([
            'order' => $this->transform($order->fresh(['customer', 'items'])),
        ]);
    }

    private function transform(Order $order): array
    {
        return [
            'id' => $order->id,
            'reference' => $order->reference,
            'status' => $order->status,
            'total' => (float) $order->total,
            'invoice_number' => $order->invoice_number,
            'external_id' => $order->external_id,
            'placed_at' => optional($order->placed_at)->toIso8601String(),
        ];
    }
}
