<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'apple'];

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        $driver = Socialite::driver($provider);

        // Apple returns to the callback as a cross-site POST, so state can't
        // round-trip via the session cookie — go stateless there.
        if ($provider === 'apple') {
            $driver = $driver->stateless();
        }

        return $driver->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        try {
            $driver = Socialite::driver($provider);
            if ($provider === 'apple') {
                $driver = $driver->stateless();
            }
            $social = $driver->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Could not sign in with '.ucfirst($provider).'. Please try again.',
            ]);
        }

        $email = $social->getEmail();
        if (! $email) {
            return redirect()->route('login')->withErrors([
                'email' => ucfirst($provider).' did not share an email address.',
            ]);
        }

        $user = User::where('email', strtolower($email))->first();
        if (! $user) {
            $user = User::create([
                'email' => strtolower($email),
                'name' => $social->getName() ?: Str::of($email)->before('@')->toString(),
                'password' => Hash::make(Str::random(40)),
            ]);
            UserProvisioning::seedNewUser($user);
        }

        if ($user->wasRecentlyCreated) {
            UserProvisioning::attributeReferral($user, request()->session()->pull('referral_code'));
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->route($user->onboarding_completed_at ? 'dashboard' : 'welcome');
    }
}
