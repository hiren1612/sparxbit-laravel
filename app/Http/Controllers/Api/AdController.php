<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bit;
use App\Models\User;
use Illuminate\Http\Request;

class AdController extends Controller
{
    private const MIN_VIEWER_BITS = 3;
    private const MIN_BITS = 3;

    public function feed(Request $request)
    {
        $user = $request->user();
        $viewerBits = Bit::where('user_id', $user->id)->where('is_system', false)->count();
        if ($viewerBits < self::MIN_VIEWER_BITS) {
            return response()->json([]);
        }

        $candidates = User::where('id', '!=', $user->id)
            ->whereNotNull('business_name')
            ->whereNotNull('ad_link')
            ->where('status', 'active')
            ->get();

        $out = [];
        foreach ($candidates as $c) {
            $active = 0;
            $refereeIds = User::where('referred_by_user_id', $c->id)->pluck('id');
            foreach ($refereeIds as $rid) {
                if (Bit::where('user_id', $rid)->where('is_system', false)->count() >= self::MIN_BITS) {
                    $active++;
                }
            }
            if ($active >= 1) {
                $out[] = [
                    'businessName' => $c->business_name,
                    'tagline' => $c->ad_tagline,
                    'link' => $c->ad_link,
                ];
            }
        }

        return response()->json($out);
    }
}
