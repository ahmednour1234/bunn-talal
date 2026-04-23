@props(['headers' => [], 'empty' => 'لا توجد بيانات'])

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary-700">
                    @foreach($headers as $header)
                        <th class="px-5 py-4 text-right font-bold text-white whitespace-nowrap text-xs tracking-wide">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if(isset($pagination))
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
            {{ $pagination }}
        </div>
    @endif
</div>