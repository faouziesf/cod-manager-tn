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
            $table->string('key');
            $table->text('value');
            $table->timestamps();
        });
        
        // Insérer les paramètres par défaut
        DB::table('settings')->insert([
            ['key' => 'standard_max_daily_attempts', 'value' => '3'],
            ['key' => 'standard_max_attempts', 'value' => '9'],
            ['key' => 'standard_attempt_interval', 'value' => '2.5'],
            ['key' => 'scheduled_max_daily_attempts', 'value' => '2'],
            ['key' => 'scheduled_max_attempts', 'value' => '5'],
            ['key' => 'scheduled_attempt_interval', 'value' => '3.5'],
            ['key' => 'old_attempt_interval', 'value' => '3.5'],
            ['key' => 'woocommerce_api_key', 'value' => ''],
            ['key' => 'woocommerce_api_secret', 'value' => ''],
            ['key' => 'woocommerce_status_to_import', 'value' => 'processing'],
            ['key' => 'google_sheet_id', 'value' => ''],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
}