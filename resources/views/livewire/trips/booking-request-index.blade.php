<div dir="rtl" class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700">طلبات الحجز</h1>
            <p class="text-sm text-gray-400 mt-0.5">إدارة طلبات حجز العملاء للمناديب</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('trips.index') }}" class="text-sm text-gray-500 hover:text-primary-700 flex items-center gap-1">
                الرحلات
            </a>
            <a href="{{ route('trips.booking-requests.create') }}" class="inline-flex items-center gap-2 bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                طلب جديد
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm font-semibold px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="grid grid-cols-3 gap-3">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث باسم العميل أو الهاتف..."
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:ring-2 focus:ring-primary-300">
            <select wire:model.live="statusFilter" class="border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:ring-2 focus:ring-primary-300">
                <option value="">كل الحالات</option>
                @foreach($statusLabels as $val => $label)
                <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="delegateFilter" class="border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:ring-2 focus:ring-primary-300">
                <option value="">كل المناديب</option>
                @foreach($delegates as $d)
                <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm text-right">
            <thead>
                <tr class="bg-primary-700 text-white text-xs">
                    <th class="px-4 py-3 font-semibold">العميل</th>
                    <th class="px-4 py-3 font-semibold">الهاتف</th>
                    <th class="px-4 py-3 font-semibold">المندوب</th>
                    <th class="px-4 py-3 font-semibold">الرحلة</th>
                    <th class="px-4 py-3 font-semibold">الحالة</th>
                    <th class="px-4 py-3 font-semibold">التاريخ</th>
                    <th class="px-4 py-3 font-semibold">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($requests as $req)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $req->customer_name }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $req->customer_phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $req->delegate?->name }}</td>
                    <td class="px-4 py-3">
                        @if($req->trip)
                        <a href="{{ route('trips.show', $req->trip_id) }}" class="text-xs font-mono text-primary-600 hover:underline">{{ $req->trip->trip_number }}</a>
                        @else
                        <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold {{ $req->statusColor() }}">{{ $req->statusLabel() }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $req->created_at->format('Y-m-d') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1 flex-wrap">
                            <button wire:click="showBooking({{ $req->id }})" title="عرض التفاصيل"
                                class="text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded hover:bg-blue-100">عرض</button>
                            @if($req->status === 'pending')
                            <button wire:click="updateStatus({{ $req->id }},'confirmed')"
                                class="text-xs font-semibold text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded hover:bg-green-100">قبول</button>
                            <button wire:click="updateStatus({{ $req->id }},'cancelled')"
                                class="text-xs font-semibold text-red-700 bg-red-50 border border-red-200 px-2 py-0.5 rounded hover:bg-red-100">رفض</button>
                            @elseif($req->status === 'confirmed')
                            <span class="text-xs text-gray-400">مقبول</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-16 text-center text-gray-400">
                        <p class="font-semibold">لا توجد طلبات حجز</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-50">
            {{ $requests->links() }}
        </div>
    </div>

    {{-- Show Modal --}}
    @if($showModal && $selected)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:click.self="closeModal">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden" dir="rtl">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-5 py-4 bg-primary-700 text-white">
                <h2 class="font-bold text-base">تفاصيل طلب الحجز #{{ $selected->id }}</h2>
                <button wire:click="closeModal" class="text-white/70 hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            {{-- Meta --}}
            <div class="px-5 py-4 grid grid-cols-2 gap-3 text-sm border-b border-gray-100">
                <div><span class="text-gray-400">العميل:</span> <span class="font-semibold text-gray-800">{{ $selected->customer_name ?? '—' }}</span></div>
                <div><span class="text-gray-400">الهاتف:</span> <span class="text-gray-700">{{ $selected->customer_phone ?? '—' }}</span></div>
                <div><span class="text-gray-400">المندوب:</span> <span class="text-gray-700">{{ $selected->delegate?->name }}</span></div>
                <div><span class="text-gray-400">الرحلة:</span> <span class="text-gray-700">{{ $selected->trip?->trip_number ?? '—' }}</span></div>
                <div><span class="text-gray-400">الحالة:</span> <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $selected->statusColor() }}">{{ $selected->statusLabel() }}</span></div>
                <div><span class="text-gray-400">التاريخ:</span> <span class="text-gray-700">{{ $selected->created_at->format('Y-m-d') }}</span></div>
                @if($selected->customer_address)
                <div class="col-span-2"><span class="text-gray-400">العنوان:</span> <span class="text-gray-700">{{ $selected->customer_address }}</span></div>
                @endif
                @if($selected->notes)
                <div class="col-span-2"><span class="text-gray-400">ملاحظات:</span> <span class="text-gray-700">{{ $selected->notes }}</span></div>
                @endif
            </div>
            {{-- Items --}}
            <div class="px-5 py-4">
                <p class="text-xs font-bold text-gray-500 uppercase mb-2">الأصناف</p>
                <div class="space-y-2">
                    @forelse($selected->items as $item)
                    <div class="flex items-center justify-between bg-gray-50 rounded-xl px-3 py-2">
                        <span class="font-semibold text-gray-800 text-sm">{{ $item->product?->name }}</span>
                        <div class="flex items-center gap-3 text-xs text-gray-500">
                            <span>{{ $item->quantity }} {{ $item->unit?->symbol ?? '' }}</span>
                            <span class="font-semibold text-gray-700">{{ number_format($item->unit_price, 2) }}</span>
                            <span class="font-bold text-primary-700">= {{ number_format($item->quantity * $item->unit_price, 2) }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 text-center py-2">لا توجد أصناف</p>
                    @endforelse
                </div>
                {{-- Total --}}
                @if($selected->items->isNotEmpty())
                <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100">
                    <span class="text-sm font-bold text-gray-600">الإجمالي</span>
                    <span class="text-base font-extrabold text-primary-700">
                        {{ number_format($selected->items->sum(fn($i) => $i->quantity * $i->unit_price), 2) }}
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>
