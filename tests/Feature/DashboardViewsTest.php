<?php

namespace Tests\Feature;

use App\Models\Business\BusinessUser;
use App\Models\Business\Office;
use App\Models\Business\OfficeUser;
use App\Models\Business\Specialty;
use App\Support\DashboardRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardViewsTest extends TestCase
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
    public function the_user_dashboard_renders_inside_the_flowbite_shell(): void
    {
        $user = $this->createUser(['account_type' => 'individual']);

        $response = $this->actingAs($user, 'business')
            ->get(route('amrtm.user.dashboard'))
            ->assertOk();

        $response->assertSee('id="dash-sidebar"', false);
        $response->assertSee('id="dash-dd-name"', false);
        $response->assertSee('/dashboard#requests', false);
        $response->assertSee('/dashboard#payments', false);
        $response->assertSee('id="page-overview"', false);
        $response->assertSee('id="charge-modal"', false);
        $response->assertSee('["overview","requests","profile","payments"]', false);
        $response->assertSee('مستخدم اختبار');
    }

    /** @test */
    public function an_establishment_client_sees_contracts_in_the_user_dashboard_menu(): void
    {
        $user = $this->createUser(['account_type' => 'establishment']);

        $this->actingAs($user, 'business')
            ->get(route('amrtm.user.dashboard'))
            ->assertOk()
            ->assertSee('العقود');
    }

    /** @test */
    public function the_admin_dashboard_renders_inside_the_flowbite_shell_with_admin_menu(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'email' => 'admin@example.com']);

        $response = $this->actingAs($admin, 'business')
            ->get(route('amrtm.admin.dashboard'))
            ->assertOk();

        $response->assertSee('id="dash-sidebar"', false);
        $response->assertSee('/admin/analytics', false);
        $response->assertSee('/admin/org-structure', false);
        $response->assertSee('id="page-overview"', false);
        $response->assertSee('id="dash-notifications-dropdown"', false);
    }

    /** @test */
    public function a_normal_client_cannot_open_the_admin_dashboard(): void
    {
        $client = $this->createUser();

        $this->actingAs($client, 'business')
            ->get(route('amrtm.admin.dashboard'))
            ->assertForbidden();
    }

    /** @test */
    public function every_admin_page_renders_on_its_own_route_inside_the_shared_shell(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'email' => 'pages-admin@example.com']);

        // كل صفحة فرعية تُفتح على مسار مستقل وتُقدَّم داخل غلاف اللوحة مع كتلة صفحتها فقط.
        $pages = [
            'overview'           => 'overview',
            'requests'           => 'requests',
            'pricing'            => 'pricing',
            'contracts'          => 'contracts',
            'finance'            => 'finance',
            'off-finance'        => 'off-finance',
            'catalog'            => 'catalog',
            'users'              => 'users',
            'analytics'          => 'analytics',
            'logs'               => 'logs',
            'offices'            => 'offices',
            'office-specialties' => 'office-specialties',
            'services-approvals' => 'services-approvals',
            'permissions'        => 'permissions',
            'settings'           => 'settings',
        ];

        foreach ($pages as $path => $pageId) {
            $html = $this->actingAs($admin, 'business')
                ->get("/admin/{$path}")
                ->assertOk()
                ->getContent();

            $this->assertStringContainsString('id="page-' . $pageId . '"', $html, "مسار /admin/{$path} يجب أن يعرض صفحته الداخلية");
            $this->assertStringContainsString('id="dash-sidebar"', $html, "مسار /admin/{$path} يجب أن يقدم داخل غلاف اللوحة");
        }

        // طبقة التوحيد: الأصناف الهيكلية على سطح بطاقة موحد (أبيض + --b1 + 1rem + ظل).
        $overview = $this->actingAs($admin, 'business')
            ->get(route('amrtm.admin.dashboard'))
            ->getContent();

        $this->assertMatchesRegularExpression('/\.sc\s*\{[^}]*border-radius:\s*1rem/', $overview);
        $this->assertMatchesRegularExpression('/\.top-card\s*\{[^}]*border-radius:\s*1rem/', $overview);
        $this->assertMatchesRegularExpression('/\.cat-list\s*\{[^}]*border-radius:\s*1rem/', $overview);
        $this->assertMatchesRegularExpression('/\.cat-add-form\s*\{[^}]*border-radius:\s*1rem/', $overview);
    }

    /** @test */
    public function the_admin_dashboard_highlights_no_sidebar_item_when_no_fragment_is_present(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'email' => 'admin-active@example.com']);

        $html = $this->actingAs($admin, 'business')
            ->get(route('amrtm.admin.dashboard'))
            ->assertOk()
            ->getContent();

        // في صفحة /admin بدون مرساة، يجب ألا تُظهَر بنود القائمة كبنود نشطة جميعها.
        $this->assertSame(0, substr_count($html, 'aria-current="page"'), 'No sidebar item should be highlighted without a matching route.');
    }

    /** @test */
    public function the_admin_analytics_route_highlights_the_analytics_sidebar_item(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'email' => 'admin-anactive@example.com']);

        $html = $this->actingAs($admin, 'business')
            ->get('/admin/analytics')
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, 'aria-current="page"'), 'Exactly one sidebar item must be highlighted on /admin/analytics.');
        $this->assertStringContainsString('href="/admin/analytics"', $html);
    }

    /** @test */
    public function the_admin_requests_page_exposes_pool_broadcast_wiring_for_office_requests(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'email' => 'bc-admin@example.com']);

        $html = $this->actingAs($admin, 'business')
            ->get('/admin/requests')
            ->assertOk()
            ->getContent();

        // طلبات شبكة المكاتب: زر «بث لشبكة المكاتب» + قائمة المكاتب المؤهلة + الاستدعاءات.
        $this->assertStringContainsString('togBroadcastPick', $html);
        $this->assertStringContainsString('doBroadcast', $html);
        $this->assertStringContainsString('Admin.eligibleOffices', $html);
        $this->assertStringContainsString('Admin.broadcastRequest', $html);
        $this->assertStringContainsString('بث لشبكة المكاتب', $html);
        $this->assertStringContainsString('id="bo-${i}"', $html);
        $this->assertStringContainsString('id="bl-${i}"', $html);
    }

    /** @test */
    public function the_org_structure_page_highlights_exactly_the_org_structure_item(): void
    {
        $supervisor = $this->createUser(['role' => 'supervisor', 'email' => 'super-active@example.com']);

        $html = $this->actingAs($supervisor, 'business')
            ->get(route('amrtm.admin.org-structure'))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, 'aria-current="page"'), 'Exactly the org-structure item must be highlighted.');
        $this->assertStringContainsString('href="/admin/org-structure"', $html);
    }

    /** @test */
    public function the_admin_homepage_management_page_renders_inside_the_flowbite_shell(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'email' => 'hp-admin@example.com']);

        $response = $this->actingAs($admin, 'business')
            ->get(route('amrtm.admin.homepage'))
            ->assertOk();

        $response->assertSee('id="dash-sidebar"', false);
        $response->assertSee('id="ahp-save-btn"', false);
        $response->assertSee('id="set_site_title"', false);
        $response->assertSee('id="slidesList"', false);
        $response->assertSee('id="slideModal"', false);
        $response->assertSee(route('amrtm.admin.api.homepage.slides'), false);
    }

    /** @test */
    public function the_admin_icons_page_renders_inside_the_flowbite_shell(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'email' => 'icons-admin@example.com']);

        $response = $this->actingAs($admin, 'business')
            ->get(route('amrtm.admin.icons'))
            ->assertOk();

        $response->assertSee('id="dash-sidebar"', false);
        $response->assertSee('id="iconGrid"', false);
        $response->assertSee('id="uploadZone"', false);
        $response->assertSee('id="progressBar"', false);
        $response->assertSee('id="searchInput"', false);
        $response->assertSee('id="countBadge"', false);
    }

    /** @test */
    public function the_dev_components_playground_is_not_reachable_when_debug_is_off(): void
    {
        // في بيئة التشغيل (غير dev) يجب أن يعطي المسار 404 ولا يظهر أبداً،
        // حتى مع route:cache — الضمان في الـ controller نفسه (abort_if ! debug).
        config(['app.debug' => false]);

        $this->get('/dev/components')->assertNotFound();
    }

    /** @test */
    public function the_dev_components_playground_renders_when_debug_is_on(): void
    {
        // في وضع التطوير نفسها تظهر، وداخل قشرة Flowbite.
        $response = $this->get('/dev/components')->assertOk();

        $response->assertSee('id="play-log"', false);
        $response->assertSee('id="p-amount"', false);
    }

    /** @test */
    public function the_office_dashboard_renders_inside_the_flowbite_shell(): void
    {
        ['office' => $office, 'user' => $officeUser] = $this->createOffice();

        $response = $this->actingAs($officeUser, 'office')
            ->get(route('amrtm.office.dashboard'));

        $response->assertOk();
        $response->assertSee('id="dash-sidebar"', false);
        $response->assertSee('/office/dashboard#reqs', false);
        $response->assertSee('/office/dashboard#finance', false);
    }

    /** @test */
    public function the_office_dashboard_exposes_pool_claim_wiring_for_office_network_requests(): void
    {
        ['office' => $office, 'user' => $officeUser] = $this->createOffice();

        $html = $this->actingAs($officeUser, 'office')
            ->get(route('amrtm.office.dashboard'))
            ->assertOk()
            ->getContent();

        // شبكة المكاتب: قائمة الطلبات القابلة للحجز + زر الحجز + توجيه إشعار البث إليها.
        $this->assertStringContainsString('id="pg-pool-reqs"', $html);
        $this->assertStringContainsString('/office/dashboard#pool-reqs', $html);
        $this->assertStringContainsString('claimable-requests', $html);
        $this->assertStringContainsString('claimPoolRequest', $html);
        $this->assertStringContainsString("n.type === 'pool_broadcast'", $html);
        $this->assertStringContainsString('شبكة المكاتب', $html);
    }

    /** @test */
    public function the_office_profile_page_renders_inside_the_flowbite_shell(): void
    {
        ['office' => $office, 'user' => $officeUser] = $this->createOffice();

        $response = $this->actingAs($officeUser, 'office')
            ->get(route('amrtm.office.profile'));

        $response->assertOk();
        $response->assertSee('id="dash-sidebar"', false);
        $response->assertSee('name="office_name_ar"', false);
        $response->assertSee('name="office_name_en"', false);
        $response->assertSee(route('amrtm.office.profile.update'), false);
    }

    /** @test */
    public function the_office_profile_update_persists_selected_specialties_in_pivot_and_column(): void
    {
        $specA = Specialty::query()->create([
            'office_type' => 'engineering',
            'name_ar' => 'توثيق هندسي',
            'name_en' => 'Engineering Notarization',
            'is_active' => true,
        ]);
        $specB = Specialty::query()->create([
            'office_type' => 'engineering',
            'name_ar' => 'استشارات بناء',
            'name_en' => 'Construction Consulting',
            'is_active' => true,
        ]);
        Specialty::query()->create([
            'office_type' => 'engineering',
            'name_ar' => 'تخصص معطل',
            'name_en' => 'Inactive Specialty',
            'is_active' => false,
        ]);

        ['office' => $office, 'user' => $officeUser] = $this->createOffice();

        $response = $this->actingAs($officeUser, 'office')
            ->post(route('amrtm.office.profile.update'), [
                'office_name_ar' => 'منشأة اختبار المساندة',
                'office_name_en' => 'Support Test Facility',
                'phone' => '0500000002',
                'name' => 'مالك المنشأة',
                'specialty_ids' => $specA->id . ',' . $specB->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $office->refresh();
        $this->assertEqualsCanonicalizing([(int) $specA->id, (int) $specB->id], $office->specialtiesRelation()->pluck('bs_specialties.id')->map(fn ($id) => (int) $id)->all());
        $this->assertSame(['توثيق هندسي', 'استشارات بناء'], $office->specialties);

        $response = $this->actingAs($officeUser, 'office')
            ->post(route('amrtm.office.profile.update'), [
                'office_name_ar' => 'منشأة اختبار المساندة',
                'office_name_en' => 'Support Test Facility',
                'phone' => '0500000002',
                'name' => 'مالك المنشأة',
                'specialty_ids' => '',
            ]);

        $office->refresh();
        $this->assertEmpty($office->specialtiesRelation()->pluck('bs_specialties.id')->all());
        $this->assertSame([], $office->specialties);
    }

    /** @test */
    public function the_office_profile_update_rejects_specialties_not_allowed_for_the_office_type(): void
    {
        Specialty::query()->create([
            'office_type' => 'engineering',
            'name_ar' => 'توثيق هندسي',
            'name_en' => 'Engineering Notarization',
            'is_active' => true,
        ]);
        Specialty::query()->create([
            'office_type' => 'legal',
            'name_ar' => 'توثيق قانوني',
            'name_en' => 'Legal Notarization',
            'is_active' => true,
        ]);

        ['office' => $office, 'user' => $officeUser] = $this->createOffice();
        $legalId = Specialty::query()->where('office_type', 'legal')->value('id');

        $this->actingAs($officeUser, 'office')
            ->post(route('amrtm.office.profile.update'), [
                'office_name_ar' => 'منشأة اختبار المساندة',
                'office_name_en' => 'Support Test Facility',
                'phone' => '0500000002',
                'name' => 'مالك المنشأة',
                'specialty_ids' => (string) $legalId,
            ])
            ->assertSessionHasErrors('specialty_ids');

        $office->refresh();
        $this->assertEmpty($office->specialtiesRelation()->pluck('bs_specialties.id')->all());
    }

    private function createOffice(array $overrides = []): array
    {
        $office = Office::query()->create(array_merge([
            'type' => 'engineering',
            'name_ar' => 'منشأة اختبار المساندة',
            'name_en' => 'Support Test Facility',
            'phone' => '0500000002',
            'email' => 'office-dash@example.com',
            'is_active' => true,
            'is_verified' => true,
            'account_types' => [DashboardRegistry::TYPE_SUPPORT_OFFICE],
        ], $overrides));

        $officeUser = OfficeUser::query()->create([
            'office_id' => $office->id,
            'name' => 'مالك المنشأة',
            'email' => $office->email,
            'password' => Hash::make('secret123'),
            'role' => 'owner',
            'is_active' => true,
        ]);

        return ['office' => $office, 'user' => $officeUser];
    }

    /** @test */
    public function the_dashboard_blade_sources_do_not_use_raw_native_form_elements(): void
    {
        $base = resource_path('views') . DIRECTORY_SEPARATOR;
        $files = array_merge(
            glob($base . str_replace('/', DIRECTORY_SEPARATOR, 'update_service/dashboard/*.blade.php')) ?: [],
            glob($base . str_replace('/', DIRECTORY_SEPARATOR, 'update_service/dashboard/admin/*.blade.php')) ?: [],
            glob($base . str_replace('/', DIRECTORY_SEPARATOR, 'update_service/dashboard/admin/pages/*.blade.php')) ?: [],
            [
                $base . str_replace('/', DIRECTORY_SEPARATOR, 'update_service/admin_homepage.blade.php'),
                $base . str_replace('/', DIRECTORY_SEPARATOR, 'update_service/admin_icons.blade.php'),
            ]
        );

        $banned = ['<input', '<select', '<textarea', '<label', '<button'];

        foreach (array_unique($files) as $path) {
            $rel = ltrim(str_replace($base, '', $path), '\\/');
            $source = file_get_contents($path);
            $this->assertNotFalse($source, 'يجب أن يكون الملف موجوداً: ' . $rel);

            foreach ($banned as $tag) {
                $this->assertStringNotContainsString(
                    $tag,
                    (string) $source,
                    $rel . ' لا يجوز احتواء عناصر HTML أصلية (' . $tag . ') — استخدم مكوّن x-ui'
                );
            }
        }
    }

    /** @test */
    public function the_dashboard_scripts_do_not_embed_escaping_xui_components(): void
    {
        $base = resource_path('views') . DIRECTORY_SEPARATOR;
        $files = array_merge(
            glob($base . str_replace('/', DIRECTORY_SEPARATOR, 'update_service/dashboard/*.blade.php')) ?: [],
            glob($base . str_replace('/', DIRECTORY_SEPARATOR, 'update_service/dashboard/admin/*.blade.php')) ?: [],
            glob($base . str_replace('/', DIRECTORY_SEPARATOR, 'update_service/dashboard/admin/pages/*.blade.php')) ?: [],
            [
                $base . str_replace('/', DIRECTORY_SEPARATOR, 'update_service/admin_homepage.blade.php'),
                $base . str_replace('/', DIRECTORY_SEPARATOR, 'update_service/admin_icons.blade.php'),
            ]
        );

        $escaping = [
            '<x-ui.input',
            '<x-ui.textarea',
            '<x-ui.select',
            '<x-ui.datepicker',
            '<x-ui.amount-field',
            '<x-ui.eye-field',
        ];

        foreach (array_unique($files) as $path) {
            $rel = ltrim(str_replace($base, '', $path), '\\/');
            $lines = file($path, FILE_IGNORE_NEW_LINES);
            $this->assertNotFalse($lines, 'يجب أن يكون الملف موجوداً: ' . $rel);

            $inScript = false;

            foreach ($lines as $index => $line) {
                if (preg_match('/<script\b[^>]*>/', $line)) {
                    $inScript = true;
                }

                if (!$inScript) {
                    continue;
                }

                if (preg_match('#</script>#', $line)) {
                    $inScript = false;
                }

                foreach ($escaping as $tag) {
                    $this->assertStringNotContainsString(
                        $tag,
                        $line,
                        $rel . ':' . ($index + 1) . ' — لا يجوز مكوّن يُمرّر خصائصه عبر e() داخل <script> (يتشفّر \' و&\' داخل \${...})؛ استخدم مُنشئات JS: xuiInput/xuiTextarea/xuiSelect...'
                    );
                }
            }
        }
    }
}