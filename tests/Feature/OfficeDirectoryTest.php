<?php

namespace Tests\Feature;

use App\Models\Business\Office;
use App\Models\Business\OfficeService;
use App\Models\Business\Specialty;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OfficeDirectoryTest extends TestCase
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
        $suffix = substr(md5(uniqid()), 0, 6);
        return Office::query()->create(array_merge([
            'type'            => 'law',
            'name_ar'         => 'مكتب معتمد',
            'name_en'         => 'Certified Office',
            'phone'           => '0550000000',
            'email'           => 'law-' . $suffix . '@example.com',
            'is_active'       => true,
            'is_verified'     => true,
            'commission_rate' => 10,
        ], $overrides));
    }

    private function createOfficeService(int $officeId, int $specialtyId, array $overrides = []): OfficeService
    {
        return OfficeService::query()->create(array_merge([
            'office_id'         => $officeId,
            'specialty_id'      => $specialtyId,
            'source_service_id' => 777,
            'name_ar'           => 'استخراج جواز السفر',
            'name_en'           => 'Passport Issuance',
            'price'             => 300,
            'duration_unit'     => 'day',
            'duration_min'      => 5,
            'duration_max'      => 5,
            'is_active'         => true,
            'approval_status'   => 'approved',
        ], $overrides));
    }

    /** @test */
    public function the_specialty_directory_groups_shared_services_without_duplication(): void
    {
        $spec = Specialty::query()->create([
            'office_type' => 'law',
            'name_ar'     => 'تخصص قانوني موحد',
            'name_en'     => 'Unified Legal Specialty',
        ]);

        $supportingA = $this->createOffice([
            'name_ar'           => 'مكتب المساند الأول',
            'subscription_type' => 'commission',
        ]);
        $supportingB = $this->createOffice([
            'name_ar'           => 'مكتب المساند الثاني',
            'subscription_type' => 'commission',
        ]);

        $supportingA->specialtiesRelation()->attach($spec->id);
        $supportingB->specialtiesRelation()->attach($spec->id);

        $this->createOfficeService($supportingA->id, $spec->id);
        $this->createOfficeService($supportingB->id, $spec->id);

        $response = $this->get('/offices/law');
        $html = (string) $response->getContent();

        $response->assertStatus(200);
        $this->assertSame(1, substr_count($html, 'data-spec-id="' . $spec->id . '"'));
        $response->assertDontSee('مكتب المساند الأول');
        $response->assertDontSee('مكتب المساند الثاني');
        $this->assertSame(1, substr_count($html, 'data-services-ar="استخراج جواز السفر"'));
    }

    /** @test */
    public function the_specialty_detail_page_renders_deduplicated_shared_services(): void
    {
        $spec = Specialty::query()->create([
            'office_type' => 'law',
            'name_ar'     => 'تخصص قانوني موحد',
            'name_en'     => 'Unified Legal Specialty',
        ]);

        $supportingA = $this->createOffice([
            'name_ar'           => 'مكتب المساند الأول',
            'subscription_type' => 'commission',
        ]);
        $supportingB = $this->createOffice([
            'name_ar'           => 'مكتب المساند الثاني',
            'subscription_type' => 'commission',
        ]);

        $this->createOfficeService($supportingA->id, $spec->id);
        $this->createOfficeService($supportingB->id, $spec->id);

        $response = $this->get('/offices/law/' . $spec->id);
        $html      = (string) $response->getContent();

        $response->assertStatus(200);
        $response->assertSee('window.SPECIALTY_ID = ' . $spec->id . ';', false);
        $response->assertSee('(1 خدمة)');
        $this->assertSame(1, substr_count($html, 'data-ar="استخراج جواز السفر"'));
    }

    /** @test */
    public function a_legacy_office_id_url_redirects_to_its_primary_specialty(): void
    {
        $spec = Specialty::query()->create([
            'office_type' => 'law',
            'name_ar'     => 'تخصص قانوني موحد',
            'name_en'     => 'Unified Legal Specialty',
        ]);

        $supporting = $this->createOffice([
            'subscription_type' => 'commission',
        ]);
        $supporting->forceFill(['id' => 500])->save();
        $supporting = $supporting->fresh();

        $supporting->specialtiesRelation()->attach($spec->id);
        $this->createOfficeService($supporting->id, $spec->id);

        $this->get('/offices/law/' . $supporting->id)
            ->assertStatus(301)
            ->assertRedirect('/offices/law/' . $spec->id);
    }

    /** @test */
    public function a_consultant_service_is_not_listed_in_the_supporting_specialties(): void
    {
        $spec = Specialty::query()->create([
            'office_type' => 'law',
            'name_ar'     => 'تخصص مستشار مباشر',
            'name_en'     => 'Direct Consultant Specialty',
        ]);

        $consultant = $this->createOffice([
            'subscription_type' => 'subscription',
        ]);
        $this->createOfficeService($consultant->id, $spec->id);

        $this->get('/offices/law')
            ->assertStatus(200)
            ->assertDontSee('data-spec-id="' . $spec->id . '"', false);
    }
}