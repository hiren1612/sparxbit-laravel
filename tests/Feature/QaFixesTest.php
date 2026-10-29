<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Livewire\Settings\MyAd;
use App\Models\Bit;
use App\Models\Entry;
use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

// Regression tests for the QA report fixes (F1, F2, F5).
class QaFixesTest extends TestCase
{
    use RefreshDatabase;

    private function freshUser(array $attrs = []): User
    {
        $user = User::factory()->create($attrs);
        app(UserProvisioning::class)->seedNewUser($user);

        return $user->fresh();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // F1 — reminders are on by default for new users.
    public function test_new_users_have_reminders_on_by_default(): void
    {
        $this->assertTrue($this->freshUser()->notifications_push_enabled);
    }

    // F1 — setting a reminder on a Bit switches reminder delivery on.
    public function test_enabling_a_bit_reminder_turns_on_delivery(): void
    {
        $user = $this->freshUser();
        $user->forceFill(['notifications_push_enabled' => false])->save();

        Bit::create([
            'user_id' => $user->id, 'title' => 'Stretch', 'icon_key' => 'run',
            'goal_per_week' => 7, 'status' => 'active', 'start_date' => now()->toDateString(),
            'reminder_enabled' => true, 'reminder_time' => '08:00:00', 'reminder_days' => [],
        ]);

        $this->assertTrue($user->fresh()->notifications_push_enabled);
    }

    // F2 — "today" is the user's local date, not the server's UTC date.
    public function test_daily_checkin_uses_the_users_local_date(): void
    {
        // 20:00 UTC on 28 Oct is 01:30 on 29 Oct in India.
        Carbon::setTestNow(Carbon::parse('2026-10-28 20:00:00', 'UTC'));
        $user = $this->freshUser(['timezone' => 'Asia/Kolkata']);
        $system = $user->bits()->where('is_system', true)->first();

        Livewire::actingAs($user)->test(Dashboard::class)
            ->assertSet('selectedDateISO', '2026-10-29');

        $this->assertTrue(Entry::where('bit_id', $system->id)->whereDate('date', '2026-10-29')->exists());
        $this->assertFalse(Entry::where('bit_id', $system->id)->whereDate('date', '2026-10-28')->exists());
    }

    public function test_local_today_differs_from_utc_near_midnight(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-28 20:00:00', 'UTC'));
        $user = $this->freshUser(['timezone' => 'Asia/Kolkata']);

        $this->assertSame('2026-10-29', $user->localToday()->format('Y-m-d'));
        $this->assertSame('2026-10-28', now()->format('Y-m-d'));
    }

    // F5 — the API rejects non-http(s) ad links (blocks javascript: XSS).
    public function test_api_rejects_javascript_ad_link(): void
    {
        $user = $this->freshUser();
        Sanctum::actingAs($user);

        $this->patchJson('/api/me', ['adLink' => 'javascript:alert(1)'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('adLink');

        $this->patchJson('/api/me', ['adLink' => 'https://example.com'])->assertOk();
        $this->assertSame('https://example.com', $user->fresh()->ad_link);
    }

    // F5 — the web form rejects it too.
    public function test_web_my_ad_rejects_javascript_link(): void
    {
        $user = $this->freshUser();

        Livewire::actingAs($user)->test(MyAd::class)
            ->set('link', 'javascript:alert(1)')
            ->call('save')
            ->assertHasErrors('link');
    }
}
