<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $taxId ? 'تعديل الضريبة' : 'إضافة ضريبة جديدة' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $taxId ? 'تعديل بيانات الضريبة' : 'إضافة ضريبة جديدة إلى النظام' }}</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary-700">بيانات الضريبة</h3>
        </div>

        <form wire:submit="save" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Name --}}
                <x-form-input
                    label="اسم الضريبة"
                    name="name"
                    wire:model="name"
                    placeholder="مثال: ضريبة القيمة المضافة"
                    required
                    :error="$errors->first('name')"
                />

                {{-- Type --}}
                <div class="mb-4">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        نوع الضريبة <span class="text-red-500">*</span>
                    </label>
                    <select id="type" wire:model="type"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm {{ $errors->has('type') ? 'border-red-400 ring-1 ring-red-300' : '' }}">
                        <option value="percentage">نسبة مئوية %</option>
                        <option value="fixed">مبلغ ثابت</option>
                    </select>
                    @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Rate --}}
                <x-form-input
                    label="القيمة"
                    name="rate"
                    type="number"
                    wire:model="rate"
                    placeholder="0.00"
                    required
                    :error="$errors->first('rate')"
                />
            </div>

            {{-- Active Status --}}
            <div class="mt-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5">
                    <span class="text-sm font-medium text-gray-700">ضريبة نشطة</span>
                </label>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    {{ $taxId ? 'تحديث الضريبة' : 'حفظ الضريبة' }}
                </x-button>
                <x-button variant="secondary" href="{{ route('taxes.index') }}">
                    إلغاء
                </x-button>
            </div>
        </form>
    </div>
</div>
