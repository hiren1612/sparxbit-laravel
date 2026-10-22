<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Support\Carbon;

class AchievementService
{
    public const STREAK_MILESTONES = [7, 30];

    // Award any newly-earned trophies for the user and return the most notable
    // new one to celebrate (or null if nothing new).
    public static function sync(User $user): ?Achievement
    {
        $newest = null;

        // 1) Weekly goal smashed (this week) — one per ISO week.
        if (self::weeklyGoalMet($user)) {
            $weekKey = 'weekly:'.now()->isoFormat('GGGG-[W]WW');
            $a = self::awardOnce($user, $weekKey, 'weekly',
                'Weekly goal smashed! 🎉',
                "You hit your goal on every Bit this week. Incredible consistency — keep the momentum going!");
            if ($a) {
                $newest = $a;
            }
        }

        // 2) Check-in streak milestones.
        $streak = self::checkinStreak($user);
        foreach (self::STREAK_MILESTONES as $m) {
            if ($streak >= $m) {
                $a = self::awardOnce($user, "streak-{$m}", 'streak',
                    "{$m}-day streak unlocked! 🔥",
                    "You've shown up ".$m." days in a row. That's how habits are built — you're doing amazing!");
                if ($a) {
                    $newest = $a; // prefer the higher/later milestone
                }
            }
        }

        return $newest;
    }

    private static function awardOnce(User $user, string $key, string $type, string $title, string $body): ?Achievement
    {
        if (Achievement::where('user_id', $user->id)->where('key', $key)->exists()) {
            return null;
        }

        return Achievement::create([
            'user_id' => $user->id,
            'key' => $key,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'earned_at' => now(),
        ]);
    }

    public static function weeklyGoalMet(User $user): bool
    {
        $sundayFirst = $user->week_starts_on === 'sunday';
        $weekStart = now()->startOfWeek($sundayFirst ? Carbon::SUNDAY : Carbon::MONDAY)->startOfDay();
        $weekISOs = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i)->format('Y-m-d'))->all();
        $firstDay = $weekISOs[0];
        $lastDay = $weekISOs[6];

        $bits = $user->bits()->where('is_system', false)->where('status', 'active')->get()
            ->filter(fn ($b) => (! $b->start_date || $b->start_date->format('Y-m-d') <= $lastDay)
                && (! $b->end_date || $b->end_date->format('Y-m-d') >= $firstDay));

        if ($bits->isEmpty()) {
            return false;
        }

        $entries = Entry::where('user_id', $user->id)->whereIn('date', $weekISOs)->where('status', 'done')->get();

        foreach ($bits as $b) {
            $done = $entries->where('bit_id', $b->id)->count();
            if ($done < max(1, $b->goal_per_week)) {
                return false; // at least one bit hasn't met its goal
            }
        }

        return true;
    }

    public static function checkinStreak(User $user): int
    {
        $system = $user->bits()->where('is_system', true)->first();
        if (! $system) {
            return 0;
        }
        $checked = Entry::where('bit_id', $system->id)->where('status', 'done')->pluck('date')
            ->map(fn ($d) => $d->format('Y-m-d'))->all();

        $today = now()->startOfDay();
        $todayISO = $today->format('Y-m-d');
        $streak = 0;
        $offset = in_array($todayISO, $checked, true) ? 0 : 1;
        for ($i = $offset; $i < 400; $i++) {
            if (in_array($today->copy()->subDays($i)->format('Y-m-d'), $checked, true)) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }
}
