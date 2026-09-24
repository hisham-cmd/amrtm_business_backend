<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_legacy_amrtm_url_redirects_to_root(): void
    {
        $response = $this->get('/amrtm');

        $response->assertStatus(301);
        $response->assertRedirect('/');
    }

    public function test_api_services_endpoint_returns_success(): void
    {
        $response = $this->get('/api/services');

        $response->assertStatus(200);
    }

    public function test_legacy_api_services_alias_returns_success(): void
    {
        $response = $this->get('/amrtm/api/services');

        $response->assertStatus(200);
    }
}
