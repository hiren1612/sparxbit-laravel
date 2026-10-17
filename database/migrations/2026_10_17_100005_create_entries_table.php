<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bit_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['done', 'missed', 'skipped']);
            $table->enum('source', ['manual', 'reminder_tap', 'backfill'])->default('manual');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('bit_id')->references('id')->on('bits')->cascadeOnDelete();
            $table->unique(['bit_id', 'date']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
