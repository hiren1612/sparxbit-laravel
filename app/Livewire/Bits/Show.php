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
        $iso = now()->format('Y-m-d');
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
        $today = now()->startOfDay();
        $todayISO = $today->format('Y-m-d');
        $goal = max(1, $bit->goal_per_week);

        $weekStart = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekDays = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i));
        $weekISOs = $weekDays->map(fn ($d) => $d->format('Y-m-d'))->all();

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

        return view('livewire.bits.show', [
            'emoji' => Dashboard::emoji($bit->icon_key),
            'categoryLabel' => $bit->category?->name,
            'goal' => $goal,
            'doneThisWeek' => min($doneThisWeek, $goal),
            'totalDone' => $totalDone,
            'completion' => $completion,
            'isDoneToday' => $statusOn($todayISO) === 'done',
            'weekStrip' => $weekStrip,
            'history' => $history,
            'changes' => $changes,
            'ended' => $endISO && $endISO < $todayISO,
            'endedLong' => $bit->end_date?->format('j M Y'),
            'startLong' => $bit->start_date?->format('j M Y') ?? '—',
            'reminder' => $bit->reminder_time ? substr($bit->reminder_time, 0, 5).' daily' : 'Off',
        ]);
    }
}
