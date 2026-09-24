<?php

namespace Tests\Feature;

use App\Models\Business\BusinessUser;
use App\Models\Business\Office;
use App\Models\Business\OfficeUser;
use App\Models\Business\Specialty;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthRedirectTest extends TestCase
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

    private function createUser(): BusinessUser
    {
        return BusinessUser::query()->create([
            'name'         => 'مستخدم تسجيل الدخول',
            'email'        => 'login@example.com',
            'phone'        => '0550000000',
            'password'     => Hash::make('secret123'),
            'role'         => 'user',
            'account_type' => 'individual',
            'is_active'    => true,
        ]);
    }

    private function createOffice(array $overrides = []): Office
    {
        return Office::query()->create(array_merge([
            'type'            => 'customs',
            'name_ar'         => 'شركة التخليص الجمركي',
            'name_en'         => 'Customs Clearance Co',
            'phone'           => '0550000001',
            'email'           => 'office@example.com',
            'is_active'       => true,
            'is_verified'     => true,
            'commission_rate' => 10,
        ], $overrides));
    }

    private function createOfficeUser(int $officeId): OfficeUser
    {
        return OfficeUser::query()->create([
            'office_id' => $officeId,
            'name'      => 'مسؤول المكتب',
            'email'     => 'office@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'owner',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function login_redirects_back_to_the_requested_page(): void
    {
        $this->createUser();

        $target = '/amrtm/offices/services/5';

        $this->post(route('amrtm.login.submit'), [
            'email'    => 'login@example.com',
            'password' => 'secret123',
            'redirect' => $target,
        ])->assertRedirect($target);

        $this->assertAuthenticatedAs(BusinessUser::where('email', 'login@example.com')->first(), 'business');
    }

    /** @test */
    public function login_ignores_external_redirect_targets(): void
    {
        $this->createUser();

        $this->post(route('amrtm.login.submit'), [
            'email'    => 'login@example.com',
            'password' => 'secret123',
            'redirect' => 'https://evil.example.com/phish',
        ])->assertRedirect(route('amrtm.dashboard.hub'));
    }

    /** @test */
    public function office_user_logged_in_via_main_login_sees_profile_menu(): void
    {
        $office = $this->createOffice();
        $this->createOfficeUser($office->id);

        $spec = Specialty::query()->create([
            'office_type' => 'customs',
            'name_ar'     => 'التخليص الجمركي',
            'name_en'     => 'Customs Clearance',
        ]);
        $office->specialtiesRelation()->attach($spec->id);

        $target = '/offices/' . $office->type . '/' . $spec->id;

        $this->post(route('amrtm.login.submit'), [
            'email'    => 'office@example.com',
            'password' => 'secret123',
            'redirect' => $target,
        ])->assertRedirect($target);

        $this->assertAuthenticatedAs(OfficeUser::where('email', 'office@example.com')->first(), 'office');

        $response = $this->get($target);

        $response->assertOk();
        $response->assertSee('id="nb-auth" class="flex', false);
        $response->assertSee('nb-user-chip');
        $response->assertSee('مسؤول'); // الاسم الظاهر في شريحة المستخدم
        $response->assertSee('/office/dashboard', false); // رابط لوحة تحكم المكتب
        $response->assertSee('/office/logout', false);    // تسجيل الخروج الخاص بالمكتب

        $response->assertDontSee('id="nb-guest" class="flex', false);
    }
}