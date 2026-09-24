@extends('update_service.dashboard.admin.layout')

@php
    $reqs = $pageData['requests']['data'] ?? [];
    $total = $pageData['requests']['total'] ?? 0;

    $statusMap = [
        'pending'     => ['label' => 'قيد الانتظار',       'color' => 'var(--orange)', 'bg' => 'rgba(230,81,0,.1)'],
        'processing'  => ['label' => 'جاري المعالجة',       'color' => 'var(--blue)',   'bg' => 'rgba(2,119,189,.1)'],
        'in_progress' => ['label' => 'قيد التنفيذ',         'color' => 'var(--yellow)', 'bg' => 'rgba(249,168,37,.1)'],
        'done'        => ['label' => 'تمت العملية',         'color' => 'var(--green)',  'bg' => 'rgba(4,120,87,.1)'],
        'rejected'    => ['label' => 'مرفوض',              'color' => 'var(--red)',    'bg' => 'rgba(220,38,38,.1)'],
    ];

    $assignStatusMap = [
        'pending'     => 'بانتظار قبول المكتب',
        'accepted'    => 'قبلها المكتب',
        'in_progress' => 'قيد التنفيذ بالمكتب',
        'waiting_docs'=> 'ينتظر مستندات',
        'done'        => 'أنجزها المكتب',
        'rejected'    => 'رفضها المكتب',
    ];

    if (! function_exists('renderAdminIcon')) {
        function renderAdminIcon($icon, $color = '#059669') {
            if ($icon && str_starts_with($icon, 'img:')) {
                $file = substr($icon, 4);
                return '<img src="/icons/' . e(urlencode($file)) . '" style="max-width:80%;max-height:80%;object-fit:contain;" onerror="this.style.opacity=\'.2\'">';
            }
            $safe = e($icon ?: 'ti-file-text');
            $safeColor = e($color);
            return '<i class="ti ' . $safe . '" style="color:' . $safeColor . '"></i>';
        }
    }
@endphp

@section('admin-content')
                <div class="page" id="page-requests">
                    <div class="pg-hd">
                        <div>
                            <div class="pg-ttl" id="req-ttl">قائمة الطلبات</div>
                            <div class="pg-sub" id="req-sub">إجمالي {{ $total }} طلب</div>
                        </div>
                    </div>
                    <div class="req-filters">
                        <x-ui.button class="rf-btn on" onclick="filterReqs('all',this)" id="rf-all">الكل</x-ui.button>
                        <x-ui.button class="rf-btn" onclick="filterReqs('pending',this)" id="rf-pend">قيد
                            الانتظار</x-ui.button>
                        <x-ui.button class="rf-btn" onclick="filterReqs('processing',this)" id="rf-proc">جاري
                            المعالجة</x-ui.button>
                        <x-ui.button class="rf-btn" onclick="filterReqs('done',this)" id="rf-done">مكتملة</x-ui.button>
                        <x-ui.button class="rf-btn" onclick="filterReqs('rejected',this)" id="rf-rej">مرفوضة</x-ui.button>
                    </div>
                    <div id="req-list">
                        @forelse($reqs as $i => $req)
                            @php
                                $st = $statusMap[$req['status']] ?? ['label' => $req['status'], 'color' => '#999', 'bg' => 'rgba(0,0,0,.05)'];
                                $gs = $req['gov_service'] ?? null;
                                $ent = $req['entity'] ?? null;
                                $svcNm = $gs['name_ar'] ?? $gs['name_en'] ?? '—';
                                $entNm = $ent['name_ar'] ?? '—';
                                $entColor = $ent['color'] ?? '#059669';
                                $entBg = $ent['bg'] ?? 'rgba(5,150,105,.09)';
                                $logs = $req['logs'] ?? [];
                                $canFulfill = $req['status'] === 'pending' && empty($req['fulfillment']);
                                $isPooled = ($req['origin'] ?? '') === 'office' && $req['status'] === 'pending' && ($req['fulfillment'] ?? '') === 'open' && empty($req['office_id']);
                                $needFulfill = $canFulfill || $isPooled;
                                $fulfillLbl = $isPooled ? 'بث / إنجاز' : 'إسناد / إنجاز';
                                $reqId = $req['id'];
                                $createdAt = $req['created_at'] ?? null;
                                $dateStr = $createdAt ? \Carbon\Carbon::parse($createdAt)->locale('ar')->isoFormat('D MMM YYYY') : '—';

                                /* ── منطق الأزرار: حسب رحلة الطلب والصلاحيات ── */
                                $reqStatus = $req['status'] ?? 'pending';
                                $isOpen   = in_array($reqStatus, ['pending', 'processing', 'in_progress'], true);
                                $isClosed = in_array($reqStatus, ['done', 'rejected'], true);
                                $adminRole = auth('business')->user()->role ?? 'admin';
                                $canAssign = ($adminRole === 'admin') && $needFulfill;

                                // الخطوة المنطقية التالية في رحلة الطلب (مع تمييزها كإجراء رئيسي)
                                $next = null;
                                if ($reqStatus === 'pending') {
                                    $next = ['processing',  'جاري المعالجة', 'proc',   'ti-loader'];
                                } elseif ($reqStatus === 'processing') {
                                    $next = ['in_progress', 'قيد التنفيذ',   'inprog', 'ti-settings'];
                                } elseif ($reqStatus === 'in_progress') {
                                    $next = ['done',        'مكتمل',         'done',   'ti-circle-check'];
                                }
                            @endphp
                            <div class="req-card" id="rc-{{ $i }}">
                                <div class="req-hd" onclick="togReq({{ $i }})">
                                    <div class="req-ico" style="background:{{ $entBg }};">
                                        {!! renderAdminIcon($gs['icon'] ?? null, $entColor) !!}
                                    </div>
                                    <div class="req-info">
                                        <div class="req-nm">{{ $svcNm }}</div>
                                        <div class="req-meta">
                                            <span>{{ $req['client_name'] ?? '—' }}</span><div class="dot"></div>
                                            <span>{{ $entNm }}</span><div class="dot"></div>
                                            <span>{{ $req['ref_number'] ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="req-time">{{ $dateStr }}</div>
                                    <div class="req-st {{ $req['status'] }}">{{ $st['label'] }}</div>
                                    <i class="ti ti-chevron-down req-chv"></i>
                                </div>
                                <div class="req-body">
                                    <div class="req-dg">
                                        <div class="rd"><div class="rd-l">الاسم</div><div class="rd-v">{{ $req['client_name'] ?? '—' }}</div></div>
                                        <div class="rd"><div class="rd-l">البريد</div><div class="rd-v">{{ $req['client_email'] ?? '—' }}</div></div>
                                        <div class="rd"><div class="rd-l">الجوال</div><div class="rd-v">{{ $req['client_phone'] ?? '—' }}</div></div>
                                        <div class="rd"><div class="rd-l">الهوية</div><div class="rd-v">{{ $req['client_id_number'] ?? '—' }}</div></div>
                                        @if(!empty($req['company_name']))
                                            <div class="rd"><div class="rd-l">الشركة</div><div class="rd-v">{{ $req['company_name'] }}</div></div>
                                        @endif
                                        <div class="rd"><div class="rd-l">المبلغ</div><div class="rd-v">{{ $req['price'] ?? 0 }} ر.س</div></div>
                                    </div>
                                    <div class="time-row">
                                        <x-ui.input id="ti-{{ $i }}" type="text" value="{{ $req['estimated_completion'] ?? '' }}" placeholder="الوقت المتوقع" class="time-inp focus:ring-0!" />
                                        <x-ui.button type="button" class="time-btn focus:ring-0!" onclick="setTime({{ $i }},'{{ $reqId }}')">حفظ الوقت</x-ui.button>
                                    </div>
                                    <div class="st-actions">
                                        @if($isClosed)
                                            {{-- طلب مغلق: لا إجراءات حالة، تُعرض شارة النتيجة فقط --}}
                                            <span class="req-closed-flag {{ $reqStatus }}">
                                                <i class="ti {{ $reqStatus === 'done' ? 'ti-circle-check' : 'ti-x' }}"></i>
                                                {{ $reqStatus === 'done' ? 'اكتمل الطلب' : 'الطلب مرفوض' }}
                                            </span>
                                        @elseif($isOpen)
                                            {{-- الخطوة المنطقية التالية في رحلة الطلب (إجراء رئيسي بارز) --}}
                                            @if($next)
                                                <x-ui.button type="button" class="sa {{ $next[2] }} sa-next focus:ring-0!" onclick="updStatus({{ $i }},'{{ $reqId }}','{{ $next[0] }}')"><i class="ti {{ $next[3] }}"></i>{{ $next[1] }}</x-ui.button>
                                            @endif
                                            {{-- اختصار للخطوات المتقدمة في نفس المرحلة الجارية --}}
                                            @if($reqStatus === 'processing')
                                                <x-ui.button type="button" class="sa done focus:ring-0!" onclick="updStatus({{ $i }},'{{ $reqId }}','done')"><i class="ti ti-circle-check"></i>مكتمل</x-ui.button>
                                            @endif
                                            <span class="sa-sep"></span>
                                            <x-ui.button type="button" class="sa rej focus:ring-0!" onclick="togRejArea({{ $i }})"><i class="ti ti-x"></i>رفض</x-ui.button>
                                            <x-ui.button type="button" class="sa note focus:ring-0!" onclick="togNoteArea({{ $i }})"><i class="ti ti-message"></i>ملاحظة</x-ui.button>
                                            <x-ui.button type="button" class="sa info focus:ring-0!" onclick="togInfoArea({{ $i }})"><i class="ti ti-info-circle"></i>طلب معلومات</x-ui.button>
                                            @if($canAssign)
                                                <x-ui.button type="button" class="sa assign focus:ring-0!" onclick="togAssignArea({{ $i }})"><i class="ti ti-user-check"></i>{{ $fulfillLbl }}</x-ui.button>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="assign-area" id="aa-{{ $i }}" style="{{ $needFulfill ? '' : 'display:none' }}">
                                        <div class="assign-toggle">
                                            <x-ui.button type="button" class="at at-yes focus:ring-0!" onclick="takeInternal('{{ $reqId }}')"><i class="ti ti-building"></i>إنجاز من المنصة</x-ui.button>
                                            @if($isPooled)
                                                <x-ui.button type="button" class="at at-off focus:ring-0!" onclick="togBroadcastPick({{ $i }},'{{ $reqId }}')"><i class="ti ti-broadcast"></i>بث لشبكة المكاتب</x-ui.button>
                                            @else
                                                <x-ui.button type="button" class="at at-off focus:ring-0!" onclick="togOfficePick({{ $i }},'{{ $reqId }}')"><i class="ti ti-building-bank"></i>إسناد لمكتب مساند</x-ui.button>
                                            @endif
                                        </div>
                                        @if($isPooled)
                                            <div class="assign-offices" id="bo-{{ $i }}" style="display:none">
                                                <p class="assign-hint">المكاتب المؤهلة لشبكة المكاتب — اختر للبث:</p>
                                                <div class="office-list" id="bl-{{ $i }}"></div>
                                            </div>
                                        @else
                                            <div class="assign-offices" id="ao-{{ $i }}" style="display:none">
                                                <p class="assign-hint">اختر المكتب المساند:</p>
                                                <div class="office-list" id="ol-{{ $i }}"></div>
                                            </div>
                                        @endif
                                    </div>
                                    @php
                                        $officeData = $req['office'] ?? null;
                                        $officeStatus = $req['office_status'] ?? null;
                                        $showOfficeInfo = !empty($officeData);
                                    @endphp
                                    <div class="assign-info" id="ai-{{ $i }}" style="display:{{ $showOfficeInfo ? 'flex' : 'none' }}">
                                        <i class="ti ti-building"></i>
                                        <span>
                                            @if($showOfficeInfo)
                                                {{ $officeData['name_ar'] ?? $officeData['name_en'] ?? '' }}
                                                @if($officeStatus)
                                                    — {{ $assignStatusMap[$officeStatus] ?? $officeStatus }}
                                                @else
                                                    — بانتظار قبول المكتب
                                                @endif
                                            @endif
                                        </span>
                                    </div>
                                    <div class="rej-area" id="ra-{{ $i }}">
                                        <x-ui.textarea id="rt-{{ $i }}" placeholder="اكتب سبب الرفض..." rows="2" class="focus:ring-0!"></x-ui.textarea>
                                        <x-ui.button type="button" class="rej-send focus:ring-0!" onclick="sendRej({{ $i }},'{{ $reqId }}')"><i class="ti ti-send"></i>إرسال الرفض</x-ui.button>
                                    </div>
                                    <div class="note-area" id="na-{{ $i }}">
                                        <x-ui.textarea id="nt-{{ $i }}" placeholder="اكتب ملاحظة للمستخدم..." rows="2" class="focus:ring-0!"></x-ui.textarea>
                                        <x-ui.button type="button" class="note-send focus:ring-0!" onclick="doSendNote({{ $i }},'{{ $reqId }}')"><i class="ti ti-send"></i>إرسال الملاحظة</x-ui.button>
                                    </div>
                                    <div class="info-area" id="ia-{{ $i }}">
                                        <x-ui.textarea id="it-{{ $i }}" placeholder="اكتب ما تحتاجه من معلومات..." rows="2" class="focus:ring-0!"></x-ui.textarea>
                                        <x-ui.button type="button" class="info-send focus:ring-0!" onclick="doRequestInfo({{ $i }},'{{ $reqId }}')"><i class="ti ti-send"></i>إرسال طلب المعلومات</x-ui.button>
                                    </div>
                                    @if(count($logs) > 0)
                                        <div class="req-log-ttl"><i class="ti ti-history text-emerald-500"></i>السجل</div>
                                        @foreach($logs as $log)
                                            @php
                                                $logSt = $statusMap[$log['status']] ?? ['label' => $log['status'] ?? '—', 'color' => '#999'];
                                                $logNote = $log['note'] ?? $logSt['label'];
                                                $logDate = $log['created_at'] ?? null;
                                                $logDateStr = $logDate ? \Carbon\Carbon::parse($logDate)->locale('ar')->isoFormat('D MMM YYYY') : '';
                                            @endphp
                                            <div class="log-row">
                                                <div class="log-dot" style="background:{{ $logSt['color'] }}"></div>
                                                <div class="log-txt">{{ $logNote }}</div>
                                                <div class="log-time">{{ $logDateStr }}</div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center text-sm text-slate-500">لا توجد طلبات</div>
                        @endforelse
                    </div>
                </div>


@endsection