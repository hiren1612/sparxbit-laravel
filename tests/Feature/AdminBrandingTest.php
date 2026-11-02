<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminBrandingTest extends TestCase
{
    public function test_admin_login_shows_the_sparx_bit_logo_not_laravel(): void
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();

        $this->assertStringContainsString('Sparx Bit', $html);
        $this->assertStringContainsString('linear-gradient(135deg, #5b7ff0', $html); // gradient ✨ tile
        $this->assertStringContainsString('favicon.png', $html);
        $this->assertStringNotContainsString('<title>Login - Laravel', $html);
    }
}
