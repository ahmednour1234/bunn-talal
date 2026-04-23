@props(['variant' => 'primary', 'size' => 'md', 'href' => null])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 shadow-sm';

    $variants = match($variant) {
        'primary'   => 'bg-gradient-to-l from-primary-700 to-primary-500 text-white hover:from-primary-800 hover:to-primary-600 focus:ring-primary-400 hover:shadow-md hover:shadow-primary-200',
        'secondary' => 'bg-white text-primary-700 border-2 border-primary-200 hover:bg-primary-50 hover:border-primary-400 focus:ring-primary-300',
        'danger'    => 'bg-gradient-to-l from-red-700 to-red-500 text-white hover:from-red-800 hover:to-red-600 focus:ring-red-400 hover:shadow-md hover:shadow-red-200',
        'success'   => 'bg-gradient-to-l from-green-700 to-green-500 text-white hover:from-green-800 hover:to-green-600 focus:ring-green-400 hover:shadow-md hover:shadow-green-200',
        'ghost'     => 'text-gray-600 hover:bg-gray-100 focus:ring-gray-300 shadow-none',
        default     => 'bg-gradient-to-l from-primary-700 to-primary-500 text-white hover:from-primary-800 hover:to-primary-600 focus:ring-primary-400',
    };

    $sizes = match($size) {
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-7 py-3.5 text-base',
        default => 'px-5 py-2.5 text-sm',
    };

    $classes = "$base $variants $sizes";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }}>
        {{ $slot }}
    </button>
@endif