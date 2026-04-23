<div>
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">أوامر الصرف المخزني</h1>
            <p class="text-sm text-gray-500 mt-1">إدارة أوامر صرف المنتجات للمناديب</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('inventory-dispatches.create'))
            <x-button variant="primary" href="{{ route('inventory-dispatches.create') }}">
                <x-icon name="plus" class="w-4 h-4" />
                أمر صرف جديد
            </x-button>
        @endif
    </div>

    {{-- Filters --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-6">
        <div class="flex flex-row gap-3 items-center">
            <div class="relative flex-1">
                <x-icon name="search" class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث..."
                    class="w-full pr-10 pl-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
            </div>
            <select wire:model.live="statusFilter"
                class="w-36 px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                <option value="">كل الحالات</option>
                <option value="pending">قيد الإعداد</option>
                <option value="dispatched">تم الصرف</option>
                <option value="partial_return">مرتجع جزئي</option>
                <option value="returned">مرتجع كامل</option>
                <option value="settled">تمت التسوية</option>
            </select>
            <select wire:model.live="branchFilter"
                class="w-36 px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                <option value="">كل الفروع</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="delegateFilter"
                class="w-36 px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                <option value="">كل المناديب</option>
                @foreach($delegates as $delegate)
                    <option value="{{ $delegate->id }}">{{ $delegate->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Table --}}
    <x-data-table :headers="['#', 'الفرع', 'المندوب', 'المسؤول', 'التاريخ', 'إجمالي التكلفة', 'المبيعات المتوقعة', 'الكمية المصروفة', 'معه الآن', 'الحالة', 'الإجراءات']">
        @forelse($dispatches as $dispatch)
            <tr class="hover:bg-primary-50/50 transition-colors">
                <td class="px-6 py-4 text-gray-500">{{ $dispatch->id }}</td>
                <td class="px-6 py-4 font-medium text-gray-800">{{ $dispatch->branch->name }}</td>
                <td class="px-6 py-4 text-gray-600">{{ $dispatch->delegate->name }}</td>
                <td class="px-6 py-4 text-gray-600">{{ $dispatch->admin->name }}</td>
                <td class="px-6 py-4 text-gray-500 text-xs">{{ $dispatch->date->format('Y/m/d') }}</td>
                <td class="px-6 py-4 text-gray-600" dir="ltr">{{ number_format($dispatch->total_cost, 2) }}</td>
                <td class="px-6 py-4 text-gray-600" dir="ltr">{{ number_format($dispatch->expected_sales, 2) }}</td>
                @php
                    $totalDispatched = $dispatch->items->sum('quantity');
                    $remaining = $dispatch->items->sum(fn($i) => $i->quantity - ($i->returned_quantity ?? 0));
                @endphp
                <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                        {{ $totalDispatched }}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $remaining > 0 ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        {{ $remaining }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    @php
                        $statusColors = [
                            'pending' => 'bg-gray-100 text-gray-700',
                            'dispatched' => 'bg-blue-100 text-blue-700',
                            'partial_return' => 'bg-orange-100 text-orange-700',
                            'returned' => 'bg-yellow-100 text-yellow-700',
                            'settled' => 'bg-green-100 text-green-700',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$dispatch->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $dispatch->status_label }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-1">
                        <a href="{{ route('inventory-dispatches.show', $dispatch) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="عرض التفاصيل">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </a>
                        <a href="{{ route('inventory-dispatches.pdf', $dispatch->id) }}" target="_blank" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="تحميل PDF">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                        </a>
                        @if(auth('admin')->user()?->hasPermission('inventory-dispatches.delete') && $dispatch->status === 'pending')
                            <button wire:click="delete({{ $dispatch->id }})" wire:confirm="هل أنت متأكد من حذف أمر الصرف؟"
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="حذف">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="px-6 py-12 text-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-gray-300"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                    <p>لا توجد أوامر صرف مخزني</p>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $dispatches->links() }}
        </x-slot:pagination>
    </x-data-table>
</div>
