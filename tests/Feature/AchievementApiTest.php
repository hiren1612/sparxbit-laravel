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

    public function test_one_bit_finishing_the_week_returns_both_bit_and_weekly_trophies(): void
    {
        $user = User::factory()->create(['timezone' => 'UTC', 'week_starts_on' => 'monday']);
        app(UserProvisioning::class)->seedNewUser($user);
        Sanctum::actingAs($user->fresh());
        $today = $user->localToday()->toDateString();
        $bit = Bit::create(['user_id' => $user->id, 'title' => 'Water', 'icon_key' => 'water', 'goal_per_week' => 1, 'status' => 'active', 'start_date' => $today]);
        Entry::create(['bit_id' => $bit->id, 'user_id' => $user->id, 'date' => $today, 'status' => 'done', 'source' => 'manual']);

        $res = $this->postJson('/api/achievements/sync')->assertOk();
        $this->assertSame(['bit', 'weekly'], array_column($res->json('celebrations'), 'type'));
        $this->assertSame('weekly', $res->json('celebration.type'));
    }

    public function test_website_shows_each_new_trophy_in_turn(): void
    {
        $user = User::factory()->create(['timezone' => 'UTC', 'week_starts_on' => 'monday', 'onboarding_completed_at' => now()]);
        app(UserProvisioning::class)->seedNewUser($user);
        $today = $user->localToday()->toDateString();
        $bit = Bit::create(['user_id' => $user->id, 'title' => 'Water', 'icon_key' => 'water', 'goal_per_week' => 1, 'status' => 'active', 'start_date' => $today]);
        Entry::create(['bit_id' => $bit->id, 'user_id' => $user->id, 'date' => $today, 'status' => 'done', 'source' => 'manual']);

        $c = \Livewire\Livewire::actingAs($user->fresh())->test(\App\Livewire\Dashboard::class);
        $this->assertSame('bit', $c->get('celebration')['type']);
        $c->call('dismissCelebration');
        $this->assertSame('weekly', $c->get('celebration')['type']);
        $c->call('dismissCelebration');
        $this->assertNull($c->get('celebration'));
    }
}
