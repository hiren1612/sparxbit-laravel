<?php

namespace App\Services;

use App\Models\User;
use App\Models\WebPushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush as Pusher;

class WebPush
{
    public static function configured(): bool
    {
        return (bool) (config('services.webpush.public_key') && config('services.webpush.private_key'));
    }

    /**
     * Send a browser push to every subscription a user has registered.
     * Automatically prunes dead subscriptions (410 Gone / 404).
     *
     * @return int number of messages the push service accepted
     */
    public static function sendToUser(User $user, string $title, string $body, string $url = '/', ?string $tag = null): int
    {
        $subs = $user->webPushSubscriptions()->get();
        if ($subs->isEmpty() || ! self::configured()) {
            return 0;
        }

        try {
            $pusher = new Pusher([
                'VAPID' => [
                    'subject' => config('services.webpush.subject'),
                    'publicKey' => config('services.webpush.public_key'),
                    'privateKey' => config('services.webpush.private_key'),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('WebPush init failed: '.$e->getMessage());

            return 0;
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'tag' => $tag ?: 'sparx',
        ]);

        $byEndpoint = [];
        foreach ($subs as $sub) {
            $byEndpoint[$sub->endpoint] = $sub;
            try {
                $pusher->queueNotification(
                    Subscription::create([
                        'endpoint' => $sub->endpoint,
                        'keys' => ['p256dh' => $sub->p256dh, 'auth' => $sub->auth],
                    ]),
                    $payload
                );
            } catch (\Throwable $e) {
                Log::warning('WebPush queue failed: '.$e->getMessage());
            }
        }

        $sent = 0;
        foreach ($pusher->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            if ($report->isSuccess()) {
                $sent++;

                continue;
            }
            // 404/410 → the subscription is permanently gone; remove it.
            if ($report->isSubscriptionExpired() && isset($byEndpoint[$endpoint])) {
                $byEndpoint[$endpoint]->delete();
            }
        }

        return $sent;
    }

    public static function store(User $user, array $sub, ?string $ua = null): WebPushSubscription
    {
        $endpoint = $sub['endpoint'];

        return WebPushSubscription::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $endpoint)],
            [
                'user_id' => $user->id,
                'endpoint' => $endpoint,
                'p256dh' => $sub['keys']['p256dh'] ?? '',
                'auth' => $sub['keys']['auth'] ?? '',
                'ua' => $ua ? substr($ua, 0, 255) : null,
            ]
        );
    }

    public static function forget(User $user, string $endpoint): void
    {
        $user->webPushSubscriptions()->where('endpoint_hash', hash('sha256', $endpoint))->delete();
    }
}
