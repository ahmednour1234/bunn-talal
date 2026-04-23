<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700 tracking-tight">إدارة المدراء</h1>
            <p class="text-sm text-gray-400 mt-0.5">عرض وإدارة جميع مدراء النظام</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('admins.create'))
            <x-button variant="primary" href="{{ route('admins.create') }}">
                <x-icon name="plus" class="w-4 h-4" />إضافة مدير
            </x-button>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5">
        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث بالاسم أو البريد الإلكتروني..."
                class="w-full pr-9 pl-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 text-sm focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all">
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm text-right table-fixed">
            <colgroup>
                <col class="w-10">
                <col class="w-[28%]">
                <col class="w-[25%]">
                <col class="w-[20%]">
                <col class="w-[13%]">
                <col class="w-[14%]">
            </colgroup>
            <thead>
                <tr class="bg-primary-700">
                    <th class="px-4 py-3 text-xs font-bold text-white text-center">#</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">المدير</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">البريد الإلكتروني</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">الأدوار</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">تاريخ الإنشاء</th>
                    <th class="px-4 py-3 text-xs font-bold text-white text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $i => $adminItem)
                <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-stone-50/40' }} hover:bg-stone-50 transition-colors border-b border-gray-50">
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono text-center">{{ $i + 1 }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 justify-end">
                            <p class="font-bold text-gray-800 text-sm truncate">{{ $adminItem->name }}</p>
                            <div class="w-9 h-9 rounded-lg bg-primary-800 flex items-center justify-center text-white font-extrabold text-sm flex-shrink-0">
                                {{ mb_substr($adminItem->name, 0, 1) }}
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs truncate" dir="ltr">{{ $adminItem->email }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1 justify-end">
                            @foreach($adminItem->roles as $role)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-stone-100 text-stone-700 whitespace-nowrap">{{ $role->display_name }}</span>
                            @endforeach
                            @if($adminItem->roles->isEmpty())
                                <span class="text-xs text-gray-400">بدون أدوار</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $adminItem->created_at->format('Y/m/d') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 justify-center">
                            @if(auth('admin')->user()?->hasPermission('admins.edit'))
                                <a href="{{ route('admins.edit', $adminItem) }}" class="w-8 h-8 rounded-lg bg-stone-100 text-stone-600 hover:bg-stone-200 flex items-center justify-center transition-colors flex-shrink-0" title="تعديل">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                                </a>
                            @endif
                            @if(auth('admin')->user()?->hasPermission('admins.delete'))
                                <button wire:click="delete({{ $adminItem->id }})" wire:confirm="هل أنت متأكد من حذف هذا المدير؟"
                                    class="w-8 h-8 rounded-lg bg-stone-100 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors flex-shrink-0" title="حذف">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        <p class="text-gray-400 text-sm">لا يوجد مدراء مسجلون</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($admins->hasPages())<div class="mt-4">{{ $admins->links() }}</div>@endif
</div>