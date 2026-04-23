<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $roleId ? 'تعديل الدور' : 'إضافة دور جديد' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $roleId ? 'تعديل بيانات الدور وصلاحياته' : 'إنشاء دور جديد وتعيين الصلاحيات' }}</p>
    </div>

    <form wire:submit="save">
        {{-- Role Info --}}
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-primary-700">بيانات الدور</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-input
                        label="اسم الدور (بالإنجليزية)"
                        name="name"
                        wire:model="name"
                        placeholder="مثال: editor"
                        required
                        :error="$errors->first('name')"
                    />
                    <x-form-input
                        label="الاسم المعروض"
                        name="display_name"
                        wire:model="display_name"
                        placeholder="مثال: محرر"
                        required
                        :error="$errors->first('display_name')"
                    />
                </div>
                <div class="mt-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="2"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all text-sm"
                        placeholder="وصف مختصر للدور..."
                    ></textarea>
                </div>
            </div>
        </div>

        {{-- Permissions --}}
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-primary-700">الصلاحيات</h3>
                <p class="text-xs text-gray-500 mt-1">اختر الصلاحيات التي يمتلكها هذا الدور</p>
            </div>
            <div class="p-6">
                @foreach($permissionsGrouped as $group => $permissions)
                    <div class="mb-6 last:mb-0">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-primary-600">{{ $group }}</h4>
                            <button
                                type="button"
                                wire:click="toggleAll('{{ $group }}')"
                                class="text-xs text-primary-500 hover:text-primary-700 transition-colors"
                            >
                                تحديد/إلغاء الكل
                            </button>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach($permissions as $permission)
                                <label class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 hover:bg-primary-50 cursor-pointer transition-colors {{ in_array($permission->id, $selectedPermissions) ? 'bg-primary-50 border-primary-300' : '' }}">
                                    <input
                                        type="checkbox"
                                        wire:model.live="selectedPermissions"
                                        value="{{ $permission->id }}"
                                        class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                    >
                                    <span class="text-sm text-gray-700">{{ $permission->display_name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <x-button type="submit" variant="primary">
                {{ $roleId ? 'تحديث الدور' : 'حفظ الدور' }}
            </x-button>
            <x-button variant="secondary" href="{{ route('roles.index') }}">
                إلغاء
            </x-button>
        </div>
    </form>
</div>
