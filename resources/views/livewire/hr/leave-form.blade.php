<div dir="rtl" class="space-y-6 max-w-2xl mx-auto">
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm font-bold px-5 py-3 rounded-2xl flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-primary-700">{{ $leaveId ? 'تعديل الإجازة' : 'إضافة إجازة' }}</h1>
        <a href="{{ route('hr.leaves.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← عودة</a>
    </div>

    <form wire:submit="save" class="bg-white rounded-2xl shadow-sm border border-primary-100 p-6 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Delegate --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">المندوب <span class="text-red-500">*</span></label>
                <select wire:model="delegate_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                    <option value="">اختر المندوب</option>
                    @foreach($delegates as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
                @error('delegate_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Type --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">نوع الإجازة <span class="text-red-500">*</span></label>
                <select wire:model="type" class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                    @foreach($types as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">الحالة</label>
                <select wire:model="status" class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                    @foreach($statuses as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Start Date --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">تاريخ البداية <span class="text-red-500">*</span></label>
                <input type="date" wire:model="start_date" class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                @error('start_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- End Date --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">تاريخ النهاية <span class="text-red-500">*</span></label>
                <input type="date" wire:model="end_date" class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                @error('end_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Reason --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">السبب</label>
                <textarea wire:model="reason" rows="3" placeholder="سبب الإجازة..."
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"></textarea>
                @error('reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('hr.leaves.index') }}" class="px-6 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-colors text-sm">إلغاء</a>
            <button type="submit" class="px-8 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold transition-colors text-sm">
                {{ $leaveId ? 'تحديث' : 'حفظ' }}
            </button>
        </div>
    </form>
</div>
