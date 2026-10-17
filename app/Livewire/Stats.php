<?php

namespace App\Livewire;

use App\Models\Entry;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Stats extends Component
{
    public string $range = 'week';
    public ?string $expandedBitId = null;

    public function setRange(string $r): void
    {
        $this->range = $r;
        $this->expandedBitId = null;
    }

    public function toggleExpand(string $id): void
    {
        $this->expandedBitId = $this->expandedBitId === $id ? null : $id;
    }

    private function pickTitle(int $score, string $range): array
    {
        if ($range === 'week') {
            if ($score >= 85) {
                return ['Great week! 🌟', "You completed {$score}% of your Bits this week. Keep the momentum going."];
            }
            if ($score >= 60) {
                return ['Solid week', "You completed {$score}% of your Bits this week."];
            }
            if ($score > 0) {
                return ['A start is a start', "You completed {$score}% of your Bits this week — every bit counts."];
            }

            return ['No data yet', 'Track a Bit today to start seeing insights.'];
        }
        if ($range === 'month') {
            if ($score >= 80) {
                return ['Strong month 📈', "{$score}% completion this month — a solid run."];
            }
            if ($score > 0) {
                return ['Building rhythm', "{$score}% completion this month."];
            }

            return ['No data yet', 'Track a Bit today to start seeing insights.'];
        }
        if ($score >= 70) {
            return ['Building habits', "Your completion rate over the past year is {$score}%. Every Bit you complete counts."];
        }

        return ['Just getting started', "{$score}% completion over the past year. Small Bits compound."];
    }

    public function render()
    {
        $user = auth()->user();
        $sundayFirst = $user->week_starts_on === 'sunday';
        $weeks = $this->range === 'week' ? 1 : ($this->range === 'month' ? 5 : 52);
        $windowDays = $this->range === 'week' ? 7 : ($this->range === 'month' ? 35 : 364);
        $today = now()->startOfDay();
        $todayISO = $today->format('Y-m-d');
        $curWeekStart = $today->copy()->startOfWeek($sundayFirst ? Carbon::SUNDAY : Carbon::MONDAY);

        $bits = $user->bits()->where('is_system', false)->where('status', '!=', 'archived')->orderBy('created_at')->get();
        $systemBit = $user->bits()->where('is_system', true)->first();

        // All done entries we may need (widest window = 52 weeks back).
        $windowStartISO = $curWeekStart->copy()->subWeeks($weeks - 1)->format('Y-m-d');
        $entries = Entry::where('user_id', $user->id)->where('status', 'done')
            ->whereBetween('date', [$windowStartISO, $todayISO])->get();
        $doneCountOn = function ($bitId, $isos) use ($entries) {
            return $entries->filter(fn ($e) => $e->bit_id === $bitId && in_array($e->date->format('Y-m-d'), $isos, true))->count();
        };

        // Build week list (oldest -> newest).
        $weekList = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $ws = $curWeekStart->copy()->subWeeks($i);
            $dayISOs = collect(range(0, 6))->map(fn ($d) => $ws->copy()->addDays($d)->format('Y-m-d'))->all();
            $weekList[] = ['wsISO' => $ws->format('Y-m-d'), 'label' => $ws->day.' '.$ws->format('M'), 'dayISOs' => $dayISOs, 'endISO' => $dayISOs[6]];
        }

        // Per-week goal vs done + pooled totals; per-bit done/target.
        $rows = [];
        $perBit = [];
        foreach ($bits as $b) {
            $perBit[$b->id] = ['done' => 0, 'target' => 0];
        }
        $startOf = fn ($b) => $b->start_date?->format('Y-m-d');
        $endOf = fn ($b) => $b->end_date?->format('Y-m-d');

        foreach ($weekList as $wk) {
            $goal = 0;
            $done = 0;
            foreach ($bits as $b) {
                if ($startOf($b) && $startOf($b) > $wk['endISO']) {
                    continue;
                }
                if ($endOf($b) && $endOf($b) < $wk['wsISO']) {
                    continue;
                }
                $g = max(1, $b->goal_per_week);
                $d = $doneCountOn($b->id, $wk['dayISOs']);
                $goal += $g;
                $done += min($d, $g);
                $perBit[$b->id]['target'] += $g;
                $perBit[$b->id]['done'] += min($d, $g);
            }
            if ($goal > 0) {
                $rows[] = ['label' => $wk['label'], 'goal' => $goal, 'done' => $done, 'pct' => (int) round($done / $goal * 100), 'met' => $done >= $goal];
            }
        }
        $totalGoal = array_sum(array_column($rows, 'goal'));
        $totalDone = array_sum(array_column($rows, 'done'));
        $score = $totalGoal ? (int) round($totalDone / $totalGoal * 100) : 0;

        [$title, $sub] = $this->pickTitle($score, $this->range);
        $circ = 2 * M_PI * 40;

        // Per-bit rings (sorted by rate desc).
        $liveBits = $bits->map(function ($b) use ($perBit) {
            $target = max(1, $perBit[$b->id]['target']);
            $done = $perBit[$b->id]['done'];

            return [
                'id' => $b->id, 'emoji' => Dashboard::emoji($b->icon_key), 'name' => $b->title,
                'done' => min($done, $target), 'target' => $target,
                'rate' => min($done, $target) / $target,
                'pct' => (int) round(min($done, $target) / $target * 100),
            ];
        })->filter(fn ($b) => $perBit[$b['id']]['target'] > 0)
            ->sortByDesc('rate')->values();

        $strongest = $liveBits->first();
        $weakest = $liveBits->last();

        // Daily check-in stats.
        $checkIns = $systemBit
            ? Entry::where('bit_id', $systemBit->id)->where('status', 'done')->orderBy('date')->get()
            : collect();
        $checkedSet = $checkIns->map(fn ($e) => $e->date->format('Y-m-d'))->all();
        $sinceISO = $today->copy()->subDays($windowDays - 1)->format('Y-m-d');
        $inWindow = collect($checkedSet)->filter(fn ($d) => $d >= $sinceISO && $d <= $todayISO)->count();
        $streak = 0;
        $offset = in_array($todayISO, $checkedSet, true) ? 0 : 1;
        for ($i = $offset; $i < 365; $i++) {
            if (in_array($today->copy()->subDays($i)->format('Y-m-d'), $checkedSet, true)) {
                $streak++;
            } else {
                break;
            }
        }

        // Check-in dots / heatmap + activity grid cells.
        $checkinCells = $this->buildCells($weeks, $sundayFirst, $today, fn ($iso) => in_array($iso, $checkedSet, true) ? 1 : 0);
        $activeBitsCount = max(1, $bits->where('status', 'active')->count());
        $doneByDate = [];
        foreach ($entries as $e) {
            $iso = $e->date->format('Y-m-d');
            $doneByDate[$iso] = ($doneByDate[$iso] ?? 0) + 1;
        }
        $activityCells = $this->buildCells($weeks, $sundayFirst, $today, function ($iso) use ($doneByDate, $activeBitsCount) {
            $count = $doneByDate[$iso] ?? 0;
            if ($count === 0) {
                return 0;
            }
            $ratio = $count / $activeBitsCount;

            return $ratio >= 1 ? 4 : ($ratio >= 0.66 ? 3 : ($ratio >= 0.33 ? 2 : 1));
        });

        return view('livewire.stats', [
            'score' => $score, 'title' => $title, 'sub' => $sub,
            'circ' => $circ, 'dashoffset' => $circ - ($score / 100) * $circ,
            'delta' => $this->range === 'week' ? 'This week' : ($this->range === 'month' ? 'This month' : 'Past year'),
            'rangeLabel' => $this->range === 'week' ? 'this week' : ($this->range === 'month' ? 'last 5 weeks' : 'the past year'),
            'barRows' => $rows, 'totalGoal' => $totalGoal, 'totalDone' => $totalDone, 'achieved' => $score,
            'liveBits' => $liveBits, 'strongest' => $strongest, 'weakest' => $weakest,
            'checkin' => ['streak' => $streak, 'total' => count($checkedSet), 'inWindow' => $inWindow, 'windowDays' => $windowDays],
            'checkinCells' => $checkinCells, 'activityCells' => $activityCells,
            'weeks' => $weeks,
        ]);
    }

    // Build a 7×weeks grid (or 7-cell strip when weeks==1) of {iso, label, dow, day, level, future}.
    private function buildCells(int $weeks, bool $sundayFirst, Carbon $today, callable $level): array
    {
        $todayISO = $today->format('Y-m-d');
        if ($weeks <= 1) {
            $cells = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = $today->copy()->subDays($i);
                $iso = $d->format('Y-m-d');
                $cells[] = ['iso' => $iso, 'dow' => strtoupper(substr($d->format('D'), 0, 1)), 'day' => $d->day, 'level' => $level($iso), 'future' => false, 'isToday' => $iso === $todayISO];
            }

            return $cells;
        }
        $dow = (int) $today->format('w');
        $startOffset = $sundayFirst ? $dow : ($dow === 0 ? 6 : $dow - 1);
        $weekStart = $today->copy()->subDays($startOffset);
        $totalDays = $weeks * 7;
        $firstDay = $weekStart->copy()->subDays($totalDays - 7);
        $cells = [];
        for ($i = 0; $i < $totalDays; $i++) {
            $d = $firstDay->copy()->addDays($i);
            $iso = $d->format('Y-m-d');
            $future = $iso > $todayISO;
            $cells[] = ['iso' => $iso, 'level' => $future ? 0 : $level($iso), 'future' => $future];
        }

        return $cells;
    }
}
