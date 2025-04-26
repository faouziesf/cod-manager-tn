<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 3)->default(0);
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price', 'stock', 'is_active']);
        });
    }
}