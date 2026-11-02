<?php

namespace Tests\Feature;

use App\Livewire\Calendar;
use App\Livewire\Dashboard;
use App\Models\Bit;
use App\Models\Entry;
use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class PastWeekTrophyTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // Monday 2 Nov 2026; the "Run" Bit ran Sat 31 Oct – Sun 1 Nov, goal 2.
    private function runBitUser(): array
    {
        Carbon::setTestNow('2026-11-02 10:00:00');
        $user = User::factory()->create(['timezone' => 'UTC', 'week_starts_on' => 'monday', 'onboarding_completed_at' => now()]);
        app(UserProvisioning::class)->seedNewUser($user);
        $bit = Bit::create([
            'user_id' => $user->id, 'title' => 'Run', 'icon_key' => 'run', 'goal_per_week' => 2,
            'status' => 'active', 'start_date' => '2026-10-31', 'end_date' => '2026-11-01',
        ]);

        return [$user->fresh(), $bit];
    }

    public function test_goal_reached_last_week_still_gets_its_trophy(): void
    {
        [$user, $bit] = $this->runBitUser();
        foreach (['2026-10-31', '2026-11-01'] as $d) {
            Entry::create(['bit_id' => $bit->id, 'user_id' => $user->id, 'date' => $d, 'status' => 'done', 'source' => 'manual']);
        }
        Sanctum::actingAs($user);

        $types = array_column($this->postJson('/api/achievements/sync')->json('celebrations'), 'title');
        $this->assertSame(['Run goal hit! 🎯', "Last week's goal smashed! 🎉"], $types);
    }

    public function test_past_days_cannot_be_ticked_or_unticked_on_the_website(): void
    {
        [$user, $bit] = $this->runBitUser();
        Entry::create(['bit_id' => $bit->id, 'user_id' => $user->id, 'date' => '2026-10-31', 'status' => 'done', 'source' => 'manual']);

        // Home: untick a past day — ignored.
        Livewire::actingAs($user)->test(Dashboard::class)
            ->call('selectDay', '2026-10-31')
            ->call('toggle', $bit->id);
        $this->assertDatabaseHas('entries', ['bit_id' => $bit->id, 'status' => 'done']);

        // Calendar: tick another past day — ignored.
        Livewire::actingAs($user)->test(Calendar::class)->call('toggle', $bit->id, '2026-11-01');
        $this->assertSame(1, Entry::where('bit_id', $bit->id)->count());
    }
}
