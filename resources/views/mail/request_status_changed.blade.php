<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>تحديث طلبك - آمر تم</title>
<style>
  body{font-family:'Arial',sans-serif;background:#f8fafc;margin:0;padding:20px;direction:rtl;}
  .wrap{max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1);}
  .hd{background:linear-gradient(135deg,#059669,#16a34a);padding:32px 28px;text-align:center;}
  .hd h1{color:#fff;font-size:22px;margin:0;font-weight:900;}
  .hd p{color:rgba(255,255,255,.75);font-size:13px;margin:6px 0 0;}
  .bd{padding:28px;}
  .greeting{font-size:16px;color:#059669;font-weight:700;margin-bottom:16px;}
  .status-box{border-radius:12px;padding:16px 20px;margin:16px 0;border:1.5px solid;}
  .ref{font-size:13px;color:#64748b;margin-bottom:8px;}
  .status-lbl{font-size:18px;font-weight:900;}
  .note-box{background:#f8fafc;border-radius:10px;padding:14px 16px;margin-top:14px;font-size:14px;color:#334155;line-height:1.6;}
  .ft{padding:20px 28px;background:#f8fafc;text-align:center;font-size:11.5px;color:#9BA3BF;border-top:1px solid rgba(5,150,105,.08);}
  .btn{display:inline-block;margin-top:18px;padding:12px 28px;background:#059669;color:#fff;border-radius:10px;text-decoration:none;font-size:14px;font-weight:700;}
</style>
</head>
<body>
<div class="wrap">
  <div class="hd">
    <h1>آمر تم</h1>
    <p>منصة الخدمات الحكومية والتجارية</p>
  </div>
  <div class="bd">
    <div class="greeting">مرحباً {{ $serviceRequest->client_name }}،</div>
    <p style="color:#334155;font-size:14px;line-height:1.7;">
      نود إعلامك بأنه تم تحديث حالة طلبك المقدم عبر منصة آمر تم.
    </p>

    @php
      $colors = [
        'قيد الانتظار'          => ['border'=>'#E65100','bg'=>'rgba(230,81,0,.08)','color'=>'#E65100'],
        'جاري المعالجة'         => ['border'=>'#0277BD','bg'=>'rgba(2,119,189,.08)','color'=>'#0277BD'],
        'قيد التنفيذ'            => ['border'=>'#F9A825','bg'=>'rgba(249,168,37,.08)','color'=>'#F9A825'],
        'تمت العملية'           => ['border'=>'#047857','bg'=>'rgba(4,120,87,.08)','color'=>'#047857'],
        'مرفوض'                  => ['border'=>'#dc2626','bg'=>'rgba(220,38,38,.08)','color'=>'#dc2626'],
        'ملاحظة من الإدارة'     => ['border'=>'#6A1B9A','bg'=>'rgba(106,27,154,.08)','color'=>'#6A1B9A'],
        'مطلوب معلومات إضافية' => ['border'=>'#E65100','bg'=>'rgba(230,81,0,.08)','color'=>'#E65100'],
      ];
      $c = $colors[$statusLabel] ?? ['border'=>'#059669','bg'=>'rgba(5,150,105,.06)','color'=>'#059669'];
    @endphp

    <div class="status-box" style="border-color:{{ $c['border'] }};background:{{ $c['bg'] }};">
      <div class="ref">رقم الطلب: <strong>{{ $serviceRequest->ref_number }}</strong></div>
      <div class="status-lbl" style="color:{{ $c['color'] }};">{{ $statusLabel }}</div>
      @if($serviceRequest->reject_reason)
        <div style="margin-top:8px;font-size:13px;color:#dc2626;">سبب الرفض: {{ $serviceRequest->reject_reason }}</div>
      @endif
      @if($serviceRequest->estimated_completion)
        <div style="margin-top:6px;font-size:13px;color:#334155;">الوقت المتوقع: {{ $serviceRequest->estimated_completion }}</div>
      @endif
    </div>

    @if($adminNote)
      <div class="note-box">
        <strong>رسالة من الإدارة:</strong><br/>
        {{ $adminNote }}
      </div>
    @endif

    <div style="text-align:center;">
      <a href="{{ url('/amrtm/dashboard') }}" class="btn">عرض طلباتي</a>
    </div>
  </div>
  <div class="ft">
    هذا البريد أُرسل تلقائياً من منصة آمر تم.<br/>
    إذا كان لديك استفسار، تواصل معنا عبر المنصة.
  </div>
</div>
</body>
</html>
