<div class="p-6" dir="rtl">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('collections.index') }}" class="p-2 rounded-xl hover:bg-gray-100 transition-colors text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-800">تسجيل تحصيل جديد</h1>
            <p class="text-sm text-gray-500">تحصيل مبالغ من العملاء عبر المناديب</p>
        </div>
    </div>

    @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium text-white" style="background:#D9534F">{{ session('error') }}</div>
    @endif

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Left Column: Collection Details --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Basic Info --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <h2 class="text-sm font-bold text-gray-700 mb-4 pb-2 border-b border-gray-100">بيانات التحصيل</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Delegate --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">المندوب</label>
                            <select wire:model.live="delegateId" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary-300">
                                <option value="">-- بدون مندوب (اختياري) --</option>
                                @foreach($delegates as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Customer --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">العميل <span class="text-red-500">*</span></label>
                            <select wire:model.live="customerId" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary-300 @error('customerId') border-red-400 @enderror">
                                <option value="">-- اختر العميل --</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('customerId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Treasury --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">الخزينة <span class="text-red-500">*</span></label>
                            <select wire:model.live="treasuryId" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary-300 @error('treasuryId') border-red-400 @enderror">
                                <option value="">-- اختر الخزينة --</option>
                                @foreach($treasuries as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                            @error('treasuryId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Branch --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">الفرع</label>
                            <select wire:model.live="branchId" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary-300">
                                <option value="">-- اختر الفرع (اختياري) --</option>
                                @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Date --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">تاريخ التحصيل <span class="text-red-500">*</span></label>
                            <input wire:model.live="collectionDate" type="date" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-300 @error('collectionDate') border-red-400 @enderror">
                            @error('collectionDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">الحالة</label>
                            <select wire:model.live="status" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary-300">
                                <option value="completed">مكتمل</option>
                                <option value="pending">معلق</option>
                            </select>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mt-4">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">ملاحظات</label>
                        <textarea wire:model.live="notes" rows="2" placeholder="ملاحظات اختيارية..."
                                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary-300 resize-none"></textarea>
                    </div>
                </div>

                {{-- Available Orders --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <h2 class="text-sm font-bold text-gray-700 mb-4 pb-2 border-b border-gray-100">الطلبات المتاحة للتحصيل</h2>

                    @if(!$customerId)
                    <div class="py-8 text-center text-gray-400 text-sm">
                        اختر العميل أولاً لعرض فواتير البيع غير المحصّلة
                    </div>
                    @elseif(empty($availableOrders))
                    <div class="py-8 text-center text-gray-400 text-sm">
                        لا توجد طلبات مستحقة لهذا المندوب / العميل
                    </div>
                    @else
                    <div class="space-y-2">
                        @foreach($availableOrders as $order)
                        @php $alreadyAdded = collect($items)->pluck('sale_order_id')->contains($order['id']); @endphp
                        <div class="flex items-center justify-between p-3 rounded-xl border {{ $alreadyAdded ? 'border-emerald-200 bg-emerald-50' : 'border-gray-100 hover:border-primary-200 hover:bg-primary-50' }} transition-colors">
                            <div class="text-right">
                                <p class="text-sm font-bold" style="color:#4E342E">{{ $order['order_number'] }}</p>
                                <p class="text-xs text-gray-500">التاريخ: {{ $order['date'] }} | الإجمالي: {{ number_format($order['total'], 0) }} | المتبقي: <span class="font-bold text-red-500">{{ number_format($order['remaining'], 0) }}</span></p>
                            </div>
                            @if($alreadyAdded)
                            <span class="text-xs font-bold text-emerald-600 px-2 py-1 rounded-lg bg-emerald-100">تمت الإضافة</span>
                            @else
                            <button type="button" wire:click="addOrder({{ $order['id'] }})"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-colors" style="background:#4E342E">
                                إضافة
                            </button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

            </div>

            {{-- Right Column: Items Summary --}}
            <div class="space-y-5">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 sticky top-4">
                    <h2 class="text-sm font-bold text-gray-700 mb-4 pb-2 border-b border-gray-100">الطلبات المضافة</h2>

                    @error('items') <p class="text-xs text-red-500 mb-3">{{ $message }}</p> @enderror

                    @if(empty($items))
                    <div class="py-4 text-center text-gray-400 text-sm">لم تتم إضافة فواتير بعد</div>
                    {{-- Manual amount when no orders --}}
                    <div class="mt-3">
                        <label class="block text-xs font-semibold text-gray-600 mb-1 text-right">المبلغ المحصّل <span class="text-red-500">*</span></label>
                        <input wire:model.live="manualAmount" type="number" step="0.01" min="0.01" placeholder="0.00"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary-300 @error('manualAmount') border-red-400 @enderror">
                        @error('manualAmount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    @else
                    <div class="space-y-3">
                        @foreach($items as $index => $item)
                        <div class="border border-gray-100 rounded-xl p-3">
                            <div class="flex items-center justify-between mb-2">
                                <button type="button" wire:click="removeOrder({{ $index }})" class="text-red-400 hover:text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                                <p class="text-xs font-bold" style="color:#4E342E">{{ $item['order_number'] }}</p>
                            </div>
                            <p class="text-xs text-gray-400 mb-2 text-right">متبقي: {{ number_format($item['remaining'], 0) }} ج.م</p>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1 text-right">المبلغ المحصّل</label>
                                <input wire:model.live="items.{{ $index }}.amount" type="number" step="0.01" min="0.01" max="{{ $item['remaining'] }}"
                                       class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary-300">
                                @error("items.{$index}.amount") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-lg font-extrabold" style="color:#4E342E">{{ number_format($totalAmount, 2) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
                        <p class="text-xs text-gray-500">الإجمالي</p>
                    </div>
                    @endif

                    {{-- Save Button --}}
                    <button type="submit"
                            class="mt-5 w-full py-3 rounded-xl text-sm font-bold text-white shadow-sm transition-all"
                            style="background:#4E342E"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>حفظ التحصيل</span>
                        <span wire:loading>جارٍ الحفظ...</span>
                    </button>
                    <a href="{{ route('collections.index') }}"
                       class="mt-2 block text-center py-2.5 rounded-xl text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                        إلغاء
                    </a>
                </div>
            </div>

        </div>
    </form>
</div>
