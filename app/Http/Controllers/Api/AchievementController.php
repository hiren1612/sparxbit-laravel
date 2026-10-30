<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AchievementService;
use App\Services\UserProvisioning;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Mobile counterpart of the dashboard celebration: award any newly earned
// trophies (per-Bit goal 🎯, weekly 🏆, streak 🔥) and return the one to show.
class AchievementController extends Controller
{
    public function sync(Request $request): JsonResponse
    {
        $user = $request->user();
        $achievement = AchievementService::sync($user);

        if (! $achievement) {
            return response()->json(['celebration' => null]);
        }

        if (! $user->referral_code) {
            $user->update(['referral_code' => UserProvisioning::genReferralCode()]);
        }

        return response()->json([
            'celebration' => [
                'type' => $achievement->type,
                'title' => $achievement->title,
                'body' => $achievement->body,
                'shareUrl' => url('/?ref='.strtolower($user->referral_code)),
                'shareText' => 'I just unlocked "'.$achievement->title.'" on Sparx Bit! 🎉 Build better habits with me:',
            ],
        ]);
    }
}
