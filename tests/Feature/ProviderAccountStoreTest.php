<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ProviderAccountStoreTest extends TestCase
{
    private string $publicRoot;

    protected function setUp(): void
    {
        parent::setUp();

        // بيئة معزولة: نوجّه اتصال business إلى SQLite في الذاكرة بدلاً من MySQL
        // الحقيقي، ونجهّز public disk في مجلد مؤقت.
        config([
            'database.connections.business' => [
                'driver'                  => 'sqlite',
                'database'                => ':memory:',
                'prefix'                  => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('business');

        $this->publicRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'amrtm-test-docs-' . uniqid();
        (new \Illuminate\Filesystem\Filesystem())->ensureDirectoryExists($this->publicRoot);

        config([
            'filesystems.disks.public' => [
                'driver'     => 'local',
                'root'       => $this->publicRoot,
                'url'        => '/storage',
                'visibility' => 'public',
            ],
        ]);

        Artisan::call('migrate', ['--database' => 'business', '--force' => true]);
    }

    protected function tearDown(): void
    {
        $fs = new \Illuminate\Filesystem\Filesystem();
        if ($fs->exists($this->publicRoot ?? '')) {
            $fs->deleteDirectory($this->publicRoot);
        }

        parent::tearDown();
    }

    private function jpegUpload(string $name): UploadedFile
    {
        return UploadedFile::fake()->create($name, 16, 'image/jpeg');
    }

    private function pdfUpload(string $name): UploadedFile
    {
        return UploadedFile::fake()->create($name, 64, 'application/pdf');
    }

    /** @test */
    public function it_stores_a_consultant_engineering_office_end_to_end(): void
    {
        $email = 'eng_e2e_' . time() . '_' . random_int(100, 999) . '@test.local';

        $payload = [
            'name_ar'                => 'مكتب أفق الهندسة للاستشارات',
            'name_en'                => 'Horizon Engineering Consultants',
            'office_type'            => 'engineering',
            'entity_type'            => 'company',
            'subscription_type'      => 'subscription',
            'phone'                  => '0555001234',
            'email'                  => $email,
            'password'               => 'Secret12345!',
            'password_confirmation'  => 'Secret12345!',
            'country'                => 'السعودية',
            'governorate'            => 'الرياض',
            'city'                   => 'الرياض',
            'district'               => 'العليا',
            'street'                 => 'شارع التحلية',
            'building_number'        => '12',
            'office_number'          => '301',
            'cr_number'              => 'CR-889900',
            'license_number'         => 'EN-112233',
            'trademark_registration_number' => 'TM-556677',
            'specialty'              => 'other',
            'manual_specialty'       => 'استشارات إدارة المشاريع الهندسية',
            'services'               => [
                ['name_ar' => 'استشارة تصميم معمارية', 'name_en' => 'Architectural Design Consultancy', 'price' => 1500, 'duration' => '3 أيام'],
            ],
            'custom_services'        => [
                ['name_ar' => 'معاينة موقع', 'name_en' => 'Site Inspection', 'price' => 800, 'duration' => 'يوم'],
            ],
            'commercial_register_image' => $this->jpegUpload('cr.jpg'),
            'license_image'             => $this->jpegUpload('license.jpg'),
            'trademark_certificate'     => $this->jpegUpload('trademark.jpg'),
            'certificates'              => [
                $this->jpegUpload('cert1.jpg'),
            ],
            'appreciation_certificates' => [
                $this->jpegUpload('appr1.jpg'),
            ],
            'cv'                        => $this->pdfUpload('cv.pdf'),
        ];

        $response = $this->post(route('amrtm.provider.account.store', ['type' => 'consultant']), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');
        $response->assertRedirect(route('amrtm.provider.account.create'));

        $this->assertDatabaseHas('bs_offices', [
            'name_ar'          => 'مكتب أفق الهندسة للاستشارات',
            'type'             => 'engineering',
            'entity_type'      => 'company',
            'email'            => $email,
            'subscription_type'=> 'subscription',
            'is_active'        => 1,
        ], 'business');

        $office = DB::connection('business')->table('bs_offices')->where('email', $email)->first();
        $this->assertNotNull($office, 'Office row must exist');
        $this->assertMatchesRegularExpression('/^OFF-\d{6}$/', (string) $office->office_code);

        $accountTypes = json_decode((string) $office->account_types, true);
        $this->assertEquals(['consultant'], $accountTypes);

        $this->assertDatabaseHas('bs_office_users', [
            'office_id' => $office->id,
            'email'     => $email,
            'role'      => 'owner',
        ], 'business');

        $this->assertDatabaseHas('bs_office_profiles', [
            'office_id'                    => $office->id,
            'country'                      => 'السعودية',
            'custom_specialty'             => 'استشارات إدارة المشاريع الهندسية',
            'trademark_registration_number'=> 'TM-556677',
            'verification_status'          => 'pending',
        ], 'business');

        $this->assertSame(6, DB::connection('business')->table('bs_office_documents')->where('office_id', $office->id)->count());

        $this->assertSame(2, DB::connection('business')->table('bs_office_services')->where('office_id', $office->id)->count());

        // ملفات السجل والترخيص والسيرة الذاتية يجب أن تكون محفوظة فعلياً على القرص
        $docs = DB::connection('business')->table('bs_office_documents')->where('office_id', $office->id)->get();
        $storage = new \Illuminate\Filesystem\Filesystem();
        foreach ($docs as $doc) {
            $this->assertTrue($storage->exists($this->publicRoot . DIRECTORY_SEPARATOR . $doc->file), 'Uploaded file must exist on public disk: ' . $doc->file);
        }
    }

    /** @test */
    public function it_stores_consultant_without_office_type_defaulting_to_freelance(): void
    {
        $email = 'consultant_no_type_' . time() . '_' . random_int(100, 999) . '@test.local';

        $payload = [
            'name_ar'                   => 'مستشار أعمال مستقل',
            'name_en'                   => 'Independent Business Consultant',
            'account_type'              => 'consultant',
            'entity_type'               => 'institution',
            'subscription_type'         => 'subscription',
            'phone'                     => '0555003333',
            'email'                     => $email,
            'password'                  => 'Secret12345!',
            'password_confirmation'     => 'Secret12345!',
            'country'                   => 'السعودية',
            'governorate'               => 'الرياض',
            'city'                      => 'الرياض',
            'cr_number'                 => 'CR-CONSNO',
            'license_number'            => 'CN-LIC001',
            'specialty'                 => 'other',
            'manual_specialty'          => 'استشارات إدارية وتطوير الأعمال',
            'commercial_register_image' => $this->jpegUpload('cr.jpg'),
            'license_image'             => $this->jpegUpload('license.jpg'),
            'cv'                        => $this->pdfUpload('cv.pdf'),
        ];

        $response = $this->post(route('amrtm.provider.account.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $office = DB::connection('business')->table('bs_offices')->where('email', $email)->first();
        $this->assertNotNull($office);
        $this->assertSame('freelance', (string) $office->type);

        $accountTypes = json_decode((string) $office->account_types, true);
        $this->assertEquals(['consultant'], $accountTypes);
        $this->assertSame('subscription', (string) $office->subscription_type);
    }

    /** @test */
    public function it_fails_validation_when_required_fields_are_missing(): void
    {
        $response = $this->post(route('amrtm.provider.account.store'), [
            'name_ar' => 'مكتب تجريبي',
        ]);

        $response->assertSessionHasErrors(['name_en', 'office_type', 'entity_type', 'phone', 'email', 'password', 'cr_number', 'license_number', 'country', 'governorate', 'city', 'commercial_register_image', 'license_image', 'cv']);
        $this->assertSame(0, DB::connection('business')->table('bs_offices')->count());
    }

    /** @test */
    public function it_returns_json_redirect_on_success_when_the_request_expects_json(): void
    {
        $email = 'json_' . time() . '_' . random_int(100, 999) . '@test.local';

        $payload = [
            'name_ar'                   => 'مكتب التجارب الجاهزة',
            'name_en'                   => 'Ready Trials Office',
            'office_type'               => 'engineering',
            'entity_type'               => 'company',
            'subscription_type'         => 'subscription',
            'phone'                     => '0555007777',
            'email'                     => $email,
            'password'                  => 'Secret12345!',
            'password_confirmation'     => 'Secret12345!',
            'country'                   => 'السعودية',
            'governorate'               => 'الرياض',
            'city'                      => 'الرياض',
            'cr_number'                 => 'CR-JSON99',
            'license_number'            => 'EN-JSON77',
            'specialty'                 => 'other',
            'manual_specialty'          => 'استشارات هندسية عامة',
            'commercial_register_image' => $this->jpegUpload('cr.jpg'),
            'license_image'             => $this->jpegUpload('license.jpg'),
            'cv'                        => $this->pdfUpload('cv.pdf'),
        ];

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post(route('amrtm.provider.account.store', ['type' => 'consultant']), $payload);

        $response->assertOk();
        $response->assertJson(fn (AssertableJson $json) => $json
            ->where('redirect', route('amrtm.provider.account.create'))
        );
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bs_offices', [
            'email' => $email,
            'type'  => 'engineering',
        ], 'business');
    }

    /** @test */
    public function it_returns_422_json_with_field_errors_when_json_validation_fails(): void
    {
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post(route('amrtm.provider.account.store'), [
                'name_ar' => 'مكتب تجريبي',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['commercial_register_image', 'license_image', 'cv']);
    }

    /** @test */
    public function it_rejects_duplicate_email(): void
    {
        $email = 'dup_' . time() . '@test.local';

        $base = [
            'name_ar' => 'مكتب أول',
            'name_en' => 'Office One',
            'office_type' => 'law',
            'entity_type' => 'institution',
            'phone' => '0501112222',
            'email' => $email,
            'password' => 'Secret12345!',
            'password_confirmation' => 'Secret12345!',
            'country' => 'السعودية',
            'governorate' => 'الرياض',
            'city' => 'الرياض',
            'cr_number' => 'CR-111222',
            'license_number' => 'LA-333444',
            'specialty' => 'other',
            'manual_specialty' => 'قضايا تجارية',
            'commercial_register_image' => $this->jpegUpload('cr.jpg'),
            'license_image' => $this->jpegUpload('license.jpg'),
            'cv' => $this->pdfUpload('cv.pdf'),
        ];

        $this->post(route('amrtm.provider.account.store'), $base)->assertSessionHasNoErrors();

        $this->post(route('amrtm.provider.account.store'), $base)
            ->assertSessionHasErrors(['email']);

        $this->assertSame(1, DB::connection('business')->table('bs_offices')->count());
    }

    /** @test */
    public function it_stores_a_mixed_support_and_consultant_facility(): void
    {
        $email = 'mixed_' . time() . '_' . random_int(100, 999) . '@test.local';

        $payload = [
            'name_ar'                => 'منشأة النهضة المساندة والاستشارية',
            'name_en'                => 'Nahda Support & Consulting Facility',
            'office_type'            => 'services',
            'entity_type'            => 'company',
            'subscription_type'      => 'subscription',
            'account_type'           => 'mixed',
            'phone'                  => '0555009000',
            'email'                  => $email,
            'password'               => 'Secret12345!',
            'password_confirmation'  => 'Secret12345!',
            'country'                => 'السعودية',
            'governorate'            => 'جدة',
            'city'                   => 'جدة',
            'district'               => 'الشاطئ',
            'street'                 => 'طريق الأمير سلطان',
            'building_number'        => '45',
            'office_number'          => '102',
            'cr_number'              => 'CR-MIXED99',
            'license_number'         => 'SV-998877',
            'specialty'              => 'other',
            'manual_specialty'       => 'خدمات مساندة واستشارات إدارية',
            'commercial_register_image' => $this->jpegUpload('cr.jpg'),
            'license_image'             => $this->jpegUpload('license.jpg'),
            'cv'                        => $this->pdfUpload('cv.pdf'),
        ];

        $response = $this->post(route('amrtm.provider.account.store', ['type' => 'mixed']), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('amrtm.provider.account.create'));

        $office = DB::connection('business')->table('bs_offices')->where('email', $email)->first();
        $this->assertNotNull($office);

        $accountTypes = json_decode((string) $office->account_types, true);
        $this->assertEquals(['support_office', 'consultant'], $accountTypes);
        $this->assertSame('subscription', (string) $office->subscription_type);
    }

    /** @test */
    public function it_persists_catalog_and_custom_services_with_approval_rules(): void
    {
        $entity = \App\Models\Entity::query()->create([
            'category_id' => \App\Models\Category::query()->create([
                'key'     => 'ministries',
                'name_ar' => 'الوزارات',
                'name_en' => 'Ministries',
            ])->id,
            'name_ar'   => 'وزارة التجارة',
            'name_en'   => 'Ministry of Commerce',
            'is_active' => true,
        ]);

        $specialty = \App\Models\Business\Specialty::query()->create([
            'office_type' => 'law',
            'name_ar'     => 'القضايا التجارية',
            'name_en'     => 'Commercial Cases',
            'is_active'   => true,
        ]);

        $svc = \App\Models\GovService::query()->create([
            'entity_id'      => $entity->id,
            'name_ar'        => 'إصدار سجل تجاري',
            'name_en'        => 'Commercial Register',
            'price'          => 300,
            'duration_min'   => 5,
            'duration_max'   => 5,
            'duration_unit'  => 'day',
            'is_active'      => true,
            'custom_fields'  => [
                ['label' => 'صورة الهوية', 'required' => true],
            ],
        ]);

        $specialty->services()->sync([$svc->id]);

        $email = 'approval_' . time() . '_' . random_int(100, 999) . '@test.local';

        $payload = [
            'name_ar'                   => 'مكتب الاعتماد التجريبي',
            'name_en'                   => 'Approval Trial Office',
            'office_type'               => 'law',
            'entity_type'               => 'company',
            'subscription_type'         => 'subscription',
            'phone'                     => '0555001234',
            'email'                     => $email,
            'password'                  => 'Secret12345!',
            'password_confirmation'     => 'Secret12345!',
            'country'                   => 'السعودية',
            'governorate'               => 'الرياض',
            'city'                      => 'الرياض',
            'cr_number'                 => 'CR-APPRV99',
            'license_number'            => 'LA-APPRV77',
            'specialty'                 => (string) $specialty->id,
            'manual_specialty'          => '',
            'services'                  => [
                [
                    'name_ar'           => $svc->name_ar,
                    'name_en'           => $svc->name_en,
                    'price'             => 350,
                    'duration'          => '5 يوم',
                    'requirements'      => 'صورة الهوية',
                    'specialty_id'      => $specialty->id,
                    'entity_id'         => $entity->id,
                    'source_service_id' => $svc->id,
                ],
            ],
            'custom_services'           => [
                [
                    'name_ar'      => 'معاينة موقع للمنازعات',
                    'name_en'      => 'Site Inspection for Disputes',
                    'price'        => 800,
                    'duration'     => 'يومان',
                    'requirements' => 'موافقة مسبقة',
                    'specialty_id' => $specialty->id,
                ],
            ],
            'commercial_register_image' => $this->jpegUpload('cr.jpg'),
            'license_image'             => $this->jpegUpload('license.jpg'),
            'cv'                        => $this->pdfUpload('cv.pdf'),
        ];

        $this->post(route('amrtm.provider.account.store'), $payload)->assertSessionHasNoErrors();

        $office = DB::connection('business')->table('bs_offices')->where('email', $email)->first();
        $this->assertNotNull($office);

        $rows = DB::connection('business')
            ->table('bs_office_services')
            ->where('office_id', $office->id)
            ->orderBy('source_type')
            ->get();

        $this->assertCount(2, $rows);

        $catalog = $rows->firstWhere('source_type', 'catalog');
        $this->assertNotNull($catalog);
        $this->assertSame((string) $svc->id, (string) $catalog->source_service_id);
        $this->assertSame('approved', $catalog->approval_status);
        $this->assertSame(1, (int) $catalog->is_active);
        $this->assertSame((int) $specialty->id, (int) $catalog->specialty_id);
        $this->assertSame((int) $entity->id, (int) $catalog->entity_id);
        $this->assertSame(350.0, (float) $catalog->price);
        $this->assertSame('صورة الهوية', $catalog->requirements);

        $custom = $rows->firstWhere('source_type', 'custom');
        $this->assertNotNull($custom);
        $this->assertSame('pending', $custom->approval_status);
        $this->assertSame(0, (int) $custom->is_active);
        $this->assertSame((int) $specialty->id, (int) $custom->specialty_id);
        $this->assertSame(800.0, (float) $custom->price);
    }

    /** @test */
    public function it_persists_per_service_custom_fields_for_catalog_and_custom_services(): void
    {
        $entity = \App\Models\Entity::query()->create([
            'category_id' => \App\Models\Category::query()->create([
                'key'     => 'interior',
                'name_ar' => 'الديكور',
                'name_en' => 'Interior Design',
            ])->id,
            'name_ar'   => 'هيئة التصميم الداخلي',
            'name_en'   => 'Interior Design Authority',
            'is_active' => true,
        ]);

        $specialty = \App\Models\Business\Specialty::query()->create([
            'office_type' => 'engineering',
            'name_ar'     => 'التصميم الداخلي',
            'name_en'     => 'Interior Design',
            'is_active'   => true,
        ]);

        $svc = \App\Models\GovService::query()->create([
            'entity_id'      => $entity->id,
            'name_ar'        => 'رخصة ديكور',
            'name_en'        => 'Decor License',
            'price'          => 400,
            'duration_min'   => 5,
            'duration_max'   => 5,
            'duration_unit'  => 'day',
            'is_active'      => true,
            'custom_fields'  => [
                ['label' => 'صورة الهوية', 'required' => true],
            ],
        ]);

        $specialty->services()->sync([$svc->id]);

        $email = 'fields_' . time() . '_' . random_int(100, 999) . '@test.local';

        $payload = [
            'name_ar'                   => 'مكتب الحقول المخصصة',
            'name_en'                   => 'Custom Fields Office',
            'office_type'               => 'engineering',
            'entity_type'               => 'company',
            'subscription_type'         => 'subscription',
            'phone'                     => '0555005555',
            'email'                     => $email,
            'password'                  => 'Secret12345!',
            'password_confirmation'     => 'Secret12345!',
            'country'                   => 'السعودية',
            'governorate'               => 'الرياض',
            'city'                      => 'الرياض',
            'cr_number'                 => 'CR-FIELDS1',
            'license_number'            => 'EN-FIELDS2',
            'specialty'                 => (string) $specialty->id,
            'manual_specialty'          => '',
            'services'                  => [
                [
                    'name_ar'           => $svc->name_ar,
                    'name_en'           => $svc->name_en,
                    'price'             => 450,
                    'duration'          => '5 يوم',
                    'specialty_id'      => $specialty->id,
                    'entity_id'         => $entity->id,
                    'source_service_id' => $svc->id,
                    'custom_fields'     => [
                        [
                            'key'            => 'sqm',
                            'type'           => 'number',
                            'label_ar'       => 'المساحة بالمتر المربع',
                            'label_en'       => 'Area in sqm',
                            'placeholder_ar' => 'مثال: 120',
                            'required'       => true,
                            'min'            => 5,
                            'max'            => 2000,
                            'sort_order'     => 1,
                            'options'        => [],
                        ],
                        [
                            'key'            => 'room_type',
                            'type'           => 'select',
                            'label_ar'       => 'نوع الغرفة',
                            'required'       => false,
                            'sort_order'     => 2,
                            'options'        => [
                                ['value' => 'living', 'label_ar' => 'معيشة', 'label_en' => 'Living'],
                                ['value' => 'bedroom', 'label_ar' => 'نوم', 'label_en' => 'Bedroom'],
                            ],
                        ],
                    ],
                ],
            ],
            'custom_services'           => [
                [
                    'name_ar'       => 'تنسيق أثاث',
                    'name_en'       => 'Furniture Styling',
                    'price'         => 600,
                    'duration'      => '3 أيام',
                    'specialty_id'  => $specialty->id,
                    'custom_fields' => [
                        [
                            'key'        => 'furniture_count',
                            'type'       => 'text',
                            'label_ar'   => 'عدد قطع الأثاث',
                            'required'   => true,
                            'sort_order' => 0,
                        ],
                    ],
                ],
            ],
            'commercial_register_image' => $this->jpegUpload('cr.jpg'),
            'license_image'             => $this->jpegUpload('license.jpg'),
            'cv'                        => $this->pdfUpload('cv.pdf'),
        ];

        $this->post(route('amrtm.provider.account.store'), $payload)->assertSessionHasNoErrors();

        $office = DB::connection('business')->table('bs_offices')->where('email', $email)->first();
        $this->assertNotNull($office);

        $rows = DB::connection('business')
            ->table('bs_office_services')
            ->where('office_id', $office->id)
            ->get();

        $this->assertCount(2, $rows);

        $catalog = $rows->firstWhere('source_type', 'catalog');
        $this->assertNotNull($catalog);

        $catalogFields = json_decode((string) $catalog->custom_fields, true);
        $this->assertIsArray($catalogFields);
        $this->assertCount(2, $catalogFields);
        $this->assertSame(['sqm', 'room_type'], array_column($catalogFields, 'key'));
        $this->assertSame('number', $catalogFields[0]['type']);
        $this->assertSame('المساحة بالمتر المربع', $catalogFields[0]['label_ar']);
        $this->assertTrue($catalogFields[0]['required']);
        $this->assertSame(['living', 'bedroom'], array_column($catalogFields[1]['options'], 'value'));

        $custom = $rows->firstWhere('source_type', 'custom');
        $this->assertNotNull($custom);

        $customFields = json_decode((string) $custom->custom_fields, true);
        $this->assertIsArray($customFields);
        $this->assertCount(1, $customFields);
        $this->assertSame('عدد قطع الأثاث', $customFields[0]['label_ar']);
        $this->assertTrue($customFields[0]['required']);
    }

    /** @test */
    public function it_rejects_invalid_custom_field_definitions_on_services(): void
    {
        $response = $this->post(route('amrtm.provider.account.store'), [
            'name_ar'                   => 'مكتب تعريفات خاطئة',
            'name_en'                   => 'Wrong Definitions Office',
            'office_type'               => 'engineering',
            'entity_type'               => 'company',
            'subscription_type'         => 'subscription',
            'phone'                     => '0555006666',
            'email'                     => 'badfields_' . time() . '@test.local',
            'password'                  => 'Secret12345!',
            'password_confirmation'     => 'Secret12345!',
            'country'                   => 'السعودية',
            'governorate'               => 'الرياض',
            'city'                      => 'الرياض',
            'cr_number'                 => 'CR-BAD001',
            'license_number'            => 'EN-BAD002',
            'specialty'                 => 'other',
            'manual_specialty'          => 'تخصص مخصص للتجربة',
            'custom_services'           => [
                [
                    'name_ar'  => 'خدمة خاطئة',
                    'name_en'  => 'Invalid Service',
                    'price'    => 100,
                    'custom_fields' => [
                        ['type' => 'text', 'label_ar' => ''],        // label مفقود
                    ],
                ],
                [
                    'name_ar'  => 'خدمة خاطئة 2',
                    'name_en'  => 'Invalid Service 2',
                    'price'    => 100,
                    'custom_fields' => [
                        ['type' => 'select', 'label_ar' => 'قائمة', 'options' => [['label_ar' => 'خيار']]], // value مفقود
                    ],
                ],
            ],
            'commercial_register_image' => $this->jpegUpload('cr.jpg'),
            'license_image'             => $this->jpegUpload('license.jpg'),
            'cv'                        => $this->pdfUpload('cv.pdf'),
        ]);

        $response->assertSessionHasErrors([
            'custom_services.0.custom_fields.0.label_ar',
            'custom_services.1.custom_fields.0.options.0.value',
        ]);

        $this->assertSame(0, DB::connection('business')->table('bs_offices')->count());
    }
}