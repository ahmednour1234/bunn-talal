<div class="w-full max-w-md">
    <div class="bg-card rounded-2xl shadow-xl border border-primary-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-sidebar px-8 py-10 text-center">
            <h1 class="text-3xl font-bold text-white mb-2">بن طلال</h1>
            <p class="text-white/70 text-sm">لوحة إدارة المناديب والمخزون والمبيعات</p>
        </div>

        {{-- Form --}}
        <div class="p-8">
            <h2 class="text-xl font-bold text-primary-700 text-center mb-2">تسجيل الدخول</h2>
            <p class="text-sm text-gray-500 text-center mb-6">أدخل بياناتك للوصول إلى لوحة التحكم</p>

            <form wire:submit="login">
                {{-- Email --}}
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                    <input
                        type="email"
                        id="email"
                        wire:model="email"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all duration-200 text-sm"
                        placeholder="admin@bintalal.com"
                        autofocus
                    >
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور</label>
                    <input
                        type="password"
                        id="password"
                        wire:model="password"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all duration-200 text-sm"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember --}}
                <div class="flex items-center mb-6">
                    <input type="checkbox" id="remember" wire:model="remember" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <label for="remember" class="mr-2 text-sm text-gray-600">تذكرني</label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full bg-primary-600 text-white py-3 rounded-lg font-bold text-sm hover:bg-primary-700 focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 transition-all duration-200 shadow-sm"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70 cursor-wait"
                >
                    <span wire:loading.remove>دخول</span>
                    <span wire:loading>جاري تسجيل الدخول...</span>
                </button>
            </form>
        </div>
    </div>
</div>
