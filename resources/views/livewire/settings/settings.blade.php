<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-700 tracking-tight">إعدادات البرنامج</h1>
            <p class="text-sm text-gray-400 mt-0.5">تخصيص بيانات الشركة والمظهر العام</p>
        </div>
    </div>

    @if($saved)
        <div class="mb-5 flex items-center gap-3 bg-stone-50 border border-stone-200 text-stone-700 px-5 py-3 rounded-xl text-sm font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-700 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            تم حفظ الإعدادات بنجاح
        </div>
    @endif

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Main settings card --}}
            <div class="lg:col-span-2 space-y-5">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-base font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100">بيانات الشركة</h2>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">العملة <span class="text-red-500">*</span></label>
                        <select wire:model="currency"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-sm focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all">
                            <option value="">-- اختر العملة --</option>
                            <option value="ريال يمني">ريال يمني (YER)</option>
                            <option value="ريال سعودي">ريال سعودي (SAR)</option>
                            <option value="دولار أمريكي">دولار أمريكي (USD)</option>
                            <option value="يورو">يورو (EUR)</option>
                            <option value="درهم إماراتي">درهم إماراتي (AED)</option>
                            <option value="دينار كويتي">دينار كويتي (KWD)</option>
                            <option value="دينار أردني">دينار أردني (JOD)</option>
                            <option value="جنيه مصري">جنيه مصري (EGP)</option>
                            <option value="ريال قطري">ريال قطري (QAR)</option>
                        </select>
                        @error('currency') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">اسم البرنامج / الشركة <span class="text-red-500">*</span></label>
                        <input wire:model="app_name" type="text" placeholder="أدخل اسم البرنامج..."
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-sm focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all">
                        @error('app_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">الرقم الضريبي</label>
                        <input wire:model="tax_number" type="text" placeholder="أدخل الرقم الضريبي..." dir="ltr"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-sm focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all">
                        @error('tax_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">السجل التجاري</label>
                        <input wire:model="commercial_register" type="text" placeholder="أدخل رقم السجل التجاري..." dir="ltr"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-sm focus:bg-white focus:ring-2 focus:ring-primary-300 focus:border-primary-400 transition-all">
                        @error('commercial_register') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Logo card --}}
            <div class="space-y-5">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-base font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100">شعار البرنامج</h2>

                    {{-- Current logo preview --}}
                    <div class="flex flex-col items-center mb-5">
                        @if($logo)
                            <img src="{{ $logo->temporaryUrl() }}" class="w-28 h-28 rounded-2xl object-cover border-4 border-primary-100 shadow mb-2" alt="معاينة الشعار">
                            <p class="text-xs text-gray-400">معاينة الشعار الجديد</p>
                        @elseif($currentLogo)
                            <img src="{{ Storage::url($currentLogo) }}" class="w-28 h-28 rounded-2xl object-cover border-4 border-primary-100 shadow mb-2" alt="الشعار الحالي">
                            <p class="text-xs text-gray-400">الشعار الحالي</p>
                        @else
                            <div class="w-28 h-28 rounded-2xl bg-primary-50 border-4 border-primary-100 flex items-center justify-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-primary-200" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                                </svg>
                            </div>
                            <p class="text-xs text-gray-400">لم يُرفع شعار بعد</p>
                        @endif
                    </div>

                    {{-- Upload area --}}
                    <label class="block cursor-pointer">
                        <div class="border-2 border-dashed border-gray-200 hover:border-primary-300 rounded-xl p-5 text-center transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            <p class="text-xs text-gray-500 font-medium">اضغط لرفع الشعار</p>
                            <p class="text-[11px] text-gray-400 mt-1">PNG, JPG, GIF — حد أقصى 2 ميجابايت</p>
                        </div>
                        <input wire:model="logo" type="file" accept="image/*" class="hidden">
                    </label>
                    @error('logo') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror

                    <div wire:loading wire:target="logo" class="mt-3 text-center">
                        <span class="text-xs text-primary-700 animate-pulse">جاري رفع الصورة...</span>
                    </div>
                </div>

                {{-- Save button --}}
                <button type="submit"
                    class="w-full py-3 px-6 bg-primary-700 hover:bg-primary-800 text-white font-bold rounded-xl text-sm transition-colors shadow-sm flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    حفظ الإعدادات
                </button>
            </div>

        </div>
    </form>
</div>