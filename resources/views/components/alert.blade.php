@props(['type' => 'success', 'message'])

@php
    $classes = match($type) {
        'success' => 'bg-green-50 border-green-300 text-green-800',
        'error' => 'bg-red-50 border-red-300 text-red-800',
        'warning' => 'bg-yellow-50 border-yellow-300 text-yellow-800',
        'info' => 'bg-blue-50 border-blue-300 text-blue-800',
        default => 'bg-green-50 border-green-300 text-green-800',
    };
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 4000)"
    x-transition:leave="ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    class="rounded-lg border px-4 py-3 mb-4 flex items-center justify-between {{ $classes }}"
>
    <span class="text-sm font-medium">{{ $message }}</span>
    <button @click="show = false" class="hover:opacity-70 transition-opacity">
        <x-icon name="x-mark" class="w-4 h-4" />
    </button>
</div>
