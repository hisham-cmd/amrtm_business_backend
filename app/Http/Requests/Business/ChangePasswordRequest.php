<?php

namespace App\Http\Requests\Business;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password'          => ['required', 'string'],
            'new_password'              => ['required', 'string', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['required'],
        ];
    }
}
