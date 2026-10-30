<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Bit;
use App\Models\Entry;
use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function freshUser(): User
    {
        $user = User::factory()->create();
        app(UserProvisioning::class)->seedNewUser($user);

        return $user->fresh();
    }

    public function test_home_renders_with_week_score_and_bits(): void
    {
        $user = $this->freshUser();
        $bit = Bit::create([
            'user_id' => $user->id, 'title' => 'Run', 'icon_key' => 'run',
            'goal_per_week' => 7, 'status' => 'active', 'start_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($user)->test(Dashboard::class)
            ->assertSee('Good ') // greeting (morning/afternoon/evening — time-dependent)
            ->assertSee('This week')
            ->assertSee('Run')
            ->assertSee('Daily Check-In');
    }

    public function test_toggle_marks_bit_done_and_auto_checks_in(): void
    {
        $user = $this->freshUser();
        $bit = Bit::create([
            'user_id' => $user->id, 'title' => 'Read', 'icon_key' => 'book',
            'goal_per_week' => 5, 'status' => 'active', 'start_date' => now()->toDateString(),
        ]);
        $system = $user->bits()->where('is_system', true)->first();
        $today = now()->format('Y-m-d');

        Livewire::actingAs($user)->test(Dashboard::class)
            ->call('toggle', $bit->id);

        $this->assertDatabaseHas('entries', ['bit_id' => $bit->id, 'status' => 'done']);
        $this->assertDatabaseHas('entries', ['bit_id' => $system->id, 'status' => 'done']);

        // Toggling again removes the bit's entry.
        Livewire::actingAs($user)->test(Dashboard::class)
            ->call('toggle', $bit->id);

        $this->assertDatabaseMissing('entries', ['bit_id' => $bit->id, 'date' => $today]);
    }

    public function test_empty_state_cta_switches_from_first_bit_to_new_bit(): void
    {
        $user = $this->freshUser();

        // Brand-new user (no Bits) sees the first-run copy.
        Livewire::actingAs($user)->test(Dashboard::class)
            ->assertSee('Create your first Bit')
            ->assertDontSee('No Bits this week');

        // After creating a Bit, an empty week shows "New Bit" instead.
        Bit::create([
            'user_id' => $user->id, 'title' => 'Stretch', 'icon_key' => 'star',
            'goal_per_week' => 7, 'status' => 'active', 'start_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($user)->test(Dashboard::class)
            ->call('prevWeek') // a week before the Bit existed → no active Bits
            ->assertSee('New Bit')
            ->assertDontSee('Create your first Bit');
    }

    public function test_opening_home_auto_marks_the_daily_checkin_for_today(): void
    {
        $user = $this->freshUser();
        $system = $user->bits()->where('is_system', true)->first();
        $today = now()->format('Y-m-d');

        // No check-in before the user opens Home today.
        $this->assertDatabaseMissing('entries', ['bit_id' => $system->id, 'date' => $today]);

        Livewire::actingAs($user)->test(Dashboard::class);

        // Simply landing on Home counts today's Daily Check-In.
        $this->assertDatabaseHas('entries', ['bit_id' => $system->id, 'date' => $today, 'status' => 'done']);

        // Opening again does not duplicate it.
        Livewire::actingAs($user)->test(Dashboard::class);
        $this->assertSame(1, Entry::where('bit_id', $system->id)->whereDate('date', $today)->count());
    }

    public function test_greeting_reflects_the_users_local_time(): void
    {
        $user = $this->freshUser();
        $user->update(['timezone' => 'Asia/Kolkata']); // UTC+5:30
        $user = $user->fresh();

        // 09:00 UTC = 14:30 in Kolkata → afternoon.
        \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::parse('2026-10-28 09:00:00', 'UTC'));
        Livewire::actingAs($user)->test(Dashboard::class)->assertSee('Good afternoon');

        // 02:00 UTC = 07:30 in Kolkata → morning.
        \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::parse('2026-10-28 02:00:00', 'UTC'));
        Livewire::actingAs($user)->test(Dashboard::class)->assertSee('Good morning');

        // 15:00 UTC = 20:30 in Kolkata → evening.
        \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::parse('2026-10-28 15:00:00', 'UTC'));
        Livewire::actingAs($user)->test(Dashboard::class)->assertSee('Good evening');

        \Illuminate\Support\Carbon::setTestNow();
    }

    public function test_future_day_is_viewable_but_not_checkable(): void
    {
        $user = $this->freshUser();
        $bit = Bit::create([
            'user_id' => $user->id, 'title' => 'Run', 'icon_key' => 'run',
            'goal_per_week' => 5, 'status' => 'active', 'start_date' => now()->subWeek()->toDateString(),
        ]);
        $tomorrow = $user->localToday()->addDay()->toDateString();

        Livewire::actingAs($user)->test(Dashboard::class)
            ->call('selectDay', $tomorrow)
            ->assertSet('selectedDateISO', $tomorrow)
            ->assertSee('Run')
            ->assertSee('Upcoming day')
            ->call('toggle', $bit->id); // even a forced call must not record it

        $this->assertDatabaseMissing('entries', ['bit_id' => $bit->id, 'date' => $tomorrow]);
    }

    public function test_future_start_bit_excluded_until_it_begins(): void
    {
        $user = $this->freshUser();
        $future = Bit::create([
            'user_id' => $user->id, 'title' => 'Future Habit', 'icon_key' => 'star',
            'goal_per_week' => 7, 'status' => 'active', 'start_date' => now()->addWeeks(2)->toDateString(),
        ]);

        // Not shown on today's Home (it hasn't started yet).
        Livewire::actingAs($user)->test(Dashboard::class)
            ->assertDontSee('Future Habit');
    }

    public function test_past_day_toggle_does_not_auto_check_in(): void
    {
        $user = $this->freshUser();
        $bit = Bit::create([
            'user_id' => $user->id, 'title' => 'Walk', 'icon_key' => 'walk',
            'goal_per_week' => 7, 'status' => 'active', 'start_date' => now()->subWeek()->toDateString(),
        ]);
        $system = $user->bits()->where('is_system', true)->first();
        $yesterday = now()->subDay()->format('Y-m-d');

        Livewire::actingAs($user)->test(Dashboard::class)
            ->set('selectedDateISO', $yesterday)
            ->call('toggle', $bit->id);

        $this->assertDatabaseHas('entries', ['bit_id' => $bit->id, 'date' => $yesterday, 'status' => 'done']);
        // The Daily Check-In must NOT be auto-stamped for a retroactive past day.
        $this->assertDatabaseMissing('entries', ['bit_id' => $system->id, 'date' => $yesterday]);
    }

    public function test_mark_all_done_today(): void
    {
        $user = $this->freshUser();
        foreach (['A', 'B'] as $t) {
            Bit::create(['user_id' => $user->id, 'title' => $t, 'icon_key' => 'star', 'goal_per_week' => 7, 'status' => 'active', 'start_date' => now()->subWeek()->toDateString()]);
        }
        $today = now()->format('Y-m-d');
        $bitIds = $user->bits()->where('is_system', false)->pluck('id');

        Livewire::actingAs($user)->test(Dashboard::class)->call('markAll');

        $this->assertSame(2, Entry::whereIn('bit_id', $bitIds)->whereDate('date', $today)->where('status', 'done')->count());
    }

    public function test_resume_paused_bit(): void
    {
        $user = $this->freshUser();
        $bit = Bit::create(['user_id' => $user->id, 'title' => 'Paused One', 'icon_key' => 'moon', 'goal_per_week' => 7, 'status' => 'paused', 'start_date' => now()->subWeek()->toDateString()]);

        Livewire::actingAs($user)->test(Dashboard::class)
            ->assertSee('Ready when you are')
            ->assertSee('Paused One')
            ->call('resume', $bit->id);

        $this->assertDatabaseHas('bits', ['id' => $bit->id, 'status' => 'active']);
    }

    public function test_overview_section_shows_on_current_week(): void
    {
        $user = $this->freshUser();
        Bit::create(['user_id' => $user->id, 'title' => 'C', 'icon_key' => 'run', 'goal_per_week' => 7, 'status' => 'active', 'start_date' => now()->subWeek()->toDateString()]);

        Livewire::actingAs($user)->test(Dashboard::class)
            ->assertSee('Overview')
            ->assertSee('On track');
    }

    public function test_cannot_navigate_past_current_week(): void
    {
        $user = $this->freshUser();

        Livewire::actingAs($user)->test(Dashboard::class)
            ->assertSet('weekOffset', 0)
            ->call('nextWeek')
            ->assertSet('weekOffset', 0) // clamped — no future weeks
            ->call('prevWeek')
            ->assertSet('weekOffset', -1)
            ->call('nextWeek')
            ->assertSet('weekOffset', 0);
    }
}
