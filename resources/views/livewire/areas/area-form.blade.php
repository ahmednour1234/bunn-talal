<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $areaId ? 'تعديل المنطقة' : 'إضافة منطقة جديدة' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $areaId ? 'تعديل بيانات المنطقة' : 'إضافة منطقة جديدة إلى النظام' }}</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary-700">بيانات المنطقة</h3>
        </div>

        <form wire:submit="save" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Name --}}
                <x-form-input
                    label="اسم المنطقة"
                    name="name"
                    wire:model="name"
                    placeholder="مثال: القاهرة"
                    required
                    :error="$errors->first('name')"
                />

                {{-- Active --}}
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5">
                        <span class="text-sm font-medium text-gray-700">منطقة نشطة</span>
                    </label>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    {{ $areaId ? 'تحديث المنطقة' : 'حفظ المنطقة' }}
                </x-button>
                <x-button variant="secondary" href="{{ route('areas.index') }}">
                    إلغاء
                </x-button>
            </div>
        </form>
    </div>
</div>
