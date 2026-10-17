<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bit;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    private const MIN_BITS = 3;

    public function mine(Request $request)
    {
        $user = $request->user();
        $referees = User::where('referred_by_user_id', $user->id)
            ->orderBy('created_at')->get();

        $referrals = [];
        $active = 0;
        foreach ($referees as $ref) {
            $count = Bit::where('user_id', $ref->id)->where('is_system', false)->count();
            $isActive = $count >= self::MIN_BITS;
            if ($isActive) {
                $active++;
            }
            $referrals[] = [
                'name' => $ref->name ?: explode('@', $ref->email)[0],
                'active' => $isActive,
                'date' => $ref->created_at?->toISOString(),
            ];
        }

        $live = $active >= 1 && $user->business_name && $user->ad_link;

        return [
            'referrals' => $referrals,
            'totalCount' => $referees->count(),
            'activeCount' => $active,
            'ad' => [
                'businessName' => $user->business_name,
                'tagline' => $user->ad_tagline,
                'link' => $user->ad_link,
                'live' => (bool) $live,
            ],
        ];
    }

    public function claim(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|max:32']);
        $user = $request->user();
        if ($user->referred_by_user_id) {
            return ['ok' => true, 'alreadyClaimed' => true];
        }
        $referrer = User::where('referral_code', trim($data['code']))->first();
        if (! $referrer || $referrer->id === $user->id) {
            abort(400, 'Invalid referral code.');
        }
        $user->update(['referred_by_user_id' => $referrer->id]);
        Referral::create([
            'referrer_id' => $referrer->id,
            'referee_id' => $user->id,
            'code' => trim($data['code']).':'.$user->id,
            'status' => 'completed',
            'activated_at' => now(),
        ]);

        return ['ok' => true];
    }
}
