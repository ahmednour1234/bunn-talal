<x-layouts.app>
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700 tracking-tight">تقرير الفواتير والتحصيلات</h1>
            <p class="text-sm text-gray-400 mt-0.5">بعنا بكام، حصّلنا كام، الملغي والمرتجعات والصافي خلال الفترة</p>
        </div>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-700 text-white text-sm font-medium rounded-xl hover:bg-primary-800 transition-colors print:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
            طباعة
        </button>
    </div>

    {{-- Date Filter --}}
    <form method="GET" action="{{ route('reports.sales-collections') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6 print:hidden">
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
            <a href="{{ route('reports.sales-collections', ['date_from' => now()->startOfMonth()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}"
                class="px-3 py-2.5 bg-gray-100 text-gray-600 text-sm rounded-xl hover:bg-gray-200 transition-colors">هذا الشهر</a>
            <a href="{{ route('reports.sales-collections', ['date_from' => now()->startOfYear()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}"
                class="px-3 py-2.5 bg-gray-100 text-gray-600 text-sm rounded-xl hover:bg-gray-200 transition-colors">هذه السنة</a>
        </div>
    </form>

    <p class="text-xs text-gray-400 mb-5">
        الفترة: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
    </p>

    @php
        $fmt = fn($n) => number_format((float)$n, 2);
        $color = fn($n) => $n >= 0 ? 'text-green-600' : 'text-red-600';
    @endphp

    {{-- ═══ Summary Cards ═══ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">إجمالي المبيعات</p>
            <p class="text-2xl font-extrabold text-primary-700" dir="ltr">{{ $fmt($salesTotal) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $salesCount }} فاتورة — بعنا بكام</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">إجمالي المحصّل</p>
            <p class="text-2xl font-extrabold text-green-600" dir="ltr">{{ $fmt($totalCollected) }}</p>
            <p class="text-xs text-gray-400 mt-1">مدفوعات الفواتير + سندات التحصيل</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">صافي المرتجعات</p>
            <p class="text-2xl font-extrabold text-red-600" dir="ltr">{{ $fmt($returnsTotal) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $returnsCount }} مرتجع مؤكد</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 {{ $netSales >= 0 ? 'border-green-200 bg-green-50/30' : 'border-red-200 bg-red-50/30' }}">
            <p class="text-xs text-gray-400 mb-1">صافي المبيعات</p>
            <p class="text-2xl font-extrabold {{ $color($netSales) }}" dir="ltr">{{ $fmt($netSales) }}</p>
            <p class="text-xs text-gray-400 mt-1">المبيعات − المرتجعات</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- ═══ SECTION 1: SALES ═══ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
                </div>
                <h2 class="font-bold text-gray-700">الفواتير (المبيعات)</h2>
            </div>
            <div class="divide-y divide-gray-50">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">إجمالي الفواتير ({{ $salesCount }})</span>
                    <span class="text-sm font-bold text-gray-800" dir="ltr">{{ $fmt($salesTotal) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">المحصّل من الفواتير</span>
                    <span class="text-sm font-bold text-green-600" dir="ltr">{{ $fmt($salesCollected) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">المتبقي (آجل / غير محصل)</span>
                    <span class="text-sm font-bold text-orange-600" dir="ltr">{{ $fmt($salesOutstanding) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">فواتير ملغية ({{ $cancelledOrdersCount }})</span>
                    <span class="text-sm font-bold text-red-600" dir="ltr">{{ $fmt($cancelledOrdersTotal) }}</span>
                </div>
            </div>
        </div>

        {{-- ═══ SECTION 2: COLLECTIONS ═══ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                </div>
                <h2 class="font-bold text-gray-700">سندات التحصيل</h2>
            </div>
            <div class="divide-y divide-gray-50">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">تحصيلات مكتملة ({{ $collectionsCount }})</span>
                    <span class="text-sm font-bold text-green-600" dir="ltr">{{ $fmt($collectionsTotal) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">تحصيلات ملغية ({{ $cancelledCollectionsCount }})</span>
                    <span class="text-sm font-bold text-red-600" dir="ltr">{{ $fmt($cancelledCollectionsTotal) }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between px-5 py-4 bg-green-50/50 border-t border-green-100">
                <span class="text-sm font-bold text-green-700">إجمالي المحصّل (فواتير + سندات)</span>
                <span class="text-lg font-extrabold text-green-700" dir="ltr">{{ $fmt($totalCollected) }}</span>
            </div>
        </div>

        {{-- ═══ SECTION 3: RETURNS ═══ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                </div>
                <h2 class="font-bold text-gray-700">المرتجعات</h2>
            </div>
            <div class="divide-y divide-gray-50">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">مرتجعات مؤكدة ({{ $returnsCount }})</span>
                    <span class="text-sm font-bold text-red-600" dir="ltr">{{ $fmt($returnsTotal) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">مرتجعات ملغية ({{ $cancelledReturnsCount }})</span>
                    <span class="text-sm font-bold text-gray-500" dir="ltr">{{ $fmt($cancelledReturnsTotal) }}</span>
                </div>
            </div>
        </div>

        {{-- ═══ SECTION 4: NET ═══ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
                </div>
                <h2 class="font-bold text-gray-700">الصافي</h2>
            </div>
            <div class="divide-y divide-gray-50">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">صافي المبيعات (مبيعات − مرتجعات)</span>
                    <span class="text-sm font-bold {{ $color($netSales) }}" dir="ltr">{{ $fmt($netSales) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-600">صافي المحصّل (محصّل − مرتجعات)</span>
                    <span class="text-sm font-bold {{ $color($netCollected) }}" dir="ltr">{{ $fmt($netCollected) }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between px-5 py-4 bg-blue-50/50 border-t border-blue-100">
                <span class="text-sm font-bold text-blue-700">صافي المبيعات للفترة</span>
                <span class="text-lg font-extrabold {{ $color($netSales) }}" dir="ltr">{{ $fmt($netSales) }}</span>
            </div>
        </div>

    </div>
</x-layouts.app>
