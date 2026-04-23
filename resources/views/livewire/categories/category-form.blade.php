<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $categoryId ? 'تعديل التصنيف' : 'إضافة تصنيف جديد' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $categoryId ? 'تعديل بيانات التصنيف' : 'إضافة تصنيف جديد إلى النظام' }}</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary-700">بيانات التصنيف</h3>
        </div>

        <form wire:submit="save" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Name --}}
                <x-form-input
                    label="اسم التصنيف"
                    name="name"
                    wire:model="name"
                    placeholder="مثال: مشروبات ساخنة"
                    required
                    :error="$errors->first('name')"
                />

                {{-- Image --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">صورة التصنيف</label>
                    <input
                        type="file"
                        wire:model="image"
                        accept="image/*"
                        class="w-full border border-gray-200 rounded-lg p-2 text-sm file:ml-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                    >
                    @error('image')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    {{-- Preview --}}
                    <div class="mt-3">
                        @if($image && !$errors->has('image'))
                            <img src="{{ $image->temporaryUrl() }}" alt="معاينة" class="w-20 h-20 rounded-lg object-cover border border-gray-200">
                        @elseif($existingImage)
                            <img src="{{ asset('storage/' . $existingImage) }}" alt="الصورة الحالية" class="w-20 h-20 rounded-lg object-cover border border-gray-200">
                        @endif
                    </div>
                </div>
            </div>

            {{-- Active Status --}}
            <div class="mt-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5">
                    <span class="text-sm font-medium text-gray-700">تصنيف نشط</span>
                </label>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    {{ $categoryId ? 'تحديث التصنيف' : 'حفظ التصنيف' }}
                </x-button>
                <x-button variant="secondary" href="{{ route('categories.index') }}">
                    إلغاء
                </x-button>
            </div>
        </form>
    </div>
</div>
