<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Bit;
use App\Models\Entry;
use App\Models\User;
use App\Services\AchievementService;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class AchievementTest extends TestCase
{
    use RefreshDatabase;

    private function freshUser(): User
    {
        $user = User::factory()->create(['week_starts_on' => 'monday', 'timezone' => 'UTC']);
        app(UserProvisioning::class)->seedNewUser($user);

        return $user->fresh();
    }

    public function test_weekly_goal_awards_a_trophy_when_every_bit_hits_its_goal(): void
    {
        $user = $this->freshUser();
        $bit = Bit::create([
            'user_id' => $user->id, 'title' => 'Run', 'icon_key' => 'run',
            'goal_per_week' => 2, 'status' => 'active',
            'start_date' => now()->startOfWeek(Carbon::MONDAY)->toDateString(),
        ]);

        // No trophy before the goal is met.
        $this->assertNull(AchievementService::sync($user));

        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        foreach ([0, 1] as $i) {
            Entry::create([
                'bit_id' => $bit->id, 'user_id' => $user->id,
                'date' => $weekStart->copy()->addDays($i)->toDateString(),
                'status' => 'done',
            ]);
        }

        $trophy = AchievementService::sync($user);
        $this->assertNotNull($trophy);
        $this->assertSame('weekly', $trophy->type);
        $this->assertDatabaseHas('achievements', ['user_id' => $user->id, 'type' => 'weekly']);

        // Idempotent — syncing again does not re-award the same week.
        $this->assertNull(AchievementService::sync($user));
    }

    public function test_seven_day_checkin_streak_unlocks_a_streak_trophy(): void
    {
        $user = $this->freshUser();
        $system = $user->bits()->where('is_system', true)->first();

        for ($i = 0; $i < 7; $i++) {
            Entry::create([
                'bit_id' => $system->id, 'user_id' => $user->id,
                'date' => now()->subDays($i)->toDateString(),
                'status' => 'done',
            ]);
        }

        $this->assertSame(7, AchievementService::checkinStreak($user));

        $trophy = AchievementService::sync($user);
        $this->assertNotNull($trophy);
        $this->assertSame('streak', $trophy->type);
        $this->assertDatabaseHas('achievements', ['user_id' => $user->id, 'key' => 'streak-7']);
        $this->assertDatabaseMissing('achievements', ['user_id' => $user->id, 'key' => 'streak-30']);
    }

    public function test_dashboard_shows_the_celebration_after_completing_the_weekly_goal(): void
    {
        $user = $this->freshUser();
        $bit = Bit::create([
            'user_id' => $user->id, 'title' => 'Meditate', 'icon_key' => 'calm',
            'goal_per_week' => 1, 'status' => 'active',
            'start_date' => now()->startOfWeek(Carbon::MONDAY)->toDateString(),
        ]);

        Livewire::actingAs($user)->test(Dashboard::class)
            ->call('toggle', $bit->id)
            ->assertSet('celebration', fn ($c) => is_array($c) && $c['type'] === 'weekly')
            ->assertSee('Share your trophy')
            ->call('dismissCelebration')
            ->assertSet('celebration', null);
    }
}
