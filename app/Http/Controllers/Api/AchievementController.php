<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AchievementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Mobile counterpart of the dashboard celebration: award any newly earned
// trophies (per-Bit goal 🎯, weekly 🏆, streak 🔥) and return them all, in
// the order to show. "celebration" (the most notable) is kept for older apps.
class AchievementController extends Controller
{
    public function sync(Request $request): JsonResponse
    {
        $user = $request->user();
        $all = array_map(fn ($a) => AchievementService::celebration($user, $a), AchievementService::syncAll($user));

        return response()->json([
            'celebration' => $all ? end($all) : null,
            'celebrations' => $all,
        ]);
    }
}
