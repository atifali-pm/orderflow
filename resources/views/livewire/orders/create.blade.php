<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New order') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form wire:submit="save" class="space-y-6 bg-white p-6 rounded-lg shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700">Customer</label>
                        <select id="customer_id" wire:model="customer_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                            @endforeach
                        </select>
                        @error('customer_id') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <input id="notes" type="text" wire:model="notes"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Optional order notes" />
                        @error('notes') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Line items</h3>
                        <button type="button" wire:click="addItem"
                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">+ Add item</button>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit price</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Line total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($items as $index => $item)
                                    <tr wire:key="item-{{ $index }}">
                                        <td class="px-3 py-2">
                                            <input type="text" wire:model="items.{{ $index }}.sku"
                                                   class="w-32 rounded border-gray-300 text-sm" placeholder="SKU-1001" />
                                            @error("items.$index.sku") <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" wire:model="items.{{ $index }}.description"
                                                   class="w-full rounded border-gray-300 text-sm" placeholder="Item description" />
                                            @error("items.$index.description") <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" min="1" wire:model.live="items.{{ $index }}.quantity"
                                                   class="w-20 rounded border-gray-300 text-sm" />
                                            @error("items.$index.quantity") <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.unit_price"
                                                   class="w-28 rounded border-gray-300 text-sm" />
                                            @error("items.$index.unit_price") <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-3 py-2 text-right text-sm text-gray-900">
                                            ${{ number_format((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0), 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button type="button" wire:click="removeItem({{ $index }})"
                                                    class="text-xs text-rose-600 hover:text-rose-800">Remove</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="4" class="px-3 py-2 text-right text-sm font-semibold text-gray-700">Order total</td>
                                    <td class="px-3 py-2 text-right text-sm font-semibold text-gray-900">
                                        ${{ number_format($this->total, 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('orders.index') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-800">Cancel</a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        Create order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
