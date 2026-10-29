<?php

namespace Tests\Feature;

use App\Livewire\About;
use App\Models\Setting;
use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppContentTest extends TestCase
{
    use RefreshDatabase;

    private function freshUser(): User
    {
        $user = User::factory()->create();
        app(UserProvisioning::class)->seedNewUser($user);

        return $user->fresh();
    }

    public function test_about_text_is_editable_from_the_backend(): void
    {
        $user = $this->freshUser();

        // Default (seeded) tagline shows.
        Livewire::actingAs($user)->test(About::class)
            ->assertSee('Small habits, big change');

        // Admin edits the settings (model save, as Filament does) → About reflects it.
        foreach (['about.tagline' => 'CUSTOM-TAGLINE-MARKER', 'about.version' => '2.5.0', 'about.footer' => 'CUSTOM-FOOTER-MARKER'] as $k => $v) {
            $s = Setting::where('key', $k)->first();
            $s->value = $v;
            $s->save();
        }

        Livewire::actingAs($user)->test(About::class)
            ->assertSee('CUSTOM-TAGLINE-MARKER')
            ->assertSee('2.5.0')
            ->assertSee('CUSTOM-FOOTER-MARKER')
            ->assertDontSee('Small habits, big change');
    }

    public function test_setting_get_falls_back_to_default(): void
    {
        $this->assertSame('fallback', Setting::get('does.not.exist', 'fallback'));
    }
}
