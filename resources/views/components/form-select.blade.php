@props(['label', 'name', 'options' => [], 'selected' => null, 'required' => false, 'error' => null, 'placeholder' => 'اختر...'])

<div class="mb-4">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>
    <select
        id="{{ $name }}"
        {{ $attributes->merge(['class' => 'w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all duration-200 text-sm' . ($error ? ' border-red-400 ring-1 ring-red-300' : '')]) }}
        @if($required) required @endif
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $value => $label)
            <option value="{{ $value }}" @selected($value == $selected)>{{ $label }}</option>
        @endforeach
    </select>
    @if($error)
        <p class="text-red-500 text-xs mt-1">{{ $error }}</p>
    @endif
</div>
