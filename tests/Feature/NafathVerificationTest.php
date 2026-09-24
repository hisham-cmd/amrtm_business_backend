<?php

namespace Tests\Feature;

use App\Models\Business\BusinessUser;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NafathVerificationTest extends TestCase
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

        config([
            'nafath.app_id'   => 'test-app-id',
            'nafath.app_key'  => 'test-app-key',
        ]);
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

    private function fakeNafath(string $status = 'WAITING'): void
    {
        Http::fake([
            '*/moi/init' => Http::response(['transId' => 'T-123'], 200),
            '*/moi/checkStatus' => Http::response(['status' => $status], 200),
        ]);
    }

    public function test_a_guest_can_open_the_verify_page(): void
    {
        $this->get(route('amrtm.nafath.show'))
            ->assertOk()
            ->assertSee('رقم الهوية الوطنية / الإقامة')
            ->assertSee('إرسال طلب الموافقة');
    }

    public function test_verify_page_supports_register_intent(): void
    {
        $this->get(route('amrtm.nafath.show', ['intent' => 'register']))
            ->assertOk()
            ->assertSee('توثيق الهوية الوطنية');
    }

    public function test_an_authenticated_user_cannot_open_the_verify_page(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'business')
            ->get(route('amrtm.nafath.show'))
            ->assertRedirect(route('amrtm.dashboard.hub'));
    }

    public function test_verify_redirects_to_wait_after_initiation(): void
    {
        $this->fakeNafath();

        $this->post(route('amrtm.nafath.verify'), ['national_id' => '1234567890'])
            ->assertRedirect(route('amrtm.nafath.wait'));

        $this->assertEquals('T-123', session('nafath_pending.trans_id'));
        $this->assertEquals('1234567890', session('nafath_pending.national_id'));
    }

    public function test_verify_rejects_malformed_national_id(): void
    {
        $this->post(route('amrtm.nafath.verify'), ['national_id' => '99876543210'])
            ->assertSessionHasErrors('national_id');

        $this->post(route('amrtm.nafath.verify'), ['national_id' => 'abc'])
            ->assertSessionHasErrors('national_id');
    }

    public function test_verify_requires_national_id(): void
    {
        $this->post(route('amrtm.nafath.verify'), [])
            ->assertSessionHasErrors('national_id');
    }

    public function test_verify_reports_when_integration_is_not_configured(): void
    {
        config([
            'nafath.app_id'  => null,
            'nafath.app_key' => null,
        ]);

        $this->post(route('amrtm.nafath.verify'), ['national_id' => '1234567890'])
            ->assertSessionHasErrors('national_id');
    }

    public function test_status_reports_waiting(): void
    {
        $this->fakeNafath('WAITING');

        $this->post(route('amrtm.nafath.verify'), ['national_id' => '1234567890']);

        $this->get(route('amrtm.nafath.status', ['transId' => 'T-123']))
            ->assertOk()
            ->assertJsonPath('status', 'WAITING');
    }

    public function test_status_completed_logs_in_existing_account(): void
    {
        $this->fakeNafath('COMPLETED');
        $this->createUser(['id_number' => '1234567890']);

        $this->post(route('amrtm.nafath.verify'), ['national_id' => '1234567890']);

        $this->get(route('amrtm.nafath.status', ['transId' => 'T-123']))
            ->assertOk()
            ->assertJsonPath('status', 'COMPLETED')
            ->assertJsonPath('redirect', route('amrtm.dashboard.hub'));

        $this->assertAuthenticated('business');

        $user = BusinessUser::query()->where('id_number', '1234567890')->firstOrFail();
        $this->assertNotNull($user->nafath_verified_at);
        $this->assertNull(session('nafath_pending'));
    }

    public function test_status_returns_no_account_for_unregistered_identity(): void
    {
        $this->fakeNafath('COMPLETED');

        $this->post(route('amrtm.nafath.verify'), ['national_id' => '1234567890']);

        $this->get(route('amrtm.nafath.status', ['transId' => 'T-123']))
            ->assertStatus(404)
            ->assertJsonPath('status', 'NO_ACCOUNT');
    }

    public function test_status_register_intent_marks_verified_identity(): void
    {
        $this->fakeNafath('COMPLETED');

        $this->post(route('amrtm.nafath.verify'), [
            'national_id' => '1234567890',
            'intent'      => 'register',
        ]);

        $this->get(route('amrtm.nafath.status', ['transId' => 'T-123']))
            ->assertOk()
            ->assertJsonPath('status', 'VERIFIED')
            ->assertJsonPath('verified_national_id', '1234567890');

        $this->assertEquals('1234567890', session('nafath_verified.national_id'));
    }

    public function test_status_expires_when_pending_has_no_trans_id(): void
    {
        $this->get(route('amrtm.nafath.status', ['transId' => 'UNKNOWN']))
            ->assertStatus(410)
            ->assertJsonPath('status', 'EXPIRED');
    }

    public function test_wait_page_redirects_when_no_pending_request(): void
    {
        $this->get(route('amrtm.nafath.wait'))
            ->assertRedirect(route('amrtm.nafath.show', ['intent' => 'login']));
    }

    public function test_callback_forwards_to_wait_when_pending(): void
    {
        $this->fakeNafath();

        $this->post(route('amrtm.nafath.verify'), ['national_id' => '1234567890']);

        $this->get(route('amrtm.nafath.callback'))
            ->assertRedirect(route('amrtm.nafath.wait'));
    }

    public function test_register_attaches_verified_identity_from_session(): void
    {
        $this->withSession([
            'nafath_verified' => [
                'national_id' => '1234567890',
                'verified_at' => now(),
            ],
        ]);

        $this->post(route('amrtm.register.submit'), [
            'name' => 'عميل اختبار',
            'email' => 'nafath-client@example.com',
            'phone' => '0550000000',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'account_type' => 'individual',
            'father_name' => 'والد',
            'family_name' => 'عائلة',
            'id_number' => '1234567890',
            'job_sector' => 'private',
            'employment_status' => 'affiliated',
        ])->assertRedirect(route('amrtm.dashboard.hub'));

        $user = BusinessUser::query()->where('email', 'nafath-client@example.com')->firstOrFail();
        $this->assertEquals('1234567890', $user->id_number);
        $this->assertNotNull($user->nafath_verified_at);
        $this->assertNull(session('nafath_verified'));
    }

    public function test_register_rejects_identity_mismatch_with_verified_session(): void
    {
        $this->withSession([
            'nafath_verified' => [
                'national_id' => '1234567890',
                'verified_at' => now(),
            ],
        ]);

        $this->post(route('amrtm.register.submit'), [
            'name' => 'عميل اختبار',
            'email' => 'nafath-mismatch@example.com',
            'phone' => '0550000000',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'account_type' => 'individual',
            'father_name' => 'والد',
            'family_name' => 'عائلة',
            'id_number' => '2222222222',
            'job_sector' => 'private',
            'employment_status' => 'affiliated',
        ])->assertSessionHasErrors('id_number');

        $this->assertDatabaseMissing('bs_users', ['email' => 'nafath-mismatch@example.com'], 'business');
    }
}