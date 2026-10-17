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
}
