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
                <h1 class="text-2xl md:text-3xl font-extrabold text-primary-800 tracking-tight">تقرير الحضور والغياب</h1>
                <p class="text-sm text-gray-600 mt-2">عرض وتحليل سجلات حضور المناديب مع ملخص تفصيلي لكل مندوب</p>
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
            <p class="text-xs text-gray-500 mb-1">إجمالي السجلات</p>
            <p class="text-2xl font-bold text-gray-800">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] text-center">
            <p class="text-xs text-gray-500 mb-1">حاضر</p>
            <p class="text-2xl font-bold text-green-600">{{ $summary['present'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] text-center">
            <p class="text-xs text-gray-500 mb-1">غائب</p>
            <p class="text-2xl font-bold text-red-500">{{ $summary['absent'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] text-center">
            <p class="text-xs text-gray-500 mb-1">متأخر</p>
            <p class="text-2xl font-bold text-amber-500">{{ $summary['late'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_16px_rgba(15,23,42,0.05)] text-center">
            <p class="text-xs text-gray-500 mb-1">في إجازة</p>
            <p class="text-2xl font-bold text-blue-500">{{ $summary['on_leave'] }}</p>
        </div>
    </div>

    {{-- Per-delegate summary --}}
    @if($perDelegate->isNotEmpty())
    <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(15,23,42,0.05)] overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-700">ملخص حضور كل مندوب</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold">المندوب</th>
                        <th class="px-4 py-3 text-center font-semibold">حاضر</th>
                        <th class="px-4 py-3 text-center font-semibold">غائب</th>
                        <th class="px-4 py-3 text-center font-semibold">متأخر</th>
                        <th class="px-4 py-3 text-center font-semibold">في إجازة</th>
                        <th class="px-4 py-3 text-center font-semibold">المجموع</th>
                        <th class="px-4 py-3 text-center font-semibold">نسبة الحضور</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($perDelegate as $row)
                    @php $pct = $row['total'] > 0 ? round(($row['present'] / $row['total']) * 100) : 0; @endphp
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $row['name'] }}</td>
                        <td class="px-4 py-3 text-center text-green-600 font-semibold">{{ $row['present'] }}</td>
                        <td class="px-4 py-3 text-center text-red-500 font-semibold">{{ $row['absent'] }}</td>
                        <td class="px-4 py-3 text-center text-amber-500 font-semibold">{{ $row['late'] }}</td>
                        <td class="px-4 py-3 text-center text-blue-500 font-semibold">{{ $row['on_leave'] }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 font-semibold">{{ $row['total'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Detailed records --}}
    <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(15,23,42,0.05)] overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-700">السجلات التفصيلية ({{ $records->count() }} سجل)</h2>
        </div>
        @if($records->isEmpty())
            <div class="p-12 text-center text-gray-400 text-sm">لا توجد سجلات مطابقة للفلاتر المحددة</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold">#</th>
                        <th class="px-4 py-3 text-right font-semibold">المندوب</th>
                        <th class="px-4 py-3 text-right font-semibold">التاريخ</th>
                        <th class="px-4 py-3 text-right font-semibold">وقت الدخول</th>
                        <th class="px-4 py-3 text-right font-semibold">وقت الخروج</th>
                        <th class="px-4 py-3 text-right font-semibold">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($records as $i => $rec)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $rec->delegate?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $rec->date }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $rec->check_in ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $rec->check_out ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $colors = ['present' => 'bg-green-100 text-green-700', 'absent' => 'bg-red-100 text-red-700',
                                           'late' => 'bg-amber-100 text-amber-700', 'on_leave' => 'bg-blue-100 text-blue-700'];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $colors[$rec->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $rec->status_label }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
