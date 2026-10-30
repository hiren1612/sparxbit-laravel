<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AppContentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Setting::flushCache(); // the static cache outlives the rolled-back row
        parent::tearDown();
    }

    public function test_returns_admin_edited_about_copy(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $s = Setting::firstOrNew(['key' => 'about.tagline']);
        $s->value = 'Edited in admin';
        $s->save();

        $this->getJson('/api/app-content')->assertOk()->assertJson(['aboutTagline' => 'Edited in admin']);
    }

    public function test_requires_auth(): void
    {
        $this->getJson('/api/app-content')->assertUnauthorized();
    }
}
