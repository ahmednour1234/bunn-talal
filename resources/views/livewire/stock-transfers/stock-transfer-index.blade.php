<div>
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">تحويلات المخزون</h1>
            <p class="text-sm text-gray-500 mt-1">إدارة تحويلات المنتجات بين الفروع</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('stock-transfers.create'))
            <x-button variant="primary" href="{{ route('stock-transfers.create') }}">
                <x-icon name="plus" class="w-4 h-4" />
                طلب تحويل جديد
            </x-button>
        @endif
    </div>

    {{-- Filters --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <x-icon name="search" class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث..."
                    class="w-full pr-10 pl-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
            </div>
            <select wire:model.live="statusFilter"
                class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                <option value="">كل الحالات</option>
                <option value="pending">قيد الانتظار</option>
                <option value="approved">تمت الموافقة</option>
                <option value="rejected">مرفوض</option>
                <option value="received">تم الاستلام</option>
            </select>
            <select wire:model.live="branchFilter"
                class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                <option value="">كل الفروع</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Table --}}
    <x-data-table :headers="['#', 'من فرع', 'إلى فرع', 'طلب بواسطة', 'عدد الأصناف', 'الحالة', 'التاريخ', 'الإجراءات']">
        @forelse($transfers as $transfer)
            <tr class="hover:bg-primary-50/50 transition-colors">
                <td class="px-6 py-4 text-gray-500">{{ $transfer->id }}</td>
                <td class="px-6 py-4 font-medium text-gray-800">{{ $transfer->fromBranch->name }}</td>
                <td class="px-6 py-4 font-medium text-gray-800">{{ $transfer->toBranch->name }}</td>
                <td class="px-6 py-4 text-gray-600">{{ $transfer->requestedBy->name }}</td>
                <td class="px-6 py-4 text-gray-600">{{ $transfer->items->count() }}</td>
                <td class="px-6 py-4">
                    @php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-700',
                            'approved' => 'bg-blue-100 text-blue-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            'received' => 'bg-green-100 text-green-700',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$transfer->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $transfer->status_label }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-500 text-xs">{{ $transfer->created_at->format('Y/m/d H:i') }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-1">
                        @if($transfer->status === 'pending' && auth('admin')->user()?->hasPermission('stock-transfers.approve'))
                            <button wire:click="approve({{ $transfer->id }})" wire:confirm="هل أنت متأكد من الموافقة على هذا التحويل؟"
                                class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="موافقة">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            </button>
                            <button wire:click="reject({{ $transfer->id }})" wire:confirm="هل أنت متأكد من رفض هذا التحويل؟"
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="رفض">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            </button>
                        @endif
                        @if($transfer->status === 'approved' && auth('admin')->user()?->hasPermission('stock-transfers.receive'))
                            <button wire:click="receive({{ $transfer->id }})" wire:confirm="هل أنت متأكد من استلام هذا التحويل؟"
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="استلام">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" /></svg>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>

            {{-- Expandable items row --}}
            @if($transfer->items->count())
                <tr class="bg-gray-50/50">
                    <td colspan="8" class="px-8 py-3">
                        <div class="flex flex-wrap gap-2">
                            @foreach($transfer->items as $item)
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-white rounded-lg border border-gray-200 text-xs text-gray-600">
                                    {{ $item->product->name ?? '-' }}
                                    <span class="font-bold text-primary-700">×{{ $item->quantity }}</span>
                                </span>
                            @endforeach
                        </div>
                    </td>
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-gray-300"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                    <p>لا توجد تحويلات مخزون</p>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $transfers->links() }}
        </x-slot:pagination>
    </x-data-table>
</div>
