<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'attempt_count')) {
                $table->integer('attempt_count')->default(0);
            }
            if (!Schema::hasColumn('orders', 'daily_attempt_count')) {
                $table->integer('daily_attempt_count')->default(0);
            }
            if (!Schema::hasColumn('orders', 'next_attempt_at')) {
                $table->timestamp('next_attempt_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'scheduled_date')) {
                $table->timestamp('scheduled_date')->nullable();
            }
            if (!Schema::hasColumn('orders', 'max_attempts')) {
                $table->integer('max_attempts')->default(9);
            }
            if (!Schema::hasColumn('orders', 'max_daily_attempts')) {
                $table->integer('max_daily_attempts')->default(3);
            }
            if (!Schema::hasColumn('orders', 'confirmed_price')) {
                $table->decimal('confirmed_price', 10, 3)->nullable();
            }
            if (!Schema::hasColumn('orders', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->foreign('assigned_to')->references('id')->on('users');
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'attempt_count', 'daily_attempt_count', 'next_attempt_at',
                'scheduled_date', 'max_attempts', 'max_daily_attempts', 
                'confirmed_price'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            if (Schema::hasColumn('orders', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
                $table->dropColumn('assigned_to');
            }
        });
    }
};