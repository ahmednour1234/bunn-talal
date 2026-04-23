<div dir="rtl" class="max-w-5xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700">طلب حجز جديد</h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $tripId ? 'إضافة طلب حجز للرحلة' : 'طلب حجز مستقل' }}</p>
        </div>
        <a href="{{ $tripId ? route('trips.show', $tripId) : route('trips.booking-requests') }}" class="text-sm text-gray-500 hover:text-primary-700 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
            عودة
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm font-semibold px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    {{-- Basic Info --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-sm font-bold text-gray-700 mb-4 pb-2 border-b border-gray-100">معلومات الطلب</h2>
        <div class="grid grid-cols-2 gap-4">

            {{-- Delegate --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">المندوب <span class="text-red-500">*</span></label>
                <select wire:model="delegateId" {{ $tripId ? 'disabled' : '' }}
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-right focus:ring-2 focus:ring-primary-300 bg-white @error('delegateId') border-red-400 @enderror">
                    <option value="0">-- اختر المندوب --</option>
                    @foreach($delegates as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
                @error('delegateId')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Trip --}}
            @if(!$tripId)
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">الرحلة (اختياري)</label>
                <select wire:model="tripId"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-right focus:ring-2 focus:ring-primary-300 bg-white">
                    <option value="">بدون رحلة</option>
                    @foreach($trips as $t)
                    <option value="{{ $t->id }}">{{ $t->trip_number }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">الرحلة</label>
                <div class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-600">
                    {{ $trips->firstWhere('id', $tripId)?->trip_number ?? 'رحلة #'.$tripId }}
                </div>
            </div>
            @endif

            {{-- Customer Name (optional) --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">اسم العميل <span class="text-gray-400 font-normal">(اختياري)</span></label>
                <input type="text" wire:model="customerName" placeholder="اسم العميل (اختياري)"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-right focus:ring-2 focus:ring-primary-300 @error('customerName') border-red-400 @enderror">
                @error('customerName')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Phone --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">رقم الهاتف</label>
                <input type="text" wire:model="customerPhone" placeholder="اختياري"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-300">
            </div>

            {{-- Address --}}
            <div class="col-span-2">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">العنوان</label>
                <input type="text" wire:model="customerAddress" placeholder="عنوان العميل (اختياري)"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-300">
            </div>
        </div>
    </div>

    {{-- Products Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-700">المنتجات المطلوبة</h2>
            <button wire:click="addItem" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary-700 border border-primary-200 bg-primary-50 px-3 py-1.5 rounded-lg hover:bg-primary-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                إضافة منتج
            </button>
        </div>

        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-right">المنتج</th>
                    <th class="px-3 py-3 text-right w-20">الكمية</th>
                    <th class="px-3 py-3 text-right w-24">الوحدة</th>
                    <th class="px-3 py-3 text-right w-28">سعر البيع</th>
                    <th class="px-3 py-3 text-right w-24">الضريبة</th>
                    <th class="px-3 py-3 text-right w-28">الإجمالي</th>
                    <th class="px-3 py-3 text-right w-24">ملاحظات</th>
                    <th class="px-2 py-3 w-8"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($items as $i => $item)
                @php
                    $qty         = (float)($item['quantity'] ?? 1);
                    $price       = (float)($item['unit_price'] ?? 0);
                    $taxRate     = (float)($item['tax_rate'] ?? 0);
                    $taxAmt      = $price * ($taxRate / 100);
                    $total       = $qty * ($price + $taxAmt);
                    $maxQty      = (int)($item['max_quantity'] ?? 0);
                    $hasProduct  = !empty($item['product_id']);
                    $overStock   = $hasProduct && $maxQty > 0 && $qty > $maxQty;
                    $noStock     = $hasProduct && $maxQty === 0;
                @endphp
                <tr class="hover:bg-gray-50/50 transition-colors align-top">
                    {{-- Product --}}
                    <td class="px-4 py-2.5">
                        <select wire:model.live="items.{{ $i }}.product_id"
                            class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-xs text-right focus:ring-2 focus:ring-primary-300 bg-white">
                            <option value="">-- اختر منتج --</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    {{-- Quantity --}}
                    <td class="px-3 py-2.5 w-24">
                        <input type="number" wire:model.live="items.{{ $i }}.quantity"
                            step="1" min="1" {{ $maxQty > 0 ? 'max="'.$maxQty.'"' : '' }}
                            class="w-full border rounded-lg px-2.5 py-2 text-xs text-right focus:ring-2 focus:ring-primary-300 {{ $overStock ? 'border-red-400 bg-red-50' : ($noStock ? 'border-amber-300 bg-amber-50' : 'border-gray-200') }}">
                        @if($hasProduct)
                            @if($noStock)
                            <p class="text-xs text-amber-600 mt-0.5 font-semibold">لا يوجد مخزون</p>
                            @elseif($overStock)
                            <p class="text-xs text-red-500 mt-0.5 font-semibold">الحد: {{ $maxQty }}</p>
                            @else
                            <p class="text-xs text-gray-400 mt-0.5">متاح: {{ $maxQty }}</p>
                            @endif
                        @endif
                    </td>
                    {{-- Unit (dropdown from hierarchy) --}}
                    <td class="px-3 py-2.5 w-28">
                        @if($hasProduct && !empty($item['available_units']))
                        <select wire:model.live="items.{{ $i }}.unit_id"
                            class="w-full border border-gray-200 rounded-lg px-2 py-2 text-xs text-right focus:ring-2 focus:ring-primary-300 bg-white">
                            @foreach($item['available_units'] as $u)
                            <option value="{{ $u['id'] }}">{{ $u['name'] }}</option>
                            @endforeach
                        </select>
                        @else
                        <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                    {{-- Price (auto from product, read-only) --}}
                    <td class="px-3 py-2.5 text-center">
                        @if($hasProduct && $price > 0)
                        <span class="inline-block bg-green-50 border border-green-200 text-green-800 text-xs font-bold px-2.5 py-1.5 rounded-lg">
                            {{ number_format($price, 2) }}
                        </span>
                        @else
                        <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                    {{-- Tax (auto from product, read-only) --}}
                    <td class="px-3 py-2.5 text-center">
                        @if($hasProduct && $taxRate > 0)
                        <span class="inline-block bg-blue-50 text-blue-700 text-xs font-semibold px-2 py-1 rounded-lg">{{ $taxRate }}%</span>
                        <p class="text-xs text-gray-400 mt-0.5">{{ number_format($taxAmt, 2) }}</p>
                        @elseif($hasProduct)
                        <span class="text-xs text-gray-400">بدون ضريبة</span>
                        @else
                        <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                    {{-- Total --}}
                    <td class="px-3 py-2.5 text-sm font-extrabold text-primary-700 text-center">
                        @if($hasProduct)
                        {{ number_format($total, 2) }}
                        @else
                        <span class="text-gray-300 font-normal text-xs">—</span>
                        @endif
                    </td>
                    {{-- Notes --}}
                    <td class="px-3 py-2.5">
                        <input type="text" wire:model="items.{{ $i }}.notes" placeholder="—"
                            class="w-full border border-gray-200 rounded-lg px-2 py-2 text-xs focus:ring-2 focus:ring-primary-300">
                    </td>
                    {{-- Delete --}}
                    <td class="px-2 py-2.5 text-center">
                        @if(count($items) > 1)
                        <button wire:click="removeItem({{ $i }})" class="w-6 h-6 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors mx-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            @if(count($items) > 1)
            <tfoot>
                <tr class="bg-gray-50">
                    <td colspan="5" class="px-4 py-3 text-xs font-bold text-gray-500 text-right">الإجمالي الكلي</td>
                    <td class="px-3 py-3 text-sm font-extrabold text-primary-700 text-right">
                        @php $grand = collect($items)->sum(fn($it) => (float)($it['quantity']??1) * ((float)($it['unit_price']??0) * (1 + (float)($it['tax_rate']??0)/100))); @endphp
                        {{ number_format($grand, 2) }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
        </div>
    </div>

    {{-- Notes + Submit --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">ملاحظات الطلب</label>
            <textarea wire:model="notes" rows="2" placeholder="أي ملاحظات إضافية على الطلب..."
                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-right focus:ring-2 focus:ring-primary-300 resize-none"></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-1 border-t border-gray-50">
            <a href="{{ $tripId ? route('trips.show', $tripId) : route('trips.booking-requests') }}"
                class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">إلغاء</a>
            <button wire:click="save" wire:loading.attr="disabled"
                class="px-6 py-2.5 text-sm font-bold text-white bg-primary-700 rounded-xl hover:bg-primary-800 transition-colors disabled:opacity-60">
                <span wire:loading.remove>حفظ الطلب</span>
                <span wire:loading>جارٍ الحفظ...</span>
            </button>
        </div>
    </div>
</div>