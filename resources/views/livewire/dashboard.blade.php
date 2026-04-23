<div>
    @php
        $netProfit = $saleOrdersTotal - $saleReturnsTotal - $purchaseTotal + $purchaseReturnsTotal;
        $today = \Carbon\Carbon::now()->locale('ar')->isoFormat('dddd، D MMMM YYYY');
    @endphp

    {{-- HEADER --}}
    <div class="relative rounded-2xl px-6 py-5 mb-6 overflow-hidden shadow-lg" style="background:linear-gradient(135deg,#4E342E 0%,#3E2723 100%)">
        <div class="flex items-center justify-between relative z-10">
            <div class="flex items-center gap-3 flex-wrap">
                @if($pendingSaleOrdersCount > 0)
                <span class="inline-flex items-center gap-1.5 bg-white/10 border border-white/20 text-white/90 text-xs font-bold px-3 py-1.5 rounded-full backdrop-blur-sm">
                    <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                    {{ $pendingSaleOrdersCount }} طلب بانتظار التأكيد
                </span>
                @endif
                @if($lowStockCount > 0)
                <span class="inline-flex items-center gap-1.5 bg-white/10 border border-white/20 text-white/90 text-xs font-bold px-3 py-1.5 rounded-full backdrop-blur-sm">
                    <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                    {{ $lowStockCount }} منتج مخزونه منخفض
                </span>
                @endif
                @if($saleOrdersRemaining > 0)
                <span class="inline-flex items-center gap-1.5 bg-white/10 border border-white/20 text-white/90 text-xs font-bold px-3 py-1.5 rounded-full backdrop-blur-sm">
                    غير محصّل: {{ number_format($saleOrdersRemaining, 0) }} ج.م
                </span>
                @endif
                @if($activeTripsCount > 0)
                <span class="inline-flex items-center gap-1.5 bg-white/10 border border-white/20 text-white/90 text-xs font-bold px-3 py-1.5 rounded-full backdrop-blur-sm">
                    <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
                    {{ $activeTripsCount }} رحلة نشطة
                </span>
                @endif
                @if($pendingBookingRequests > 0)
                <span class="inline-flex items-center gap-1.5 bg-white/10 border border-white/20 text-white/90 text-xs font-bold px-3 py-1.5 rounded-full backdrop-blur-sm">
                    <span class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></span>
                    {{ $pendingBookingRequests }} طلب حجز معلق
                </span>
                @endif
            </div>
            <div class="text-right">
                <h1 class="text-2xl font-extrabold text-white">لوحة التحكم</h1>
                <p class="text-xs text-primary-200 mt-0.5">{{ $today }}</p>
            </div>
        </div>
    </div>

    {{-- ROW 1 - MAIN KPI --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

        <a href="{{ route('sale-orders.index') }}" class="rounded-2xl bg-primary-800 p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block group">                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 p-2.5 rounded-xl">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    </div>
                    <span class="text-xs text-primary-200 font-semibold">إجمالي المبيعات</span>
                </div>
                <p class="text-2xl font-extrabold text-white text-right leading-none mb-1">{{ number_format($saleOrdersTotal, 0) }}<span class="text-sm font-medium text-primary-200 mr-1">ج.م</span></p>
                <p class="text-xs text-primary-200 text-right">محصّل: <span class="text-white font-bold">{{ number_format($saleOrdersPaid, 0) }} ج.م</span></p>
        </a>

        <a href="{{ route('purchase-invoices.index') }}" class="rounded-2xl bg-primary-700 p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block group">                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 p-2.5 rounded-xl">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                    </div>
                    <span class="text-xs text-primary-200 font-semibold">إجمالي المشتريات</span>
                </div>
                <p class="text-2xl font-extrabold text-white text-right leading-none mb-1">{{ number_format($purchaseTotal, 0) }}<span class="text-sm font-medium text-primary-200 mr-1">ج.م</span></p>
                <p class="text-xs text-primary-200 text-right">
                    @if($unpaidPurchasesCount > 0)
                        <span class="text-white font-bold">{{ $unpaidPurchasesCount }} فاتورة غير مسددة</span>
                    @else
                        <span class="text-white font-bold">جميع الفواتير مسددة ✓</span>
                    @endif
                </p>
        </a>

        <a href="{{ route('treasuries.index') }}" class="rounded-2xl bg-primary-600 p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block group">                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 p-2.5 rounded-xl">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </div>
                    <span class="text-xs text-primary-200 font-semibold">رصيد الخزائن</span>
                </div>
                <p class="text-2xl font-extrabold text-white text-right leading-none mb-1">{{ number_format($totalTreasuryBalance, 0) }}<span class="text-sm font-medium text-primary-200 mr-1">ج.م</span></p>
                <p class="text-xs text-primary-200 text-right">{{ $financialTransactionsCount }} معاملة مالية</p>
        </a>

        <a href="{{ route('products.index') }}" class="rounded-2xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block group" style="background:#C9A66B">                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 p-2.5 rounded-xl">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                    </div>
                    <span class="text-xs text-primary-200 font-semibold">قيمة المخزون</span>
                </div>
                <p class="text-2xl font-extrabold text-white text-right leading-none mb-1">{{ number_format($totalStockValue, 0) }}<span class="text-sm font-medium text-primary-200 mr-1">ج.م</span></p>
                <p class="text-xs text-primary-200 text-right">{{ $productsCount }} منتج @if($lowStockCount > 0)&bull; <span class="text-white font-bold">{{ $lowStockCount }} منخفض</span>@endif</p>
        </a>

    </div>

    {{-- ROW 2 - SECONDARY KPI --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <a href="{{ route('sale-orders.index') }}" class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5 hover:shadow-md hover:-translate-y-0.5 transition-all block group">
            <div class="flex items-center justify-between mb-3">
                <div class="bg-stone-100 p-2 rounded-xl">
                    <svg class="w-4 h-4 text-primary-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2" /></svg>
                </div>
                <span class="text-xs text-gray-400 font-semibold">طلبات المبيعات</span>
            </div>
            <p class="text-3xl font-extrabold text-gray-800 text-right leading-none mb-2">{{ $saleOrdersCount }}</p>
            <p class="text-xs text-right">
                @if($pendingSaleOrdersCount > 0)
                    <span class="inline-flex items-center gap-1 bg-stone-100 text-stone-700 px-2 py-0.5 rounded-full font-semibold border border-stone-200"><span class="w-1.5 h-1.5 bg-stone-500 rounded-full animate-pulse"></span>{{ $pendingSaleOrdersCount }} معلق</span>
                @else
                    <span class="inline-flex items-center gap-1 bg-stone-100 text-stone-600 px-2 py-0.5 rounded-full font-semibold border border-stone-200">لا يوجد معلق</span>
                @endif
            </p>
        </a>

        <a href="{{ route('delegates.index') }}" class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5 hover:shadow-md hover:-translate-y-0.5 transition-all block group">
            <div class="flex items-center justify-between mb-3">
                <div class="bg-stone-100 p-2 rounded-xl">
                    <svg class="w-4 h-4 text-primary-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0M15 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                </div>
                <span class="text-xs text-gray-400 font-semibold">المناديب</span>
            </div>
            <p class="text-3xl font-extrabold text-gray-800 text-right leading-none mb-2">{{ $delegatesCount }}</p>
            <p class="text-xs text-gray-500 text-right">عهدة: <span class="text-primary-700 font-bold">{{ number_format($totalDelegatesCustody, 0) }} ج.م</span></p>
        </a>

        <a href="{{ route('reports.income-statement') }}" class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5 hover:shadow-md hover:-translate-y-0.5 transition-all block group">
            <div class="flex items-center justify-between mb-3">
                <div class="bg-stone-100 p-2 rounded-xl">
                    <svg class="w-4 h-4 text-primary-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $netProfit >= 0 ? 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941' : 'M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0-3.182-5.511M20.25 21l-1.368-5.612' }}" /></svg>
                </div>
                <span class="text-xs text-gray-400 font-semibold">صافي الربح</span>
            </div>
            <p class="text-2xl font-extrabold text-right leading-none mb-2 {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-red-500' }}">{{ number_format(abs($netProfit), 0) }}<span class="text-sm font-medium text-gray-400 mr-1">ج.م</span></p>
            <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-full text-white" style="{{ $netProfit >= 0 ? 'background:#4CAF50' : 'background:#D9534F' }}">{{ $netProfit >= 0 ? '▲ ربح' : '▼ خسارة' }}</span>
        </a>

        <a href="{{ route('financial-transactions.index') }}" class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5 hover:shadow-md hover:-translate-y-0.5 transition-all block group">
            <div class="flex items-center justify-between mb-3">
                <div class="bg-stone-100 p-2 rounded-xl">
                    <svg class="w-4 h-4 text-primary-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                </div>
                <span class="text-xs text-gray-400 font-semibold">المعاملات المالية</span>
            </div>
            <p class="text-3xl font-extrabold text-gray-800 text-right leading-none mb-2">{{ $financialTransactionsCount }}</p>
            <p class="text-xs text-gray-500 text-right">{{ $accountsCount }} حساب نشط</p>
        </a>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- TRIPS & DELEGATES STATISTICS --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    @if(($activeTripsCount + $settledTripsCount + $draftTripsCount) > 0 || $pendingBookingRequests > 0)
    <div class="mb-4">
        {{-- Section header --}}
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-primary-700/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                </div>
                <h2 class="text-sm font-extrabold text-gray-800">الرحلات والمناديب</h2>
            </div>
            <a href="{{ route('trips.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-800">عرض كل الرحلات ←</a>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-3">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-all">
                <p class="text-xs text-gray-400 mb-1">رحلات نشطة</p>
                <p class="text-2xl font-extrabold {{ $activeTripsCount > 0 ? 'text-primary-700' : 'text-gray-300' }}">{{ $activeTripsCount }}</p>
                <p class="text-xs text-gray-400 mt-0.5">مندوب في الطريق: <span class="font-bold text-gray-700">{{ $delegatesOnTrip }}</span></p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-all">
                <p class="text-xs text-gray-400 mb-1">مسودة</p>
                <p class="text-2xl font-extrabold {{ $draftTripsCount > 0 ? 'text-amber-600' : 'text-gray-300' }}">{{ $draftTripsCount }}</p>
                <p class="text-xs text-gray-400 mt-0.5">لم تبدأ بعد</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-all">
                <p class="text-xs text-gray-400 mb-1">مُسوَّاة</p>
                <p class="text-2xl font-extrabold text-green-600">{{ $settledTripsCount }}</p>
                <p class="text-xs text-gray-400 mt-0.5">منها خسارة: <span class="font-bold {{ $tripsWithDeficit > 0 ? 'text-red-500' : 'text-gray-400' }}">{{ $tripsWithDeficit }}</span></p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-all">
                <p class="text-xs text-gray-400 mb-1">طلبات حجز معلقة</p>
                <p class="text-2xl font-extrabold {{ $pendingBookingRequests > 0 ? 'text-amber-600' : 'text-gray-300' }}">{{ $pendingBookingRequests }}</p>
                <p class="text-xs text-gray-400 mt-0.5">بانتظار المراجعة</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-all {{ ($totalTripCashDeficit + $totalTripProductDeficit) > 0 ? 'border-red-200 bg-red-50/40' : '' }}">
                <p class="text-xs text-gray-400 mb-1">عجز كاش كلي</p>
                <p class="text-xl font-extrabold {{ $totalTripCashDeficit > 0 ? 'text-red-600' : 'text-gray-300' }}">{{ $totalTripCashDeficit > 0 ? number_format($totalTripCashDeficit, 0) : '—' }}</p>
                @if($totalTripCashDeficit > 0)<p class="text-xs text-red-400">ج.م</p>@endif
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-all {{ $totalTripProductDeficit > 0 ? 'border-red-200 bg-red-50/40' : '' }}">
                <p class="text-xs text-gray-400 mb-1">عجز بضاعة كلي</p>
                <p class="text-xl font-extrabold {{ $totalTripProductDeficit > 0 ? 'text-red-600' : 'text-gray-300' }}">{{ $totalTripProductDeficit > 0 ? number_format($totalTripProductDeficit, 0) : '—' }}</p>
                @if($totalTripProductDeficit > 0)<p class="text-xs text-red-400">ج.م</p>@endif
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-all {{ ($totalTripCashDeficit + $totalTripProductDeficit) > 0 ? 'border-red-200 bg-red-50/40' : 'border-green-200 bg-green-50/40' }}">
                <p class="text-xs text-gray-400 mb-1">إجمالي الخسائر</p>
                @php $totalLoss = $totalTripCashDeficit + $totalTripProductDeficit; @endphp
                <p class="text-xl font-extrabold {{ $totalLoss > 0 ? 'text-red-700' : 'text-green-600' }}">{{ $totalLoss > 0 ? number_format($totalLoss, 0) : '✓' }}</p>
                @if($totalLoss > 0)<p class="text-xs text-red-400">ج.م</p>@else<p class="text-xs text-green-500">لا خسائر</p>@endif
            </div>
        </div>

        {{-- Delegate Trip Performance Table --}}
        @if($delegateTripPerformance->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-gray-50">
                <h3 class="text-xs font-bold text-gray-700">أداء المناديب في الرحلات</h3>
                <a href="{{ route('trips.index') }}" class="text-xs text-primary-600 font-semibold hover:text-primary-800">عرض الكل</a>
            </div>
            <table class="w-full text-sm text-right">
                <thead>
                    <tr class="text-xs font-semibold text-gray-400 border-b border-gray-50">
                        <th class="px-4 py-2.5">المندوب</th>
                        <th class="px-3 py-2.5 text-center">إجمالي الرحلات</th>
                        <th class="px-3 py-2.5 text-center">نشطة الآن</th>
                        <th class="px-3 py-2.5 text-center">عجز كاش</th>
                        <th class="px-3 py-2.5 text-center">عجز بضاعة</th>
                        <th class="px-3 py-2.5 text-center">التقييم</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($delegateTripPerformance as $del)
                    @php $hasLoss = ($del->total_cash_def + $del->total_prod_def) > 0; @endphp
                    <tr class="{{ $hasLoss ? 'bg-red-50/30' : '' }} hover:bg-primary-50/40 transition-colors cursor-pointer" onclick="window.location='{{ route('delegates.show', $del->id) }}'">
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-primary-100 flex items-center justify-center text-xs font-extrabold text-primary-700 flex-shrink-0">
                                    {{ mb_substr($del->name, 0, 1) }}
                                </div>
                                <span class="font-semibold text-gray-800 group-hover:text-primary-700">{{ $del->name }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-2.5 text-center font-bold text-gray-700">{{ $del->trips_total }}</td>
                        <td class="px-3 py-2.5 text-center">
                            @if($del->trips_active > 0)
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-primary-700 bg-primary-50 px-2 py-0.5 rounded-full">
                                <span class="w-1.5 h-1.5 bg-primary-500 rounded-full animate-pulse"></span>{{ $del->trips_active }}
                            </span>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @if($del->total_cash_def > 0)
                            <span class="text-xs font-bold text-red-600">{{ number_format($del->total_cash_def, 0) }} ج.م</span>
                            @else
                            <span class="text-green-500 text-xs">✓</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @if($del->total_prod_def > 0)
                            <span class="text-xs font-bold text-red-600">{{ number_format($del->total_prod_def, 0) }} ج.م</span>
                            @else
                            <span class="text-green-500 text-xs">✓</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @php $rating = (float)($del->rating ?? 100); @endphp
                                <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $rating >= 80 ? 'bg-green-500' : ($rating >= 60 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ min(100, $rating) }}%"></div>
                                </div>
                                <span class="text-xs font-bold {{ $rating >= 80 ? 'text-green-600' : ($rating >= 60 ? 'text-amber-600' : 'text-red-600') }}">{{ $rating }}</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Deficit Alerts --}}
        @if($tripDeficitAlerts->isNotEmpty())
        <div class="mt-3 bg-red-50 border border-red-200 rounded-2xl overflow-hidden">
            <div class="px-5 py-3 border-b border-red-100 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                <h3 class="text-xs font-bold text-red-700">تنبيهات عجز في الرحلات المُسوَّاة</h3>
            </div>
            <div class="divide-y divide-red-100">
            @foreach($tripDeficitAlerts as $ta)
            <a href="{{ route('trips.show', $ta->id) }}" class="flex items-center justify-between px-5 py-3 hover:bg-red-100/50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-red-100 flex items-center justify-center text-xs font-bold text-red-700 flex-shrink-0">{{ mb_substr($ta->delegate?->name ?? '?', 0, 1) }}</div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">{{ $ta->trip_number }} — {{ $ta->delegate?->name }}</p>
                        <p class="text-xs text-gray-500">{{ $ta->settled_at?->format('Y-m-d') }}</p>
                    </div>
                </div>
                <div class="text-left space-y-0.5">
                    @if($ta->settlement_cash_deficit > 0)
                    <p class="text-xs text-red-600 font-bold">كاش: {{ number_format($ta->settlement_cash_deficit, 2) }} ج.م</p>
                    @endif
                    @if($ta->settlement_product_deficit > 0)
                    <p class="text-xs text-red-600 font-bold">بضاعة: {{ number_format($ta->settlement_product_deficit, 2) }} ج.م</p>
                    @endif
                </div>
            </a>
            @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ROW 3 - TODAY ORDERS + TODAY TRANSACTIONS --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-4">

        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-stone-50 flex items-center justify-between px-5 py-4 border-b border-stone-100">
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500">إجمالي: <span class="text-primary-700 font-bold">{{ number_format($todaySaleOrdersTotal, 0) }} ج.م</span> &bull; محصّل: <span class="text-green-600 font-bold">{{ number_format($todaySaleOrdersPaid, 0) }} ج.م</span></span>
                    <a href="{{ route('sale-orders.index') }}" class="text-xs text-primary-700 hover:text-primary-800 font-semibold bg-stone-100 px-2.5 py-1 rounded-lg hover:bg-stone-200 transition-colors">عرض الكل</a>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1 bg-stone-200 text-stone-700 text-xs font-bold px-3 py-1 rounded-full"><span class="w-1.5 h-1.5 bg-primary-700 rounded-full"></span>{{ $todaySaleOrdersCount }} أمر اليوم</span>
                    <h3 class="text-sm font-bold text-gray-800">أوامر الصرف اليوم</h3>
                    <div class="bg-stone-200 p-1.5 rounded-lg"><svg class="w-4 h-4 text-primary-800" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg></div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead>
                        <tr class="bg-primary-700">
                            <th class="px-4 py-3 text-xs font-bold text-white">رقم الأمر</th>
                            <th class="px-4 py-3 text-xs font-bold text-white">العميل</th>
                            <th class="px-4 py-3 text-xs font-bold text-white">المندوب</th>
                            <th class="px-4 py-3 text-xs font-bold text-white">الإجمالي</th>
                            <th class="px-4 py-3 text-xs font-bold text-white">المحصّل</th>
                            <th class="px-4 py-3 text-xs font-bold text-white">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todaySaleOrders as $i => $order)
                        @php
                            $sc = ['draft'=>['bg-stone-100 text-stone-600 border border-stone-200','مسودة'],'confirmed'=>['text-white','مؤكد','background:#6D4C41'],'partial_paid'=>['text-white','جزئي','background:#F4B400'],'paid'=>['text-white','مدفوع','background:#4CAF50'],'cancelled'=>['text-white','ملغي','background:#D9534F']];
                            $s = $sc[$order->status] ?? ['bg-gray-100 text-gray-600', $order->status, ''];
                        @endphp
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/40' }} hover:bg-stone-50/40 transition-colors border-b border-gray-50">
                            <td class="px-4 py-3.5 font-mono text-xs font-bold text-primary-700">{{ $order->order_number }}</td>
                            <td class="px-4 py-3.5 text-gray-700 font-semibold">{{ $order->customer?->name ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-gray-500 text-xs">{{ $order->delegate?->name ?? '—' }}</td>
                            <td class="px-4 py-3.5 font-bold text-gray-800">{{ number_format($order->total, 0) }}<span class="text-xs text-gray-400 font-normal mr-0.5">ج.م</span></td>
                            <td class="px-4 py-3.5 font-bold text-primary-700">{{ number_format($order->paid_amount, 0) }}<span class="text-xs text-gray-400 font-normal mr-0.5">ج.م</span></td>
                            <td class="px-4 py-3.5"><span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold {{ $s[0] }}" @if(!empty($s[2])) style="{{ $s[2] }}" @endif>{{ $s[1] }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center"><div class="flex flex-col items-center gap-3"><div class="w-14 h-14 rounded-full bg-stone-100 flex items-center justify-center"><svg class="w-7 h-7 text-stone-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg></div><p class="text-sm text-gray-400 font-medium">لا توجد أوامر صرف اليوم</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="bg-gray-50 px-4 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 text-right mb-2">معاملات اليوم</h3>
                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-xl px-3 py-2 text-right" style="background:#f0fdf4;border:1px solid #86efac">
                        <p class="text-xs font-medium" style="color:#16a34a">إيرادات</p>
                        <p class="text-sm font-extrabold text-primary-800">{{ number_format($todayTransactionsRevenue, 0) }}</p>
                    </div>
                    <div class="rounded-xl px-3 py-2 text-right" style="background:#fef2f2;border:1px solid #fca5a5">
                        <p class="text-xs font-medium" style="color:#dc2626">مصروفات</p>
                        <p class="text-sm font-extrabold" style="color:#dc2626">{{ number_format($todayTransactionsExpense, 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="flex-1 divide-y divide-gray-50 overflow-y-auto">
                @forelse($todayTransactions as $tx)
                <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors">
                    <div class="flex flex-col items-end gap-0.5 text-right flex-1 min-w-0">
                        <span class="text-xs font-bold {{ $tx->type === 'revenue' ? 'text-emerald-600' : 'text-red-500' }}">{{ $tx->type === 'revenue' ? '+' : '-' }}{{ number_format($tx->amount, 0) }} ج.م</span>
                        <span class="text-xs text-gray-400 truncate max-w-full">{{ $tx->account?->name ?? '—' }}</span>
                    </div>
                    <div class="w-2 h-2 rounded-full flex-shrink-0 mr-3 {{ $tx->type === 'revenue' ? 'bg-emerald-500' : 'bg-red-400' }}"></div>
                </div>
                @empty
                <div class="flex-1 flex flex-col items-center justify-center py-10 gap-2">
                    <div class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center"><svg class="w-5 h-5 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg></div>
                    <p class="text-xs text-gray-300">لا توجد معاملات اليوم</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ROW 4 - TOP SELLING + LOW STOCK --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-4">

        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-stone-50 flex items-center justify-between px-5 py-4 border-b border-stone-100">
                <a href="{{ route('products.index') }}" class="text-xs text-primary-700 hover:text-primary-800 font-semibold bg-stone-100 px-2.5 py-1 rounded-lg hover:bg-stone-200 transition-colors">عرض الكل</a>
                <div class="flex items-center gap-2.5">
                    <h3 class="text-sm font-bold text-gray-800">المنتجات الأكثر مبيعاً</h3>
                    <div class="bg-stone-200 p-1.5 rounded-lg"><svg class="w-4 h-4 text-primary-800" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg></div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead><tr class="bg-primary-700"><th class="px-4 py-3 text-xs font-bold text-white w-10">#</th><th class="px-4 py-3 text-xs font-bold text-white">المنتج</th><th class="px-4 py-3 text-xs font-bold text-white">الكمية</th><th class="px-4 py-3 text-xs font-bold text-white">الإيراد</th></tr></thead>
                    <tbody>
                        @forelse($topSellingProducts as $i => $item)
                        @php $maxQty = $topSellingProducts->first()?->total_qty ?? 1; @endphp
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/40' }} hover:bg-stone-50/40 transition-colors border-b border-gray-50">
                            <td class="px-4 py-3.5">
                                @if($i===0)<span class="inline-flex w-7 h-7 rounded-full bg-primary-800 text-white text-xs font-extrabold items-center justify-center shadow-sm">1</span>
                                @elseif($i===1)<span class="inline-flex w-7 h-7 rounded-full bg-stone-500 text-white text-xs font-extrabold items-center justify-center shadow-sm">2</span>
                                @elseif($i===2)<span class="inline-flex w-7 h-7 rounded-full bg-stone-700 text-white text-xs font-extrabold items-center justify-center shadow-sm">3</span>
                                @else<span class="inline-flex w-7 h-7 rounded-full bg-gray-100 text-gray-500 text-xs font-bold items-center justify-center">{{ $i+1 }}</span>@endif
                            </td>
                            <td class="px-4 py-3.5"><p class="text-sm font-bold text-gray-800">{{ $item->product?->name ?? '—' }}</p>@if($item->product?->category?->name)<p class="text-xs text-gray-400 mt-0.5">{{ $item->product->category->name }}</p>@endif</td>
                            <td class="px-4 py-3.5"><div class="flex items-center gap-2 justify-end"><span class="font-bold text-gray-700">{{ number_format($item->total_qty, 0) }}</span><div class="w-20 bg-gray-100 rounded-full h-2 overflow-hidden"><div class="bg-primary-700 h-2 rounded-full" style="width:{{ min(100,($item->total_qty/$maxQty)*100) }}%"></div></div></div></td>
                            <td class="px-4 py-3.5"><span class="font-bold text-primary-700">{{ number_format($item->total_revenue, 0) }}</span><span class="text-xs text-gray-400 mr-0.5">ج.م</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-gray-300">لا توجد بيانات مبيعات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-stone-50 flex items-center justify-between px-5 py-4 border-b border-stone-100">
                <a href="{{ route('products.index') }}" class="text-xs text-primary-700 hover:text-primary-800 font-semibold bg-stone-100 px-2.5 py-1 rounded-lg hover:bg-stone-200 transition-colors">إدارة المخزون</a>
                <div class="flex items-center gap-2.5">
                    <h3 class="text-sm font-bold text-gray-800">تنبيهات المخزون</h3>
                    @if($lowStockCount > 0)<span class="inline-flex items-center gap-1 bg-stone-200 text-stone-700 text-xs font-bold px-2.5 py-1 rounded-full"><span class="w-1.5 h-1.5 bg-primary-700 rounded-full animate-pulse"></span>{{ $lowStockCount }}</span>
                    @else<div class="bg-stone-200 p-1.5 rounded-lg"><svg class="w-4 h-4 text-stone-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div>@endif
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($lowStockProducts as $product)
                <div class="flex items-center gap-3 px-4 py-3 {{ $product->total_qty == 0 ? 'hover:bg-stone-50/40' : 'hover:bg-stone-50/30' }} transition-colors">
                    @if($product->total_qty == 0)
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-stone-200 flex items-center justify-center"><span class="text-xs font-extrabold text-stone-700">0</span></div>
                    @else
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-stone-100 flex items-center justify-center"><span class="text-xs font-extrabold text-stone-700">{{ $product->total_qty }}</span></div>
                    @endif
                    <div class="flex-1 min-w-0 text-right">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $product->name }}</p>
                        <p class="text-xs text-gray-400">{{ number_format($product->selling_price, 0) }} ج.م</p>
                    </div>
                    @if($product->total_qty == 0)
                        <span class="flex-shrink-0 text-xs font-bold text-white px-2 py-0.5 rounded-lg" style="background:#D9534F">نفد</span>
                    @else
                        <span class="flex-shrink-0 text-xs font-bold text-white px-2 py-0.5 rounded-lg" style="background:#F4B400">منخفض</span>
                    @endif
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-12 gap-3">
                    <div class="w-14 h-14 bg-stone-100 rounded-full flex items-center justify-center"><svg class="w-7 h-7 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div>
                    <p class="text-sm text-gray-400 font-medium">المخزون كافٍ</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ROW 4.5 - REVENUE/EXPENSE CHART + SUPPLIER ALERTS --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-4">

        {{-- Revenue vs Expense Line Chart --}}
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-stone-50 flex items-center justify-between px-5 py-4 border-b border-stone-100">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full inline-block" style="background:#4CAF50"></span>
                        <span class="text-xs text-gray-500">إيرادات</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full inline-block" style="background:#D9534F"></span>
                        <span class="text-xs text-gray-500">مصروفات</span>
                    </div>
                </div>
                <div class="flex items-center gap-2.5">
                    <h3 class="text-sm font-bold text-gray-800">الإيرادات والمصروفات</h3>
                    <div class="bg-stone-200 p-1.5 rounded-lg">
                        <svg class="w-4 h-4 text-primary-800" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 border-b border-gray-50">
                <div class="px-5 py-3 text-right border-l border-gray-50">
                    <p class="text-xs text-gray-400 mb-0.5">إجمالي الإيرادات (6 أشهر)</p>
                    <p class="text-lg font-extrabold text-green-600">{{ number_format(array_sum($chartRevenue), 0) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
                </div>
                <div class="px-5 py-3 text-right">
                    <p class="text-xs text-gray-400 mb-0.5">إجمالي المصروفات (6 أشهر)</p>
                    <p class="text-lg font-extrabold text-red-500">{{ number_format(array_sum($chartExpense), 0) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
                </div>
            </div>
            <div class="p-4" style="height:220px;position:relative;">
                <canvas id="revenueExpenseChart" style="position:absolute;inset:16px;width:calc(100% - 32px)!important;height:calc(100% - 32px)!important;"></canvas>
            </div>
        </div>

        {{-- Supplier Payment Alerts --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="bg-stone-50 flex items-center justify-between px-5 py-4 border-b border-stone-100">
                <div class="flex items-center gap-2">
                    @if($overdueSupplierCount > 0)
                    <span class="inline-flex items-center gap-1 bg-red-100 text-red-600 text-xs font-bold px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
                        {{ $overdueSupplierCount }} متأخر
                    </span>
                    @endif
                    <a href="{{ route('purchase-invoices.index') }}" class="text-xs text-primary-700 font-semibold bg-stone-100 px-2.5 py-1 rounded-lg hover:bg-stone-200 transition-colors">عرض الكل</a>
                </div>
                <div class="flex items-center gap-2.5">
                    <h3 class="text-sm font-bold text-gray-800">مستحقات الموردين</h3>
                    <div class="bg-stone-200 p-1.5 rounded-lg">
                        <svg class="w-4 h-4 text-primary-800" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                    </div>
                </div>
            </div>
            <div class="flex-1 divide-y divide-gray-50 overflow-y-auto" style="max-height:300px">
                @forelse($supplierAlerts as $inv)
                @php
                    $remaining = (float)$inv->total - (float)$inv->paid_amount;
                    $isOverdue = $inv->due_date && $inv->due_date->lt(today());
                    $daysLeft = $inv->due_date ? today()->diffInDays($inv->due_date, false) : null;
                @endphp
                <div class="flex items-center gap-3 px-4 py-3 {{ $isOverdue ? 'bg-red-50/50' : 'hover:bg-gray-50' }} transition-colors">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl {{ $isOverdue ? 'bg-red-100' : 'bg-amber-50' }} flex items-center justify-center">
                        <svg class="w-4 h-4 {{ $isOverdue ? 'text-red-500' : 'text-amber-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    </div>
                    <div class="flex-1 text-right min-w-0">
                        <p class="text-xs font-bold text-gray-800 truncate">{{ $inv->supplier?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $inv->invoice_number }}</p>
                        @if($inv->due_date)
                        <p class="text-xs {{ $isOverdue ? 'text-red-500 font-bold' : 'text-gray-400' }}">
                            {{ $isOverdue ? 'متأخر ' . abs($daysLeft) . ' يوم' : 'يستحق خلال ' . $daysLeft . ' يوم' }}
                        </p>
                        @else
                        <p class="text-xs text-gray-300">بدون تاريخ استحقاق</p>
                        @endif
                    </div>
                    <div class="text-left flex-shrink-0">
                        <p class="text-sm font-extrabold {{ $isOverdue ? 'text-red-600' : 'text-amber-700' }}">{{ number_format($remaining, 0) }}</p>
                        <p class="text-xs text-gray-400">ج.م</p>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-12 gap-3">
                    <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <p class="text-sm text-gray-400 font-medium">لا توجد مستحقات متأخرة</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ROW 4.6 - TOP CUSTOMERS + AT-RISK CUSTOMERS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

        {{-- Top Customers --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-stone-50 flex items-center justify-between px-5 py-4 border-b border-stone-100">
                <a href="{{ route('customers.index') }}" class="text-xs text-primary-700 hover:text-primary-800 font-semibold bg-stone-100 px-2.5 py-1 rounded-lg hover:bg-stone-200 transition-colors">عرض الكل</a>
                <div class="flex items-center gap-2.5">
                    <h3 class="text-sm font-bold text-gray-800">أفضل العملاء</h3>
                    <div class="bg-stone-200 p-1.5 rounded-lg">
                        <svg class="w-4 h-4 text-primary-800" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead>
                        <tr class="bg-primary-700">
                            <th class="px-4 py-3 text-xs font-bold text-white w-8">#</th>
                            <th class="px-4 py-3 text-xs font-bold text-white">العميل</th>
                            <th class="px-4 py-3 text-xs font-bold text-white">الطلبات</th>
                            <th class="px-4 py-3 text-xs font-bold text-white">الإجمالي</th>
                            <th class="px-4 py-3 text-xs font-bold text-white">المتبقي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topCustomers as $i => $customer)
                        @php $remaining = (float)$customer->total_sales - (float)$customer->total_paid; @endphp
                        <tr class="{{ $i%2===0?'bg-white':'bg-gray-50/40' }} hover:bg-stone-50/40 transition-colors border-b border-gray-50">
                            <td class="px-4 py-3">
                                @if($i===0)<span class="inline-flex w-6 h-6 rounded-full text-xs font-extrabold items-center justify-center text-white" style="background:#C9A66B">1</span>
                                @elseif($i===1)<span class="inline-flex w-6 h-6 rounded-full text-xs font-extrabold items-center justify-center text-white" style="background:#8D6E63">2</span>
                                @elseif($i===2)<span class="inline-flex w-6 h-6 rounded-full text-xs font-extrabold items-center justify-center text-white" style="background:#6D4C41">3</span>
                                @else<span class="text-xs text-gray-400 font-medium">{{ $i+1 }}</span>@endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-bold text-gray-800">{{ $customer->name }}</p>
                                <p class="text-xs text-gray-400">{{ $customer->phone }}</p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-stone-100 text-primary-700 text-xs font-extrabold">{{ $customer->orders_count }}</span>
                            </td>
                            <td class="px-4 py-3 font-bold text-primary-700">{{ number_format($customer->total_sales, 0) }}<span class="text-xs text-gray-400 font-normal mr-0.5">ج.م</span></td>
                            <td class="px-4 py-3">
                                @if($remaining > 0)
                                <span class="font-bold text-amber-600">{{ number_format($remaining, 0) }}<span class="text-xs text-gray-400 font-normal mr-0.5">ج.م</span></span>
                                @else
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">مسدّد</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-300">لا توجد بيانات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- At-Risk Customers --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-stone-50 flex items-center justify-between px-5 py-4 border-b border-stone-100">
                <div class="flex items-center gap-2">
                    @if($atRiskCustomers->count() > 0)
                    <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                        {{ $atRiskCustomers->count() }} عميل
                    </span>
                    @endif
                    <a href="{{ route('customers.index') }}" class="text-xs text-primary-700 font-semibold bg-stone-100 px-2.5 py-1 rounded-lg hover:bg-stone-200 transition-colors">عرض الكل</a>
                </div>
                <div class="flex items-center gap-2.5">
                    <h3 class="text-sm font-bold text-gray-800">عملاء في خطر</h3>
                    <div class="bg-amber-100 p-1.5 rounded-lg">
                        <svg class="w-4 h-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead>
                        <tr class="bg-amber-700">
                            <th class="px-4 py-3 text-xs font-bold text-white">العميل</th>
                            <th class="px-4 py-3 text-xs font-bold text-white">الطلبات</th>
                            <th class="px-4 py-3 text-xs font-bold text-white">المستحق</th>
                            <th class="px-4 py-3 text-xs font-bold text-white">نسبة السداد</th>
                            <th class="px-4 py-3 text-xs font-bold text-white">الخطر</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($atRiskCustomers as $i => $customer)
                        @php
                            $payRate = $customer->total_sales > 0 ? (($customer->total_paid / $customer->total_sales) * 100) : 0;
                            $riskLevel = $payRate < 25 ? ['عالي', 'bg-red-100 text-red-600'] : ($payRate < 60 ? ['متوسط', 'bg-amber-100 text-amber-700'] : ['منخفض', 'bg-yellow-50 text-yellow-700']);
                            $creditUtil = $customer->credit_limit > 0 ? min(100, ($customer->outstanding / $customer->credit_limit) * 100) : 0;
                        @endphp
                        <tr class="{{ $i%2===0?'bg-white':'bg-amber-50/20' }} hover:bg-amber-50/30 transition-colors border-b border-gray-50">
                            <td class="px-4 py-3">
                                <p class="text-sm font-bold text-gray-800">{{ $customer->name }}</p>
                                <p class="text-xs text-gray-400">{{ $customer->phone }}</p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-amber-50 text-amber-700 text-xs font-extrabold">{{ $customer->orders_count }}</span>
                            </td>
                            <td class="px-4 py-3 font-extrabold text-red-600">{{ number_format($customer->outstanding, 0) }}<span class="text-xs text-gray-400 font-normal mr-0.5">ج.م</span></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5 justify-end">
                                    <span class="text-xs font-bold text-gray-600">{{ number_format($payRate, 0) }}%</span>
                                    <div class="w-16 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $payRate < 25 ? 'bg-red-500' : ($payRate < 60 ? 'bg-amber-500' : 'bg-yellow-400') }}" style="width:{{ $payRate }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold {{ $riskLevel[1] }}">{{ $riskLevel[0] }}</span>
                            </td>
                        </tr>
                        @empty
                        <div class="flex flex-col items-center justify-center py-10 gap-3 text-center">
                        </div>
                        <tr><td colspan="5" class="px-5 py-10 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center mx-auto">
                                    <svg class="w-6 h-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                </div>
                                <p class="text-sm text-gray-400">لا يوجد عملاء في خطر</p>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ROW 5 - ACCOUNTS + TREASURIES + CHART --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-4">

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="bg-stone-50 flex items-center justify-between px-5 py-4 border-b border-stone-100">
                <a href="{{ route('accounts.index') }}" class="text-xs text-primary-700 hover:text-primary-800 font-semibold bg-stone-100 px-2.5 py-1 rounded-lg hover:bg-stone-200 transition-colors">عرض الكل</a>
                <div class="flex items-center gap-2.5">
                    <h3 class="text-sm font-bold text-gray-800">الحسابات</h3>
                    <div class="bg-stone-200 p-1.5 rounded-lg"><svg class="w-4 h-4 text-primary-800" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" /></svg></div>
                </div>
            </div>
            <div class="grid grid-cols-2">
                <div class="px-4 py-3.5 bg-stone-50 border-l border-stone-200 text-right">
                    <p class="text-xs text-stone-500 font-medium mb-1">إجمالي الإيرادات</p>
                    <p class="text-lg font-extrabold text-primary-800">{{ number_format($accountsRevenue, 0) }}<span class="text-xs font-normal text-stone-400 mr-1">ج.م</span></p>
                </div>
                <div class="px-4 py-3.5 bg-stone-100 text-right">
                    <p class="text-xs text-stone-500 font-medium mb-1">إجمالي المصروفات</p>
                    <p class="text-lg font-extrabold text-stone-700">{{ number_format($accountsExpense, 0) }}<span class="text-xs font-normal text-stone-400 mr-1">ج.م</span></p>
                </div>
            </div>
            <div class="divide-y divide-gray-50 overflow-y-auto flex-1" style="max-height:220px">
                @forelse($accounts as $i => $account)
                @php $colors=['bg-stone-100 border-stone-200','bg-stone-200 border-stone-300','bg-primary-50 border-primary-200','bg-stone-100 border-stone-200','bg-stone-200 border-stone-300','bg-primary-50 border-primary-200']; $c=$colors[$i%6]; @endphp
                <div class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50/60 transition-colors">
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $account->financial_transactions_count }}</span>
                    <div class="flex-1 text-right">
                        <p class="text-sm font-semibold text-gray-800">{{ $account->name }}</p>
                        @if($account->account_number)<p class="text-xs text-gray-400 font-mono">{{ $account->account_number }}</p>@endif
                    </div>
                    <div class="flex-shrink-0 w-1.5 h-8 rounded-full {{ str_replace('bg-','bg-',$c) }}" style="background: var(--tw-border-opacity)"></div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-xs text-gray-300">لا توجد حسابات</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="bg-stone-50 flex items-center justify-between px-4 py-4 border-b border-stone-100">
                <a href="{{ route('treasury-transactions.index') }}" class="text-xs text-primary-700 font-semibold">معاملات</a>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-gray-800">الخزائن</h3>
                    <div class="bg-stone-200 p-1.5 rounded-lg"><svg class="w-4 h-4 text-primary-800" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg></div>
                </div>
            </div>
            @php $tc=['bg-primary-800','bg-primary-700','bg-primary-600','bg-primary-500']; @endphp
            <div class="flex-1 p-3 flex flex-col gap-2 overflow-y-auto">
                @forelse($treasuries as $ti => $treasury)
                <div class="{{ $tc[$ti % count($tc)] }} rounded-xl px-4 py-3 text-right shadow-sm">
                    <p class="text-xs text-white/80 font-medium mb-0.5">{{ $treasury->name }}</p>
                    <p class="text-xl font-extrabold text-white">{{ number_format($treasury->balance, 0) }}<span class="text-xs font-normal text-white/70 mr-1">ج.م</span></p>
                </div>
                @empty
                <div class="flex-1 flex items-center justify-center"><p class="text-xs text-gray-300">لا توجد خزائن</p></div>
                @endforelse
            </div>
            <div class="px-4 py-3 bg-primary-900 text-right">
                <p class="text-xs text-primary-200 font-medium">الإجمالي</p>
                <p class="text-base font-extrabold text-white">{{ number_format($totalTreasuryBalance, 0) }} <span class="text-xs font-normal text-primary-200">ج.م</span></p>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-stone-50 flex items-center justify-between px-5 py-4 border-b border-stone-100">
                <span class="text-xs text-gray-400 bg-white border border-gray-100 rounded-xl px-3 py-1">آخر 6 أشهر</span>
                <h3 class="text-sm font-bold text-gray-800">المبيعات والمشتريات</h3>
            </div>
            <div class="p-4" style="height:260px;position:relative;">
                <canvas id="salesPurchasesChart" style="position:absolute;inset:16px;width:calc(100% - 32px)!important;height:calc(100% - 32px)!important;"></canvas>
            </div>
        </div>

    </div>

    {{-- ROW 6 - DELEGATES + SUMMARY --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-4">

        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-stone-50 flex items-center justify-between px-5 py-4 border-b border-stone-100">
                <a href="{{ route('delegates.index') }}" class="text-xs text-primary-700 hover:text-primary-800 font-semibold bg-stone-100 px-2.5 py-1 rounded-lg hover:bg-stone-200 transition-colors">عرض الكل</a>
                <div class="flex items-center gap-2.5">
                    <h3 class="text-sm font-bold text-gray-800">أداء المناديب</h3>
                    <div class="bg-stone-200 p-1.5 rounded-lg"><svg class="w-4 h-4 text-primary-800" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg></div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead><tr class="bg-primary-700"><th class="px-5 py-3 text-xs font-bold text-white">المندوب</th><th class="px-4 py-3 text-xs font-bold text-white">المستحق</th><th class="px-4 py-3 text-xs font-bold text-white">المحصّل</th><th class="px-4 py-3 text-xs font-bold text-white">العهدة</th><th class="px-4 py-3 text-xs font-bold text-white">الحالة</th></tr></thead>
                    <tbody>
                        @forelse($delegatesPerformance as $i => $delegate)
                        @php $ac=['bg-stone-200 text-stone-700','bg-stone-300 text-stone-800','bg-primary-100 text-primary-800','bg-stone-200 text-stone-700','bg-stone-300 text-stone-800','bg-primary-100 text-primary-800'][$i%6]; @endphp
                        <tr class="{{ $i%2===0?'bg-white':'bg-gray-50/40' }} hover:bg-primary-50/30 transition-colors border-b border-gray-50">
                            <td class="px-5 py-3.5"><div class="flex items-center gap-2.5 justify-end"><span class="font-bold text-gray-800">{{ $delegate->name }}</span><span class="w-9 h-9 rounded-xl {{ $ac }} flex items-center justify-center text-sm font-extrabold flex-shrink-0 shadow-sm">{{ mb_substr($delegate->name, 0, 1) }}</span></div></td>
                            <td class="px-4 py-3.5 text-gray-600 font-semibold">{{ number_format($delegate->total_due, 0) }}<span class="text-xs text-gray-400 mr-0.5">ج.م</span></td>
                            <td class="px-4 py-3.5 font-bold text-primary-700">{{ number_format($delegate->total_collected, 0) }}<span class="text-xs text-gray-400 mr-0.5">ج.م</span></td>
                            <td class="px-4 py-3.5 font-bold text-stone-600">{{ number_format($delegate->cash_custody ?? 0, 0) }}<span class="text-xs text-gray-400 mr-0.5">ج.م</span></td>
                            <td class="px-4 py-3.5">@if($delegate->is_active)<span class="inline-flex items-center gap-1 bg-stone-200 text-stone-700 text-xs font-bold px-2.5 py-1 rounded-full"><span class="w-1.5 h-1.5 bg-primary-700 rounded-full"></span>نشط</span>@else<span class="inline-flex items-center gap-1 bg-stone-300 text-stone-700 text-xs font-bold px-2.5 py-1 rounded-full"><span class="w-1.5 h-1.5 bg-stone-500 rounded-full"></span>متابعة</span>@endif</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-gray-300 text-sm">لا يوجد مناديب</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="lg:col-span-2 rounded-2xl overflow-hidden shadow-lg flex flex-col" style="background:#3E2723;">
            <div class="px-5 pt-5 pb-4 border-b border-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-white/40 font-medium">ملخص شامل</span>
                    <h3 class="text-sm font-extrabold text-white">ملخص التشغيل</h3>
                </div>
            </div>
            <div class="flex-1 px-5 py-4">
                <ul class="space-y-2.5 text-sm">
                    <li class="flex items-center justify-between"><span class="text-white font-bold text-lg">{{ $confirmedSaleOrdersCount }}</span><span class="text-white/60 text-xs">أوامر مبيعات مؤكدة</span></li>
                    <li class="flex items-center justify-between"><span class="text-primary-200 font-bold">{{ number_format($saleOrdersPaid, 0) }} ج.م</span><span class="text-white/60 text-xs">إجمالي المحصّل</span></li>
                    <li class="h-px bg-white/10 my-1"></li>
                    <li class="flex items-center justify-between"><span class="text-white font-bold">{{ $purchaseCount }}</span><span class="text-white/60 text-xs">فواتير المشتريات</span></li>
                    <li class="flex items-center justify-between"><span class="text-stone-300 font-bold">{{ number_format($purchasePaid, 0) }} ج.م</span><span class="text-white/60 text-xs">مدفوع للموردين</span></li>
                    <li class="h-px bg-white/10 my-1"></li>
                    <li class="flex items-center justify-between"><span class="text-primary-200 font-bold">{{ number_format($accountsRevenue, 0) }} ج.م</span><span class="text-white/60 text-xs">إجمالي الإيرادات</span></li>
                    <li class="flex items-center justify-between"><span class="text-stone-300 font-bold">{{ number_format($accountsExpense, 0) }} ج.م</span><span class="text-white/60 text-xs">إجمالي المصروفات</span></li>
                    <li class="h-px bg-white/10 my-1"></li>
                    <li class="flex items-center justify-between"><span class="text-primary-200 font-bold text-base">{{ number_format($totalTreasuryBalance, 0) }} ج.م</span><span class="text-white/60 text-xs">رصيد الخزائن</span></li>
                    <li class="flex items-center justify-between"><span class="{{ $netProfit >= 0 ? 'text-primary-200' : 'text-stone-300' }} font-extrabold text-lg">{{ number_format($netProfit, 0) }} ج.م</span><span class="text-white/60 text-xs">صافي الربح التقديري</span></li>
                </ul>
            </div>
            <div class="px-4 pb-5 grid grid-cols-2 gap-2">
                <a href="{{ route('sale-orders.index') }}" class="bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-3 py-2 rounded-xl text-center transition-colors border border-white/10">طلبات المبيعات</a>
                <a href="{{ route('purchase-invoices.index') }}" class="bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-3 py-2 rounded-xl text-center transition-colors border border-white/10">المشتريات</a>
                <a href="{{ route('financial-transactions.index') }}" class="bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-3 py-2 rounded-xl text-center transition-colors border border-white/10">معاملات مالية</a>
                <a href="{{ route('accounts.index') }}" class="bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-3 py-2 rounded-xl text-center transition-colors border border-white/10">الحسابات</a>
                <a href="{{ route('delegates.index') }}" class="bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-3 py-2 rounded-xl text-center transition-colors border border-white/10">المناديب</a>
                <a href="{{ route('products.index') }}" class="bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-3 py-2 rounded-xl text-center transition-colors border border-white/10">المنتجات</a>
            </div>
        </div>

    </div>

    {{-- ROW 7 - DONUT + ENTITY CARDS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-stone-50 px-5 py-4 border-b border-stone-100">
                <h4 class="text-sm font-bold text-gray-800 text-right">حالة طلبات المبيعات</h4>
            </div>
            <div class="p-4" style="height:220px;position:relative;">
                <canvas id="saleStatusChart" style="position:absolute;inset:16px;width:calc(100% - 32px)!important;height:calc(100% - 32px)!important;"></canvas>
            </div>
        </div>

        <div class="lg:col-span-2">
            @php
                $entityCards = [
                    ['العملاء',   $customersCount,  'bg-primary-800',  'customers.index',  'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
                    ['الموردون',  $suppliersCount,  'bg-primary-700',  'suppliers.index',  'M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z'],
                    ['المناديب',  $delegatesCount,  'bg-primary-600',  'delegates.index',  'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
                    ['المدراء',   $adminsCount,     'bg-primary-900',  'admins.index',     'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
                    ['التصنيفات', $categoriesCount, 'bg-primary-600', 'categories.index','M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3ZM6 6h.008v.008H6V6Z'],
                    ['المركبات',  $vehiclesCount,   'bg-primary-500',  'vehicles.index',   'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
                ];
            @endphp
            <div class="grid grid-cols-3 gap-3 h-full">
                @foreach($entityCards as [$label, $count, $gradient, $route, $icon])
                <a href="{{ route($route) }}" class="relative overflow-hidden {{ $gradient }} rounded-2xl p-4 text-center hover:shadow-lg hover:-translate-y-1 transition-all block shadow-md">
                    <div class="absolute top-0 left-0 w-20 h-20 rounded-full bg-white/10 -translate-x-1/2 -translate-y-1/2"></div>
                    <div class="relative">
                        <svg class="w-6 h-6 text-white/70 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                        <p class="text-2xl font-extrabold text-white mb-0.5">{{ $count }}</p>
                        <p class="text-xs text-white/80 font-medium">{{ $label }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        (function(){
            const ctx = document.getElementById('revenueExpenseChart');
            if(!ctx) return;
            new Chart(ctx,{
                type:'line',
                data:{
                    labels: @json($chartLabels),
                    datasets:[
                        {
                            label:'الإيرادات',
                            data: @json($chartRevenue),
                            borderColor:'#4CAF50',
                            backgroundColor:'rgba(76,175,80,0.08)',
                            tension:0.4,
                            fill:true,
                            pointBackgroundColor:'#4CAF50',
                            pointRadius:4,
                            pointHoverRadius:6,
                            borderWidth:2
                        },
                        {
                            label:'المصروفات',
                            data: @json($chartExpense),
                            borderColor:'#D9534F',
                            backgroundColor:'rgba(217,83,79,0.06)',
                            tension:0.4,
                            fill:true,
                            pointBackgroundColor:'#D9534F',
                            pointRadius:4,
                            pointHoverRadius:6,
                            borderWidth:2
                        }
                    ]
                },
                options:{
                    responsive:false,
                    plugins:{legend:{position:'bottom',labels:{font:{size:11,family:'Tajawal'},padding:12,usePointStyle:true,pointStyleWidth:8}}},
                    scales:{
                        x:{grid:{display:false},ticks:{font:{size:10,family:'Tajawal'}}},
                        y:{beginAtZero:true,grid:{color:'rgba(0,0,0,0.04)'},ticks:{font:{size:10,family:'Tajawal'},maxTicksLimit:5}}
                    }
                }
            });
        })();
        (function(){
            const ctx = document.getElementById('salesPurchasesChart');
            if(!ctx) return;
            new Chart(ctx,{
                type:'bar',
                data:{
                    labels: @json($chartLabels),
                    datasets:[
                        {label:'المبيعات',data:@json($chartSales),backgroundColor:'rgba(78,52,46,0.88)',borderRadius:6,borderSkipped:false},
                        {label:'المشتريات',data:@json($chartPurchases),backgroundColor:'rgba(201,166,107,0.70)',borderRadius:6,borderSkipped:false}
                    ]
                },
                options:{
                    responsive:false,
                    plugins:{legend:{position:'bottom',labels:{font:{size:11,family:'Tajawal'},padding:12,usePointStyle:true,pointStyleWidth:8}}},
                    scales:{
                        x:{grid:{display:false},ticks:{font:{size:10,family:'Tajawal'}}},
                        y:{beginAtZero:true,grid:{color:'rgba(0,0,0,0.04)'},ticks:{font:{size:10,family:'Tajawal'},maxTicksLimit:5}}
                    }
                }
            });
        })();
        (function(){
            const ctx = document.getElementById('saleStatusChart');
            if(!ctx) return;
            const d = @json($saleStatusCounts);
            const lm = {draft:'مسودة',confirmed:'مؤكد',partial_paid:'جزئي',paid:'مدفوع',cancelled:'ملغي'};
            const cm = {draft:'#E6C58A',confirmed:'#4E342E',partial_paid:'#F4B400',paid:'#4CAF50',cancelled:'#D9534F'};
            const keys = Object.keys(d);
            new Chart(ctx,{
                type:'doughnut',
                data:{
                    labels:keys.map(k=>lm[k]||k),
                    datasets:[{data:Object.values(d),backgroundColor:keys.map(k=>cm[k]||'#e2e8f0'),borderWidth:3,borderColor:'#fff',hoverOffset:6}]
                },
                options:{
                    responsive:false,
                    cutout:'70%',
                    plugins:{legend:{position:'bottom',labels:{font:{size:10,family:'Tajawal'},padding:10,usePointStyle:true,pointStyleWidth:8}}}
                }
            });
        })();
    </script>
    @endpush
</div>