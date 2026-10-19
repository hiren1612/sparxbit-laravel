<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MagicLinkService;
use App\Services\UserProvisioning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MagicLinkController extends Controller
{
    public function verify(Request $request, string $token)
    {
        $user = MagicLinkService::consume($token);

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => 'That sign-in link is invalid or has expired. Please request a new one.',
            ]);
        }

        if ($user->wasRecentlyCreated) {
            UserProvisioning::attributeReferral($user, $request->session()->pull('referral_code'));
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route($user->onboarding_completed_at ? 'dashboard' : 'welcome');
    }
}
