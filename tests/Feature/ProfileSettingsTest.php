<?php

namespace Tests\Feature;

use App\Livewire\Categories;
use App\Livewire\Feedback;
use App\Livewire\Profile;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\DeleteAccount;
use App\Livewire\Settings\EditProfile;
use App\Livewire\Settings\WeekStart;
use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        $user = User::factory()->create(['name' => 'Hiren']);
        app(UserProvisioning::class)->seedNewUser($user);

        return $user->fresh();
    }

    public function test_profile_and_subpages_render(): void
    {
        $user = $this->user();
        foreach (['profile', 'profile.edit', 'profile.appearance', 'profile.weekstart', 'profile.notifications', 'profile.privacy', 'profile.email', 'profile.myad', 'profile.delete', 'refer', 'categories', 'feedback', 'about'] as $route) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }
    }

    public function test_edit_profile_saves(): void
    {
        $user = $this->user();
        Livewire::actingAs($user)->test(EditProfile::class)
            ->set('name', 'New Name')->set('bio', 'hi')->call('pickAvatar', '🦊')->call('save');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name', 'bio' => 'hi', 'avatar_url' => 'emoji:🦊']);
    }

    public function test_appearance_and_week_start(): void
    {
        $user = $this->user();
        Livewire::actingAs($user)->test(Appearance::class)->call('select', 'dark');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'theme' => 'dark']);

        Livewire::actingAs($user)->test(WeekStart::class)->call('select', 'sunday');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'week_starts_on' => 'sunday']);
    }

    public function test_categories_add_and_feedback(): void
    {
        $user = $this->user();
        Livewire::actingAs($user)->test(Categories::class)
            ->set('name', 'Finance')->set('color', '#2A5FCC')->call('add');
        $this->assertDatabaseHas('categories', ['user_id' => $user->id, 'name' => 'Finance']);

        Livewire::actingAs($user)->test(Feedback::class)
            ->set('type', 'feature')->set('message', 'Add streak freezes')->call('submit')
            ->assertSet('sent', true);
        $this->assertDatabaseHas('feedback', ['user_id' => $user->id, 'type' => 'feature']);
    }

    public function test_delete_account_requires_confirmation(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)->test(DeleteAccount::class)
            ->set('confirm', 'nope')->call('destroyAccount')->assertHasErrors('confirm');
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        Livewire::actingAs($user)->test(DeleteAccount::class)
            ->set('confirm', 'DELETE')->call('destroyAccount');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_sign_out(): void
    {
        $user = $this->user();
        Livewire::actingAs($user)->test(Profile::class)->call('logout');
        $this->assertGuest();
    }
}
