<?php

namespace App\Services;

use App\Models\Bit;
use App\Models\Category;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Str;

// Replaces the old handle_new_user trigger: seeds a new account's starter data.
class UserProvisioning
{
    public static function genReferralCode(): string
    {
        do {
            $code = Str::lower(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    public static function seedNewUser(User $user): void
    {
        $cats = [['Health', '#22C55E'], ['Work', '#6366F1'], ['Mind', '#A855F7']];
        foreach ($cats as [$name, $color]) {
            Category::create([
                'user_id' => $user->id,
                'name' => $name,
                'color_hex' => $color,
                'is_system' => true,
            ]);
        }

        Bit::create([
            'user_id' => $user->id,
            'title' => 'Daily Check-In',
            'why' => 'A daily moment to reflect and stay on track.',
            'goal_per_week' => 7,
            'start_date' => now()->format('Y-m-d'),
            'icon_key' => 'sparkle',
            'is_system' => true,
            'status' => 'active',
            'reminder_days' => [],
        ]);
    }

    public static function attributeReferral(User $referee, ?string $code): void
    {
        if (! $code) {
            return;
        }
        $referrer = User::where('referral_code', trim($code))->first();
        if (! $referrer || $referrer->id === $referee->id) {
            return;
        }
        $referee->update(['referred_by_user_id' => $referrer->id]);
        Referral::create([
            'referrer_id' => $referrer->id,
            'referee_id' => $referee->id,
            'code' => trim($code).':'.$referee->id,
            'status' => 'completed',
            'activated_at' => now(),
        ]);
    }
}
