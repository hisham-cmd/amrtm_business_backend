<?php

namespace App\Http\Requests\Business;

use App\Support\ServiceCustomFields;
use Illuminate\Foundation\Http\FormRequest;

class CreateGovServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entity_id'      => ['required', 'integer', 'exists:business.bs_entities,id'],
            'name_ar'        => ['required', 'string', 'max:200'],
            'name_en'        => ['required', 'string', 'max:200'],
            'icon'           => ['nullable', 'string', 'max:100'],
            'price'          => ['required', 'numeric', 'min:0'],
            'duration_min'   => ['required', 'integer', 'min:1'],
            'duration_max'   => ['required', 'integer', 'min:1', 'gte:duration_min'],
            'duration_unit'  => ['required', 'string', 'in:day,hour,week,month'],
            'description_ar' => ['nullable', 'string', 'max:1000'],
            'description_en' => ['nullable', 'string', 'max:1000'],
            'sort_order'     => ['nullable', 'integer', 'min:0'],
            'is_active'      => ['sometimes', 'boolean'],
            'specialty_ids'  => ['nullable', 'array', 'max:100'],
            'specialty_ids.*' => ['integer', 'exists:business.bs_specialties,id'],
            'custom_fields'                    => ['nullable', 'array', 'max:30'],
            'custom_fields.*.key'              => ['required', 'string', 'max:80', 'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/', 'distinct'],
            'custom_fields.*.type'             => ['required', 'string', 'in:' . implode(',', ServiceCustomFields::TYPES)],
            'custom_fields.*.label_ar'         => ['required', 'string', 'max:200'],
            'custom_fields.*.label_en'         => ['nullable', 'string', 'max:200'],
            'custom_fields.*.placeholder_ar'   => ['nullable', 'string', 'max:250'],
            'custom_fields.*.placeholder_en'   => ['nullable', 'string', 'max:250'],
            'custom_fields.*.help_ar'          => ['nullable', 'string', 'max:500'],
            'custom_fields.*.help_en'          => ['nullable', 'string', 'max:500'],
            'custom_fields.*.required'         => ['nullable', 'boolean'],
            'custom_fields.*.min'              => ['nullable', 'numeric'],
            'custom_fields.*.max'              => ['nullable', 'numeric'],
            'custom_fields.*.sort_order'       => ['nullable', 'integer', 'min:0'],
            'custom_fields.*.options'          => ['nullable', 'array', 'max:50'],
            'custom_fields.*.options.*.value'  => ['required_with:custom_fields.*.options', 'string', 'max:100'],
            'custom_fields.*.options.*.label_ar' => ['required_with:custom_fields.*.options', 'string', 'max:200'],
            'custom_fields.*.options.*.label_en' => ['nullable', 'string', 'max:200'],
        ];
    }
}
