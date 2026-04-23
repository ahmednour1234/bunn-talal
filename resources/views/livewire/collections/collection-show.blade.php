<div class="p-6" dir="rtl">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('collections.index') }}" class="p-2 rounded-xl hover:bg-gray-100 transition-colors text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-800">{{ $collection->collection_number }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">تفاصيل التحصيل</p>
            </div>
        </div>
        @if($collection->status !== 'cancelled' && auth('admin')->user()?->hasPermission('collections.edit'))
        <button wire:click="cancelCollection" wire:confirm="هل أنت متأكد من إلغاء هذا التحصيل؟ سيتم استرداد المبالغ من الطلبات."
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white" style="background:#D9534F">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            إلغاء التحصيل
        </button>
        @endif
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium text-white" style="background:#4CAF50">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium text-white" style="background:#D9534F">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Main Details --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Info Card --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <h2 class="text-sm font-bold text-gray-700 mb-4 pb-2 border-b border-gray-100">بيانات التحصيل</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">المندوب</p>
                        <p class="text-sm font-bold text-gray-800">{{ $collection->delegate?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">العميل</p>
                        <p class="text-sm font-bold text-gray-800">{{ $collection->customer?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">الخزينة</p>
                        <p class="text-sm font-bold text-gray-800">{{ $collection->treasury?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">الفرع</p>
                        <p class="text-sm font-bold text-gray-800">{{ $collection->branch?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">التاريخ</p>
                        <p class="text-sm font-bold text-gray-800">{{ $collection->collection_date?->format('Y-m-d') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">سجّل بواسطة</p>
                        <p class="text-sm font-bold text-gray-800">{{ $collection->admin?->name ?? '—' }}</p>
                    </div>
                </div>
                @if($collection->notes)
                <div class="mt-4 pt-3 border-t border-gray-50">
                    <p class="text-xs text-gray-400 mb-0.5">ملاحظات</p>
                    <p class="text-sm text-gray-700">{{ $collection->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Order Items --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <h2 class="text-sm font-bold text-gray-700 mb-4 pb-2 border-b border-gray-100">الطلبات المحصّلة</h2>
                <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead>
                        <tr class="text-xs font-bold text-white" style="background:#6D4C41">
                            <th class="px-3 py-2 rounded-r-lg">#</th>
                            <th class="px-3 py-2">رقم الطلب</th>
                            <th class="px-3 py-2">تاريخ الطلب</th>
                            <th class="px-3 py-2">إجمالي الطلب</th>
                            <th class="px-3 py-2 rounded-l-lg">المبلغ المحصّل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($collection->items as $i => $item)
                        <tr class="hover:bg-stone-50">
                            <td class="px-3 py-2 text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-3 py-2">
                                @if($item->saleOrder)
                                <a href="{{ route('sale-orders.show', $item->saleOrder->id) }}" class="font-bold hover:underline" style="color:#4E342E">
                                    {{ $item->saleOrder->order_number }}
                                </a>
                                @else
                                —
                                @endif
                            </td>
                            <td class="px-3 py-2 text-gray-500 text-xs">{{ $item->saleOrder?->date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ number_format($item->saleOrder?->total ?? 0, 0) }}</td>
                            <td class="px-3 py-2 font-bold" style="color:#4CAF50">{{ number_format($item->amount, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>

        </div>

        {{-- Summary Sidebar --}}
        <div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 sticky top-4">
                {{-- Status Badge --}}
                @php
                    $sc = [
                        'completed' => ['bg' => '#4CAF50', 'label' => 'مكتمل'],
                        'pending'   => ['bg' => '#F4B400', 'label' => 'معلق'],
                        'cancelled' => ['bg' => '#D9534F', 'label' => 'ملغي'],
                    ][$collection->status] ?? ['bg' => '#9E9E9E', 'label' => $collection->status];
                @endphp
                <div class="text-center mb-5">
                    <span class="inline-block px-4 py-1.5 rounded-full text-sm font-bold text-white" style="background:{{ $sc['bg'] }}">
                        {{ $sc['label'] }}
                    </span>
                </div>

                {{-- Total --}}
                <div class="text-center py-5 rounded-xl mb-4" style="background:#F7F4F1">
                    <p class="text-xs text-gray-500 mb-1">إجمالي التحصيل</p>
                    <p class="text-3xl font-extrabold" style="color:#4E342E">{{ number_format($collection->total_amount, 0) }}</p>
                    <p class="text-sm text-gray-400">ريال يمني</p>
                </div>

                {{-- Counts --}}
                <div class="flex justify-between text-sm">
                    <div class="text-center">
                        <p class="font-bold text-gray-700">{{ $collection->items->count() }}</p>
                        <p class="text-xs text-gray-400">طلب</p>
                    </div>
                    <div class="text-center">
                        <p class="font-bold text-gray-700">{{ $collection->collection_date?->format('Y-m-d') }}</p>
                        <p class="text-xs text-gray-400">تاريخ التحصيل</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
