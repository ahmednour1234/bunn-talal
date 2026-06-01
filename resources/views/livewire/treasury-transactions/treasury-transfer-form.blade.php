<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">تحويل بين الخزن</h1>
        <p class="text-sm text-gray-500 mt-1">نقل مبلغ من خزنة إلى خزنة أخرى</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-8">

            {{-- Transfer Info --}}
            <div>
                <h3 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">بيانات التحويل</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    {{-- From --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            من خزنة <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="from_treasury_id"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                            <option value="">اختر خزنة المصدر</option>
                            @foreach($treasuries as $treasury)
                                <option value="{{ $treasury->id }}">
                                    {{ $treasury->name }} ({{ number_format($treasury->balance, 2) }} ج.م)
                                </option>
                            @endforeach
                        </select>
                        @error('from_treasury_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        @if($fromTreasury)
                            <p class="text-xs text-gray-400 mt-1">
                                الرصيد المتاح:
                                <span class="font-semibold text-primary-700">{{ number_format($fromTreasury->balance, 2) }} ج.م</span>
                            </p>
                        @endif
                    </div>

                    {{-- Arrow --}}
                    <div class="flex items-center justify-center md:pt-6">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100 text-primary-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 rtl:rotate-180">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    </div>

                    {{-- To --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            إلى خزنة <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="to_treasury_id"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                            <option value="">اختر خزنة الوجهة</option>
                            @foreach($treasuries as $treasury)
                                @if($treasury->id != $from_treasury_id)
                                    <option value="{{ $treasury->id }}">
                                        {{ $treasury->name }} ({{ number_format($treasury->balance, 2) }} ج.م)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('to_treasury_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            المبلغ <span class="text-red-500">*</span>
                        </label>
                        <input type="number" wire:model="amount" step="0.01" min="0.01"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
                            placeholder="0.00">
                        @error('amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-form-input label="التاريخ" name="date" type="date" wire:model="date" required :error="$errors->first('date')" />
                    <x-form-input label="رقم المرجع" name="reference_number" wire:model="reference_number" placeholder="اختياري" :error="$errors->first('reference_number')" />
                </div>
            </div>

            {{-- Description --}}
            <div>
                <h3 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">ملاحظات</h3>
                <textarea wire:model="description" rows="3"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
                    placeholder="ملاحظات (اختياري)..."></textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    تنفيذ التحويل
                </x-button>
                <x-button variant="secondary" href="{{ route('treasury-transactions.index') }}">
                    إلغاء
                </x-button>
            </div>
        </form>
    </div>
</div>
