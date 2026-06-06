<div dir="rtl">
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-primary-700">مرتجع مشتريات: {{ $return->return_number }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $return->date->format('Y-m-d') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchase-returns.show.pdf', $return->id) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors text-sm font-medium shadow-sm">
                <x-icon name="document-arrow-down" class="w-4 h-4" />
                تصدير PDF
            </a>
            <a href="{{ route('purchase-returns.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors text-sm font-medium">
                <x-icon name="arrow-right" class="w-4 h-4" />
                العودة للقائمة
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">إجمالي قيمة المرتجع</div>
            <div class="text-xl font-bold text-gray-700">{{ number_format($return->subtotal, 2) }}</div>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
            <div class="text-xs text-red-600 mb-1">الخسائر</div>
            <div class="text-xl font-bold text-red-700">{{ number_format($return->loss_amount, 2) }}</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <div class="text-xs text-green-600 mb-1">المبلغ المسترد</div>
            <div class="text-xl font-bold text-green-700">{{ number_format($return->refund_amount, 2) }}</div>
        </div>
        <div class="bg-white border border-primary-100 rounded-xl p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">الحالة</div>
            @php
                $statusColors = [
                    'pending'   => 'bg-amber-100 text-amber-700',
                    'confirmed' => 'bg-blue-100 text-blue-700',
                    'refunded'  => 'bg-green-100 text-green-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                ];
            @endphp
            <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold {{ $statusColors[$return->status] ?? '' }}">
                {{ $return->status_label }}
            </span>
        </div>
    </div>

    {{-- Return Info --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
        <h2 class="text-base font-semibold text-primary-700 mb-4">معلومات المرتجع</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-400 mb-1">رقم المرتجع</p>
                <p class="font-mono text-sm font-semibold text-primary-700">{{ $return->return_number }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">فاتورة المشتريات</p>
                @if($return->invoice)
                    <p class="font-mono text-sm font-semibold text-primary-600">{{ $return->invoice->invoice_number }}</p>
                @else
                    <p class="text-sm text-gray-400">—</p>
                @endif
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">المورد</p>
                <p class="font-semibold text-sm">{{ $return->supplier?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">الفرع</p>
                <p class="text-sm">{{ $return->branch?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">التاريخ</p>
                <p class="text-sm">{{ $return->date->format('Y-m-d') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">المسؤول</p>
                <p class="text-sm">{{ $return->admin?->name ?? '—' }}</p>
            </div>
            @if($return->treasury)
                <div>
                    <p class="text-xs text-gray-400 mb-1">الخزينة</p>
                    <p class="text-sm">{{ $return->treasury->name }}</p>
                </div>
            @endif
            @if($return->notes)
                <div class="col-span-2 md:col-span-4">
                    <p class="text-xs text-gray-400 mb-1">ملاحظات</p>
                    <p class="text-sm text-gray-600">{{ $return->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Items Table --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden mb-4">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-primary-700">تفاصيل المنتجات المرتجعة</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-primary-50/50">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">#</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">المنتج</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الوحدة</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الكمية المرتجعة</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">سعر الوحدة</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الخسارة</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الإجمالي</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">السبب</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($return->items as $i => $item)
                        @php
                            $lineTotal = ($item->quantity * $item->unit_price) - $item->loss_amount;
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium">{{ $item->product->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $item->unit?->symbol ?? $item->unit?->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-semibold">{{ number_format($item->quantity) }}</td>
                            <td class="px-4 py-3">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-4 py-3 text-red-500">{{ number_format($item->loss_amount, 2) }}</td>
                            <td class="px-4 py-3 font-semibold text-green-600">{{ number_format(max($lineTotal, 0), 2) }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $item->reason ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr class="font-bold">
                        <td colspan="5" class="px-4 py-3 text-gray-600">الإجمالي</td>
                        <td class="px-4 py-3 text-red-500">{{ number_format($return->loss_amount, 2) }}</td>
                        <td class="px-4 py-3 text-green-700">{{ number_format($return->refund_amount, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Actions --}}
    @if($return->status === 'pending')
        <div class="flex items-center gap-3">
            @if(auth('admin')->user()?->hasPermission('purchase-returns.create'))
                <button wire:click="confirmReturn" wire:confirm="هل تريد تأكيد هذا المرتجع وخصم الكميات من الفرع؟"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors text-sm font-medium">
                    <x-icon name="check-circle" class="w-4 h-4" />
                    تأكيد المرتجع
                </button>
                <button wire:click="cancelReturn" wire:confirm="هل تريد إلغاء هذا المرتجع؟"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors text-sm font-medium">
                    <x-icon name="x-circle" class="w-4 h-4" />
                    إلغاء المرتجع
                </button>
            @endif
        </div>
    @endif
</div>
