<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-primary-700">خطة تقسيط: {{ $plan->plan_number }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $plan->party_type_label }} — {{ $plan->party_name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('installments.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors text-sm font-medium">
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

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">الإجمالي</div>
            <div class="text-xl font-bold text-gray-700">{{ number_format($plan->total_amount, 2) }}</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <div class="text-xs text-green-600 mb-1">المسدَّد</div>
            <div class="text-xl font-bold text-green-700">{{ number_format($plan->paid_amount, 2) }}</div>
        </div>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-center">
            <div class="text-xs text-amber-600 mb-1">المتبقي</div>
            <div class="text-xl font-bold text-amber-700">{{ number_format($plan->outstanding, 2) }}</div>
        </div>
        <div class="bg-white border border-primary-100 rounded-xl p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">الحالة</div>
            @php $sc = ['active'=>'bg-green-100 text-green-700','completed'=>'bg-blue-100 text-blue-700','cancelled'=>'bg-red-100 text-red-700']; @endphp
            <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold {{ $sc[$plan->status] ?? '' }}">{{ $plan->status_label }}</span>
        </div>
    </div>

    {{-- Plan Info --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
        <h2 class="text-base font-semibold text-primary-700 mb-4">معلومات الخطة</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-400 mb-1">رقم الخطة</p>
                <p class="font-mono text-sm font-semibold text-primary-700">{{ $plan->plan_number }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">{{ $plan->party_type === 'customer' ? 'العميل' : 'المورد' }}</p>
                <p class="font-semibold text-sm">{{ $plan->party_name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">الفرع</p>
                <p class="text-sm">{{ $plan->branch?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">تاريخ البدء</p>
                <p class="text-sm">{{ $plan->start_date->format('Y-m-d') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">الدفعة المقدمة</p>
                <p class="text-sm font-semibold">{{ number_format($plan->down_payment, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">عدد الأقساط</p>
                <p class="text-sm">{{ $plan->installments_count }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">قيمة القسط</p>
                <p class="text-sm font-semibold text-primary-600">{{ number_format($plan->installment_amount, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">الدورية</p>
                <p class="text-sm">{{ $plan->frequency_label }}</p>
            </div>
            @if($plan->reference_type !== 'manual')
                <div>
                    <p class="text-xs text-gray-400 mb-1">المستند المرتبط</p>
                    <p class="text-sm font-mono text-primary-600">{{ $plan->reference_number ?? '—' }}</p>
                </div>
            @endif
            @if($plan->treasury)
                <div>
                    <p class="text-xs text-gray-400 mb-1">الخزينة</p>
                    <p class="text-sm">{{ $plan->treasury->name }}</p>
                </div>
            @endif
            @if($plan->notes)
                <div class="col-span-2 md:col-span-4">
                    <p class="text-xs text-gray-400 mb-1">ملاحظات</p>
                    <p class="text-sm text-gray-600">{{ $plan->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Progress Bar --}}
    @php
        $progress = $plan->total_amount > 0 ? min(100, round(($plan->paid_amount / $plan->total_amount) * 100)) : 0;
    @endphp
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-5 mb-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-700">تقدم السداد</h3>
            <span class="text-sm font-bold text-primary-700">{{ $progress }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="bg-gradient-to-r from-primary-500 to-green-500 h-3 rounded-full transition-all duration-500"
                 style="width: {{ $progress }}%"></div>
        </div>
    </div>

    {{-- Installment Entries Table --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden mb-4">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-primary-700">جدول الأقساط</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-primary-50/50">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">#</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">تاريخ الاستحقاق</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">المبلغ</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">المدفوع</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">المتبقي</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الحالة</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">تاريخ الدفع</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الخزينة</th>
                        @if($plan->status === 'active')
                            <th class="px-4 py-3 text-center font-semibold text-primary-700">دفع</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($plan->entries as $entry)
                        @php
                            $eColors = ['pending'=>'bg-yellow-100 text-yellow-700','partial'=>'bg-amber-100 text-amber-700','paid'=>'bg-green-100 text-green-700','overdue'=>'bg-red-100 text-red-700'];
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors {{ $entry->status === 'overdue' ? 'bg-red-50/30' : '' }}">
                            <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $entry->entry_number }}</td>
                            <td class="px-4 py-3 {{ $entry->status === 'overdue' ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                {{ $entry->due_date->format('Y-m-d') }}
                            </td>
                            <td class="px-4 py-3 font-semibold">{{ number_format($entry->amount, 2) }}</td>
                            <td class="px-4 py-3 text-green-600 font-semibold">{{ number_format($entry->paid_amount, 2) }}</td>
                            <td class="px-4 py-3 text-amber-600">{{ number_format($entry->remaining, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $eColors[$entry->status] ?? '' }}">
                                    {{ $entry->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $entry->paid_at?->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $entry->treasury?->name ?? '—' }}</td>
                            @if($plan->status === 'active')
                                <td class="px-4 py-3 text-center">
                                    @if(in_array($entry->status, ['pending','partial','overdue']))
                                        <button wire:click="openPayForm({{ $entry->id }}, {{ $entry->remaining }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs bg-green-50 text-green-700 rounded-lg hover:bg-green-100">
                                            <x-icon name="banknotes" class="w-3 h-3" />
                                            سدّد
                                        </button>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Payment Modal --}}
    @if($showPayForm)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-bold text-primary-700 mb-4">تسديد القسط</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">المبلغ <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" wire:model.live="payAmount"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-300 @error('payAmount') border-red-400 @enderror">
                        @error('payAmount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الخزينة <span class="text-red-500">*</span></label>
                        <select wire:model="payTreasuryId"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-300 @error('payTreasuryId') border-red-400 @enderror">
                            <option value="">اختر الخزينة</option>
                            @foreach($treasuries as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                        @error('payTreasuryId')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                        <input type="text" wire:model="payNotes" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-300">
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-5">
                    <button wire:click="payInstallment"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium">
                        <x-icon name="check" class="w-4 h-4" />
                        تأكيد الدفع
                    </button>
                    <button wire:click="closePayForm"
                        class="px-5 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors font-medium">
                        إلغاء
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Cancel Plan --}}
    @if($plan->status === 'active' && auth('admin')->user()?->hasPermission('installments.edit'))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mt-2">
            <p class="text-sm text-red-700 mb-3">هل تريد إلغاء هذه الخطة؟</p>
            <button wire:click="cancelPlan" wire:confirm="هل أنت متأكد من إلغاء خطة التقسيط؟"
                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">
                <x-icon name="x-circle" class="w-4 h-4" />
                إلغاء الخطة
            </button>
        </div>
    @endif
</div>
