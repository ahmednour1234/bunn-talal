<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary-700">{{ $adminId ? 'تعديل المدير' : 'إضافة مدير جديد' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $adminId ? 'تعديل بيانات المدير وأدواره' : 'إنشاء حساب مدير جديد وتعيين الأدوار' }}</p>
    </div>

    <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary-700">بيانات المدير</h3>
        </div>

        <form wire:submit="save" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Name --}}
                <x-form-input
                    label="اسم المدير"
                    name="name"
                    wire:model="name"
                    placeholder="مثال: أحمد محمد"
                    required
                    :error="$errors->first('name')"
                />

                {{-- Email --}}
                <x-form-input
                    label="البريد الإلكتروني"
                    name="email"
                    type="email"
                    wire:model="email"
                    placeholder="admin@bintalal.com"
                    required
                    :error="$errors->first('email')"
                />

                {{-- Password --}}
                <x-form-input
                    label="{{ $adminId ? 'كلمة المرور الجديدة (اتركها فارغة إذا لم تريد التغيير)' : 'كلمة المرور' }}"
                    name="password"
                    type="password"
                    wire:model="password"
                    placeholder="••••••••"
                    :required="!$adminId"
                    :error="$errors->first('password')"
                />

                {{-- Password Confirmation --}}
                <x-form-input
                    label="تأكيد كلمة المرور"
                    name="password_confirmation"
                    type="password"
                    wire:model="password_confirmation"
                    placeholder="••••••••"
                    :required="!$adminId"
                    :error="$errors->first('password_confirmation')"
                />
            </div>

            {{-- Roles --}}
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">الأدوار</label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($roles as $role)
                        <label class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 hover:bg-primary-50 cursor-pointer transition-colors {{ in_array($role->id, $selectedRoles) ? 'bg-primary-50 border-primary-300' : '' }}">
                            <input
                                type="checkbox"
                                wire:model="selectedRoles"
                                value="{{ $role->id }}"
                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            >
                            <span class="text-sm text-gray-700">{{ $role->display_name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('selectedRoles')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <x-button type="submit" variant="primary">
                    {{ $adminId ? 'تحديث المدير' : 'حفظ المدير' }}
                </x-button>
                <x-button variant="secondary" href="{{ route('admins.index') }}">
                    إلغاء
                </x-button>
            </div>
        </form>
    </div>
</div>
