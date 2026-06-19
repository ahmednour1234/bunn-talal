<x-layouts.app>
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700 tracking-tight">تقرير أرباح المنتجات</h1>
            <p class="text-sm text-gray-400 mt-0.5">سعر الشراء والبيع، المباع والمتبقي، المرتجعات والربح لكل منتج خلال الفترة</p>
        </div>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-700 text-white text-sm font-medium rounded-xl hover:bg-primary-800 transition-colors print:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
            طباعة
        </button>
    </div>

    @php
        $fmt   = fn($n) => number_format((float)$n, 2);
        $qfmt  = fn($n) => rtrim(rtrim(number_format((float)$n, 2), '0'), '.');
        $color = fn($n) => $n > 0 ? 'text-green-600' : ($n < 0 ? 'text-red-600' : 'text-gray-500');
    @endphp

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports.product-profit') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6 print:hidden">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">من تاريخ</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                    class="border border-gray-200 rounded-xl bg-gray-50 text-sm px-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">إلى تاريخ</label>
                <input type="date" name="date_to" value="{{ $dateTo }}"
                    class="border border-gray-200 rounded-xl bg-gray-50 text-sm px-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all">
            </div>
            @unless($scopedBranchId)
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">الفرع</label>
                <select name="branch_id"
                    class="border border-gray-200 rounded-xl bg-gray-50 text-sm px-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all">
                    <option value="">كل الفروع</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string)$branchId === (string)$branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endunless
            <button type="submit" class="px-5 py-2.5 bg-primary-700 text-white text-sm font-medium rounded-xl hover:bg-primary-800 transition-colors">
                عرض التقرير
            </button>
            <a href="{{ route('reports.product-profit', ['date_from' => now()->startOfMonth()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}"
                class="px-3 py-2.5 bg-gray-100 text-gray-600 text-sm rounded-xl hover:bg-gray-200 transition-colors">هذا الشهر</a>
            <a href="{{ route('reports.product-profit', ['date_from' => now()->startOfYear()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}"
                class="px-3 py-2.5 bg-gray-100 text-gray-600 text-sm rounded-xl hover:bg-gray-200 transition-colors">هذه السنة</a>
        </div>
    </form>

    <p class="text-xs text-gray-400 mb-5">
        الفترة: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
        @if($branchId) — الفرع: {{ $branches->firstWhere('id', (int)$branchId)?->name ?? '—' }} @else — كل الفروع @endif
    </p>

    {{-- ═══ Summary Cards ═══ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">إجمالي المبيعات</p>
            <p class="text-2xl font-extrabold text-primary-700" dir="ltr">{{ $fmt($totals['salesTotal']) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $activeOrdersCount }} فاتورة موجودة</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">إجمالي التكلفة</p>
            <p class="text-2xl font-extrabold text-amber-600" dir="ltr">{{ $fmt($totals['costTotal']) }}</p>
            <p class="text-xs text-gray-400 mt-1">تكلفة البضاعة المباعة</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">المرتجعات</p>
            <p class="text-2xl font-extrabold text-red-600" dir="ltr">{{ $fmt($totals['refundTotal']) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $returnsCount }} مرتجع</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">صافي الربح</p>
            <p class="text-2xl font-extrabold {{ $color($totals['netProfit']) }}" dir="ltr">{{ $fmt($totals['netProfit']) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $cancelledOrdersCount }} فاتورة ملغية</p>
        </div>
    </div>

    {{-- ═══ Products Table ═══ --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-right">
                <thead>
                    <tr class="bg-primary-700 text-white">
                        <th class="px-3 py-3 text-xs font-bold text-center">#</th>
                        <th class="px-3 py-3 text-xs font-bold">المنتج</th>
                        <th class="px-3 py-3 text-xs font-bold text-center">سعر الشراء</th>
                        <th class="px-3 py-3 text-xs font-bold text-center">سعر البيع</th>
                        <th class="px-3 py-3 text-xs font-bold text-center">المباع</th>
                        <th class="px-3 py-3 text-xs font-bold text-center">المرتجع</th>
                        <th class="px-3 py-3 text-xs font-bold text-center">صافي الكمية</th>
                        <th class="px-3 py-3 text-xs font-bold text-center">المتبقي بالمخزون</th>
                        <th class="px-3 py-3 text-xs font-bold text-center">المبيعات</th>
                        <th class="px-3 py-3 text-xs font-bold text-center">التكلفة</th>
                        <th class="px-3 py-3 text-xs font-bold text-center">مرتجعات</th>
                        <th class="px-3 py-3 text-xs font-bold text-center">صافي الربح</th>
                        <th class="px-3 py-3 text-xs font-bold text-center">عدد الفواتير</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $r)
                    <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-stone-50/40' }} hover:bg-stone-50 transition-colors border-b border-gray-50">
                        <td class="px-3 py-3 text-xs text-gray-400 font-mono text-center">{{ $i + 1 }}</td>
                        <td class="px-3 py-3">
                            <p class="font-bold text-gray-800 text-sm truncate">{{ $r['product']->name }}</p>
                            <p class="text-xs text-gray-400">{{ $r['product']->unit?->symbol ?? '' }}</p>
                        </td>
                        <td class="px-3 py-3 text-center text-gray-700" dir="ltr">{{ $fmt($r['avgCostPrice']) }}</td>
                        <td class="px-3 py-3 text-center text-gray-700" dir="ltr">{{ $fmt($r['avgSellPrice']) }}</td>
                        <td class="px-3 py-3 text-center font-medium text-gray-800" dir="ltr">{{ $qfmt($r['qtySold']) }}</td>
                        <td class="px-3 py-3 text-center text-red-600" dir="ltr">{{ $qfmt($r['qtyReturned']) }}</td>
                        <td class="px-3 py-3 text-center font-bold text-gray-800" dir="ltr">{{ $qfmt($r['qtyNet']) }}</td>
                        <td class="px-3 py-3 text-center text-gray-600" dir="ltr">{{ $qfmt($r['remainingQty']) }}</td>
                        <td class="px-3 py-3 text-center text-primary-700 font-medium" dir="ltr">{{ $fmt($r['salesTotal']) }}</td>
                        <td class="px-3 py-3 text-center text-amber-600" dir="ltr">{{ $fmt($r['costTotal']) }}</td>
                        <td class="px-3 py-3 text-center text-red-600" dir="ltr">{{ $fmt($r['refundTotal']) }}</td>
                        <td class="px-3 py-3 text-center font-extrabold {{ $color($r['netProfit']) }}" dir="ltr">{{ $fmt($r['netProfit']) }}</td>
                        <td class="px-3 py-3 text-center text-gray-600">{{ $r['invoices'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="px-5 py-16 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                            <p class="text-gray-400 text-sm">لا توجد مبيعات في هذه الفترة</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                <tfoot>
                    <tr class="bg-primary-50 border-t-2 border-primary-200 font-bold">
                        <td class="px-3 py-3 text-center" colspan="4">الإجمالي</td>
                        <td class="px-3 py-3 text-center text-gray-800" dir="ltr">{{ $qfmt($totals['qtySold']) }}</td>
                        <td class="px-3 py-3 text-center text-red-600" dir="ltr">{{ $qfmt($totals['qtyReturned']) }}</td>
                        <td class="px-3 py-3" colspan="2"></td>
                        <td class="px-3 py-3 text-center text-primary-700" dir="ltr">{{ $fmt($totals['salesTotal']) }}</td>
                        <td class="px-3 py-3 text-center text-amber-600" dir="ltr">{{ $fmt($totals['costTotal']) }}</td>
                        <td class="px-3 py-3 text-center text-red-600" dir="ltr">{{ $fmt($totals['refundTotal']) }}</td>
                        <td class="px-3 py-3 text-center {{ $color($totals['netProfit']) }}" dir="ltr">{{ $fmt($totals['netProfit']) }}</td>
                        <td class="px-3 py-3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-layouts.app>
