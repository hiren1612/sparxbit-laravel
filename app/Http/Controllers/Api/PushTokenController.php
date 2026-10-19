<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushToken;
use Illuminate\Http\Request;

class PushTokenController extends Controller
{
    // Register (or re-point) a device's Expo push token to the current user.
    public function store(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string|max:255',
            'platform' => 'nullable|string|in:ios,android',
        ]);

        $token = PushToken::updateOrCreate(
            ['token' => $data['token']],
            ['user_id' => $request->user()->id, 'platform' => $data['platform'] ?? null],
        );

        return response()->json(['id' => $token->id], 201);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate(['token' => 'required|string']);
        PushToken::where('token', $data['token'])->where('user_id', $request->user()->id)->delete();

        return response()->json(['ok' => true]);
    }
}
