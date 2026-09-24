<?php

namespace Tests\Feature;

use App\Models\Business\BusinessUser;
use App\Models\Business\Office;
use App\Models\BusinessNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminOfficeVerifyTest extends TestCase
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

    private function createUser(array $overrides = []): BusinessUser
    {
        return BusinessUser::query()->create(array_merge([
            'name'         => 'مدير النظام',
            'email'        => 'admin@verify.test',
            'phone'        => '0551234567',
            'id_number'    => '1098765432',
            'password'     => Hash::make('secret123'),
            'role'         => 'user',
            'account_type' => 'individual',
            'is_active'    => true,
        ], $overrides));
    }

    private function createOffice(array $overrides = []): Office
    {
        return Office::query()->create(array_merge([
            'type'            => 'services',
            'name_ar'         => 'مكتب الاعتماد',
            'name_en'         => 'Verify Office',
            'phone'           => '0550000000',
            'email'           => 'office-' . bin2hex(random_bytes(4)) . '@example.com',
            'is_active'       => false,
            'is_verified'     => false,
            'commission_rate' => 0,
        ], $overrides));
    }

    private function latestOfficeNotification(int $officeId): ?BusinessNotification
    {
        return BusinessNotification::query()
            ->where('office_id', $officeId)
            ->latest('id')
            ->first();
    }

    public function test_verify_commission_office_stores_rate_and_notifies_with_commission(): void
    {
        $admin  = $this->createUser(['role' => 'admin', 'email' => 'admin@commission.test']);
        $office = $this->createOffice([
            'account_types'     => [Office::ACCOUNT_TYPE_SUPPORT_OFFICE],
            'subscription_type' => 'commission',
        ]);

        $response = $this->actingAs($admin, 'business')
            ->postJson("/api/admin/offices/{$office->id}/verify", ['commission_rate' => 15])
            ->assertOk();

        $this->assertTrue($response->json('office.is_verified'));
        $this->assertTrue($response->json('office.is_active'));

        $office->refresh();
        $this->assertEquals(15.0, $office->commission_rate);

        $this->assertDatabaseHas('bs_notifications', [
            'recipient_type' => 'office',
            'office_id'      => $office->id,
            'type'           => 'office_verified',
        ], 'business');

        $notification = $this->latestOfficeNotification($office->id);
        $this->assertNotNull($notification);
        $this->assertStringContainsString('15%', $notification->body);
        $this->assertSame(['office_id' => $office->id, 'commission_rate' => 15], $notification->data);
    }

    public function test_verify_consultant_office_does_not_prompt_or_apply_commission(): void
    {
        $admin  = $this->createUser(['role' => 'admin', 'email' => 'admin@consultant.test']);
        $office = $this->createOffice([
            'account_types'     => [Office::ACCOUNT_TYPE_CONSULTANT],
            'subscription_type' => 'subscription',
        ]);

        $response = $this->actingAs($admin, 'business')
            ->postJson("/api/admin/offices/{$office->id}/verify", [])
            ->assertOk();

        $this->assertTrue($response->json('office.is_verified'));

        $notification = $this->latestOfficeNotification($office->id);
        $this->assertNotNull($notification);
        $this->assertStringNotContainsString('عمولة', $notification->body);
        $this->assertStringContainsString('الاشتراك السنوي', $notification->body);
        $this->assertArrayNotHasKey('commission_rate', $notification->data ?? []);
    }

    public function test_verify_mixed_office_is_treated_as_commission_based(): void
    {
        $admin  = $this->createUser(['role' => 'admin', 'email' => 'admin@mixed.test']);
        $office = $this->createOffice([
            'account_types'     => [Office::ACCOUNT_TYPE_SUPPORT_OFFICE, Office::ACCOUNT_TYPE_CONSULTANT],
            'subscription_type' => 'subscription',
        ]);

        $this->actingAs($admin, 'business')
            ->postJson("/api/admin/offices/{$office->id}/verify", ['commission_rate' => 8])
            ->assertOk();

        $office->refresh();
        $this->assertEquals(8.0, $office->commission_rate);

        $notification = $this->latestOfficeNotification($office->id);
        $this->assertNotNull($notification);
        $this->assertStringContainsString('عمولة', $notification->body);
        $this->assertSame(['office_id' => $office->id, 'commission_rate' => 8], $notification->data);
    }

    public function test_verify_rejects_invalid_commission_rate_with_422(): void
    {
        $admin  = $this->createUser(['role' => 'admin', 'email' => 'admin@invalid.test']);
        $office = $this->createOffice([
            'account_types'     => [Office::ACCOUNT_TYPE_SUPPORT_OFFICE],
            'subscription_type' => 'commission',
        ]);

        $this->actingAs($admin, 'business')
            ->postJson("/api/admin/offices/{$office->id}/verify", ['commission_rate' => 120])
            ->assertStatus(422);

        $office->refresh();
        $this->assertFalse($office->is_verified);
    }

    public function test_verify_office_requires_admin_role(): void
    {
        $user   = $this->createUser(['role' => 'user', 'email' => 'client@verify.test']);
        $office = $this->createOffice([
            'account_types'     => [Office::ACCOUNT_TYPE_SUPPORT_OFFICE],
            'subscription_type' => 'commission',
        ]);

        $this->actingAs($user, 'business')
            ->postJson("/api/admin/offices/{$office->id}/verify", ['commission_rate' => 10])
            ->assertForbidden();

        $office->refresh();
        $this->assertFalse($office->is_verified);
    }
}