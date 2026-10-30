<?php

namespace Tests\Feature;

use App\Models\Bit;
use App\Models\Entry;
use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AchievementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_returns_a_celebration_once_then_nothing(): void
    {
        $user = User::factory()->create(['timezone' => 'UTC', 'week_starts_on' => 'monday']);
        app(UserProvisioning::class)->seedNewUser($user);
        $user = $user->fresh();
        Sanctum::actingAs($user);

        // Nothing earned yet.
        $this->postJson('/api/achievements/sync')->assertOk()->assertJson(['celebration' => null]);

        $today = $user->localToday()->toDateString();
        $bit = Bit::create([
            'user_id' => $user->id, 'title' => 'Run', 'icon_key' => 'run',
            'goal_per_week' => 1, 'status' => 'active', 'start_date' => $today,
        ]);
        Entry::create(['bit_id' => $bit->id, 'user_id' => $user->id, 'date' => $today, 'status' => 'done', 'source' => 'manual']);

        $res = $this->postJson('/api/achievements/sync')->assertOk();
        $this->assertNotNull($res->json('celebration'));
        $this->assertContains($res->json('celebration.type'), ['bit', 'weekly', 'streak']);
        $this->assertStringContainsString('?ref=', $res->json('celebration.shareUrl'));

        // Awarded once — a second sync has nothing new.
        $this->postJson('/api/achievements/sync')->assertOk()->assertJson(['celebration' => null]);
    }

    public function test_sync_requires_auth(): void
    {
        $this->postJson('/api/achievements/sync')->assertUnauthorized();
    }
}
