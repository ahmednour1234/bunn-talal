<div class="space-y-5" dir="rtl">

    {{-- ─── Header ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-primary-800 flex items-center justify-center text-white font-extrabold text-xl shadow-md">
                {{ mb_substr($customer->name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-primary-700">{{ $customer->name }}</h1>
                <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                    <span>{{ $customer->phone ?? '—' }}</span>
                    @if($customer->area)
                        <span class="text-gray-300">|</span>
                        <span>{{ $customer->area->name }}</span>
                    @endif
                    <span class="text-gray-300">|</span>
                    @php
                        $clsColors = ['premium' => 'bg-yellow-100 text-yellow-700', 'medium' => 'bg-blue-50 text-blue-700', 'regular' => 'bg-gray-100 text-gray-600'];
                        $clsColor  = $clsColors[$customer->classification] ?? 'bg-gray-100 text-gray-600';
                    @endphp
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $clsColor }}">
                        {{ $customer->classification_label }}
                    </span>
                </div>
            </div>
        </div>
        <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-primary-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            العودة للقائمة
        </a>
    </div>

    {{-- ─── KPI Cards ───────────────────────────────────────────────── --}}
    @php
        $creditLimit  = (float) $customer->credit_limit;
        $utilization  = $creditLimit > 0 ? min(100, round(($currentBalance / $creditLimit) * 100)) : 0;
        $utilizationColor = $utilization > 100 ? 'text-red-600' : ($utilization > 80 ? 'text-amber-600' : 'text-green-600');
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">إجمالي الفواتير</p>
            <p class="text-xl font-extrabold text-primary-700">{{ number_format($totalInvoiced, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $orders->whereNotIn('status', ['cancelled','draft'])->count() }} فاتورة</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">إجمالي المحصّل</p>
            <p class="text-xl font-extrabold text-green-600">{{ number_format($totalPaid + $totalCollected, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">مدفوعات مباشرة وتحصيلات</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">المتبقي (رصيد)</p>
            <p class="text-xl font-extrabold {{ $currentBalance > 0 ? 'text-amber-600' : 'text-green-600' }}">{{ number_format($currentBalance, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $currentBalance > 0 ? 'مدين' : 'دائن أو متعادل' }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">الحد الائتماني</p>
            <p class="text-xl font-extrabold text-primary-700">{{ number_format($creditLimit, 0) }}</p>
            <div class="mt-1.5 bg-gray-100 rounded-full h-1.5">
                <div class="h-1.5 rounded-full {{ $utilization > 100 ? 'bg-red-500' : ($utilization > 80 ? 'bg-amber-400' : 'bg-green-500') }}"
                     style="width: {{ min($utilization, 100) }}%"></div>
            </div>
            <p class="text-xs {{ $utilizationColor }} mt-1 font-semibold">{{ $utilization }}% مستخدم</p>
        </div>
    </div>

    {{-- ─── Tabs ────────────────────────────────────────────────────── --}}
    @php
        $tabs = [
            'overview'      => 'نظرة عامة',
            'invoices'      => 'الفواتير',
            'collections'   => 'التحصيلات',
            'installments'  => 'الأقساط',
            'returns'       => 'المرتجعات',
            'statement'     => 'كشف الحساب',
            'analysis'      => 'تحليل الائتمان',
        ];
    @endphp

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        {{-- Tab Nav --}}
        <div class="flex overflow-x-auto border-b border-gray-100 bg-gray-50/50">
            @foreach($tabs as $key => $label)
            <button wire:click="setTab('{{ $key }}')"
                class="flex-shrink-0 px-5 py-3 text-sm font-semibold transition-all whitespace-nowrap
                    {{ $activeTab === $key
                        ? 'text-primary-700 border-b-2 border-primary-700 bg-white -mb-px'
                        : 'text-gray-500 hover:text-primary-600 hover:bg-gray-100' }}">
                {{ $label }}
                @if($key === 'analysis')
                    <span class="mr-1 text-xs font-bold {{ $analysis['score'] >= 70 ? 'text-green-600' : ($analysis['score'] >= 50 ? 'text-blue-600' : ($analysis['score'] >= 30 ? 'text-amber-600' : 'text-red-600')) }}">
                        {{ $analysis['score'] }}
                    </span>
                @endif
            </button>
            @endforeach
        </div>

        <div class="p-5">

            {{-- ══ Overview Tab ══════════════════════════════════════ --}}
            @if($activeTab === 'overview')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Customer Info --}}
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-gray-700 border-b pb-2">بيانات العميل</h3>
                    <div class="grid grid-cols-2 gap-y-3 text-sm">
                        <span class="text-gray-500">الاسم</span><span class="font-semibold text-gray-800">{{ $customer->name }}</span>
                        <span class="text-gray-500">الهاتف</span><span class="font-semibold text-gray-800" dir="ltr">{{ $customer->phone ?? '—' }}</span>
                        <span class="text-gray-500">البريد</span><span class="font-semibold text-gray-800" dir="ltr">{{ $customer->email ?? '—' }}</span>
                        <span class="text-gray-500">المنطقة</span><span class="font-semibold text-gray-800">{{ $customer->area?->name ?? '—' }}</span>
                        <span class="text-gray-500">العنوان</span><span class="font-semibold text-gray-800">{{ $customer->address ?? '—' }}</span>
                        <span class="text-gray-500">التصنيف</span><span class="font-semibold text-gray-800">{{ $customer->classification_label }}</span>
                        <span class="text-gray-500">الرصيد الافتتاحي</span><span class="font-semibold text-gray-800">{{ number_format($customer->opening_balance, 2) }}</span>
                        <span class="text-gray-500">الحالة</span>
                        <span>
                            @if($customer->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700"><span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> نشط</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700"><span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> معطل</span>
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-gray-700 border-b pb-2">ملخص النشاط</h3>
                    <div class="space-y-2.5">
                        @php
                            $stats = [
                                ['label' => 'فواتير البيع', 'value' => $orders->whereNotIn('status',['cancelled','draft'])->count() . ' فاتورة', 'color' => 'text-primary-700'],
                                ['label' => 'إجمالي الفواتير', 'value' => number_format($totalInvoiced, 2), 'color' => 'text-primary-700'],
                                ['label' => 'إجمالي المدفوع', 'value' => number_format($totalPaid, 2), 'color' => 'text-green-600'],
                                ['label' => 'التحصيلات', 'value' => number_format($totalCollected, 2) . ' (' . $collections->where('status','completed')->count() . ' تحصيل)', 'color' => 'text-green-600'],
                                ['label' => 'المرتجعات', 'value' => number_format($totalReturns, 2), 'color' => 'text-red-500'],
                                ['label' => 'أقساط معلقة', 'value' => number_format($totalInstallmentDue, 2), 'color' => 'text-amber-600'],
                                ['label' => 'الرصيد الحالي', 'value' => number_format($currentBalance, 2) . ($currentBalance > 0 ? ' (مدين)' : ' (دائن)'), 'color' => $currentBalance > 0 ? 'text-amber-600' : 'text-green-600'],
                            ];
                        @endphp
                        @foreach($stats as $s)
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                            <span class="text-sm text-gray-500">{{ $s['label'] }}</span>
                            <span class="text-sm font-bold {{ $s['color'] }}">{{ $s['value'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- ══ Invoices Tab ══════════════════════════════════════ --}}
            @if($activeTab === 'invoices')
            <div class="overflow-x-auto">
                <div class="flex gap-4 mb-4">
                    @php
                        $statusSummary = [
                            'paid'         => ['label' => 'مدفوع', 'color' => 'bg-green-50 text-green-700'],
                            'partial_paid' => ['label' => 'جزئي', 'color' => 'bg-amber-50 text-amber-700'],
                            'confirmed'    => ['label' => 'مؤكد', 'color' => 'bg-blue-50 text-blue-700'],
                            'cancelled'    => ['label' => 'ملغي', 'color' => 'bg-red-50 text-red-600'],
                        ];
                    @endphp
                    @foreach($statusSummary as $st => $info)
                    @php $cnt = $orders->where('status', $st)->count(); @endphp
                    @if($cnt > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold {{ $info['color'] }}">
                        {{ $info['label'] }}: {{ $cnt }}
                    </span>
                    @endif
                    @endforeach
                </div>
                <table class="w-full text-sm text-right">
                    <thead>
                        <tr class="bg-primary-700 text-white">
                            <th class="px-4 py-2.5 font-semibold">رقم الفاتورة</th>
                            <th class="px-4 py-2.5 font-semibold">التاريخ</th>
                            <th class="px-4 py-2.5 font-semibold">الإجمالي</th>
                            <th class="px-4 py-2.5 font-semibold">المدفوع</th>
                            <th class="px-4 py-2.5 font-semibold">المتبقي</th>
                            <th class="px-4 py-2.5 font-semibold">الحالة</th>
                            <th class="px-4 py-2.5 font-semibold">المندوب</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($orders as $order)
                        @php
                            $sc = ['paid'=>'bg-green-100 text-green-700','partial_paid'=>'bg-amber-100 text-amber-700','confirmed'=>'bg-blue-100 text-blue-700','cancelled'=>'bg-red-100 text-red-600','draft'=>'bg-gray-100 text-gray-500'];
                            $slabels = ['paid'=>'مدفوع','partial_paid'=>'جزئي','confirmed'=>'مؤكد','cancelled'=>'ملغي','draft'=>'مسودة'];
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-2.5 font-mono text-xs text-primary-600 font-semibold">{{ $order->order_number }}</td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $order->date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-2.5 font-semibold">{{ number_format($order->total, 2) }}</td>
                            <td class="px-4 py-2.5 text-green-600 font-semibold">{{ number_format($order->paid_amount, 2) }}</td>
                            <td class="px-4 py-2.5 font-semibold {{ $order->remaining_amount > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ number_format($order->remaining_amount, 2) }}</td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$order->status] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ $slabels[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $order->delegate?->name ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">لا توجد فواتير</td></tr>
                        @endforelse
                    </tbody>
                    @if($orders->count())
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr class="font-bold">
                            <td colspan="2" class="px-4 py-2.5 text-gray-600">الإجمالي</td>
                            <td class="px-4 py-2.5">{{ number_format($totalInvoiced, 2) }}</td>
                            <td class="px-4 py-2.5 text-green-600">{{ number_format($totalPaid, 2) }}</td>
                            <td class="px-4 py-2.5 text-amber-600">{{ number_format($totalRemaining, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @endif

            {{-- ══ Collections Tab ══════════════════════════════════ --}}
            @if($activeTab === 'collections')
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead>
                        <tr class="bg-primary-700 text-white">
                            <th class="px-4 py-2.5 font-semibold">رقم التحصيل</th>
                            <th class="px-4 py-2.5 font-semibold">التاريخ</th>
                            <th class="px-4 py-2.5 font-semibold">المبلغ</th>
                            <th class="px-4 py-2.5 font-semibold">الخزينة</th>
                            <th class="px-4 py-2.5 font-semibold">المندوب</th>
                            <th class="px-4 py-2.5 font-semibold">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($collections as $col)
                        @php
                            $csc = ['completed'=>'bg-green-100 text-green-700','pending'=>'bg-amber-100 text-amber-700','cancelled'=>'bg-red-100 text-red-600'];
                            $csl = ['completed'=>'مكتمل','pending'=>'معلق','cancelled'=>'ملغي'];
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-2.5 font-mono text-xs text-primary-600 font-semibold">{{ $col->collection_number }}</td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $col->collection_date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-2.5 font-bold text-green-600">{{ number_format($col->total_amount, 2) }}</td>
                            <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $col->treasury?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $col->delegate?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $csc[$col->status] ?? '' }}">{{ $csl[$col->status] ?? $col->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">لا توجد تحصيلات</td></tr>
                        @endforelse
                    </tbody>
                    @if($collections->count())
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr class="font-bold">
                            <td colspan="2" class="px-4 py-2.5 text-gray-600">إجمالي التحصيلات المكتملة</td>
                            <td class="px-4 py-2.5 text-green-600">{{ number_format($totalCollected, 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @endif

            {{-- ══ Installments Tab ══════════════════════════════════ --}}
            @if($activeTab === 'installments')
            <div class="space-y-4">
                <div class="grid grid-cols-3 gap-4 mb-2">
                    <div class="bg-blue-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500">إجمالي الخطط</p>
                        <p class="text-lg font-extrabold text-blue-700">{{ number_format($totalInstallmentAmount, 2) }}</p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500">المدفوع</p>
                        <p class="text-lg font-extrabold text-green-700">{{ number_format($totalInstallmentPaid, 2) }}</p>
                    </div>
                    <div class="bg-amber-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500">المتبقي</p>
                        <p class="text-lg font-extrabold text-amber-700">{{ number_format($totalInstallmentDue, 2) }}</p>
                    </div>
                </div>

                @forelse($installmentPlans as $plan)
                @php
                    $psc = ['active'=>'bg-green-100 text-green-700','completed'=>'bg-blue-100 text-blue-700','cancelled'=>'bg-red-100 text-red-600'];
                    $psl = ['active'=>'نشط','completed'=>'مكتمل','cancelled'=>'ملغي'];
                    $pProgress = $plan->total_amount > 0 ? min(100, round(($plan->paid_amount / $plan->total_amount) * 100)) : 0;
                @endphp
                <div class="border border-gray-100 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <span class="font-mono text-xs text-primary-600 font-bold">{{ $plan->plan_number }}</span>
                            @if($plan->reference_type === 'sale_order')
                                <span class="mr-2 text-xs text-gray-400">مرتبط بفاتورة</span>
                            @endif
                        </div>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $psc[$plan->status] ?? '' }}">{{ $psl[$plan->status] ?? $plan->status }}</span>
                    </div>
                    <div class="grid grid-cols-4 gap-3 text-sm mb-3">
                        <div><p class="text-xs text-gray-400">الإجمالي</p><p class="font-bold">{{ number_format($plan->total_amount, 2) }}</p></div>
                        <div><p class="text-xs text-gray-400">المدفوع</p><p class="font-bold text-green-600">{{ number_format($plan->paid_amount, 2) }}</p></div>
                        <div><p class="text-xs text-gray-400">المتبقي</p><p class="font-bold text-amber-600">{{ number_format($plan->outstanding, 2) }}</p></div>
                        <div><p class="text-xs text-gray-400">الأقساط</p><p class="font-bold">{{ $plan->installments_count }} × {{ number_format($plan->installment_amount, 2) }}</p></div>
                    </div>
                    <div class="bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $pProgress >= 100 ? 'bg-green-500' : 'bg-primary-600' }}" style="width: {{ $pProgress }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $pProgress }}% مسدد</p>

                    {{-- Overdue entries --}}
                    @php $overdueEntries = $plan->entries->where('status', 'overdue'); @endphp
                    @if($overdueEntries->count())
                    <div class="mt-2 p-2 bg-red-50 rounded-lg text-xs text-red-600 font-semibold">
                        ⚠️ {{ $overdueEntries->count() }} قسط متأخر — إجمالي: {{ number_format($overdueEntries->sum(fn($e) => $e->amount - $e->paid_amount), 2) }}
                    </div>
                    @endif
                </div>
                @empty
                <div class="py-10 text-center text-gray-400">لا توجد خطط تقسيط</div>
                @endforelse
            </div>
            @endif

            {{-- ══ Returns Tab ══════════════════════════════════════ --}}
            @if($activeTab === 'returns')
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead>
                        <tr class="bg-primary-700 text-white">
                            <th class="px-4 py-2.5 font-semibold">رقم المرتجع</th>
                            <th class="px-4 py-2.5 font-semibold">التاريخ</th>
                            <th class="px-4 py-2.5 font-semibold">قيمة المرتجع</th>
                            <th class="px-4 py-2.5 font-semibold">الفرع</th>
                            <th class="px-4 py-2.5 font-semibold">الحالة</th>
                            <th class="px-4 py-2.5 font-semibold">ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($returns as $ret)
                        @php
                            $rsc = ['completed'=>'bg-blue-100 text-blue-700','pending'=>'bg-amber-100 text-amber-700','cancelled'=>'bg-red-100 text-red-600'];
                            $rsl = ['completed'=>'مكتمل','pending'=>'معلق','cancelled'=>'ملغي'];
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-2.5 font-mono text-xs text-primary-600 font-semibold">{{ $ret->return_number }}</td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $ret->date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-2.5 font-bold text-red-500">{{ number_format($ret->refund_amount, 2) }}</td>
                            <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $ret->branch?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $rsc[$ret->status] ?? '' }}">{{ $rsl[$ret->status] ?? $ret->status }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-gray-400 text-xs truncate max-w-40">{{ $ret->notes ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">لا توجد مرتجعات</td></tr>
                        @endforelse
                    </tbody>
                    @if($returns->count())
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr class="font-bold">
                            <td colspan="2" class="px-4 py-2.5 text-gray-600">إجمالي المرتجعات المكتملة</td>
                            <td class="px-4 py-2.5 text-red-500">{{ number_format($totalReturns, 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @endif

            {{-- ══ Statement Tab ════════════════════════════════════ --}}
            @if($activeTab === 'statement')
            <div class="overflow-x-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-700">كشف حساب العميل</h3>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-gray-500">الرصيد الافتتاحي: <span class="font-bold text-gray-800">{{ number_format($customer->opening_balance, 2) }}</span></span>
                        <span class="{{ $currentBalance > 0 ? 'text-amber-600' : 'text-green-600' }} font-bold">
                            الرصيد الحالي: {{ number_format($currentBalance, 2) }}
                            {{ $currentBalance > 0 ? '(مدين)' : '(دائن)' }}
                        </span>
                    </div>
                </div>
                <table class="w-full text-sm text-right">
                    <thead>
                        <tr class="bg-primary-700 text-white">
                            <th class="px-4 py-2.5 font-semibold">التاريخ</th>
                            <th class="px-4 py-2.5 font-semibold">البيان</th>
                            <th class="px-4 py-2.5 font-semibold">المرجع</th>
                            <th class="px-4 py-2.5 font-semibold text-red-200">مدين ↑</th>
                            <th class="px-4 py-2.5 font-semibold text-green-200">دائن ↓</th>
                            <th class="px-4 py-2.5 font-semibold">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        {{-- Opening balance row --}}
                        <tr class="bg-gray-50">
                            <td class="px-4 py-2.5 text-gray-400 text-xs">—</td>
                            <td class="px-4 py-2.5 text-gray-500 font-medium">رصيد افتتاحي</td>
                            <td class="px-4 py-2.5 text-gray-400">—</td>
                            <td class="px-4 py-2.5">—</td>
                            <td class="px-4 py-2.5">—</td>
                            <td class="px-4 py-2.5 font-bold {{ (float)$customer->opening_balance > 0 ? 'text-amber-600' : 'text-green-600' }}">
                                {{ number_format($customer->opening_balance, 2) }}
                            </td>
                        </tr>
                        @php
                            $typeIcon = ['invoice'=>'🧾','payment'=>'💵','return'=>'↩️','collection'=>'📥'];
                            $typeColor = ['invoice'=>'text-gray-800','payment'=>'text-green-600','return'=>'text-blue-600','collection'=>'text-green-700'];
                        @endphp
                        @forelse($ledger as $row)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $row['date']?->format('Y-m-d') }}</td>
                            <td class="px-4 py-2.5 {{ $typeColor[$row['type']] ?? '' }}">
                                {{ $typeIcon[$row['type']] ?? '' }} {{ $row['description'] }}
                            </td>
                            <td class="px-4 py-2.5 font-mono text-xs text-gray-500">{{ $row['reference'] }}</td>
                            <td class="px-4 py-2.5 {{ $row['debit'] > 0 ? 'text-red-500 font-semibold' : 'text-gray-300' }}">
                                {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}
                            </td>
                            <td class="px-4 py-2.5 {{ $row['credit'] > 0 ? 'text-green-600 font-semibold' : 'text-gray-300' }}">
                                {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}
                            </td>
                            <td class="px-4 py-2.5 font-bold {{ $row['balance'] > 0 ? 'text-amber-600' : 'text-green-600' }}">
                                {{ number_format($row['balance'], 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">لا توجد حركات</td></tr>
                        @endforelse
                    </tbody>
                    @if($ledger->count())
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200 font-bold">
                        <tr>
                            <td colspan="3" class="px-4 py-2.5 text-gray-700">الإجمالي</td>
                            <td class="px-4 py-2.5 text-red-500">{{ number_format($ledger->sum('debit'), 2) }}</td>
                            <td class="px-4 py-2.5 text-green-600">{{ number_format($ledger->sum('credit'), 2) }}</td>
                            <td class="px-4 py-2.5 {{ $currentBalance > 0 ? 'text-amber-600' : 'text-green-600' }}">{{ number_format($currentBalance, 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @endif

            {{-- ══ Analysis Tab ════════════════════════════════════ --}}
            @if($activeTab === 'analysis')
            <div class="space-y-5">

                {{-- Score Card --}}
                <div class="flex flex-col md:flex-row gap-5">
                    <div class="flex-shrink-0 bg-gradient-to-br
                        {{ $analysis['recommendationColor'] === 'green' ? 'from-green-600 to-green-700' :
                           ($analysis['recommendationColor'] === 'blue' ? 'from-blue-600 to-blue-700' :
                           ($analysis['recommendationColor'] === 'amber' ? 'from-amber-500 to-amber-600' : 'from-red-600 to-red-700')) }}
                        rounded-2xl p-6 text-white text-center min-w-40">
                        <p class="text-5xl font-extrabold">{{ $analysis['score'] }}</p>
                        <p class="text-xs opacity-80 mt-1">نقطة من 100</p>
                        <p class="text-2xl mt-2">{{ $analysis['recommendationIcon'] }}</p>
                        <p class="text-sm font-semibold mt-1">{{ $analysis['recommendation'] }}</p>
                    </div>

                    <div class="flex-1 space-y-2.5">
                        <h3 class="text-sm font-bold text-gray-700 mb-3">تفاصيل التقييم</h3>
                        @foreach($analysis['points'] as $point)
                        <div class="flex items-start gap-2.5 p-3 rounded-xl
                            {{ $point['positive'] === true ? 'bg-green-50' : ($point['positive'] === false ? 'bg-red-50' : 'bg-gray-50') }}">
                            <span class="text-base flex-shrink-0">{{ $point['icon'] }}</span>
                            <span class="text-sm {{ $point['positive'] === true ? 'text-green-700' : ($point['positive'] === false ? 'text-red-700' : 'text-gray-600') }}">
                                {{ $point['text'] }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Credit Limit Comparison --}}
                <div class="border border-gray-100 rounded-2xl p-5">
                    <h3 class="text-sm font-bold text-gray-700 mb-4">الحد الائتماني مقارنةً بالرصيد</h3>
                    @php
                        $creditLimit = (float) $customer->credit_limit;
                        $utilPct = $creditLimit > 0 ? min(120, round(($currentBalance / $creditLimit) * 100)) : 0;
                        $barColor = $utilPct > 100 ? 'bg-red-500' : ($utilPct > 80 ? 'bg-amber-400' : 'bg-green-500');
                    @endphp
                    <div class="grid grid-cols-3 gap-4 mb-4 text-center">
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-500">الحد الائتماني الحالي</p>
                            <p class="text-xl font-extrabold text-primary-700">{{ number_format($creditLimit, 0) }}</p>
                        </div>
                        <div class="{{ $currentBalance > $creditLimit ? 'bg-red-50' : 'bg-amber-50' }} rounded-xl p-3">
                            <p class="text-xs text-gray-500">رصيد العميل</p>
                            <p class="text-xl font-extrabold {{ $currentBalance > $creditLimit ? 'text-red-600' : 'text-amber-600' }}">{{ number_format($currentBalance, 0) }}</p>
                        </div>
                        @if($analysis['suggestedLimit'] != $creditLimit)
                        <div class="bg-blue-50 rounded-xl p-3">
                            <p class="text-xs text-gray-500">الحد المقترح</p>
                            <p class="text-xl font-extrabold text-blue-600">{{ number_format($analysis['suggestedLimit'], 0) }}</p>
                        </div>
                        @else
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-500">التوصية</p>
                            <p class="text-sm font-bold text-gray-600 mt-1">{{ $analysis['recommendation'] }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div class="h-3 rounded-full transition-all {{ $barColor }}" style="width: {{ min($utilPct, 100) }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                        <span>0</span>
                        <span class="{{ $utilPct > 80 ? 'font-bold text-red-500' : '' }}">{{ $utilPct }}% مستخدم</span>
                        <span>{{ number_format($creditLimit, 0) }}</span>
                    </div>
                    @if($currentBalance > $creditLimit)
                    <div class="mt-3 p-3 bg-red-50 border border-red-100 rounded-xl text-sm text-red-700 font-semibold">
                        ⚠️ العميل تجاوز الحد الائتماني بمقدار {{ number_format($currentBalance - $creditLimit, 2) }}
                    </div>
                    @endif
                </div>

                {{-- Payment Discipline --}}
                @if($analysis['totalEntries'] > 0)
                <div class="border border-gray-100 rounded-2xl p-5">
                    <h3 class="text-sm font-bold text-gray-700 mb-3">انضباط السداد</h3>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div class="bg-green-50 rounded-xl p-3">
                            <p class="text-xs text-gray-500">إجمالي الأقساط</p>
                            <p class="text-2xl font-extrabold text-gray-700">{{ $analysis['totalEntries'] }}</p>
                        </div>
                        <div class="bg-red-50 rounded-xl p-3">
                            <p class="text-xs text-gray-500">متأخرة</p>
                            <p class="text-2xl font-extrabold text-red-600">{{ $analysis['overdueCount'] }}</p>
                        </div>
                        <div class="bg-green-50 rounded-xl p-3">
                            <p class="text-xs text-gray-500">في الموعد</p>
                            <p class="text-2xl font-extrabold text-green-600">{{ $analysis['totalEntries'] - $analysis['overdueCount'] }}</p>
                        </div>
                    </div>
                </div>
                @endif

            </div>
            @endif

        </div>
    </div>

</div>
