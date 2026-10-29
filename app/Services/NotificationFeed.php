<?php

namespace App\Services;

use App\Models\Entry;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class NotificationFeed
{
    // Store a persisted notification in the bell feed (e.g. a reminder that
    // just fired). Deduped so the same message can't pile up within the window.
    public static function push(User $user, array $data, int $dedupeMinutes = 30): ?UserNotification
    {
        if ($dedupeMinutes > 0) {
            $exists = $user->notifications()
                ->where('title', $data['title'] ?? '')
                ->where('body', $data['body'] ?? '')
                ->where('created_at', '>=', now()->subMinutes($dedupeMinutes))
                ->exists();
            if ($exists) {
                return null;
            }
        }

        return $user->notifications()->create([
            'type' => $data['type'] ?? 'reminder',
            'icon' => $data['icon'] ?? '🔔',
            'tone' => $data['tone'] ?? 'blue',
            'title' => $data['title'] ?? 'Reminder',
            'body' => $data['body'] ?? '',
        ]);
    }

    // Build the feed for a user: persisted notifications (reminders) merged with
    // the derived activity (streak milestones, weekly goals, today's wins),
    // newest first. Shared by the Notifications page and the Home bell dot.
    public static function items(User $user): Collection
    {
        $today = $user->localToday()->startOfDay();
        $todayISO = $today->format('Y-m-d');
        $sundayFirst = $user->week_starts_on === 'sunday';
        $weekStart = $user->localToday()->startOfWeek($sundayFirst ? Carbon::SUNDAY : Carbon::MONDAY)->startOfDay();
        $weekISOs = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i)->format('Y-m-d'))->all();

        $bits = $user->bits()->where('is_system', false)->where('status', 'active')->get();
        $weekEntries = Entry::where('user_id', $user->id)->whereIn('date', $weekISOs)->where('status', 'done')->get();

        $items = collect();

        // 1) Persisted notifications (reminders), last 30 days.
        foreach ($user->notifications()->where('created_at', '>=', now()->subDays(30))->limit(50)->get() as $n) {
            $items->push([
                'icon' => $n->icon, 'tone' => $n->tone,
                'title' => $n->title, 'body' => $n->body,
                'when' => self::relative($n->created_at),
                'ts' => $n->created_at,
            ]);
        }

        foreach ($bits as $b) {
            $done = $weekEntries->where('bit_id', $b->id)->count();
            if ($done >= max(1, $b->goal_per_week)) {
                $items->push([
                    'icon' => '🎉', 'tone' => 'green',
                    'title' => 'Weekly goal smashed',
                    'body' => "You hit your goal for {$b->title} this week!",
                    'when' => 'This week', 'ts' => $weekStart,
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
                    'when' => 'Today', 'ts' => $e->completed_at ?? $today,
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
                $items->push([
                    'icon' => '🔥', 'tone' => 'orange',
                    'title' => "{$streak}-day streak!",
                    'body' => "You've opened Sparx Bit {$streak} days in a row. Keep it going.",
                    'when' => 'Today', 'ts' => $today->copy()->addSeconds(1),
                ]);
            }
        }

        // Newest first.
        return $items->sortByDesc('ts')->values();
    }

    private static function relative(Carbon $ts): string
    {
        if ($ts->isToday()) {
            return $ts->format('g:i A');
        }
        if ($ts->isYesterday()) {
            return 'Yesterday';
        }

        return $ts->format('j M');
    }

    // Is there activity the user hasn't checked? True if any persisted
    // notification is unread, or there's derived activity since today began.
    public static function hasUnread(User $user): bool
    {
        if ($user->notifications()->whereNull('read_at')->exists()) {
            return true;
        }
        if (self::items($user)->isEmpty()) {
            return false;
        }
        $seen = $user->notifications_seen_at;

        return ! $seen || $seen->lt($user->localNow()->startOfDay());
    }

    // Mark the feed as seen (clears the bell dot).
    public static function markSeen(User $user): void
    {
        $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);
        $user->forceFill(['notifications_seen_at' => now()])->save();
    }
}
