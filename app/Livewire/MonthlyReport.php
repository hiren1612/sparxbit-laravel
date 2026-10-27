<?php

namespace App\Livewire;

use App\Models\Entry;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class MonthlyReport extends Component
{
    public int $monthOffset = 0; // 0 = this month, -1 = last month, ...

    public function prevMonth(): void
    {
        $this->monthOffset--;
    }

    public function nextMonth(): void
    {
        if ($this->monthOffset < 0) {
            $this->monthOffset++;
        }
    }

    // Compute the report data for the selected month.
    private function build(): array
    {
        $user = auth()->user();
        $sundayFirst = $user->week_starts_on === 'sunday';
        $weekStartDow = $sundayFirst ? Carbon::SUNDAY : Carbon::MONDAY;

        $monthStart = now()->startOfMonth()->addMonths($this->monthOffset)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth()->startOfDay();
        $todayISO = now()->format('Y-m-d');

        $monthDays = [];
        for ($d = $monthStart->copy(); $d <= $monthEnd; $d->addDay()) {
            $monthDays[] = $d->format('Y-m-d');
        }
        $daysInMonth = count($monthDays);

        $bits = $user->bits()->where('is_system', false)->where('status', '!=', 'archived')
            ->orderBy('created_at')->get();
        $systemBit = $user->bits()->where('is_system', true)->first();

        $entries = Entry::where('user_id', $user->id)->where('status', 'done')
            ->whereBetween('date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])->get();

        // Week-starts overlapping the month (respecting week-start preference).
        $weekStarts = [];
        $cursor = $monthStart->copy()->startOfWeek($weekStartDow);
        while ($cursor <= $monthEnd) {
            $weekStarts[] = $cursor->copy();
            $cursor->addWeek();
        }

        $startOf = fn ($b) => $b->start_date?->format('Y-m-d');
        $endOf = fn ($b) => $b->end_date?->format('Y-m-d');

        $rows = [];
        $totalTarget = 0;
        $totalDone = 0;

        foreach ($bits as $b) {
            $target = 0;
            $done = 0;
            foreach ($weekStarts as $ws) {
                $we = $ws->copy()->addDays(6);
                if ($startOf($b) && $startOf($b) > $we->format('Y-m-d')) {
                    continue;
                }
                if ($endOf($b) && $endOf($b) < $ws->format('Y-m-d')) {
                    continue;
                }
                $g = max(1, $b->goal_per_week);
                // Days of this week that fall inside the month.
                $weekDays = collect(range(0, 6))
                    ->map(fn ($i) => $ws->copy()->addDays($i)->format('Y-m-d'))
                    ->filter(fn ($iso) => in_array($iso, $monthDays, true))->all();
                $d = $entries->filter(fn ($e) => $e->bit_id === $b->id && in_array($e->date->format('Y-m-d'), $weekDays, true))->count();
                $target += $g;
                $done += min($d, $g);
            }
            if ($target === 0) {
                continue; // bit wasn't active this month
            }
            $totalTarget += $target;
            $totalDone += $done;
            $rows[] = [
                'id' => $b->id,
                'emoji' => Dashboard::emoji($b->icon_key),
                'name' => $b->title,
                'goal' => max(1, $b->goal_per_week),
                'done' => $done,
                'target' => $target,
                'pct' => (int) round(min($done, $target) / $target * 100),
            ];
        }
        usort($rows, fn ($a, $b) => $b['pct'] <=> $a['pct']);

        $score = $totalTarget > 0 ? (int) round($totalDone / $totalTarget * 100) : 0;

        // Check-in days (opened the app) this month.
        $checkinDays = $systemBit
            ? $entries->where('bit_id', $systemBit->id)->map(fn ($e) => $e->date->format('Y-m-d'))->unique()->values()->all()
            : [];

        // Days with any real completion.
        $activeDays = $entries->whereIn('bit_id', $bits->pluck('id'))
            ->map(fn ($e) => $e->date->format('Y-m-d'))->unique()->values()->all();

        $totalCompletions = $entries->whereIn('bit_id', $bits->pluck('id'))->count();

        // Calendar grid cells (lead blanks + each day with activity flag).
        $firstDow = (int) $monthStart->copy()->dayOfWeek; // 0=Sun..6=Sat
        $lead = $sundayFirst ? $firstDow : ($firstDow === 0 ? 6 : $firstDow - 1);
        $cells = [];
        for ($i = 0; $i < $lead; $i++) {
            $cells[] = null;
        }
        foreach ($monthDays as $iso) {
            $cells[] = [
                'day' => (int) substr($iso, 8, 2),
                'active' => in_array($iso, $activeDays, true),
                'checkin' => in_array($iso, $checkinDays, true),
                'today' => $iso === $todayISO,
            ];
        }

        return [
            'monthStart' => $monthStart,
            'monthLabel' => $monthStart->format('F Y'),
            'daysInMonth' => $daysInMonth,
            'rows' => $rows,
            'score' => $score,
            'checkinCount' => count($checkinDays),
            'activeCount' => count($activeDays),
            'totalCompletions' => $totalCompletions,
            'dayLabels' => $sundayFirst ? ['S', 'M', 'T', 'W', 'T', 'F', 'S'] : ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
            'cells' => $cells,
            'canNext' => $this->monthOffset < 0,
        ];
    }

    // Stream the selected month's report as a CSV download.
    public function export()
    {
        $data = $this->build();
        $rows = [];
        $rows[] = ['Sparx Bit — Monthly Report'];
        $rows[] = ['Month', $data['monthLabel']];
        $rows[] = ['Overall completion', $data['score'].'%'];
        $rows[] = ['Total completions', $data['totalCompletions']];
        $rows[] = ['Check-in days', $data['checkinCount'].' of '.$data['daysInMonth']];
        $rows[] = ['Active days', $data['activeCount'].' of '.$data['daysInMonth']];
        $rows[] = [];
        $rows[] = ['Bit', 'Goal / week', 'Completed', 'Target', 'Completion %'];
        foreach ($data['rows'] as $r) {
            $rows[] = [$r['name'], $r['goal'], $r['done'], $r['target'], $r['pct'].'%'];
        }

        $filename = 'sparxbit-report-'.$data['monthStart']->format('Y-m').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $line) {
                fputcsv($out, $line);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        return view('livewire.monthly-report', $this->build());
    }
}
