<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">طلب تحويل مخزون جديد</h1>
        <p class="text-sm text-gray-500 mt-1">إنشاء طلب تحويل منتجات بين الفروع</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary-700">بيانات التحويل</h3>
        </div>

        <form wire:submit="save" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- From Branch --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        من فرع <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="from_branch_id"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm {{ $errors->has('from_branch_id') ? 'border-red-400 ring-1 ring-red-300' : '' }}">
                        <option value="">-- اختر الفرع --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('from_branch_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- To Branch --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        إلى فرع <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="to_branch_id"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm {{ $errors->has('to_branch_id') ? 'border-red-400 ring-1 ring-red-300' : '' }}">
                        <option value="">-- اختر الفرع --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('to_branch_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Notes --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                <textarea wire:model="notes" rows="2"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
                    placeholder="ملاحظات إضافية..."></textarea>
            </div>

            {{-- Items --}}
            <div class="mt-6 pt-6 border-t border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-primary-700">المنتجات المراد تحويلها</h3>
                    <button type="button" wire:click="addItem" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-primary-700 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors">
                        <x-icon name="plus" class="w-3.5 h-3.5" />
                        إضافة منتج
                    </button>
                </div>

                @error('items') <p class="text-red-500 text-xs mb-3">{{ $message }}</p> @enderror

                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-50/60">
                            <tr>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700">المنتج</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700 w-44">وحدة التحويل</th>
                                <th class="px-4 py-3 text-right font-semibold text-primary-700 w-40">الكمية</th>
                                <th class="px-4 py-3 text-center font-semibold text-primary-700 w-16">حذف</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($items as $index => $item)
                                <tr wire:key="transfer-item-{{ $index }}" class="align-top">
                                    <td class="px-4 py-3">
                                        <select wire:model.live="items.{{ $index }}.product_id"
                                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                            <option value="">-- اختر --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">
                                                    {{ $product->name }}
                                                    (متاح: {{ $product->branches->first()?->pivot->quantity ?? 0 }} {{ $product->unit?->symbol ?? '' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("items.{$index}.product_id")<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(!empty($item['available_units']))
                                            <select wire:model.live="items.{{ $index }}.unit_id"
                                                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                                @foreach($item['available_units'] as $unitOption)
                                                    <option value="{{ $unitOption['id'] }}">{{ $unitOption['name'] }} ({{ $unitOption['symbol'] }})</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <div class="px-3 py-2.5 bg-gray-50 rounded-lg text-sm text-gray-400 border border-gray-100">—</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" wire:model.live="items.{{ $index }}.quantity"
                                            min="1" max="{{ $item['max_quantity'] ?? '' }}"
                                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                                        @if(!empty($item['max_quantity']) && (int)$item['max_quantity'] > 0)
                                            <p class="text-[11px] text-gray-400 mt-1">الحد الأقصى: {{ $item['max_quantity'] }} {{ $item['unit_symbol'] ?? '' }}</p>
                                        @endif
                                        @error("items.{$index}.quantity")<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
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

            {{-- Actions --}}
            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    إرسال طلب التحويل
                </x-button>
                <x-button variant="secondary" href="{{ route('stock-transfers.index') }}">
                    إلغاء
                </x-button>
            </div>
        </form>
    </div>
</div>
