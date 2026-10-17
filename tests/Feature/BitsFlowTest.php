<?php

namespace Tests\Feature;

use App\Livewire\Bits\Create;
use App\Livewire\Bits\Edit;
use App\Livewire\Bits\Index;
use App\Livewire\Bits\Show;
use App\Models\Bit;
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
