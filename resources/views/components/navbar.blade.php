<header class="bg-card shadow-sm border-b border-primary-100 px-4 lg:px-6 py-3 lg:py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        {{-- Hamburger button — mobile only --}}
        <button @click="sidebarOpen = !sidebarOpen"
            class="lg:hidden p-2 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-primary-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
        <h2 class="text-base lg:text-lg font-bold text-primary-700">{{ $title ?? 'لوحة التحكم' }}</h2>
    </div>
    <div class="flex items-center gap-3 lg:gap-4">
        <livewire:notification-bell />
        <span class="text-sm text-gray-600">{{ auth('admin')->user()?->name ?? 'مدير' }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-1 text-sm text-red-600 hover:text-red-800 transition-colors">
                <x-icon name="logout" class="w-4 h-4" />
                <span>خروج</span>
            </button>
        </form>
    </div>
</header>
