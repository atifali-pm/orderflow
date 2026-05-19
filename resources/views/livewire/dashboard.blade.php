<div wire:poll.10s>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>

    <div class="py-8 space-y-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-xs uppercase text-gray-500 font-semibold tracking-wider">Total orders</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalOrders }}</div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-xs uppercase text-gray-500 font-semibold tracking-wider">Revenue (paid+shipped)</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900">${{ number_format($totalRevenue, 2) }}</div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-xs uppercase text-gray-500 font-semibold tracking-wider">Pending</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-3xl font-semibold text-gray-900">{{ $totalsByStatus['pending'] }}</span>
                        <span class="text-xs text-yellow-700 bg-yellow-100 px-2 py-0.5 rounded-full">awaiting payment</span>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-xs uppercase text-gray-500 font-semibold tracking-wider">Shipped</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-3xl font-semibold text-gray-900">{{ $totalsByStatus['shipped'] }}</span>
                        <span class="text-xs text-sky-700 bg-sky-100 px-2 py-0.5 rounded-full">on the way</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">Orders by status</h3>
                        <a href="{{ route('orders.index') }}" wire:navigate class="text-xs text-indigo-600 hover:text-indigo-800">View all</a>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($totalsByStatus as $status => $count)
                            <li class="flex items-center justify-between px-4 py-2 text-sm">
                                <span class="flex items-center gap-2">
                                    <span @class([
                                        'inline-block h-2 w-2 rounded-full',
                                        'bg-yellow-400' => $status === 'pending',
                                        'bg-emerald-500' => $status === 'paid',
                                        'bg-sky-500' => $status === 'shipped',
                                        'bg-rose-500' => $status === 'cancelled',
                                    ])></span>
                                    <span class="capitalize text-gray-700">{{ $status }}</span>
                                </span>
                                <span class="font-medium text-gray-900">{{ $count }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-sm lg:col-span-2">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">Recent orders</h3>
                        <a href="{{ route('orders.create') }}" wire:navigate class="text-xs text-indigo-600 hover:text-indigo-800">New order</a>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @forelse ($recentOrders as $order)
                            <li class="flex items-center justify-between px-4 py-3 text-sm hover:bg-gray-50">
                                <div>
                                    <a href="{{ route('orders.show', $order) }}" wire:navigate class="font-mono text-gray-900 hover:text-indigo-700">{{ $order->reference }}</a>
                                    <div class="text-xs text-gray-500">{{ $order->customer->name }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-gray-900 font-medium">${{ number_format((float) $order->total, 2) }}</div>
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
                            </li>
                        @empty
                            <li class="px-4 py-6 text-center text-sm text-gray-500">No orders yet. Create one to get started.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700">Recent automation activity</h3>
                    <span class="text-xs text-gray-500">Polling every 10s</span>
                </div>
                <ul class="divide-y divide-gray-100">
                    @forelse ($recentLogs as $log)
                        <li class="flex items-start gap-3 px-4 py-3 text-sm">
                            <span @class([
                                'mt-1 inline-block h-2 w-2 rounded-full flex-shrink-0',
                                'bg-emerald-500' => $log->status === 'ok',
                                'bg-sky-500' => $log->status === 'info',
                                'bg-rose-500' => $log->status === 'failed',
                            ])></span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <div class="font-mono text-gray-900">{{ $log->step }}</div>
                                    <div class="text-xs text-gray-500">{{ $log->received_at->diffForHumans() }}</div>
                                </div>
                                @if ($log->order)
                                    <a href="{{ route('orders.show', $log->order) }}" wire:navigate class="text-xs text-indigo-600 hover:text-indigo-800 font-mono">{{ $log->order->reference }}</a>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-center text-sm text-gray-500">No automation activity yet. n8n callbacks will land here as orders move through their workflow.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
