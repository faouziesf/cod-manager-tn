<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->timestamps();
        });
        
        // Insérer les paramètres par défaut
        DB::table('settings')->insert([
            ['key' => 'standard_max_daily_attempts', 'value' => '3', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'standard_max_attempts', 'value' => '9', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'standard_attempt_interval', 'value' => '2.5', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'scheduled_max_daily_attempts', 'value' => '2', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'scheduled_max_attempts', 'value' => '5', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'scheduled_attempt_interval', 'value' => '3.5', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'old_attempt_interval', 'value' => '3.5', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
}