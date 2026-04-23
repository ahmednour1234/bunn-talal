<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $productId ? 'تعديل المنتج' : 'إضافة منتج جديد' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $productId ? 'تعديل بيانات المنتج' : 'إضافة منتج جديد إلى النظام' }}</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary-700">بيانات المنتج</h3>
        </div>

        <form wire:submit="save" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Name --}}
                <x-form-input
                    label="اسم المنتج"
                    name="name"
                    wire:model="name"
                    placeholder="مثال: بن يمني فاخر"
                    required
                    :error="$errors->first('name')"
                />

                {{-- Category --}}
                <div class="mb-4">
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                        التصنيف <span class="text-red-500">*</span>
                    </label>
                    <select id="category_id" wire:model="category_id"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm {{ $errors->has('category_id') ? 'border-red-400 ring-1 ring-red-300' : '' }}">
                        <option value="">-- اختر التصنيف --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Unit --}}
                <div class="mb-4">
                    <label for="unit_id" class="block text-sm font-medium text-gray-700 mb-2">
                        وحدة القياس <span class="text-red-500">*</span>
                    </label>
                    <select id="unit_id" wire:model.live="unit_id"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm {{ $errors->has('unit_id') ? 'border-red-400 ring-1 ring-red-300' : '' }}">
                        <option value="">-- اختر الوحدة --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                        @endforeach
                    </select>
                    @error('unit_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Image --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">صورة المنتج</label>
                    <input type="file" wire:model="image" accept="image/*"
                        class="w-full border border-gray-200 rounded-lg p-2 text-sm file:ml-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <div class="mt-3">
                        @if($image && !$errors->has('image'))
                            <img src="{{ $image->temporaryUrl() }}" alt="معاينة" class="w-20 h-20 rounded-lg object-cover border border-gray-200">
                        @elseif($existingImage)
                            <img src="{{ asset('storage/' . $existingImage) }}" alt="الصورة الحالية" class="w-20 h-20 rounded-lg object-cover border border-gray-200">
                        @endif
                    </div>
                </div>

                {{-- Cost Price --}}
                <x-form-input
                    label="سعر التكلفة"
                    name="cost_price"
                    type="number"
                    wire:model="cost_price"
                    placeholder="0.00"
                    required
                    :error="$errors->first('cost_price')"
                />

                {{-- Selling Price --}}
                <x-form-input
                    label="سعر البيع"
                    name="selling_price"
                    type="number"
                    wire:model="selling_price"
                    placeholder="0.00"
                    required
                    :error="$errors->first('selling_price')"
                />

                {{-- Discount --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">الخصم</label>
                    <div class="flex gap-2">
                        <input
                            type="number"
                            wire:model="discount"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            class="flex-1 px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm {{ $errors->has('discount') ? 'border-red-400 ring-1 ring-red-300' : '' }}"
                        >
                        <select wire:model="discount_type"
                            class="w-36 px-3 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                            <option value="fixed">مبلغ ثابت</option>
                            <option value="percentage">نسبة %</option>
                        </select>
                    </div>
                    @error('discount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Tax --}}
                <div class="mb-4">
                    <label for="tax_id" class="block text-sm font-medium text-gray-700 mb-2">
                        الضريبة
                    </label>
                    <select id="tax_id" wire:model="tax_id"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                        <option value="">-- بدون ضريبة --</option>
                        @foreach($taxes as $tax)
                            <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->formatted_rate }})</option>
                        @endforeach
                    </select>
                    @error('tax_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Active Status --}}
            <div class="mt-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5">
                    <span class="text-sm font-medium text-gray-700">منتج نشط</span>
                </label>
            </div>

            {{-- Branch Quantities --}}
            <div class="mt-6 pt-6 border-t border-gray-100">
                <h3 class="text-lg font-bold text-primary-700 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    الكميات في الفروع
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($branches as $branch)
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $branch->name }}</label>
                            <div class="flex gap-2">
                                <input
                                    type="number"
                                    wire:model="branch_quantities.{{ $branch->id }}"
                                    min="0"
                                    class="flex-1 px-4 py-2.5 border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
                                    placeholder="0"
                                >
                                <select wire:model="branch_units.{{ $branch->id }}"
                                    class="w-28 px-2 py-2.5 border border-gray-200 rounded-lg bg-white text-xs focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all">
                                    <option value="">الوحدة</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->symbol }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    {{ $productId ? 'تحديث المنتج' : 'حفظ المنتج' }}
                </x-button>
                <x-button variant="secondary" href="{{ route('products.index') }}">
                    إلغاء
                </x-button>
            </div>
        </form>
    </div>
</div>
