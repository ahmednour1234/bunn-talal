@props(['id', 'title' => '', 'maxWidth' => 'lg'])

@php
    $maxWidthClass = match($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-lg',
    };
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal-{{ $id }}.window="open = true"
    x-on:close-modal-{{ $id }}.window="open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100] overflow-y-auto"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50"
        @click="open = false"
    ></div>

    {{-- Modal Content --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-white rounded-2xl shadow-xl {{ $maxWidthClass }} w-full"
            @click.stop
        >
            {{-- Header --}}
            @if($title)
                <div class="flex items-center justify-between p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-primary-700">{{ $title }}</h3>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <x-icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>
            @endif

            {{-- Body --}}
            <div class="p-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
