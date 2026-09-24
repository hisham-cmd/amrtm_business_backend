<?php

namespace Tests\Feature;

use App\Models\Business\BusinessUser;
use App\Models\ServicePayment;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminFinanceTest extends TestCase
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
            'name' => 'عميل اختبار',
            'email' => 'client@example.com',
            'phone' => '0550000000',
            'password' => Hash::make('secret123'),
            'role' => 'user',
            'account_type' => 'individual',
            'is_active' => true,
        ], $overrides));
    }

    /** @test */
    public function admin_can_fetch_the_finance_ledger_with_summary_and_transactions(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'name' => 'مدير النظام', 'email' => 'admin@finance.test']);
        $client = $this->createUser(['name' => 'عميل أحمد']);

        ServicePayment::create(['user_id' => $client->id, 'amount' => 100, 'type' => 'charge', 'status' => 'completed', 'description_ar' => 'شحن رصيد', 'description_en' => 'Top-up', 'transaction_ref' => 'TXN-100']);
        ServicePayment::create(['user_id' => $client->id, 'amount' => 40, 'type' => 'payment', 'status' => 'completed', 'description_ar' => 'دفع مقابل خدمة', 'description_en' => 'Service payment', 'transaction_ref' => 'TXN-200']);
        ServicePayment::create(['user_id' => $client->id, 'amount' => 10, 'type' => 'refund', 'status' => 'completed', 'description_ar' => 'استرداد', 'description_en' => 'Refund', 'transaction_ref' => 'TXN-300']);

        $response = $this->actingAs($admin, 'business')
            ->withHeader('Accept', 'application/json')
            ->getJson(route('amrtm.api.admin.finance'))
            ->assertOk();

        $json = $response->json();

        $this->assertEquals(70, $json['summary']['total_revenue']);
        $this->assertArrayHasKey('this_week', $json['summary']);
        $this->assertArrayHasKey('avg_order', $json['summary']);
        $this->assertArrayHasKey('pending', $json['summary']);

        $this->assertCount(3, $json['transactions']);
        $this->assertEquals('عميل أحمد', $json['transactions'][0]['client']);
        $this->assertEquals('charge', $json['transactions'][0]['type']);
        $this->assertEquals(100, $json['transactions'][0]['amount']);

        $this->assertEquals(1, $json['pagination']['page']);
        $this->assertEquals(3, $json['pagination']['total']);
    }

    /** @test */
    public function finance_ledger_respects_type_and_search_filters(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'name' => 'مدير النظام', 'email' => 'admin2@finance.test']);
        $client = $this->createUser(['name' => 'عميل فريد']);

        ServicePayment::create(['user_id' => $client->id, 'amount' => 50, 'type' => 'charge', 'status' => 'completed', 'description_ar' => 'شحن رصيد', 'description_en' => 'Top-up', 'transaction_ref' => 'TXN-C1']);
        ServicePayment::create(['user_id' => $client->id, 'amount' => 25, 'type' => 'payment', 'status' => 'completed', 'description_ar' => 'دفع', 'description_en' => 'Payment', 'transaction_ref' => 'TXN-P1']);

        $this->actingAs($admin, 'business')
            ->getJson(route('amrtm.api.admin.finance', ['type' => 'charge']))
            ->assertOk()
            ->assertJsonCount(1, 'transactions')
            ->assertJsonPath('transactions.0.type', 'charge');

        $this->actingAs($admin, 'business')
            ->getJson(route('amrtm.api.admin.finance', ['search' => 'TXN-P1']))
            ->assertOk()
            ->assertJsonCount(1, 'transactions')
            ->assertJsonPath('transactions.0.type', 'payment');

        $this->actingAs($admin, 'business')
            ->getJson(route('amrtm.api.admin.finance', ['search' => 'عميل فريد']))
            ->assertOk()
            ->assertJsonCount(2, 'transactions');
    }

    /** @test */
    public function a_regular_client_cannot_access_the_finance_ledger(): void
    {
        $client = $this->createUser(['name' => 'عميل عادي']);

        $this->actingAs($client, 'business')
            ->withHeader('Accept', 'application/json')
            ->getJson(route('amrtm.api.admin.finance'))
            ->assertForbidden();
    }

    /** @test */
    public function manual_balance_adjustment_reflects_in_the_ledger(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'name' => 'مدير النظام', 'email' => 'admin3@finance.test']);
        $client = $this->createUser(['name' => 'عميل محظوظ']);

        $this->actingAs($admin, 'business')
            ->withHeader('Accept', 'application/json')
            ->postJson(route('amrtm.api.admin.users.balance', ['id' => $client->id]), [
                'amount' => 250,
                'type' => 'charge',
                'reason' => 'شحن يدوي من الإدارة',
            ])
            ->assertOk()
            ->assertJsonPath('new_balance', 250);

        $this->actingAs($admin, 'business')
            ->getJson(route('amrtm.api.admin.finance', ['search' => 'عميل محظوظ']))
            ->assertOk()
            ->assertJsonCount(1, 'transactions')
            ->assertJsonPath('transactions.0.amount', 250)
            ->assertJsonPath('transactions.0.type', 'charge')
            ->assertJsonPath('summary.total_revenue', 250);
    }
}