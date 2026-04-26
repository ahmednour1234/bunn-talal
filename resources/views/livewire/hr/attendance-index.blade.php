<div dir="rtl" class="space-y-6">
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm font-bold px-5 py-3 rounded-2xl flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">سجل الحضور والانصراف</h1>
            <p class="text-sm text-gray-500 mt-1">تتبع حضور المناديب يومياً</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('hr.create'))
        <x-button variant="primary" href="{{ route('hr.attendance.create') }}">
            <x-icon name="plus" class="w-4 h-4" />
            تسجيل حضور
        </x-button>
        @endif
    </div>

    {{-- Filters --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="relative">
                <x-icon name="search" class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث..."
                    class="w-full pr-10 pl-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
            </div>
            <select wire:model.live="filterDelegate" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                <option value="">كل المناديب</option>
                @foreach($delegates as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                <option value="">كل الحالات</option>
                @foreach($statuses as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="filterDateFrom" placeholder="من تاريخ"
                class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
            <input type="date" wire:model.live="filterDateTo" placeholder="إلى تاريخ"
                class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
        </div>
    </div>

    {{-- Table --}}
    <x-data-table :headers="['#', 'المندوب', 'التاريخ', 'وقت الحضور', 'وقت الانصراف', 'الحالة', 'ملاحظات', 'الإجراءات']">
        @forelse($attendances as $att)
        <tr class="hover:bg-primary-50/50 transition-colors">
            <td class="px-6 py-4 text-gray-500 text-sm">{{ $att->id }}</td>
            <td class="px-6 py-4 font-semibold text-gray-800">{{ $att->delegate->name }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ $att->date->format('Y-m-d') }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ $att->check_in ?? '-' }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ $att->check_out ?? '-' }}</td>
            <td class="px-6 py-4">
                @php
                    $colors = ['present' => 'green', 'absent' => 'red', 'late' => 'amber', 'on_leave' => 'blue'];
                    $color = $colors[$att->status] ?? 'gray';
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-{{ $color }}-100 text-{{ $color }}-700">
                    {{ $att->status_label }}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $att->notes ?? '-' }}</td>
            <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                    @if(auth('admin')->user()?->hasPermission('hr.edit'))
                    <a href="{{ route('hr.attendance.edit', $att->id) }}"
                        class="text-xs bg-primary-100 hover:bg-primary-200 text-primary-700 px-3 py-1.5 rounded-lg font-semibold transition-colors">
                        تعديل
                    </a>
                    @endif
                    @if(auth('admin')->user()?->hasPermission('hr.delete'))
                    <button wire:click="delete({{ $att->id }})" wire:confirm="هل تريد حذف هذا السجل؟"
                        class="text-xs bg-gray-100 hover:bg-red-100 text-gray-600 hover:text-red-600 px-3 py-1.5 rounded-lg font-semibold transition-colors">
                        حذف
                    </button>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="px-6 py-12 text-center text-gray-400">لا توجد سجلات حضور</td></tr>
        @endforelse
    </x-data-table>
    <div class="mt-4">{{ $attendances->links() }}</div>
</div>
