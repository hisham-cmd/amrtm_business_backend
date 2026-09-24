<?php

namespace Tests\Feature;

use App\Models\Business\BusinessUser;
use App\Models\Business\Office;
use App\Models\Business\OfficeService;
use App\Models\Business\OfficeUser;
use App\Models\BusinessNotification;
use App\Models\ServicePayment;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OfficeRequestSubmitTest extends TestCase
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
            'name'         => 'هشام العميل',
            'email'        => 'hisham@example.com',
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
        ], $overrides));
    }

    private function createConsultantOffice(array $overrides = []): Office
    {
        return $this->createOffice(array_merge([
            'account_types'   => [Office::ACCOUNT_TYPE_CONSULTANT],
            'subscription_type' => 'subscription',
        ], $overrides));
    }

    private function createSupportingOffice(array $overrides = []): Office
    {
        return $this->createOffice(array_merge([
            'account_types'   => [Office::ACCOUNT_TYPE_SUPPORT_OFFICE],
            'subscription_type' => 'commission',
        ], $overrides));
    }

    private function createOfficeService(int $officeId): OfficeService
    {
        return OfficeService::query()->create([
            'office_id'   => $officeId,
            'name_ar'     => 'إصدار سجل تجاري',
            'name_en'     => 'Commercial Register',
            'price'       => 200,
            'is_active'   => true,
            'sort_order'  => 0,
        ]);
    }

    private function createOfficeUser(Office $office): OfficeUser
    {
        return OfficeUser::query()->create([
            'office_id' => $office->id,
            'name'      => 'مدير المكتب',
            'email'     => 'office-user@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'owner',
            'is_active' => true,
        ]);
    }

    private function seedBalance(BusinessUser $user, float $amount): void
    {
        ServicePayment::create([
            'user_id'        => $user->id,
            'amount'         => $amount,
            'type'           => 'charge',
            'status'         => 'completed',
            'description_ar' => 'رصيد تجريبي',
            'description_en' => 'Test balance',
        ]);
    }

    /** @test */
    public function a_client_submits_an_office_request_using_profile_data(): void
    {
        $user    = $this->createUser();
        $office  = $this->createConsultantOffice();
        $service = $this->createOfficeService($office->id);

        $response = $this->actingAs($user, 'business')
            ->postJson('/amrtm/api/office-requests', [
                'office_id'         => $office->id,
                'office_service_id' => $service->id,
                'notes'             => 'أحتاج الإنجاز بشكل عاجل',
            ]);

        $response->assertCreated();

        $data = $response->json();
        $this->assertArrayHasKey('ref_number', $data);
        $this->assertStringStartsWith('OF-', $data['ref_number']);

        $this->assertDatabaseHas('bs_requests', [
            'origin'            => 'office',
            'user_id'           => $user->id,
            'office_id'         => $office->id,
            'office_service_id' => $service->id,
            'client_name'       => 'هشام العميل',
            'client_phone'      => '0551234567',
            'client_email'      => 'hisham@example.com',
            'client_id_number'  => '1098765432',
            'notes'             => 'أحتاج الإنجاز بشكل عاجل',
            'price'             => 200,
            'commission_amount' => 20,
            'consultation_type' => 'standard',
            'fulfillment'       => 'assigned',
            'office_status'     => 'pending',
        ], 'business');
    }

    /** @test */
    public function consultation_type_is_stored_from_the_payload(): void
    {
        $user    = $this->createUser(['email' => 'video@example.com']);
        $office  = $this->createSupportingOffice();
        $service = $this->createOfficeService($office->id);

        $this->seedBalance($user, 500);

        $this->actingAs($user, 'business')
            ->postJson('/amrtm/api/office-requests', [
                'office_id'         => $office->id,
                'office_service_id' => $service->id,
                'consultation_type' => 'video',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('bs_requests', [
            'origin'            => 'office',
            'office_id'         => null,
            'user_id'           => $user->id,
            'consultation_type' => 'video',
        ], 'business');
    }

    /** @test */
    public function a_guest_is_rejected_when_submitting_an_office_request(): void
    {
        $response = $this->postJson('/amrtm/api/office-requests', [
            'office_id'         => 1,
            'office_service_id' => 1,
        ]);

        $response->assertStatus(401);

        $this->assertDatabaseCount('bs_requests', 0, 'business');
    }

    /** @test */
    public function an_office_notification_carries_request_id_and_the_office_can_open_the_request(): void
    {
        $user    = $this->createUser(['email' => 'notif@example.com']);
        $office  = $this->createConsultantOffice();
        $service = $this->createOfficeService($office->id);
        $officeUser = $this->createOfficeUser($office);

        $this->actingAs($user, 'business')
            ->postJson('/amrtm/api/office-requests', [
                'office_id'         => $office->id,
                'office_service_id' => $service->id,
                'notes'             => 'طلب مباشر من الإشعار',
            ])->assertCreated();

        $req = ServiceRequest::query()->where('office_id', $office->id)
            ->where('origin', ServiceRequest::ORIGIN_OFFICE)
            ->firstOrFail();

        $this->assertDatabaseHas('bs_notifications', [
            'recipient_type' => 'office',
            'office_id'      => $office->id,
            'type'           => 'new_office_request',
            'request_id'     => null,
        ], 'business');

        $note = BusinessNotification::where('office_id', $office->id)
            ->where('recipient_type', 'office')
            ->firstOrFail();

        $this->assertSame($req->id, $note->data['request_id']);
        $this->assertSame($req->ref_number, $note->data['ref_number']);

        $this->actingAs($officeUser, 'office')
            ->getJson("/office/api/requests/{$req->id}")
            ->assertStatus(200)
            ->assertJsonPath('id', $req->id)
            ->assertJsonPath('ref_number', $req->ref_number)
            ->assertJsonPath('origin', ServiceRequest::ORIGIN_OFFICE)
            ->assertJsonPath('office_status', 'pending');
    }

    /** @test */
    public function the_office_notification_is_listed_and_can_be_marked_read(): void
    {
        $user    = $this->createUser(['email' => 'read@example.com']);
        $office  = $this->createConsultantOffice();
        $service = $this->createOfficeService($office->id);
        $officeUser = $this->createOfficeUser($office);

        $this->actingAs($user, 'business')
            ->postJson('/amrtm/api/office-requests', [
                'office_id'         => $office->id,
                'office_service_id' => $service->id,
            ])->assertCreated();

        $note = BusinessNotification::where('office_id', $office->id)
            ->where('recipient_type', 'office')
            ->firstOrFail();

        $this->actingAs($officeUser, 'office')
            ->getJson('/office/api/notifications')
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $note->id)
            ->assertJsonPath('data.0.data.request_id', $note->data['request_id'])
            ->assertJsonPath('unread', 1);

        $this->actingAs($officeUser, 'office')
            ->postJson("/office/api/notifications/{$note->id}/read")
            ->assertStatus(200);

        $this->assertDatabaseHas('bs_notifications', [
            'id'      => $note->id,
            'is_read' => true,
        ], 'business');
    }

    /** @test */
    public function a_supporting_office_request_waits_for_admin_assignment_without_office_notification(): void
    {
        $user    = $this->createUser(['email' => 'support@example.com']);
        $office  = $this->createSupportingOffice();
        $service = $this->createOfficeService($office->id);

        $this->seedBalance($user, 500);

        $this->actingAs($user, 'business')
            ->postJson('/amrtm/api/office-requests', [
                'office_id'         => $office->id,
                'office_service_id' => $service->id,
                'notes'             => 'طلب مكتب مساند',
            ])->assertCreated();

        $req = ServiceRequest::query()
            ->where('origin', ServiceRequest::ORIGIN_OFFICE)
            ->where('office_service_id', $service->id)
            ->whereNull('office_id')
            ->firstOrFail();

        $this->assertSame(ServiceRequest::FULFILLMENT_OPEN, $req->fulfillment);
        $this->assertNull($req->assigned_at);
        $this->assertSame('pending', $req->office_status);

        // المكاتب المساندة تُدفع مسبقاً من رصيد العميل.
        $this->assertSame('prepaid', $req->payment_status);
        $this->assertNotNull($req->paid_at);

        $this->assertDatabaseHas('bs_payments', [
            'user_id'    => $user->id,
            'request_id' => $req->id,
            'amount'     => 200,
            'type'       => 'payment',
            'status'     => 'completed',
        ], 'business');

        $this->assertDatabaseMissing('bs_notifications', [
            'recipient_type' => 'office',
            'office_id'      => $office->id,
            'type'           => 'new_office_request',
        ], 'business');

        $adminNote = BusinessNotification::where('recipient_type', 'admin')
            ->where('type', 'new_office_request')
            ->firstOrFail();
        $this->assertSame($req->id, $adminNote->data['request_id']);
        $this->assertSame($req->ref_number, $adminNote->data['ref_number']);

        $userNote = BusinessNotification::where('recipient_type', 'user')
            ->where('user_id', $user->id)
            ->where('type', 'request_submitted')
            ->firstOrFail();
        $this->assertSame($req->id, $userNote->data['request_id']);
        $this->assertSame($req->ref_number, $userNote->data['ref_number']);
    }

    /** @test */
    public function a_supporting_office_request_is_rejected_when_balance_is_insufficient(): void
    {
        $user    = $this->createUser(['email' => 'poor@example.com']);
        $office  = $this->createSupportingOffice();
        $service = $this->createOfficeService($office->id);

        $this->seedBalance($user, 50);

        $response = $this->actingAs($user, 'business')
            ->postJson('/amrtm/api/office-requests', [
                'office_id'         => $office->id,
                'office_service_id' => $service->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('balance');

        $this->assertDatabaseCount('bs_requests', 0, 'business');
    }

    /** @test */
    public function consultant_requests_are_not_prepaid_and_defer_payment_to_settlement(): void
    {
        $user    = $this->createUser(['email' => 'consult@example.com']);
        $office  = $this->createConsultantOffice();
        $service = $this->createOfficeService($office->id);

        $this->actingAs($user, 'business')
            ->postJson('/amrtm/api/office-requests', [
                'office_id'         => $office->id,
                'office_service_id' => $service->id,
            ])->assertCreated();

        $req = ServiceRequest::query()
            ->where('origin', ServiceRequest::ORIGIN_OFFICE)
            ->where('office_service_id', $service->id)
            ->firstOrFail();

        $this->assertNull($req->payment_status);
        $this->assertNull($req->paid_at);

        $this->assertDatabaseMissing('bs_payments', [
            'user_id'    => $user->id,
            'request_id' => $req->id,
            'type'       => 'payment',
        ], 'business');
    }
}