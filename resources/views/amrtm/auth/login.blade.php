@extends('layouts.public')

@section('title', 'تسجيل الدخول — آمر تم')

@php
    $initialMode = session('_auth_mode', '');
@endphp

@section('content')

    {{-- NAVBAR --}}
    @include('partials.public.navbar', ['active' => 'home'])

    <div class="relative flex min-h-[calc(100vh-72px)] items-center justify-center bg-[#F4F6FB] px-4 py-10 sm:px-6">

        <!-- main card -->
        <div
            class="relative flex w-full max-w-[1050px] min-h-[650px] flex-col overflow-hidden rounded-[28px] shadow-[0_35px_90px_rgba(0,108,53,.22)] lg:flex-row">

            <!-- form panel -->
            <div
                class="relative max-h-[90vh] flex-1 overflow-y-auto bg-gradient-to-br from-[#006C35] via-[#047A3F] to-[#005C2E] px-6 py-10 sm:px-10 lg:px-14">

                <!-- auth tabs -->
                <div
                    class="mb-8 flex w-full gap-1 rounded-full border border-white/20 bg-white/10 p-1 shadow-[inset_0_1px_0_rgba(255,255,255,.06)]">
                    <button type="button" id="tab-login"
                        class="auth-tab flex-1 cursor-pointer rounded-full px-2 py-3 text-sm font-extrabold transition-all duration-300"
                        onclick="showMode('login')">
                        تسجيل الدخول
                    </button>
                    <button type="button" id="tab-register"
                        class="auth-tab flex-1 cursor-pointer rounded-full px-2 py-3 text-sm font-extrabold transition-all duration-300"
                        onclick="showMode('register')">
                        حساب جديد
                    </button>
                </div>

                <!-- ===================== LOGIN ===================== -->
                <div id="login-section" class="{{ $initialMode === 'register' ? 'hidden' : '' }}">
                    <h1 class="mb-1 text-2xl font-extrabold leading-relaxed text-white sm:text-[27px]">
                        تسجيل الدخول
                    </h1>
                    <p class="mb-7 text-[13px] leading-relaxed text-white/70">
                        أدخل بيانات حسابك للوصول إلى منصة آمر تم
                    </p>

                    @if(session('success'))
                        <div
                            class="mb-5 flex items-center gap-2 rounded-xl border border-green-300/40 bg-green-400/20 px-4 py-3 text-sm font-semibold text-green-50">
                            <i class="ti ti-circle-check"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any() && $initialMode !== 'register')
                        <div
                            class="mb-5 flex items-center gap-2 rounded-xl border border-red-300/40 bg-red-500/20 px-4 py-3 text-sm font-semibold text-red-50">
                            <i class="ti ti-alert-circle"></i>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('amrtm.login.submit') }}" autocomplete="on">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ request('redirect') }}">

                        <div class="mb-5">
                            <label for="login-email" class="mb-1.5 block text-sm font-semibold text-white">البريد
                                الإلكتروني</label>
                            <div class="relative">
                                <i
                                    class="ti ti-mail pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                                <input type="email" id="login-email" name="email" value="{{ old('email') }}"
                                    placeholder="example@email.com" autocomplete="email" required
                                    class="w-full rounded-xl border border-white/25 bg-white/15 py-3 pr-11 pl-4 text-sm text-white placeholder:text-white/45 backdrop-blur-md transition-all duration-300 focus:border-white/50 focus:outline-none focus:ring-4 focus:ring-white/20">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label for="login-password" class="mb-1.5 block text-sm font-semibold text-white">كلمة
                                المرور</label>
                            <div class="relative">
                                <i
                                    class="ti ti-lock pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                                <input type="password" id="login-password" name="password" placeholder="أدخل كلمة المرور"
                                    autocomplete="current-password" value="" required
                                    class="w-full rounded-xl border border-white/25 bg-white/15 py-3 pr-11 pl-12 text-sm text-white placeholder:text-white/45 backdrop-blur-md transition-all duration-300 focus:border-white/50 focus:outline-none focus:ring-4 focus:ring-white/20">
                                <button type="button"
                                    class="absolute left-2.5 top-1/2 -translate-y-1/2 cursor-pointer rounded-lg p-2 text-white/60 transition-colors duration-200 hover:text-white"
                                    onclick="togglePassword('login-password','login-eye')">
                                    <i id="login-eye" class="ti ti-eye"></i>
                                </button>
                            </div>

                            @include('partials.public.password-requirements', ['target' => 'login-password', 'theme' => 'dark'])
                        </div>

                        <label
                            class="mb-6 flex cursor-pointer select-none items-center gap-2.5 text-sm font-semibold text-white/85">
                            <input type="checkbox" name="remember" class="h-4 w-4 cursor-pointer rounded accent-[#5FD4A8]">
                            تذكرني
                        </label>

                        <button type="submit"
                            class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#5FD4A8] py-3.5 text-sm font-extrabold text-[#024921] transition-all duration-300 hover:bg-[#7BE3BC] hover:shadow-[0_10px_30px_rgba(95,212,168,.35)] focus:ring-4 focus:ring-[#5FD4A8]/40">
                            <i class="ti ti-login-2"></i>
                            تسجيل الدخول
                        </button>

                        <div class="mt-5 flex items-center justify-center gap-2 text-xs font-bold text-white/60">
                            <i class="ti ti-shield-check"></i>
                            اتصال آمن ومشفر
                        </div>

                        <div class="my-5 flex items-center gap-3">
                            <span class="h-px flex-1 bg-white/15"></span>
                            <span class="text-[11px] font-bold text-white/50">أو</span>
                            <span class="h-px flex-1 bg-white/15"></span>
                        </div>

                        <a href="{{ route('amrtm.nafath.show', ['intent' => 'login']) }}"
                            class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 py-3 text-sm font-extrabold text-white backdrop-blur-md transition-all duration-300 hover:border-[#5FD4A8]/60 hover:bg-white/20">
                            <i class="ti ti-shield-check"></i>
                            الدخول عبر نفاذ
                        </a>
                    </form>

                    <div class="mt-7 border-t border-white/15 pt-5">
                        <button type="button" onclick="showMode('register')"
                            class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 py-3 text-sm font-extrabold text-white transition-all duration-300 hover:border-white/40 hover:bg-white/20">
                            <i class="ti ti-user-plus"></i>
                            إنشاء حساب جديد
                        </button>
                    </div>
                </div>

                <!-- ===================== REGISTER ===================== -->
                <div id="register-section" class="{{ $initialMode === 'register' ? '' : 'hidden' }}">
                    <h1 class="mb-1 text-2xl font-extrabold leading-relaxed text-white sm:text-[27px]">
                        إنشاء حساب جديد
                    </h1>
                    <p class="mb-7 text-[13px] leading-relaxed text-white/70">
                        اختر نوع الحساب الذي تريد إنشاءه
                    </p>

                    <!-- register choice -->
                    <div id="register-choice" class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <a href="{{ route('amrtm.provider.account.create', ['type' => 'client']) }}"
                            class="relative flex min-h-[145px] cursor-pointer flex-col justify-center overflow-hidden rounded-2xl border border-white/25 bg-white/10 p-5 text-right no-underline backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:border-white/45 hover:bg-white/15 hover:shadow-[0_16px_35px_rgba(0,50,30,.30)]">
                            <div
                                class="relative z-[2] mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 text-[#A8F0D4]">
                                <i class="ti ti-user-shield"></i>
                            </div>
                            <strong class="relative z-[2] text-base font-extrabold text-white">عميل طالب خدمة</strong>
                            <small class="relative z-[2] mt-1 text-xs leading-relaxed text-white/65">
                                للأفراد والمنشآت الراغبين في طلب خدمات المنصة
                            </small>
                        </a>

                        <a href="{{ route('amrtm.provider.account.create') }}"
                            class="relative flex min-h-[145px] cursor-pointer flex-col justify-center overflow-hidden rounded-2xl border border-white/25 bg-white/10 p-5 text-right no-underline backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:border-white/45 hover:bg-white/15 hover:shadow-[0_16px_35px_rgba(0,50,30,.30)]">
                            <div
                                class="relative z-[2] mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 text-[#A8F0D4]">
                                <i class="ti ti-building"></i>
                            </div>
                            <strong class="relative z-[2] text-base font-extrabold text-white">المكاتب المساندة</strong>
                            <small class="relative z-[2] mt-1 text-xs leading-relaxed text-white/65">
                                مقدم خدمة بعمولة لكل عميل — يتم تعيين الطلبات له من لوحة التحكم
                            </small>
                        </a>

                        <a href="{{ route('amrtm.provider.account.create', ['type' => 'consultant']) }}"
                            class="relative flex min-h-[145px] cursor-pointer flex-col justify-center overflow-hidden rounded-2xl border border-white/25 bg-white/10 p-5 text-right no-underline backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:border-white/45 hover:bg-white/15 hover:shadow-[0_16px_35px_rgba(0,50,30,.30)]">
                            <div
                                class="relative z-[2] mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 text-[#A8F0D4]">
                                <i class="ti ti-user-star"></i>
                            </div>
                            <strong class="relative z-[2] text-base font-extrabold text-white">المستشارين</strong>
                            <small class="relative z-[2] mt-1 text-xs leading-relaxed text-white/65">
                                مكتب باشتراك سنوي — بدون عمولة — بياناته ظاهرة للعملاء
                            </small>
                        </a>

                        <a href="{{ route('amrtm.provider.account.create', ['type' => 'client', 'account_type' => 'establishment']) }}"
                            class="relative flex min-h-[145px] cursor-pointer flex-col justify-center overflow-hidden rounded-2xl border border-white/25 bg-white/10 p-5 text-right no-underline backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:border-white/45 hover:bg-white/15 hover:shadow-[0_16px_35px_rgba(0,50,30,.30)]">
                            <div
                                class="relative z-[2] mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 text-[#A8F0D4]">
                                <i class="ti ti-file-contract"></i>
                            </div>
                            <strong class="relative z-[2] text-base font-extrabold text-white">إنشاء عقد جديد</strong>
                            <small class="relative z-[2] mt-1 text-xs leading-relaxed text-white/65">
                                سجّل منشأتك أولاً لإنشاء العقود — وتلقي العقود الواردة كطرف ثانٍ
                            </small>
                        </a>
                    </div>

                </div>
            </div>

            <!-- brand panel -->
            <div
                class="relative hidden w-[35%] shrink-0 flex-col items-center justify-center overflow-hidden bg-gradient-to-b from-white via-[#f5fcf8] to-[#e9f7ef] p-8 text-center lg:flex">
                <div
                    class="relative z-[2] mb-6 flex h-[155px] w-[155px] items-center justify-center rounded-full border border-[#006C35]/20 bg-white shadow-[0_18px_45px_rgba(0,108,53,.12)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_22px_50px_rgba(0,108,53,.16)]">
                    <img src="{{ asset('images/new-logo1.png') }}" alt="آمر تم" class="h-[120px] w-[120px] object-contain">
                </div>

                <h2 class="relative z-[2] mb-2 text-[27px] font-extrabold leading-[1.65] text-[#14532d]">
                    أهلاً بك في<br><span>آمر تم</span>
                </h2>

                <p class="relative z-[2] max-w-[250px] text-[13px] leading-relaxed text-[#5b7a6d]">
                    منصة متكاملة لإدارة الطلبات والخدمات الحكومية وقطاع الأعمال
                </p>

                <div
                    class="relative z-[2] mt-6 inline-flex items-center gap-2 rounded-full border border-[#006C35]/20 bg-[#006C35]/8 px-4 py-2 text-xs font-extrabold text-[#006C35]">
                    <i class="ti ti-shield-check"></i>
                    منصة آمنة وموثوقة
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function togglePassword(inputId, iconId) {
                const input = document.getElementById(inputId);
                const icon = document.getElementById(iconId);
                if (!input || !icon) return;
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('ti-eye');
                    icon.classList.add('ti-eye-off');
                } else {
                    input.type = 'password';
                    icon.classList.remove('ti-eye-off');
                    icon.classList.add('ti-eye');
                }
            }

            function setTabActive(tabId, active) {
                const btn = document.getElementById(tabId);
                if (!btn) return;
                const base = [
                    'auth-tab', 'flex-1', 'cursor-pointer', 'rounded-full', 'px-2', 'py-3',
                    'text-sm', 'font-extrabold', 'transition-all', 'duration-300'
                ];
                const onCls = 'bg-gradient-to-l from-[#5FD4A8] to-[#3ECB8E] text-[#024921] shadow-[0_6px_18px_rgba(95,212,168,.35)]';
                const offCls = 'text-white/75 hover:bg-white/15 hover:text-white';
                btn.className = base.concat(active ? onCls.split(' ') : offCls.split(' ')).join(' ');
            }

            function showMode(mode) {
                const loginSection = document.getElementById('login-section');
                const regSection = document.getElementById('register-section');
                if (!loginSection || !regSection) return;

                loginSection.classList.toggle('hidden', mode !== 'login');
                regSection.classList.toggle('hidden', mode !== 'register');

                setTabActive('tab-login', mode === 'login');
                setTabActive('tab-register', mode === 'register');
            }

            document.addEventListener('DOMContentLoaded', function () {
                const initialMode = @json(session('_auth_mode', ''));

                showMode(initialMode === 'register' ? 'register' : 'login');
            });
        </script>
    @endpush
@endsection