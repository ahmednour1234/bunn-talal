<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">إنشاء طلب إهلاك</h1>
        <p class="text-sm text-gray-500 mt-1">إهلاك كميات من منتجات فرع معين</p>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    <form wire:submit="save">
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
            <h2 class="text-lg font-semibold text-primary-700 mb-4 pb-3 border-b border-gray-100">بيانات الإهلاك</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الفرع <span class="text-red-500">*</span></label>
                    <select wire:model.live="branch_id" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 @error('branch_id') border-red-400 ring-1 ring-red-300 @enderror">
                        <option value="">اختر الفرع</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">التاريخ <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="date" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                    @error('date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">سبب الإهلاك <span class="text-red-500">*</span></label>
                <textarea wire:model="reason" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 @error('reason') border-red-400 ring-1 ring-red-300 @enderror" placeholder="مثال: تلف بسبب سوء التخزين، انتهاء صلاحية..."></textarea>
                @error('reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                <textarea wire:model="notes" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300"></textarea>
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-primary-700">المنتجات المراد إهلاكها</h2>
                <button type="button" wire:click="addItem" class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary-50 text-primary-700 rounded-lg hover:bg-primary-100 text-sm font-medium">
                    <x-icon name="plus" class="w-4 h-4" />
                    إضافة منتج
                </button>
            </div>

            @error('items')<p class="text-red-500 text-xs mb-3">{{ $message }}</p>@enderror

            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="w-full text-sm">
                    <thead class="bg-primary-50/60">
                        <tr>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700">المنتج</th>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700 w-44">وحدة الإهلاك</th>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700 w-36">الكمية</th>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700 w-36">تكلفة الوحدة</th>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700 w-36">الخسارة</th>
                            <th class="px-4 py-3 text-center font-semibold text-primary-700 w-16">حذف</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($items as $index => $item)
                            <tr wire:key="dep-item-{{ $index }}" class="align-top">
                                <td class="px-4 py-3">
                                    <select wire:model.live="items.{{ $index }}.product_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                        <option value="">اختر المنتج</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} (المتاح: {{ $product->branches->first()?->pivot?->quantity ?? 0 }} {{ $product->unit?->symbol ?? '' }})</option>
                                        @endforeach
                                    </select>
                                    @error("items.$index.product_id")<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </td>
                                <td class="px-4 py-3">
                                    @if(!empty($item['available_units']))
                                        <select wire:model.live="items.{{ $index }}.unit_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                            @foreach($item['available_units'] as $unitOption)
                                                <option value="{{ $unitOption['id'] }}">{{ $unitOption['name'] }} ({{ $unitOption['symbol'] }})</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="px-3 py-2.5 bg-gray-50 rounded-lg text-sm text-gray-400 border border-gray-100 text-center">—</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" wire:model.live="items.{{ $index }}.quantity" min="1"
                                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                    @if(!empty($item['max_quantity']) && (int)$item['max_quantity'] > 0)
                                        <p class="text-[11px] text-gray-400 mt-1">الحد الأقصى: {{ $item['max_quantity'] }} {{ $item['unit_symbol'] ?? '' }}</p>
                                    @endif
                                    @error("items.$index.quantity")<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </td>
                                <td class="px-4 py-3">
                                    <div class="px-3 py-2.5 bg-gray-50 rounded-lg text-sm border border-gray-100 text-gray-600 text-center">
                                        {{ number_format((float)($item['cost_price'] ?? 0), 2) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="px-3 py-2.5 bg-red-50 rounded-lg text-sm font-semibold text-red-700 text-center">
                                        {{ number_format(((int)($item['quantity'] ?? 0)) * ((float)($item['cost_price'] ?? 0)), 2) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if(count($items) > 1)
                                        <button type="button" wire:click="removeItem({{ $index }})" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg">
                                            <x-icon name="x-mark" class="w-4 h-4" />
                                        </button>
                                    @else
                                        <span class="text-gray-300 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Total Loss --}}
        <div class="bg-card rounded-2xl shadow-sm border border-red-200 p-6 mb-4">
            <div class="text-center">
                <div class="text-sm text-red-600 mb-1">إجمالي الخسارة المتوقعة</div>
                <div class="text-2xl font-bold text-red-700">{{ number_format($this->totalLoss, 2) }}</div>
                <div class="text-xs text-gray-400 mt-1">سيتم خصم الكميات بعد الموافقة</div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors font-medium shadow-sm">
                <x-icon name="check" class="w-4 h-4" />
                إرسال طلب الإهلاك
            </button>
            <a href="{{ route('product-depreciations.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors font-medium">إلغاء</a>
        </div>
    </form>
</div>
