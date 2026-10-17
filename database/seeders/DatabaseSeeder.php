<?php

namespace Database\Seeders;

use App\Models\Goal;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Goals (global catalogue; user_id = null) ---
        $goals = [
            ['key' => 'health', 'label' => 'Health & Fitness', 'icon_key' => 'heart'],
            ['key' => 'career', 'label' => 'Career & Growth', 'icon_key' => 'briefcase'],
            ['key' => 'mental', 'label' => 'Mind & Calm', 'icon_key' => 'brain'],
            ['key' => 'family', 'label' => 'Family & Home', 'icon_key' => 'home'],
            ['key' => 'social', 'label' => 'Friends & Social', 'icon_key' => 'users'],
            ['key' => 'personal', 'label' => 'Personal Projects', 'icon_key' => 'sparkle'],
            ['key' => 'other', 'label' => 'Something Else', 'icon_key' => 'star'],
        ];
        foreach ($goals as $g) {
            Goal::firstOrCreate(
                ['user_id' => null, 'key' => $g['key']],
                ['label' => $g['label'], 'icon_key' => $g['icon_key']],
            );
        }

        // --- Starter suggestions ---
        $suggestions = [
            ['key' => 'move_daily', 'goal_key' => 'health', 'title' => 'Move for 20 minutes', 'default_why' => 'Feel stronger and more energetic.', 'default_goal_per_week' => 5, 'icon_key' => 'run'],
            ['key' => 'water', 'goal_key' => 'health', 'title' => 'Drink enough water', 'default_goal_per_week' => 7, 'icon_key' => 'droplet'],
            ['key' => 'sleep', 'goal_key' => 'health', 'title' => 'Sleep by 11pm', 'default_goal_per_week' => 6, 'icon_key' => 'moon'],
            ['key' => 'deep_work', 'goal_key' => 'career', 'title' => 'One hour of deep work', 'default_goal_per_week' => 5, 'icon_key' => 'target'],
            ['key' => 'learn', 'goal_key' => 'career', 'title' => 'Learn something new', 'default_goal_per_week' => 4, 'icon_key' => 'book'],
            ['key' => 'meditate', 'goal_key' => 'mental', 'title' => 'Meditate for 10 minutes', 'default_goal_per_week' => 5, 'icon_key' => 'lotus'],
            ['key' => 'journal', 'goal_key' => 'mental', 'title' => 'Write in my journal', 'default_goal_per_week' => 4, 'icon_key' => 'pen'],
            ['key' => 'family_time', 'goal_key' => 'family', 'title' => 'Quality time with family', 'default_goal_per_week' => 4, 'icon_key' => 'home'],
            ['key' => 'reach_out', 'goal_key' => 'social', 'title' => 'Reach out to a friend', 'default_goal_per_week' => 3, 'icon_key' => 'phone'],
            ['key' => 'side_project', 'goal_key' => 'personal', 'title' => 'Work on my side project', 'default_goal_per_week' => 3, 'icon_key' => 'sparkle'],
        ];
        foreach ($suggestions as $s) {
            Suggestion::updateOrCreate(['key' => $s['key']], $s);
        }

        // --- Admin user (for the Filament panel at /admin) ---
        User::firstOrCreate(
            ['email' => 'info@yourdeveloperonline.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'referral_code' => 'admin001',
                'onboarding_completed_at' => now(),
            ],
        );
    }
}
