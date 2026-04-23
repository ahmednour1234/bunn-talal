<div>
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">تفاصيل أمر الصرف #{{ $dispatch->id }}</h1>
            <p class="text-sm text-gray-500 mt-1">عرض تفاصيل أمر الصرف المخزني</p>
        </div>
        <x-button variant="secondary" href="{{ route('inventory-dispatches.index') }}">
            رجوع
        </x-button>
    </div>

    {{-- Info Row --}}
    @php
        $statusColors = [
            'pending'        => 'bg-gray-100 text-gray-700',
            'dispatched'     => 'bg-blue-100 text-blue-700',
            'partial_return' => 'bg-orange-100 text-orange-700',
            'returned'       => 'bg-yellow-100 text-yellow-700',
            'settled'        => 'bg-green-100 text-green-700',
        ];
    @endphp
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-6">
        <div class="flex flex-wrap gap-3">
            <div class="flex flex-col gap-0.5 bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 min-w-[120px]">
                <span class="text-[11px] text-gray-400">الفرع</span>
                <span class="font-bold text-primary-700 text-sm">{{ $dispatch->branch->name }}</span>
            </div>
            <div class="flex flex-col gap-0.5 bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 min-w-[140px]">
                <span class="text-[11px] text-gray-400">المندوب</span>
                <span class="font-bold text-primary-700 text-sm">{{ $dispatch->delegate->name }}</span>
            </div>
            <div class="flex flex-col gap-0.5 bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 min-w-[120px]">
                <span class="text-[11px] text-gray-400">المسؤول</span>
                <span class="font-bold text-gray-700 text-sm">{{ $dispatch->admin->name }}</span>
            </div>
            <div class="flex flex-col gap-0.5 bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 min-w-[110px]">
                <span class="text-[11px] text-gray-400">التاريخ</span>
                <span class="font-medium text-gray-700 text-sm">{{ $dispatch->date->format('Y/m/d') }}</span>
            </div>
            <div class="flex flex-col gap-0.5 bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 min-w-[90px]">
                <span class="text-[11px] text-gray-400">الحالة</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold w-fit {{ $statusColors[$dispatch->status] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $dispatch->status_label }}
                </span>
            </div>
            @if($dispatch->notes)
            <div class="flex flex-col gap-0.5 bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 flex-1 min-w-[140px]">
                <span class="text-[11px] text-gray-400">ملاحظات</span>
                <span class="text-gray-600 text-sm">{{ $dispatch->notes }}</span>
            </div>
            @endif
            @if($dispatch->trip_id)
            <div class="flex flex-col gap-0.5 bg-primary-50 border border-primary-200 rounded-xl px-4 py-2.5 min-w-[160px]">
                <span class="text-[11px] text-primary-500">الرحلة المرتبطة</span>
                <a href="{{ route('trips.show', $dispatch->trip_id) }}" class="font-bold text-primary-700 text-sm hover:underline">
                    {{ $dispatch->trip?->trip_number ?? '#'.$dispatch->trip_id }}
                </a>
                <span class="text-xs mt-0.5 font-semibold {{ $dispatch->trip?->status === 'settled' ? 'text-green-600' : 'text-amber-600' }}">
                    {{ $dispatch->trip?->statusLabel() ?? '' }}
                    @if($dispatch->trip?->status === 'settled')
                    — سوّاها {{ $dispatch->trip?->settler?->name ?? '' }}
                    @endif
                </span>
            </div>
            @endif
        </div>
    </div>

    @if($dispatch->status === 'settled')
    <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-4 flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
        <div>
            <p class="text-sm font-bold text-green-800">تمت تسوية هذا الأمر ضمن رحلة المندوب</p>
            @if($dispatch->trip)
            <p class="text-xs text-green-600">
                الرحلة: <a href="{{ route('trips.show', $dispatch->trip_id) }}" class="font-bold hover:underline">{{ $dispatch->trip->trip_number }}</a>
                | سوّاها: {{ $dispatch->trip->settler?->name ?? '—' }}
                | {{ $dispatch->trip->settled_at?->format('Y-m-d') ?? '—' }}
            </p>
            @endif
        </div>
    </div>
    @endif

    {{-- Financial Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-red-50 rounded-2xl border border-red-100 p-5 text-center">
            <p class="text-xs text-red-500 mb-1">إجمالي التكلفة</p>
            <p class="text-2xl font-bold text-red-700" dir="ltr">{{ number_format($dispatch->total_cost, 2) }}</p>
        </div>
        <div class="bg-blue-50 rounded-2xl border border-blue-100 p-5 text-center">
            <p class="text-xs text-blue-500 mb-1">المبيعات المتوقعة</p>
            <p class="text-2xl font-bold text-blue-700" dir="ltr">{{ number_format($dispatch->expected_sales, 2) }}</p>
        </div>
        <div class="bg-green-50 rounded-2xl border border-green-100 p-5 text-center">
            <p class="text-xs text-green-500 mb-1">المبيعات الفعلية</p>
            <p class="text-2xl font-bold text-green-700" dir="ltr">{{ number_format($dispatch->actual_sales, 2) }}</p>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary-700">الأصناف</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-primary-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-bold text-primary-700">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-primary-700">الوحدة</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-primary-700">الكمية المصروفة</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-primary-700">المرتجع</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-primary-700">معه الآن</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-primary-700">سعر التكلفة</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-primary-700">سعر البيع</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($dispatch->items as $item)
                    @php $unitSymbol = $item->product->unit?->symbol ?? ''; $remaining = $item->quantity - ($item->returned_quantity ?? 0); @endphp
                        <tr class="hover:bg-primary-50/50">
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $item->product->name }}</td>
                            <td class="px-6 py-4 text-gray-500 text-xs">{{ $unitSymbol }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                    {{ $item->quantity }} {{ $unitSymbol }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700">
                                    {{ $item->returned_quantity ?? 0 }} {{ $unitSymbol }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $remaining > 0 ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $remaining }} {{ $unitSymbol }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600" dir="ltr">{{ number_format($item->cost_price, 2) }}</td>
                            <td class="px-6 py-4 text-gray-600" dir="ltr">{{ number_format($item->selling_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Actions --}}
    @if(auth('admin')->user()?->hasPermission('inventory-dispatches.edit'))
        <div class="flex items-center gap-3 mb-6">
            @if(in_array($dispatch->status, ['dispatched', 'partial_return']))
                <x-button type="button" variant="secondary" wire:click="toggleReturnForm">
                    {{ $showReturnForm ? 'إخفاء نموذج المرتجع' : 'تسجيل مرتجعات' }}
                </x-button>
            @endif
            @if(in_array($dispatch->status, ['dispatched', 'partial_return', 'returned']))
                <x-button type="button" variant="success" wire:click="toggleSettleForm">
                    {{ $showSettleForm ? 'إخفاء نموذج التسوية' : 'تسوية الحساب' }}
                </x-button>
            @endif
        </div>

        {{-- Return Form --}}
        @if($showReturnForm)
            <div class="bg-orange-50 rounded-2xl border border-orange-200 p-6 mb-6">
                <h3 class="text-lg font-bold text-orange-700 mb-4">تسجيل كميات مرتجعة</h3>
                <div class="overflow-x-auto rounded-xl border border-orange-100">
                    <table class="w-full text-sm">
                        <thead class="bg-orange-100/60">
                            <tr>
                                <th class="px-4 py-3 text-right font-semibold text-orange-700">المنتج</th>
                                <th class="px-4 py-3 text-right font-semibold text-orange-700 w-44">وحدة الإرجاع</th>
                                <th class="px-4 py-3 text-right font-semibold text-orange-700 w-40">الكمية المرتجعة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-orange-100 bg-white">
                            @foreach($dispatch->items as $item)
                            @php $remaining = $item->quantity - ($item->returned_quantity ?? 0); @endphp
                            @if($remaining > 0)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-700">
                                        {{ $item->product->name }}
                                        <span class="text-xs text-gray-400 mr-1">(متبقي: {{ $remaining }} {{ $item->product->unit?->symbol ?? '' }})</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(!empty($returnAvailableUnits[$item->id]))
                                            <select wire:model.live="returnUnitIds.{{ $item->id }}"
                                                class="w-full px-3 py-2 border border-orange-200 rounded-lg bg-white text-sm">
                                                @foreach($returnAvailableUnits[$item->id] as $unitOpt)
                                                    <option value="{{ $unitOpt['id'] }}">{{ $unitOpt['name'] }} ({{ $unitOpt['symbol'] }})</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <span class="text-gray-400 text-xs">{{ $item->product->unit?->symbol ?? '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" wire:model="returnQuantities.{{ $item->id }}"
                                            min="0" max="{{ $returnMaxQuantities[$item->id] ?? $remaining }}"
                                            class="w-full px-3 py-2 border border-orange-200 rounded-lg text-sm bg-white">
                                        @if(!empty($returnMaxQuantities[$item->id]))
                                            <p class="text-[11px] text-gray-400 mt-1">الحد الأقصى: {{ $returnMaxQuantities[$item->id] }} {{ $returnUnitSymbols[$item->id] ?? '' }}</p>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <x-button type="button" variant="primary" wire:click="submitReturn">
                        حفظ المرتجعات
                    </x-button>
                </div>
            </div>
        @endif

        {{-- Settle Form --}}
        @if($showSettleForm)
            <div class="bg-green-50 rounded-2xl border border-green-200 p-6 mb-6">
                <h3 class="text-lg font-bold text-green-700 mb-4">تسوية الحساب</h3>
                <div class="max-w-md">
                    <x-form-input
                        label="المبيعات الفعلية"
                        name="actualSales"
                        type="number"
                        wire:model="actualSales"
                        placeholder="0.00"
                        required
                        :error="$errors->first('actualSales')"
                    />
                </div>
                <div class="mt-4">
                    <x-button type="button" variant="success" wire:click="submitSettle">
                        تأكيد التسوية
                    </x-button>
                </div>
            </div>
        @endif
    @endif

</div>
