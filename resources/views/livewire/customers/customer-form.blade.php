<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $customerId ? 'تعديل العميل' : 'إضافة عميل جديد' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $customerId ? 'تعديل بيانات العميل' : 'إضافة عميل جديد إلى النظام' }}</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary-700">البيانات الأساسية</h3>
        </div>

        <form wire:submit="save" class="p-6 space-y-8">
            {{-- Basic Info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Name --}}
                <x-form-input
                    label="اسم العميل"
                    name="name"
                    wire:model="name"
                    placeholder="مثال: أحمد محمد"
                    required
                    :error="$errors->first('name')"
                />

                {{-- Phone --}}
                <x-form-input
                    label="رقم الهاتف"
                    name="phone"
                    wire:model="phone"
                    placeholder="مثال: 01012345678"
                    :error="$errors->first('phone')"
                />

                {{-- Email --}}
                <x-form-input
                    label="البريد الإلكتروني"
                    name="email"
                    type="email"
                    wire:model="email"
                    placeholder="مثال: ahmed@example.com"
                    :error="$errors->first('email')"
                />

                {{-- Classification --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تصنيف العميل <span class="text-red-500">*</span></label>
                    <select wire:model="classification"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                        @foreach($classificationLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('classification')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Area --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">المنطقة</label>
                    <select wire:model="area_id"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm">
                        <option value="">اختر المنطقة</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                        @endforeach
                    </select>
                    @error('area_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Active --}}
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5">
                        <span class="text-sm font-medium text-gray-700">عميل نشط</span>
                    </label>
                </div>
            </div>

            {{-- Financial Info --}}
            <div>
                <h4 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">البيانات المالية</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Credit Limit --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الحد الائتماني <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="credit_limit" step="0.01" min="0"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
                            placeholder="0.00">
                        @error('credit_limit')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Opening Balance --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الرصيد الافتتاحي <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="opening_balance" step="0.01" min="0"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
                            placeholder="0.00">
                        @error('opening_balance')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Address & Map --}}
            <div>
                <h4 class="text-base font-bold text-primary-700 mb-4 pb-2 border-b border-gray-100">العنوان والموقع</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    {{-- Address --}}
                    <x-form-input
                        label="العنوان"
                        name="address"
                        wire:model="address"
                        placeholder="مثال: شارع التحرير، القاهرة"
                        :error="$errors->first('address')"
                    />

                    <div class="grid grid-cols-2 gap-4">
                        {{-- Latitude --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">خط العرض</label>
                            <input type="number" wire:model="latitude" step="0.0000001" id="latitude"
                                class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
                                placeholder="30.0444">
                            @error('latitude')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Longitude --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">خط الطول</label>
                            <input type="number" wire:model="longitude" step="0.0000001" id="longitude"
                                class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
                                placeholder="31.2357">
                            @error('longitude')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Map --}}
                <div id="map" class="w-full h-72 rounded-xl border border-gray-200 bg-gray-100"></div>
                <p class="text-xs text-gray-400 mt-2">اضغط على الخريطة لتحديد موقع العميل</p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    {{ $customerId ? 'تحديث العميل' : 'حفظ العميل' }}
                </x-button>
                <x-button variant="secondary" href="{{ route('customers.index') }}">
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

                const lat = @js($latitude ?? 30.0444);
                const lng = @js($longitude ?? 31.2357);
                const hasCoords = @js($latitude && $longitude);

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
