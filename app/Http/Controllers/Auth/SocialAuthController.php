<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MagicLinkService;
use App\Services\UserProvisioning;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'apple'];

    // Marks a sign-in started by the mobile app (in its in-app browser).
    private const APP_COOKIE = 'sparx_app_auth';

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

    // Mobile app: the same Google / Apple sign-in as the website, run in the
    // app's in-app browser. The callback then hands the app a one-time token
    // (sparxbit://magic?token=…) instead of a web session. SameSite=None so the
    // flag survives Apple's cross-site POST back to the callback.
    public function appRedirect(string $provider)
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
        $secure = request()->isSecure();
        Cookie::queue(cookie(self::APP_COOKIE, '1', 15, null, null, $secure, true, false, $secure ? 'none' : 'lax'));

        return $this->redirect($provider);
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
        $forApp = (bool) request()->cookie(self::APP_COOKIE);

        try {
            $driver = Socialite::driver($provider);
            if ($provider === 'apple') {
                $driver = $driver->stateless();
            }
            $social = $driver->user();
        } catch (\Throwable $e) {
            return $this->fail($forApp, 'Could not sign in with '.ucfirst($provider).'. Please try again.');
        }

        $email = $social->getEmail();
        if (! $email) {
            return $this->fail($forApp, ucfirst($provider).' did not share an email address.');
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

        if ($forApp) {
            Cookie::queue(Cookie::forget(self::APP_COOKIE));

            // The app exchanges this single-use token for its own session.
            return redirect()->away('sparxbit://magic?token='.MagicLinkService::issueToken($user->email));
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->route($user->onboarding_completed_at ? 'dashboard' : 'welcome');
    }

    private function fail(bool $forApp, string $message)
    {
        if ($forApp) {
            Cookie::queue(Cookie::forget(self::APP_COOKIE));

            return redirect()->away('sparxbit://magic?error='.rawurlencode($message));
        }

        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
