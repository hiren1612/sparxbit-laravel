<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_use_sparx_design(): void
    {
        $this->get('/login')->assertOk()->assertSee('Welcome back')->assertSee('Sparx Bit');
        $this->get('/register')->assertOk()->assertSee('Create your account');
        $this->get('/forgot-password')->assertOk()->assertSee('Reset your password');
    }

    public function test_web_registration_provisions_account(): void
    {
        Volt::test('pages.auth.register')
            ->set('name', 'Hiren')
            ->set('email', 'hiren@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register');

        $user = User::where('email', 'hiren@example.com')->first();
        $this->assertNotNull($user);
        // Seeded: a Daily Check-In system bit, system categories, and a referral code.
        $this->assertTrue($user->bits()->where('is_system', true)->exists());
        $this->assertGreaterThanOrEqual(3, $user->categories()->count());
    }
}
