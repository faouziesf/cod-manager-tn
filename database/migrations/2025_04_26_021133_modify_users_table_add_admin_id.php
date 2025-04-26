<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('admin_id')->after('id')->nullable()->constrained('admins')->onDelete('cascade');
            $table->enum('role', ['manager', 'employee'])->after('email_verified_at')->default('employee');
            $table->boolean('active')->default(true)->after('role');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn(['admin_id', 'role', 'active']);
        });
    }
};