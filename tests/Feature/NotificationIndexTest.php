<?php

namespace Tests\Feature;

use App\Models\Business\BusinessUser;
use App\Models\Business\Office;
use App\Models\Business\OfficeUser;
use App\Models\BusinessNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationIndexTest extends TestCase
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
            'name'         => 'مستخدم إشعارات',
            'email'        => 'notif@example.com',
            'phone'        => '0551234000',
            'id_number'    => '1098765433',
            'password'     => Hash::make('secret123'),
            'role'         => 'user',
            'account_type' => 'individual',
            'is_active'    => true,
        ], $overrides));
    }

    private function createOffice(): Office
    {
        return Office::query()->create([
            'type'            => 'services',
            'name_ar'         => 'مكتب الإشعارات',
            'name_en'         => 'Notification Office',
            'phone'           => '0550000000',
            'email'           => 'notif-office@example.com',
            'is_active'       => true,
            'is_verified'     => true,
            'commission_rate' => 10,
            'account_types'   => [Office::ACCOUNT_TYPE_CONSULTANT],
            'subscription_type' => 'subscription',
        ]);
    }

    private function createOfficeUser(Office $office): OfficeUser
    {
        return OfficeUser::query()->create([
            'office_id' => $office->id,
            'name'      => 'مدير المكتب',
            'email'     => 'notif-office-user@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'owner',
            'is_active' => true,
        ]);
    }

    private function seedUserNotifications(int $userId, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            BusinessNotification::forUser($userId, 'info', "إشعار {$i}", 'نص الإشعار', ['request_id' => $i]);
        }
    }

    private function seedOfficeNotifications(int $officeId, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            BusinessNotification::forOffice($officeId, 'info', "إشعار مكتب {$i}", 'نص الإشعار', ['request_id' => $i]);
        }
    }

    /** @test */
    public function the_user_notifications_index_is_limited_to_30_by_default(): void
    {
        $user = $this->createUser();
        $this->seedUserNotifications($user->id, 35);

        $response = $this->actingAs($user, 'business')
            ->getJson('/api/notifications')
            ->assertOk();

        $this->assertCount(30, $response->json('data'));
        $this->assertSame(35, $response->json('unread'));
    }

    /** @test */
    public function the_user_notifications_index_returns_all_when_the_all_flag_is_set(): void
    {
        $user = $this->createUser(['email' => 'notif-all@example.com']);
        $this->seedUserNotifications($user->id, 35);

        $response = $this->actingAs($user, 'business')
            ->getJson('/api/notifications?all=1')
            ->assertOk();

        $this->assertCount(35, $response->json('data'));
        $this->assertSame(35, $response->json('unread'));
    }

    /** @test */
    public function the_office_notifications_index_returns_all_when_the_all_flag_is_set(): void
    {
        $office = $this->createOffice();
        $officeUser = $this->createOfficeUser($office);
        $this->seedOfficeNotifications($office->id, 35);

        $response = $this->actingAs($officeUser, 'office')
            ->getJson('/office/api/notifications?all=1')
            ->assertOk();

        $this->assertCount(35, $response->json('data'));
        $this->assertSame(35, $response->json('unread'));
    }

    /** @test */
    public function the_office_notifications_index_is_limited_to_30_by_default(): void
    {
        $office = $this->createOffice();
        $officeUser = $this->createOfficeUser($office);
        $this->seedOfficeNotifications($office->id, 35);

        $response = $this->actingAs($officeUser, 'office')
            ->getJson('/office/api/notifications')
            ->assertOk();

        $this->assertCount(30, $response->json('data'));
        $this->assertSame(35, $response->json('unread'));
    }
}