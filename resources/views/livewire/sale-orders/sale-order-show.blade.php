<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-primary-700">طلب مبيعات: {{ $order->order_number }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('sale-orders.pdf', $order->id) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors text-sm font-medium shadow-sm">
                <x-icon name="document-arrow-down" class="w-4 h-4" />
                تصدير PDF
            </a>
            @if(auth('admin')->user()?->hasPermission('sale-returns.create') && in_array($order->status, ['confirmed','partial_paid','paid']))
                <a href="{{ route('sale-returns.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 text-white rounded-xl hover:bg-amber-700 transition-colors text-sm font-medium shadow-sm">
                    <x-icon name="arrow-uturn-left" class="w-4 h-4" />
                    مرتجع مبيعات
                </a>
            @endif
            <a href="{{ route('sale-orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors text-sm font-medium">
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

    {{-- Order Info --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-400 mb-1">رقم الطلب</p>
                <p class="font-mono text-sm font-semibold text-primary-700">{{ $order->order_number }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">العميل</p>
                <p class="font-semibold text-sm">{{ $order->customer->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">الفرع</p>
                <p class="text-sm">{{ $order->branch->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">المندوب</p>
                <p class="text-sm">{{ $order->delegate?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">التاريخ</p>
                <p class="text-sm">{{ $order->date->format('Y-m-d') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">تاريخ الاستحقاق</p>
                <p class="text-sm">{{ $order->due_date?->format('Y-m-d') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">طريقة الدفع</p>
                <p class="text-sm">{{ $order->payment_method_label }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">الحالة</p>
                @php
                    $statusColors = ['draft'=>'bg-gray-100 text-gray-700','confirmed'=>'bg-blue-100 text-blue-700','partial_paid'=>'bg-amber-100 text-amber-700','paid'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700'];
                @endphp
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? '' }}">{{ $order->status_label }}</span>
            </div>
            @if($order->notes)
                <div class="col-span-2 md:col-span-4">
                    <p class="text-xs text-gray-400 mb-1">ملاحظات</p>
                    <p class="text-sm text-gray-600">{{ $order->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Totals --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">الإجمالي</div>
            <div class="text-xl font-bold text-gray-700">{{ number_format($order->total, 2) }}</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <div class="text-xs text-green-600 mb-1">المدفوع</div>
            <div class="text-xl font-bold text-green-700">{{ number_format($order->paid_amount, 2) }}</div>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
            <div class="text-xs text-red-600 mb-1">المتبقي</div>
            <div class="text-xl font-bold text-red-700">{{ number_format($order->remaining_amount, 2) }}</div>
        </div>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-center">
            <div class="text-xs text-amber-600 mb-1">الخصم</div>
            <div class="text-xl font-bold text-amber-700">{{ number_format($order->discount_amount, 2) }}</div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden mb-4">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-primary-700">المنتجات</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-primary-50/50">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">#</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">المنتج</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الوحدة</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الكمية</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">سعر الوحدة</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الخصم</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الضريبة</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الإجمالي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($order->items as $i => $item)
                        <tr>
                            <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium">{{ $item->product->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $item->unit?->name ?? $item->product->unit?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ number_format($item->quantity, 2) }}</td>
                            <td class="px-4 py-3">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-4 py-3 text-amber-600">{{ number_format($item->discount, 2) }}</td>
                            <td class="px-4 py-3 text-blue-600">{{ number_format($item->tax_amount, 2) }}</td>
                            <td class="px-4 py-3 font-semibold text-primary-700">{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Payments --}}
    @if($order->payments->count())
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden mb-4">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-primary-700">سجل المدفوعات</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-primary-50/50">
                        <tr>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700">التاريخ</th>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700">المبلغ</th>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700">الخزينة</th>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700">المسؤول</th>
                            <th class="px-4 py-3 text-right font-semibold text-primary-700">ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($order->payments as $payment)
                            <tr>
                                <td class="px-4 py-3 text-gray-500">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 font-semibold text-green-600">{{ number_format($payment->amount, 2) }}</td>
                                <td class="px-4 py-3">{{ $payment->treasury?->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $payment->admin?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $payment->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Add Payment --}}
    @if(in_array($order->status, ['confirmed', 'partial_paid']) && $order->remaining_amount > 0)
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-primary-700">إضافة دفعة</h2>
                <button type="button" wire:click="togglePaymentForm" class="text-sm text-primary-600 hover:underline">
                    {{ $showPaymentForm ? 'إخفاء' : 'إظهار' }}
                </button>
            </div>
            @if($showPaymentForm)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">المبلغ <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" wire:model.live="paymentAmount" min="0.01" max="{{ $order->remaining_amount }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 @error('paymentAmount') border-red-400 @enderror">
                        @error('paymentAmount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الخزينة <span class="text-red-500">*</span></label>
                        <select wire:model="paymentTreasuryId" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 @error('paymentTreasuryId') border-red-400 @enderror">
                            <option value="">اختر الخزينة</option>
                            @foreach($treasuries as $treasury)
                                <option value="{{ $treasury->id }}">{{ $treasury->name }}</option>
                            @endforeach
                        </select>
                        @error('paymentTreasuryId')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                        <input type="text" wire:model="paymentNotes" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="button" wire:click="addPayment" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors text-sm font-medium">
                        <x-icon name="banknotes" class="w-4 h-4" />
                        تسجيل الدفعة
                    </button>
                </div>
            @endif
        </div>
    @endif

    {{-- Cancel --}}
    @if(auth('admin')->user()?->hasPermission('sale-orders.edit') && in_array($order->status, ['draft','confirmed']))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
            <p class="text-sm text-red-700 mb-3">هل تريد إلغاء هذا الطلب؟ سيتم استعادة المخزون.</p>
            <button type="button" wire:click="cancelOrder" wire:confirm="هل أنت متأكد من إلغاء هذا الطلب؟"
                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">
                <x-icon name="x-circle" class="w-4 h-4" />
                إلغاء الطلب
            </button>
        </div>
    @endif
</div>
