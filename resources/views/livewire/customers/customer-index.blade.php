<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700 tracking-tight">إدارة العملاء</h1>
            <p class="text-sm text-gray-400 mt-0.5">عرض وإدارة جميع العملاء</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('customers.create'))
            <x-button variant="primary" href="{{ route('customers.create') }}">
                <x-icon name="plus" class="w-4 h-4" />إضافة عميل
            </x-button>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-48">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث بالاسم أو الهاتف..."
                    class="w-full pr-9 pl-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 text-sm focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all">
            </div>
            @if(isset($classificationFilter))
            <select wire:model.live="classificationFilter" class="border border-gray-200 rounded-xl bg-gray-50 text-sm px-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all">
                <option value="">كل التصنيفات</option>
                <option value="premium">مميز</option>
                <option value="medium">متوسط</option>
                <option value="regular">عادي</option>
            </select>
            @endif
            @if(isset($areaFilter))
            <select wire:model.live="areaFilter" class="border border-gray-200 rounded-xl bg-gray-50 text-sm px-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all">
                <option value="">كل المناطق</option>
                @foreach(\App\Models\Area::orderBy('name')->get() as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
            @endif
        </div>
    </div>

    @php
        $classificationLabels = ['premium' => 'مميز', 'medium' => 'متوسط', 'regular' => 'عادي'];
    @endphp

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm text-right table-fixed">
            <colgroup>
                <col class="w-10">
                <col class="w-[24%]">
                <col class="w-[13%]">
                <col class="w-[12%]">
                <col class="w-[10%]">
                <col class="w-[13%]">
                <col class="w-[10%]">
                <col class="w-[18%]">
            </colgroup>
            <thead>
                <tr class="bg-primary-700">
                    <th class="px-4 py-3 text-xs font-bold text-white text-center">#</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">العميل</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">الهاتف</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">المنطقة</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">التصنيف</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">الحد الائتماني</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">الحالة</th>
                    <th class="px-4 py-3 text-xs font-bold text-white text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $i => $customer)
                <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-stone-50/40' }} hover:bg-stone-50 transition-colors border-b border-gray-50">
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono text-center">{{ $i + 1 }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 justify-end">
                            <div class="text-right min-w-0 flex-1">
                                <p class="font-bold text-gray-800 text-sm truncate">{{ $customer->name }}</p>
                                <p class="text-xs text-gray-400 truncate" dir="ltr">{{ $customer->email ?? '—' }}</p>
                            </div>
                            <div class="w-9 h-9 rounded-lg bg-primary-800 flex items-center justify-center text-white font-extrabold text-sm flex-shrink-0">
                                {{ mb_substr($customer->name, 0, 1) }}
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-700 font-medium text-sm" dir="ltr">{{ $customer->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600 text-sm truncate">{{ $customer->area?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-stone-100 text-stone-700 whitespace-nowrap">
                            {{ $classificationLabels[$customer->classification] ?? $customer->classification }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-bold text-gray-800 text-sm">{{ number_format($customer->credit_limit, 0) }}</td>
                    <td class="px-4 py-3">
                        @if($customer->is_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-stone-100 text-stone-700 whitespace-nowrap">
                                <span class="w-1.5 h-1.5 rounded-full bg-stone-500"></span>نشط
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-stone-200 text-stone-600 whitespace-nowrap">
                                <span class="w-1.5 h-1.5 rounded-full bg-stone-400"></span>معطل
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 justify-center">
                            @if(auth('admin')->user()?->hasPermission('customers.view'))
                                <a href="{{ route('customers.show', $customer) }}"
                                    class="w-8 h-8 rounded-lg bg-primary-50 text-primary-700 hover:bg-primary-100 flex items-center justify-center transition-colors flex-shrink-0" title="عرض الملف">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                </a>
                            @endif
                            @if(auth('admin')->user()?->hasPermission('customers.edit'))
                                <button wire:click="toggleActive({{ $customer->id }})"
                                    class="w-8 h-8 rounded-lg bg-stone-100 text-stone-600 hover:bg-stone-200 flex items-center justify-center transition-colors flex-shrink-0"
                                    title="{{ $customer->is_active ? 'تعطيل' : 'تفعيل' }}">
                                    @if($customer->is_active)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    @endif
                                </button>
                                <a href="{{ route('customers.edit', $customer) }}"
                                    class="w-8 h-8 rounded-lg bg-stone-100 text-stone-600 hover:bg-stone-200 flex items-center justify-center transition-colors flex-shrink-0" title="تعديل">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                                </a>
                            @endif
                            @if(auth('admin')->user()?->hasPermission('customers.delete'))
                                <button wire:click="delete({{ $customer->id }})" wire:confirm="هل أنت متأكد من حذف هذا العميل؟"
                                    class="w-8 h-8 rounded-lg bg-stone-100 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors flex-shrink-0" title="حذف">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-16 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        <p class="text-gray-400 text-sm">لا يوجد عملاء مسجلون</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
        <div class="mt-4">{{ $customers->links() }}</div>
    @endif
</div>