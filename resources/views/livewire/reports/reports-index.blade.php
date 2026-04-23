<div>
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">التقارير المالية</h1>
            <p class="text-sm text-gray-500 mt-1">عرض ملخص الحالة المالية</p>
        </div>
        <div class="flex items-center gap-2">
            <x-button variant="secondary" size="sm" href="{{ route('reports.export.pdf', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 7.5H5.25" /></svg>
                طباعة PDF
            </x-button>
        </div>
    </div>

    {{-- Accounting Reports Quick Links --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6" dir="rtl">
        <a href="{{ route('reports.income-statement') }}"
           class="flex items-center gap-4 bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:border-primary-300 hover:shadow-md transition-all group">
            <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0 group-hover:bg-green-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
            </div>
            <div>
                <p class="font-bold text-gray-800 text-sm">قائمة الدخل</p>
                <p class="text-xs text-gray-400">الإيرادات والمصروفات وصافي الربح</p>
            </div>
        </a>
        <a href="{{ route('reports.account-statement') }}"
           class="flex items-center gap-4 bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:border-primary-300 hover:shadow-md transition-all group">
            <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z"/></svg>
            </div>
            <div>
                <p class="font-bold text-gray-800 text-sm">كشف الحساب</p>
                <p class="text-xs text-gray-400">حركات الحسابات المدين والدائن</p>
            </div>
        </a>
        <a href="{{ route('reports.balance-sheet') }}"
           class="flex items-center gap-4 bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:border-primary-300 hover:shadow-md transition-all group">
            <div class="w-11 h-11 rounded-xl bg-primary-100 flex items-center justify-center flex-shrink-0 group-hover:bg-primary-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0-3 9a5.002 5.002 0 0 0 6.001 0M6 7l3 9M6 7l6-2m6 2 3-1m-3 1-3 9a5.002 5.002 0 0 0 6.001 0M18 7l3 9m-3-9-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
            </div>
            <div>
                <p class="font-bold text-gray-800 text-sm">الميزانية العمومية</p>
                <p class="text-xs text-gray-400">الأصول والالتزامات وحقوق الملكية</p>
            </div>
        </a>
    </div>

    {{-- Date Filters --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                <input type="date" wire:model.live="dateFrom"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" wire:model.live="dateTo"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي أرصدة الخزن</p>
            <p class="text-xl font-bold text-primary-700" dir="ltr">{{ number_format($totalTreasuryBalance, 2) }}</p>
        </div>
        <div class="bg-card rounded-2xl shadow-sm border border-green-200 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي الإيرادات</p>
            <p class="text-xl font-bold text-green-700" dir="ltr">{{ number_format($totalRevenues, 2) }}</p>
        </div>
        <div class="bg-card rounded-2xl shadow-sm border border-red-200 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي المصروفات</p>
            <p class="text-xl font-bold text-red-700" dir="ltr">{{ number_format($totalExpenses, 2) }}</p>
        </div>
        <div class="bg-card rounded-2xl shadow-sm border border-green-200 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي الإيداعات</p>
            <p class="text-xl font-bold text-green-600" dir="ltr">{{ number_format($totalDeposits, 2) }}</p>
        </div>
        <div class="bg-card rounded-2xl shadow-sm border border-red-200 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي السحوبات</p>
            <p class="text-xl font-bold text-red-600" dir="ltr">{{ number_format($totalWithdrawals, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Treasury Balances --}}
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-primary-700">أرصدة الخزن</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($treasuryBalances as $treasury)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <span class="text-sm text-gray-700">{{ $treasury->name }}</span>
                        <span class="text-sm font-mono font-bold {{ $treasury->balance >= 0 ? 'text-green-700' : 'text-red-700' }}" dir="ltr">{{ number_format($treasury->balance, 2) }}</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">لا يوجد خزن</div>
                @endforelse
            </div>
        </div>

        {{-- Expenses By Account --}}
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-primary-700">المصروفات حسب الحساب</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($expensesByAccount as $item)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <span class="text-sm text-gray-700">{{ $item->account?->name ?? '—' }}</span>
                        <span class="text-sm font-mono font-bold text-red-700" dir="ltr">{{ number_format($item->total, 2) }}</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">لا يوجد مصروفات</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Revenues By Account --}}
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-primary-700">الإيرادات حسب الحساب</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($revenuesByAccount as $item)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <span class="text-sm text-gray-700">{{ $item->account?->name ?? '—' }}</span>
                        <span class="text-sm font-mono font-bold text-green-700" dir="ltr">{{ number_format($item->total, 2) }}</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">لا يوجد إيرادات</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-primary-700">آخر المعاملات</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentTransactions as $tx)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <span class="text-sm text-gray-700">{{ $tx->account?->name }}</span>
                            @if($tx->description)
                                <p class="text-xs text-gray-400">{{ Str::limit($tx->description, 30) }}</p>
                            @endif
                        </div>
                        <div class="text-left">
                            <span class="text-sm font-mono font-bold {{ $tx->type === 'revenue' ? 'text-green-700' : 'text-red-700' }}" dir="ltr">
                                {{ $tx->type === 'revenue' ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                            </span>
                            <p class="text-xs text-gray-400" dir="ltr">{{ $tx->date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">لا يوجد معاملات</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
