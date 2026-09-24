<?php

namespace App\Http\Requests\Business;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEntityRequest extends FormRequest
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
            'category_id' => ['sometimes', 'integer'],
            'name_ar'     => ['sometimes', 'string', 'max:200'],
            'name_en'     => ['sometimes', 'string', 'max:200'],
            'icon'        => ['sometimes', 'string', 'max:100'],
            'color'       => ['sometimes', 'string', 'max:30'],
            'bg'          => ['sometimes', 'string', 'max:60'],
            'tag_ar'      => ['nullable', 'string', 'max:100'],
            'tag_en'      => ['nullable', 'string', 'max:100'],
            'is_active'   => ['sometimes', 'boolean'],
            'sort_order'  => ['sometimes', 'integer', 'min:0'],
            'images' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
