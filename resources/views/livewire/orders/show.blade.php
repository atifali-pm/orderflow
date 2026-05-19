<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Order <span class="font-mono">{{ $order->reference }}</span>
            </h2>
            <a href="{{ route('orders.index') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-800">Back to orders</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-md px-4 py-2">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-xs uppercase text-gray-500 font-semibold tracking-wider">Customer</div>
                    <div class="mt-1 text-sm font-medium text-gray-900">{{ $order->customer->name }}</div>
                    <div class="text-sm text-gray-600">{{ $order->customer->email }}</div>
                    @if ($order->customer->company)
                        <div class="text-xs text-gray-500 mt-1">{{ $order->customer->company }}</div>
                    @endif
                </div>

                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-xs uppercase text-gray-500 font-semibold tracking-wider">Status</div>
                    <div class="mt-1">
                        <span @class([
                            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                            'bg-yellow-100 text-yellow-800' => $order->status === 'pending',
                            'bg-emerald-100 text-emerald-800' => $order->status === 'paid',
                            'bg-sky-100 text-sky-800' => $order->status === 'shipped',
                            'bg-rose-100 text-rose-800' => $order->status === 'cancelled',
                        ])>
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 mt-2">
                        Placed {{ optional($order->placed_at)->format('M j, Y \a\t g:i a') ?? 'n/a' }}
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @if ($order->status === 'pending')
                            <button type="button" wire:click="markPaid"
                                    class="text-xs px-2 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700">
                                Mark paid
                            </button>
                        @endif

                        @if (in_array($order->status, ['pending', 'paid'], true))
                            <button type="button" wire:click="markShipped"
                                    class="text-xs px-2 py-1 rounded bg-sky-600 text-white hover:bg-sky-700">
                                Mark shipped
                            </button>
                        @endif

                        @if ($order->status !== 'cancelled')
                            <button type="button" wire:click="cancel"
                                    wire:confirm="Cancel this order?"
                                    class="text-xs px-2 py-1 rounded bg-rose-600 text-white hover:bg-rose-700">
                                Cancel
                            </button>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-xs uppercase text-gray-500 font-semibold tracking-wider">Total</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format((float) $order->total, 2) }}</div>
                    <div class="text-xs text-gray-500">{{ $order->items->count() }} line item{{ $order->items->count() === 1 ? '' : 's' }}</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Unit price</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Line total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-6 py-3 text-sm font-mono text-gray-900">{{ $item->sku }}</td>
                                <td class="px-6 py-3 text-sm text-gray-700">{{ $item->description }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $item->quantity }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-700">${{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-900 font-medium">${{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($order->notes)
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-xs uppercase text-gray-500 font-semibold tracking-wider">Notes</div>
                    <p class="mt-1 text-sm text-gray-700">{{ $order->notes }}</p>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-sm p-6" wire:poll.5s="refreshTimeline">
                <div class="flex items-center justify-between">
                    <div class="text-xs uppercase text-gray-500 font-semibold tracking-wider">Automation timeline</div>
                    @if ($order->invoice_number)
                        <span class="text-xs text-gray-500">Invoice <span class="font-mono text-gray-700">{{ $order->invoice_number }}</span></span>
                    @endif
                </div>

                @if ($order->automationLogs->isEmpty())
                    <div class="mt-3 border border-dashed border-gray-300 rounded-md p-6 text-center text-sm text-gray-500">
                        No automation events yet. Place or update an order; the n8n workflow will populate this timeline.
                    </div>
                @else
                    <ol class="mt-4 space-y-3">
                        @foreach ($order->automationLogs as $log)
                            <li class="flex gap-3">
                                <div class="mt-1 h-2 w-2 rounded-full flex-shrink-0 @if ($log->status === 'failed') bg-rose-500 @elseif ($log->status === 'info') bg-sky-500 @else bg-emerald-500 @endif"></div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-medium text-gray-900 font-mono">{{ $log->step }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->received_at->diffForHumans() }}</div>
                                    </div>
                                    @if ($log->payload)
                                        <pre class="mt-1 text-xs bg-gray-50 border border-gray-100 rounded p-2 overflow-x-auto">{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}</pre>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>
    </div>
</div>
