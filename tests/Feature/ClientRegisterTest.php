<?php

namespace Tests\Feature;

use App\Models\Business\BusinessUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientRegisterTest extends TestCase
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

    private function individualPayload(array $overrides = []): array
    {
        return array_merge([
            '_auth_mode'      => 'register',
            '_register_type'  => 'client',
            'account_type'    => 'individual',
            'name'            => 'محمد عبدالله',
            'father_name'     => 'عبدالله',
            'grandfather_name' => 'أحمد',
            'family_name'     => 'الغامدي',
            'id_number'       => '1098765432',
            'job_sector'      => 'government',
            'employment_status' => 'affiliated',
            'email'           => 'client@example.com',
            'phone'           => '0551234567',
            'phone_dial'      => '+966',
            'password'        => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'terms'           => '1',
        ], $overrides);
    }

    /** @test */
    public function it_registers_an_individual_client(): void
    {
        $response = $this->post(route('amrtm.register.submit'), $this->individualPayload());

        $response->assertRedirect(route('amrtm.dashboard.hub'));

        $user = BusinessUser::where('email', 'client@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('user', $user->role);
        $this->assertSame('individual', $user->account_type);
        $this->assertSame('+966', $user->phone_dial);
        $this->assertNull($user->legal_name);
        $this->assertNull($user->country);
        $this->assertTrue(Hash::check('Secret123!', $user->password));
        $this->assertAuthenticatedAs($user, 'business');
        $this->assertDatabaseHas('bs_users', [
            'email' => 'client@example.com',
        ], 'business');
    }

    /** @test */
    public function it_registers_an_establishment_client_with_full_address(): void
    {
        $response = $this->post(route('amrtm.register.submit'), $this->individualPayload([
            'account_type' => 'establishment',
            'legal_name'   => 'مؤسسة آمر تم للاستشارات',
            'entity_type'  => 'institution',
            'cr_number'    => '1010123456',
            'country'      => 'المملكة العربية السعودية',
            'region'       => 'منطقة الرياض',
            'city'         => 'الرياض',
            'district'     => 'العليا',
            'street'       => 'طريق الملك فهد',
        ]));

        $response->assertRedirect(route('amrtm.dashboard.hub'));

        $user = BusinessUser::where('email', 'client@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('establishment', $user->account_type);
        $this->assertSame('مؤسسة آمر تم للاستشارات', $user->legal_name);
        $this->assertSame('institution', $user->entity_type);
        $this->assertSame('1010123456', $user->cr_number);
        $this->assertSame('المملكة العربية السعودية', $user->country);
        $this->assertSame('منطقة الرياض', $user->region);
        $this->assertSame('الرياض', $user->city);
        $this->assertSame('العليا', $user->district);
        $this->assertSame('طريق الملك فهد', $user->street);
    }

    /** @test */
    public function it_rejects_an_establishment_submission_missing_org_fields(): void
    {
        $response = $this->from(route('amrtm.login'))
            ->post(route('amrtm.register.submit'), $this->individualPayload([
                'account_type' => 'establishment',
            ]));

        $response->assertRedirect(route('amrtm.login'));
        $response->assertSessionHasErrors([
            'legal_name', 'entity_type', 'cr_number', 'country', 'region', 'city',
        ]);

        $this->assertDatabaseMissing('bs_users', [
            'email' => 'client@example.com',
        ], 'business');
    }

    /** @test */
    public function it_accepts_an_individual_submission_with_individual_fields(): void
    {
        $response = $this->post(route('amrtm.register.submit'), $this->individualPayload());

        $response->assertRedirect(route('amrtm.dashboard.hub'));
        $this->assertDatabaseHas('bs_users', [
            'email' => 'client@example.com',
        ], 'business');
    }

    /** @test */
    public function it_stores_the_profile_photo_upload_for_an_individual_client(): void
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->createWithContent(
            'avatar.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=')
        );

        $response = $this->post(route('amrtm.register.submit'), $this->individualPayload([
            'profile_photo' => $photo,
        ]));

        $response->assertRedirect(route('amrtm.dashboard.hub'));

        $user = BusinessUser::where('email', 'client@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->profile_photo);
        $this->assertStringStartsWith('client-profiles/', $user->profile_photo);
        Storage::disk('public')->assertExists($user->profile_photo);
    }

    /** @test */
    public function it_rejects_a_non_image_profile_photo(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('notes.txt', 10, 'text/plain');

        $response = $this->from(route('amrtm.login'))
            ->post(route('amrtm.register.submit'), $this->individualPayload([
                'profile_photo' => $file,
            ]));

        $response->assertRedirect(route('amrtm.login'));
        $response->assertSessionHasErrors(['profile_photo']);
        $this->assertDatabaseMissing('bs_users', [
            'email' => 'client@example.com',
        ], 'business');
    }

    /** @test */
    public function it_rejects_an_individual_submission_missing_id_number(): void
    {
        $response = $this->from(route('amrtm.login'))
            ->post(route('amrtm.register.submit'), $this->individualPayload([
                'id_number' => null,
            ]));

        $response->assertRedirect(route('amrtm.login'));
        $response->assertSessionHasErrors(['id_number']);
        $this->assertDatabaseMissing('bs_users', [
            'email' => 'client@example.com',
        ], 'business');
    }

    /** @test */
    public function it_allows_an_establishment_submission_without_id_number(): void
    {
        $response = $this->post(route('amrtm.register.submit'), $this->individualPayload([
            'account_type' => 'establishment',
            'legal_name'   => 'مؤسسة آمر تم للاستشارات',
            'entity_type'  => 'institution',
            'cr_number'    => '1010123456',
            'country'      => 'المملكة العربية السعودية',
            'region'       => 'منطقة الرياض',
            'city'         => 'الرياض',
            'id_number'    => null,
        ]));

        $response->assertRedirect(route('amrtm.dashboard.hub'));

        $user = BusinessUser::where('email', 'client@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->id_number);
    }
}