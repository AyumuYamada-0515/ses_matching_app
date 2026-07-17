<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DeploymentAccessTest extends TestCase
{
    public function test_access_gate_is_disabled_when_credentials_are_not_configured(): void
    {
        config(['deployment_access.username' => null, 'deployment_access.password' => null]);

        $this->get('/login')->assertOk();
    }

    public function test_access_gate_rejects_requests_without_basic_authentication(): void
    {
        config(['deployment_access.username' => 'preview', 'deployment_access.password' => 'secret']);

        $this->get('/login')
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate');
    }

    public function test_access_gate_accepts_matching_basic_authentication(): void
    {
        config(['deployment_access.username' => 'preview', 'deployment_access.password' => 'secret']);

        $this->withBasicAuth('preview', 'secret')->get('/login')->assertOk();
    }

    public function test_health_check_remains_available_without_authentication(): void
    {
        config(['deployment_access.username' => 'preview', 'deployment_access.password' => 'secret']);

        $this->get('/up')->assertOk();
    }
    public function test_forwarded_https_scheme_is_trusted_for_cloud_deployments(): void
    {
        Route::get('/proxy-check', fn (Request $request) => [
            'secure' => $request->isSecure(),
            'login_url' => route('login'),
        ]);

        $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'preview.example',
        ])->get('/proxy-check')->assertOk()->assertJson([
            'secure' => true,
            'login_url' => 'https://preview.example/login',
        ]);
    }
}
