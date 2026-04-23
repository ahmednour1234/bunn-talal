<div dir="rtl" class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700">الرحلات</h1>
            <p class="text-sm text-gray-400 mt-0.5">إدارة رحلات المناديب</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('trips.booking-requests') }}" class="inline-flex items-center gap-2 bg-amber-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-amber-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                طلبات الحجز
            </a>
            <a href="{{ route('trips.create') }}" class="inline-flex items-center gap-2 bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                رحلة جديدة
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="md:col-span-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث برقم الرحلة..."
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-right focus:ring-2 focus:ring-primary-300">
            </div>
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
            <div class="flex gap-2">
                <input type="date" wire:model.live="dateFrom" class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300">
                <input type="date" wire:model.live="dateTo" class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300">
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm text-right">
            <thead>
                <tr class="bg-primary-700 text-white text-xs">
                    <th class="px-4 py-3 font-semibold">رقم الرحلة</th>
                    <th class="px-4 py-3 font-semibold">المندوب</th>
                    <th class="px-4 py-3 font-semibold">الفرع</th>
                    <th class="px-4 py-3 font-semibold">تاريخ البدء</th>
                    <th class="px-4 py-3 font-semibold">الحالة</th>
                    <th class="px-4 py-3 font-semibold">المحصّل</th>
                    <th class="px-4 py-3 font-semibold">المفوتر</th>
                    <th class="px-4 py-3 font-semibold">العجز</th>
                    <th class="px-4 py-3 font-semibold">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($trips as $trip)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-3 font-mono text-xs font-bold text-primary-700">{{ $trip->trip_number }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $trip->delegate?->name }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $trip->branch?->name }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $trip->start_date?->format('Y-m-d') }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold {{ $trip->statusColor() }}">{{ $trip->statusLabel() }}</span>
                    </td>
                    <td class="px-4 py-3 font-semibold text-green-700">{{ number_format($trip->total_collected, 0) }} ج.م</td>
                    <td class="px-4 py-3 font-semibold text-gray-700">{{ number_format($trip->total_invoiced, 0) }} ج.م</td>
                    <td class="px-4 py-3">
                        @if($trip->settlement_cash_deficit > 0 || $trip->settlement_product_deficit > 0)
                        <span class="inline-flex items-center gap-1 bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            عجز
                        </span>
                        @elseif($trip->status === 'settled')
                        <span class="text-xs text-green-600 font-semibold">✓ سليم</span>
                        @else
                        <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 justify-end">
                            <a href="{{ route('trips.show', $trip->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary-700 bg-primary-50 border border-primary-200 px-2.5 py-1 rounded-lg hover:bg-primary-100 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                عرض
                            </a>
                            @if(!in_array($trip->status, ['settled','cancelled']))
                            <a href="{{ route('trips.edit', $trip->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 px-2.5 py-1 rounded-lg hover:bg-gray-100 transition-colors">
                                تعديل
                            </a>
                            @endif
                            @if(in_array($trip->status, ['returning']))
                            <a href="{{ route('trips.settle', $trip->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-white bg-amber-600 px-2.5 py-1 rounded-lg hover:bg-amber-700 transition-colors">
                                تسوية
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-16 text-center text-gray-400">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25"/></svg>
                            </div>
                            <p class="font-semibold text-gray-400">لا توجد رحلات</p>
                            <a href="{{ route('trips.create') }}" class="text-sm text-primary-600 hover:underline">إنشاء رحلة جديدة</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-50">
            {{ $trips->links() }}
        </div>
    </div>
</div>
