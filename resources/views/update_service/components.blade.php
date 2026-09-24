@extends('layouts.dashboard')

@section('title', 'مصفوفة المكونات — آمر تم')

@section('dashboard-content')
@php
    $codeSample = 'width: 100%; border-radius: 12px; padding: 10px 14px; border: 1px solid #e2e8f0; font-size: 13px; font-family: Consolas, monospace; direction: ltr; text-align: left; background: #f8fafc;';
@endphp

<div class="space-y-10">
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-extrabold text-slate-900">مصفوفة مكونات لوحة التحكم (x-ui.*)</h2>
        <p class="mt-2 text-sm text-slate-500">
            صفحة تطوير خاصة تثبت عمل كل مكوّن داخل هيكل Flowbite (layouts.dashboard).
            تم التفاعل مع كل عنصر يكتب حدثاً في السجل أدناه للتأكد أن الـ JS callbacks تعمل فعلاً — لا مجرد عرض HTML.
        </p>

        <div class="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
            <span id="pg-now" data-testid="pg-now" class="rounded-lg bg-[#006C35] px-3 py-1 text-sm font-bold text-white">3</span>
            <x-ui.button id="play-clear" data-testid="play-clear" type="button" onclick="document.getElementById('play-log').innerHTML=''">مسح السجل</x-ui.button>
            <ol id="play-log" data-testid="play-log" class="max-h-40 min-h-8 flex-1 overflow-y-auto rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600"></ol>
        </div>
    </div>

    {{-- ── 1. Inputs & fields ─────────────────────────────── --}}
    <section data-section="inputs" class="grid gap-6 lg:grid-cols-3">
        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">نص خام</h3>
            <x-ui.input name="p-input" id="p-input" label="اسم العميل" placeholder="مثلاً: محمد عبدالله" oninput="play('input', this.value)" />
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">هاتف (ltr)</h3>
            <x-ui.input name="p-phone" id="p-phone" label="رقم الجوال" placeholder="مثلاً: 0551234567" dir="ltr" oninput="play('phone', this.value)" />
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">مبلغ مالي</h3>
            <x-ui.amount-field id="p-amount" name="p-amount" label="الرسوم" placeholder="مثلاً: 350" currency="ر.س" min="0" step="1" oninput="play('amount', this.value)" />
        </x-ui.card>
    </section>

    {{-- ── 2. Secret field ─────────────────────────────────── --}}
    <section data-section="secret" class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">كلمة مرور مع عين</h3>
            <x-ui.eye-field id="p-eyefield" name="p-eyefield" label="كلمة المرور" placeholder="أدخل كلمة المرور" autocomplete="new-password" />
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">نص طويل</h3>
            <x-ui.textarea id="p-textarea" name="p-textarea" label="ملاحظات" placeholder="مثلاً: تفاصيل إضافية عن الطلب" rows="4" oninput="play('textarea', this.value)" />
        </x-ui.card>
    </section>

    {{-- ── 3. Chips & radio-chips ───────────────────────────── --}}
    <section data-section="chips" class="grid gap-6 lg:grid-cols-3">
        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">Chips — اختيار واحد</h3>
            <x-ui.label for="p-chips-single">طريقة الاستلام</x-ui.label>
            <x-ui.chips id="p-chips-single" name="p-chips-single" mode="single" onchange="play('chips-single', this.dataset.chipValue)"
                :items="[
                    ['value' => 'fetch','label' => 'استلام من المكتب','active' => true],
                    ['value' => 'send','label' => 'إرسال بالبريد'],
                    ['value' => 'digital','label' => 'نسخة إلكترونية','badge' => 2],
                ]" />
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">Chips — اختيار متعدد</h3>
            <x-ui.label>قنوات التواصل</x-ui.label>
            <x-ui.chips name="p-chips-multi" mode="multiple" onchange="play('chips-multi', this.dataset.chipValue)"
                :items="[
                    ['value' => 'sms','label' => 'رسالة SMS','dot' => '#2563EB'],
                    ['value' => 'email','label' => 'بريد إلكتروني','dot' => '#16A34A','active' => true],
                    ['value' => 'wa','label' => 'واتساب','dot' => '#006C35'],
                ]" />
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">Radio chips</h3>
            <x-ui.label>نوع الاستشارة</x-ui.label>
            <div class="flex flex-wrap gap-2">
                <x-ui.radio-chip name="p-consult" id="p-radio-standard" value="standard" checked onchange="play('radio', this.value)" class="cursor-pointer rounded-full border border-slate-200 bg-slate-50 px-3.5 py-1.5 text-sm font-semibold text-slate-600">استشارة صوتية</x-ui.radio-chip>
                <x-ui.radio-chip name="p-consult" id="p-radio-video" value="video" onchange="play('radio', this.value)" class="cursor-pointer rounded-full border border-slate-200 bg-slate-50 px-3.5 py-1.5 text-sm font-semibold text-slate-600">استشارة مرئية</x-ui.radio-chip>
                <x-ui.radio-chip name="p-consult" id="p-radio-text" value="text" disabled onchange="play('radio', this.value)" class="cursor-pointer rounded-full border border-slate-200 bg-slate-100 px-3.5 py-1.5 text-sm font-semibold text-slate-400">نصي (معطّل)</x-ui.radio-chip>
            </div>
        </x-ui.card>
    </section>

    {{-- ── 4. Checkbox & toggle ─────────────────────────────── --}}
    <section data-section="toggles" class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">Checkbox</h3>
            <x-ui.checkbox id="p-check" name="p-check" value="1" label="أوافق على شروط الاستخدام" checked data-testid="p-check" onchange="play('checkbox', this.checked)" />
            <div class="mt-4">
                <x-ui.checkbox id="p-check-bare" name="p-check-bare" value="1" onchange="play('checkbox-bare', this.checked)" />
            </div>
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">Toggle (مفتاح تبديل)</h3>
            <x-ui.toggle id="p-toggle" name="p-toggle" label="تفعيل التنبيهات" description="تصلك رسائل عند كل حالة جديدة" checked onchange="play('toggle', this.checked)" />
            <div class="mt-5">
                <x-ui.toggle id="p-toggle2" name="p-toggle2" label="الوضع الصامت" onchange="play('toggle2', this.checked)" />
            </div>
        </x-ui.card>
    </section>

    {{-- ── 5. Select & datepicker ──────────────────────────── --}}
    <section data-section="select" class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">Select</h3>
            <x-ui.select id="p-select" name="p-select" label="الجهة" placeholder="اختر الجهة" onchange="play('select', this.value)">
                <option value="moi">وزارة الداخلية</option>
                <option value="mc">وزارة التجارة</option>
                <option value="mci">وزارة الاستثمار</option>
            </x-ui.select>
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">تحديد التاريخ (Flowbite Datepicker)</h3>
            <x-ui.datepicker id="p-date" name="p-date" label="تاريخ الطلب" placeholder="مثلاً: 2026-09-15" format="yyyy-mm-dd" autohide />
        </x-ui.card>
    </section>

    {{-- ── 6. Buttons & badges ───────────────────────────────── --}}
    <section data-section="buttons" class="space-y-6">
        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">الأزرار (variants + sizes)</h3>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button variant="primary" onclick="play('button', 'primary')" data-testid="btn-primary">حفظ</x-ui.button>
                <x-ui.button variant="secondary" onclick="play('button', 'secondary')" data-testid="btn-secondary">إلغاء</x-ui.button>
                <x-ui.button variant="danger" onclick="play('button', 'danger')" data-testid="btn-danger">حذف</x-ui.button>
                <x-ui.button variant="ghost" onclick="play('button', 'ghost')" data-testid="btn-ghost">رجوع</x-ui.button>
                <x-ui.button variant="primary" size="sm" onclick="play('button', 'sm')" data-testid="btn-sm">صغير</x-ui.button>
                <x-ui.button variant="primary" size="lg" onclick="play('button', 'lg')" data-testid="btn-lg">كبير</x-ui.button>
                <x-ui.button variant="primary" disabled data-testid="btn-disabled">مُعطّل</x-ui.button>
                <x-ui.button variant="secondary" href="/dev/components" data-testid="btn-link">زر كرابط</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">الشارات (badges)</h3>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.badge data-testid="badge-default">جديد</x-ui.badge>
                <x-ui.badge variant="success">مكتمل</x-ui.badge>
                <x-ui.badge variant="warning">قيد المعالجة</x-ui.badge>
                <x-ui.badge variant="danger">مرفوض</x-ui.badge>
                <x-ui.badge variant="primary" size="lg">مميز</x-ui.badge>
                <x-ui.badge variant="gray">مسودة</x-ui.badge>
            </div>
        </x-ui.card>
    </section>

    {{-- ── 7. Card & table & pagination ─────────────────────── --}}
    <section data-section="tables" class="space-y-6">
        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">جدول بيانات</h3>
            <x-ui.table :head="['الطلب','الحالة','المبلغ']" aria-label="جدول الطلبات">
                <tr data-testid="table-row-1">
                    <td class="px-4 py-3">RV-1001</td>
                    <td><x-ui.badge variant="success">مكتمل</x-ui.badge></td>
                    <td class="px-4 py-3" dir="ltr">350 ر.س</td>
                </tr>
                <tr>
                    <td class="px-4 py-3">RV-1002</td>
                    <td><x-ui.badge variant="warning">قيد المعالجة</x-ui.badge></td>
                    <td class="px-4 py-3" dir="ltr">120 ر.س</td>
                </tr>
            </x-ui.table>
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">جدول فارغ</h3>
            <x-ui.table :head="['الطلب','الحالة']" empty="لا توجد طلبات حالياً" emptyColspan="2" data-testid="table-empty" />
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">ترقيم الصفحات</h3>
            <x-ui.pagination current="3" last="7" onchange="pg" aria-label="صفحات النتائج" data-testid="p-pager" />
        </x-ui.card>
    </section>

    {{-- ── 8. Modal ───────────────────────────────────────────── --}}
    <section data-section="modal" class="space-y-6">
        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">نافذة منبثقة (Flowbite Modal)</h3>
            <x-ui.button variant="primary" onclick="AMRTM_MODAL.open('play-modal')" data-testid="modal-open">فتح النافذة</x-ui.button>
        </x-ui.card>
    </section>

    {{-- ── 9. JS builders ─────────────────────────────────────── --}}
    <section data-section="jsbuild" class="space-y-6">
        <x-ui.card>
            <h3 class="mb-4 text-sm font-extrabold text-slate-800">البناة البرمجية AMRTM_UI (خالية من العناصر الخام)</h3>
            <p id="jsbuild-flag" data-testid="jsbuild-flag" class="mb-3 text-xs font-bold text-slate-500">جارٍ البناء...</p>
            <div id="p-jsbuild" data-testid="p-jsbuild" class="space-y-2"></div>
        </x-ui.card>
    </section>
</div>

<x-ui.modal id="play-modal" maxWidth="md" title="نافذة تجريبية">
    <x-ui.label for="modal-input">حقل داخل النافذة</x-ui.label>
    <x-ui.input id="modal-input" name="modal-input" placeholder="مثلاً: اكتب شيئاً ثم أغلق النافذة" />
    <x-slot name="footer">
        <x-ui.button variant="secondary" onclick="AMRTM_MODAL.close('play-modal')" data-testid="modal-close">إغلاق</x-ui.button>
        <x-ui.button variant="primary" onclick="play('modal', 'confirm')" data-testid="modal-confirm">تأكيد</x-ui.button>
    </x-slot>
</x-ui.modal>

@endsection

@push('scripts')
<script>
    function play(name, detail) {
        var log = document.getElementById('play-log');
        if (!log) return;
        var li = document.createElement('li');
        li.className = 'border-b border-slate-100 py-1 last:border-0';
        li.setAttribute('data-play-entry', name);
        li.textContent = '[' + new Date().toLocaleTimeString('ar-SA') + '] ' + name + ' = ' + String(detail);
        log.appendChild(li);
        log.scrollTop = log.scrollHeight;
    }

    function pg(n) {
        document.getElementById('pg-now').textContent = n;
        play('pagination', n);
    }

    window.addEventListener('DOMContentLoaded', function () {
        if (!window.AMRTM_UI) { document.getElementById('jsbuild-flag').textContent = 'AMRTM_UI غير متوفر'; return; }
        document.getElementById('p-jsbuild').innerHTML =
            AMRTM_UI.checkbox({ name: 'js_cb', checked: true, onchange: 'play(\'checkbox-js\', this.checked)' }) +
            AMRTM_UI.button({ onclick: 'play(\'button-js\', 1)' }, 'زر مبني برمجياً') +
            AMRTM_UI.input({ name: 'js_input', placeholder: 'مثلاً: أصل X' }) ;
        document.getElementById('jsbuild-flag').textContent = 'built';
    });
</script>
@endpush