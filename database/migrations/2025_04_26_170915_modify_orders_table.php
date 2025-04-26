<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_modify_orders_table.php
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('current_daily_attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->decimal('confirmed_total_price', 10, 2)->nullable();
            
            // Supprimez ces colonnes seulement si elles existent
            if (Schema::hasColumn('orders', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
            if (Schema::hasColumn('orders', 'quantity')) {
                $table->dropColumn('quantity');
            }
            if (Schema::hasColumn('orders', 'max_attempts')) {
                $table->dropColumn('max_attempts');
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['current_daily_attempts', 'last_attempt_at', 'total_price', 'confirmed_total_price']);
            
            // On ne peut pas restaurer les colonnes supprimées ici
        });
    }
};
