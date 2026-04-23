<div>
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">إدارة الضرائب</h1>
            <p class="text-sm text-gray-500 mt-1">عرض وإدارة جميع الضرائب</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('taxes.create'))
            <x-button variant="primary" href="{{ route('taxes.create') }}">
                <x-icon name="plus" class="w-4 h-4" />
                إضافة ضريبة
            </x-button>
        @endif
    </div>

    {{-- Search --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-6">
        <div class="relative">
            <x-icon name="search" class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="بحث بالاسم..."
                class="w-full pr-10 pl-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
            >
        </div>
    </div>

    {{-- Table --}}
    <x-data-table :headers="['#', 'اسم الضريبة', 'النوع', 'القيمة', 'الحالة', 'تاريخ الإنشاء', 'الإجراءات']">
        @forelse($taxes as $tax)
            <tr class="hover:bg-primary-50/50 transition-colors">
                <td class="px-6 py-4 text-gray-500">{{ $tax->id }}</td>
                <td class="px-6 py-4 font-medium text-gray-800">{{ $tax->name }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $tax->type === 'percentage' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                        {{ $tax->type_label }}
                    </span>
                </td>
                <td class="px-6 py-4 font-semibold text-gray-700" dir="ltr">{{ $tax->formatted_rate }}</td>
                <td class="px-6 py-4">
                    @if($tax->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">نشط</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">معطل</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-gray-500 text-sm">{{ $tax->created_at->format('Y-m-d') }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        @if(auth('admin')->user()?->hasPermission('taxes.edit'))
                            <button wire:click="toggleActive({{ $tax->id }})"
                                class="p-2 {{ $tax->is_active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition-colors"
                                title="{{ $tax->is_active ? 'تعطيل' : 'تفعيل' }}">
                                @if($tax->is_active)
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                @endif
                            </button>
                            <a href="{{ route('taxes.edit', $tax) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="تعديل">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </a>
                        @endif
                        @if(auth('admin')->user()?->hasPermission('taxes.delete'))
                            <button wire:click="delete({{ $tax->id }})" wire:confirm="هل أنت متأكد من حذف هذه الضريبة؟" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="حذف">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-gray-300"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    <p>لا توجد ضرائب مسجلة</p>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $taxes->links() }}
        </x-slot:pagination>
    </x-data-table>
</div>
