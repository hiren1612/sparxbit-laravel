<?php

namespace Tests\Feature;

use App\Livewire\Bits\Create;
use App\Livewire\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WebTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_frontend(): void
    {
        $user = User::factory()->create();
        $bit = $user->bits()->create([
            'title' => 'Walk', 'start_date' => now()->format('Y-m-d'),
            'goal_per_week' => 5, 'reminder_days' => [],
        ]);

        $this->actingAs($user);

        // Dashboard renders with the bit
        $this->get('/dashboard')->assertOk()->assertSee('Walk');

        // Toggle marks today done, then removes it
        Livewire::test(Dashboard::class)->call('toggle', $bit->id);
        $this->assertDatabaseHas('entries', [
            'bit_id' => $bit->id, 'date' => now()->format('Y-m-d'), 'status' => 'done',
        ]);
        Livewire::test(Dashboard::class)->call('toggle', $bit->id);
        $this->assertDatabaseMissing('entries', [
            'bit_id' => $bit->id, 'date' => now()->format('Y-m-d'),
        ]);

        // Bits index
        $this->get('/bits')->assertOk()->assertSee('Walk');

        // Create a bit via the Livewire form
        Livewire::test(Create::class)
            ->set('title', 'Read 20 pages')
            ->set('goalPerWeek', 4)
            ->set('startDate', now()->format('Y-m-d'))
            ->call('save')
            ->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('bits', ['title' => 'Read 20 pages', 'user_id' => $user->id]);

        // Guests are redirected to login
        auth()->logout();
        $this->get('/dashboard')->assertRedirect(route('login'));
    }
}
