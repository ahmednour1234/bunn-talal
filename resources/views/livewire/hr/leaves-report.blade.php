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
                <h1 class="text-2xl md:text-3xl font-extrabold text-primary-800 tracking-tight">تقرير الإجازات</h1>
                <p class="text-sm text-gray-600 mt-2">عرض وتحليل سجلات إجازات المناديب مع إحصائيات تفصيلية</p>
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
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
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
                <label class="text-xs font-semibold text-gray-500 mb-1 block">النوع</label>
                <select wire:model.live="typeFilter" class="w-full px-3 py-2.5 border border-gray-100 rounded-xl bg-white text-sm">
                    <option value="">الكل</option>
                    @foreach($types as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block">الحالة</label>
                <select wire:model.live="statusFilter" class="w-full px-3 py-2.5 border border-gray-100 rounded-xl bg-white text-sm">
                    <option value="">الكل</option>
                    @foreach($statuses as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block">من تاريخ</label>
                <input type="date" wire:model.live="dateFrom" class="w-full px-3 py-2.5 border border-gray-100 rounded-xl bg-white text-sm" />
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block">إلى تاريخ</label>
                <input type="date" wire:model.live="dateTo" class="w-full px-3 py-2.5 border border-gray-100 rounded-xl bg-white text-sm" />
            </div>
        </div>
        <div class="mt-3 flex items-center justify-between">
            <span class="text-xs text-primary-700 bg-primary-50 rounded-lg px-2 py-1">إجمالي السجلات: {{ $summary['total'] }}</span>
            <button wire:click="clearFilters" type="button"
                class="px-4 py-2 rounded-xl text-white text-xs font-semibold transition hover:opacity-90"
                style="background-color: #6b4f3a;">مسح الفلاتر</button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي الطلبات</p>
            <p class="text-2xl font-bold text-gray-800">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] text-center">
            <p class="text-xs text-gray-500 mb-1">موافق عليها</p>
            <p class="text-2xl font-bold text-green-600">{{ $summary['approved'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] text-center">
            <p class="text-xs text-gray-500 mb-1">قيد الانتظار</p>
            <p class="text-2xl font-bold text-amber-500">{{ $summary['pending'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] text-center">
            <p class="text-xs text-gray-500 mb-1">مرفوضة</p>
            <p class="text-2xl font-bold text-red-500">{{ $summary['rejected'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي أيام الإجازة</p>
            <p class="text-2xl font-bold text-primary-700">{{ $summary['total_days'] }}</p>
        </div>
    </div>

    {{-- By type breakdown --}}
    @if($byType->isNotEmpty())
    <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(15,23,42,0.05)] p-5 md:p-6">
        <h2 class="text-sm font-bold text-gray-700 mb-4">توزيع الإجازات حسب النوع</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($types as $val => $label)
                <div class="bg-amber-50/60 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 mb-1">{{ $label }}</p>
                    <p class="text-xl font-bold text-primary-700">{{ $byType[$val] ?? 0 }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(15,23,42,0.05)] overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-700">تفاصيل الإجازات ({{ $leaves->count() }} سجل)</h2>
        </div>
        @if($leaves->isEmpty())
            <div class="p-12 text-center text-gray-400 text-sm">لا توجد سجلات مطابقة للفلاتر المحددة</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold">#</th>
                        <th class="px-4 py-3 text-right font-semibold">المندوب</th>
                        <th class="px-4 py-3 text-right font-semibold">النوع</th>
                        <th class="px-4 py-3 text-right font-semibold">من</th>
                        <th class="px-4 py-3 text-right font-semibold">إلى</th>
                        <th class="px-4 py-3 text-right font-semibold">الأيام</th>
                        <th class="px-4 py-3 text-right font-semibold">الحالة</th>
                        <th class="px-4 py-3 text-right font-semibold">السبب</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($leaves as $i => $leave)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $leave->delegate?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $leave->type_label }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $leave->start_date?->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $leave->end_date?->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 font-semibold text-primary-700">{{ $leave->days }}</td>
                        <td class="px-4 py-3">
                            @if($leave->status === 'approved')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ $leave->status_label }}</span>
                            @elseif($leave->status === 'pending')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">{{ $leave->status_label }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">{{ $leave->status_label }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $leave->reason ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
