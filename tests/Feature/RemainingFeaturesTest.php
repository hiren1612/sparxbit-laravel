<?php

namespace Tests\Feature;

use App\Livewire\Settings\Language;
use App\Livewire\Settings\Sync;
use App\Mail\BitReminderMail;
use App\Models\Bit;
use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RemainingFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $attrs = []): User
    {
        $user = User::factory()->create($attrs);
        app(UserProvisioning::class)->seedNewUser($user);

        return $user->fresh();
    }

    public function test_new_pages_render(): void
    {
        $user = $this->user();
        foreach (['notifications.index', 'profile.language', 'profile.sync', 'help'] as $route) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }
    }

    public function test_web_registration_attributes_referral(): void
    {
        $referrer = $this->user();
        $referrer->update(['referral_code' => 'abc123xy']);

        session(['referral_code' => 'abc123xy']);
        Volt::test('pages.auth.register')
            ->set('name', 'Invitee')
            ->set('email', 'invitee@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register');

        $invitee = User::where('email', 'invitee@example.com')->first();
        $this->assertSame($referrer->id, $invitee->referred_by_user_id);
        $this->assertDatabaseHas('referrals', ['referrer_id' => $referrer->id, 'referee_id' => $invitee->id]);
    }

    public function test_language_and_sync_export(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)->test(Language::class)->call('select', 'es');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'language' => 'es']);

        Livewire::actingAs($user)->test(Sync::class)->call('export')->assertFileDownloaded();
    }

    public function test_reminder_command_emails_due_bits(): void
    {
        Mail::fake();
        $user = $this->user(['notifications_push_enabled' => true, 'timezone' => 'UTC']);
        Bit::create([
            'user_id' => $user->id, 'title' => 'Meditate', 'icon_key' => 'lotus',
            'goal_per_week' => 7, 'status' => 'active', 'start_date' => now()->subWeek()->toDateString(),
            'reminder_enabled' => true, 'reminder_time' => now()->format('H:i').':00', 'reminder_days' => [],
        ]);

        $this->artisan('bits:send-reminders')->assertSuccessful();

        Mail::assertSent(BitReminderMail::class, fn ($mail) => $mail->hasTo($user->email));
    }

    public function test_reminder_not_sent_when_already_done(): void
    {
        Mail::fake();
        $user = $this->user(['notifications_push_enabled' => true, 'timezone' => 'UTC']);
        $bit = Bit::create([
            'user_id' => $user->id, 'title' => 'Run', 'icon_key' => 'run',
            'goal_per_week' => 7, 'status' => 'active', 'start_date' => now()->subWeek()->toDateString(),
            'reminder_enabled' => true, 'reminder_time' => now()->format('H:i').':00', 'reminder_days' => [],
        ]);
        \App\Models\Entry::create(['bit_id' => $bit->id, 'user_id' => $user->id, 'date' => now()->toDateString(), 'status' => 'done', 'source' => 'manual']);

        $this->artisan('bits:send-reminders')->assertSuccessful();
        Mail::assertNothingSent();
    }
}
