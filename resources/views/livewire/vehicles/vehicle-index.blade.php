<div>
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">إدارة المركبات</h1>
            <p class="text-sm text-gray-500 mt-1">عرض وإدارة جميع المركبات</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('vehicles.create'))
            <x-button variant="primary" href="{{ route('vehicles.create') }}">
                <x-icon name="plus" class="w-4 h-4" />
                إضافة مركبة
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
                placeholder="بحث بالاسم أو الكود..."
                class="w-full pr-10 pl-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
            >
        </div>
    </div>

    {{-- Table --}}
    <x-data-table :headers="['#', 'اسم المركبة', 'الكود', 'الحالة', 'تاريخ الإنشاء', 'الإجراءات']">
        @forelse($vehicles as $vehicle)
            <tr class="hover:bg-primary-50/50 transition-colors">
                <td class="px-6 py-4 text-gray-500">{{ $vehicle->id }}</td>
                <td class="px-6 py-4 font-medium text-gray-800">{{ $vehicle->name }}</td>
                <td class="px-6 py-4 text-gray-600 font-mono" dir="ltr">{{ $vehicle->code }}</td>
                <td class="px-6 py-4">
                    @if($vehicle->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            نشط
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                            معطل
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 text-gray-500 text-xs">{{ $vehicle->created_at->format('Y/m/d') }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        @if(auth('admin')->user()?->hasPermission('vehicles.edit'))
                            <button
                                wire:click="toggleActive({{ $vehicle->id }})"
                                class="p-2 {{ $vehicle->is_active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition-colors"
                                title="{{ $vehicle->is_active ? 'تعطيل' : 'تفعيل' }}"
                            >
                                @if($vehicle->is_active)
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                @endif
                            </button>
                            <a href="{{ route('vehicles.edit', $vehicle) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="تعديل">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </a>
                        @endif
                        @if(auth('admin')->user()?->hasPermission('vehicles.delete'))
                            <button
                                wire:click="delete({{ $vehicle->id }})"
                                wire:confirm="هل أنت متأكد من حذف هذه المركبة؟"
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="حذف"
                            >
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-gray-300">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0H6.375c-.621 0-1.125-.504-1.125-1.125V11.25m19.5 0h1.5m-16.5 0h7.5m-7.5 0-1 3.5M15 11.25h3.375c.621 0 1.125.504 1.125 1.125V18" />
                    </svg>
                    <p>لا توجد مركبات مسجلة</p>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $vehicles->links() }}
        </x-slot:pagination>
    </x-data-table>
</div>
