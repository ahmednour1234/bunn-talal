<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700 tracking-tight">إدارة التصنيفات</h1>
            <p class="text-sm text-gray-400 mt-0.5">عرض وإدارة جميع التصنيفات</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('categories.create'))
            <x-button variant="primary" href="{{ route('categories.create') }}">
                <x-icon name="plus" class="w-4 h-4" />إضافة تصنيف
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
    $catColors = [
        ['from-fuchsia-400','to-pink-600','bg-fuchsia-50'],
        ['from-cyan-400','to-blue-600','bg-cyan-50'],
        ['from-lime-400','to-green-600','bg-lime-50'],
        ['from-amber-400','to-orange-600','bg-amber-50'],
        ['from-violet-400','to-purple-600','bg-violet-50'],
        ['from-rose-400','to-red-600','bg-rose-50'],
    ];
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @forelse($categories as $category)
            @php $cc = $catColors[$category->id % count($catColors)]; @endphp
            <div class="flex items-center gap-4 bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 hover:shadow-md hover:scale-[1.01] transition-all">
                <div class="w-14 h-14 rounded-2xl overflow-hidden flex-shrink-0 shadow-sm">
                    @if($category->image)
                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gradient-to-br {{ $cc[0] }} {{ $cc[1] }} flex items-center justify-center text-white font-extrabold text-xl">
                            {{ mb_substr($category->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-800 text-sm truncate">{{ $category->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $category->created_at->format('Y/m/d') }}</p>
                    <div class="mt-1.5">
                        @if($category->is_active)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>نشط
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>معطل
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col gap-1.5">
                    @if(auth('admin')->user()?->hasPermission('categories.edit'))
                        <a href="{{ route('categories.edit', $category) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                        </a>
                    @endif
                    @if(auth('admin')->user()?->hasPermission('categories.delete'))
                        <button wire:click="delete({{ $category->id }})" wire:confirm="هل أنت متأكد من حذف هذا التصنيف؟" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white rounded-2xl border border-gray-100 py-16 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-14 h-14 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" /></svg>
                <p class="text-gray-400 text-sm">لا توجد تصنيفات مسجلة</p>
            </div>
        @endforelse
    </div>
    @if($categories->hasPages())<div class="mt-4">{{ $categories->links() }}</div>@endif
</div>