<?php

namespace Tests\Feature;

use App\Models\Business\Office;
use App\Models\Business\OfficeService;
use App\Models\Business\OfficeUser;
use App\Models\Category;
use App\Models\Entity;
use App\Models\GovService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OfficeServicePricingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.business' => [
                'driver'                  => 'sqlite',
                'database'                => ':memory:',
                'prefix'                  => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('business');

        Artisan::call('migrate', ['--database' => 'business', '--force' => true]);
    }

    private function createOffice(array $overrides = []): Office
    {
        return Office::query()->create(array_merge([
            'type'              => 'services',
            'name_ar'           => 'مكتب الخدمات',
            'name_en'           => 'Services Office',
            'phone'             => '0550000000',
            'email'             => 'office@example.com',
            'is_active'         => true,
            'is_verified'       => true,
            'commission_rate'   => 10,
            'subscription_type' => 'commission',
        ], $overrides));
    }

    private function createOfficeUser(int $officeId): OfficeUser
    {
        return OfficeUser::query()->create([
            'office_id' => $officeId,
            'name'      => 'مدير المكتب',
            'email'     => 'owner@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'owner',
            'is_active' => true,
        ]);
    }

    private function createSourceService(?array $customFields = null): GovService
    {
        $cat = Category::query()->create([
            'key'     => 'ministries',
            'name_ar' => 'الوزارات',
            'name_en' => 'Ministries',
        ]);

        $entity = Entity::query()->create([
            'category_id' => $cat->id,
            'name_ar'     => 'وزارة التجارة',
            'name_en'     => 'Ministry of Commerce',
            'is_active'   => true,
        ]);

        $payload = [
            'entity_id'      => $entity->id,
            'name_ar'        => 'إصدار سجل تجاري',
            'name_en'        => 'Commercial Register',
            'price'          => 300,
            'duration_min'   => 3,
            'duration_max'   => 5,
            'duration_unit'  => 'day',
            'is_active'      => true,
        ];

        if ($customFields !== null) {
            $payload['custom_fields'] = $customFields;
        }

        return GovService::query()->create($payload);
    }

    public function test_supporting_office_link_keeps_source_price_and_duration(): void
    {
        $office = $this->createOffice();
        $user   = $this->createOfficeUser($office->id);
        $source = $this->createSourceService();

        $this->actingAs($user, 'office')
            ->postJson('/office/api/link-catalog-service', [
                'source_service_id' => $source->id,
                'price'             => 9999,
                'duration_min'      => 99,
                'duration_max'      => 100,
                'duration_unit'     => 'hour',
            ])
            ->assertCreated()
            ->assertJsonPath('service.source_type', 'catalog')
            ->assertJsonPath('service.approval_status', 'approved')
            ->assertJsonPath('service.price', 300);

        $this->assertDatabaseHas('bs_office_services', [
            'office_id'         => $office->id,
            'source_service_id' => $source->id,
            'price'             => 300,
            'duration_min'      => 3,
            'duration_max'      => 5,
            'duration_unit'     => 'day',
        ], 'business');
    }

    public function test_supporting_office_catalog_update_keeps_source_price_and_duration(): void
    {
        $office = $this->createOffice();
        $user   = $this->createOfficeUser($office->id);
        $source = $this->createSourceService();

        $service = OfficeService::query()->create([
            'office_id'         => $office->id,
            'source_service_id' => $source->id,
            'entity_id'         => $source->entity_id,
            'name_ar'           => $source->name_ar,
            'name_en'           => $source->name_en,
            'price'             => $source->price,
            'duration_min'      => $source->duration_min,
            'duration_max'      => $source->duration_max,
            'duration_unit'     => $source->duration_unit,
            'source_type'       => 'catalog',
            'approval_status'   => 'approved',
            'is_active'         => true,
            'sort_order'        => 0,
        ]);

        $this->actingAs($user, 'office')
            ->putJson('/office/api/services/' . $service->id, [
                'price'          => 7777,
                'duration_min'   => 1,
                'duration_max'   => 2,
                'duration_unit'  => 'hour',
                'requirements'   => 'بطاقة الهوية الوطنية',
                'is_active'      => true,
            ])
            ->assertOk();

        $this->assertDatabaseHas('bs_office_services', [
            'id'            => $service->id,
            'price'         => 300,
            'duration_min'  => 3,
            'duration_max'  => 5,
            'duration_unit' => 'day',
            'requirements'  => 'بطاقة الهوية الوطنية',
        ], 'business');
    }

    public function test_consultant_office_link_honors_request_price(): void
    {
        $office = $this->createOffice(['subscription_type' => 'subscription']);
        $user   = $this->createOfficeUser($office->id);
        $source = $this->createSourceService();

        $this->actingAs($user, 'office')
            ->postJson('/office/api/link-catalog-service', [
                'source_service_id' => $source->id,
                'price'             => 150,
                'duration_min'      => 1,
                'duration_max'      => 2,
                'duration_unit'     => 'week',
            ])
            ->assertCreated()
            ->assertJsonPath('service.price', 150);

        $this->assertDatabaseHas('bs_office_services', [
            'office_id'         => $office->id,
            'source_service_id' => $source->id,
            'price'             => 150,
            'duration_min'      => 1,
            'duration_max'      => 2,
            'duration_unit'     => 'week',
        ], 'business');
    }

    public function test_supporting_office_custom_service_keeps_own_price_and_duration(): void
    {
        $office = $this->createOffice();
        $user   = $this->createOfficeUser($office->id);

        $this->actingAs($user, 'office')
            ->postJson('/office/api/services', [
                'name_ar'       => 'صياغة عقد تجاري',
                'name_en'       => 'Commercial Contract Drafting',
                'price'         => 450,
                'duration_min'  => 2,
                'duration_max'  => 4,
                'duration_unit' => 'day',
            ])
            ->assertCreated()
            ->assertJsonPath('service.source_type', 'custom')
            ->assertJsonPath('service.approval_status', 'pending')
            ->assertJsonPath('service.price', 450);

        $this->assertDatabaseHas('bs_office_services', [
            'office_id'       => $office->id,
            'price'           => 450,
            'duration_min'    => 2,
            'duration_max'    => 4,
            'duration_unit'   => 'day',
            'source_type'     => 'custom',
            'approval_status' => 'pending',
            'is_active'       => false,
        ], 'business');
    }

    public function test_custom_service_persists_and_returns_custom_fields(): void
    {
        $office = $this->createOffice();
        $user   = $this->createOfficeUser($office->id);

        $fields = [
            ['key' => 'national_id', 'type' => 'text', 'label_ar' => 'رقم الهوية', 'label_en' => 'National ID', 'required' => true, 'sort_order' => 0],
            ['key' => 'cr_number', 'type' => 'text', 'label_ar' => 'رقم السجل التجاري', 'sort_order' => 1],
        ];

        $this->actingAs($user, 'office')
            ->postJson('/office/api/services', [
                'name_ar'       => 'تأسيس شركة',
                'name_en'       => 'Company Formation',
                'price'         => 500,
                'duration_min'  => 5,
                'duration_max'  => 7,
                'duration_unit' => 'day',
                'custom_fields' => $fields,
            ])
            ->assertCreated()
            ->assertJsonPath('service.source_type', 'custom')
            ->assertJsonPath('service.custom_fields.0.key', 'national_id')
            ->assertJsonPath('service.custom_fields.0.required', true)
            ->assertJsonPath('service.custom_fields.1.key', 'cr_number');

        $stored = OfficeService::where('office_id', $office->id)->first();
        $this->assertIsArray($stored->custom_fields);
        $this->assertCount(2, $stored->custom_fields);
        $this->assertEquals('national_id', $stored->custom_fields[0]['key']);
    }

    public function test_catalog_link_copies_source_custom_fields(): void
    {
        $office = $this->createOffice();
        $user   = $this->createOfficeUser($office->id);
        $source = $this->createSourceService([
            ['key' => 'id_copy', 'type' => 'file', 'label_ar' => 'صورة الهوية', 'required' => true, 'sort_order' => 0],
        ]);

        $this->actingAs($user, 'office')
            ->postJson('/office/api/link-catalog-service', [
                'source_service_id' => $source->id,
                'price'             => 300,
            ])
            ->assertCreated()
            ->assertJsonPath('service.custom_fields.0.key', 'id_copy');

        $stored = OfficeService::where('office_id', $office->id)
            ->where('source_service_id', $source->id)
            ->first();
        $this->assertIsArray($stored->custom_fields);
        $this->assertEquals('id_copy', $stored->custom_fields[0]['key']);
        $this->assertEquals('file', $stored->custom_fields[0]['type']);
    }

    public function test_catalog_update_keeps_source_custom_fields_for_consultant_office(): void
    {
        $office = $this->createOffice(['subscription_type' => 'subscription']);
        $user   = $this->createOfficeUser($office->id);
        $source = $this->createSourceService([
            ['key' => 'id_copy', 'type' => 'file', 'label_ar' => 'صورة الهوية', 'sort_order' => 0],
        ]);

        $service = OfficeService::query()->create([
            'office_id'         => $office->id,
            'source_service_id' => $source->id,
            'entity_id'         => $source->entity_id,
            'name_ar'           => $source->name_ar,
            'name_en'           => $source->name_en,
            'price'             => $source->price,
            'duration_min'      => $source->duration_min,
            'duration_max'      => $source->duration_max,
            'duration_unit'     => $source->duration_unit,
            'custom_fields'     => $source->custom_fields,
            'source_type'       => 'catalog',
            'approval_status'   => 'approved',
            'is_active'         => true,
            'sort_order'        => 0,
        ]);

        $this->actingAs($user, 'office')
            ->putJson('/office/api/services/' . $service->id, [
                'price'          => 250,
                'is_active'      => true,
                'custom_fields'  => [
                    ['key' => 'hacked', 'type' => 'text', 'label_ar' => 'حقل دخيل', 'sort_order' => 0],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('bs_office_services', [
            'id'    => $service->id,
            'price' => 250,
        ], 'business');

        $stored = OfficeService::find($service->id);
        $this->assertEquals('id_copy', $stored->custom_fields[0]['key']);
    }

    public function test_custom_service_update_persists_custom_fields(): void
    {
        $office = $this->createOffice();
        $user   = $this->createOfficeUser($office->id);

        $service = OfficeService::query()->create([
            'office_id'       => $office->id,
            'name_ar'         => 'صياغة عقد',
            'name_en'         => 'Contract Drafting',
            'price'           => 300,
            'duration_min'    => 1,
            'duration_max'    => 2,
            'duration_unit'   => 'day',
            'source_type'     => 'custom',
            'approval_status' => 'pending',
            'is_active'       => false,
            'sort_order'      => 0,
        ]);

        $this->actingAs($user, 'office')
            ->putJson('/office/api/services/' . $service->id, [
                'name_ar'       => 'صياغة عقد تجاري',
                'name_en'       => 'Commercial Contract Drafting',
                'price'         => 450,
                'is_active'     => false,
                'custom_fields' => [
                    ['key' => 'party_name', 'type' => 'text', 'label_ar' => 'اسم الطرف', 'required' => true, 'sort_order' => 0],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('service.custom_fields.0.key', 'party_name');

        $stored = OfficeService::find($service->id);
        $this->assertIsArray($stored->custom_fields);
        $this->assertEquals('party_name', $stored->custom_fields[0]['key']);
        $this->assertEquals(true, $stored->custom_fields[0]['required']);
    }

    public function test_custom_service_rejects_invalid_field_key(): void
    {
        $office = $this->createOffice();
        $user   = $this->createOfficeUser($office->id);

        $this->actingAs($user, 'office')
            ->postJson('/office/api/services', [
                'name_ar'       => 'خدمة اختبار',
                'name_en'       => 'Test Service',
                'price'         => 100,
                'custom_fields' => [
                    ['key' => 'invalid key!', 'type' => 'text', 'label_ar' => 'حقل خاطئ', 'sort_order' => 0],
                ],
            ])
            ->assertStatus(422);
    }
}