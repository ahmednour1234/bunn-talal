<div>
    <style>
        .filters-row { display:flex; gap:12px; align-items:center; flex-wrap:nowrap; }
        .filters-row .filter-search { flex:1.4 1 0; min-width:0; }
        .filters-row .filter-item { flex:1 1 0; min-width:0; }
        @media (max-width:992px) { .filters-row { flex-wrap:wrap; } .filters-row .filter-search, .filters-row .filter-item { flex:1 1 100%; width:100%; } }
    </style>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">مرتجعات المبيعات</h1>
            <p class="text-sm text-gray-500 mt-1">إدارة مرتجعات المبيعات</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('sale-returns.create'))
            <a href="{{ route('sale-returns.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors text-sm font-medium shadow-sm">
                <x-icon name="plus" class="w-4 h-4" />
                مرتجع جديد
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
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-primary-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                <x-icon name="document-text" class="w-5 h-5 text-primary-600" />
            </div>
            <div>
                <p class="text-xs text-gray-500">إجمالي المرتجعات</p>
                <p class="text-xl font-bold text-primary-700">{{ $stats['count'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-yellow-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center">
                <x-icon name="clock" class="w-5 h-5 text-yellow-600" />
            </div>
            <div>
                <p class="text-xs text-gray-500">في الانتظار</p>
                <p class="text-xl font-bold text-yellow-700">{{ $stats['status_counts']['pending'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-green-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                <x-icon name="check-circle" class="w-5 h-5 text-green-600" />
            </div>
            <div>
                <p class="text-xs text-gray-500">تم الاسترداد</p>
                <p class="text-xl font-bold text-green-700">{{ $stats['status_counts']['refunded'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                <x-icon name="banknotes" class="w-5 h-5 text-blue-600" />
            </div>
            <div>
                <p class="text-xs text-gray-500">إجمالي المبالغ المستردة</p>
                <p class="text-xl font-bold text-blue-700">{{ number_format($stats['refund'], 2) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-4">
        <div class="filters-row">
            <div class="relative filter-search">
                <x-icon name="search" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث برقم المرتجع أو العميل..."
                    class="w-full pr-10 pl-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-primary-300">
            </div>
            <div class="filter-item">
                <select wire:model.live="statusFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm">
                    <option value="">كل الحالات</option>
                    @foreach($statusLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-item">
                <select wire:model.live="customerFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm">
                    <option value="">كل العملاء</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-item">
                <select wire:model.live="branchFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm">
                    <option value="">كل الفروع</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-primary-50/50">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">رقم المرتجع</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">طلب المبيعات</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">العميل</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الفرع</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">التاريخ</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">المبلغ المسترد</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الحالة</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">بيان</th>
                        <th class="px-4 py-3 text-center font-semibold text-primary-700">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($returns as $return)
                        @php
                            $statusColors = ['pending'=>'bg-yellow-100 text-yellow-700','confirmed'=>'bg-blue-100 text-blue-700','refunded'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700'];
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-primary-600 font-semibold">{{ $return->return_number }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $return->order->order_number }}</td>
                            <td class="px-4 py-3">{{ $return->customer->name }}</td>
                            <td class="px-4 py-3">{{ $return->branch->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $return->date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 font-semibold text-green-600">{{ number_format($return->refund_amount, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$return->status] ?? '' }}">{{ $return->status_label }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('sale-returns.show', $return->id) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs bg-primary-50 text-primary-700 rounded-lg hover:bg-primary-100">
                                        <x-icon name="eye" class="w-3 h-3" />
                                        عرض
                                    </a>
                                    <a href="{{ route('sale-returns.pdf', $return->id) }}" target="_blank"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs bg-red-50 text-red-700 rounded-lg hover:bg-red-100">
                                        <x-icon name="document-arrow-down" class="w-3 h-3" />
                                        PDF
                                    </a>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if($return->status === 'pending' && auth('admin')->user()?->hasPermission('sale-returns.create'))
                                        <button type="button" wire:click="confirmReturn({{ $return->id }})" wire:confirm="هل تريد تأكيد هذا المرتجع؟"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs bg-green-50 text-green-700 rounded-lg hover:bg-green-100">
                                            <x-icon name="check" class="w-3 h-3" />
                                            تأكيد
                                        </button>
                                        <button type="button" wire:click="cancelReturn({{ $return->id }})" wire:confirm="هل تريد إلغاء هذا المرتجع؟"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs bg-red-50 text-red-700 rounded-lg hover:bg-red-100">
                                            <x-icon name="x-mark" class="w-3 h-3" />
                                            إلغاء
                                        </button>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                                <x-icon name="inbox" class="w-10 h-10 mx-auto mb-2 opacity-30" />
                                لا توجد مرتجعات مبيعات
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($returns->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $returns->links() }}</div>
        @endif
    </div>
</div>
