<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">تفاصيل طلب الإهلاك</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $depreciation->depreciation_number }}</p>
        </div>
        <a href="{{ route('product-depreciations.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors text-sm font-medium">العودة للقائمة</a>
        <a href="{{ route('product-depreciations.pdf', $depreciation->id) }}" target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors text-sm font-medium">
            <x-icon name="document-arrow-down" class="w-4 h-4" />
            تصدير PDF
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Info --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-6 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div><span class="text-xs text-gray-500">الفرع</span><p class="font-semibold text-gray-700 mt-1">{{ $depreciation->branch->name }}</p></div>
            <div><span class="text-xs text-gray-500">التاريخ</span><p class="font-semibold text-gray-700 mt-1">{{ $depreciation->date->format('Y-m-d') }}</p></div>
            <div><span class="text-xs text-gray-500">أنشأ بواسطة</span><p class="font-semibold text-gray-700 mt-1">{{ $depreciation->admin->name }}</p></div>
            <div>
                <span class="text-xs text-gray-500">الحالة</span>
                @php
                    $statusColors = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700'];
                @endphp
                <p class="mt-1"><span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$depreciation->status] ?? '' }}">{{ $depreciation->status_label }}</span></p>
            </div>
        </div>
        @if($depreciation->approvedByAdmin)
            <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 gap-6">
                <div><span class="text-xs text-gray-500">{{ $depreciation->status === 'approved' ? 'وافق بواسطة' : 'رفض بواسطة' }}</span><p class="font-semibold text-gray-700 mt-1">{{ $depreciation->approvedByAdmin->name }}</p></div>
                <div><span class="text-xs text-gray-500">تاريخ {{ $depreciation->status === 'approved' ? 'الموافقة' : 'الرفض' }}</span><p class="font-semibold text-gray-700 mt-1">{{ $depreciation->approved_at?->format('Y-m-d H:i') }}</p></div>
            </div>
        @endif
        <div class="mt-4 pt-4 border-t border-gray-100">
            <span class="text-xs text-gray-500">سبب الإهلاك</span>
            <p class="text-sm text-gray-600 mt-1">{{ $depreciation->reason }}</p>
        </div>
        @if($depreciation->notes)
            <div class="mt-3"><span class="text-xs text-gray-500">ملاحظات</span><p class="text-sm text-gray-600 mt-1">{{ $depreciation->notes }}</p></div>
        @endif
    </div>

    {{-- Total Loss --}}
    <div class="bg-card rounded-2xl shadow-sm border border-red-200 p-4 mb-4 text-center">
        <div class="text-sm text-red-600 mb-1">إجمالي الخسارة</div>
        <div class="text-2xl font-bold text-red-700">{{ number_format($depreciation->total_loss, 2) }}</div>
    </div>

    {{-- Items --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden mb-4">
        <div class="p-4 border-b border-gray-100"><h2 class="text-lg font-semibold text-primary-700">المنتجات</h2></div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-primary-50/50">
                <tr>
                    <th class="px-4 py-3 text-right font-semibold text-primary-700">#</th>
                    <th class="px-4 py-3 text-right font-semibold text-primary-700">المنتج</th>
                    <th class="px-4 py-3 text-right font-semibold text-primary-700">الكمية</th>
                    <th class="px-4 py-3 text-right font-semibold text-primary-700">الوحدة</th>
                    <th class="px-4 py-3 text-right font-semibold text-primary-700">تكلفة الوحدة</th>
                    <th class="px-4 py-3 text-right font-semibold text-primary-700">الخسارة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($depreciation->items as $i => $item)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-medium">{{ $item->product->name }}</td>
                        <td class="px-4 py-3">{{ $item->quantity }}</td>
                        <td class="px-4 py-3">{{ $item->unit?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ number_format($item->cost_price, 2) }}</td>
                        <td class="px-4 py-3 font-semibold text-red-600">{{ number_format($item->total_loss, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    {{-- Actions --}}
    @if($depreciation->status === 'pending' && auth('admin')->user()?->hasPermission('product-depreciations.approve'))
        <div class="flex items-center gap-3">
            <button wire:click="approve" wire:confirm="هل أنت متأكد من الموافقة؟ سيتم خصم الكميات من المخزون." class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium">
                <x-icon name="check" class="w-4 h-4" />
                موافقة
            </button>
            <button wire:click="reject" wire:confirm="هل أنت متأكد من رفض طلب الإهلاك؟" class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium">
                <x-icon name="x-mark" class="w-4 h-4" />
                رفض
            </button>
        </div>
    @endif
</div>
