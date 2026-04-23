<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $supplierId ? 'تعديل المورد' : 'إضافة مورد جديد' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $supplierId ? 'تعديل بيانات المورد' : 'إضافة مورد جديد إلى النظام' }}</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-8">

            {{-- Basic Info --}}
            <div>
                <h3 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">البيانات الأساسية</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <x-form-input label="اسم المورد" name="name" wire:model="name" placeholder="مثال: شركة الأمل" required :error="$errors->first('name')" />
                    <x-form-input label="رقم الهاتف" name="phone" wire:model="phone" placeholder="مثال: 01012345678" :error="$errors->first('phone')" />
                    <x-form-input label="البريد الإلكتروني" name="email" type="email" wire:model="email" placeholder="مثال: supplier@example.com" :error="$errors->first('email')" />
                    <x-form-input label="اسم الشركة" name="company_name" wire:model="company_name" placeholder="مثال: شركة الأمل للتجارة" :error="$errors->first('company_name')" />
                    <x-form-input label="الرقم الضريبي" name="tax_number" wire:model="tax_number" placeholder="مثال: 123-456-789" :error="$errors->first('tax_number')" />

                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5">
                            <span class="text-sm font-medium text-gray-700">مورد نشط</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div>
                <h3 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">العنوان</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">العنوان</label>
                    <textarea wire:model="address" rows="2"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
                        placeholder="مثال: شارع التحرير، القاهرة"></textarea>
                    @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Financial Info --}}
            <div>
                <h3 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">البيانات المالية</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الحد الائتماني <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="credit_limit" step="0.01" min="0"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm" placeholder="0.00">
                        @error('credit_limit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الرصيد الافتتاحي <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="opening_balance" step="0.01" min="0"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm" placeholder="0.00">
                        @error('opening_balance') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <h3 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">ملاحظات</h3>
                <div>
                    <textarea wire:model="notes" rows="3"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
                        placeholder="أي ملاحظات إضافية عن المورد..."></textarea>
                    @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    {{ $supplierId ? 'تحديث المورد' : 'حفظ المورد' }}
                </x-button>
                <x-button variant="secondary" href="{{ route('suppliers.index') }}">
                    إلغاء
                </x-button>
            </div>
        </form>
    </div>
</div>
