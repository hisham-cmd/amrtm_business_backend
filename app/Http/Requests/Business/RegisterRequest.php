<?php

namespace App\Http\Requests\Business;

use App\Models\Business\BusinessUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $rules = [
            'name'        => ['required', 'string', 'min:2', 'max:100'],
            'email'       => ['required', 'email', 'max:200', Rule::unique(BusinessUser::class)],
            'phone'       => ['required', 'string', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'phone_dial'  => ['nullable', 'string', 'max:10'],
            'password'    => ['required', 'confirmed', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$/'],
            'account_type'=> ['nullable', 'in:individual,establishment'],
            'father_name' => ['nullable', 'string', 'max:100'],
            'grandfather_name' => ['nullable', 'string', 'max:100'],
            'family_name' => ['nullable', 'string', 'max:100'],
            'id_number'   => ['nullable', 'string', 'max:20'],
            'job_sector'  => ['nullable', 'in:government,private'],
            'employment_status' => ['nullable', 'in:retired,affiliated'],
            'legal_name'  => ['nullable', 'string', 'max:191'],
            'entity_type' => ['nullable', 'in:company,institution'],
            'cr_number'   => ['nullable', 'string', 'max:50'],
            'cr_expiry_date'   => ['nullable', 'date'],
            'license_expiry_date' => ['nullable', 'date'],
            'country'     => ['nullable', 'string', 'max:191'],
            'region'      => ['nullable', 'string', 'max:191'],
            'city'        => ['nullable', 'string', 'max:191'],
            'district'    => ['nullable', 'string', 'max:191'],
            'street'      => ['nullable', 'string', 'max:191'],
            'building_number' => ['nullable', 'string', 'max:191'],
            'office_number'   => ['nullable', 'string', 'max:191'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ];

        if ($this->input('account_type') === 'establishment') {
            $rules['legal_name'] = ['required', 'string', 'max:191'];
            $rules['entity_type'] = ['required', 'in:company,institution'];
            $rules['cr_number']  = ['required', 'string', 'max:50'];
            $rules['country']    = ['required', 'string', 'max:191'];
            $rules['region']     = ['required', 'string', 'max:191'];
            $rules['city']       = ['required', 'string', 'max:191'];
        }

        if ($this->input('account_type') === 'individual') {
            $rules['father_name'] = ['required', 'string', 'max:100'];
            $rules['family_name'] = ['required', 'string', 'max:100'];
            $rules['id_number']   = ['required', 'string', 'max:20'];
            $rules['job_sector']  = ['required', 'in:government,private'];
            $rules['employment_status'] = ['required', 'in:retired,affiliated'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'الاسم الكامل مطلوب.',
            'name.min'           => 'الاسم يجب أن يحتوي على حرفين على الأقل.',
            'email.required'     => 'البريد الإلكتروني مطلوب.',
            'email.unique'       => 'هذا البريد الإلكتروني مسجل مسبقاً.',
            'phone.required'     => 'رقم الهاتف مطلوب.',
            'phone.regex'        => 'صيغة رقم الهاتف غير صحيحة.',
            'password.required'  => 'كلمة المرور مطلوبة.',
            'password.confirmed' => 'كلمة المرور وتأكيدها غير متطابقين.',
            'password.min'       => 'كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل وتشمل أرقاماً وحروفاً.',
            'password.regex'     => 'كلمة المرور يجب أن تتضمن حرفاً إنجليزياً كبيراً وصغيراً ورقماً ورمزاً خاصاً (!@#$%^&*).',
            'account_type.in'    => 'نوع الحساب غير صحيح.',
            'father_name.required' => 'اسم الأب مطلوب.',
            'family_name.required' => 'اسم العائلة مطلوب.',
            'id_number.required'   => 'رقم الهوية مطلوب.',
            'job_sector.required'  => 'يرجى تحديد نوع الوظيفة (حكومي أو خاص).',
            'job_sector.in'        => 'نوع الوظيفة غير صحيح.',
            'employment_status.required' => 'يرجى تحديد الحالة الوظيفية (متقاعد أو منتسب).',
            'employment_status.in' => 'الحالة الوظيفية غير صحيحة.',
            'legal_name.required' => 'الاسم التجاري للمنشأة مطلوب.',
            'cr_number.required'  => 'رقم السجل التجاري مطلوب.',
            'entity_type.required' => 'نوع المنشأة (شركة / مؤسسة) مطلوب.',
            'entity_type.in'       => 'نوع المنشأة غير صحيح.',
            'country.required'    => 'الدولة مطلوبة.',
            'region.required'     => 'المنطقة مطلوبة.',
            'city.required'       => 'المدينة مطلوبة.',
            'profile_photo.image'  => 'الصورة الشخصية يجب أن تكون صورة (JPG أو PNG أو WebP).',
            'profile_photo.mimes'  => 'الصورة الشخصية يجب أن تكون بصيغة JPG أو PNG أو WebP.',
            'profile_photo.max'    => 'حجم الصورة الشخصية يجب ألا يتجاوز 5 ميجابايت.',
        ];
    }
}