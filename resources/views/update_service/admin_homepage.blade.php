@extends('layouts.dashboard')

@section('title', 'إدارة الواجهة والمحتوى | آمر تم')

@section('dashboard-content')
@php
    $user = auth('business')->user();
    $persona = [
        'key' => $user->role ?? 'admin',
        'label' => ($user->role ?? '') === 'supervisor' ? 'مشرف' : 'مدير النظام',
        'types' => [\App\Support\DashboardRegistry::TYPE_ADMIN],
        'name' => $user->name ?? '',
    ];
    $pageTitle = 'إدارة الواجهة والمحتوى';
    $dashboardMenu = \App\Support\DashboardRegistry::menuFor([\App\Support\DashboardRegistry::TYPE_ADMIN], 'admin');
@endphp

<style>
:root{--pri:#059669;--pri2:#047857;--pri3:#16a34a;--bg:#f8fafc;--sur:#fff;--sur2:#f8fafc;--b1:rgba(5,150,105,.1);--b2:rgba(5,150,105,.2);--bc:rgba(5,150,105,.07);--t1:#0f172a;--t2:#334155;--t3:#64748b;--t4:#94a3b8;--pd:rgba(5,150,105,.08);--pd2:rgba(5,150,105,.14);--sh:rgba(5,150,105,.07);--sh2:rgba(5,150,105,.15);--red:#dc2626;--green:#047857;}

.ahp{padding:.2rem 0;color:var(--t1);}
.btn-pri{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:9px;background:var(--pri);color:#fff;font-family:'Cairo',sans-serif;font-size:13.5px;font-weight:700;cursor:pointer;border:none;box-shadow:0 3px 10px var(--sh2);transition:all .2s;}
.btn-pri:hover{background:var(--pri2);transform:translateY(-1px);}
.btn-ghost{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:9px;background:var(--sur);color:var(--t2);font-family:'Cairo',sans-serif;font-size:13px;font-weight:700;cursor:pointer;border:1.5px solid var(--b1);transition:all .2s;}
.btn-ghost:hover{background:var(--pd);color:var(--pri);}
.btn-pri:disabled,.btn-pri[disabled]{opacity:.65;cursor:not-allowed;transform:none!important;box-shadow:none;pointer-events:none;}
@keyframes ahpspin{to{transform:rotate(360deg);}}
.ahpspin{display:inline-block;animation:ahpspin .8s linear infinite;}

.ahp .pg-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.4rem;flex-wrap:wrap;gap:.8rem;}
.ahp .pg-ttl{font-size:19px;font-weight:800;color:var(--t1);}
.ahp .pg-sub{font-size:12.5px;color:var(--t3);margin-top:3px;}

.ahp .card{background:var(--sur);border:1px solid var(--b1);border-radius:16px;padding:1.5rem;margin-bottom:1.6rem;box-shadow:0 3px 12px var(--sh);}
.ahp .card-hd{display:flex;align-items:center;gap:10px;margin-bottom:1.3rem;}
.ahp .card-hd i{font-size:20px;color:var(--pri);}
.ahp .card-ttl{font-size:15.5px;font-weight:800;color:var(--t1);}
.ahp .card-sub{font-size:12px;color:var(--t3);margin-top:2px;}
.ahp .grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.ahp .grid-1{grid-template-columns:1fr;}
.ahp .fld{display:flex;flex-direction:column;gap:6px;}
.ahp .fld label{font-size:13px;font-weight:700;color:var(--t2);}
.ahp .fld small{font-size:10.5px;color:var(--t4);}
.ahp .fld input,.ahp .fld select,.ahp .fld textarea{height:42px;padding:0 13px;border-radius:9px;border:1.5px solid var(--b1);background:var(--sur2);color:var(--t1);font-family:'Cairo',sans-serif;font-size:13.5px;outline:none;transition:border-color .2s;}
.ahp .fld textarea{height:auto;min-height:90px;padding:10px 13px;resize:vertical;}
.ahp .fld input:focus,.ahp .fld select:focus,.ahp .fld textarea:focus{border-color:var(--pri);background:var(--sur);}
.ahp .fld input[type=file]{padding:8px;height:auto;background:var(--sur);}
.ahp .fld-row{display:flex;gap:.8rem;flex-wrap:wrap;align-items:flex-end;}

.ahp .media-prev{display:flex;align-items:center;gap:1rem;margin-top:1rem;flex-wrap:wrap;}
.ahp .media-box{width:210px;border-radius:12px;overflow:hidden;border:1.5px solid var(--b1);background:var(--sur2);}
.ahp .media-box img{width:100%;height:130px;object-fit:cover;display:block;}
.ahp .media-box.video video{width:100%;height:130px;object-fit:cover;display:block;}
.ahp .media-cap{font-size:12px;font-weight:700;color:var(--t2);text-align:center;padding:8px;}
.ahp .media-name{font-size:11px;color:var(--t3);text-align:center;padding:0 8px 8px;word-break:break-all;}

.ahp .slides-list{display:flex;flex-direction:column;gap:.8rem;}
.ahp .slide-row{display:flex;align-items:center;gap:1rem;padding:.9rem;border-radius:12px;border:1.5px solid var(--b1);background:var(--sur2);}
.ahp .slide-thumb{width:120px;height:70px;border-radius:9px;object-fit:cover;border:1px solid var(--b1);flex-shrink:0;background:var(--sur);}
.ahp .slide-info{flex:1;min-width:0;}
.ahp .slide-ttl{font-size:13.5px;font-weight:700;color:var(--t1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ahp .slide-meta{font-size:11px;color:var(--t3);margin-top:3px;}
.ahp .slide-actions{display:flex;gap:6px;flex-shrink:0;}
.ahp .ic-btn{width:34px;height:34px;border-radius:8px;border:1.5px solid var(--b1);background:var(--sur);color:var(--t2);font-size:16px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .2s;}
.ahp .ic-btn:hover{background:var(--pd);color:var(--pri);border-color:var(--pri);}
.ahp .ic-btn.up:disabled,.ahp .ic-btn.down:disabled{opacity:.35;cursor:not-allowed;}
.ahp .ic-btn.del{color:var(--red);}
.ahp .ic-btn.del:hover{background:rgba(220,38,38,.09);border-color:var(--red);color:var(--red);}
.ahp .ic-btn.active{background:var(--green);border-color:var(--green);color:#fff;}
.ahp .ic-btn.inactive{color:var(--t3);}
.ahp .status-pill{font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;display:inline-block;}
.ahp .status-pill.on{background:rgba(4,120,87,.12);color:#047857;}
.ahp .status-pill.off{background:rgba(100,116,139,.14);color:var(--t3);}

.ahp .modal{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:1000;padding:1rem;}
.ahp .modal.show{display:flex;}
.ahp .modal-box{background:var(--sur);border-radius:18px;width:100%;max-width:560px;max-height:92vh;overflow-y:auto;padding:1.6rem;box-shadow:0 20px 60px rgba(0,0,0,.25);}
.ahp .modal-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;}
.ahp .modal-ttl{font-size:16px;font-weight:800;color:var(--t1);}
.ahp .modal-x{width:32px;height:32px;border-radius:8px;border:none;background:var(--pd);color:var(--t2);font-size:16px;cursor:pointer;}
.ahp .modal-x:hover{background:var(--pd2);}
.ahp .modal-ft{display:flex;gap:.7rem;justify-content:flex-end;align-items:center;margin-top:1.4rem;}

.ahp .empty{text-align:center;padding:3rem 1rem;color:var(--t3);}
.ahp .empty i{font-size:48px;display:block;margin-bottom:.8rem;opacity:.35;}
.ahp .empty-ttl{font-size:14.5px;font-weight:700;color:var(--t2);margin-bottom:.3rem;}
.ahp .empty-sub{font-size:12.5px;}

@media (max-width:820px){ .ahp .grid{grid-template-columns:1fr;} }
</style>

<div class="ahp">
  <div class="pg-hd">
    <div>
      <div class="pg-ttl">إدارة الواجهة ومحتواها</div>
      <div class="pg-sub">تحكم في نصوص الواجهة الرئيسية، الفيديو، وشرائح السلايدر</div>
    </div>
    <x-ui.button class="btn-pri" id="ahp-save-btn" onclick="saveSettings()"><i class="ti ti-device-floppy"></i> حفظ الإعدادات</x-ui.button>
  </div>

  <!-- ── Section: أساسي ── -->
  <div class="card">
    <div class="card-hd"><i class="ti ti-letter-case"></i><div><div class="card-ttl">المحتوى الأساسي</div><div class="card-sub">العنوان والوصف ونصوص الأقسام الرئيسية</div></div></div>
    <div class="grid">
      <div class="fld"><x-ui.label>عنوان المنصة</x-ui.label><x-ui.input id="set_site_title" placeholder="مثال: منصة آمر تم لخدمات قطاع الأعمال" /></div>
      <div class="fld"><x-ui.label>السطر الترحيبي (نجمة/تاغلاين)</x-ui.label><x-ui.input id="set_site_tagline" placeholder="مثال: أختر الخدمة المطلوبة من خلال الجهات التالية" /></div>
      <div class="fld" style="grid-column:1/-1"><x-ui.label>وصف المنصة</x-ui.label><x-ui.textarea id="set_site_subtitle" placeholder="اكتب وصفاً تعريفياً مختصراً للمنصة..."></x-ui.textarea></div>
      <div class="fld"><x-ui.label>تسمية المكتب الرئيسي</x-ui.label><x-ui.input id="set_main_office_label" placeholder="مثال: المكتب الرئيسي" /></div>
    </div>
  </div>

  <!-- ── Section: تواصل ── -->
  <div class="card">
    <div class="card-hd"><i class="ti ti-phone"></i><div><div class="card-ttl">بيانات التواصل</div><div class="card-sub">رقم الاتصال والواتساب والعنوان الظاهر في الواجهة</div></div></div>
    <div class="grid">
      <div class="fld"><x-ui.label>رقم الاتصال</x-ui.label><x-ui.input id="set_contact_phone" dir="ltr" style="text-align:left" placeholder="966920002164" /><small>بالصيغة الدولية بدون +</small></div>
      <div class="fld"><x-ui.label>رقم الواتساب</x-ui.label><x-ui.input id="set_contact_whatsapp" dir="ltr" style="text-align:left" placeholder="966504915222" /><small>بالصيغة الدولية بدون + (يُستخدم في رابط wa.me)</small></div>
      <div class="fld" style="grid-column:1/-1"><x-ui.label>العنوان / الموقع</x-ui.label><x-ui.input id="set_contact_address" placeholder="مثال: الرياض، المملكة العربية السعودية" /></div>
    </div>
  </div>

  <!-- ── Section: الفيديو ── -->
  <div class="card">
    <div class="card-hd"><i class="ti ti-video"></i><div><div class="card-ttl">الفيديو التعريفي</div><div class="card-sub">ملف الفيديو وصورة الغلاف الظاهرين عند ضغط زر التشغيل</div></div></div>
    <div class="grid">
      <div class="fld"><x-ui.label>ملف الفيديو (MP4)</x-ui.label><x-ui.input type="file" id="set_video_file" accept="video/mp4,video/quicktime,video/webm" /><small>اتركه فارغاً للإبقاء على الفيديو الحالي</small></div>
      <div class="fld"><x-ui.label>صورة الغلاف (Poster)</x-ui.label><x-ui.input type="file" id="set_video_poster" accept="image/png,image/jpeg,image/webp" /><small>اتركه فارغاً للإبقاء على الغلاف الحالي</small></div>
    </div>
    <div class="media-prev" id="videoPreview"></div>
  </div>

  <!-- ── Section: السلايدات ── -->
  <div class="card">
    <div class="card-hd">
      <i class="ti ti-photo"></i>
      <div><div class="card-ttl">شرائح السلايدر الرئيسية</div><div class="card-sub">اضف، رتّب، عدّل أو أزل صور الخلفية العلوية — ظاهر مباشرة في الواجهة</div></div>
      <div style="margin-right:auto"><x-ui.button class="btn-pri" onclick="openSlideModal()"><i class="ti ti-plus"></i> إضافة سلايد</x-ui.button></div>
    </div>
    <div class="slides-list" id="slidesList"></div>
  </div>
</div>

<!-- Slide Modal -->
<div class="ahp">
  <div class="modal" id="slideModal">
    <div class="modal-box">
      <div class="modal-hd">
        <div class="modal-ttl" id="slideModalTitle">إضافة سلايد</div>
        <x-ui.button class="modal-x" onclick="closeSlideModal()"><i class="ti ti-x"></i></x-ui.button>
      </div>
      <div style="display:flex;flex-direction:column;gap:1rem;">
        <div class="fld"><x-ui.label>عنوان السلايد (اختياري)</x-ui.label><x-ui.input id="sl_title" placeholder="مثال: قطاع الأعمال" /></div>
        <div class="fld"><x-ui.label>رابط (اختياري)</x-ui.label><x-ui.input id="sl_link" placeholder="https://..." /></div>
        <div class="fld"><x-ui.label>صورة الخلفية</x-ui.label><x-ui.input type="file" id="sl_image" accept="image/png,image/jpeg,image/webp" /><small>يفضل بحجم واسع عريض (16:9)</small></div>
        <div class="fld">
          <x-ui.label>الحالة</x-ui.label>
          <div style="display:flex;gap:8px;">
            <x-ui.button type="button" class="btn-ghost" id="sl_active_btn" onclick="toggleSlideStatusModal()"><span id="sl_active_label">مفعل</span></x-ui.button>
          </div>
        </div>
      </div>
      <div class="modal-ft">
        <x-ui.button class="btn-ghost" onclick="closeSlideModal()">إلغاء</x-ui.button>
        <x-ui.button class="btn-pri" id="slideSaveBtn" onclick="saveSlideForm()"><i class="ti ti-check"></i> حفظ</x-ui.button>
      </div>
    </div>
  </div>

  </div>

<script>
const API_SETTINGS     = '{{ route('amrtm.admin.api.homepage.settings') }}';
const API_SETTINGS_SAVE= '{{ route('amrtm.admin.api.homepage.settings.save') }}';
const API_SLIDES       = '{{ route('amrtm.admin.api.homepage.slides') }}';
const API_SLIDES_STORE = '{{ route('amrtm.admin.api.homepage.slides.store') }}';
const API_SLIDES_REORDER = '{{ route('amrtm.admin.api.homepage.slides.reorder') }}';
const CSRF             = document.querySelector('meta[name="csrf-token"]').content;

let slides = [];
let editingSlideId = null;
const EMPTY_VIDEO = 'videos/0829.mp4';
const EMPTY_POSTER = 'images/logo2.jpg';

/* ══════════ LOAD ══════════ */
async function loadAll() {
  await loadSettings();
  await loadSlides();
}

async function loadSettings() {
  try {
    const res     = await fetch(API_SETTINGS);
    const data    = await res.json();
    const s       = data.settings || {};
    setVal('set_site_title',           s.site_title);
    setVal('set_site_tagline',         s.site_tagline);
    setVal('set_site_subtitle',        s.site_subtitle);
    setVal('set_main_office_label',    s.main_office_label);
    setVal('set_contact_phone',        s.contact_phone);
    setVal('set_contact_whatsapp',     s.contact_whatsapp);
    setVal('set_contact_address',      s.contact_address);
    renderVideoPreview(data.defaults || {});
    document.getElementById('set_video_poster').dataset.videoPoster = s.video_poster || EMPTY_POSTER;
    document.getElementById('set_video_file').dataset.videoFile    = s.video_file   || EMPTY_VIDEO;
  } catch {
    toast('تعذر تحميل الإعدادات', 'err');
  }
}

function setVal(id, v) { document.getElementById(id).value = v || ''; }

function renderVideoPreview(defaults) {
  const poster = document.getElementById('set_video_poster').dataset.videoPoster || defaults.video_poster || EMPTY_POSTER;
  const file   = document.getElementById('set_video_file').dataset.videoFile   || defaults.video_file   || EMPTY_VIDEO;
  const box = document.getElementById('videoPreview');
  box.innerHTML = `
    <div class="media-box">
      <video id="vp-vid" src="${asset(file)}" poster="${asset(poster)}" muted controls style="width:100%;height:130px;object-fit:cover;"></video>
      <div class="media-cap">معاينة الفيديو</div>
      <div class="media-name">${file}</div>
    </div>
    <div class="media-box">
      <img src="${asset(poster)}" alt="غلاف"/>
      <div class="media-cap">صورة الغلاف</div>
      <div class="media-name">${poster}</div>
    </div>`;
}

/* ══════════ SAVE SETTINGS ══════════ */
function collectSettingsForm() {
  const fd = new FormData();
  const map = {
    site_title:           'set_site_title',
    site_tagline:         'set_site_tagline',
    site_subtitle:        'set_site_subtitle',
    main_office_label:    'set_main_office_label',
    contact_phone:        'set_contact_phone',
    contact_whatsapp:     'set_contact_whatsapp',
    contact_address:      'set_contact_address',
  };
  for (const k in map) fd.append(k, document.getElementById(map[k]).value);
  const vf = document.getElementById('set_video_file');
  const pf = document.getElementById('set_video_poster');
  if (vf.files.length) fd.append('video_file', vf.files[0]);
  if (pf.files.length) fd.append('video_poster', pf.files[0]);
  fd.append('_token', CSRF);
  return fd;
}

async function saveSettings() {
  const btn = document.getElementById('ahp-save-btn');
  btn.disabled = true;
  try {
    const res  = await fetch(API_SETTINGS_SAVE, {
      method: 'POST', body: collectSettingsForm(),
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
    });
    const data = await res.json();
    if (res.ok) { toast(data.message || 'تم الحفظ', 'ok'); await loadSettings(); }
    else        { toast(data.message || 'تعذر الحفظ', 'err'); }
  } catch {
    toast('فشل الاتصال بالسيرفر', 'err');
  } finally {
    btn.disabled = false;
  }
}

/* ══════════ SLIDES ══════════ */
async function loadSlides() {
  try {
    const res  = await fetch(API_SLIDES);
    const data = await res.json();
    slides = data.slides || [];
    renderSlides();
  } catch {
    toast('تعذر تحميل السلايدات', 'err');
  }
}

function renderSlides() {
  const el = document.getElementById('slidesList');
  if (!slides.length) {
    el.innerHTML = `<div class="empty">
      <i class="ti ti-photo-off"></i>
      <div class="empty-ttl">لا توجد شرائح بعد</div>
      <div class="empty-sub">أضف أول سلايد ليظهر في أعلى الواجهة</div>
    </div>`;
    return;
  }
  el.innerHTML = slides.map((s, i) => `
    <div class="slide-row" data-id="${s.id}">
      <img class="slide-thumb" src="${s.image_url || asset('images/slide-riyadh-business.jpg')}" alt="${s.title || 'سلايد'}" onerror="this.src='${asset('images/slide-riyadh-business.jpg')}'"/>
      <div class="slide-info">
        <div class="slide-ttl">${escapeHtml(s.title || 'سلايد بدون عنوان')}</div>
        <div class="slide-meta">${s.link_url ? 'الرابط: ' + escapeHtml(s.link_url) : ''} — <span class="status-pill ${s.is_active ? 'on' : 'off'}">${s.is_active ? 'مفعل' : 'غير مفعل'}</span></div>
      </div>
      <div class="slide-actions">
        ${AMRTM_UI.button({class:'ic-btn up', title:'تحريك لأعلى', onclick:'moveSlide(' + i + ', -1)', disabled: i === 0}, '<i class=\"ti ti-chevron-up\"></i>')}
        ${AMRTM_UI.button({class:'ic-btn down', title:'تحريك لأسفل', onclick:'moveSlide(' + i + ', 1)', disabled: i === slides.length - 1}, '<i class=\"ti ti-chevron-down\"></i>')}
        ${AMRTM_UI.button({class:'ic-btn ' + (s.is_active ? 'active' : 'inactive'), title:s.is_active ? 'إلغاء التفعيل' : 'تفعيل', onclick:'toggleSlide(' + s.id + ', this)'}, '<i class=\"ti ' + (s.is_active ? 'ti-eye' : 'ti-eye-off') + '\"></i>')}
        ${AMRTM_UI.button({class:'ic-btn', title:'تعديل', onclick:'openSlideModal(' + s.id + ')'}, '<i class=\"ti ti-edit\"></i>')}
        ${AMRTM_UI.button({class:'ic-btn del', title:'حذف', onclick:'deleteSlide(' + s.id + ')'}, '<i class=\"ti ti-trash\"></i>')}
      </div>
    </div>
  `).join('');
}

function moveSlide(i, dir) {
  const j = i + dir;
  if (j < 0 || j >= slides.length) return;
  [slides[i], slides[j]] = [slides[j], slides[i]];
  renderSlides();
  persistOrder();
}

async function persistOrder() {
  try {
    await fetch(API_SLIDES_REORDER, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ order: slides.map(s => s.id), _token: CSRF }),
    });
  } catch {
    toast('فشل حفظ الترتيب', 'err');
  }
}

async function toggleSlide(id, btn) {
  try {
    const res  = await fetch(API_SLIDES + '/' + id + '/toggle', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    const data = await res.json();
    if (res.ok) { toast(data.message, 'ok'); await loadSlides(); }
    else        { toast(data.message || 'تعذر التغيير', 'err'); }
  } catch {
    toast('فشل الاتصال', 'err');
  }
}

async function deleteSlide(id) {
  if (!confirm('هل تريد حذف هذا السلايد؟')) return;
  try {
    const res  = await fetch(API_SLIDES + '/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } });
    const data = await res.json();
    if (res.ok) { toast(data.message, 'ok'); await loadSlides(); }
    else        { toast(data.message || 'تعذر الحذف', 'err'); }
  } catch {
    toast('فشل الاتصال', 'err');
  }
}

/* ══════════ SLIDE MODAL ══════════ */
let slideModalActive = true;

function openSlideModal(id) {
  editingSlideId = id || null;
  slideModalActive = true;
  document.getElementById('sl_title').value = '';
  document.getElementById('sl_link').value = '';
  document.getElementById('sl_image').value = '';
  updateSlideModalStatus();
  if (id) {
    const s = slides.find(x => x.id === id);
    if (s) {
      document.getElementById('sl_title').value = s.title || '';
      document.getElementById('sl_link').value = s.link_url || '';
      slideModalActive = !!s.is_active;
      updateSlideModalStatus();
    }
    document.getElementById('slideModalTitle').textContent = 'تعديل السلايد';
  } else {
    document.getElementById('slideModalTitle').textContent = 'إضافة سلايد جديد';
  }
  document.getElementById('slideModal').classList.add('show');
}

function closeSlideModal() {
  document.getElementById('slideModal').classList.remove('show');
  editingSlideId = null;
}

function toggleSlideStatusModal() {
  slideModalActive = !slideModalActive;
  updateSlideModalStatus();
}

function updateSlideModalStatus() {
  const btn = document.getElementById('sl_active_btn');
  const lbl = document.getElementById('sl_active_label');
  lbl.textContent = slideModalActive ? 'مفعل' : 'غير مفعل';
  btn.style.borderColor = slideModalActive ? 'rgba(4,120,87,.5)' : 'rgba(100,116,139,.5)';
  btn.style.color = slideModalActive ? '#047857' : 'var(--t3)';
}

/* ضغط الصور الكبيرة في المتصفح قبل الرفع — يمنع فشل الحفظ للصور الأكبر من حدود الرفع */
const SLIDE_MAX_WIDTH   = 1920;              // أقصى عرض للسلايد (كافٍ لشاشات العرض)
const SLIDE_QUALITY     = 0.85;              // جودة ضغط JPEG المبدئية
const SLIDE_COMPRESS_OVER = 1024 * 1024;     // لا نلمس الصور الأصغر من 1MB
const SLIDE_TARGET_SIZE = 2 * 1024 * 1024;   // الهدف: ألا يتجاوز الملف المرسل 2MB

function compressImageFile(file) {
  return new Promise((resolve) => {
    if (!file || !file.type.startsWith('image/') || file.type === 'image/gif'
        || file.size <= SLIDE_COMPRESS_OVER) { resolve(file); return; }
    const img = new Image();
    const url = URL.createObjectURL(file);
    img.onload = () => {
      try {
        // نخفض الجودة تدريجياً ثم نصفّر الأبعاد حتى ينزل الحجم تحت الهدف
        const qualities = [SLIDE_QUALITY, 0.7, 0.55, 0.4, 0.3];
        const widths    = [SLIDE_MAX_WIDTH, SLIDE_MAX_WIDTH, 1600, 1280, 1024];
        let done = false;

        const attempt = (idx) => {
          if (done) return;
          const scale = Math.min(1, widths[idx] / img.naturalWidth);
          const w = Math.max(1, Math.round(img.naturalWidth  * scale));
          const h = Math.max(1, Math.round(img.naturalHeight * scale));
          const canvas = document.createElement('canvas');
          canvas.width = w; canvas.height = h;
          canvas.getContext('2d').drawImage(img, 0, 0, w, h);
          canvas.toBlob((blob) => {
            if (blob && blob.size > 0 && (blob.size <= SLIDE_TARGET_SIZE || idx === qualities.length - 1)) {
              URL.revokeObjectURL(url);
              if (blob.size < file.size) {
                const name = (file.name.replace(/\.[^.]+$/, '') || 'slide') + '-optimized.jpg';
                resolve(new File([blob], name, { type: 'image/jpeg' }));
              } else { resolve(file); } // الضغط لم يفد — نرسل الأصلية
              done = true;
            } else {
              attempt(idx + 1); // ما زال كبيراً — جرّب جودة/عرض أقل
            }
          }, 'image/jpeg', qualities[idx]);
        };
        attempt(0);
      } catch { URL.revokeObjectURL(url); resolve(file); }
    };
    img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
    img.src = url;
  });
}

let savingSlide = false; // قفل الحفظ لمنع الإرسال المزدوج

async function saveSlideForm() {
  if (savingSlide) return;
  const title = document.getElementById('sl_title').value.trim();
  const link  = document.getElementById('sl_link').value.trim();
  const image = document.getElementById('sl_image').files[0];

  const fd = new FormData();
  fd.append('title', title);
  fd.append('link_url', link);
  fd.append('is_active', slideModalActive ? '1' : '0');
  fd.append('_token', CSRF);

  let url, method;
  if (editingSlideId) {
    url = API_SLIDES + '/' + editingSlideId;
    method = 'PUT';
  } else {
    if (!image) { toast('يرجى اختيار صورة للسلايد', 'err'); return; }
    url = API_SLIDES;
    method = 'POST';
  }

  const btn = document.getElementById('slideSaveBtn');
  const btnHtml = btn.innerHTML;
  savingSlide = true;
  btn.disabled = true;
  btn.innerHTML = '<i class="ti ti-loader-2 ahpspin"></i> جارٍ الحفظ...';

  try {
    let upload = image;
    if (upload) upload = await compressImageFile(upload); // يعرض فقط للصور الكبيرة
    if (upload) fd.append('image', upload);

    const res = await fetch(url, {
      method, body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
    });
    let data = {};
    try { data = await res.json(); } catch { data = {}; }
    if (res.ok) { toast(data.message || 'تم الحفظ', 'ok'); closeSlideModal(); await loadSlides(); }
    else        { toast(data.message || ('تعذر الحفظ (رمز ' + res.status + ')'), 'err'); }
  } catch {
    toast('فشل الاتصال', 'err');
  } finally {
    savingSlide = false;
    btn.disabled = false;
    btn.innerHTML = btnHtml;
  }
}

/* ══════════ HELPERS ══════════ */
function asset(p) {
  if (!p) return '';
  if (/^https?:\/\//.test(p)) return p;
  return window.__BASE__ + '/' + p;
}
window.__BASE__ = '{{ asset('') }}'.replace(/\/$/, '');

function escapeHtml(s) {
  return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function toast(msg, type='ok') {
  if (window.AmrtmNotify) {
    const map = { ok:'success', err:'error', warn:'warning', info:'info' };
    window.AmrtmNotify.basic(msg, map[type] || 'success');
    return;
  }
  if (typeof window.showToast === 'function') window.showToast(msg, type === 'err' ? 'error' : 'success');
}

/* ══════════ INIT ══════════ */
document.getElementById('slideModal').addEventListener('click', e => {
  if (e.target.id === 'slideModal') closeSlideModal();
});
loadAll();
</script>
@endsection