<?php

namespace Tests\Feature;

use App\Livewire\Bits\Create;
use App\Livewire\Bits\Edit;
use App\Livewire\Bits\Index;
use App\Livewire\Bits\Show;
use App\Models\Bit;
use App\Models\Entry;
use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BitsFlowTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        $user = User::factory()->create();
        app(UserProvisioning::class)->seedNewUser($user);

        return $user->fresh();
    }

    private function bit(User $user, array $attrs = []): Bit
    {
        return Bit::create(array_merge([
            'user_id' => $user->id, 'title' => 'Run', 'icon_key' => 'run',
            'goal_per_week' => 5, 'status' => 'active', 'start_date' => now()->toDateString(),
        ], $attrs));
    }

    public function test_all_bits_routes_render(): void
    {
        $user = $this->user();
        $bit = $this->bit($user);

        $this->actingAs($user)->get(route('bits.index'))->assertOk()->assertSee('All Bits');
        $this->actingAs($user)->get(route('bits.archive'))->assertOk()->assertSee('Archive');
        $this->actingAs($user)->get(route('bits.create'))->assertOk()->assertSee('New Bit');
        $this->actingAs($user)->get(route('bits.show', $bit))->assertOk()->assertSee('Bit Detail')->assertSee('Run');
        $this->actingAs($user)->get(route('bits.edit', $bit))->assertOk()->assertSee('Edit Bit');
    }

    public function test_create_bit(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)->test(Create::class)
            ->set('title', 'Meditate')
            ->set('goalPerWeek', 3)
            ->set('iconKey', 'lotus')
            ->call('save');

        $this->assertDatabaseHas('bits', ['user_id' => $user->id, 'title' => 'Meditate', 'goal_per_week' => 3, 'icon_key' => 'lotus']);
    }

    public function test_new_bit_defaults_to_today_and_saves(): void
    {
        $user = $this->user();
        $today = $user->localToday()->toDateString();

        Livewire::actingAs($user)->test(Create::class)
            ->assertSet('startDate', $today)       // defaults to today, whatever weekday
            ->set('title', 'Journal')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bits', ['user_id' => $user->id, 'title' => 'Journal', 'start_date' => $today]);

        // A past date is rejected.
        Livewire::actingAs($user)->test(Create::class)
            ->set('title', 'Old')
            ->set('startDate', now()->subWeeks(2)->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString())
            ->call('save')
            ->assertHasErrors('startDate');
    }

    public function test_title_is_trimmed_and_tags_stripped(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)->test(Create::class)
            ->set('title', '  <b>Stretch</b>  ')
            ->set('why', '  stay limber  ')
            ->call('save');

        $this->assertDatabaseHas('bits', ['user_id' => $user->id, 'title' => 'Stretch', 'why' => 'stay limber']);
    }

    public function test_title_auto_suggests_icon_on_create(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)->test(Create::class)
            ->assertSet('iconKey', 'sparkle')
            ->set('title', 'Morning run')
            ->assertSet('iconKey', 'run');
    }

    public function test_start_date_must_be_week_aligned_and_end_on_sunday(): void
    {
        $user = $this->user(); // monday-start by default

        // A Wednesday start and a Tuesday end are both rejected.
        $wed = now()->next(\Carbon\Carbon::WEDNESDAY)->toDateString();
        $tue = now()->next(\Carbon\Carbon::TUESDAY)->addWeek()->toDateString();

        Livewire::actingAs($user)->test(Create::class)
            ->set('title', 'Lift')
            ->set('startDate', $wed)
            ->set('unlimited', false)
            ->set('endDate', $tue)
            ->call('save')
            ->assertHasErrors(['startDate', 'endDate']);

        // A Monday start with a Sunday end saves cleanly.
        $mon = now()->next(\Carbon\Carbon::MONDAY)->toDateString();
        $sun = now()->next(\Carbon\Carbon::MONDAY)->addWeek()->next(\Carbon\Carbon::SUNDAY)->toDateString();

        Livewire::actingAs($user)->test(Create::class)
            ->set('title', 'Lift')
            ->set('startDate', $mon)
            ->set('unlimited', false)
            ->set('endDate', $sun)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bits', ['user_id' => $user->id, 'title' => 'Lift', 'start_date' => $mon]);
    }

    public function test_moving_start_date_auto_adjusts_the_end_and_saves(): void
    {
        $user = $this->user(); // monday-start
        $mon = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
        $sun = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(6)->toDateString();
        $bit = $this->bit($user, ['start_date' => $mon, 'end_date' => $sun]);

        $nextMon = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addWeek()->toDateString();
        $nextSun = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addWeek()->addDays(6)->toDateString();

        Livewire::actingAs($user)->test(Edit::class, ['bit' => $bit])
            ->assertSet('endDate', $sun)
            ->set('startDate', $nextMon)      // would strand the old end (25th) before the new start
            ->assertSet('endDate', $nextSun)  // end auto-moves to the new week's Sunday
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bits', ['id' => $bit->id, 'start_date' => $nextMon, 'end_date' => $nextSun]);
    }

    public function test_edit_logs_goal_change(): void
    {
        $user = $this->user();
        $bit = $this->bit($user, ['goal_per_week' => 3]);

        Livewire::actingAs($user)->test(Edit::class, ['bit' => $bit])
            ->assertSet('title', 'Run')
            ->set('goalPerWeek', 6)
            ->call('save');

        $this->assertDatabaseHas('bits', ['id' => $bit->id, 'goal_per_week' => 6]);
        $this->assertDatabaseHas('bit_changes', ['bit_id' => $bit->id, 'field' => 'goal', 'from_val' => '3', 'to_val' => '6']);
    }

    public function test_show_history_cycle_and_pause(): void
    {
        $user = $this->user();
        $bit = $this->bit($user);
        $today = now()->format('Y-m-d');

        Livewire::actingAs($user)->test(Show::class, ['bit' => $bit])
            ->call('toggleToday');
        $this->assertDatabaseHas('entries', ['bit_id' => $bit->id, 'date' => $today, 'status' => 'done']);

        // Cycle today: done -> skipped
        Livewire::actingAs($user)->test(Show::class, ['bit' => $bit])
            ->call('cycle', $today);
        $this->assertDatabaseHas('entries', ['bit_id' => $bit->id, 'date' => $today, 'status' => 'skipped']);

        Livewire::actingAs($user)->test(Show::class, ['bit' => $bit])
            ->call('pauseResume');
        $this->assertDatabaseHas('bits', ['id' => $bit->id, 'status' => 'paused']);
    }

    public function test_goal_upgrade_after_four_perfect_weeks(): void
    {
        $user = $this->user();
        $bit = $this->bit($user, ['goal_per_week' => 3, 'start_date' => now()->subWeeks(6)->startOfWeek()->toDateString()]);

        // Fill the last 4 weeks with 3 done days each (at goal).
        for ($w = 0; $w < 4; $w++) {
            $ws = now()->startOfWeek()->subWeeks($w);
            foreach (range(0, 2) as $i) {
                Entry::create(['bit_id' => $bit->id, 'user_id' => $user->id, 'date' => $ws->copy()->addDays($i)->toDateString(), 'status' => 'done', 'source' => 'manual']);
            }
        }

        Livewire::actingAs($user)->test(Show::class, ['bit' => $bit])
            ->assertSee('4 perfect weeks')
            ->call('upgradeGoal');

        $this->assertDatabaseHas('bits', ['id' => $bit->id, 'goal_per_week' => 4]);
        $this->assertDatabaseHas('bit_changes', ['bit_id' => $bit->id, 'field' => 'goal', 'to_val' => '4']);
    }

    public function test_backfill_logs_a_past_day(): void
    {
        $user = $this->user();
        $bit = $this->bit($user, ['start_date' => now()->subWeeks(2)->toDateString()]);
        $threeDaysAgo = now()->subDays(3)->format('Y-m-d');

        Livewire::actingAs($user)->test(Show::class, ['bit' => $bit])
            ->set('backfillOpen', true)
            ->call('backfillToggle', $threeDaysAgo);

        $this->assertDatabaseHas('entries', ['bit_id' => $bit->id, 'date' => $threeDaysAgo, 'status' => 'done']);
    }

    public function test_index_archive_and_delete(): void
    {
        $user = $this->user();
        $bit = $this->bit($user);

        Livewire::actingAs($user)->test(Index::class)
            ->call('archive', $bit->id);
        $this->assertDatabaseHas('bits', ['id' => $bit->id, 'status' => 'archived']);

        Livewire::actingAs($user)->test(Index::class)
            ->call('delete', $bit->id);
        $this->assertDatabaseMissing('bits', ['id' => $bit->id]);
    }
}
