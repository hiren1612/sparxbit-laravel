<?php

namespace App\Services;

use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MagicLinkService
{
    // Create a single-use, 20-minute magic-link token for an email and return
    // the full sign-in URL to send them.
    public static function issue(string $email): string
    {
        $email = strtolower(trim($email));
        $token = Str::random(48);

        MagicLink::create([
            'email' => $email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(20),
        ]);

        return route('magic.verify', ['token' => $token]);
    }

    // Consume a token: returns the (found-or-created, provisioned) user, or null
    // when the token is invalid / expired / already used.
    public static function consume(string $token): ?User
    {
        $link = MagicLink::where('token_hash', hash('sha256', $token))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $link) {
            return null;
        }

        $link->update(['used_at' => now()]);

        $user = User::where('email', $link->email)->first();
        if (! $user) {
            $user = User::create([
                'email' => $link->email,
                'name' => Str::of($link->email)->before('@')->toString(),
                'password' => Hash::make(Str::random(40)),
            ]);
            UserProvisioning::seedNewUser($user);
        }

        return $user;
    }
}
