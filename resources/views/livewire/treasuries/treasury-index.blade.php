<div>
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">إدارة الخزن</h1>
            <p class="text-sm text-gray-500 mt-1">عرض وإدارة جميع الخزن</p>
        </div>
        <div class="flex items-center gap-2">
            <x-button variant="secondary" size="sm" href="{{ route('treasuries.export.excel') }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                Excel
            </x-button>
            <x-button variant="secondary" size="sm" href="{{ route('treasuries.export.pdf') }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 7.5H5.25" /></svg>
                PDF
            </x-button>
            @if(auth('admin')->user()?->hasPermission('treasuries.create'))
                <x-button variant="primary" href="{{ route('treasuries.create') }}">
                    <x-icon name="plus" class="w-4 h-4" />
                    إضافة خزنة
                </x-button>
            @endif
        </div>
    </div>

    {{-- Search --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-6">
        <div class="relative">
            <x-icon name="search" class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث بالاسم..."
                class="w-full pr-10 pl-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
        </div>
    </div>

    {{-- Table --}}
    <x-data-table :headers="['#', 'اسم الخزنة', 'الرصيد', 'الحالة', 'الإجراءات']">
        @forelse($treasuries as $treasury)
            <tr class="hover:bg-primary-50/50 transition-colors">
                <td class="px-6 py-4 text-gray-500">{{ $treasury->id }}</td>
                <td class="px-6 py-4 font-medium text-gray-800">{{ $treasury->name }}</td>
                <td class="px-6 py-4 text-sm font-mono text-center {{ $treasury->balance >= 0 ? 'text-green-700' : 'text-red-700' }}" dir="ltr">{{ number_format($treasury->balance, 2) }}</td>
                <td class="px-6 py-4">
                    @if($treasury->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">نشط</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">معطل</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        @if(auth('admin')->user()?->hasPermission('treasuries.edit'))
                            <button wire:click="toggleActive({{ $treasury->id }})"
                                class="p-2 {{ $treasury->is_active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition-colors"
                                title="{{ $treasury->is_active ? 'تعطيل' : 'تفعيل' }}">
                                @if($treasury->is_active)
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                @endif
                            </button>
                            <a href="{{ route('treasuries.edit', $treasury) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="تعديل">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </a>
                        @endif
                        @if(auth('admin')->user()?->hasPermission('treasuries.delete'))
                            <button wire:click="delete({{ $treasury->id }})" wire:confirm="هل أنت متأكد من حذف هذه الخزنة؟" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="حذف">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                    <x-icon name="lock-closed" class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                    <p>لا يوجد خزن مسجلة</p>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $treasuries->links() }}
        </x-slot:pagination>
    </x-data-table>
</div>
