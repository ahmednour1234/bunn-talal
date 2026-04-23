<div class="p-4 md:p-6 lg:p-7 space-y-6">
    <style>
        .filters-row {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            flex-wrap: nowrap;
        }
        .filters-row .filter-item {
            flex: 1 1 0;
            min-width: 0;
        }
        .filters-row .filter-action {
            width: 140px;
            flex: 0 0 140px;
        }
        @media (max-width: 992px) {
            .filters-row {
                flex-wrap: wrap;
            }
            .filters-row .filter-item,
            .filters-row .filter-action {
                width: 100%;
                flex: 1 1 100%;
            }
        }
        @media print {
            .no-print { display: none !important; }
            .print-soft { box-shadow: none !important; border: 0 !important; }
            body { background: #fff !important; }
        }
    </style>

    <div>
        <div class="rounded-3xl bg-gradient-to-l from-white via-primary-50/20 to-amber-50/25 p-6 md:p-7 shadow-[0_10px_30px_rgba(15,23,42,0.06)] print-soft">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-primary-800 tracking-tight">تقرير مخازن الفروع</h1>
                    <p class="text-sm text-gray-600 mt-2">عرض المخزون الحالي لكل فرع مع إمكانية التصفية بالفرع ونطاق التاريخ</p>
                </div>
                <div class="flex items-center gap-2 no-print">
                    <a
                        href="{{ route('reports.branch-inventory.export.pdf', ['branch' => $branchFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                        class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-xs font-semibold text-white hover:opacity-90 transition"
                        style="background-color: #8b6a4a;"
                    >
                        PDF
                    </a>
                    <button
                        type="button"
                        onclick="window.print()"
                        class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-xs font-semibold text-white hover:opacity-90 transition"
                        style="background-color: #6b4f3a;"
                    >
                        طباعة
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(15,23,42,0.05)] p-5 md:p-6 no-print print-soft">
        <div class="filters-row">
            <div class="filter-item">
                <label class="text-xs font-semibold text-gray-500 mb-1 block">الفرع</label>
                <select wire:model.live="branchFilter" class="w-full px-4 py-2.5 border border-gray-100 rounded-xl bg-white text-sm focus:border-primary-300 focus:ring-primary-100">
                    <option value="">كل الفروع</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-item">
                <label class="text-xs font-semibold text-gray-500 mb-1 block">من تاريخ</label>
                <input type="date" wire:model.live="dateFrom" class="w-full px-4 py-2.5 border border-gray-100 rounded-xl bg-white text-sm focus:border-primary-300 focus:ring-primary-100" />
            </div>

            <div class="filter-item">
                <label class="text-xs font-semibold text-gray-500 mb-1 block">إلى تاريخ</label>
                <input type="date" wire:model.live="dateTo" class="w-full px-4 py-2.5 border border-gray-100 rounded-xl bg-white text-sm focus:border-primary-300 focus:ring-primary-100" />
            </div>

            <div class="filter-action">
                <button wire:click="clearFilters" type="button" class="w-full px-3 py-2.5 rounded-xl text-white text-xs font-semibold transition hover:opacity-90" style="background-color: #6b4f3a;">مسح</button>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 mt-4 pt-4 border-t border-gray-100/80">
            @if($dateFrom || $dateTo)
                <span class="text-xs text-gray-500 bg-gray-100 rounded-lg px-2 py-1">التاريخ: {{ $dateFrom ?: '---' }} - {{ $dateTo ?: '---' }}</span>
            @endif
            <span class="text-xs text-primary-700 bg-primary-50 rounded-lg px-2 py-1">عدد العناصر: {{ number_format(count($inventory)) }}</span>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white/80 rounded-2xl p-2.5 shadow-[0_6px_20px_rgba(15,23,42,0.04)] no-print print-soft">
        <div class="grid grid-cols-2 gap-2">
            <button
                type="button"
                wire:click="setTab('summary')"
                class="px-4 py-2.5 rounded-xl text-sm font-semibold transition"
                style="{{ $activeTab === 'summary' ? 'background-color:#6b4f3a;color:#ffffff;' : 'background-color:#ffffff;color:#6b4f3a;border:1px solid #ece7e2;' }}"
            >
                ملخص الفروع
            </button>
            <button
                type="button"
                wire:click="setTab('details')"
                class="px-4 py-2.5 rounded-xl text-sm font-semibold transition"
                style="{{ $activeTab === 'details' ? 'background-color:#6b4f3a;color:#ffffff;' : 'background-color:#ffffff;color:#6b4f3a;border:1px solid #ece7e2;' }}"
            >
                باقي التفاصيل (جدول)
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    @if($activeTab === 'summary' && count($summary) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 md:gap-6">
            @foreach($summary as $s)
                <div class="bg-white rounded-3xl shadow-[0_10px_24px_rgba(15,23,42,0.05)] p-5 md:p-6 hover:shadow-[0_14px_34px_rgba(15,23,42,0.08)] transition print-soft">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-primary-800">{{ $s->branch_name }}</h3>
                        <span class="text-[11px] bg-amber-50 text-amber-700 px-2 py-1 rounded-lg">فرع</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="bg-slate-50/70 rounded-2xl p-3.5">
                            <span class="text-xs text-gray-500">المنتجات</span>
                            <p class="font-extrabold text-gray-700 mt-1">{{ $s->total_products }}</p>
                        </div>
                        <div class="bg-slate-50/70 rounded-2xl p-3.5">
                            <span class="text-xs text-gray-500">الكمية</span>
                            <p class="font-extrabold text-gray-700 mt-1">{{ number_format($s->total_quantity) }}</p>
                        </div>
                        <div class="bg-primary-50/80 rounded-2xl p-3.5">
                            <span class="text-xs text-primary-700">قيمة التكلفة</span>
                            <p class="font-extrabold text-primary-800 mt-1">{{ number_format($s->total_cost_value, 2) }}</p>
                        </div>
                        <div class="bg-emerald-50/80 rounded-2xl p-3.5">
                            <span class="text-xs text-emerald-700">قيمة البيع</span>
                            <p class="font-extrabold text-emerald-800 mt-1">{{ number_format($s->total_selling_value, 2) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($activeTab === 'summary' && count($summary) === 0)
        <div class="bg-white rounded-3xl shadow-[0_10px_24px_rgba(15,23,42,0.05)] p-8 text-center print-soft">
            <p class="text-gray-400 font-semibold">لا يوجد ملخص متاح حسب الفلاتر الحالية</p>
        </div>
    @endif

    {{-- Inventory Table --}}
    @if($activeTab === 'details')
    <div class="bg-white rounded-3xl shadow-[0_10px_24px_rgba(15,23,42,0.05)] overflow-hidden print-soft">
        <div class="px-5 py-4 border-b border-gray-100/80 bg-gradient-to-l from-white to-primary-50/20">
            <p class="text-sm font-semibold text-primary-800">تفاصيل المخزون</p>
            <p class="text-xs text-gray-500 mt-1">عرض المنتجات المتاحة حاليا حسب الفرع</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-primary-50/45">
                    <tr>
                        <th class="px-6 py-4 text-right font-bold text-primary-800">الفرع</th>
                        <th class="px-6 py-4 text-right font-bold text-primary-800">المنتج</th>
                        <th class="px-6 py-4 text-right font-bold text-primary-800">التصنيف</th>
                        <th class="px-6 py-4 text-right font-bold text-primary-800">الوحدة</th>
                        <th class="px-6 py-4 text-right font-bold text-primary-800">الكمية</th>
                        <th class="px-6 py-4 text-right font-bold text-primary-800">سعر التكلفة</th>
                        <th class="px-6 py-4 text-right font-bold text-primary-800">سعر البيع</th>
                        <th class="px-6 py-4 text-right font-bold text-primary-800">قيمة التكلفة</th>
                        <th class="px-6 py-4 text-right font-bold text-primary-800">قيمة البيع</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($inventory as $item)
                        <tr class="hover:bg-amber-50/30 transition">
                            <td class="px-6 py-4 font-semibold text-gray-700">{{ $item->branch_name }}</td>
                            <td class="px-6 py-4 font-bold text-gray-800">{{ $item->product_name }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $item->category_name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $item->unit_name ?? '-' }}</td>
                            <td class="px-6 py-4 font-semibold">{{ number_format($item->quantity) }}</td>
                            <td class="px-6 py-4">{{ number_format($item->cost_price, 2) }}</td>
                            <td class="px-6 py-4">{{ number_format($item->selling_price, 2) }}</td>
                            <td class="px-6 py-4 text-primary-700 font-bold">{{ number_format($item->cost_value, 2) }}</td>
                            <td class="px-6 py-4 text-green-700 font-bold">{{ number_format($item->selling_value, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <p class="text-gray-400 font-semibold">لا توجد بيانات مطابقة للفلاتر الحالية</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
