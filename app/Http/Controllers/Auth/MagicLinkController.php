<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MagicLinkService;
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

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route($user->onboarding_completed_at ? 'dashboard' : 'welcome');
    }
}
