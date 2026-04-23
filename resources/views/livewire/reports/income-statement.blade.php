<div dir="rtl" class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700">قائمة الدخل</h1>
            <p class="text-sm text-gray-400 mt-0.5">Income Statement</p>
        </div>
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-primary-700 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
            التقارير
        </a>
    </div>

    {{-- Period Selector --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="flex flex-wrap items-center gap-3">
            @foreach(['month' => 'هذا الشهر', 'quarter' => 'هذا الربع', 'year' => 'هذه السنة', 'custom' => 'مخصص'] as $key => $label)
            <button wire:click="applyPeriod('{{ $key }}')"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ $period === $key ? 'bg-primary-700 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>
        <div class="flex gap-4 mt-3">
            <div class="flex-1">
                <label class="block text-xs text-gray-500 mb-1">من</label>
                <input type="date" wire:model.live="dateFrom" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300">
            </div>
            <div class="flex-1">
                <label class="block text-xs text-gray-500 mb-1">إلى</label>
                <input type="date" wire:model.live="dateTo" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300">
            </div>
        </div>
    </div>

    {{-- Summary KPIs --}}
    @php
        $netColor = $netProfit >= 0 ? 'text-green-600' : 'text-red-600';
        $gpColor  = $grossProfit >= 0 ? 'text-green-600' : 'text-red-600';
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">إجمالي الإيرادات</p>
            <p class="text-xl font-extrabold text-primary-700">{{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">مجمل الربح</p>
            <p class="text-xl font-extrabold {{ $gpColor }}">{{ number_format($grossProfit, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">إجمالي المصروفات</p>
            <p class="text-xl font-extrabold text-red-500">{{ number_format($totalExpenses, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">صافي الربح / الخسارة</p>
            <p class="text-xl font-extrabold {{ $netColor }}">{{ number_format($netProfit, 2) }}</p>
            <p class="text-xs {{ $netColor }} mt-0.5">{{ $netProfit >= 0 ? '▲ ربح' : '▼ خسارة' }}</p>
        </div>
    </div>

    {{-- Main Income Statement Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-sm font-bold text-gray-700">قائمة الدخل التفصيلية</h2>
        </div>
        <div class="p-5 space-y-1">

            {{-- REVENUES SECTION --}}
            <div class="flex justify-between items-center py-2 border-b-2 border-primary-700">
                <span class="font-extrabold text-primary-700 text-sm">الإيرادات</span>
                <span class="font-extrabold text-primary-700 text-sm">المبلغ</span>
            </div>

            <div class="flex justify-between items-center py-2 pr-4 text-sm text-gray-700">
                <span>إيرادات المبيعات</span>
                <span class="font-semibold">{{ number_format($salesRevenue, 2) }}</span>
            </div>
            @if($salesReturns > 0)
            <div class="flex justify-between items-center py-2 pr-4 text-sm text-red-500">
                <span>مرتجعات المبيعات</span>
                <span class="font-semibold">({{ number_format($salesReturns, 2) }})</span>
            </div>
            @endif
            <div class="flex justify-between items-center py-2 pr-4 text-sm font-semibold border-b border-gray-100">
                <span>صافي إيرادات المبيعات</span>
                <span class="text-primary-700">{{ number_format($netSalesRevenue, 2) }}</span>
            </div>

            @foreach($otherRevenues as $rev)
            <div class="flex justify-between items-center py-2 pr-4 text-sm text-gray-700">
                <span>إيراد آخر — {{ $rev->account?->name ?? 'غير محدد' }}</span>
                <span class="font-semibold">{{ number_format($rev->total, 2) }}</span>
            </div>
            @endforeach

            <div class="flex justify-between items-center py-2.5 px-4 bg-green-50 rounded-xl mt-2 text-sm font-bold text-green-700">
                <span>إجمالي الإيرادات</span>
                <span>{{ number_format($totalRevenue, 2) }}</span>
            </div>

            {{-- COGS SECTION --}}
            <div class="flex justify-between items-center py-2 border-b-2 border-amber-600 mt-4">
                <span class="font-extrabold text-amber-700 text-sm">تكلفة البضاعة المباعة (COGS)</span>
                <span class="font-extrabold text-amber-700 text-sm">المبلغ</span>
            </div>

            <div class="flex justify-between items-center py-2 pr-4 text-sm text-gray-700">
                <span>فواتير المشتريات</span>
                <span class="font-semibold">{{ number_format($purchaseCost, 2) }}</span>
            </div>
            @if($purchaseReturns > 0)
            <div class="flex justify-between items-center py-2 pr-4 text-sm text-green-600">
                <span>مرتجعات المشتريات</span>
                <span class="font-semibold">({{ number_format($purchaseReturns, 2) }})</span>
            </div>
            @endif
            <div class="flex justify-between items-center py-2.5 px-4 bg-amber-50 rounded-xl mt-2 text-sm font-bold text-amber-700">
                <span>صافي التكلفة</span>
                <span>{{ number_format($netCogs, 2) }}</span>
            </div>

            {{-- GROSS PROFIT --}}
            <div class="flex justify-between items-center py-3 px-4 {{ $grossProfit >= 0 ? 'bg-green-50' : 'bg-red-50' }} rounded-xl mt-3 text-sm font-bold {{ $gpColor }}">
                <span>مجمل الربح</span>
                <span>{{ number_format($grossProfit, 2) }}</span>
            </div>

            {{-- EXPENSES SECTION --}}
            <div class="flex justify-between items-center py-2 border-b-2 border-red-500 mt-4">
                <span class="font-extrabold text-red-600 text-sm">المصروفات التشغيلية</span>
                <span class="font-extrabold text-red-600 text-sm">المبلغ</span>
            </div>

            @foreach($expenseLines as $exp)
            <div class="flex justify-between items-center py-2 pr-4 text-sm text-gray-700">
                <span>{{ $exp->account?->name ?? 'غير محدد' }}</span>
                <span class="font-semibold text-red-500">{{ number_format($exp->total, 2) }}</span>
            </div>
            @endforeach

            <div class="flex justify-between items-center py-2.5 px-4 bg-red-50 rounded-xl mt-2 text-sm font-bold text-red-600">
                <span>إجمالي المصروفات</span>
                <span>{{ number_format($totalExpenses, 2) }}</span>
            </div>

            {{-- NET PROFIT --}}
            <div class="flex justify-between items-center py-4 px-5 {{ $netProfit >= 0 ? 'bg-green-700' : 'bg-red-700' }} rounded-2xl mt-4 text-white font-extrabold text-base">
                <span>صافي الربح / الخسارة</span>
                <span>{{ number_format($netProfit, 2) }}</span>
            </div>

            @if($totalRevenue > 0)
            <div class="flex justify-between items-center py-2 pr-4 text-xs text-gray-400">
                <span>هامش الربح الصافي</span>
                <span>{{ round(($netProfit / $totalRevenue) * 100, 1) }}%</span>
            </div>
            @endif
        </div>
    </div>

</div>
