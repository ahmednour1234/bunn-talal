<div>
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-700">إدارة الصلاحيات</h1>
            <p class="text-sm text-gray-500 mt-1">عرض جميع الصلاحيات المتاحة في النظام</p>
        </div>
        @if(auth('admin')->user()?->hasPermission('permissions.create'))
            <x-button variant="primary" wire:click="$toggle('showCreate')">
                <x-icon name="plus" class="w-4 h-4" />
                إضافة صلاحية
            </x-button>
        @endif
    </div>

    {{-- Create Form --}}
    @if($showCreate)
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-primary-700">إضافة صلاحية جديدة</h3>
            </div>
            <form wire:submit="create" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-form-input
                        label="اسم الصلاحية (بالإنجليزية)"
                        name="newName"
                        wire:model="newName"
                        placeholder="مثال: reports.view"
                        required
                        :error="$errors->first('newName')"
                    />
                    <x-form-input
                        label="الاسم المعروض"
                        name="newDisplayName"
                        wire:model="newDisplayName"
                        placeholder="مثال: عرض التقارير"
                        required
                        :error="$errors->first('newDisplayName')"
                    />
                    <x-form-input
                        label="المجموعة"
                        name="newGroupName"
                        wire:model="newGroupName"
                        placeholder="مثال: التقارير"
                        required
                        :error="$errors->first('newGroupName')"
                    />
                </div>
                <div class="flex items-center gap-3 mt-4">
                    <x-button type="submit" variant="primary" size="sm">حفظ الصلاحية</x-button>
                    <x-button variant="ghost" size="sm" wire:click="$toggle('showCreate')">إلغاء</x-button>
                </div>
            </form>
        </div>
    @endif

    {{-- Permissions Grouped --}}
    @foreach($permissionsGrouped as $group => $permissions)
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 overflow-hidden mb-4">
            <div class="p-4 border-b border-gray-100 bg-primary-50/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-primary-700">{{ $group }}</h3>
                    <span class="text-xs text-gray-500">{{ $permissions->count() }} صلاحية</span>
                </div>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($permissions as $permission)
                        <div class="flex items-center justify-between p-3 rounded-lg border border-gray-100 hover:border-primary-200 transition-colors">
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ $permission->display_name }}</p>
                                <p class="text-xs text-gray-400" dir="ltr">{{ $permission->name }}</p>
                            </div>
                            @if(auth('admin')->user()?->hasPermission('permissions.create'))
                                <button
                                    wire:click="delete({{ $permission->id }})"
                                    wire:confirm="هل أنت متأكد من حذف هذه الصلاحية؟"
                                    class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                                    title="حذف"
                                >
                                    <x-icon name="trash" class="w-3.5 h-3.5" />
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    @if($permissionsGrouped->isEmpty())
        <div class="bg-card rounded-2xl shadow-sm border border-primary-100 p-12 text-center">
            <x-icon name="key" class="w-12 h-12 mx-auto mb-3 text-gray-300" />
            <p class="text-gray-400">لا توجد صلاحيات مسجلة</p>
        </div>
    @endif
</div>
