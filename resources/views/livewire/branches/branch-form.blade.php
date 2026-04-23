<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $branchId ? 'تعديل الفرع' : 'إضافة فرع جديد' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $branchId ? 'تعديل بيانات الفرع' : 'حفظ بيانات الفرع وتحديد الموقع على الخريطة' }}</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary-700">بيانات الفرع</h3>
        </div>

        <form wire:submit="save" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Name --}}
                <x-form-input
                    label="اسم الفرع"
                    name="name"
                    wire:model="name"
                    placeholder="مثال: فرع صنعاء الرئيسي"
                    required
                    :error="$errors->first('name')"
                />

                {{-- Phone --}}
                <x-form-input
                    label="رقم الهاتف"
                    name="phone"
                    wire:model="phone"
                    placeholder="770000000"
                    :error="$errors->first('phone')"
                />

                {{-- Email --}}
                <x-form-input
                    label="البريد الإلكتروني"
                    name="email"
                    type="email"
                    wire:model="email"
                    placeholder="branch@bintalal.com"
                    :error="$errors->first('email')"
                />
            </div>

            {{-- Map Section --}}
            <div class="mt-6 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <x-icon name="map-pin" class="w-4 h-4 inline" />
                    موقع الفرع على الخريطة
                </label>
                <p class="text-xs text-gray-500 mb-3">انقر على الخريطة لتحديد موقع الفرع</p>

                <div id="map" class="w-full h-80 rounded-xl border border-gray-200 z-0"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Latitude --}}
                <x-form-input
                    label="خط العرض (Latitude)"
                    name="latitude"
                    wire:model="latitude"
                    placeholder="15.3694"
                    :error="$errors->first('latitude')"
                />

                {{-- Longitude --}}
                <x-form-input
                    label="خط الطول (Longitude)"
                    name="longitude"
                    wire:model="longitude"
                    placeholder="44.1910"
                    :error="$errors->first('longitude')"
                />
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    {{ $branchId ? 'تحديث الفرع' : 'حفظ الفرع' }}
                </x-button>
                <x-button variant="secondary" href="{{ route('branches.index') }}">
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

            let mapInstance = null;
            let markerInstance = null;

            function initMap() {
                const mapEl = document.getElementById('map');
                if (!mapEl || mapInstance) return;

                const lat = @js($latitude) || 15.3694;
                const lng = @js($longitude) || 44.1910;
                const hasLocation = @js($latitude) && @js($longitude);

                mapInstance = L.map('map').setView([lat, lng], hasLocation ? 14 : 6);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap'
                }).addTo(mapInstance);

                if (hasLocation) {
                    markerInstance = L.marker([lat, lng]).addTo(mapInstance);
                }

                mapInstance.on('click', function(e) {
                    const { lat, lng } = e.latlng;

                    if (markerInstance) {
                        markerInstance.setLatLng(e.latlng);
                    } else {
                        markerInstance = L.marker(e.latlng).addTo(mapInstance);
                    }

                    @this.set('latitude', lat.toFixed(7));
                    @this.set('longitude', lng.toFixed(7));
                });

                setTimeout(() => mapInstance.invalidateSize(), 200);
            }
        </script>
    @endpush
</div>
