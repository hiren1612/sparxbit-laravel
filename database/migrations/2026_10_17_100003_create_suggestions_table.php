<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('goal_key');
            $table->string('title');
            $table->text('default_why')->nullable();
            $table->integer('default_goal_per_week')->default(5);
            $table->string('icon_key')->default('sparkle');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestions');
    }
};
