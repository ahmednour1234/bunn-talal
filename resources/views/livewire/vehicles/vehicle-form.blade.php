<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $vehicleId ? 'تعديل المركبة' : 'إضافة مركبة جديدة' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $vehicleId ? 'تعديل بيانات المركبة' : 'إضافة مركبة جديدة إلى النظام' }}</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary-700">بيانات المركبة</h3>
        </div>

        <form wire:submit="save" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Name --}}
                <x-form-input
                    label="اسم المركبة"
                    name="name"
                    wire:model="name"
                    placeholder="مثال: تويوتا هايلكس"
                    required
                    :error="$errors->first('name')"
                />

                {{-- Code --}}
                <x-form-input
                    label="كود المركبة"
                    name="code"
                    wire:model="code"
                    placeholder="مثال: VH-001"
                    required
                    :error="$errors->first('code')"
                />
            </div>

            {{-- Active Status --}}
            <div class="mt-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model="is_active"
                        class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5"
                    >
                    <span class="text-sm font-medium text-gray-700">مركبة نشطة</span>
                </label>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    {{ $vehicleId ? 'تحديث المركبة' : 'حفظ المركبة' }}
                </x-button>
                <x-button variant="secondary" href="{{ route('vehicles.index') }}">
                    إلغاء
                </x-button>
            </div>
        </form>
    </div>
</div>
