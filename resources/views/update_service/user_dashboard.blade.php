@extends('layouts.dashboard')

@section('title', 'حسابي | آمر تم')

@section('dashboard-content')
@php
    $user = auth('business')->user();
    $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    $frontType = $isAdmin
        ? \App\Support\DashboardRegistry::TYPE_ADMIN
        : (($user->account_type ?? '') === 'establishment'
            ? \App\Support\DashboardRegistry::TYPE_ESTABLISHMENT
            : \App\Support\DashboardRegistry::TYPE_INDIVIDUAL);
    $frontTypes = [$frontType];
    $persona = [
        'key' => $isAdmin ? ($user->role ?? 'admin') : $frontType,
        'label' => $isAdmin
            ? ($user->role === 'supervisor' ? 'مشرف' : 'مدير النظام')
            : \App\Support\DashboardRegistry::types()[$frontType]['ar'],
        'types' => $frontTypes,
        'name' => $user->name ?? '',
    ];
    $pageTitle = 'حسابي — ' . $persona['label'];
    $hubStats = [
        'my_requests' => $user ? \App\Models\ServiceRequest::query()->where('user_id', $user->id)->count() : 0,
    ];
    $dashboardMenu = \App\Support\DashboardRegistry::menuFor($frontTypes, $isAdmin ? 'admin' : 'user');
    foreach ($dashboardMenu as &$grp) {
        foreach ($grp['items'] as &$itm) {
            $itm['count'] = $hubStats[$itm['key']] ?? null;
        }
        unset($itm);
    }
    unset($grp);
@endphp
<style>
:root{--pri:#059669;--pri2:#047857;--pri3:#16a34a;--bg:#f8fafc;--sur:#fff;--sur2:#f8fafc;--b1:rgba(5,150,105,.1);--b2:rgba(5,150,105,.2);--bc:rgba(5,150,105,.07);--t1:#0f172a;--t2:#334155;--t3:#64748b;--t4:#94a3b8;--pd:rgba(5,150,105,.08);--pd2:rgba(5,150,105,.14);--sh:rgba(5,150,105,.07);--sh2:rgba(5,150,105,.15);--hf:#059669;--ht:#16a34a;--green:#047857;--orange:#E65100;--red:#dc2626;--blue:#0277BD;--yellow:#F9A825;--purple:#6A1B9A;}
body.ar{font-family:'Cairo',sans-serif;direction:rtl;}
body.en{font-family:'Inter',sans-serif;direction:ltr;}

/* LAYOUT */
.layout{display:flex;height:100vh;overflow:hidden;}
.od-main{flex:1;display:flex;flex-direction:column;min-height:100vh;width:100%;}
.od-main .main{overflow:visible;}

/* MAIN */
.main{flex:1;overflow-y:auto;display:flex;flex-direction:column;}
/* Topbar actions */
.lng{display:flex;padding:2px;border-radius:8px;background:var(--sur2);border:1px solid var(--b1);gap:1px;}
.lt{padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;color:var(--t3);transition:all .2s;}
.lt.on{background:var(--pri);color:#fff;}
.tb-btn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:9px;background:var(--pri);color:#fff;font-family:inherit;font-size:12.5px;font-weight:700;cursor:pointer;border:none;text-decoration:none;transition:all .2s;}
.tb-btn:hover{background:var(--pri2);}

/* CONTENT */
.page{display:none;}
.page.on{display:block;animation:fu .28s ease;}
@keyframes fu{from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:translateY(0);}}

/* REQUEST CARDS */
.req-list{display:flex;flex-direction:column;gap:.85rem;}
.req-card{background:var(--sur);border-radius:14px;border:1px solid var(--b1);overflow:hidden;box-shadow:0 2px 8px var(--sh);}
.req-hd{display:flex;align-items:center;gap:.9rem;padding:.95rem 1.2rem;cursor:pointer;}
.req-ico{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.req-ico i{font-size:19px;}
.req-info{flex:1;min-width:0;}
.req-nm{font-size:13px;font-weight:700;color:var(--t1);}
.req-meta{font-size:11px;color:var(--t3);margin-top:2px;display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;}
.dot{width:3px;height:3px;border-radius:50%;background:var(--t4);}
.req-st{padding:4px 11px;border-radius:20px;font-size:11.5px;font-weight:700;flex-shrink:0;}
.req-st.pending   {background:rgba(230,81,0,.1);color:var(--orange);}
.req-st.processing{background:rgba(2,119,189,.1);color:var(--blue);}
.req-st.in_progress{background:rgba(249,168,37,.1);color:var(--yellow);}
.req-st.done      {background:rgba(4,120,87,.1);color:var(--green);}
.req-st.rejected  {background:rgba(220,38,38,.1);color:var(--red);}
.req-chv{font-size:15px;color:var(--t4);transition:transform .2s;}
.req-card.open .req-chv{transform:rotate(180deg);}
/* Detail */
.req-body{display:none;border-top:1px solid var(--b1);padding:1.1rem 1.2rem;}
.req-card.open .req-body{display:block;}
/* Timeline */
.timeline{margin-top:.8rem;}
.tl-item{display:flex;gap:.8rem;padding:.5rem 0;position:relative;}
.tl-item:not(:last-child)::after{content:'';position:absolute;right:11px;top:26px;width:1px;height:calc(100% - 10px);background:var(--b1);}
body.en .tl-item:not(:last-child)::after{right:auto;left:11px;}
.tl-dot{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0;margin-top:1px;}
.tl-txt{font-size:12px;color:var(--t2);flex:1;line-height:1.5;}
.tl-time{font-size:10.5px;color:var(--t3);}
/* Reject reason box */
.rej-box{background:rgba(220,38,38,.06);border:1px solid rgba(220,38,38,.2);border-radius:10px;padding:.8rem 1rem;margin-top:.8rem;font-size:12.5px;color:var(--red);display:flex;align-items:flex-start;gap:7px;}
.rej-box i{font-size:15px;flex-shrink:0;margin-top:1px;}
/* Estimated time */
.est-box{background:rgba(2,119,189,.07);border:1px solid rgba(2,119,189,.2);border-radius:10px;padding:.75rem 1rem;margin-top:.7rem;font-size:12.5px;color:var(--blue);display:flex;align-items:center;gap:7px;}
.of-box{background:rgba(106,27,154,.07);border:1px solid rgba(106,27,154,.2);border-radius:10px;padding:.75rem 1rem;margin-top:.7rem;font-size:12.5px;color:var(--purple);display:flex;align-items:center;gap:7px;}

/* PROFILE PAGE */
.av-img{width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid var(--pri);box-shadow:0 4px 14px var(--sh2);}
.av-file{display:none;}
/* Profile form */
.fld{margin-bottom:1rem;}
.fld label{display:block;font-size:12.5px;font-weight:700;color:var(--t1);margin-bottom:5px;}
.fld input{width:100%;height:44px;padding:0 13px;border-radius:10px;border:1.5px solid var(--b1);background:var(--sur);color:var(--t1);font-family:inherit;font-size:13px;outline:none;transition:all .2s;}
.fld input:focus{border-color:var(--pri);box-shadow:0 0 0 3px var(--pd);}
.save-btn{display:inline-flex;align-items:center;gap:6px;padding:9px 20px;border-radius:10px;background:var(--pri);color:#fff;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;border:none;transition:all .2s;}
.save-btn:hover{background:var(--pri2);}

/* CHARGE MODAL */
.cm-box{background:var(--sur);border-radius:20px;width:100%;max-width:400px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.2);}
.cm-hd{background:linear-gradient(135deg,var(--hf),var(--ht));padding:1.3rem 1.7rem;display:flex;align-items:center;gap:.8rem;}
.cm-hd i{font-size:22px;color:#fff;}
.cm-hd-nm{font-size:16px;font-weight:800;color:#fff;}
.cm-x{margin-right:auto;width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,.18);border:none;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;cursor:pointer;}
body.en .cm-x{margin-right:0;margin-left:auto;}
.cm-body{padding:1.5rem 1.7rem;}
.custom-inp{width:100%;height:46px;padding:0 14px;border-radius:11px;border:1.5px solid var(--b1);background:var(--sur);color:var(--t1);font-family:inherit;font-size:14px;outline:none;margin-bottom:1.2rem;transition:border-color .2s;}
.custom-inp:focus{border-color:var(--pri);}
.cm-sub{width:100%;height:48px;background:linear-gradient(135deg,var(--hf),var(--ht));color:#fff;font-family:inherit;font-size:15px;font-weight:800;border:none;border-radius:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;box-shadow:0 4px 14px var(--sh2);transition:all .25s;}
.cm-sub:hover{transform:translateY(-1px);}
.cm-sub.ld{opacity:.75;pointer-events:none;}

/* PAYMENT HISTORY */
.pay-table{background:var(--sur);border-radius:14px;border:1px solid var(--b1);overflow:hidden;box-shadow:0 2px 8px var(--sh);}
.pay-row{display:flex;align-items:center;gap:.9rem;padding:.85rem 1.2rem;border-bottom:1px solid var(--bc);}
.pay-row:last-child{border-bottom:none;}
.pay-ico{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;}
.pay-nm{font-size:12.5px;font-weight:700;color:var(--t1);flex:1;}
.pay-date{font-size:11px;color:var(--t3);}
.pay-amt{font-size:14px;font-weight:800;flex-shrink:0;}
.pay-amt.credit{color:var(--green);}
.pay-amt.debit{color:var(--red);}

/* RESPONSIVE */
@media(max-width:580px){
  .fld{margin-bottom:.8rem;}
}
</style>
<div class="od-main">
<!-- MAIN -->
<div class="main">
  @push('dash-actions')
    <!-- زر المحادثات — يفتح أحدث طلب داخل صفحة التتبع (حيث المحادثة مع المكتب) -->
    <x-ui.button class="tb-btn" onclick="openLatestTrack()" title="المحادثات" aria-label="المحادثات"><i class="ti ti-messages text-xl"></i></x-ui.button>
    <div class="lng"><div class="lt on" id="la" onclick="setLang('ar')">AR</div><div class="lt" id="le" onclick="setLang('en')">EN</div></div>
    <x-ui.button class="tb-btn" onclick="openChargeModal()"><i class="ti ti-plus"></i><span id="tb-charge">شحن الرصيد</span></x-ui.button>
  @endpush

  <!-- الشريط العلوي + لوحة الإشعارات المكررة حُذفت — الإشعارات في topbar اللayout (DashNotif) -->

  <div class="p-6 md:p-8">

    <!-- OVERVIEW PAGE -->
    <div class="page on" id="page-overview">
      <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
        <div class="text-2xl font-extrabold text-[--t1]" id="ov-ttl">مرحباً! 👋</div>
        <div class="mt-1 text-sm text-[--t3]" id="ov-sub">إليك ملخص حسابك</div></div>
        <a class="inline-flex items-center gap-2 rounded-xl bg-[--pri] px-4 py-2 text-sm font-bold text-white transition hover:bg-[--pri2]" href="{{ route('amrtm.index') }}"><i class="ti ti-plus"></i><span id="ov-new">طلب جديد</span></a>
      </div>
      <!-- Stats -->
      <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="flex items-center gap-4 rounded-2xl bg-[--sur] p-5 shadow-[--sh] transition-transform hover:-translate-y-0.5"><div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-xl" style="background:rgba(2,119,189,.1);"><i class="ti ti-file-text" style="color:var(--blue);"></i></div><div><div class="text-2xl font-black text-[--t1]" id="sc-total">0</div><div class="mt-0.5 text-[13px] text-[--t3]" id="sc-total-l">إجمالي الطلبات</div></div></div>
        <div class="flex items-center gap-4 rounded-2xl bg-[--sur] p-5 shadow-[--sh] transition-transform hover:-translate-y-0.5"><div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-xl" style="background:rgba(230,81,0,.1);"><i class="ti ti-loader" style="color:var(--orange);"></i></div><div><div class="text-2xl font-black text-[--t1]" id="sc-pend">0</div><div class="mt-0.5 text-[13px] text-[--t3]" id="sc-pend-l">قيد الانتظار</div></div></div>
        <div class="flex items-center gap-4 rounded-2xl bg-[--sur] p-5 shadow-[--sh] transition-transform hover:-translate-y-0.5"><div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-xl" style="background:rgba(4,120,87,.1);"><i class="ti ti-circle-check" style="color:var(--green);"></i></div><div><div class="text-2xl font-black text-[--t1]" id="sc-done">0</div><div class="mt-0.5 text-[13px] text-[--t3]" id="sc-done-l">مكتملة</div></div></div>
        <div class="flex items-center gap-4 rounded-2xl bg-[--sur] p-5 shadow-[--sh] transition-transform hover:-translate-y-0.5"><div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-xl" style="background:linear-gradient(135deg,var(--hf),var(--ht));"><i class="ti ti-wallet" style="color:#fff;"></i></div><div><div class="text-2xl font-black text-[--t1]" id="sc-bal">0</div><div class="mt-0.5 text-[13px] text-[--t3]" id="sc-bal-l">رصيدي (ر.س)</div></div></div>
      </div>
      <!-- Recent requests -->
      <div class="mb-4 text-sm font-bold text-[--t1]" id="ov-recent">آخر الطلبات</div>
      <div class="req-list" id="ov-req-list"></div>
    </div>

    <!-- REQUESTS PAGE -->
    <div class="page" id="page-requests">
      <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><div class="text-2xl font-extrabold text-[--t1]" id="req-ttl">طلباتي</div><div class="mt-1 text-sm text-[--t3]" id="req-sub">جميع طلباتك المقدمة</div></div>
        <a class="inline-flex items-center gap-2 rounded-xl bg-[--pri] px-4 py-2 text-sm font-bold text-white transition hover:bg-[--pri2]" href="{{ route('amrtm.index') }}"><i class="ti ti-plus"></i><span id="req-new">طلب جديد</span></a>
      </div>
      <!-- Filter -->
      <div class="mb-5 flex flex-wrap gap-2.5">
        <x-ui.button class="filter-btn on" onclick="filterReqs('all',this)" id="rf-all">الكل</x-ui.button>
        <x-ui.button class="filter-btn" onclick="filterReqs('pending',this)" id="rf-pend">قيد الانتظار</x-ui.button>
        <x-ui.button class="filter-btn" onclick="filterReqs('processing',this)" id="rf-proc">جاري المعالجة</x-ui.button>
        <x-ui.button class="filter-btn" onclick="filterReqs('done',this)" id="rf-done">مكتملة</x-ui.button>
        <x-ui.button class="filter-btn" onclick="filterReqs('rejected',this)" id="rf-rej">مرفوضة</x-ui.button>
      </div>
      <div class="req-list" id="req-list"></div>
    </div>

    <!-- PROFILE PAGE -->
    <div class="page" id="page-profile">
      <div class="mb-6 flex items-center justify-between"><div><div class="text-2xl font-extrabold text-[--t1]" id="prof-ttl">ملفي الشخصي</div><div class="mt-1 text-sm text-[--t3]" id="prof-sub">إدارة بياناتك الشخصية</div></div></div>
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-[340px_1fr]">
        <div class="flex flex-col gap-6">
          <!-- Avatar card -->
          <div class="rounded-2xl bg-[--sur] p-8 text-center shadow-[--sh]">
            <div class="mx-auto relative h-24 w-24">
              <img class="av-img h-24 w-24 rounded-full border-4 border-[--b2] object-cover" id="av-img" src="" alt=""/>
              <div class="absolute bottom-0 start-[-2px] flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-[--pri] text-white shadow" onclick="document.getElementById('av-file').click()"><i class="ti ti-camera"></i></div>
              <x-ui.input type="file" class="av-file hidden" id="av-file" accept="image/*" onchange="uploadAvatar(this)" />
            </div>
            <div class="mt-3 text-lg font-bold text-[--t1]" id="av-nm">—</div>
            <div class="text-sm text-[--t3]" id="av-role">مستخدم</div>
          </div>
          <!-- Balance -->
          <div class="rounded-2xl bg-gradient-to-br from-[--hf] to-[--ht] p-6 text-center text-white shadow-[--sh2]">
            <div class="text-sm text-white/70" id="bal-lbl">رصيدك الحالي</div>
            <div class="mt-1 text-3xl font-black tabular-nums" id="bal-val">0.00</div>
            <div class="text-xs text-white/60" id="bal-sub">ريال سعودي</div>
            <x-ui.button class="mt-4 inline-flex items-center gap-2 rounded-xl bg-white/95 px-5 py-2.5 text-sm font-bold text-[--hf] transition hover:bg-white" onclick="openChargeModal()"><i class="ti ti-plus"></i><span id="charge-lbl">شحن الرصيد</span></x-ui.button>
          </div>
        </div>
        <!-- Profile form -->
        <div class="rounded-2xl bg-[--sur] p-8 shadow-[--sh]">
          <div class="mb-5 text-base font-extrabold text-[--t1]" id="pf-ttl">تعديل البيانات الشخصية</div>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="fld col-span-1"><x-ui.label id="pf-ln" :for="'pf-name'">الاسم الكامل</x-ui.label><x-ui.input type="text" id="pf-name" /></div>
            <div class="fld col-span-1"><x-ui.label id="pf-le" :for="'pf-email'">البريد الإلكتروني</x-ui.label><x-ui.input type="email" id="pf-email" /></div>
          </div>
          <div class="fld"><x-ui.label id="pf-lp" :for="'pf-phone'">رقم الهاتف</x-ui.label><x-ui.input type="tel" id="pf-phone" /></div>
          <x-ui.button class="save-btn" onclick="saveProfile()" id="save-btn"><i class="ti ti-check"></i><span id="save-lbl">حفظ التغييرات</span></x-ui.button>
          <div class="my-6 h-px bg-[--bc]"></div>
          <div class="mb-5 text-base font-extrabold text-[--t1]" id="pf-pass-ttl">تغيير كلمة المرور</div>
          <div class="fld"><x-ui.label id="pf-lcp" :for="'pf-old-pass'">كلمة المرور الحالية</x-ui.label><x-ui.eye-field id="pf-old-pass" placeholder="••••••••" class="w-full rounded-xl border border-[--b1] bg-[--sur2] px-4 py-2.5 outline-none transition focus:border-[--pri]" /></div>
          <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="fld"><x-ui.label id="pf-lnp" :for="'pf-new-pass'">كلمة المرور الجديدة</x-ui.label><x-ui.eye-field id="pf-new-pass" placeholder="••••••••" class="w-full rounded-xl border border-[--b1] bg-[--sur2] px-4 py-2.5 outline-none transition focus:border-[--pri]" /></div>
            <div class="fld"><x-ui.label id="pf-lcp2" :for="'pf-conf-pass'">تأكيد كلمة المرور</x-ui.label><x-ui.eye-field id="pf-conf-pass" placeholder="••••••••" class="w-full rounded-xl border border-[--b1] bg-[--sur2] px-4 py-2.5 outline-none transition focus:border-[--pri]" /></div>
          </div>
          <x-ui.button class="save-btn mt-5" onclick="changePass()" style="background:rgba(220,38,38,.1);color:var(--red);box-shadow:none;border:1px solid rgba(220,38,38,.2);" id="pass-btn"><i class="ti ti-lock"></i><span id="pass-lbl">تغيير كلمة المرور</span></x-ui.button>
        </div>
      </div>
    </div>

    <!-- PAYMENTS PAGE -->
    <div class="page" id="page-payments">
      <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><div class="text-2xl font-extrabold text-[--t1]" id="pay-ttl">سجل المدفوعات</div><div class="mt-1 text-sm text-[--t3]" id="pay-sub">جميع معاملاتك المالية</div></div>
        <x-ui.button class="inline-flex items-center gap-2 rounded-xl bg-[--pri] px-4 py-2 text-sm font-bold text-white transition hover:bg-[--pri2]" onclick="openChargeModal()"><i class="ti ti-plus"></i><span id="pay-charge">شحن الرصيد</span></x-ui.button>
      </div>
      <!-- Balance summary -->
      <div class="mb-6 flex max-w-xs items-center gap-4 rounded-2xl bg-[--sur] p-5 shadow-[--sh]">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-xl" style="background:linear-gradient(135deg,var(--hf),var(--ht));"><i class="ti ti-wallet" style="color:#fff;"></i></div>
        <div><div class="text-2xl font-black text-[--t1]" id="pay-bal">0.00</div><div class="mt-0.5 text-[13px] text-[--t3]" id="pay-bal-l">رصيدك الحالي (ر.س)</div></div>
      </div>
      <div class="pay-table" id="pay-list"></div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /od-main -->

<!-- CHARGE MODAL (Flowbite Modal) -->
<div id="charge-modal" data-modal-target="charge-modal" tabindex="-1" aria-hidden="true"
  class="fixed inset-0 z-[120] hidden items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 p-4 backdrop-blur-sm"
  onclick="if(event.target===this&&!_hpBusy)closeCharge()">
  <div class="cm-box mx-auto w-full" style="max-width:480px;">
    <div class="cm-hd">
      <i class="ti ti-wallet"></i>
      <div class="cm-hd-nm" id="cm-ttl">شحن الرصيد</div>
      <x-ui.button class="cm-x" onclick="closeCharge()"><i class="ti ti-x"></i></x-ui.button>
    </div>
    <!-- Step 1: Amount selection -->
    <div class="cm-body" id="cm-step1">
      <div style="font-size:12.5px;color:var(--t2);margin-bottom:.8rem;" id="cm-sub">اختر المبلغ أو أدخل مبلغاً مخصصاً</div>
      <x-ui.chips
        class="mb-3"
        :items="[
          ['value' => '100', 'label' => '100 ر.س', 'onchange' => 'setAmt(100)'],
          ['value' => '250', 'label' => '250 ر.س', 'onchange' => 'setAmt(250)'],
          ['value' => '500', 'label' => '500 ر.س', 'onchange' => 'setAmt(500)'],
          ['value' => '1000', 'label' => '1,000 ر.س', 'onchange' => 'setAmt(1000)'],
          ['value' => '2000', 'label' => '2,000 ر.س', 'onchange' => 'setAmt(2000)'],
          ['value' => '5000', 'label' => '5,000 ر.س', 'onchange' => 'setAmt(5000)'],
        ]"
      />
      <x-ui.amount-field id="cm-amt" placeholder="أو أدخل مبلغاً مخصصاً..." min="10" :step="1" />
      <x-ui.button class="cm-sub" id="cm-sub-btn" onclick="doCharge()">
        <i class="ti ti-credit-card"></i><span id="cm-btn-lbl">متابعة للدفع</span>
      </x-ui.button>
    </div>
    <!-- Step 2: HyperPay redirect -->
    <div class="cm-body" id="cm-step2" style="display:none;">
      <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.9rem;">
        <x-ui.button onclick="backToAmount()" style="background:none;border:none;cursor:pointer;color:var(--t3);font-size:16px;padding:0;"><i class="ti ti-arrow-right"></i></x-ui.button>
        <span style="font-size:13px;font-weight:700;color:var(--t2);">تأكيد الدفع</span>
        <span style="margin-right:auto;font-size:14px;font-weight:800;color:var(--pri);" id="cm-pay-amt"></span>
      </div>
      <div style="padding:.6rem 0 .2rem;font-size:13.5px;color:var(--t2);text-align:center;">
        سيتم تحويلك إلى بوابة الدفع الآمنة لإتمام عملية الدفع ببطاقتك.
      </div>
      <x-ui.button class="cm-sub" id="cm-hp-btn" onclick="finishChargeToGateway()">
        <i class="ti ti-credit-card"></i><span id="cm-hp-lbl">إتمام الدفع عبر البوابة</span>
      </x-ui.button>
      <div style="margin-top:.8rem;display:flex;align-items:center;justify-content:center;gap:.4rem;">
        <span style="font-size:10px;color:var(--t4);">مدفوعات آمنة عبر بوابة HyperPay</span>
      </div>
    </div>
  </div>
</div>

<style>
.filter-btn{padding:6px 14px;border-radius:8px;font-size:12.5px;font-weight:600;cursor:pointer;border:1.5px solid var(--b1);background:transparent;color:var(--t2);transition:all .2s;font-family:inherit;}
.filter-btn.on{background:var(--pri);color:#fff;border-color:var(--pri);}
.filter-btn:hover:not(.on){background:var(--pd);}
</style>

<script>
window.AMRTM_USER = {!! auth('business')->check() ? json_encode([
    'id'    => auth('business')->id(),
    'name'  => auth('business')->user()->name,
    'email' => auth('business')->user()->email,
    'phone' => auth('business')->user()->phone ?? '',
    'role'  => auth('business')->user()->role,
    'balance' => 0,
]) : 'null' !!};
window.AMRTM_CSRF = '{{ csrf_token() }}';
window.AMRTM_API_BASE = '{{ url("/api") }}';

// ── Notifications SDK ──
window.Notifications = {
  _base: window.AMRTM_API_BASE + '/notifications',
  _h: () => ({ 'Accept':'application/json','X-CSRF-TOKEN':window.AMRTM_CSRF,'Content-Type':'application/json' }),
  getAll:     async function(all)    { const r = await fetch(this._base+(all?'?all=1':''), {headers:this._h(),credentials:'same-origin'}); return r.json(); },
  unreadCount:async function()    { const r = await fetch(this._base+'/unread-count', {headers:this._h(),credentials:'same-origin'}); return r.json(); },
  markRead:   async function(id)  { await fetch(this._base+'/'+id+'/read', {method:'POST',headers:this._h(),credentials:'same-origin'}); },
  markAllRead:async function()    { await fetch(this._base+'/read-all',   {method:'POST',headers:this._h(),credentials:'same-origin'}); },
};
window.AMRTM_ROUTES = {
    login:         '{{ route("amrtm.login") }}',
    register:      '{{ route("amrtm.register") }}',
    logout:        '{{ route("amrtm.logout") }}',
    home:          '{{ route("amrtm.index") }}',
    userDashboard: '{{ route("amrtm.user.dashboard") }}',
    adminDashboard:'{{ route("amrtm.admin.dashboard") }}',
    mainSite:      '{{ url("/") }}',
};
window.AMRTM_HYPERPAY_ENABLED = @json(app(\App\Services\HyperPayService::class)->isConfigured());
@if(session('payment_success'))
window._paymentMsg = {type:'success', text: '{{ session("payment_success") }}'};
@elseif(session('payment_error'))
window._paymentMsg = {type:'error', text: '{{ session("payment_error") }}'};
@endif
</script>
<script src="{{ asset('js/amrtm-web.js') }}"></script>
<script>
const T={
  ar:{nm:'آمر تم',da:'حسابي',s1:'الرئيسية',s2:'الطلبات',s3:'الحساب',s4:'أخرى',siOv:'نظرة عامة',siReq:'طلباتي',siProf:'ملفي الشخصي',siPay:'المدفوعات',siHome:'الرئيسية',tbCharge:'شحن الرصيد',ovTtl:'مرحباً! 👋',ovSub:'إليك ملخص حسابك',ovNew:'طلب جديد',ovRecent:'آخر الطلبات',scTot:'إجمالي الطلبات',scPend:'قيد الانتظار',scDone:'مكتملة',scBal:'رصيدي (ر.س)',reqTtl:'طلباتي',reqSub:'جميع طلباتك المقدمة',reqNew:'طلب جديد',rfAll:'الكل',rfPend:'قيد الانتظار',rfProc:'جاري المعالجة',rfDone:'مكتملة',rfRej:'مرفوضة',profTtl:'ملفي الشخصي',profSub:'إدارة بياناتك الشخصية',pfTtl:'تعديل البيانات الشخصية',pfLn:'الاسم الكامل',pfLe:'البريد الإلكتروني',pfLp:'رقم الهاتف',saveLbl:'حفظ التغييرات',pfPassTtl:'تغيير كلمة المرور',pfLcp:'كلمة المرور الحالية',pfLnp:'كلمة المرور الجديدة',pfLcp2:'تأكيد كلمة المرور',passLbl:'تغيير كلمة المرور',balLbl:'رصيدك الحالي',balSub:'ريال سعودي',chargeLbl:'شحن الرصيد',payTtl:'سجل المدفوعات',paySub:'جميع معاملاتك المالية',payCharge:'شحن الرصيد',payBalL:'رصيدك الحالي (ر.س)',cmTtl:'شحن الرصيد',cmSub:'اختر المبلغ أو أدخل مبلغاً مخصصاً',cmBtnLbl:'شحن الآن',sar:'ر.س',tbTitle:'نظرة عامة',user:'مستخدم',
    stPend:'قيد الانتظار',stProc:'جاري المعالجة',stInprog:'قيد التنفيذ',stDone:'تمت العملية',stRej:'مرفوض',
    estLbl:'وقت الإنجاز المتوقع: ',rejLbl:'سبب الرفض: ',noReqs:'لا توجد طلبات',noPayments:'لا توجد معاملات',
    chargeSuccess:'تم شحن الرصيد بنجاح!',saveSuc:'تم حفظ البيانات',passOk:'تم تغيير كلمة المرور',
  },
  en:{nm:'Amrtm',da:'My Account',s1:'Main',s2:'Requests',s3:'Account',s4:'Other',siOv:'Overview',siReq:'My Requests',siProf:'My Profile',siPay:'Payments',siHome:'Home',tbCharge:'Top Up',ovTtl:'Welcome! 👋',ovSub:'Here is your account summary',ovNew:'New Request',ovRecent:'Recent Requests',scTot:'Total Requests',scPend:'Pending',scDone:'Completed',scBal:'Balance (SAR)',reqTtl:'My Requests',reqSub:'All your submitted requests',reqNew:'New Request',rfAll:'All',rfPend:'Pending',rfProc:'Processing',rfDone:'Completed',rfRej:'Rejected',profTtl:'My Profile',profSub:'Manage your personal information',pfTtl:'Edit Personal Information',pfLn:'Full Name',pfLe:'Email Address',pfLp:'Phone Number',saveLbl:'Save Changes',pfPassTtl:'Change Password',pfLcp:'Current Password',pfLnp:'New Password',pfLcp2:'Confirm Password',passLbl:'Change Password',balLbl:'Current Balance',balSub:'Saudi Riyal',chargeLbl:'Top Up Balance',payTtl:'Payment History',paySub:'All your financial transactions',payCharge:'Top Up',payBalL:'Current Balance (SAR)',cmTtl:'Top Up Balance',cmSub:'Choose an amount or enter a custom amount',cmBtnLbl:'Top Up Now',sar:'SAR',tbTitle:'Overview',user:'User',
    stPend:'Pending',stProc:'Processing',stInprog:'In Progress',stDone:'Completed',stRej:'Rejected',
    estLbl:'Estimated completion: ',rejLbl:'Rejection reason: ',noReqs:'No requests found',noPayments:'No transactions',
    chargeSuccess:'Balance topped up successfully!',saveSuc:'Data saved successfully',passOk:'Password changed successfully',
  }
};

let lang=localStorage.getItem('amrtm_lang')||'ar';
let curPage='overview';
let userData=null;
let allReqs=[];
let allPays=[];
let reqFilter='all';

/* ══ INIT ══ */
async function init(){
  // Require login
  if(typeof Auth!=='undefined'&&!Auth.isLoggedIn()){
    window.location.href=((AMRTM_ROUTES&&AMRTM_ROUTES.login)||'/login')+'?redirect='+encodeURIComponent(location.href);return;
  }
  const u=typeof Auth!=='undefined'?Auth.getUser():null;
  applyLang(lang);
  const _ptEl=document.getElementById('dash-page-title');
  if(_ptEl) _ptEl.textContent=T[lang].siOv;
  await loadData();
}

async function loadData(){
  try{
    const data=await Dashboard.userStats();
    userData=data.user;
    allReqs=data.recent_requests||[];
    allPays=data.recent_payments||[];
    renderAll();
  }catch(e){
    console.error('Dashboard load error:',e);
    if(typeof showToast!=='undefined')showToast('حدث خطأ في تحميل البيانات','error');
  }
}

function renderAll(){
  const t=T[lang];
  // Sidebar profile
  const av=userData?.avatar_url||`https://ui-avatars.com/api/?name=${encodeURIComponent(userData?.name||'U')}&background=1A237E&color=fff&size=64`;
  setImgSrc('av-img',av);
  S('av-nm',userData?.name||'—');S('dash-dd-name',userData?.name||'—');
  const roleMap={admin:{ar:'أدمن',en:'Admin'},supervisor:{ar:'مشرف',en:'Supervisor'},user:{ar:'مستخدم',en:'User'}};
  const roleLbl=(roleMap[userData?.role]||{})[lang]||t.user;
  S('av-role',roleLbl);
  // Stats
  const stats=userData?.stats||{};
  S('sc-total',stats.total||0);S('sc-pend',stats.pending||0);S('sc-done',stats.done||0);
  const bal=(parseFloat(userData?.balance||0)).toFixed(2);
  S('sc-bal',bal);S('bal-val',bal);S('pay-bal',bal);
  // Profile form
  setVal('pf-name',userData?.name||'');setVal('pf-email',userData?.email||'');setVal('pf-phone',userData?.phone||'');
  // Title
  S('ov-ttl',(t.ovTtl.replace('!',''))+' '+((userData?.name||'').split(' ')[0])+'! 👋');
  renderRecentReqs();
  renderReqList();
  renderPayList();
}

/* ══ REQUESTS ══ */
function renderRecentReqs(){
  renderReqCards(allReqs.slice(0,3),'ov-req-list');
}
function renderReqList(){
  loadAllRequests(_reqPage,_reqStatus);
}

/* Full requests page (paginated from API) */
let _reqPage=1;
let _reqStatus='all';
let _reqLastPage=1;

async function loadAllRequests(page,status){
  page=page||1;status=status||'all';
  _reqPage=page;_reqStatus=status;
  const el=document.getElementById('req-list');
  if(el)el.innerHTML=`<div style="text-align:center;padding:2.5rem;color:var(--t3);"><i class="ti ti-loader-2" style="font-size:28px;animation:spin .7s linear infinite;display:inline-block;"></i></div>`;
  try{
    const qs='/requests?page='+page+(status!=='all'?'&status='+status:'');
    const res=await API.get(qs);
    const items=res.data||[];
    _reqLastPage=res.last_page||1;
    renderReqCards(items,'req-list');
    renderReqPagination();
  }catch(e){
    if(el)el.innerHTML=`<div style="text-align:center;padding:2rem;color:var(--red);">حدث خطأ في تحميل الطلبات. <span style="cursor:pointer;text-decoration:underline;" onclick="loadAllRequests()">إعادة المحاولة</span></div>`;
  }
}

function renderReqPagination(){
  let pc=document.getElementById('req-pagination');
  if(!pc){
    pc=document.createElement('div');
    pc.id='req-pagination';
    pc.style.cssText='display:flex;justify-content:center;align-items:center;gap:.6rem;margin-top:1.2rem;';
    document.getElementById('req-list')?.after(pc);
  }
  if(_reqLastPage<=1){pc.innerHTML='';return;}
  pc.innerHTML=AMRTM_UI.button({onclick:"loadAllRequests("+(_reqPage-1)+",'"+_reqStatus+"')", disabled:_reqPage<=1||null, class:'filter-btn', style:_reqPage<=1?'opacity:.4;cursor:default;':''}, '<i class="ti ti-chevron-'+(lang==='ar'?'right':'left')+'"></i>')+
    '<span style="font-size:13px;color:var(--t2);font-weight:600;">'+_reqPage+' / '+_reqLastPage+'</span>'+
    AMRTM_UI.button({onclick:"loadAllRequests("+(_reqPage+1)+",'"+_reqStatus+"')", disabled:_reqPage>=_reqLastPage||null, class:'filter-btn', style:_reqPage>=_reqLastPage?'opacity:.4;cursor:default;':''}, '<i class="ti ti-chevron-'+(lang==='ar'?'left':'right')+'"></i>');
}

function filterReqs(f,btn){
  reqFilter=f;
  document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('on'));
  if(btn)btn.classList.add('on');
  loadAllRequests(1,f);
}
function renderReqCards(reqs,containerId){
  const t=T[lang];
  const el=document.getElementById(containerId);if(!el)return;
  if(!reqs||!reqs.length){
    el.innerHTML=`<div style="text-align:center;padding:2.5rem;color:var(--t3);">${t.noReqs}</div>`;return;
  }
  el.innerHTML=reqs.map((r,i)=>{
    const gs=r.gov_service||{};
    const ent=r.entity||{};
    const nm=lang==='ar'?(gs.name_ar||gs.name_en||'—'):(gs.name_en||gs.name_ar||'—');
    const entNm=lang==='ar'?(ent.name_ar||''):(ent.name_en||'');
    const {label,color,bg}=stInfo(r.status);
    const color2=ent.color||'#059669';
    const logs=r.logs||[];
    const est=r.estimated_completion;
    const rej=r.reject_reason;
    const cardId=`rc-${containerId}-${i}`;
    return `<div class="req-card" id="${cardId}">
      <div class="req-hd" onclick="togReq('${cardId}')">
        <div class="req-ico" style="background:${bg};border:1px solid ${color2}22;"><i class="ti ${gs.icon||'ti-file-text'}" style="color:${color2};"></i></div>
        <div class="req-info">
          <div class="req-nm">${nm}</div>
          <div class="req-meta"><span>${entNm}</span><div class="dot"></div><span>${r.ref_number||'—'}</span><div class="dot"></div><span>${fmtDate(r.created_at)}</span></div>
        </div>
        <div class="req-st ${r.status}">${label}</div>
        <i class="ti ti-chevron-down req-chv"></i>
      </div>
      <div class="req-body">
        ${est?`<div class="est-box"><i class="ti ti-clock"></i><span>${t.estLbl}<b>${est}</b></span></div>`:''}
        ${rej?`<div class="rej-box"><i class="ti ti-alert-circle"></i><span>${t.rejLbl}${rej}</span></div>`:''}
        ${r.office?`<div class="of-box"><i class="ti ti-building"></i><span><b>${lang==='ar'?'المنفذ':'Handler'}:</b> ${lang==='ar'?r.office.name_ar:r.office.name_en}</span></div>`:''}
        ${logs.length?`<div class="timeline">${logs.map(l=>`<div class="tl-item"><div class="tl-dot" style="background:${stInfo(l.status).bg};"><i class="ti ti-circle-check" style="color:${stInfo(l.status).color};font-size:12px;"></i></div><div class="tl-txt">${l.note||stInfo(l.status).label}</div><div class="tl-time">${fmtDate(l.created_at)}</div></div>`).join('')}</div>`:''}
        <div style="margin-top:.8rem;font-size:12px;color:var(--t3);">المبلغ: <b style="color:var(--t1);">${parseFloat(r.price||0).toFixed(2)} ${t.sar}</b></div>
        <a href="${window.TRACK_BASE||'/requests'}/${r.id}/track"
           class="inline-flex items-center gap-1.5 mt-3 rounded-lg border border-[--b1] bg-[--sur2] px-3 py-2 text-xs font-bold no-underline transition-all hover:border-[--b2] hover:bg-[#059669] hover:text-white" style="color:var(--t2);">
          <i class="ti ti-route"></i><span>${lang==='ar'?'تتبع الطلب':'Track Request'}</span>
        </a>
      </div>
    </div>`;
  }).join('');
}
function togReq(id){document.getElementById(id)?.classList.toggle('open');}

/* ══ PAYMENTS (overview summary — full page uses loadPayHistory) ══ */
function renderPayList(){}

/* ══ PROFILE ══ */
async function saveProfile(){
  const name=gV('pf-name'),phone=gV('pf-phone');
  if(!name){if(typeof showToast!=='undefined')showToast('الاسم مطلوب','warning');return;}
  const btn=document.getElementById('save-btn');
  if(btn){btn.disabled=true;btn.style.opacity='.7';}
  try{
    await Profile.update({name,phone});
    if(userData)userData.name=name;
    S('av-nm',name);S('dash-dd-name',name);
    if(typeof showToast!=='undefined')showToast(T[lang].saveSuc,'success');
  }catch(e){
    if(typeof showToast!=='undefined')showToast(e?.data?.message||'حدث خطأ','error');
  }finally{if(btn){btn.disabled=false;btn.style.opacity='';}}
}
async function changePass(){
  const old=gV('pf-old-pass'),np=gV('pf-new-pass'),cp=gV('pf-conf-pass');
  if(!old||!np||np!==cp){if(typeof showToast!=='undefined')showToast('تحقق من كلمة المرور','error');return;}
  if(np.length<8){if(typeof showToast!=='undefined')showToast('كلمة المرور يجب أن تكون 8 أحرف على الأقل','warning');return;}
  const btn=document.getElementById('pass-btn');
  if(btn){btn.disabled=true;btn.style.opacity='.7';}
  try{
    await Profile.changePassword({current_password:old,new_password:np,new_password_confirmation:cp});
    if(typeof showToast!=='undefined')showToast(T[lang].passOk,'success');
    setVal('pf-old-pass','');setVal('pf-new-pass','');setVal('pf-conf-pass','');
  }catch(e){
    if(typeof showToast!=='undefined')showToast(e?.data?.message||'حدث خطأ','error');
  }finally{if(btn){btn.disabled=false;btn.style.opacity='';}}
}
function uploadAvatar(inp){
  const file=inp.files[0];if(!file)return;
  const reader=new FileReader();
  reader.onload=e=>{setImgSrc('av-img',e.target.result);};
  reader.readAsDataURL(file);
  if(typeof showToast!=='undefined')showToast('تم تحديث الصورة محلياً','info');
}

/* ══ CHARGE ══ */
let _hpBusy = false;
let _hpRedirectUrl = null;

function openChargeModal(){
  AMRTM_MODAL.open('charge-modal');
  document.body.style.overflow='hidden';
  showStep1();
}
function closeCharge(){
  if(_hpBusy) return; // prevent accidental close while starting payment
  AMRTM_MODAL.close('charge-modal');
  document.body.style.overflow='';
  _hpBusy=false;
  _hpRedirectUrl=null;
  showStep1();
}
function showStep1(){
  document.getElementById('cm-step1').style.display='';
  document.getElementById('cm-step2').style.display='none';
  _hpBusy=false;
  _hpRedirectUrl=null;
  const btn=document.getElementById('cm-hp-btn');
  if(btn) btn.disabled=false;
}
function backToAmount(){showStep1();}
function setAmt(v){
  const inp=document.getElementById('cm-amt');
  if(inp)inp.value=v;
}
function doCharge(){
  const amt=parseFloat(document.getElementById('cm-amt').value||0);
  if(amt<10){if(typeof showToast!=='undefined')showToast('الحد الأدنى 10 ر.س','warning');return;}
  if(!window.AMRTM_HYPERPAY_ENABLED){
    if(typeof showToast!=='undefined')showToast('بوابة الدفع غير مفعّلة حالياً، تواصل مع الإدارة.','warning');
    return;
  }
  // Transition to step 2
  document.getElementById('cm-step1').style.display='none';
  document.getElementById('cm-step2').style.display='';
  document.getElementById('cm-pay-amt').textContent = amt.toFixed(2) + ' ر.س';
  _hpBusy=true;
  const btn=document.getElementById('cm-hp-btn');
  const btnLbl=document.getElementById('cm-hp-lbl');
  btn.disabled=true;
  btnLbl.textContent=lang==='ar'?'جارٍ تجهيز الدفع...':'Preparing payment...';
  fetch(AMRTM_API_BASE+'/payments/charge',{
    method:'POST',
    headers:{'Accept':'application/json','X-CSRF-TOKEN':AMRTM_CSRF,'Content-Type':'application/json'},
    credentials:'same-origin',
    body:JSON.stringify({amount:amt}),
  }).then(r=>r.json().then(d=>{return {ok:r.ok,d: d||{}};}))
    .then(({ok,d})=>{
      _hpBusy=false;
      btn.disabled=false;
      btnLbl.textContent=lang==='ar'?'إتمام الدفع عبر البوابة':'Pay via Gateway';
      if(!ok){ if(typeof showToast!=='undefined')showToast(d.message||'تعذر بدء الدفع','error'); return; }
      _hpRedirectUrl=d.redirect_url;
    })
    .catch(()=>{
      _hpBusy=false;
      btn.disabled=false;
      btnLbl.textContent=lang==='ar'?'إتمام الدفع عبر البوابة':'Pay via Gateway';
      if(typeof showToast!=='undefined')showToast('تعذر الاتصال ببوابة الدفع','error');
    });
}
function finishChargeToGateway(){
  if(_hpRedirectUrl) window.location.href=_hpRedirectUrl;
}

/* ══ NAVIGATION ══ */
function showPage(p){
  curPage=p;
  document.querySelectorAll('.page').forEach(el=>el.classList.remove('on'));
  document.getElementById('page-'+p)?.classList.add('on');
  const titles={overview:T[lang].siOv,requests:T[lang].siReq,profile:T[lang].siProf,payments:T[lang].siPay};
  S('dash-page-title',titles[p]||'');
  if(p==='requests') loadAllRequests(_reqPage,_reqStatus);
  if(p==='payments') loadPayHistory();
}

/* ══ LANG ══ */
function setLang(l){
  lang=l;localStorage.setItem('amrtm_lang',l);
  document.documentElement.setAttribute('lang',l);document.documentElement.setAttribute('dir',l==='ar'?'rtl':'ltr');
  document.body.classList.remove('ar','en');document.body.classList.add(l);
  document.getElementById('la').classList.toggle('on',l==='ar');document.getElementById('le').classList.toggle('on',l==='en');
  applyLang(l);renderAll();
  const titles={overview:T[l].siOv,requests:T[l].siReq,profile:T[l].siProf,payments:T[l].siPay};
  S('dash-page-title',titles[curPage]||'');
}
function applyLang(l){
  const t=T[l];
  [['si-ov','siOv'],['si-req','siReq'],['si-prof','siProf'],['si-pay','siPay'],['si-home','siHome'],
   ['tb-charge','tbCharge'],['ov-sub','ovSub'],['ov-new','ovNew'],['ov-recent','ovRecent'],
   ['sc-total-l','scTot'],['sc-pend-l','scPend'],['sc-done-l','scDone'],['sc-bal-l','scBal'],
   ['req-ttl','reqTtl'],['req-sub','reqSub'],['req-new','reqNew'],
   ['rf-all','rfAll'],['rf-pend','rfPend'],['rf-proc','rfProc'],['rf-done','rfDone'],['rf-rej','rfRej'],
   ['prof-ttl','profTtl'],['prof-sub','profSub'],['pf-ttl','pfTtl'],
   ['pf-ln','pfLn'],['pf-le','pfLe'],['pf-lp','pfLp'],['save-lbl','saveLbl'],
   ['pf-pass-ttl','pfPassTtl'],['pf-lcp','pfLcp'],['pf-lnp','pfLnp'],['pf-lcp2','pfLcp2'],['pass-lbl','passLbl'],
   ['bal-lbl','balLbl'],['bal-sub','balSub'],['charge-lbl','chargeLbl'],
   ['pay-ttl','payTtl'],['pay-sub','paySub'],['pay-charge','payCharge'],['pay-bal-l','payBalL'],
   ['cm-ttl','cmTtl'],['cm-sub','cmSub'],['cm-btn-lbl','cmBtnLbl'],
  ].forEach(([id,k])=>S(id,t[k]));
}

/* ══ LOGOUT ══ */
function doLogout(){
  if(typeof Auth!=='undefined')Auth.logout();
  else{localStorage.removeItem('amrtm_token');localStorage.removeItem('amrtm_user');window.location.href=(AMRTM_ROUTES&&AMRTM_ROUTES.home)||'/';}
}

/* ══ HELPERS ══ */
function S(id,v){const el=document.getElementById(id);if(el)el.textContent=v;}
function gV(id){return document.getElementById(id)?.value?.trim()||'';}
function setVal(id,v){const el=document.getElementById(id);if(el)el.value=v;}
function setImgSrc(id,src){const el=document.getElementById(id);if(el)el.src=src;}
function fmtDate(d){if(!d)return'—';return new Date(d).toLocaleDateString(lang==='ar'?'ar-SA':'en-US',{year:'numeric',month:'short',day:'numeric'});}
function stInfo(status){
  const m={pending:{label:T[lang].stPend,color:'var(--orange)',bg:'rgba(230,81,0,.1)'},processing:{label:T[lang].stProc,color:'var(--blue)',bg:'rgba(2,119,189,.1)'},in_progress:{label:T[lang].stInprog,color:'var(--yellow)',bg:'rgba(249,168,37,.1)'},done:{label:T[lang].stDone,color:'var(--green)',bg:'rgba(4,120,87,.1)'},rejected:{label:T[lang].stRej,color:'var(--red)',bg:'rgba(220,38,38,.1)'}};
  return m[status]||{label:status,color:'#999',bg:'rgba(0,0,0,.05)'};
}

/* ══ PAYMENT HISTORY (full paginated load) ══ */
let _payPage=1;
let _payLastPage=1;

async function loadPayHistory(page){
  page=page||1;_payPage=page;
  const el=document.getElementById('pay-list');
  if(el)el.innerHTML=`<div style="text-align:center;padding:2rem;color:var(--t3);"><i class="ti ti-loader-2" style="font-size:28px;animation:spin .7s linear infinite;display:inline-block;"></i></div>`;
  try{
    const res=await Payments.history(page);
    const items=res.data||[];
    _payLastPage=res.last_page||1;
    renderPayListFull(items);
    renderPayPagination();
  }catch(e){
    if(el)el.innerHTML=`<div style="text-align:center;padding:2rem;color:var(--red);">حدث خطأ في تحميل المدفوعات</div>`;
  }
}

function renderPayListFull(pays){
  const t=T[lang];
  const el=document.getElementById('pay-list');if(!el)return;
  if(!pays||!pays.length){
    el.innerHTML=`<div style="text-align:center;padding:2rem;color:var(--t3);">${t.noPayments}</div>`;return;
  }
  el.innerHTML=pays.map(p=>{
    const isCharge=p.type==='charge';const isRefund=p.type==='refund';
    const ico=isCharge?'ti-arrow-down-circle':isRefund?'ti-arrow-up-circle':'ti-arrow-up-circle';
    const icoBg=isCharge?'rgba(4,120,87,.1)':'rgba(220,38,38,.1)';
    const icoColor=isCharge?'var(--green)':'var(--red)';
    const amtClass=isCharge?'credit':'debit';
    const sign=isCharge||isRefund?'+':'-';
    const desc=lang==='ar'?(p.description_ar||p.type):(p.description_en||p.type);
    return `<div class="pay-row">
      <div class="pay-ico" style="background:${icoBg};"><i class="ti ${ico}" style="color:${icoColor};"></i></div>
      <div style="flex:1;min-width:0;"><div class="pay-nm">${desc}</div><div class="pay-date">${fmtDate(p.created_at)}</div></div>
      <div class="pay-amt ${amtClass}">${sign}${parseFloat(p.amount||0).toFixed(2)} ${t.sar}</div>
    </div>`;
  }).join('');
}

function renderPayPagination(){
  let pc=document.getElementById('pay-pagination');
  if(!pc){
    pc=document.createElement('div');
    pc.id='pay-pagination';
    pc.style.cssText='display:flex;justify-content:center;align-items:center;gap:.6rem;margin-top:1.2rem;';
    document.getElementById('pay-list')?.after(pc);
  }
  if(_payLastPage<=1){pc.innerHTML='';return;}
  pc.innerHTML=AMRTM_UI.button({onclick:"loadPayHistory("+(_payPage-1)+")", disabled:_payPage<=1||null, class:'filter-btn', style:_payPage<=1?'opacity:.4;cursor:default;':''}, '<i class="ti ti-chevron-'+(lang==='ar'?'right':'left')+'"></i>')+
    '<span style="font-size:13px;color:var(--t2);font-weight:600;">'+_payPage+' / '+_payLastPage+'</span>'+
    AMRTM_UI.button({onclick:"loadPayHistory("+(_payPage+1)+")", disabled:_payPage>=_payLastPage||null, class:'filter-btn', style:_payPage>=_payLastPage?'opacity:.4;cursor:default;':''}, '<i class="ti ti-chevron-'+(lang==='ar'?'left':'right')+'"></i>');
}

/* RUN */
init();

// Show payment result flash message
if(window._paymentMsg){
  setTimeout(()=>{
    if(typeof showToast!=='undefined' && typeof AmrtmNotify==='undefined')showToast(window._paymentMsg.text, window._paymentMsg.type);
    window._paymentMsg=null;
  }, 800);
}

/* ══ NOTIFICATIONS → DashNotif (layout topbar) ══ */
(window.__dashNotifProviders = window.__dashNotifProviders || []).push({
  getAll: (all) => window.Notifications.getAll(all),
  unreadCount: () => window.Notifications.unreadCount(),
  markRead: (id) => window.Notifications.markRead(id),
  markAllRead: () => window.Notifications.markAllRead(),
  allUrl: '/dashboard#requests'
});

/* ══ OPEN LATEST REQUEST TRACK (زر المحادثات) ══ */
async function openLatestTrack(){
  try{
    const d=await API.get('/requests?page=1');
    const list=d.data||[];
    if(list.length){
      window.location.href=(window.TRACK_BASE||'/requests')+'/'+list[0].id+'/track';
      return;
    }
    showPage('requests');
  }catch(e){
    showPage('requests');
  }
}

/* ══ OPEN REQUEST FROM NOTIFICATION CLICK ══ */
window.addEventListener('amrtm:notif-open', function (e) {
  var n = e.detail;
  if (!n) return;
  var reqId = n.request_id || (n.data && n.data.request_id);
  if (reqId) {
    window.location.href = (window.TRACK_BASE || '/requests') + '/' + reqId + '/track';
    return;
  }
  showPage('requests');
});
</script>
@include('partials.public.hash-deep-link', ['hashPages' => ['overview', 'requests', 'profile', 'payments']])

@endsection