<div class="relative" wire:poll.30s x-data="{ open: @entangle('open') }" @click.outside="open = false">

    {{-- Bell Button --}}
    <button @click="open = !open"
        class="relative w-9 h-9 flex items-center justify-center rounded-xl transition-all duration-200 focus:outline-none
               {{ $count > 0 ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600' }}">

        {{-- Bell icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        {{-- Pulsing dot badge --}}
        @if($count > 0)
        <span class="absolute top-1 right-1 flex h-2.5 w-2.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
        </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute left-0 mt-3 w-[340px] bg-white rounded-2xl shadow-2xl border border-gray-100/80 z-50 overflow-hidden origin-top-left">

        {{-- Arrow --}}
        <div class="absolute -top-1.5 left-3 w-3 h-3 bg-white border-l border-t border-gray-100 rotate-45 z-10"></div>

        {{-- Header --}}
        <div class="relative flex items-center justify-between px-5 py-3.5 bg-gradient-to-l from-primary-800 to-primary-900">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                <span class="text-sm font-bold text-white">الإشعارات</span>
            </div>
            @if($count > 0)
            <span class="text-[11px] font-bold text-primary-800 bg-white/90 px-2.5 py-0.5 rounded-full shadow-sm">
                {{ $count }} {{ $count === 1 ? 'إشعار' : 'إشعارات' }}
            </span>
            @else
            <span class="text-[11px] text-primary-300">لا جديد</span>
            @endif
        </div>

        {{-- List --}}
        <div class="max-h-[340px] overflow-y-auto">
            @forelse($notifications as $notification)

            @php
                $colors = match($notification['icon']) {
                    'sale'     => ['bg' => 'bg-amber-50',  'border' => 'border-amber-200',  'icon_bg' => 'bg-amber-100',  'icon_text' => 'text-amber-600',  'dot' => 'bg-amber-400'],
                    'booking'  => ['bg' => 'bg-blue-50',   'border' => 'border-blue-200',   'icon_bg' => 'bg-blue-100',   'icon_text' => 'text-blue-600',   'dot' => 'bg-blue-400'],
                    'purchase' => ['bg' => 'bg-red-50',    'border' => 'border-red-200',    'icon_bg' => 'bg-red-100',    'icon_text' => 'text-red-600',    'dot' => 'bg-red-400'],
                    default    => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'icon_bg' => 'bg-orange-100', 'icon_text' => 'text-orange-600', 'dot' => 'bg-orange-400'],
                };
            @endphp

            <a href="{{ $notification['route'] }}"
                @click="open = false"
                class="flex items-center gap-3 px-4 py-3 border-b border-gray-50 hover:bg-gray-50/80 transition-colors group relative">

                {{-- Colored left accent bar --}}
                <div class="absolute right-0 top-3 bottom-3 w-[3px] rounded-l-full {{ $colors['dot'] }} opacity-0 group-hover:opacity-100 transition-opacity"></div>

                {{-- Icon --}}
                <div class="flex-shrink-0 w-9 h-9 rounded-xl {{ $colors['icon_bg'] }} flex items-center justify-center shadow-sm">
                    @if($notification['icon'] === 'sale')
                    <svg class="w-[18px] h-[18px] {{ $colors['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75" />
                    </svg>
                    @elseif($notification['icon'] === 'booking')
                    <svg class="w-[18px] h-[18px] {{ $colors['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    @elseif($notification['icon'] === 'purchase')
                    <svg class="w-[18px] h-[18px] {{ $colors['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                    </svg>
                    @else
                    <svg class="w-[18px] h-[18px] {{ $colors['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                    @endif
                </div>

                {{-- Text + arrow --}}
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] text-gray-700 group-hover:text-primary-800 font-medium leading-snug truncate">
                        {{ $notification['text'] }}
                    </p>
                    <p class="text-[11px] text-gray-400 mt-0.5">اضغط للعرض</p>
                </div>

                <svg class="w-4 h-4 text-gray-300 group-hover:text-primary-400 flex-shrink-0 rotate-180 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>

            @empty
            <div class="flex flex-col items-center justify-center py-12 px-6 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-500">كل شيء على ما يرام!</p>
                <p class="text-xs text-gray-400 mt-1">لا توجد إشعارات تحتاج انتباهك</p>
            </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if($count > 0)
        <div class="px-4 py-2.5 bg-gray-50 border-t border-gray-100 text-center">
            <p class="text-[11px] text-gray-400">يتم التحديث تلقائياً كل 30 ثانية</p>
        </div>
        @endif
    </div>
</div>
