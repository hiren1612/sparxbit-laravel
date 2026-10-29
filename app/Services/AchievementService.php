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
        $week = $user->localToday()->isoFormat('GGGG-[W]WW');
        $candidates = []; // priority => Achievement (higher priority wins the popup)

        // 1) Per-Bit weekly goal — one per Bit per ISO week (lowest priority).
        foreach (self::bitsHittingGoal($user) as $bit) {
            $a = self::awardOnce($user, "bit:{$bit->id}:{$week}", 'bit',
                "{$bit->title} goal hit! 🎯",
                "You reached your weekly goal for {$bit->title}. Nice work — keep it rolling!");
            if ($a) {
                $candidates[1] = $a;
            }
        }

        // 2) Weekly goal smashed — every active Bit done this week.
        if (self::weeklyGoalMet($user)) {
            $a = self::awardOnce($user, 'weekly:'.$week, 'weekly',
                'Weekly goal smashed! 🎉',
                "You hit your goal on every Bit this week. Incredible consistency — keep the momentum going!");
            if ($a) {
                $candidates[2] = $a;
            }
        }

        // 3) Check-in streak milestones (highest priority).
        $streak = self::checkinStreak($user);
        foreach (self::STREAK_MILESTONES as $m) {
            if ($streak >= $m) {
                $a = self::awardOnce($user, "streak-{$m}", 'streak',
                    "{$m}-day streak unlocked! 🔥",
                    "You've shown up ".$m." days in a row. That's how habits are built — you're doing amazing!");
                if ($a) {
                    $candidates[3] = $a;
                }
            }
        }

        if (empty($candidates)) {
            return null;
        }
        ksort($candidates);

        return end($candidates); // highest-priority newly-earned achievement
    }

    // Active Bits that have reached their weekly goal in the current week.
    public static function bitsHittingGoal(User $user): \Illuminate\Support\Collection
    {
        $sundayFirst = $user->week_starts_on === 'sunday';
        $weekStart = $user->localToday()->startOfWeek($sundayFirst ? Carbon::SUNDAY : Carbon::MONDAY)->startOfDay();
        $weekISOs = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i)->format('Y-m-d'))->all();
        $firstDay = $weekISOs[0];
        $lastDay = $weekISOs[6];

        $bits = $user->bits()->where('is_system', false)->where('status', 'active')->get()
            ->filter(fn ($b) => (! $b->start_date || $b->start_date->format('Y-m-d') <= $lastDay)
                && (! $b->end_date || $b->end_date->format('Y-m-d') >= $firstDay));

        $entries = Entry::where('user_id', $user->id)->whereIn('date', $weekISOs)->where('status', 'done')->get();

        return $bits->filter(fn ($b) => $entries->where('bit_id', $b->id)->count() >= max(1, $b->goal_per_week))->values();
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
        $weekStart = $user->localToday()->startOfWeek($sundayFirst ? Carbon::SUNDAY : Carbon::MONDAY)->startOfDay();
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

        $today = $user->localToday()->startOfDay();
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
