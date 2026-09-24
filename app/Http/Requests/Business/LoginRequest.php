<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email', 'max:200'],
            'password' => ['required', 'string', 'min:6', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'البريد الإلكتروني مطلوب.',
            'email.email'       => 'صيغة البريد الإلكتروني غير صحيحة.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min'      => 'كلمة المرور يجب أن تحتوي على 6 أحرف على الأقل.',
        ];
    }
}