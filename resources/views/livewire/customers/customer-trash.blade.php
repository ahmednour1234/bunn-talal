<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700 tracking-tight">سلة محذوفات العملاء</h1>
            <p class="text-sm text-gray-400 mt-0.5">العملاء المحذوفون — يمكن استعادتهم أو حذفهم نهائياً</p>
        </div>
        <a href="{{ route('customers.index') }}"
            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium bg-stone-100 text-stone-600 hover:bg-stone-200 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            العودة للعملاء
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 text-green-700 text-sm font-medium border border-green-100">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-700 text-sm font-medium border border-red-100">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm text-right">
            <thead>
                <tr class="bg-primary-700">
                    <th class="px-4 py-3 text-xs font-bold text-white text-center">#</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">العميل</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">الهاتف</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">التصنيف</th>
                    <th class="px-4 py-3 text-xs font-bold text-white">تاريخ الحذف</th>
                    <th class="px-4 py-3 text-xs font-bold text-white text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @php $classificationLabels = ['premium' => 'مميز', 'medium' => 'متوسط', 'regular' => 'عادي']; @endphp
                @forelse($customers as $i => $customer)
                <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-stone-50/40' }} hover:bg-stone-50 transition-colors border-b border-gray-50">
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono text-center">{{ $i + 1 }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 justify-end">
                            <div class="text-right min-w-0 flex-1">
                                <p class="font-bold text-gray-800 text-sm truncate">{{ $customer->name }}</p>
                                <p class="text-xs text-gray-400 truncate" dir="ltr">{{ $customer->email ?? '—' }}</p>
                            </div>
                            <div class="w-9 h-9 rounded-lg bg-stone-300 flex items-center justify-center text-stone-600 font-extrabold text-sm flex-shrink-0">
                                {{ mb_substr($customer->name, 0, 1) }}
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-700 font-medium text-sm" dir="ltr">{{ $customer->phone ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-stone-100 text-stone-700 whitespace-nowrap">
                            {{ $classificationLabels[$customer->classification] ?? $customer->classification }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $customer->deleted_at->format('Y-m-d H:i') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 justify-center">
                            <button wire:click="restore({{ $customer->id }})"
                                wire:confirm="هل تريد استعادة هذا العميل؟"
                                class="w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 flex items-center justify-center transition-colors flex-shrink-0" title="استعادة">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                            </button>
                            <button wire:click="forceDelete({{ $customer->id }})"
                                wire:confirm="تحذير: سيتم حذف العميل نهائياً ولا يمكن التراجع. هل أنت متأكد؟"
                                class="w-8 h-8 rounded-lg bg-stone-100 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors flex-shrink-0" title="حذف نهائي">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        <p class="text-gray-400 text-sm">سلة المحذوفات فارغة</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($customers->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>
