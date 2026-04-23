<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">مرتجع مبيعات جديد</h1>
        <p class="text-sm text-gray-500 mt-1">اختر طلب المبيعات وحدد المنتجات المرتجعة</p>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    <form wire:submit="save">
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
            <h2 class="text-lg font-semibold text-primary-700 mb-4 pb-3 border-b border-gray-100">بيانات الإرجاع</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">طلب المبيعات <span class="text-red-500">*</span></label>
                    <select wire:model.live="sale_order_id" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 @error('sale_order_id') border-red-400 @enderror">
                        <option value="">اختر طلب المبيعات</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->order_number }} — {{ $order->customer->name }}</option>
                        @endforeach
                    </select>
                    @error('sale_order_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                @if($loaded_customer_name)
                    <div>
                        <p class="text-xs text-gray-400 mb-1">العميل</p>
                        <p class="font-semibold text-sm">{{ $loaded_customer_name }}</p>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الإرجاع <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="date" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                    @error('date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الخزينة (للاسترداد)</label>
                    <select wire:model="treasury_id" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                        <option value="">بدون استرداد نقدي</option>
                        @foreach($treasuries as $treasury)
                            <option value="{{ $treasury->id }}">{{ $treasury->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                    <input type="text" wire:model="notes" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                </div>
            </div>
        </div>

        @if(!empty($items))
            <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
                <h2 class="text-lg font-semibold text-primary-700 mb-4 pb-3 border-b border-gray-100">المنتجات المرتجعة</h2>
                @error('items')<p class="text-red-500 text-xs mb-3">{{ $message }}</p>@enderror

                <div class="overflow-x-auto border border-gray-100 rounded-xl">
                    <table class="w-full text-sm min-w-[900px]">
                        <thead class="bg-primary-50/50">
                            <tr>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700">المنتج</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700 w-36">وحدة الإرجاع</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700 w-28">الكمية المباعة</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700 w-28">كمية الإرجاع</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700 w-32">سعر الوحدة</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700 w-28">الإجمالي</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700">السبب</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($items as $index => $item)
                                <tr wire:key="item-{{ $index }}" class="align-top">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-800">{{ $item['product_name'] }}</div>
                                        <div class="text-xs text-gray-400">أصلي: {{ $item['order_unit_symbol'] }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(!empty($item['available_units']))
                                            <select wire:model.live="items.{{ $index }}.unit_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                                @foreach($item['available_units'] as $unitOpt)
                                                    <option value="{{ $unitOpt['id'] }}">{{ $unitOpt['name'] }} ({{ $unitOpt['symbol'] }})</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <div class="text-sm text-gray-400">{{ $item['unit_symbol'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-500">{{ $item['original_qty'] }}</td>
                                    <td class="px-4 py-3">
                                        <input type="number" step="0.0001" wire:model.live="items.{{ $index }}.quantity" min="0"
                                            max="{{ $item['max_quantity'] }}"
                                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                        @if($item['max_quantity'] > 0)
                                            <p class="text-[11px] text-gray-400 mt-1">أقصى: {{ $item['max_quantity'] }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="px-3 py-2.5 bg-gray-50 rounded-lg text-sm text-gray-700">{{ number_format((float)$item['unit_price'], 4) }}</div>
                                        <p class="text-[11px] text-gray-400 mt-1">{{ $item['unit_symbol'] }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="px-3 py-2.5 bg-primary-50 rounded-lg text-sm font-semibold text-primary-700 text-center">
                                            {{ number_format((float)($item['quantity'] ?? 0) * (float)($item['unit_price'] ?? 0), 2) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" wire:model="items.{{ $index }}.reason" placeholder="سبب الإرجاع..."
                                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Totals --}}
                @php $totals = $this->calculatedTotals; @endphp
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1">إجمالي المرتجع</div>
                        <div class="text-xl font-bold text-gray-700">{{ number_format($totals['subtotal'], 2) }}</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 text-center">
                        <div class="text-xs text-green-600 mb-1">المبلغ المسترد</div>
                        <div class="text-xl font-bold text-green-700">{{ number_format($totals['refund'], 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors font-medium shadow-sm">
                    <x-icon name="check" class="w-4 h-4" />
                    حفظ المرتجع
                </button>
                <a href="{{ route('sale-returns.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors font-medium">
                    إلغاء
                </a>
            </div>
        @else
            <div class="flex items-center gap-3 mt-4">
                <a href="{{ route('sale-returns.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors font-medium">
                    إلغاء
                </a>
            </div>
        @endif
    </form>
</div>
