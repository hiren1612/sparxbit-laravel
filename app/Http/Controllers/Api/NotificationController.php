<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationFeed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Mobile counterpart of the website's bell: the same feed (fired reminders +
// weekly goals, today's wins, streak milestones) and the unread dot.
class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'unread' => NotificationFeed::hasUnread($user),
            'items' => NotificationFeed::items($user)->map(fn ($i) => [
                'icon' => $i['icon'],
                'tone' => $i['tone'],
                'title' => $i['title'],
                'body' => $i['body'],
                'when' => $i['when'],
            ])->values(),
        ]);
    }

    public function seen(Request $request): JsonResponse
    {
        NotificationFeed::markSeen($request->user());

        return response()->json(['ok' => true]);
    }
}
