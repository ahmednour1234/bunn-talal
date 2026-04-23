<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $unitId ? 'تعديل وحدة القياس' : 'إضافة وحدة قياس جديدة' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $unitId ? 'تعديل بيانات الوحدة' : 'إضافة وحدة قياس جديدة مع معامل التحويل' }}</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary-700">بيانات الوحدة</h3>
        </div>

        <form wire:submit="save" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Name --}}
                <x-form-input
                    label="اسم الوحدة"
                    name="name"
                    wire:model="name"
                    placeholder="مثال: كيلوجرام"
                    required
                    :error="$errors->first('name')"
                />

                {{-- Symbol --}}
                <x-form-input
                    label="الرمز"
                    name="symbol"
                    wire:model="symbol"
                    placeholder="مثال: kg"
                    required
                    :error="$errors->first('symbol')"
                />

                {{-- Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نوع الوحدة <span class="text-red-500">*</span></label>
                    <select wire:model.live="type"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                        @foreach($typeLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Base Unit --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الوحدة الأساسية</label>
                    <select wire:model="base_unit_id"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                        <option value="">هذه وحدة أساسية</option>
                        @foreach($baseUnits as $bu)
                            <option value="{{ $bu->id }}">{{ $bu->name }} ({{ $bu->symbol }})</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">اتركها فارغة إذا كانت هذه هي الوحدة الأساسية للنوع</p>
                    @error('base_unit_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Conversion Factor --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">معامل التحويل <span class="text-red-500">*</span></label>
                    <input type="number" wire:model="conversion_factor" step="0.000001" min="0.000001"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
                        placeholder="مثال: 1000">
                    <p class="text-xs text-gray-400 mt-1">كم وحدة أساسية تساوي وحدة واحدة من هذه</p>
                    @error('conversion_factor')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Active --}}
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5">
                        <span class="text-sm font-medium text-gray-700">وحدة نشطة</span>
                    </label>
                </div>
            </div>

            {{-- Info Box --}}
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <h4 class="text-sm font-bold text-blue-700 mb-2">أمثلة على التحويل:</h4>
                <ul class="text-xs text-blue-600 space-y-1 list-disc list-inside">
                    <li><strong>جرام</strong> → وحدة أساسية (معامل = 1)</li>
                    <li><strong>كيلوجرام</strong> → وحدة أساسية: جرام، معامل = 1000</li>
                    <li><strong>طن</strong> → وحدة أساسية: جرام، معامل = 1000000</li>
                    <li><strong>قطعة</strong> → وحدة أساسية (معامل = 1)</li>
                    <li><strong>كرتونة</strong> → وحدة أساسية: قطعة، معامل = 24</li>
                </ul>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    {{ $unitId ? 'تحديث الوحدة' : 'حفظ الوحدة' }}
                </x-button>
                <x-button variant="secondary" href="{{ route('units.index') }}">
                    إلغاء
                </x-button>
            </div>
        </form>
    </div>
</div>
