@extends('layouts.public')

@section('title', ($pageTitle ?? 'لوحة التحكم') . ' — آمر تم')

@section('content')
@php
    $currentPath = request()->path();
    $dashPersona = $persona ?? ['name' => 'مستخدم', 'label' => '', 'key' => '', 'types' => []];
    $firstName = explode(' ', $dashPersona['name'])[0];
    $officeLogoUrl = isset($office) && $office ? $office->logo_url : null;
@endphp

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- ══ Sidebar (Flowbite Drawer, right placement for RTL) ═══════════════════ --}}
    <aside
        id="dash-sidebar"
        aria-label="Sidebar"
        class="fixed inset-y-0 right-0 z-40 flex h-screen w-72 flex-col border-l border-gray-200 bg-white transition-transform duration-300 ease-in-out translate-x-full dark:border-gray-700 dark:bg-gray-800 lg:translate-x-0"
    >
        {{-- Brand --}}
        <div class="flex items-center justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-700">
            <a href="{{ route('amrtm.dashboard.hub') }}" class="flex items-center gap-2.5">
                <span class="relative flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-emerald-50 ring-1 ring-emerald-100 dark:bg-gray-700 dark:ring-gray-600">
                    <img src="{{ asset('images/official-logo.jpg') }}" alt="آمر تم" class="h-9 w-9 rounded-lg object-contain">
                </span>
                <span class="leading-tight">
                    <span class="block text-[15px] font-black text-gray-900 dark:text-white">آمر تم</span>
                    <span class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">لوحة التحكم الموحّدة</span>
                </span>
            </a>

            <button
                type="button"
                data-drawer-hide="dash-sidebar"
                aria-controls="dash-sidebar"
                class="rounded-lg p-1.5 text-gray-500 transition-colors duration-200 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white lg:hidden"
                aria-label="إغلاق القائمة"
            >
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <div class="space-y-6">
                @foreach ($dashboardMenu ?? [] as $group)
                    @php $groupId = 'dash-group-' . Str::slug($group['label']); @endphp
                    @if (!empty($group['items']))
                    <div>
                        <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            {{ $group['label'] }}
                        </p>
                        <ul class="space-y-1">
                            @foreach ($group['items'] as $item)
                                @php
                                    $itemParts = parse_url($item['href']);
                                    $hrefPath = ltrim($itemParts['path'] ?? '', '/');
                                    $hrefFragment = $itemParts['fragment'] ?? null;
                                    // روابط المراسي (/admin#requests) لا يصل fragment للخادم؛
                                    // تمييزها يُدار جافاسكربتياً عبر location.hash داخل القالب.
                                    $isActive = $currentPath === $hrefPath && $hrefFragment === null;
                                @endphp
                                <li>
                                    <a
                                        href="{{ $item['href'] }}"
                                        class="sidebar-item group/item relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-600 transition-all duration-200 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white {{ $isActive ? 'is-active' : '' }}"
                                        @if ($isActive) aria-current="page" @endif
                                    >
                                        <i class="nav-item-icon ti {{ $item['icon'] }} text-lg text-gray-400 group-hover/item:text-emerald-600 dark:text-gray-500 dark:group-hover/item:text-emerald-400 {{ $isActive ? 'is-active' : '' }}"></i>
                                        <span class="flex-1 whitespace-nowrap">{{ $item['ar'] }}</span>
                                        @if (!empty($item['en']))
                                            <span class="text-[10px] font-normal text-gray-400 dark:text-gray-500">{{ $item['en'] }}</span>
                                        @endif
                                        @if (isset($item['count']) && $item['count'] !== null)
                                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-700 tabular-nums dark:bg-emerald-900/40 dark:text-emerald-300">{{ number_format($item['count']) }}</span>
                                        @endif
                                        <span class="nav-bar"></span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                @endforeach

                <div class="pt-2">
                    <a
                        href="{{ route('amrtm.index') }}"
                        class="sidebar-item group/item relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-emerald-700 transition-all duration-200 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-900/30"
                    >
                        <i class="nav-item-icon ti ti-world text-lg text-emerald-600 group-hover/item:text-emerald-700 dark:text-emerald-400"></i>
                        <span class="flex-1 whitespace-nowrap">الانتقال إلى الموقع الالكتروني</span>
                        <i class="ti ti-arrow-left text-base text-emerald-500"></i>
                    </a>
                </div>
            </div>
        </nav>

        {{-- Logout --}}
        <div class="border-t border-gray-200 p-4 dark:border-gray-700">
            @if (auth('business')->check())
                <button
                    type="button"
                    data-dashboard-logout
                    class="flex w-full cursor-pointer items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-red-600 transition-colors duration-200 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10"
                >
                    <i class="ti ti-logout text-lg"></i> تسجيل الخروج
                </button>
                <form id="dash-logout-form" method="POST" action="{{ route('amrtm.logout') }}" class="hidden">@csrf</form>
            @elseif (auth('office')->check())
                <button
                    type="button"
                    data-dashboard-office-logout
                    class="flex w-full cursor-pointer items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-red-600 transition-colors duration-200 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10"
                >
                    <i class="ti ti-logout text-lg"></i> تسجيل الخروج
                </button>
                <form id="dash-office-logout-form" method="POST" action="{{ route('amrtm.office.logout') }}" class="hidden">@csrf</form>
            @endif
        </div>
    </aside>

    {{-- ══ Main column (RTL: sidebar fixed right → pad right) ═══════════════════ --}}
    <div class="flex min-h-screen w-full flex-col lg:pr-72">
        {{-- Topbar (Flowbite Admin Dashboard header) --}}
        <header class="sticky top-0 z-30 border-b border-gray-200 bg-white/80 backdrop-blur-xl dark:border-gray-700 dark:bg-gray-800/80">
            <div class="flex h-[72px] items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        data-drawer-target="dash-sidebar"
                        data-drawer-toggle="dash-sidebar"
                        data-drawer-placement="right"
                        data-drawer-backdrop="true"
                        aria-controls="dash-sidebar"
                        class="cursor-pointer rounded-lg p-2 text-gray-500 transition-colors duration-200 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white lg:hidden"
                        aria-label="فتح القائمة"
                    >
                        <i class="ti ti-menu-2 text-xl"></i>
                    </button>
                    <div class="leading-tight">
                        <h1 id="dash-page-title" class="text-base font-extrabold text-gray-900 dark:text-white sm:text-lg">{{ $pageTitle ?? 'لوحة التحكم' }}</h1>
                        @if (isset($persona) && $persona)
                            <p class="hidden text-[12px] font-medium text-gray-500 sm:block dark:text-gray-400">
                                {{ $persona['label'] ?? '' }} <span class="mx-1 text-gray-300 dark:text-gray-600">•</span> {{ $persona['name'] ?? '' }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
{{-- Page-specific actions (search / lang / charge) --}}
                @stack('dash-actions')

                {{-- Notifications --}}
                <button
                    id="dash-notifications-dropdown-btn"
                    data-dropdown-toggle="dash-notifications-dropdown"
                    data-dropdown-trigger="click"
                    class="relative cursor-pointer rounded-xl border border-gray-200 bg-white p-2.5 text-gray-500 transition-all duration-300 hover:border-emerald-200 hover:text-emerald-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:border-emerald-400 dark:hover:text-emerald-300"
                    aria-label="الإشعارات"
                >
                    <span
                        id="dash-notif-ping"
                        aria-hidden="true"
                        class="pointer-events-none absolute -inset-1 hidden rounded-2xl border-2 border-red-400 dark:border-red-500"
                    ></span>
                    <i class="ti ti-bell text-xl"></i>
                    <span
                        id="dash-notif-badge"
                        data-notif-badge
                        class="absolute -left-1.5 -top-1.5 hidden min-w-[18px] items-center justify-center rounded-full bg-red-500 px-1 py-0.5 text-[10px] font-bold text-white shadow-sm dark:border-2 dark:border-gray-700 tabular-nums"
                    >0</span>
                </button>

                    {{-- Dark mode toggle --}}
                    <button
                        data-dashboard-dark-toggle
                        class="cursor-pointer rounded-xl border border-gray-200 bg-white p-2.5 text-gray-500 transition-all duration-300 hover:border-emerald-200 hover:text-emerald-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:border-emerald-400 dark:hover:text-emerald-300"
                        aria-label="تبديل المظهر"
                    >
                        <i class="ti ti-moon text-xl"></i>
                    </button>

                    {{-- User dropdown --}}
                    <button
                        id="dash-user-dropdown-btn"
                        data-dropdown-toggle="dash-user-dropdown"
                        data-dropdown-trigger="click"
                        class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-gray-200 bg-white p-1.5 pe-4 transition-all duration-300 hover:border-emerald-200 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:hover:border-emerald-400"
                    >
                        <img
                            class="h-9 w-9 rounded-lg object-cover ring-2 ring-emerald-100 dark:ring-gray-600"
                            src="{{ $officeLogoUrl ?: 'https://ui-avatars.com/api/?name=' . urlencode($dashPersona['name'] ?? 'مستخدم') . '&background=006C35&color=fff&size=64' }}"
                            alt="{{ $dashPersona['name'] ?? '' }}"
                        />
                        <span class="hidden text-right sm:block">
                            <span class="block max-w-[120px] truncate text-[13px] font-bold text-gray-800 dark:text-gray-200">{{ $firstName }}</span>
                            <span class="block text-[11px] font-medium text-emerald-600 dark:text-emerald-400">{{ $dashPersona['label'] ?? '' }}</span>
                        </span>
                        <i class="ti ti-chevron-down hidden text-sm text-gray-400 sm:block dark:text-gray-500"></i>
                    </button>

                    {{-- User dropdown panel (Flowbite Dropdown) --}}
                    <div id="dash-user-dropdown" class="z-50 my-4 hidden w-56 list-none divide-y divide-gray-100 rounded-2xl border border-gray-200 bg-white text-gray-900 shadow-xl dark:divide-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <div class="px-5 py-4">
                            <p id="dash-dd-name" class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ $dashPersona['name'] ?? '' }}</p>
                            <p class="truncate text-xs font-medium text-gray-500 dark:text-gray-400">{{ $dashPersona['label'] ?? '' }}</p>
                        </div>
                        <div class="py-2">
                            <a href="{{ route('amrtm.dashboard.hub') }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm font-medium text-gray-700 transition-colors duration-200 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                <i class="ti ti-layout-dashboard text-gray-400 dark:text-gray-500"></i> لوحة التحكم
                            </a>
                        </div>
                        <div class="py-2">
                            @if (auth('business')->check())
                                <form method="POST" action="{{ route('amrtm.logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full cursor-pointer items-center gap-2.5 px-5 py-2.5 text-sm font-semibold text-red-600 transition-colors duration-200 hover:bg-red-50 dark:text-red-400 dark:hover:bg-gray-700">
                                        <i class="ti ti-logout"></i> تسجيل الخروج
                                    </button>
                                </form>
                            @elseif (auth('office')->check())
                                <form method="POST" action="{{ route('amrtm.office.logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full cursor-pointer items-center gap-2.5 px-5 py-2.5 text-sm font-semibold text-red-600 transition-colors duration-200 hover:bg-red-50 dark:text-red-400 dark:hover:bg-gray-700">
                                        <i class="ti ti-logout"></i> تسجيل الخروج
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notifications panel (Flowbite Dropdown) --}}
            <div id="dash-notifications-dropdown" class="z-50 my-4 hidden w-[340px] rounded-2xl border border-gray-200 bg-white p-0 text-gray-900 shadow-xl dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                    <p class="text-sm font-bold text-gray-900 dark:text-white">الإشعارات</p>
                    <button
                        type="button"
                        id="dash-notif-markall"
                        class="hidden rounded-lg bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 transition-colors duration-200 hover:bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60"
                    >تعليم الكل كمقروء</button>
                </div>
                <div id="dash-notif-list" class="max-h-[60vh] overflow-y-auto">
                    <div id="dash-notif-empty" class="flex flex-col items-center gap-2 px-4 py-8 text-center">
                        <i class="ti ti-bell-off text-3xl text-gray-300 dark:text-gray-600"></i>
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">لا توجد إشعارات حالياً</p>
                    </div>
                </div>
                <a id="dash-notif-all" href="{{ auth('office')->check() ? '/office/dashboard#reqs' : '/dashboard' }}" class="mt-0 block rounded-b-2xl border-t border-gray-100 bg-gray-50 py-2.5 text-center text-xs font-bold text-emerald-700 transition-colors duration-200 hover:bg-emerald-50 dark:border-gray-700 dark:bg-gray-700 dark:text-emerald-300 dark:hover:bg-gray-600">
                    عرض كل الإشعارات
                </a>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @yield('dashboard-content')
        </main>

        {{-- Footer --}}
        <footer class="border-t border-gray-200 bg-white/60 py-5 text-center text-xs font-medium text-gray-400 backdrop-blur dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-500">
            آمر تم © {{ date('Y') }} — لوحة التحكم الموحّدة
        </footer>
    </div>
</div>
@endsection

@push('styles')
<style>
    {{-- تمييز بند القائمة النشط (يُدار بالخادم للمسارات، وبـ JS لروابط المراسي) --}}
    #dash-sidebar .nav-bar {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 1.5rem;
        width: 4px;
        border-radius: 0.25rem 0 0 0.25rem;
        background: #10b981;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    #dash-sidebar a.sidebar-item.is-active {
        background: #ecfdf5;
        color: #064e3b;
    }
    #dash-sidebar a.sidebar-item.is-active > .nav-item-icon {
        color: #059669 !important;
    }
    #dash-sidebar a.sidebar-item.is-active > .nav-bar {
        opacity: 1;
    }
    .dark #dash-sidebar a.sidebar-item.is-active {
        background: #374151;
        color: #fff;
    }
    .dark #dash-sidebar a.sidebar-item.is-active > .nav-item-icon {
        color: #34d399 !important;
    }
    .dark #dash-sidebar a.sidebar-item.is-active > .nav-bar {
        background: #34d399;
    }

    {{-- ══ Notifications bell — animated unread indicators ══ --}}
    #dash-notifications-dropdown-btn.has-unread {
        border-color: #fecaca;
        color: #dc2626;
    }
    .dark #dash-notifications-dropdown-btn.has-unread {
        border-color: #f87171;
        color: #f87171;
    }
    #dash-notifications-dropdown-btn.has-unread i {
        animation: dashNotifSway 3.2s ease-in-out infinite;
    }
    #dash-notif-badge {
        animation: dashNotifBadgePulse 1.9s ease-in-out infinite;
    }
    #dash-notif-ping {
        animation: dashNotifPingCont 1.9s ease-out infinite;
    }
    #dash-notifications-dropdown-btn.is-ringing i {
        animation: dashNotifRing 0.75s ease-in-out;
    }
    @keyframes dashNotifSway {
        0%, 18%, 100% { transform: rotate(0); }
        3% { transform: rotate(16deg); }
        6% { transform: rotate(-12deg); }
        9% { transform: rotate(9deg); }
        12% { transform: rotate(-6deg); }
        15% { transform: rotate(3deg); }
    }
    @keyframes dashNotifBadgePulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.45); }
        50% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
    }
    @keyframes dashNotifPingCont {
        0% { transform: scale(1); opacity: 0.55; }
        75%, 100% { transform: scale(1.16); opacity: 0; }
    }
    @keyframes dashNotifRing {
        0%, 100% { transform: rotate(0); }
        10% { transform: rotate(24deg); }
        20% { transform: rotate(-20deg); }
        30% { transform: rotate(16deg); }
        40% { transform: rotate(-12deg); }
        50% { transform: rotate(8deg); }
        60% { transform: rotate(-4deg); }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        {{-- ══ Sidebar active item — روابط المراسي (/admin#requests) تُطابق location.hash ══ --}}
        var sidebarNav = document.getElementById('dash-sidebar');

        function applySidebarActive(item, active) {
            if (!item) return;
            item.classList.toggle('is-active', active);
            var icon = item.querySelector('.nav-item-icon');
            if (icon) icon.classList.toggle('is-active', active);
            if (active) item.setAttribute('aria-current', 'page');
            else item.removeAttribute('aria-current');
        }

        function syncSidebarHash() {
            if (!sidebarNav) return;
            var hash = window.location.hash || '';
            sidebarNav.querySelectorAll('a.sidebar-item[href*="#"]').forEach(function (item) {
                var href = item.getAttribute('href') || '';
                var idx = href.indexOf('#');
                if (idx < 0) return;
                applySidebarActive(item, hash !== '' && hash === href.slice(idx));
            });
        }

        window.addEventListener('hashchange', syncSidebarHash);
        syncSidebarHash();
        {{-- Logout triggers --}}
        var logoutBtn = document.querySelector('[data-dashboard-logout]');
        var logoutForm = document.getElementById('dash-logout-form');
        if (logoutBtn && logoutForm) {
            logoutBtn.addEventListener('click', function () { logoutForm.submit(); });
        }
        var officeLogoutBtn = document.querySelector('[data-dashboard-office-logout]');
        var officeLogoutForm = document.getElementById('dash-office-logout-form');
        if (officeLogoutBtn && officeLogoutForm) {
            officeLogoutBtn.addEventListener('click', function () { officeLogoutForm.submit(); });
        }

        {{-- ══ Unified notifications (DashNotif) ══ --}}
        window.DashNotif = {
            _provider: null,
            _loaded: false,
            _prev: 0,
            _ready: false,
            _gestured: false,
            _armed: false,
            _actx: null,
            setProvider: function (p) {
                this._provider = p;
                this._loaded = false;
                this._allMode = false;
                var all = this._allEl();
                if (all && p && p.allUrl) all.setAttribute('href', p.allUrl);
            },
            _badgeEl: function () { return document.getElementById('dash-notif-badge'); },
            _setBadge: function (n) {
                var badge = this._badgeEl();
                n = Number(n) || 0;
                if (!badge) return;
                var fresh = this._ready && n > this._prev && n > 0;
                badge.textContent = n > 99 ? '99+' : String(n);
                badge.style.display = n > 0 ? '' : 'none';
                this._setPing(n > 0);
                if (fresh) this._notifyNew();
                this._prev = n;
                this._ready = true;
                try {
                    window.dispatchEvent(new CustomEvent('amrtm:notif-count', { detail: n }));
                } catch (e) { /* ignore */ }
            },
            _setPing: function (active) {
                var btn = document.getElementById('dash-notifications-dropdown-btn');
                var ping = document.getElementById('dash-notif-ping');
                if (btn) btn.classList.toggle('has-unread', active);
                if (ping) ping.classList.toggle('hidden', !active);
            },
            _notifyNew: function () {
                var btn = document.getElementById('dash-notifications-dropdown-btn');
                if (btn) {
                    btn.classList.remove('is-ringing');
                    void btn.offsetWidth;
                    btn.classList.add('is-ringing');
                    window.setTimeout(function () { btn.classList.remove('is-ringing'); }, 800);
                }
                this._playChime();
            },
            _ensureAudio: function () {
                if (this._actx) return this._actx;
                var AC = window.AudioContext || window.webkitAudioContext;
                if (!AC) return null;
                try { this._actx = new AC(); } catch (e) { this._actx = null; }
                return this._actx;
            },
            _armAudio: function () {
                var self = this;
                if (this._armed) return;
                this._armed = true;
                var arm = function () {
                    ['pointerdown', 'keydown', 'touchstart', 'click'].forEach(function (ev) {
                        window.removeEventListener(ev, arm);
                    });
                    self._gestured = true;
                    var ctx = self._ensureAudio();
                    if (ctx && ctx.state === 'suspended') { try { ctx.resume(); } catch (e) { /* ignore */ } }
                };
                ['pointerdown', 'keydown', 'touchstart', 'click'].forEach(function (ev) {
                    window.addEventListener(ev, arm, { passive: true });
                });
            },
            _playChime: function () {
                if (!this._gestured) return;
                var ctx = this._ensureAudio();
                if (!ctx) return;
                if (ctx.state === 'suspended') { try { ctx.resume(); } catch (e) { /* ignore */ } }
                if (ctx.state !== 'running') return;
                var t = ctx.currentTime + 0.03;
                var master = ctx.createGain();
                master.gain.setValueAtTime(0.0001, t);
                master.gain.exponentialRampToValueAtTime(0.32, t + 0.02);
                master.gain.exponentialRampToValueAtTime(0.0001, t + 1.2);
                master.connect(ctx.destination);
                [1567.98, 2093.0].forEach(function (f, i) {
                    var at = t + i * 0.13;
                    var o = ctx.createOscillator();
                    o.type = 'triangle';
                    o.frequency.setValueAtTime(f, at);
                    var g = ctx.createGain();
                    g.gain.setValueAtTime(0.0001, at);
                    g.gain.exponentialRampToValueAtTime(0.55, at + 0.018);
                    g.gain.exponentialRampToValueAtTime(0.0001, at + 0.6);
                    o.connect(g);
                    g.connect(master);
                    o.start(at);
                    o.stop(at + 0.65);
                });
            },
            refreshBadge: async function () {
                var p = this._provider;
                if (!p) return;
                try {
                    var r = await p.unreadCount();
                    this._setBadge(r && r.count);
                } catch (e) { /* ignore */ }
            },
            _markAllEl: function () { return document.getElementById('dash-notif-markall'); },
            _emptyEl: function () { return document.getElementById('dash-notif-empty'); },
            _listEl: function () { return document.getElementById('dash-notif-list'); },
            _allEl: function () { return document.getElementById('dash-notif-all'); },
            render: function (items, unread) {
                var list = this._listEl(), empty = this._emptyEl(), markAll = this._markAllEl();
                if (!list) return;
                this._items = (items && items.slice()) || [];
                this._setBadge(unread);
                var has = items && items.length > 0;
                if (empty) empty.style.display = has ? 'none' : '';
                if (markAll) markAll.style.display = has ? '' : 'none';
                if (!has) { list.innerHTML = '<div class="min-h-10"></div>'; return; }
                var colors = {
                    status_update: '#1565C0', admin_note: '#6A1B9A', info_request: '#E65100',
                    request_submitted: '#1B5E20', status_changed: '#0E7490', new_message: '#4338CA',
                    new_office_request: '#07847A', info: '#0284C7'
                };
                list.innerHTML = items.map(function (n) {
                    var color = colors[n.type] || '#006C35';
                    var dot = '<span style="width:8px;height:8px;border-radius:50%;background:' + (n.is_read ? '#CBD5E1' : color) + ';flex-shrink:0;margin-top:6px"></span>';
                    return '<div class="flex cursor-pointer items-start gap-3 px-4 py-3 transition-colors duration-200 hover:bg-gray-50 dark:hover:bg-gray-700 ' + (n.is_read ? '' : 'bg-emerald-50/40 dark:bg-emerald-900/10') + '" onclick="DashNotif.read(' + n.id + ', this)">' +
                        dot +
                        '<div class="min-w-0 flex-1"><div class="text-[13px] font-bold text-gray-800 dark:text-gray-200">' + (n.title ? String(n.title).replace(/</g, '&lt;') : '') + '</div>' +
                        '<div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">' + (n.body ? String(n.body).replace(/</g, '&lt;') : '') + '</div>' +
                        '<div class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">' + (n.created_at ? new Date(n.created_at).toLocaleDateString('ar-SA', { year: 'numeric', month: 'short', day: 'numeric' }) : '') + '</div></div></div>';
                }).join('');
            },
            open: async function () {
                var p = this._provider;
                if (!p) return;
                var list = this._listEl();
                if (!list) return;
                if (this._loaded) return;
                this._loaded = true;
                list.innerHTML = '<div class="flex justify-center py-8"><i class="ti ti-loader text-xl text-gray-300 animate-spin"></i></div>';
                try {
                    var d = await p.getAll();
                    this.render(d && d.data, d && d.unread);
                } catch (e) {
                    list.innerHTML = '<div class="px-4 py-8 text-center text-sm text-red-500">تعذّر تحميل الإشعارات</div>';
                    this._setBadge(0);
                }
            },
            read: async function (id, el) {
                var p = this._provider;
                if (!p) return;
                if (el) { el.classList.remove('bg-emerald-50/40', 'dark:bg-emerald-900/10'); }
                try { await p.markRead(id); } catch (e) { /* ignore */ }
                this.refreshBadge();
                this._openFromRead(id);
            },
            _openFromRead: function (id) {
                var item = null;
                (this._items || []).forEach(function (n) { if (n.id === id) item = n; });
                if (!item) return;
                try {
                    window.dispatchEvent(new CustomEvent('amrtm:notif-open', { detail: item }));
                } catch (e) { /* ignore */ }
            },
            markAll: async function () {
                var p = this._provider;
                if (!p) return;
                try { await p.markAllRead(); } catch (e) { /* ignore */ }
                this._loaded = false;
                this.open();
                this.refreshBadge();
            },
            showAll: async function () {
                var p = this._provider;
                var list = this._listEl();
                if (!p || !list) return;
                var allEl = this._allEl();
                if (this._allMode) {
                    this._allMode = false;
                    this._loaded = false;
                    this.open();
                    if (allEl) allEl.textContent = 'عرض كل الإشعارات';
                    return;
                }
                list.innerHTML = '<div class="flex justify-center py-8"><i class="ti ti-loader text-xl text-gray-300 animate-spin"></i></div>';
                try {
                    var d = await p.getAll(true);
                    this.render((d && d.data) || [], d && d.unread);
                    this._allMode = true;
                    if (allEl) allEl.textContent = 'عرض الأحدث فقط';
                } catch (e) {
                    list.innerHTML = '<div class="px-4 py-8 text-center text-sm text-red-500">تعذّر تحميل كل الإشعارات</div>';
                }
            }
        };

        {{-- Drain providers registered by child pages --}}
        (window.__dashNotifProviders = window.__dashNotifProviders || []).forEach(function (p) {
            window.DashNotif.setProvider(p);
            window.DashNotif.refreshBadge();
        });

        {{-- Lazy-load on first opening of the Flowbite dropdown --}}
        var notifBtn = document.getElementById('dash-notifications-dropdown-btn');
        var notifPanel = document.getElementById('dash-notifications-dropdown');
        if (notifBtn && notifPanel) {
            notifBtn.addEventListener('click', function () {
                setTimeout(function () {
                    if (!notifPanel.classList.contains('hidden')) window.DashNotif.open();
                }, 60);
            });
        }
        var markAllBtn = document.getElementById('dash-notif-markall');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', function () { window.DashNotif.markAll(); });
        }
        var allNotifBtn = document.getElementById('dash-notif-all');
        if (allNotifBtn) {
            allNotifBtn.addEventListener('click', function (e) {
                e.preventDefault();
                window.DashNotif.showAll();
            });
        }

        {{-- Poll every 30s when a provider exists --}}
        setInterval(function () { window.DashNotif.refreshBadge(); }, 30000);

        {{-- Arm audio for new-notification chime (respects autoplay policy) --}}
        window.DashNotif._armAudio();

        {{-- ══ Dark mode (Flowbite pattern: class on <html>, persisted key color-theme) ══ --}}
        var themeBtn = document.querySelector('[data-dashboard-dark-toggle]');

        function applyDark(isDark) {
            document.documentElement.classList.toggle('dark', isDark);
            if (themeBtn) themeBtn.querySelector('i').className = isDark ? 'ti ti-sun text-xl' : 'ti ti-moon text-xl';
        }

        function themeInit() {
            var saved = null;
            try { saved = localStorage.getItem('color-theme'); } catch (e) { /* ignore */ }
            applyDark(
                saved === 'dark' ||
                (saved !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches)
            );
        }

        themeInit();

        if (themeBtn) {
            themeBtn.addEventListener('click', function () {
                var next = !document.documentElement.classList.contains('dark');
                applyDark(next);
                try { localStorage.setItem('color-theme', next ? 'dark' : 'light'); } catch (e) { /* ignore */ }
            });
        }
    });
</script>
@endpush