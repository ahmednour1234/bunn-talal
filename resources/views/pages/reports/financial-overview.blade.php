<x-layouts.app>
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700 tracking-tight">التقرير المالي الشامل</h1>
            <p class="text-sm text-gray-400 mt-0.5">نظرة كاملة على الوضع المالي — إيرادات، مصروفات، مخزون، ربح وخسارة</p>
        </div>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-700 text-white text-sm font-medium rounded-xl hover:bg-primary-800 transition-colors print:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
            طباعة
        </button>
    </div>

    {{-- Date Filter --}}
    <form method="GET" action="{{ route('reports.financial-overview') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6 print:hidden">
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
            <button type="submit" class="px-5 py-2.5 bg-primary-700 text-white text-sm font-medium rounded-xl hover:bg-primary-800 transition-colors">
                عرض التقرير
            </button>
            {{-- Quick periods --}}
            <a href="{{ route('reports.financial-overview', ['date_from' => now()->startOfMonth()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}"
                class="px-3 py-2.5 bg-gray-100 text-gray-600 text-sm rounded-xl hover:bg-gray-200 transition-colors">هذا الشهر</a>
            <a href="{{ route('reports.financial-overview', ['date_from' => now()->startOfYear()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}"
                class="px-3 py-2.5 bg-gray-100 text-gray-600 text-sm rounded-xl hover:bg-gray-200 transition-colors">هذه السنة</a>
        </div>
    </form>

    <p class="text-xs text-gray-400 mb-5 print:block hidden">
        الفترة: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
    </p>

    @php
        $fmt = fn($n) => number_format((float)$n, 2);
        $color = fn($n) => $n >= 0 ? 'text-green-600' : 'text-red-600';
    @endphp

    {{-- ═══ ROW 1: Net Worth Cards ═══ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        {{-- Total Assets --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">إجمالي الأصول</p>
            <p class="text-2xl font-extrabold text-primary-700">{{ $fmt($totalAssets) }}</p>
            <p class="text-xs text-gray-400 mt-1">كاش + ذمم + مخزون</p>
        </div>
        {{-- Total Liabilities --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">إجمالي الالتزامات</p>
            <p class="text-2xl font-extrabold text-red-600">{{ $fmt($totalLiabilities) }}</p>
            <p class="text-xs text-gray-400 mt-1">مستحق للموردين</p>
        </div>
        {{-- Net Worth --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">صافي الثروة</p>
            <p class="text-2xl font-extrabold {{ $color($netWorth) }}">{{ $fmt($netWorth) }}</p>
            <p class="text-xs text-gray-400 mt-1">أصول − التزامات</p>
        </div>
        {{-- Net Profit --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 {{ $netProfit >= 0 ? 'border-green-200 bg-green-50/30' : 'border-red-200 bg-red-50/30' }}">
            <p class="text-xs text-gray-400 mb-1">صافي الربح / الخسارة</p>
            <p class="text-2xl font-extrabold {{ $color($netProfit) }}">{{ $fmt($netProfit) }}</p>
            <p class="text-xs text-gray-400 mt-1">للفترة المحددة</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- ═══ SECTION 1: CASH ═══ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                </div>
                <h2 class="font-bold text-gray-700">النقدية والخزن</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($treasuries as $t)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">{{ $t->name }}</span>
                    <span class="text-sm font-bold text-gray-800">{{ $fmt($t->balance) }}</span>
                </div>
                @endforeach
            </div>
            <div class="flex items-center justify-between px-5 py-4 bg-blue-50/50 border-t border-blue-100">
                <span class="text-sm font-bold text-blue-700">إجمالي النقدية</span>
                <span class="text-lg font-extrabold text-blue-700">{{ $fmt($totalCash) }}</span>
            </div>
        </div>

        {{-- ═══ SECTION 2: RECEIVABLES ═══ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                </div>
                <h2 class="font-bold text-gray-700">فلوس عند العملاء</h2>
            </div>
            <div class="divide-y divide-gray-50">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">ذمم مبيعات غير محصلة</span>
                    <span class="text-sm font-bold text-gray-800">{{ $fmt($customerReceivables) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">أقساط متبقية</span>
                    <span class="text-sm font-bold text-gray-800">{{ $fmt($installmentReceivables) }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between px-5 py-4 bg-orange-50/50 border-t border-orange-100">
                <span class="text-sm font-bold text-orange-700">إجمالي الذمم</span>
                <span class="text-lg font-extrabold text-orange-700">{{ $fmt($totalReceivables) }}</span>
            </div>
        </div>

        {{-- ═══ SECTION 3: INVENTORY ═══ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                </div>
                <h2 class="font-bold text-gray-700">المخزون الحالي</h2>
            </div>
            <div class="divide-y divide-gray-50">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">إجمالي الكميات</span>
                    <span class="text-sm font-bold text-gray-800">{{ number_format($inventoryCount) }} وحدة</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">قيمة المخزون بسعر التكلفة</span>
                    <span class="text-sm font-bold text-gray-800">{{ $fmt($inventoryValue) }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between px-5 py-4 bg-purple-50/50 border-t border-purple-100">
                <span class="text-sm font-bold text-purple-700">قيمة البضاعة</span>
                <span class="text-lg font-extrabold text-purple-700">{{ $fmt($inventoryValue) }}</span>
            </div>
        </div>

        {{-- ═══ SECTION 4: PURCHASES ═══ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
                </div>
                <h2 class="font-bold text-gray-700">المشتريات في الفترة</h2>
            </div>
            <div class="divide-y divide-gray-50">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">إجمالي فواتير الشراء</span>
                    <span class="text-sm font-bold text-gray-800">{{ $fmt($purchaseCost) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">مرتجعات المشتريات</span>
                    <span class="text-sm font-bold text-green-600">− {{ $fmt($purchaseReturns) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">مستحق للموردين (غير مدفوع)</span>
                    <span class="text-sm font-bold text-red-600">{{ $fmt($supplierPayables) }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between px-5 py-4 bg-red-50/50 border-t border-red-100">
                <span class="text-sm font-bold text-red-700">صافي المشتريات</span>
                <span class="text-lg font-extrabold text-red-700">{{ $fmt($netPurchases) }}</span>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- ═══ SECTION 5: SALES ═══ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <h2 class="font-bold text-gray-700">المبيعات في الفترة</h2>
            </div>
            <div class="divide-y divide-gray-50">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">إجمالي المبيعات</span>
                    <span class="text-sm font-bold text-gray-800">{{ $fmt($salesRevenue) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">المحصل فعلاً</span>
                    <span class="text-sm font-bold text-green-600">{{ $fmt($salesCollected) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">مرتجعات المبيعات</span>
                    <span class="text-sm font-bold text-red-600">− {{ $fmt($salesReturns) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">إيرادات أخرى</span>
                    <span class="text-sm font-bold text-gray-800">{{ $fmt($otherRevenuesTotal) }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between px-5 py-4 bg-green-50/50 border-t border-green-100">
                <span class="text-sm font-bold text-green-700">إجمالي الإيرادات</span>
                <span class="text-lg font-extrabold text-green-700">{{ $fmt($totalRevenue) }}</span>
            </div>
        </div>

        {{-- ═══ SECTION 6: EXPENSES ═══ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.547 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.5c0-.953-.807-1.8-1.907-1.928A48.507 48.507 0 0 0 12 2.25Z" /></svg>
                </div>
                <h2 class="font-bold text-gray-700">المصروفات في الفترة</h2>
            </div>
            <div class="divide-y divide-gray-50 max-h-60 overflow-y-auto">
                @forelse($expenseLines as $line)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">{{ $line->account?->name ?? 'غير محدد' }}</span>
                    <span class="text-sm font-bold text-red-600">{{ $fmt($line->total) }}</span>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-sm text-gray-400">لا توجد مصروفات في هذه الفترة</div>
                @endforelse

                {{-- Depreciation as expense --}}
                @if($totalDepreciation > 0)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">إهلاك المنتجات</span>
                    <span class="text-sm font-bold text-red-600">{{ $fmt($totalDepreciation) }}</span>
                </div>
                @endif
            </div>
            <div class="flex items-center justify-between px-5 py-4 bg-yellow-50/50 border-t border-yellow-100">
                <span class="text-sm font-bold text-yellow-700">إجمالي المصروفات</span>
                <span class="text-lg font-extrabold text-yellow-700">{{ $fmt($totalExpenses + $totalDepreciation) }}</span>
            </div>
        </div>

    </div>

    {{-- ═══ SECTION 7: PROFIT SUMMARY ═══ --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
            </div>
            <h2 class="font-bold text-gray-700">ملخص الأرباح والخسائر</h2>
            <span class="mr-auto text-xs text-gray-400">
                {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x md:divide-x-reverse divide-gray-100">
            <div class="px-6 py-5 text-center">
                <p class="text-xs text-gray-400 mb-1">إجمالي الإيرادات</p>
                <p class="text-2xl font-extrabold text-green-600">{{ $fmt($totalRevenue) }}</p>
                <p class="text-xs text-gray-400 mt-1">مبيعات + إيرادات أخرى</p>
            </div>
            <div class="px-6 py-5 text-center">
                <p class="text-xs text-gray-400 mb-1">إجمالي التكاليف</p>
                <p class="text-2xl font-extrabold text-red-600">{{ $fmt($netPurchases + $totalExpenses + $totalDepreciation) }}</p>
                <p class="text-xs text-gray-400 mt-1">مشتريات + مصروفات + إهلاك</p>
            </div>
            <div class="px-6 py-5 text-center {{ $netProfit >= 0 ? 'bg-green-50/50' : 'bg-red-50/50' }}">
                <p class="text-xs text-gray-400 mb-1">{{ $netProfit >= 0 ? 'صافي الربح' : 'صافي الخسارة' }}</p>
                <p class="text-3xl font-extrabold {{ $color($netProfit) }}">{{ $fmt(abs($netProfit)) }}</p>
                <p class="text-xs {{ $color($netProfit) }} font-semibold mt-1">
                    @if($totalRevenue > 0)
                        هامش {{ number_format(($netProfit / $totalRevenue) * 100, 1) }}%
                    @endif
                </p>
            </div>
        </div>

        {{-- breakdown rows --}}
        <div class="border-t border-gray-100">
            <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-x-reverse divide-gray-100">
                <div class="px-5 py-4 text-center">
                    <p class="text-xs text-gray-400">مبيعات صافية</p>
                    <p class="font-bold text-gray-700 mt-1">{{ $fmt($netSales) }}</p>
                </div>
                <div class="px-5 py-4 text-center">
                    <p class="text-xs text-gray-400">مشتريات صافية</p>
                    <p class="font-bold text-gray-700 mt-1">{{ $fmt($netPurchases) }}</p>
                </div>
                <div class="px-5 py-4 text-center">
                    <p class="text-xs text-gray-400">مصروفات</p>
                    <p class="font-bold text-gray-700 mt-1">{{ $fmt($totalExpenses) }}</p>
                </div>
                <div class="px-5 py-4 text-center">
                    <p class="text-xs text-gray-400">إهلاك</p>
                    <p class="font-bold text-gray-700 mt-1">{{ $fmt($totalDepreciation) }}</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
