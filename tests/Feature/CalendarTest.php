<?php

namespace Tests\Feature;

use App\Livewire\Calendar;
use App\Models\Bit;
use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    private function userWithBit(): array
    {
        $user = User::factory()->create();
        app(UserProvisioning::class)->seedNewUser($user);
        $user = $user->fresh();
        $bit = Bit::create([
            'user_id' => $user->id, 'title' => 'Hydrate', 'icon_key' => 'droplet',
            'goal_per_week' => 7, 'status' => 'active', 'start_date' => now()->subMonths(2)->toDateString(),
        ]);

        return [$user, $bit];
    }

    public function test_views_render(): void
    {
        [$user, $bit] = $this->userWithBit();

        $c = Livewire::actingAs($user)->test(Calendar::class)
            ->assertSee('Hydrate')->assertSee('goals met');

        $c->call('setView', 'monthly')->assertSee('Hydrate');
        $c->call('setView', 'yearly')->assertSee('Hydrate');
    }

    public function test_empty_state_prompts_first_habit_when_no_bits(): void
    {
        $user = User::factory()->create();
        app(UserProvisioning::class)->seedNewUser($user); // only the system check-in
        $user = $user->fresh();
        $user->bits()->where('is_system', false)->delete();

        Livewire::actingAs($user)->test(Calendar::class)
            ->assertSee('No bits yet')
            ->assertSee('Create your first habit');
    }

    public function test_toggle_and_period_shift(): void
    {
        [$user, $bit] = $this->userWithBit();
        $today = now()->format('Y-m-d');

        Livewire::actingAs($user)->test(Calendar::class)
            ->call('toggle', $bit->id, $today);
        $this->assertDatabaseHas('entries', ['bit_id' => $bit->id, 'date' => $today, 'status' => 'done']);

        Livewire::actingAs($user)->test(Calendar::class)
            ->assertSet('view', 'weekly')
            ->call('shiftPeriod', -1)
            ->call('goToday')
            ->assertSet('anchor', $today);
    }
}
