<?php

namespace Tests\Feature;

use App\Models\Business\BusinessUser;
use App\Models\ServicePayment;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PaymentSimulationTest extends TestCase
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
            'cache.default' => 'array',
            'services.hyperpay.mode' => 'simulate',
        ]);

        DB::purge('business');
        Cache::flush();

        Artisan::call('migrate', ['--database' => 'business', '--force' => true]);
    }

    private function createUser(array $overrides = []): BusinessUser
    {
        return BusinessUser::query()->create(array_merge([
            'name'         => 'عميل الاختبار',
            'email'        => 'pay@example.com',
            'phone'        => '0555555555',
            'id_number'    => '1111111111',
            'password'     => Hash::make('secret123'),
            'role'         => 'user',
            'account_type' => 'individual',
            'is_active'    => true,
        ], $overrides));
    }

    /** @test */
    public function a_simulated_successful_charge_credits_the_wallet(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'business');

        $initiate = $this->postJson(route('amrtm.api.payments.charge'), ['amount' => 300, 'purpose' => 'service']);
        $initiate->assertOk();

        $checkoutId = $initiate->json('checkout_id');
        $this->assertStringStartsWith('SIM-', $checkoutId);
        $this->assertSame(route('amrtm.payment.checkout', $checkoutId), $initiate->json('redirect_url'));

        $this->assertDatabaseHas('bs_payments', [
            'user_id'         => $user->id,
            'transaction_ref' => 'HP-' . $checkoutId,
            'status'          => 'pending',
            'amount'          => 0,
        ], 'business');

        $this->get(route('amrtm.payment.checkout', $checkoutId))
            ->assertOk()
            ->assertSee('وضع المحاكاة')
            ->assertSee('الرصيد المضاف إلى المحفظة')
            ->assertSee('مبلغاً يساوي قيمة الخدمة')
            ->assertSee($checkoutId);

        // المبلغ المخصص (350) أكبر من قيمة الخدمة (300) — الزائد (50) يدخل المحفظة.
        $this->post(route('amrtm.payment.simulate', $checkoutId), ['result' => 'success', 'amount' => 350])
            ->assertRedirect(route('amrtm.payment.callback', ['id' => $checkoutId]));

        $this->get(route('amrtm.payment.callback', ['id' => $checkoutId]))
            ->assertRedirect(route('amrtm.user.dashboard'));

        $this->assertDatabaseHas('bs_payments', [
            'user_id'         => $user->id,
            'transaction_ref' => 'HP-' . $checkoutId,
            'type'            => 'charge',
            'status'          => 'completed',
            'amount'          => 350,
        ], 'business');

        $this->assertSame(350.0, ServicePayment::getBalance($user->id));
    }

    /** @test */
    public function a_successful_charge_exactly_at_the_service_value_is_allowed(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'business');

        $initiate = $this->postJson(route('amrtm.api.payments.charge'), ['amount' => 300, 'purpose' => 'service']);
        $initiate->assertOk();

        $checkoutId = $initiate->json('checkout_id');

        $this->post(route('amrtm.payment.simulate', $checkoutId), ['result' => 'success', 'amount' => 300])
            ->assertRedirect(route('amrtm.payment.callback', ['id' => $checkoutId]));

        $this->get(route('amrtm.payment.callback', ['id' => $checkoutId]))
            ->assertRedirect(route('amrtm.user.dashboard'));

        $this->assertDatabaseHas('bs_payments', [
            'user_id'         => $user->id,
            'transaction_ref' => 'HP-' . $checkoutId,
            'type'            => 'charge',
            'status'          => 'completed',
            'amount'          => 300,
        ], 'business');

        $this->assertSame(300.0, ServicePayment::getBalance($user->id));
    }

    /** @test */
    public function a_success_below_the_minimum_amount_is_rejected_and_nothing_is_credited(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'business');

        $initiate = $this->postJson(route('amrtm.api.payments.charge'), ['amount' => 300, 'purpose' => 'service']);
        $initiate->assertOk();

        $checkoutId = $initiate->json('checkout_id');

        $this->post(route('amrtm.payment.simulate', $checkoutId), ['result' => 'success', 'amount' => 100])
            ->assertRedirect(route('amrtm.payment.checkout', $checkoutId))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseHas('bs_payments', [
            'user_id'         => $user->id,
            'transaction_ref' => 'HP-' . $checkoutId,
            'type'            => 'charge',
            'status'          => 'pending',
            'amount'          => 0,
        ], 'business');

        $this->assertSame(0.0, ServicePayment::getBalance($user->id));
    }

    /** @test */
    public function a_success_without_an_amount_is_rejected(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'business');

        $initiate = $this->postJson(route('amrtm.api.payments.charge'), ['amount' => 300]);
        $initiate->assertOk();

        $checkoutId = $initiate->json('checkout_id');

        $this->post(route('amrtm.payment.simulate', $checkoutId), ['result' => 'success'])
            ->assertRedirect(route('amrtm.payment.checkout', $checkoutId))
            ->assertSessionHasErrors('amount');

        $this->assertSame(0.0, ServicePayment::getBalance($user->id));
    }

    /** @test */
    public function a_simulated_failure_does_not_credit_the_wallet(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'business');

        $initiate = $this->postJson(route('amrtm.api.payments.charge'), ['amount' => 500]);
        $initiate->assertOk();

        $checkoutId = $initiate->json('checkout_id');

        $this->post(route('amrtm.payment.simulate', $checkoutId), ['result' => 'failure'])
            ->assertRedirect(route('amrtm.payment.callback', ['id' => $checkoutId]));

        $this->get(route('amrtm.payment.callback', ['id' => $checkoutId]))
            ->assertRedirect(route('amrtm.user.dashboard'));

        $this->assertDatabaseHas('bs_payments', [
            'user_id'         => $user->id,
            'transaction_ref' => 'HP-' . $checkoutId,
            'type'            => 'charge',
            'status'          => 'pending',
            'amount'          => 0,
        ], 'business');

        $this->assertDatabaseMissing('bs_payments', [
            'user_id'         => $user->id,
            'transaction_ref' => 'HP-' . $checkoutId,
            'status'          => 'completed',
        ], 'business');

        $this->assertSame(0.0, ServicePayment::getBalance($user->id));
    }
}