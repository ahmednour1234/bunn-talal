<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">خطط التقسيط</h1>
            <p class="text-sm text-gray-500 mt-1">إدارة أقساط العملاء والموردين</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('installments.create'))
            <a href="{{ route('installments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors text-sm font-medium shadow-sm">
                <x-icon name="plus" class="w-4 h-4" />
                خطة جديدة
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-primary-100 p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">الخطط النشطة</p>
            <p class="text-2xl font-bold text-primary-700">{{ $stats['active_plans'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">إجمالي الأقساط</p>
            <p class="text-lg font-bold text-blue-700">{{ number_format($stats['total_amount'], 0) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-green-100 p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">المسدَّد</p>
            <p class="text-lg font-bold text-green-700">{{ number_format($stats['total_paid'], 0) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-amber-100 p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">المتبقي</p>
            <p class="text-lg font-bold text-amber-700">{{ number_format($stats['outstanding'], 0) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-yellow-100 p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">أقساط معلقة</p>
            <p class="text-2xl font-bold text-yellow-700">{{ $stats['pending_entries'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">أقساط متأخرة</p>
            <p class="text-2xl font-bold text-red-700">{{ $stats['overdue_entries'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-4">
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <div style="flex:1.5 1 0;min-width:200px;" class="relative">
                <x-icon name="search" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث برقم الخطة أو اسم العميل/المورد..."
                    class="w-full pr-10 pl-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-primary-300">
            </div>
            <div style="flex:1 1 0;min-width:140px;">
                <select wire:model.live="partyFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm">
                    <option value="">الكل (عميل/مورد)</option>
                    @foreach($partyLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1 1 0;min-width:140px;">
                <select wire:model.live="statusFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm">
                    <option value="">كل الحالات</option>
                    @foreach($statusLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1 1 0;min-width:140px;">
                <select wire:model.live="branchFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm">
                    <option value="">كل الفروع</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1 1 0;min-width:130px;">
                <input type="date" wire:model.live="dateFrom" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm">
            </div>
            <div style="flex:1 1 0;min-width:130px;">
                <input type="date" wire:model.live="dateTo" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm">
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-primary-50/50">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">رقم الخطة</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">النوع</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">العميل / المورد</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الفرع</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">تاريخ البدء</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الإجمالي</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">المتبقي</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الأقساط</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الحالة</th>
                        <th class="px-4 py-3 text-center font-semibold text-primary-700">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($plans as $plan)
                        @php
                            $sc = ['active'=>'bg-green-100 text-green-700','completed'=>'bg-blue-100 text-blue-700','cancelled'=>'bg-red-100 text-red-700'];
                            $pc = ['customer'=>'bg-emerald-50 text-emerald-700','supplier'=>'bg-indigo-50 text-indigo-700'];
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-primary-600 font-semibold">{{ $plan->plan_number }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $pc[$plan->party_type] ?? '' }}">{{ $plan->party_type_label }}</span>
                            </td>
                            <td class="px-4 py-3 font-medium">{{ $plan->party_name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $plan->branch?->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $plan->start_date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 font-semibold">{{ number_format($plan->total_amount, 2) }}</td>
                            <td class="px-4 py-3 font-semibold text-amber-600">{{ number_format($plan->outstanding, 2) }}</td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ $plan->installments_count }} × {{ number_format($plan->installment_amount, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $sc[$plan->status] ?? '' }}">{{ $plan->status_label }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('installments.show', $plan->id) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs bg-primary-50 text-primary-700 rounded-lg hover:bg-primary-100">
                                        <x-icon name="eye" class="w-3 h-3" />
                                        عرض
                                    </a>
                                    @if($plan->status === 'active' && auth('admin')->user()?->hasPermission('installments.edit'))
                                        <button wire:click="cancelPlan({{ $plan->id }})" wire:confirm="هل تريد إلغاء هذه الخطة؟"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs bg-red-50 text-red-700 rounded-lg hover:bg-red-100">
                                            <x-icon name="x-mark" class="w-3 h-3" />
                                            إلغاء
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-gray-400">
                                <x-icon name="inbox" class="w-10 h-10 mx-auto mb-2 opacity-30" />
                                لا توجد خطط تقسيط
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($plans->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $plans->links() }}</div>
        @endif
    </div>
</div>
