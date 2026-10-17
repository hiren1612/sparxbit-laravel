<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'nullable|in:bug,feature,general',
            'message' => 'required|string|min:10|max:2000',
        ]);
        Feedback::create([
            'user_id' => $request->user()->id,
            'type' => $data['type'] ?? 'general',
            'message' => trim($data['message']),
        ]);

        return response()->json(['ok' => true]);
    }
}
