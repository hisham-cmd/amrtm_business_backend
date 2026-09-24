<?php

namespace App\Http\Requests\Business;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateEntityRequest extends FormRequest
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
            'category_id' => ['required', 'integer'],
            'name_ar'     => ['required', 'string', 'max:200'],
            'name_en'     => ['required', 'string', 'max:200'],
            'icon'        => ['required', 'string', 'max:100'],
            'color'       => ['required', 'string', 'max:30'],
            'bg'          => ['required', 'string', 'max:60'],
            'tag_ar'      => ['nullable', 'string', 'max:100'],
            'tag_en'      => ['nullable', 'string', 'max:100'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'images' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
