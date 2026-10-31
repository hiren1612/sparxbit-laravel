<?php

namespace App\Services;

use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MagicLinkService
{
    // Create a single-use, 20-minute magic-link token for an email and return
    // the full sign-in URL to send them. $forApp links open the mobile app.
    public static function issue(string $email, bool $forApp = false): string
    {
        $token = self::issueToken($email);

        return route($forApp ? 'magic.app' : 'magic.verify', ['token' => $token]);
    }

    // Create a single-use, 20-minute token (the raw value; only its hash is stored).
    public static function issueToken(string $email): string
    {
        $token = Str::random(48);
        MagicLink::create([
            'email' => strtolower(trim($email)),
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(20),
        ]);

        return $token;
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
