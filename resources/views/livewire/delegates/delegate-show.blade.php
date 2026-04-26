<div dir="rtl" class="space-y-6">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm font-bold px-5 py-3 rounded-2xl flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ══════════════════ HERO HEADER ══════════════════ --}}
    <div class="relative rounded-3xl overflow-hidden shadow-lg">
        {{-- Gradient background --}}
        <div class="bg-gradient-to-l from-[#3E2723] via-[#4E342E] to-[#6D4C41] px-8 pt-8 pb-6">
            <div class="flex items-start justify-between">
                {{-- Avatar + Info --}}
                <div class="flex items-center gap-5">
                    <div class="relative">
                        <div class="w-20 h-20 rounded-2xl bg-white/10 border-2 border-white/20 flex items-center justify-center text-white text-3xl font-extrabold shadow-inner backdrop-blur-sm">
                            {{ mb_substr($delegate->name, 0, 1) }}
                        </div>
                        <span class="absolute -bottom-2 -left-2 w-6 h-6 rounded-full border-2 border-[#4E342E] flex items-center justify-center
                            {{ $delegate->is_active ? 'bg-green-400' : 'bg-gray-400' }}">
                        </span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-extrabold text-white tracking-wide">{{ $delegate->name }}</h1>
                        <div class="flex items-center flex-wrap gap-2 mt-2">
                            @if($delegate->phone)
                            <span class="flex items-center gap-1 text-xs text-white/70 bg-white/10 px-2.5 py-1 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                {{ $delegate->phone }}
                            </span>
                            @endif
                            @if($delegate->email)
                            <span class="flex items-center gap-1 text-xs text-white/70 bg-white/10 px-2.5 py-1 rounded-lg">
                                {{ $delegate->email }}
                            </span>
                            @endif
                            <span class="text-xs font-bold px-2.5 py-1 rounded-lg {{ $delegate->is_active ? 'bg-green-500/20 text-green-300 border border-green-500/30' : 'bg-red-500/20 text-red-300 border border-red-400/30' }}">
                                {{ $delegate->is_active ? '● نشط' : '● غير نشط' }}
                            </span>
                        </div>
                        @if($delegate->branches->count())
                        <p class="text-xs text-white/50 mt-1.5">الفروع: {{ $delegate->branches->pluck('name')->join('، ') }}</p>
                        @endif
                    </div>
                </div>

                {{-- Buttons + Rating --}}
                <div class="flex flex-col items-end gap-3">
                    <div class="flex gap-2">
                        <a href="{{ route('delegates.edit', $delegate->id) }}"
                            class="text-xs bg-white/10 hover:bg-white/20 border border-white/20 text-white px-4 py-2 rounded-xl font-semibold transition-colors backdrop-blur-sm">
                            تعديل
                        </a>
                        <a href="{{ route('delegates.index') }}"
                            class="text-xs bg-white/10 hover:bg-white/20 border border-white/20 text-white px-4 py-2 rounded-xl font-semibold transition-colors backdrop-blur-sm">
                            ← عودة
                        </a>
                    </div>
                    {{-- Rating badge --}}
                    @php $rating = $delegate->rating ?? 100; @endphp
                    <div class="flex items-center gap-2 bg-white/10 border border-white/20 rounded-xl px-3 py-2 backdrop-blur-sm">
                        <svg class="w-12 h-12 -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="3"/>
                            <circle cx="18" cy="18" r="15.9" fill="none"
                                stroke="{{ $rating >= 80 ? '#4ade80' : ($rating >= 50 ? '#fbbf24' : '#f87171') }}"
                                stroke-width="3"
                                stroke-dasharray="{{ $rating }}, 100"
                                stroke-linecap="round"/>
                        </svg>
                        <div>
                            <p class="text-[10px] text-white/50 leading-none">تقييم</p>
                            <p class="text-xl font-extrabold {{ $rating >= 80 ? 'text-green-400' : ($rating >= 50 ? 'text-amber-400' : 'text-red-400') }}">{{ $rating }}</p>
                            <p class="text-[10px] {{ $rating >= 80 ? 'text-green-300' : ($rating >= 50 ? 'text-amber-300' : 'text-red-300') }}">{{ $rating >= 80 ? 'ممتاز' : ($rating >= 50 ? 'متوسط' : 'ضعيف') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KPI strip inside hero --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6">
                <div class="bg-white/10 border border-white/10 rounded-2xl px-4 py-3 text-center backdrop-blur-sm">
                    <p class="text-[11px] text-white/50 font-semibold">إجمالي الرحلات</p>
                    <p class="text-3xl font-extrabold text-white mt-0.5">{{ $totalTrips }}</p>
                </div>
                <div class="bg-white/10 border border-white/10 rounded-2xl px-4 py-3 text-center backdrop-blur-sm">
                    <p class="text-[11px] text-white/50 font-semibold">رحلات نشطة</p>
                    <p class="text-3xl font-extrabold text-amber-300 mt-0.5">{{ $activeTrips }}</p>
                </div>
                <div class="bg-white/10 border border-white/10 rounded-2xl px-4 py-3 text-center backdrop-blur-sm">
                    <p class="text-[11px] text-white/50 font-semibold">إجمالي المفوتر</p>
                    <p class="text-xl font-extrabold text-green-300 mt-0.5">{{ number_format($totalInvoiced, 0) }}</p>
                    <p class="text-[10px] text-white/30">ج.م</p>
                </div>
                <div class="bg-white/10 border border-white/10 rounded-2xl px-4 py-3 text-center backdrop-blur-sm">
                    <p class="text-[11px] text-white/50 font-semibold">إجمالي التحصيل</p>
                    <p class="text-xl font-extrabold text-blue-300 mt-0.5">{{ number_format($totalCollected, 0) }}</p>
                    <p class="text-[10px] text-white/30">ج.م</p>
                </div>
            </div>
        </div>

        {{-- Gold accent bottom bar --}}
        <div class="h-1 bg-gradient-to-l from-[#C9A66B] via-[#e8c98a] to-[#C9A66B]"></div>
    </div>

    {{-- ══════════════════ DEFICIT + LOANS CARDS ══════════════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold">عجز كاش كلي</p>
                <p class="text-xl font-extrabold {{ $cashDeficit > 0 ? 'text-red-600' : 'text-gray-300' }}">{{ number_format($cashDeficit, 2) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-orange-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold">عجز بضاعة كلي</p>
                <p class="text-xl font-extrabold {{ $productDeficit > 0 ? 'text-orange-600' : 'text-gray-300' }}">{{ number_format($productDeficit, 2) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold">سلف غير مسددة</p>
                <p class="text-xl font-extrabold {{ $totalLoanOwed > 0 ? 'text-purple-700' : 'text-gray-300' }}">{{ number_format($totalLoanOwed, 2) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
            </div>
        </div>
    </div>

    {{-- ══════════════════ TABS ══════════════════ --}}
    <div class="flex items-center gap-1 bg-white border border-gray-100 rounded-2xl shadow-sm p-1.5 w-fit">
        @foreach([['overview','نظرة عامة','M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],['trips','الرحلات','M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],['loans','السلف والمديونية','M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z'],['hr','الموارد البشرية','M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z']] as [$key, $label, $icon])
        <button wire:click="$set('activeTab', '{{ $key }}')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all
                {{ $activeTab === $key
                    ? 'bg-[#6D4C41] text-white shadow-sm'
                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
            </svg>
            {{ $label }}
            @if($key === 'loans' && $overdueLoans->count() > 0)
            <span class="bg-red-500 text-white text-[10px] font-extrabold rounded-full w-4 h-4 flex items-center justify-center leading-none">{{ $overdueLoans->count() }}</span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- ══════════════════ OVERVIEW TAB ══════════════════ --}}
    @if($activeTab === 'overview')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- Delegate Info --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-gray-50 border-b border-gray-100 px-5 py-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                <h3 class="text-sm font-bold text-gray-700">بيانات المندوب</h3>
            </div>
            <div class="p-5 space-y-3">
                @if($delegate->email)
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <span class="text-xs text-gray-400 font-semibold">البريد الإلكتروني</span>
                    <span class="text-sm font-semibold text-gray-700">{{ $delegate->email }}</span>
                </div>
                @endif
                @if($delegate->national_id)
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <span class="text-xs text-gray-400 font-semibold">رقم الهوية</span>
                    <span class="text-sm font-semibold text-gray-700 font-mono">{{ $delegate->national_id }}</span>
                </div>
                @endif
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <span class="text-xs text-gray-400 font-semibold">حد الائتمان</span>
                    <span class="text-sm font-bold text-primary-700">{{ number_format($delegate->credit_sales_limit, 2) }} ج.م</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <span class="text-xs text-gray-400 font-semibold">عمولة المبيعات</span>
                    <span class="text-sm font-bold text-primary-700">{{ $delegate->sales_commission_rate }}%</span>
                </div>
                @if($delegate->branches->count())
                <div class="flex items-start justify-between py-2">
                    <span class="text-xs text-gray-400 font-semibold">الفروع المرتبطة</span>
                    <div class="flex flex-wrap gap-1 justify-end">
                        @foreach($delegate->branches as $branch)
                        <span class="text-xs bg-primary-50 text-primary-700 border border-primary-100 px-2.5 py-0.5 rounded-full font-semibold">{{ $branch->name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Performance Summary --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-gray-50 border-b border-gray-100 px-5 py-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                <h3 class="text-sm font-bold text-gray-700">ملخص الأداء</h3>
            </div>
            <div class="p-5">
                @php
                    $rating = $delegate->rating ?? 100;
                    $totalDeficit = $cashDeficit + $productDeficit;
                @endphp
                {{-- Visual rating bar --}}
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs text-gray-400 font-semibold">مؤشر الأداء العام</span>
                        <span class="text-sm font-extrabold {{ $rating >= 80 ? 'text-green-600' : ($rating >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ $rating }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full transition-all duration-500
                            {{ $rating >= 80 ? 'bg-green-500' : ($rating >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                            style="width: {{ $rating }}%"></div>
                    </div>
                    <p class="text-[11px] {{ $rating >= 80 ? 'text-green-500' : ($rating >= 50 ? 'text-amber-500' : 'text-red-500') }} font-bold mt-1">
                        {{ $rating >= 80 ? 'أداء ممتاز' : ($rating >= 50 ? 'أداء متوسط' : 'أداء ضعيف — يحتاج متابعة') }}
                    </p>
                </div>
                <div class="space-y-2.5 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400 text-xs">رحلات مسوّاة</span>
                        <span class="font-bold text-gray-700 bg-gray-100 px-2.5 py-0.5 rounded-lg text-xs">{{ $settledTrips }} / {{ $totalTrips }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400 text-xs">إجمالي العجز (كاش + بضاعة)</span>
                        <span class="font-bold {{ $totalDeficit > 0 ? 'text-red-600' : 'text-gray-300' }} text-xs">{{ number_format($totalDeficit, 2) }} ج.م</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400 text-xs">إجمالي السلف</span>
                        <span class="font-bold text-purple-600 text-xs">{{ number_format($totalLoans, 2) }} ج.م</span>
                    </div>
                    <div class="flex items-center justify-between pt-2.5 border-t border-gray-100 mt-1">
                        <span class="text-gray-600 text-sm font-bold">المديونية الكلية</span>
                        <span class="font-extrabold text-sm {{ ($totalDeficit + $totalLoanOwed) > 0 ? 'text-red-700' : 'text-green-600' }}">
                            {{ number_format($totalDeficit + $totalLoanOwed, 2) }} ج.م
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════ TRIPS TAB ══════════════════ --}}
    @if($activeTab === 'trips')
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="bg-gray-50 border-b border-gray-100 px-5 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                <h3 class="text-sm font-bold text-gray-700">سجل الرحلات</h3>
                <span class="text-xs bg-primary-100 text-primary-700 font-bold px-2 py-0.5 rounded-full">{{ $totalTrips }}</span>
            </div>
            <a href="{{ route('trips.create') }}"
                class="flex items-center gap-1.5 text-xs bg-[#6D4C41] text-white px-3.5 py-2 rounded-xl font-bold hover:bg-[#4E342E] transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                رحلة جديدة
            </a>
        </div>
        @if($trips->isEmpty())
        <div class="py-16 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
            <p class="text-sm text-gray-400 font-semibold">لا توجد رحلات مسجلة لهذا المندوب</p>
        </div>
        @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm text-right">
            <thead>
                <tr class="text-[11px] text-gray-400 font-bold border-b border-gray-100">
                    <th class="px-4 py-3.5 bg-gray-50/80">رقم الرحلة</th>
                    <th class="px-3 py-3.5 bg-gray-50/80 text-center">الحالة</th>
                    <th class="px-3 py-3.5 bg-gray-50/80 text-center">الفرع</th>
                    <th class="px-3 py-3.5 bg-gray-50/80 text-center">تاريخ البدء</th>
                    <th class="px-3 py-3.5 bg-gray-50/80 text-center">المفوتر</th>
                    <th class="px-3 py-3.5 bg-gray-50/80 text-center">التحصيل</th>
                    <th class="px-3 py-3.5 bg-gray-50/80 text-center">عجز كاش</th>
                    <th class="px-3 py-3.5 bg-gray-50/80 text-center">عجز بضاعة</th>
                    <th class="px-3 py-3.5 bg-gray-50/80 text-center"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($trips as $trip)
            @php
                $statusMap = [
                    'draft'      => ['label'=>'مسودة',   'class'=>'bg-gray-100 text-gray-500'],
                    'active'     => ['label'=>'نشطة',    'class'=>'bg-amber-100 text-amber-700'],
                    'in_transit' => ['label'=>'في الطريق','class'=>'bg-blue-100 text-blue-700'],
                    'returning'  => ['label'=>'عائدة',   'class'=>'bg-purple-100 text-purple-700'],
                    'settled'    => ['label'=>'مسوّاة',  'class'=>'bg-green-100 text-green-700'],
                    'cancelled'  => ['label'=>'ملغية',   'class'=>'bg-red-100 text-red-600'],
                ];
                $st = $statusMap[$trip->status] ?? ['label'=>$trip->status,'class'=>'bg-gray-100 text-gray-600'];
                $hasDef = $trip->settlement_cash_deficit > 0 || $trip->settlement_product_deficit > 0;
            @endphp
            <tr class="hover:bg-gray-50/60 transition-colors {{ $hasDef ? 'bg-red-50/30' : '' }}">
                <td class="px-4 py-3.5">
                    <a href="{{ route('trips.show', $trip->id) }}" class="font-mono text-xs text-primary-700 font-extrabold hover:underline">{{ $trip->trip_number }}</a>
                </td>
                <td class="px-3 py-3.5 text-center">
                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $st['class'] }}">{{ $st['label'] }}</span>
                </td>
                <td class="px-3 py-3.5 text-center text-xs text-gray-600 font-medium">{{ $trip->branch?->name ?? '—' }}</td>
                <td class="px-3 py-3.5 text-center text-xs text-gray-400">{{ $trip->start_date?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-3 py-3.5 text-center text-xs font-bold text-gray-700">{{ number_format($trip->total_invoiced, 0) }}</td>
                <td class="px-3 py-3.5 text-center text-xs font-bold text-blue-700">{{ number_format($trip->total_collected, 0) }}</td>
                <td class="px-3 py-3.5 text-center text-xs font-extrabold {{ $trip->settlement_cash_deficit > 0 ? 'text-red-600' : 'text-gray-200' }}">
                    {{ $trip->settlement_cash_deficit > 0 ? number_format($trip->settlement_cash_deficit, 2) : '—' }}
                </td>
                <td class="px-3 py-3.5 text-center text-xs font-extrabold {{ $trip->settlement_product_deficit > 0 ? 'text-orange-600' : 'text-gray-200' }}">
                    {{ $trip->settlement_product_deficit > 0 ? number_format($trip->settlement_product_deficit, 2) : '—' }}
                </td>
                <td class="px-3 py-3.5 text-center">
                    <div class="flex gap-1 justify-center">
                        <a href="{{ route('trips.show', $trip->id) }}"
                            class="text-[11px] border border-primary-200 text-primary-700 hover:bg-primary-50 px-2.5 py-1 rounded-lg font-bold transition-colors">عرض</a>
                        @if(in_array($trip->status, ['active','returning']))
                        <a href="{{ route('trips.settle', $trip->id) }}"
                            class="text-[11px] border border-amber-200 text-amber-700 hover:bg-amber-50 px-2.5 py-1 rounded-lg font-bold transition-colors">تسوية</a>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 border-t-2 border-gray-200 text-xs font-extrabold">
                    <td colspan="4" class="px-4 py-3 text-gray-500">الإجماليات</td>
                    <td class="px-3 py-3 text-center text-gray-700">{{ number_format($totalInvoiced, 0) }}</td>
                    <td class="px-3 py-3 text-center text-blue-700">{{ number_format($totalCollected, 0) }}</td>
                    <td class="px-3 py-3 text-center text-red-600">{{ number_format($cashDeficit, 2) }}</td>
                    <td class="px-3 py-3 text-center text-orange-600">{{ number_format($productDeficit, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        </div>
        @endif
    </div>
    @endif

    {{-- ══════════════════ LOANS TAB ══════════════════ --}}
    @if($activeTab === 'loans')
    <div class="space-y-5">
        {{-- Summary cards --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-gray-400 font-semibold">إجمالي السلف</p>
                    <p class="text-lg font-extrabold text-purple-700">{{ number_format($totalLoans, 2) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-gray-400 font-semibold">المبلغ المسدَّد</p>
                    <p class="text-lg font-extrabold text-green-700">{{ number_format($totalLoanPaid, 2) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border {{ $totalLoanOwed > 0 ? 'border-red-200' : 'border-gray-100' }} shadow-sm p-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ $totalLoanOwed > 0 ? 'bg-red-100' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 {{ $totalLoanOwed > 0 ? 'text-red-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-gray-400 font-semibold">المتبقي (مديون)</p>
                    <p class="text-lg font-extrabold {{ $totalLoanOwed > 0 ? 'text-red-700' : 'text-green-600' }}">{{ number_format($totalLoanOwed, 2) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
                </div>
            </div>
        </div>

        {{-- Overdue alert --}}
        @if($overdueLoans->count() > 0)
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-red-800">يوجد {{ $overdueLoans->count() }} {{ $overdueLoans->count() == 1 ? 'سلفة متأخرة' : 'سلف متأخرة' }}</p>
                <p class="text-xs text-red-600">بإجمالي {{ number_format($overdueLoans->sum(fn($l) => max(0, (float)$l->amount - (float)$l->paid_amount)), 2) }} ج.م متأخرة</p>
            </div>
        </div>
        @endif

        {{-- Add loan toggle --}}
        <div class="flex justify-end">
            <button wire:click="$toggle('showLoanForm')"
                class="flex items-center gap-2 text-sm {{ $showLoanForm ? 'bg-gray-100 text-gray-700 border border-gray-200' : 'bg-[#6D4C41] text-white hover:bg-[#4E342E]' }} px-4 py-2.5 rounded-xl font-bold transition-colors">
                @if(!$showLoanForm)
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                @endif
                {{ $showLoanForm ? '✕ إلغاء' : 'إضافة سلفة جديدة' }}
            </button>
        </div>

        @if($showLoanForm)
        <div class="bg-white rounded-2xl border border-purple-200 shadow-sm overflow-hidden">
            <div class="bg-purple-50 border-b border-purple-100 px-5 py-3">
                <h3 class="text-sm font-bold text-purple-800">تسجيل سلفة جديدة</h3>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">المبلغ <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" wire:model="loanAmount" step="0.01" min="0.01" placeholder="0.00"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-purple-300 focus:border-transparent @error('loanAmount') border-red-400 @enderror">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">ج.م</span>
                        </div>
                        @error('loanAmount')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">تاريخ الاستحقاق</label>
                        <input type="date" wire:model="loanDueDate"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-purple-300 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">الخزنة</label>
                        <select wire:model="loanTreasuryId" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-purple-300 focus:border-transparent">
                            <option value="">-- بدون خزنة --</option>
                            @foreach($treasuries as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">ملاحظة</label>
                        <input type="text" wire:model="loanNote" placeholder="سبب السلفة أو ملاحظة..."
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-purple-300 focus:border-transparent">
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <button wire:click="saveLoan"
                        class="flex items-center gap-2 bg-purple-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-purple-800 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        حفظ السلفة
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- Loans table --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            @if($loans->isEmpty())
            <div class="py-14 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75"/></svg>
                <p class="text-sm text-gray-400 font-semibold">لا توجد سلف مسجلة</p>
            </div>
            @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm text-right">
                <thead>
                    <tr class="text-[11px] text-gray-400 font-bold border-b border-gray-100">
                        <th class="px-4 py-3.5 bg-gray-50/80">#</th>
                        <th class="px-3 py-3.5 bg-gray-50/80 text-center">المبلغ</th>
                        <th class="px-3 py-3.5 bg-gray-50/80 text-center">المسدَّد</th>
                        <th class="px-3 py-3.5 bg-gray-50/80 text-center">المتبقي</th>
                        <th class="px-3 py-3.5 bg-gray-50/80 text-center">تاريخ الاستحقاق</th>
                        <th class="px-3 py-3.5 bg-gray-50/80 text-center">الحالة</th>
                        <th class="px-3 py-3.5 bg-gray-50/80 text-center">الخزنة</th>
                        <th class="px-3 py-3.5 bg-gray-50/80">ملاحظة</th>
                        <th class="px-3 py-3.5 bg-gray-50/80 text-center"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                @foreach($loans as $loan)
                @php
                    $remaining = max(0, (float)$loan->amount - (float)$loan->paid_amount);
                    $isOverdue = !$loan->is_paid && $loan->due_date && $loan->due_date->isPast();
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors {{ $isOverdue ? 'bg-red-50/40' : '' }}">
                    <td class="px-4 py-3.5 text-xs text-gray-400 font-mono">#{{ $loan->id }}</td>
                    <td class="px-3 py-3.5 text-center font-extrabold text-purple-700 text-xs">{{ number_format($loan->amount, 2) }} <span class="text-gray-300 font-normal">ج.م</span></td>
                    <td class="px-3 py-3.5 text-center font-bold text-green-600 text-xs">{{ number_format($loan->paid_amount, 2) }}</td>
                    <td class="px-3 py-3.5 text-center font-extrabold text-xs {{ $remaining > 0 ? 'text-red-600' : 'text-gray-300' }}">
                        {{ $remaining > 0 ? number_format($remaining, 2) : '—' }}
                    </td>
                    <td class="px-3 py-3.5 text-center text-xs">
                        @if($loan->due_date)
                        <span class="font-semibold {{ $isOverdue ? 'text-red-600' : 'text-gray-600' }}">{{ $loan->due_date->format('Y-m-d') }}</span>
                        @if($isOverdue)<br><span class="text-[10px] text-red-400">{{ $loan->due_date->diffForHumans() }}</span>@endif
                        @else
                        <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-3.5 text-center">
                        @if($loan->is_paid)
                        <span class="text-[11px] bg-green-100 text-green-700 font-bold px-2.5 py-1 rounded-full">مسدَّدة ✓</span>
                        @elseif($isOverdue)
                        <span class="text-[11px] bg-red-100 text-red-700 font-bold px-2.5 py-1 rounded-full">متأخرة ⚠</span>
                        @else
                        <span class="text-[11px] bg-amber-100 text-amber-700 font-bold px-2.5 py-1 rounded-full">معلقة</span>
                        @endif
                    </td>
                    <td class="px-3 py-3.5 text-center text-xs text-gray-500">{{ $loan->treasury?->name ?? '—' }}</td>
                    <td class="px-3 py-3.5 text-xs text-gray-400 max-w-[120px] truncate">{{ $loan->note ?? '—' }}</td>
                    <td class="px-3 py-3.5 text-center">
                        @if(!$loan->is_paid)
                        <button wire:click="openPayModal({{ $loan->id }})"
                            class="text-[11px] border border-green-200 text-green-700 hover:bg-green-50 px-2.5 py-1 rounded-lg font-bold transition-colors">
                            سداد
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ══════════════════ HR TAB ══════════════════ --}}
    @if($activeTab === 'hr')
    <div class="space-y-6">
        {{-- Quick links --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('hr.leaves.create') }}?delegateId={{ $delegate->id }}"
                class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-semibold">الإجازات</p>
                    <p class="text-xl font-extrabold text-blue-700">{{ $delegate->hrLeaves()->count() }}</p>
                    <p class="text-xs text-blue-500">+ إضافة إجازة</p>
                </div>
            </a>
            <a href="{{ route('hr.attendance.create') }}?delegateId={{ $delegate->id }}"
                class="bg-white rounded-2xl border border-green-100 shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.864 4.243A7.5 7.5 0 0 1 19.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 0 0 4.5 10.5a7.464 7.464 0 0 1-1.15 3.993m1.989 3.559A11.209 11.209 0 0 0 8.25 10.5a3.75 3.75 0 1 1 7.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a14.94 14.94 0 0 1-3.6 9.75m6.633-4.596a18.666 18.666 0 0 1-2.485 5.33"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-semibold">سجلات الحضور</p>
                    <p class="text-xl font-extrabold text-green-700">{{ $delegate->hrAttendances()->whereMonth('date', now()->month)->count() }}</p>
                    <p class="text-xs text-green-500">هذا الشهر — + تسجيل</p>
                </div>
            </a>
            <a href="{{ route('hr.salaries.create') }}?delegateId={{ $delegate->id }}"
                class="bg-white rounded-2xl border border-amber-100 shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-semibold">الرواتب</p>
                    @php $pendingSalary = $delegate->hrSalaries()->where('status','pending')->count(); @endphp
                    <p class="text-xl font-extrabold {{ $pendingSalary > 0 ? 'text-amber-700' : 'text-gray-400' }}">{{ $pendingSalary }}</p>
                    <p class="text-xs text-amber-500">قيد الانتظار — + إضافة</p>
                </div>
            </a>
        </div>

        {{-- Last Leaves --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-blue-50 border-b border-blue-100 px-5 py-3 flex items-center justify-between">
                <h3 class="text-sm font-bold text-blue-800">آخر الإجازات</h3>
                <a href="{{ route('hr.leaves.index') }}" class="text-xs text-blue-600 hover:underline">عرض الكل</a>
            </div>
            @php $lastLeaves = $delegate->hrLeaves()->latest()->take(5)->get(); @endphp
            @if($lastLeaves->isEmpty())
                <p class="text-center text-sm text-gray-400 py-6">لا توجد إجازات</p>
            @else
            <div class="divide-y divide-gray-50">
                @foreach($lastLeaves as $leave)
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">{{ $leave->type_label }}</p>
                        <p class="text-xs text-gray-400">{{ $leave->start_date->format('Y-m-d') }} → {{ $leave->end_date->format('Y-m-d') }} ({{ $leave->days }} يوم)</p>
                    </div>
                    @if($leave->status === 'approved')
                        <span class="text-xs bg-green-100 text-green-700 font-bold px-2.5 py-1 rounded-full">موافق</span>
                    @elseif($leave->status === 'rejected')
                        <span class="text-xs bg-red-100 text-red-700 font-bold px-2.5 py-1 rounded-full">مرفوض</span>
                    @else
                        <span class="text-xs bg-amber-100 text-amber-700 font-bold px-2.5 py-1 rounded-full">قيد الانتظار</span>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Last Salaries --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-amber-50 border-b border-amber-100 px-5 py-3 flex items-center justify-between">
                <h3 class="text-sm font-bold text-amber-800">آخر الرواتب</h3>
                <a href="{{ route('hr.salaries.index') }}" class="text-xs text-amber-600 hover:underline">عرض الكل</a>
            </div>
            @php $lastSalaries = $delegate->hrSalaries()->orderByDesc('year')->orderByDesc('month')->take(6)->get(); @endphp
            @if($lastSalaries->isEmpty())
                <p class="text-center text-sm text-gray-400 py-6">لا توجد رواتب</p>
            @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm text-right">
                <thead><tr class="text-[11px] text-gray-400 font-bold border-b border-gray-100">
                    <th class="px-4 py-3 bg-gray-50/80">الشهر</th>
                    <th class="px-4 py-3 bg-gray-50/80">الأساسي</th>
                    <th class="px-4 py-3 bg-gray-50/80">الصافي</th>
                    <th class="px-4 py-3 bg-gray-50/80">الحالة</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                @foreach($lastSalaries as $sal)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-4 py-3 font-semibold text-gray-700">{{ $sal->month_label }} {{ $sal->year }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ number_format($sal->basic_salary, 2) }}</td>
                    <td class="px-4 py-3 font-bold text-primary-700">{{ number_format($sal->net_salary, 2) }}</td>
                    <td class="px-4 py-3">
                        @if($sal->status === 'paid')
                            <span class="text-[11px] bg-green-100 text-green-700 font-bold px-2.5 py-1 rounded-full">مصروف ✓</span>
                        @else
                            <span class="text-[11px] bg-amber-100 text-amber-700 font-bold px-2.5 py-1 rounded-full">قيد الانتظار</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </div>

        {{-- Attendance summary this month --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-green-50 border-b border-green-100 px-5 py-3 flex items-center justify-between">
                <h3 class="text-sm font-bold text-green-800">ملخص الحضور - {{ now()->locale('ar')->monthName }}</h3>
                <a href="{{ route('hr.attendance.index') }}" class="text-xs text-green-600 hover:underline">عرض الكل</a>
            </div>
            @php
                $monthAtt = $delegate->hrAttendances()->whereMonth('date', now()->month)->whereYear('date', now()->year)->get();
                $presentDays  = $monthAtt->where('status','present')->count();
                $absentDays   = $monthAtt->where('status','absent')->count();
                $lateDays     = $monthAtt->where('status','late')->count();
                $leaveDays    = $monthAtt->where('status','on_leave')->count();
            @endphp
            <div class="grid grid-cols-4 gap-0 divide-x divide-x-reverse divide-gray-100">
                <div class="text-center py-5">
                    <p class="text-2xl font-extrabold text-green-600">{{ $presentDays }}</p>
                    <p class="text-xs text-gray-400 font-semibold mt-1">حاضر</p>
                </div>
                <div class="text-center py-5">
                    <p class="text-2xl font-extrabold text-red-500">{{ $absentDays }}</p>
                    <p class="text-xs text-gray-400 font-semibold mt-1">غائب</p>
                </div>
                <div class="text-center py-5">
                    <p class="text-2xl font-extrabold text-amber-500">{{ $lateDays }}</p>
                    <p class="text-xs text-gray-400 font-semibold mt-1">متأخر</p>
                </div>
                <div class="text-center py-5">
                    <p class="text-2xl font-extrabold text-blue-500">{{ $leaveDays }}</p>
                    <p class="text-xs text-gray-400 font-semibold mt-1">إجازة</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════ PAY MODAL ══════════════════ --}}
    @if($payLoanId !== null)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden" dir="rtl">
            <div class="bg-gradient-to-l from-green-700 to-green-600 px-6 py-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75"/></svg>
                </div>
                <h3 class="text-base font-bold text-white">تسجيل دفعة سداد</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1.5">المبلغ المسدَّد <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" wire:model="payAmount" step="0.01" min="0.01"
                            class="w-full border border-gray-200 rounded-xl px-3 py-3 text-sm focus:ring-2 focus:ring-green-300 focus:border-transparent @error('payAmount') border-red-400 @enderror">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">ج.م</span>
                    </div>
                    @error('payAmount')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-2 justify-end pt-1">
                    <button wire:click="$set('payLoanId', null)"
                        class="text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 px-4 py-2.5 rounded-xl font-bold transition-colors">
                        إلغاء
                    </button>
                    <button wire:click="payLoan"
                        class="text-sm bg-green-700 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-green-800 transition-colors flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        تأكيد السداد
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>