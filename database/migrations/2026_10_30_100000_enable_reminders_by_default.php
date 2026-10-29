<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // New users get reminders on by default.
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notifications_push_enabled')->default(true)->change();
        });

        // Existing users who already set a reminder on a Bit clearly want them —
        // the old default (off) was silently swallowing their reminders.
        DB::table('users')
            ->where('notifications_push_enabled', false)
            ->whereIn('id', DB::table('bits')->where('reminder_enabled', true)->select('user_id'))
            ->update(['notifications_push_enabled' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notifications_push_enabled')->default(false)->change();
        });
    }
};
