<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialUser;
use Mockery;
use Tests\TestCase;

class SocialAppAuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogle(?string $email = 'new.person@gmail.com', bool $fail = false): void
    {
        $driver = Mockery::mock();
        $driver->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));
        if ($fail) {
            $driver->shouldReceive('user')->andThrow(new \RuntimeException('denied'));
        } else {
            $driver->shouldReceive('user')->andReturn((new SocialUser)->map(['email' => $email, 'name' => 'New Person']));
        }
        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);
    }

    public function test_app_flow_returns_a_one_time_token_the_app_can_sign_in_with(): void
    {
        $this->fakeGoogle();

        $start = $this->get('/auth/google/app');
        $start->assertRedirect('https://accounts.google.com/o/oauth2/auth');
        $start->assertCookie('sparx_app_auth');

        $cb = $this->withCookie('sparx_app_auth', '1')->get('/auth/google/callback');
        $location = $cb->headers->get('Location');
        $this->assertStringStartsWith('sparxbit://magic?token=', $location);
        $this->assertGuest(); // no web session for the app flow

        // New account is set up exactly like a website signup.
        $user = User::where('email', 'new.person@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->bits()->where('is_system', true)->exists());

        $token = substr($location, strlen('sparxbit://magic?token='));
        $this->postJson('/api/auth/magic/verify', ['token' => $token])
            ->assertOk()->assertJsonStructure(['accessToken', 'user' => ['email']]);
        // Single use.
        $this->postJson('/api/auth/magic/verify', ['token' => $token])->assertUnauthorized();
    }

    public function test_app_flow_reports_errors_back_to_the_app(): void
    {
        $this->fakeGoogle(fail: true);
        $cb = $this->withCookie('sparx_app_auth', '1')->get('/auth/google/callback');
        $this->assertStringStartsWith('sparxbit://magic?error=', $cb->headers->get('Location'));
    }

    public function test_website_flow_is_unchanged(): void
    {
        $this->fakeGoogle();
        $this->get('/auth/google/callback')->assertRedirect(route('welcome'));
        $this->assertAuthenticated();
    }
}
