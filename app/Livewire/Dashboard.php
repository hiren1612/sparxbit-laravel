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
    public ?array $celebration = null;

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
        $this->selectedDateISO = auth()->user()->localToday()->format('Y-m-d');
        $this->autoCheckInToday();
        $this->celebrate();
    }

    // Landing on Home each day counts the Daily Check-In for today ("Open the
    // app to count today"). Only creates it if today's entry doesn't exist yet,
    // and never for a past day — matches the original app's behavior.
    private function autoCheckInToday(): void
    {
        $user = auth()->user();
        $sys = $user->bits()->where('is_system', true)->first();
        if (! $sys) {
            return;
        }
        Entry::firstOrCreate(
            ['bit_id' => $sys->id, 'date' => auth()->user()->localToday()->format('Y-m-d')],
            ['user_id' => $user->id, 'status' => 'done', 'source' => 'manual', 'completed_at' => now()],
        );
    }

    // Award any newly-earned trophies and queue a celebration popup.
    private function celebrate(): void
    {
        $achievement = \App\Services\AchievementService::sync(auth()->user());
        if (! $achievement) {
            return;
        }

        $user = auth()->user();
        if (! $user->referral_code) {
            $user->update(['referral_code' => \App\Services\UserProvisioning::genReferralCode()]);
        }

        $this->celebration = [
            'type' => $achievement->type,
            'title' => $achievement->title,
            'body' => $achievement->body,
            'shareUrl' => url('/?ref='.strtolower($user->referral_code)),
            'shareText' => 'I just unlocked "'.$achievement->title.'" on Sparx Bit! 🎉 Build better habits with me:',
        ];
    }

    public function dismissCelebration(): void
    {
        $this->celebration = null;
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
        // Strict rule (matches the original): only today or past days can be
        // checked — never a future day.
        if ($date > $user->localToday()->format('Y-m-d')) {
            return;
        }
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
            if ($date === auth()->user()->localToday()->format('Y-m-d')) {
                $sys = $user->bits()->where('is_system', true)->first();
                if ($sys) {
                    Entry::updateOrCreate(
                        ['bit_id' => $sys->id, 'date' => $date],
                        ['user_id' => $user->id, 'status' => 'done', 'source' => 'manual', 'completed_at' => now()],
                    );
                }
            }
            $this->celebrate();
        }
    }

    // Mark every still-undone bit for TODAY as done in one tap (today only).
    public function markAll(): void
    {
        if ($this->selectedDateISO !== auth()->user()->localToday()->format('Y-m-d')) {
            return;
        }
        $user = auth()->user();
        $iso = $this->selectedDateISO;
        $bits = $user->bits()->where('status', 'active')->where('is_system', false)
            ->where(fn ($q) => $q->whereNull('start_date')->orWhereDate('start_date', '<=', $iso))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $iso))
            ->get();
        foreach ($bits as $b) {
            Entry::updateOrCreate(
                ['bit_id' => $b->id, 'date' => $iso],
                ['user_id' => $user->id, 'status' => 'done', 'source' => 'manual', 'completed_at' => now()],
            );
        }
        if ($bits->isNotEmpty()) {
            $sys = $user->bits()->where('is_system', true)->first();
            if ($sys) {
                Entry::updateOrCreate(
                    ['bit_id' => $sys->id, 'date' => $iso],
                    ['user_id' => $user->id, 'status' => 'done', 'source' => 'manual', 'completed_at' => now()],
                );
            }
            $this->celebrate();
        }
    }

    public function resume(string $bitId): void
    {
        $bit = auth()->user()->bits()->find($bitId);
        if ($bit && $bit->status === 'paused') {
            $bit->update(['status' => 'active']);
        }
    }

    public function render()
    {
        $user = auth()->user();
        $sundayFirst = $user->week_starts_on === 'sunday';
        $today = auth()->user()->localToday()->startOfDay();
        $todayISO = $today->format('Y-m-d');

        $currentWeekStart = auth()->user()->localToday()->startOfWeek($sundayFirst ? Carbon::SUNDAY : Carbon::MONDAY)->startOfDay();
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

        // Has the user ever created a Bit (any status)? Drives the empty-state
        // CTA label: "Create your first Bit" before the first one, "New Bit" after.
        $hasAnyBits = $user->bits()->where('is_system', false)->exists();

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

        // Dynamic hero copy (matches the original's motivational titles).
        $isCurrentWeek = $this->weekOffset === 0;
        $noun = $isCurrentWeek ? 'week' : 'run';
        $subWhen = $isCurrentWeek ? 'this week' : ($this->weekOffset === -1 ? 'last week' : strtolower($offsetLabel));
        if ($targetSum === 0) {
            $heroTitle = $isCurrentWeek ? 'Ready to begin?' : 'Nothing tracked';
            $heroSub = 'Add a Bit to start building your week.';
        } elseif ($weekPct >= 85) {
            $heroTitle = "Great {$noun}! 🌟";
            $heroSub = "You completed {$weekPct}% of your Bits {$subWhen}. Keep the momentum going.";
        } elseif ($weekPct >= 60) {
            $heroTitle = "Solid {$noun}";
            $heroSub = "You completed {$weekPct}% of your Bits {$subWhen}.";
        } elseif ($weekPct > 0) {
            $heroTitle = 'A start is a start';
            $heroSub = "You completed {$weekPct}% of your Bits {$subWhen} — every bit counts.";
        } else {
            $heroTitle = $isCurrentWeek ? 'Ready to begin?' : 'A quiet week';
            $heroSub = 'Check off a Bit to get going.';
        }

        // Overview snapshot + paused bits (current week only).
        $overview = ['onTrack' => 0, 'reviewing' => 0, 'paused' => 0, 'completed' => 0, 'upcoming' => 0, 'active' => 0];
        $paused = collect();
        if ($isCurrentWeek) {
            $allBits = $user->bits()->where('is_system', false)->get();
            $recent = collect(range(0, 6))->map(fn ($i) => $today->copy()->subDays($i)->format('Y-m-d'))->all();
            $recentDone = Entry::where('user_id', $user->id)->where('status', 'done')
                ->whereIn('date', $recent)->get();

            $activeOv = $allBits->filter(fn ($b) => $b->status === 'active'
                && (! $b->end_date || $b->end_date->format('Y-m-d') >= $todayISO));
            $overview['active'] = $activeOv->count();
            $overview['upcoming'] = $activeOv->filter(fn ($b) => $b->start_date && $b->start_date->format('Y-m-d') > $todayISO)->count();
            $started = $activeOv->filter(fn ($b) => ! $b->start_date || $b->start_date->format('Y-m-d') <= $todayISO);
            foreach ($started as $b) {
                $done = $recentDone->filter(fn ($e) => $e->bit_id === $b->id)->count();
                $goal = max(1, $b->goal_per_week);
                $pct = min(1, $done / $goal);
                if ($pct >= 0.7) {
                    $overview['onTrack']++;
                } elseif ($done > 0) {
                    $overview['reviewing']++;
                }
            }
            $overview['paused'] = $allBits->where('status', 'paused')->count();
            $overview['completed'] = $allBits->where('status', 'completed')->count();

            $allDoneByBit = Entry::where('user_id', $user->id)->where('status', 'done')
                ->whereIn('bit_id', $allBits->where('status', 'paused')->pluck('id'))->get()->groupBy('bit_id');
            $paused = $allBits->where('status', 'paused')->map(fn ($b) => [
                'id' => $b->id,
                'title' => $b->title,
                'emoji' => self::emoji($b->icon_key),
                'ago' => 'Paused '.$b->updated_at->diffForHumans(null, true).' ago',
                'doneCount' => optional($allDoneByBit->get($b->id))->count() ?? 0,
            ])->values();
        }

        $markAllCount = $isCurrentWeek && $isSelectedToday ? $todayBits->where('doneSelected', false)->count() : 0;

        // Sponsored "AdBit" — one eligible advertiser, current week only.
        $ad = $isCurrentWeek ? \App\Services\AdFeed::pickOne($user) : null;

        $hour = now()->setTimezone($user->timezone ?: config('app.timezone'))->hour;
        $greeting = match (true) {
            $hour < 5 => 'Good evening',
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            $hour < 22 => 'Good evening',
            default => 'Good evening',
        };

        return view('livewire.dashboard', [
            'greeting' => $greeting,
            'first' => $user->name ? explode(' ', $user->name)[0] : explode('@', $user->email)[0],
            'avatarUrl' => $user->avatar_url,
            'bellUnread' => \App\Services\NotificationFeed::hasUnread($user),
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
            'isCurrentWeek' => $isCurrentWeek,
            'heroTitle' => $heroTitle,
            'heroSub' => $heroSub,
            'overview' => $overview,
            'paused' => $paused,
            'markAllCount' => $markAllCount,
            'ad' => $ad,
            'hasAnyBits' => $hasAnyBits,
        ]);
    }
}
