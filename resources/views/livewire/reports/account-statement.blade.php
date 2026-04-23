<div dir="rtl" class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700">كشف حساب</h1>
            <p class="text-sm text-gray-400 mt-0.5">Account Statement</p>
        </div>
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-primary-700 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
            التقارير
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">الحساب <span class="text-red-400">*</span></label>
                <select wire:model.live="accountId" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:ring-2 focus:ring-primary-300">
                    <option value="">-- اختر الحساب --</option>
                    @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }} @if($acc->account_number)({{ $acc->account_number }})@endif</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">من تاريخ</label>
                <input type="date" wire:model.live="dateFrom" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">إلى تاريخ</label>
                <input type="date" wire:model.live="dateTo" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300">
            </div>
        </div>
    </div>

    @if(!$accountId)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" /></svg>
        </div>
        <p class="text-gray-500 font-semibold">اختر حساباً لعرض كشف الحركات</p>
    </div>
    @else
    {{-- KPI Cards --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">إجمالي المدين (مصروفات)</p>
            <p class="text-xl font-extrabold text-red-600">{{ number_format($totalDebit, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">إجمالي الدائن (إيرادات)</p>
            <p class="text-xl font-extrabold text-green-600">{{ number_format($totalCredit, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">الرصيد الختامي</p>
            <p class="text-xl font-extrabold {{ $runningBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format(abs($runningBalance), 2) }}</p>
            <p class="text-xs {{ $runningBalance >= 0 ? 'text-green-500' : 'text-red-500' }} mt-0.5">{{ $runningBalance >= 0 ? 'دائن' : 'مدين' }}</p>
        </div>
    </div>

    {{-- Account Name --}}
    @if($selectedAccount)
    <div class="bg-primary-800 text-white rounded-2xl px-5 py-3 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center font-bold text-sm flex-shrink-0">
            {{ $selectedAccount->account_number ?? '#' }}
        </div>
        <div>
            <p class="font-bold text-base">{{ $selectedAccount->name }}</p>
            <p class="text-xs text-white/60">{{ $transactions->count() }} حركة في الفترة المختارة</p>
        </div>
    </div>
    @endif

    {{-- Transactions Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm text-right">
            <thead>
                <tr class="bg-primary-700 text-white text-xs">
                    <th class="px-4 py-3 font-semibold">التاريخ</th>
                    <th class="px-4 py-3 font-semibold">البيان</th>
                    <th class="px-4 py-3 font-semibold">الخزينة</th>
                    <th class="px-4 py-3 font-semibold">النوع</th>
                    <th class="px-4 py-3 font-semibold text-red-200">مدين</th>
                    <th class="px-4 py-3 font-semibold text-green-200">دائن</th>
                    <th class="px-4 py-3 font-semibold">الرصيد</th>
                    <th class="px-4 py-3 font-semibold">بواسطة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($transactions as $tx)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $tx['date']?->format('Y-m-d') }}</td>
                    <td class="px-4 py-2.5 text-gray-700">{{ $tx['description'] ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $tx['treasury'] ?? '—' }}</td>
                    <td class="px-4 py-2.5">
                        @if($tx['type'] === 'expense')
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600">مصروف</span>
                        @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">إيراد</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 {{ $tx['debit'] > 0 ? 'text-red-500 font-semibold' : 'text-gray-300' }}">
                        {{ $tx['debit'] > 0 ? number_format($tx['debit'], 2) : '—' }}
                    </td>
                    <td class="px-4 py-2.5 {{ $tx['credit'] > 0 ? 'text-green-600 font-semibold' : 'text-gray-300' }}">
                        {{ $tx['credit'] > 0 ? number_format($tx['credit'], 2) : '—' }}
                    </td>
                    <td class="px-4 py-2.5 font-bold {{ $tx['balance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($tx['balance'], 2) }}
                    </td>
                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $tx['admin'] ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">لا توجد حركات في هذه الفترة</td></tr>
                @endforelse
            </tbody>
            @if($transactions->count())
            <tfoot class="bg-gray-50 border-t-2 border-gray-200 font-bold text-sm">
                <tr>
                    <td colspan="4" class="px-4 py-2.5 text-gray-700">الإجمالي</td>
                    <td class="px-4 py-2.5 text-red-500">{{ number_format($totalDebit, 2) }}</td>
                    <td class="px-4 py-2.5 text-green-600">{{ number_format($totalCredit, 2) }}</td>
                    <td class="px-4 py-2.5 {{ $runningBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($runningBalance, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
        </div>
    </div>
    @endif

</div>
