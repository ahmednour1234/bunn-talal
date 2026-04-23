@props(['value', 'label', 'icon' => null, 'color' => 'primary'])

@php
    $colorClasses = match($color) {
        'primary' => 'bg-primary-50 border-primary-200',
        'green' => 'bg-green-50 border-green-200',
        'blue' => 'bg-blue-50 border-blue-200',
        'yellow' => 'bg-yellow-50 border-yellow-200',
        'orange' => 'bg-orange-50 border-orange-200',
        default => 'bg-primary-50 border-primary-200',
    };
    $valueColor = match($color) {
        'primary' => 'text-primary-700',
        'green' => 'text-green-700',
        'blue' => 'text-blue-700',
        'yellow' => 'text-yellow-700',
        'orange' => 'text-orange-700',
        default => 'text-primary-700',
    };
@endphp

<div class="rounded-xl border {{ $colorClasses }} p-6 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 mb-1">{{ $label }}</p>
            <p class="text-3xl font-bold {{ $valueColor }}">{{ $value }}</p>
        </div>
        @if($icon)
            <div class="p-3 rounded-lg {{ $colorClasses }}">
                <x-icon :name="$icon" class="w-8 h-8 {{ $valueColor }}" />
            </div>
        @endif
    </div>
    @if(isset($subtitle))
        <p class="text-xs text-gray-400 mt-2">{{ $subtitle }}</p>
    @endif
</div>
