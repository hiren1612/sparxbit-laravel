<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdFeed;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function feed(Request $request)
    {
        return response()->json(AdFeed::eligibleAds($request->user())->all());
    }
}
