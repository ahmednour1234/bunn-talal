<div>
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">إدارة المنتجات</h1>
            <p class="text-sm text-gray-500 mt-1">عرض وإدارة جميع المنتجات والمخزون</p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth('admin')->user()?->hasPermission('products.create'))
                <x-button variant="secondary" href="{{ route('products.import.template') }}" size="sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    تحميل القالب
                </x-button>
                <x-button variant="primary" href="{{ route('products.create') }}">
                    <x-icon name="plus" class="w-4 h-4" />
                    إضافة منتج
                </x-button>
            @endif
        </div>
    </div>

    {{-- Search --}}
    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-6">
        <div class="relative">
            <x-icon name="search" class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="بحث بالاسم أو التصنيف..."
                class="w-full pr-10 pl-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
            >
        </div>
    </div>

    {{-- Import Form --}}
    @if(auth('admin')->user()?->hasPermission('products.create'))
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-4 mb-6">
            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-4">
                @csrf
                <label class="text-sm font-medium text-gray-700">استيراد من Excel:</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="text-sm border border-gray-200 rounded-lg p-2 file:ml-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700">
                <x-button type="submit" variant="success" size="sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                    استيراد
                </x-button>
            </form>
        </div>
    @endif

    {{-- Table --}}
    <x-data-table :headers="['#', 'الصورة', 'اسم المنتج', 'التصنيف', 'الوحدة', 'سعر التكلفة', 'سعر البيع', 'الخصم', 'الضريبة', 'الكمية الكلية', 'الحالة', 'الإجراءات']">
        @forelse($products as $product)
            <tr class="hover:bg-primary-50/50 transition-colors">
                <td class="px-6 py-4 text-gray-500">{{ $product->id }}</td>
                <td class="px-6 py-4">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover border border-gray-200">
                    @else
                        <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-primary-400"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                        </div>
                    @endif
                </td>
                <td class="px-6 py-4 font-medium text-gray-800">{{ $product->name }}</td>
                <td class="px-6 py-4 text-gray-600">{{ $product->category->name ?? '-' }}</td>
                <td class="px-6 py-4 text-gray-600">{{ $product->unit->name ?? '-' }}</td>
                <td class="px-6 py-4 text-gray-600" dir="ltr">{{ number_format($product->cost_price, 2) }}</td>
                <td class="px-6 py-4 text-gray-600" dir="ltr">{{ number_format($product->selling_price, 2) }}</td>
                <td class="px-6 py-4 text-gray-600" dir="ltr">
                    {{ number_format($product->discount, 2) }}
                    <span class="text-xs text-gray-400">{{ $product->discount_type === 'percentage' ? '%' : 'ثابت' }}</span>
                </td>
                <td class="px-6 py-4 text-gray-600">
                    @if($product->tax)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">{{ $product->tax->name }} ({{ $product->tax->formatted_rate }})</span>
                    @else
                        <span class="text-gray-400 text-xs">—</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $product->total_quantity > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $product->total_quantity }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    @if($product->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">نشط</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">معطل</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        @if(auth('admin')->user()?->hasPermission('products.edit'))
                            <button wire:click="toggleActive({{ $product->id }})"
                                class="p-2 {{ $product->is_active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition-colors"
                                title="{{ $product->is_active ? 'تعطيل' : 'تفعيل' }}">
                                @if($product->is_active)
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                @endif
                            </button>
                            <a href="{{ route('products.edit', $product) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="تعديل">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </a>
                        @endif
                        @if(auth('admin')->user()?->hasPermission('products.delete'))
                            <button wire:click="delete({{ $product->id }})" wire:confirm="هل أنت متأكد من حذف هذا المنتج؟" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="حذف">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="px-6 py-12 text-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-gray-300"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                    <p>لا توجد منتجات مسجلة</p>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $products->links() }}
        </x-slot:pagination>
    </x-data-table>
</div>
