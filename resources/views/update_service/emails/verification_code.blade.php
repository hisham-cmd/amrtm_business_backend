<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كود التوقيع على العقد</title>
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
        .body { padding: 24px; text-align: center; }
        .body p { line-height: 1.8; margin: 0 0 12px; font-size: 14px; }
        .code-box {
            display: inline-block;
            margin: 20px 0;
            padding: 16px 32px;
            background: #f1f5f9;
            border: 2px dashed #2563eb;
            border-radius: 12px;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #0f172a;
            direction: ltr;
        }
        .note {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 12px;
            margin: 16px 0;
            font-size: 12px;
            color: #92400e;
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
            <h1>كود التوقيع على العقد</h1>
            <p>عقد رقم {{ $contractNumber }}</p>
        </div>
        <div class="body">
            <p>السلام عليكم،</p>
            <p>
                تم طلب كود التوقيع على العقد رقم <strong>{{ $contractNumber }}</strong>.
                استخدم الكود التالي لإتمام التوقيع:
            </p>

            <div class="code-box">{{ $code }}</div>

            <div class="note">
                ⚠️ هذا الكود صالح لمدة <strong>5 دقائق</strong> فقط ولا يمكن استخدامه أكثر من مرة.
                <br>إذا لم تطلب هذا الكود، تجاهل هذه الرسالة.
            </div>
        </div>
        <div class="footer">
            هذه رسالة آلية أُرسلت من منصة آمر تم للخدمات التجارية. يرجى عدم الرد على هذا البريد.
        </div>
    </div>
</body>
</html>
