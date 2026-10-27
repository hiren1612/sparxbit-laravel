<?php

namespace Tests\Feature;

use App\Livewire\MonthlyReport;
use App\Models\Bit;
use App\Models\Entry;
use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class MonthlyReportTest extends TestCase
{
    use RefreshDatabase;

    private function freshUser(): User
    {
        $user = User::factory()->create(['week_starts_on' => 'monday', 'timezone' => 'UTC']);
        app(UserProvisioning::class)->seedNewUser($user);

        return $user->fresh();
    }

    public function test_report_renders_with_month_stats_and_bit_breakdown(): void
    {
        $user = $this->freshUser();
        $bit = Bit::create([
            'user_id' => $user->id, 'title' => 'Hydrate', 'icon_key' => 'droplet',
            'goal_per_week' => 7, 'status' => 'active',
            'start_date' => now()->startOfMonth()->toDateString(),
        ]);

        // Three completions this month.
        foreach ([0, 1, 2] as $i) {
            Entry::create([
                'bit_id' => $bit->id, 'user_id' => $user->id,
                'date' => now()->startOfMonth()->addDays($i)->toDateString(),
                'status' => 'done', 'source' => 'manual',
            ]);
        }

        Livewire::actingAs($user)->test(MonthlyReport::class)
            ->assertSee('Monthly Report')
            ->assertSee(now()->format('F Y'))
            ->assertSee('Hydrate')
            ->assertSee('Completions');
    }

    public function test_csv_export_downloads_the_month_summary(): void
    {
        $user = $this->freshUser();
        Bit::create([
            'user_id' => $user->id, 'title' => 'Read', 'icon_key' => 'book',
            'goal_per_week' => 3, 'status' => 'active',
            'start_date' => now()->startOfMonth()->toDateString(),
        ]);

        $this->actingAs($user);
        $filename = 'sparxbit-report-'.now()->format('Y-m').'.csv';

        Livewire::test(MonthlyReport::class)
            ->call('export')
            ->assertFileDownloaded($filename);

        // Verify the CSV content directly from the component's response.
        $response = (new MonthlyReport)->export();
        ob_start();
        $response->sendContent();
        $csv = ob_get_clean();
        $this->assertStringContainsString('Monthly Report', $csv);
        $this->assertStringContainsString('Read', $csv);
    }

    public function test_cannot_navigate_into_the_future(): void
    {
        $user = $this->freshUser();

        Livewire::actingAs($user)->test(MonthlyReport::class)
            ->assertSet('monthOffset', 0)
            ->call('nextMonth')
            ->assertSet('monthOffset', 0) // clamped
            ->call('prevMonth')
            ->assertSet('monthOffset', -1);
    }
}
