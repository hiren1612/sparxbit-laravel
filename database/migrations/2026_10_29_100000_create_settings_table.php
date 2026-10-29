<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->text('value')->nullable();
            $table->string('type')->default('textarea'); // text | textarea
            $table->string('group')->default('About');
            $table->timestamps();
        });

        $now = now();
        DB::table('settings')->insert([
            [
                'key' => 'about.tagline', 'label' => 'About – description', 'type' => 'textarea', 'group' => 'About',
                'value' => 'Small habits, big change. Sparx Bit helps you build the little daily "Bits" that compound into the person you want to become.',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'about.footer', 'label' => 'About – footer line', 'type' => 'text', 'group' => 'About',
                'value' => 'Made with 💙 for people building better days.',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'about.version', 'label' => 'App version', 'type' => 'text', 'group' => 'About',
                'value' => '1.0.0',
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
