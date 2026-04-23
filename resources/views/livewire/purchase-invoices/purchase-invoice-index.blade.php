<div>
    <style>
        .invoice-filters-row {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: nowrap;
        }
        .invoice-filters-row .filter-search {
            flex: 1.4 1 0;
            min-width: 0;
        }
        .invoice-filters-row .filter-item {
            flex: 1 1 0;
            min-width: 0;
        }
        @media (max-width: 992px) {
            .invoice-filters-row {
                flex-wrap: wrap;
            }
            .invoice-filters-row .filter-search,
            .invoice-filters-row .filter-item {
                flex: 1 1 100%;
                width: 100%;
            }
        }
    </style>

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">فواتير المشتريات</h1>
            <p class="text-sm text-gray-500 mt-1">إدارة فواتير المشتريات والمدفوعات</p>
        </div>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('purchase-invoices.export.pdf', ['search' => $search, 'status' => $statusFilter, 'supplier' => $supplierFilter, 'branch' => $branchFilter]) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-primary-700 border border-primary-200 rounded-xl hover:bg-primary-50 transition-colors text-sm font-medium shadow-sm"
            >
                <x-icon name="arrow-down-tray" class="w-4 h-4" />
                PDF
            </a>
            @if(auth('admin')->user()?->hasPermission('purchase-invoices.create'))
                <a href="{{ route('purchase-invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors text-sm font-medium shadow-sm">
                    <x-icon name="plus" class="w-4 h-4" />
                    فاتورة جديدة
                </a>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Filters --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-4">
        <div class="invoice-filters-row">
            <div class="relative filter-search">
                <x-icon name="search" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث برقم الفاتورة أو المورد..."
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
                <select wire:model.live="supplierFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm">
                    <option value="">كل الموردين</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
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

    {{-- Status Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
        @php
            $cardColors = [
                'draft' => 'bg-gray-50 border-gray-200 text-gray-700',
                'confirmed' => 'bg-blue-50 border-blue-200 text-blue-700',
                'partial_paid' => 'bg-amber-50 border-amber-200 text-amber-700',
                'paid' => 'bg-green-50 border-green-200 text-green-700',
                'cancelled' => 'bg-red-50 border-red-200 text-red-700',
            ];
        @endphp
        @foreach($statusLabels as $key => $label)
            <div class="rounded-xl border p-3 {{ $cardColors[$key] ?? 'bg-gray-50 border-gray-200 text-gray-700' }}">
                <p class="text-xs mb-1">{{ $label }}</p>
                <p class="text-xl font-bold">{{ number_format($statusSummary[$key] ?? 0) }}</p>
            </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-primary-50/50">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">رقم الفاتورة</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">المورد</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الفرع</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">التاريخ</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الإجمالي</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">المدفوع</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">المتبقي</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الحالة</th>
                        <th class="px-4 py-3 text-center font-semibold text-primary-700">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-primary-600 font-semibold">{{ $invoice->invoice_number }}</td>
                            <td class="px-4 py-3">{{ $invoice->supplier->name }}</td>
                            <td class="px-4 py-3">{{ $invoice->branch->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $invoice->date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 font-semibold">{{ number_format($invoice->total, 2) }}</td>
                            <td class="px-4 py-3 text-green-600">{{ number_format($invoice->paid_amount, 2) }}</td>
                            <td class="px-4 py-3 text-red-600 font-semibold">{{ number_format($invoice->remaining_amount, 2) }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-gray-100 text-gray-700',
                                        'confirmed' => 'bg-blue-100 text-blue-700',
                                        'partial_paid' => 'bg-amber-100 text-amber-700',
                                        'paid' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$invoice->status] ?? '' }}">
                                    {{ $invoice->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('purchase-invoices.show', $invoice->id) }}" class="p-1.5 text-primary-600 hover:bg-primary-50 rounded-lg" title="عرض">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    </a>
                                    @if(!in_array($invoice->status, ['cancelled', 'paid']))
                                        <button wire:click="cancelInvoice({{ $invoice->id }})" wire:confirm="هل أنت متأكد من إلغاء هذه الفاتورة؟" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg" title="إلغاء">
                                            <x-icon name="x-mark" class="w-4 h-4" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-400">لا توجد فواتير مشتريات</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
