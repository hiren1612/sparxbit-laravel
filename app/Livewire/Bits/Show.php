<?php

namespace App\Livewire\Bits;

use App\Livewire\Dashboard;
use App\Models\Bit;
use App\Models\Entry;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Show extends Component
{
    public Bit $bit;
    public bool $confirmDelete = false;
    public bool $upgradeDismissed = false;
    public bool $backfillOpen = false;

    private const CYCLE = [
        'done' => 'skipped',
        'skipped' => 'missed',
        'missed' => 'pending',
        'pending' => 'done',
    ];

    public function mount(Bit $bit): void
    {
        abort_unless($bit->user_id === auth()->id(), 404);
        $this->bit = $bit;
    }

    public function toggleToday(): void
    {
        $iso = auth()->user()->localToday()->format('Y-m-d');
        $entry = Entry::where('bit_id', $this->bit->id)->whereDate('date', $iso)->first();
        if ($entry && $entry->status === 'done') {
            $entry->delete();
        } else {
            Entry::updateOrCreate(
                ['bit_id' => $this->bit->id, 'date' => $iso],
                ['user_id' => $this->bit->user_id, 'status' => 'done', 'source' => 'manual', 'completed_at' => now()],
            );
        }
    }

    public function pauseResume(): void
    {
        if (! $this->bit->is_system) {
            $this->bit->update(['status' => $this->bit->status === 'paused' ? 'active' : 'paused']);
        }
    }

    public function cycle(string $dateISO): void
    {
        $entry = Entry::where('bit_id', $this->bit->id)->whereDate('date', $dateISO)->first();
        $current = $entry?->status ?? 'pending';
        $next = self::CYCLE[$current] ?? 'done';

        if ($next === 'pending') {
            $entry?->delete();
        } else {
            Entry::updateOrCreate(
                ['bit_id' => $this->bit->id, 'date' => $dateISO],
                [
                    'user_id' => $this->bit->user_id,
                    'status' => $next,
                    'source' => 'manual',
                    'completed_at' => $next === 'done' ? now() : null,
                ],
            );
        }
    }

    // Goal upgrade: bump the weekly goal by one after 4 perfect weeks.
    public function upgradeGoal(): void
    {
        if ($this->bit->goal_per_week < 7) {
            $next = $this->bit->goal_per_week + 1;
            \App\Models\BitChange::create([
                'bit_id' => $this->bit->id, 'user_id' => $this->bit->user_id,
                'field' => 'goal', 'from_val' => (string) $this->bit->goal_per_week,
                'to_val' => (string) $next, 'changed_at' => now(),
            ]);
            $this->bit->update(['goal_per_week' => $next]);
        }
        $this->upgradeDismissed = true;
    }

    public function dismissUpgrade(): void
    {
        $this->upgradeDismissed = true;
    }

    // Backfill: toggle a past day done <-> cleared from the "Log past days" sheet.
    public function backfillToggle(string $iso): void
    {
        if ($iso > auth()->user()->localToday()->format('Y-m-d')) {
            return;
        }
        $entry = Entry::where('bit_id', $this->bit->id)->whereDate('date', $iso)->first();
        if ($entry && $entry->status === 'done') {
            $entry->delete();
        } else {
            Entry::updateOrCreate(
                ['bit_id' => $this->bit->id, 'date' => $iso],
                ['user_id' => $this->bit->user_id, 'status' => 'done', 'source' => 'manual', 'completed_at' => now()],
            );
        }
    }

    public function archive(): void
    {
        if (! $this->bit->is_system) {
            $this->bit->update(['status' => 'archived']);
        }
        $this->redirectRoute('bits.index', navigate: true);
    }

    public function delete(): void
    {
        if (! $this->bit->is_system) {
            $this->bit->delete();
            $this->redirectRoute('bits.index', navigate: true);
        }
    }

    public function render()
    {
        $bit = $this->bit;
        $today = auth()->user()->localToday()->startOfDay();
        $todayISO = $today->format('Y-m-d');
        $goal = max(1, $bit->goal_per_week);

        $sundayFirst = auth()->user()->week_starts_on === 'sunday';
        $weekStart = auth()->user()->localToday()->startOfWeek($sundayFirst ? Carbon::SUNDAY : Carbon::MONDAY)->startOfDay();
        $weekDays = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i));
        $weekISOs = $weekDays->map(fn ($d) => $d->format('Y-m-d'))->all();
        $dayLabels = $sundayFirst ? ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] : ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];

        $allEntries = Entry::where('bit_id', $bit->id)->get();
        $statusOn = fn ($iso) => optional($allEntries->first(fn ($e) => $e->date->format('Y-m-d') === $iso))->status;

        $doneThisWeek = collect($weekISOs)->filter(fn ($iso) => $statusOn($iso) === 'done')->count();
        $totalDone = $allEntries->where('status', 'done')->count();
        $completion = (int) round(min($doneThisWeek, $goal) / $goal * 100);

        $weekStrip = $weekDays->map(fn ($d) => [
            'dayOfMonth' => $d->day,
            'status' => $statusOn($d->format('Y-m-d')) ?? 'pending',
        ]);

        // Recent history — last 7 days, today first.
        $startISO = $bit->start_date?->format('Y-m-d');
        $endISO = $bit->end_date?->format('Y-m-d');
        $history = collect(range(0, 6))->map(function ($i) use ($today, $statusOn, $startISO, $endISO) {
            $d = $today->copy()->subDays($i);
            $iso = $d->format('Y-m-d');
            $inactive = ($startISO && $iso < $startISO) || ($endISO && $iso > $endISO);
            $rel = $i === 0 ? 'Today' : ($i === 1 ? 'Yesterday' : "$i days ago");

            return [
                'iso' => $iso,
                'date' => $d->format('D, j M'),
                'relative' => $rel,
                'status' => $statusOn($iso) ?? 'pending',
                'inactive' => $inactive,
            ];
        });

        $changes = $bit->changes()->orderByDesc('changed_at')->limit(8)->get();

        // Goal-upgrade prompt — 4 consecutive weeks at/above goal, goal < 7.
        $showUpgrade = false;
        if ($goal < 7 && ! $this->upgradeDismissed) {
            $perfect = 0;
            for ($w = 0; $w < 4; $w++) {
                $ws = $weekStart->copy()->subWeeks($w);
                $done = collect(range(0, 6))->filter(fn ($i) => $statusOn($ws->copy()->addDays($i)->format('Y-m-d')) === 'done')->count();
                if ($done >= $goal) {
                    $perfect++;
                } else {
                    break;
                }
            }
            $showUpgrade = $perfect >= 4;
        }

        // Backfill sheet — 30 days ending today, newest first.
        $backfillDays = collect();
        if ($this->backfillOpen) {
            $backfillDays = collect(range(0, 29))->map(function ($i) use ($today, $statusOn, $startISO, $endISO) {
                $d = $today->copy()->subDays($i);
                $iso = $d->format('Y-m-d');

                return [
                    'iso' => $iso,
                    'label' => $i === 0 ? 'Today' : ($i === 1 ? 'Yesterday' : $d->format('D, j M')),
                    'done' => $statusOn($iso) === 'done',
                    'inactive' => ($startISO && $iso < $startISO) || ($endISO && $iso > $endISO),
                ];
            });
        }

        return view('livewire.bits.show', [
            'emoji' => Dashboard::emoji($bit->icon_key),
            'categoryLabel' => $bit->category?->name,
            'goal' => $goal,
            'doneThisWeek' => min($doneThisWeek, $goal),
            'totalDone' => $totalDone,
            'completion' => $completion,
            'isDoneToday' => $statusOn($todayISO) === 'done',
            'weekStrip' => $weekStrip,
            'dayLabels' => $dayLabels,
            'history' => $history,
            'changes' => $changes,
            'showUpgrade' => $showUpgrade,
            'nextGoal' => $goal + 1,
            'backfillDays' => $backfillDays,
            'ended' => $endISO && $endISO < $todayISO,
            'endedLong' => $bit->end_date?->format('j M Y'),
            'startLong' => $bit->start_date?->format('j M Y') ?? '—',
            'reminder' => $bit->reminder_time ? substr($bit->reminder_time, 0, 5).' daily' : 'Off',
        ]);
    }
}
