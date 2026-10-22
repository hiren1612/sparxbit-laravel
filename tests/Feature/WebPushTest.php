<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebPushTest extends TestCase
{
    use RefreshDatabase;

    private function freshUser(): User
    {
        $user = User::factory()->create();
        app(UserProvisioning::class)->seedNewUser($user);

        return $user->fresh();
    }

    private function sampleSub(string $endpoint = 'https://fcm.googleapis.com/fcm/send/abc123'): array
    {
        return [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => 'BPublicKeyBase64', 'auth' => 'AuthSecret'],
        ];
    }

    public function test_user_can_subscribe_and_unsubscribe_a_browser(): void
    {
        $user = $this->freshUser();

        $this->actingAs($user)
            ->postJson('/web-push/subscribe', $this->sampleSub())
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('web_push_subscriptions', ['user_id' => $user->id]);

        // Re-subscribing the same endpoint does not duplicate.
        $this->actingAs($user)->postJson('/web-push/subscribe', $this->sampleSub())->assertOk();
        $this->assertSame(1, $user->webPushSubscriptions()->count());

        $this->actingAs($user)
            ->postJson('/web-push/unsubscribe', ['endpoint' => $this->sampleSub()['endpoint']])
            ->assertOk();

        $this->assertSame(0, $user->webPushSubscriptions()->count());
    }

    public function test_subscribe_requires_auth(): void
    {
        $this->postJson('/web-push/subscribe', $this->sampleSub())->assertUnauthorized();
    }

    public function test_test_endpoint_reports_unconfigured_without_vapid_keys(): void
    {
        config(['services.webpush.public_key' => null, 'services.webpush.private_key' => null]);
        $user = $this->freshUser();

        $this->actingAs($user)
            ->postJson('/web-push/test', [])
            ->assertOk()
            ->assertJson(['configured' => false, 'sent' => 0]);
    }
}
