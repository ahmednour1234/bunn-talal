<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $accountId ? 'تعديل الحساب' : 'إضافة حساب جديد' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $accountId ? 'تعديل بيانات الحساب' : 'إضافة حساب جديد إلى النظام' }}</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-8">

            {{-- Basic Info --}}
            <div>
                <h3 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">بيانات الحساب</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <x-form-input label="اسم الحساب" name="name" wire:model="name" placeholder="مثال: حساب المصروفات العامة" required :error="$errors->first('name')" />
                    <x-form-input label="رقم الحساب" name="account_number" wire:model="account_number" placeholder="مثال: ACC-001" required :error="$errors->first('account_number')" />

                    <div class="flex items-end gap-6 pb-1">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="visible_to_delegate" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5">
                            <span class="text-sm font-medium text-gray-700">يظهر للمندوب</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5">
                            <span class="text-sm font-medium text-gray-700">حساب نشط</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    {{ $accountId ? 'تحديث الحساب' : 'حفظ الحساب' }}
                </x-button>
                <x-button variant="secondary" href="{{ route('accounts.index') }}">
                    إلغاء
                </x-button>
            </div>
        </form>
    </div>
</div>
