<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // null user_id = global seed catalogue
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->string('icon_key');
            $table->timestamps();
            $table->unique(['user_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
