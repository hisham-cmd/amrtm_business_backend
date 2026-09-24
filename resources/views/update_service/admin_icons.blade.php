@extends('layouts.dashboard')

@section('title', 'مكتبة الأيقونات | آمر تم')

@section('dashboard-content')
@php
    $user = auth('business')->user();
    $persona = [
        'key' => $user->role ?? 'admin',
        'label' => ($user->role ?? '') === 'supervisor' ? 'مشرف' : 'مدير النظام',
        'types' => [\App\Support\DashboardRegistry::TYPE_ADMIN],
        'name' => $user->name ?? '',
    ];
    $pageTitle = 'مكتبة الأيقونات';
    $dashboardMenu = \App\Support\DashboardRegistry::menuFor([\App\Support\DashboardRegistry::TYPE_ADMIN], 'admin');
@endphp

<style>
:root{--pri:#059669;--pri2:#047857;--bg:#f8fafc;--sur:#fff;--sur2:#f8fafc;--b1:rgba(5,150,105,.18);--bc:rgba(5,150,105,.08);--t1:#0f172a;--t2:#334155;--t3:#64748b;--t4:#94a3b8;--pd:rgba(5,150,105,.08);--sh:rgba(5,150,105,.07);--sh2:rgba(5,150,105,.15);--red:#dc2626;--green:#047857;}

.admi{padding:.2rem 0;color:var(--t1);}
.admi .pg-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.4rem;flex-wrap:wrap;gap:.8rem;}
.admi .pg-ttl{font-size:19px;font-weight:800;color:var(--t1);}
.admi .pg-sub{font-size:12.5px;color:var(--t3);margin-top:3px;}

.admi .upload-zone{background:var(--sur);border:2px dashed var(--b1);border-radius:16px;padding:2.2rem;text-align:center;cursor:pointer;transition:all .25s;margin-bottom:1.5rem;}
.admi .upload-zone:hover,.admi .upload-zone.drag{border-color:var(--pri);background:rgba(5,150,105,.04);}
.admi .upload-zone i{font-size:42px;color:var(--t4);display:block;margin-bottom:.7rem;}
.admi .upload-zone.drag i{color:var(--pri);}
.admi .uz-ttl{font-size:15px;font-weight:700;color:var(--t2);}
.admi .uz-sub{font-size:12px;color:var(--t3);margin-top:.3rem;}
.admi .uz-badge{display:inline-block;margin-top:.7rem;padding:4px 10px;border-radius:20px;background:var(--pd);color:var(--pri);font-size:11px;font-weight:700;}
.admi #fileInput{display:none;}

.admi .progress-bar{height:5px;border-radius:3px;background:var(--b1);overflow:hidden;margin-bottom:1rem;display:none;}
.admi .progress-fill{height:100%;background:var(--pri);width:0;transition:width .3s;border-radius:3px;}

.admi .toolbar{display:flex;align-items:center;gap:.8rem;margin-bottom:1.2rem;flex-wrap:wrap;}
.admi .search-box{flex:1;min-width:220px;position:relative;}
.admi .search-box input{width:100%;height:40px;padding:0 40px 0 14px;border-radius:10px;border:1.5px solid var(--b1);background:var(--sur);color:var(--t1);font-family:'Cairo',sans-serif;font-size:13.5px;outline:none;transition:border-color .2s;}
.admi .search-box input:focus{border-color:var(--pri);}
.admi .search-box i{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--t3);font-size:16px;pointer-events:none;}
.admi .count-badge{padding:7px 14px;border-radius:9px;background:var(--sur);border:1px solid var(--b1);font-size:12.5px;font-weight:700;color:var(--t2);white-space:nowrap;}

.admi .icon-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:.8rem;}
.admi .ic{background:var(--sur);border-radius:12px;border:1.5px solid var(--b1);padding:.85rem .5rem .7rem;display:flex;flex-direction:column;align-items:center;gap:.5rem;cursor:pointer;transition:all .2s;position:relative;overflow:hidden;}
.admi .ic:hover{border-color:var(--pri);background:rgba(5,150,105,.04);transform:translateY(-2px);box-shadow:0 6px 18px var(--sh2);}
.admi .ic img{width:38px;height:38px;object-fit:contain;}
.admi .ic-name{font-size:10px;color:var(--t3);text-align:center;word-break:break-all;line-height:1.3;}
.admi .ic-del{position:absolute;top:4px;left:4px;width:20px;height:20px;border-radius:6px;background:rgba(220,38,38,.9);border:none;color:#fff;font-size:11px;cursor:pointer;display:none;align-items:center;justify-content:center;}
.admi .ic:hover .ic-del{display:flex;}

.admi .empty{text-align:center;padding:4rem 2rem;color:var(--t3);}
.admi .empty i{font-size:52px;display:block;margin-bottom:1rem;opacity:.35;}
.admi .empty-ttl{font-size:15px;font-weight:700;color:var(--t2);margin-bottom:.4rem;}
.admi .empty-sub{font-size:13px;}
</style>

<div class="admi">
  <div class="pg-hd">
    <div>
      <div class="pg-ttl">مكتبة الأيقونات المخصصة</div>
      <div class="pg-sub">ارفع أيقونات SVG تستخدمها في الفئات والجهات والخدمات</div>
    </div>
  </div>

  <!-- UPLOAD ZONE -->
  <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
    <i class="ti ti-cloud-upload"></i>
    <div class="uz-ttl">اسحب الأيقونات هنا أو انقر للاختيار</div>
    <div class="uz-sub">يدعم SVG • PNG • يمكن رفع حتى 200 أيقونة دفعة واحدة</div>
    <div class="uz-badge">الحجم الأقصى: 512 كيلوبايت للأيقونة</div>
  </div>
  <x-ui.input type="file" id="fileInput" multiple accept=".svg,.png,.jpg,.jpeg,.webp" />

  <!-- PROGRESS -->
  <div class="progress-bar" id="progressBar">
    <div class="progress-fill" id="progressFill"></div>
  </div>

  <!-- TOOLBAR -->
  <div class="toolbar">
    <div class="search-box">
      <x-ui.input type="text" id="searchInput" placeholder="ابحث عن أيقونة باسمها..." oninput="filterIcons()" />
      <i class="ti ti-search"></i>
    </div>
    <div class="count-badge" id="countBadge">0 أيقونة</div>
  </div>

  <!-- ICON GRID -->
  <div class="icon-grid" id="iconGrid"></div>

  </div>

<script>
const API_LIST   = '{{ route('amrtm.api.admin.icons.list') }}';
const API_UPLOAD = '{{ route('amrtm.api.admin.icons.upload') }}';
const API_DELETE = '{{ route('amrtm.api.admin.icons.delete') }}';
const CSRF       = document.querySelector('meta[name="csrf-token"]').content;

let allIcons = [];

/* ── Load icons ── */
async function loadIcons() {
  const res  = await fetch(API_LIST);
  allIcons   = await res.json();
  renderGrid(allIcons);
}

function renderGrid(icons) {
  const grid = document.getElementById('iconGrid');
  document.getElementById('countBadge').textContent = icons.length + ' أيقونة';

  if (!icons.length) {
    grid.innerHTML = `<div class="empty" style="grid-column:1/-1">
      <i class="ti ti-mood-empty"></i>
      <div class="empty-ttl">لا توجد أيقونات بعد</div>
      <div class="empty-sub">ارفع أيقوناتك من المنطقة أعلاه</div>
    </div>`;
    return;
  }

  grid.innerHTML = icons.map(ic => `
    <div class="ic" title="${ic.name}" onclick="copyName('${ic.name}')">
      <x-ui.button type="button" class="ic-del" title="حذف" onclick="event.stopPropagation();deleteIcon('${ic.file}',this)">
        <i class="ti ti-x"></i>
      </x-ui.button>
      <img src="${ic.url}" alt="${ic.name}" loading="lazy" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22><text y=%2218%22 font-size=%2218%22>🖼</text></svg>'"/>
      <span class="ic-name">${ic.name}</span>
    </div>
  `).join('');
}

function filterIcons() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  renderGrid(q ? allIcons.filter(ic => ic.name.toLowerCase().includes(q)) : allIcons);
}

/* ── Copy name ── */
function copyName(name) {
  navigator.clipboard.writeText(name).then(() => toast('تم نسخ الاسم: ' + name, 'ok'));
}

/* ── Upload ── */
document.getElementById('fileInput').addEventListener('change', function() {
  if (this.files.length) uploadFiles(this.files);
});

const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag'));
zone.addEventListener('drop', e => {
  e.preventDefault();
  zone.classList.remove('drag');
  if (e.dataTransfer.files.length) uploadFiles(e.dataTransfer.files);
});

async function uploadFiles(files) {
  const bar  = document.getElementById('progressBar');
  const fill = document.getElementById('progressFill');
  bar.style.display = 'block';
  fill.style.width  = '30%';

  const fd = new FormData();
  Array.from(files).forEach(f => fd.append('icons[]', f));
  fd.append('_token', CSRF);

  try {
    fill.style.width = '70%';
    const res  = await fetch(API_UPLOAD, { method: 'POST', body: fd });
    const data = await res.json();
    fill.style.width = '100%';

    if (res.ok) {
      toast(data.message, 'ok');
      setTimeout(() => { bar.style.display='none'; fill.style.width='0'; }, 600);
      await loadIcons();
    } else {
      toast(data.message || 'حدث خطأ أثناء الرفع', 'err');
      bar.style.display = 'none';
    }
  } catch {
    toast('فشل الاتصال بالسيرفر', 'err');
    bar.style.display = 'none';
  }

  document.getElementById('fileInput').value = '';
}

/* ── Delete ── */
async function deleteIcon(file, btn) {
  if (!confirm('حذف هذه الأيقونة؟')) return;
  const res  = await fetch(API_DELETE, {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
    body: JSON.stringify({ file }),
  });
  const data = await res.json();
  if (res.ok) {
    toast(data.message, 'ok');
    btn.closest('.ic').remove();
    allIcons = allIcons.filter(ic => ic.file !== file);
    document.getElementById('countBadge').textContent = allIcons.length + ' أيقونة';
  } else {
    toast(data.message || 'فشل الحذف', 'err');
  }
}

/* ── Toast ── */
function toast(msg, type='ok') {
  if (window.AmrtmNotify) {
    const map = { ok:'success', err:'error', warn:'warning', info:'info' };
    window.AmrtmNotify.basic(msg, map[type] || 'success');
    return;
  }
  if (typeof window.showToast === 'function') window.showToast(msg, type === 'err' ? 'error' : 'success');
}

loadIcons();
</script>
@endsection