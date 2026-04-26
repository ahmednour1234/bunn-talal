<div dir="rtl" class="space-y-6 max-w-2xl mx-auto">
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm font-bold px-5 py-3 rounded-2xl flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-primary-700">{{ $salaryId ? 'تعديل الراتب' : 'إضافة راتب' }}</h1>
        <a href="{{ route('hr.salaries.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← عودة</a>
    </div>

    <form wire:submit="save" class="bg-white rounded-2xl shadow-sm border border-primary-100 p-6 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Delegate --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">المندوب <span class="text-red-500">*</span></label>
                <select wire:model.live="delegate_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                    <option value="">اختر المندوب</option>
                    @foreach($delegates as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
                @error('delegate_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Delegate Financial Info Card --}}
            @if(!empty($delegateInfo))
            <div class="md:col-span-2">
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                    <p class="text-xs font-bold text-amber-700 mb-3 flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                        البيانات المالية للمندوب: {{ $delegateInfo['name'] }}
                    </p>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                        <div class="bg-white rounded-xl border border-amber-100 px-3 py-2.5 text-center">
                            <p class="text-xs text-gray-500 mb-0.5">الراتب الأساسي</p>
                            <p class="text-lg font-extrabold text-primary-700">{{ number_format($delegateInfo['basic_salary'], 2) }}</p>
                        </div>
                        <div class="bg-white rounded-xl border border-amber-100 px-3 py-2.5 text-center">
                            <p class="text-xs text-gray-500 mb-0.5">نسبة العمولة</p>
                            <p class="text-lg font-extrabold text-primary-700">{{ number_format($delegateInfo['sales_commission_rate'], 2) }}%</p>
                        </div>
                        <div class="bg-white rounded-xl border border-amber-100 px-3 py-2.5 text-center">
                            <p class="text-xs text-gray-500 mb-0.5">العهدة (نقدي)</p>
                            <p class="text-lg font-extrabold text-blue-700">{{ number_format($delegateInfo['cash_custody'], 2) }}</p>
                        </div>
                        <div class="bg-white rounded-xl border border-amber-100 px-3 py-2.5 text-center">
                            <p class="text-xs text-gray-500 mb-0.5">المحصل</p>
                            <p class="text-lg font-extrabold text-green-700">{{ number_format($delegateInfo['total_collected'], 2) }}</p>
                        </div>
                        <div class="bg-white rounded-xl border border-amber-100 px-3 py-2.5 text-center">
                            <p class="text-xs text-gray-500 mb-0.5">عليه (مديونية)</p>
                            <p class="text-lg font-extrabold text-red-700">{{ number_format($delegateInfo['total_due'], 2) }}</p>
                        </div>
                    </div>
                    @if($delegateInfo['total_due'] > 0)
                    <div class="mt-3 flex items-center gap-2">
                        <button type="button" wire:click="applyDueAsDeduction"
                            class="text-xs bg-red-100 hover:bg-red-200 text-red-700 font-semibold px-3 py-1.5 rounded-lg transition-colors">
                            ← استخدام المديونية ({{ number_format($delegateInfo['total_due'], 2) }}) كخصم
                        </button>
                        <span class="text-xs text-gray-400">اضغط لتعبئة حقل الخصومات تلقائياً</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Month --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">الشهر <span class="text-red-500">*</span></label>
                <select wire:model="month" class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                    @foreach($months as $num => $label)
                        <option value="{{ $num }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('month')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Year --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">السنة <span class="text-red-500">*</span></label>
                <select wire:model="year" class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                    @foreach($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
                @error('year')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Basic Salary --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">الراتب الأساسي <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" min="0" wire:model.live="basic_salary"
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                @error('basic_salary')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Commissions --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">العمولات</label>
                <input type="number" step="0.01" min="0" wire:model.live="commissions"
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                @if(!empty($delegateInfo['sales_commission_rate']))
                <p class="text-xs text-amber-600 mt-1">نسبة عمولة المندوب: {{ $delegateInfo['sales_commission_rate'] }}%</p>
                @endif
                @error('commissions')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Bonuses --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">المكافآت</label>
                <input type="number" step="0.01" min="0" wire:model.live="bonuses"
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                @error('bonuses')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Deductions --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">الخصومات</label>
                <input type="number" step="0.01" min="0" wire:model.live="deductions"
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                @if(!empty($delegateInfo['total_due']) && $delegateInfo['total_due'] > 0)
                <p class="text-xs text-red-500 mt-1">مديونية المندوب: {{ number_format($delegateInfo['total_due'], 2) }}</p>
                @endif
                @error('deductions')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Net Salary Preview --}}
            <div class="md:col-span-2">
                <div class="bg-primary-50 border border-primary-200 rounded-xl px-5 py-4 flex items-center justify-between">
                    <span class="text-sm font-bold text-primary-700">صافي الراتب</span>
                    <span class="text-2xl font-extrabold text-primary-700">{{ number_format($this->netSalary, 2) }}</span>
                </div>
            </div>

            {{-- Account --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">الحساب المحاسبي</label>
                <select wire:model="account_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                    <option value="">بدون حساب</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Treasury --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">الخزنة (مصدر الصرف)</label>
                <select wire:model="treasury_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                    <option value="">بدون خزنة</option>
                    @foreach($treasuries as $tr)
                        <option value="{{ $tr->id }}">{{ $tr->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Notes --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">ملاحظات</label>
                <textarea wire:model="notes" rows="2" placeholder="ملاحظات..."
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm"></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('hr.salaries.index') }}" class="px-6 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-colors text-sm">إلغاء</a>
            <button type="submit" class="px-8 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold transition-colors text-sm">
                {{ $salaryId ? 'تحديث' : 'حفظ' }}
            </button>
        </div>
    </form>
</div>
