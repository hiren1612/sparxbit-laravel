<?php

namespace App\Http\Controllers;

use App\Services\WebPush;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebPushController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        WebPush::store($request->user(), $data, $request->userAgent());

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => ['required', 'string']]);
        WebPush::forget($request->user(), $request->input('endpoint'));

        return response()->json(['ok' => true]);
    }

    public function test(Request $request): JsonResponse
    {
        $sent = WebPush::sendToUser(
            $request->user(),
            'Sparx Bit 🔔',
            'Nice! Browser notifications are working. We\'ll remind you about your Bits here.',
            url('/dashboard'),
            'sparx-test',
        );

        return response()->json([
            'ok' => $sent > 0,
            'sent' => $sent,
            'configured' => WebPush::configured(),
        ]);
    }
}
