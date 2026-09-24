<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; }
        html, body {
            font-family: 'Tajawal', 'DejaVu Sans', sans-serif;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.8;
        }
        /* Keep container LTR so pre-shaped Arabic glyphs are NOT re-reversed.
           RTL appearance is achieved with right alignment + cell ordering. */
        body {
            direction: ltr;
            text-align: right;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header table { width: 100%; border-collapse: collapse; }
        .header table td { vertical-align: top; padding: 0; }
        .brandcell { text-align: right; }
        .brand { font-size: 18px; font-weight: bold; color: #0f172a; }
        .sub { font-size: 11px; color: #475569; margin-top: 2px; }
        .contract-no {
            text-align: left;
            direction: ltr;
            font-size: 12px;
            white-space: nowrap;
        }
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 18px 0;
            padding: 10px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0;
        }
        .meta-table tr:nth-child(odd) { background: #fbfbfc; }
        .meta-table td {
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            font-size: 12px;
        }
        .meta-table .mlabel {
            width: 30%;
            font-weight: bold;
            background: #f1f5f9;
            color: #0f172a;
            text-align: right;
        }
        .meta-table .mval {
            text-align: left;
            direction: ltr;
        }
        .clause {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 10px;
            padding: 10px 12px;
            text-align: right;
        }
        .clause .cname {
            font-weight: bold;
            font-size: 13px;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .clause .cdesc { font-size: 12px; color: #334155; }
        .section-ttl {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            margin: 16px 0 8px;
            border-right: 4px solid #0f172a;
            padding-right: 8px;
        }
        .qr-block {
            margin-top: 24px;
            text-align: center;
        }
        .qr-note { font-size: 11px; color: #64748b; margin-bottom: 6px; text-align: center; }
        .qr-img { width: 160px; height: 160px; }
        .footer {
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }
        .parties {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }
        .parties td {
            border: 1px solid #e2e8f0;
            padding: 14px;
            text-align: center;
            width: 50%;
            height: 90px;
            vertical-align: top;
            font-size: 12px;
        }
        .sign-name { margin-top: 30px; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="width:50%;" class="contract-no">
                    {{ $ar('عقد رقم') }}: {{ $contract->number }}
                </td>
                <td style="width:50%;" class="brandcell">
                    <div class="brand">{{ $ar('آمر تم للخدمات التجارية') }}</div>
                    <div class="sub">{{ $ar('عقد إلكتروني معتمد') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="title">{{ $ar($contract->type_name ?? '') }}</div>

    <table class="meta-table">
        <tr>
            <td class="mlabel">{{ $ar('رقم العقد') }}</td>
            <td class="mval">{{ $contract->number }}</td>
        </tr>
        <tr>
            <td class="mlabel">{{ $ar('نوع العقد') }}</td>
            <td class="mval">{{ $ar($contract->type_name ?? '') }}</td>
        </tr>
        <tr>
            <td class="mlabel">{{ $ar('الطرف الثاني') }}</td>
            <td class="mval">{{ $ar($contract->party_name ?? '') }}</td>
        </tr>
        <tr>
            <td class="mlabel">{{ $ar('الحالة') }}</td>
            <td class="mval">{{ $ar($contract->status ?? '') }}</td>
        </tr>
        <tr>
            <td class="mlabel">{{ $ar('تاريخ البدء') }}</td>
            <td class="mval">{{ $ar($contract->start_date ?? '') }}</td>
        </tr>
        <tr>
            <td class="mlabel">{{ $ar('تاريخ الانتهاء') }}</td>
            <td class="mval">{{ $ar($contract->end_date ?? '') }}</td>
        </tr>
        @if($contract->price !== null)
        <tr>
            <td class="mlabel">{{ $ar('قيمة العقد') }}</td>
            <td class="mval">{{ $ar(number_format((float) $contract->price)) }} <span style="direction:ltr;">ر.س</span></td>
        </tr>
        @endif
    </table>

    @if($contract->description)
        <div class="clause">
            <div class="cname">{{ $ar('وصف العقد') }}</div>
            <div class="cdesc">{{ $ar($contract->description) }}</div>
        </div>
    @endif

    <div class="section-ttl">{{ $ar('بنود العقد') }}</div>

    @foreach($clauses as $index => $clause)
        <div class="clause">
            <div class="cname">{{ $index + 1 }}. {{ $ar($clause['name']) }}</div>
            <div class="cdesc">{{ $ar($clause['rendered'] ?: ($clause['description'] ?? '')) }}</div>
        </div>
    @endforeach

    <table class="parties">
        <tr>
            <td>
                <div><strong>{{ $ar('الطرف الأول') }}</strong></div>
                <div class="sign-name">{{ $ar('التوقيع') }}</div>
            </td>
            <td>
                <div><strong>{{ $ar('الطرف الثاني') }}</strong> — {{ $ar($contract->party_name ?? '') }}</div>
                <div class="sign-name">{{ $ar('التوقيع') }}</div>
            </td>
        </tr>
    </table>

    @if($qr)
    <div class="qr-block">
        <div class="qr-note">{{ $ar('امسح الرمز للاطلاع على النسخة الإلكترونية المعتمدة من العقد') }}</div>
        {!! $qr !!}
    </div>
    @endif

    <div class="footer">
        {{ $ar('تم إصدار هذا العقد إلكترونياً عبر منصة آمر تم للخدمات التجارية.') }}<br>
        {{ $ar('راجع النسخة الإلكترونية عبر الرمز أعلاه للتحقق من صحته.') }}
    </div>
</body>
</html>
