<div>
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">إدارة وحدات القياس</h1>
            <p class="text-sm text-gray-500 mt-1">إدارة الوحدات ومعاملات التحويل</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('units.create'))
            <x-button variant="primary" href="{{ route('units.create') }}">
                <x-icon name="plus" class="w-4 h-4" />
                إضافة وحدة
            </x-button>
        @endif
    </div>

    {{-- Filters --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <x-icon name="search" class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث بالاسم أو الرمز..."
                    class="w-full pr-10 pl-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
            </div>
            <div class="w-full md:w-48">
                <select wire:model.live="typeFilter"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                    <option value="">كل الأنواع</option>
                    @foreach($typeLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <x-data-table :headers="['#', 'الاسم', 'الرمز', 'النوع', 'الوحدة الأساسية', 'معامل التحويل', 'الحالة', 'الإجراءات']">
        @forelse($units as $unit)
            <tr class="hover:bg-primary-50/50 transition-colors">
                <td class="px-6 py-4 text-gray-500">{{ $unit->id }}</td>
                <td class="px-6 py-4 font-medium text-gray-800">{{ $unit->name }}</td>
                <td class="px-6 py-4 text-gray-600 font-mono text-center" dir="ltr">{{ $unit->symbol }}</td>
                <td class="px-6 py-4">
                    @php
                        $typeColors = [
                            'weight' => 'bg-blue-100 text-blue-700',
                            'volume' => 'bg-purple-100 text-purple-700',
                            'quantity' => 'bg-orange-100 text-orange-700',
                            'length' => 'bg-teal-100 text-teal-700',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $typeColors[$unit->type] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $typeLabels[$unit->type] ?? $unit->type }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-600 text-sm">
                    @if($unit->isBaseUnit())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-primary-100 text-primary-700">وحدة أساسية</span>
                    @else
                        {{ $unit->baseUnit?->name }} ({{ $unit->baseUnit?->symbol }})
                    @endif
                </td>
                <td class="px-6 py-4 text-center font-mono text-sm" dir="ltr">
                    @if($unit->isBaseUnit())
                        <span class="text-gray-400">1</span>
                    @else
                        {{ $unit->conversion_factor * 1 }}
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if($unit->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">نشط</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">معطل</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        @if(auth('admin')->user()?->hasPermission('units.edit'))
                            <button wire:click="toggleActive({{ $unit->id }})"
                                class="p-2 {{ $unit->is_active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition-colors"
                                title="{{ $unit->is_active ? 'تعطيل' : 'تفعيل' }}">
                                @if($unit->is_active)
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                @endif
                            </button>
                            <a href="{{ route('units.edit', $unit) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="تعديل">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </a>
                        @endif
                        @if(auth('admin')->user()?->hasPermission('units.delete'))
                            <button wire:click="delete({{ $unit->id }})" wire:confirm="هل أنت متأكد من حذف هذه الوحدة؟" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="حذف">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-gray-300"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                    <p>لا توجد وحدات قياس مسجلة</p>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $units->links() }}
        </x-slot:pagination>
    </x-data-table>
</div>
