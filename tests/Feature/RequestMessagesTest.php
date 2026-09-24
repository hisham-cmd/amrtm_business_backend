<?php

namespace Tests\Feature;

use App\Models\Business\BusinessUser;
use App\Models\Business\Office;
use App\Models\Business\OfficeMessage;
use App\Models\Business\OfficeUser;
use App\Models\BusinessNotification;
use App\Models\RequestLog;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RequestMessagesTest extends TestCase
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
            'name'         => 'عميل الاختبار',
            'email'        => 'client@example.com',
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
            'name_ar'         => 'مكتب الخدمات',
            'name_en'         => 'Services Office',
            'phone'           => '0550000000',
            'email'           => 'office@example.com',
            'is_active'       => true,
            'is_verified'     => true,
            'commission_rate' => 10,
            'subscription_type' => 'commission',
            'account_types'   => [Office::ACCOUNT_TYPE_SUPPORT_OFFICE],
        ], $overrides));
    }

    private function createConsultantOffice(array $overrides = []): Office
    {
        return $this->createOffice(array_merge([
            'subscription_type' => 'subscription',
            'account_types'     => [Office::ACCOUNT_TYPE_CONSULTANT],
        ], $overrides));
    }

    private function createOfficeUser(Office $office): OfficeUser
    {
        return OfficeUser::query()->create([
            'office_id' => $office->id,
            'name'      => 'موظف المكتب',
            'email'     => 'office-user@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'owner',
            'is_active' => true,
        ]);
    }

    private function createRequest(int $userId, ?Office $office = null): ServiceRequest
    {
        return ServiceRequest::query()->create(array_merge([
            'user_id'       => $userId,
            'client_name'   => 'عميل الاختبار',
            'client_email'  => 'client@example.com',
            'client_phone'  => '0551234567',
            'client_id_number' => '1098765432',
            'price'         => 200,
            'status'        => 'processing',
            'office_status' => 'accepted',
        ], $office ? [
            'office_id'   => $office->id,
            'fulfillment' => ServiceRequest::FULFILLMENT_ASSIGNED,
            'assigned_at' => now(),
        ] : []));
    }

    private function createMessage(int $requestId, int $officeId, string $senderType, string $message): OfficeMessage
    {
        return OfficeMessage::query()->create([
            'request_id'  => $requestId,
            'office_id'   => $officeId,
            'sender_type' => $senderType,
            'sender_id'   => $senderType === 'client' ? 1 : null,
            'message'     => $message,
            'is_read'     => false,
        ]);
    }

    /** @test */
    public function a_client_can_list_messages_of_their_own_request(): void
    {
        $user    = $this->createUser();
        $office  = $this->createOffice();
        $sr      = $this->createRequest($user->id, $office);

        $this->createMessage($sr->id, $office->id, 'office', 'مرحباً! طلبك قيد التنفيذ');
        $this->createMessage($sr->id, $office->id, 'client', 'شكراً لكم');

        $this->actingAs($user, 'business')
            ->getJson(route('amrtm.api.requests.messages', $sr->id))
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.sender_type', 'office')
            ->assertJsonPath('1.sender_type', 'client');
    }

    /** @test */
    public function a_client_cannot_list_messages_of_someone_elses_request(): void
    {
        $owner  = $this->createUser(['email' => 'owner@example.com']);
        $other  = $this->createUser(['email' => 'other@example.com']);
        $office = $this->createOffice();
        $sr     = $this->createRequest($owner->id, $office);

        $this->actingAs($other, 'business')
            ->getJson(route('amrtm.api.requests.messages', $sr->id))
            ->assertForbidden();
    }

/** @test */
    public function guests_get_unauthorized_on_the_messages_api(): void
    {
        $this->get(route('amrtm.api.requests.messages', 1))
            ->assertStatus(401);
    }

    /** @test */
    public function a_client_can_send_a_message_to_the_assigned_office(): void
    {
        $user    = $this->createUser();
        $office  = $this->createOffice();
        $sr      = $this->createRequest($user->id, $office);

        $this->actingAs($user, 'business')
            ->postJson(route('amrtm.api.requests.messages.send', $sr->id), [
                'message' => 'هل يمكنكم تزويدي بمستندات الطلب؟',
            ])
            ->assertCreated()
            ->assertJsonPath('sender_type', 'client')
            ->assertJsonPath('message', 'هل يمكنكم تزويدي بمستندات الطلب؟');

        $this->assertTrue(OfficeMessage::query()->where([
            'request_id'  => $sr->id,
            'sender_type' => 'client',
        ])->exists());

        $officeNotif = BusinessNotification::query()
            ->where('type', 'message_received')
            ->where('office_id', $office->id)
            ->first();

        $this->assertNotNull($officeNotif);
        $this->assertSame($sr->id, $officeNotif->request_id);
    }

    /** @test */
    public function a_client_cannot_send_to_an_unassigned_request(): void
    {
        $user = $this->createUser();
        $sr   = $this->createRequest($user->id);

        $this->actingAs($user, 'business')
            ->postJson(route('amrtm.api.requests.messages.send', $sr->id), [
                'message' => 'رسالة تجريبية',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'لم يُسند هذا الطلب إلى مكتب بعد.');
    }

    /** @test */
    public function support_office_conversation_blocks_contact_data_and_logs_violation(): void
    {
        $user    = $this->createUser();
        $office  = $this->createOffice(); // مكتب مساند (commission)
        $sr      = $this->createRequest($user->id, $office);

        $this->actingAs($user, 'business')
            ->postJson(route('amrtm.api.requests.messages.send', $sr->id), [
                'message' => 'تواصل معي على واتساب 0551234567',
            ])
            ->assertStatus(422);

$this->assertDatabaseHas('bs_request_logs', [
            'request_id' => $sr->id,
            'log_type'   => 'contact_violation',
        ], 'business');

        $violation = RequestLog::query()
            ->where('request_id', $sr->id)
            ->where('log_type', 'contact_violation')
            ->first();

        $this->assertStringContainsString('طرف المخالف: عميل', (string) $violation->note);

        $adminNotif = BusinessNotification::query()
            ->where('type', 'contact_violation')
            ->where('request_id', $sr->id)
            ->first();

        $this->assertNotNull($adminNotif);
        $this->assertSame('client', $adminNotif->data['side'] ?? null);
    }

    /** @test */
    public function consultant_conversation_is_exempt_from_contact_data_guard(): void
    {
        $user    = $this->createUser();
        $office  = $this->createConsultantOffice();
        $sr      = $this->createRequest($user->id, $office);

        $this->actingAs($user, 'business')
            ->postJson(route('amrtm.api.requests.messages.send', $sr->id), [
                'message' => 'راسلني على البريد client@personal.com للتفاصيل',
            ])
            ->assertCreated();

$this->assertDatabaseMissing('bs_request_logs', [
            'request_id' => $sr->id,
            'log_type'   => 'contact_violation',
        ], 'business');
    }

    /** @test */
    public function a_supporting_office_sending_contact_data_is_blocked_and_logged(): void
    {
        $user   = $this->createUser();
        $office = $this->createOffice();
        $ou     = $this->createOfficeUser($office);
        $sr     = $this->createRequest($user->id, $office);

        $this->actingAs($ou, 'office')
            ->postJson('/office/api/requests/' . $sr->id . '/messages', [
                'message' => 'ابعت لي رقمك 0501234567',
            ])
            ->assertStatus(422);

$this->assertDatabaseHas('bs_request_logs', [
            'request_id' => $sr->id,
            'log_type'   => 'contact_violation',
        ], 'business');

        $violation = RequestLog::query()
            ->where('request_id', $sr->id)
            ->where('log_type', 'contact_violation')
            ->first();

        $this->assertStringContainsString('طرف المخالف: مكتب', (string) $violation->note);
    }

    /** @test */
    public function an_admin_can_view_conversations_and_messages(): void
    {
        $admin   = $this->createUser(['email' => 'admin@example.com', 'role' => 'admin']);
        $user    = $this->createUser(['email' => 'owner2@example.com']);
        $office  = $this->createOffice();
        $sr      = $this->createRequest($user->id, $office);

        $this->createMessage($sr->id, $office->id, 'client', 'سؤال من العميل');
        $this->createMessage($sr->id, $office->id, 'office', 'رد من المكتب');

        $this->actingAs($admin, 'business')
            ->getJson(route('amrtm.api.admin.requests.messages', $sr->id))
            ->assertOk()
            ->assertJsonPath('request.ref_number', $sr->ref_number)
            ->assertJsonCount(2, 'messages')
            ->assertJsonPath('messages.0.sender_type', 'client')
            ->assertJsonPath('messages.1.sender_type', 'office');

        $this->actingAs($admin, 'business')
            ->getJson(route('amrtm.api.admin.conversations'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.ref_number', $sr->ref_number)
            ->assertJsonPath('data.0.messages_count', 2);
    }

    /** @test */
    public function an_admin_can_view_violations(): void
    {
        $admin   = $this->createUser(['email' => 'admin3@example.com', 'role' => 'admin']);
        $user    = $this->createUser(['email' => 'owner3@example.com']);
        $office  = $this->createOffice();
        $sr      = $this->createRequest($user->id, $office);

        RequestLog::query()->create([
            'request_id' => $sr->id,
            'user_id'    => $user->id,
            'status'     => 'processing',
            'log_type'   => 'contact_violation',
            'note'       => 'طرف المخالف: عميل · راسلني على واتساب 0551234567',
        ]);

$this->actingAs($admin, 'business')
            ->getJson(route('amrtm.api.admin.violations'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.side', 'client')
            ->assertJsonPath('data.0.ref_number', $sr->ref_number)
            ->assertJsonPath('data.0.offender.type', 'client')
            ->assertJsonPath('data.0.offender.id', $user->id)
            ->assertJsonPath('data.0.offender.active', true);
    }

    /** @test */
    public function only_admin_can_access_admin_message_endpoints(): void
    {
        $user    = $this->createUser();
        $office = $this->createOffice();
        $sr     = $this->createRequest($user->id, $office);

        $this->actingAs($user, 'business')
            ->getJson(route('amrtm.api.admin.conversations'))
            ->assertForbidden();
    }

/** @test */
    public function the_admin_messages_page_renders(): void
    {
        $admin  = $this->createUser(['email' => 'admin4@example.com', 'role' => 'admin']);
        $user   = $this->createUser(['email' => 'owner4@example.com']);
        $office = $this->createOffice();
        $sr     = $this->createRequest($user->id, $office);

        $this->createMessage($sr->id, $office->id, 'client', 'سؤال من العميل');

        RequestLog::query()->create([
            'request_id' => $sr->id,
            'user_id'    => $user->id,
            'status'     => 'processing',
            'log_type'   => 'contact_violation',
            'note'       => 'طرف المخالف: عميل · راسلني على واتساب 0551234567',
        ]);

        $this->actingAs($admin, 'business')
            ->get(route('amrtm.admin.messages'))
            ->assertOk()
            ->assertSee('التواصل والمخالفات', false)
            ->assertSee('المحادثات', false)
            ->assertSee('المخالفات', false);
    }

    /** @test */
    public function an_office_lists_conversations_with_unread_client_messages(): void
    {
        $user   = $this->createUser();
        $office = $this->createOffice();
        $ou     = $this->createOfficeUser($office);
        $sr     = $this->createRequest($user->id, $office);

        $this->createMessage($sr->id, $office->id, 'client', 'سؤال أول');
        $second = $this->createMessage($sr->id, $office->id, 'client', 'سؤال ثانٍ');
        $second->forceFill(['created_at' => now()->addMinute()])->save();

        $read = $this->createMessage($sr->id, $office->id, 'client', 'مقروءة مسبقاً');
        $read->forceFill(['is_read' => true])->save();
        $this->createMessage($sr->id, $office->id, 'office', 'رد المكتب');

        $this->actingAs($ou, 'office')
            ->getJson(route('amrtm.office.api.messages.unread'))
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'requests')
            ->assertJsonPath('requests.0.id', $sr->id)
            ->assertJsonPath('requests.0.ref_number', $sr->ref_number)
            ->assertJsonPath('requests.0.unread_count', 2)
            ->assertJsonPath('requests.0.last_message', 'سؤال ثانٍ');
    }

    /** @test */
    public function an_office_sees_no_unread_conversations_when_all_are_read(): void
    {
        $user   = $this->createUser();
        $office = $this->createOffice();
        $ou     = $this->createOfficeUser($office);
        $sr     = $this->createRequest($user->id, $office);

        $msg = $this->createMessage($sr->id, $office->id, 'client', 'مقروءة');
        $msg->forceFill(['is_read' => true])->save();

        $this->actingAs($ou, 'office')
            ->getJson(route('amrtm.office.api.messages.unread'))
            ->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonCount(0, 'requests');
    }

    /** @test */
    public function an_office_does_not_see_other_offices_unread_conversations(): void
    {
        $user      = $this->createUser();
        $officeA   = $this->createOffice(['email' => 'a@example.com', 'name_ar' => 'مكتب أ']);
        $officeB   = $this->createOffice(['email' => 'b@example.com', 'name_ar' => 'مكتب ب']);
        $ouB       = $this->createOfficeUser($officeB);
        $ouB->forceFill(['email' => 'b-user@example.com'])->save();

        $srA = $this->createRequest($user->id, $officeA);
        $srB = $this->createRequest($user->id, $officeB);
        $this->createMessage($srA->id, $officeA->id, 'client', 'لمكتب أ فقط');
        $this->createMessage($srB->id, $officeB->id, 'client', 'لمكتب ب فقط');

        $this->actingAs($ouB, 'office')
            ->getJson(route('amrtm.office.api.messages.unread'))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonCount(1, 'requests')
            ->assertJsonPath('requests.0.id', $srB->id);
    }

    /** @test */
    public function guests_are_unauthorized_on_the_unread_messages_endpoint(): void
    {
        $this->getJson(route('amrtm.office.api.messages.unread'))
            ->assertStatus(401);
    }

    /** @test */
    public function an_admin_can_disable_a_client_violator_account(): void
    {
        $admin  = $this->createUser(['email' => 'admin-dis-client@example.com', 'role' => 'admin']);
        $user   = $this->createUser(['email' => 'violator-client@example.com']);
        $office = $this->createOffice();
        $sr     = $this->createRequest($user->id, $office);

        $log = RequestLog::query()->create([
            'request_id' => $sr->id,
            'user_id'    => $user->id,
            'status'     => 'processing',
            'log_type'   => 'contact_violation',
            'note'       => 'طرف المخالف: عميل · راسلني على واتساب 0551234567',
        ]);

        $this->actingAs($admin, 'business')
            ->postJson(route('amrtm.api.admin.violations.disable', $log->id))
            ->assertOk()
            ->assertJsonPath('side', 'client')
            ->assertJsonPath('target_id', $user->id)
            ->assertJsonPath('is_active', false)
            ->assertJsonPath('message', 'تم إيقاف حساب العميل «' . $user->name . '» نهائياً.');

        $this->assertDatabaseHas('bs_users', [
            'id'        => $user->id,
            'is_active' => 0,
        ], 'business');
    }

    /** @test */
    public function an_admin_can_disable_an_office_violator_account(): void
    {
        $admin  = $this->createUser(['email' => 'admin-dis-office@example.com', 'role' => 'admin']);
        $user   = $this->createUser(['email' => 'client-dis-office@example.com']);
        $office = $this->createOffice();
        $sr     = $this->createRequest($user->id, $office);

        $log = RequestLog::query()->create([
            'request_id' => $sr->id,
            'user_id'    => null,
            'status'     => 'processing',
            'log_type'   => 'contact_violation',
            'note'       => 'طرف المخالف: مكتب · ابعت لي رقمك 0501234567',
        ]);

        $this->actingAs($admin, 'business')
            ->postJson(route('amrtm.api.admin.violations.disable', $log->id))
            ->assertOk()
            ->assertJsonPath('side', 'office')
            ->assertJsonPath('target_id', $office->id)
            ->assertJsonPath('is_active', false)
            ->assertJsonPath('message', 'تم إيقاف حساب المكتب «' . $office->name_ar . '» نهائياً.');

        $this->assertDatabaseHas('bs_offices', [
            'id'        => $office->id,
            'is_active' => 0,
        ], 'business');
    }

    /** @test */
    public function the_violations_offender_data_is_included_in_the_list(): void
    {
        $admin  = $this->createUser(['email' => 'admin-violations-offender@example.com', 'role' => 'admin']);
        $user   = $this->createUser(['email' => 'offender-info-client@example.com']);
        $office = $this->createOffice();
        $sr     = $this->createRequest($user->id, $office);

        RequestLog::query()->create([
            'request_id' => $sr->id,
            'user_id'    => $user->id,
            'status'     => 'processing',
            'log_type'   => 'contact_violation',
            'note'       => 'طرف المخالف: عميل · رقم هاتف من نص الرسالة 773467746',
        ]);

        $this->actingAs($admin, 'business')
            ->getJson(route('amrtm.api.admin.violations'))
            ->assertOk()
            ->assertJsonPath('data.0.offender.type', 'client')
            ->assertJsonPath('data.0.offender.id', $user->id)
            ->assertJsonPath('data.0.offender.name', $user->name)
            ->assertJsonPath('data.0.offender.active', true);
    }

    /** @test */
    public function a_non_admin_cannot_disable_a_violator_account(): void
    {
        $client = $this->createUser(['email' => 'non-admin-disable@example.com']);
        $office = $this->createOffice();
        $sr     = $this->createRequest($client->id, $office);

        $log = RequestLog::query()->create([
            'request_id' => $sr->id,
            'user_id'    => $client->id,
            'status'     => 'processing',
            'log_type'   => 'contact_violation',
            'note'       => 'طرف المخالف: عميل · تواصل معي على واتساب',
        ]);

        $this->actingAs($client, 'business')
            ->postJson(route('amrtm.api.admin.violations.disable', $log->id))
            ->assertForbidden();
    }
}
