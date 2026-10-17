<?php

namespace Tests\Feature;

use App\Livewire\Onboarding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_carousel_steps_and_finish(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => null]);

        Livewire::actingAs($user)->test(Onboarding::class)
            ->assertSet('step', 1)
            ->assertSee('Set meaningful goals')
            ->call('next')->assertSet('step', 2)->assertSee('Track your progress')
            ->call('next')->assertSet('step', 3)->assertSee('Stay motivated')
            ->call('finish')
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($user->fresh()->onboarding_completed_at);
    }

    public function test_completed_user_skips_onboarding(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);

        Livewire::actingAs($user)->test(Onboarding::class)
            ->assertRedirect(route('dashboard'));
    }
}
