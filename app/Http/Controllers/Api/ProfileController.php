<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Bit;
use App\Models\BitChange;
use App\Models\Category;
use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'fullName' => 'sometimes|nullable|string|max:60',
            'username' => ['sometimes', 'nullable', 'string', 'max:30', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'bio' => 'sometimes|nullable|string|max:160',
            'avatarUrl' => 'sometimes|nullable|string',
            'timezone' => 'sometimes|string',
            'weekStartsOn' => 'sometimes|in:monday,sunday',
            'theme' => 'sometimes|in:light,dark,system',
            'language' => 'sometimes|string|max:8',
            'notificationsPushEnabled' => 'sometimes|boolean',
            'defaultReminderTime' => 'sometimes|nullable|string',
            'notificationsWeeklySummary' => 'sometimes|boolean',
            'notificationsProductUpdates' => 'sometimes|boolean',
            'autoSync' => 'sometimes|boolean',
            'businessName' => 'sometimes|nullable|string|max:60',
            'adTagline' => 'sometimes|nullable|string|max:80',
            'adLink' => 'sometimes|nullable|url:http,https|max:200',
        ]);

        $map = [
            'fullName' => 'name', 'username' => 'username', 'bio' => 'bio', 'avatarUrl' => 'avatar_url',
            'timezone' => 'timezone', 'weekStartsOn' => 'week_starts_on', 'theme' => 'theme', 'language' => 'language',
            'notificationsPushEnabled' => 'notifications_push_enabled', 'defaultReminderTime' => 'default_reminder_time',
            'notificationsWeeklySummary' => 'notifications_weekly_summary', 'notificationsProductUpdates' => 'notifications_product_updates',
            'autoSync' => 'auto_sync', 'businessName' => 'business_name', 'adTagline' => 'ad_tagline', 'adLink' => 'ad_link',
        ];
        $patch = [];
        foreach ($map as $camel => $snake) {
            if (array_key_exists($camel, $data)) {
                $patch[$snake] = $data[$camel];
            }
        }
        $user->update($patch);

        return new UserResource($user->fresh());
    }

    // Profile photo upload (web: EditProfile photo) — stored on the public disk.
    public function avatar(Request $request)
    {
        $request->validate(['photo' => 'required|image|max:4096']);
        $path = $request->file('photo')->store('avatars', 'public');
        $request->user()->update(['avatar_url' => \Illuminate\Support\Facades\Storage::url($path)]);

        return new UserResource($request->user()->fresh());
    }

    public function archive(Request $request)
    {
        $request->user()->update(['status' => 'archived', 'archived_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request)
    {
        $request->user()->delete();

        return response()->json(['ok' => true]);
    }

    public function finishOnboarding(Request $request)
    {
        $data = $request->validate([
            'notificationsPushEnabled' => 'sometimes|boolean',
            'notificationsWeeklySummary' => 'sometimes|boolean',
            'notificationsProductUpdates' => 'sometimes|boolean',
            'defaultReminderTime' => 'sometimes|nullable|string',
            'timezone' => 'sometimes|string',
        ]);
        $patch = ['onboarding_completed_at' => now()];
        $map = [
            'notificationsPushEnabled' => 'notifications_push_enabled',
            'notificationsWeeklySummary' => 'notifications_weekly_summary',
            'notificationsProductUpdates' => 'notifications_product_updates',
            'defaultReminderTime' => 'default_reminder_time',
            'timezone' => 'timezone',
        ];
        foreach ($map as $camel => $snake) {
            if (array_key_exists($camel, $data)) {
                $patch[$snake] = $data[$camel];
            }
        }
        $request->user()->update($patch);

        return new UserResource($request->user()->fresh());
    }

    public function reset(Request $request)
    {
        $data = $request->validate(['kind' => 'required|in:last7,allHistory,startFresh']);
        $user = $request->user();

        if ($data['kind'] === 'last7') {
            $cutoff = $user->localToday()->subDays(7)->format('Y-m-d');
            Entry::where('user_id', $user->id)->whereDate('date', '>=', $cutoff)->delete();
        } elseif ($data['kind'] === 'allHistory') {
            Entry::where('user_id', $user->id)->delete();
        } else {
            Entry::where('user_id', $user->id)->delete();
            BitChange::where('user_id', $user->id)->delete();
            Bit::where('user_id', $user->id)->where('is_system', false)->delete();
            Category::where('user_id', $user->id)->where('is_system', false)->delete();
        }

        return response()->json(['ok' => true]);
    }
}
