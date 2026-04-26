<div class="p-4 md:p-6 lg:p-7 space-y-6" dir="rtl">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>

    {{-- Header --}}
    <div class="rounded-3xl bg-gradient-to-l from-white via-primary-50/20 to-amber-50/25 p-6 md:p-7 shadow-[0_10px_30px_rgba(15,23,42,0.06)]">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-primary-800 tracking-tight">تقرير الرواتب</h1>
                <p class="text-sm text-gray-600 mt-2">عرض وتحليل رواتب المناديب مع إحصائيات مالية تفصيلية</p>
            </div>
            <div class="flex items-center gap-2 no-print">
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-xs font-semibold text-white hover:opacity-90 transition"
                    style="background-color: #6b4f3a;">
                    طباعة
                </button>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(15,23,42,0.05)] p-5 md:p-6 no-print">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block">المندوب</label>
                <select wire:model.live="delegateFilter" class="w-full px-3 py-2.5 border border-gray-100 rounded-xl bg-white text-sm">
                    <option value="">الكل</option>
                    @foreach($delegates as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block">السنة</label>
                <select wire:model.live="yearFilter" class="w-full px-3 py-2.5 border border-gray-100 rounded-xl bg-white text-sm">
                    <option value="">الكل</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block">الشهر</label>
                <select wire:model.live="monthFilter" class="w-full px-3 py-2.5 border border-gray-100 rounded-xl bg-white text-sm">
                    <option value="">الكل</option>
                    @foreach($months as $num => $label)
                        <option value="{{ $num }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block">الحالة</label>
                <select wire:model.live="statusFilter" class="w-full px-3 py-2.5 border border-gray-100 rounded-xl bg-white text-sm">
                    <option value="">الكل</option>
                    <option value="pending">قيد الانتظار</option>
                    <option value="paid">مصروف</option>
                </select>
            </div>
        </div>
        <div class="mt-3 flex items-center justify-between">
            <span class="text-xs text-primary-700 bg-primary-50 rounded-lg px-2 py-1">إجمالي السجلات: {{ $summary['total_records'] }}</span>
            <button wire:click="clearFilters" type="button"
                class="px-4 py-2 rounded-xl text-white text-xs font-semibold transition hover:opacity-90"
                style="background-color: #6b4f3a;">مسح الفلاتر</button>
        </div>
    </div>

    {{-- Financial Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] text-center">
            <p class="text-xs text-gray-500 mb-1">الراتب الأساسي</p>
            <p class="text-lg font-bold text-gray-800">{{ number_format($summary['total_basic'], 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] text-center">
            <p class="text-xs text-gray-500 mb-1">العمولات</p>
            <p class="text-lg font-bold text-blue-600">{{ number_format($summary['total_commissions'], 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] text-center">
            <p class="text-xs text-gray-500 mb-1">البدلات</p>
            <p class="text-lg font-bold text-green-600">{{ number_format($summary['total_bonuses'], 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] text-center">
            <p class="text-xs text-gray-500 mb-1">الخصومات</p>
            <p class="text-lg font-bold text-red-500">{{ number_format($summary['total_deductions'], 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] text-center border-2 border-primary-100">
            <p class="text-xs text-gray-500 mb-1">صافي الرواتب</p>
            <p class="text-lg font-bold text-primary-700">{{ number_format($summary['total_net'], 2) }}</p>
        </div>
    </div>

    {{-- Status summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">رواتب مصروفة</p>
                <p class="text-xl font-bold text-green-600">{{ $summary['paid_count'] }}</p>
                <p class="text-xs text-gray-400">{{ number_format($summary['total_paid_net'], 2) }} إجمالي</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">رواتب قيد الانتظار</p>
                <p class="text-xl font-bold text-amber-500">{{ $summary['pending_count'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">إجمالي الرواتب</p>
                <p class="text-xl font-bold text-primary-700">{{ $summary['total_records'] }}</p>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(15,23,42,0.05)] overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-700">تفاصيل الرواتب ({{ $salaries->count() }} سجل)</h2>
        </div>
        @if($salaries->isEmpty())
            <div class="p-12 text-center text-gray-400 text-sm">لا توجد سجلات مطابقة للفلاتر المحددة</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold">#</th>
                        <th class="px-4 py-3 text-right font-semibold">المندوب</th>
                        <th class="px-4 py-3 text-right font-semibold">الشهر</th>
                        <th class="px-4 py-3 text-right font-semibold">السنة</th>
                        <th class="px-4 py-3 text-right font-semibold">الأساسي</th>
                        <th class="px-4 py-3 text-right font-semibold">العمولات</th>
                        <th class="px-4 py-3 text-right font-semibold">البدلات</th>
                        <th class="px-4 py-3 text-right font-semibold">الخصومات</th>
                        <th class="px-4 py-3 text-right font-semibold">الصافي</th>
                        <th class="px-4 py-3 text-right font-semibold">الحالة</th>
                        <th class="px-4 py-3 text-right font-semibold">تاريخ الصرف</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($salaries as $i => $salary)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $salary->delegate?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $salary->month_label }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $salary->year }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ number_format($salary->basic_salary, 2) }}</td>
                        <td class="px-4 py-3 text-blue-600">{{ number_format($salary->commissions, 2) }}</td>
                        <td class="px-4 py-3 text-green-600">{{ number_format($salary->bonuses, 2) }}</td>
                        <td class="px-4 py-3 text-red-500">{{ number_format($salary->deductions, 2) }}</td>
                        <td class="px-4 py-3 font-bold text-primary-700">{{ number_format($salary->net_salary, 2) }}</td>
                        <td class="px-4 py-3">
                            @if($salary->status === 'paid')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">مصروف</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">قيد الانتظار</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $salary->paid_at?->toDateString() ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 text-xs font-bold border-t-2 border-gray-200">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-gray-600">المجموع</td>
                        <td class="px-4 py-3 text-gray-700">{{ number_format($summary['total_basic'], 2) }}</td>
                        <td class="px-4 py-3 text-blue-600">{{ number_format($summary['total_commissions'], 2) }}</td>
                        <td class="px-4 py-3 text-green-600">{{ number_format($summary['total_bonuses'], 2) }}</td>
                        <td class="px-4 py-3 text-red-500">{{ number_format($summary['total_deductions'], 2) }}</td>
                        <td class="px-4 py-3 text-primary-700">{{ number_format($summary['total_net'], 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>
</div>
