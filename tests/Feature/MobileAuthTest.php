<?php

namespace Tests\Feature;

use App\Mail\MagicLinkMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class MobileAuthTest extends TestCase
{
    use RefreshDatabase;

    private function magicTokenFromMail(): string
    {
        $url = null;
        Mail::assertSent(MagicLinkMail::class, function ($mail) use (&$url) {
            $url = $mail->url;

            return true;
        });

        return basename(parse_url($url, PHP_URL_PATH));
    }

    public function test_magic_link_request_emails_an_app_link(): void
    {
        Mail::fake();

        $this->postJson('/api/auth/magic/request', ['email' => 'New@Example.com'])
            ->assertOk()->assertJson(['ok' => true]);

        Mail::assertSent(MagicLinkMail::class, fn ($m) => $m->hasTo('new@example.com')
            && str_contains($m->url, '/auth/magic/app/'));
    }

    public function test_magic_link_bounce_page_opens_the_app(): void
    {
        Mail::fake();
        $this->postJson('/api/auth/magic/request', ['email' => 'a@example.com']);
        $token = $this->magicTokenFromMail();

        $this->get('/auth/magic/app/'.$token)
            ->assertOk()
            ->assertSee('sparxbit://magic?token='.$token, false);
    }

    public function test_magic_link_verify_signs_in_and_is_single_use(): void
    {
        Mail::fake();
        $this->postJson('/api/auth/magic/request', ['email' => 'fresh@example.com']);
        $token = $this->magicTokenFromMail();

        $this->postJson('/api/auth/magic/verify', ['token' => $token])
            ->assertOk()
            ->assertJsonStructure(['user', 'accessToken'])
            ->assertJsonPath('user.email', 'fresh@example.com');

        $this->assertDatabaseHas('users', ['email' => 'fresh@example.com']);

        // Second use is rejected.
        $this->postJson('/api/auth/magic/verify', ['token' => $token])->assertStatus(401);
    }

    public function test_google_sign_in_accepts_our_client_and_rejects_others(): void
    {
        config(['services.google.client_id' => 'web-client', 'services.google.mobile_client_ids' => ['ios-client']]);

        Http::fake([
            'oauth2.googleapis.com/*' => Http::sequence()
                ->push(['iss' => 'https://accounts.google.com', 'aud' => 'ios-client', 'email' => 'g@example.com', 'email_verified' => 'true', 'name' => 'Gina'])
                ->push(['iss' => 'https://accounts.google.com', 'aud' => 'someone-elses-app', 'email' => 'x@example.com', 'email_verified' => 'true']),
        ]);

        $this->postJson('/api/auth/social', ['provider' => 'google', 'token' => 'good-id-token'])
            ->assertOk()
            ->assertJsonPath('user.email', 'g@example.com');
        $this->assertSame('Gina', User::where('email', 'g@example.com')->value('name'));

        $this->postJson('/api/auth/social', ['provider' => 'google', 'token' => 'foreign-id-token'])
            ->assertStatus(401);
        $this->assertDatabaseMissing('users', ['email' => 'x@example.com']);
    }

    public function test_apple_sign_in_uses_verified_identity_token(): void
    {
        $existing = User::factory()->create(['email' => 'apple@example.com']);

        $socialUser = (new SocialiteUser)->map(['id' => 'apple-123', 'email' => 'apple@example.com', 'name' => null]);
        $driver = Mockery::mock();
        $driver->shouldReceive('userByIdentityToken')->with('id-token', 'n0nce')->once()->andReturn($socialUser);
        Socialite::shouldReceive('driver')->with('apple')->andReturn($driver);

        $this->postJson('/api/auth/social', ['provider' => 'apple', 'token' => 'id-token', 'nonce' => 'n0nce'])
            ->assertOk()
            ->assertJsonPath('user.email', 'apple@example.com');

        $this->assertSame(1, User::where('email', 'apple@example.com')->count()); // reused, not duplicated
        $this->assertSame($existing->id, User::where('email', 'apple@example.com')->value('id'));
    }

    public function test_invalid_apple_token_is_rejected(): void
    {
        $driver = Mockery::mock();
        $driver->shouldReceive('userByIdentityToken')->andThrow(new \RuntimeException('bad signature'));
        Socialite::shouldReceive('driver')->with('apple')->andReturn($driver);

        $this->postJson('/api/auth/social', ['provider' => 'apple', 'token' => 'forged'])
            ->assertStatus(401);
    }
}
