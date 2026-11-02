<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_shows_the_sparx_bit_logo_not_laravel(): void
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();

        $this->assertStringContainsString('Sparx Bit', $html);
        $this->assertStringContainsString('linear-gradient(135deg, #5b7ff0', $html); // gradient ✨ tile
        $this->assertStringContainsString('favicon.png', $html);
        $this->assertStringNotContainsString('<title>Login - Laravel', $html);
    }

    public function test_admin_sidebar_has_collapsible_groups_and_a_collapse_button(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();

        foreach (['People', 'Habits', 'Content', 'Users', 'Bits', 'App content'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
        $this->assertStringContainsString('collapsedGroups', $html); // groups open/close
        $this->assertStringContainsString('isOpen', $html);          // sidebar collapse toggle
        $this->assertStringNotContainsString('filament-info-widget', $html);
    }
}
