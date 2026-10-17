<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\Suggestion;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function goals(Request $request)
    {
        $userId = $request->user()->id;

        return Goal::where(function ($q) use ($userId) {
            $q->whereNull('user_id')->orWhere('user_id', $userId);
        })->orderBy('label')->get()->map(fn ($g) => [
            'id' => $g->id,
            'userId' => $g->user_id,
            'key' => $g->key,
            'label' => $g->label,
            'iconKey' => $g->icon_key,
        ]);
    }

    public function suggestions()
    {
        return Suggestion::orderBy('title')->get()->map(fn ($s) => [
            'key' => $s->key,
            'goalKey' => $s->goal_key,
            'title' => $s->title,
            'defaultWhy' => $s->default_why,
            'defaultGoalPerWeek' => (int) $s->default_goal_per_week,
            'iconKey' => $s->icon_key,
        ]);
    }
}
