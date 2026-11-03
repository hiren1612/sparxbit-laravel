<?php

namespace Tests\Feature;

use App\Livewire\Profile;
use App\Models\Bit;
use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileActiveBitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_bits_whose_end_date_has_passed_are_not_counted_as_active(): void
    {
        $user = User::factory()->create(['timezone' => 'UTC', 'onboarding_completed_at' => now()]);
        app(UserProvisioning::class)->seedNewUser($user);
        $today = $user->localToday();
        $make = fn ($title, $end) => Bit::create([
            'user_id' => $user->id, 'title' => $title, 'icon_key' => 'run', 'goal_per_week' => 1, 'status' => 'active',
            'start_date' => $today->copy()->subDays(10)->format('Y-m-d'), 'end_date' => $end,
        ]);
        $make('Ended', $today->copy()->subDays(2)->format('Y-m-d'));
        $make('Ends today', $today->format('Y-m-d'));
        $make('Ongoing', null);

        Livewire::actingAs($user->fresh())->test(Profile::class)->assertViewHas('activeBits', 2);
    }
}
