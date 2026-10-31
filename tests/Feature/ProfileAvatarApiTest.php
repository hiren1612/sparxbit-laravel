<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileAvatarApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploads_a_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $res = $this->post('/api/me/avatar', ['photo' => UploadedFile::fake()->image('me.jpg')], ['Accept' => 'application/json'])
            ->assertOk();

        $url = $res->json('avatarUrl');
        $this->assertStringContainsString('/storage/avatars/', $url);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $url));
    }

    public function test_rejects_non_images(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->post('/api/me/avatar', ['photo' => UploadedFile::fake()->create('x.pdf', 10)], ['Accept' => 'application/json'])
            ->assertStatus(422);
    }

    public function test_emoji_avatar_and_web_style_username_via_patch(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->patchJson('/api/me', ['avatarUrl' => 'emoji:🚀', 'username' => 'Hiren-B'])
            ->assertOk()->assertJson(['avatarUrl' => 'emoji:🚀', 'username' => 'Hiren-B']);
    }

    public function test_changes_email_like_the_website(): void
    {
        $other = User::factory()->create(['email' => 'taken@example.com']);
        Sanctum::actingAs(User::factory()->create());
        $this->patchJson('/api/me', ['email' => 'taken@example.com'])->assertStatus(422);
        $this->patchJson('/api/me', ['email' => 'new@example.com'])->assertOk()->assertJson(['email' => 'new@example.com']);
    }
}
