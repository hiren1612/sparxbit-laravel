<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Bit;
use App\Models\User;
use App\Services\AdFeed;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdFeedTest extends TestCase
{
    use RefreshDatabase;

    private function userWithBits(int $bits = 0, array $attrs = []): User
    {
        $user = User::factory()->create($attrs);
        app(UserProvisioning::class)->seedNewUser($user);
        for ($i = 0; $i < $bits; $i++) {
            Bit::create(['user_id' => $user->id, 'title' => "Bit $i", 'icon_key' => 'star', 'goal_per_week' => 5, 'status' => 'active', 'start_date' => now()->subWeek()->toDateString()]);
        }

        return $user->fresh();
    }

    public function test_ad_shows_only_when_everyone_qualifies(): void
    {
        // Advertiser with an ad set.
        $advertiser = $this->userWithBits(0, ['business_name' => "Joe's Coffee", 'ad_tagline' => 'Best brew in town', 'ad_link' => 'https://joes.test']);
        // A referred, active user (≥3 bits) unlocks the advertiser's ad.
        $this->userWithBits(3, ['referred_by_user_id' => $advertiser->id]);

        // Viewer with ≥3 bits sees the ad.
        $viewer = $this->userWithBits(3);
        Livewire::actingAs($viewer)->test(Dashboard::class)
            ->assertSee("Joe's Coffee")
            ->assertSee('Sponsored');

        // Viewer with too few bits does NOT see it.
        $newbie = $this->userWithBits(1);
        $this->assertNull(AdFeed::pickOne($newbie));
    }

    public function test_ad_hidden_without_an_active_referral(): void
    {
        $advertiser = $this->userWithBits(0, ['business_name' => 'No Refs Co', 'ad_link' => 'https://norefs.test']);
        // Referred user but NOT active (0 bits) → advertiser not eligible.
        $this->userWithBits(0, ['referred_by_user_id' => $advertiser->id]);

        $viewer = $this->userWithBits(3);
        $this->assertNull(AdFeed::pickOne($viewer));
        Livewire::actingAs($viewer)->test(Dashboard::class)->assertDontSee('No Refs Co');
    }
}
