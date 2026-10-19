<?php

namespace App\Services;

use App\Models\PushToken;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPush
{
    // Send a push notification to a set of Expo push tokens. Expo's service
    // relays to APNs (iOS) and FCM (Android), so no per-platform setup is needed
    // beyond the mobile app registering its token.
    public static function send(Collection $tokens, string $title, string $body, array $data = []): void
    {
        $tokens = $tokens->filter()->unique()->values();
        if ($tokens->isEmpty()) {
            return;
        }

        $messages = $tokens->map(fn ($t) => [
            'to' => $t,
            'title' => $title,
            'body' => $body,
            'sound' => 'default',
            'data' => $data,
        ])->all();

        try {
            $res = Http::acceptJson()->post('https://exp.host/--/api/v2/push/send', $messages);
            $receipts = $res->json('data') ?? [];

            // Prune tokens Expo reports as no longer registered.
            foreach ($receipts as $i => $r) {
                if (($r['status'] ?? null) === 'error'
                    && ($r['details']['error'] ?? null) === 'DeviceNotRegistered'
                    && isset($tokens[$i])) {
                    PushToken::where('token', $tokens[$i])->delete();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Expo push failed: '.$e->getMessage());
        }
    }
}
