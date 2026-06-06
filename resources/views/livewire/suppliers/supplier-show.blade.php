<div dir="rtl" class="space-y-5">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-600 rounded-xl px-4 py-3 text-sm font-semibold">
        {{ session('error') }}
    </div>
    @endif

    {{-- Payment Modal --}}
    @if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6" dir="rtl">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-extrabold text-gray-800">تسجيل دفعة للمورد</h3>
                <button wire:click="closePaymentModal" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ <span class="text-red-400">*</span></label>
                    <input type="number" step="0.01" wire:model="paymentAmount" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 text-right" placeholder="0.00">
                    @error('paymentAmount')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">التاريخ <span class="text-red-400">*</span></label>
                    <input type="date" wire:model="paymentDate" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                    @error('paymentDate')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الخزينة</label>
                    <select wire:model="paymentTreasuryId" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                        <option value="">-- بدون خزينة --</option>
                        @foreach($treasuries as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الفاتورة (اختياري)</label>
                    <select wire:model="paymentInvoiceId" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                        <option value="">-- بدون تحديد فاتورة --</option>
                        @foreach($invoices->whereIn('status', ['confirmed', 'partial_paid']) as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->invoice_number }} — {{ number_format($inv->total - $inv->paid_amount, 2) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                    <input type="text" wire:model="paymentNotes" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300" placeholder="سبب الدفعة...">
                </div>
            </div>
            <div class="flex items-center gap-3 mt-6">
                <button wire:click="savePayment" class="flex-1 py-2.5 bg-primary-700 text-white font-semibold rounded-xl hover:bg-primary-800 transition-colors">
                    تأكيد الدفعة
                </button>
                <button wire:click="closePaymentModal" class="flex-1 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                    إلغاء
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700">{{ $supplier->name }}</h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $supplier->company_name ?? 'بيانات المورد وكشف الحساب' }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="recalculateBalance"
                wire:confirm="سيتم إعادة حساب رصيد المورد من الصفر بناءً على كل الفواتير والدفعات والمرتجعات. هل أنت متأكد؟"
                class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 text-white text-sm font-semibold rounded-xl hover:bg-orange-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                إعادة الحساب
            </button>
            <button wire:click="openPaymentModal"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-700 text-white text-sm font-semibold rounded-xl hover:bg-primary-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                دفعة للمورد
            </button>
            <a href="{{ route('suppliers.index') }}" class="text-sm text-gray-500 hover:text-primary-700 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                الموردون
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">إجمالي الفواتير</p>
            <p class="text-xl font-extrabold text-primary-700">{{ number_format($totalInvoiced, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $invoices->whereNotIn('status', ['cancelled','draft'])->count() }} فاتورة</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">إجمالي المدفوع</p>
            <p class="text-xl font-extrabold text-green-600">{{ number_format($totalPaid, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">للمورد</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">المرتجعات المؤكدة</p>
            <p class="text-xl font-extrabold text-amber-600">{{ number_format($totalReturns, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $returns->whereIn('status', ['confirmed','refunded'])->count() }} مرتجع</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">الرصيد الحالي</p>
            <p class="text-xl font-extrabold {{ $currentBalance > 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format(abs($currentBalance), 2) }}</p>
            <p class="text-xs {{ $currentBalance > 0 ? 'text-red-400' : 'text-green-400' }} mt-1">{{ $currentBalance > 0 ? 'مدين (علينا)' : 'دائن أو متعادل' }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    @php
        $tabs = [
            'overview'  => 'نظرة عامة',
            'invoices'  => 'الفواتير',
            'returns'   => 'المرتجعات',
            'payments'  => 'الدفعات',
            'statement' => 'كشف الحساب',
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
            </button>
            @endforeach
        </div>

        <div class="p-5">

            {{-- ══ Overview Tab ══════════════════════════════════════ --}}
            @if($activeTab === 'overview')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-gray-700 border-b pb-2">بيانات المورد</h3>
                    <div class="grid grid-cols-2 gap-y-3 text-sm">
                        <span class="text-gray-500">الاسم</span><span class="font-semibold text-gray-800">{{ $supplier->name }}</span>
                        <span class="text-gray-500">الشركة</span><span class="font-semibold text-gray-800">{{ $supplier->company_name ?? '—' }}</span>
                        <span class="text-gray-500">الهاتف</span><span class="font-semibold text-gray-800" dir="ltr">{{ $supplier->phone ?? '—' }}</span>
                        <span class="text-gray-500">البريد</span><span class="font-semibold text-gray-800" dir="ltr">{{ $supplier->email ?? '—' }}</span>
                        <span class="text-gray-500">الرقم الضريبي</span><span class="font-semibold text-gray-800" dir="ltr">{{ $supplier->tax_number ?? '—' }}</span>
                        <span class="text-gray-500">العنوان</span><span class="font-semibold text-gray-800">{{ $supplier->address ?? '—' }}</span>
                        <span class="text-gray-500">الرصيد الافتتاحي</span><span class="font-semibold text-gray-800">{{ number_format($supplier->opening_balance, 2) }}</span>
                        <span class="text-gray-500">الحد الائتماني</span><span class="font-semibold text-gray-800">{{ number_format($supplier->credit_limit, 2) }}</span>
                        <span class="text-gray-500">الحالة</span>
                        <span>
                            @if($supplier->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700"><span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> نشط</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700"><span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> معطل</span>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-gray-700 border-b pb-2">ملخص النشاط</h3>
                    <div class="space-y-2.5">
                        @php
                            $stats = [
                                ['label' => 'فواتير المشتريات', 'value' => $invoices->whereNotIn('status', ['cancelled','draft'])->count() . ' فاتورة', 'color' => 'text-primary-700'],
                                ['label' => 'إجمالي الفواتير', 'value' => number_format($totalInvoiced, 2), 'color' => 'text-primary-700'],
                                ['label' => 'إجمالي المدفوع', 'value' => number_format($totalPaid, 2), 'color' => 'text-green-600'],
                                ['label' => 'المتبقي للمورد', 'value' => number_format($totalRemaining, 2), 'color' => 'text-red-500'],
                                ['label' => 'المرتجعات', 'value' => number_format($totalReturns, 2), 'color' => 'text-amber-600'],
                                ['label' => 'الرصيد الحالي', 'value' => number_format($currentBalance, 2) . ($currentBalance > 0 ? ' (علينا)' : ' (متعادل)'), 'color' => $currentBalance > 0 ? 'text-red-600' : 'text-green-600'],
                            ];
                        @endphp
                        @foreach($stats as $s)
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                            <span class="text-sm text-gray-500">{{ $s['label'] }}</span>
                            <span class="text-sm font-bold {{ $s['color'] }}">{{ $s['value'] }}</span>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="inline-flex items-center gap-2 text-sm text-primary-600 hover:underline font-semibold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                            تعديل بيانات المورد
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- ══ Invoices Tab ══════════════════════════════════════ --}}
            @if($activeTab === 'invoices')
            <div class="overflow-x-auto">
                @php
                    $statusColors = ['paid' => 'bg-green-100 text-green-700', 'partial_paid' => 'bg-amber-100 text-amber-700', 'confirmed' => 'bg-blue-100 text-blue-700', 'cancelled' => 'bg-red-100 text-red-600', 'draft' => 'bg-gray-100 text-gray-500'];
                    $statusLabels = ['paid' => 'مدفوع', 'partial_paid' => 'جزئي', 'confirmed' => 'مؤكد', 'cancelled' => 'ملغي', 'draft' => 'مسودة'];
                @endphp
                <table class="w-full text-sm text-right">
                    <thead>
                        <tr class="bg-primary-700 text-white">
                            <th class="px-4 py-2.5 font-semibold">رقم الفاتورة</th>
                            <th class="px-4 py-2.5 font-semibold">التاريخ</th>
                            <th class="px-4 py-2.5 font-semibold">الفرع</th>
                            <th class="px-4 py-2.5 font-semibold">الإجمالي</th>
                            <th class="px-4 py-2.5 font-semibold">المدفوع</th>
                            <th class="px-4 py-2.5 font-semibold">المرتجع</th>
                            <th class="px-4 py-2.5 font-semibold">المتبقي</th>
                            <th class="px-4 py-2.5 font-semibold">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($invoices as $inv)
                        @php
                            $invReturns = $returnsByInvoice[$inv->id] ?? collect();
                            $invReturnTotal = $invReturns->sum('refund_amount');
                            $remaining = max(0, (float)$inv->total - (float)$inv->paid_amount - $invReturnTotal);
                        @endphp
                        <tr class="hover:bg-gray-50/50 {{ $invReturnTotal > 0 ? 'border-r-2 border-r-amber-300' : '' }}">
                            <td class="px-4 py-2.5 font-mono text-xs text-primary-600 font-semibold">{{ $inv->invoice_number }}</td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $inv->date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $inv->branch?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5 font-semibold">{{ number_format($inv->total, 2) }}</td>
                            <td class="px-4 py-2.5 text-green-600 font-semibold">{{ number_format($inv->paid_amount, 2) }}</td>
                            <td class="px-4 py-2.5 font-semibold {{ $invReturnTotal > 0 ? 'text-amber-600' : 'text-gray-300' }}">
                                {{ $invReturnTotal > 0 ? number_format($invReturnTotal, 2) : '—' }}
                            </td>
                            <td class="px-4 py-2.5 font-semibold {{ $remaining > 0 ? 'text-red-500' : 'text-gray-400' }}">{{ number_format($remaining, 2) }}</td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$inv->status] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ $statusLabels[$inv->status] ?? $inv->status }}
                                </span>
                            </td>
                        </tr>
                        @foreach($invReturns as $ret)
                        <tr class="bg-amber-50/60 text-xs">
                            <td class="px-4 py-2 pr-8 font-mono text-amber-600 font-semibold">↩ {{ $ret->return_number }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $ret->date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-2 text-gray-400">{{ $ret->branch?->name ?? '—' }}</td>
                            <td colspan="2" class="px-4 py-2 text-gray-400">—</td>
                            <td class="px-4 py-2 text-amber-600 font-bold">{{ number_format($ret->refund_amount, 2) }}</td>
                            <td colspan="2" class="px-4 py-2 text-gray-400">مرتجع</td>
                        </tr>
                        @endforeach
                        @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">لا توجد فواتير</td></tr>
                        @endforelse
                    </tbody>
                    @if($invoices->count())
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr class="font-bold">
                            <td colspan="3" class="px-4 py-2.5 text-gray-600">الإجمالي</td>
                            <td class="px-4 py-2.5">{{ number_format($totalInvoiced, 2) }}</td>
                            <td class="px-4 py-2.5 text-green-600">{{ number_format($totalPaid, 2) }}</td>
                            <td class="px-4 py-2.5 text-amber-600">{{ number_format($totalReturns, 2) }}</td>
                            <td class="px-4 py-2.5 text-red-500">{{ number_format($totalRemaining, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @endif

            {{-- ══ Returns Tab ══════════════════════════════════════ --}}
            @if($activeTab === 'returns')
            <div class="overflow-x-auto">
                @php
                    $rStatusColors  = ['pending' => 'bg-amber-100 text-amber-700', 'confirmed' => 'bg-blue-100 text-blue-700', 'refunded' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-600'];
                    $rStatusLabels  = ['pending' => 'معلق', 'confirmed' => 'مؤكد', 'refunded' => 'مستردّ', 'cancelled' => 'ملغي'];
                @endphp
                <table class="w-full text-sm text-right">
                    <thead>
                        <tr class="bg-primary-700 text-white">
                            <th class="px-4 py-2.5 font-semibold">رقم المرتجع</th>
                            <th class="px-4 py-2.5 font-semibold">التاريخ</th>
                            <th class="px-4 py-2.5 font-semibold">رقم الفاتورة</th>
                            <th class="px-4 py-2.5 font-semibold">الفرع</th>
                            <th class="px-4 py-2.5 font-semibold">الإجمالي</th>
                            <th class="px-4 py-2.5 font-semibold">الخسائر</th>
                            <th class="px-4 py-2.5 font-semibold">قيمة المرتجع</th>
                            <th class="px-4 py-2.5 font-semibold">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($returns as $ret)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-2.5 font-mono text-xs text-primary-600 font-semibold">{{ $ret->return_number }}</td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $ret->date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-2.5 font-mono text-xs text-gray-500">{{ $ret->invoice?->invoice_number ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $ret->branch?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5 font-semibold">{{ number_format($ret->subtotal, 2) }}</td>
                            <td class="px-4 py-2.5 text-red-400">{{ number_format($ret->loss_amount, 2) }}</td>
                            <td class="px-4 py-2.5 font-bold text-amber-600">{{ number_format($ret->refund_amount, 2) }}</td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $rStatusColors[$ret->status] ?? '' }}">
                                    {{ $rStatusLabels[$ret->status] ?? $ret->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">لا توجد مرتجعات</td></tr>
                        @endforelse
                    </tbody>
                    @if($returns->whereIn('status', ['confirmed','refunded'])->count())
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr class="font-bold">
                            <td colspan="6" class="px-4 py-2.5 text-gray-600">إجمالي المرتجعات المؤكدة</td>
                            <td class="px-4 py-2.5 text-amber-600">{{ number_format($totalReturns, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @endif

            {{-- ══ Payments Tab ══════════════════════════════════════ --}}
            @if($activeTab === 'payments')
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead>
                        <tr class="bg-primary-700 text-white">
                            <th class="px-4 py-2.5 font-semibold">التاريخ</th>
                            <th class="px-4 py-2.5 font-semibold">الفاتورة</th>
                            <th class="px-4 py-2.5 font-semibold">المبلغ</th>
                            <th class="px-4 py-2.5 font-semibold">الخزينة</th>
                            <th class="px-4 py-2.5 font-semibold">طريقة الدفع</th>
                            <th class="px-4 py-2.5 font-semibold">ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($payments as $pay)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-2.5 text-gray-600">{{ $pay->payment_date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-2.5 font-mono text-xs text-primary-600">{{ $pay->invoice?->invoice_number ?? '—' }}</td>
                            <td class="px-4 py-2.5 font-bold text-green-600">{{ number_format($pay->amount, 2) }}</td>
                            <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $pay->treasury?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $pay->payment_method ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-gray-400 text-xs truncate max-w-40">{{ $pay->notes ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">لا توجد دفعات</td></tr>
                        @endforelse
                    </tbody>
                    @if($payments->count())
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr class="font-bold">
                            <td colspan="2" class="px-4 py-2.5 text-gray-600">إجمالي الدفعات</td>
                            <td class="px-4 py-2.5 text-green-600">{{ number_format($totalPayments, 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @endif

            {{-- ══ Account Statement Tab ══════════════════════════════ --}}
            @if($activeTab === 'statement')
            <div class="space-y-4">
                {{-- Summary cards --}}
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-red-50 rounded-2xl border border-red-100 p-4 text-center">
                        <p class="text-xs text-gray-400 mb-1">إجمالي المدين (فواتير)</p>
                        <p class="text-xl font-extrabold text-red-600">{{ number_format($totalDebit, 2) }}</p>
                    </div>
                    <div class="bg-green-50 rounded-2xl border border-green-100 p-4 text-center">
                        <p class="text-xs text-gray-400 mb-1">إجمالي الدائن (مدفوعات + مرتجعات)</p>
                        <p class="text-xl font-extrabold text-green-600">{{ number_format($totalCredit, 2) }}</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
                        <p class="text-xs text-gray-400 mb-1">الرصيد الختامي</p>
                        <p class="text-xl font-extrabold {{ $runningBalance > 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format(abs($runningBalance), 2) }}</p>
                        <p class="text-xs {{ $runningBalance > 0 ? 'text-red-400' : 'text-green-400' }} mt-0.5">{{ $runningBalance > 0 ? 'مدين (علينا)' : 'دائن أو متعادل' }}</p>
                    </div>
                </div>

                {{-- Ledger table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right">
                        <thead>
                            <tr class="bg-primary-700 text-white">
                                <th class="px-4 py-2.5 font-semibold">التاريخ</th>
                                <th class="px-4 py-2.5 font-semibold">البيان</th>
                                <th class="px-4 py-2.5 font-semibold">المرجع</th>
                                <th class="px-4 py-2.5 font-semibold text-red-200">مدين</th>
                                <th class="px-4 py-2.5 font-semibold text-green-200">دائن</th>
                                <th class="px-4 py-2.5 font-semibold">الرصيد</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @if($supplier->opening_balance > 0)
                            <tr class="bg-gray-50/60 text-xs text-gray-500">
                                <td class="px-4 py-2" colspan="3">رصيد افتتاحي</td>
                                <td class="px-4 py-2 text-red-400">{{ number_format($supplier->opening_balance, 2) }}</td>
                                <td class="px-4 py-2"></td>
                                <td class="px-4 py-2 font-semibold">{{ number_format($supplier->opening_balance, 2) }}</td>
                            </tr>
                            @endif
                            @forelse($ledger as $row)
                            @php
                                $typeColors = [
                                    'invoice'      => '',
                                    'payment'      => 'bg-green-50/40',
                                    'return'       => 'bg-amber-50/40',
                                    'cancellation' => 'bg-gray-50 text-gray-400 line-through',
                                ];
                                $typeIcons = [
                                    'invoice'      => '📄',
                                    'payment'      => '💳',
                                    'return'       => '↩',
                                    'cancellation' => '❌',
                                ];
                                $rowClass = $typeColors[$row['type']] ?? '';
                                if ($row['cancelled'] && $row['type'] !== 'cancellation') {
                                    $rowClass .= ' opacity-50';
                                }
                            @endphp
                            <tr class="hover:bg-gray-50/30 {{ $rowClass }}">
                                <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $row['date']?->format('Y-m-d') }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="mr-1 text-sm">{{ $typeIcons[$row['type']] ?? '' }}</span>
                                    <span class="font-medium text-gray-700">{{ $row['description'] }}</span>
                                </td>
                                <td class="px-4 py-2.5 font-mono text-xs text-primary-600">{{ $row['reference'] }}</td>
                                <td class="px-4 py-2.5 font-semibold {{ $row['debit'] > 0 ? 'text-red-500' : 'text-gray-300' }}">
                                    {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}
                                </td>
                                <td class="px-4 py-2.5 font-semibold {{ $row['credit'] > 0 ? 'text-green-600' : 'text-gray-300' }}">
                                    {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}
                                </td>
                                <td class="px-4 py-2.5 font-bold {{ $row['running_balance'] > 0 ? 'text-red-500' : 'text-green-600' }}">
                                    {{ number_format(abs($row['running_balance']), 2) }}
                                    <span class="text-xs font-normal ml-1">{{ $row['running_balance'] > 0 ? 'د' : 'دا' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">لا توجد حركات</td></tr>
                            @endforelse
                        </tbody>
                        @if($ledger->count())
                        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                            <tr class="font-bold">
                                <td colspan="3" class="px-4 py-2.5 text-gray-600">الإجمالي</td>
                                <td class="px-4 py-2.5 text-red-500">{{ number_format($totalDebit, 2) }}</td>
                                <td class="px-4 py-2.5 text-green-600">{{ number_format($totalCredit, 2) }}</td>
                                <td class="px-4 py-2.5 {{ $runningBalance > 0 ? 'text-red-500' : 'text-green-600' }}">
                                    {{ number_format(abs($runningBalance), 2) }}
                                    <span class="text-xs font-normal">{{ $runningBalance > 0 ? '(مدين)' : '(دائن)' }}</span>
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>

</div>
