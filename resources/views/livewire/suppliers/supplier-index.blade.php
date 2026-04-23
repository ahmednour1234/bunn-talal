<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700 tracking-tight">إدارة الموردين</h1>
            <p class="text-sm text-gray-400 mt-0.5">عرض وإدارة جميع الموردين</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('suppliers.create'))
            <x-button variant="primary" href="{{ route('suppliers.create') }}">
                <x-icon name="plus" class="w-4 h-4" />إضافة مورد
            </x-button>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5">
        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث بالاسم أو الشركة أو الهاتف..."
                class="w-full pr-9 pl-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 text-sm focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all">
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm text-right table-fixed">
            <colgroup>
                <col class="w-10">
                <col class="w-[26%]">
                <col class="w-[14%]">
                <col class="w-[13%]">
                <col class="w-[13%]">
                <col class="w-[12%]">
                <col class="w-[10%]">
                <col class="w-[12%]">
            </colgroup>
            <thead>
                <tr class="bg-primary-700">
                    <th class="px-4 py-3 text-xs font-bold text-white text-center">#</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">المورد</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">الشركة</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">الهاتف</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">الرقم الضريبي</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">الحد الائتماني</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">الحالة</th>
                    <th class="px-4 py-3 text-xs font-bold text-white text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $i => $supplier)
                <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-stone-50/40' }} hover:bg-stone-50 transition-colors border-b border-gray-50">
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono text-center">{{ $i + 1 }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 justify-end">
                            <div class="text-right min-w-0 flex-1">
                                <p class="font-bold text-gray-800 text-sm truncate">{{ $supplier->name }}</p>
                                <p class="text-xs text-gray-400 truncate" dir="ltr">{{ $supplier->email ?? '—' }}</p>
                            </div>
                            <div class="w-9 h-9 rounded-lg bg-primary-800 flex items-center justify-center text-white font-extrabold text-sm flex-shrink-0">
                                {{ mb_substr($supplier->name, 0, 1) }}
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-sm truncate">{{ $supplier->company_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-700 font-medium text-sm" dir="ltr">{{ $supplier->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs truncate" dir="ltr">{{ $supplier->tax_number ?? '—' }}</td>
                    <td class="px-4 py-3 font-bold text-gray-800 text-sm">{{ number_format($supplier->credit_limit, 0) }}</td>
                    <td class="px-4 py-3">
                        @if($supplier->is_active)
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
                            @if(auth('admin')->user()?->hasPermission('suppliers.edit'))
                                <button wire:click="toggleActive({{ $supplier->id }})"
                                    class="w-8 h-8 rounded-lg bg-stone-100 text-stone-600 hover:bg-stone-200 flex items-center justify-center transition-colors flex-shrink-0"
                                    title="{{ $supplier->is_active ? 'تعطيل' : 'تفعيل' }}">
                                    @if($supplier->is_active)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    @endif
                                </button>
                                <a href="{{ route('suppliers.edit', $supplier) }}"
                                    class="w-8 h-8 rounded-lg bg-stone-100 text-stone-600 hover:bg-stone-200 flex items-center justify-center transition-colors flex-shrink-0" title="تعديل">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                                </a>
                            @endif
                            @if(auth('admin')->user()?->hasPermission('suppliers.delete'))
                                <button wire:click="delete({{ $supplier->id }})" wire:confirm="هل أنت متأكد من حذف هذا المورد؟"
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" /></svg>
                        <p class="text-gray-400 text-sm">لا يوجد موردون مسجلون</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    @if($suppliers->hasPages())<div class="mt-4">{{ $suppliers->links() }}</div>@endif
</div>