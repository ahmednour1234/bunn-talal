<div dir="rtl" class="space-y-6">
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm font-bold px-5 py-3 rounded-2xl flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm font-bold px-5 py-3 rounded-2xl flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">الرواتب</h1>
            <p class="text-sm text-gray-500 mt-1">إدارة وصرف رواتب المناديب</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('hr.create'))
        <x-button variant="primary" href="{{ route('hr.salaries.create') }}">
            <x-icon name="plus" class="w-4 h-4" />
            إضافة راتب
        </x-button>
        @endif
    </div>

    {{-- Filters --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <select wire:model.live="filterDelegate" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                <option value="">كل المناديب</option>
                @foreach($delegates as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterMonth" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                <option value="">كل الأشهر</option>
                @foreach($months as $num => $label)
                    <option value="{{ $num }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterYear" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                <option value="">كل السنوات</option>
                @foreach($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                <option value="">كل الحالات</option>
                <option value="pending">قيد الانتظار</option>
                <option value="paid">مصروف</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <x-data-table :headers="['#', 'المندوب', 'الشهر', 'السنة', 'الأساسي', 'العمولات', 'مكافآت', 'خصومات', 'الصافي', 'الحالة', 'الحساب', 'الإجراءات']">
        @forelse($salaries as $sal)
        <tr class="hover:bg-primary-50/50 transition-colors">
            <td class="px-4 py-4 text-gray-500 text-sm">{{ $sal->id }}</td>
            <td class="px-4 py-4 font-semibold text-gray-800">{{ $sal->delegate->name }}</td>
            <td class="px-4 py-4 text-sm text-gray-600">{{ $sal->month_label }}</td>
            <td class="px-4 py-4 text-sm text-gray-600">{{ $sal->year }}</td>
            <td class="px-4 py-4 text-sm font-medium text-gray-700">{{ number_format($sal->basic_salary, 2) }}</td>
            <td class="px-4 py-4 text-sm text-green-600">{{ number_format($sal->commissions, 2) }}</td>
            <td class="px-4 py-4 text-sm text-green-600">{{ number_format($sal->bonuses, 2) }}</td>
            <td class="px-4 py-4 text-sm text-red-600">{{ number_format($sal->deductions, 2) }}</td>
            <td class="px-4 py-4 text-sm font-bold text-primary-700">{{ number_format($sal->net_salary, 2) }}</td>
            <td class="px-4 py-4">
                @if($sal->status === 'paid')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">مصروف</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">قيد الانتظار</span>
                @endif
            </td>
            <td class="px-4 py-4 text-sm text-gray-500">{{ $sal->account?->name ?? '-' }}</td>
            <td class="px-4 py-4">
                <div class="flex items-center gap-2">
                    @if($sal->status === 'pending' && auth('admin')->user()?->hasPermission('hr.edit'))
                        <button wire:click="pay({{ $sal->id }})" wire:confirm="تأكيد صرف الراتب وخصمه من الخزنة والحساب؟"
                            class="text-xs bg-green-100 hover:bg-green-200 text-green-700 px-3 py-1.5 rounded-lg font-semibold transition-colors whitespace-nowrap">
                            صرف الراتب
                        </button>
                        <a href="{{ route('hr.salaries.edit', $sal->id) }}"
                            class="text-xs bg-primary-100 hover:bg-primary-200 text-primary-700 px-3 py-1.5 rounded-lg font-semibold transition-colors">
                            تعديل
                        </a>
                        <button wire:click="delete({{ $sal->id }})" wire:confirm="هل تريد حذف هذا الراتب؟"
                            class="text-xs bg-gray-100 hover:bg-red-100 text-gray-600 hover:text-red-600 px-3 py-1.5 rounded-lg font-semibold transition-colors">
                            حذف
                        </button>
                    @else
                        <span class="text-xs text-gray-400 italic">تم الصرف {{ $sal->paid_at?->format('Y-m-d') }}</span>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="12" class="px-6 py-12 text-center text-gray-400">لا توجد رواتب</td></tr>
        @endforelse
    </x-data-table>
    <div class="mt-4">{{ $salaries->links() }}</div>
</div>
