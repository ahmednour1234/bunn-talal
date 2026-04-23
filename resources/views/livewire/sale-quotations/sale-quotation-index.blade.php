<div>
    <style>
        .filters-row { display:flex; gap:12px; align-items:center; flex-wrap:nowrap; }
        .filters-row .filter-search { flex:1.4 1 0; min-width:0; }
        .filters-row .filter-item { flex:1 1 0; min-width:0; }
        @media (max-width:992px) { .filters-row { flex-wrap:wrap; } .filters-row .filter-search, .filters-row .filter-item { flex:1 1 100%; width:100%; } }
    </style>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">عروض الأسعار</h1>
            <p class="text-sm text-gray-500 mt-1">إدارة عروض الأسعار للعملاء</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('sale-quotations.create'))
            <a href="{{ route('sale-quotations.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors text-sm font-medium shadow-sm">
                <x-icon name="plus" class="w-4 h-4" />
                عرض سعر جديد
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-4">
        <div class="filters-row">
            <div class="relative filter-search">
                <x-icon name="search" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث برقم العرض أو العميل..."
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
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">رقم العرض</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">العميل</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الفرع</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">التاريخ</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">ينتهي</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الإجمالي</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الحالة</th>
                        <th class="px-4 py-3 text-center font-semibold text-primary-700">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($quotations as $quotation)
                        @php
                            $statusColors = ['draft'=>'bg-gray-100 text-gray-700','sent'=>'bg-blue-100 text-blue-700','accepted'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700','expired'=>'bg-orange-100 text-orange-700'];
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-primary-600 font-semibold">{{ $quotation->quotation_number }}</td>
                            <td class="px-4 py-3">{{ $quotation->customer->name }}</td>
                            <td class="px-4 py-3">{{ $quotation->branch->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $quotation->date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $quotation->expiry_date?->format('Y-m-d') ?? '-' }}</td>
                            <td class="px-4 py-3 font-semibold">{{ number_format($quotation->total, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$quotation->status] ?? '' }}">{{ $quotation->status_label }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('sale-quotations.show', $quotation->id) }}" class="p-1.5 text-primary-600 hover:bg-primary-50 rounded-lg" title="عرض">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    </a>
                                    <a href="{{ route('sale-quotations.pdf', $quotation->id) }}" target="_blank" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="PDF">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    </a>
                                    @if(in_array($quotation->status, ['draft','sent']) && auth('admin')->user()?->hasPermission('sale-quotations.edit'))
                                        <button type="button" wire:click="cancelQuotation({{ $quotation->id }})" wire:confirm="هل تريد رفض هذا العرض؟"
                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg" title="رفض">
                                            <x-icon name="x-circle" class="w-4 h-4" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                <x-icon name="inbox" class="w-10 h-10 mx-auto mb-2 opacity-30" />
                                لا توجد عروض أسعار
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotations->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $quotations->links() }}</div>
        @endif
    </div>
</div>
