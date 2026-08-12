<?php

namespace Tests\Feature\Api;

use App\Models\MobileSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_settings_endpoint_returns_no_cache_headers_without_breaking_response_shape(): void
    {
        config(['app.url' => 'https://new-server.example.com']);

        MobileSetting::create([
            'conclusion_apps' => 'Pengaturan mobile terbaru',
            'app_version_student' => '.20.1',
        ]);

        $response = $this->getJson('/api/mobile-settings');

        $response
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('Expires', '0')
            ->assertHeader('X-Server-Url', 'https://new-server.example.com')
            ->assertJsonStructure([
                '*' => ['id', 'conclusion_apps', 'app_version_student'],
            ]);
    }
}
