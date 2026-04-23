<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $treasuryId ? 'تعديل الخزنة' : 'إضافة خزنة جديدة' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $treasuryId ? 'تعديل بيانات الخزنة' : 'إضافة خزنة جديدة إلى النظام' }}</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-8">

            {{-- Basic Info --}}
            <div>
                <h3 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">بيانات الخزنة</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <x-form-input label="اسم الخزنة" name="name" wire:model="name" placeholder="مثال: الخزنة الرئيسية" required :error="$errors->first('name')" />
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الرصيد <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="balance" step="0.01" min="0"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm" placeholder="0.00">
                        @error('balance') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5">
                            <span class="text-sm font-medium text-gray-700">خزنة نشطة</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    {{ $treasuryId ? 'تحديث الخزنة' : 'حفظ الخزنة' }}
                </x-button>
                <x-button variant="secondary" href="{{ route('treasuries.index') }}">
                    إلغاء
                </x-button>
            </div>
        </form>
    </div>
</div>
