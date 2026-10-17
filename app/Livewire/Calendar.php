<?php

namespace App\Livewire;

use App\Models\Entry;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Calendar extends Component
{
    public string $view = 'weekly';
    public string $anchor = '';

    private const PALETTE = [
        ['filled' => '#A8E6CF', 'text' => '#2A7050'],
        ['filled' => '#FFB7B7', 'text' => '#C53760'],
        ['filled' => '#B6D6F4', 'text' => '#2A5FCC'],
        ['filled' => '#E0B8E8', 'text' => '#7A3FB0'],
        ['filled' => '#7AB6F0', 'text' => '#1E5AA8'],
        ['filled' => '#FFB37A', 'text' => '#B85A10'],
        ['filled' => '#9CE0DC', 'text' => '#1A7575'],
        ['filled' => '#FFD27A', 'text' => '#8A6510'],
    ];

    private const NEUTRAL_FADED = '#EEF1F5';
    private const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    private const MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    public function mount(): void
    {
        $this->anchor = now()->format('Y-m-d');
    }

    public function setView(string $v): void
    {
        $this->view = $v;
    }

    public function goToday(): void
    {
        $this->anchor = now()->format('Y-m-d');
    }

    public function shiftPeriod(int $dir): void
    {
        $a = Carbon::parse($this->anchor);
        $a = match ($this->view) {
            'weekly' => $a->addWeeks($dir),
            'monthly' => $a->addMonthsNoOverflow($dir),
            default => $a->addYears($dir),
        };
        $this->anchor = $a->format('Y-m-d');
    }

    // Tap a day cell — toggle done <-> cleared for that bit/day.
    public function toggle(string $bitId, string $iso): void
    {
        $bit = auth()->user()->bits()->find($bitId);
        if (! $bit || $bit->is_system) {
            return;
        }
        if ($iso > now()->format('Y-m-d')) {
            return;
        }
        $entry = Entry::where('bit_id', $bitId)->whereDate('date', $iso)->first();
        if ($entry && $entry->status === 'done') {
            $entry->delete();
        } else {
            Entry::updateOrCreate(
                ['bit_id' => $bitId, 'date' => $iso],
                ['user_id' => $bit->user_id, 'status' => 'done', 'source' => 'manual', 'completed_at' => now()],
            );
        }
    }

    private function dayOutOfRange($bit, string $iso): bool
    {
        $start = $bit->start_date?->format('Y-m-d');
        $end = $bit->end_date?->format('Y-m-d');

        return ($start && $iso < $start) || ($end && $iso > $end);
    }

    private function rangeCompletionPct($bit, int $doneCount, string $rangeStart, string $rangeEnd): int
    {
        $start = $bit->start_date?->format('Y-m-d');
        $end = $bit->end_date?->format('Y-m-d');
        $effStart = ($start && $start > $rangeStart) ? $start : $rangeStart;
        $effEnd = ($end && $end < $rangeEnd) ? $end : $rangeEnd;
        if ($effStart > $effEnd) {
            return 0;
        }
        $activeDays = Carbon::parse($effStart)->diffInDays(Carbon::parse($effEnd)) + 1;
        $goal = max(1, min(7, $bit->goal_per_week));
        $expected = $activeDays * ($goal / 7);
        if ($expected <= 0) {
            return 0;
        }

        return (int) min(100, round(min($doneCount, $expected) / $expected * 100));
    }

    public function render()
    {
        $user = auth()->user();
        $sundayFirst = $user->week_starts_on === 'sunday';
        $todayISO = now()->format('Y-m-d');
        $anchor = Carbon::parse($this->anchor)->startOfDay();

        $dayLabels = $sundayFirst ? ['S', 'M', 'T', 'W', 'T', 'F', 'S'] : ['M', 'T', 'W', 'T', 'F', 'S', 'S'];

        $allBits = $user->bits()->where('is_system', false)->where('status', '!=', 'archived')
            ->orderBy('created_at')->get()->values();

        // Period bounds
        if ($this->view === 'weekly') {
            $weekStart = $anchor->copy()->startOfWeek($sundayFirst ? Carbon::SUNDAY : Carbon::MONDAY);
            $periodStart = $weekStart->format('Y-m-d');
            $periodEnd = $weekStart->copy()->addDays(6)->format('Y-m-d');
        } elseif ($this->view === 'monthly') {
            $periodStart = $anchor->copy()->startOfMonth()->format('Y-m-d');
            $periodEnd = $anchor->copy()->endOfMonth()->format('Y-m-d');
        } else {
            $periodStart = $anchor->copy()->startOfYear()->format('Y-m-d');
            $periodEnd = $anchor->copy()->endOfYear()->format('Y-m-d');
        }

        $overlaps = fn ($b) => ($b->start_date?->format('Y-m-d') ?? '0000-01-01') <= $periodEnd
            && ($b->end_date?->format('Y-m-d') ?? '9999-12-31') >= $periodStart;

        // keep palette index stable to the full list, then filter to period
        $bits = $allBits->map(fn ($b, $i) => ['bit' => $b, 'pal' => self::PALETTE[$i % count(self::PALETTE)]])
            ->filter(fn ($row) => $overlaps($row['bit']))->values();

        $entries = Entry::where('user_id', $user->id)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->where('status', 'done')->get();
        $doneOn = fn ($bitId, $iso) => $entries->contains(fn ($e) => $e->bit_id === $bitId && $e->date->format('Y-m-d') === $iso);

        $data = [
            'faded' => self::NEUTRAL_FADED,
            'view' => $this->view,
            'dayLabels' => $dayLabels,
            'hasBits' => $bits->isNotEmpty(),
            'todayISO' => $todayISO,
        ];

        if ($this->view === 'weekly') {
            $weekDays = collect(range(0, 6))->map(fn ($i) => Carbon::parse($periodStart)->addDays($i));
            $data['rangeLabel'] = $weekDays->first()->day.' '.self::MONTHS_SHORT[$weekDays->first()->month - 1]
                .' – '.$weekDays->last()->day.' '.self::MONTHS_SHORT[$weekDays->last()->month - 1];

            $rows = $bits->map(function ($row) use ($weekDays, $doneOn, $todayISO) {
                $b = $row['bit'];
                $dayStates = $weekDays->map(function ($d) use ($b, $doneOn, $todayISO) {
                    $iso = $d->format('Y-m-d');
                    $oor = $this->dayOutOfRange($b, $iso);

                    return [
                        'iso' => $iso,
                        'done' => $doneOn($b->id, $iso) && ! $oor,
                        'isFuture' => $iso > $todayISO,
                        'outOfRange' => $oor,
                    ];
                });
                $doneCount = $dayStates->where('done', true)->count();
                $target = min(7, max(1, $b->goal_per_week));

                return ['bit' => $b, 'pal' => $row['pal'], 'dayStates' => $dayStates, 'isPerfect' => $doneCount >= $target];
            });

            // stats
            $bestDayCount = 0;
            $bestDayName = '';
            $totalDone = 0;
            $activeDays = 0;
            foreach ($weekDays as $d) {
                $iso = $d->format('Y-m-d');
                $dayDone = 0;
                foreach ($bits as $row) {
                    if (! $this->dayOutOfRange($row['bit'], $iso) && $doneOn($row['bit']->id, $iso)) {
                        $dayDone++;
                    }
                }
                $totalDone += $dayDone;
                if ($dayDone > 0) {
                    $activeDays++;
                }
                if ($dayDone > $bestDayCount) {
                    $bestDayCount = $dayDone;
                    $bestDayName = $d->format('D');
                }
            }
            $goalsMet = 0;
            foreach ($bits as $row) {
                $b = $row['bit'];
                $done = $weekDays->filter(fn ($d) => ! $this->dayOutOfRange($b, $d->format('Y-m-d')) && $doneOn($b->id, $d->format('Y-m-d')))->count();
                if ($done >= min(7, max(1, $b->goal_per_week))) {
                    $goalsMet++;
                }
            }
            $data['rows'] = $rows;
            $data['stats'] = [
                'goalsMet' => $goalsMet, 'goalsTotal' => $bits->count(),
                'bestDayName' => $bestDayName, 'bestDayCount' => $bestDayCount,
                'totalDone' => $totalDone, 'daysActive' => $activeDays,
            ];
        } elseif ($this->view === 'monthly') {
            $year = $anchor->year;
            $month = $anchor->month;
            $daysInMonth = $anchor->copy()->daysInMonth;
            $data['rangeLabel'] = self::MONTHS[$month - 1].' '.$year;
            $isCurrentMonth = $year === now()->year && $month === now()->month;
            $monthEnd = $isCurrentMonth ? $todayISO : $anchor->copy()->endOfMonth()->format('Y-m-d');

            $data['cards'] = $bits->map(function ($row) use ($year, $month, $daysInMonth, $doneOn, $todayISO, $periodStart, $monthEnd) {
                $b = $row['bit'];
                $cells = collect(range(1, $daysInMonth))->map(function ($day) use ($b, $year, $month, $doneOn, $todayISO) {
                    $iso = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $oor = $this->dayOutOfRange($b, $iso);

                    return [
                        'day' => $day, 'iso' => $iso,
                        'done' => ! $oor && $doneOn($b->id, $iso),
                        'isFuture' => $iso > $todayISO, 'outOfRange' => $oor,
                    ];
                });
                $doneCount = $cells->where('done', true)->count();
                $pct = $this->rangeCompletionPct($b, $doneCount, $periodStart, $monthEnd);

                return ['bit' => $b, 'pal' => $row['pal'], 'cells' => $cells, 'doneCount' => $doneCount, 'pct' => $pct, 'isPerfect' => $pct >= 100 && $doneCount > 0];
            });
        } else {
            $year = $anchor->year;
            $data['rangeLabel'] = (string) $year;
            $jan1 = Carbon::create($year, 1, 1);
            $jan1Dow = (int) $jan1->format('w'); // 0=Sun
            $leadBlanks = $sundayFirst ? $jan1Dow : ($jan1Dow === 0 ? 6 : $jan1Dow - 1);
            $yearDays = [];
            for ($d = $jan1->copy(); $d->year === $year; $d->addDay()) {
                $yearDays[] = $d->format('Y-m-d');
            }
            $yearEnd = $year === now()->year ? $todayISO : "$year-12-31";

            $data['leadBlanks'] = $leadBlanks;
            $data['grids'] = $bits->map(function ($row) use ($yearDays, $doneOn, $todayISO, $periodStart, $yearEnd) {
                $b = $row['bit'];
                $cells = collect($yearDays)->map(function ($iso) use ($b, $doneOn, $todayISO) {
                    $oor = $this->dayOutOfRange($b, $iso);

                    return ['iso' => $iso, 'done' => ! $oor && $doneOn($b->id, $iso), 'isFuture' => $iso > $todayISO, 'outOfRange' => $oor];
                });
                $doneCount = $cells->where('done', true)->count();
                $pct = $this->rangeCompletionPct($b, $doneCount, $periodStart, $yearEnd);

                return ['bit' => $b, 'pal' => $row['pal'], 'cells' => $cells, 'doneCount' => $doneCount, 'pct' => $pct];
            });
        }

        return view('livewire.calendar', $data);
    }
}
