<?php

namespace App\Http\Requests\Business;

use App\Models\GovService;
use App\Support\ServiceCustomFields;
use Illuminate\Foundation\Http\FormRequest;

class SubmitRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth('business')->user();
        return $user && !$user->isAdmin();
    }

    public function rules(): array
    {
        $rules = [
            'service_id'       => ['required', 'integer', 'exists:business.bs_services,id'],
            'client_name'      => ['required', 'string', 'min:2', 'max:200'],
            'client_email'     => ['required', 'email', 'max:200'],
            'client_phone'     => ['required', 'string', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'company_name'     => ['nullable', 'string', 'max:200'],
            'company_cr'       => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\-\/]+$/'],
            'notes'            => ['nullable', 'string', 'max:2000'],
            'attachments'      => ['nullable', 'array', 'max:5'],
            'attachments.*'    => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            /*
             |----------------------------------------------------------------------
             | القيم لا التعريفات
             |----------------------------------------------------------------------
             | كانت 'custom_fields' => array وهو يرفض أي قيمة نصية، لكن الواجهة
             | ترسل القيم فقط: custom_fields[<key>] = قيمة. والقواعد الديناميكية
             | أدناه (custom_fields.<key>) تتحقق من كل قيمة على حدة.
             | لذلك نتحقق أنها بنية مفاتيح/قيم map<string,mixed> لا تعريفات.
             */
            'custom_fields'    => ['nullable'],
            'custom_fields.*'  => ['nullable'],
        ];

        $service = GovService::find($this->input('service_id'));
        if ($service) {
            $rules = array_merge($rules, ServiceCustomFields::validationRules($service->custom_fields ?? []));
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'service_id.required'       => 'يجب اختيار الخدمة.',
            'service_id.exists'         => 'الخدمة المحددة غير موجودة.',
            'client_name.required'      => 'اسم العميل مطلوب.',
            'client_email.required'     => 'البريد الإلكتروني مطلوب.',
            'client_phone.required'     => 'رقم الهاتف مطلوب.',
            'client_phone.regex'        => 'صيغة رقم الهاتف غير صحيحة.',
            'attachments.max'           => 'لا يمكن رفع أكثر من 5 ملفات.',
            'attachments.*.mimes'       => 'صيغ الملفات المقبولة: PDF، JPG، PNG.',
            'attachments.*.max'         => 'الحد الأقصى لحجم الملف 10 ميغابايت.',
        ];
    }

    public function attributes(): array
    {
        $service = GovService::find($this->input('service_id'));

        return collect($service?->custom_fields ?? [])->mapWithKeys(
            fn(array $field) => ['custom_fields.' . $field['key'] => $field['label_ar']]
        )->all();
    }

    protected function failedAuthorization(): never
    {
        abort(403, 'المدير والمشرف لا يمكنهم تقديم طلبات.');
    }
}