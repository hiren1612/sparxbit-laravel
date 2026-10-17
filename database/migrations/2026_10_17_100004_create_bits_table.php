<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 50);
            $table->string('why', 300)->nullable();
            $table->string('description', 500)->nullable();
            $table->integer('goal_per_week')->default(7);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->uuid('category_id')->nullable();
            $table->uuid('goal_id')->nullable();
            $table->string('icon_key')->default('sparkle');
            $table->boolean('reminder_enabled')->default(false);
            $table->string('reminder_time')->nullable();
            $table->json('reminder_days')->nullable(); // array of weekday numbers
            $table->boolean('is_system')->default(false);
            $table->enum('status', ['active', 'paused', 'archived', 'completed'])->default('active');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('goal_id')->references('id')->on('goals')->nullOnDelete();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bits');
    }
};
