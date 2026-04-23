<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-primary-700">عرض سعر: {{ $quotation->quotation_number }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('sale-quotations.pdf', $quotation->id) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors text-sm font-medium shadow-sm">
                <x-icon name="document-arrow-down" class="w-4 h-4" />
                تصدير PDF
            </a>
            <a href="{{ route('sale-quotations.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors text-sm font-medium">
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

    {{-- Info --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-400 mb-1">رقم العرض</p>
                <p class="font-mono text-sm font-semibold text-primary-700">{{ $quotation->quotation_number }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">العميل</p>
                <p class="font-semibold text-sm">{{ $quotation->customer->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">الفرع</p>
                <p class="text-sm">{{ $quotation->branch->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">المندوب</p>
                <p class="text-sm">{{ $quotation->delegate?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">تاريخ العرض</p>
                <p class="text-sm">{{ $quotation->date->format('Y-m-d') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">تاريخ الانتهاء</p>
                <p class="text-sm">{{ $quotation->expiry_date?->format('Y-m-d') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">الحالة</p>
                @php $statusColors = ['draft'=>'bg-gray-100 text-gray-700','sent'=>'bg-blue-100 text-blue-700','accepted'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700','expired'=>'bg-orange-100 text-orange-700']; @endphp
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$quotation->status] ?? '' }}">{{ $quotation->status_label }}</span>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">الإجمالي</p>
                <p class="text-xl font-bold text-primary-700">{{ number_format($quotation->total, 2) }}</p>
            </div>
            @if($quotation->notes)
                <div class="col-span-2 md:col-span-4">
                    <p class="text-xs text-gray-400 mb-1">ملاحظات</p>
                    <p class="text-sm text-gray-600">{{ $quotation->notes }}</p>
                </div>
            @endif
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
                    @foreach($quotation->items as $i => $item)
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

    {{-- Convert to Order --}}
    @if(in_array($quotation->status, ['draft','sent']) && auth('admin')->user()?->hasPermission('sale-orders.create'))
        <div class="bg-green-50 border border-green-200 rounded-2xl p-6 mb-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-green-700">تحويل إلى طلب مبيعات</h2>
                <button type="button" wire:click="toggleConvertForm" class="text-sm text-green-600 hover:underline">
                    {{ $showConvertForm ? 'إخفاء' : 'إظهار' }}
                </button>
            </div>
            @if($showConvertForm)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">طريقة الدفع <span class="text-red-500">*</span></label>
                        <select wire:model.live="convertPaymentMethod" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-green-300">
                            <option value="cash">نقدي</option>
                            <option value="credit">آجل</option>
                            <option value="partial">جزئي</option>
                        </select>
                    </div>
                    @if(in_array($convertPaymentMethod, ['cash','partial']))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الخزينة <span class="text-red-500">*</span></label>
                            <select wire:model="convertTreasuryId" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-green-300 @error('convertTreasuryId') border-red-400 @enderror">
                                <option value="">اختر الخزينة</option>
                                @foreach($treasuries as $treasury)
                                    <option value="{{ $treasury->id }}">{{ $treasury->name }}</option>
                                @endforeach
                            </select>
                            @error('convertTreasuryId')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    @endif
                </div>
                <div class="mt-4">
                    <button type="button" wire:click="convertToOrder" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors text-sm font-medium">
                        <x-icon name="arrow-path" class="w-4 h-4" />
                        تحويل إلى طلب مبيعات
                    </button>
                </div>
            @endif
        </div>
    @endif

    {{-- Reject --}}
    @if(in_array($quotation->status, ['draft','sent']) && auth('admin')->user()?->hasPermission('sale-quotations.edit'))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
            <button type="button" wire:click="cancelQuotation" wire:confirm="هل تريد رفض هذا العرض؟"
                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">
                <x-icon name="x-circle" class="w-4 h-4" />
                رفض العرض
            </button>
        </div>
    @endif
</div>
