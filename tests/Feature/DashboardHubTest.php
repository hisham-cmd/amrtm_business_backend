<?php

namespace Tests\Feature;

use App\Models\Business\BusinessUser;
use App\Models\Business\Office;
use App\Models\Business\OfficeUser;
use App\Models\TypeInterface;
use App\Support\DashboardRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardHubTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.business' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('business');

        Artisan::call('migrate', ['--database' => 'business', '--force' => true]);
    }

    private function createUser(array $overrides = []): BusinessUser
    {
        return BusinessUser::query()->create(array_merge([
            'name' => 'مستخدم اختبار',
            'email' => 'user@example.com',
            'phone' => '0550000000',
            'password' => Hash::make('secret123'),
            'role' => 'user',
            'account_type' => 'individual',
            'is_active' => true,
        ], $overrides));
    }

    /** @test */
    public function guest_is_redirected_to_login_from_the_hub(): void
    {
        $this->get(route('amrtm.dashboard.hub'))
            ->assertRedirect(route('amrtm.login'));
    }

    /** @test */
    public function an_individual_client_can_open_the_unified_hub(): void
    {
        $user = $this->createUser(['account_type' => 'individual']);

        $this->actingAs($user, 'business')
            ->get(route('amrtm.dashboard.hub'))
            ->assertOk()
            ->assertViewHas('persona', function ($persona) {
                return $persona['types'] === [DashboardRegistry::TYPE_INDIVIDUAL];
            })
            ->assertViewHas('dashboardMenu')
            ->assertSee('لوحة التحكم الموحّدة');
    }

    /** @test */
    public function a_supervisor_can_open_the_org_structure_page(): void
    {
        $supervisor = $this->createUser(['role' => 'supervisor']);

        $this->actingAs($supervisor, 'business')
            ->get(route('amrtm.admin.org-structure'))
            ->assertOk()
            ->assertSee('الهيكل التنظيمي')
            ->assertViewHas('matrix');
    }

    /** @test */
    public function a_normal_client_cannot_open_the_org_structure_page(): void
    {
        $client = $this->createUser();

        $this->actingAs($client, 'business')
            ->get(route('amrtm.admin.org-structure'))
            ->assertForbidden();
    }

    /** @test */
    public function supervisor_can_toggle_an_interface_for_a_type(): void
    {
        $supervisor = $this->createUser(['role' => 'supervisor']);

        $this->actingAs($supervisor, 'business')
            ->postJson(route('amrtm.admin.org-structure.toggle'), [
                'type_key' => DashboardRegistry::TYPE_SUPPORT_OFFICE,
                'interface_key' => 'services',
                'enabled' => false,
            ])
            ->assertOk()
            ->assertJson(['isSuccess' => true]);

        $this->assertDatabaseHas('bs_type_interfaces', [
            'type_key' => DashboardRegistry::TYPE_SUPPORT_OFFICE,
            'interface_key' => 'services',
            'is_enabled' => 0,
        ], 'business');

        // إعادة التفعيل مرة أخرى (updateOrCreate لا يكسر)
        $this->actingAs($supervisor, 'business')
            ->postJson(route('amrtm.admin.org-structure.toggle'), [
                'type_key' => DashboardRegistry::TYPE_SUPPORT_OFFICE,
                'interface_key' => 'services',
                'enabled' => true,
            ])
            ->assertOk();

        $this->assertTrue((bool) TypeInterface::query()
            ->where('type_key', DashboardRegistry::TYPE_SUPPORT_OFFICE)
            ->where('interface_key', 'services')
            ->value('is_enabled'));
    }

    /** @test */
    public function toggle_rejects_unknown_type_or_interface(): void
    {
        $supervisor = $this->createUser(['role' => 'supervisor']);

        $this->actingAs($supervisor, 'business')
            ->postJson(route('amrtm.admin.org-structure.toggle'), [
                'type_key' => 'nonexistent',
                'interface_key' => 'services',
                'enabled' => false,
            ])
            ->assertStatus(422);
    }

    /** @test */
    public function defaults_apply_when_no_override_row_exists()
    {
        $enabled = DashboardRegistry::enabledInterfaces([DashboardRegistry::TYPE_INDIVIDUAL]);

        $this->assertContains('overview', $enabled);
        $this->assertContains('my_requests', $enabled);
        $this->assertNotContains('analytics', $enabled);
    }

    /** @test */
    public function a_multi_type_facility_gets_the_union_of_its_types()
    {
        // نشط افتراضياً فقط للمساند، والاستشارات للاستشاري — المنشأة المركبة ترثهما.
        $enabledSupport = DashboardRegistry::enabledInterfaces([DashboardRegistry::TYPE_SUPPORT_OFFICE]);
        $enabledConsultant = DashboardRegistry::enabledInterfaces([DashboardRegistry::TYPE_CONSULTANT]);

        $union = DashboardRegistry::enabledInterfaces([
            DashboardRegistry::TYPE_SUPPORT_OFFICE,
            DashboardRegistry::TYPE_CONSULTANT,
        ]);

        foreach (array_merge($enabledSupport, $enabledConsultant) as $key) {
            $this->assertContains($key, $union, "Missing persisted={$key}");
        }

        $this->assertNotContains('analytics', $union);
        $this->assertContains('consultations', $union);
    }

    /** @test */
    public function the_mixed_type_exists_with_union_defaults()
    {
        $this->assertContains(DashboardRegistry::TYPE_MIXED, DashboardRegistry::typeKeys());

        $mixedDefaults = DashboardRegistry::defaults()[DashboardRegistry::TYPE_MIXED];
        $supportDefaults = DashboardRegistry::defaults()[DashboardRegistry::TYPE_SUPPORT_OFFICE];
        $consultantDefaults = DashboardRegistry::defaults()[DashboardRegistry::TYPE_CONSULTANT];

        foreach (array_merge($supportDefaults, $consultantDefaults) as $key) {
            $this->assertContains($key, $mixedDefaults, "Missing default={$key}");
        }
    }

    /** @test */
    public function a_combined_facility_is_governed_by_the_mixed_type_override()
    {
        // تعطيل "الاستشارات" للنوع المدمج فقط — يجب أن ينعكس على المنشأة المركبة.
        TypeInterface::query()->create([
            'type_key' => DashboardRegistry::TYPE_MIXED,
            'interface_key' => 'consultations',
            'is_enabled' => false,
        ]);

        $union = DashboardRegistry::enabledInterfaces([
            DashboardRegistry::TYPE_SUPPORT_OFFICE,
            DashboardRegistry::TYPE_CONSULTANT,
        ]);

        $this->assertNotContains('consultations', $union);

        // المنشأة الاستشارية الخالصة تبقى محتفظة باستشاراتها.
        $pureConsultant = DashboardRegistry::enabledInterfaces([DashboardRegistry::TYPE_CONSULTANT]);
        $this->assertContains('consultations', $pureConsultant);
    }

    /** @test */
    public function supervisor_can_toggle_an_interface_for_the_mixed_type()
    {
        $supervisor = $this->createUser(['role' => 'supervisor']);

        $this->actingAs($supervisor, 'business')
            ->postJson(route('amrtm.admin.org-structure.toggle'), [
                'type_key' => DashboardRegistry::TYPE_MIXED,
                'interface_key' => 'finance',
                'enabled' => false,
            ])
            ->assertOk()
            ->assertJson(['isSuccess' => true]);

        $this->assertDatabaseHas('bs_type_interfaces', [
            'type_key' => DashboardRegistry::TYPE_MIXED,
            'interface_key' => 'finance',
            'is_enabled' => 0,
        ], 'business');
    }

    /** @test */
    public function the_org_structure_page_exposes_the_mixed_column()
    {
        $supervisor = $this->createUser(['role' => 'supervisor']);

        $this->actingAs($supervisor, 'business')
            ->get(route('amrtm.admin.org-structure'))
            ->assertOk()
            ->assertViewHas('matrix', function ($matrix) {
                return isset($matrix[DashboardRegistry::TYPE_MIXED])
                    && ($matrix[DashboardRegistry::TYPE_MIXED]['enabled']['consultations'] ?? false) === true;
            });
    }

    /** @test */
    public function persona_label_honors_the_mixed_combination()
    {
        $office = $this->createOffice(['support_office', 'consultant']);
        $officeUser = OfficeUser::query()->create([
            'office_id' => $office->id,
            'name' => 'مالك المنشأة المدمجة',
            'email' => $office->email,
            'password' => Hash::make('secret123'),
            'role' => 'owner',
        ]);

        $this->actingAs($officeUser, 'office')
            ->get(route('amrtm.dashboard.hub'))
            ->assertOk()
            ->assertSee('منشأة مساندة واستشارية')
            ->assertSee('حساب مدمج');
    }

    /** @test */
    public function an_explicit_override_can_disable_a_default_interface()
    {
        TypeInterface::query()->create([
            'type_key' => DashboardRegistry::TYPE_INDIVIDUAL,
            'interface_key' => 'my_requests',
            'is_enabled' => false,
        ]);

        $enabled = DashboardRegistry::enabledInterfaces([DashboardRegistry::TYPE_INDIVIDUAL]);

        $this->assertNotContains('my_requests', $enabled);
        $this->assertContains('overview', $enabled);
    }

    /** @test */
    public function menu_for_offers_office_hrefs_for_facility_link_context()
    {
        $menu = DashboardRegistry::menuFor(
            [DashboardRegistry::TYPE_SUPPORT_OFFICE, DashboardRegistry::TYPE_CONSULTANT],
            'office'
        );

        $hrefs = collect($menu)->pluck('items')->flatten(1)->pluck('href');

        $this->assertContains('/office/dashboard#reqs', $hrefs->all());
        $this->assertContains('/office/dashboard#finance', $hrefs->all());
        $this->assertContains('/office/dashboard#contracts', $hrefs->all());
        $this->assertContains('/dashboard-hub', $hrefs->all());
    }

    private function createOffice(array $accountTypes = ['support_office', 'consultant']): Office
    {
        return Office::query()->create([
            'type' => 'engineering',
            'name_ar' => 'منشأة مركبة للاختبار',
            'name_en' => 'Composite Test Facility',
            'phone' => '0500000001',
            'email' => 'office-'.uniqid().'@example.com',
            'is_active' => true,
            'is_verified' => true,
            'account_types' => $accountTypes,
        ]);
    }

    /** @test */
    public function a_multi_type_facility_sees_the_union_menu_in_the_hub()
    {
        $office = $this->createOffice(['support_office', 'consultant']);
        $officeUser = OfficeUser::query()->create([
            'office_id' => $office->id,
            'name' => 'مالك المنشأة',
            'email' => $office->email,
            'password' => Hash::make('secret123'),
            'role' => 'owner',
        ]);

        $this->actingAs($officeUser, 'office')
            ->get(route('amrtm.dashboard.hub'))
            ->assertOk()
            ->assertViewHas('persona', function ($persona) {
                return in_array(DashboardRegistry::TYPE_SUPPORT_OFFICE, $persona['types'], true)
                    && in_array(DashboardRegistry::TYPE_CONSULTANT, $persona['types'], true);
            })
            ->assertViewHas('dashboardMenu', function ($menu) {
                $keys = collect($menu)->pluck('items')->flatten(1)->pluck('key')->all();

                return in_array('consultations', $keys, true);
            });
    }

    /** @test */
    public function an_establishment_client_menu_contains_contracts()
    {
        $user = $this->createUser(['account_type' => 'establishment']);

        $this->actingAs($user, 'business')
            ->get(route('amrtm.dashboard.hub'))
            ->assertOk()
            ->assertViewHas('persona', function ($persona) {
                return $persona['types'] === [DashboardRegistry::TYPE_ESTABLISHMENT];
            })
            ->assertViewHas('dashboardMenu', function ($menu) {
                $keys = collect($menu)->pluck('items')->flatten(1)->pluck('key')->all();

                return in_array('contracts', $keys, true);
            });
    }
}
