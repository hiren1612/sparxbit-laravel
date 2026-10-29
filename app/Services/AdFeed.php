<?php

namespace App\Services;

use App\Models\Bit;
use App\Models\User;
use Illuminate\Support\Collection;

class AdFeed
{
    // Minimum real Bits the VIEWER must have before any ads are shown to them.
    public const MIN_VIEWER_BITS = 3;
    // Minimum real Bits a referred user needs before they count as "active"
    // (which is what unlocks the referrer's ad).
    public const MIN_BITS = 3;

    // Sponsored ads eligible to be shown to this viewer. An advertiser qualifies
    // once they've set a business name + link AND at least one person they
    // referred has become active (≥ MIN_BITS real Bits).
    public static function eligibleAds(User $viewer): Collection
    {
        $viewerBits = Bit::where('user_id', $viewer->id)->where('is_system', false)->count();
        if ($viewerBits < self::MIN_VIEWER_BITS) {
            return collect();
        }

        $candidates = User::where('id', '!=', $viewer->id)
            ->whereNotNull('business_name')
            ->whereNotNull('ad_link')
            ->where('status', 'active')
            ->get();

        return $candidates->filter(function ($c) {
            $refereeIds = User::where('referred_by_user_id', $c->id)->pluck('id');
            foreach ($refereeIds as $rid) {
                if (Bit::where('user_id', $rid)->where('is_system', false)->count() >= self::MIN_BITS) {
                    return true; // has ≥1 active referral → ad unlocked
                }
            }

            return false;
        })->map(fn ($c) => [
            'businessName' => $c->business_name,
            'tagline' => $c->ad_tagline,
            // Only ever render http(s) links (blocks javascript:/data: payloads).
            'link' => preg_match('#^https?://#i', (string) $c->ad_link) ? $c->ad_link : null,
        ])->values();
    }

    // Pick a single ad for the viewer, rotating by day so it's stable-but-varying.
    public static function pickOne(User $viewer): ?array
    {
        $ads = self::eligibleAds($viewer)->filter(fn ($a) => trim((string) $a['businessName']) !== '')->values();
        if ($ads->isEmpty()) {
            return null;
        }
        $idx = intdiv(now()->timestamp, 86400) % $ads->count();

        return $ads[$idx] ?? $ads[0];
    }
}
