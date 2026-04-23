<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $delegateId ? 'تعديل المندوب' : 'إضافة مندوب جديد' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $delegateId ? 'تعديل بيانات المندوب' : 'إضافة مندوب مبيعات جديد إلى النظام' }}</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-8">

            {{-- Basic Info --}}
            <div>
                <h3 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">البيانات الأساسية</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <x-form-input label="اسم المندوب" name="name" wire:model="name" placeholder="مثال: محمد أحمد" required :error="$errors->first('name')" />
                    <x-form-input label="رقم الهاتف" name="phone" wire:model="phone" placeholder="مثال: 01012345678" :error="$errors->first('phone')" />
                    <x-form-input label="البريد الإلكتروني" name="email" type="email" wire:model="email" placeholder="مثال: delegate@example.com" :error="$errors->first('email')" />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور @if(!$delegateId)<span class="text-red-500">*</span>@endif</label>
                        <input type="password" wire:model="password"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
                            placeholder="{{ $delegateId ? 'اتركها فارغة إذا لا تريد التغيير' : 'كلمة المرور' }}">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-form-input label="رقم البطاقة" name="national_id" wire:model="national_id" placeholder="مثال: 29901012345678" :error="$errors->first('national_id')" />

                    {{-- National ID Image --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">صورة البطاقة</label>
                        <input type="file" wire:model="national_id_image" accept="image/*"
                            class="w-full border border-gray-200 rounded-lg p-2 text-sm file:ml-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        @error('national_id_image')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <div class="mt-2">
                            @if($national_id_image && !$errors->has('national_id_image'))
                                <img src="{{ $national_id_image->temporaryUrl() }}" alt="معاينة" class="w-20 h-14 rounded-lg object-cover border border-gray-200">
                            @elseif($existingIdImage)
                                <img src="{{ asset('storage/' . $existingIdImage) }}" alt="صورة البطاقة" class="w-20 h-14 rounded-lg object-cover border border-gray-200">
                            @endif
                        </div>
                    </div>

                    {{-- Active --}}
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5">
                            <span class="text-sm font-medium text-gray-700">مندوب نشط</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Assignments --}}
            <div>
                <h3 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">الفروع والمناطق والتصنيفات</h3>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Branches --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الفروع</label>
                        <div class="border border-gray-200 rounded-lg p-3 max-h-48 overflow-y-auto space-y-2 bg-gray-50">
                            @foreach($branches as $branch)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-white p-1.5 rounded transition-colors">
                                    <input type="checkbox" wire:model="selectedBranches" value="{{ $branch->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                                    <span class="text-sm text-gray-700">{{ $branch->name }}</span>
                                </label>
                            @endforeach
                            @if($branches->isEmpty())
                                <p class="text-xs text-gray-400 text-center py-2">لا توجد فروع</p>
                            @endif
                        </div>
                    </div>

                    {{-- Areas --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">المناطق</label>
                        <div class="border border-gray-200 rounded-lg p-3 max-h-48 overflow-y-auto space-y-2 bg-gray-50">
                            @foreach($areas as $area)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-white p-1.5 rounded transition-colors">
                                    <input type="checkbox" wire:model="selectedAreas" value="{{ $area->id }}" class="rounded border-gray-300 text-green-600 focus:ring-green-500 w-4 h-4">
                                    <span class="text-sm text-gray-700">{{ $area->name }}</span>
                                </label>
                            @endforeach
                            @if($areas->isEmpty())
                                <p class="text-xs text-gray-400 text-center py-2">لا توجد مناطق</p>
                            @endif
                        </div>
                    </div>

                    {{-- Categories --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">التصنيفات</label>
                        <div class="border border-gray-200 rounded-lg p-3 max-h-48 overflow-y-auto space-y-2 bg-gray-50">
                            @foreach($categories as $category)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-white p-1.5 rounded transition-colors">
                                    <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 w-4 h-4">
                                    <span class="text-sm text-gray-700">{{ $category->name }}</span>
                                </label>
                            @endforeach
                            @if($categories->isEmpty())
                                <p class="text-xs text-gray-400 text-center py-2">لا توجد تصنيفات</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Financial Info --}}
            <div>
                <h3 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">البيانات المالية</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حد البيع الآجل <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="credit_sales_limit" step="0.01" min="0"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm" placeholder="0.00">
                        <p class="text-xs text-gray-400 mt-1">الحد الأقصى للبيع الآجل</p>
                        @error('credit_sales_limit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">العهدة <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="cash_custody" step="0.01" min="0"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm" placeholder="0.00">
                        <p class="text-xs text-gray-400 mt-1">عهدة الفلوس عند المندوب</p>
                        @error('cash_custody') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">نسبة العمولة % <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="sales_commission_rate" step="0.01" min="0" max="100"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm" placeholder="0">
                        @error('sales_commission_rate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">محصّل <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="total_collected" step="0.01" min="0"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm" placeholder="0.00">
                        @error('total_collected') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">عليه <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="total_due" step="0.01" min="0"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm" placeholder="0.00">
                        @error('total_due') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Location --}}
            <div>
                <h3 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">الموقع الحالي</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">خط العرض</label>
                        <input type="number" wire:model="current_latitude" step="0.0000001" id="latitude"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm" placeholder="30.0444">
                        @error('current_latitude') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">خط الطول</label>
                        <input type="number" wire:model="current_longitude" step="0.0000001" id="longitude"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm" placeholder="31.2357">
                        @error('current_longitude') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div id="map" class="w-full h-72 rounded-xl border border-gray-200 bg-gray-100"></div>
                <p class="text-xs text-gray-400 mt-2">اضغط على الخريطة لتحديد موقع المندوب الحالي</p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    {{ $delegateId ? 'تحديث المندوب' : 'حفظ المندوب' }}
                </x-button>
                <x-button variant="secondary" href="{{ route('delegates.index') }}">
                    إلغاء
                </x-button>
            </div>
        </form>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener('livewire:navigated', initMap);
            document.addEventListener('DOMContentLoaded', initMap);

            function initMap() {
                const mapEl = document.getElementById('map');
                if (!mapEl || mapEl._leaflet_id) return;

                const lat = @js($current_latitude ?? 30.0444);
                const lng = @js($current_longitude ?? 31.2357);
                const hasCoords = @js($current_latitude && $current_longitude);

                const map = L.map('map').setView([lat, lng], hasCoords ? 15 : 6);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);

                let marker = null;
                if (hasCoords) {
                    marker = L.marker([lat, lng]).addTo(map);
                }

                map.on('click', function (e) {
                    const { lat, lng } = e.latlng;
                    if (marker) map.removeLayer(marker);
                    marker = L.marker([lat, lng]).addTo(map);

                    document.getElementById('latitude').value = lat.toFixed(7);
                    document.getElementById('longitude').value = lng.toFixed(7);
                    document.getElementById('latitude').dispatchEvent(new Event('input'));
                    document.getElementById('longitude').dispatchEvent(new Event('input'));
                });
            }
        </script>
    @endpush
</div>
