<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->integer('quantity')->default(1);
            $table->string('customer_name');
            $table->string('customer_phone1');
            $table->string('customer_phone2')->nullable();
            $table->text('delivery_address');
            $table->string('region');
            $table->string('city');
            $table->enum('status', ['new', 'confirmed', 'dated', 'recall', 'canceled'])->default('new');
            $table->date('callback_date')->nullable();
            $table->integer('max_attempts')->default(3);
            $table->integer('current_attempts')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};