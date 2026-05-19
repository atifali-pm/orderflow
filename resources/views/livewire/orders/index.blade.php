<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Orders') }}</h2>
            <a href="{{ route('orders.create') }}" wire:navigate
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                {{ __('New order') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap items-center gap-3">
                <label for="statusFilter" class="text-sm font-medium text-gray-700">Filter by status</label>
                <select id="statusFilter" wire:model.live="statusFilter"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>

                @if ($statusFilter !== '')
                    <button type="button" wire:click="$set('statusFilter', '')"
                            class="text-xs text-gray-500 underline">Clear filter</button>
                @endif

                <div class="ml-auto text-sm text-gray-500">
                    Showing {{ $orders->total() }} order{{ $orders->total() === 1 ? '' : 's' }}
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Placed</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm font-mono text-gray-900">{{ $order->reference }}</td>
                                <td class="px-6 py-3 text-sm text-gray-700">
                                    <div class="font-medium">{{ $order->customer->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->customer->company }}</div>
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    <span @class([
                                        'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                        'bg-yellow-100 text-yellow-800' => $order->status === 'pending',
                                        'bg-emerald-100 text-emerald-800' => $order->status === 'paid',
                                        'bg-sky-100 text-sky-800' => $order->status === 'shipped',
                                        'bg-rose-100 text-rose-800' => $order->status === 'cancelled',
                                    ])>
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm text-right text-gray-900 font-medium">
                                    ${{ number_format((float) $order->total, 2) }}
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-500">
                                    {{ optional($order->placed_at)->format('M j, Y') ?? 'n/a' }}
                                </td>
                                <td class="px-6 py-3 text-sm text-right">
                                    <a href="{{ route('orders.show', $order) }}" wire:navigate
                                       class="text-indigo-600 hover:text-indigo-900">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                    No orders match the current filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $orders->links() }}</div>
        </div>
    </div>
</div>
