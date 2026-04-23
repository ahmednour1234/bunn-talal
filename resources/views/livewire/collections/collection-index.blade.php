<div class="p-6" dir="rtl">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-800">التحصيلات</h1>
            <p class="text-sm text-gray-500 mt-0.5">متابعة تحصيلات المناديب من العملاء</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('collections.create'))
        <a href="{{ route('collections.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white shadow-sm transition-all"
           style="background:#4E342E">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            تسجيل تحصيل جديد
        </a>
        @endif
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium text-white" style="background:#4CAF50">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium text-white" style="background:#D9534F">{{ session('error') }}</div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 text-right">
            <p class="text-xs text-gray-500 mb-1">إجمالي المحصّل</p>
            <p class="text-xl font-extrabold" style="color:#4E342E">{{ number_format($summary['total'], 0) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 text-right">
            <p class="text-xs text-gray-500 mb-1">مكتمل</p>
            <p class="text-xl font-extrabold text-emerald-600">{{ $summary['completed'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 text-right">
            <p class="text-xs text-gray-500 mb-1">معلق</p>
            <p class="text-xl font-extrabold" style="color:#F4B400">{{ $summary['pending'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 text-right">
            <p class="text-xs text-gray-500 mb-1">ملغي</p>
            <p class="text-xl font-extrabold" style="color:#D9534F">{{ $summary['cancelled'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 mb-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
            <div class="col-span-2">
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="بحث برقم التحصيل / العميل / المندوب..."
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary-300">
            </div>
            <select wire:model.live="statusFilter" class="border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary-300">
                <option value="">كل الحالات</option>
                @foreach($statusLabels as $val => $label)
                <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="delegateFilter" class="border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary-300">
                <option value="">كل المناديب</option>
                @foreach($delegates as $d)
                <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <select wire:model.live="customerFilter" class="border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary-300">
                <option value="">كل العملاء</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="branchFilter" class="border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary-300">
                <option value="">كل الفروع</option>
                @foreach($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>
            <input wire:model.live="dateFrom" type="date" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-300">
            <input wire:model.live="dateTo" type="date" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-300">
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full table-fixed text-sm text-right">
            <colgroup>
                <col style="width:5%">
                <col style="width:14%">
                <col style="width:16%">
                <col style="width:16%">
                <col style="width:10%">
                <col style="width:12%">
                <col style="width:11%">
                <col style="width:10%">
                <col style="width:6%">
            </colgroup>
            <thead>
                <tr class="text-xs font-bold text-white" style="background:#6D4C41">
                    <th class="px-3 py-3">#</th>
                    <th class="px-3 py-3">رقم التحصيل</th>
                    <th class="px-3 py-3">المندوب</th>
                    <th class="px-3 py-3">العميل</th>
                    <th class="px-3 py-3">التاريخ</th>
                    <th class="px-3 py-3">المبلغ</th>
                    <th class="px-3 py-3">الفرع</th>
                    <th class="px-3 py-3">الحالة</th>
                    <th class="px-3 py-3">إجراء</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($collections as $col)
                @php
                    $sc = [
                        'completed' => ['bg' => '#4CAF50', 'label' => 'مكتمل'],
                        'pending'   => ['bg' => '#F4B400', 'label' => 'معلق'],
                        'cancelled' => ['bg' => '#D9534F', 'label' => 'ملغي'],
                    ][$col->status] ?? ['bg' => '#9E9E9E', 'label' => $col->status];
                @endphp
                <tr class="hover:bg-stone-50 transition-colors">
                    <td class="px-3 py-3 text-gray-400 text-xs">{{ $collections->firstItem() + $loop->index }}</td>
                    <td class="px-3 py-3">
                        <a href="{{ route('collections.show', $col->id) }}" class="font-bold hover:underline" style="color:#4E342E">{{ $col->collection_number }}</a>
                    </td>
                    <td class="px-3 py-3 font-medium text-gray-700 truncate">{{ $col->delegate?->name ?? '—' }}</td>
                    <td class="px-3 py-3 text-gray-700 truncate">{{ $col->customer?->name ?? '—' }}</td>
                    <td class="px-3 py-3 text-gray-500 text-xs">{{ $col->collection_date?->format('Y-m-d') }}</td>
                    <td class="px-3 py-3 font-bold" style="color:#4E342E">{{ number_format($col->total_amount, 0) }}</td>
                    <td class="px-3 py-3 text-gray-500 text-xs truncate">{{ $col->branch?->name ?? '—' }}</td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold text-white" style="background:{{ $sc['bg'] }}">{{ $sc['label'] }}</span>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('collections.show', $col->id) }}" class="p-1.5 rounded-lg hover:bg-primary-50 text-primary-700 transition-colors" title="عرض">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            </a>
                            @if($col->status !== 'cancelled' && auth('admin')->user()?->hasPermission('collections.edit'))
                            <button wire:click="cancelCollection({{ $col->id }})" wire:confirm="هل أنت متأكد من إلغاء هذا التحصيل؟"
                                    class="p-1.5 rounded-lg hover:bg-red-50 text-red-400 transition-colors flex-shrink-0" title="إلغاء">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-gray-400 text-sm">لا توجد تحصيلات</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($collections->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $collections->links() }}
        </div>
        @endif
    </div>
</div>
