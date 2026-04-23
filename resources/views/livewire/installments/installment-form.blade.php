<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">خطة تقسيط جديدة</h1>
            <p class="text-sm text-gray-500 mt-1">إنشاء جدول أقساط تلقائي</p>
        </div>
        <a href="{{ route('installments.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors text-sm font-medium">
            <x-icon name="arrow-right" class="w-4 h-4" />
            العودة للقائمة
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    <form wire:submit="save" class="space-y-5">

        {{-- Party Type --}}
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6">
            <h2 class="text-base font-semibold text-primary-700 mb-4">نوع الطرف</h2>
            <div class="grid grid-cols-2 gap-4 max-w-xs">
                @foreach($partyLabels as $key => $label)
                    <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all
                        {{ $partyType === $key ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300' }}">
                        <input type="radio" wire:model.live="partyType" value="{{ $key }}" class="accent-primary-600">
                        <span class="font-medium text-sm">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Party Selection --}}
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6">
            <h2 class="text-base font-semibold text-primary-700 mb-4">بيانات الطرف</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($partyType === 'customer')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">العميل <span class="text-red-500">*</span></label>
                        <select wire:model.live="customerId" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 @error('customerId') border-red-400 @enderror">
                            <option value="">اختر العميل</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('customerId')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Reference: Sale Order --}}
                    @if($customerId)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ربط بطلب مبيعات (اختياري)</label>
                            <select wire:model.live="referenceType" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-sm mb-2">
                                <option value="manual">بدون ربط</option>
                                <option value="sale_order">طلب مبيعات</option>
                            </select>
                            @if($referenceType === 'sale_order' && !empty($referenceOptions))
                                <select wire:model.live="referenceId" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-sm">
                                    <option value="">اختر الطلب</option>
                                    @foreach($referenceOptions as $opt)
                                        <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                                    @endforeach
                                </select>
                            @elseif($referenceType === 'sale_order')
                                <p class="text-xs text-gray-400">لا توجد طلبات غير مسددة لهذا العميل</p>
                            @endif
                        </div>
                    @endif
                @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">المورد <span class="text-red-500">*</span></label>
                        <select wire:model.live="supplierId" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 @error('supplierId') border-red-400 @enderror">
                            <option value="">اختر المورد</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('supplierId')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Reference: Purchase Invoice --}}
                    @if($supplierId)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ربط بفاتورة مشتريات (اختياري)</label>
                            <select wire:model.live="referenceType" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-sm mb-2">
                                <option value="manual">بدون ربط</option>
                                <option value="purchase_invoice">فاتورة مشتريات</option>
                            </select>
                            @if($referenceType === 'purchase_invoice' && !empty($referenceOptions))
                                <select wire:model.live="referenceId" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-sm">
                                    <option value="">اختر الفاتورة</option>
                                    @foreach($referenceOptions as $opt)
                                        <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                                    @endforeach
                                </select>
                            @elseif($referenceType === 'purchase_invoice')
                                <p class="text-xs text-gray-400">لا توجد فواتير غير مسددة لهذا المورد</p>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Plan Details --}}
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6">
            <h2 class="text-base font-semibold text-primary-700 mb-4">تفاصيل الخطة</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الفرع <span class="text-red-500">*</span></label>
                    <select wire:model="branchId" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 @error('branchId') border-red-400 @enderror">
                        <option value="">اختر الفرع</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branchId')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الخزينة (افتراضية)</label>
                    <select wire:model="treasuryId" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                        <option value="">— اختياري —</option>
                        @foreach($treasuries as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ البدء <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="startDate" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 @error('startDate') border-red-400 @enderror">
                    @error('startDate')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">المبلغ الإجمالي <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" wire:model.live="totalAmount"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 @error('totalAmount') border-red-400 @enderror">
                    @error('totalAmount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الدفعة المقدمة</label>
                    <input type="number" step="0.01" min="0" wire:model.live="downPayment"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">عدد الأقساط <span class="text-red-500">*</span></label>
                    <input type="number" min="1" max="120" wire:model.live="installmentsCount"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 @error('installmentsCount') border-red-400 @enderror">
                    @error('installmentsCount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">دورية السداد <span class="text-red-500">*</span></label>
                    <select wire:model="frequency" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                        @foreach($frequencyLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                    <input type="text" wire:model="notes" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300">
                </div>
            </div>
        </div>

        {{-- Live Preview --}}
        @if($totalAmount && $installmentsCount)
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-2xl p-5 text-white">
                <h3 class="font-semibold mb-3">ملخص الخطة</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div>
                        <p class="text-white/70 text-xs mb-1">الإجمالي</p>
                        <p class="text-xl font-bold">{{ number_format((float)$totalAmount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-white/70 text-xs mb-1">الدفعة المقدمة</p>
                        <p class="text-xl font-bold">{{ number_format((float)$downPayment, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-white/70 text-xs mb-1">المبلغ المقسط</p>
                        <p class="text-xl font-bold">{{ number_format($previewRemaining, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-white/70 text-xs mb-1">القسط الشهري</p>
                        <p class="text-xl font-bold">{{ number_format($previewInstallmentAmount, 2) }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Buttons --}}
        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors font-medium">
                <x-icon name="check" class="w-4 h-4" />
                إنشاء خطة التقسيط
            </button>
            <a href="{{ route('installments.index') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors font-medium text-sm">
                إلغاء
            </a>
        </div>
    </form>
</div>
