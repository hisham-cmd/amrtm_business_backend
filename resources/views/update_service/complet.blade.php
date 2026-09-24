<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>استكمال بيانات المكتب</title>

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

<style>

/* =====================================================
   RESET
===================================================== */

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: "Cairo", sans-serif;
    background: #f3f6f9;
    color: #173b4d;
    min-height: 100vh;
}

button,
input,
select,
textarea {
    font-family: inherit;
}


/* =====================================================
   PAGE
===================================================== */

.complete-page {
    width: 100%;
    min-height: 100vh;
    padding: 35px 20px 80px;
}

.complete-container {
    width: min(1200px, 100%);
    margin: auto;
}


/* =====================================================
   ALERT
===================================================== */

.alert {
    padding: 15px 20px;
    border-radius: 14px;
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 600;
}

.alert-error {
    background: #fff0f0;
    color: #a93232;
    border: 1px solid #f1cccc;
}

.alert-info {
    background: #eef7fb;
    color: #17627a;
    border: 1px solid #cfe5ee;
}

.alert-success {
    background: #eaf8f0;
    color: #187a48;
    border: 1px solid #c9ecd8;
}

.alert ul {
    padding-right: 20px;
}

.alert li {
    margin-bottom: 5px;
}


/* =====================================================
   HEADER
===================================================== */

.page-header {
    background: linear-gradient(
        135deg,
        #125568,
        #176d7d
    );

    color: #fff;

    border-radius: 22px;

    padding: 40px 30px;

    text-align: center;

    box-shadow:
        0 15px 35px rgba(18,85,104,.15);

    margin-bottom: 25px;
}

.page-header-icon {
    width: 62px;
    height: 62px;

    margin: 0 auto 15px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 16px;

    background: rgba(255,255,255,.14);

    font-size: 30px;
}

.page-header h1 {
    font-size: 30px;
    font-weight: 800;
    margin-bottom: 8px;
}

.page-header p {
    font-size: 15px;
    color: rgba(255,255,255,.9);
}


/* =====================================================
   STATUS
===================================================== */

.status-card {
    background: #fff;

    border-radius: 18px;

    padding: 24px;

    margin-bottom: 25px;

    text-align: center;

    box-shadow:
        0 8px 25px rgba(0,0,0,.05);
}

.status-title {
    font-size: 15px;
    font-weight: 800;
    margin-bottom: 10px;
}

.status-badge {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    padding: 8px 22px;

    border-radius: 30px;

    background: #e8f8fc;

    color: #08708b;

    font-size: 14px;

    font-weight: 700;
}


/* =====================================================
   SECTION
===================================================== */

.section-card {
    background: #fff;

    border-radius: 20px;

    padding: 30px;

    margin-bottom: 25px;

    box-shadow:
        0 8px 25px rgba(0,0,0,.05);
}

.section-header {
    display: flex;

    align-items: center;

    gap: 15px;

    padding-bottom: 20px;

    margin-bottom: 25px;

    border-bottom: 1px solid #edf0f2;
}

.section-title {
    display: flex;

    align-items: center;

    gap: 12px;

    color: #125568;

    font-size: 21px;

    font-weight: 800;
}

.section-icon {
    width: 42px;
    height: 42px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: #eaf5f8;

    color: #125568;

    font-size: 20px;
}


/* =====================================================
   CURRENT OFFICE DATA
===================================================== */

.office-data-grid {
    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 15px;
}

.data-item {

    background: #f8fafb;

    border: 1px solid #e5edef;

    border-radius: 14px;

    padding: 17px 18px;

    min-height: 82px;

    display: flex;

    flex-direction: column;

    justify-content: center;

    gap: 6px;
}

.data-label {

    color: #718992;

    font-size: 12px;

    font-weight: 700;
}

.data-value {

    color: #173b4d;

    font-size: 15px;

    font-weight: 800;

    word-break: break-word;
}


/* =====================================================
   FORM
===================================================== */

.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 20px;
}

.form-group {

    display: flex;

    flex-direction: column;

    gap: 8px;
}

.form-group.full {
    grid-column: 1 / -1;
}

.form-group label {

    color: #173b4d;

    font-size: 14px;

    font-weight: 800;
}

.form-group input,
.form-group select {

    width: 100%;

    border: 1px solid #dce6ea;

    background: #fff;

    border-radius: 12px;

    padding: 13px 15px;

    color: #173b4d;

    font-size: 14px;

    outline: none;

    transition: .2s ease;
}

.form-group input:focus,
.form-group select:focus {

    border-color: #176d7d;

    box-shadow:
        0 0 0 3px rgba(23,109,125,.08);
}

.form-hint {

    color: #8a9ba3;

    font-size: 12px;
}

.field-error {

    color: #c0392b;

    font-size: 12px;

    font-weight: 700;
}


/* =====================================================
   SPECIALTY
===================================================== */

.specialty-section {
    width: 100%;
}


/* صندوق تخصصات قاعدة البيانات */

.specialty-box {

    background: #f8fafb;

    border: 1px solid #e3ecef;

    border-radius: 16px;

    padding: 22px;
}

.specialty-box-title {

    color: #125568;

    font-size: 16px;

    font-weight: 800;

    margin-bottom: 18px;
}


/*
   مهم:
   6 تخصصات في الصف
*/

.specialties-list {

    display: grid;

    grid-template-columns:
        repeat(6, minmax(0, 1fr));

    gap: 10px;
}


/* عنصر التخصص */

.specialty-option {
    position: relative;
}


/* إخفاء الراديو الأصلي */

.specialty-option input {

    position: absolute;

    opacity: 0;

    pointer-events: none;
}


/* شكل التخصص */

.specialty-option label {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 8px;

    min-height: 52px;

    padding: 10px 12px;

    background: #fff;

    border: 1px solid #dce7ea;

    border-radius: 11px;

    cursor: pointer;

    color: #385a68;

    font-size: 12px;

    font-weight: 700;

    transition: .2s ease;

    text-align: center;
}


/* الدائرة */

.specialty-option label::after {

    content: "";

    width: 17px;

    height: 17px;

    border-radius: 50%;

    border: 2px solid #b8cbd1;

    flex-shrink: 0;

    transition: .2s ease;
}


/* المختار */

.specialty-option input:checked + label {

    background: #eaf6f8;

    border-color: #176d7d;

    color: #125568;

    box-shadow:
        0 4px 12px rgba(23,109,125,.08);
}


/* دائرة المختار */

.specialty-option input:checked + label::after {

    border-color: #176d7d;

    background: #176d7d;

    box-shadow:
        inset 0 0 0 4px #fff;
}


/* =====================================================
   CUSTOM SPECIALTY
===================================================== */

.custom-specialty-box {

    margin-top: 18px;

    background: #f8fafb;

    border: 1px solid #e3ecef;

    border-radius: 16px;

    padding: 20px;
}

.custom-specialty-box .specialty-box-title {

    margin-bottom: 12px;
}

.custom-specialty-box input {

    width: 100%;

    border: 1px solid #dce6ea;

    border-radius: 12px;

    padding: 13px 15px;

    outline: none;

    background: #fff;

    color: #173b4d;

    font-size: 14px;

    transition: .2s ease;
}

.custom-specialty-box input:focus {

    border-color: #176d7d;

    box-shadow:
        0 0 0 3px rgba(23,109,125,.08);
}


/*
   لا يتم تعطيل الخانة نهائيًا
*/

.custom-specialty-box input:disabled {

    opacity: 1;

    background: #fff;

    cursor: text;
}


/* =====================================================
   DOCUMENTS
===================================================== */

.documents-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 18px;
}

.document-card {

    background: #f8fafb;

    border: 1px solid #e3ecef;

    border-radius: 16px;

    padding: 22px;

    transition: .2s ease;
}

.document-card:hover {

    border-color: #9bbdc6;

    box-shadow:
        0 7px 18px rgba(18,85,104,.06);
}

.document-icon {

    width: 46px;
    height: 46px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: #eaf5f8;

    color: #125568;

    font-size: 22px;

    margin-bottom: 12px;
}

.document-card label {

    display: block;

    color: #173b4d;

    font-size: 15px;

    font-weight: 800;

    margin-bottom: 10px;
}

.document-card input[type="file"] {

    width: 100%;

    padding: 10px;

    border: 1px dashed #aac3cb;

    border-radius: 10px;

    background: #fff;

    color: #527486;

    cursor: pointer;
}

.document-card small {

    display: block;

    margin-top: 8px;

    color: #8a9ba3;

    font-size: 12px;
}

.selected-file-name {

    margin-top: 8px;

    color: #176d7d;

    font-size: 12px;

    font-weight: 700;

    word-break: break-word;
}


/* =====================================================
   ACTION
===================================================== */

.form-actions {

    display: flex;

    justify-content: flex-end;

    gap: 12px;

    margin-top: 25px;
}

.save-button {

    border: none;

    padding: 14px 32px;

    border-radius: 12px;

    background: linear-gradient(
        135deg,
        #125568,
        #176d7d
    );

    color: #fff;

    font-size: 15px;

    font-weight: 800;

    cursor: pointer;

    transition: .2s ease;
}

.save-button:hover {

    transform: translateY(-1px);

    box-shadow:
        0 8px 20px rgba(18,85,104,.18);
}

.save-button:disabled {

    opacity: .7;

    cursor: not-allowed;

    transform: none;
}


/* =====================================================
   SUCCESS
===================================================== */

.success-screen {

    background: #fff;

    border-radius: 22px;

    padding: 60px 30px;

    text-align: center;

    box-shadow:
        0 12px 35px rgba(0,0,0,.06);
}

.success-icon {

    width: 80px;
    height: 80px;

    margin: 0 auto 20px;

    border-radius: 50%;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #eaf8f0;

    color: #187a48;

    font-size: 42px;
}

.success-screen h2 {

    color: #125568;

    font-size: 25px;

    font-weight: 800;

    margin-bottom: 12px;
}

.success-screen p {

    color: #647d88;

    font-size: 15px;

    line-height: 2;

    max-width: 650px;

    margin: auto;
}


/* =====================================================
   RESPONSIVE
===================================================== */


/*
   أقل من 1100:
   4 تخصصات في الصف
*/

@media (max-width: 1100px) {

    .specialties-list {

        grid-template-columns:
            repeat(4, minmax(0, 1fr));
    }

}


/*
   أقل من 900
*/

@media (max-width: 900px) {

    .office-data-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .specialties-list {

        grid-template-columns:
            repeat(3, minmax(0, 1fr));
    }

}


/*
   الموبايل
*/

@media (max-width: 768px) {

    .complete-page {

        padding: 20px 12px 60px;
    }

    .page-header {

        padding: 28px 18px;

        border-radius: 17px;
    }

    .page-header h1 {

        font-size: 23px;
    }

    .page-header p {

        font-size: 13px;
    }

    .section-card {

        padding: 20px 15px;

        border-radius: 16px;
    }

    .section-title {

        font-size: 18px;
    }

    .office-data-grid {

        grid-template-columns: 1fr;
    }

    .form-grid {

        grid-template-columns: 1fr;
    }

    .form-group.full {

        grid-column: auto;
    }

    .specialties-list {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .documents-grid {

        grid-template-columns: 1fr;
    }

    .form-actions {

        flex-direction: column;
    }

    .save-button {

        width: 100%;
    }

}


/*
   شاشة صغيرة جدًا
*/

@media (max-width: 430px) {

    .specialties-list {

        grid-template-columns: 1fr;
    }

}

</style>

</head>


<body>

<div class="complete-page">

<div class="complete-container">


{{-- =====================================================
     MESSAGES
===================================================== --}}

@if($errors->any())

<div class="alert alert-error">

    <ul>

        @foreach($errors->all() as $error)

            <li>
                {{ $error }}
            </li>

        @endforeach

    </ul>

</div>

@endif


@if(session('success'))

<div class="success-screen" data-amrtm-flash-static>

    <div class="success-icon">
        ✓
    </div>

    <h2>
        تم إرسال البيانات بنجاح
    </h2>

    <p>
        تم استلام بيانات المكتب بنجاح،
        وسيتم مراجعة البيانات والمستندات
        من قبل الإدارة.
    </p>

</div>

@else


@if(session('info'))

<div class="alert alert-info">
    {{ session('info') }}
</div>

@endif


{{-- =====================================================
     HEADER
===================================================== --}}

<header class="page-header">

    <div class="page-header-icon">
        🏢
    </div>

    <h1>
        استكمال بيانات المكتب
    </h1>

    <p>
        يرجى استكمال البيانات والمستندات المطلوبة
        حتى يتم إرسال الملف للمراجعة والاعتماد.
    </p>

</header>


{{-- =====================================================
     STATUS
===================================================== --}}

<div class="status-card">

    <div class="status-title">
        حالة الملف
    </div>

    <div class="status-badge"
         id="profileStatus">

        جاري استكمال البيانات

    </div>

</div>


{{-- =====================================================
     CURRENT OFFICE DATA
===================================================== --}}

<section class="section-card">

    <div class="section-header">

        <h2 class="section-title">

            <span class="section-icon">
                ℹ
            </span>

            بيانات المكتب الحالية

        </h2>

    </div>


    <div class="office-data-grid">


        <div class="data-item">

            <div class="data-label">
                اسم المكتب بالعربي
            </div>

            <div class="data-value">
                {{ $office->name_ar ?: 'غير مسجل' }}
            </div>

        </div>


        <div class="data-item">

            <div class="data-label">
                اسم المكتب بالإنجليزي
            </div>

            <div class="data-value">
                {{ $office->name_en ?: 'غير مسجل' }}
            </div>

        </div>


        <div class="data-item">

            <div class="data-label">
                نوع المكتب
            </div>

            <div class="data-value">
                {{ $office->typeLabelAr() }}
            </div>

        </div>


        <div class="data-item">

            <div class="data-label">
                رقم الجوال
            </div>

            <div class="data-value">
                {{ $office->phone ?: 'غير مسجل' }}
            </div>

        </div>


        <div class="data-item">

            <div class="data-label">
                البريد الإلكتروني
            </div>

            <div class="data-value">
                {{ $office->email ?: 'غير مسجل' }}
            </div>

        </div>


        <div class="data-item">

            <div class="data-label">
                المدينة
            </div>

            <div class="data-value">
                {{ $office->city ?: 'غير مسجلة' }}
            </div>

        </div>


        <div class="data-item">

            <div class="data-label">
                السجل التجاري
            </div>

            <div class="data-value">
                {{ $office->cr_number ?: 'غير مسجل' }}
            </div>

        </div>


    </div>

</section>


{{-- =====================================================
     MAIN FORM
===================================================== --}}

<form
    id="completeOfficeForm"
    action="{{ route('amrtm.office.complete.save') }}"
    method="POST"
    enctype="multipart/form-data"
>

@csrf


{{-- =====================================================
     OFFICE DATA
===================================================== --}}

<section class="section-card">

    <div class="section-header">

        <h2 class="section-title">

            <span class="section-icon">
                📝
            </span>

            البيانات المطلوبة

        </h2>

    </div>


    <div class="form-grid">


        {{-- رقم الترخيص --}}

        <div class="form-group">

            <label>
                رقم الترخيص *
            </label>

            <input
                type="text"
                name="license_number"
                value="{{ old('license_number', $profile?->license_number) }}"
                placeholder="أدخل رقم الترخيص"
                required
            >

            @error('license_number')
                <span class="field-error">
                    {{ $message }}
                </span>
            @enderror

        </div>


        {{-- الدولة --}}

        <div class="form-group">

            <label>
                الدولة *
            </label>

            <input
                type="text"
                name="country"
                value="{{ old(
                    'country',
                    $profile?->country ?? 'المملكة العربية السعودية'
                ) }}"
                required
            >

            @error('country')
                <span class="field-error">
                    {{ $message }}
                </span>
            @enderror

        </div>


        {{-- المنطقة --}}

        <div class="form-group">

            <label>
                المنطقة *
            </label>

            <input
                type="text"
                name="governorate"
                value="{{ old('governorate', $profile?->governorate) }}"
                placeholder="مثال: الرياض"
                required
            >

            @error('governorate')
                <span class="field-error">
                    {{ $message }}
                </span>
            @enderror

        </div>


        {{-- المدينة --}}

        <div class="form-group">

            <label>
                المدينة *
            </label>

            <input
                type="text"
                name="city"
                value="{{ old('city', $profile?->city ?? $office->city) }}"
                placeholder="أدخل المدينة"
                required
            >

            @error('city')
                <span class="field-error">
                    {{ $message }}
                </span>
            @enderror

        </div>


        {{-- الحي --}}

        <div class="form-group">

            <label>
                الحي *
            </label>

            <input
                type="text"
                name="district"
                value="{{ old('district', $profile?->district) }}"
                placeholder="أدخل الحي"
                required
            >

            @error('district')
                <span class="field-error">
                    {{ $message }}
                </span>
            @enderror

        </div>


        {{-- الشارع --}}

        <div class="form-group">

            <label>
                الشارع *
            </label>

            <input
                type="text"
                name="street"
                value="{{ old('street', $profile?->street) }}"
                placeholder="أدخل اسم الشارع"
                required
            >

            @error('street')
                <span class="field-error">
                    {{ $message }}
                </span>
            @enderror

        </div>


        {{-- رقم المبنى --}}

        <div class="form-group">

            <label>
                رقم المبنى *
            </label>

            <input
                type="text"
                name="building_number"
                value="{{ old('building_number', $profile?->building_number) }}"
                placeholder="رقم المبنى"
                required
            >

            @error('building_number')
                <span class="field-error">
                    {{ $message }}
                </span>
            @enderror

        </div>


        {{-- رقم المكتب --}}

        <div class="form-group">

            <label>
                رقم المكتب
            </label>

            <input
                type="text"
                name="office_number"
                value="{{ old('office_number', $profile?->office_number) }}"
                placeholder="رقم المكتب"
            >

        </div>


        {{-- عدد الحالات --}}

        <div class="form-group">

            <label>
                عدد الحالات / الأعمال السابقة
            </label>

            <input
                type="number"
                name="handled_cases"
                min="0"
                value="{{ old(
                    'handled_cases',
                    $profile?->handled_cases ?? 0
                ) }}"
            >

        </div>


    </div>

</section>


{{-- =====================================================
     SPECIALTY
===================================================== --}}

<section class="section-card specialty-section">

    <div class="section-header">

        <h2 class="section-title">

            <span class="section-icon">
                ⭐
            </span>

            تخصص المكتب

        </h2>

    </div>


    {{-- التخصصات القادمة من قاعدة البيانات فقط --}}

    <div class="specialty-box">

        <div class="specialty-box-title">

            اختر تخصصًا واحدًا من القائمة

        </div>


        @if($specialties->count() > 0)

            <div class="specialties-list">

                @foreach($specialties as $specialty)

                    <div class="specialty-option">

                        <input
                            type="radio"
                            id="specialty_{{ $specialty->id }}"
                            name="specialty"
                            value="{{ $specialty->id }}"

                            {{ (string) old(
                                'specialty',
                                optional(
                                    $office->specialtiesRelation->first()
                                )->id
                            ) === (string) $specialty->id
                                ? 'checked'
                                : ''
                            }}
                        >

                        <label
                            for="specialty_{{ $specialty->id }}"
                        >

                            {{ $specialty->name_ar }}

                        </label>

                    </div>

                @endforeach

            </div>

        @else

<div class="alert alert-info" data-amrtm-flash-static>

                لا توجد تخصصات مضافة لهذا النوع من المكاتب في قاعدة البيانات حاليًا.

                يمكنك كتابة تخصصك يدويًا في الخانة بالأسفل.

            </div>

        @endif

    </div>


    {{-- =================================================
         CUSTOM SPECIALTY
    ================================================= --}}

    <div class="custom-specialty-box">

        <div class="specialty-box-title">

            أو اكتب تخصصًا غير موجود في القائمة

        </div>


        <input
            type="text"
            id="custom_specialty"
            name="custom_specialty"
            value="{{ old(
                'custom_specialty',
                $profile?->custom_specialty
            ) }}"
            placeholder="اكتب تخصصك هنا"
        >


        <span class="form-hint">

            يمكنك اختيار تخصص من القائمة أو كتابة تخصص غير موجود.
            مسموح بتخصص واحد فقط.

        </span>


        @error('specialty')

            <span class="field-error">
                {{ $message }}
            </span>

        @enderror


        @error('custom_specialty')

            <span class="field-error">
                {{ $message }}
            </span>

        @enderror

    </div>

</section>


{{-- =====================================================
     DOCUMENTS
===================================================== --}}

<section class="section-card">

    <div class="section-header">

        <h2 class="section-title">

            <span class="section-icon">
                📁
            </span>

            مستندات المكتب

        </h2>

    </div>


    <div class="documents-grid">


        {{-- العلامة التجارية --}}

        <div class="document-card">

            <div class="document-icon">
                📢
            </div>

            <label for="commercial_ad">
                إرفاق العلامة التجارية *
            </label>

            <input
                type="file"
                id="commercial_ad"
                name="commercial_ad"
                accept=".pdf,.jpg,.jpeg,.png"
                required
            >

            <small>
                PDF أو JPG أو PNG — الحد الأقصى 5MB
            </small>

            <div
                class="selected-file-name"
                id="commercial_ad_name"
            ></div>

            @error('commercial_ad')

                <span class="field-error">
                    {{ $message }}
                </span>

            @enderror

        </div>


        {{-- الترخيص --}}

        <div class="document-card">

            <div class="document-icon">
                📄
            </div>

            <label for="license_file">
                إرفاق صورة الترخيص *
            </label>

            <input
                type="file"
                id="license_file"
                name="license_file"
                accept=".pdf,.jpg,.jpeg,.png"
                required
            >

            <small>
                PDF أو JPG أو PNG — الحد الأقصى 5MB
            </small>

            <div
                class="selected-file-name"
                id="license_file_name"
            ></div>

            @error('license_file')

                <span class="field-error">
                    {{ $message }}
                </span>

            @enderror

        </div>


        {{-- السجل التجاري --}}

        <div class="document-card">

            <div class="document-icon">
                📋
            </div>

            <label for="cr_file">
                إرفاق السجل التجاري *
            </label>

            <input
                type="file"
                id="cr_file"
                name="cr_file"
                accept=".pdf,.jpg,.jpeg,.png"
                required
            >

            <small>
                PDF أو JPG أو PNG — الحد الأقصى 5MB
            </small>

            <div
                class="selected-file-name"
                id="cr_file_name"
            ></div>

            @error('cr_file')

                <span class="field-error">
                    {{ $message }}
                </span>

            @enderror

        </div>


        {{-- الشهادة المهنية --}}

        <div class="document-card">

            <div class="document-icon">
                🎓
            </div>

            <label for="professional_certificate">
                إرفاق الشهادات العملية *
            </label>

            <input
                type="file"
                id="professional_certificate"
                name="professional_certificate"
                accept=".pdf,.jpg,.jpeg,.png"
                required
            >

            <small>
                PDF أو JPG أو PNG — الحد الأقصى 5MB
            </small>

            <div
                class="selected-file-name"
                id="professional_certificate_name"
            ></div>

            @error('professional_certificate')

                <span class="field-error">
                    {{ $message }}
                </span>

            @enderror

        </div>


        {{-- شهادات التقدير --}}

        <div class="document-card">

            <div class="document-icon">
                🏆
            </div>

            <label for="appreciation_certificates">
                إرفاق شهادات التقدير *
            </label>

            <input
                type="file"
                id="appreciation_certificates"
                name="appreciation_certificates[]"
                accept=".pdf,.jpg,.jpeg,.png"
                multiple
                required
            >

            <small>
                يمكنك اختيار أكثر من شهادة — الحد الأقصى 5MB للملف
            </small>

            <div
                class="selected-file-name"
                id="appreciation_certificates_name"
            ></div>

            @error('appreciation_certificates')

                <span class="field-error">
                    {{ $message }}
                </span>

            @enderror

        </div>


        {{-- السيرة الذاتية --}}

        <div class="document-card">

            <div class="document-icon">
                📑
            </div>

            <label for="cv_file">
                إرفاق السيرة الذاتية *
            </label>

            <input
                type="file"
                id="cv_file"
                name="cv_file"
                accept=".pdf,.jpg,.jpeg,.png"
                required
            >

            <small>
                PDF أو JPG أو PNG — الحد الأقصى 5MB
            </small>

            <div
                class="selected-file-name"
                id="cv_file_name"
            ></div>

            @error('cv_file')

                <span class="field-error">
                    {{ $message }}
                </span>

            @enderror

        </div>


    </div>

</section>


{{-- =====================================================
     ACTION
===================================================== --}}

<div class="form-actions">

    <button
        type="submit"
        class="save-button"
        id="saveButton"
    >

        حفظ وإرسال البيانات للمراجعة

    </button>

</div>


</form>

@endif

</div>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {


    /* =====================================================
       PROFILE STATUS
    ===================================================== */

    const statusElement =
        document.getElementById('profileStatus');


    if (statusElement) {

        const profileCompleted =
            @json(
                (bool) (
                    $profile?->profile_completed ?? false
                )
            );


        const verificationStatus =
            @json(
                $profile?->verification_status ?? null
            );


        if (
            verificationStatus === 'pending'
        ) {

            statusElement.textContent =
                'قيد المراجعة';

        }

        else if (
            verificationStatus === 'approved'
        ) {

            statusElement.textContent =
                'تم اعتماد المكتب';

        }

        else if (
            profileCompleted
        ) {

            statusElement.textContent =
                'البيانات مكتملة';

        }

        else {

            statusElement.textContent =
                'جاري استكمال البيانات';

        }

    }


    /* =====================================================
       SPECIALTY
       
       القاعدة:
       
       1- اختيار واحد فقط من DB
       2- أو تخصص يدوي واحد
       3- الخانتان تظلّان مفتوحتين
       4- عند الكتابة اليدوية يتم إلغاء اختيار DB
       5- عند اختيار DB يتم مسح اليدوي
    ===================================================== */

    const specialtyInputs =
        document.querySelectorAll(
            'input[name="specialty"]'
        );


    const customSpecialty =
        document.getElementById(
            'custom_specialty'
        );


    /* ---------------------------------------------
       عند اختيار تخصص من القائمة
    --------------------------------------------- */

    specialtyInputs.forEach(function (input) {

        input.addEventListener(
            'change',
            function () {

                if (this.checked && customSpecialty) {

                    customSpecialty.value = '';

                }

            }
        );

    });


    /* ---------------------------------------------
       عند كتابة تخصص يدوي
    --------------------------------------------- */

    if (customSpecialty) {

        customSpecialty.addEventListener(
            'input',
            function () {

                const value =
                    this.value.trim();


                if (value !== '') {

                    /*
                     * إلغاء أي تخصص
                     * تم اختياره من DB
                     */

                    specialtyInputs.forEach(
                        function (input) {

                            input.checked = false;

                        }
                    );

                }

            }
        );

    }


    /* =====================================================
       FILE NAMES
    ===================================================== */

    const fileInputs = [

        'commercial_ad',

        'license_file',

        'cr_file',

        'professional_certificate',

        'appreciation_certificates',

        'cv_file'

    ];


    fileInputs.forEach(
        function (inputId) {


            const input =
                document.getElementById(
                    inputId
                );


            const output =
                document.getElementById(
                    inputId + '_name'
                );


            if (
                !input ||
                !output
            ) {

                return;

            }


            input.addEventListener(
                'change',
                function () {


                    if (
                        !this.files ||
                        !this.files.length
                    ) {

                        output.textContent =
                            '';

                        return;

                    }


                    if (
                        this.files.length === 1
                    ) {

                        output.textContent =
                            '✓ ' +
                            this.files[0].name;

                    }

                    else {

                        output.textContent =
                            '✓ تم اختيار ' +
                            this.files.length +
                            ' ملفات';

                    }

                }
            );

        }
    );


    /* =====================================================
       FORM SUBMIT
    ===================================================== */

    const form =
        document.getElementById(
            'completeOfficeForm'
        );


    if (form) {

        form.addEventListener(
            'submit',
            function (event) {


                const selected =
                    document.querySelector(
                        'input[name="specialty"]:checked'
                    );


                const custom =
                    customSpecialty
                        ? customSpecialty.value.trim()
                        : '';


                /* -----------------------------------------
                   لازم تخصص واحد
                ----------------------------------------- */

                if (
                    !selected &&
                    custom === ''
                ) {

                    event.preventDefault();

                    if (window.AmrtmNotify) AmrtmNotify.warning(
                        'يرجى اختيار تخصص واحد من القائمة أو كتابة تخصص غير موجود.'
                    ); else alert(
                        'يرجى اختيار تخصص واحد من القائمة أو كتابة تخصص غير موجود.'
                    );

                    return;

                }


                /* -----------------------------------------
                   ممنوع الاثنين معًا
                ----------------------------------------- */

                if (
                    selected &&
                    custom !== ''
                ) {

                    event.preventDefault();

                    if (window.AmrtmNotify) AmrtmNotify.warning(
                        'اختر تخصصًا واحدًا فقط: إما من القائمة أو اكتب تخصصًا جديدًا.'
                    ); else alert(
                        'اختر تخصصًا واحدًا فقط: إما من القائمة أو اكتب تخصصًا جديدًا.'
                    );

                    return;

                }


                /* -----------------------------------------
                   منع الضغط مرتين
                ----------------------------------------- */

                const saveButton =
                    document.getElementById(
                        'saveButton'
                    );


                if (saveButton) {

                    saveButton.disabled =
                        true;

                    saveButton.textContent =
                        'جاري إرسال البيانات...';

                }

            }
        );

    }

});

</script>

</body>

</html>