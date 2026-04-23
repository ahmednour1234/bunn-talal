<div>
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">إدارة المناديب</h1>
            <p class="text-sm text-gray-500 mt-1">عرض وإدارة جميع مناديب المبيعات</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('delegates.create'))
            <x-button variant="primary" href="{{ route('delegates.create') }}">
                <x-icon name="plus" class="w-4 h-4" />
                إضافة مندوب
            </x-button>
        @endif
    </div>

    {{-- Search --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-6">
        <div class="relative">
            <x-icon name="search" class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث بالاسم أو الهاتف أو البريد..."
                class="w-full pr-10 pl-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
        </div>
    </div>

    {{-- Table --}}
    <x-data-table :headers="['#', 'المندوب', 'الهاتف', 'الفروع', 'المناطق', 'حد الآجل', 'العهدة', 'محصّل / عليه', 'العمولة %', 'الحالة', 'الإجراءات']">
        @forelse($delegates as $delegate)
            <tr class="hover:bg-primary-50/50 transition-colors">
                <td class="px-6 py-4 text-gray-500">{{ $delegate->id }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center shrink-0">
                            <x-icon name="delegate" class="w-5 h-5 text-primary-600" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $delegate->name }}</p>
                            @if($delegate->email)
                                <p class="text-xs text-gray-400" dir="ltr">{{ $delegate->email }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-gray-600 text-sm" dir="ltr">{{ $delegate->phone ?? '—' }}</td>
                <td class="px-6 py-4">
                    <div class="flex flex-wrap gap-1">
                        @foreach($delegate->branches->take(3) as $branch)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">{{ $branch->name }}</span>
                        @endforeach
                        @if($delegate->branches->count() > 3)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">+{{ $delegate->branches->count() - 3 }}</span>
                        @endif
                        @if($delegate->branches->isEmpty())
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex flex-wrap gap-1">
                        @foreach($delegate->areas->take(2) as $area)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700">{{ $area->name }}</span>
                        @endforeach
                        @if($delegate->areas->count() > 2)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">+{{ $delegate->areas->count() - 2 }}</span>
                        @endif
                        @if($delegate->areas->isEmpty())
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 text-sm font-mono text-center" dir="ltr">{{ number_format($delegate->credit_sales_limit, 2) }}</td>
                <td class="px-6 py-4 text-sm font-mono text-center" dir="ltr">{{ number_format($delegate->cash_custody, 2) }}</td>
                <td class="px-6 py-4 text-center">
                    <div class="text-xs space-y-0.5">
                        <p class="text-green-600 font-mono" dir="ltr">{{ number_format($delegate->total_collected, 2) }} <span class="text-gray-400">محصّل</span></p>
                        <p class="text-red-600 font-mono" dir="ltr">{{ number_format($delegate->total_due, 2) }} <span class="text-gray-400">عليه</span></p>
                    </div>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700">{{ $delegate->sales_commission_rate * 1 }}%</span>
                </td>
                <td class="px-6 py-4">
                    @if($delegate->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">نشط</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">معطل</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('delegates.show', $delegate) }}" class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition-colors" title="عرض الملف">
                            <x-icon name="eye" class="w-4 h-4" />
                        </a>
                        @if(auth('admin')->user()?->hasPermission('delegates.edit'))
                            <button wire:click="toggleActive({{ $delegate->id }})"
                                class="p-2 {{ $delegate->is_active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition-colors"
                                title="{{ $delegate->is_active ? 'تعطيل' : 'تفعيل' }}">
                                @if($delegate->is_active)
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                @endif
                            </button>
                            <a href="{{ route('delegates.edit', $delegate) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="تعديل">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </a>
                        @endif
                        @if(auth('admin')->user()?->hasPermission('delegates.delete'))
                            <button wire:click="delete({{ $delegate->id }})" wire:confirm="هل أنت متأكد من حذف هذا المندوب؟" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="حذف">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="px-6 py-12 text-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-gray-300"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                    <p>لا يوجد مناديب مسجلين</p>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $delegates->links() }}
        </x-slot:pagination>
    </x-data-table>
</div>
