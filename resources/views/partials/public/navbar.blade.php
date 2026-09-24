@php
    $user = $user ?? auth('business')->user() ?? auth('office')->user();
    $authed = $user !== null;
    $isOffice = $authed && $user instanceof \App\Models\Business\OfficeUser;
    $isActive = $active ?? 'home';
    $isHomepage = request()->routeIs('amrtm.index');
    $isAdminUser = $authed && !$isOffice && method_exists($user, 'isAdmin') && $user->isAdmin();
    $dashUrl = $isOffice
        ? route('amrtm.office.dashboard')
        : ($isAdminUser ? route('amrtm.admin.dashboard') : route('amrtm.user.dashboard'));
    $logoutUrl = $isOffice ? route('amrtm.office.logout') : route('amrtm.logout');
    $dashLabel = ($isOffice || $isAdminUser) ? 'لوحة التحكم' : 'حسابي';
    $officeLogoUrl = $isOffice && $user->office
        ? $user->office->logo_url
        : null;
@endphp

<nav class="sticky top-0 inset-x-0 z-[999] w-full border-b border-slate-200 bg-white shadow-sm">
    <div class="mx-auto flex h-[72px] w-full max-w-[1400px] items-center justify-between gap-4 px-4 md:px-6">
        <!-- Logo -->
        <a class="flex shrink-0 items-center gap-2.5 no-underline" href="{{ route('amrtm.index') }}">
            <span class="relative flex h-[52px] w-[52px] items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-[#f8fafc]">
                <img src="{{ asset('images/official-logo.jpg') }}"
                     alt="آمر تم"
                     class="relative h-[48px] w-[48px] object-contain"
                     onerror="this.style.display='none';this.nextElementSibling&&this.nextElementSibling.classList.remove('hidden');">
                <svg class="hidden" viewBox="0 0 24 24" fill="none" stroke="rgba(0,108,53,0.7)" stroke-width="2" width="24" height="24">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                </svg>
            </span>
            <span class="hidden text-xl font-black text-[#006C35] sm:block">آمر تم</span>
        </a>

        <!-- Desktop Links -->
        <div class="hidden flex-1 items-center justify-center gap-1 lg:flex">
            @if($isHomepage)
                <div onclick="window.scrollTo({top:0,behavior:'smooth'})"
                     class="{{ $isActive==='home' ? 'bg-[#006C35]/10 text-[#006C35]' : 'text-gray-500 hover:bg-[#006C35]/10 hover:text-[#006C35]' }} relative cursor-pointer rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200" id="nl-home">الرئيسية</div>
                <div onclick="typeof scrollCards==='function' ? scrollCards() : null"
                     class="{{ $isActive==='services' ? 'bg-[#006C35]/10 text-[#006C35]' : 'text-gray-500 hover:bg-[#006C35]/10 hover:text-[#006C35]' }} relative cursor-pointer rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200" id="nl-svcs">الخدمات</div>
            @else
                <a href="{{ route('amrtm.index') }}"
                   class="text-gray-500 hover:bg-[#006C35]/10 hover:text-[#006C35] relative cursor-pointer rounded-lg px-4 py-2 text-sm font-semibold no-underline transition-all duration-200" id="nl-home">الرئيسية</a>
                <a href="{{ route('amrtm.index') }}#services"
                   class="text-gray-500 hover:bg-[#006C35]/10 hover:text-[#006C35] relative cursor-pointer rounded-lg px-4 py-2 text-sm font-semibold no-underline transition-all duration-200" id="nl-svcs">الخدمات</a>
            @endif
            <a href="{{ $isHomepage ? '#about' : route('amrtm.index') . '#about' }}"
               class="{{ $isActive==='about' ? 'bg-[#006C35]/10 text-[#006C35]' : 'text-gray-500 hover:bg-[#006C35]/10 hover:text-[#006C35]' }} relative cursor-pointer rounded-lg px-4 py-2 text-sm font-semibold no-underline transition-all duration-200" id="nl-about">عن المنصة</a>
            <a href="{{ $isHomepage ? '#contact' : route('amrtm.index') . '#contact' }}"
               class="{{ $isActive==='contact' ? 'bg-[#006C35]/10 text-[#006C35]' : 'text-gray-500 hover:bg-[#006C35]/10 hover:text-[#006C35]' }} relative cursor-pointer rounded-lg px-4 py-2 text-sm font-semibold no-underline transition-all duration-200" id="nl-con">تواصل معنا</a>
        </div>

        <!-- Right Section -->
        <div class="flex shrink-0 items-center gap-2.5">
            <!-- Language Toggle -->
            <div class="flex items-center overflow-hidden rounded-full border-[1.5px] border-slate-300 bg-gray-50">
                <button type="button" id="la" onclick="navLang('ar')"
                        class="cursor-pointer px-2.5 py-2 text-xs font-bold text-gray-400 transition-all duration-200 sm:px-3">AR</button>
                <button type="button" id="le" onclick="navLang('en')"
                        class="cursor-pointer px-2.5 py-2 text-xs font-bold text-gray-400 transition-all duration-200 sm:px-3">EN</button>
            </div>

            <!-- Guest Buttons -->
            <div id="nb-guest" class="{{ $authed ? 'hidden' : 'flex' }} items-center gap-2">
                <a href="{{ route('amrtm.login') }}" id="nb-li"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-gray-700 no-underline transition-all duration-200 hover:bg-gray-50 sm:px-4">
                    <i class="fa fa-right-to-bracket text-sm"></i><span id="nl-li" class="hidden sm:inline">دخول</span>
                </a>
                <a href="{{ route('amrtm.register') }}" id="nb-re"
                   class="inline-flex items-center gap-2 rounded-lg bg-[#006C35] px-3 py-2 text-sm font-semibold text-white no-underline transition-all duration-200 hover:bg-[#00843D] sm:px-4">
                    <i class="fa fa-user-plus text-sm"></i><span id="nl-re" class="hidden sm:inline">تسجيل</span>
                </a>
                <a href="{{ route('amrtm.office.login') }}"
                   class="hidden items-center gap-2 rounded-lg border border-[#006C35]/20 px-4 py-2 text-sm font-semibold text-[#006C35] no-underline transition-all duration-200 hover:bg-[#006C35] hover:text-white lg:inline-flex">
                    <i class="fa fa-building text-sm"></i>
                    <span class="sm:hidden">المكاتب</span>
                </a>
            </div>

            <!-- Auth Buttons -->
            <div id="nb-auth" class="{{ $authed ? 'flex' : 'hidden' }} items-center gap-2">
                @if($user)
                    <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-gray-700 no-underline transition-all duration-200 hover:bg-gray-50" id="nb-dash-lnk"
                       href="{{ $dashUrl }}">
                        <i class="fa fa-gauge-high text-sm"></i>
                        <span id="nl-da" class="hidden sm:inline">{{ $dashLabel }}</span>
                    </a>
                    <div id="nb-user-chip" onclick="location.href='{{ $dashUrl }}'"
                         class="flex cursor-pointer items-center gap-2.5 rounded-xl bg-gray-100 px-2 py-1.5 transition-all duration-200 hover:bg-gray-200">
                        <img class="h-9 w-9 rounded-full object-cover" id="nb-av"
                             src="{{ $officeLogoUrl ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=006C35&color=fff&size=64' }}"
                             alt="{{ $user->name }}" />
                        <span class="hidden max-w-[80px] truncate text-[13px] font-bold text-gray-800 sm:block" id="nb-un">{{ explode(' ', $user->name)[0] }}</span>
                    </div>
                    <form id="nb-logout-form" method="POST" action="{{ $logoutUrl }}" class="hidden">@csrf</form>
                    <button type="button" onclick="document.getElementById('nb-logout-form').submit()"
                            class="cursor-pointer rounded-lg p-1.5 text-gray-400 transition-colors duration-200 hover:text-red-500" title="تسجيل الخروج">
                        <i class="fa fa-right-from-bracket text-lg"></i>
                    </button>
                @endif
            </div>

            <!-- Hamburger -->
            <button type="button"
                    class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl bg-gray-100 text-gray-600 transition-all duration-200 hover:bg-gray-200 lg:hidden"
                    onclick="togMob()">
                <i class="fa fa-bars text-lg"></i>
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Menu -->
<div id="mob-dd" class="fixed top-[72px] left-0 right-0 bottom-0 z-[1998] hidden flex-col items-stretch gap-1 overflow-y-auto bg-white px-4 pb-6 pt-2">
    @if($isHomepage)
        <div class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-gray-700 hover:bg-[#006C35]/10" onclick="window.scrollTo({top:0,behavior:'smooth'});clsMob();">
            <i class="fa fa-house w-5 text-center"></i><span id="mn-h">الرئيسية</span>
        </div>
        <div class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-gray-700 hover:bg-[#006C35]/10" onclick="typeof scrollCards==='function' ? scrollCards() : null;clsMob();">
            <i class="fa fa-table-cells-large w-5 text-center"></i><span id="mn-s">الخدمات</span>
        </div>
    @else
        <a class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold no-underline text-gray-700 hover:bg-[#006C35]/10" href="{{ route('amrtm.index') }}">
            <i class="fa fa-house w-5 text-center"></i><span id="mn-h">الرئيسية</span>
        </a>
        <a class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold no-underline text-gray-700 hover:bg-[#006C35]/10" href="{{ route('amrtm.index') }}#services">
            <i class="fa fa-table-cells-large w-5 text-center"></i><span id="mn-s">الخدمات</span>
        </a>
    @endif
    @if($authed && $user)
        <a class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold no-underline text-gray-700 hover:bg-[#006C35]/10" href="{{ $dashUrl }}">
            <i class="fa fa-gauge-high w-5 text-center"></i><span>لوحة التحكم</span>
        </a>
        <div class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-red-500 hover:bg-red-50" onclick="document.getElementById('mob-logout-form').submit()">
            <i class="fa fa-right-from-bracket w-5 text-center"></i><span>تسجيل الخروج</span>
        </div>
        <form id="mob-logout-form" method="POST" action="{{ $logoutUrl }}" class="hidden">@csrf</form>
    @else
        <a class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold no-underline text-gray-700 hover:bg-[#006C35]/10" href="{{ route('amrtm.login') }}">
            <i class="fa fa-right-to-bracket w-5 text-center"></i><span id="mn-l">تسجيل الدخول</span>
        </a>
        <a class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold no-underline text-gray-700 hover:bg-[#006C35]/10" href="{{ route('amrtm.register') }}">
            <i class="fa fa-user-plus w-5 text-center"></i><span id="mn-r">إنشاء حساب</span>
        </a>
    @endif
</div>

@push('scripts')
<script>
    window._NAV_T = {
        ar: {home:'الرئيسية', svcs:'الخدمات', about:'عن المنصة', con:'تواصل معنا', li:'دخول', re:'تسجيل', da:'حسابي', mn_h:'الرئيسية', mn_s:'الخدمات', mn_l:'تسجيل الدخول', mn_r:'إنشاء حساب'},
        en: {home:'Home', svcs:'Services', about:'About', con:'Contact', li:'Sign In', re:'Register', da:'My Account', mn_h:'Home', mn_s:'Services', mn_l:'Sign In', mn_r:'Create Account'}
    };

    function _applyNavLang(l) {
        var t = window._NAV_T[l] || window._NAV_T.ar;
        var ids = {'nl-home':'home', 'nl-svcs':'svcs', 'nl-about':'about', 'nl-con':'con', 'nl-li':'li', 'nl-re':'re', 'nl-da':'da', 'mn-h':'mn_h', 'mn-s':'mn_s', 'mn-l':'mn_l', 'mn-r':'mn_r'};
        Object.keys(ids).forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.textContent = t[ids[id]];
        });
    }

    window.navLang = function(l) {
        if (typeof setLang === 'function') {
            setLang(l);
        }
        localStorage.setItem('amrtm_lang', l);
        document.documentElement.setAttribute('lang', l);
        document.documentElement.setAttribute('dir', l === 'ar' ? 'rtl' : 'ltr');
        _applyNavLang(l);
        _setLangBtn('la', l === 'ar');
        _setLangBtn('le', l === 'en');
    };

    function _setLangBtn(id, active) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('bg-[#006C35]', 'text-white', 'text-gray-400');
        if (active) {
            el.classList.add('bg-[#006C35]', 'text-white');
        } else {
            el.classList.add('text-gray-400');
        }
    }

    window.togMob = function() {
        var d = document.getElementById('mob-dd');
        if (d) { d.classList.toggle('hidden'); d.classList.toggle('flex'); }
    };
    window.clsMob = function() {
        var d = document.getElementById('mob-dd');
        if (d) { d.classList.remove('flex'); d.classList.add('hidden'); }
    };

    document.addEventListener('DOMContentLoaded', function() {
        var l = localStorage.getItem('amrtm_lang') || 'ar';
        _setLangBtn('la', l === 'ar');
        _setLangBtn('le', l === 'en');
        if (l !== 'ar') navLang(l);
    });
</script>
@endpush
