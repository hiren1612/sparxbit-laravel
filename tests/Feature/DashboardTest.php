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
            ->assertSee('Good morning')
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
