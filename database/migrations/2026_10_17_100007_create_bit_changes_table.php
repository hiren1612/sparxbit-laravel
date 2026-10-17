<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bit_changes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bit_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('field', ['goal', 'start', 'end']);
            $table->string('from_val');
            $table->string('to_val');
            $table->timestamp('changed_at')->useCurrent();

            $table->foreign('bit_id')->references('id')->on('bits')->cascadeOnDelete();
            $table->index('bit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bit_changes');
    }
};
