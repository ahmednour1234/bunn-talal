<div dir="rtl" class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700">{{ $tripId ? 'تعديل الرحلة' : 'رحلة جديدة' }}</h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $tripId ? 'تحديث بيانات الرحلة' : 'إنشاء رحلة مندوب جديدة' }}</p>
        </div>
        <a href="{{ route('trips.index') }}" class="text-sm text-gray-500 hover:text-primary-700 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
            العودة للقائمة
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm font-semibold px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">

        <div class="grid grid-cols-2 gap-5">
            {{-- Delegate --}}
            <div class="col-span-2 md:col-span-1">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">المندوب <span class="text-red-500">*</span></label>
                <select wire:model="delegateId" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-right focus:ring-2 focus:ring-primary-300 @error('delegateId') border-red-400 @enderror">
                    <option value="0">-- اختر المندوب --</option>
                    @foreach($delegates as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
                @error('delegateId')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Branch --}}
            <div class="col-span-2 md:col-span-1">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">الفرع <span class="text-red-500">*</span></label>
                <select wire:model="branchId" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-right focus:ring-2 focus:ring-primary-300 @error('branchId') border-red-400 @enderror">
                    <option value="0">-- اختر الفرع --</option>
                    @foreach($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
                @error('branchId')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Start Date --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">تاريخ البدء <span class="text-red-500">*</span></label>
                <input type="date" wire:model="startDate"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-300 @error('startDate') border-red-400 @enderror">
                @error('startDate')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Expected Return Date --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">تاريخ العودة المتوقع</label>
                <input type="date" wire:model="expectedReturnDate"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-300">
            </div>

            @if($tripId)
            {{-- Status (edit only) --}}
            <div class="col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">الحالة</label>
                <select wire:model="status" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-right focus:ring-2 focus:ring-primary-300">
                    @foreach($statusLabels as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Notes --}}
            <div class="col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">ملاحظات</label>
                <textarea wire:model="notes" rows="3" placeholder="أي ملاحظات حول الرحلة..."
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-right focus:ring-2 focus:ring-primary-300 resize-none"></textarea>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end gap-3 pt-2 border-t border-gray-50">
            <a href="{{ route('trips.index') }}" class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">إلغاء</a>
            <button wire:click="save" wire:loading.attr="disabled" class="px-6 py-2.5 text-sm font-semibold text-white bg-primary-700 rounded-xl hover:bg-primary-800 transition-colors disabled:opacity-60">
                <span wire:loading.remove>{{ $tripId ? 'حفظ التعديلات' : 'إنشاء الرحلة' }}</span>
                <span wire:loading>جارٍ الحفظ...</span>
            </button>
        </div>
    </div>
</div>
