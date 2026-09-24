<?php

namespace Tests\Feature;

use App\Models\Business\BusinessUser;
use App\Models\Business\Office;
use App\Models\Business\OfficeService;
use App\Models\Business\OfficeUser;
use App\Models\BusinessNotification;
use App\Models\Category;
use App\Models\Entity;
use App\Models\GovService;
use App\Models\ServicePayment;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RequestPoolTest extends TestCase
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
            'email'           => 'office-' . bin2hex(random_bytes(4)) . '@example.com',
            'is_active'       => true,
            'is_verified'     => true,
            'commission_rate' => 10,
        ], $overrides));
    }

    private function createSupportingOffice(array $overrides = []): Office
    {
        return $this->createOffice(array_merge([
            'account_types'     => [Office::ACCOUNT_TYPE_SUPPORT_OFFICE],
            'subscription_type' => 'commission',
        ], $overrides));
    }

    private function createConsultantOffice(array $overrides = []): Office
    {
        return $this->createOffice(array_merge([
            'account_types'     => [Office::ACCOUNT_TYPE_CONSULTANT],
            'subscription_type' => 'subscription',
        ], $overrides));
    }

    private function createOfficeUser(Office $office, array $overrides = []): OfficeUser
    {
        return OfficeUser::query()->create(array_merge([
            'office_id' => $office->id,
            'name'      => 'مدير المكتب',
            'email'     => "office-{$office->id}@example.com",
            'password'  => Hash::make('secret123'),
            'role'      => 'owner',
            'is_active' => true,
        ], $overrides));
    }

    private function seedGovService(): GovService
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

        return GovService::query()->create([
            'entity_id'     => $entity->id,
            'name_ar'       => 'إصدار سجل تجاري',
            'name_en'       => 'Commercial Register',
            'price'         => 300,
            'duration_min'  => 5,
            'duration_max'  => 5,
            'duration_unit' => 'day',
            'is_active'     => true,
        ]);
    }

    private function createOfficeService(int $officeId, ?int $sourceServiceId = null, array $overrides = []): OfficeService
    {
        return OfficeService::query()->create(array_merge([
            'office_id'         => $officeId,
            'name_ar'           => 'إصدار سجل تجاري',
            'name_en'           => 'Commercial Register',
            'price'             => 200,
            'source_service_id' => $sourceServiceId,
            'is_active'         => true,
            'approval_status'   => 'approved',
            'sort_order'        => 0,
        ], $overrides));
    }

    private function submitPooledRequest(BusinessUser $user, Office $fromOffice, OfficeService $service): ServiceRequest
    {
        ServicePayment::create([
            'user_id'        => $user->id,
            'amount'         => 1000,
            'type'           => 'charge',
            'status'         => 'completed',
            'description_ar' => 'رصيد تجريبي',
            'description_en' => 'Test balance',
        ]);

        $this->actingAs($user, 'business')
            ->postJson('/amrtm/api/office-requests', [
                'office_id'         => $fromOffice->id,
                'office_service_id' => $service->id,
                'notes'             => 'طلب شبكة المكاتب',
            ])->assertCreated();

        return ServiceRequest::query()
            ->where('origin', ServiceRequest::ORIGIN_OFFICE)
            ->where('office_service_id', $service->id)
            ->whereNull('office_id')
            ->firstOrFail();
    }

    public function test_admin_lists_eligible_offices_across_linked_services(): void
    {
        $user        = $this->createUser(['email' => 'pool-a@example.com']);
        $admin       = $this->createUser(['role' => 'admin', 'email' => 'admin-a@example.com']);
        $source      = $this->createSupportingOffice(['name_ar' => 'مكتب المصدر', 'name_en' => 'Source Office', 'commission_rate' => 12]);
        $linked      = $this->createSupportingOffice(['name_ar' => 'مكتب مرتبط', 'name_en' => 'Linked Office', 'commission_rate' => 10]);
        $unlinked    = $this->createSupportingOffice(['name_ar' => 'مكتب غير مرتبط', 'name_en' => 'Unlinked Office', 'commission_rate' => 8]);
        $consultant  = $this->createConsultantOffice(['name_ar' => 'مكتب استشاري', 'name_en' => 'Consultant Office']);

        $govSvc = $this->seedGovService();
        $svcSource = $this->createOfficeService($source->id, $govSvc->id);
        $svcLinked = $this->createOfficeService($linked->id, $govSvc->id);
        $svcUnlinked = $this->createOfficeService($unlinked->id);
        $this->createOfficeService($consultant->id, $govSvc->id);

        $req = $this->submitPooledRequest($user, $source, $svcSource);

        $response = $this->actingAs($admin, 'business')
            ->getJson("/api/v1/admin/requests/{$req->id}/eligible-offices")
            ->assertOk();

        $ids = collect($response->json('offices'))->pluck('id')->all();

        $this->assertContains($source->id, $ids);
        $this->assertContains($linked->id, $ids);
        $this->assertNotContains($unlinked->id, $ids);
        $this->assertNotContains($consultant->id, $ids);
    }

    public function test_admin_broadcasts_request_to_selected_eligible_offices(): void
    {
        $user    = $this->createUser(['email' => 'pool-b@example.com']);
        $admin   = $this->createUser(['role' => 'admin', 'email' => 'admin-b@example.com']);
        $source  = $this->createSupportingOffice(['commission_rate' => 12]);
        $linked  = $this->createSupportingOffice(['commission_rate' => 10]);

        $govSvc = $this->seedGovService();
        $svcSource = $this->createOfficeService($source->id, $govSvc->id);
        $svcLinked = $this->createOfficeService($linked->id, $govSvc->id);

        $req = $this->submitPooledRequest($user, $source, $svcSource);

        $response = $this->actingAs($admin, 'business')
            ->postJson("/api/v1/admin/requests/{$req->id}/broadcast", [
                'office_ids' => [$linked->id],
            ])
            ->assertOk();

        $this->assertSame(1, $response->json('candidates'));
        $this->assertSame('processing', $response->json('request.status'));

        $req->refresh();
        $this->assertSame('processing', $req->status);
        $this->assertSame(ServiceRequest::FULFILLMENT_OPEN, $req->fulfillment);
        $this->assertNull($req->office_id);
        $this->assertSame([$linked->id], $req->candidate_office_ids);
        $this->assertNull($req->claimed_at);

        $this->assertDatabaseHas('bs_notifications', [
            'recipient_type' => 'office',
            'office_id'      => $linked->id,
            'type'           => 'pool_broadcast',
        ], 'business');

        $this->assertDatabaseHas('bs_request_logs', [
            'request_id' => $req->id,
            'log_type'   => 'pool_broadcast',
        ], 'business');
    }

    public function test_admin_broadcast_rejects_non_eligible_office(): void
    {
        $user      = $this->createUser(['email' => 'pool-c@example.com']);
        $admin     = $this->createUser(['role' => 'admin', 'email' => 'admin-c@example.com']);
        $source    = $this->createSupportingOffice();
        $unlinked  = $this->createSupportingOffice(['name_ar' => 'مكتب غير مؤهل', 'name_en' => 'Ineligible Office']);

        $govSvc = $this->seedGovService();
        $svcSource = $this->createOfficeService($source->id, $govSvc->id);
        $this->createOfficeService($unlinked->id);

        $req = $this->submitPooledRequest($user, $source, $svcSource);

        $this->actingAs($admin, 'business')
            ->postJson("/api/v1/admin/requests/{$req->id}/broadcast", [
                'office_ids' => [$unlinked->id],
            ])
            ->assertStatus(422);

        $this->assertSame('pending', $req->refresh()->status);
    }

    public function test_admin_broadcast_fails_when_no_eligible_offices(): void
    {
        $user   = $this->createUser(['email' => 'pool-d@example.com']);
        $admin  = $this->createUser(['role' => 'admin', 'email' => 'admin-d@example.com']);
        $source = $this->createSupportingOffice();

        $govSvc = $this->seedGovService();
        $svcSource = $this->createOfficeService($source->id, $govSvc->id);

        $req = $this->submitPooledRequest($user, $source, $svcSource);

        $source->update(['is_active' => false]);

        $this->actingAs($admin, 'business')
            ->postJson("/api/v1/admin/requests/{$req->id}/broadcast")
            ->assertStatus(422)
            ->assertJson(['message' => 'لا توجد مكاتب مساندة مؤهلة لهذه الخدمة حالياً. يمكنك معالجة الطلب داخلياً.']);
    }

    public function test_admin_direct_assign_of_pooled_request_is_blocked(): void
    {
        $user    = $this->createUser(['email' => 'pool-e@example.com']);
        $admin   = $this->createUser(['role' => 'admin', 'email' => 'admin-e@example.com']);
        $source  = $this->createSupportingOffice();
        $linked  = $this->createSupportingOffice(['commission_rate' => 10]);

        $govSvc = $this->seedGovService();
        $svcSource = $this->createOfficeService($source->id, $govSvc->id);
        $this->createOfficeService($linked->id, $govSvc->id);

        $req = $this->submitPooledRequest($user, $source, $svcSource);

        $this->actingAs($admin, 'business')
            ->postJson("/api/admin/requests/{$req->id}/assign", [
                'office_id' => $linked->id,
            ])
            ->assertStatus(422);

        $this->assertNull($req->refresh()->office_id);
    }

    public function test_claimable_requests_only_return_for_broadcast_eligible_offices(): void
    {
        $user    = $this->createUser(['email' => 'pool-f@example.com']);
        $admin   = $this->createUser(['role' => 'admin', 'email' => 'admin-f@example.com']);
        $source  = $this->createSupportingOffice();
        $linked  = $this->createSupportingOffice(['commission_rate' => 10]);
        $other   = $this->createSupportingOffice(['name_ar' => 'مكتب آخر', 'name_en' => 'Other Office']);

        $govSvc = $this->seedGovService();
        $svcSource = $this->createOfficeService($source->id, $govSvc->id);
        $this->createOfficeService($linked->id, $govSvc->id);

        $req = $this->submitPooledRequest($user, $source, $svcSource);

        $this->actingAs($admin, 'business')
            ->postJson("/api/v1/admin/requests/{$req->id}/broadcast")
            ->assertOk();

        $linkedUser = $this->createOfficeUser($linked);
        $otherUser  = $this->createOfficeUser($other);

        $this->actingAs($linkedUser, 'office')
            ->getJson('/office/api/claimable-requests')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $req->id);

        $this->actingAs($otherUser, 'office')
            ->getJson('/office/api/claimable-requests')
            ->assertOk()
            ->assertJsonPath('total', 0);
    }

    public function test_first_office_claims_the_request_and_second_claim_fails(): void
    {
        $user  = $this->createUser(['email' => 'pool-g@example.com']);
        $admin = $this->createUser(['role' => 'admin', 'email' => 'admin-g@example.com']);
        $source = $this->createSupportingOffice(['commission_rate' => 12]);
        $winner = $this->createSupportingOffice(['commission_rate' => 10]);
        $loser  = $this->createSupportingOffice(['commission_rate' => 15]);

        $govSvc = $this->seedGovService();
        $svcSource = $this->createOfficeService($source->id, $govSvc->id);
        $this->createOfficeService($winner->id, $govSvc->id);
        $this->createOfficeService($loser->id, $govSvc->id);

        $req = $this->submitPooledRequest($user, $source, $svcSource);

        $this->actingAs($admin, 'business')
            ->postJson("/api/v1/admin/requests/{$req->id}/broadcast", [
                'office_ids' => [$winner->id, $loser->id],
            ])
            ->assertOk();

        $winnerUser = $this->createOfficeUser($winner);
        $loserUser  = $this->createOfficeUser($loser);

        $this->actingAs($winnerUser, 'office')
            ->postJson("/office/api/requests/{$req->id}/claim")
            ->assertOk()
            ->assertJsonPath('request.ref_number', $req->ref_number);

        $req->refresh();
        $this->assertSame($winner->id, $req->office_id);
        $this->assertSame(ServiceRequest::FULFILLMENT_ASSIGNED, $req->fulfillment);
        $this->assertSame('processing', $req->status);
        $this->assertNotNull($req->claimed_at);
        $this->assertNotNull($req->assigned_at);
        $this->assertSame(20.0, (float) $req->commission_amount);

        $this->assertDatabaseHas('bs_notifications', [
            'recipient_type' => 'admin',
            'type'           => 'office_claimed',
        ], 'business');

        $this->actingAs($loserUser, 'office')
            ->postJson("/office/api/requests/{$req->id}/claim")
            ->assertStatus(422);

        $this->actingAs($winnerUser, 'office')
            ->postJson("/office/api/requests/{$req->id}/claim")
            ->assertStatus(422);
    }

    public function test_non_eligible_office_cannot_claim(): void
    {
        $user   = $this->createUser(['email' => 'pool-h@example.com']);
        $admin  = $this->createUser(['role' => 'admin', 'email' => 'admin-h@example.com']);
        $source = $this->createSupportingOffice();
        $linked = $this->createSupportingOffice(['commission_rate' => 10]);

        $govSvc = $this->seedGovService();
        $svcSource = $this->createOfficeService($source->id, $govSvc->id);
        $this->createOfficeService($linked->id, $govSvc->id);

        $req = $this->submitPooledRequest($user, $source, $svcSource);

        $this->actingAs($admin, 'business')
            ->postJson("/api/v1/admin/requests/{$req->id}/broadcast", [
                'office_ids' => [$linked->id],
            ])
            ->assertOk();

        $requesterUser = $this->createOfficeUser($source);
        $this->actingAs($requesterUser, 'office')
            ->postJson("/office/api/requests/{$req->id}/claim")
            ->assertStatus(422);

        $this->assertNull($req->refresh()->office_id);
    }

    public function test_consultant_office_cannot_claim_pool_request(): void
    {
        $user   = $this->createUser(['email' => 'pool-i@example.com']);
        $admin  = $this->createUser(['role' => 'admin', 'email' => 'admin-i@example.com']);
        $source = $this->createSupportingOffice();
        $linked = $this->createSupportingOffice(['commission_rate' => 10]);
        $consultant = $this->createConsultantOffice();

        $govSvc = $this->seedGovService();
        $svcSource = $this->createOfficeService($source->id, $govSvc->id);
        $this->createOfficeService($linked->id, $govSvc->id);

        $req = $this->submitPooledRequest($user, $source, $svcSource);

        $this->actingAs($admin, 'business')
            ->postJson("/api/v1/admin/requests/{$req->id}/broadcast", [
                'office_ids' => [$linked->id],
            ])
            ->assertOk();

        $consultantUser = $this->createOfficeUser($consultant);

        $this->actingAs($consultantUser, 'office')
            ->postJson("/office/api/requests/{$req->id}/claim")
            ->assertStatus(422);

        $this->assertNull($req->refresh()->office_id);
    }

    public function test_claimed_pool_request_moves_to_requests_tab_and_leaves_direct_requests(): void
    {
        $user   = $this->createUser(['email' => 'pool-j@example.com']);
        $admin  = $this->createUser(['role' => 'admin', 'email' => 'admin-j@example.com']);
        $source = $this->createSupportingOffice(['name_ar' => 'مكتب المصدر', 'name_en' => 'Source Office']);
        $winner = $this->createSupportingOffice(['name_ar' => 'مكتب الحاجز', 'name_en' => 'Winner Office', 'commission_rate' => 10]);

        $govSvc = $this->seedGovService();
        $svcSource = $this->createOfficeService($source->id, $govSvc->id);
        $this->createOfficeService($winner->id, $govSvc->id);

        $req = $this->submitPooledRequest($user, $source, $svcSource);

        $this->actingAs($admin, 'business')
            ->postJson("/api/v1/admin/requests/{$req->id}/broadcast", [
                'office_ids' => [$winner->id],
            ])
            ->assertOk();

        $winnerUser = $this->createOfficeUser($winner);

        $this->actingAs($winnerUser, 'office')
            ->postJson("/office/api/requests/{$req->id}/claim")
            ->assertOk();

        // بعد الحجز: الطلب يظهر ضمن «الطلبات» (getRequests) ويُفتح عبر requests/{id}.
        $this->actingAs($winnerUser, 'office')
            ->getJson('/office/api/requests?status=all')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $req->id);

        $this->actingAs($winnerUser, 'office')
            ->getJson("/office/api/requests/{$req->id}")
            ->assertOk()
            ->assertJsonPath('origin', ServiceRequest::ORIGIN_OFFICE);

        // ولا يظهر في «الطلبات المباشرة» (direct-requests) بعد الآن.
        $this->actingAs($winnerUser, 'office')
            ->getJson('/office/api/direct-requests')
            ->assertOk()
            ->assertJsonPath('total', 0);

        // تحديث الحالة من تبويب «الطلبات» يعمل على حجز الشبكة.
        $this->actingAs($winnerUser, 'office')
            ->putJson("/office/api/requests/{$req->id}/status", [
                'office_status' => 'accepted',
            ])
            ->assertOk()
            ->assertJsonPath('office_status', 'accepted');

        $this->assertSame('processing', $req->refresh()->status);

        // الرفض يعيد الطلب للبث (fulfillment=open + بلا مكتب) فيظهر مجدداً قابلاً للحجز.
        $this->actingAs($winnerUser, 'office')
            ->putJson("/office/api/requests/{$req->id}/status", [
                'office_status' => 'rejected',
                'note'          => 'غير متوفر حالياً',
            ])
            ->assertOk();

        $req->refresh();
        $this->assertSame(ServiceRequest::FULFILLMENT_OPEN, $req->fulfillment);
        $this->assertNull($req->office_id);
        $this->assertSame('pending', $req->status);

        $this->actingAs($winnerUser, 'office')
            ->getJson('/office/api/claimable-requests')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $req->id);
    }
}