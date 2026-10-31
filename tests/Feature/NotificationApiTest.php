<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\NotificationFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_matches_the_website_bell_and_can_be_marked_seen(): void
    {
        $user = User::factory()->create(['timezone' => 'UTC']);
        Sanctum::actingAs($user);
        NotificationFeed::push($user, ['title' => 'Time for Run', 'body' => 'Your reminder']);

        $this->getJson('/api/notifications')->assertOk()
            ->assertJson(['unread' => true])
            ->assertJsonPath('items.0.title', 'Time for Run');

        $this->postJson('/api/notifications/seen')->assertOk();
        $this->getJson('/api/notifications')->assertOk()->assertJson(['unread' => false]);
    }
}
