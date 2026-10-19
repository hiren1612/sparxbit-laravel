<?php

namespace Tests\Feature;

use App\Livewire\Notifications;
use App\Models\Bit;
use App\Models\Entry;
use App\Models\PushToken;
use App\Models\User;
use App\Services\NotificationFeed;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class ExtrasTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $attrs = []): User
    {
        $user = User::factory()->create($attrs);
        app(UserProvisioning::class)->seedNewUser($user);

        return $user->fresh();
    }

    public function test_legal_pages_are_public(): void
    {
        $this->get(route('terms'))->assertOk()->assertSee('Terms of Service');
        $this->get(route('privacy'))->assertOk()->assertSee('Privacy Policy');
        $this->get(route('licenses'))->assertOk()->assertSee('Licences');
        // Login shows the legal line.
        $this->get('/login')->assertSee('By continuing');
    }

    public function test_unread_dot_logic_and_mark_seen(): void
    {
        $user = $this->user();
        $bit = Bit::create(['user_id' => $user->id, 'title' => 'Walk', 'icon_key' => 'walk', 'goal_per_week' => 7, 'status' => 'active', 'start_date' => now()->subWeek()->toDateString()]);
        Entry::create(['bit_id' => $bit->id, 'user_id' => $user->id, 'date' => now()->toDateString(), 'status' => 'done', 'source' => 'manual']);

        // Activity today + never seen → unread.
        $this->assertTrue(NotificationFeed::hasUnread($user->fresh()));

        // Visiting the Notifications page marks seen.
        Livewire::actingAs($user)->test(Notifications::class);
        $this->assertNotNull($user->fresh()->notifications_seen_at);
        $this->assertFalse(NotificationFeed::hasUnread($user->fresh()));
    }

    public function test_push_token_registration(): void
    {
        $user = $this->user();
        Sanctum::actingAs($user);

        $this->postJson('/api/push-tokens', ['token' => 'ExponentPushToken[abc]', 'platform' => 'ios'])
            ->assertCreated();
        $this->assertDatabaseHas('push_tokens', ['user_id' => $user->id, 'token' => 'ExponentPushToken[abc]']);

        $this->deleteJson('/api/push-tokens', ['token' => 'ExponentPushToken[abc]'])->assertOk();
        $this->assertDatabaseMissing('push_tokens', ['token' => 'ExponentPushToken[abc]']);
    }

    public function test_reminder_also_sends_push(): void
    {
        Mail::fake();
        Http::fake(['exp.host/*' => Http::response(['data' => []], 200)]);

        $user = $this->user(['notifications_push_enabled' => true, 'timezone' => 'UTC']);
        $bit = Bit::create([
            'user_id' => $user->id, 'title' => 'Stretch', 'icon_key' => 'lotus',
            'goal_per_week' => 7, 'status' => 'active', 'start_date' => now()->subWeek()->toDateString(),
            'reminder_enabled' => true, 'reminder_time' => now()->format('H:i').':00', 'reminder_days' => [],
        ]);
        PushToken::create(['user_id' => $user->id, 'token' => 'ExponentPushToken[xyz]', 'platform' => 'android']);

        $this->artisan('bits:send-reminders')->assertSuccessful();

        Http::assertSent(fn ($req) => str_contains($req->url(), 'exp.host'));
    }
}
