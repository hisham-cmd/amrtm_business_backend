<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عقد رقم {{ $contract->number }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f1f5f9;
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            color: #1e293b;
        }
        .wrapper {
            max-width: 600px;
            margin: 24px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background: #0f172a;
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 6px 0 0; opacity: .85; font-size: 13px; }
        .body { padding: 24px; }
        .body p { line-height: 1.8; margin: 0 0 12px; font-size: 14px; }
        .btn {
            display: inline-block;
            margin: 16px 0;
            padding: 12px 28px;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
        }
        .meta {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px;
            margin: 16px 0;
            font-size: 13px;
        }
        .footer {
            padding: 16px 24px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>عقد رقم {{ $contract->number }}</h1>
            <p>{{ $contract->type_name }}</p>
        </div>
        <div class="body">
            <p>السلام عليكم،</p>
            <p>
                تم إصدار عقد إلكتروني باسمكم. يمكنكم الاطلاع على تفاصيل العقد كاملة من خلال
                الرابط أدناه، حيث ستجدون جميع البنود وبيانات العقد.
            </p>

            <div class="meta">
                <div><strong>رقم العقد:</strong> {{ $contract->number }}</div>
                <div><strong>نوع العقد:</strong> {{ $contract->type_name }}</div>
                <div><strong>الطرف الثاني:</strong> {{ $contract->party_name }}</div>
                <div><strong>تاريخ البدء:</strong> {{ $contract->start_date }}</div>
                <div><strong>تاريخ الانتهاء:</strong> {{ $contract->end_date }}</div>
            </div>

            <p style="text-align:center;">
                <a href="{{ $link }}" class="btn" style="color:#ffffff;">عرض العقد والتوقيع</a>
            </p>

            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px;margin:16px 0;font-size:13px;text-align:center;color:#1d4ed8;">
                <strong>ملاحظة مهمة:</strong> لتوقيع العقد، يلزم تسجيل الدخول أو إنشاء حساب جديد في المنصة.
                <br>يمكنك التسجيل مجاناً من خلال الرابط أعلاه.
            </div>

            <p style="font-size:12px;color:#64748b;">
                هذا الرابط صالح لمدة 30 يوماً. عند انتهاء الصلاحية، يرجى التواصل مع مكتب إصدار العقد لإعادة إرساله.
            </p>
        </div>
        <div class="footer">
            هذه رسالة آلية أُرسلت من منصة آمرتم للخدمات التجارية. يرجى عدم الرد على هذا البريد.
        </div>
    </div>
</body>
</html>
