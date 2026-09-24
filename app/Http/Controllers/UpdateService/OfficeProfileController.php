<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Models\Business\Office;
use App\Models\Business\OfficeProfile;
use App\Models\Business\Specialty;
use App\Models\Business\OfficeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class OfficeProfileController extends Controller
{
    /**
     * صفحة استكمال بيانات المكتب
     */
    public function show(?string $token = null)
{
    /*
    |--------------------------------------------------------------------------
    | لو فيه Token جاي من رابط الدعوة
    |--------------------------------------------------------------------------
    */

    if ($token) {

        $office = Office::where('public_token', $token)
            ->first();

        if (!$office) {
            abort(404, 'رابط المكتب غير صحيح أو منتهي');
        }

    } else {

        /*
        |--------------------------------------------------------------------------
        | الدخول الطبيعي للمكتب من خلال تسجيل الدخول
        |--------------------------------------------------------------------------
        */

        $user = Auth::guard('office')->user();

        if (!$user) {
            return redirect()->route('amrtm.office.login');
        }

        $office = $user->office;

        if (!$office) {
            abort(404, 'المكتب غير موجود');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | بيانات المكتب
    |--------------------------------------------------------------------------
    */

    $profile = $office->profile;

    /*
    |--------------------------------------------------------------------------
    | التخصصات الأساسية
    |--------------------------------------------------------------------------
    */

    $specialties = Specialty::where('office_type', $office->type)
        ->where('is_active', true)
        ->orderBy('name_ar')
        ->get();

    return view(
        'update_service.complet',
        compact(
            'office',
            'profile',
            'specialties'
        )
    );
}

public function publicProfile($token)
{
    $office = Office::where('public_token', $token)
        ->where('is_verified', true)
        ->where('is_active', true)
        ->with([
            'profile',
            'documents',
            'specialtiesRelation',
            'services',
        ])
        ->firstOrFail();

    return view('office.public', compact('office'));
}

public function save(Request $request)
{
    $user = Auth::guard('office')->user();

    if (!$user) {
        return redirect()->route('amrtm.login');
    }

    $office = $user->office;

    if (!$office) {
        abort(404, 'المكتب غير موجود');
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([

        'license_number' => [
            'required',
            'string',
            'max:100',
        ],

        'cr_expiry_date' => [
            'nullable',
            'date',
        ],

        'license_expiry_date' => [
            'nullable',
            'date',
        ],

        'country' => [
            'required',
            'string',
            'max:100',
        ],

        'governorate' => [
            'required',
            'string',
            'max:100',
        ],

        'city' => [
            'required',
            'string',
            'max:100',
        ],

        'district' => [
            'required',
            'string',
            'max:150',
        ],

        'street' => [
            'required',
            'string',
            'max:200',
        ],

        'building_number' => [
            'required',
            'string',
            'max:50',
        ],

        'office_number' => [
            'nullable',
            'string',
            'max:50',
        ],

        'handled_cases' => [
            'nullable',
            'integer',
            'min:0',
        ],

        /*
        |--------------------------------------------------------------------------
        | التخصص
        |--------------------------------------------------------------------------
        */

        'specialty' => [
            'nullable',
            'integer',
        ],

        'custom_specialty' => [
            'nullable',
            'string',
            'max:255',
        ],

        /*
        |--------------------------------------------------------------------------
        | الملفات
        |--------------------------------------------------------------------------
        */

        'commercial_ad' => [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120',
        ],

        'license_file' => [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120',
        ],

        'cr_file' => [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120',
        ],

        'professional_certificate' => [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120',
        ],

        'appreciation_certificates' => [
            'nullable',
            'array',
        ],

        'appreciation_certificates.*' => [
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120',
        ],

        'cv_file' => [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120',
        ],

    ], [

        'license_number.required' =>
            'رقم الترخيص مطلوب.',

        'country.required' =>
            'الدولة مطلوبة.',

        'governorate.required' =>
            'المنطقة مطلوبة.',

        'city.required' =>
            'المدينة مطلوبة.',

        'district.required' =>
            'الحي مطلوب.',

        'street.required' =>
            'الشارع مطلوب.',

        'building_number.required' =>
            'رقم المبنى مطلوب.',

        'commercial_ad.mimes' =>
            'الإعلان التجاري يجب أن يكون PDF أو JPG أو JPEG أو PNG.',

        'commercial_ad.max' =>
            'حجم الإعلان التجاري يجب ألا يتجاوز 5MB.',

        'license_file.mimes' =>
            'ملف الترخيص يجب أن يكون PDF أو JPG أو JPEG أو PNG.',

        'license_file.max' =>
            'حجم ملف الترخيص يجب ألا يتجاوز 5MB.',

        'cr_file.mimes' =>
            'السجل التجاري يجب أن يكون PDF أو JPG أو JPEG أو PNG.',

        'cr_file.max' =>
            'حجم السجل التجاري يجب ألا يتجاوز 5MB.',

        'professional_certificate.mimes' =>
            'الشهادة المهنية يجب أن تكون PDF أو JPG أو JPEG أو PNG.',

        'professional_certificate.max' =>
            'حجم الشهادة المهنية يجب ألا يتجاوز 5MB.',

        'appreciation_certificates.*.mimes' =>
            'شهادة التقدير يجب أن تكون PDF أو JPG أو JPEG أو PNG.',

        'appreciation_certificates.*.max' =>
            'حجم شهادة التقدير يجب ألا يتجاوز 5MB.',

        'cv_file.mimes' =>
            'السيرة الذاتية يجب أن تكون PDF أو JPG أو JPEG أو PNG.',

        'cv_file.max' =>
            'حجم السيرة الذاتية يجب ألا يتجاوز 5MB.',
    ]);


    /*
    |--------------------------------------------------------------------------
    | تجهيز التخصص
    |--------------------------------------------------------------------------
    */

    $selectedSpecialty = $validated['specialty'] ?? null;

    $customSpecialty = trim(
        $validated['custom_specialty'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | ممنوع الاثنين معًا
    |--------------------------------------------------------------------------
    */

    if (
        !empty($selectedSpecialty) &&
        !empty($customSpecialty)
    ) {

        return back()
            ->withErrors([
                'specialty' =>
                    'اختر تخصصًا من القائمة أو اكتب تخصصًا آخر، وليس الاثنين معًا.'
            ])
            ->withInput();
    }


    /*
    |--------------------------------------------------------------------------
    | لازم يكون فيه تخصص واحد
    |--------------------------------------------------------------------------
    */

    if (
        empty($selectedSpecialty) &&
        empty($customSpecialty)
    ) {

        return back()
            ->withErrors([
                'specialty' =>
                    'يجب اختيار تخصص من القائمة أو كتابة تخصصك.'
            ])
            ->withInput();
    }


    /*
    |--------------------------------------------------------------------------
    | لو اختار تخصص من القائمة
    |--------------------------------------------------------------------------
    */

    $specialtyId = null;

    if (!empty($selectedSpecialty)) {

        $specialtyId = (int) $selectedSpecialty;

        /*
        |--------------------------------------------------------------------------
        | التأكد أن التخصص موجود
        | ونفس نوع المكتب
        |--------------------------------------------------------------------------
        */

        $specialtyExists = DB::connection('business')
            ->table('bs_specialties')
            ->where('id', $specialtyId)
            ->where('office_type', $office->type)
            ->where('is_active', 1)
            ->exists();

        if (!$specialtyExists) {

            return back()
                ->withErrors([
                    'specialty' =>
                        'التخصص المختار غير متاح لنوع هذا المكتب.'
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | عند اختيار تخصص من القائمة
        | لا نريد تخصصًا مخصصًا
        |--------------------------------------------------------------------------
        */

        $customSpecialty = null;
    }


    /*
    |--------------------------------------------------------------------------
    | Transaction
    |--------------------------------------------------------------------------
    */

    try {

        DB::connection('business')->transaction(
            function () use (
                $office,
                $validated,
                $request,
                $specialtyId,
                $customSpecialty
            ) {

                /*
                |--------------------------------------------------------------------------
                | Profile
                |--------------------------------------------------------------------------
                */

                $profile = OfficeProfile::firstOrNew([
                    'office_id' => $office->id,
                ]);


                /*
                |--------------------------------------------------------------------------
                | إنشاء كود المكتب مرة واحدة
                |--------------------------------------------------------------------------
                */

                if (empty($profile->office_code)) {

                    do {

                        $officeCode =
                            'OFF-' .
                            strtoupper(
                                Str::random(10)
                            );

                    } while (
                        OfficeProfile::where(
                            'office_code',
                            $officeCode
                        )->exists()
                    );

                    $profile->office_code =
                        $officeCode;
                }


                /*
                |--------------------------------------------------------------------------
                | QR
                |--------------------------------------------------------------------------
                */

                if (empty($profile->qr_code)) {

                    $profile->qr_code =
                        $profile->office_code;
                }


                /*
                |--------------------------------------------------------------------------
                | بيانات المكتب
                |--------------------------------------------------------------------------
                */

                $profile->license_number =
                    $validated['license_number'];

                $profile->cr_number =
                    $office->cr_number;

                $profile->cr_expiry_date =
                    $validated['cr_expiry_date'] ?? null;

                $profile->license_expiry_date =
                    $validated['license_expiry_date'] ?? null;

                $profile->mobile =
                    $office->phone;


                /*
                |--------------------------------------------------------------------------
                | العنوان
                |--------------------------------------------------------------------------
                */

                $profile->country =
                    $validated['country'];

                $profile->governorate =
                    $validated['governorate'];

                $profile->city =
                    $validated['city'];

                $profile->district =
                    $validated['district'];

                $profile->street =
                    $validated['street'];

                $profile->building_number =
                    $validated['building_number'];

                $profile->office_number =
                    $validated['office_number'] ?? null;


                /*
                |--------------------------------------------------------------------------
                | الوصف
                |--------------------------------------------------------------------------
                */

                $profile->description_ar = null;

                $profile->description_en = null;


                /*
                |--------------------------------------------------------------------------
                | التخصص المكتوب يدويًا
                |--------------------------------------------------------------------------
                |
                | مهم جدًا:
                | لا نضيفه إلى bs_specialties.
                | نخزنه فقط داخل Profile.
                |--------------------------------------------------------------------------
                */

                $profile->custom_specialty =
                    !empty($customSpecialty)
                        ? $customSpecialty
                        : null;


                /*
                |--------------------------------------------------------------------------
                | عدد القضايا
                |--------------------------------------------------------------------------
                */

                $profile->handled_cases =
                    $validated['handled_cases'] ?? 0;


                /*
                |--------------------------------------------------------------------------
                | حالة الملف
                |--------------------------------------------------------------------------
                */

                $profile->profile_completed = true;

                $profile->verification_status = 'pending';

                $profile->submitted_at = now();

                $profile->approved_at = null;


                /*
                |--------------------------------------------------------------------------
                | حفظ Profile
                |--------------------------------------------------------------------------
                */

                $profile->save();


                /*
                |--------------------------------------------------------------------------
                | التخصص
                |--------------------------------------------------------------------------
                */

                if ($specialtyId) {

                    /*
                    | تخصص من القائمة
                    |
                    | sync وليس syncWithoutDetaching
                    | حتى يكون للمكتب تخصص واحد فقط.
                    */

                    $office
                        ->specialtiesRelation()
                        ->sync([
                            $specialtyId
                        ]);

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | تخصص مكتوب يدويًا
                    |--------------------------------------------------------------------------
                    |
                    | نحذف أي تخصص مربوط بالمكتب
                    | ونحتفظ بالتخصص المكتوب في Profile فقط.
                    |--------------------------------------------------------------------------
                    */

                    $office
                        ->specialtiesRelation()
                        ->detach();
                }


                /*
                |--------------------------------------------------------------------------
                | الملفات
                |--------------------------------------------------------------------------
                */

                $documents = [

                    'commercial_ad' =>
                        'commercial_ad',

                    'license_file' =>
                        'license',

                    'cr_file' =>
                        'commercial_register',

                    'professional_certificate' =>
                        'professional_certificate',

                    'cv_file' =>
                        'cv',
                ];


                foreach (
                    $documents
                    as $inputName => $documentType
                ) {

                    if (
                        !$request->hasFile(
                            $inputName
                        )
                    ) {
                        continue;
                    }


                    $uploadedFile =
                        $request->file(
                            $inputName
                        );


                    $path =
                        $uploadedFile->store(
                            'office_documents/' .
                            $office->id,
                            'public'
                        );


                    OfficeDocument::create([

                        'office_id' =>
                            $office->id,

                        'document_type' =>
                            $documentType,

                        'file' =>
                            $path,

                        'file_name' =>
                            $uploadedFile
                                ->getClientOriginalName(),

                        'is_verified' =>
                            false,
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | شهادات التقدير
                |--------------------------------------------------------------------------
                */

                if (
                    $request->hasFile(
                        'appreciation_certificates'
                    )
                ) {

                    foreach (
                        $request->file(
                            'appreciation_certificates'
                        )
                        as $uploadedFile
                    ) {

                        $path =
                            $uploadedFile->store(
                                'office_documents/' .
                                $office->id .
                                '/appreciation',
                                'public'
                            );


                        OfficeDocument::create([

                            'office_id' =>
                                $office->id,

                            'document_type' =>
                                'appreciation_certificate',

                            'file' =>
                                $path,

                            'file_name' =>
                                $uploadedFile
                                    ->getClientOriginalName(),

                            'is_verified' =>
                                false,
                        ]);
                    }
                }
            }
        );

    } catch (\Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | مهم أثناء حل المشكلة
        |--------------------------------------------------------------------------
        */

        report($e);

        return back()
            ->withErrors([
                'save' =>
                    'حدث خطأ أثناء حفظ بيانات المكتب والملفات.'
            ])
            ->withInput();
    }


    /*
    |--------------------------------------------------------------------------
    | النجاح
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route('amrtm.office.complete')
        ->with(
            'success',
            'تم استكمال بيانات المكتب وإرسالها للمراجعة بنجاح.'
        );
}
/**
 * إرسال المكتب للمراجعة
 */
public function submit()
{
    $user = Auth::guard('office')->user();

    if (!$user) {
        return redirect()->route('amrtm.login');
    }


    $office = $user->office;

    if (!$office) {
        abort(404, 'المكتب غير موجود');
    }


    /*
    |--------------------------------------------------------------------------
    | الحصول على ملف المكتب
    |--------------------------------------------------------------------------
    */

    $profile = $office->profile;


    /*
    |--------------------------------------------------------------------------
    | لا يوجد Profile
    |--------------------------------------------------------------------------
    */

    if (!$profile) {

        return redirect()
            ->route(
                'amrtm.office.complete'
            )
            ->withErrors([
                'profile' =>
                    'يجب استكمال بيانات المكتب أولاً.'
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | التأكد من اكتمال البيانات
    |--------------------------------------------------------------------------
    */

    if (!$profile->profile_completed) {

        return redirect()
            ->route(
                'amrtm.office.complete'
            )
            ->withErrors([
                'profile' =>
                    'يجب استكمال جميع البيانات المطلوبة أولاً.'
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | التأكد من وجود كود المكتب
    |--------------------------------------------------------------------------
    */

    if (empty($profile->office_code)) {

        return redirect()
            ->route(
                'amrtm.office.complete'
            )
            ->withErrors([
                'profile' =>
                    'لم يتم إنشاء كود المكتب. يرجى حفظ البيانات مرة أخرى.'
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | التأكد من وجود QR Code
    |--------------------------------------------------------------------------
    */

    if (empty($profile->qr_code)) {

        $profile->qr_code =
            $profile->office_code;

        $profile->save();
    }


    /*
    |--------------------------------------------------------------------------
    | المكتب قيد المراجعة
    |--------------------------------------------------------------------------
    */

    if (
        $profile->verification_status ===
        'pending'
    ) {

        return redirect()
            ->route(
                'amrtm.office.complete'
            )
            ->with(
                'info',
                'بيانات المكتب قيد المراجعة بالفعل.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | المكتب معتمد
    |--------------------------------------------------------------------------
    */

    if (
        $profile->verification_status ===
        'approved'
    ) {

        return redirect()
            ->route(
                'amrtm.office.dashboard'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | إرسال للمراجعة
    |--------------------------------------------------------------------------
    */

    $profile->update([

        'verification_status' =>
            'pending',

        'submitted_at' =>
            now(),

        'approved_at' =>
            null,
    ]);


    /*
    |--------------------------------------------------------------------------
    | النجاح
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route(
            'amrtm.office.complete'
        )
        ->with(
            'success',
            'تم إرسال بيانات المكتب للمراجعة بنجاح.'
        );
}
}