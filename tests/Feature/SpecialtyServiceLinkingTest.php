<?php

namespace Tests\Feature;

use App\Models\Business\Specialty;
use App\Models\Category;
use App\Models\Entity;
use App\Models\GovService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SpecialtyServiceLinkingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.business' => [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('business');

        Artisan::call('migrate', ['--database' => 'business', '--force' => true]);
    }

    private function seedCatalog(): array
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

        $svc = GovService::query()->create([
            'entity_id'      => $entity->id,
            'name_ar'        => 'إصدار سجل تجاري',
            'name_en'        => 'Commercial Register',
            'price'          => 300,
            'duration_min'   => 5,
            'duration_max'   => 5,
            'duration_unit'  => 'day',
            'is_active'      => true,
        ]);

        $specLaw = Specialty::query()->create([
            'office_type' => 'law',
            'name_ar'     => 'القضايا التجارية',
            'name_en'     => 'Commercial Cases',
            'is_active'   => true,
        ]);

        $specCustoms = Specialty::query()->create([
            'office_type' => 'customs',
            'name_ar'     => 'التخليص الجمركي',
            'name_en'     => 'Customs Clearance',
            'is_active'   => true,
        ]);

        return compact('cat', 'entity', 'svc', 'specLaw', 'specCustoms');
    }

    public function test_specialty_services_pivot_relations(): void
    {
        ['svc' => $svc, 'specLaw' => $specLaw, 'specCustoms' => $specCustoms] = $this->seedCatalog();

        $specLaw->services()->sync([$svc->id]);

        $this->assertCount(1, $specLaw->fresh()->services);
        $this->assertCount(1, $svc->fresh()->specialties);
        $this->assertNull($specCustoms->fresh()->services->first());
    }

    public function test_registration_specialties_endpoint_returns_linked_services(): void
    {
        ['svc' => $svc, 'specLaw' => $specLaw] = $this->seedCatalog();

        $specLaw->services()->sync([$svc->id]);

        $res = $this->getJson('/provider-account/specialties?office_type=law');

        $res->assertOk()->assertJson(['success' => true]);

        $specialties = $res->json('specialties');

        $this->assertNotEmpty($specialties);

        $linked = collect($specialties)->firstWhere('name_ar', 'القضايا التجارية');

        $this->assertNotNull($linked);
        $this->assertCount(1, $linked['services']);
        $this->assertEquals($svc->name_ar, $linked['services'][0]['name_ar']);
        $this->assertEquals(300, $linked['services'][0]['price']);
        $this->assertEquals('5 أيام', $linked['services'][0]['duration']);
        $this->assertEquals(5, $linked['services'][0]['duration_min']);
        $this->assertEquals(5, $linked['services'][0]['duration_max']);
        $this->assertEquals('day', $linked['services'][0]['duration_unit']);
    }

    public function test_specialties_without_links_fall_back_to_catalog(): void
    {
        $this->seedCatalog();

        $res = $this->getJson('/provider-account/specialties?office_type=customs');

        $res->assertOk()->assertJson(['success' => true]);

        $specialties = $res->json('specialties');

        $this->assertNotEmpty($specialties);

        $customs = collect($specialties)->firstWhere('name_ar', 'التخليص الجمركي');

        $this->assertNotNull($customs);
        $this->assertIsArray($customs['services']);
    }

    public function test_office_specialties_endpoint_returns_all(): void
    {
        $this->seedCatalog();

        $res = $this->getJson('/office/specialties');

        $res->assertOk();

        $data = $res->json();

        $this->assertGreaterThanOrEqual(2, count($data));

        $types = array_column($data, 'office_type');

        $this->assertContains('law', $types);
        $this->assertContains('customs', $types);
    }

    public function test_linked_service_inactive_is_excluded(): void
    {
        ['svc' => $svc, 'specLaw' => $specLaw] = $this->seedCatalog();

        $svc->update(['is_active' => false]);

        $specLaw->services()->sync([$svc->id]);

        $res = $this->getJson('/provider-account/specialties?office_type=law');

        $res->assertOk();

        $linked = collect($res->json('specialties'))->firstWhere('name_ar', 'القضايا التجارية');

        $this->assertNotNull($linked);
        $this->assertCount(0, $linked['services']);
    }
}