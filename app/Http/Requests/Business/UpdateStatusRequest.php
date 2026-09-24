<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('business')->check() && auth('business')->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'status'               => ['sometimes', 'in:pending,processing,in_progress,done,rejected'],
            'reject_reason'        => ['nullable', 'string', 'max:1000'],
            'estimated_completion' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in'                => 'حالة الطلب غير صحيحة.',
            'reject_reason.max'        => 'سبب الرفض يجب ألا يتجاوز 1000 حرف.',
            'estimated_completion.max' => 'الوقت المتوقع يجب ألا يتجاوز 200 حرف.',
        ];
    }

    protected function failedAuthorization(): never
    {
        abort(403, 'غير مصرح لك بتحديث حالة الطلبات.');
    }
}
