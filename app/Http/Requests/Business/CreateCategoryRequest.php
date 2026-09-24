<?php

namespace App\Http\Requests\Business;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateCategoryRequest extends FormRequest
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
            'key'        => ['required', 'string', 'max:100'],
            'name_ar'    => ['required', 'string', 'max:200'],
            'name_en'    => ['required', 'string', 'max:200'],
            'icon'       => ['required', 'string', 'max:100'],
            'color'      => ['required', 'string', 'max:30'],
            'bg'         => ['required', 'string', 'max:60'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
