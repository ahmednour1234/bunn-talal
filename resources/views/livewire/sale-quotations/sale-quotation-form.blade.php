<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">إنشاء عرض سعر</h1>
        <p class="text-sm text-gray-500 mt-1">إضافة عرض سعر جديد للعميل</p>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
    @endif
    @if($errors->has('general'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ $errors->first('general') }}</div>
    @endif

    <form wire:submit="save">
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
            <h2 class="text-lg font-semibold text-primary-700 mb-4 pb-3 border-b border-gray-100">بيانات عرض السعر</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">العميل <span class="text-red-500">*</span></label>
                    <select wire:model.live="customer_id" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 @error('customer_id') border-red-400 @enderror">
                        <option value="">اختر العميل</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الفرع <span class="text-red-500">*</span></label>
                    <select wire:model="branch_id" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 @error('branch_id') border-red-400 @enderror">
                        <option value="">اختر الفرع</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">المندوب</label>
                    <select wire:model="delegate_id" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                        <option value="">بدون مندوب</option>
                        @foreach($delegates as $delegate)
                            <option value="{{ $delegate->id }}">{{ $delegate->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ العرض <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="date" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                    @error('date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الانتهاء</label>
                    <input type="date" wire:model="expiry_date" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                    @error('expiry_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الخصم</label>
                    <input type="number" step="0.01" wire:model.live="discount_amount" min="0" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نوع الخصم</label>
                    <select wire:model.live="discount_type" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                        <option value="fixed">مبلغ ثابت</option>
                        <option value="percentage">نسبة %</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                <textarea wire:model="notes" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300"></textarea>
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-primary-700">المنتجات</h2>
                <button type="button" wire:click="addItem" class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary-50 text-primary-700 rounded-lg hover:bg-primary-100 text-sm font-medium">
                    <x-icon name="plus" class="w-4 h-4" />
                    إضافة منتج
                </button>
            </div>

            @error('items')<p class="text-red-500 text-xs mb-3">{{ $message }}</p>@enderror

            <div class="overflow-x-auto border border-gray-100 rounded-xl">
                <table class="w-full text-sm min-w-[960px]">
                    <thead class="bg-primary-50/50">
                        <tr>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700">المنتج</th>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700 w-36">وحدة البيع</th>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700 w-28">الكمية</th>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700 w-32">سعر الوحدة</th>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700 w-28">الخصم</th>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700 w-28">الضريبة</th>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700 w-28">الإجمالي</th>
                            <th class="px-4 py-3 text-center font-semibold text-primary-700 w-14">إجراء</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($items as $index => $item)
                            <tr wire:key="item-{{ $index }}" class="align-top">
                                <td class="px-4 py-3">
                                    <select wire:model.live="items.{{ $index }}.product_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                        <option value="">اختر المنتج</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    @error("items.$index.product_id")<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </td>
                                <td class="px-4 py-3">
                                    @if(!empty($item['available_units']))
                                        <select wire:model.live="items.{{ $index }}.unit_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                            @foreach($item['available_units'] as $unitOpt)
                                                <option value="{{ $unitOpt['id'] }}">{{ $unitOpt['name'] }} ({{ $unitOpt['symbol'] }})</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="px-3 py-2.5 bg-gray-50 rounded-lg text-sm text-gray-400 border border-gray-100">—</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.0001" wire:model.live="items.{{ $index }}.quantity" min="0.0001" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.0001" wire:model.live="items.{{ $index }}.unit_price" min="0" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                    @if(!empty($item['unit_symbol']))
                                        <p class="text-[11px] text-gray-400 mt-1">{{ $item['unit_symbol'] }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" wire:model.live="items.{{ $index }}.discount" min="0" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" wire:model.live="items.{{ $index }}.tax_amount" min="0" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="px-3 py-2.5 bg-primary-50 rounded-lg text-sm font-semibold text-primary-700 text-center">
                                        {{ number_format((((float)($item['quantity'] ?? 0) * (float)($item['unit_price'] ?? 0)) - (float)($item['discount'] ?? 0)) + (float)($item['tax_amount'] ?? 0), 2) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if(count($items) > 1)
                                        <button type="button" wire:click="removeItem({{ $index }})" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg">
                                            <x-icon name="x-mark" class="w-4 h-4" />
                                        </button>
                                    @endif
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
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <div class="text-xs text-gray-500 mb-1">المجموع الفرعي</div>
                    <div class="text-lg font-bold text-gray-700">{{ number_format($totals['subtotal'], 2) }}</div>
                </div>
                <div class="bg-amber-50 rounded-xl p-4 text-center">
                    <div class="text-xs text-amber-600 mb-1">الخصم</div>
                    <div class="text-lg font-bold text-amber-700">{{ number_format($totals['discount'], 2) }}</div>
                </div>
                <div class="bg-primary-50 rounded-xl p-4 text-center">
                    <div class="text-xs text-primary-600 mb-1">الإجمالي</div>
                    <div class="text-xl font-bold text-primary-700">{{ number_format($totals['total'], 2) }}</div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors font-medium shadow-sm">
                <x-icon name="check" class="w-4 h-4" />
                حفظ عرض السعر
            </button>
            <a href="{{ route('sale-quotations.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors font-medium">
                إلغاء
            </a>
        </div>
    </form>
</div>
