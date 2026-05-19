<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutomationLog;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutomationLogController extends Controller
{
    public function store(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'step' => ['required', 'string', 'max:128'],
            'status' => ['sometimes', 'string', 'in:ok,failed,info'],
            'payload' => ['sometimes', 'array'],
            'received_at' => ['sometimes', 'date'],
        ]);

        $log = AutomationLog::create([
            'order_id' => $order->id,
            'step' => $data['step'],
            'status' => $data['status'] ?? 'ok',
            'payload' => $data['payload'] ?? null,
            'received_at' => $data['received_at'] ?? now(),
        ]);

        return response()->json([
            'log' => [
                'id' => $log->id,
                'step' => $log->step,
                'status' => $log->status,
                'received_at' => $log->received_at->toIso8601String(),
            ],
        ], 201);
    }
}
