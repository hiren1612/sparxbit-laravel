<?php

namespace App\Services;

use App\Models\Entry;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class NotificationFeed
{
    // Build the derived activity feed for a user (streak milestones, weekly
    // goals hit, today's completions). Shared by the Notifications page and the
    // Home bell unread indicator.
    public static function items(User $user): Collection
    {
        $today = now()->startOfDay();
        $todayISO = $today->format('Y-m-d');
        $sundayFirst = $user->week_starts_on === 'sunday';
        $weekStart = now()->startOfWeek($sundayFirst ? Carbon::SUNDAY : Carbon::MONDAY)->startOfDay();
        $weekISOs = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i)->format('Y-m-d'))->all();

        $bits = $user->bits()->where('is_system', false)->where('status', 'active')->get();
        $weekEntries = Entry::where('user_id', $user->id)->whereIn('date', $weekISOs)->where('status', 'done')->get();

        $items = collect();

        foreach ($bits as $b) {
            $done = $weekEntries->where('bit_id', $b->id)->count();
            if ($done >= max(1, $b->goal_per_week)) {
                $items->push([
                    'icon' => '🎉', 'tone' => 'green',
                    'title' => 'Weekly goal smashed',
                    'body' => "You hit your goal for {$b->title} this week!",
                    'when' => 'This week',
                ]);
            }
        }

        foreach ($weekEntries->filter(fn ($e) => $e->date->format('Y-m-d') === $todayISO) as $e) {
            $bit = $bits->firstWhere('id', $e->bit_id);
            if ($bit) {
                $items->push([
                    'icon' => '✅', 'tone' => 'blue',
                    'title' => 'Nice work',
                    'body' => "You completed {$bit->title} today.",
                    'when' => 'Today',
                ]);
            }
        }

        $system = $user->bits()->where('is_system', true)->first();
        if ($system) {
            $checked = Entry::where('bit_id', $system->id)->where('status', 'done')->pluck('date')
                ->map(fn ($d) => $d->format('Y-m-d'))->all();
            $streak = 0;
            $offset = in_array($todayISO, $checked, true) ? 0 : 1;
            for ($i = $offset; $i < 400; $i++) {
                if (in_array($today->copy()->subDays($i)->format('Y-m-d'), $checked, true)) {
                    $streak++;
                } else {
                    break;
                }
            }
            if (in_array($streak, [3, 7, 14, 30, 50, 100, 200, 365], true)) {
                $items->prepend([
                    'icon' => '🔥', 'tone' => 'orange',
                    'title' => "{$streak}-day streak!",
                    'body' => "You've opened Sparx Bit {$streak} days in a row. Keep it going.",
                    'when' => 'Today',
                ]);
            }
        }

        return $items->values();
    }

    // Is there activity the user hasn't checked since their last visit today?
    public static function hasUnread(User $user): bool
    {
        if (self::items($user)->isEmpty()) {
            return false;
        }
        $seen = $user->notifications_seen_at;

        return ! $seen || $seen->lt(now()->startOfDay());
    }
}
