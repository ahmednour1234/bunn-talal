<div>
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">حركات الخزن</h1>
            <p class="text-sm text-gray-500 mt-1">عرض جميع حركات الإيداع والسحب</p>
        </div>
        <div class="flex items-center gap-2">
            <x-button variant="secondary" size="sm" href="{{ route('treasury-transactions.export.excel', ['search' => $search, 'treasury' => $treasuryFilter, 'type' => $typeFilter]) }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                Excel
            </x-button>
            <x-button variant="secondary" size="sm" href="{{ route('treasury-transactions.export.pdf', ['search' => $search, 'treasury' => $treasuryFilter, 'type' => $typeFilter]) }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 7.5H5.25" /></svg>
                PDF
            </x-button>
            @if(auth('admin')->user()?->hasPermission('treasury-transactions.create'))
                <x-button variant="primary" href="{{ route('treasury-transactions.create') }}">
                    <x-icon name="plus" class="w-4 h-4" />
                    إضافة حركة
                </x-button>
            @endif
        </div>
    </div>

    {{-- Treasury Balance Cards --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="bg-stone-50 flex items-center justify-between px-4 py-4 border-b border-stone-100">
            <span class="text-xs text-primary-700 font-semibold">أرصدة الخزائن</span>
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-gray-800">الخزائن</h3>
                <div class="bg-stone-200 p-1.5 rounded-lg">
                    <svg class="w-4 h-4 text-primary-800" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                </div>
            </div>
        </div>
        @php $tc = ['bg-primary-800', 'bg-primary-700', 'bg-primary-600', 'bg-primary-500']; @endphp
        <div class="p-3 grid grid-cols-2 md:grid-cols-4 gap-2">
            @forelse($treasuries as $ti => $treasury)
                <div class="{{ $tc[$ti % count($tc)] }} rounded-xl px-4 py-3 text-right shadow-sm">
                    <p class="text-xs text-white/80 font-medium mb-0.5">{{ $treasury->name }}</p>
                    <p class="text-xl font-extrabold text-white">{{ number_format($treasury->balance, 0) }}<span class="text-xs font-normal text-white/70 mr-1">ج.م</span></p>
                </div>
            @empty
                <div class="col-span-4 py-4 text-center text-xs text-gray-300">لا توجد خزائن</div>
            @endforelse
        </div>
        <div class="px-4 py-3 bg-primary-900 text-right">
            <p class="text-xs text-primary-200 font-medium">الإجمالي</p>
            <p class="text-base font-extrabold text-white">{{ number_format($totalTreasuryBalance, 0) }} <span class="text-xs font-normal text-primary-200">ج.م</span></p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('treasury-transactions.index') }}" class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <x-icon name="search" class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" />
                <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث بالوصف أو رقم المرجع..."
                    class="w-full pr-10 pl-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
            </div>
            <select name="treasury" onchange="this.form.submit()" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                <option value="">كل الخزن</option>
                @foreach($treasuries as $treasury)
                    <option value="{{ $treasury->id }}" {{ request('treasury') == $treasury->id ? 'selected' : '' }}>{{ $treasury->name }}</option>
                @endforeach
            </select>
            <select name="type" onchange="this.form.submit()" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                <option value="">كل الأنواع</option>
                @foreach($typeLabels as $value => $label)
                    <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="mt-3 flex justify-end gap-2">
            @if(request()->hasAny(['search', 'treasury', 'type']))
                <a href="{{ route('treasury-transactions.index') }}" class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">مسح الفلاتر</a>
            @endif
            <button type="submit" class="px-4 py-2 text-sm bg-primary-700 text-white rounded-lg hover:bg-primary-800 transition-colors">بحث</button>
        </div>
    </form>

    {{-- Table --}}
    <x-data-table :headers="['#', 'الخزنة', 'النوع', 'المبلغ', 'الوصف', 'التاريخ', 'رقم المرجع', 'بواسطة']">
        @forelse($transactions as $tx)
            <tr class="hover:bg-primary-50/50 transition-colors">
                <td class="px-6 py-4 text-gray-500">{{ $tx->id }}</td>
                <td class="px-6 py-4 font-medium text-gray-800">{{ $tx->treasury?->name ?? '—' }}</td>
                <td class="px-6 py-4">
                    @if($tx->type === 'deposit')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">إيداع</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">سحب</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm font-mono text-center {{ $tx->type === 'deposit' ? 'text-green-700' : 'text-red-700' }}" dir="ltr">{{ number_format($tx->amount, 2) }}</td>
                <td class="px-6 py-4 text-gray-600 text-sm">{{ Str::limit($tx->description, 40) ?? '—' }}</td>
                <td class="px-6 py-4 text-gray-600 text-sm" dir="ltr">{{ $tx->date->format('Y-m-d') }}</td>
                <td class="px-6 py-4 text-gray-600 text-sm font-mono" dir="ltr">{{ $tx->reference_number ?? '—' }}</td>
                <td class="px-6 py-4 text-gray-600 text-sm">{{ $tx->admin?->name ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                    <x-icon name="arrows-right-left" class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                    <p>لا يوجد حركات مسجلة</p>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $transactions->links() }}
        </x-slot:pagination>
    </x-data-table>
</div>
