@extends('layouts.public')

@section('title', 'تتبع الطلب #' . $sr->ref_number . ' | آمر تم')

@php
    $service = $sr->govService;
    $entity  = $sr->entity;
    $office  = $sr->office;
    $isAssigned = $sr->isAssigned();
    $isAdmin = auth('business')->check() && auth('business')->user()->isAdmin();
@endphp

@section('content')
@include('partials.public.corporate-ui')
@include('partials.public.navbar', ['active' => 'home'])

{{-- ═══ Hero ═══ --}}
<section class="cui-hero overflow-hidden">
    <div class="mx-auto max-w-[1280px] px-4 py-16">
        <nav class="mb-6 text-sm text-white/60">
            <a href="{{ route('amrtm.index') }}" class="no-underline transition-colors hover:text-white">الرئيسية</a>
            <span class="mx-2">/</span>
            <a href="{{ route('amrtm.user.dashboard') }}" class="no-underline transition-colors hover:text-white">حسابي</a>
            <span class="mx-2">/</span>
            <a href="{{ route('amrtm.user.dashboard') }}#requests" class="no-underline transition-colors hover:text-white">طلباتي</a>
            <span class="mx-2">/</span>
            <span class="text-white">{{ $sr->ref_number }}</span>
        </nav>
        <div class="cui-hero-row--search">
            <div class="cui-hero-titleblock">
                <p class="mb-2 flex items-center gap-2 text-sm font-semibold text-[#7DDBA4]">
                    <i class="ti ti-map-pin-pin"></i> تتبع حالة الطلب
                </p>
                <h1 class="text-3xl font-bold text-white lg:text-4xl">طلب #{{ $sr->ref_number }}</h1>
                <p class="mt-2 text-white/70">
                    {{ $service->name_ar ?? 'خدمة' }}
                    @if($entity)
                        <span class="text-white/40">—</span>
                        {{ $entity->name_ar }}
                    @endif
                </p>
            </div>
            <div class="cui-hero-side mt-6 lg:mt-0">
                <span class="cui-glass inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold text-white">
                    <i class="ti ti-clock-hour-4"></i>
                    {{ $statusMeta['label'] }}
                </span>
            </div>
        </div>
    </div>
</section>

{{-- ═══ المحتوى ═══ --}}
<section class="py-12">
    <div class="mx-auto grid max-w-[1280px] grid-cols-1 gap-8 px-4 lg:grid-cols-[1fr_360px]">

        {{-- ═══ العمود الرئيسي: الـ Timeline ═══ --}}
        <div>
            <div class="mb-6 flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--cui-primary-soft)] text-[var(--cui-primary)]">
                    <i class="ti ti-route text-2xl"></i>
                </span>
                <div>
                    <h2 class="text-lg font-bold text-[#0A1F14]">مسار الطلب</h2>
                    <p class="text-sm text-gray-400">المراحل التي مر بها طلبك بالترتيب الزمني</p>
                </div>
            </div>

            <div class="timeline-wrap">
                @foreach($timeline as $index => $step)
                    <div class="tl-item">
                        <div class="tl-rail">
                            <div class="tl-dot {{ $step['state'] }} {{ $step['stage'] }}">
                                <i class="ti {{ $step['icon'] }}"></i>
                            </div>
                            @if(!$loop->last)
                                <div class="tl-line {{ $index < $loop->count - 1 && $timeline[$index + 1]['state'] === 'done' ? 'is-done' : ($step['state'] === 'done' ? 'is-done' : '') }}"></div>
                            @endif
                        </div>

                        <div class="tl-content">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="font-bold text-[#0A1F14]">{{ $step['label'] }}</h3>
                                <span class="tl-badge {{ $step['state'] }}">
                                    @if($step['state'] === 'done') مكتمل
                                    @elseif($step['state'] === 'current') حالياً
                                    @else في الانتظار
                                    @endif
                                </span>
                            </div>
                            @if($step['note'] && !str_contains($step['label'], 'قيد التنفيذ'))
                                <p class="mt-1.5 text-sm leading-relaxed text-gray-500">{{ $step['note'] }}</p>
                            @endif
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-gray-400" dir="ltr">
                                <i class="ti ti-calendar-time"></i>
                                {{ $step['time'] ? \Carbon\Carbon::parse($step['time'])->isoFormat('DD MMM YYYY — h:mm A') : '—' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($statusMeta['status'] === 'done')
                <div class="mt-6 flex items-center gap-3 rounded-2xl border border-[var(--cui-primary-border)] bg-[var(--cui-primary-soft)] p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[var(--cui-primary)] text-white">
                        <i class="ti ti-circle-check text-2xl"></i>
                    </span>
                    <div>
                        <p class="font-bold text-[#0A1F14]">اكتمل طلبك بنجاح</p>
                        <p class="text-sm text-gray-500">شكراً لتعاملك مع منصة آمر تم. يمكنك مراجعة المرفقات أدناه.</p>
                    </div>
                </div>
            @endif

            {{-- المرفقات --}}
            @if(!empty($sr->attachments))
                <div class="mt-8">
                    <h3 class="mb-4 flex items-center gap-2 font-bold text-[#0A1F14]">
                        <i class="ti ti-paperclip text-[var(--cui-primary)]"></i> المرفقات
                    </h3>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($sr->attachments as $attachment)
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($attachment) }}"
                               target="_blank"
                               class="group flex items-center gap-3 rounded-xl border border-[var(--cui-border)] bg-white p-4 no-underline transition-all hover:border-[var(--cui-border-hover)] hover:shadow-md">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[var(--cui-primary-soft)] text-[var(--cui-primary)]">
                                    <i class="ti ti-file-download"></i>
                                </span>
                                <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-700 group-hover:text-[#0A1F14]">
                                    {{ basename($attachment) }}
                                </span>
                                <i class="ti ti-download text-gray-300 transition-colors group-hover:text-[var(--cui-primary)]"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- المحادثة مع المكتب --}}
            @if($isAssigned && $office)
                <div class="mt-8" id="trk-chat-card">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h3 class="flex items-center gap-2 font-bold text-[#0A1F14]">
                            <i class="ti ti-messages text-[var(--cui-primary)]"></i> المحادثة مع {{ $office->name_ar }}
                        </h3>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[var(--cui-primary-soft)] px-3 py-1 text-xs font-semibold text-[var(--cui-primary)]">
                            <i class="ti ti-shield-lock"></i> داخل المنصة فقط — يُمنع تبادل وسائل التواصل
                        </span>
                    </div>
                    <div class="rounded-2xl border border-[var(--cui-border)] bg-white p-5 shadow-sm">
                        <div class="chat" id="trk-chat-box"></div>
                        <div class="mt-2 flex flex-col gap-2">
                            <div id="trk-att-list" class="hidden flex flex-wrap gap-2"></div>
                            <div class="flex items-end gap-3">
                                <x-ui.textarea class="msg-area" id="trk-msg-inp" :rows="2"
                                    placeholder="اكتب رسالتك للمكتب هنا..." />
                                <div class="flex shrink-0 flex-col gap-2">
                                    <button type="button" id="trk-att-btn" onclick="document.getElementById('trk-att-files').click()"
                                        class="flex h-10 cursor-pointer items-center justify-center rounded-xl border border-[var(--cui-border)] bg-white px-3 text-gray-500 transition-all duration-300 hover:border-[var(--cui-primary-border)] hover:text-[var(--cui-primary)]"
                                        title="إرفاق ملف" aria-label="إرفاق ملف (صور / PDF / مستندات)">
                                        <i class="ti ti-paperclip text-lg"></i>
                                    </button>
                                    <input type="file" id="trk-att-files" class="hidden"
                                        accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,.tif,.tiff,.pdf,.txt,.csv,.json,.md,.log,.doc,.docx,.xls,.xlsx,.zip"
                                        multiple onchange="handleAttFiles(event)" />
                                    <x-ui.button variant="primary" class="shrink-0" onclick="doSendMsg()" id="trk-send-btn">
                                        <i class="ti ti-send"></i> إرسال
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-8 flex items-start gap-3 rounded-2xl border border-[var(--cui-border)] bg-white p-5">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--cui-primary-soft)] text-[var(--cui-primary)]">
                        <i class="ti ti-messages"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-[#0A1F14]">المحادثة مع المكتب</h3>
                        <p class="mt-1 text-sm leading-relaxed text-gray-500">
                            @if($office)
                                ستبدأ المحادثة مع {{ $office->name_ar }} داخل المنصة بعد إسناد الطلب إليه رسمياً.
                            @else
                                يبدأ التواصل مع المكتب المنفّذ داخل المنصة بعد إسناد طلبك. راقب هذه الصفحة لمعرفة المكتب المنفّذ.
                            @endif
                        </p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ═══ العمود الجانبي: ملخص الطلب ═══ --}}
        <aside class="space-y-6">
            {{-- بطاقة المعلومات --}}
            <div class="rounded-2xl border border-[var(--cui-border)] bg-white p-6 shadow-sm">
                <h3 class="mb-5 flex items-center gap-2 text-base font-bold text-[#0A1F14]">
                    <i class="ti ti-file-text text-[var(--cui-primary)]"></i> بيانات الطلب
                </h3>
                <dl class="space-y-4 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-400">رقم المرجع</dt>
                        <dd class="font-bold text-[#0A1F14] tabular-nums" dir="ltr">{{ $sr->ref_number }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-400">الخدمة</dt>
                        <dd class="text-left font-semibold text-[#0A1F14]">{{ $service->name_ar ?? '—' }}</dd>
                    </div>
                    @if($entity)
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-400">الجهة</dt>
                        <dd class="text-left font-semibold text-[#0A1F14]">{{ $entity->name_ar }}</dd>
                    </div>
                    @endif
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-400">التكلفة</dt>
                        <dd class="font-bold text-[var(--cui-primary)] tabular-nums" dir="ltr">
                            {{ number_format((float) $sr->price, 2) }} ر.س
                        </dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-400">تاريخ التقديم</dt>
                        <dd class="tabular-nums" dir="ltr">
                            {{ \Carbon\Carbon::parse($sr->created_at)->isoFormat('DD MMM YYYY') }}
                        </dd>
                    </div>
                    @if($sr->estimated_completion)
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-400">الإنجاز المتوقع</dt>
                        <dd class="font-semibold text-[#0A1F14]">{{ $sr->estimated_completion }}</dd>
                    </div>
                    @endif
                    @if($sr->completed_at)
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-400">تاريخ الإنجاز</dt>
                        <dd class="tabular-nums" dir="ltr">
                            {{ \Carbon\Carbon::parse($sr->completed_at)->isoFormat('DD MMM YYYY') }}
                        </dd>
                    </div>
                    @endif
                </dl>

                <div class="mt-5 flex items-center gap-2 rounded-xl bg-[var(--cui-primary-soft)] px-4 py-3 text-sm font-medium text-[var(--cui-primary)]">
                    <i class="ti ti-info-circle"></i>
                    <span id="trk-hint">الحالة: <b>{{ $statusMeta['label'] }}</b></span>
                </div>
            </div>

            {{-- بطاقة المكتب المساند --}}
            @if($office)
            <div class="rounded-2xl border border-[var(--cui-border)] bg-white p-6 shadow-sm">
                <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-[#0A1F14]">
                    <i class="ti ti-building text-[var(--cui-primary)]"></i> {{ $isAssigned ? 'المكتب المنفذ' : 'معلومات المكتب' }}
                </h3>
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[var(--cui-primary-soft)] text-xl text-[var(--cui-primary)]">
                        <i class="ti ti-building"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-bold text-[#0A1F14]">{{ $office->name_ar }}</p>
                        @if($office->city)
                            <p class="flex items-center gap-1 text-xs text-gray-400"><i class="ti ti-map-pin"></i> {{ $office->city }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- إجراءات --}}
            <div class="space-y-3">
                <a href="{{ route('amrtm.user.dashboard') }}#requests"
                   class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#006C35] py-3 font-semibold text-white no-underline transition-all duration-300 hover:bg-[#0B3B2C]" id="trk-back">
                    <i class="ti ti-arrow-right"></i> العودة إلى طلباتي
                </a>
                <a href="{{ route('amrtm.index') }}"
                   class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 py-3 font-semibold text-gray-600 no-underline transition-all duration-300 hover:bg-gray-50" id="trk-home">
                    <i class="ti ti-home"></i> الرئيسية
                </a>
            </div>
        </aside>
    </div>
</section>

@include('partials.public.footer')
@endsection

@push('styles')
<style>
/* ── Timeline ── */
.timeline-wrap { display: flex; flex-direction: column; }

.tl-item { display: flex; gap: 16px; }

.tl-rail { display: flex; flex-direction: column; align-items: center; }

.tl-dot {
    width: 40px; height: 40px; border-radius: 9999px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    background: #fff;
    border: 2px solid var(--cui-border-hover);
    color: var(--cui-text-muted);
    flex-shrink: 0;
    position: relative;
    z-index: 1;
    transition: all .3s ease;
}

.tl-dot.done       { background: var(--cui-primary); border-color: var(--cui-primary); color: #fff; box-shadow: 0 4px 16px rgba(0, 108, 53, .3); animation: cuiFadeIn .4s ease both; }
.tl-dot.current    { background: #fff; border-color: var(--cui-primary); color: var(--cui-primary); box-shadow: 0 0 0 6px rgba(0, 108, 53, .08); animation: pulse-dot 2s ease infinite; }
.tl-dot.rejected.current,
.tl-dot.rejected.done { background: #dc2626; border-color: #dc2626; color: #fff; box-shadow: 0 4px 16px rgba(220, 38, 38, .3); }

.tl-line {
    width: 2px; height: 56px; background: var(--cui-border);
    transition: background .3s ease; flex-shrink: 0;
}
.tl-line.is-done { background: var(--cui-primary); opacity: .35; }

.tl-content { padding-bottom: 30px; min-width: 0; }

.tl-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 11px; border-radius: 999px;
    font-size: 11px; font-weight: 700; white-space: nowrap;
}
.tl-badge.done    { background: var(--cui-primary-soft); color: var(--cui-primary); }
.tl-badge.current { background: rgba(0, 108, 53, .12); color: var(--cui-primary); border: 1.5px solid var(--cui-primary); }
.tl-badge.upcoming{ background: #f3f4f6; color: #9ca3af; }

@keyframes pulse-dot {
    0%, 100% { box-shadow: 0 0 0 6px rgba(0, 108, 53, .08); }
    50%      { box-shadow: 0 0 0 12px rgba(0, 108, 53, .03); }
}

/* ── المحادثة مع المكتب ── */
.chat {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 340px;
    overflow-y: auto;
    padding: 4px 2px;
}

.msg {
    max-width: 80%;
    padding: 10px 14px;
    border-radius: 14px;
    font-size: 13px;
    line-height: 1.7;
    white-space: pre-wrap;
    word-break: break-word;
}

.msg-office {
    align-self: flex-end;
    background: linear-gradient(135deg, #059669, #047857);
    color: #fff;
    border-radius: 14px 14px 4px 14px;
    box-shadow: 0 6px 16px rgba(5, 150, 105, .22);
}

.msg-client {
    align-self: flex-start;
    background: #f8fafc;
    color: #333;
    border: 1px solid #eef2f6;
    border-radius: 14px 14px 14px 4px;
}

.msg-meta {
    font-size: 10px;
    opacity: .65;
    margin-top: 4px;
}

.msg-area {
    width: 100% !important;
    border: 1.5px solid var(--cui-border-hover) !important;
    border-radius: 12px !important;
    padding: 10px 13px !important;
    font-size: 13px !important;
    font-family: 'Cairo', sans-serif !important;
    resize: vertical !important;
    min-height: 58px !important;
    transition: border-color .3s ease !important;
}

.msg-area:focus {
    border-color: var(--cui-primary) !important;
    outline: none !important;
    box-shadow: 0 0 0 4px rgba(0, 108, 53, .08) !important;
}

.chat::-webkit-scrollbar { width: 6px; }
.chat::-webkit-scrollbar-thumb { background: var(--cui-border-hover); border-radius: 99px; }

.trk-att-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    max-width: 220px;
    padding: 5px 10px;
    border-radius: 10px;
    border: 1px solid var(--cui-border);
    background: var(--cui-muted);
    font-size: 12px;
    color: var(--cui-foreground);
}
.trk-att-chip .trk-att-rm {
    cursor: pointer;
    color: #f87171;
    line-height: 1;
    border: 0;
    background: none;
    padding: 0;
}
.msg-att {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    padding: 8px 10px;
    border-radius: 10px;
    font-size: 12px;
    text-decoration: none;
    color: inherit;
    background: rgba(255, 255, 255, .16);
    border: 1px solid rgba(255, 255, 255, .22);
    transition: background .3s ease;
}
.msg-att:hover { background: rgba(255, 255, 255, .28); }
.msg-client .msg-att {
    background: #fff;
    border-color: var(--cui-border);
    color: #334155;
}
.msg-client .msg-att:hover { background: var(--cui-muted); }
.msg-att-ico { flex-shrink: 0; font-size: 16px; }
.msg-att-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>
@endpush

@if($isAssigned && $office)
@push('scripts')
<script>
    (function () {
        const CSRF = '{{ csrf_token() }}';
        const CHAT_ID = {{ $sr->id }};
        const API = {
            list: '{{ route('amrtm.api.requests.messages', $sr->id) }}',
            send: '{{ route('amrtm.api.requests.messages.send', $sr->id) }}'
        };
        const box = document.getElementById('trk-chat-box');
        const inp = document.getElementById('trk-msg-inp');
        const btn = document.getElementById('trk-send-btn');

        function esc(v) {
            return String(v == null ? '' : v)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function fmtDate(s) {
            if (!s) return '—';
            const d = new Date(s);
            if (isNaN(d.getTime())) return '—';
            return d.toLocaleString('ar-SA', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
        }

        async function req(method, url, body) {
            const opt = {
                method,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            };
            if (body) {
                opt.headers['Content-Type'] = 'application/json';
                opt.body = JSON.stringify(body);
            }
            const r = await fetch(url, opt);
            const j = await r.json().catch(() => ({}));
            if (!r.ok) throw j;
            return j;
        }

        function renderMsgs(msgs) {
            if (!msgs.length) {
                box.innerHTML = '<div style="text-align:center;font-size:13px;color:#9ca3af;padding:16px">لا توجد رسائل بعد — أرسل أول رسالة</div>';
                return;
            }
            box.innerHTML = msgs.map(function (m) {
                const side = m.sender_type === 'client' ? 'client' : 'office';
                const who = side === 'office' ? 'المكتب المنفّذ' : 'أنت';
                const atts = Array.isArray(m.attachments) && m.attachments.length
                    ? '<div class="msg-att-wrap">' + m.attachments.map(function (a) {
                        var icon = a.kind === 'image' ? 'ti-photo' : (a.kind === 'pdf' ? 'ti-file-text' : 'ti-file');
                        return '<a class="msg-att" href="' + attUrl(m, a.path) + '" target="_blank" rel="noopener">' +
                            '<i class="ti ' + icon + ' msg-att-ico"></i>' +
                            '<span class="msg-att-name">' + esc(a.name || 'مرفق') + '</span>' +
                            '<i class="ti ti-download" style="margin-right:auto;opacity:.7"></i>' +
                            '</a>';
                    }).join('') + '</div>' : '';
                return '<div class="msg msg-' + side + '">' +
                    esc(m.message) +
                    atts +
                    '<div class="msg-meta">' + fmtDate(m.created_at) + ' · ' + who + '</div>' +
                    '</div>';
            }).join('');
            box.scrollTop = box.scrollHeight;
        }

        async function loadMessages() {
            if (document.visibilityState !== 'visible') return;
            if (!inp || document.activeElement === inp) return;
            try {
                const msgs = await req('GET', API.list, null);
                renderMsgs(msgs);
            } catch (e) { /* تجاهل أخطاء الاستطلاع */ }
        }

        function attUrl(m, path) {
            return '{{ route('api.v1.requests.messages.attachment', ['requestId' => '__RID__', 'messageId' => '__MID__', 'file' => '__FILE__']) }}'
                .replace('__RID__', m.request_id || CHAT_ID)
                .replace('__MID__', m.id)
                .replace('__FILE__', encodeURIComponent(path || ''));
        }

        function getAttFiles() {
            const input = document.getElementById('trk-att-files');
            return input ? Array.from(input.files || []) : [];
        }

        function renderAttChips() {
            const list = document.getElementById('trk-att-list');
            const files = getAttFiles();
            if (!list) return;
            list.classList.toggle('hidden', files.length === 0);
            list.innerHTML = files.map(function (f, i) {
                return '<span class="trk-att-chip"><i class="ti ti-paperclip"></i>' +
                    esc(f.name) +
                    '<button type="button" class="trk-att-rm" onclick="removeAttFile(' + i + ')" title="إزالة">' +
                    '<i class="ti ti-x"></i></button></span>';
            }).join('');
        }

        function handleAttFiles() {
            const input = document.getElementById('trk-att-files');
            if (!input) return;
            const allowed = 5;
            if (input.files.length > allowed) {
                if (window.AmrtmNotify) window.AmrtmNotify.error('الحد الأقصى ' + allowed + ' ملفات للرسالة');
                input.value = '';
            }
            renderAttChips();
        }

        function removeAttFile(i) {
            const input = document.getElementById('trk-att-files');
            if (!input) return;
            const dt = new DataTransfer();
            Array.from(input.files).forEach(function (f, idx) {
                if (idx !== i) dt.items.add(f);
            });
            input.files = dt.files;
            renderAttChips();
        }

        async function doSendMsg() {
            const msg = inp.value.trim();
            const files = getAttFiles();
            if (!msg && !files.length) {
                if (window.AmrtmNotify) window.AmrtmNotify.basic('اكتب رسالة أو أرفق ملفاً أولاً', 'warning');
                inp.focus();
                return;
            }
            btn.disabled = true;
            try {
                const fd = new FormData();
                if (msg) fd.append('message', msg);
                files.forEach(function (f) { fd.append('attachments[]', f); });
                const opt = {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
                };
                if (files.length) {
                    opt.body = fd;
                } else {
                    opt.headers['Content-Type'] = 'application/json';
                    opt.body = JSON.stringify({ message: msg });
                }
                const r = await fetch(API.send, opt);
                const j = await r.json().catch(() => ({}));
                if (!r.ok) throw j;
                inp.value = '';
                const input = document.getElementById('trk-att-files');
                if (input) input.value = '';
                renderAttChips();
                const msgs = await req('GET', API.list, null);
                renderMsgs(msgs);
                if (window.AmrtmNotify) window.AmrtmNotify.basic('تم إرسال الرسالة', 'success');
            } catch (e) {
                const msgErr = (e && e.message) || 'حدث خطأ أثناء الإرسال';
                if (window.AmrtmNotify) { window.AmrtmNotify.error(msgErr); }
                else { alert(msgErr); }
            } finally {
                btn.disabled = false;
            }
        }

        window.doSendMsg = doSendMsg;

        document.addEventListener('DOMContentLoaded', function () {
            if (inp) {
                inp.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        doSendMsg();
                    }
                });
            }
            loadMessages();
            setInterval(loadMessages, 5000);
        });
    })();
</script>
@endpush
@endif