<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BitResource;
use App\Models\Bit;
use App\Models\Category;
use App\Models\Goal;
use App\Models\Suggestion;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function seedBits(Request $request)
    {
        $data = $request->validate([
            'keys' => 'required|array|max:20',
            'keys.*' => 'string',
            'timezone' => 'nullable|string',
        ]);
        $user = $request->user();

        $suggestions = Suggestion::whereIn('key', $data['keys'])->get();
        $goalKeys = $suggestions->pluck('goal_key')->unique()->all();
        $goals = Goal::whereNull('user_id')->whereIn('key', $goalKeys)->get()->keyBy('key');

        $created = collect();
        foreach ($suggestions as $s) {
            $goal = $goals->get($s->goal_key);
            $categoryId = null;
            if ($goal) {
                $name = substr($goal->label, 0, 30);
                $cat = Category::firstOrCreate(
                    ['user_id' => $user->id, 'name' => $name],
                    ['color_hex' => '#6366F1'],
                );
                $categoryId = $cat->id;
            }
            $bit = Bit::create([
                'user_id' => $user->id,
                'title' => $s->title,
                'why' => $s->default_why,
                'goal_per_week' => $s->default_goal_per_week,
                'icon_key' => $s->icon_key,
                'start_date' => $user->localToday()->format('Y-m-d'),
                'goal_id' => $goal?->id,
                'category_id' => $categoryId,
                'reminder_days' => [],
            ]);
            $created->push($bit);
        }

        return BitResource::collection($created);
    }
}
