<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmrtmPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads_at_root(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_legacy_amrtm_redirects_to_root(): void
    {
        $response = $this->get('/amrtm');
        $response->assertStatus(301);
        $response->assertRedirect('/');
    }

    public function test_login_page_loads_at_root_login(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_api_services_endpoint_returns_json(): void
    {
        $response = $this->get('/api/services');
        $response->assertStatus(200);
    }

    public function test_legacy_api_services_works(): void
    {
        $response = $this->get('/amrtm/api/services');
        $response->assertStatus(200);
    }
}
