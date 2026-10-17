<?php

namespace Tests\Feature;

use App\Livewire\Stats;
use App\Models\Bit;
use App\Models\Entry;
use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_insights_render_across_ranges(): void
    {
        $user = User::factory()->create();
        app(UserProvisioning::class)->seedNewUser($user);
        $user = $user->fresh();

        $bit = Bit::create([
            'user_id' => $user->id, 'title' => 'Walk', 'icon_key' => 'walk',
            'goal_per_week' => 7, 'status' => 'active', 'start_date' => now()->subMonth()->toDateString(),
        ]);
        Entry::create(['bit_id' => $bit->id, 'user_id' => $user->id, 'date' => now()->toDateString(), 'status' => 'done', 'source' => 'manual']);

        $c = Livewire::actingAs($user)->test(Stats::class)
            ->assertSee('Insights')
            ->assertSee('Goals')
            ->assertSee('Daily Check-In')
            ->assertSee('Walk');

        $c->call('setRange', 'month')->assertSet('range', 'month')->assertSee('Insights');
        $c->call('setRange', 'all')->assertSet('range', 'all')->assertSee('Insights');
        $c->call('toggleExpand', $bit->id)->assertSet('expandedBitId', $bit->id);
    }

    public function test_per_bit_ring_caps_total_not_per_week(): void
    {
        $user = User::factory()->create();
        app(UserProvisioning::class)->seedNewUser($user);
        $user = $user->fresh();

        $bit = Bit::create([
            'user_id' => $user->id, 'title' => 'Gym', 'icon_key' => 'run',
            'goal_per_week' => 3, 'status' => 'active', 'start_date' => now()->subWeeks(1)->startOfWeek()->toDateString(),
        ]);

        // Last week: 5 done (over goal). This week: 1 done. Over the 5-week
        // "month" range the ring target counts both active weeks (3+3=6) and
        // total done is 6, so a strong week offsets a light one → 100%.
        $lastWeek = now()->subWeek()->startOfWeek();
        foreach (range(0, 4) as $i) {
            Entry::create(['bit_id' => $bit->id, 'user_id' => $user->id, 'date' => $lastWeek->copy()->addDays($i)->toDateString(), 'status' => 'done', 'source' => 'manual']);
        }
        Entry::create(['bit_id' => $bit->id, 'user_id' => $user->id, 'date' => now()->startOfWeek()->toDateString(), 'status' => 'done', 'source' => 'manual']);

        // The ring renders "done/target" = 6/6 (capped at the total, not 4/6
        // which per-week capping min(5,3)+min(1,3) would have produced).
        Livewire::actingAs($user)->test(Stats::class)
            ->call('setRange', 'month')
            ->assertSee('Gym')
            ->assertSee('6/6')
            ->assertDontSee('4/6');
    }
}
