<div>
    <style>
        .returns-filters-row {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: nowrap;
        }
        .returns-filters-row .filter-search {
            flex: 1.3 1 0;
            min-width: 0;
        }
        .returns-filters-row .filter-item {
            flex: 1 1 0;
            min-width: 0;
        }
        @media (max-width: 992px) {
            .returns-filters-row {
                flex-wrap: wrap;
            }
            .returns-filters-row .filter-search,
            .returns-filters-row .filter-item {
                width: 100%;
                flex: 1 1 100%;
            }
        }

        .returns-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .returns-stat-card {
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            padding: 24px;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .returns-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.10);
        }

        .returns-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }

        .returns-stat-title {
            font-size: 12px;
            margin-bottom: 8px;
            font-weight: 600;
            opacity: .85;
        }

        .returns-stat-value {
            font-size: 30px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        @media (max-width: 1100px) {
            .returns-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .returns-stats-grid {
                grid-template-columns: 1fr;
            }
            .returns-stat-value {
                font-size: 24px;
            }
        }
    </style>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">مرتجعات المشتريات</h1>
            <p class="text-sm text-gray-500 mt-1">إدارة مرتجعات فواتير المشتريات</p>
        </div>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('purchase-returns.export.pdf', ['search' => $search, 'status' => $statusFilter, 'supplier' => $supplierFilter]) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-primary-700 border border-primary-200 rounded-xl hover:bg-primary-50 transition-colors text-sm font-medium shadow-sm"
            >
                <x-icon name="arrow-down-tray" class="w-4 h-4" />
                PDF
            </a>
            @if(auth('admin')->user()?->hasPermission('purchase-returns.create'))
                <a href="{{ route('purchase-returns.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors text-sm font-medium shadow-sm">
                    <x-icon name="plus" class="w-4 h-4" />
                    مرتجع جديد
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Filters --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-4">
        <div class="returns-filters-row">
            <div class="relative filter-search">
                <x-icon name="search" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث..."
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
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="mb-5">
        <div class="returns-stats-grid">
        <div class="returns-stat-card bg-gradient-to-br from-white to-slate-50/70">
            <div class="returns-stat-icon bg-slate-100">
                <x-icon name="arrow-uturn-left" class="w-5 h-5 text-slate-600" />
            </div>
            <p class="returns-stat-title text-slate-500">إجمالي المرتجعات</p>
            <p class="returns-stat-value text-primary-700">{{ number_format($summaryStats['count'] ?? 0) }}</p>
        </div>
        <div class="returns-stat-card bg-gradient-to-br from-blue-50 to-blue-100/40">
            <div class="returns-stat-icon bg-blue-100">
                <x-icon name="banknotes" class="w-5 h-5 text-blue-600" />
            </div>
            <p class="returns-stat-title text-blue-600">إجمالي القيم</p>
            <p class="returns-stat-value text-blue-700">{{ number_format($summaryStats['subtotal'] ?? 0, 2) }}</p>
        </div>
        <div class="returns-stat-card bg-gradient-to-br from-red-50 to-red-100/40">
            <div class="returns-stat-icon bg-red-100">
                <x-icon name="exclamation-triangle" class="w-5 h-5 text-red-600" />
            </div>
            <p class="returns-stat-title text-red-600">إجمالي الخسائر</p>
            <p class="returns-stat-value text-red-700">{{ number_format($summaryStats['loss'] ?? 0, 2) }}</p>
        </div>
        <div class="returns-stat-card bg-gradient-to-br from-emerald-50 to-emerald-100/40">
            <div class="returns-stat-icon bg-emerald-100">
                <x-icon name="check-circle" class="w-5 h-5 text-emerald-600" />
            </div>
            <p class="returns-stat-title text-emerald-600">إجمالي المسترد</p>
            <p class="returns-stat-value text-emerald-700">{{ number_format($summaryStats['refund'] ?? 0, 2) }}</p>
        </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-primary-50/50">
                    <tr>
                        <th class="px-6 py-5 text-right font-semibold text-primary-700">رقم المرتجع</th>
                        <th class="px-6 py-5 text-right font-semibold text-primary-700">رقم الفاتورة</th>
                        <th class="px-6 py-5 text-right font-semibold text-primary-700">المورد</th>
                        <th class="px-6 py-5 text-right font-semibold text-primary-700">الفرع</th>
                        <th class="px-6 py-5 text-right font-semibold text-primary-700">التاريخ</th>
                        <th class="px-6 py-5 text-right font-semibold text-primary-700">الإجمالي</th>
                        <th class="px-6 py-5 text-right font-semibold text-primary-700">الخسائر</th>
                        <th class="px-6 py-5 text-right font-semibold text-primary-700">المسترد</th>
                        <th class="px-6 py-5 text-right font-semibold text-primary-700">الحالة</th>
                        <th class="px-6 py-5 text-center font-semibold text-primary-700">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($returns as $return)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-5 font-mono text-xs text-primary-600 font-semibold">{{ $return->return_number }}</td>
                            <td class="px-6 py-5 font-mono text-xs">{{ $return->invoice?->invoice_number ?? '-' }}</td>
                            <td class="px-6 py-5">{{ $return->supplier->name }}</td>
                            <td class="px-6 py-5">{{ $return->branch->name }}</td>
                            <td class="px-6 py-5 text-gray-500">{{ $return->date->format('Y-m-d') }}</td>
                            <td class="px-6 py-5 font-semibold">{{ number_format($return->subtotal, 2) }}</td>
                            <td class="px-6 py-5 text-red-600">{{ number_format($return->loss_amount, 2) }}</td>
                            <td class="px-6 py-5 text-green-600 font-semibold">{{ number_format($return->refund_amount, 2) }}</td>
                            <td class="px-6 py-5">
                                @php
                                    $statusColors = ['pending'=>'bg-amber-100 text-amber-700','confirmed'=>'bg-blue-100 text-blue-700','refunded'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700'];
                                @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$return->status] ?? '' }}">{{ $return->status_label }}</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('purchase-returns.show.pdf', $return->id) }}" target="_blank"
                                       class="p-1.5 text-primary-600 hover:bg-primary-50 rounded-lg" title="PDF الفاتورة">
                                        <x-icon name="document-arrow-down" class="w-4 h-4" />
                                    </a>
                                    @if($return->status === 'pending')
                                        <button wire:click="confirmReturn({{ $return->id }})" wire:confirm="هل أنت متأكد من تأكيد المرتجع؟" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg" title="تأكيد">
                                            <x-icon name="check" class="w-4 h-4" />
                                        </button>
                                        <button wire:click="cancelReturn({{ $return->id }})" wire:confirm="هل أنت متأكد من إلغاء المرتجع؟" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg" title="إلغاء">
                                            <x-icon name="x-mark" class="w-4 h-4" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-6 py-16 text-center text-gray-400">لا توجد مرتجعات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">{{ $returns->links() }}</div>
    </div>
</div>
