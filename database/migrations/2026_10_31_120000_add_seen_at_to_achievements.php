<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// When the celebration popup for a trophy was actually shown and closed. A
// trophy stays "unseen" until then, so it's offered again (on the website or
// the phone) instead of being lost if the first popup never appeared.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            $table->timestamp('seen_at')->nullable()->after('earned_at');
        });

        // Older trophies count as seen; the last 2 days' are offered again so
        // ones that never popped up still get celebrated once.
        DB::table('achievements')->where('earned_at', '<', now()->subDays(2))->update(['seen_at' => DB::raw('earned_at')]);
    }

    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            $table->dropColumn('seen_at');
        });
    }
};
