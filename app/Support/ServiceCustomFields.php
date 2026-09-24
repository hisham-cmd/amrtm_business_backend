<?php

namespace App\Support;

use Illuminate\Support\Str;

final class ServiceCustomFields
{
    public const TYPES = ['text', 'textarea', 'number', 'email', 'tel', 'date', 'select', 'radio', 'checkbox', 'file'];

    /** @return array<int, array<string, mixed>> */
    public static function normalize(?array $fields): array
    {
        return collect($fields ?? [])
            ->values()
            ->map(function (array $field, int $index): array {
                $type = in_array($field['type'] ?? '', self::TYPES, true) ? $field['type'] : 'text';
                $labelAr = trim((string) ($field['label_ar'] ?? ''));
                $key = Str::snake(trim((string) ($field['key'] ?? $labelAr)));
                $key = preg_replace('/[^a-zA-Z0-9_]/', '', $key) ?: 'field_' . ($index + 1);

                $options = collect($field['options'] ?? [])
                    ->filter(fn($option) => is_array($option) && trim((string) ($option['label_ar'] ?? '')) !== '')
                    ->values()
                    ->map(fn(array $option, int $optionIndex) => [
                        'value' => trim((string) ($option['value'] ?? '')) ?: 'option_' . ($optionIndex + 1),
                        'label_ar' => trim((string) $option['label_ar']),
                        'label_en' => trim((string) ($option['label_en'] ?? $option['label_ar'])),
                    ])->all();

                return [
                    'key' => $key,
                    'type' => $type,
                    'label_ar' => $labelAr,
                    'label_en' => trim((string) ($field['label_en'] ?? $labelAr)),
                    'placeholder_ar' => trim((string) ($field['placeholder_ar'] ?? '')),
                    'placeholder_en' => trim((string) ($field['placeholder_en'] ?? '')),
                    'help_ar' => trim((string) ($field['help_ar'] ?? '')),
                    'help_en' => trim((string) ($field['help_en'] ?? '')),
                    'required' => filter_var($field['required'] ?? false, FILTER_VALIDATE_BOOL),
                    'min' => isset($field['min']) && $field['min'] !== '' ? (float) $field['min'] : null,
                    'max' => isset($field['max']) && $field['max'] !== '' ? (float) $field['max'] : null,
                    'options' => in_array($type, ['select', 'radio'], true) ? $options : [],
                    'sort_order' => (int) ($field['sort_order'] ?? $index),
                ];
            })
            ->sortBy('sort_order')
            ->values()
            ->all();
    }

    /** @return array<string, array<int, mixed>> */
    public static function validationRules(array $fields): array
    {
        $rules = [];

        foreach (self::normalize($fields) as $field) {
            $fieldRules = [$field['required'] ? 'required' : 'nullable'];

            if ($field['type'] === 'checkbox') {
                $fieldRules[] = 'boolean';
                if ($field['required']) {
                    $fieldRules[] = 'accepted';
                }
            } elseif ($field['type'] === 'file') {
                $fieldRules[] = 'file';
                $fieldRules[] = 'mimes:pdf,jpg,jpeg,png';
                $fieldRules[] = 'max:10240';
            } elseif ($field['type'] === 'number') {
                $fieldRules[] = 'numeric';
                if ($field['min'] !== null) $fieldRules[] = 'min:' . $field['min'];
                if ($field['max'] !== null) $fieldRules[] = 'max:' . $field['max'];
            } elseif ($field['type'] === 'email') {
                $fieldRules[] = 'email';
                $fieldRules[] = 'max:255';
            } elseif ($field['type'] === 'date') {
                $fieldRules[] = 'date';
            } elseif (in_array($field['type'], ['select', 'radio'], true)) {
                $values = array_column($field['options'], 'value');
                $fieldRules[] = 'string';
                if ($values) $fieldRules[] = 'in:' . implode(',', $values);
            } else {
                $fieldRules[] = 'string';
                if ($field['min'] !== null) $fieldRules[] = 'min:' . (int) $field['min'];
                $fieldRules[] = 'max:' . (int) ($field['max'] ?? ($field['type'] === 'textarea' ? 5000 : 500));
            }

            $rules['custom_fields.' . $field['key']] = $fieldRules;
        }

        return $rules;
    }
}
