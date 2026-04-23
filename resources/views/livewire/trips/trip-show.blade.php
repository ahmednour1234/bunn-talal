<div dir="rtl" class="space-y-5">

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm font-semibold px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm font-semibold px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif

    {{-- ═══ SETTLEMENT APPROVAL BANNER ══════════════════════════════ --}}
    @if($trip->settlement_status === 'pending')
    <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-5 space-y-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-extrabold text-amber-800">بانتظار اعتماد التسوية</h3>
                <p class="text-xs text-amber-600 mt-0.5">
                    قدّم {{ $trip->settler?->name ?? 'مسؤول' }} طلب تسوية بتاريخ {{ $trip->settled_at?->format('Y-m-d H:i') }} — يحتاج موافقة مسؤول أعلى.
                </p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3 text-sm">
                    <div class="bg-white rounded-xl border border-amber-200 p-3 text-center">
                        <p class="text-xs text-gray-400">الكاش المتوقع</p>
                        <p class="font-extrabold text-gray-800">{{ number_format($trip->settlement_cash_expected, 2) }} ج.م</p>
                    </div>
                    <div class="bg-white rounded-xl border border-amber-200 p-3 text-center">
                        <p class="text-xs text-gray-400">الكاش الفعلي</p>
                        <p class="font-extrabold text-green-700">{{ number_format($trip->settlement_cash_actual, 2) }} ج.م</p>
                    </div>
                    <div class="bg-white rounded-xl border border-amber-200 p-3 text-center">
                        <p class="text-xs text-gray-400">عجز الكاش</p>
                        <p class="font-extrabold {{ $trip->settlement_cash_deficit > 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($trip->settlement_cash_deficit, 2) }} ج.م</p>
                    </div>
                    <div class="bg-white rounded-xl border border-amber-200 p-3 text-center">
                        <p class="text-xs text-gray-400">عجز البضاعة</p>
                        <p class="font-extrabold {{ $trip->settlement_product_deficit > 0 ? 'text-orange-600' : 'text-green-600' }}">{{ number_format($trip->settlement_product_deficit, 2) }} ج.م</p>
                    </div>
                </div>
            </div>
        </div>
        @if(auth('admin')->user()?->hasPermission('trips.approve-settlement'))
        <div class="flex flex-col gap-3 border-t border-amber-200 pt-4">
            <div class="flex gap-2">
                <button wire:click="approveSettlement"
                    wire:confirm="هل تؤكد اعتماد هذه التسوية؟ سيتم إرجاع البضاعة للمخزن وإغلاق الرحلة."
                    class="flex-1 bg-green-600 text-white text-sm font-bold py-2.5 rounded-xl hover:bg-green-700 transition-colors">
                    ✓ اعتماد التسوية
                </button>
                <a href="{{ route('trips.settle', $trip->id) }}"
                    class="flex-1 text-center bg-blue-50 border border-blue-200 text-blue-700 text-sm font-bold py-2.5 rounded-xl hover:bg-blue-100 transition-colors">
                    ✎ تعديل التسوية
                </a>
            </div>
            <div class="flex gap-2 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-red-700 mb-1">سبب الرفض (مطلوب عند الرفض)</label>
                    <input type="text" wire:model="rejectionReason" placeholder="مثال: الأرقام غير مطابقة..."
                        class="w-full border border-red-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-red-300">
                </div>
                <button wire:click="rejectSettlement"
                    class="bg-red-600 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-red-700 transition-colors whitespace-nowrap">
                    ✕ رفض
                </button>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ═══ REJECTED SETTLEMENT BANNER ════════════════════════════════ --}}
    @if($trip->settlement_status === 'rejected')
    <div class="bg-red-50 border-2 border-red-300 rounded-2xl p-4 flex items-start gap-3">
        <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </div>
        <div class="flex-1">
            <h3 class="text-sm font-extrabold text-red-800">تم رفض التسوية</h3>
            <p class="text-xs text-red-600 mt-0.5">السبب: {{ $trip->settlement_rejection_reason ?? '—' }}</p>
            @if(auth('admin')->user()?->hasPermission('trips.approve-settlement'))
            <button wire:click="reopenSettlement" wire:confirm="هل تريد إعادة فتح الرحلة وإلغاء بيانات التسوية السابقة؟"
                class="mt-2 text-xs bg-red-100 border border-red-300 text-red-700 px-3 py-1.5 rounded-lg font-semibold hover:bg-red-200">
                إعادة فتح الرحلة
            </button>
            @endif
        </div>
    </div>
    @endif

    {{-- ═══ SETTLED BANNER (approved or legacy settled with no approval status) ════════════════════════════════ --}}
    @if($trip->status === 'settled' && in_array($trip->settlement_status, ['approved', null]))
    <div class="bg-green-50 border border-green-200 rounded-2xl p-4 flex items-start gap-3">
        <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
        </div>
        <div class="flex-1">
            <h3 class="text-sm font-extrabold text-green-800">تمت التسوية ✓</h3>
            <p class="text-xs text-green-600">
                سوّاها: {{ $trip->settler?->name ?? '—' }} | اعتمدها: {{ $trip->approver?->name ?? '—' }} | {{ $trip->settlement_approved_at?->format('Y-m-d H:i') }}
            </p>
            @if(auth('admin')->user()?->hasPermission('trips.approve-settlement'))
            <button wire:click="reopenSettlement" wire:confirm="تحذير: سيتم إلغاء التسوية وإعادة الرحلة نشطة. هل أنت متأكد؟"
                class="mt-2 text-xs bg-green-100 border border-green-300 text-green-700 px-3 py-1.5 rounded-lg font-semibold hover:bg-green-200">
                إعادة فتح التسوية
            </button>
            @endif
        </div>
    </div>
    @endif

    {{-- Header Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-l from-primary-700 to-primary-800 p-5 text-white">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-primary-200 mb-1">رحلة المندوب</p>
                    <h2 class="text-2xl font-extrabold tracking-wide">{{ $trip->trip_number }}</h2>
                    <p class="text-sm text-primary-100 mt-1">{{ $trip->delegate?->name }}</p>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $trip->statusColor() }}">{{ $trip->statusLabel() }}</span>
                    <div class="flex gap-2">
                        <a href="{{ route('trips.pdf', $trip->id) }}" target="_blank"
                            class="inline-flex items-center gap-1 bg-white/20 hover:bg-white/30 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                            PDF
                        </a>
                        @if(!in_array($trip->status, ['settled','cancelled']))
                        <a href="{{ route('trips.edit', $trip->id) }}"
                            class="inline-flex items-center gap-1 bg-white/20 hover:bg-white/30 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                            تعديل
                        </a>
                        @endif
                        @if($trip->status === 'returning' || $trip->status === 'active')
                        <a href="{{ route('trips.settle', $trip->id) }}"
                            class="inline-flex items-center gap-1 bg-amber-400 hover:bg-amber-500 text-amber-900 text-xs font-bold px-3 py-1.5 rounded-lg transition-colors">
                            تسوية
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="grid grid-cols-2 md:grid-cols-5 divide-x divide-x-reverse divide-gray-100 border-t border-gray-50">
            <div class="px-5 py-4 text-center">
                <p class="text-xs text-gray-400 font-medium">العهدة النقدية</p>
                <p class="text-lg font-extrabold {{ $trip->cash_custody_amount > 0 ? 'text-amber-600' : 'text-gray-300' }} mt-0.5">
                    {{ $trip->cash_custody_amount > 0 ? number_format($trip->cash_custody_amount, 0) : '—' }}
                    @if($trip->cash_custody_amount > 0)<span class="text-xs font-normal text-gray-400">ج.م</span>@endif
                </p>
            </div>
            <div class="px-5 py-4 text-center">
                <p class="text-xs text-gray-400 font-medium">إجمالي المفوتر</p>
                <p class="text-lg font-extrabold text-gray-800 mt-0.5">{{ number_format($trip->total_invoiced, 0) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
            </div>
            <div class="px-5 py-4 text-center">
                <p class="text-xs text-gray-400 font-medium">إجمالي المحصّل</p>
                <p class="text-lg font-extrabold text-green-600 mt-0.5">{{ number_format($trip->total_collected, 0) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
            </div>
            <div class="px-5 py-4 text-center">
                <p class="text-xs text-gray-400 font-medium">قيمة المرتجع</p>
                <p class="text-lg font-extrabold text-red-600 mt-0.5">{{ number_format($trip->total_returned_value, 0) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
            </div>
            <div class="px-5 py-4 text-center">
                <p class="text-xs text-gray-400 font-medium">عجز الكاش</p>
                <p class="text-lg font-extrabold {{ $trip->settlement_cash_deficit > 0 ? 'text-red-600' : 'text-gray-400' }} mt-0.5">
                    {{ $trip->settlement_cash_deficit > 0 ? number_format($trip->settlement_cash_deficit, 0).' ج.م' : '—' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Status Actions --}}
    @if(!in_array($trip->status, ['settled','cancelled']))
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-400 mb-3">تغيير حالة الرحلة</p>
        <div class="flex flex-wrap gap-2">
            @if($trip->status === 'draft')
                <button wire:click="changeStatus('active')" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors">تفعيل الرحلة</button>
            @elseif($trip->status === 'active')
                <button wire:click="changeStatus('in_transit')" class="px-4 py-2 bg-purple-600 text-white text-xs font-bold rounded-lg hover:bg-purple-700 transition-colors">بدء التنقل</button>
            @elseif($trip->status === 'in_transit')
                <button wire:click="changeStatus('returning')" class="px-4 py-2 bg-amber-600 text-white text-xs font-bold rounded-lg hover:bg-amber-700 transition-colors">بدء العودة</button>
            @endif
            <button wire:click="changeStatus('cancelled')" onclick="return confirm('هل أنت متأكد من إلغاء الرحلة؟')"
                class="px-4 py-2 bg-red-100 text-red-700 text-xs font-bold rounded-lg hover:bg-red-200 transition-colors">إلغاء الرحلة</button>
        </div>
    </div>
    @endif

    {{-- Deficit Alert --}}
    @if($trip->settlement_cash_deficit > 0 || $trip->settlement_product_deficit > 0)
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-start gap-3">
        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
        </div>
        <div>
            <p class="font-bold text-red-800 text-sm">تنبيه: تم رصد عجز في هذه الرحلة</p>
            @if($trip->settlement_cash_deficit > 0)
            <p class="text-xs text-red-600 mt-0.5">عجز كاش: {{ number_format($trip->settlement_cash_deficit, 2) }} ج.م</p>
            @endif
            @if($trip->settlement_product_deficit > 0)
            <p class="text-xs text-red-600 mt-0.5">عجز بضاعة: {{ number_format($trip->settlement_product_deficit, 2) }}</p>
            @endif
        </div>
    </div>
    @endif

    {{-- Tabs --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex border-b border-gray-100 overflow-x-auto">
            @foreach([
                ['overview',     'نظرة عامة'],
                ['dispatches',   'أوامر الصرف ('.$dispatches->count().')'],
                ['orders',       'فواتير البيع ('.$saleOrders->count().')'],
                ['collections',  'التحصيلات ('.$collections->count().')'],
                ['returns',      'المرتجعات ('.$saleReturns->count().')'],
                ['bookings',     'طلبات الحجز ('.$bookingRequests->count().')'],
            ] as [$key, $label])
            <button wire:click="$set('activeTab','{{ $key }}')"
                class="px-5 py-3 text-xs font-bold whitespace-nowrap transition-colors border-b-2 {{ $activeTab === $key ? 'border-primary-600 text-primary-700 bg-primary-50/50' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>

        <div class="p-5">

            {{-- Overview Tab --}}
            @if($activeTab === 'overview')
            <div class="space-y-5">

                {{-- Cash Custody Card --}}
                <div class="rounded-xl border {{ $trip->cash_custody_amount > 0 ? 'border-amber-200 bg-amber-50' : 'border-gray-200 bg-gray-50' }} p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg {{ $trip->cash_custody_amount > 0 ? 'bg-amber-400/20' : 'bg-gray-200' }} flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 {{ $trip->cash_custody_amount > 0 ? 'text-amber-700' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold {{ $trip->cash_custody_amount > 0 ? 'text-amber-700' : 'text-gray-500' }}">العهدة النقدية للمندوب</p>
                                @if($trip->cash_custody_amount > 0)
                                <p class="text-xl font-extrabold text-amber-800">{{ number_format($trip->cash_custody_amount, 2) }} <span class="text-sm font-normal text-amber-600">ج.م</span></p>
                                @if($trip->custodyTreasury)
                                <p class="text-xs text-amber-600 mt-0.5">من خزنة: <strong>{{ $trip->custodyTreasury->name }}</strong></p>
                                @endif
                                @if($trip->cash_custody_note)
                                <p class="text-xs text-amber-600/80 mt-0.5">{{ $trip->cash_custody_note }}</p>
                                @endif
                                @else
                                <p class="text-sm text-gray-400">لم يتم تسجيل عهدة نقدية لهذه الرحلة</p>
                                @endif
                            </div>
                        </div>
                        @if(!in_array($trip->status, ['settled', 'cancelled']))
                        <button wire:click="$set('showCustodyForm', {{ $showCustodyForm ? 'false' : 'true' }})"
                            class="text-xs font-semibold border px-3 py-1.5 rounded-lg transition-colors
                                   {{ $trip->cash_custody_amount > 0 ? 'border-amber-300 text-amber-700 bg-amber-100 hover:bg-amber-200' : 'border-gray-300 text-gray-600 bg-white hover:bg-gray-100' }}">
                            {{ $trip->cash_custody_amount > 0 ? 'تعديل العهدة' : '+ تسجيل عهدة' }}
                        </button>
                        @endif
                    </div>

                    @if($showCustodyForm)
                    <div class="mt-3 pt-3 border-t {{ $trip->cash_custody_amount > 0 ? 'border-amber-200' : 'border-gray-200' }}">
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">المبلغ (ج.م) *</label>
                                <input type="number" wire:model="custodyAmount" step="0.01" min="0" placeholder="0.00"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-right focus:ring-2 focus:ring-amber-300 @error('custodyAmount') border-red-400 @enderror">
                                @error('custodyAmount')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">الخزنة *</label>
                                <select wire:model="custodyTreasuryId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-right focus:ring-2 focus:ring-amber-300 @error('custodyTreasuryId') border-red-400 @enderror">
                                    <option value="0">-- اختر الخزنة --</option>
                                    @foreach($treasuries as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }} ({{ number_format($t->balance, 0) }} ج.م)</option>
                                    @endforeach
                                </select>
                                @error('custodyTreasuryId')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">ملاحظات</label>
                                <input type="text" wire:model="custodyNote" placeholder="اختياري"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-300">
                            </div>
                        </div>
                        <div class="flex gap-2 justify-end mt-3">
                            <button wire:click="$set('showCustodyForm', false)" class="px-4 py-1.5 text-xs font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">إلغاء</button>
                            <button wire:click="saveCustody" class="px-4 py-1.5 text-xs font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700">حفظ العهدة</button>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Trip Details --}}
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-400 text-xs">الفرع</dt><dd class="font-semibold text-gray-800 mt-0.5">{{ $trip->branch?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">تاريخ البدء</dt><dd class="font-semibold text-gray-800 mt-0.5">{{ $trip->start_date?->format('Y-m-d') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">تاريخ العودة المتوقع</dt><dd class="font-semibold text-gray-800 mt-0.5">{{ $trip->expected_return_date?->format('Y-m-d') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">تاريخ العودة الفعلي</dt><dd class="font-semibold text-gray-800 mt-0.5">{{ $trip->actual_return_date?->format('Y-m-d') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">منشئ الرحلة</dt><dd class="font-semibold text-gray-800 mt-0.5">{{ $trip->admin?->name ?? '—' }}</dd></div>
                    @if($trip->status === 'settled')
                    <div><dt class="text-gray-400 text-xs">تمت التسوية بواسطة</dt><dd class="font-semibold text-gray-800 mt-0.5">{{ $trip->settler?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">تاريخ التسوية</dt><dd class="font-semibold text-gray-800 mt-0.5">{{ $trip->settled_at?->format('Y-m-d H:i') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">الكاش المتوقع</dt><dd class="font-semibold text-gray-800 mt-0.5">{{ number_format($trip->settlement_cash_expected, 2) }} ج.م</dd></div>
                    <div><dt class="text-gray-400 text-xs">الكاش الفعلي</dt><dd class="font-semibold text-green-700 mt-0.5">{{ number_format($trip->settlement_cash_actual, 2) }} ج.م</dd></div>
                    @endif
                    <div class="col-span-2"><dt class="text-gray-400 text-xs">ملاحظات</dt><dd class="text-gray-700 mt-0.5">{{ $trip->notes ?? '—' }}</dd></div>
                    @if($trip->settlement_notes)
                    <div class="col-span-2"><dt class="text-gray-400 text-xs">ملاحظات التسوية</dt><dd class="text-gray-700 mt-0.5">{{ $trip->settlement_notes }}</dd></div>
                    @endif
                </dl>

                {{-- Dispatched Products Summary --}}
                @php
                    $dispatchedProducts = collect();
                    foreach($dispatches as $d) {
                        foreach($d->items as $item) {
                            $pid = $item->product_id;
                            if ($dispatchedProducts->has($pid)) {
                                $existing = $dispatchedProducts->get($pid);
                                $existing['dispatched_qty'] += $item->quantity;
                                $existing['dispatched_value'] += $item->quantity * $item->selling_price;
                                $dispatchedProducts->put($pid, $existing);
                            } else {
                                $dispatchedProducts->put($pid, [
                                    'name'             => $item->product?->name ?? '—',
                                    'unit'             => $item->product?->unit?->name ?? '—',
                                    'unit_symbol'      => $item->product?->unit?->symbol ?? '',
                                    'selling_price'    => (float) $item->selling_price,
                                    'dispatched_qty'   => (float) $item->quantity,
                                    'dispatched_value' => (float) ($item->quantity * $item->selling_price),
                                    'sold_qty'         => 0,
                                ]);
                            }
                        }
                    }
                    // Subtract sold quantities from trip sale orders
                    foreach($saleOrders as $so) {
                        foreach($so->items as $soi) {
                            $pid = $soi->product_id;
                            if ($dispatchedProducts->has($pid)) {
                                $existing = $dispatchedProducts->get($pid);
                                $existing['sold_qty'] += (float) $soi->quantity;
                                $dispatchedProducts->put($pid, $existing);
                            }
                        }
                    }
                @endphp
                @if($dispatchedProducts->isNotEmpty())
                <div class="rounded-xl border border-gray-200 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-100">
                        <h3 class="text-xs font-bold text-gray-700">المنتجات المصروفة مع المندوب</h3>
                        <span class="text-xs text-gray-400">{{ $dispatchedProducts->count() }} منتج</span>
                    </div>
                    <table class="w-full text-sm text-right">
                        <thead>
                            <tr class="text-xs text-gray-400 border-b border-gray-100">
                                <th class="px-4 py-2.5 font-semibold">المنتج</th>
                                <th class="px-3 py-2.5 font-semibold text-center">المصروف</th>
                                <th class="px-3 py-2.5 font-semibold text-center">المُباع</th>
                                <th class="px-3 py-2.5 font-semibold text-center">الحالي معه</th>
                                <th class="px-3 py-2.5 font-semibold text-center">الوحدة</th>
                                <th class="px-3 py-2.5 font-semibold text-center">سعر البيع</th>
                                <th class="px-3 py-2.5 font-semibold text-center">قيمة الحالي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($dispatchedProducts as $pid => $prod)
                            @php
                                $currentQty   = max(0, $prod['dispatched_qty'] - $prod['sold_qty']);
                                $currentValue = $currentQty * $prod['selling_price'];
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-2.5 font-semibold text-gray-800">{{ $prod['name'] }}</td>
                                <td class="px-3 py-2.5 text-center text-gray-600">{{ number_format($prod['dispatched_qty'], 0) }}</td>
                                <td class="px-3 py-2.5 text-center text-primary-600 font-semibold">{{ number_format($prod['sold_qty'], 0) }}</td>
                                <td class="px-3 py-2.5 text-center">
                                    <span class="inline-block font-extrabold text-sm px-2.5 py-1 rounded-lg {{ $currentQty > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-green-50 text-green-700 border border-green-200' }}">
                                        {{ number_format($currentQty, 0) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span class="inline-block bg-gray-100 text-gray-700 text-xs font-semibold px-2.5 py-1 rounded-md">{{ $prod['unit'] }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-center text-green-700 font-semibold text-xs">{{ number_format($prod['selling_price'], 2) }} ج.م</td>
                                <td class="px-3 py-2.5 text-center font-extrabold {{ $currentQty > 0 ? 'text-amber-700' : 'text-gray-400' }}">
                                    {{ number_format($currentValue, 2) }} ج.م
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 border-t border-gray-100">
                                <td class="px-4 py-2.5 text-xs font-bold text-gray-500">الإجمالي</td>
                                <td class="px-3 py-2.5 text-center text-xs font-bold text-gray-600">{{ number_format($dispatchedProducts->sum('dispatched_qty'), 0) }}</td>
                                <td class="px-3 py-2.5 text-center text-xs font-bold text-primary-600">{{ number_format($dispatchedProducts->sum('sold_qty'), 0) }}</td>
                                <td class="px-3 py-2.5 text-center text-xs font-extrabold text-amber-700">
                                    {{ number_format($dispatchedProducts->sum(fn($p) => max(0, $p['dispatched_qty'] - $p['sold_qty'])), 0) }}
                                </td>
                                <td colspan="2"></td>
                                <td class="px-3 py-2.5 text-center text-xs font-extrabold text-amber-700">
                                    {{ number_format($dispatchedProducts->sum(fn($p) => max(0, $p['dispatched_qty'] - $p['sold_qty']) * $p['selling_price']), 2) }} ج.م
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif

            </div>
            @endif

            {{-- Dispatches Tab --}}
            @if($activeTab === 'dispatches')
            @if($dispatches->isEmpty())
            <p class="text-center text-gray-400 py-10 text-sm">لا توجد أوامر صرف مرتبطة بهذه الرحلة</p>
            @else
            <table class="w-full text-sm text-right">
                <thead><tr class="text-xs text-gray-400 border-b border-gray-100">
                    <th class="pb-2 font-semibold">رقم الأمر</th>
                    <th class="pb-2 font-semibold">الفرع</th>
                    <th class="pb-2 font-semibold">التاريخ</th>
                    <th class="pb-2 font-semibold">الحالة</th>
                    <th class="pb-2 font-semibold">الإجمالي</th>
                    <th class="pb-2 font-semibold text-center">الإجراء</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                @foreach($dispatches as $d)
                @php
                    $statusColors = [
                        'pending'        => 'bg-amber-100 text-amber-700',
                        'dispatched'     => 'bg-green-100 text-green-700',
                        'partial_return' => 'bg-blue-100 text-blue-700',
                        'returned'       => 'bg-gray-100 text-gray-600',
                        'settled'        => 'bg-primary-100 text-primary-700',
                    ];
                    $statusLabels = [
                        'pending'        => 'قيد الإعداد',
                        'dispatched'     => 'تم الصرف',
                        'partial_return' => 'مرتجع جزئي',
                        'returned'       => 'مرتجع كامل',
                        'settled'        => 'مُسوَّى',
                    ];
                @endphp
                <tr class="text-gray-700 {{ $d->status === 'pending' ? 'bg-amber-50/30' : '' }}">
                    <td class="py-2.5 font-mono text-xs text-primary-700 font-bold">{{ $d->dispatch_number }}</td>
                    <td class="py-2.5 text-gray-500 text-xs">{{ $d->branch?->name }}</td>
                    <td class="py-2.5 text-gray-500 text-xs">{{ $d->date?->format('Y-m-d') ?? $d->dispatch_date?->format('Y-m-d') }}</td>
                    <td class="py-2.5">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $statusColors[$d->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $statusLabels[$d->status] ?? $d->status }}
                        </span>
                    </td>
                    <td class="py-2.5 font-semibold">{{ number_format($d->expected_sales ?? $d->total_value ?? 0, 0) }} ج.م</td>
                    <td class="py-2.5 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('inventory-dispatches.show', $d->id) }}" wire:navigate
                                class="text-xs font-semibold px-2 py-1 rounded border bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100 transition-colors">
                                عرض
                            </a>
                            @if($d->status === 'pending')
                            <button wire:click="confirmDispatch({{ $d->id }})"
                                wire:confirm="سيتم تأكيد أمر الصرف وخصم الكميات من المخزون. هل أنت متأكد؟"
                                class="text-xs font-bold px-2 py-1 rounded border text-white bg-green-600 border-green-600 hover:bg-green-700 transition-colors">
                                تأكيد الصرف ✓
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
            @endif

            {{-- Sale Orders Tab --}}
            @if($activeTab === 'orders')
            @if($saleOrders->isEmpty())
            <p class="text-center text-gray-400 py-10 text-sm">لا توجد فواتير بيع مرتبطة</p>
            @else
            <table class="w-full text-sm text-right">
                <thead><tr class="text-xs text-gray-400 border-b border-gray-100">
                    <th class="pb-2 font-semibold">رقم الفاتورة</th>
                    <th class="pb-2 font-semibold">العميل</th>
                    <th class="pb-2 font-semibold">التاريخ</th>
                    <th class="pb-2 font-semibold">الحالة</th>
                    <th class="pb-2 font-semibold">الإجمالي</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                @foreach($saleOrders as $o)
                <tr class="text-gray-700">
                    <td class="py-2.5 font-mono text-xs">{{ $o->order_number }}</td>
                    <td class="py-2.5">{{ $o->customer?->name }}</td>
                    <td class="py-2.5 text-gray-500">{{ $o->order_date?->format('Y-m-d') }}</td>
                    <td class="py-2.5">{{ $o->status }}</td>
                    <td class="py-2.5 font-semibold">{{ number_format($o->final_amount, 0) }} ج.م</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
            @endif

            {{-- Collections Tab --}}
            @if($activeTab === 'collections')
            @if($collections->isEmpty())
            <p class="text-center text-gray-400 py-10 text-sm">لا توجد تحصيلات مرتبطة</p>
            @else
            <table class="w-full text-sm text-right">
                <thead><tr class="text-xs text-gray-400 border-b border-gray-100">
                    <th class="pb-2 font-semibold">رقم التحصيل</th>
                    <th class="pb-2 font-semibold">العميل</th>
                    <th class="pb-2 font-semibold">التاريخ</th>
                    <th class="pb-2 font-semibold">المبلغ</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                @foreach($collections as $c)
                <tr class="text-gray-700">
                    <td class="py-2.5 font-mono text-xs">{{ $c->collection_number }}</td>
                    <td class="py-2.5">{{ $c->customer?->name }}</td>
                    <td class="py-2.5 text-gray-500">{{ $c->collection_date?->format('Y-m-d') }}</td>
                    <td class="py-2.5 font-semibold text-green-700">{{ number_format($c->amount, 0) }} ج.م</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
            @endif

            {{-- Returns Tab --}}
            @if($activeTab === 'returns')
            @if($saleReturns->isEmpty())
            <p class="text-center text-gray-400 py-10 text-sm">لا توجد مرتجعات مرتبطة</p>
            @else
            <table class="w-full text-sm text-right">
                <thead><tr class="text-xs text-gray-400 border-b border-gray-100">
                    <th class="pb-2 font-semibold">رقم المرتجع</th>
                    <th class="pb-2 font-semibold">العميل</th>
                    <th class="pb-2 font-semibold">التاريخ</th>
                    <th class="pb-2 font-semibold">الإجمالي</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                @foreach($saleReturns as $r)
                <tr class="text-gray-700">
                    <td class="py-2.5 font-mono text-xs">{{ $r->return_number }}</td>
                    <td class="py-2.5">{{ $r->customer?->name }}</td>
                    <td class="py-2.5 text-gray-500">{{ $r->return_date?->format('Y-m-d') }}</td>
                    <td class="py-2.5 font-semibold text-red-700">{{ number_format($r->final_amount, 0) }} ج.م</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
            @endif

            {{-- Booking Requests Tab --}}
            @if($activeTab === 'bookings')
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-700">طلبات الحجز</h3>
                <a href="{{ route('trips.booking-requests.create', ['trip_id' => $trip->id]) }}" wire:navigate class="text-xs font-semibold text-primary-600 border border-primary-200 px-3 py-1.5 rounded-lg hover:bg-primary-50 transition-colors">+ إضافة طلب</a>
            </div>



            @if($bookingRequests->isEmpty())
            <p class="text-center text-gray-400 py-10 text-sm">لا توجد طلبات حجز</p>
            @else
            <table class="w-full text-sm text-right">
                <thead><tr class="text-xs text-gray-400 border-b border-gray-100">
                    <th class="pb-2 font-semibold">المندوب</th>
                    <th class="pb-2 font-semibold">العميل</th>
                    <th class="pb-2 font-semibold">الحالة</th>
                    <th class="pb-2 font-semibold">التاريخ</th>
                    <th class="pb-2 font-semibold text-center">الإجراء</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                @foreach($bookingRequests as $br)
                <tr class="text-gray-700 {{ $viewingBrId === $br->id ? 'bg-primary-50/40' : '' }}">
                    <td class="py-2.5 font-semibold text-primary-700">{{ $br->delegate?->name ?? '—' }}</td>
                    <td class="py-2.5 text-gray-500 text-xs">
                        {{ $br->customer_name !== 'غير محدد' ? $br->customer_name : '—' }}
                        @if($br->customer_phone)
                        <span class="block text-gray-400">{{ $br->customer_phone }}</span>
                        @endif
                    </td>
                    <td class="py-2.5">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $br->statusColor() }}">{{ $br->statusLabel() }}</span>
                    </td>
                    <td class="py-2.5 text-gray-500 text-xs">{{ $br->created_at->format('Y-m-d') }}</td>
                    <td class="py-2.5">
                        <div class="flex gap-1 justify-center items-center">
                            {{-- View button --}}
                            <button wire:click="viewBooking({{ $br->id }})"
                                class="text-xs font-semibold px-2 py-1 rounded border transition-colors
                                       {{ $viewingBrId === $br->id ? 'bg-primary-100 border-primary-300 text-primary-700' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100' }}">
                                {{ $viewingBrId === $br->id ? 'إخفاء' : 'عرض' }}
                            </button>
                            @if($br->status === 'pending')
                            <button wire:click="updateBookingStatus({{ $br->id }},'confirmed')"
                                wire:confirm="سيتم قبول الطلب وإنشاء أمر صرف تلقائياً. هل أنت متأكد؟"
                                class="text-xs font-semibold text-green-700 bg-green-50 border border-green-200 px-2 py-1 rounded hover:bg-green-100">قبول + صرف</button>
                            <button wire:click="updateBookingStatus({{ $br->id }},'cancelled')" class="text-xs font-semibold text-red-700 bg-red-50 border border-red-200 px-2 py-1 rounded hover:bg-red-100">رفض</button>
                            @endif
                        </div>
                    </td>
                </tr>
                {{-- Detail panel --}}
                @if($viewingBrId === $br->id)
                <tr>
                    <td colspan="5" class="bg-gray-50 px-4 py-4 border-b border-gray-100">
                        <div class="space-y-3">
                            {{-- Info row --}}
                            <div class="flex flex-wrap gap-4 text-xs text-gray-600">
                                <span><strong class="text-gray-500">المندوب:</strong> {{ $br->delegate?->name ?? '—' }}</span>
                                <span><strong class="text-gray-500">العميل:</strong> {{ $br->customer_name }}</span>
                                @if($br->customer_phone)<span><strong class="text-gray-500">الهاتف:</strong> {{ $br->customer_phone }}</span>@endif
                                @if($br->customer_address)<span><strong class="text-gray-500">العنوان:</strong> {{ $br->customer_address }}</span>@endif
                                @if($br->notes)<span><strong class="text-gray-500">ملاحظات:</strong> {{ $br->notes }}</span>@endif
                            </div>
                            {{-- Items table --}}
                            @if($br->items->isNotEmpty())
                            <table class="w-full text-xs bg-white rounded-lg border border-gray-200 overflow-hidden">
                                <thead>
                                    <tr class="bg-gray-100 text-gray-500 font-semibold">
                                        <th class="px-3 py-2 text-right">المنتج</th>
                                        <th class="px-3 py-2 text-right">الكمية</th>
                                        <th class="px-3 py-2 text-right">وحدة القياس</th>
                                        <th class="px-3 py-2 text-right">السعر</th>
                                        <th class="px-3 py-2 text-right">الإجمالي</th>
                                        <th class="px-3 py-2 text-right">ملاحظات</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($br->items as $bri)
                                    <tr>
                                        <td class="px-3 py-2 font-semibold text-gray-700">{{ $bri->product?->name ?? '—' }}</td>
                                        <td class="px-3 py-2">{{ $bri->quantity }}</td>
                                        <td class="px-3 py-2">
                                            @if($bri->unit)
                                            <span class="inline-block bg-gray-100 text-gray-700 text-xs font-semibold px-2 py-0.5 rounded-md">{{ $bri->unit->name }}</span>
                                            @elseif($bri->product?->unit)
                                            <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-md">{{ $bri->product->unit->name }}</span>
                                            @else
                                            <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-green-700 font-semibold">{{ number_format($bri->unit_price, 2) }} ج.م</td>
                                        <td class="px-3 py-2 font-bold text-primary-700">{{ number_format($bri->quantity * $bri->unit_price, 2) }} ج.م</td>
                                        <td class="px-3 py-2 text-gray-400">{{ $bri->notes ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50 font-bold">
                                        <td colspan="4" class="px-3 py-2 text-right text-gray-500">الإجمالي</td>
                                        <td class="px-3 py-2 text-primary-700">{{ number_format($br->items->sum(fn($i) => $i->quantity * $i->unit_price), 2) }} ج.م</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            @else
                            <p class="text-xs text-gray-400 italic">لا توجد منتجات في هذا الطلب</p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endif
                @endforeach
                </tbody>
            </table>
            @endif
            @endif

        </div>
    </div>

    {{-- Back --}}
    <div class="flex justify-start">
        <a href="{{ route('trips.index') }}" class="text-sm text-gray-500 hover:text-primary-700 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
            العودة لقائمة الرحلات
        </a>
    </div>
</div>
