<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AchievementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Mobile counterpart of the dashboard celebration. Awards any newly earned
// trophies (per-Bit goal 🎯, weekly 🏆, streak 🔥) and returns every trophy
// whose popup hasn't been shown yet; the app reports each one it showed via
// /achievements/seen. "celebration" (only a newly awarded one) is kept for
// older app builds, which never report seen.
class AchievementController extends Controller
{
    public function sync(Request $request): JsonResponse
    {
        $user = $request->user();
        $new = AchievementService::syncAll($user);

        return response()->json([
            'celebration' => $new ? AchievementService::celebration($user, end($new)) : null,
            'celebrations' => array_map(fn ($a) => AchievementService::celebration($user, $a), AchievementService::unseen($user)),
        ]);
    }

    public function seen(Request $request): JsonResponse
    {
        $data = $request->validate(['ids' => 'required|array|max:50', 'ids.*' => 'integer']);
        AchievementService::markSeen($request->user(), $data['ids']);

        return response()->json(['ok' => true]);
    }
}
