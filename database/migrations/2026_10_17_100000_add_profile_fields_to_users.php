<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Extends the default users table with Sparx Bit profile fields (merging what
// Supabase/Node split into auth.users + profiles into one table).
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('email');
            $table->text('bio')->nullable();
            $table->text('avatar_url')->nullable();
            $table->string('timezone')->default('UTC');
            $table->enum('week_starts_on', ['monday', 'sunday'])->default('monday');
            $table->enum('theme', ['light', 'dark', 'system'])->default('system');
            $table->string('language', 8)->default('en');
            $table->boolean('notifications_push_enabled')->default(false);
            $table->string('default_reminder_time')->nullable();
            $table->boolean('notifications_weekly_summary')->default(true);
            $table->boolean('notifications_product_updates')->default(false);
            $table->enum('status', ['active', 'archived', 'deleted'])->default('active');
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->boolean('auto_sync')->default(true);
            $table->string('referral_code')->nullable()->unique();
            // Plain column (no DB FK) for cross-DB (SQLite local / MySQL prod) safety.
            $table->unsignedBigInteger('referred_by_user_id')->nullable()->index();
            $table->string('premium_tier')->nullable();
            $table->string('business_name')->nullable();
            $table->string('ad_tagline')->nullable();
            $table->string('ad_link')->nullable();
            $table->boolean('is_admin')->default(false); // Filament panel access
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'bio', 'avatar_url', 'timezone', 'week_starts_on', 'theme',
                'language', 'notifications_push_enabled', 'default_reminder_time',
                'notifications_weekly_summary', 'notifications_product_updates', 'status',
                'onboarding_completed_at', 'archived_at', 'auto_sync', 'referral_code',
                'referred_by_user_id', 'premium_tier', 'business_name', 'ad_tagline',
                'ad_link', 'is_admin',
            ]);
        });
    }
};
