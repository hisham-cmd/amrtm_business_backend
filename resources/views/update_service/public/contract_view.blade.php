<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>عقد رقم {{ $contract->number }}</title>
    <style>
        * { box-sizing: border-box; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }
        html, body {
            margin: 0;
            padding: 0;
            background: #0f172a;
            -webkit-touch-callout: none;
            -webkit-user-drag: none;
        }
        .topbar {
            background: #0f172a;
            color: #fff;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .topbar .brand { font-weight: 700; font-size: 16px; }
        .topbar .badge {
            background: #1e293b;
            border: 1px solid #334155;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            color: #93c5fd;
        }
        .container {
            max-width: 820px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,.25);
        }
        .page-title {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            padding-bottom: 14px;
            border-bottom: 2px solid #0f172a;
            margin-bottom: 16px;
        }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .meta-table td {
            border: 1px solid #e2e8f0;
            padding: 9px 12px;
            font-size: 13px;
        }
        .meta-table .label { background: #f8fafc; font-weight: 600; width: 30%; }
        .clause {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 12px;
            background: #fcfcfd;
        }
        .clause .cname { font-weight: 700; font-size: 14px; color: #0f172a; margin-bottom: 4px; }
        .clause .cdesc { font-size: 13px; color: #334155; line-height: 1.9; }
        .notice {
            margin-top: 18px;
            padding: 12px 14px;
            border-radius: 8px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            font-size: 12px;
            text-align: center;
        }
        .footer {
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            padding: 18px;
        }
        .watermark {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            pointer-events: none;
            z-index: 5;
            background:
                radial-gradient(ellipse at center,
                    transparent 50%,
                    rgba(255,255,255,.05) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: .4;
        }
        .watermark span {
            font-size: 34px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 4px;
            writing-mode: horizontal-tb;
            transform: rotate(-18deg);
            white-space: nowrap;
        }
        .readonly {
            background: #f1f5f9;
            border: 1px dashed #94a3b8;
            color: #475569;
            text-align: center;
            font-size: 12px;
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        @media print {
            .readonly, .notice, .watermark { display: none; }
            body { background: #fff; }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="brand">آمر تم للخدمات التجارية</div>
        <div class="badge">عرض للطرف الثاني — للاطلاع فقط</div>
    </div>

    <div class="watermark"><span>{{ $contract->party_name }}</span></div>

    <div class="container">
        <div class="card">
            <div class="readonly">
                هذه نسخة إلكترونية صادرة للطرف الثاني للاطلاع فقط — لا يمكن تعديل أو نسخ أو طباعة محتواها.
            </div>

            <div class="page-title">عقد رقم {{ $contract->number }}</div>

            <table class="meta-table">
                <tr>
                    <td class="label">نوع العقد</td>
                    <td>{{ $contract->type_name }}</td>
                </tr>
                <tr>
                    <td class="label">الطرف الثاني</td>
                    <td>{{ $contract->party_name }}</td>
                </tr>
                <tr>
                    <td class="label">تاريخ البدء</td>
                    <td>{{ $contract->start_date }}</td>
                    <td class="label">تاريخ الانتهاء</td>
                    <td>{{ $contract->end_date }}</td>
                </tr>
                @if($contract->price !== null)
                <tr>
                    <td class="label">قيمة العقد</td>
                    <td colspan="3">{{ number_format((float) $contract->price) }}</td>
                </tr>
                @endif
            </table>

            @if($contract->description)
                <div class="clause">
                    <div class="cname">وصف العقد</div>
                    <div class="cdesc">{{ $contract->description }}</div>
                </div>
            @endif

            <div style="font-weight:700;font-size:14px;margin:16px 0 10px;color:#0f172a;">بنود العقد</div>

            @foreach($clauses as $index => $clause)
                <div class="clause">
                    <div class="cname">{{ $index + 1 }}. {{ $clause['name'] }}</div>
                    <div class="cdesc">{{ $clause['rendered'] ?: ($clause['description'] ?? '') }}</div>
                </div>
            @endforeach

            <!-- Signing Status -->
            <div id="signing-section" style="margin-top:20px;">
                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
                    <div style="flex:1;min-width:200px;padding:12px;border-radius:8px;text-align:center;font-size:13px;
                        {{ $contract->party1_signed_at ? 'background:#dcfce7;border:1px solid #86efac;color:#166534;' : 'background:#fef3c7;border:1px solid #fbbf24;color:#92400e;' }}">
                        <strong>الطرف الأول</strong><br>
                        {{ $contract->party1_signed_at ? '✓ تم التوقيع' : '⏳ في انتظار التوقيع' }}
                    </div>
                    <div style="flex:1;min-width:200px;padding:12px;border-radius:8px;text-align:center;font-size:13px;
                        {{ $contract->party2_signed_at ? 'background:#dcfce7;border:1px solid #86efac;color:#166534;' : 'background:#fef3c7;border:1px solid #fbbf24;color:#92400e;' }}">
                        <strong>الطرف الثاني</strong><br>
                        {{ $contract->party2_signed_at ? '✓ تم التوقيع' : '⏳ في انتظار التوقيع' }}
                    </div>
                </div>

                @if($contract->party1_signed_at && ! $contract->party2_signed_at)
                    <div style="text-align:center;margin-bottom:16px;">
                        <button id="btn-request-code" onclick="requestSignCode()"
                            style="padding:14px 32px;background:#2563eb;color:#fff;border:none;border-radius:10px;
                                   font-size:16px;font-weight:700;cursor:pointer;transition:background .2s;"
                            onmouseover="this.style.style='background:#1d4ed8'"
                            onmouseout="this.style.background='#2563eb'">
                            التوقيع على العقد
                        </button>
                    </div>

                    <!-- Verification Code Form -->
                    <div id="code-form" style="display:none;text-align:center;margin-bottom:16px;">
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px;max-width:400px;margin:0 auto;">
                            <p style="margin:0 0 12px;font-size:14px;color:#334155;">
                                تم إرسال كود التوقيع إلى بريدك الإلكتروني
                            </p>
                            <input id="code-input" type="text" maxlength="6" placeholder="000000"
                                style="width:200px;padding:12px;text-align:center;font-size:24px;font-weight:700;
                                       border:2px solid #e2e8f0;border-radius:8px;letter-spacing:8px;direction:ltr;"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                            <div style="margin-top:12px;display:flex;gap:8px;justify-content:center;">
                                <button id="btn-verify" onclick="verifyCode()"
                                    style="padding:10px 24px;background:#16a34a;color:#fff;border:none;border-radius:8px;
                                           font-size:14px;font-weight:600;cursor:pointer;">
                                    تأكيد التوقيع
                                </button>
                                <button onclick="requestSignCode()"
                                    style="padding:10px 24px;background:#e2e8f0;color:#475569;border:none;border-radius:8px;
                                           font-size:14px;font-weight:600;cursor:pointer;">
                                    إعادة الإرسال
                                </button>
                            </div>
                            <p id="code-message" style="margin:12px 0 0;font-size:13px;display:none;"></p>
                        </div>
                    </div>
                @endif

                @if($contract->party2_signed_at)
                    <div style="text-align:center;background:#dcfce7;border:1px solid #86efac;border-radius:12px;padding:20px;">
                        <p style="margin:0;font-size:18px;font-weight:700;color:#166534;">
                            ✓ تم توقيع العقد بنجاح من الطرفين
                        </p>
                        <p style="margin:8px 0 0;font-size:13px;color:#166534;">
                            هذا العقد مُوقَّع إلكترونياً ومعتمد من الطرفين
                        </p>
                    </div>
                @endif

                @if(! $contract->party1_signed_at)
                    <div class="notice">
                        هذا العقد في انتظار توقيع الطرف الأول قبل أن تتمكن من التوقيع.
                    </div>
                @endif
            </div>

            <div class="notice" style="margin-top:12px;">
                هذا العقد صادر إلكترونياً ومعتمد. للتحقق من صحته، يرجى التواصل مع المكتب المُصدر.
            </div>
        </div>

        <div class="footer">
            آمر تم للخدمات التجارية — جميع الحقوق محفوظة
        </div>
    </div>

    <script>
        const CONTRACT_TOKEN = '{{ $token }}';
        const BASE_URL = window.location.origin;

        async function requestSignCode() {
            const btn = document.getElementById('btn-request-code');
            if (btn) { btn.disabled = true; btn.textContent = 'جاري الإرسال...'; }

            try {
                const res = await fetch(BASE_URL + '/contract-view/' + CONTRACT_TOKEN + '/request-sign-code', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (!res.ok) throw data;

                document.getElementById('code-form').style.display = 'block';
                if (btn) { btn.style.display = 'none'; }
            } catch (e) {
                if (window.AmrtmNotify) AmrtmNotify.error(e.message || 'حدث خطأ أثناء إرسال الكود'); else alert(e.message || 'حدث خطأ أثناء إرسال الكود');
                if (btn) { btn.disabled = false; btn.textContent = 'التوقيع على العقد'; }
            }
        }

        async function verifyCode() {
            const code = document.getElementById('code-input').value.trim();
            const msgEl = document.getElementById('code-message');
            const btn = document.getElementById('btn-verify');

            if (code.length !== 6) {
                msgEl.style.display = 'block';
                msgEl.style.color = '#dc2626';
                msgEl.textContent = 'أدخل كود مكون من 6 أرقام';
                return;
            }

            btn.disabled = true;
            btn.textContent = 'جاري التحقق...';

            try {
                const res = await fetch(BASE_URL + '/contract-view/' + CONTRACT_TOKEN + '/verify-sign-code', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ code: code })
                });
                const data = await res.json();
                if (!res.ok) throw data;

                msgEl.style.display = 'block';
                msgEl.style.color = '#16a34a';
                msgEl.textContent = '✓ ' + data.message;
                setTimeout(() => location.reload(), 1500);
            } catch (e) {
                msgEl.style.display = 'block';
                msgEl.style.color = '#dc2626';
                msgEl.textContent = e.message || 'كود خاطئ';
                btn.disabled = false;
                btn.textContent = 'تأكيد التوقيع';
            }
        }

        (function () {
            function block(e) {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                return false;
            }

            document.addEventListener('contextmenu', block);
            document.addEventListener('copy', block);
            document.addEventListener('cut', block);
            document.addEventListener('paste', block);
            document.addEventListener('dragstart', block);
            document.addEventListener('drop', block);

            var down = 0;
            document.addEventListener('mousedown', block);
            document.addEventListener('mouseup', block);
            document.addEventListener('selectstart', block);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'PrintScreen' || e.key === 'Print') {
                    block(e);
                }
                if (e.ctrlKey && (e.key === 'p' || e.key === 'P')) {
                    block(e);
                }
                if (e.ctrlKey && (e.key === 'u' || e.key === 'U')) {
                    block(e);
                }
                if ((e.ctrlKey || e.metaKey) && ['c','x','s','a'].indexOf((e.key||'').toLowerCase()) !== -1) {
                    block(e);
                }
            });

            document.addEventListener('touchstart', function (e) {
                if (e.touches && e.touches.length > 1) block(e);
            }, { passive: false });
        })();
    </script>
</body>
</html>
