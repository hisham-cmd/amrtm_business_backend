<?php

namespace Tests\Unit;

use App\Support\ServiceCustomFields;
use PHPUnit\Framework\TestCase;

class ServiceCustomFieldsTest extends TestCase
{
    public function test_it_normalizes_and_orders_field_definitions(): void
    {
        $fields = ServiceCustomFields::normalize([
            [
                'key' => 'license_type',
                'type' => 'select',
                'label_ar' => 'نوع الرخصة',
                'required' => true,
                'sort_order' => 2,
                'options' => [['value' => 'commercial', 'label_ar' => 'تجارية']],
            ],
            [
                'key' => 'license_number',
                'type' => 'text',
                'label_ar' => 'رقم الرخصة',
                'min' => 5,
                'max' => 20,
                'sort_order' => 1,
            ],
        ]);

        $this->assertSame(['license_number', 'license_type'], array_column($fields, 'key'));
        $this->assertTrue($fields[1]['required']);
        $this->assertSame('تجارية', $fields[1]['options'][0]['label_en']);
    }

    public function test_it_builds_runtime_validation_rules(): void
    {
        $rules = ServiceCustomFields::validationRules([
            ['key' => 'amount', 'type' => 'number', 'label_ar' => 'المبلغ', 'required' => true, 'min' => 10, 'max' => 100],
            ['key' => 'approval', 'type' => 'checkbox', 'label_ar' => 'الموافقة', 'required' => true],
        ]);

        $this->assertSame(['required', 'numeric', 'min:10', 'max:100'], $rules['custom_fields.amount']);
        $this->assertSame(['required', 'boolean', 'accepted'], $rules['custom_fields.approval']);
    }

    public function test_it_supports_service_specific_file_uploads(): void
    {
        $rules = ServiceCustomFields::validationRules([
            ['key' => 'identity_copy', 'type' => 'file', 'label_ar' => 'صورة الهوية', 'required' => true],
        ]);

        $this->assertSame(
            ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            $rules['custom_fields.identity_copy']
        );
    }
}