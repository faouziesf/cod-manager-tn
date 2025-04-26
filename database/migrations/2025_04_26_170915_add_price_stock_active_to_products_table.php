<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_add_price_stock_active_to_products_table.php
        public function up()
        {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('price', 10, 2)->default(0);
                $table->integer('stock')->default(0);
                $table->boolean('active')->default(true);
            });
        }

        public function down()
        {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn(['price', 'stock', 'active']);
            });
        }
};
