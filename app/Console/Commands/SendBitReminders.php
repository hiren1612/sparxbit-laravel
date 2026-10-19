<?php

namespace App\Console\Commands;

use App\Mail\BitReminderMail;
use App\Models\Bit;
use App\Models\Entry;
use App\Services\ExpoPush;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendBitReminders extends Command
{
    protected $signature = 'bits:send-reminders';

    protected $description = 'Email users a digest of Bits whose reminder time is now';

    public function handle(): int
    {
        $bits = Bit::query()
            ->where('reminder_enabled', true)
            ->where('status', 'active')
            ->whereNotNull('reminder_time')
            ->with('user')
            ->get();

        $now = now();
        $due = [];

        foreach ($bits as $bit) {
            if (! $bit->user) {
                continue;
            }
            $tz = $bit->user->timezone ?: config('app.timezone');
            $local = $now->copy()->setTimezone($tz);

            // Time must match (to the minute).
            if (substr((string) $bit->reminder_time, 0, 5) !== $local->format('H:i')) {
                continue;
            }

            // Day-of-week filter (empty = every day).
            $days = $bit->reminder_days ?? [];
            if (! empty($days) && ! in_array((int) $local->format('w'), $days, true)) {
                continue;
            }

            // Skip if already completed today.
            $doneToday = Entry::where('bit_id', $bit->id)
                ->whereDate('date', $local->format('Y-m-d'))
                ->where('status', 'done')->exists();
            if ($doneToday) {
                continue;
            }

            $due[$bit->user_id]['user'] = $bit->user;
            $due[$bit->user_id]['bits'][] = $bit;
        }

        $sent = 0;
        foreach ($due as $group) {
            if (! ($group['user']->notifications_push_enabled ?? true)) {
                continue;
            }
            $user = $group['user'];
            $bits = collect($group['bits']);

            Mail::to($user->email)->send(new BitReminderMail($user, $bits));

            // Push notification to the user's mobile devices (if any registered).
            $tokens = $user->pushTokens()->pluck('token');
            if ($tokens->isNotEmpty()) {
                $count = $bits->count();
                ExpoPush::send(
                    $tokens,
                    'Time for your Bits 💪',
                    $count === 1
                        ? "Don't forget: {$bits->first()->title}"
                        : "You've got {$count} Bits to complete today.",
                    ['screen' => 'home'],
                );
            }
            $sent++;
        }

        $this->info("Sent {$sent} reminder digest(s).");

        return self::SUCCESS;
    }
}
