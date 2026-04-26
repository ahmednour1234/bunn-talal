<div dir="rtl" class="space-y-6">
    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm font-bold px-5 py-3 rounded-2xl flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm font-bold px-5 py-3 rounded-2xl flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">سجل الإجازات</h1>
            <p class="text-sm text-gray-500 mt-1">إدارة إجازات المناديب</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('hr.create'))
        <x-button variant="primary" href="{{ route('hr.leaves.create') }}">
            <x-icon name="plus" class="w-4 h-4" />
            إضافة إجازة
        </x-button>
        @endif
    </div>

    {{-- Filters --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="relative">
                <x-icon name="search" class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث باسم المندوب..."
                    class="w-full pr-10 pl-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
            </div>
            <select wire:model.live="filterDelegate" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                <option value="">كل المناديب</option>
                @foreach($delegates as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterType" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                <option value="">كل الأنواع</option>
                @foreach($types as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all text-sm">
                <option value="">كل الحالات</option>
                @foreach($statuses as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Table --}}
    <x-data-table :headers="['#', 'المندوب', 'النوع', 'من', 'إلى', 'الأيام', 'الحالة', 'الموافق', 'الإجراءات']">
        @forelse($leaves as $leave)
        <tr class="hover:bg-primary-50/50 transition-colors">
            <td class="px-6 py-4 text-gray-500 text-sm">{{ $leave->id }}</td>
            <td class="px-6 py-4 font-semibold text-gray-800">{{ $leave->delegate->name }}</td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ $leave->type_label }}</span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ $leave->start_date->format('Y-m-d') }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ $leave->end_date->format('Y-m-d') }}</td>
            <td class="px-6 py-4 text-sm font-bold text-gray-700">{{ $leave->days }}</td>
            <td class="px-6 py-4">
                @if($leave->status === 'approved')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">موافق عليها</span>
                @elseif($leave->status === 'rejected')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">مرفوضة</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">قيد الانتظار</span>
                @endif
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $leave->approvedBy?->name ?? '-' }}</td>
            <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                    @if($leave->status === 'pending' && auth('admin')->user()?->hasPermission('hr.edit'))
                        <button wire:click="approve({{ $leave->id }})" wire:confirm="تأكيد الموافقة على الإجازة؟"
                            class="text-xs bg-green-100 hover:bg-green-200 text-green-700 px-3 py-1.5 rounded-lg font-semibold transition-colors">
                            موافقة
                        </button>
                        <button wire:click="openReject({{ $leave->id }})"
                            class="text-xs bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1.5 rounded-lg font-semibold transition-colors">
                            رفض
                        </button>
                    @endif
                    @if(auth('admin')->user()?->hasPermission('hr.edit'))
                    <a href="{{ route('hr.leaves.edit', $leave->id) }}"
                        class="text-xs bg-primary-100 hover:bg-primary-200 text-primary-700 px-3 py-1.5 rounded-lg font-semibold transition-colors">
                        تعديل
                    </a>
                    @endif
                    @if(auth('admin')->user()?->hasPermission('hr.delete'))
                    <button wire:click="delete({{ $leave->id }})" wire:confirm="هل تريد حذف هذا السجل؟"
                        class="text-xs bg-gray-100 hover:bg-red-100 text-gray-600 hover:text-red-600 px-3 py-1.5 rounded-lg font-semibold transition-colors">
                        حذف
                    </button>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="9" class="px-6 py-12 text-center text-gray-400">لا توجد إجازات</td></tr>
        @endforelse
    </x-data-table>
    <div class="mt-4">{{ $leaves->links() }}</div>

    {{-- Reject Modal --}}
    @if($showRejectModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="$set('showRejectModal', false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" dir="rtl">
            <h3 class="text-lg font-bold text-gray-800 mb-4">سبب رفض الإجازة</h3>
            <textarea wire:model="rejectionReason" rows="3" placeholder="اكتب سبب الرفض..."
                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-red-300 focus:border-red-400 transition-all"></textarea>
            @error('rejectionReason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            <div class="flex gap-3 mt-4">
                <button wire:click="confirmReject"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 rounded-xl transition-colors">
                    تأكيد الرفض
                </button>
                <button wire:click="$set('showRejectModal', false)"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl transition-colors">
                    إلغاء
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
