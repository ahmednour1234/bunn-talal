<div dir="rtl" class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700">كشف حركات الخزنة</h1>
            <p class="text-sm text-gray-400 mt-0.5">مصادر الإيداع والسحب لكل خزنة</p>
        </div>
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-primary-700 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
            التقارير
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports.treasury-statement') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">من تاريخ</label>
                <input type="date" name="date_from" value="{{ request('date_from', $dateFrom) }}"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-300 focus:border-primary-400 bg-gray-50">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">إلى تاريخ</label>
                <input type="date" name="date_to" value="{{ request('date_to', $dateTo) }}"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-300 focus:border-primary-400 bg-gray-50">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">الخزنة</label>
                <select name="treasury_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-300 focus:border-primary-400 bg-gray-50">
                    <option value="">كل الخزائن</option>
                    @foreach($treasuries as $t)
                        <option value="{{ $t->id }}" {{ request('treasury_id', $treasuryId) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-primary-700 text-white rounded-lg text-sm font-medium hover:bg-primary-800 transition-colors">عرض</button>
                @if(request()->hasAny(['date_from', 'date_to', 'treasury_id']))
                    <a href="{{ route('reports.treasury-statement') }}" class="px-4 py-2.5 border border-gray-200 text-gray-500 rounded-lg text-sm hover:bg-gray-50 transition-colors">مسح</a>
                @endif
            </div>
        </div>
    </form>

    {{-- Treasury Balance Cards --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="bg-stone-50 flex items-center justify-between px-4 py-3 border-b border-stone-100">
            <span class="text-xs text-primary-700 font-semibold">الأرصدة الحالية</span>
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-gray-800">الخزائن</h3>
                <div class="bg-stone-200 p-1.5 rounded-lg">
                    <svg class="w-4 h-4 text-primary-800" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                </div>
            </div>
        </div>
        @php $tc = ['bg-primary-800', 'bg-primary-700', 'bg-primary-600', 'bg-primary-500']; @endphp
        <div class="p-3 grid grid-cols-2 md:grid-cols-4 gap-2">
            @forelse($treasuryBalances as $ti => $tb)
                <div class="{{ $tc[$ti % count($tc)] }} rounded-xl px-4 py-3 text-right shadow-sm">
                    <p class="text-xs text-white/80 font-medium mb-0.5">{{ $tb->name }}</p>
                    <p class="text-xl font-extrabold text-white">{{ number_format($tb->balance, 0) }}<span class="text-xs font-normal text-white/70 mr-1">ج.م</span></p>
                </div>
            @empty
                <div class="col-span-4 py-3 text-center text-xs text-gray-300">لا توجد خزائن</div>
            @endforelse
        </div>
        <div class="px-4 py-3 bg-primary-900 grid grid-cols-3 gap-4 text-right">
            <div>
                <p class="text-xs text-primary-300 font-medium">إجمالي الإيداعات (الفترة)</p>
                <p class="text-base font-extrabold text-green-400">{{ number_format($totalDeposits, 2) }} <span class="text-xs font-normal text-primary-300">ج.م</span></p>
            </div>
            <div>
                <p class="text-xs text-primary-300 font-medium">إجمالي السحبات (الفترة)</p>
                <p class="text-base font-extrabold text-red-400">{{ number_format($totalWithdrawals, 2) }} <span class="text-xs font-normal text-primary-300">ج.م</span></p>
            </div>
            <div>
                <p class="text-xs text-primary-300 font-medium">صافي الفترة</p>
                @php $net = $totalDeposits - $totalWithdrawals; @endphp
                <p class="text-base font-extrabold {{ $net >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ number_format($net, 2) }} <span class="text-xs font-normal text-primary-300">ج.م</span></p>
            </div>
        </div>
    </div>

    {{-- Summary by Source --}}
    @if($bySource->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="bg-stone-50 px-4 py-3 border-b border-stone-100">
            <h3 class="text-sm font-bold text-gray-800">ملخص حسب المصدر</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-right">
                <thead>
                    <tr class="bg-primary-800 text-white">
                        <th class="px-4 py-3 font-semibold">المصدر</th>
                        <th class="px-4 py-3 font-semibold text-center">عدد العمليات</th>
                        <th class="px-4 py-3 font-semibold text-center">إيداعات</th>
                        <th class="px-4 py-3 font-semibold text-center">سحبات</th>
                        <th class="px-4 py-3 font-semibold text-center">الصافي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($bySource as $row)
                        @php $rowNet = $row['deposits'] - $row['withdrawals']; @endphp
                        <tr class="hover:bg-primary-50/40 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                <span class="inline-flex items-center gap-1.5">
                                    @if($row['source'] === 'مبيعات')
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    @elseif($row['source'] === 'مشتريات')
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    @elseif($row['source'] === 'تسوية رحلات')
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    @elseif($row['source'] === 'مرتجع مبيعات')
                                        <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                                    @elseif($row['source'] === 'مرتجع مشتريات')
                                        <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                    @endif
                                    {{ $row['source'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ $row['count'] }}</td>
                            <td class="px-4 py-3 text-center font-mono {{ $row['deposits'] > 0 ? 'text-green-700' : 'text-gray-300' }}">
                                {{ $row['deposits'] > 0 ? number_format($row['deposits'], 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono {{ $row['withdrawals'] > 0 ? 'text-red-600' : 'text-gray-300' }}">
                                {{ $row['withdrawals'] > 0 ? number_format($row['withdrawals'], 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-semibold {{ $rowNet >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                {{ number_format($rowNet, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-bold border-t-2 border-gray-200">
                        <td class="px-4 py-3 text-gray-700">الإجمالي</td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $allRows->count() }}</td>
                        <td class="px-4 py-3 text-center font-mono text-green-700">{{ number_format($totalDeposits, 2) }}</td>
                        <td class="px-4 py-3 text-center font-mono text-red-600">{{ number_format($totalWithdrawals, 2) }}</td>
                        <td class="px-4 py-3 text-center font-mono {{ ($totalDeposits - $totalWithdrawals) >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($totalDeposits - $totalWithdrawals, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    {{-- Detailed Transactions Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="bg-stone-50 px-4 py-3 border-b border-stone-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800">تفاصيل الحركات</h3>
            <span class="text-xs text-gray-400 bg-white border border-gray-100 rounded-xl px-3 py-1">{{ $allRows->count() }} حركة</span>
        </div>
        <div class="overflow-x-auto">
            @if($allRows->isEmpty())
                <div class="py-16 text-center text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    <p class="text-sm">لا توجد حركات في هذه الفترة</p>
                </div>
            @else
                <table class="w-full text-sm text-right">
                    <thead>
                        <tr class="bg-primary-800 text-white">
                            <th class="px-4 py-3 font-semibold">التاريخ</th>
                            <th class="px-4 py-3 font-semibold">المصدر</th>
                            <th class="px-4 py-3 font-semibold">البيان</th>
                            <th class="px-4 py-3 font-semibold">المرجع</th>
                            <th class="px-4 py-3 font-semibold text-center">النوع</th>
                            <th class="px-4 py-3 font-semibold text-center">إيداع</th>
                            <th class="px-4 py-3 font-semibold text-center">سحب</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($allRows as $row)
                            <tr class="hover:bg-primary-50/30 transition-colors">
                                <td class="px-4 py-3 text-gray-500 text-xs font-mono" dir="ltr">
                                    {{ $row['date'] instanceof \Carbon\Carbon ? $row['date']->format('Y-m-d') : \Carbon\Carbon::parse($row['date'])->format('Y-m-d') }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $sourceColors = [
                                            'مبيعات'         => 'bg-green-100 text-green-700',
                                            'مشتريات'        => 'bg-red-100 text-red-700',
                                            'تسوية رحلات'   => 'bg-amber-100 text-amber-700',
                                            'مرتجع مبيعات'  => 'bg-orange-100 text-orange-700',
                                            'مرتجع مشتريات' => 'bg-blue-100 text-blue-700',
                                            'يدوي'           => 'bg-gray-100 text-gray-600',
                                        ];
                                        $color = $sourceColors[$row['source']] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">{{ $row['source'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-700 text-xs">{{ $row['description'] }}</td>
                                <td class="px-4 py-3 text-gray-400 text-xs font-mono" dir="ltr">{{ $row['ref'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($row['type'] === 'deposit')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">↑ إيداع</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">↓ سحب</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-semibold text-green-700">
                                    {{ $row['type'] === 'deposit' ? number_format($row['amount'], 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-semibold text-red-600">
                                    {{ $row['type'] === 'withdrawal' ? number_format($row['amount'], 2) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-primary-900 text-white font-bold">
                            <td colspan="5" class="px-4 py-3 text-right">الإجمالي</td>
                            <td class="px-4 py-3 text-center font-mono text-green-300">{{ number_format($totalDeposits, 2) }}</td>
                            <td class="px-4 py-3 text-center font-mono text-red-300">{{ number_format($totalWithdrawals, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>

</div>
