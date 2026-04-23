<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700 tracking-tight">إدارة الأدوار</h1>
            <p class="text-sm text-gray-400 mt-0.5">عرض وإدارة أدوار وصلاحيات النظام</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('roles.create'))
            <x-button variant="primary" href="{{ route('roles.create') }}">
                <x-icon name="plus" class="w-4 h-4" />إضافة دور
            </x-button>
        @endif
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5">
        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="بحث بالاسم..."
                class="w-full pr-9 pl-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 text-sm focus:bg-white focus:ring-2 focus:ring-primary-300 transition-all">
        </div>
    </div>
    @php
    $roleGradients = [
        ['from-emerald-400','to-teal-600'],['from-blue-400','to-indigo-600'],
        ['from-violet-400','to-purple-600'],['from-amber-400','to-orange-600'],
        ['from-rose-400','to-pink-600'],['from-cyan-400','to-blue-600'],
    ];
    @endphp
    <div class="space-y-2.5">
        @forelse($roles as $role)
            @php $rg = $roleGradients[$role->id % count($roleGradients)]; @endphp
            <div class="flex items-center gap-4 bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 hover:shadow-md hover:border-emerald-100 transition-all">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br {{ $rg[0] }} {{ $rg[1] }} flex items-center justify-center text-white flex-shrink-0 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                </div>
                <div class="w-48 min-w-0">
                    <p class="font-bold text-gray-800 text-sm truncate">{{ $role->display_name }}</p>
                    <p class="text-xs text-gray-400 font-mono" dir="ltr">{{ $role->name }}</p>
                </div>
                <div class="hidden md:flex items-center gap-2 flex-1">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-violet-100 text-violet-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" /></svg>
                        {{ $role->permissions_count ?? $role->permissions->count() }} صلاحية
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                        {{ $role->admins_count ?? $role->admins->count() }} مستخدم
                    </span>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    @if(auth('admin')->user()?->hasPermission('roles.edit'))
                        <a href="{{ route('roles.edit', $role) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                        </a>
                    @endif
                    @if(auth('admin')->user()?->hasPermission('roles.delete'))
                        <button wire:click="delete({{ $role->id }})" wire:confirm="هل أنت متأكد من حذف هذا الدور؟" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 py-16 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-14 h-14 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                <p class="text-gray-400 text-sm">لا توجد أدوار مسجلة</p>
            </div>
        @endforelse
    </div>
    @if($roles->hasPages())<div class="mt-4">{{ $roles->links() }}</div>@endif
</div>