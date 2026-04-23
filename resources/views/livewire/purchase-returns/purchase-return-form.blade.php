<div>
    <style>
        .return-totals-row {
            display: flex;
            gap: 16px;
            align-items: stretch;
            flex-wrap: nowrap;
        }
        .return-totals-row .total-card {
            flex: 1 1 0;
            min-width: 0;
        }
        @media (max-width: 992px) {
            .return-totals-row {
                flex-wrap: wrap;
            }
            .return-totals-row .total-card {
                flex: 1 1 100%;
            }
        }
    </style>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">إنشاء مرتجع مشتريات</h1>
        <p class="text-sm text-gray-500 mt-1">إرجاع منتجات من فاتورة مشتريات</p>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    <form wire:submit="save">
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
            <h2 class="text-lg font-semibold text-primary-700 mb-4 pb-3 border-b border-gray-100">بيانات المرتجع</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">فاتورة المشتريات <span class="text-red-500">*</span></label>
                    <select wire:model.live="purchase_invoice_id" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 @error('purchase_invoice_id') border-red-400 ring-1 ring-red-300 @enderror">
                        <option value="">اختر الفاتورة</option>
                        @foreach($invoices as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->invoice_number }} - {{ $inv->supplier->name }} ({{ number_format($inv->total, 2) }})</option>
                        @endforeach
                    </select>
                    @error('purchase_invoice_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">التاريخ <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="date" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                    @error('date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الخزينة (للاسترداد)</label>
                    <select wire:model="treasury_id" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                        <option value="">بدون خزينة</option>
                        @foreach($treasuries as $treasury)
                            <option value="{{ $treasury->id }}">{{ $treasury->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                <textarea wire:model="notes" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300"></textarea>
            </div>
        </div>

        {{-- Items from Invoice --}}
        @if(count($items) > 0)
            <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
                <h2 class="text-lg font-semibold text-primary-700 mb-4 pb-3 border-b border-gray-100">منتجات الفاتورة — حدد الكميات المراد إرجاعها</h2>

                @error('items')<p class="text-red-500 text-xs mb-3">{{ $message }}</p>@enderror

                <div class="overflow-x-auto border border-gray-100 rounded-xl">
                    <table class="w-full text-sm min-w-[980px]">
                        <thead class="bg-primary-50/50">
                            <tr>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700">المنتج</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700">الكمية بالفاتورة</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700">سعر الوحدة</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700">وحدة الإرجاع</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700">كمية الإرجاع</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700">مبلغ الخسارة</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700">السبب</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700">المسترد</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($items as $index => $item)
                                @php
                                    $qty = (int)($item['quantity'] ?? 0);
                                    $price = (float)($item['unit_price'] ?? 0);
                                    $loss = (float)($item['loss_amount'] ?? 0);
                                    $refund = ($qty * $price) - $loss;
                                @endphp
                                <tr wire:key="return-item-{{ $index }}" class="align-top">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-800">{{ $item['product_name'] ?? 'منتج' }}</div>
                                        <div class="text-xs text-gray-400 mt-1">{{ $item['unit_symbol'] ?: '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 font-semibold">{{ $item['original_qty'] ?? 0 }} {{ $item['invoice_unit_symbol'] ?? $item['unit_symbol'] }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ number_format((float)($item['unit_price'] ?? 0), 2) }}</td>
                                    <td class="px-4 py-3">
                                        <select wire:model.live="items.{{ $index }}.unit_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                            @foreach(($item['available_units'] ?? []) as $unitOption)
                                                <option value="{{ $unitOption['id'] }}">{{ $unitOption['name'] }} ({{ $unitOption['symbol'] }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input
                                            type="number"
                                            wire:model.live="items.{{ $index }}.quantity"
                                            min="0"
                                            max="{{ $item['max_quantity'] ?? 0 }}"
                                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm"
                                        >
                                        <p class="text-[11px] text-gray-400 mt-1">الحد الأقصى: {{ $item['max_quantity'] ?? 0 }} {{ $item['unit_symbol'] ?: '' }}</p>
                                        @error("items.$index.quantity")<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    </td>
                                    <td class="px-4 py-3">
                                        <input
                                            type="number"
                                            step="0.01"
                                            wire:model.live="items.{{ $index }}.loss_amount"
                                            min="0"
                                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm"
                                        >
                                    </td>
                                    <td class="px-4 py-3">
                                        <input
                                            type="text"
                                            wire:model="items.{{ $index }}.reason"
                                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm"
                                            placeholder="سبب الإرجاع"
                                        >
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="px-3 py-2.5 bg-green-50 rounded-lg text-sm font-semibold text-green-700 text-center">{{ number_format(max(0, $refund), 2) }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Totals --}}
            <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
                @php $totals = $this->calculatedTotals; @endphp
                <div class="return-totals-row">
                    <div class="total-card bg-gray-50 rounded-xl p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1">إجمالي المرتجع</div>
                        <div class="text-lg font-bold text-gray-700">{{ number_format($totals['subtotal'], 2) }}</div>
                    </div>
                    <div class="total-card bg-red-50 rounded-xl p-4 text-center">
                        <div class="text-xs text-red-600 mb-1">الخسائر المتحملة</div>
                        <div class="text-lg font-bold text-red-700">{{ number_format($totals['loss'], 2) }}</div>
                    </div>
                    <div class="total-card bg-green-50 rounded-xl p-4 text-center">
                        <div class="text-xs text-green-600 mb-1">المبلغ المسترد</div>
                        <div class="text-xl font-bold text-green-700">{{ number_format($totals['refund'], 2) }}</div>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors font-medium shadow-sm" @if(count($items) === 0) disabled @endif>
                <x-icon name="check" class="w-4 h-4" />
                حفظ المرتجع
            </button>
            <a href="{{ route('purchase-returns.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors font-medium">إلغاء</a>
        </div>
    </form>
</div>
