<?php

namespace Tests\Feature;

use App\Models\Business\BusinessUser;
use App\Models\Entity;
use App\Models\GovService;
use App\Models\RequestLog;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RequestTrackTest extends TestCase
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
            'name' => 'مستخدم اختبار',
            'email' => 'user@example.com',
            'phone' => '0550000000',
            'password' => Hash::make('secret123'),
            'role' => 'user',
            'account_type' => 'individual',
            'is_active' => true,
        ], $overrides));
    }

    private function createServiceRequest(int $userId): ServiceRequest
    {
        $category = \App\Models\Category::query()->create([
            'key' => 'ministries',
            'name_ar' => 'الوزارات',
            'name_en' => 'Ministries',
        ]);

        $entity = Entity::query()->create([
            'category_id' => $category->id,
            'name_ar' => 'وزارة التجارة',
            'name_en' => 'Ministry of Commerce',
            'is_active' => true,
        ]);

        $service = GovService::query()->create([
            'entity_id' => $entity->id,
            'name_ar' => 'إصدار سجل تجاري',
            'name_en' => 'Commercial Register',
            'price' => 200,
            'is_active' => true,
        ]);

        $sr = ServiceRequest::query()->create([
            'user_id' => $userId,
            'service_id' => $service->id,
            'entity_id' => $entity->id,
            'client_name' => 'مستخدم اختبار',
            'client_email' => 'user@example.com',
            'client_phone' => '0550000000',
            'client_id_number' => '1234567890',
            'price' => 200,
            'status' => 'processing',
        ]);

        RequestLog::query()->create([
            'request_id' => $sr->id,
            'user_id' => $userId,
            'status' => 'pending',
            'log_type' => 'status_change',
            'note' => 'تم تقديم الطلب',
        ]);

        RequestLog::query()->create([
            'request_id' => $sr->id,
            'user_id' => $userId,
            'status' => 'processing',
            'log_type' => 'status_change',
            'note' => 'تمت مراجعة الطلب',
        ]);

        return $sr;
    }

    /** @test */
    public function guests_are_redirected_to_login(): void
    {
        $this->get('/requests/1/track')->assertRedirect(route('amrtm.login'));
    }

    /** @test */
    public function a_client_can_track_their_own_request(): void
    {
        $user = $this->createUser();
        $sr = $this->createServiceRequest($user->id);

        $response = $this->actingAs($user, 'business')
            ->get(route('amrtm.requests.track', $sr->id))
            ->assertOk();

        $response->assertSee('طلب #' . $sr->ref_number, false);
        $response->assertSee($sr->ref_number);
        $response->assertSee('مسار الطلب');
        $response->assertSee('تم تقديم الطلب');
        $response->assertSee('قيد التنفيذ');
        $response->assertSee('بيانات الطلب');
    }

    /** @test */
    public function a_client_cannot_track_someone_elses_request(): void
    {
        $owner = $this->createUser(['email' => 'owner@example.com']);
        $other = $this->createUser(['email' => 'other@example.com']);
        $sr = $this->createServiceRequest($owner->id);

        $this->actingAs($other, 'business')
            ->get(route('amrtm.requests.track', $sr->id))
            ->assertForbidden();
    }

    /** @test */
    public function an_admin_can_track_any_request(): void
    {
        $owner = $this->createUser(['email' => 'owner2@example.com']);
        $admin = $this->createUser(['email' => 'admin-trx@example.com', 'role' => 'admin']);
        $sr = $this->createServiceRequest($owner->id);

        $response = $this->actingAs($admin, 'business')
            ->get(route('amrtm.requests.track', $sr->id))
            ->assertOk();

        $response->assertSee('طلب #' . $sr->ref_number, false);
    }

    /** @test */
    public function a_completed_request_shows_the_done_stage(): void
    {
        $user = $this->createUser();
        $sr = $this->createServiceRequest($user->id);
        $sr->update(['status' => 'done', 'completed_at' => now()]);

        RequestLog::query()->create([
            'request_id' => $sr->id,
            'user_id' => $user->id,
            'status' => 'done',
            'log_type' => 'status_change',
            'note' => 'اكتمل الطلب',
        ]);

        $response = $this->actingAs($user, 'business')
            ->get(route('amrtm.requests.track', $sr->id))
            ->assertOk();

        $response->assertSee('اكتملت العملية');
        $response->assertSee('اكتمل طلبك بنجاح');
    }

    /** @test */
    public function a_rejected_request_shows_the_rejection_stage(): void
    {
        $user = $this->createUser();
        $sr = $this->createServiceRequest($user->id);
        $sr->update(['status' => 'rejected', 'reject_reason' => 'نقص في المستندات']);

        RequestLog::query()->create([
            'request_id' => $sr->id,
            'user_id' => $user->id,
            'status' => 'rejected',
            'log_type' => 'status_change',
            'note' => 'نقص في المستندات',
        ]);

        $response = $this->actingAs($user, 'business')
            ->get(route('amrtm.requests.track', $sr->id))
            ->assertOk();

        $response->assertSee('تعذّر إتمام الطلب');
        $response->assertSee('نقص في المستندات');
    }
}