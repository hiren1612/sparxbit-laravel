<?php

namespace App\Livewire;

use App\Models\Entry;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Dashboard extends Component
{
    public int $weekOffset = 0;
    public string $selectedDateISO = '';

    private const ICONS = [
        'run' => '🏃', 'walk' => '🚶', 'book' => '📖', 'droplet' => '💧', 'moon' => '🌙',
        'target' => '🎯', 'lotus' => '🧘', 'meditate' => '🧘', 'pen' => '✍️', 'home' => '🏠',
        'phone' => '📞', 'sparkle' => '✨', 'heart' => '❤️', 'briefcase' => '💼', 'brain' => '🧠',
        'users' => '👥', 'star' => '⭐', 'planning' => '🗓️',
    ];

    public static function emoji(?string $key): string
    {
        return self::ICONS[$key] ?? '✨';
    }

    public function mount(): void
    {
        $this->selectedDateISO = now()->format('Y-m-d');
    }

    public function prevWeek(): void
    {
        $this->selectedDateISO = Carbon::parse($this->selectedDateISO)->subWeek()->format('Y-m-d');
        $this->weekOffset--;
    }

    public function nextWeek(): void
    {
        if ($this->weekOffset >= 0) {
            return;
        }
        $this->selectedDateISO = Carbon::parse($this->selectedDateISO)->addWeek()->format('Y-m-d');
        $this->weekOffset++;
    }

    public function selectDay(string $iso): void
    {
        $this->selectedDateISO = $iso;
    }

    // Toggle a (non-system) bit's done status for the selected day. Checking a
    // bit also auto-marks the Daily Check-In for that day.
    public function toggle(string $bitId): void
    {
        $user = auth()->user();
        $bit = $user->bits()->find($bitId);
        if (! $bit || $bit->is_system) {
            return;
        }
        $date = $this->selectedDateISO;
        $entry = Entry::where('bit_id', $bitId)->whereDate('date', $date)->first();

        if ($entry && $entry->status === 'done') {
            $entry->delete();
        } else {
            Entry::updateOrCreate(
                ['bit_id' => $bitId, 'date' => $date],
                ['user_id' => $user->id, 'status' => 'done', 'source' => 'manual', 'completed_at' => now()],
            );
            // Auto-mark the Daily Check-In only when checking a bit for TODAY —
            // the check-in means "opened the app that day", which a retroactive
            // past-day tap doesn't imply. Never auto-unsets it.
            if ($date === now()->format('Y-m-d')) {
                $sys = $user->bits()->where('is_system', true)->first();
                if ($sys) {
                    Entry::updateOrCreate(
                        ['bit_id' => $sys->id, 'date' => $date],
                        ['user_id' => $user->id, 'status' => 'done', 'source' => 'manual', 'completed_at' => now()],
                    );
                }
            }
        }
    }

    public function render()
    {
        $user = auth()->user();
        $sundayFirst = $user->week_starts_on === 'sunday';
        $today = now()->startOfDay();
        $todayISO = $today->format('Y-m-d');

        $currentWeekStart = now()->startOfWeek($sundayFirst ? Carbon::SUNDAY : Carbon::MONDAY)->startOfDay();
        $weekStart = $currentWeekStart->copy()->addWeeks($this->weekOffset);
        $weekDates = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i));
        $weekISOs = $weekDates->map(fn ($d) => $d->format('Y-m-d'))->all();

        $selected = Carbon::parse($this->selectedDateISO)->startOfDay();
        $isSelectedToday = $this->selectedDateISO === $todayISO;
        $isSelectedFuture = $selected->gt($today);

        $bits = $user->bits()->where('status', 'active')
            ->orderByRaw('reminder_time is null, reminder_time')
            ->orderBy('created_at')->get();
        $realBits = $bits->where('is_system', false)->values();
        $systemBit = $bits->firstWhere('is_system', true);

        $firstDay = $weekISOs[0];
        $lastDay = $weekISOs[6];

        $entries = Entry::where('user_id', $user->id)->whereIn('date', $weekISOs)->get();
        $doneOn = fn ($bitId, $iso) => $entries->first(fn ($e) => $e->bit_id === $bitId
            && $e->date->format('Y-m-d') === $iso && $e->status === 'done') !== null;

        // A bit counts toward the viewed week only if its active span overlaps
        // that week (so a past week before the bit existed, or after it ended,
        // doesn't borrow today's goals).
        $activeInWeek = fn ($b) => (! $b->start_date || $b->start_date->format('Y-m-d') <= $lastDay)
            && (! $b->end_date || $b->end_date->format('Y-m-d') >= $firstDay);

        $perBit = [];
        $doneSum = 0;
        $targetSum = 0;
        foreach ($realBits as $b) {
            $goal = max(1, $b->goal_per_week);
            $raw = $entries->filter(fn ($e) => $e->bit_id === $b->id && $e->status === 'done')->count();
            $capped = min($raw, $goal);
            $perBit[$b->id] = ['done' => $capped, 'goal' => $goal];
            if ($activeInWeek($b)) {
                $doneSum += $capped;
                $targetSum += $goal;
            }
        }
        $weekPct = $targetSum > 0 ? (int) round($doneSum / $targetSum * 100) : 0;

        // Today's Bits = bits that existed on the selected day (start ≤ sel ≤ end).
        $sel = $this->selectedDateISO;
        $todayBits = $realBits
            ->filter(fn ($b) => (! $b->start_date || $b->start_date->format('Y-m-d') <= $sel)
                && (! $b->end_date || $b->end_date->format('Y-m-d') >= $sel))
            ->map(fn ($b) => [
                'id' => $b->id,
                'title' => $b->title,
                'emoji' => self::emoji($b->icon_key),
                'freq' => $b->goal_per_week === 7 ? 'Daily' : $b->goal_per_week.'× / week',
                'doneSelected' => $doneOn($b->id, $this->selectedDateISO),
                'weekDone' => $perBit[$b->id]['done'],
                'weekGoal' => $perBit[$b->id]['goal'],
                'pct' => (int) round($perBit[$b->id]['done'] / $perBit[$b->id]['goal'] * 100),
            ])
            // Unchecked first (least weekly progress first), checked bits last.
            ->sortBy(fn ($b) => ($b['doneSelected'] ? 1000 : 0) + $b['pct'])
            ->values();

        $strip = $weekDates->map(fn ($d) => [
            'iso' => $d->format('Y-m-d'),
            'day' => $d->day,
            'dow' => strtoupper(substr($d->format('D'), 0, 1)),
            'isToday' => $this->weekOffset === 0 && $d->format('Y-m-d') === $todayISO,
            'isSelected' => $d->format('Y-m-d') === $this->selectedDateISO,
            'hasDone' => $this->weekOffset === 0
                && $entries->first(fn ($e) => $e->date->format('Y-m-d') === $d->format('Y-m-d') && $e->status === 'done') !== null,
        ]);

        $checkinDone = $systemBit ? $doneOn($systemBit->id, $this->selectedDateISO) : false;

        $circ = 2 * M_PI * 40;
        $dashoffset = $circ - ($weekPct / 100) * $circ;

        $offsetLabel = match (true) {
            $this->weekOffset === 0 => 'This week',
            $this->weekOffset === -1 => 'Last week',
            $this->weekOffset === 1 => 'Next week',
            $this->weekOffset < 0 => (-$this->weekOffset).' weeks ago',
            default => $this->weekOffset.' weeks from now',
        };

        $doneCount = $todayBits->where('doneSelected', true)->count();
        $totalCount = $todayBits->count();

        return view('livewire.dashboard', [
            'first' => $user->name ? explode(' ', $user->name)[0] : explode('@', $user->email)[0],
            'weekLabel' => $weekStart->day.' '.$weekStart->format('M').' – '.$weekDates->last()->day.' '.$weekDates->last()->format('M'),
            'offsetLabel' => $offsetLabel,
            'strip' => $strip,
            'todayBits' => $todayBits,
            'weekPct' => $weekPct,
            'doneSum' => $doneSum,
            'targetSum' => $targetSum,
            'circ' => $circ,
            'dashoffset' => $dashoffset,
            'isSelectedToday' => $isSelectedToday,
            'isSelectedFuture' => $isSelectedFuture,
            'checkinDone' => $checkinDone,
            'canNext' => $this->weekOffset < 0,
            'selectedLabel' => $selected->format('D, j M'),
            'doneCount' => $doneCount,
            'totalCount' => $totalCount,
        ]);
    }
}
