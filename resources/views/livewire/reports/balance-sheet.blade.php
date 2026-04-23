<div dir="rtl" class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700">الميزانية العمومية</h1>
            <p class="text-sm text-gray-400 mt-0.5">Balance Sheet — كما في {{ $asOf }}</p>
        </div>
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-primary-700 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
            التقارير
        </a>
    </div>

    {{-- Date Selector --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-4">
            <label class="text-xs text-gray-500 font-semibold flex-shrink-0">كما في تاريخ:</label>
            <input type="date" wire:model.live="asOfDate" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300">
        </div>
    </div>

    {{-- Balance Check --}}
    @php
        $balanced = abs($totalAssets - ($totalLiabilities + $equity)) < 1;
        $netIncomeColor = $netIncome >= 0 ? 'text-green-600' : 'text-red-600';
    @endphp

    {{-- Top Summary --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-blue-700 text-white rounded-2xl p-4 text-center">
            <p class="text-xs opacity-70 mb-1">إجمالي الأصول</p>
            <p class="text-2xl font-extrabold">{{ number_format($totalAssets, 2) }}</p>
        </div>
        <div class="bg-amber-600 text-white rounded-2xl p-4 text-center">
            <p class="text-xs opacity-70 mb-1">إجمالي الالتزامات</p>
            <p class="text-2xl font-extrabold">{{ number_format($totalLiabilities, 2) }}</p>
        </div>
        <div class="bg-primary-800 text-white rounded-2xl p-4 text-center">
            <p class="text-xs opacity-70 mb-1">حقوق الملكية</p>
            <p class="text-2xl font-extrabold">{{ number_format($equity, 2) }}</p>
        </div>
    </div>

    {{-- Main Balance Sheet --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- ASSETS COLUMN --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-blue-700 text-white">
                <h2 class="font-extrabold text-base">الأصول</h2>
                <p class="text-xs opacity-70">Assets</p>
            </div>
            <div class="p-5 space-y-1">

                {{-- Current Assets --}}
                <div class="text-xs font-bold text-blue-700 uppercase tracking-wider py-2 border-b border-blue-100">أصول متداولة</div>

                {{-- Cash --}}
                <div class="flex justify-between py-2 text-sm text-gray-700 pr-2">
                    <span>النقد والخزائن</span>
                    <span class="font-semibold">{{ number_format($totalCash, 2) }}</span>
                </div>
                @foreach($treasuries as $t)
                <div class="flex justify-between py-1 text-xs text-gray-400 pr-6">
                    <span>{{ $t->name }}</span>
                    <span>{{ number_format($t->balance, 2) }}</span>
                </div>
                @endforeach

                <div class="flex justify-between py-2 text-sm text-gray-700 pr-2">
                    <span>الذمم المدينة (فواتير غير مسددة)</span>
                    <span class="font-semibold">{{ number_format($receivables, 2) }}</span>
                </div>

                @if($installmentReceivables > 0)
                <div class="flex justify-between py-2 text-sm text-gray-700 pr-2">
                    <span>أقساط مستحقة من العملاء</span>
                    <span class="font-semibold">{{ number_format($installmentReceivables, 2) }}</span>
                </div>
                @endif

                <div class="flex justify-between py-2 text-sm text-gray-700 pr-2">
                    <span>قيمة المخزون</span>
                    <span class="font-semibold">{{ number_format($inventoryValue, 2) }}</span>
                </div>

                <div class="flex justify-between py-2.5 px-3 bg-blue-50 rounded-xl mt-2 text-sm font-bold text-blue-700">
                    <span>إجمالي الأصول المتداولة</span>
                    <span>{{ number_format($totalCurrentAssets, 2) }}</span>
                </div>

                {{-- Non-current --}}
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider py-2 border-b border-gray-100 mt-3">أصول ثابتة</div>
                <div class="flex justify-between py-2 text-sm text-gray-400 pr-2">
                    <span>أصول ثابتة</span>
                    <span>0.00</span>
                </div>

                <div class="flex justify-between py-3 px-4 bg-blue-700 text-white rounded-xl mt-3 font-extrabold text-sm">
                    <span>إجمالي الأصول</span>
                    <span>{{ number_format($totalAssets, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- LIABILITIES + EQUITY COLUMN --}}
        <div class="space-y-5">

            {{-- LIABILITIES --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-amber-600 text-white">
                    <h2 class="font-extrabold text-base">الالتزامات</h2>
                    <p class="text-xs opacity-70">Liabilities</p>
                </div>
                <div class="p-5 space-y-1">

                    <div class="text-xs font-bold text-amber-700 uppercase tracking-wider py-2 border-b border-amber-100">التزامات متداولة</div>

                    <div class="flex justify-between py-2 text-sm text-gray-700 pr-2">
                        <span>الذمم الدائنة (موردون)</span>
                        <span class="font-semibold">{{ number_format($payables, 2) }}</span>
                    </div>

                    @if($installmentPayables > 0)
                    <div class="flex justify-between py-2 text-sm text-gray-700 pr-2">
                        <span>أقساط مستحقة للموردين</span>
                        <span class="font-semibold">{{ number_format($installmentPayables, 2) }}</span>
                    </div>
                    @endif

                    @if($saleReturnsPending > 0)
                    <div class="flex justify-between py-2 text-sm text-gray-700 pr-2">
                        <span>مرتجعات بيع معلقة</span>
                        <span class="font-semibold">{{ number_format($saleReturnsPending, 2) }}</span>
                    </div>
                    @endif

                    <div class="flex justify-between py-3 px-4 bg-amber-600 text-white rounded-xl mt-3 font-extrabold text-sm">
                        <span>إجمالي الالتزامات</span>
                        <span>{{ number_format($totalLiabilities, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- EQUITY --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-primary-800 text-white">
                    <h2 class="font-extrabold text-base">حقوق الملكية</h2>
                    <p class="text-xs opacity-70">Equity</p>
                </div>
                <div class="p-5 space-y-1">

                    <div class="flex justify-between py-2 text-sm text-gray-700 pr-2">
                        <span>صافي الدخل المتراكم</span>
                        <span class="font-semibold {{ $netIncomeColor }}">{{ number_format($netIncome, 2) }}</span>
                    </div>

                    <div class="flex justify-between py-2.5 text-sm text-gray-600 pr-2 text-xs italic">
                        <span>حقوق الملكية = الأصول − الالتزامات</span>
                        <span></span>
                    </div>

                    <div class="flex justify-between py-3 px-4 bg-primary-800 text-white rounded-xl mt-2 font-extrabold text-sm">
                        <span>إجمالي حقوق الملكية</span>
                        <span>{{ number_format($equity, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Balance Check --}}
            <div class="flex items-center gap-2 px-4 py-3 {{ $balanced ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }} rounded-2xl text-sm font-semibold">
                @if($balanced)
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                الميزانية متوازنة — الأصول = الالتزامات + حقوق الملكية
                @else
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                ملاحظة: الأرصدة غير متوازنة تماماً — قد تحتاج مراجعة الأرصدة الافتتاحية
                @endif
            </div>
        </div>
    </div>

</div>
