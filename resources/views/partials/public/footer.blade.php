<footer class="mt-16 border-t border-slate-200 bg-white">
    <div class="mx-auto w-full max-w-[1400px]">
        <!-- Main Footer -->
        <div class="grid grid-cols-1 gap-8 px-4 py-10 md:grid-cols-2 md:px-6 lg:grid-cols-4">
            <!-- Brand -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-[46px] w-[46px] items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-[#f8fafc]">
                        <img src="{{ asset('images/official-logo.jpg') }}" alt="آمر تم" class="h-[42px] w-[42px] object-contain">
                    </span>
                    <span class="text-lg font-black text-[#006C35]">آمر تم</span>
                </div>
                <p class="text-sm leading-relaxed text-gray-500">
                    منصة سعودية متكاملة لتقديم الخدمات الحكومية إلكترونياً بكل سهولة وأمان.
                </p>
            </div>

            <!-- روابط المنصة -->
            <div>
                <h3 class="mb-4 text-sm font-bold text-gray-900">روابط المنصة</h3>
                <ul class="space-y-3 text-sm text-gray-500">
                    <li><a href="{{ route('amrtm.index') }}" class="transition-colors no-underline duration-200 hover:text-[#006C35]">الرئيسية</a></li>
                    <li><a href="#services" class="transition-colors no-underline duration-200 hover:text-[#006C35]">الخدمات</a></li>
                    <li><a href="{{ route('amrtm.offices.directory', 'law') }}" class="transition-colors no-underline duration-200 hover:text-[#006C35]">المكاتب المعتمدة</a></li>
                </ul>
            </div>

            <!-- حسابات -->
            <div>
                <h3 class="mb-4 text-sm font-bold text-gray-900">حسابك</h3>
                <ul class="space-y-3 text-sm text-gray-500">
                    <li><a href="{{ route('amrtm.login') }}" class="transition-colors no-underline duration-200 hover:text-[#006C35]">دخول</a></li>
                    <li><a href="{{ route('amrtm.register') }}" class="transition-colors no-underline duration-200 hover:text-[#006C35]">تسجيل جديد</a></li>
                    <li><a href="{{ route('amrtm.user.dashboard') }}" class="transition-colors no-underline duration-200 hover:text-[#006C35]">لوحة المستخدم</a></li>
                </ul>
            </div>

            <!-- تواصل -->
            <div>
                <h3 class="mb-4 text-sm font-bold text-gray-900">تواصل معنا</h3>
                <ul class="space-y-3 text-sm text-gray-500">
                    <li class="flex items-center gap-2"><i class="fa fa-envelope text-[#006C35]"></i> support@amrtm.sa</li>
                    <li class="flex items-center gap-2"><i class="fa fa-phone text-[#006C35]"></i> 920000000</li>
                </ul>
            </div>
        </div>

        <!-- Copyright -->
        <div class="border-t border-slate-200 py-5 text-center text-sm text-gray-500" id="fcp">
            © 2026 <b class="text-[#006C35]">آمر تم</b> — جميع الحقوق محفوظة
        </div>
    </div>
</footer>