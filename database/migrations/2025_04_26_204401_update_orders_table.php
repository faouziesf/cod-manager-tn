<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('attempt_count')->default(0);
            $table->integer('daily_attempt_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamp('scheduled_date')->nullable();
            $table->integer('max_attempts')->default(9);
            $table->integer('max_daily_attempts')->default(3);
            $table->decimal('confirmed_price', 10, 3)->nullable();
            $table->enum('status', ['new', 'confirmed', 'cancelled', 'scheduled', 'old'])->default('new');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['attempt_count', 'daily_attempt_count', 'last_attempt_at', 'next_attempt_at', 'scheduled_date', 'max_attempts', 'max_daily_attempts', 'confirmed_price', 'status', 'assigned_to']);
        });
    }
}