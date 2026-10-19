<?php

namespace Tests\Feature;

use App\Models\MagicLink;
use App\Models\User;
use App\Services\MagicLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;
use Tests\TestCase;

class MagicAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_offers_magic_and_social(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee('Continue with Magic Link')
            ->assertSee('Continue with Google')
            ->assertSee('Sign in with Apple');
    }

    public function test_requesting_a_magic_link_sends_mail_and_stores_token(): void
    {
        Mail::fake();

        Volt::test('pages.auth.login')
            ->set('email', 'newbie@example.com')
            ->call('sendMagicLink')
            ->assertSet('magicSent', true);

        $this->assertDatabaseHas('magic_links', ['email' => 'newbie@example.com']);
        Mail::assertSent(\App\Mail\MagicLinkMail::class);
        // The account is only created when the link is consumed.
        $this->assertDatabaseMissing('users', ['email' => 'newbie@example.com']);
    }

    public function test_magic_link_is_single_use_and_provisions_new_users(): void
    {
        $url = MagicLinkService::issue('fresh@example.com');
        $token = Str::afterLast($url, '/');

        $user = MagicLinkService::consume($token);
        $this->assertNotNull($user);
        $this->assertSame('fresh@example.com', $user->email);
        // New account is seeded (system check-in bit + categories).
        $this->assertTrue($user->bits()->where('is_system', true)->exists());

        // Second use is rejected.
        $this->assertNull(MagicLinkService::consume($token));
    }

    public function test_verify_route_logs_the_user_in(): void
    {
        $url = MagicLinkService::issue('link@example.com');
        $token = Str::afterLast($url, '/');

        $this->get(route('magic.verify', ['token' => $token]))
            ->assertRedirect(route('welcome'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'link@example.com']);
    }

    public function test_expired_or_bad_token_is_rejected(): void
    {
        $this->get(route('magic.verify', ['token' => 'totally-invalid']))
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_existing_user_signs_in_without_duplicate(): void
    {
        $existing = User::factory()->create(['email' => 'repeat@example.com']);
        $url = MagicLinkService::issue('repeat@example.com');
        $token = Str::afterLast($url, '/');

        $user = MagicLinkService::consume($token);
        $this->assertSame($existing->id, $user->id);
        $this->assertSame(1, User::where('email', 'repeat@example.com')->count());
    }
}
