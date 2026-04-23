<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">تقرير حركة المخزون</h1>
        <p class="text-sm text-gray-500 mt-1">عرض حركات المخزون لفرع معين</p>
    </div>

    {{-- Filters --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">الفرع <span class="text-red-500">*</span></label>
                <select wire:model.live="branchFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm">
                    <option value="">اختر الفرع</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">من تاريخ</label>
                <input type="date" wire:model.live="dateFrom" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">إلى تاريخ</label>
                <input type="date" wire:model.live="dateTo" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm">
            </div>
        </div>
    </div>

    @if($report)
        {{-- Summary --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
            <div class="bg-card rounded-2xl shadow-sm border border-green-200 p-4 text-center">
                <div class="text-xs text-green-600 mb-1">المشتريات</div>
                <div class="text-lg font-bold text-green-700">{{ number_format($report['summary']['purchases'] ?? 0) }}</div>
            </div>
            <div class="bg-card rounded-2xl shadow-sm border border-amber-200 p-4 text-center">
                <div class="text-xs text-amber-600 mb-1">المرتجعات</div>
                <div class="text-lg font-bold text-amber-700">{{ number_format($report['summary']['returns'] ?? 0) }}</div>
            </div>
            <div class="bg-card rounded-2xl shadow-sm border border-blue-200 p-4 text-center">
                <div class="text-xs text-blue-600 mb-1">تحويلات واردة</div>
                <div class="text-lg font-bold text-blue-700">{{ number_format($report['summary']['transfers_in'] ?? 0) }}</div>
            </div>
            <div class="bg-card rounded-2xl shadow-sm border border-purple-200 p-4 text-center">
                <div class="text-xs text-purple-600 mb-1">تحويلات صادرة</div>
                <div class="text-lg font-bold text-purple-700">{{ number_format($report['summary']['transfers_out'] ?? 0) }}</div>
            </div>
            <div class="bg-card rounded-2xl shadow-sm border border-red-200 p-4 text-center">
                <div class="text-xs text-red-600 mb-1">الإهلاك</div>
                <div class="text-lg font-bold text-red-700">{{ number_format($report['summary']['depreciations'] ?? 0) }}</div>
            </div>
        </div>

        {{-- Details --}}
        @foreach(['purchases' => ['title' => 'المشتريات', 'color' => 'green'], 'returns' => ['title' => 'المرتجعات', 'color' => 'amber'], 'transfers_in' => ['title' => 'تحويلات واردة', 'color' => 'blue'], 'transfers_out' => ['title' => 'تحويلات صادرة', 'color' => 'purple'], 'depreciations' => ['title' => 'الإهلاك', 'color' => 'red']] as $key => $info)
            @if(count($report[$key] ?? []) > 0)
                <div class="bg-card rounded-2xl shadow-sm border border-{{ $info['color'] }}-100 overflow-hidden mb-4">
                    <div class="p-4 border-b border-gray-100 bg-{{ $info['color'] }}-50/50">
                        <h2 class="text-lg font-semibold text-{{ $info['color'] }}-700">{{ $info['title'] }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-right text-gray-600">التاريخ</th>
                                    <th class="px-4 py-2 text-right text-gray-600">الرقم</th>
                                    <th class="px-4 py-2 text-right text-gray-600">التفاصيل</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($report[$key] as $item)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-4 py-2 text-gray-500">{{ $item->date ?? $item->created_at ?? '-' }}</td>
                                        <td class="px-4 py-2 font-mono text-xs">{{ $item->invoice_number ?? $item->return_number ?? $item->transfer_number ?? $item->depreciation_number ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $item->supplier_name ?? $item->from_branch ?? $item->to_branch ?? $item->reason ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach
    @elseif(!$branchFilter)
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-12 text-center text-gray-400">
            اختر فرعاً لعرض تقرير الحركة
        </div>
    @endif
</div>
