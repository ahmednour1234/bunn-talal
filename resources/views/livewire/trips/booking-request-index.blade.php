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
                        @if($req->status === 'pending')
                        <div class="flex gap-1">
                            <button wire:click="updateStatus({{ $req->id }},'confirmed')"
                                class="text-xs font-semibold text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded hover:bg-green-100">قبول</button>
                            <button wire:click="updateStatus({{ $req->id }},'cancelled')"
                                class="text-xs font-semibold text-red-700 bg-red-50 border border-red-200 px-2 py-0.5 rounded hover:bg-red-100">رفض</button>
                        </div>
                        @elseif($req->status === 'confirmed')
                        <span class="text-xs text-gray-400">مقبول</span>
                        @endif
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
</div>
