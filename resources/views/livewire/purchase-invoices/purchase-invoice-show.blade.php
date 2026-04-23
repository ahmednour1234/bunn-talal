<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">تفاصيل فاتورة المشتريات</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $invoice->invoice_number }}</p>
        </div>
        <a href="{{ route('purchase-invoices.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors text-sm font-medium">
            العودة للقائمة
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Invoice Info --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div><span class="text-xs text-gray-500">المورد</span><p class="font-semibold text-gray-700 mt-1">{{ $invoice->supplier->name }}</p></div>
            <div><span class="text-xs text-gray-500">الفرع</span><p class="font-semibold text-gray-700 mt-1">{{ $invoice->branch->name }}</p></div>
            <div><span class="text-xs text-gray-500">التاريخ</span><p class="font-semibold text-gray-700 mt-1">{{ $invoice->date->format('Y-m-d') }}</p></div>
            <div><span class="text-xs text-gray-500">تاريخ الاستحقاق</span><p class="font-semibold text-gray-700 mt-1">{{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</p></div>
            <div>
                <span class="text-xs text-gray-500">الحالة</span>
                @php
                    $statusColors = ['draft'=>'bg-gray-100 text-gray-700','confirmed'=>'bg-blue-100 text-blue-700','partial_paid'=>'bg-amber-100 text-amber-700','paid'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700'];
                @endphp
                <p class="mt-1"><span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$invoice->status] ?? '' }}">{{ $invoice->status_label }}</span></p>
            </div>
            <div><span class="text-xs text-gray-500">طريقة الدفع</span><p class="font-semibold text-gray-700 mt-1">{{ \App\Models\PurchaseInvoice::paymentMethodLabels()[$invoice->payment_method] ?? $invoice->payment_method }}</p></div>
            <div><span class="text-xs text-gray-500">أنشأ بواسطة</span><p class="font-semibold text-gray-700 mt-1">{{ $invoice->admin->name }}</p></div>
            <div><span class="text-xs text-gray-500">الخزينة</span><p class="font-semibold text-gray-700 mt-1">{{ $invoice->treasury?->name ?? '-' }}</p></div>
        </div>
        @if($invoice->notes)
            <div class="mt-4 pt-4 border-t border-gray-100"><span class="text-xs text-gray-500">ملاحظات</span><p class="text-sm text-gray-600 mt-1">{{ $invoice->notes }}</p></div>
        @endif
    </div>

    {{-- Totals --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">المجموع الفرعي</div>
            <div class="text-lg font-bold text-gray-700">{{ number_format($invoice->subtotal, 2) }}</div>
        </div>
        <div class="bg-card rounded-2xl shadow-sm border border-amber-200 p-4 text-center">
            <div class="text-xs text-amber-600 mb-1">الخصم</div>
            <div class="text-lg font-bold text-amber-700">{{ number_format($invoice->discount_amount, 2) }}</div>
        </div>
        <div class="bg-card rounded-2xl shadow-sm border border-primary-200 p-4 text-center">
            <div class="text-xs text-primary-600 mb-1">الإجمالي</div>
            <div class="text-xl font-bold text-primary-700">{{ number_format($invoice->total, 2) }}</div>
        </div>
        <div class="bg-card rounded-2xl shadow-sm border border-green-200 p-4 text-center">
            <div class="text-xs text-green-600 mb-1">المدفوع</div>
            <div class="text-lg font-bold text-green-700">{{ number_format($invoice->paid_amount, 2) }}</div>
        </div>
        <div class="bg-card rounded-2xl shadow-sm border border-red-200 p-4 text-center">
            <div class="text-xs text-red-600 mb-1">المتبقي</div>
            <div class="text-lg font-bold text-red-700">{{ number_format($invoice->remaining_amount, 2) }}</div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden mb-4">
        <div class="p-4 border-b border-gray-100"><h2 class="text-lg font-semibold text-primary-700">المنتجات</h2></div>
        <table class="w-full text-sm">
            <thead class="bg-primary-50/50">
                <tr>
                    <th class="px-4 py-3 text-right font-semibold text-primary-700">#</th>
                    <th class="px-4 py-3 text-right font-semibold text-primary-700">المنتج</th>
                    <th class="px-4 py-3 text-right font-semibold text-primary-700">الكمية</th>
                    <th class="px-4 py-3 text-right font-semibold text-primary-700">سعر الوحدة</th>
                    <th class="px-4 py-3 text-right font-semibold text-primary-700">الخصم</th>
                    <th class="px-4 py-3 text-right font-semibold text-primary-700">الإجمالي</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($invoice->items as $i => $item)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-medium">{{ $item->product->name }}</td>
                        <td class="px-4 py-3">{{ $item->quantity }} {{ $item->unit?->name ?? '' }}</td>
                        <td class="px-4 py-3">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-3">{{ number_format($item->discount, 2) }}</td>
                        <td class="px-4 py-3 font-semibold">{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Payments --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden mb-4">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-primary-700">المدفوعات</h2>
            @if(!in_array($invoice->status, ['cancelled', 'paid']))
                <button wire:click="$toggle('showPaymentForm')" class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 text-sm font-medium">
                    <x-icon name="plus" class="w-4 h-4" />
                    إضافة دفعة
                </button>
            @endif
        </div>

        @if($showPaymentForm)
            <div class="p-4 bg-green-50/50 border-b border-green-100">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">المبلغ <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" wire:model="paymentAmount" max="{{ $invoice->remaining_amount }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                        @error('paymentAmount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">الخزينة</label>
                        <select wire:model="paymentTreasuryId" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                            <option value="">اختر الخزينة</option>
                            @foreach($treasuries as $treasury)
                                <option value="{{ $treasury->id }}">{{ $treasury->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ملاحظات</label>
                        <input type="text" wire:model="paymentNotes" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="addPayment" class="px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">تأكيد الدفع</button>
                        <button wire:click="$toggle('showPaymentForm')" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">إلغاء</button>
                    </div>
                </div>
            </div>
        @endif

        <table class="w-full text-sm">
            <thead class="bg-green-50/50">
                <tr>
                    <th class="px-4 py-3 text-right font-semibold text-green-700">#</th>
                    <th class="px-4 py-3 text-right font-semibold text-green-700">المبلغ</th>
                    <th class="px-4 py-3 text-right font-semibold text-green-700">التاريخ</th>
                    <th class="px-4 py-3 text-right font-semibold text-green-700">الخزينة</th>
                    <th class="px-4 py-3 text-right font-semibold text-green-700">بواسطة</th>
                    <th class="px-4 py-3 text-right font-semibold text-green-700">ملاحظات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($invoice->payments as $i => $payment)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-semibold text-green-700">{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-4 py-3">{{ $payment->payment_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">{{ $payment->treasury?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $payment->admin->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $payment->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">لا توجد مدفوعات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Actions --}}
    @if(!in_array($invoice->status, ['cancelled']))
        <div class="flex items-center gap-3">
            @if(!in_array($invoice->status, ['paid']))
                <button wire:click="cancelInvoice" wire:confirm="هل أنت متأكد من إلغاء هذه الفاتورة؟ سيتم إرجاع المخزون والمدفوعات." class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium">
                    إلغاء الفاتورة
                </button>
            @endif
            <a href="{{ route('purchase-returns.create') }}?invoice_id={{ $invoice->id }}" class="inline-flex items-center gap-2 px-6 py-3 bg-amber-600 text-white rounded-xl hover:bg-amber-700 transition-colors font-medium">
                إنشاء مرتجع
            </a>
        </div>
    @endif
</div>
