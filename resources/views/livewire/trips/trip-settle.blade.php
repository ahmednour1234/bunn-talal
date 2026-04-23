<div dir="rtl" class="max-w-3xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700">تسوية الرحلة</h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $trip->trip_number }} — {{ $trip->delegate?->name }}</p>
        </div>
        <a href="{{ route('trips.show', $trip->id) }}" class="text-sm text-gray-500 hover:text-primary-700 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
            عودة
        </a>
    </div>

    {{-- Summary Bar --}}
    <div class="bg-primary-700 text-white rounded-2xl p-5 grid grid-cols-4 gap-4 text-center">
        <div>
            <p class="text-xs text-primary-200 mb-1">العهدة النقدية</p>
            <p class="text-xl font-extrabold {{ $trip->cash_custody_amount > 0 ? 'text-amber-300' : 'text-primary-300' }}">
                {{ $trip->cash_custody_amount > 0 ? number_format($trip->cash_custody_amount, 2) : '—' }}
            </p>
            @if($trip->cash_custody_amount > 0)<p class="text-xs text-primary-200">ج.م</p>@endif
        </div>
        <div>
            <p class="text-xs text-primary-200 mb-1">إجمالي المفوتر</p>
            <p class="text-xl font-extrabold">{{ number_format($trip->total_invoiced, 2) }}</p>
            <p class="text-xs text-primary-200">ج.م</p>
        </div>
        <div>
            <p class="text-xs text-primary-200 mb-1">إجمالي التحصيل</p>
            <p class="text-xl font-extrabold">{{ number_format($trip->total_collected, 2) }}</p>
            <p class="text-xs text-primary-200">ج.م</p>
        </div>
        <div>
            <p class="text-xs text-primary-200 mb-1">قيمة المرتجع</p>
            <p class="text-xl font-extrabold">{{ number_format($trip->total_returned_value, 2) }}</p>
            <p class="text-xs text-primary-200">ج.م</p>
        </div>
    </div>

    @if($trip->cash_custody_amount > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-amber-800">عهدة نقدية مسجلة — يجب استيفاؤها بشكل منفصل</p>
            <p class="text-lg font-extrabold text-amber-800">{{ number_format($trip->cash_custody_amount, 2) }} ج.م
                @if($trip->custodyTreasury)<span class="text-xs font-normal text-amber-600"> من خزنة: {{ $trip->custodyTreasury->name }}</span>@endif
            </p>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- PRODUCT SETTLEMENT TABLE --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-gray-800">استلام البضاعة</h2>
                <p class="text-xs text-gray-400 mt-0.5">أدخل الكمية الفعلية المُستلمة لكل منتج</p>
            </div>
            @php
                $totalDeficitVal = collect($productItems)->sum(function($item) {
                    $d = max(0, $item['expected_remaining'] - (float)$item['actual_received']);
                    return $d * $item['selling_price'];
                });
            @endphp
            @if($totalDeficitVal > 0)
            <span class="text-xs font-bold text-red-700 bg-red-50 border border-red-200 px-3 py-1.5 rounded-lg">
                عجز بضاعة: {{ number_format($totalDeficitVal, 2) }} ج.م
            </span>
            @else
            <span class="text-xs font-bold text-green-700 bg-green-50 border border-green-200 px-3 py-1.5 rounded-lg">✓ لا عجز في البضاعة</span>
            @endif
        </div>

        @if(count($productItems) === 0)
        <p class="text-center text-gray-400 py-10 text-sm">لا توجد أوامر صرف مرتبطة بهذه الرحلة</p>
        @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-right">المنتج</th>
                    <th class="px-3 py-3 text-center">الوحدة</th>
                    <th class="px-3 py-3 text-center">المصروف</th>
                    <th class="px-3 py-3 text-center">المُباع</th>
                    <th class="px-3 py-3 text-center">المتبقي المتوقع</th>
                    <th class="px-3 py-3 text-center w-32">الفعلي المُستلَم</th>
                    <th class="px-3 py-3 text-center">الناقص</th>
                    <th class="px-3 py-3 text-center">قيمة الناقص</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($productItems as $i => $item)
            @php
                $deficitQty = max(0, $item['expected_remaining'] - (float)$item['actual_received']);
                $deficitVal = $deficitQty * $item['selling_price'];
                $hasDeficit = $deficitQty > 0.001;
            @endphp
            <tr class="{{ $hasDeficit ? 'bg-red-50/40' : '' }} hover:bg-gray-50/50 transition-colors">
                <td class="px-4 py-3 font-semibold text-gray-800">{{ $item['name'] }}</td>
                <td class="px-3 py-3 text-center">
                    <span class="inline-block bg-gray-100 text-gray-600 text-xs font-bold px-2.5 py-1 rounded-lg">{{ $item['unit'] }}</span>
                </td>
                <td class="px-3 py-3 text-center">
                    <span class="font-bold text-gray-700">{{ number_format($item['dispatched'], 0) }}</span>
                    <span class="text-xs text-gray-400 mr-0.5">{{ $item['unit'] }}</span>
                </td>
                <td class="px-3 py-3 text-center">
                    <span class="font-bold text-blue-600">{{ number_format($item['sold'], 0) }}</span>
                    <span class="text-xs text-gray-400 mr-0.5">{{ $item['unit'] }}</span>
                </td>
                <td class="px-3 py-3 text-center">
                    <span class="inline-block bg-primary-50 text-primary-700 font-extrabold text-xs px-2 py-1 rounded-lg">
                        {{ number_format($item['expected_remaining'], 0) }}
                    </span>
                    <span class="text-xs text-gray-400 mr-0.5">{{ $item['unit'] }}</span>
                </td>
                <td class="px-3 py-3 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <input type="number"
                            wire:model.live="productItems.{{ $i }}.actual_received"
                            step="0.001" min="0"
                            max="{{ $item['expected_remaining'] }}"
                            class="w-24 border {{ $hasDeficit ? 'border-red-300 bg-red-50' : 'border-gray-200' }} rounded-lg px-2 py-1.5 text-sm text-center focus:ring-2 focus:ring-primary-300 font-bold">
                        <span class="text-xs text-gray-500 font-semibold whitespace-nowrap">{{ $item['unit'] }}</span>
                    </div>
                </td>
                <td class="px-3 py-3 text-center">
                    @if($hasDeficit)
                    <span class="inline-block bg-red-100 text-red-700 font-extrabold text-xs px-2 py-1 rounded-lg">
                        {{ number_format($deficitQty, 0) }}
                    </span>
                    <span class="text-xs text-red-400 mr-0.5">{{ $item['unit'] }}</span>
                    @else
                    <span class="text-green-500 text-xs font-bold">✓</span>
                    @endif
                </td>
                <td class="px-3 py-3 text-center">
                    @if($hasDeficit)
                    <span class="text-red-700 font-extrabold text-sm">{{ number_format($deficitVal, 2) }}</span>
                    <span class="text-xs text-red-400 block">ج.م</span>
                    @else
                    <span class="text-gray-300 text-xs">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 border-t border-gray-200 font-bold">
                    <td colspan="6" class="px-4 py-3 text-right text-xs text-gray-500">إجمالي قيمة الناقص</td>
                    <td colspan="2" class="px-3 py-3 text-center text-base {{ $totalDeficitVal > 0 ? 'text-red-700' : 'text-green-600' }}">
                        {{ number_format($totalDeficitVal, 2) }} ج.م
                    </td>
                </tr>
            </tfoot>
        </table>
        </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- CASH SETTLEMENT --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
        <h2 class="text-base font-bold text-gray-800 border-b border-gray-50 pb-3">تسوية الكاش</h2>

        @php $expectedCash = (float)$trip->total_collected + (float)$trip->cash_custody_amount; @endphp
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 space-y-2">
            <p class="text-xs text-blue-600 font-semibold">الكاش المتوقع تسليمه</p>
            <p class="text-2xl font-extrabold text-blue-800">{{ number_format($expectedCash, 2) }} ج.م</p>
            <div class="text-xs text-blue-500 space-y-0.5 border-t border-blue-200 pt-2">
                <div class="flex justify-between">
                    <span>إجمالي التحصيل</span>
                    <span class="font-bold">{{ number_format($trip->total_collected, 2) }} ج.م</span>
                </div>
                @if($trip->cash_custody_amount > 0)
                <div class="flex justify-between">
                    <span>العهدة النقدية (يجب إعادتها)</span>
                    <span class="font-bold text-amber-600">{{ number_format($trip->cash_custody_amount, 2) }} ج.م</span>
                </div>
                @endif
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">الكاش الفعلي المُسلَّم <span class="text-red-500">*</span></label>
            <input type="number" wire:model.live="cashActual" step="0.01" min="0"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-lg font-bold text-right focus:ring-2 focus:ring-primary-300 @error('cashActual') border-red-400 @enderror">
            @error('cashActual')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        @php $liveCashDeficit = max(0, round($expectedCash - (float)$cashActual, 2)); @endphp
        @if($liveCashDeficit > 0)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
            </div>
            <div>
                <p class="text-xs text-red-600 font-semibold">سيتم تسجيل عجز كاش</p>
                <p class="text-xl font-extrabold text-red-700">{{ number_format($liveCashDeficit, 2) }} ج.م</p>
            </div>
        </div>
        @else
        <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-xs text-green-700 font-semibold text-center">✓ لا يوجد عجز في الكاش</div>
        @endif

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">ملاحظات التسوية</label>
            <textarea wire:model="settlementNotes" rows="3" placeholder="أي ملاحظات حول التسوية..."
                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-right focus:ring-2 focus:ring-primary-300 resize-none"></textarea>
        </div>

        @if($liveCashDeficit > 0 || $totalDeficitVal > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-800">
            <p class="font-bold mb-1">تأثير على تقييم المندوب</p>
            <ul class="list-disc list-inside space-y-0.5 text-amber-700">
                @if($liveCashDeficit > 0)<li>خصم 5 نقاط بسبب عجز الكاش ({{ number_format($liveCashDeficit, 2) }} ج.م)</li>@endif
                @if($totalDeficitVal > 0)<li>خصم 5 نقاط بسبب عجز البضاعة ({{ number_format($totalDeficitVal, 2) }} ج.م)</li>@endif
            </ul>
        </div>
        @endif

        <div class="flex justify-end gap-3 pt-2 border-t border-gray-50">
            <a href="{{ route('trips.show', $trip->id) }}" class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">إلغاء</a>
            <button wire:click="settle" wire:loading.attr="disabled" wire:confirm="هل أنت متأكد من تسوية الرحلة؟ لا يمكن التراجع عن هذا الإجراء."
                class="px-6 py-2.5 text-sm font-bold text-white bg-primary-700 rounded-xl hover:bg-primary-800 transition-colors disabled:opacity-60">
                <span wire:loading.remove>تأكيد التسوية</span>
                <span wire:loading>جارٍ الحفظ...</span>
            </button>
        </div>
    </div>
</div>