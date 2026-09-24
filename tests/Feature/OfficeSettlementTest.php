<?php

namespace Tests\Feature;

use App\Models\Business\BusinessUser;
use App\Models\Business\Office;
use App\Models\OfficeSettlement;
use App\Models\ServicePayment;
use App\Models\ServiceRequest;
use App\Services\ServiceRequestService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OfficeSettlementTest extends TestCase
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

    private function createOffice(array $overrides = []): Office
    {
        return Office::query()->create(array_merge([
            'type'            => 'services',
            'name_ar'         => 'مكتب التنفيذ',
            'name_en'         => 'Execution Office',
            'phone'           => '0550000000',
            'email'           => 'exec@example.com',
            'is_active'       => true,
            'is_verified'     => true,
            'commission_rate' => 10,
        ], $overrides));
    }

    private function createDoneRequest(Office $office, array $overrides = [], string $email = 'settle@example.com'): ServiceRequest
    {
        return ServiceRequest::query()->create(array_merge([
            'origin'            => ServiceRequest::ORIGIN_OFFICE,
            'user_id'           => $this->createUser($email)->id,
            'office_id'         => $office->id,
            'client_name'       => 'عميل التسوية',
            'client_email'      => 'settle-client@example.com',
            'client_phone'      => '0551112222',
            'price'             => 200,
            'commission_amount' => 20,
            'status'            => 'done',
            'office_status'     => 'done',
            'payment_status'    => 'prepaid',
            'paid_at'           => now(),
        ], $overrides));
    }

    private function createUser(string $email = 'settle@example.com'): BusinessUser
    {
        return BusinessUser::query()->create([
            'name'         => 'عميل التسوية',
            'email'        => $email,
            'phone'        => '0551112222',
            'password'     => Hash::make('secret123'),
            'role'         => 'user',
            'account_type' => 'individual',
            'is_active'    => true,
        ]);
    }

    /** @test */
    public function a_done_request_with_an_executing_office_creates_one_pending_settlement(): void
    {
        $office = $this->createOffice();
        $sr     = $this->createDoneRequest($office);

        $settlement = app(ServiceRequestService::class)->createSettlementIfEligible($sr->fresh());

        $this->assertNotNull($settlement);
        $this->assertSame($office->id, $settlement->office_id);
        $this->assertSame($sr->id, $settlement->request_id);
        $this->assertEquals(180, (float) $settlement->amount);
        $this->assertEquals(20, (float) $settlement->commission_amount);
        $this->assertSame(OfficeSettlement::STATUS_PENDING, $settlement->status);

        $this->assertDatabaseCount('bs_office_settlements', 1, 'business');
    }

    /** @test */
    public function settlement_is_created_only_once_per_request(): void
    {
        $office = $this->createOffice();
        $sr     = $this->createDoneRequest($office);

        app(ServiceRequestService::class)->createSettlementIfEligible($sr->fresh());
        app(ServiceRequestService::class)->createSettlementIfEligible($sr->fresh());

        $this->assertDatabaseCount('bs_office_settlements', 1, 'business');
    }

    /** @test */
    public function no_settlement_is_created_before_completion(): void
    {
        $office = $this->createOffice();
        $sr     = $this->createDoneRequest($office, ['status' => 'in_progress', 'office_status' => 'in_progress']);

        $settlement = app(ServiceRequestService::class)->createSettlementIfEligible($sr->fresh());

        $this->assertNull($settlement);
        $this->assertDatabaseCount('bs_office_settlements', 0, 'business');
    }

    /** @test */
    public function no_settlement_is_created_without_an_executing_office(): void
    {
        $sr = $this->createDoneRequest($this->createOffice(), ['office_id' => null]);

        $settlement = app(ServiceRequestService::class)->createSettlementIfEligible($sr->fresh());

        $this->assertNull($settlement);
        $this->assertDatabaseCount('bs_office_settlements', 0, 'business');
    }

    /** @test */
    public function outstanding_amount_totals_only_pending_settlements(): void
    {
        $office = $this->createOffice();
        $sr     = $this->createDoneRequest($office);

        app(ServiceRequestService::class)->createSettlementIfEligible($sr->fresh());

        $paid = OfficeSettlement::firstOrFail();
        $paid->markPaid('BANK-REF-1');

        $pendingSr = $this->createDoneRequest($office, [
            'ref_number'        => 'OF-SETTLE-2',
            'client_name'       => 'عميل ثانٍ',
            'price'             => 100,
            'commission_amount' => 10,
        ], 'settle2@example.com');
        app(ServiceRequestService::class)->createSettlementIfEligible($pendingSr->fresh());

        $this->assertEquals(90, OfficeSettlement::outstandingForOffice($office->id));

        // يؤكد أن علامة «مدفوعة» كتبت على السجل الأول فقط.
        $this->assertSame(OfficeSettlement::STATUS_PAID, $paid->fresh()->status);
        $this->assertSame('BANK-REF-1', $paid->fresh()->transaction_ref);
        $this->assertNotNull($paid->fresh()->settled_at);
    }
}