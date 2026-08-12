<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ForceMobileRefreshCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_requires_target_option(): void
    {
        $this->artisan('mobile:force-refresh')
            ->expectsOutput('Gunakan --all atau --user_id=ID')
            ->assertExitCode(2);
    }

    public function test_it_revokes_tokens_and_clears_fcm_for_single_user(): void
    {
        Cache::put('mobile-settings-cache', 'stale-value', 600);

        $targetUser = User::factory()->create(['fcm_token' => 'target-token']);
        $otherUser = User::factory()->create(['fcm_token' => 'other-token']);

        $targetUser->createToken('auth_token');
        $otherUser->createToken('auth_token');

        $this->artisan('mobile:force-refresh', ['--user_id' => $targetUser->id])
            ->expectsOutput('Force refresh mobile berhasil dijalankan.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'fcm_token' => null,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $otherUser->id,
            'fcm_token' => 'other-token',
        ]);

        $this->assertNull(Cache::get('mobile-settings-cache'));
    }
}
