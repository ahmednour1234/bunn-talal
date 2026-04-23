<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">إهلاك المنتجات</h1>
            <p class="text-sm text-gray-500 mt-1">إدارة طلبات إهلاك المنتجات من الفروع</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('product-depreciations.create'))
            <a href="{{ route('product-depreciations.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors text-sm font-medium shadow-sm">
                <x-icon name="plus" class="w-4 h-4" />
                طلب إهلاك جديد
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <x-icon name="search" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث..."
                    class="w-full pr-10 pl-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-primary-300">
            </div>
            <select wire:model.live="statusFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm">
                <option value="">كل الحالات</option>
                @foreach($statusLabels as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="branchFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm">
                <option value="">كل الفروع</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-primary-50/50">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">رقم الإهلاك</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الفرع</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">التاريخ</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">إجمالي الخسارة</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">الحالة</th>
                        <th class="px-4 py-3 text-right font-semibold text-primary-700">أنشأ بواسطة</th>
                        <th class="px-4 py-3 text-center font-semibold text-primary-700">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($depreciations as $dep)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-primary-600 font-semibold">{{ $dep->depreciation_number }}</td>
                            <td class="px-4 py-3">{{ $dep->branch->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $dep->date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 font-semibold text-red-600">{{ number_format($dep->total_loss, 2) }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700'];
                                @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$dep->status] ?? '' }}">{{ $dep->status_label }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $dep->admin->name }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('product-depreciations.show', $dep->id) }}" class="p-1.5 text-primary-600 hover:bg-primary-50 rounded-lg inline-flex" title="عرض">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">لا توجد طلبات إهلاك</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">{{ $depreciations->links() }}</div>
    </div>
</div>
