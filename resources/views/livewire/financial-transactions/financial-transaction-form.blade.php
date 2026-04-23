<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $transactionId ? 'تعديل المعاملة' : 'إضافة معاملة جديدة' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $transactionId ? 'تعديل بيانات المعاملة المالية' : 'تسجيل مصروف أو إيراد جديد' }}</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-8">

            {{-- Transaction Info --}}
            <div>
                <h3 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">بيانات المعاملة</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">النوع <span class="text-red-500">*</span></label>
                        <select wire:model="type" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                            @foreach($typeLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الحساب <span class="text-red-500">*</span></label>
                        <select wire:model="account_id" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                            <option value="">اختر الحساب</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->account_number }})</option>
                            @endforeach
                        </select>
                        @error('account_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الخزنة</label>
                        <select wire:model="treasury_id" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                            <option value="">بدون خزنة</option>
                            @foreach($treasuries as $treasury)
                                <option value="{{ $treasury->id }}">{{ $treasury->name }}</option>
                            @endforeach
                        </select>
                        @error('treasury_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">المبلغ <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="amount" step="0.01" min="0.01"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm" placeholder="0.00">
                        @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <x-form-input label="التاريخ" name="date" type="date" wire:model="date" required :error="$errors->first('date')" />
                </div>
            </div>

            {{-- Description --}}
            <div>
                <h3 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">الوصف</h3>
                <div>
                    <textarea wire:model="description" rows="3"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
                        placeholder="وصف المعاملة (اختياري)..."></textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    {{ $transactionId ? 'تحديث المعاملة' : 'حفظ المعاملة' }}
                </x-button>
                <x-button variant="secondary" href="{{ route('financial-transactions.index') }}">
                    إلغاء
                </x-button>
            </div>
        </form>
    </div>
</div>
